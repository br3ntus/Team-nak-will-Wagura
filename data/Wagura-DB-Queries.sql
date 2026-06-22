-- ============================================================
-- Team-nak-will
-- Members:
-- Villafranca, Brent Aldhee (Project Manager)
-- Balantac, Zeljhay
-- Mengote, Nicole
-- Quibral, Mark Anthony
-- ============================================================

-- ============================================================
-- WAGURA_DB
-- ============================================================

CREATE DATABASE IF NOT EXISTS wagura_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE wagura_db;

-- ============================================================
-- TABLE CREATION
-- ============================================================

CREATE TABLE IF NOT EXISTS admins (
    admin_id   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(100)        NOT NULL,
    email      VARCHAR(150) UNIQUE NOT NULL,
    password   VARCHAR(255)        NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS users (
    user_id    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(75)         NOT NULL,
    last_name  VARCHAR(75)         NOT NULL,
    email      VARCHAR(150) UNIQUE NOT NULL,
    username   VARCHAR(75)  UNIQUE NOT NULL,
    password   VARCHAR(255)        NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS categories (
    category_id   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) UNIQUE NOT NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS breeds (
    breed_id   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    breed_name VARCHAR(100) NOT NULL,
    pet_type   ENUM('Dog', 'Cat') NOT NULL,
    UNIQUE KEY uq_breed (breed_name, pet_type)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS pets (
    pet_id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id       INT UNSIGNED NOT NULL,
    breed_id      INT UNSIGNED NOT NULL,
    pet_code      VARCHAR(20)  UNIQUE NOT NULL,
    name          VARCHAR(100) NOT NULL,
    date_of_birth DATE,
    weight        DECIMAL(5,2),
    sex           ENUM('Male', 'Female'),
    color         VARCHAR(100),
    medical_notes TEXT,
    photo         VARCHAR(255),
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_pets_user  FOREIGN KEY (user_id)  REFERENCES users(user_id)  ON DELETE CASCADE,
    CONSTRAINT fk_pets_breed FOREIGN KEY (breed_id) REFERENCES breeds(breed_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS health_logs (
    log_id     INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pet_id     INT UNSIGNED NOT NULL,
    log_type   ENUM('Feeding', 'Weight', 'Vet Visit', 'Symptoms') NOT NULL,
    log_date   DATE NOT NULL,
    log_time   TIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_logs_pet FOREIGN KEY (pet_id) REFERENCES pets(pet_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS feeding_logs (
    log_id           INT UNSIGNED PRIMARY KEY,
    food_description TEXT NOT NULL,
    CONSTRAINT fk_feeding_log FOREIGN KEY (log_id) REFERENCES health_logs(log_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS weight_logs (
    log_id    INT UNSIGNED PRIMARY KEY,
    weight_kg DECIMAL(5,2) NOT NULL,
    CONSTRAINT fk_weight_log FOREIGN KEY (log_id) REFERENCES health_logs(log_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS vet_logs (
    log_id       INT UNSIGNED PRIMARY KEY,
    clinic_name  VARCHAR(150),
    doctor_notes TEXT,
    CONSTRAINT fk_vet_log FOREIGN KEY (log_id) REFERENCES health_logs(log_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS symptom_logs (
    log_id               INT UNSIGNED PRIMARY KEY,
    symptoms_description TEXT NOT NULL,
    CONSTRAINT fk_symptom_log FOREIGN KEY (log_id) REFERENCES health_logs(log_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS articles (
    article_id  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    admin_id    INT UNSIGNED,
    category_id INT UNSIGNED NOT NULL,
    breed_id    INT UNSIGNED,
    title       VARCHAR(255) NOT NULL,
    content     TEXT         NOT NULL,
    read_time   VARCHAR(20)  NOT NULL DEFAULT '3 min',
    icon        VARCHAR(50),
    status      ENUM('Published', 'Draft') NOT NULL DEFAULT 'Published',
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_articles_admin    FOREIGN KEY (admin_id)    REFERENCES admins(admin_id)    ON DELETE SET NULL,
    CONSTRAINT fk_articles_category FOREIGN KEY (category_id) REFERENCES categories(category_id),
    CONSTRAINT fk_articles_breed    FOREIGN KEY (breed_id)    REFERENCES breeds(breed_id)    ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS daily_insights (
    insight_id   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    admin_id     INT UNSIGNED,
    category_id  INT UNSIGNED NOT NULL,
    insight_text TEXT NOT NULL,
    post_date    DATE NOT NULL,
    CONSTRAINT fk_insights_admin    FOREIGN KEY (admin_id)    REFERENCES admins(admin_id)    ON DELETE SET NULL,
    CONSTRAINT fk_insights_category FOREIGN KEY (category_id) REFERENCES categories(category_id)
) ENGINE=InnoDB;


-- ============================================================
-- SEED DATA
-- ============================================================

-- ADMIN (admin123)
INSERT INTO admins (admin_id, name, email, password) VALUES
(1, 'Admin Wagura', 'admin@wagura.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- CATEGORIES 
INSERT INTO categories (category_id, category_name) VALUES
(1, 'PH Guide'),
(2, 'Dogs'),
(3, 'Cats'),
(4, 'General');

-- BREEDS
INSERT INTO breeds (breed_id, breed_name, pet_type) VALUES
(1, 'Aspin',     'Dog'),
(2, 'Persian',   'Cat'),
(3, 'Shih Tzu',  'Dog'),
(4, 'Puspin',    'Cat'),
(5, 'Chihuahua', 'Dog');

-- USERS
INSERT INTO users (user_id, first_name, last_name, email, username, password, created_at) VALUES
(2, 'Juan',   'dela Cruz',  'juan@email.com',          'juandelacruz', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2026-03-18 10:00:00'),
(3, 'Maria',  'Lim',        'maria@email.com',         'marialim',     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2026-02-14 09:00:00'),
(4, 'Rico',   'Cruz',       'rico@email.com',          'ricocruz',     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2026-03-19 11:00:00'),
(7, 'Aldhee', 'Villafranca','altarfcnc506@gmail.com',  'Aldhee',       '$2y$10$L2iVK9Sv4zVzp7mU83Bmkui6Bk1rkKMc2IpSsSf8dIVssoaBq1xum', '2026-06-21 00:00:00');

-- PETS
INSERT INTO pets (pet_id, user_id, breed_id, pet_code, name, date_of_birth, weight, sex, color, medical_notes, created_at) VALUES
(2, 2, 2, 'PET-0002', 'Mochi', '2023-03-01', 3.20, 'Female', 'White',       'Spayed. Indoor cat.',              '2026-03-01 08:45:00'),
(3, 2, 3, 'PET-0003', 'Bruno', '2025-03-01', 5.10, 'Male',   'Black/White', 'Anti-rabies updated Jan 2026.',    '2026-03-01 09:00:00'),
(4, 2, 4, 'PET-0004', 'Niko',  '2024-03-01', 2.80, 'Male',   'Gray',        NULL,                               '2026-03-18 10:15:00'),
(5, 7, 1, 'WG-DOG-0001', 'Coco',  '2024-03-01', 8.50, 'Male',   'Brown', 'Vaccinated. Check for ticks monthly.', '2026-03-01 08:30:00'),
(7, 7, 3, 'WG-DOG-0002', 'Tone',  '2025-06-21', 5.00, 'Male',   NULL,    NULL,                                    '2026-06-21 00:00:00'),
(8, 7, 1, 'WG-DOG-0003', 'John',  '2025-06-21', 5.50, 'Male',   NULL,    NULL,                                    '2026-06-21 00:00:00');

-- HEALTH LOGS
INSERT INTO health_logs (log_id, pet_id, log_type, log_date, log_time) VALUES
(1,  2, 'Feeding',   '2026-03-20', '07:30:00'),
(3,  3, 'Feeding',   '2026-03-20', '12:00:00'),
(4,  3, 'Vet Visit', '2026-03-19', '14:00:00'),
(5,  2, 'Symptoms',  '2026-03-19', '18:00:00'),
(6,  2, 'Weight', '2026-03-01', '08:00:00'),
(7,  2, 'Weight', '2026-03-07', '08:00:00'),
(8,  2, 'Weight', '2026-03-14', '08:00:00'),
(9,  2, 'Weight', '2026-03-18', '08:00:00'),
(10, 2, 'Weight', '2026-03-20', '08:00:00'),
(11, 5, 'Weight', '2026-03-01', '08:00:00'),
(12, 5, 'Weight', '2026-03-07', '08:00:00'),
(13, 5, 'Weight', '2026-03-14', '08:00:00'),
(14, 5, 'Weight', '2026-03-18', '08:00:00'),
(15, 5, 'Weight', '2026-03-20', '08:00:00'),
(16, 3, 'Weight', '2026-05-24', '08:00:00'),
(17, 3, 'Weight', '2026-05-31', '08:00:00'),
(18, 3, 'Weight', '2026-06-07', '08:00:00'),
(19, 3, 'Weight', '2026-06-14', '08:00:00'),
(20, 3, 'Weight', '2026-06-21', '08:00:00'),
(21, 4, 'Weight', '2026-05-24', '08:00:00'),
(22, 4, 'Weight', '2026-05-31', '08:00:00'),
(23, 4, 'Weight', '2026-06-07', '08:00:00'),
(24, 4, 'Weight', '2026-06-14', '08:00:00'),
(25, 4, 'Weight', '2026-06-21', '08:00:00'),
(31, 7, 'Weight', '2026-05-24', '08:00:00'),
(32, 7, 'Weight', '2026-05-31', '08:00:00'),
(33, 7, 'Weight', '2026-06-07', '08:00:00'),
(34, 7, 'Weight', '2026-06-14', '08:00:00'),
(35, 7, 'Weight', '2026-06-21', '08:00:00'),
(36, 8, 'Weight', '2026-05-24', '08:00:00'),
(37, 8, 'Weight', '2026-05-31', '08:00:00'),
(38, 8, 'Weight', '2026-06-07', '08:00:00'),
(39, 8, 'Weight', '2026-06-14', '08:00:00'),
(40, 8, 'Weight', '2026-06-21', '08:00:00');

-- FEEDING LOGS
INSERT INTO feeding_logs (log_id, food_description) VALUES
(1, '200g dry food + water refilled'),
(3, '150g wet food');

-- WEIGHT LOGS 
INSERT INTO weight_logs (log_id, weight_kg) VALUES
-- Mochi
(6,  8.10), (7,  8.20), (8,  8.40), (9,  8.50), (10, 8.70),
-- Coco
(11, 8.10), (12, 8.20), (13, 8.40), (14, 8.50), (15, 8.70),
-- Bruno
(16, 2.86), (17, 2.97), (18, 3.07), (19, 3.10), (20, 3.18),
-- Niko
(21, 4.75), (22, 4.89), (23, 4.91), (24, 5.05), (25, 5.12),
-- Tone
(31, 4.66), (32, 4.79), (33, 4.83), (34, 4.94), (35, 5.02),
-- John
(36, 5.17), (37, 5.27), (38, 5.36), (39, 5.42), (40, 5.51);

-- VET LOGS
INSERT INTO vet_logs (log_id, clinic_name, doctor_notes) VALUES
(4, 'Laguna Animal Clinic', 'Annual checkup — all clear');

-- SYMPTOM LOGS
INSERT INTO symptom_logs (log_id, symptoms_description) VALUES
(5, 'Scratching around neck area, possible ticks');

-- ARTICLES 
INSERT INTO articles (article_id, admin_id, category_id, breed_id, title, content, read_time, icon, status, created_at) VALUES
(1, 1, 1, NULL, 'Protecting your dog from ticks in humid Laguna weather',
 'Learn how to check for ticks and prevent infestations during the rainy season. Ticks thrive in humid environments. Always inspect your dog after outdoor walks, especially around the ears and neck. Use vet-approved tick prevention treatments monthly.',
 '3 min', 'fa-bug', 'Published', '2026-03-10 09:00:00'),
(2, 1, 2, 1, 'Rabies prevention tips for Aspin owners near strays',
 'Essential vaccination schedule and safety tips for dogs exposed to stray animals. Aspins face higher exposure risk. Ensure annual anti-rabies vaccination and keep your dog away from unfamiliar strays in your barangay.',
 '4 min', 'fa-syringe', 'Published', '2026-03-08 10:00:00'),
(3, 1, 3, NULL, 'Keeping your cat cool during hot season in Laguna',
 'Tips for managing heat stress and keeping your cat hydrated during summer months. Provide fresh water multiple times daily, avoid direct sunlight, and consider wet food to increase fluid intake during hot days.',
 '2 min', 'fa-temp', 'Published', '2026-03-05 11:00:00'),
(4, 1, 4, NULL, 'What to do when your pet encounters a stray animal',
 'Step by step guide on handling stray encounters safely in your neighborhood. Keep calm, do not let pets sniff strays directly, check for bite marks after encounters, and consult your vet if contact occurred.',
 '5 min', 'fa-paw', 'Published', '2026-03-03 12:00:00');

-- DAILY INSIGHTS 
INSERT INTO daily_insights (insight_id, admin_id, category_id, insight_text, post_date) VALUES
(1, 1, 3, 'Did you know? Cats sleep 12 to 16 hours a day to conserve energy for hunting.', '2026-03-20'),
(2, 1, 2, 'Aspins need extra deworming every 3 months due to outdoor exposure.',            '2026-03-19'),
(3, 1, 1, 'High humidity in Laguna can cause skin issues in pets. Check your pet daily.',   '2026-03-18');

-- ============================================================
-- FINNNNN
-- ============================================================

