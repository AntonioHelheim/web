-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 08-09-2026 a las 12:33:54
-- Versión del servidor: 10.6.27-MariaDB-cll-lve
-- Versión de PHP: 8.4.24

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

--
-- Volcado de datos para la tabla `certificates`
--

INSERT INTO `certificates` (`id_certificate`, `id_user_test_assigned`, `code`, `file_path`, `issued_at`, `state`, `created_by`, `date_create`, `last_update`) VALUES
(1, 1, '936BE095D960', 'uploads/certificados/certificado_936BE095D960.pdf', '2026-08-14 16:00:00', 1, 'seed_test_data', '2026-08-14 16:00:00', '2026-08-14 16:00:00'),
(2, 4, '64F1EF36CA5B', 'uploads/certificados/certificado_64F1EF36CA5B.pdf', '2026-08-14 16:00:00', 1, 'seed_test_data', '2026-08-14 16:00:00', '2026-08-14 16:00:00'),
(3, 6, '77EF651285FD', 'uploads/certificados/certificado_77EF651285FD.pdf', '2026-08-16 16:00:00', 1, 'seed_test_data', '2026-08-16 16:00:00', '2026-08-16 16:00:00'),
(4, 7, '8E17769BA267', 'uploads/certificados/certificado_8E17769BA267.pdf', '2026-08-20 16:00:00', 1, 'seed_test_data', '2026-08-20 16:00:00', '2026-08-20 16:00:00'),
(5, 8, '601BC5B0714B', 'uploads/certificados/certificado_601BC5B0714B.pdf', '2026-08-19 16:00:00', 1, 'seed_test_data', '2026-08-19 16:00:00', '2026-08-19 16:00:00'),
(6, 13, '6FCB1DC3B0B3', 'uploads/certificados/certificado_6FCB1DC3B0B3.pdf', '2026-08-26 16:00:00', 1, 'seed_test_data', '2026-08-26 16:00:00', '2026-08-26 16:00:00'),
(7, 14, 'A5F8E897CA3D', 'uploads/certificados/certificado_A5F8E897CA3D.pdf', '2026-08-27 16:00:00', 1, 'seed_test_data', '2026-08-27 16:00:00', '2026-08-27 16:00:00');

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
(2, '1234', 'tecaivot', 'Santiago', 'contacto@tecaivot.cl', 1, 'phpmyadmin', '2026-08-15 12:00:58', '2026-08-15 12:00:58'),
(3, '123456789', 'engie', 'sistema prueba mvp \r\nSafetyControlTower v1.0', 'prueba@engie.cl', 1, 'phpmyadmin', '2026-09-08 01:05:35', '2026-09-08 01:05:35');

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

--
-- Volcado de datos para la tabla `company_center`
--

INSERT INTO `company_center` (`id_company_center`, `id_company`, `name`, `description`, `state`, `create_by`, `date_create`, `last_update`) VALUES
(1, 1, 'Oficina Central Helheim', 'Oficina administrativa y equipo de desarrollo del proyecto SCT.', 1, 'seed_test_data', '2026-08-04 09:00:00', '2026-08-04 09:00:00'),
(2, 2, 'Oficina Tecaivot Santiago', 'Oficina central de operaciones y administración de Tecaivot.', 1, 'seed_test_data', '2026-08-04 09:30:00', '2026-08-04 09:30:00'),
(3, 2, 'Bodega Tecaivot Renca', 'Centro de acopio, bodegaje y logística de insumos de Tecaivot.', 1, 'seed_test_data', '2026-08-04 09:30:00', '2026-08-04 09:30:00'),
(4, 3, 'Central Termoeléctrica Nueva Renca', 'Instalación de generación eléctrica de ENGIE ubicada en Renca, Región Metropolitana.', 1, 'seed_test_data', '2026-09-08 08:30:00', '2026-09-08 08:30:00'),
(5, 3, 'Parque Eólico Calama', 'Parque de generación eólica de ENGIE en la Región de Antofagasta.', 1, 'seed_test_data', '2026-09-08 08:30:00', '2026-09-08 08:30:00'),
(6, 3, 'Subestación Eléctrica Los Andes', 'Subestación de transmisión eléctrica de ENGIE en Los Andes, Región de Valparaíso.', 1, 'seed_test_data', '2026-09-08 08:30:00', '2026-09-08 08:30:00');

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

--
-- Volcado de datos para la tabla `company_test`
--

INSERT INTO `company_test` (`id_test`, `name`, `type`, `description`, `version`, `state`, `attempts_allowed`, `approval_percentage`, `effective_date_from`, `effective_date_until`, `created_by`, `date_created`, `last_update`, `id_company`) VALUES
(1, 'Inducción General Helheim', 'induccion', 'Curso de inducción general en seguridad para el personal de Helheim en la plataforma SCT.', 1, 1, 2, 70, '2026-08-01 00:00:00', '2026-12-31 23:59:59', 'seed_test_data', '2026-08-01 10:00:00', '2026-08-01 10:00:00', 1),
(2, 'Inducción General Tecaivot', 'induccion', 'Curso de inducción general en seguridad para el personal de Tecaivot.', 1, 1, 2, 70, '2026-08-01 00:00:00', '2026-12-31 23:59:59', 'seed_test_data', '2026-08-01 10:30:00', '2026-08-01 10:30:00', 2),
(3, 'Inducción de Seguridad - Contratistas ENGIE', 'induccion', 'Inducción obligatoria de seguridad y salud ocupacional para contratistas que ingresan a instalaciones de ENGIE.', 1, 1, 2, 80, '2026-08-01 00:00:00', '2026-12-31 23:59:59', 'seed_test_data', '2026-09-08 09:15:00', '2026-09-08 09:15:00', 3),
(4, 'Inducción Trabajo en Altura', 'induccion', 'Curso específico sobre procedimientos seguros para trabajo en altura en instalaciones de ENGIE.', 1, 1, 2, 75, '2026-08-01 00:00:00', '2026-12-31 23:59:59', 'seed_test_data', '2026-09-08 09:15:00', '2026-09-08 09:15:00', 3);

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

--
-- Volcado de datos para la tabla `company_test_rel_questions`
--

INSERT INTO `company_test_rel_questions` (`id_rel`, `id_test`, `id_question`, `assigned_score`, `created_by`, `date_create`, `last_update`) VALUES
(1, 1, 1, 10, 'seed_test_data', '2026-08-01 10:00:00', '2026-08-01 10:00:00'),
(2, 1, 2, 10, 'seed_test_data', '2026-08-01 10:00:00', '2026-08-01 10:00:00'),
(3, 1, 3, 10, 'seed_test_data', '2026-08-01 10:00:00', '2026-08-01 10:00:00'),
(4, 1, 4, 10, 'seed_test_data', '2026-08-01 10:00:00', '2026-08-01 10:00:00'),
(5, 1, 5, 10, 'seed_test_data', '2026-08-01 10:00:00', '2026-08-01 10:00:00'),
(6, 1, 6, 10, 'seed_test_data', '2026-08-01 10:00:00', '2026-08-01 10:00:00'),
(7, 2, 1, 10, 'seed_test_data', '2026-08-01 10:30:00', '2026-08-01 10:30:00'),
(8, 2, 2, 10, 'seed_test_data', '2026-08-01 10:30:00', '2026-08-01 10:30:00'),
(9, 2, 3, 10, 'seed_test_data', '2026-08-01 10:30:00', '2026-08-01 10:30:00'),
(10, 2, 4, 10, 'seed_test_data', '2026-08-01 10:30:00', '2026-08-01 10:30:00'),
(11, 2, 5, 10, 'seed_test_data', '2026-08-01 10:30:00', '2026-08-01 10:30:00'),
(12, 2, 6, 10, 'seed_test_data', '2026-08-01 10:30:00', '2026-08-01 10:30:00'),
(13, 3, 1, 10, 'seed_test_data', '2026-09-08 09:15:00', '2026-09-08 09:15:00'),
(14, 3, 2, 10, 'seed_test_data', '2026-09-08 09:15:00', '2026-09-08 09:15:00'),
(15, 3, 3, 10, 'seed_test_data', '2026-09-08 09:15:00', '2026-09-08 09:15:00'),
(16, 3, 4, 10, 'seed_test_data', '2026-09-08 09:15:00', '2026-09-08 09:15:00'),
(17, 3, 5, 10, 'seed_test_data', '2026-09-08 09:15:00', '2026-09-08 09:15:00'),
(18, 3, 6, 10, 'seed_test_data', '2026-09-08 09:15:00', '2026-09-08 09:15:00'),
(19, 3, 7, 10, 'seed_test_data', '2026-09-08 09:15:00', '2026-09-08 09:15:00'),
(20, 3, 8, 10, 'seed_test_data', '2026-09-08 09:15:00', '2026-09-08 09:15:00'),
(21, 3, 9, 10, 'seed_test_data', '2026-09-08 09:15:00', '2026-09-08 09:15:00'),
(22, 3, 10, 10, 'seed_test_data', '2026-09-08 09:15:00', '2026-09-08 09:15:00'),
(23, 4, 1, 10, 'seed_test_data', '2026-09-08 09:15:00', '2026-09-08 09:15:00'),
(24, 4, 2, 10, 'seed_test_data', '2026-09-08 09:15:00', '2026-09-08 09:15:00'),
(25, 4, 11, 10, 'seed_test_data', '2026-09-08 09:15:00', '2026-09-08 09:15:00'),
(26, 4, 12, 10, 'seed_test_data', '2026-09-08 09:15:00', '2026-09-08 09:15:00');

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
(8, 'juanantonioconchaloyola@gmail.com', '127.0.0.1', 1, '2026-09-04 00:03:55'),
(9, 'juanantonioconchaloyola@gmail.com', '179.4.50.96', 1, '2026-09-04 00:20:06'),
(10, 'juanantonioconchaloyola@gmail.com', '179.4.50.96', 1, '2026-09-04 00:33:27'),
(11, 'fco.fredes.g@gmail.com', '186.78.103.70', 1, '2026-09-06 15:05:44'),
(12, 'juanantonioconchaloyola@gmail.com', '179.4.50.96', 1, '2026-09-08 12:06:26'),
(13, 'francisco.fredes@engie.com', '98.98.28.182', 1, '2026-09-08 12:22:49');

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
(8, 'juanantonioconchaloyola@gmail.com', '$2y$10$NVqpkpRpqrSVlYVUAkr6/OBaMRldMA6KOjcnUq/5cB0/q/.UaW3nO', '2026-09-04 06:13:51', '2026-09-04 00:03:55', 0, '127.0.0.1', '2026-09-04 00:03:51'),
(9, 'juanantonioconchaloyola@gmail.com', '$2y$10$Fp4NgpS4vPH/u52GZp4ulO88R2zuB3eSk9eJfHMMd9ZswTK2dDDte', '2026-09-04 04:29:51', '2026-09-04 00:20:06', 0, '179.4.50.96', '2026-09-04 00:19:51'),
(10, 'juanantonioconchaloyola@gmail.com', '$2y$10$N18w4ehS4EXzXEf8u.YA0.//4/h6tYR/IUo2dddMSq3a20C20tmx6', '2026-09-04 04:43:06', '2026-09-04 00:33:27', 0, '179.4.50.96', '2026-09-04 00:33:06'),
(11, 'fco.fredes.g@gmail.com', '$2y$10$P.ZrtL86tkzuZsgDrUvoa.msJI/Qy80Q3UBE.dcqJZi2IdaFSOl3K', '2026-09-06 18:15:14', '2026-09-06 15:05:44', 0, '186.78.103.70', '2026-09-06 15:05:14'),
(12, 'juanantonioconchaloyola@gmail.com', '$2y$10$D3aTEK8HxF5C2lmchLzaV.c1QLG3xOSt3Gt9/9BSdXCWAlyzAWVzC', '2026-09-08 15:16:10', '2026-09-08 12:06:26', 0, '179.4.50.96', '2026-09-08 12:06:10'),
(13, 'Francisco.fredes@engie.com', '$2y$10$mlB2yhiYN5O5ewasQSF9pO/0SVt4ghySpJdskqf6siQHpqvEoAJ7C', '2026-09-08 15:32:19', '2026-09-08 12:22:49', 0, '98.98.28.182', '2026-09-08 12:22:19');

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

