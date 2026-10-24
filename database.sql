-- StackEvent database schema
-- For phpMyAdmin: create/select your database first, then import this file.
-- InfinityFree creates the database for you; do not run CREATE DATABASE here.
-- Existing tables are left untouched (CREATE TABLE IF NOT EXISTS).

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS users (
    user_id INT NOT NULL AUTO_INCREMENT,
    full_name VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL,
    email VARCHAR(150) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'staff') NOT NULL DEFAULT 'admin',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id),
    UNIQUE KEY uq_users_username (username),
    UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS participants (
    participants_id INT NOT NULL AUTO_INCREMENT,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) DEFAULT NULL,
    gender VARCHAR(20) DEFAULT NULL,
    birthdate DATE DEFAULT NULL,
    city VARCHAR(100) DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (participants_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS organizers (
    organizer_id INT NOT NULL AUTO_INCREMENT,
    organizer_name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL,
    contact_number VARCHAR(20) DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (organizer_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS events (
    event_id INT NOT NULL AUTO_INCREMENT,
    event_name VARCHAR(200) NOT NULL,
    event_date DATE NOT NULL,
    venue VARCHAR(200) DEFAULT NULL,
    organizer_id INT DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (event_id),
    KEY fk_event_organizer (organizer_id),
    CONSTRAINT fk_event_organizer FOREIGN KEY (organizer_id)
        REFERENCES organizers (organizer_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS registrations (
    registration_id INT NOT NULL AUTO_INCREMENT,
    event_id INT DEFAULT NULL,
    participant_id INT NOT NULL,
    registration_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (registration_id),
    KEY fk_reg_participant (participant_id),
    KEY fk_reg_event (event_id),
    CONSTRAINT fk_reg_event FOREIGN KEY (event_id)
        REFERENCES events (event_id) ON DELETE CASCADE,
    CONSTRAINT fk_reg_participant FOREIGN KEY (participant_id)
        REFERENCES participants (participants_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
