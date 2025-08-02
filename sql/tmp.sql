DROP DATABASE IF EXISTS student_qa_system;
CREATE DATABASE student_qa_system;
USE student_qa_system;

CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE modules (
    module_id INT PRIMARY KEY AUTO_INCREMENT,
    module_code VARCHAR(20) NOT NULL UNIQUE,
    module_name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE posts (
    post_id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    image_path VARCHAR(255) DEFAULT NULL,
    user_id INT NOT NULL,
    module_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (module_id) REFERENCES modules(module_id) ON DELETE CASCADE
);

CREATE TABLE contact_messages (
    message_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    subject VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO users (username, email) VALUES
('john_doe', 'john.doe@student.ac.uk'),
('jane_smith', 'jane.smith@student.ac.uk'),
('mike_wilson', 'mike.wilson@student.ac.uk'),
('sarah_jones', 'sarah.jones@student.ac.uk'),
('admin', 'admin@student.ac.uk');

INSERT INTO modules (module_code, module_name) VALUES
('COMP1841', 'Web Programming 1'),
('COMP1640', 'Enterprise Web Development'),
('COMP1842', 'Server-Side Web Development'),
('COMP1649', 'Human Computer Interaction'),
('COMP1647', 'Computer Systems Architecture');

INSERT INTO posts (title, content, user_id, module_id) VALUES
('Need help with PHP PDO connections', 'I am struggling to connect to MySQL database using PDO. Can anyone help me understand the proper syntax?', 1, 1),
('CSS Grid vs Flexbox question', 'When should I use CSS Grid over Flexbox for my web layouts? What are the main differences?', 2, 1),
('JavaScript validation not working', 'My form validation script is not preventing form submission. Here is my code...', 3, 1),
('Database normalization help', 'Can someone explain the difference between 1NF, 2NF, and 3NF with simple examples?', 4, 2),
('Responsive design best practices', 'What are the current best practices for creating responsive web designs in 2024?', 1, 1);