--
-- Volcado de datos para la tabla `projects`
--

INSERT INTO `projects` (`id_project`, `id_company`, `name`, `description`, `state`, `created_by`, `date_create`, `last_update`) VALUES
(1, 1, 'Implementación Interna SCT', 'Proyecto piloto para validar el uso interno de la plataforma en Helheim.', 1, 'seed_test_data', '2026-08-04 09:00:00', '2026-08-04 09:00:00'),
(2, 2, 'Auditoría Operacional Tecaivot 2026', 'Levantamiento y auditoría de procesos operacionales internos de Tecaivot.', 1, 'seed_test_data', '2026-08-04 09:30:00', '2026-08-04 09:30:00'),
(3, 2, 'Soporte Cliente Zona Norte', 'Servicios de soporte técnico y mantenimiento para clientes de la zona norte.', 1, 'seed_test_data', '2026-08-04 09:30:00', '2026-08-04 09:30:00'),
(4, 3, 'Mantenimiento Central Nueva Renca 2026', 'Plan de mantenimiento anual de la central termoeléctrica.', 1, 'seed_test_data', '2026-09-08 08:30:00', '2026-09-08 08:30:00'),
(5, 3, 'Construcción Parque Eólico Calama - Fase 1', 'Obras civiles y montaje de aerogeneradores, fase 1 del proyecto.', 1, 'seed_test_data', '2026-09-08 08:30:00', '2026-09-08 08:30:00'),
(6, 3, 'Ampliación Subestación Los Andes', 'Proyecto de ampliación de capacidad de la subestación eléctrica.', 1, 'seed_test_data', '2026-09-08 08:30:00', '2026-09-08 08:30:00');

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

--
-- Volcado de datos para la tabla `questions`
--

INSERT INTO `questions` (`id_questions`, `question`, `url_add_material`, `difficulty`, `points`, `state`, `add_expl_question`, `create_by`, `date_create`, `last_update`) VALUES
(1, '¿Qué debes hacer si detectas una condición insegura en tu lugar de trabajo?', '', 1, 10, 1, 'Reportar de inmediato permite corregir el riesgo antes de que derive en un accidente.', 'seed_test_data', '2026-08-01 09:00:00', '2026-08-01 09:00:00'),
(2, '¿Cuál es el elemento de protección personal (EPP) básico obligatorio en toda faena?', '', 1, 10, 1, 'El casco de seguridad es un EPP básico en la mayoría de las faenas industriales.', 'seed_test_data', '2026-08-01 09:00:00', '2026-08-01 09:00:00'),
(3, '¿Qué significa la sigla EPP?', '', 1, 10, 1, 'EPP corresponde a Elemento de Protección Personal.', 'seed_test_data', '2026-08-01 09:00:00', '2026-08-01 09:00:00'),
(4, 'En caso de un \'casi accidente\' (near miss), ¿qué corresponde hacer?', '', 2, 10, 1, 'Un casi accidente debe reportarse igual que un accidente, ya que evidencia un riesgo real.', 'seed_test_data', '2026-08-01 09:00:00', '2026-08-01 09:00:00'),
(5, '¿Qué acción corresponde a un \'acto inseguro\'?', '', 2, 10, 1, 'Operar maquinaria sin capacitación es un acto inseguro típico.', 'seed_test_data', '2026-08-01 09:00:00', '2026-08-01 09:00:00'),
(6, '¿Cuál es el objetivo principal de una inducción de seguridad?', '', 1, 10, 1, 'La inducción busca entregar los conocimientos mínimos para trabajar de forma segura.', 'seed_test_data', '2026-08-01 09:00:00', '2026-08-01 09:00:00'),
(7, '¿Qué debes hacer antes de operar maquinaria o equipos?', '', 2, 10, 1, 'Solo se debe operar equipos con la capacitación y autorización correspondiente.', 'seed_test_data', '2026-08-01 09:00:00', '2026-08-01 09:00:00'),
(8, '¿Qué es un protocolo MINSAL en el contexto de seguridad laboral?', '', 3, 10, 1, 'Los protocolos MINSAL son estándares técnicos del Ministerio de Salud.', 'seed_test_data', '2026-08-01 09:00:00', '2026-08-01 09:00:00'),
(9, '¿Qué se debe hacer al reportar un incidente en la plataforma SCT?', '', 2, 10, 1, 'Un reporte de calidad requiere descripción clara, tipo de evento y nivel de criticidad.', 'seed_test_data', '2026-08-01 09:00:00', '2026-08-01 09:00:00'),
(10, '¿Qué rol tiene la \'jefatura de empresa\' dentro de la plataforma SCT?', '', 2, 10, 1, 'La jefatura gestiona centros, proyectos, trabajadores y eventos de su propia empresa.', 'seed_test_data', '2026-08-01 09:00:00', '2026-08-01 09:00:00'),
(11, '¿A partir de qué altura se considera \'trabajo en altura\' según la normativa chilena general?', '', 2, 10, 1, 'La normativa chilena general considera trabajo en altura a partir de 1,8 metros.', 'seed_test_data', '2026-08-01 09:00:00', '2026-08-01 09:00:00'),
(12, '¿Qué elemento es obligatorio al realizar trabajo en altura?', '', 1, 10, 1, 'El arnés de seguridad certificado junto con línea de vida es obligatorio en trabajo en altura.', 'seed_test_data', '2026-08-01 09:00:00', '2026-08-01 09:00:00');

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

--
-- Volcado de datos para la tabla `questions_options`
--

