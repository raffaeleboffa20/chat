CREATE DATABASE chat_lan CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE chat_lan;

CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
