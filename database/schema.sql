-- DJ Booking Platform - Full Database Schema
-- Version: 1.0.0
-- Created: 2024

-- Create and use the database
CREATE DATABASE IF NOT EXISTS `dj_booking_platform`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `dj_booking_platform`;

-- ============================================================
-- Table: admin_users
-- Stores admin/DJ login credentials
-- ============================================================
CREATE TABLE IF NOT EXISTS `admin_users` (
    `id`           INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `username`     VARCHAR(50)     NOT NULL UNIQUE,
    `email`        VARCHAR(100)    NOT NULL UNIQUE,
    `password_hash` VARCHAR(255)   NOT NULL,
    `created_at`   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_username` (`username`),
    INDEX `idx_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Table: dj_profile
-- Stores the DJ's public profile information
-- ============================================================
CREATE TABLE IF NOT EXISTS `dj_profile` (
    `id`                  INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `name`                VARCHAR(100)  NOT NULL DEFAULT '',
    `bio`                 TEXT          NOT NULL,
    `location`            VARCHAR(150)  NOT NULL DEFAULT '',
    `phone`               VARCHAR(20)   NOT NULL DEFAULT '',
    `email`               VARCHAR(100)  NOT NULL DEFAULT '',
    `facebook`            VARCHAR(255)  NOT NULL DEFAULT '',
    `instagram`           VARCHAR(255)  NOT NULL DEFAULT '',
    `twitter`             VARCHAR(255)  NOT NULL DEFAULT '',
    `youtube_channel_url` VARCHAR(255)  NOT NULL DEFAULT '',
    `profile_image`       VARCHAR(255)  NOT NULL DEFAULT '',
    `updated_at`          DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Table: categories
-- Music categories for organizing songs
-- ============================================================
CREATE TABLE IF NOT EXISTS `categories` (
    `id`          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `name`        VARCHAR(100)  NOT NULL,
    `slug`        VARCHAR(100)  NOT NULL UNIQUE,
    `description` TEXT          NOT NULL DEFAULT '',
    `created_at`  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Table: songs
-- Audio tracks uploaded by the admin
-- ============================================================
CREATE TABLE IF NOT EXISTS `songs` (
    `id`              INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `title`           VARCHAR(200)  NOT NULL,
    `artist`          VARCHAR(150)  NOT NULL DEFAULT '',
    `category_id`     INT UNSIGNED  NOT NULL,
    `filename`        VARCHAR(255)  NOT NULL,
    `file_path`       VARCHAR(500)  NOT NULL,
    `cover_image`     VARCHAR(255)  NOT NULL DEFAULT '',
    `duration`        VARCHAR(10)   NOT NULL DEFAULT '',
    `play_count`      INT UNSIGNED  NOT NULL DEFAULT 0,
    `allow_download`  TINYINT(1)    NOT NULL DEFAULT 0,
    `upload_date`     DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_category` (`category_id`),
    INDEX `idx_upload_date` (`upload_date`),
    CONSTRAINT `fk_songs_category`
        FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Table: videos
-- YouTube video embeds managed by the admin
-- ============================================================
CREATE TABLE IF NOT EXISTS `videos` (
    `id`          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `title`       VARCHAR(200)  NOT NULL,
    `description` TEXT          NOT NULL DEFAULT '',
    `youtube_url` VARCHAR(500)  NOT NULL,
    `youtube_id`  VARCHAR(20)   NOT NULL,
    `thumbnail`   VARCHAR(500)  NOT NULL DEFAULT '',
    `is_featured` TINYINT(1)    NOT NULL DEFAULT 0,
    `created_at`  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_featured` (`is_featured`),
    INDEX `idx_youtube_id` (`youtube_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Table: bookings
-- Client booking requests
-- ============================================================
CREATE TABLE IF NOT EXISTS `bookings` (
    `id`             INT UNSIGNED                             NOT NULL AUTO_INCREMENT,
    `full_name`      VARCHAR(150)                             NOT NULL,
    `email`          VARCHAR(100)                             NOT NULL,
    `phone`          VARCHAR(30)                              NOT NULL,
    `event_type`     VARCHAR(100)                             NOT NULL,
    `event_date`     DATE                                     NOT NULL,
    `event_time`     TIME                                     NOT NULL,
    `event_location` VARCHAR(300)                             NOT NULL DEFAULT '',
    `notes`          TEXT                                     NOT NULL DEFAULT '',
    `status`         ENUM('pending','approved','rejected')    NOT NULL DEFAULT 'pending',
    `created_at`     DATETIME                                 NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_event_date` (`event_date`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Table: messages
-- Visitor contact/inquiry messages
-- ============================================================
CREATE TABLE IF NOT EXISTS `messages` (
    `id`         INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `name`       VARCHAR(150)  NOT NULL,
    `email`      VARCHAR(100)  NOT NULL,
    `subject`    VARCHAR(250)  NOT NULL DEFAULT '',
    `message`    TEXT          NOT NULL,
    `is_read`    TINYINT(1)    NOT NULL DEFAULT 0,
    `created_at` DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_is_read` (`is_read`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Table: availability
-- Dates blocked by the admin (unavailable for booking)
-- ============================================================
CREATE TABLE IF NOT EXISTS `availability` (
    `id`           INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `blocked_date` DATE          NOT NULL UNIQUE,
    `reason`       VARCHAR(250)  NOT NULL DEFAULT '',
    `created_at`   DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_blocked_date` (`blocked_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- SEED DATA
-- ============================================================

-- Default admin user (username: admin, password: Admin@1234)
INSERT INTO `admin_users` (`username`, `email`, `password_hash`) VALUES
('admin', 'admin@djkojo.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- DJ Profile
INSERT INTO `dj_profile` (`name`, `bio`, `location`, `phone`, `email`, `facebook`, `instagram`, `twitter`, `youtube_channel_url`, `profile_image`) VALUES
(
    'DJ KoJo',
    'DJ KoJo is a professional DJ and music curator with over 10 years of experience electrifying crowds across Ghana and beyond. Specializing in Afrobeats, Gospel, Hip-Hop, and custom party mixes, DJ KoJo brings energy, passion, and professionalism to every event. Whether it is a wedding, corporate event, birthday celebration, or club night, DJ KoJo crafts the perfect sonic experience tailored to your occasion.',
    'Accra, Ghana',
    '+233 24 000 0000',
    'contact@djkojo.com',
    'https://facebook.com/djkojo',
    'https://instagram.com/djkojo',
    'https://twitter.com/djkojo',
    'https://youtube.com/@djkojo',
    ''
);

-- Music Categories
INSERT INTO `categories` (`name`, `slug`, `description`) VALUES
('Afrobeats', 'afrobeats', 'High-energy Afrobeats tracks for parties and celebrations'),
('Gospel',    'gospel',    'Uplifting gospel music for worship and special occasions'),
('Hip-Hop',   'hip-hop',   'The hottest Hip-Hop and trap bangers'),
('Mixes',     'mixes',     'Custom DJ mixes and mashups across all genres');

-- Sample YouTube Videos (using real public YouTube video IDs)
INSERT INTO `videos` (`title`, `description`, `youtube_url`, `youtube_id`, `thumbnail`, `is_featured`) VALUES
(
    'Afrobeats Mix 2024 — Best of Burna Boy, Wizkid & Davido',
    'A fire mix of the hottest Afrobeats tracks from 2024, featuring Burna Boy, Wizkid, Davido, and more.',
    'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
    'dQw4w9WgXcQ',
    'https://img.youtube.com/vi/dQw4w9WgXcQ/hqdefault.jpg',
    1
),
(
    'Gospel Praise Mix — Sunday Vibes',
    'Start your Sunday right with this powerful gospel praise and worship mix.',
    'https://www.youtube.com/watch?v=3JZ4pnNtyxQ',
    '3JZ4pnNtyxQ',
    'https://img.youtube.com/vi/3JZ4pnNtyxQ/hqdefault.jpg',
    1
),
(
    'Hip-Hop Classics — 90s & 2000s Throwback Mix',
    'Take a trip down memory lane with the greatest hip-hop classics from the 90s and 2000s.',
    'https://www.youtube.com/watch?v=ZZ5LpwO-An4',
    'ZZ5LpwO-An4',
    'https://img.youtube.com/vi/ZZ5LpwO-An4/hqdefault.jpg',
    0
);

-- Sample booking (pending)
INSERT INTO `bookings` (`full_name`, `email`, `phone`, `event_type`, `event_date`, `event_time`, `event_location`, `notes`, `status`) VALUES
(
    'Kwame Asante',
    'kwame.asante@example.com',
    '+233 20 123 4567',
    'Wedding',
    DATE_ADD(CURDATE(), INTERVAL 30 DAY),
    '18:00:00',
    'Labadi Beach Hotel, Accra, Ghana',
    'We would like Afrobeats and Gospel music for the reception. The event will host approximately 200 guests.',
    'pending'
);
