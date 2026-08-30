-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost
-- Tiempo de generación: 30-08-2026 a las 18:11:21
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `bifrost`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `battle_challenges`
--

CREATE TABLE `battle_challenges` (
  `id` int(10) UNSIGNED NOT NULL,
  `from_user_id` int(10) UNSIGNED NOT NULL,
  `to_user_id` int(10) UNSIGNED NOT NULL,
  `status` enum('pending','accepted','declined') NOT NULL DEFAULT 'pending',
  `battle_id` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `login_attempts`
--

CREATE TABLE `login_attempts` (
  `id` int(10) UNSIGNED NOT NULL,
  `identifier` varchar(190) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `success` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `login_attempts`
--

INSERT INTO `login_attempts` (`id`, `identifier`, `ip_address`, `success`, `created_at`) VALUES
(2, 'juanantonioconchaloyola@gmail.com', '127.0.0.1', 1, '2026-08-30 16:08:20');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `login_codes`
--

CREATE TABLE `login_codes` (
  `id_login_code` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `code_hash` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `attempts` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `ip_address` varchar(45) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `login_codes`
--

INSERT INTO `login_codes` (`id_login_code`, `user_id`, `code_hash`, `expires_at`, `used_at`, `attempts`, `ip_address`, `created_at`) VALUES
(2, 1, '$2y$10$5ECIpuWQSehe7ARG9JVXZOc6emTp9FThTBjQazOnbQV7EUT6/NbXS', '2026-08-30 18:18:14', '2026-08-30 12:08:20', 0, '127.0.0.1', '2026-08-30 16:08:14');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `player_positions`
--

CREATE TABLE `player_positions` (
  `user_id` int(10) UNSIGNED NOT NULL,
  `username` varchar(32) NOT NULL,
  `map_key` varchar(64) NOT NULL DEFAULT 'overworld',
  `pos_x` int(11) NOT NULL DEFAULT 0,
  `pos_y` int(11) NOT NULL DEFAULT 0,
  `facing` varchar(8) NOT NULL DEFAULT 'down',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pvp_battles`
--

CREATE TABLE `pvp_battles` (
  `id` int(10) UNSIGNED NOT NULL,
  `player1_id` int(10) UNSIGNED NOT NULL,
  `player2_id` int(10) UNSIGNED NOT NULL,
  `mon1_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`mon1_json`)),
  `mon2_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`mon2_json`)),
  `turn_user_id` int(10) UNSIGNED NOT NULL,
  `last_action` varchar(255) NOT NULL DEFAULT '',
  `status` enum('active','finished') NOT NULL DEFAULT 'active',
  `winner_id` int(10) UNSIGNED DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `saves`
--

CREATE TABLE `saves` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `map_key` varchar(64) NOT NULL DEFAULT 'overworld',
  `pos_x` int(11) NOT NULL DEFAULT 0,
  `pos_y` int(11) NOT NULL DEFAULT 0,
  `party_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`party_json`)),
  `inventory_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`inventory_json`)),
  `character_created` tinyint(1) NOT NULL DEFAULT 0,
  `gender` enum('boy','girl') DEFAULT NULL,
  `skin_color` varchar(7) NOT NULL DEFAULT '#f1c27d',
  `hair_color` varchar(7) NOT NULL DEFAULT '#2c1b18',
  `eye_color` varchar(7) NOT NULL DEFAULT '#3b2415',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `saves`
--

INSERT INTO `saves` (`id`, `user_id`, `map_key`, `pos_x`, `pos_y`, `party_json`, `inventory_json`, `character_created`, `gender`, `skin_color`, `hair_color`, `eye_color`, `updated_at`) VALUES
(31, 1, 'overworld', 14, 2, '[]', '{\"pokeball\":5}', 1, 'boy', '#f1c27d', '#2c1b18', '#3b2415', '2026-08-30 16:08:47');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `species`
--

CREATE TABLE `species` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(32) NOT NULL,
  `type` varchar(16) NOT NULL DEFAULT 'normal',
  `base_hp` int(10) UNSIGNED NOT NULL DEFAULT 20,
  `base_atk` int(10) UNSIGNED NOT NULL DEFAULT 10,
  `base_def` int(10) UNSIGNED NOT NULL DEFAULT 10,
  `sprite_key` varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `species`
--

INSERT INTO `species` (`id`, `name`, `type`, `base_hp`, `base_atk`, `base_def`, `sprite_key`) VALUES
(25, 'Chispodrilo', 'fuego', 22, 12, 7, 'fire_1'),
(26, 'Braseryx', 'fuego', 28, 16, 11, 'fire_2'),
(27, 'Vulcanor', 'fuego', 35, 21, 15, 'fire_3'),
(28, 'Marejino', 'agua', 24, 9, 10, 'water_1'),
(29, 'Corrientauro', 'agua', 30, 13, 14, 'water_2'),
(30, 'Abisalgo', 'agua', 38, 17, 19, 'water_3'),
(31, 'Brotalín', 'planta', 23, 10, 9, 'grass_1'),
(32, 'Espigón', 'planta', 29, 14, 13, 'grass_2'),
(33, 'Follascorpio', 'planta', 36, 18, 17, 'grass_3'),
(34, 'Chispequín', 'electricidad', 21, 13, 6, 'electric_1'),
(35, 'Voltígero', 'electricidad', 27, 17, 10, 'electric_2'),
(36, 'Amperidna', 'electricidad', 33, 22, 13, 'electric_3'),
(37, 'Puñolet', 'lucha', 24, 13, 8, 'fighting_1'),
(38, 'Katáfaro', 'lucha', 30, 17, 12, 'fighting_2'),
(39, 'Granmaestro', 'lucha', 37, 22, 16, 'fighting_3'),
(40, 'Plumín', 'volador', 20, 11, 7, 'flying_1'),
(41, 'Ventizarro', 'volador', 26, 15, 10, 'flying_2'),
(42, 'Tormenpluma', 'volador', 32, 19, 13, 'flying_3'),
(43, 'Sombrigato', 'oscuro', 22, 12, 8, 'dark_1'),
(44, 'Penumbraz', 'oscuro', 28, 16, 11, 'dark_2'),
(45, 'Eclipsino', 'oscuro', 35, 20, 15, 'dark_3'),
(46, 'Solete', 'diurno', 23, 11, 9, 'day_1'),
(47, 'Auroraz', 'diurno', 29, 15, 12, 'day_2'),
(48, 'Radialbo', 'diurno', 36, 19, 16, 'day_3');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `username` varchar(32) NOT NULL,
  `email` varchar(190) DEFAULT NULL,
  `password_hash` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `created_at`) VALUES
(1, 'jacl14021988', 'juanantonioconchaloyola@gmail.com', NULL, '2026-08-30 16:08:13');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `battle_challenges`
--
ALTER TABLE `battle_challenges`
  ADD PRIMARY KEY (`id`),
  ADD KEY `from_user_id` (`from_user_id`),
  ADD KEY `to_user_id` (`to_user_id`);

--
-- Indices de la tabla `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `login_codes`
--
ALTER TABLE `login_codes`
  ADD PRIMARY KEY (`id_login_code`),
  ADD KEY `user_id` (`user_id`);

