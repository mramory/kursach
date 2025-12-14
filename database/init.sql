SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS podcasts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    cover_image VARCHAR(255),
    author VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS episodes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    podcast_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    audio_file VARCHAR(255) NOT NULL,
    episode_number INT,
    published_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (podcast_id) REFERENCES podcasts(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO users (username, password) VALUES 
('admin', '$2y$10$Ek7TZLsLiSLT3IrZXPgmruN31UsHYPlJ8iDVjwoG1hl96FoT3gWFa');

INSERT IGNORE INTO podcasts (title, description, author) VALUES 
('Технологии будущего', 'Подкаст о новых технологиях и инновациях. Обсуждаем искусственный интеллект, блокчейн, квантовые вычисления и другие прорывные технологии.', 'Иван Иванов'),
('История России', 'Увлекательные рассказы об истории нашей страны. От древних времен до современности, важные события и личности.', 'Петр Петров'),
('Наука и открытия', 'Еженедельный подкаст о последних научных открытиях, исследованиях и достижениях в различных областях науки.', 'Мария Соколова'),
('Бизнес и стартапы', 'Интервью с успешными предпринимателями, обсуждение бизнес-стратегий и истории создания компаний.', 'Алексей Кузнецов'),
('Культура и искусство', 'Разговоры о кино, музыке, литературе и современном искусстве. Интервью с деятелями культуры.', 'Елена Волкова'),
('Психология и саморазвитие', 'Практические советы по личностному росту, психологии отношений и работе над собой.', 'Дмитрий Морозов'),
('Спорт и здоровье', 'Обсуждение спортивных событий, тренировок, здорового образа жизни и питания.', 'Сергей Лебедев'),
('Путешествия', 'Истории о путешествиях по разным странам, советы туристам и рассказы о культурах мира.', 'Анна Новикова');

Технологии будущего"
INSERT IGNORE INTO episodes (podcast_id, title, description, audio_file, episode_number) VALUES 
(1, 'Искусственный интеллект в 2024 году', 'Обзор последних достижений в области ИИ, нейросети и машинное обучение.', 'https://example.com/audio/tech1.mp3', 1),
(1, 'Блокчейн и криптовалюты', 'Как работает блокчейн технология и что ждет криптовалюты в будущем.', 'https://example.com/audio/tech2.mp3', 2),
(1, 'Квантовые компьютеры', 'Революция в вычислениях: что такое квантовые компьютеры и как они изменят мир.', 'https://example.com/audio/tech3.mp3', 3),
(1, 'Виртуальная и дополненная реальность', 'AR и VR технологии: от игр до профессионального применения.', 'https://example.com/audio/tech4.mp3', 4);

История России"
INSERT IGNORE INTO episodes (podcast_id, title, description, audio_file, episode_number) VALUES 
(2, 'Киевская Русь: истоки государства', 'Образование древнерусского государства и его ранняя история.', 'https://example.com/audio/history1.mp3', 1),
(2, 'Монгольское нашествие', 'Как монголы завоевали Русь и что это означало для развития страны.', 'https://example.com/audio/history2.mp3', 2),
(2, 'Петр Великий и его реформы', 'Эпоха Петра I: преобразования, которые изменили Россию.', 'https://example.com/audio/history3.mp3', 3),
(2, 'Отечественная война 1812 года', 'Великая война с Наполеоном и ее значение для России.', 'https://example.com/audio/history4.mp3', 4),
(2, 'Революция 1917 года', 'События, которые привели к падению империи и созданию нового государства.', 'https://example.com/audio/history5.mp3', 5);

Наука и открытия"
INSERT IGNORE INTO episodes (podcast_id, title, description, audio_file, episode_number) VALUES 
(3, 'Открытие новых экзопланет', 'Астрономы находят все больше планет, похожих на Землю. Что это значит?', 'https://example.com/audio/science1.mp3', 1),
(3, 'Генная терапия: будущее медицины', 'Как редактирование генов может излечить неизлечимые болезни.', 'https://example.com/audio/science2.mp3', 2),
(3, 'Изменение климата: факты и мифы', 'Что наука говорит о глобальном потеплении и что мы можем сделать.', 'https://example.com/audio/science3.mp3', 3);

Бизнес и стартапы"
INSERT IGNORE INTO episodes (podcast_id, title, description, audio_file, episode_number) VALUES 
(4, 'Как создать успешный стартап', 'Интервью с основателем IT-компании о первых шагах в бизнесе.', 'https://example.com/audio/business1.mp3', 1),
(4, 'Инвестиции в стартапы', 'Как привлечь инвестиции и на что обращают внимание инвесторы.', 'https://example.com/audio/business2.mp3', 2),
(4, 'Маркетинг в цифровую эпоху', 'Современные инструменты продвижения бизнеса в интернете.', 'https://example.com/audio/business3.mp3', 3),
(4, 'Удаленная работа: плюсы и минусы', 'Как организовать эффективную удаленную команду.', 'https://example.com/audio/business4.mp3', 4);

Культура и искусство"
INSERT IGNORE INTO episodes (podcast_id, title, description, audio_file, episode_number) VALUES 
(5, 'Современное российское кино', 'Обзор лучших фильмов последних лет и тенденции в кинематографе.', 'https://example.com/audio/culture1.mp3', 1),
(5, 'Музыкальные фестивали 2024', 'Какие музыкальные события стоит посетить в этом году.', 'https://example.com/audio/culture2.mp3', 2),
(5, 'Литература: что читать сейчас', 'Рекомендации книг от известных критиков и писателей.', 'https://example.com/audio/culture3.mp3', 3);

Психология и саморазвитие"
INSERT IGNORE INTO episodes (podcast_id, title, description, audio_file, episode_number) VALUES 
(6, 'Как справиться со стрессом', 'Практические техники управления стрессом в повседневной жизни.', 'https://example.com/audio/psychology1.mp3', 1),
(6, 'Тайм-менеджмент: как все успевать', 'Эффективные методы планирования времени и повышения продуктивности.', 'https://example.com/audio/psychology2.mp3', 2),
(6, 'Эмоциональный интеллект', 'Что такое EQ и как его развивать для успеха в жизни и карьере.', 'https://example.com/audio/psychology3.mp3', 3),
(6, 'Привычки успешных людей', 'Какие привычки помогают достигать целей и жить полной жизнью.', 'https://example.com/audio/psychology4.mp3', 4);

Спорт и здоровье"
INSERT IGNORE INTO episodes (podcast_id, title, description, audio_file, episode_number) VALUES 
(7, 'Тренировки дома: с чего начать', 'Эффективные упражнения для домашних тренировок без специального оборудования.', 'https://example.com/audio/sport1.mp3', 1),
(7, 'Правильное питание для активных людей', 'Как составить сбалансированный рацион для спортивного образа жизни.', 'https://example.com/audio/sport2.mp3', 2),
(7, 'Восстановление после тренировок', 'Важность отдыха и методы восстановления для лучших результатов.', 'https://example.com/audio/sport3.mp3', 3);

Путешествия"
INSERT IGNORE INTO episodes (podcast_id, title, description, audio_file, episode_number) VALUES 
(8, 'Япония: страна контрастов', 'Путешествие по Японии: от традиций до современных технологий.', 'https://example.com/audio/travel1.mp3', 1),
(8, 'Европа на выходные', 'Как спланировать бюджетное путешествие по европейским городам.', 'https://example.com/audio/travel2.mp3', 2),
(8, 'Россия: неизведанные места', 'Скрытые жемчужины нашей страны, которые стоит посетить.', 'https://example.com/audio/travel3.mp3', 3),
(8, 'Путешествие автостопом', 'Истории и советы для тех, кто хочет путешествовать автостопом.', 'https://example.com/audio/travel4.mp3', 4);

