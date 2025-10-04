-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Počítač: 127.0.0.1
-- Vytvořeno: Sob 04. říj 2025, 15:01
-- Verze serveru: 10.4.32-MariaDB
-- Verze PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Databáze: `krs`
--

-- --------------------------------------------------------

--
-- Struktura tabulky `articles`
--

CREATE TABLE `articles` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `content` longtext NOT NULL,
  `excerpt` text DEFAULT NULL,
  `author_id` int(11) NOT NULL,
  `status` varchar(20) DEFAULT 'draft',
  `published_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

--
-- Vypisuji data pro tabulku `articles`
--

INSERT INTO `articles` (`id`, `title`, `slug`, `content`, `excerpt`, `author_id`, `status`, `published_at`, `created_at`, `updated_at`, `deleted_at`) VALUES
(5, 'Lorem ipsum dolor sit amet consectetuer ', 'lorem-ipsum-dolor-sit-amet-consectetuer--1758668587', '<p>Nibh nec commodo Ut sed fames et elit sed aliquam tellus. Wisi scelerisque et elit et nascetur malesuada Phasellus Nam magna cursus. Id nunc egestas urna justo suscipit Pellentesque tellus wisi Sed Curabitur. Turpis ligula nulla lacus enim non vel congue faucibus wisi Pellentesque. Orci aliquam Donec consequat tristique convallis Integer id ut libero vel. Sociis nisl venenatis condimentum congue Nullam vitae Maecenas nec orci pede. Suscipit.</p>\r\n<p>Est augue vitae vitae in lacus vitae at auctor in mi. Dapibus tincidunt felis ligula Quisque semper Donec interdum In felis id. Suscipit Aenean convallis Mauris felis Curabitur eleifend gravida pharetra venenatis nunc. Orci porttitor laoreet nisl metus quis Vestibulum Pellentesque In eros Curabitur. Tristique senectus pellentesque sit justo Quisque Nam odio mauris Fusce Maecenas. Platea.</p>\r\n<p>Massa odio risus porttitor montes velit quis vitae ac consequat ut. Semper Aenean ipsum mus ligula tristique Phasellus mauris ultrices accumsan Nunc. Cras Ut at vel Vestibulum ornare ac senectus ante ut tellus. Elit Aliquam tempus Nullam interdum tempor Curabitur nibh porta non wisi. Eros Cras at consectetuer amet accumsan pellentesque.</p>\r\n\r\n', 'Lorem ipsum dolor sit amet consectetuer eros id mus nulla Nulla. Nam Curabitur dignissim urna orci eu orci Lorem quis congue semper. Id faucibus Vivamus nunc adipiscing euismod eu cursus velit et pretium. Vestibulum sit pellentesque pede Sed metus natoque Aenean Vestibulum mattis Nunc. Maecenas Vestibulum ac fermentum ellentesque Sed', 1, 'published', '2025-09-23 23:03:07', '2025-09-19 00:09:00', '2025-09-29 22:39:17', NULL),
(7, 'Soběstačný domov', 'sobestacny-domov-1759182490', 'aučíte se, jak si vyrobit sýr na gril, tvaroh, čerstvé farmářské sýry a dám vám i návody na poněkud složitější, zrající sýry.', 'Vítejte v kurzu, mám ohromnou radost, že jste tady, že jste se rozhodli udělat ten první krůček k soběstačnosti a že právě já můžu být vaší průvodkyní! 🙂    Začněte tím, že si přehrajete video vpravo, ve kterém máte úvodní praktické informace.  Kdybyste cokoliv potřebovali, neváhejte mě kontaktovat, jsem tu pro vás. Přeju hodně štěstí při výrobě!    Jana ', 1, 'published', '2025-09-29 21:48:10', '2025-09-29 21:47:45', '2025-10-03 10:35:26', NULL);

-- --------------------------------------------------------

--
-- Struktura tabulky `article_categories`
--

CREATE TABLE `article_categories` (
  `article_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

--
-- Vypisuji data pro tabulku `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `parent_id`, `created_at`) VALUES
(1, 'Novinky', 'novinky', 'Aktuální novinky a události', NULL, '2025-09-11 19:41:52'),
(2, 'Technologie', 'technologie', 'Články o technologiích', NULL, '2025-09-11 19:41:52'),
(3, 'Kultura', 'kultura', 'Kulturní události a recenze', NULL, '2025-09-11 19:41:52');

-- --------------------------------------------------------

--
-- Struktura tabulky `galleries`
--

CREATE TABLE `galleries` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `author_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `gallery_images`
--

CREATE TABLE `gallery_images` (
  `id` int(11) NOT NULL,
  `gallery_id` int(11) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` varchar(20) DEFAULT 'author',
  `active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

--
-- Vypisuji data pro tabulku `users`
--

INSERT INTO `users` (`id`, `username`, `password_hash`, `email`, `role`, `active`, `created_at`, `last_login`) VALUES
(1, 'admin', '$2y$10$i5QZMebyfORru1U1uNxvKexuEo1Ym/izESiB71s3.lBcgCEHdd0QS', 'admin@example.com', 'admin', 1, '2025-09-11 19:41:52', NULL),
(2, 'editor', '$2y$10$i5QZMebyfORru1U1uNxvKexuEo1Ym/izESiB71s3.lBcgCEHdd0QS', 'editor@example.com', 'editor', 1, '2025-09-11 19:41:52', NULL),
(3, 'author', '$2y$10$i5QZMebyfORru1U1uNxvKexuEo1Ym/izESiB71s3.lBcgCEHdd0QS', 'author@example.com', 'author', 1, '2025-09-11 19:41:52', NULL);

--
-- Indexy pro exportované tabulky
--

--
-- Indexy pro tabulku `articles`
--
ALTER TABLE `articles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_articles_slug` (`slug`),
  ADD KEY `idx_articles_status` (`status`),
  ADD KEY `idx_articles_author` (`author_id`),
  ADD KEY `idx_articles_published` (`published_at`);

--
-- Indexy pro tabulku `article_categories`
--
ALTER TABLE `article_categories`
  ADD PRIMARY KEY (`article_id`,`category_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexy pro tabulku `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_categories_slug` (`slug`);

--
-- Indexy pro tabulku `galleries`
--
ALTER TABLE `galleries`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `author_id` (`author_id`),
  ADD KEY `idx_galleries_slug` (`slug`);

--
-- Indexy pro tabulku `gallery_images`
--
ALTER TABLE `gallery_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_gallery_images_gallery` (`gallery_id`);

--
-- Indexy pro tabulku `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `idx_users_username` (`username`),
  ADD KEY `idx_users_email` (`email`);

--
-- AUTO_INCREMENT pro tabulky
--

--
-- AUTO_INCREMENT pro tabulku `articles`
--
ALTER TABLE `articles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT pro tabulku `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pro tabulku `galleries`
--
ALTER TABLE `galleries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `gallery_images`
--
ALTER TABLE `gallery_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Omezení pro exportované tabulky
--

--
-- Omezení pro tabulku `articles`
--
ALTER TABLE `articles`
  ADD CONSTRAINT `articles_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Omezení pro tabulku `article_categories`
--
ALTER TABLE `article_categories`
  ADD CONSTRAINT `article_categories_ibfk_1` FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `article_categories_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- Omezení pro tabulku `galleries`
--
ALTER TABLE `galleries`
  ADD CONSTRAINT `galleries_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Omezení pro tabulku `gallery_images`
--
ALTER TABLE `gallery_images`
  ADD CONSTRAINT `gallery_images_ibfk_1` FOREIGN KEY (`gallery_id`) REFERENCES `galleries` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