INSERT INTO `questions_options` (`id_questions_options`, `id_questions`, `text_option`, `is_it_co`, `add_expl_opt`, `state`, `created_by`, `date_create`, `last_update`) VALUES
(1, 1, 'Reportarla de inmediato al supervisor/sistema', 1, 'Es la conducta esperada ante cualquier riesgo detectado.', 1, 'seed_test_data', '2026-08-01 09:00:00', '2026-08-01 09:00:00'),
(2, 1, 'Ignorarla si no te afecta directamente', 0, 'Puede afectar a otro trabajador más adelante.', 1, 'seed_test_data', '2026-08-01 09:00:00', '2026-08-01 09:00:00'),
(3, 1, 'Esperar a que otro trabajador la reporte', 0, 'La responsabilidad de reportar es de todos.', 1, 'seed_test_data', '2026-08-01 09:00:00', '2026-08-01 09:00:00'),
(4, 1, 'Solucionarla tú mismo sin avisar a nadie', 0, 'Puede requerir competencias o autorizaciones que no tienes.', 1, 'seed_test_data', '2026-08-01 09:00:00', '2026-08-01 09:00:00'),
(5, 2, 'Casco de seguridad', 1, 'Protege ante golpes y caída de objetos.', 1, 'seed_test_data', '2026-08-01 09:00:00', '2026-08-01 09:00:00'),
(6, 2, 'Corbata', 0, 'No es un elemento de protección y puede ser un riesgo de atrapamiento.', 1, 'seed_test_data', '2026-08-01 09:00:00', '2026-08-01 09:00:00'),
(7, 2, 'Reloj de pulsera', 0, 'No cumple función de protección.', 1, 'seed_test_data', '2026-08-01 09:00:00', '2026-08-01 09:00:00'),
(8, 2, 'Perfume', 0, 'No cumple función de protección.', 1, 'seed_test_data', '2026-08-01 09:00:00', '2026-08-01 09:00:00'),
(9, 3, 'Elemento de Protección Personal', 1, 'Corresponde a la definición estándar utilizada en prevención de riesgos.', 1, 'seed_test_data', '2026-08-01 09:00:00', '2026-08-01 09:00:00'),
(10, 3, 'Equipo de Prevención Preventiva', 0, 'No corresponde a la sigla utilizada.', 1, 'seed_test_data', '2026-08-01 09:00:00', '2026-08-01 09:00:00'),
(11, 3, 'Estándar de Protección Pública', 0, 'No corresponde a la sigla utilizada.', 1, 'seed_test_data', '2026-08-01 09:00:00', '2026-08-01 09:00:00'),
(12, 3, 'Evaluación de Peligros y Prevención', 0, 'No corresponde a la sigla utilizada.', 1, 'seed_test_data', '2026-08-01 09:00:00', '2026-08-01 09:00:00'),
(13, 4, 'No reportarlo porque no hubo consecuencias', 0, 'El objetivo es prevenir, no solo reaccionar.', 1, 'seed_test_data', '2026-08-01 09:00:00', '2026-08-01 09:00:00'),
(14, 4, 'Reportarlo igual, pudo haber derivado en accidente', 1, 'Permite tomar acciones preventivas antes de que ocurra un accidente real.', 1, 'seed_test_data', '2026-08-01 09:00:00', '2026-08-01 09:00:00'),
(15, 4, 'Reportarlo solo si hay lesionados', 0, 'El near miss se reporta precisamente porque no hubo lesionados.', 1, 'seed_test_data', '2026-08-01 09:00:00', '2026-08-01 09:00:00'),
(16, 4, 'Reportarlo únicamente al final del mes', 0, 'El reporte debe ser oportuno, no diferido.', 1, 'seed_test_data', '2026-08-01 09:00:00', '2026-08-01 09:00:00'),
(17, 5, 'Usar el EPP correctamente', 0, 'Es una conducta segura, no insegura.', 1, 'seed_test_data', '2026-08-01 09:00:00', '2026-08-01 09:00:00'),
(18, 5, 'Operar maquinaria sin la capacitación necesaria', 1, 'Expone al trabajador y a terceros a un riesgo evitable.', 1, 'seed_test_data', '2026-08-01 09:00:00', '2026-08-01 09:00:00'),
(19, 5, 'Señalizar un área de riesgo', 0, 'Es una conducta segura.', 1, 'seed_test_data', '2026-08-01 09:00:00', '2026-08-01 09:00:00'),
(20, 5, 'Detener una tarea insegura', 0, 'Es una conducta segura y recomendada.', 1, 'seed_test_data', '2026-08-01 09:00:00', '2026-08-01 09:00:00'),
(21, 6, 'Cumplir un trámite administrativo', 0, 'No es el objetivo principal, aunque también deja registro.', 1, 'seed_test_data', '2026-08-01 09:00:00', '2026-08-01 09:00:00'),
(22, 6, 'Dar los conocimientos mínimos para trabajar seguro', 1, 'Es el objetivo central de toda inducción de seguridad.', 1, 'seed_test_data', '2026-08-01 09:00:00', '2026-08-01 09:00:00'),
(23, 6, 'Aumentar el tiempo de contratación', 0, 'No es el objetivo de la inducción.', 1, 'seed_test_data', '2026-08-01 09:00:00', '2026-08-01 09:00:00'),
(24, 6, 'Reemplazar el uso de EPP', 0, 'La inducción complementa el uso de EPP, no lo reemplaza.', 1, 'seed_test_data', '2026-08-01 09:00:00', '2026-08-01 09:00:00'),
(25, 7, 'Verificar que tienes capacitación y autorización', 1, 'Es un requisito básico de seguridad operacional.', 1, 'seed_test_data', '2026-08-01 09:00:00', '2026-08-01 09:00:00'),
(26, 7, 'Operarla igual aunque no la conozcas', 0, 'Expone a un riesgo grave de accidente.', 1, 'seed_test_data', '2026-08-01 09:00:00', '2026-08-01 09:00:00'),
(27, 7, 'Pedir a otro que la opere sin autorización', 0, 'También constituye un acto inseguro.', 1, 'seed_test_data', '2026-08-01 09:00:00', '2026-08-01 09:00:00'),
(28, 7, 'Ninguna de las anteriores', 0, 'Existe una acción correcta entre las alternativas.', 1, 'seed_test_data', '2026-08-01 09:00:00', '2026-08-01 09:00:00'),
(29, 8, 'Norma técnica del Ministerio de Salud', 1, 'Corresponde a la definición oficial.', 1, 'seed_test_data', '2026-08-01 09:00:00', '2026-08-01 09:00:00'),
(30, 8, 'Un tipo de examen médico anual', 0, 'No corresponde a la definición.', 1, 'seed_test_data', '2026-08-01 09:00:00', '2026-08-01 09:00:00'),
(31, 8, 'Un formulario de vacaciones', 0, 'No corresponde a la definición.', 1, 'seed_test_data', '2026-08-01 09:00:00', '2026-08-01 09:00:00'),
(32, 8, 'Un procedimiento exclusivo para oficinas', 0, 'Los protocolos aplican también a faenas y terreno.', 1, 'seed_test_data', '2026-08-01 09:00:00', '2026-08-01 09:00:00'),
(33, 9, 'Describir el evento, su tipo y su criticidad', 1, 'Permite dar seguimiento y priorizar correctamente el evento.', 1, 'seed_test_data', '2026-08-01 09:00:00', '2026-08-01 09:00:00'),
(34, 9, 'Dejar todos los campos vacíos', 0, 'Impide el seguimiento adecuado del evento.', 1, 'seed_test_data', '2026-08-01 09:00:00', '2026-08-01 09:00:00'),
(35, 9, 'Reportarlo solo de palabra sin registro', 0, 'No queda trazabilidad del evento.', 1, 'seed_test_data', '2026-08-01 09:00:00', '2026-08-01 09:00:00'),
(36, 9, 'Esperar una semana para registrarlo', 0, 'El reporte debe ser oportuno.', 1, 'seed_test_data', '2026-08-01 09:00:00', '2026-08-01 09:00:00'),
(37, 10, 'Gestionar centros, proyectos y eventos propios', 1, 'Corresponde al nivel de acceso definido para este rol.', 1, 'seed_test_data', '2026-08-01 09:00:00', '2026-08-01 09:00:00'),
(38, 10, 'Ninguno, es un rol solo de lectura', 0, 'La jefatura tiene permisos de gestión, no solo de lectura.', 1, 'seed_test_data', '2026-08-01 09:00:00', '2026-08-01 09:00:00'),
(39, 10, 'Administrar todas las empresas del sistema', 0, 'Esa función corresponde al administrador global.', 1, 'seed_test_data', '2026-08-01 09:00:00', '2026-08-01 09:00:00'),
(40, 10, 'Ver únicamente su propio perfil', 0, 'Su alcance es mayor al de un trabajador.', 1, 'seed_test_data', '2026-08-01 09:00:00', '2026-08-01 09:00:00'),
(41, 11, '1,8 metros', 1, 'Es la referencia general utilizada en la normativa chilena de seguridad laboral.', 1, 'seed_test_data', '2026-08-01 09:00:00', '2026-08-01 09:00:00'),
(42, 11, '10 metros', 0, 'Es un valor muy superior al considerado por la normativa.', 1, 'seed_test_data', '2026-08-01 09:00:00', '2026-08-01 09:00:00'),
(43, 11, '50 centímetros', 0, 'Es un valor muy inferior al considerado por la normativa.', 1, 'seed_test_data', '2026-08-01 09:00:00', '2026-08-01 09:00:00'),
(44, 11, 'No existe una altura definida', 0, 'Sí existe un umbral de referencia.', 1, 'seed_test_data', '2026-08-01 09:00:00', '2026-08-01 09:00:00'),
(45, 12, 'Arnés de seguridad certificado y línea de vida', 1, 'Es el sistema de protección contra caídas exigido para este tipo de trabajo.', 1, 'seed_test_data', '2026-08-01 09:00:00', '2026-08-01 09:00:00'),
(46, 12, 'Guantes de cocina', 0, 'No corresponde a un EPP de trabajo en altura.', 1, 'seed_test_data', '2026-08-01 09:00:00', '2026-08-01 09:00:00'),
(47, 12, 'Paraguas', 0, 'No corresponde a un EPP de trabajo en altura.', 1, 'seed_test_data', '2026-08-01 09:00:00', '2026-08-01 09:00:00'),
(48, 12, 'Ninguno, no es necesario', 0, 'El uso de arnés es obligatorio por normativa.', 1, 'seed_test_data', '2026-08-01 09:00:00', '2026-08-01 09:00:00');

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

--
-- Volcado de datos para la tabla `security_events`
--

