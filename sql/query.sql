CREATE DATABASE IF NOT EXISTS student_questions;
USE student_questions;

CREATE TABLE users (
	id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE modules (
	id INT AUTO_INCREMENT PRIMARY KEY,
    module_code VARCHAR(20) NOT NULL UNIQUE,
    module_name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE posts (
	id INT AUTO_INCREMENT PRIMARY KEY,
  	title VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    image_path VARCHAR(255),
    user_id INT,
    module_id INT,
   	created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (module_id) REFERENCES modules(id) ON DELETE SET NULL
);

CREATE TABLE contact_messages (
	id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    subject VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO users (username, email) VALUES 
('john_doe', 'john@student.ac.uk'),
('jane_smith', 'jane@student.ac.uk'),
('mike_wilson', 'mike@student.ac.uk'),
('sarah_jones', 'sarah@student.ac.uk');

INSERT INTO modules (module_code, module_name, description) VALUES 
('COMP1841', 'Web Programming 1', 'Introduction to web development...'),
('COMP1640', 'Enterprise Web Development', 'Advanced web development...'),
('COMP1649', 'System Analysis & Design', 'Software development lifecycle...'),
('COMP1752', 'Network Technology', 'Computer networks...'),
('COMP1741', 'Database Systems', 'Database design...');

INSERT INTO posts (title, content, user_id, module_id) VALUES 
('Help with PHP PDO Connection', 'I am having trouble connecting...', 1, 1),
('CSS Grid vs Flexbox', 'When should I use CSS Grid...', 2, 1),
('JavaScript Async/Await', 'Can someone explain how...', 3, 1),
('Database Normalization', 'I need help understanding...', 4, 5),
('Network Security Basics', 'What are the fundamental...', 1, 4);
