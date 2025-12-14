<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once '../config/database.php';

$method = $_SERVER['REQUEST_METHOD'];
$path = $_GET['path'] ?? '';

$db = new Database();
$conn = $db->getConnection();

if (!$conn) {
    http_response_code(503);
    echo json_encode(['error' => 'Database connection failed. Please try again in a few moments.']);
    exit;
}

switch ($method) {
    case 'GET':
        if ($path === 'podcasts') {
            $stmt = $conn->query("SELECT * FROM podcasts ORDER BY created_at DESC");
            $podcasts = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($podcasts);
        } elseif (preg_match('/^podcasts\/(\d+)$/', $path, $matches)) {
            $podcast_id = $matches[1];
            $stmt = $conn->prepare("SELECT * FROM podcasts WHERE id = ?");
            $stmt->execute([$podcast_id]);
            $podcast = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($podcast) {
                $stmt = $conn->prepare("SELECT * FROM episodes WHERE podcast_id = ? ORDER BY episode_number ASC");
                $stmt->execute([$podcast_id]);
                $podcast['episodes'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            
            echo json_encode($podcast);
        } elseif ($path === 'episodes') {
            $stmt = $conn->query("SELECT e.*, p.title as podcast_title FROM episodes e 
                                  JOIN podcasts p ON e.podcast_id = p.id 
                                  ORDER BY e.published_at DESC");
            $episodes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($episodes);
        }
        break;
        
    case 'POST':
        if ($path === 'login') {
            $data = json_decode(file_get_contents('php://input'), true);
            $username = $data['username'] ?? '';
            $password = $data['password'] ?? '';
            
            if (empty($username) || empty($password)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Логин и пароль обязательны']);
                break;
            }
            
            $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user) {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Пользователь не найден']);
                break;
            }
            
            if (password_verify($password, $user['password'])) {
                $_SESSION['admin'] = true;
                echo json_encode(['success' => true]);
            } else {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Неверный пароль']);
            }
        } elseif ($path === 'podcasts' && isset($_SESSION['admin'])) {
            $data = json_decode(file_get_contents('php://input'), true);
            $stmt = $conn->prepare("INSERT INTO podcasts (title, description, author) VALUES (?, ?, ?)");
            $stmt->execute([$data['title'], $data['description'], $data['author']]);
            echo json_encode(['success' => true, 'id' => $conn->lastInsertId()]);
        } elseif ($path === 'episodes' && isset($_SESSION['admin'])) {
            $data = json_decode(file_get_contents('php://input'), true);
            $stmt = $conn->prepare("INSERT INTO episodes (podcast_id, title, description, audio_file, episode_number) 
                                   VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $data['podcast_id'], 
                $data['title'], 
                $data['description'], 
                $data['audio_file'], 
                $data['episode_number'] ?? 0
            ]);
            echo json_encode(['success' => true, 'id' => $conn->lastInsertId()]);
        }
        break;
        
    case 'DELETE':
        if (preg_match('/^podcasts\/(\d+)$/', $path, $matches) && isset($_SESSION['admin'])) {
            $podcast_id = $matches[1];
            $stmt = $conn->prepare("DELETE FROM podcasts WHERE id = ?");
            $stmt->execute([$podcast_id]);
            echo json_encode(['success' => true]);
        } elseif (preg_match('/^episodes\/(\d+)$/', $path, $matches) && isset($_SESSION['admin'])) {
            $episode_id = $matches[1];
            $stmt = $conn->prepare("DELETE FROM episodes WHERE id = ?");
            $stmt->execute([$episode_id]);
            echo json_encode(['success' => true]);
        }
        break;
}
?>

