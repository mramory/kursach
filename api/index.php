<?php
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_samesite', 'Lax');

session_start();
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Origin: ' . ($_SERVER['HTTP_ORIGIN'] ?? '*'));
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
        } elseif (preg_match('/^search\/episodes\/(\d+)$/', $path, $matches)) {
            $podcast_id = $matches[1];
            $query = $_GET['q'] ?? '';
            
            if (empty($query)) {
                $stmt = $conn->prepare("SELECT * FROM episodes WHERE podcast_id = ? ORDER BY episode_number ASC");
                $stmt->execute([$podcast_id]);
            } else {
                $searchTerm = '%' . $query . '%';
                $stmt = $conn->prepare("SELECT * FROM episodes 
                                       WHERE podcast_id = ? AND (title LIKE ? OR description LIKE ?) 
                                       ORDER BY episode_number ASC");
                $stmt->execute([$podcast_id, $searchTerm, $searchTerm]);
            }
            
            $episodes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($episodes);
        } elseif ($path === 'search') {
            $query = $_GET['q'] ?? '';
            $type = $_GET['type'] ?? 'all';
            
            if (empty($query)) {
                echo json_encode(['podcasts' => [], 'episodes' => []]);
                break;
            }
            
            $searchTerm = '%' . $query . '%';
            $results = ['podcasts' => [], 'episodes' => []];
            
            if ($type === 'all' || $type === 'podcasts') {
                $stmt = $conn->prepare("SELECT * FROM podcasts 
                                       WHERE title LIKE ? OR description LIKE ? OR author LIKE ? 
                                       ORDER BY created_at DESC");
                $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
                $results['podcasts'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            
            if ($type === 'all' || $type === 'episodes') {
                $stmt = $conn->prepare("SELECT e.*, p.title as podcast_title FROM episodes e 
                                       JOIN podcasts p ON e.podcast_id = p.id 
                                       WHERE e.title LIKE ? OR e.description LIKE ? 
                                       ORDER BY e.published_at DESC");
                $stmt->execute([$searchTerm, $searchTerm]);
                $results['episodes'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            
            echo json_encode($results);
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
                if (session_id()) {
                    setcookie(session_name(), session_id(), [
                        'expires' => time() + 3600,
                        'path' => '/',
                        'samesite' => 'Lax',
                        'httponly' => true
                    ]);
                }
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
        
    case 'PUT':
        if (!isset($_SESSION['admin'])) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Unauthorized - Session not set']);
            break;
        }
        
        if (preg_match('/^podcasts\/(\d+)$/', $path, $matches)) {
            $podcast_id = $matches[1];
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);
            
            if (!$data) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Invalid JSON data']);
                break;
            }
            
            try {
                $stmt = $conn->prepare("UPDATE podcasts SET title = ?, description = ?, author = ? WHERE id = ?");
                $stmt->execute([$data['title'], $data['description'], $data['author'], $podcast_id]);
                echo json_encode(['success' => true]);
            } catch (PDOException $e) {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
        } elseif (preg_match('/^episodes\/(\d+)$/', $path, $matches)) {
            $episode_id = $matches[1];
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);
            
            if (!$data) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Invalid JSON data']);
                break;
            }
            
            try {
                $stmt = $conn->prepare("UPDATE episodes SET podcast_id = ?, title = ?, description = ?, audio_file = ?, episode_number = ? WHERE id = ?");
                $stmt->execute([
                    $data['podcast_id'], 
                    $data['title'], 
                    $data['description'], 
                    $data['audio_file'], 
                    $data['episode_number'] ?? 0,
                    $episode_id
                ]);
                echo json_encode(['success' => true]);
            } catch (PDOException $e) {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Not found']);
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

