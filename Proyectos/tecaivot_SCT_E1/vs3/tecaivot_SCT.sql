-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 20-08-2026 a las 13:00:50
-- Versión del servidor: 10.11.18-MariaDB-cll-lve
-- Versión de PHP: 8.4.24

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `tecaivot_SCT`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `audits`
--

CREATE TABLE `audits` (
  `id_audits` int(11) NOT NULL,
  `id_company` int(11) NOT NULL,
  `name_auditor` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `score` varchar(50) NOT NULL,
  `obs` varchar(50) NOT NULL,
  `created_by` varchar(50) NOT NULL,
  `date_create` datetime NOT NULL,
  `last_update` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `company`
--

CREATE TABLE `company` (
  `id_company` int(11) NOT NULL,
  `rut` varchar(50) NOT NULL,
  `razon social` varchar(50) NOT NULL,
  `address` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `state` int(11) NOT NULL,
  `created_by` varchar(50) NOT NULL,
  `date_create` datetime NOT NULL,
  `last_update` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `company`
--

INSERT INTO `company` (`id_company`, `rut`, `razon social`, `address`, `email`, `state`, `created_by`, `date_create`, `last_update`) VALUES
(1, '77742346-0', 'helheim', 'Santiago', 'contacto@helheim.cl', 1, 'phpmyadmin', '2026-08-14 20:50:47', '2026-08-14 20:50:47'),
(2, '1234', 'tecaivot', 'Santiago', 'contacto@tecaivot.cl', 1, 'phpmyadmin', '2026-08-15 12:00:58', '2026-08-15 12:00:58');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `company_center`
--

CREATE TABLE `company_center` (
  `id_company_center` int(11) NOT NULL,
  `id_company` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` varchar(50) NOT NULL,
  `state` int(11) NOT NULL,
  `create_by` varchar(50) NOT NULL,
  `date_create` datetime NOT NULL,
  `last_update` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `company_test`
--

CREATE TABLE `company_test` (
  `id_test` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` varchar(50) NOT NULL,
  `version` int(11) NOT NULL,
  `state` int(11) NOT NULL,
  `attempts_allowed` int(11) NOT NULL,
  `approval_percentage` int(11) NOT NULL,
  `effective_date_from` datetime NOT NULL,
  `effective_date_until` datetime NOT NULL,
  `created_by` varchar(50) NOT NULL,
  `date_created` datetime NOT NULL,
  `last_update` datetime NOT NULL,
  `id_company` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `company_test_rel_questions`
--

CREATE TABLE `company_test_rel_questions` (
  `id_rel` int(11) NOT NULL,
  `id_test` int(11) NOT NULL,
  `id_question` int(11) NOT NULL,
  `assigned_score` int(11) NOT NULL,
  `created_by` varchar(50) NOT NULL,
  `date_create` datetime NOT NULL,
  `last_update` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `log`
--

CREATE TABLE `log` (
  `id_log` int(11) NOT NULL,
  `id_users` varchar(50) NOT NULL,
  `id_action` int(11) NOT NULL,
  `bd_msj` varchar(50) NOT NULL,
  `create_date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `login_attempts`
--

CREATE TABLE `login_attempts` (
  `id` int(11) NOT NULL,
  `identifier` varchar(255) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `success` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `login_attempts`
--

INSERT INTO `login_attempts` (`id`, `identifier`, `ip_address`, `success`, `created_at`) VALUES
(20, 'juanantonioconchaloyola@gmail.com', '179.4.50.96', 1, '2026-08-15 13:43:55'),
(21, 'juanantonioconchaloyola@gmail.com', '179.4.50.96', 1, '2026-08-15 14:05:12'),
(22, 'juanantonioconchaloyola@gmail.com', '179.4.50.96', 1, '2026-08-15 14:05:22'),
(23, 'juanantonioconchaloyola@gmail.com', '179.4.50.96', 1, '2026-08-15 14:05:33'),
(24, 'juanantonioconchaloyola@gmail.com', '179.4.50.96', 1, '2026-08-15 14:06:12'),
(25, 'juanantonioconchaloyola@gmail.com', '179.4.50.96', 1, '2026-08-15 14:06:35'),
(26, 'juanantonioconchaloyola@gmail.com', '179.4.50.96', 1, '2026-08-15 14:19:16'),
(27, 'spam@gmail.com', '179.4.50.96', 0, '2026-08-15 14:19:53'),
(28, 'juanantonioconchaloyola@gmail.com', '179.4.50.96', 1, '2026-08-15 14:45:02'),
(29, 'pablotroncoso@gmail.com', '190.162.229.170', 1, '2026-08-16 18:41:41');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `questions`
--

CREATE TABLE `questions` (
  `id_questions` int(11) NOT NULL,
  `question` varchar(50) NOT NULL,
  `url_add_material` varchar(50) NOT NULL,
  `difficulty` int(11) NOT NULL,
  `points` int(11) NOT NULL,
  `state` int(11) NOT NULL,
  `add_expl_question` varchar(100) NOT NULL,
  `create_by` varchar(50) NOT NULL,
  `date_create` datetime NOT NULL,
  `last_update` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `questions_options`
--

CREATE TABLE `questions_options` (
  `id_questions_options` int(11) NOT NULL,
  `id_questions` int(11) NOT NULL,
  `text_option` varchar(50) NOT NULL,
  `is_it_co` int(11) NOT NULL,
  `add_expl_opt` varchar(100) NOT NULL,
  `state` int(11) NOT NULL,
  `created_by` varchar(50) NOT NULL,
  `date_create` datetime NOT NULL,
  `last_update` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `security_events`
--

CREATE TABLE `security_events` (
  `id_security_events` int(11) NOT NULL,
  `id_company` int(11) NOT NULL,
  `id_company_center` int(11) NOT NULL,
  `id_worker` varchar(50) NOT NULL,
  `id_worker_name` varchar(50) NOT NULL,
  `id_event` int(11) NOT NULL,
  `event_date` datetime NOT NULL,
  `description` varchar(50) NOT NULL,
  `criticality` int(11) NOT NULL,
  `state` int(11) NOT NULL,
  `created_by` varchar(50) NOT NULL,
  `date_create` datetime NOT NULL,
  `last_update` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `security_event_tracking`
--

CREATE TABLE `security_event_tracking` (
  `id_security_event_tracking` int(11) NOT NULL,
  `id_security events` int(11) NOT NULL,
  `tracking_description` varchar(50) NOT NULL,
  `person_charge` varchar(50) NOT NULL,
  `commitment_date` datetime NOT NULL,
  `deadline` datetime NOT NULL,
  `created_by` varchar(50) NOT NULL,
  `date_create` datetime NOT NULL,
  `last_update` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id_users` varchar(50) NOT NULL COMMENT 'e-mail',
  `id_company` varchar(50) NOT NULL,
  `name` varchar(50) NOT NULL,
  `lastname` varchar(50) NOT NULL,
  `rut` varchar(10) NOT NULL,
  `password_hash` varchar(255) DEFAULT NULL,
  `state` int(11) NOT NULL,
  `language` varchar(11) NOT NULL,
  `last_access` datetime NOT NULL,
  `created_by` varchar(50) NOT NULL,
  `date_create` datetime NOT NULL,
  `last_update` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id_users`, `id_company`, `name`, `lastname`, `rut`, `password_hash`, `state`, `language`, `last_access`, `created_by`, `date_create`, `last_update`) VALUES
('fco.fredes.g@gmail.com', '2', 'francisco', 'fredes', '1234', '$2y$10$GV6jDq5BKJrImfLVKKpti.oxbYxdjR8dQ1MdmCP8pyyiOlH4Ozwt6', 1, 'ESP', '2026-08-15 11:58:40', 'phpmyadmin', '2026-08-15 11:58:40', '2026-08-15 11:58:40'),
('juanantonioconchaloyola@gmail.com', '1', 'antonio', 'helheim', '16725278-8', '$2y$10$PGRe/y2lW9AwLK2MLi.thOaRnGY3f31zrynzexuE8tBq2XTNR2sHW', 1, 'ESP', '2026-08-14 20:46:32', 'phpmyadmin', '2026-08-14 20:46:30', '2026-08-14 20:46:30'),
('malazga99@gmail.com', '1', 'maite', 'lazcano', '20153481-k', NULL, 1, 'ESP', '2026-08-14 21:41:57', 'phpmyadmin', '2026-08-14 21:41:57', '2026-08-14 21:41:57'),
('pablotroncoso@gmail.com', '1', 'pablo', 'troncoso', '1234', '$2y$10$QJNn.HuRSIAIXxC5IxYElOuJNaqPa3raIeq18aSyClXkAVBqF6MFm', 1, 'ESP', '2026-08-14 21:43:14', 'phpmyadmin', '2026-08-14 21:43:14', '2026-08-14 21:43:14');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users_role`
--

CREATE TABLE `users_role` (
  `id_users_role` int(11) NOT NULL,
  `id_users` varchar(50) NOT NULL,
  `id_role_group` int(11) NOT NULL,
  `state` int(11) NOT NULL,
  `create_by` varchar(50) NOT NULL,
  `date_create` datetime NOT NULL,
  `last_update` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users_role_group`
--

CREATE TABLE `users_role_group` (
  `id_role_group` int(11) NOT NULL,
  `id_company` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` varchar(50) NOT NULL,
  `state` int(11) NOT NULL,
  `create_by` varchar(50) NOT NULL,
  `date_create` datetime NOT NULL,
  `last_update` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users_test_answers`
--

CREATE TABLE `users_test_answers` (
  `id_users_test_answers` int(11) NOT NULL,
  `id_users` int(11) NOT NULL,
  `id_company` int(11) NOT NULL,
  `id_test` int(11) NOT NULL,
  `id_test_try` int(11) NOT NULL,
  `id_rel` int(11) NOT NULL,
  `id_question` int(11) NOT NULL,
  `id_questions_options` int(11) NOT NULL,
  `date_create` datetime NOT NULL,
  `last_update` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users_test_assigned`
--

CREATE TABLE `users_test_assigned` (
  `id_user_test_assigned` int(11) NOT NULL,
  `id_users` int(11) NOT NULL,
  `id_test` int(11) NOT NULL,
  `id_company` int(11) NOT NULL,
  `assignamente_date` datetime NOT NULL,
  `deadline` datetime NOT NULL,
  `state` int(11) NOT NULL,
  `created_by` varchar(50) NOT NULL,
  `date_create` datetime NOT NULL,
  `last_update` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `audits`
--
ALTER TABLE `audits`
  ADD PRIMARY KEY (`id_audits`);

--
-- Indices de la tabla `company`
--
ALTER TABLE `company`
  ADD PRIMARY KEY (`id_company`),
  ADD UNIQUE KEY `rut` (`rut`);

--
-- Indices de la tabla `company_center`
--
ALTER TABLE `company_center`
  ADD PRIMARY KEY (`id_company_center`);

--
-- Indices de la tabla `company_test`
--
ALTER TABLE `company_test`
  ADD PRIMARY KEY (`id_test`);

--
-- Indices de la tabla `company_test_rel_questions`
--
ALTER TABLE `company_test_rel_questions`
  ADD PRIMARY KEY (`id_rel`);

--
-- Indices de la tabla `log`
--
ALTER TABLE `log`
  ADD PRIMARY KEY (`id_log`);

--
-- Indices de la tabla `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_identifier_time` (`identifier`,`created_at`),
  ADD KEY `idx_ip_time` (`ip_address`,`created_at`);

--
-- Indices de la tabla `questions`
--
ALTER TABLE `questions`
  ADD PRIMARY KEY (`id_questions`);

--
-- Indices de la tabla `questions_options`
--
ALTER TABLE `questions_options`
  ADD PRIMARY KEY (`id_questions_options`);

--
-- Indices de la tabla `security_events`
--
ALTER TABLE `security_events`
  ADD PRIMARY KEY (`id_security_events`);

--
-- Indices de la tabla `security_event_tracking`
--
ALTER TABLE `security_event_tracking`
  ADD PRIMARY KEY (`id_security_event_tracking`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_users`);

--
-- Indices de la tabla `users_role`
--
ALTER TABLE `users_role`
  ADD PRIMARY KEY (`id_users_role`);

--
-- Indices de la tabla `users_role_group`
--
ALTER TABLE `users_role_group`
  ADD PRIMARY KEY (`id_role_group`);

--
-- Indices de la tabla `users_test_answers`
--
ALTER TABLE `users_test_answers`
  ADD PRIMARY KEY (`id_users_test_answers`);

--
-- Indices de la tabla `users_test_assigned`
--
ALTER TABLE `users_test_assigned`
  ADD PRIMARY KEY (`id_user_test_assigned`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `audits`
--
ALTER TABLE `audits`
  MODIFY `id_audits` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `company`
--
ALTER TABLE `company`
  MODIFY `id_company` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `company_center`
--
ALTER TABLE `company_center`
  MODIFY `id_company_center` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `company_test`
--
ALTER TABLE `company_test`
  MODIFY `id_test` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `company_test_rel_questions`
--
ALTER TABLE `company_test_rel_questions`
  MODIFY `id_rel` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `log`
--
ALTER TABLE `log`
  MODIFY `id_log` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT de la tabla `questions`
--
ALTER TABLE `questions`
  MODIFY `id_questions` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `questions_options`
--
ALTER TABLE `questions_options`
  MODIFY `id_questions_options` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `security_events`
--
ALTER TABLE `security_events`
  MODIFY `id_security_events` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `security_event_tracking`
--
ALTER TABLE `security_event_tracking`
  MODIFY `id_security_event_tracking` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `users_role`
--
ALTER TABLE `users_role`
  MODIFY `id_users_role` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `users_role_group`
--
ALTER TABLE `users_role_group`
  MODIFY `id_role_group` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `users_test_answers`
--
ALTER TABLE `users_test_answers`
  MODIFY `id_users_test_answers` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `users_test_assigned`
--
ALTER TABLE `users_test_assigned`
  MODIFY `id_user_test_assigned` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
