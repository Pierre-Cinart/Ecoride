-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost:3306
-- Généré le : jeu. 22 mai 2025 à 21:20
-- Version du serveur : 8.0.34
-- Version de PHP : 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `ecoride`
--

-- --------------------------------------------------------

--
-- Structure de la table `cashback`
--

CREATE TABLE `cashback` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `type` enum('refund','payment') NOT NULL,
  `credits_requested` int NOT NULL,
  `reason` text,
  `status` enum('pending','approved','refused') DEFAULT 'pending',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `cashback`
--

INSERT INTO `cashback` (`id`, `user_id`, `type`, `credits_requested`, `reason`, `status`, `created_at`) VALUES
(1, 21, 'refund', 16, 'Annulation du trajet ID #10 le 16/05/2025', 'approved', '2025-05-12 17:08:31'),
(2, 21, 'refund', 16, 'Annulation du trajet ID #10 le 16/05/2025', 'approved', '2025-05-12 18:03:08'),
(3, 24, 'refund', 16, 'Annulation du trajet ID #10 le 16/05/2025', 'approved', '2025-05-13 15:20:38'),
(4, 21, 'refund', 16, 'Annulation du trajet ID #10 le 18/05/2025', 'pending', '2025-05-15 17:56:40'),
(5, 21, 'refund', 220, 'Annulation du trajet ID #19 le 21/05/2025', 'approved', '2025-05-17 21:31:42');

-- --------------------------------------------------------

--
-- Structure de la table `driver_preferences`
--