INSERT INTO `security_events` (`id_security_events`, `id_company`, `id_company_center`, `id_project`, `module`, `id_worker`, `id_worker_name`, `id_event`, `event_date`, `description`, `criticality`, `state`, `created_by`, `date_create`, `last_update`) VALUES
(1, 1, 1, 1, 'seguridad', NULL, NULL, 4, '2026-08-18 09:30:00', 'Cable de extensión eléctrica en mal estado detectado en sala de servidores de pruebas.', 'baja', 3, 'barbara.contreras@test.helheim.cl', '2026-08-18 09:30:00', '2026-08-18 09:30:00'),
(2, 1, 1, 1, 'seguridad', 2, 'Matías Rojas', 3, '2026-08-25 11:15:00', 'Trabajador estuvo a punto de resbalar en la escalera de acceso a la oficina por piso mojado sin señalización.', 'media', 2, 'jefatura.test@test.helheim.cl', '2026-08-25 11:15:00', '2026-08-25 11:15:00'),
(3, 2, 2, 2, 'seguridad', NULL, NULL, 4, '2026-08-12 10:00:00', 'Extintor vencido detectado en bodega de insumos de la oficina Tecaivot Santiago.', 'baja', 3, 'camila.soto@test.tecaivot.cl', '2026-08-12 10:00:00', '2026-08-12 10:00:00'),
(4, 2, 3, 3, 'seguridad', 4, 'Rodrigo Salinas', 5, '2026-08-22 15:40:00', 'Trabajador manipuló equipos eléctricos sin desenergizar previamente el circuito.', 'media', 2, 'jefatura.test@test.tecaivot.cl', '2026-08-22 15:40:00', '2026-08-22 15:40:00'),
(5, 2, 2, 2, 'seguridad', 3, 'Camila Soto', 2, '2026-09-01 08:50:00', 'Trabajadora sufrió corte superficial en la mano al manipular material cortante en bodega.', 'alta', 1, 'admin.test@test.tecaivot.cl', '2026-09-01 08:50:00', '2026-09-01 08:50:00'),
(6, 3, 4, 4, 'seguridad', 8, 'Nicolás Vargas', 1, '2026-08-14 12:10:00', 'Electricista sufrió quemadura de segundo grado en el brazo derecho durante labores de mantenimiento en tablero eléctrico, con licencia médica de 5 días.', 'critica', 2, 'cristobal.fuentes@test.engie.cl', '2026-08-14 12:10:00', '2026-08-14 12:10:00'),
(7, 3, 4, 4, 'seguridad', 15, 'Antonia Fernández', 3, '2026-08-10 09:00:00', 'Carga suspendida pasó cerca de un trabajador de bodega sin señalización de zona de izaje.', 'media', 3, 'francisca.torres@test.engie.cl', '2026-08-10 09:00:00', '2026-08-10 09:00:00'),
(8, 3, 4, 4, 'seguridad', NULL, NULL, 4, '2026-08-05 08:20:00', 'Baranda de protección perimetral con corrosión avanzada en plataforma de mantenimiento.', 'baja', 3, 'francisca.torres@test.engie.cl', '2026-08-05 08:20:00', '2026-08-05 08:20:00'),
(9, 3, 5, 5, 'seguridad', 7, 'Patricio Gómez', 1, '2026-09-02 14:05:00', 'Operador de grúa sufrió esguince de tobillo al descender de la cabina de la grúa torre.', 'alta', 1, 'javiera.reyes@test.engie.cl', '2026-09-02 14:05:00', '2026-09-02 14:05:00'),
(10, 3, 5, 5, 'seguridad', 10, 'Sebastián Morales', 5, '2026-08-28 10:30:00', 'Soldador realizó trabajos en caliente sin el retiro previo de material combustible cercano.', 'media', 2, 'javiera.reyes@test.engie.cl', '2026-08-28 10:30:00', '2026-08-28 10:30:00'),
(11, 3, 5, 5, 'seguridad', 11, 'Javiera Reyes', 3, '2026-08-30 16:45:00', 'Aerogenerador en izaje osciló de forma inesperada por una ráfaga de viento; el personal se retiró oportunamente del área de riesgo.', 'alta', 2, 'cristobal.fuentes@test.engie.cl', '2026-08-30 16:45:00', '2026-08-30 16:45:00'),
(12, 3, 6, 6, 'seguridad', 12, 'Felipe Castillo', 2, '2026-08-07 13:15:00', 'Operador sufrió un golpe menor en la mano al manipular una herramienta manual, atendido en el policlínico de faena sin licencia médica.', 'media', 3, 'admin.test@test.engie.cl', '2026-08-07 13:15:00', '2026-08-07 13:15:00'),
(13, 3, 6, 6, 'seguridad', NULL, NULL, 4, '2026-09-05 09:40:00', 'Señalización de riesgo eléctrico ilegible en el acceso a la sala de tableros de la subestación.', 'media', 1, 'cristobal.fuentes@test.engie.cl', '2026-09-05 09:40:00', '2026-09-05 09:40:00'),
(14, 3, 6, 6, 'seguridad', 13, 'Constanza Pizarro', 5, '2026-08-19 11:00:00', 'Técnico ingresó a un área restringida sin autorización ni acompañamiento correspondiente.', 'baja', 2, 'javiera.reyes@test.engie.cl', '2026-08-19 11:00:00', '2026-08-19 11:00:00');

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

--
-- Volcado de datos para la tabla `security_event_evidence`
--

INSERT INTO `security_event_evidence` (`id_evidence`, `id_security_events`, `file_path`, `original_name`, `file_type`, `uploaded_by`, `date_create`) VALUES
(1, 1, 'uploads/eventos/evento_1_d8c8abc23e5062c4.jpg', 'cable_extension_danado.jpg', 'imagen', 'barbara.contreras@test.helheim.cl', '2026-08-18 09:30:00'),
(2, 6, 'uploads/eventos/evento_6_5a2c1ca630ca35b9.jpg', 'tablero_electrico_quemadura.jpg', 'imagen', 'cristobal.fuentes@test.engie.cl', '2026-08-14 12:10:00'),
(3, 6, 'uploads/eventos/evento_6_30929cbb203c479f.pdf', 'informe_medico_preliminar.pdf', 'documento', 'cristobal.fuentes@test.engie.cl', '2026-08-14 12:10:00'),
(4, 7, 'uploads/eventos/evento_7_77806509a02e51ca.jpg', 'zona_izaje_bodega.jpg', 'imagen', 'francisca.torres@test.engie.cl', '2026-08-10 09:00:00'),
(5, 9, 'uploads/eventos/evento_9_4007a04f59d4e8ea.jpg', 'grua_torre_acceso.jpg', 'imagen', 'javiera.reyes@test.engie.cl', '2026-09-02 14:05:00'),
(6, 12, 'uploads/eventos/evento_12_2f7befe90309713c.jpg', 'herramienta_manual_dañada.jpg', 'imagen', 'admin.test@test.engie.cl', '2026-08-07 13:15:00');

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

--
-- Volcado de datos para la tabla `security_event_tracking`
--

INSERT INTO `security_event_tracking` (`id_security_event_tracking`, `id_security_events`, `tracking_description`, `person_charge`, `commitment_date`, `deadline`, `created_by`, `date_create`, `last_update`) VALUES
(1, 1, 'Se reemplazó el cable de extensión defectuoso por uno nuevo certificado.', 'Bárbara Contreras', '2026-08-18 00:00:00', '2026-08-19 23:59:59', 'barbara.contreras@test.helheim.cl', '2026-08-19 12:00:00', '2026-08-19 12:00:00'),
(2, 2, 'Se instalaron conos de seguridad y señalética de piso mojado en el acceso.', 'Fernanda Vidal', '2026-08-25 00:00:00', '2026-08-27 23:59:59', 'jefatura.test@test.helheim.cl', '2026-08-26 12:00:00', '2026-08-26 12:00:00'),
(3, 3, 'Se reemplazó el extintor vencido por uno nuevo y se actualizó el registro de mantenimiento.', 'Camila Soto', '2026-08-12 00:00:00', '2026-08-13 23:59:59', 'camila.soto@test.tecaivot.cl', '2026-08-13 12:00:00', '2026-08-13 12:00:00'),
(4, 4, 'Se reforzó la capacitación en bloqueo y etiquetado (LOTO) al trabajador involucrado.', 'Daniela Contreras', '2026-08-22 00:00:00', '2026-08-29 23:59:59', 'jefatura.test@test.tecaivot.cl', '2026-08-24 12:00:00', '2026-08-24 12:00:00'),
(5, 6, 'Trabajador derivado a centro asistencial; se inició investigación de causa raíz del incidente.', 'Cristóbal Fuentes', '2026-08-14 00:00:00', '2026-08-21 23:59:59', 'cristobal.fuentes@test.engie.cl', '2026-08-16 12:00:00', '2026-08-16 12:00:00'),
(6, 6, 'Investigación de causa raíz concluida; se reforzarán procedimientos de bloqueo eléctrico en tableros.', 'Cristóbal Fuentes', '2026-08-21 00:00:00', '2026-08-28 23:59:59', 'cristobal.fuentes@test.engie.cl', '2026-08-27 12:00:00', '2026-08-27 12:00:00'),
(7, 7, 'Se delimitó la zona de izaje con cinta de peligro y se reforzó la señalización visual.', 'Francisca Torres', '2026-08-10 00:00:00', '2026-08-11 23:59:59', 'francisca.torres@test.engie.cl', '2026-08-11 12:00:00', '2026-08-11 12:00:00'),
(8, 8, 'Se reemplazó el tramo de baranda con corrosión y se programó inspección trimestral.', 'Francisca Torres', '2026-08-05 00:00:00', '2026-08-08 23:59:59', 'francisca.torres@test.engie.cl', '2026-08-07 12:00:00', '2026-08-07 12:00:00'),
(9, 10, 'Se detuvo la actividad de soldadura y se retiró el material combustible del área.', 'Javiera Reyes', '2026-08-28 00:00:00', '2026-08-29 23:59:59', 'javiera.reyes@test.engie.cl', '2026-08-28 12:00:00', '2026-08-28 12:00:00'),
(10, 11, 'Se reforzó el protocolo de suspensión de izajes ante ráfagas de viento sobre el límite permitido.', 'Cristóbal Fuentes', '2026-08-30 00:00:00', '2026-09-02 23:59:59', 'cristobal.fuentes@test.engie.cl', '2026-08-31 12:00:00', '2026-08-31 12:00:00'),
(11, 12, 'Se realizó revisión de herramientas manuales del área y reemplazo de las dañadas.', 'Marcela Vega', '2026-08-07 00:00:00', '2026-08-09 23:59:59', 'admin.test@test.engie.cl', '2026-08-08 12:00:00', '2026-08-08 12:00:00'),
(12, 14, 'Se recordó al equipo el procedimiento de acceso a áreas restringidas y control de ingreso.', 'Javiera Reyes', '2026-08-19 00:00:00', '2026-08-21 23:59:59', 'javiera.reyes@test.engie.cl', '2026-08-20 12:00:00', '2026-08-20 12:00:00');

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

