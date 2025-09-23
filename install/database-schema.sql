-- Vytvoření databáze
CREATE DATABASE IF NOT EXISTS krs_db CHARACTER SET utf8mb4 COLLATE utf8mb4_czech_ci;
USE krs;

-- Tabulka uživatelů
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL,
    role VARCHAR(20) DEFAULT 'author',
    active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL
);

-- Tabulka článků
CREATE TABLE articles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    content LONGTEXT NOT NULL,
    excerpt TEXT,
    author_id INT NOT NULL,
    status VARCHAR(20) DEFAULT 'draft',
    published_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabulka kategorií
CREATE TABLE categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    parent_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Propojení článků a kategorií
CREATE TABLE article_categories (
    article_id INT NOT NULL,
    category_id INT NOT NULL,
    PRIMARY KEY (article_id, category_id)
);

-- Tabulka pro fotogalerii
CREATE TABLE galleries (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description TEXT,
    author_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabulka obrázků v galerii
CREATE TABLE gallery_images (
    id INT PRIMARY KEY AUTO_INCREMENT,
    gallery_id INT NOT NULL,
    filename VARCHAR(255) NOT NULL,
    title VARCHAR(255),
    description TEXT,
    sort_order INT DEFAULT 0,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Přidání cizích klíčů
ALTER TABLE articles ADD FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE;
ALTER TABLE article_categories ADD FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE;
ALTER TABLE article_categories ADD FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE;
ALTER TABLE galleries ADD FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE;
ALTER TABLE gallery_images ADD FOREIGN KEY (gallery_id) REFERENCES galleries(id) ON DELETE CASCADE;

-- Vytvoření indexů
CREATE INDEX idx_users_username ON users(username);
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_articles_slug ON articles(slug);
CREATE INDEX idx_articles_status ON articles(status);
CREATE INDEX idx_articles_author ON articles(author_id);
CREATE INDEX idx_categories_slug ON categories(slug);
CREATE INDEX idx_galleries_slug ON galleries(slug);
CREATE INDEX idx_gallery_images_gallery ON gallery_images(gallery_id);

-- Vložení testovacích dat
INSERT INTO users (username, password_hash, email, role) VALUES
('admin', '$2y$12$rQ9b5cW3fL6hN8gV2xZJ3.DS7cK9lM2nR1tB5vC8wX0zY3fG7hJ6u', 'admin@example.com', 'admin'),
('editor', '$2y$12$rQ9b5cW3fL6hN8gV2xZJ3.DS7cK9lM2nR1tB5vC8wX0zY3fG7hJ6u', 'editor@example.com', 'editor'),
('author', '$2y$12$rQ9b5cW3fL6hN8gV2xZJ3.DS7cK9lM2nR1tB5vC8wX0zY3fG7hJ6u', 'author@example.com', 'author');

INSERT INTO categories (name, slug, description) VALUES
('Novinky', 'novinky', 'Aktuální novinky a události'),
('Technologie', 'technologie', 'Články o technologiích'),
('Kultura', 'kultura', 'Kulturní události a recenze');

INSERT INTO articles (title, slug, content, author_id, status, published_at) VALUES
('Vítejte v redakčním systému', 'vitejte-v-redakcnim-systemu', 'Toto je úvodní článek...', 1, 'published', NOW()),
('Nový redakční systém KRS', 'novy-redakcni-system-krs', 'Představujeme nový redakční systém...', 2, 'published', NOW());

INSERT INTO article_categories (article_id, category_id) VALUES
(1, 1),
(2, 1),
(2, 2);