CREATE TABLE `driver_preferences` (
  `id` int NOT NULL,
  `driver_id` int NOT NULL,
  `allows_smoking` tinyint(1) DEFAULT '0',
  `allows_pets` tinyint(1) DEFAULT '0',
  `note_personnelle` text,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `driver_preferences`
--

INSERT INTO `driver_preferences` (`id`, `driver_id`, `allows_smoking`, `allows_pets`, `note_personnelle`, `updated_at`) VALUES
(1, 22, 1, 1, 'J’aime bien une ambiance musicale', '2025-05-13 19:13:11');

-- --------------------------------------------------------

--
-- Structure de la table `ratings`
--

CREATE TABLE `ratings` (
  `id` int NOT NULL,
  `author_id` int NOT NULL,
  `trip_id` int NOT NULL,
  `rating` decimal(2,1) DEFAULT NULL,
  `comment` text,
  `status` enum('pending','accepted','refused','waiting','deleted') DEFAULT 'waiting',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ;

--
-- Déchargement des données de la table `ratings`
--

INSERT INTO `ratings` (`id`, `author_id`, `trip_id`, `rating`, `comment`, `status`, `created_at`) VALUES
(4, 21, 7, 3.0, 'pas mal', 'deleted', '2025-05-13 16:14:10'),
(5, 21, 8, 3.5, 'le top du top ! ', 'accepted', '2025-05-09 14:24:10'),
(6, 21, 9, 4.0, 'cool', 'pending', '2025-05-12 18:23:03'),
(7, 21, 13, 4.0, 'très satisfait', 'accepted', '2025-05-11 18:13:08'),
(8, 21, 14, 4.0, NULL, 'deleted', '2025-05-12 10:32:09');

-- --------------------------------------------------------

--
-- Structure de la table `reports`
--

CREATE TABLE `reports` (
  `id` int NOT NULL,
  `reporter_id` int NOT NULL,
  `reported_id` int NOT NULL,
  `trip_id` int NOT NULL,
  `description` text NOT NULL,
  `status` enum('pending','resolved','ignored') DEFAULT 'pending',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `transactions`
--

CREATE TABLE `transactions` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `type` enum('add','refund','penalty','fee','withdraw','bonus','adjustment') NOT NULL,
  `credits` int NOT NULL,
  `description` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `transactions`
--

INSERT INTO `transactions` (`id`, `user_id`, `type`, `credits`, `description`, `created_at`) VALUES
(1, 21, 'add', 255, 'Achat simulé via cb', '2025-05-21 11:32:39'),
(2, 21, 'add', 2000, 'Achat simulé via cb', '2025-05-21 13:47:07'),
(7, 21, 'refund', 125, 'Remboursement suite à annulation trajet 21', '2025-05-21 14:00:30'),
(8, 22, 'penalty', 2, 'Pénalité pour annulation de trajet avec participants (ID 21)', '2025-05-21 14:00:34'),
(9, 21, 'refund', 180, 'Remboursement suite à annulation trajet 15', '2025-05-21 14:18:53'),
(10, 22, 'penalty', 2, 'Pénalité pour annulation de trajet avec participants (ID 15)', '2025-05-21 14:18:57'),
(11, 21, 'refund', 50, 'Remboursement suite à annulation trajet 25', '2025-05-21 14:23:09'),
(12, 22, 'penalty', 2, 'Pénalité pour annulation de trajet avec participants (ID 25)', '2025-05-21 14:23:09'),
(13, 21, 'refund', 10, 'Remboursement suite à annulation trajet 26', '2025-05-21 14:42:27'),
(14, 22, 'penalty', 2, 'Pénalité pour annulation de trajet avec participants (ID 26)', '2025-05-21 14:42:27'),
(15, 22, 'fee', 2, 'Frais de mise en ligne du trajet', '2025-05-21 15:01:25');

-- --------------------------------------------------------

--
-- Structure de la table `trips`
--

CREATE TABLE `trips` (
  `id` int NOT NULL,
  `driver_id` int NOT NULL,
  `vehicle_id` int NOT NULL,
  `departure_city` varchar(100) DEFAULT NULL,
  `departure_address` varchar(255) NOT NULL,
  `arrival_city` varchar(100) DEFAULT NULL,
  `arrival_address` varchar(255) NOT NULL,
  `departure_date` date DEFAULT NULL,
  `departure_time` time DEFAULT NULL,
  `price` decimal(9,2) NOT NULL,
  `is_ecological` tinyint(1) DEFAULT '0',
  `available_seats` int DEFAULT NULL,
  `status` enum('planned','ongoing','completed','canceled') DEFAULT 'planned',
  `estimated_duration` int DEFAULT NULL COMMENT 'Durée estimée en minutes (calculée via ORS)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `trips`
--

INSERT INTO `trips` (`id`, `driver_id`, `vehicle_id`, `departure_city`, `departure_address`, `arrival_city`, `arrival_address`, `departure_date`, `departure_time`, `price`, `is_ecological`, `available_seats`, `status`, `estimated_duration`) VALUES
(7, 22, 4, 'Lille', 'Adresse Lille', 'Paris', 'Adresse Paris', '2025-04-24', '08:00:00', 20.00, 1, 3, 'completed', NULL),
(8, 22, 4, 'Paris', 'Adresse Paris', 'Lyon', 'Adresse Lyon', '2025-04-29', '09:00:00', 20.00, 1, 3, 'completed', NULL),
(9, 22, 4, 'Lyon', 'Adresse Lyon', 'Nice', 'Adresse Nice', '2025-05-04', '10:00:00', 20.00, 1, 3, 'completed', NULL),
(10, 22, 4, 'Toulouse', 'Adresse Toulouse', 'Bordeaux', 'Adresse Bordeaux', '2025-05-18', '14:00:00', 18.00, 1, 3, 'planned', NULL),
(11, 22, 4, 'Bordeaux', 'Adresse Bordeaux', 'Nantes', 'Adresse Nantes', '2025-05-18', '10:30:00', 18.00, 1, 2, 'planned', NULL),
(12, 22, 4, 'Nantes', 'Adresse Nantes', 'Rennes', 'Adresse Rennes', '2025-05-20', '12:15:00', 18.00, 1, 2, 'planned', NULL),
(13, 22, 4, 'Nice', 'Adresse Nice', 'Marseille', 'Adresse Marseille', '2025-04-10', '08:30:00', 16.00, 1, 2, 'completed', NULL),
(14, 22, 4, 'Marseille', 'Adresse Marseille', 'Montpellier', 'Adresse Montpellier', '2025-04-15', '14:15:00', 14.00, 0, 2, 'completed', NULL),
(17, 22, 4, 'Paris', '10 Rue de Savoie Rue de Savoie', 'Chamigny', '7 Sente des Clos Sente des Clos', '2025-05-31', '18:09:00', 999.00, 1, 1, 'planned', 3748),
(22, 22, 4, 'Paris', '1 Rue d&#039;Anjou Rue d&#039;Anjou', 'Bussy-Saint-Martin', '8 Rue des Epinettes Rue des Epinettes', '2025-05-31', '16:00:00', 28.00, 1, 2, 'planned', 2596),
(23, 22, 4, 'Paris', '12 Rue Euler Rue Euler', 'Pantin', '13 Rue Montgolfier Rue Montgolfier', '2025-05-30', '17:12:00', 145.00, 1, 1, 'planned', 1519),
(24, 22, 4, 'Paris', '2 Port des Invalides Port des Invalides', 'Vitry-sur-Seine', '7 Avenue de la Commune de Paris Avenue de la Commune de Paris', '2025-05-30', '18:21:00', 88.00, 1, 2, 'planned', 1614),
(27, 22, 4, 'Lille', '26 Rue de la Vieille Aventure Rue de la Vieille Aventure', 'Saintines', '33 Rue Pasteur Rue Pasteur', '2025-05-30', '20:06:00', 200.00, 1, 2, 'planned', 6008);

-- --------------------------------------------------------

--
-- Structure de la table `trip_participants`
--

CREATE TABLE `trip_participants` (
  `id` int NOT NULL,
  `trip_id` int NOT NULL,
  `user_id` int NOT NULL,
  `confirmed` tinyint(1) DEFAULT '0',
  `credits_used` int DEFAULT NULL,
  `confirmation_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `trip_participants`
--

INSERT INTO `trip_participants` (`id`, `trip_id`, `user_id`, `confirmed`, `credits_used`, `confirmation_date`) VALUES
(3, 13, 21, 1, 16, '2025-05-11 18:13:08'),
(4, 14, 21, 1, 14, '2025-05-11 18:13:08'),
(5, 7, 21, 1, 20, '2025-05-11 22:58:44'),
(6, 8, 21, 1, 20, '2025-05-11 22:58:44'),
(7, 9, 21, 1, 20, '2025-05-11 22:58:44'),
(8, 10, 21, 0, 18, '2025-05-15 17:56:40'),
(9, 10, 24, 0, 18, '2025-05-13 15:20:38'),
(14, 23, 21, 1, 145, '2025-05-21 13:38:47'),
(15, 17, 21, 1, 999, '2025-05-21 13:44:34');

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `pseudo` varchar(50) NOT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `role` enum('user','driver','employee','admin') NOT NULL DEFAULT 'user',
  `credits` int DEFAULT '20',
  `is_verified_email` tinyint(1) DEFAULT '0',
  `email_verification_token` varchar(255) DEFAULT NULL,
  `email_token_expires_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `jwt_token` varchar(255) DEFAULT NULL,
  `jwt_token_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('authorized','drive_blocked','blocked','all_blocked','banned') DEFAULT 'authorized',
  `driver_warnings` int NOT NULL DEFAULT '0',
  `user_warnings` int NOT NULL DEFAULT '0',
  `profil_picture` varchar(255) DEFAULT NULL,
  `permit_picture` varchar(255) DEFAULT NULL,
  `permit_status` enum('waiting','pending','approved','refused') DEFAULT 'waiting',
  `birthdate` date DEFAULT NULL,
  `gender` enum('male','female') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `pseudo`, `first_name`, `last_name`, `email`, `password`, `phone_number`, `role`, `credits`, `is_verified_email`, `email_verification_token`, `email_token_expires_at`, `created_at`, `jwt_token`, `jwt_token_time`, `status`, `driver_warnings`, `user_warnings`, `profil_picture`, `permit_picture`, `permit_status`, `birthdate`, `gender`) VALUES
(19, 'admin', 'José', 'Admin', 'admin@ecoride.fr', '$2y$10$./VTcjrBydXwHAoZKSyx/.MghZW9JS.dbh/rxqvQy5BLs8fyS3asu', '0100000001', 'admin', 20, 1, NULL, NULL, '2025-05-09 16:24:10', 'd614e6e5072bd5e49d0d27ce97d28dcb6d6b4a99a2bd42cbda3f7ad9bb1baffd9f7ead230bf166da7684fff22bc26721d26da1f133879a01db776231a81d2370', '2025-05-22 23:14:56', 'authorized', 0, 0, NULL, NULL, 'waiting', '1985-06-10', 'male'),
(20, 'employe1', 'Employé', 'Test', 'employe@ecoride.fr', '$2y$10$PqbZfFNNuRy9yGZQSCWRneKmZqqh1nA5NfwT71zsOnuogDwRKo/zq', '0100000002', 'employee', 20, 1, NULL, NULL, '2025-05-09 16:24:10', '37490ce434c5901eb40ea4083c74897cb801393fe14e4ce68bf8d031fb37762a9a004a812b3c01292846535c034980041bf49820dae0eeb83df409edd5ec9a51', '2025-05-21 20:27:10', 'authorized', 0, 0, NULL, NULL, 'waiting', '1990-08-15', 'male'),
(21, 'user1', 'User', 'Test', 'user@ecoride.fr', '$2y$10$9GX5Zd.zPl9mK6BdAOgbU.xBznDi9KxrnsK7OARoasE34uD9qIoPq', '0100000003', 'user', 2143, 1, NULL, NULL, '2025-05-09 16:24:10', '02ea9bc4612d57a0b75320e4902a3ff8e3898b5ad14e1479e660999f9d7fca2e3313c3bcad750ccb63672ff4773fb4c9866e9af120cdfb8fd2e9e14a861ccba1', '2025-05-22 10:32:03', 'authorized', 0, 10, NULL, NULL, 'waiting', '1995-04-20', 'male'),
(22, 'driver1', 'Conducteur', 'Valide', 'driver.valide@ecoride.fr', '$2y$10$5g5Ldm.cNTjXoSlEPH0fL.MW3/j275gDW5bKhfCTs1NYEt.jpQnzW', '0100000004', 'driver', 10, 1, NULL, NULL, '2025-05-09 16:24:10', '11fa1ae9bfe745d698cec85efa4b62909aa3cff42654737b07c5f7034c4939b934120a33b16576913ad09957b1e1155604ad1cf449bb1c3152f3cafb32f49f96', '2025-05-22 10:37:37', 'authorized', 3, 10, NULL, NULL, 'approved', '1980-02-05', 'male'),
(23, 'driver2', 'Conducteur', 'Attente', 'driver.attente@ecoride.fr', '$2y$10$SeGwdYtFAKLN22TYIcGBu.Q1S3hiGD38oHpxm3nePHPQnW8GiFI7C', '0100000005', 'driver', 20, 1, NULL, NULL, '2025-05-09 16:24:10', NULL, '2025-05-09 14:24:10', 'authorized', 0, 0, NULL, NULL, 'waiting', '1992-11-18', 'female'),
(24, 'wamere', 'Pierre', 'Poljack', 'cinae@gmail.com', '$2y$10$LawQhqwc5U/AiwcH1S5fBOLHv5HnXUUV0657zGfc18YGet.0h2nWG', '0671038294', 'user', 102, 1, '8fe1762e3eaa6b76ade1aa8b3e855afdb813d3420d72bab274d63540accc656693d3195b81df75c22548fc1ba0d393da1778af18168f1e4c7acd9249c6ffad79', '2025-05-14 12:07:30', '2025-05-13 13:52:18', 'beb27620abedf77a38b087fba97861209e0ee93ad6407a9e0b761b8ecb50aa211ddc0024c59631c421fda09f0b0d61f665caff0322bb9ad076b256bc9a3793ae', '2025-05-13 15:24:47', 'authorized', 0, 0, NULL, NULL, 'waiting', '1996-03-30', 'male'),
(32, 'dmt', 'Daniel', 'Martin', 'dmt@gg.fr', '$2y$10$UYxfd/7Mp6PL0ifFH9uWg.3F5EkYREc8yn1M1slhpp/XApYQKRTnq', '0671038888', 'user', 0, 1, 'ce45592e611fac31781f8ddb29a31e6839bef6a2516db6655194357e99f6bbd4c649eea61a7e446ca163e57e83eb07b30c25d30a2fe4a375d288119aeb41afc7', '2025-05-17 16:21:54', '2025-05-16 18:21:54', 'adb1af4d9ddd3ee0e3f40da5d796546fda7a84aa5280a8f47a63ec0c39a14792f787f7225a2f8f5469c7a15d780524dcc67c1f33df324ed0c7a468f4fe4fd6e9', '2025-05-16 21:09:00', 'drive_blocked', 0, 20, NULL, 'uploads/dmt/permit/68276622d7403.webp', 'pending', '2006-01-16', 'male'),
(33, 'juju', 'julie', 'labelle', 'juju@gg.fr', '$2y$10$L.Q4b7FlOTSAaY8amBx85OR1mk26bdv4dc3vfD0h2YM5B1D/rLI2K', '0658963247', 'user', 10, 1, '339cfce0829458950b5553c630a2454613bba8245cbbf5d092b8d3cdcd2b5bfe2fc76a8adfb2f222ef356b595488e73c1e34826c603b7da8753287c260d8678c', '2025-05-17 16:24:50', '2025-05-16 18:24:50', NULL, '2025-05-16 16:24:50', 'authorized', 0, 20, NULL, NULL, 'waiting', '1995-09-26', 'female'),
(34, 'EMP_20250521_205537_5c04d7', 'nouvel', 'employé', 'new@test.mail.com', '$2y$10$SPlGqbpk3oe8EFTh8zYztefxbZFYQeBjPwSdV0BB9uh7AFV.gEOhK', '0671000000', 'employee', 20, 1, NULL, NULL, '2025-05-21 22:55:37', '0c68a902ab5430686253eb1d2e5879a5975279e9e6ddf24a33bff2cdb8aa5d55f9daf05e5de9e03ce67d47430481b2fcb8cd713335b87596826d31020ba2ac8f', '2025-05-22 22:38:30', 'authorized', 0, 0, NULL, NULL, 'waiting', '1997-09-16', 'male');

-- --------------------------------------------------------

--
-- Structure de la table `vehicles`
--

CREATE TABLE `vehicles` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `brand` varchar(100) DEFAULT NULL,
  `model` varchar(100) DEFAULT NULL,
  `color` varchar(50) DEFAULT NULL,
  `fuel_type` enum('electric','hybrid','gasoline','diesel') DEFAULT NULL,
  `registration_number` varchar(50) DEFAULT NULL,
  `first_registration_date` date DEFAULT NULL,
  `seats` int NOT NULL,
  `picture` varchar(255) DEFAULT NULL,
  `registration_document` varchar(255) DEFAULT NULL,
  `insurance_document` varchar(255) DEFAULT NULL,
  `documents_status` enum('pending','approved','refused') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `vehicles`
--

INSERT INTO `vehicles` (`id`, `user_id`, `brand`, `model`, `color`, `fuel_type`, `registration_number`, `first_registration_date`, `seats`, `picture`, `registration_document`, `insurance_document`, `documents_status`) VALUES
(4, 22, 'Renault', 'ZOE', 'Blanc', 'electric', 'AB-123-CD', '2023-05-15', 4, '', NULL, NULL, 'approved'),
(26, 22, 'Renault', 'TEST', 'Blanc', 'electric', 'AB-1234-CD', '2023-05-15', 4, 'uploads/driver1/vehicle/6829ab285a95d.webp', 'uploads/driver1/document/6829ab2828848.webp', 'uploads/driver1/document/6829ab28423fa.webp', 'pending'),
(31, 22, 'test', 'test insert', 'non précisée', 'gasoline', 'non renseignée', '2025-05-18', 5, 'uploads/driver1/vehicle/6829cadcc8740.webp', 'uploads/driver1/document/6829cadc8b8d8.webp', 'uploads/driver1/document/6829cadcac680.webp', 'pending'),
(34, 22, 'qqqqqq', 'qqqqqqq', 'non précisée', 'gasoline', 'qqqqqqqqqqq', '2025-05-14', 4, 'uploads/driver1/vehicle/6829d2cf9bc6b.webp', 'uploads/driver1/document/6829d2cf56540.webp', 'uploads/driver1/document/6829d2cf7bfa0.webp', 'pending');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `cashback`
--
ALTER TABLE `cashback`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `driver_preferences`
--
ALTER TABLE `driver_preferences`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `driver_id` (`driver_id`);

--
-- Index pour la table `ratings`
--
ALTER TABLE `ratings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_ratings_author` (`author_id`),
  ADD KEY `fk_ratings_trip` (`trip_id`);

--
-- Index pour la table `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_reports_reporter` (`reporter_id`),
  ADD KEY `fk_reports_reported` (`reported_id`),
  ADD KEY `fk_reports_trip` (`trip_id`);

--
-- Index pour la table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `trips`
--
ALTER TABLE `trips`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_trips_driver` (`driver_id`),
  ADD KEY `fk_trips_vehicle` (`vehicle_id`);

--
-- Index pour la table `trip_participants`
--
ALTER TABLE `trip_participants`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_trip_participants_trip` (`trip_id`),
  ADD KEY `fk_trip_participants_user` (`user_id`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `email_2` (`email`),
  ADD UNIQUE KEY `pseudo` (`pseudo`),
  ADD UNIQUE KEY `phone_number` (`phone_number`);

--
-- Index pour la table `vehicles`
--
ALTER TABLE `vehicles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_registration_number` (`registration_number`),
  ADD KEY `fk_vehicles_user` (`user_id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `cashback`
--
ALTER TABLE `cashback`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `driver_preferences`
--
ALTER TABLE `driver_preferences`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `ratings`
--
ALTER TABLE `ratings`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `reports`
--
ALTER TABLE `reports`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT pour la table `trips`
--
ALTER TABLE `trips`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT pour la table `trip_participants`
--
ALTER TABLE `trip_participants`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT pour la table `vehicles`
--
ALTER TABLE `vehicles`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `cashback`
--
ALTER TABLE `cashback`
  ADD CONSTRAINT `cashback_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Contraintes pour la table `driver_preferences`
--
ALTER TABLE `driver_preferences`
  ADD CONSTRAINT `driver_preferences_ibfk_1` FOREIGN KEY (`driver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `ratings`
--
ALTER TABLE `ratings`
  ADD CONSTRAINT `fk_ratings_author` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_ratings_trip` FOREIGN KEY (`trip_id`) REFERENCES `trips` (`id`);

--
-- Contraintes pour la table `reports`
--
ALTER TABLE `reports`
  ADD CONSTRAINT `fk_reports_reported` FOREIGN KEY (`reported_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_reports_reporter` FOREIGN KEY (`reporter_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_reports_trip` FOREIGN KEY (`trip_id`) REFERENCES `trips` (`id`);

--
-- Contraintes pour la table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `trips`
--
ALTER TABLE `trips`
  ADD CONSTRAINT `fk_trips_driver` FOREIGN KEY (`driver_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_trips_vehicle` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`);

--
-- Contraintes pour la table `trip_participants`
--
ALTER TABLE `trip_participants`
  ADD CONSTRAINT `fk_trip_participants_trip` FOREIGN KEY (`trip_id`) REFERENCES `trips` (`id`),
  ADD CONSTRAINT `fk_trip_participants_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Contraintes pour la table `vehicles`
--
ALTER TABLE `vehicles`
  ADD CONSTRAINT `fk_vehicles_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
