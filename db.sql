-- db.sql for cerita_site_full
CREATE DATABASE IF NOT EXISTS cerita_site_full CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE cerita_site_full;

CREATE TABLE IF NOT EXISTS admins (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL, -- initially plaintext 'admin'
  remember_token VARCHAR(255) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS stories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  excerpt VARCHAR(512),
  content TEXT NOT NULL,
  image VARCHAR(255) DEFAULT NULL,
  theme VARCHAR(100) DEFAULT 'default',
  author VARCHAR(150) DEFAULT 'Anonim',
  approved TINYINT(1) DEFAULT 0, -- 0 pending, 1 approved
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL
);

CREATE TABLE IF NOT EXISTS visits (
  id INT AUTO_INCREMENT PRIMARY KEY,
  visit_date DATE NOT NULL,
  views INT DEFAULT 0,
  UNIQUE KEY (visit_date)
);

-- default admin (username: admin, password: admin)
INSERT IGNORE INTO admins (username, password) VALUES ('admin', 'admin');