--
-- Volcado de datos para la tabla `test_materials`
--

INSERT INTO `test_materials` (`id_material`, `id_test`, `title`, `material_type`, `file_path`, `content_text`, `sort_order`, `created_by`, `date_create`) VALUES
(1, 1, 'Bienvenida al curso', 'texto', NULL, 'Bienvenido/a a la inducción general de seguridad de Helheim. Este curso entrega los conocimientos mínimos para desempeñar tu labor de forma segura dentro de la plataforma Safety Control Tower. Revisa el material y luego rinde la evaluación.', 1, 'seed_test_data', '2026-08-01 10:00:00'),
(2, 2, 'Bienvenida al curso', 'texto', NULL, 'Bienvenido/a a la inducción general de seguridad de Tecaivot. Este curso entrega los conocimientos mínimos para desempeñar tu labor de forma segura. Revisa el material y luego rinde la evaluación.', 1, 'seed_test_data', '2026-08-01 10:30:00'),
(3, 3, 'Bienvenida al curso', 'texto', NULL, 'Bienvenido/a a la inducción de seguridad para contratistas de ENGIE. Es obligatoria antes de ingresar a cualquier instalación. Revisa el material de apoyo y luego rinde la evaluación con un mínimo de 80% de aprobación.', 1, 'seed_test_data', '2026-09-08 09:15:00'),
(4, 3, 'Video de bienvenida - Cultura de Seguridad', 'video', 'https://example.com/placeholder/video-induccion-engie.mp4', NULL, 2, 'seed_test_data', '2026-09-08 09:15:00'),
(5, 4, 'Manual de Trabajo en Altura (documento de referencia)', 'documento', 'https://example.com/placeholder/manual-trabajo-en-altura.pdf', NULL, 1, 'seed_test_data', '2026-09-08 09:15:00');

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
('admin.completo.test@test.helheim.cl', 1, NULL, 'Diego', 'Herrera', '18102198-5', NULL, 1, 'es', '1970-01-01 00:00:00', 'seed_test_data', '2026-08-04 09:00:00', '2026-08-04 09:00:00'),
('admin.completo.test@test.tecaivot.cl', 2, NULL, 'Francisca', 'Muñoz', '18102347-3', NULL, 1, 'es', '1970-01-01 00:00:00', 'seed_test_data', '2026-08-04 09:30:00', '2026-08-04 09:30:00'),
('admin.test@test.engie.cl', 3, NULL, 'Marcela', 'Vega', '18102701-0', NULL, 1, 'es', '1970-01-01 00:00:00', 'seed_test_data', '2026-09-08 08:30:00', '2026-09-08 08:30:00'),
('admin.test@test.tecaivot.cl', 2, NULL, 'Andrés', 'Pizarro', '18102439-9', NULL, 1, 'es', '1970-01-01 00:00:00', 'seed_test_data', '2026-08-04 09:30:00', '2026-08-04 09:30:00'),
('barbara.contreras@test.helheim.cl', 1, 1, 'Bárbara', 'Contreras', '18100200-K', NULL, 1, 'es', '1970-01-01 00:00:00', 'seed_test_data', '2026-08-05 09:00:00', '2026-08-05 09:00:00'),
('camila.soto@test.tecaivot.cl', 2, 3, 'Camila', 'Soto', '18100308-1', NULL, 1, 'es', '1970-01-01 00:00:00', 'seed_test_data', '2026-08-05 09:30:00', '2026-08-05 09:30:00'),
('cliente.test@test.helheim.cl', 1, NULL, 'Ignacio', 'Bravo', '18102287-6', NULL, 1, 'es', '1970-01-01 00:00:00', 'seed_test_data', '2026-08-04 09:00:00', '2026-08-04 09:00:00'),
('cristobal.fuentes@test.engie.cl', 3, 6, 'Cristóbal', 'Fuentes', '18100740-0', NULL, 1, 'es', '1970-01-01 00:00:00', 'seed_test_data', '2026-09-08 09:00:00', '2026-09-08 09:00:00'),
('fco.fredes.g@gmail.com', 2, NULL, 'francisco', 'fredes', '1234', '$2y$10$GV6jDq5BKJrImfLVKKpti.oxbYxdjR8dQ1MdmCP8pyyiOlH4Ozwt6', 1, 'ESP', '2026-08-15 11:58:40', 'phpmyadmin', '2026-08-15 11:58:40', '2026-08-15 11:58:40'),
('francisca.torres@test.engie.cl', 3, 9, 'Francisca', 'Torres', '18101131-9', NULL, 1, 'es', '1970-01-01 00:00:00', 'seed_test_data', '2026-09-08 09:00:00', '2026-09-08 09:00:00'),
('Francisco.fredes@engie.com', 3, NULL, 'francisco', 'fredes', '18102892-0', NULL, 1, 'ESP', '2026-09-08 01:13:19', 'phpmyadmin', '2026-09-08 01:13:19', '2026-09-08 01:13:19'),
('javiera.reyes@test.engie.cl', 3, 11, 'Javiera', 'Reyes', '18101404-0', NULL, 1, 'es', '1970-01-01 00:00:00', 'seed_test_data', '2026-09-08 09:00:00', '2026-09-08 09:00:00'),
('jefatura.test@test.helheim.cl', 1, NULL, 'Fernanda', 'Vidal', '18102243-4', NULL, 1, 'es', '1970-01-01 00:00:00', 'seed_test_data', '2026-08-04 09:00:00', '2026-08-04 09:00:00'),
('jefatura.test@test.tecaivot.cl', 2, NULL, 'Daniela', 'Contreras', '18102535-2', NULL, 1, 'es', '1970-01-01 00:00:00', 'seed_test_data', '2026-08-04 09:30:00', '2026-08-04 09:30:00'),
('jonathan.vera@engie.com', 3, NULL, 'Jonathan', 'Vera', '18102935-8', NULL, 1, 'ESP', '2026-09-08 01:11:21', 'phpmyadmin', '2026-09-08 01:11:21', '2026-09-08 01:11:21'),
('juanantonioconchaloyola@gmail.com', 1, NULL, 'antonio', 'helheim', '16725278-8', '$2y$10$PGRe/y2lW9AwLK2MLi.thOaRnGY3f31zrynzexuE8tBq2XTNR2sHW', 1, 'ESP', '2026-08-14 20:46:32', 'phpmyadmin', '2026-08-14 20:46:30', '2026-08-14 20:46:30'),
('Laura.Lira@external.engie.com', 3, NULL, 'Laura', 'Lira', '18103202-2', NULL, 1, 'ESP', '2026-09-08 01:12:37', 'phpmyadmin', '2026-09-08 01:12:37', '2026-09-08 01:12:37'),
('malazga99@gmail.com', 1, NULL, 'maite', 'lazcano', '20153481-k', NULL, 1, 'ESP', '2026-08-14 21:41:57', 'phpmyadmin', '2026-08-14 21:41:57', '2026-08-14 21:41:57'),
('pablotroncoso@gmail.com', 1, NULL, 'pablo', 'troncoso', '123456789', '', 1, 'ESP', '2026-08-14 21:43:14', 'phpmyadmin', '2026-08-14 21:43:14', '2026-08-14 21:43:14'),
('patricio.gomez@test.engie.cl', 3, 7, 'Patricio', 'Gómez', '18100834-2', NULL, 1, 'es', '1970-01-01 00:00:00', 'seed_test_data', '2026-09-08 09:00:00', '2026-09-08 09:00:00'),
('wagner.leite@engie.com', 3, NULL, 'Wagner', 'Leite', '18103115-8', NULL, 1, 'ESP', '2026-09-08 01:10:12', 'phpmyadmin', '2026-09-08 01:10:12', '2026-09-08 01:10:12');

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
(1, 'juanantonioconchaloyola@gmail.com', 9, 1, 'migracion', '2026-08-20 17:42:42', '2026-09-08 03:15:17'),
(2, 'malazga99@gmail.com', 9, 1, 'migracion', '2026-08-20 17:42:42', '2026-09-08 03:15:17'),
(3, 'pablotroncoso@gmail.com', 9, 1, 'migracion', '2026-08-20 17:42:42', '2026-09-08 03:15:17'),
(4, 'fco.fredes.g@gmail.com', 3, 1, 'migracion', '2026-08-20 17:42:42', '2026-08-20 17:42:42'),
(8, 'barbara.contreras@test.helheim.cl', 6, 1, 'seed_test_data', '2026-08-05 09:00:00', '2026-08-05 09:00:00'),
(9, 'camila.soto@test.tecaivot.cl', 5, 1, 'seed_test_data', '2026-08-05 09:30:00', '2026-08-05 09:30:00'),
(10, 'cristobal.fuentes@test.engie.cl', 17, 1, 'seed_test_data', '2026-09-08 09:00:00', '2026-09-08 09:00:00'),
(11, 'patricio.gomez@test.engie.cl', 18, 1, 'seed_test_data', '2026-09-08 09:00:00', '2026-09-08 09:00:00'),
(12, 'francisca.torres@test.engie.cl', 18, 1, 'seed_test_data', '2026-09-08 09:00:00', '2026-09-08 09:00:00'),
(13, 'javiera.reyes@test.engie.cl', 17, 1, 'seed_test_data', '2026-09-08 09:00:00', '2026-09-08 09:00:00'),
(14, 'admin.completo.test@test.helheim.cl', 9, 1, 'seed_test_data', '2026-08-04 09:00:00', '2026-08-04 09:00:00'),
(15, 'jefatura.test@test.helheim.cl', 12, 1, 'seed_test_data', '2026-08-04 09:00:00', '2026-08-04 09:00:00'),
(16, 'cliente.test@test.helheim.cl', 4, 1, 'seed_test_data', '2026-08-04 09:00:00', '2026-08-04 09:00:00'),
(17, 'admin.completo.test@test.tecaivot.cl', 8, 1, 'seed_test_data', '2026-08-04 09:30:00', '2026-08-04 09:30:00'),
(18, 'admin.test@test.tecaivot.cl', 1, 1, 'seed_test_data', '2026-08-04 09:30:00', '2026-08-04 09:30:00'),
(19, 'jefatura.test@test.tecaivot.cl', 11, 1, 'seed_test_data', '2026-08-04 09:30:00', '2026-08-04 09:30:00'),
(20, 'admin.test@test.engie.cl', 14, 1, 'seed_test_data', '2026-09-08 08:30:00', '2026-09-08 08:30:00'),
(21, 'Francisco.fredes@engie.com', 17, 1, 'seed_test_data', '2026-09-08 08:00:00', '2026-09-08 08:00:00'),
(22, 'jonathan.vera@engie.com', 16, 1, 'seed_test_data', '2026-09-08 08:00:00', '2026-09-08 08:00:00'),
(23, 'wagner.leite@engie.com', 14, 1, 'seed_test_data', '2026-09-08 08:00:00', '2026-09-08 08:00:00'),
(24, 'Laura.Lira@external.engie.com', 18, 1, 'seed_test_data', '2026-09-08 08:00:00', '2026-09-08 08:00:00');

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
(12, 1, 'jefatura', 'Jefatura de empresa', 1, 'migracion_safetyco', '2026-09-03 18:45:55', '2026-09-03 18:45:55'),
(13, 3, 'Representante de la empresa cliente', 'perfil de prueba mvp', 1, 'phpmyadmin', '2026-09-08 01:06:24', '2026-09-08 01:06:24'),
(14, 3, 'administrador', 'Acceso completo a la plataforma', 1, 'seed_test_data', '2026-09-08 08:00:00', '2026-09-08 08:00:00'),
(15, 3, 'administrador_completo', 'Administrador global', 1, 'seed_test_data', '2026-09-08 08:00:00', '2026-09-08 08:00:00'),
(16, 3, 'cliente', 'Representante de la empresa cliente', 1, 'seed_test_data', '2026-09-08 08:00:00', '2026-09-08 08:00:00'),
(17, 3, 'jefatura', 'Jefatura de empresa', 1, 'seed_test_data', '2026-09-08 08:00:00', '2026-09-08 08:00:00'),
(18, 3, 'trabajador', 'Trabajador en terreno', 1, 'seed_test_data', '2026-09-08 08:00:00', '2026-09-08 08:00:00');

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