--
-- Indices de la tabla `player_positions`
--
ALTER TABLE `player_positions`
  ADD PRIMARY KEY (`user_id`);

--
-- Indices de la tabla `pvp_battles`
--
ALTER TABLE `pvp_battles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `player1_id` (`player1_id`),
  ADD KEY `player2_id` (`player2_id`);

--
-- Indices de la tabla `saves`
--
ALTER TABLE `saves`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_save` (`user_id`);

--
-- Indices de la tabla `species`
--
ALTER TABLE `species`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `battle_challenges`
--
ALTER TABLE `battle_challenges`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `login_codes`
--
ALTER TABLE `login_codes`
  MODIFY `id_login_code` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `pvp_battles`
--
ALTER TABLE `pvp_battles`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `saves`
--
ALTER TABLE `saves`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT de la tabla `species`
--
ALTER TABLE `species`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `battle_challenges`
--
ALTER TABLE `battle_challenges`
  ADD CONSTRAINT `battle_challenges_ibfk_1` FOREIGN KEY (`from_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `battle_challenges_ibfk_2` FOREIGN KEY (`to_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `login_codes`
--
ALTER TABLE `login_codes`
  ADD CONSTRAINT `login_codes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `player_positions`
--
ALTER TABLE `player_positions`
  ADD CONSTRAINT `player_positions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `pvp_battles`
--
ALTER TABLE `pvp_battles`
  ADD CONSTRAINT `pvp_battles_ibfk_1` FOREIGN KEY (`player1_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pvp_battles_ibfk_2` FOREIGN KEY (`player2_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `saves`
--
ALTER TABLE `saves`
  ADD CONSTRAINT `saves_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
