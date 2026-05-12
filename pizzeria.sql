-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost:8889
-- Généré le : jeu. 07 mai 2026 à 12:31
-- Version du serveur : 8.0.40
-- Version de PHP : 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `shop-clone`
--

-- --------------------------------------------------------

--
-- Structure de la table `command`
--

CREATE TABLE `command` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `first_name` varchar(125) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(125) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `zip_code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `city` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone_number` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total` double NOT NULL,
  `preparation_status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `updated_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `address_complement` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `delivery_type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `command`
--

INSERT INTO `command` (`id`, `user_id`, `first_name`, `last_name`, `address`, `zip_code`, `city`, `phone_number`, `status`, `total`, `preparation_status`, `created_at`, `updated_at`, `address_complement`, `delivery_type`) VALUES
(89, 6, 'sébastien', 'Petit', '11 rue du val ferrant', '27330', 'Bois Anzerzay', '0780468148', 'Payé', 12.99, 'Livré', '2026-05-06 15:09:01', '2026-05-07 10:40:43', NULL, 'À emporter');

-- --------------------------------------------------------

--
-- Structure de la table `command_items`
--

CREATE TABLE `command_items` (
  `id` int NOT NULL,
  `command_id` int NOT NULL,
  `product_id` int NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` int NOT NULL,
  `price` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `command_items`
--

INSERT INTO `command_items` (`id`, `command_id`, `product_id`, `title`, `quantity`, `price`) VALUES
(97, 89, 35, 'Reine', 1, 12.99);

-- --------------------------------------------------------

--
-- Structure de la table `contact`
--

CREATE TABLE `contact` (
  `id` int NOT NULL,
  `firstname` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `lastname` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `doctrine_migration_versions`
--

CREATE TABLE `doctrine_migration_versions` (
  `version` varchar(191) COLLATE utf8mb3_unicode_ci NOT NULL,
  `executed_at` datetime DEFAULT NULL,
  `execution_time` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Déchargement des données de la table `doctrine_migration_versions`
--

INSERT INTO `doctrine_migration_versions` (`version`, `executed_at`, `execution_time`) VALUES
('DoctrineMigrations\\Version20260411141359', '2026-04-12 11:40:05', 86),
('DoctrineMigrations\\Version20260411151447', '2026-04-12 11:40:05', 4),
('DoctrineMigrations\\Version20260411161730', '2026-04-12 11:40:05', 5),
('DoctrineMigrations\\Version20260412114037', '2026-04-12 11:40:43', 31),
('DoctrineMigrations\\Version20260412120033', '2026-04-12 12:00:47', 23),
('DoctrineMigrations\\Version20260412121517', '2026-04-12 12:15:29', 14),
('DoctrineMigrations\\Version20260502125053', '2026-05-02 12:51:06', 12);

-- --------------------------------------------------------

--
-- Structure de la table `messenger_messages`
--

CREATE TABLE `messenger_messages` (
  `id` bigint NOT NULL,
  `body` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `headers` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue_name` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `available_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `delivered_at` datetime DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `picture`
--

CREATE TABLE `picture` (
  `id` int NOT NULL,
  `product_id` int DEFAULT NULL,
  `filename` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `picture`
--

INSERT INTO `picture` (`id`, `product_id`, `filename`) VALUES
(3, 47, '129a658a1121dd919015aaa616c40ddd.jpg'),
(4, 48, 'cfe38a952618e08ee2f561e0032eccea.jpg'),
(5, 49, 'dd95142e32be3f5c657bae07dcd9b413.jpg');

-- --------------------------------------------------------

--
-- Structure de la table `product`
--

CREATE TABLE `product` (
  `id` int NOT NULL,
  `title` varchar(125) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `product`
--

INSERT INTO `product` (`id`, `title`, `description`) VALUES
(35, 'Reine', 'Une délicieuse pizza Reine'),
(36, 'Hawaïenne', 'Une délicieuse pizza Hawaïenne'),
(37, 'Végétarienne', 'Une délicieuse pizza Végétarienne'),
(38, 'Quatre fromages', 'Une délicieuse pizza Quatre fromages'),
(40, 'Calzone', 'Une délicieuse pizza Calzone'),
(41, 'Diavola', 'Une délicieuse pizza Diavola'),
(42, 'Orientale', 'Une délicieuse pizza Orientale'),
(43, 'Mediterraneenne', 'Une délicieuse pizza Mediterraneenne'),
(44, 'Savoyarde', 'Une délicieuse pizza Savoyarde'),
(45, 'Lyonnaise', 'Une délicieuse pizza Lyonnaise'),
(46, 'Buitoni', 'Bonne'),
(47, 'Buitoni', 'Bonne pizza'),
(48, 'Buitoni', 'Bonne pizza'),
(49, 'ccsfcsdcsd', 'csqcsqcsqcsqcqscqs');

-- --------------------------------------------------------

--
-- Structure de la table `product_option`
--

CREATE TABLE `product_option` (
  `id` int NOT NULL,
  `product_id` int NOT NULL,
  `name` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `product_option`
--

INSERT INTO `product_option` (`id`, `product_id`, `name`, `price`) VALUES
(118, 35, 'Petite', 6.99),
(119, 35, 'Moyenne', 8.99),
(120, 35, 'Grande', 12.99),
(121, 36, 'Petite', 6.99),
(122, 36, 'Moyenne', 8.99),
(123, 36, 'Grande', 12.99),
(124, 37, 'Petite', 6.99),
(125, 37, 'Moyenne', 8.99),
(126, 37, 'Grande', 12.99),
(127, 38, 'Petite', 6.99),
(128, 38, 'Moyenne', 8.99),
(129, 38, 'Grande', 12.99),
(133, 40, 'Petite', 6.99),
(134, 40, 'Moyenne', 8.99),
(135, 40, 'Grande', 12.99),
(136, 41, 'Petite', 6.99),
(137, 41, 'Moyenne', 8.99),
(138, 41, 'Grande', 12.99),
(139, 42, 'Petite', 6.99),
(140, 42, 'Moyenne', 8.99),
(141, 42, 'Grande', 12.99),
(142, 43, 'Petite', 6.99),
(143, 43, 'Moyenne', 8.99),
(144, 43, 'Grande', 12.99),
(145, 44, 'Petite', 6.99),
(146, 44, 'Moyenne', 8.99),
(147, 44, 'Grande', 12.99),
(148, 45, 'Petite', 6.99),
(149, 45, 'Moyenne', 8.99),
(150, 45, 'Grande', 12.99),
(151, 46, 'Petite', 3),
(152, 46, 'Moyenne', 5),
(153, 46, 'Grande', 10),
(154, 47, 'Petite', 8),
(155, 47, 'Moyenne', 15),
(156, 47, 'Grande', 20),
(157, 48, 'Petite', 8),
(158, 48, 'Moyenne', 15),
(159, 48, 'Grande', 20),
(160, 49, 'Petite', 6),
(161, 49, 'Moyenne', 12),
(162, 49, 'Grande', 20);

-- --------------------------------------------------------

--
-- Structure de la table `user`
--

CREATE TABLE `user` (
  `id` int NOT NULL,
  `email` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `reset_token` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reset_token_expires_at` datetime DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
  `roles` json NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `user`
--

INSERT INTO `user` (`id`, `email`, `reset_token`, `reset_token_expires_at`, `roles`, `password`) VALUES
(5, 'sebastienpetit27330@gmail.com', NULL, NULL, '[\"ROLE_ADMIN\"]', '$2y$13$JGKRyrXi5vxiMLsVyA/Kp.E9lrI3wNLkm7eYhhqAbN166/WbndtCW'),
(6, 'sebastien.p0027@gmail.com', NULL, NULL, '[\"ROLE_USER\"]', '$2y$13$GvdED5yV5jHaM1nYjQhekuEQC4w0udwhTeJTwM2fdBtoq4C9a6dau');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `command`
--
ALTER TABLE `command`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_8ECAEAD4A76ED395` (`user_id`);

--
-- Index pour la table `command_items`
--
ALTER TABLE `command_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_72B6BC7E33E1689A` (`command_id`),
  ADD KEY `IDX_72B6BC7E4584665A` (`product_id`);

--
-- Index pour la table `contact`
--
ALTER TABLE `contact`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `doctrine_migration_versions`
--
ALTER TABLE `doctrine_migration_versions`
  ADD PRIMARY KEY (`version`);

--
-- Index pour la table `messenger_messages`
--
ALTER TABLE `messenger_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_75EA56E0FB7336F0` (`queue_name`),
  ADD KEY `IDX_75EA56E0E3BD61CE` (`available_at`),
  ADD KEY `IDX_75EA56E016BA31DB` (`delivered_at`);

--
-- Index pour la table `picture`
--
ALTER TABLE `picture`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_16DB4F894584665A` (`product_id`);

--
-- Index pour la table `product`
--
ALTER TABLE `product`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `product_option`
--
ALTER TABLE `product_option`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_38FA41144584665A` (`product_id`);

--
-- Index pour la table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UNIQ_IDENTIFIER_EMAIL` (`email`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `command`
--
ALTER TABLE `command`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=90;

--
-- AUTO_INCREMENT pour la table `command_items`
--
ALTER TABLE `command_items`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=98;

--
-- AUTO_INCREMENT pour la table `contact`
--
ALTER TABLE `contact`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `messenger_messages`
--
ALTER TABLE `messenger_messages`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `picture`
--
ALTER TABLE `picture`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT pour la table `product`
--
ALTER TABLE `product`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT pour la table `product_option`
--
ALTER TABLE `product_option`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=169;

--
-- AUTO_INCREMENT pour la table `user`
--
ALTER TABLE `user`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `command`
--
ALTER TABLE `command`
  ADD CONSTRAINT `FK_8ECAEAD4A76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `command_items`
--
ALTER TABLE `command_items`
  ADD CONSTRAINT `FK_72B6BC7E33E1689A` FOREIGN KEY (`command_id`) REFERENCES `command` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_72B6BC7E4584665A` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `picture`
--
ALTER TABLE `picture`
  ADD CONSTRAINT `FK_16DB4F894584665A` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `product_option`
--
ALTER TABLE `product_option`
  ADD CONSTRAINT `FK_38FA41144584665A` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