--
-- Volcado de datos para la tabla `users_test_answers`
--

INSERT INTO `users_test_answers` (`id_users_test_answers`, `id_users`, `id_company`, `id_test`, `id_test_try`, `id_rel`, `id_question`, `id_questions_options`, `date_create`, `last_update`) VALUES
(1, 'barbara.contreras@test.helheim.cl', 1, 1, 1, 1, 1, 1, '2026-08-11 10:00:00', '2026-08-11 10:00:00'),
(2, 'barbara.contreras@test.helheim.cl', 1, 1, 1, 2, 2, 5, '2026-08-11 10:00:00', '2026-08-11 10:00:00'),
(3, 'barbara.contreras@test.helheim.cl', 1, 1, 1, 3, 3, 9, '2026-08-11 10:00:00', '2026-08-11 10:00:00'),
(4, 'barbara.contreras@test.helheim.cl', 1, 1, 1, 4, 4, 14, '2026-08-11 10:00:00', '2026-08-11 10:00:00'),
(5, 'barbara.contreras@test.helheim.cl', 1, 1, 1, 5, 5, 18, '2026-08-11 10:00:00', '2026-08-11 10:00:00'),
(6, 'barbara.contreras@test.helheim.cl', 1, 1, 1, 6, 6, 22, '2026-08-11 10:00:00', '2026-08-11 10:00:00'),
(7, 'cliente.test@test.helheim.cl', 1, 1, 1, 1, 1, 1, '2026-08-11 10:00:00', '2026-08-11 10:00:00'),
(8, 'cliente.test@test.helheim.cl', 1, 1, 1, 2, 2, 5, '2026-08-11 10:00:00', '2026-08-11 10:00:00'),
(9, 'cliente.test@test.helheim.cl', 1, 1, 1, 3, 3, 9, '2026-08-11 10:00:00', '2026-08-11 10:00:00'),
(10, 'cliente.test@test.helheim.cl', 1, 1, 1, 4, 4, 13, '2026-08-11 10:00:00', '2026-08-11 10:00:00'),
(11, 'cliente.test@test.helheim.cl', 1, 1, 1, 5, 5, 17, '2026-08-11 10:00:00', '2026-08-11 10:00:00'),
(12, 'cliente.test@test.helheim.cl', 1, 1, 1, 6, 6, 21, '2026-08-11 10:00:00', '2026-08-11 10:00:00'),
(13, 'cliente.test@test.helheim.cl', 1, 1, 2, 1, 1, 1, '2026-08-12 10:00:00', '2026-08-12 10:00:00'),
(14, 'cliente.test@test.helheim.cl', 1, 1, 2, 2, 2, 5, '2026-08-12 10:00:00', '2026-08-12 10:00:00'),
(15, 'cliente.test@test.helheim.cl', 1, 1, 2, 3, 3, 10, '2026-08-12 10:00:00', '2026-08-12 10:00:00'),
(16, 'cliente.test@test.helheim.cl', 1, 1, 2, 4, 4, 13, '2026-08-12 10:00:00', '2026-08-12 10:00:00'),
(17, 'cliente.test@test.helheim.cl', 1, 1, 2, 5, 5, 17, '2026-08-12 10:00:00', '2026-08-12 10:00:00'),
(18, 'cliente.test@test.helheim.cl', 1, 1, 2, 6, 6, 21, '2026-08-12 10:00:00', '2026-08-12 10:00:00'),
(19, 'camila.soto@test.tecaivot.cl', 2, 2, 1, 7, 1, 1, '2026-08-12 10:00:00', '2026-08-12 10:00:00'),
(20, 'camila.soto@test.tecaivot.cl', 2, 2, 1, 8, 2, 5, '2026-08-12 10:00:00', '2026-08-12 10:00:00'),
(21, 'camila.soto@test.tecaivot.cl', 2, 2, 1, 9, 3, 9, '2026-08-12 10:00:00', '2026-08-12 10:00:00'),
(22, 'camila.soto@test.tecaivot.cl', 2, 2, 1, 10, 4, 14, '2026-08-12 10:00:00', '2026-08-12 10:00:00'),
(23, 'camila.soto@test.tecaivot.cl', 2, 2, 1, 11, 5, 18, '2026-08-12 10:00:00', '2026-08-12 10:00:00'),
(24, 'camila.soto@test.tecaivot.cl', 2, 2, 1, 12, 6, 22, '2026-08-12 10:00:00', '2026-08-12 10:00:00'),
(25, 'admin.test@test.tecaivot.cl', 2, 2, 1, 7, 1, 1, '2026-08-12 10:00:00', '2026-08-12 10:00:00'),
(26, 'admin.test@test.tecaivot.cl', 2, 2, 1, 8, 2, 5, '2026-08-12 10:00:00', '2026-08-12 10:00:00'),
(27, 'admin.test@test.tecaivot.cl', 2, 2, 1, 9, 3, 9, '2026-08-12 10:00:00', '2026-08-12 10:00:00'),
(28, 'admin.test@test.tecaivot.cl', 2, 2, 1, 10, 4, 14, '2026-08-12 10:00:00', '2026-08-12 10:00:00'),
(29, 'admin.test@test.tecaivot.cl', 2, 2, 1, 11, 5, 18, '2026-08-12 10:00:00', '2026-08-12 10:00:00'),
(30, 'admin.test@test.tecaivot.cl', 2, 2, 1, 12, 6, 21, '2026-08-12 10:00:00', '2026-08-12 10:00:00'),
(31, 'patricio.gomez@test.engie.cl', 3, 3, 1, 13, 1, 1, '2026-08-16 10:00:00', '2026-08-16 10:00:00'),
(32, 'patricio.gomez@test.engie.cl', 3, 3, 1, 14, 2, 5, '2026-08-16 10:00:00', '2026-08-16 10:00:00'),
(33, 'patricio.gomez@test.engie.cl', 3, 3, 1, 15, 3, 9, '2026-08-16 10:00:00', '2026-08-16 10:00:00'),
(34, 'patricio.gomez@test.engie.cl', 3, 3, 1, 16, 4, 14, '2026-08-16 10:00:00', '2026-08-16 10:00:00'),
(35, 'patricio.gomez@test.engie.cl', 3, 3, 1, 17, 5, 18, '2026-08-16 10:00:00', '2026-08-16 10:00:00'),
(36, 'patricio.gomez@test.engie.cl', 3, 3, 1, 18, 6, 22, '2026-08-16 10:00:00', '2026-08-16 10:00:00'),
(37, 'patricio.gomez@test.engie.cl', 3, 3, 1, 19, 7, 25, '2026-08-16 10:00:00', '2026-08-16 10:00:00'),
(38, 'patricio.gomez@test.engie.cl', 3, 3, 1, 20, 8, 29, '2026-08-16 10:00:00', '2026-08-16 10:00:00'),
(39, 'patricio.gomez@test.engie.cl', 3, 3, 1, 21, 9, 33, '2026-08-16 10:00:00', '2026-08-16 10:00:00'),
(40, 'patricio.gomez@test.engie.cl', 3, 3, 1, 22, 10, 38, '2026-08-16 10:00:00', '2026-08-16 10:00:00'),
(41, 'francisca.torres@test.engie.cl', 3, 3, 1, 13, 1, 1, '2026-08-16 10:00:00', '2026-08-16 10:00:00'),
(42, 'francisca.torres@test.engie.cl', 3, 3, 1, 14, 2, 5, '2026-08-16 10:00:00', '2026-08-16 10:00:00'),
(43, 'francisca.torres@test.engie.cl', 3, 3, 1, 15, 3, 9, '2026-08-16 10:00:00', '2026-08-16 10:00:00'),
(44, 'francisca.torres@test.engie.cl', 3, 3, 1, 16, 4, 14, '2026-08-16 10:00:00', '2026-08-16 10:00:00'),
(45, 'francisca.torres@test.engie.cl', 3, 3, 1, 17, 5, 18, '2026-08-16 10:00:00', '2026-08-16 10:00:00'),
(46, 'francisca.torres@test.engie.cl', 3, 3, 1, 18, 6, 22, '2026-08-16 10:00:00', '2026-08-16 10:00:00'),
(47, 'francisca.torres@test.engie.cl', 3, 3, 1, 19, 7, 25, '2026-08-16 10:00:00', '2026-08-16 10:00:00'),
(48, 'francisca.torres@test.engie.cl', 3, 3, 1, 20, 8, 29, '2026-08-16 10:00:00', '2026-08-16 10:00:00'),
(49, 'francisca.torres@test.engie.cl', 3, 3, 1, 21, 9, 33, '2026-08-16 10:00:00', '2026-08-16 10:00:00'),
(50, 'francisca.torres@test.engie.cl', 3, 3, 1, 22, 10, 37, '2026-08-16 10:00:00', '2026-08-16 10:00:00'),
(51, 'javiera.reyes@test.engie.cl', 3, 3, 1, 13, 1, 1, '2026-08-16 10:00:00', '2026-08-16 10:00:00'),
(52, 'javiera.reyes@test.engie.cl', 3, 3, 1, 14, 2, 5, '2026-08-16 10:00:00', '2026-08-16 10:00:00'),
(53, 'javiera.reyes@test.engie.cl', 3, 3, 1, 15, 3, 9, '2026-08-16 10:00:00', '2026-08-16 10:00:00'),
(54, 'javiera.reyes@test.engie.cl', 3, 3, 1, 16, 4, 14, '2026-08-16 10:00:00', '2026-08-16 10:00:00'),
(55, 'javiera.reyes@test.engie.cl', 3, 3, 1, 17, 5, 18, '2026-08-16 10:00:00', '2026-08-16 10:00:00'),
(56, 'javiera.reyes@test.engie.cl', 3, 3, 1, 18, 6, 22, '2026-08-16 10:00:00', '2026-08-16 10:00:00'),
(57, 'javiera.reyes@test.engie.cl', 3, 3, 1, 19, 7, 26, '2026-08-16 10:00:00', '2026-08-16 10:00:00'),
(58, 'javiera.reyes@test.engie.cl', 3, 3, 1, 20, 8, 30, '2026-08-16 10:00:00', '2026-08-16 10:00:00'),
(59, 'javiera.reyes@test.engie.cl', 3, 3, 1, 21, 9, 34, '2026-08-16 10:00:00', '2026-08-16 10:00:00'),
(60, 'javiera.reyes@test.engie.cl', 3, 3, 1, 22, 10, 38, '2026-08-16 10:00:00', '2026-08-16 10:00:00'),
(61, 'javiera.reyes@test.engie.cl', 3, 3, 2, 13, 1, 1, '2026-08-17 10:00:00', '2026-08-17 10:00:00'),
(62, 'javiera.reyes@test.engie.cl', 3, 3, 2, 14, 2, 5, '2026-08-17 10:00:00', '2026-08-17 10:00:00'),
(63, 'javiera.reyes@test.engie.cl', 3, 3, 2, 15, 3, 9, '2026-08-17 10:00:00', '2026-08-17 10:00:00'),
(64, 'javiera.reyes@test.engie.cl', 3, 3, 2, 16, 4, 14, '2026-08-17 10:00:00', '2026-08-17 10:00:00'),
(65, 'javiera.reyes@test.engie.cl', 3, 3, 2, 17, 5, 18, '2026-08-17 10:00:00', '2026-08-17 10:00:00'),
(66, 'javiera.reyes@test.engie.cl', 3, 3, 2, 18, 6, 22, '2026-08-17 10:00:00', '2026-08-17 10:00:00'),
(67, 'javiera.reyes@test.engie.cl', 3, 3, 2, 19, 7, 25, '2026-08-17 10:00:00', '2026-08-17 10:00:00'),
(68, 'javiera.reyes@test.engie.cl', 3, 3, 2, 20, 8, 30, '2026-08-17 10:00:00', '2026-08-17 10:00:00'),
(69, 'javiera.reyes@test.engie.cl', 3, 3, 2, 21, 9, 34, '2026-08-17 10:00:00', '2026-08-17 10:00:00'),
(70, 'javiera.reyes@test.engie.cl', 3, 3, 2, 22, 10, 38, '2026-08-17 10:00:00', '2026-08-17 10:00:00'),
(71, 'francisca.torres@test.engie.cl', 3, 4, 1, 23, 1, 1, '2026-08-21 10:00:00', '2026-08-21 10:00:00'),
(72, 'francisca.torres@test.engie.cl', 3, 4, 1, 24, 2, 5, '2026-08-21 10:00:00', '2026-08-21 10:00:00'),
(73, 'francisca.torres@test.engie.cl', 3, 4, 1, 25, 11, 41, '2026-08-21 10:00:00', '2026-08-21 10:00:00'),
(74, 'francisca.torres@test.engie.cl', 3, 4, 1, 26, 12, 45, '2026-08-21 10:00:00', '2026-08-21 10:00:00'),
(75, 'javiera.reyes@test.engie.cl', 3, 4, 1, 23, 1, 1, '2026-08-21 10:00:00', '2026-08-21 10:00:00'),
(76, 'javiera.reyes@test.engie.cl', 3, 4, 1, 24, 2, 5, '2026-08-21 10:00:00', '2026-08-21 10:00:00'),
(77, 'javiera.reyes@test.engie.cl', 3, 4, 1, 25, 11, 41, '2026-08-21 10:00:00', '2026-08-21 10:00:00'),
(78, 'javiera.reyes@test.engie.cl', 3, 4, 1, 26, 12, 46, '2026-08-21 10:00:00', '2026-08-21 10:00:00');

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

