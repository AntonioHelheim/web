-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost
-- Tiempo de generación: 04-09-2026 a las 06:06:46
-- Versión del servidor: 10.4.28-MariaDB
-- Versión de PHP: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `safetyco_SCT`
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
  `obs` text NOT NULL,
  `created_by` varchar(50) NOT NULL,
  `date_create` datetime NOT NULL,
  `last_update` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `certificates`
--

CREATE TABLE `certificates` (
  `id_certificate` int(11) NOT NULL,
  `id_user_test_assigned` int(11) NOT NULL,
  `code` varchar(50) NOT NULL COMMENT 'código único de verificación',
  `file_path` varchar(255) NOT NULL,
  `issued_at` datetime NOT NULL,
  `state` int(11) NOT NULL DEFAULT 1,
  `created_by` varchar(50) NOT NULL,
  `date_create` datetime NOT NULL,
  `last_update` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `change_history`
--

CREATE TABLE `change_history` (
  `id_change` int(11) NOT NULL,
  `table_name` varchar(64) NOT NULL,
  `record_id` varchar(50) NOT NULL,
  `field_name` varchar(64) NOT NULL,
  `old_value` text DEFAULT NULL,
  `new_value` text DEFAULT NULL,
  `changed_by` varchar(50) NOT NULL,
  `changed_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `company`
--

CREATE TABLE `company` (
  `id_company` int(11) NOT NULL,
  `rut` varchar(50) NOT NULL,
  `razon_social` varchar(150) NOT NULL,
  `address` varchar(255) NOT NULL,
  `email` varchar(50) NOT NULL,
  `state` int(11) NOT NULL,
  `created_by` varchar(50) NOT NULL,
  `date_create` datetime NOT NULL,
  `last_update` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `company`
--

INSERT INTO `company` (`id_company`, `rut`, `razon_social`, `address`, `email`, `state`, `created_by`, `date_create`, `last_update`) VALUES
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
  `description` varchar(255) NOT NULL,
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
  `type` enum('induccion','autoevaluacion','auditoria','otro') NOT NULL DEFAULT 'induccion',
  `description` varchar(255) NOT NULL,
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
-- Estructura de tabla para la tabla `dynamic_forms`
--

CREATE TABLE `dynamic_forms` (
  `id_form` int(11) NOT NULL,
  `id_company` int(11) DEFAULT NULL,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `state` int(11) NOT NULL DEFAULT 1,
  `created_by` varchar(50) NOT NULL,
  `date_create` datetime NOT NULL,
  `last_update` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `dynamic_form_fields`
--

CREATE TABLE `dynamic_form_fields` (
  `id_field` int(11) NOT NULL,
  `id_form` int(11) NOT NULL,
  `label` varchar(150) NOT NULL,
  `field_type` varchar(30) NOT NULL COMMENT 'text, number, date, select, checkbox, file...',
  `options` text DEFAULT NULL COMMENT 'opciones separadas por | si aplica',
  `is_required` tinyint(1) NOT NULL DEFAULT 0,
  `sort_order` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `event_types`
--

CREATE TABLE `event_types` (
  `id_event_type` int(11) NOT NULL,
  `module` varchar(30) NOT NULL DEFAULT 'seguridad',
  `name` varchar(100) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `state` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `event_types`
--

INSERT INTO `event_types` (`id_event_type`, `module`, `name`, `description`, `state`) VALUES
(1, 'seguridad', 'Accidente con tiempo perdido', 'Incidente que genera ausencia laboral', 1),
(2, 'seguridad', 'Accidente sin tiempo perdido', 'Incidente sin ausencia laboral', 1),
(3, 'seguridad', 'Casi accidente (near miss)', 'Evento que pudo derivar en accidente', 1),
(4, 'seguridad', 'Condición insegura', 'Hallazgo de condición de riesgo', 1),
(5, 'seguridad', 'Acto inseguro', 'Conducta de riesgo observada', 1),
(6, 'medio_ambiente', 'Derrame', 'Derrame de sustancia o residuo', 1),
(7, 'medio_ambiente', 'Incumplimiento normativo ambiental', 'Hallazgo de incumplimiento', 1),
(8, 'arqueologia', 'Hallazgo arqueológico', 'Hallazgo durante excavación/obra', 1),
(9, 'arqueologia', 'Paralización por hallazgo', 'Detención de obra por hallazgo', 1);

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
(1, 'juanantonioconchaloyola@gmail.com', '127.0.0.1', 1, '2026-09-03 14:55:28'),
(2, 'juanantonioconchaloyola@gmail.com', '127.0.0.1', 1, '2026-09-03 15:10:16'),
(3, 'juanantonioconchaloyola@gmail.com', '127.0.0.1', 1, '2026-09-03 18:40:47'),
(4, 'juanantonioconchaloyola@gmail.com', '127.0.0.1', 1, '2026-09-03 18:41:13'),
(5, 'juanantonioconchaloyola@gmail.com', '127.0.0.1', 1, '2026-09-03 19:09:38'),
(6, 'juanantonioconchaloyola@gmail.com', '127.0.0.1', 1, '2026-09-03 23:41:30'),
(7, 'juanantonioconchaloyola@gmail.com', '127.0.0.1', 1, '2026-09-03 23:55:54'),
(8, 'juanantonioconchaloyola@gmail.com', '127.0.0.1', 1, '2026-09-04 00:03:55');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `login_codes`
--

CREATE TABLE `login_codes` (
  `id_login_code` int(11) NOT NULL,
  `id_users` varchar(50) NOT NULL COMMENT 'FK a users.id_users (email)',
  `code_hash` varchar(255) NOT NULL COMMENT 'código de 6 dígitos, nunca en texto plano (password_hash)',
  `expires_at` datetime NOT NULL COMMENT 'vigencia: 10 minutos desde su creación',
  `used_at` datetime DEFAULT NULL COMMENT 'se marca al primer uso exitoso; evita reutilización',
  `attempts` int(11) NOT NULL DEFAULT 0 COMMENT 'intentos fallidos de verificación contra este código',
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `login_codes`
--

INSERT INTO `login_codes` (`id_login_code`, `id_users`, `code_hash`, `expires_at`, `used_at`, `attempts`, `ip_address`, `created_at`) VALUES
(1, 'juanantonioconchaloyola@gmail.com', '$2y$10$UR7RyTmeOmlbjXh1TPDMs.AwbJoVsu17Sw4d.8n3arGyL.GXik.oO', '2026-09-03 21:05:21', '2026-09-03 14:55:28', 0, '127.0.0.1', '2026-09-03 14:55:21'),
(2, 'juanantonioconchaloyola@gmail.com', '$2y$10$6D.aaTwiP7/tUBEWRDQqM.X3nRSemC.lsxgu3bJwFCYnk/Jcl9Nyq', '2026-09-03 21:20:12', '2026-09-03 15:10:16', 0, '127.0.0.1', '2026-09-03 15:10:12'),
(3, 'juanantonioconchaloyola@gmail.com', '$2y$10$71JyXWtLJecSmc8cYYgmeeRfUkqJ7z.WuhPZ2hzsubn7GQ6TTJlEW', '2026-09-04 00:50:42', '2026-09-03 18:40:47', 0, '127.0.0.1', '2026-09-03 18:40:42'),
(4, 'juanantonioconchaloyola@gmail.com', '$2y$10$yCPYQ6.XrofJdirJk1rDOeCwDVateYT8FG8DhBBUillfOedFUYC2q', '2026-09-04 00:51:09', '2026-09-03 18:41:13', 0, '127.0.0.1', '2026-09-03 18:41:09'),
(5, 'juanantonioconchaloyola@gmail.com', '$2y$10$ledihePF3KUozo33PLS8Fe/hT83UKyqEwdLPvTij9Tu7N0Ch/XISW', '2026-09-04 01:19:33', '2026-09-03 19:09:38', 0, '127.0.0.1', '2026-09-03 19:09:33'),
(6, 'juanantonioconchaloyola@gmail.com', '$2y$10$pfeMJ8aUbwh6goRwB1wdWeatsLc.Iuj20ng09Kk3BTYtHvTK5p9aW', '2026-09-04 05:51:24', '2026-09-03 23:41:30', 0, '127.0.0.1', '2026-09-03 23:41:24'),
(7, 'juanantonioconchaloyola@gmail.com', '$2y$10$ZzpcuBw2isGelv5O9IhsJuNfM/a2k9jgoP7EPbPuFoRxfGbttLEt2', '2026-09-04 06:05:49', '2026-09-03 23:55:54', 0, '127.0.0.1', '2026-09-03 23:55:49'),
(8, 'juanantonioconchaloyola@gmail.com', '$2y$10$NVqpkpRpqrSVlYVUAkr6/OBaMRldMA6KOjcnUq/5cB0/q/.UaW3nO', '2026-09-04 06:13:51', '2026-09-04 00:03:55', 0, '127.0.0.1', '2026-09-04 00:03:51');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `password_resets`
--

CREATE TABLE `password_resets` (
  `id_reset` int(11) NOT NULL,
  `id_users` varchar(50) NOT NULL,
  `token_hash` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `permissions`
--

CREATE TABLE `permissions` (
  `id_permission` int(11) NOT NULL,
  `code` varchar(80) NOT NULL COMMENT 'ej. ''workers.create'', ''events.view''',
  `description` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `programs`
--

CREATE TABLE `programs` (
  `id_program` int(11) NOT NULL,
  `id_company` int(11) NOT NULL,
  `id_project` int(11) DEFAULT NULL,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `state` int(11) NOT NULL DEFAULT 1,
  `created_by` varchar(50) NOT NULL,
  `date_create` datetime NOT NULL,
  `last_update` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `program_monthly_tracking`
--

CREATE TABLE `program_monthly_tracking` (
  `id_tracking` int(11) NOT NULL,
  `id_program` int(11) NOT NULL,
  `period_month` date NOT NULL COMMENT 'primer día del mes reportado',
  `progress_percentage` decimal(5,2) NOT NULL DEFAULT 0.00,
  `comments` text DEFAULT NULL,
  `created_by` varchar(50) NOT NULL,
  `date_create` datetime NOT NULL,
  `last_update` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `projects`
--

CREATE TABLE `projects` (
  `id_project` int(11) NOT NULL,
  `id_company` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `state` int(11) NOT NULL DEFAULT 1 COMMENT '1=activo, 0=inactivo',
  `created_by` varchar(50) NOT NULL,
  `date_create` datetime NOT NULL,
  `last_update` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `protocols`
--

CREATE TABLE `protocols` (
  `id_protocol` int(11) NOT NULL,
  `id_company` int(11) DEFAULT NULL COMMENT 'NULL = protocolo base reutilizable por todas las empresas',
  `code` varchar(50) NOT NULL COMMENT 'código del protocolo MINSAL',
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `parameters` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'parametrización flexible del protocolo' CHECK (json_valid(`parameters`)),
  `state` int(11) NOT NULL DEFAULT 1,
  `created_by` varchar(50) NOT NULL,
  `date_create` datetime NOT NULL,
  `last_update` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `questions`
--

CREATE TABLE `questions` (
  `id_questions` int(11) NOT NULL,
  `question` text NOT NULL,
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
-- Estructura de tabla para la tabla `role_permissions`
--

CREATE TABLE `role_permissions` (
  `id_role_group` int(11) NOT NULL,
  `id_permission` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `security_events`
--

CREATE TABLE `security_events` (
  `id_security_events` int(11) NOT NULL,
  `id_company` int(11) NOT NULL,
  `id_company_center` int(11) NOT NULL,
  `id_project` int(11) DEFAULT NULL,
  `module` varchar(30) NOT NULL DEFAULT 'seguridad',
  `id_worker` int(11) DEFAULT NULL,
  `id_worker_name` varchar(150) DEFAULT NULL COMMENT 'snapshot histórico del nombre del trabajador',
  `id_event` int(11) NOT NULL,
  `event_date` datetime NOT NULL,
  `description` text NOT NULL,
  `criticality` enum('baja','media','alta','critica') NOT NULL DEFAULT 'media',
  `state` int(11) NOT NULL,
  `created_by` varchar(50) NOT NULL,
  `date_create` datetime NOT NULL,
  `last_update` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `security_event_evidence`
--

CREATE TABLE `security_event_evidence` (
  `id_evidence` int(11) NOT NULL,
  `id_security_events` int(11) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `file_type` enum('imagen','documento') NOT NULL,
  `uploaded_by` varchar(50) NOT NULL,
  `date_create` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `security_event_tracking`
--

CREATE TABLE `security_event_tracking` (
  `id_security_event_tracking` int(11) NOT NULL,
  `id_security_events` int(11) NOT NULL,
  `tracking_description` text NOT NULL,
  `person_charge` varchar(50) NOT NULL,
  `commitment_date` datetime NOT NULL,
  `deadline` datetime NOT NULL,
  `created_by` varchar(50) NOT NULL,
  `date_create` datetime NOT NULL,
  `last_update` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `test_materials`
--

CREATE TABLE `test_materials` (
  `id_material` int(11) NOT NULL,
  `id_test` int(11) NOT NULL,
  `title` varchar(150) NOT NULL,
  `material_type` enum('documento','texto','video','otro') NOT NULL DEFAULT 'documento',
  `file_path` varchar(255) DEFAULT NULL,
  `content_text` text DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_by` varchar(50) NOT NULL,
  `date_create` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id_users` varchar(50) NOT NULL COMMENT 'e-mail',
  `id_company` int(11) NOT NULL,
  `id_worker` int(11) DEFAULT NULL,
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

INSERT INTO `users` (`id_users`, `id_company`, `id_worker`, `name`, `lastname`, `rut`, `password_hash`, `state`, `language`, `last_access`, `created_by`, `date_create`, `last_update`) VALUES
('fco.fredes.g@gmail.com', 2, NULL, 'francisco', 'fredes', '1234', '$2y$10$GV6jDq5BKJrImfLVKKpti.oxbYxdjR8dQ1MdmCP8pyyiOlH4Ozwt6', 1, 'ESP', '2026-08-15 11:58:40', 'phpmyadmin', '2026-08-15 11:58:40', '2026-08-15 11:58:40'),
('juanantonioconchaloyola@gmail.com', 1, NULL, 'antonio', 'helheim', '16725278-8', '$2y$10$PGRe/y2lW9AwLK2MLi.thOaRnGY3f31zrynzexuE8tBq2XTNR2sHW', 1, 'ESP', '2026-08-14 20:46:32', 'phpmyadmin', '2026-08-14 20:46:30', '2026-08-14 20:46:30'),
('malazga99@gmail.com', 1, NULL, 'maite', 'lazcano', '20153481-k', NULL, 1, 'ESP', '2026-08-14 21:41:57', 'phpmyadmin', '2026-08-14 21:41:57', '2026-08-14 21:41:57'),
('pablotroncoso@gmail.com', 1, NULL, 'pablo', 'troncoso', '123456789', '', 1, 'ESP', '2026-08-14 21:43:14', 'phpmyadmin', '2026-08-14 21:43:14', '2026-08-14 21:43:14');

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

--
-- Volcado de datos para la tabla `users_role`
--

INSERT INTO `users_role` (`id_users_role`, `id_users`, `id_role_group`, `state`, `create_by`, `date_create`, `last_update`) VALUES
(1, 'juanantonioconchaloyola@gmail.com', 2, 1, 'migracion', '2026-08-20 17:42:42', '2026-08-20 17:42:42'),
(2, 'malazga99@gmail.com', 2, 1, 'migracion', '2026-08-20 17:42:42', '2026-08-20 17:42:42'),
(3, 'pablotroncoso@gmail.com', 2, 1, 'migracion', '2026-08-20 17:42:42', '2026-08-20 17:42:42'),
(4, 'fco.fredes.g@gmail.com', 3, 1, 'migracion', '2026-08-20 17:42:42', '2026-08-20 17:42:42');

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

--
-- Volcado de datos para la tabla `users_role_group`
--

INSERT INTO `users_role_group` (`id_role_group`, `id_company`, `name`, `description`, `state`, `create_by`, `date_create`, `last_update`) VALUES
(1, 2, 'administrador', 'Acceso completo a la plataforma', 1, 'migracion', '2026-08-20 17:42:42', '2026-08-20 17:42:42'),
(2, 1, 'administrador', 'Acceso completo a la plataforma', 1, 'migracion', '2026-08-20 17:42:42', '2026-08-20 17:42:42'),
(3, 2, 'cliente', 'Representante de la empresa cliente', 1, 'migracion', '2026-08-20 17:42:42', '2026-08-20 17:42:42'),
(4, 1, 'cliente', 'Representante de la empresa cliente', 1, 'migracion', '2026-08-20 17:42:42', '2026-08-20 17:42:42'),
(5, 2, 'trabajador', 'Trabajador en terreno', 1, 'migracion', '2026-08-20 17:42:42', '2026-08-20 17:42:42'),
(6, 1, 'trabajador', 'Trabajador en terreno', 1, 'migracion', '2026-08-20 17:42:42', '2026-08-20 17:42:42'),
(8, 2, 'administrador_completo', 'Administrador global', 1, 'migracion_safetyco', '2026-09-03 18:45:55', '2026-09-03 18:45:55'),
(9, 1, 'administrador_completo', 'Administrador global', 1, 'migracion_safetyco', '2026-09-03 18:45:55', '2026-09-03 18:45:55'),
(11, 2, 'jefatura', 'Jefatura de empresa', 1, 'migracion_safetyco', '2026-09-03 18:45:55', '2026-09-03 18:45:55'),
(12, 1, 'jefatura', 'Jefatura de empresa', 1, 'migracion_safetyco', '2026-09-03 18:45:55', '2026-09-03 18:45:55');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users_test_answers`
--

CREATE TABLE `users_test_answers` (
  `id_users_test_answers` int(11) NOT NULL,
  `id_users` varchar(50) NOT NULL COMMENT 'FK a users.id_users (email)',
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
  `id_users` varchar(50) NOT NULL COMMENT 'FK a users.id_users (email)',
  `id_test` int(11) NOT NULL,
  `id_company` int(11) NOT NULL,
  `assignamente_date` datetime NOT NULL,
  `deadline` datetime NOT NULL,
  `state` int(11) NOT NULL,
  `created_by` varchar(50) NOT NULL,
  `date_create` datetime NOT NULL,
  `last_update` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `workers`
--

CREATE TABLE `workers` (
  `id_worker` int(11) NOT NULL,
  `id_company` int(11) NOT NULL,
  `rut` varchar(12) NOT NULL,
  `name` varchar(100) NOT NULL,
  `lastname` varchar(100) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `position` varchar(100) DEFAULT NULL COMMENT 'cargo',
  `photo_path` varchar(255) DEFAULT NULL,
  `state` int(11) NOT NULL DEFAULT 1 COMMENT '1=activo, 0=inactivo',
  `created_by` varchar(50) NOT NULL,
  `date_create` datetime NOT NULL,
  `last_update` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `worker_projects`
--

CREATE TABLE `worker_projects` (
  `id_worker` int(11) NOT NULL,
  `id_project` int(11) NOT NULL,
  `date_create` datetime NOT NULL
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
-- Indices de la tabla `certificates`
--
ALTER TABLE `certificates`
  ADD PRIMARY KEY (`id_certificate`),
  ADD UNIQUE KEY `uq_certificate_code` (`code`),
  ADD KEY `idx_certificates_assigned` (`id_user_test_assigned`);

--
-- Indices de la tabla `change_history`
--
ALTER TABLE `change_history`
  ADD PRIMARY KEY (`id_change`),
  ADD KEY `idx_changehistory_record` (`table_name`,`record_id`);

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
  ADD PRIMARY KEY (`id_company_center`),
  ADD KEY `fk_company_center_company` (`id_company`);

--
-- Indices de la tabla `company_test`
--
ALTER TABLE `company_test`
  ADD PRIMARY KEY (`id_test`),
  ADD KEY `fk_companytest_company` (`id_company`);

--
-- Indices de la tabla `company_test_rel_questions`
--
ALTER TABLE `company_test_rel_questions`
  ADD PRIMARY KEY (`id_rel`),
  ADD KEY `fk_reltest_test` (`id_test`),
  ADD KEY `fk_reltest_question` (`id_question`);

--
-- Indices de la tabla `dynamic_forms`
--
ALTER TABLE `dynamic_forms`
  ADD PRIMARY KEY (`id_form`),
  ADD KEY `fk_dynamicforms_company` (`id_company`);

--
-- Indices de la tabla `dynamic_form_fields`
--
ALTER TABLE `dynamic_form_fields`
  ADD PRIMARY KEY (`id_field`),
  ADD KEY `idx_formfields_form` (`id_form`);

--
-- Indices de la tabla `event_types`
--
ALTER TABLE `event_types`
  ADD PRIMARY KEY (`id_event_type`);

--
-- Indices de la tabla `log`
--
ALTER TABLE `log`
  ADD PRIMARY KEY (`id_log`),
  ADD KEY `fk_log_user` (`id_users`);

--
-- Indices de la tabla `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_identifier_time` (`identifier`,`created_at`),
  ADD KEY `idx_ip_time` (`ip_address`,`created_at`);

--
-- Indices de la tabla `login_codes`
--
ALTER TABLE `login_codes`
  ADD PRIMARY KEY (`id_login_code`),
  ADD KEY `idx_login_codes_user` (`id_users`),
  ADD KEY `idx_login_codes_ip_created` (`ip_address`,`created_at`);

--
-- Indices de la tabla `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id_reset`),
  ADD KEY `idx_password_resets_user` (`id_users`);

--
-- Indices de la tabla `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id_permission`),
  ADD UNIQUE KEY `uq_permission_code` (`code`);

--
-- Indices de la tabla `programs`
--
ALTER TABLE `programs`
  ADD PRIMARY KEY (`id_program`),
  ADD KEY `fk_programs_company` (`id_company`),
  ADD KEY `fk_programs_project` (`id_project`);

--
-- Indices de la tabla `program_monthly_tracking`
--
ALTER TABLE `program_monthly_tracking`
  ADD PRIMARY KEY (`id_tracking`),
  ADD UNIQUE KEY `uq_program_period` (`id_program`,`period_month`);

--
-- Indices de la tabla `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`id_project`),
  ADD KEY `idx_projects_company` (`id_company`);

--
-- Indices de la tabla `protocols`
--
ALTER TABLE `protocols`
  ADD PRIMARY KEY (`id_protocol`),
  ADD UNIQUE KEY `uq_protocol_code_company` (`code`,`id_company`),
  ADD KEY `fk_protocols_company` (`id_company`);

--
-- Indices de la tabla `questions`
--
ALTER TABLE `questions`
  ADD PRIMARY KEY (`id_questions`);

--
-- Indices de la tabla `questions_options`
--
ALTER TABLE `questions_options`
  ADD PRIMARY KEY (`id_questions_options`),
  ADD KEY `fk_questionsoptions_question` (`id_questions`);

--
-- Indices de la tabla `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD PRIMARY KEY (`id_role_group`,`id_permission`),
  ADD KEY `fk_roleperm_permission` (`id_permission`);

--
-- Indices de la tabla `security_events`
--
ALTER TABLE `security_events`
  ADD PRIMARY KEY (`id_security_events`),
  ADD KEY `fk_events_company` (`id_company`),
  ADD KEY `fk_events_center` (`id_company_center`),
  ADD KEY `fk_events_project` (`id_project`),
  ADD KEY `fk_events_worker` (`id_worker`),
  ADD KEY `fk_events_type` (`id_event`);

--
-- Indices de la tabla `security_event_evidence`
--
ALTER TABLE `security_event_evidence`
  ADD PRIMARY KEY (`id_evidence`),
  ADD KEY `idx_evidence_event` (`id_security_events`);

--
-- Indices de la tabla `security_event_tracking`
--
ALTER TABLE `security_event_tracking`
  ADD PRIMARY KEY (`id_security_event_tracking`),
  ADD KEY `fk_tracking_event` (`id_security_events`);

--
-- Indices de la tabla `test_materials`
--
ALTER TABLE `test_materials`
  ADD PRIMARY KEY (`id_material`),
  ADD KEY `idx_materials_test` (`id_test`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_users`),
  ADD KEY `fk_users_company` (`id_company`),
  ADD KEY `fk_users_worker` (`id_worker`);

--
-- Indices de la tabla `users_role`
--
ALTER TABLE `users_role`
  ADD PRIMARY KEY (`id_users_role`),
  ADD KEY `fk_usersrole_user` (`id_users`),
  ADD KEY `fk_usersrole_group` (`id_role_group`);

--
-- Indices de la tabla `users_role_group`
--
ALTER TABLE `users_role_group`
  ADD PRIMARY KEY (`id_role_group`),
  ADD KEY `fk_rolegroup_company` (`id_company`);

--
-- Indices de la tabla `users_test_answers`
--
ALTER TABLE `users_test_answers`
  ADD PRIMARY KEY (`id_users_test_answers`),
  ADD KEY `fk_testanswers_user` (`id_users`),
  ADD KEY `fk_testanswers_company` (`id_company`),
  ADD KEY `fk_testanswers_test` (`id_test`),
  ADD KEY `fk_testanswers_rel` (`id_rel`),
  ADD KEY `fk_testanswers_question` (`id_question`),
  ADD KEY `fk_testanswers_option` (`id_questions_options`);

--
-- Indices de la tabla `users_test_assigned`
--
ALTER TABLE `users_test_assigned`
  ADD PRIMARY KEY (`id_user_test_assigned`),
  ADD KEY `fk_testassigned_user` (`id_users`),
  ADD KEY `fk_testassigned_test` (`id_test`),
  ADD KEY `fk_testassigned_company` (`id_company`);

--
-- Indices de la tabla `workers`
--
ALTER TABLE `workers`
  ADD PRIMARY KEY (`id_worker`),
  ADD UNIQUE KEY `uq_worker_rut_company` (`rut`,`id_company`),
  ADD KEY `idx_workers_company` (`id_company`);

--
-- Indices de la tabla `worker_projects`
--
ALTER TABLE `worker_projects`
  ADD PRIMARY KEY (`id_worker`,`id_project`),
  ADD KEY `fk_workerprojects_project` (`id_project`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `audits`
--
ALTER TABLE `audits`
  MODIFY `id_audits` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `certificates`
--
ALTER TABLE `certificates`
  MODIFY `id_certificate` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `change_history`
--
ALTER TABLE `change_history`
  MODIFY `id_change` int(11) NOT NULL AUTO_INCREMENT;

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
-- AUTO_INCREMENT de la tabla `dynamic_forms`
--
ALTER TABLE `dynamic_forms`
  MODIFY `id_form` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `dynamic_form_fields`
--
ALTER TABLE `dynamic_form_fields`
  MODIFY `id_field` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `event_types`
--
ALTER TABLE `event_types`
  MODIFY `id_event_type` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `log`
--
ALTER TABLE `log`
  MODIFY `id_log` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `login_codes`
--
ALTER TABLE `login_codes`
  MODIFY `id_login_code` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id_reset` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id_permission` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `programs`
--
ALTER TABLE `programs`
  MODIFY `id_program` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `program_monthly_tracking`
--
ALTER TABLE `program_monthly_tracking`
  MODIFY `id_tracking` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `projects`
--
ALTER TABLE `projects`
  MODIFY `id_project` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `protocols`
--
ALTER TABLE `protocols`
  MODIFY `id_protocol` int(11) NOT NULL AUTO_INCREMENT;

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
-- AUTO_INCREMENT de la tabla `security_event_evidence`
--
ALTER TABLE `security_event_evidence`
  MODIFY `id_evidence` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `security_event_tracking`
--
ALTER TABLE `security_event_tracking`
  MODIFY `id_security_event_tracking` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `test_materials`
--
ALTER TABLE `test_materials`
  MODIFY `id_material` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `users_role`
--
ALTER TABLE `users_role`
  MODIFY `id_users_role` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `users_role_group`
--
ALTER TABLE `users_role_group`
  MODIFY `id_role_group` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

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

--
-- AUTO_INCREMENT de la tabla `workers`
--
ALTER TABLE `workers`
  MODIFY `id_worker` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `certificates`
--
ALTER TABLE `certificates`
  ADD CONSTRAINT `fk_certificates_assigned` FOREIGN KEY (`id_user_test_assigned`) REFERENCES `users_test_assigned` (`id_user_test_assigned`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Filtros para la tabla `company_center`
--
ALTER TABLE `company_center`
  ADD CONSTRAINT `fk_company_center_company` FOREIGN KEY (`id_company`) REFERENCES `company` (`id_company`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Filtros para la tabla `company_test`
--
ALTER TABLE `company_test`
  ADD CONSTRAINT `fk_companytest_company` FOREIGN KEY (`id_company`) REFERENCES `company` (`id_company`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Filtros para la tabla `company_test_rel_questions`
--
ALTER TABLE `company_test_rel_questions`
  ADD CONSTRAINT `fk_reltest_question` FOREIGN KEY (`id_question`) REFERENCES `questions` (`id_questions`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_reltest_test` FOREIGN KEY (`id_test`) REFERENCES `company_test` (`id_test`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `dynamic_forms`
--
ALTER TABLE `dynamic_forms`
  ADD CONSTRAINT `fk_dynamicforms_company` FOREIGN KEY (`id_company`) REFERENCES `company` (`id_company`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Filtros para la tabla `dynamic_form_fields`
--
ALTER TABLE `dynamic_form_fields`
  ADD CONSTRAINT `fk_formfields_form` FOREIGN KEY (`id_form`) REFERENCES `dynamic_forms` (`id_form`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `log`
--
ALTER TABLE `log`
  ADD CONSTRAINT `fk_log_user` FOREIGN KEY (`id_users`) REFERENCES `users` (`id_users`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Filtros para la tabla `login_codes`
--
ALTER TABLE `login_codes`
  ADD CONSTRAINT `fk_logincodes_user` FOREIGN KEY (`id_users`) REFERENCES `users` (`id_users`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `fk_passwordresets_user` FOREIGN KEY (`id_users`) REFERENCES `users` (`id_users`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `programs`
--
ALTER TABLE `programs`
  ADD CONSTRAINT `fk_programs_company` FOREIGN KEY (`id_company`) REFERENCES `company` (`id_company`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_programs_project` FOREIGN KEY (`id_project`) REFERENCES `projects` (`id_project`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Filtros para la tabla `program_monthly_tracking`
--
ALTER TABLE `program_monthly_tracking`
  ADD CONSTRAINT `fk_tracking_program` FOREIGN KEY (`id_program`) REFERENCES `programs` (`id_program`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `projects`
--
ALTER TABLE `projects`
  ADD CONSTRAINT `fk_projects_company` FOREIGN KEY (`id_company`) REFERENCES `company` (`id_company`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Filtros para la tabla `protocols`
--
ALTER TABLE `protocols`
  ADD CONSTRAINT `fk_protocols_company` FOREIGN KEY (`id_company`) REFERENCES `company` (`id_company`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Filtros para la tabla `questions_options`
--
ALTER TABLE `questions_options`
  ADD CONSTRAINT `fk_questionsoptions_question` FOREIGN KEY (`id_questions`) REFERENCES `questions` (`id_questions`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD CONSTRAINT `fk_roleperm_group` FOREIGN KEY (`id_role_group`) REFERENCES `users_role_group` (`id_role_group`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_roleperm_permission` FOREIGN KEY (`id_permission`) REFERENCES `permissions` (`id_permission`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `security_events`
--
ALTER TABLE `security_events`
  ADD CONSTRAINT `fk_events_center` FOREIGN KEY (`id_company_center`) REFERENCES `company_center` (`id_company_center`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_events_company` FOREIGN KEY (`id_company`) REFERENCES `company` (`id_company`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_events_project` FOREIGN KEY (`id_project`) REFERENCES `projects` (`id_project`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_events_type` FOREIGN KEY (`id_event`) REFERENCES `event_types` (`id_event_type`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_events_worker` FOREIGN KEY (`id_worker`) REFERENCES `workers` (`id_worker`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Filtros para la tabla `security_event_evidence`
--
ALTER TABLE `security_event_evidence`
  ADD CONSTRAINT `fk_evidence_event` FOREIGN KEY (`id_security_events`) REFERENCES `security_events` (`id_security_events`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `security_event_tracking`
--
ALTER TABLE `security_event_tracking`
  ADD CONSTRAINT `fk_tracking_event` FOREIGN KEY (`id_security_events`) REFERENCES `security_events` (`id_security_events`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `test_materials`
--
ALTER TABLE `test_materials`
  ADD CONSTRAINT `fk_testmaterials_test` FOREIGN KEY (`id_test`) REFERENCES `company_test` (`id_test`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_users_company` FOREIGN KEY (`id_company`) REFERENCES `company` (`id_company`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_users_worker` FOREIGN KEY (`id_worker`) REFERENCES `workers` (`id_worker`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Filtros para la tabla `users_role`
--
ALTER TABLE `users_role`
  ADD CONSTRAINT `fk_usersrole_group` FOREIGN KEY (`id_role_group`) REFERENCES `users_role_group` (`id_role_group`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_usersrole_user` FOREIGN KEY (`id_users`) REFERENCES `users` (`id_users`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `users_role_group`
--
ALTER TABLE `users_role_group`
  ADD CONSTRAINT `fk_rolegroup_company` FOREIGN KEY (`id_company`) REFERENCES `company` (`id_company`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Filtros para la tabla `users_test_answers`
--
ALTER TABLE `users_test_answers`
  ADD CONSTRAINT `fk_testanswers_company` FOREIGN KEY (`id_company`) REFERENCES `company` (`id_company`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_testanswers_option` FOREIGN KEY (`id_questions_options`) REFERENCES `questions_options` (`id_questions_options`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_testanswers_question` FOREIGN KEY (`id_question`) REFERENCES `questions` (`id_questions`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_testanswers_rel` FOREIGN KEY (`id_rel`) REFERENCES `company_test_rel_questions` (`id_rel`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_testanswers_test` FOREIGN KEY (`id_test`) REFERENCES `company_test` (`id_test`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_testanswers_user` FOREIGN KEY (`id_users`) REFERENCES `users` (`id_users`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Filtros para la tabla `users_test_assigned`
--
ALTER TABLE `users_test_assigned`
  ADD CONSTRAINT `fk_testassigned_company` FOREIGN KEY (`id_company`) REFERENCES `company` (`id_company`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_testassigned_test` FOREIGN KEY (`id_test`) REFERENCES `company_test` (`id_test`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_testassigned_user` FOREIGN KEY (`id_users`) REFERENCES `users` (`id_users`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Filtros para la tabla `workers`
--
ALTER TABLE `workers`
  ADD CONSTRAINT `fk_workers_company` FOREIGN KEY (`id_company`) REFERENCES `company` (`id_company`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Filtros para la tabla `worker_projects`
--
ALTER TABLE `worker_projects`
  ADD CONSTRAINT `fk_workerprojects_project` FOREIGN KEY (`id_project`) REFERENCES `projects` (`id_project`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_workerprojects_worker` FOREIGN KEY (`id_worker`) REFERENCES `workers` (`id_worker`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