--
-- Volcado de datos para la tabla `users_test_assigned`
--

INSERT INTO `users_test_assigned` (`id_user_test_assigned`, `id_users`, `id_test`, `id_company`, `assignamente_date`, `deadline`, `state`, `created_by`, `date_create`, `last_update`) VALUES
(1, 'barbara.contreras@test.helheim.cl', 1, 1, '2026-08-10 09:00:00', '2026-08-24 23:59:59', 2, 'seed_test_data', '2026-08-10 09:00:00', '2026-08-10 09:00:00'),
(2, 'jefatura.test@test.helheim.cl', 1, 1, '2026-08-10 09:00:00', '2026-09-20 23:59:59', 1, 'seed_test_data', '2026-08-10 09:00:00', '2026-08-10 09:00:00'),
(3, 'cliente.test@test.helheim.cl', 1, 1, '2026-08-10 09:00:00', '2026-08-24 23:59:59', 3, 'seed_test_data', '2026-08-10 09:00:00', '2026-08-10 09:00:00'),
(4, 'camila.soto@test.tecaivot.cl', 2, 2, '2026-08-11 09:00:00', '2026-08-25 23:59:59', 2, 'seed_test_data', '2026-08-11 09:00:00', '2026-08-11 09:00:00'),
(5, 'jefatura.test@test.tecaivot.cl', 2, 2, '2026-08-11 09:00:00', '2026-09-25 23:59:59', 1, 'seed_test_data', '2026-08-11 09:00:00', '2026-08-11 09:00:00'),
(6, 'admin.test@test.tecaivot.cl', 2, 2, '2026-08-11 09:00:00', '2026-08-25 23:59:59', 2, 'seed_test_data', '2026-08-11 09:00:00', '2026-08-11 09:00:00'),
(7, 'patricio.gomez@test.engie.cl', 3, 3, '2026-08-15 09:00:00', '2026-08-29 23:59:59', 2, 'seed_test_data', '2026-08-15 09:00:00', '2026-08-15 09:00:00'),
(8, 'francisca.torres@test.engie.cl', 3, 3, '2026-08-15 09:00:00', '2026-08-29 23:59:59', 2, 'seed_test_data', '2026-08-15 09:00:00', '2026-08-15 09:00:00'),
(9, 'cristobal.fuentes@test.engie.cl', 3, 3, '2026-08-15 09:00:00', '2026-09-30 23:59:59', 1, 'seed_test_data', '2026-08-15 09:00:00', '2026-08-15 09:00:00'),
(10, 'javiera.reyes@test.engie.cl', 3, 3, '2026-08-15 09:00:00', '2026-08-29 23:59:59', 3, 'seed_test_data', '2026-08-15 09:00:00', '2026-08-15 09:00:00'),
(11, 'admin.test@test.engie.cl', 3, 3, '2026-08-16 09:00:00', '2026-09-30 23:59:59', 1, 'seed_test_data', '2026-08-16 09:00:00', '2026-08-16 09:00:00'),
(12, 'patricio.gomez@test.engie.cl', 4, 3, '2026-08-20 09:00:00', '2026-09-05 23:59:59', 1, 'seed_test_data', '2026-08-20 09:00:00', '2026-08-20 09:00:00'),
(13, 'francisca.torres@test.engie.cl', 4, 3, '2026-08-20 09:00:00', '2026-09-05 23:59:59', 2, 'seed_test_data', '2026-08-20 09:00:00', '2026-08-20 09:00:00'),
(14, 'javiera.reyes@test.engie.cl', 4, 3, '2026-08-20 09:00:00', '2026-09-05 23:59:59', 2, 'seed_test_data', '2026-08-20 09:00:00', '2026-08-20 09:00:00');

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

--
-- Volcado de datos para la tabla `workers`
--

INSERT INTO `workers` (`id_worker`, `id_company`, `rut`, `name`, `lastname`, `email`, `phone`, `position`, `photo_path`, `state`, `created_by`, `date_create`, `last_update`) VALUES
(1, 1, '18100200-K', 'Bárbara', 'Contreras', 'barbara.contreras@test.helheim.cl', '+56 9 5511 2201', 'Prevencionista de Riesgos', NULL, 1, 'seed_test_data', '2026-08-05 09:00:00', '2026-08-05 09:00:00'),
(2, 1, '18100265-4', 'Matías', 'Rojas', 'matias.rojas@test.helheim.cl', '+56 9 5511 2202', 'Analista de Calidad (QA)', NULL, 1, 'seed_test_data', '2026-08-05 09:00:00', '2026-08-05 09:00:00'),
(3, 2, '18100308-1', 'Camila', 'Soto', 'camila.soto@test.tecaivot.cl', '+56 9 5522 3301', 'Prevencionista de Riesgos', NULL, 1, 'seed_test_data', '2026-08-05 09:30:00', '2026-08-05 09:30:00'),
(4, 2, '18100534-3', 'Rodrigo', 'Salinas', 'rodrigo.salinas@test.tecaivot.cl', '+56 9 5522 3302', 'Técnico de Soporte TI', NULL, 1, 'seed_test_data', '2026-08-05 09:30:00', '2026-08-05 09:30:00'),
(5, 2, '18100641-2', 'Valentina', 'Rojas', 'valentina.rojas@test.tecaivot.cl', '+56 9 5522 3303', 'Desarrolladora Backend', NULL, 1, 'seed_test_data', '2026-08-05 09:30:00', '2026-08-05 09:30:00'),
(6, 3, '18100740-0', 'Cristóbal', 'Fuentes', 'cristobal.fuentes@test.engie.cl', '+56 9 5533 4401', 'Supervisor HSE', NULL, 1, 'seed_test_data', '2026-09-08 09:00:00', '2026-09-08 09:00:00'),
(7, 3, '18100834-2', 'Patricio', 'Gómez', 'patricio.gomez@test.engie.cl', '+56 9 5533 4402', 'Operador de Grúa', NULL, 1, 'seed_test_data', '2026-09-08 09:00:00', '2026-09-08 09:00:00'),
(8, 3, '18100906-3', 'Nicolás', 'Vargas', 'nicolas.vargas@test.engie.cl', '+56 9 5533 4403', 'Electricista', NULL, 1, 'seed_test_data', '2026-09-08 09:00:00', '2026-09-08 09:00:00'),
(9, 3, '18101131-9', 'Francisca', 'Torres', 'francisca.torres@test.engie.cl', '+56 9 5533 4404', 'Prevencionista de Riesgos', NULL, 1, 'seed_test_data', '2026-09-08 09:00:00', '2026-09-08 09:00:00'),
(10, 3, '18101194-7', 'Sebastián', 'Morales', 'sebastian.morales@test.engie.cl', '+56 9 5533 4405', 'Soldador Calificado', NULL, 1, 'seed_test_data', '2026-09-08 09:00:00', '2026-09-08 09:00:00'),
(11, 3, '18101404-0', 'Javiera', 'Reyes', 'javiera.reyes@test.engie.cl', '+56 9 5533 4406', 'Jefa de Terreno', NULL, 1, 'seed_test_data', '2026-09-08 09:00:00', '2026-09-08 09:00:00'),
(12, 3, '18101630-2', 'Felipe', 'Castillo', 'felipe.castillo@test.engie.cl', '+56 9 5533 4407', 'Operador Maquinaria Pesada', NULL, 1, 'seed_test_data', '2026-09-08 09:00:00', '2026-09-08 09:00:00'),
(13, 3, '18101806-2', 'Constanza', 'Pizarro', 'constanza.pizarro@test.engie.cl', '+56 9 5533 4408', 'Técnico Instrumentista', NULL, 1, 'seed_test_data', '2026-09-08 09:00:00', '2026-09-08 09:00:00'),
(14, 3, '18101865-8', 'Tomás', 'Espinoza', 'tomas.espinoza@test.engie.cl', '+56 9 5533 4409', 'Operador Grúa Horquilla', NULL, 1, 'seed_test_data', '2026-09-08 09:00:00', '2026-09-08 09:00:00'),
(15, 3, '18102053-9', 'Antonia', 'Fernández', 'antonia.fernandez@test.engie.cl', '+56 9 5533 4410', 'Encargada de Bodega', NULL, 1, 'seed_test_data', '2026-09-08 09:00:00', '2026-09-08 09:00:00');

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
-- Volcado de datos para la tabla `worker_projects`
--

INSERT INTO `worker_projects` (`id_worker`, `id_project`, `date_create`) VALUES
(1, 1, '2026-08-05 09:00:00'),
(2, 1, '2026-08-05 09:00:00'),
(3, 2, '2026-08-05 09:30:00'),
(4, 3, '2026-08-05 09:30:00'),
(5, 2, '2026-08-05 09:30:00'),
(6, 4, '2026-09-08 09:00:00'),
(7, 5, '2026-09-08 09:00:00'),
(8, 4, '2026-09-08 09:00:00'),
(9, 4, '2026-09-08 09:00:00'),
(9, 5, '2026-09-08 09:00:00'),
(10, 5, '2026-09-08 09:00:00'),
(11, 5, '2026-09-08 09:00:00'),
(12, 6, '2026-09-08 09:00:00'),
(13, 6, '2026-09-08 09:00:00'),
(14, 6, '2026-09-08 09:00:00'),
(15, 4, '2026-09-08 09:00:00');

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
  MODIFY `id_certificate` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `change_history`
--
ALTER TABLE `change_history`
  MODIFY `id_change` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `company`
--
ALTER TABLE `company`
  MODIFY `id_company` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `company_center`
--
ALTER TABLE `company_center`
  MODIFY `id_company_center` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `company_test`
--
ALTER TABLE `company_test`
  MODIFY `id_test` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `company_test_rel_questions`
--
ALTER TABLE `company_test_rel_questions`
  MODIFY `id_rel` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `login_codes`
--
ALTER TABLE `login_codes`
  MODIFY `id_login_code` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

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
  MODIFY `id_project` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `protocols`
--
ALTER TABLE `protocols`
  MODIFY `id_protocol` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `questions`
--
ALTER TABLE `questions`
  MODIFY `id_questions` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `questions_options`
--
ALTER TABLE `questions_options`
  MODIFY `id_questions_options` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT de la tabla `security_events`
--
ALTER TABLE `security_events`
  MODIFY `id_security_events` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de la tabla `security_event_evidence`
--
ALTER TABLE `security_event_evidence`
  MODIFY `id_evidence` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `security_event_tracking`
--
ALTER TABLE `security_event_tracking`
  MODIFY `id_security_event_tracking` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `test_materials`
--
ALTER TABLE `test_materials`
  MODIFY `id_material` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `users_role`
--
ALTER TABLE `users_role`
  MODIFY `id_users_role` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT de la tabla `users_role_group`
--
ALTER TABLE `users_role_group`
  MODIFY `id_role_group` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de la tabla `users_test_answers`
--
ALTER TABLE `users_test_answers`
  MODIFY `id_users_test_answers` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=79;

--
-- AUTO_INCREMENT de la tabla `users_test_assigned`
--
ALTER TABLE `users_test_assigned`
  MODIFY `id_user_test_assigned` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de la tabla `workers`
--
ALTER TABLE `workers`
  MODIFY `id_worker` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

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
