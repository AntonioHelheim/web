-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 14-09-2026 a las 18:33:40
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
  `id_test` int(11) DEFAULT NULL,
  `id_user_test_assigned` int(11) DEFAULT NULL,
  `id_users_auditor` varchar(50) DEFAULT NULL,
  `name_auditor` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `score` varchar(50) NOT NULL,
  `obs` text NOT NULL,
  `status` enum('pendiente','en_curso','completada','cancelada') NOT NULL DEFAULT 'pendiente',
  `completed_at` datetime DEFAULT NULL,
  `created_by` varchar(50) NOT NULL,
  `date_create` datetime NOT NULL,
  `last_update` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `audits`
--

INSERT INTO `audits` (`id_audits`, `id_company`, `id_test`, `id_user_test_assigned`, `id_users_auditor`, `name_auditor`, `email`, `score`, `obs`, `status`, `completed_at`, `created_by`, `date_create`, `last_update`) VALUES
(1, 4, 6, 19, 'GerenteEmpresaDemo@demoSCT.cl', 'Gerente DEMO', 'GerenteEmpresaDemo@demoSCT.cl', '', '', 'pendiente', NULL, 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 19:53:15', '2026-09-08 19:53:15'),
(2, 4, 6, 20, 'JefaturaEmpresaDemo@demoSCT.cl', 'Jefatura DEMO', 'JefaturaEmpresaDemo@demoSCT.cl', '66.7%', 'Primer intento registrado. Se mantienen observaciones pendientes de cierre.', 'en_curso', NULL, 'adminEmpresaDemo@demoSCT.cl', '2026-09-03 19:53:15', '2026-09-05 19:53:15'),
(3, 4, 6, 21, 'UsuarioEmpresaDemo@demoSCT.cl', 'Usuario DEMO', 'UsuarioEmpresaDemo@demoSCT.cl', '100.0%', 'Auditoría completada sin hallazgos críticos.', 'completada', '2026-09-06 19:53:15', 'adminEmpresaDemo@demoSCT.cl', '2026-09-01 19:53:15', '2026-09-06 19:53:15'),
(4, 4, 6, 22, 'adminEmpresaDemo@demoSCT.cl', 'Administrador DEMO', 'adminEmpresaDemo@demoSCT.cl', '66.7%', 'Auditoría finalizada sin alcanzar el porcentaje mínimo de cumplimiento.', 'completada', '2026-09-07 19:53:15', 'adminEmpresaDemo@demoSCT.cl', '2026-09-01 19:53:15', '2026-09-07 19:53:15'),
(5, 4, 7, 23, 'UsuarioEmpresaDemo@demoSCT.cl', 'Usuario DEMO', 'UsuarioEmpresaDemo@demoSCT.cl', '', '', 'pendiente', NULL, 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 19:53:15', '2026-09-08 19:53:15'),
(6, 4, 10, 30, 'GerenteEmpresaDemo@demoSCT.cl', 'Gerente DEMO', 'GerenteEmpresaDemo@demoSCT.cl', '100.0%', 'Auditoría completada sin desviaciones críticas.', 'completada', '2026-09-03 17:20:00', 'adminEmpresaDemo@demoSCT.cl', '2026-09-01 09:00:00', '2026-09-03 17:20:00'),
(7, 4, 10, 31, 'JefaturaEmpresaDemo@demoSCT.cl', 'Jefatura DEMO', 'JefaturaEmpresaDemo@demoSCT.cl', '', '', 'pendiente', NULL, 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 23:02:46', '2026-09-08 23:02:46'),
(8, 4, 10, 32, 'adminEmpresaDemo@demoSCT.cl', 'Administrador DEMO', 'adminEmpresaDemo@demoSCT.cl', '60.0%', 'Primer intento bajo el mínimo; la asignación permanece disponible para un segundo intento.', 'en_curso', NULL, 'adminEmpresaDemo@demoSCT.cl', '2026-09-02 09:00:00', '2026-09-06 18:00:00'),
(9, 4, 10, 33, 'UsuarioEmpresaDemo@demoSCT.cl', 'Usuario DEMO', 'UsuarioEmpresaDemo@demoSCT.cl', '60.0%', 'Auditoría finalizada sin alcanzar el mínimo requerido después de dos intentos.', 'completada', '2026-09-07 18:40:00', 'adminEmpresaDemo@demoSCT.cl', '2026-09-01 10:00:00', '2026-09-07 18:40:00'),
(10, 4, 11, 34, 'JefaturaEmpresaDemo@demoSCT.cl', 'Jefatura DEMO', 'JefaturaEmpresaDemo@demoSCT.cl', '', '', 'pendiente', NULL, 'adminEmpresaDemo@demoSCT.cl', '2026-09-01 09:00:00', '2026-09-01 09:00:00');

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
  `id_company` int(11) DEFAULT NULL COMMENT 'Snapshot del alcance de empresa; NULL = global/sin alcance',
  `module` varchar(50) NOT NULL DEFAULT 'legacy' COMMENT 'Módulo funcional SCT que originó el cambio',
  `action` varchar(30) NOT NULL DEFAULT 'update' COMMENT 'create/update/state_change/assign/unassign/delete/upload/remove/review/track/issue_access',
  `table_name` varchar(64) NOT NULL,
  `record_id` varchar(50) NOT NULL,
  `record_label` varchar(255) DEFAULT NULL COMMENT 'Etiqueta legible del registro al momento del cambio',
  `field_name` varchar(64) NOT NULL,
  `old_value` text DEFAULT NULL,
  `new_value` text DEFAULT NULL,
  `changed_by` varchar(50) NOT NULL,
  `changed_at` datetime NOT NULL DEFAULT current_timestamp(),
  `request_id` varchar(64) DEFAULT NULL COMMENT 'Agrupa varios campos modificados por una misma operación'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `change_history`
--

INSERT INTO `change_history` (`id_change`, `id_company`, `module`, `action`, `table_name`, `record_id`, `record_label`, `field_name`, `old_value`, `new_value`, `changed_by`, `changed_at`, `request_id`) VALUES
(1, 4, 'proyectos', 'state_change', 'projects', '7', 'DEMO QA - Implementación SCT', 'state', '0', '1', 'adminEmpresaDemo@demoSCT.cl', '2026-09-01 19:53:16', 'legacy-1'),
(2, 4, 'trabajadores', 'update', 'workers', '18', 'Usuario DEMO', 'position', 'Ayudante', 'Usuario Operativo', 'JefaturaEmpresaDemo@demoSCT.cl', '2026-09-02 19:53:16', 'legacy-2'),
(3, 4, 'eventos', 'state_change', 'security_events', '16', 'Evento #16', 'state', '1', '2', 'JefaturaEmpresaDemo@demoSCT.cl', '2026-09-06 19:53:16', 'legacy-3'),
(4, 4, 'usuarios', 'update', 'users', 'GerenteEmpresaDemo@demoSCT.cl', 'Gerente DEMO', 'language', 'es', 'pt', 'GerenteEmpresaDemo@demoSCT.cl', '2026-09-10 00:52:14', 'ebca5c60bfd7b46e93086f5c0ea6fa79'),
(5, 4, 'usuarios', 'update', 'users', 'GerenteEmpresaDemo@demoSCT.cl', 'Gerente DEMO', 'language', 'pt', 'es', 'GerenteEmpresaDemo@demoSCT.cl', '2026-09-10 00:52:32', 'd19e11bd2b2c1dcf13c802d218bc90e3');

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
  `logo_path` varchar(255) DEFAULT NULL,
  `state` int(11) NOT NULL,
  `created_by` varchar(50) NOT NULL,
  `date_create` datetime NOT NULL,
  `last_update` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `company`
--

INSERT INTO `company` (`id_company`, `rut`, `razon_social`, `address`, `email`, `logo_path`, `state`, `created_by`, `date_create`, `last_update`) VALUES
(1, '77742346-0', 'helheim', 'Santiago', 'contacto@helheim.cl', 'uploads/empresas/company_1_86efe1f5d2ff02c3.png', 1, 'phpmyadmin', '2026-08-14 20:50:47', '2026-09-08 16:35:13'),
(2, '1234', 'tecaivot', 'Santiago', 'contacto@tecaivot.cl', NULL, 1, 'phpmyadmin', '2026-08-15 12:00:58', '2026-08-15 12:00:58'),
(3, '123456789', 'engie', 'sistema prueba mvp \r\nSafetyControlTower v1.0', 'prueba@engie.cl', NULL, 1, 'phpmyadmin', '2026-09-08 01:05:35', '2026-09-08 01:05:35'),
(4, '99999999-9', 'Empresa DEMO SCT', 'Ambiente de demostración Safety Control Tower', 'demo@demoSCT.cl', NULL, 1, 'seed_demo_sct', '2026-09-08 18:43:22', '2026-09-08 18:43:22');

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
(6, 3, 'Subestación Eléctrica Los Andes', 'Subestación de transmisión eléctrica de ENGIE en Los Andes, Región de Valparaíso.', 1, 'seed_test_data', '2026-09-08 08:30:00', '2026-09-08 08:30:00'),
(7, 4, 'DEMO QA - Casa Matriz', 'Centro administrativo utilizado para pruebas funcionales de Safety Control Tower.', 1, 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 19:53:15', '2026-09-08 19:53:15'),
(8, 4, 'DEMO QA - Planta Operacional', 'Planta de demostración para eventos, trabajadores, auditorías y seguimiento.', 1, 'JefaturaEmpresaDemo@demoSCT.cl', '2026-09-08 19:53:15', '2026-09-08 19:53:15'),
(9, 4, 'DEMO QA - Faena Piloto', 'Faena de prueba para flujos de terreno y trabajo en altura.', 1, 'JefaturaEmpresaDemo@demoSCT.cl', '2026-09-08 19:53:15', '2026-09-08 19:53:15'),
(10, 4, 'DEMO QA - Centro Inactivo', 'Centro deshabilitado para validar filtros y estados inactivos.', 0, 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 19:53:15', '2026-09-08 19:53:15');

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
(4, 'Inducción Trabajo en Altura', 'induccion', 'Curso específico sobre procedimientos seguros para trabajo en altura en instalaciones de ENGIE.', 1, 1, 2, 75, '2026-08-01 00:00:00', '2026-12-31 23:59:59', 'seed_test_data', '2026-09-08 09:15:00', '2026-09-08 09:15:00', 3),
(5, 'DEMO QA - Inducción General', 'induccion', 'Inducción de seguridad de la Empresa DEMO para probar asignaciones, intentos y resultados.', 1, 1, 2, 70, '2026-08-09 19:53:15', '2027-03-07 19:53:15', 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 19:53:15', '2026-09-08 19:53:15', 4),
(6, 'DEMO QA - Auditoría Seguridad Operacional', 'auditoria', 'Auditoría DEMO para validar flujo pendiente, en curso, aprobada y reprobada.', 1, 1, 2, 80, '2026-08-09 19:53:15', '2027-03-07 19:53:15', 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 19:53:15', '2026-09-08 19:53:15', 4),
(7, 'DEMO QA - Auditoría Trabajo en Altura', 'auditoria', 'Auditoría específica para validar trabajo en altura y uso de EPP.', 1, 1, 1, 80, '2026-08-09 19:53:15', '2027-03-07 19:53:15', 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 19:53:15', '2026-09-08 19:53:15', 4),
(8, 'DEMO QA - Autoevaluación Cultura Preventiva', 'autoevaluacion', 'Autoevaluación DEMO de cultura preventiva y uso de la plataforma.', 1, 1, 2, 70, '2026-08-09 19:53:15', '2027-03-07 19:53:15', 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 19:53:15', '2026-09-08 19:53:15', 4),
(9, 'DEMO EXT - Inducción Contratistas', 'induccion', 'Inducción adicional para probar ejecución real, intentos y generación efectiva de certificado desde la aplicación.', 1, 1, 2, 80, '2026-09-01 00:00:00', '2026-12-31 23:59:59', 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 23:02:46', '2026-09-08 23:02:46', 4),
(10, 'DEMO EXT - Auditoría Orden y Aseo', 'auditoria', 'Auditoría adicional para validar estados pendiente, en curso, cumple y no cumple utilizando los perfiles DEMO.', 1, 1, 2, 80, '2026-09-01 00:00:00', '2026-12-31 23:59:59', 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 23:02:46', '2026-09-08 23:02:46', 4),
(11, 'DEMO EXT - Auditoría Plazo Vencido', 'auditoria', 'Auditoría vigente con una asignación cuyo plazo ya venció; debe quedar visible pero no ejecutable.', 1, 1, 1, 80, '2026-09-01 00:00:00', '2026-12-31 23:59:59', 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 23:02:46', '2026-09-08 23:02:46', 4),
(12, 'DEMO EXT - Autoevaluación Riesgos Críticos', 'autoevaluacion', 'Autoevaluación adicional para probar pendiente, en curso, aprobación y reprobación con los cuatro perfiles DEMO.', 1, 1, 2, 80, '2026-09-01 00:00:00', '2026-12-31 23:59:59', 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 23:02:46', '2026-09-08 23:02:46', 4),
(13, 'DEMO EXT - Autoevaluación Plazo Vencido', 'autoevaluacion', 'Evaluación vigente cuya asignación al Usuario DEMO ya venció; sirve para validar el bloqueo por deadline.', 1, 1, 1, 75, '2026-09-01 00:00:00', '2026-12-31 23:59:59', 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 23:02:46', '2026-09-08 23:02:46', 4),
(14, 'DEMO EXT - Autoevaluación Inactiva', 'autoevaluacion', 'Plantilla inactiva destinada a validar filtros, activación y edición antes de la primera asignación.', 1, 0, 1, 70, '2026-09-01 00:00:00', '2026-12-31 23:59:59', 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 23:02:46', '2026-09-08 23:02:46', 4);

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
(26, 4, 12, 10, 'seed_test_data', '2026-09-08 09:15:00', '2026-09-08 09:15:00'),
(27, 2, 8, 10, 'fco.fredes.g@gmail.com', '2026-09-08 13:46:56', '2026-09-08 13:46:56'),
(28, 2, 9, 10, 'fco.fredes.g@gmail.com', '2026-09-08 13:46:59', '2026-09-08 13:46:59'),
(29, 5, 1, 10, 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 19:53:15', '2026-09-08 19:53:15'),
(30, 5, 2, 10, 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 19:53:15', '2026-09-08 19:53:15'),
(31, 5, 3, 10, 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 19:53:15', '2026-09-08 19:53:15'),
(32, 5, 4, 10, 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 19:53:15', '2026-09-08 19:53:15'),
(33, 5, 5, 10, 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 19:53:15', '2026-09-08 19:53:15'),
(34, 5, 6, 10, 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 19:53:15', '2026-09-08 19:53:15'),
(36, 6, 1, 10, 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 19:53:15', '2026-09-08 19:53:15'),
(37, 6, 2, 10, 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 19:53:15', '2026-09-08 19:53:15'),
(38, 6, 4, 10, 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 19:53:15', '2026-09-08 19:53:15'),
(39, 6, 5, 10, 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 19:53:15', '2026-09-08 19:53:15'),
(40, 6, 9, 10, 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 19:53:15', '2026-09-08 19:53:15'),
(41, 6, 10, 10, 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 19:53:15', '2026-09-08 19:53:15'),
(43, 7, 1, 10, 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 19:53:15', '2026-09-08 19:53:15'),
(44, 7, 2, 10, 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 19:53:15', '2026-09-08 19:53:15'),
(45, 7, 7, 10, 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 19:53:15', '2026-09-08 19:53:15'),
(46, 7, 11, 10, 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 19:53:15', '2026-09-08 19:53:15'),
(47, 7, 12, 10, 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 19:53:15', '2026-09-08 19:53:15'),
(50, 8, 1, 10, 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 19:53:15', '2026-09-08 19:53:15'),
(51, 8, 3, 10, 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 19:53:15', '2026-09-08 19:53:15'),
(52, 8, 4, 10, 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 19:53:15', '2026-09-08 19:53:15'),
(53, 8, 6, 10, 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 19:53:15', '2026-09-08 19:53:15'),
(54, 8, 8, 10, 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 19:53:15', '2026-09-08 19:53:15'),
(55, 8, 9, 10, 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 19:53:15', '2026-09-08 19:53:15'),
(56, 8, 10, 10, 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 19:53:15', '2026-09-08 19:53:15'),
(57, 9, 1, 10, 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 23:02:46', '2026-09-08 23:02:46'),
(58, 9, 2, 10, 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 23:02:46', '2026-09-08 23:02:46'),
(59, 9, 4, 10, 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 23:02:46', '2026-09-08 23:02:46'),
(60, 9, 7, 10, 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 23:02:46', '2026-09-08 23:02:46'),
(61, 9, 11, 10, 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 23:02:46', '2026-09-08 23:02:46'),
(62, 9, 12, 10, 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 23:02:46', '2026-09-08 23:02:46'),
(64, 10, 1, 10, 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 23:02:46', '2026-09-08 23:02:46'),
(65, 10, 4, 10, 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 23:02:46', '2026-09-08 23:02:46'),
(66, 10, 5, 10, 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 23:02:46', '2026-09-08 23:02:46'),
(67, 10, 9, 10, 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 23:02:46', '2026-09-08 23:02:46'),
(68, 10, 10, 10, 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 23:02:46', '2026-09-08 23:02:46'),
(71, 11, 2, 10, 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 23:02:46', '2026-09-08 23:02:46'),
(72, 11, 6, 10, 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 23:02:46', '2026-09-08 23:02:46'),
(73, 11, 8, 10, 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 23:02:46', '2026-09-08 23:02:46'),
(74, 11, 12, 10, 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 23:02:46', '2026-09-08 23:02:46'),
(78, 12, 1, 10, 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 23:02:46', '2026-09-08 23:02:46'),
(79, 12, 2, 10, 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 23:02:46', '2026-09-08 23:02:46'),
(80, 12, 5, 10, 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 23:02:46', '2026-09-08 23:02:46'),
(81, 12, 7, 10, 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 23:02:46', '2026-09-08 23:02:46'),
(82, 12, 9, 10, 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 23:02:46', '2026-09-08 23:02:46'),
(83, 12, 11, 10, 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 23:02:46', '2026-09-08 23:02:46'),
(85, 13, 1, 10, 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 23:02:46', '2026-09-08 23:02:46'),
(86, 13, 3, 10, 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 23:02:46', '2026-09-08 23:02:46'),
(87, 13, 6, 10, 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 23:02:46', '2026-09-08 23:02:46'),
(88, 13, 10, 10, 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 23:02:46', '2026-09-08 23:02:46'),
(92, 14, 2, 10, 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 23:02:47', '2026-09-08 23:02:47'),
(93, 14, 4, 10, 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 23:02:47', '2026-09-08 23:02:47'),
(94, 14, 8, 10, 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 23:02:47', '2026-09-08 23:02:47'),
(95, 14, 12, 10, 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 23:02:47', '2026-09-08 23:02:47');

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

--
-- Volcado de datos para la tabla `dynamic_forms`
--

INSERT INTO `dynamic_forms` (`id_form`, `id_company`, `name`, `description`, `state`, `created_by`, `date_create`, `last_update`) VALUES
(1, 4, 'DEMO QA - Checklist Inspección de Terreno', 'Formulario configurable para validar tipos de campo, obligatoriedad y orden.', 1, 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 19:53:16', '2026-09-08 19:53:16'),
(2, 4, 'DEMO QA - Observación Preventiva', 'Formulario de prueba para registrar conductas seguras, condiciones inseguras y oportunidades de mejora.', 1, 'JefaturaEmpresaDemo@demoSCT.cl', '2026-09-08 19:53:16', '2026-09-08 19:53:16'),
(3, NULL, 'DEMO EXT - Reporte Global de Condición', 'Formulario global de prueba visible para Empresa DEMO. Incluye date, select, number, text, checkbox y file.', 1, 'admin.completo.test@test.helheim.cl', '2026-09-08 23:02:47', '2026-09-08 23:02:47'),
(4, 4, 'DEMO EXT - Inspección Equipos de Emergencia', 'Formulario sin envíos para probar edición completa antes de la primera respuesta y bloqueo posterior.', 1, 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 23:02:47', '2026-09-08 23:02:47'),
(5, 4, 'DEMO EXT - Encuesta Ergonomía Inactiva', 'Formulario inactivo para comprobar filtros, activación y que no aparezca en Mis Formularios.', 0, 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 23:02:47', '2026-09-08 23:02:47'),
(6, 4, 'DEMO PROT - Evaluación Sílice', 'Formulario funcional DEMO para registrar contexto de exposición y controles, sin aplicar umbrales regulatorios automáticos.', 1, 'adminEmpresaDemo@demoSCT.cl', '2026-09-09 00:52:19', '2026-09-09 00:52:19'),
(7, 4, 'DEMO PROT - Seguimiento Psicosocial', 'Formulario DEMO de seguimiento de gestión organizacional. No reproduce ni reemplaza instrumentos oficiales.', 1, 'adminEmpresaDemo@demoSCT.cl', '2026-09-09 00:52:19', '2026-09-09 00:52:19'),
(8, 4, 'DEMO PROT - Complemento PREXOR', 'Formulario complementario DEMO para registrar antecedentes operacionales de ruido sin interpretar límites regulatorios.', 1, 'adminEmpresaDemo@demoSCT.cl', '2026-09-09 00:52:19', '2026-09-09 00:52:19'),
(9, NULL, 'DEMO PROT - Control Documental Global', 'Formulario global de prueba para validar implementación de un protocolo global en una empresa específica.', 1, 'adminEmpresaDemo@demoSCT.cl', '2026-09-09 00:52:19', '2026-09-09 00:52:19');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `dynamic_form_answers`
--

CREATE TABLE `dynamic_form_answers` (
  `id_answer` int(11) NOT NULL,
  `id_submission` int(11) NOT NULL,
  `id_field` int(11) NOT NULL,
  `value_text` longtext DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `original_name` varchar(255) DEFAULT NULL,
  `mime_type` varchar(100) DEFAULT NULL,
  `created_by` varchar(50) NOT NULL,
  `date_create` datetime NOT NULL,
  `last_update` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `dynamic_form_answers`
--

INSERT INTO `dynamic_form_answers` (`id_answer`, `id_submission`, `id_field`, `value_text`, `file_path`, `original_name`, `mime_type`, `created_by`, `date_create`, `last_update`) VALUES
(1, 1, 1, '2026-09-08', NULL, NULL, NULL, 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 18:00:00', '2026-09-08 18:00:00'),
(2, 1, 2, 'Casa Matriz - Acceso principal', NULL, NULL, NULL, 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 18:00:00', '2026-09-08 18:00:00'),
(3, 1, 3, 'Conforme', NULL, NULL, NULL, 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 18:00:00', '2026-09-08 18:00:00'),
(4, 1, 4, '[\"Sí\"]', NULL, NULL, NULL, 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 18:00:00', '2026-09-08 18:00:00'),
(5, 1, 5, 'Sin hallazgos críticos; condiciones generales conformes.', NULL, NULL, NULL, 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 18:00:00', '2026-09-08 18:00:00'),
(6, 1, 6, NULL, NULL, NULL, NULL, 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 18:00:00', '2026-09-08 18:00:00'),
(8, 2, 1, '2026-09-08', NULL, NULL, NULL, 'JefaturaEmpresaDemo@demoSCT.cl', '2026-09-08 18:12:00', '2026-09-08 18:12:00'),
(9, 2, 2, 'Planta Operacional - Bodega', NULL, NULL, NULL, 'JefaturaEmpresaDemo@demoSCT.cl', '2026-09-08 18:12:00', '2026-09-08 18:12:00'),
(10, 2, 3, 'Observación', NULL, NULL, NULL, 'JefaturaEmpresaDemo@demoSCT.cl', '2026-09-08 18:12:00', '2026-09-08 18:12:00'),
(11, 2, 4, '[\"Sí\"]', NULL, NULL, NULL, 'JefaturaEmpresaDemo@demoSCT.cl', '2026-09-08 18:12:00', '2026-09-08 18:12:00'),
(12, 2, 5, 'Señalética de evacuación parcialmente deteriorada; requiere reposición.', NULL, NULL, NULL, 'JefaturaEmpresaDemo@demoSCT.cl', '2026-09-08 18:12:00', '2026-09-08 18:12:00'),
(13, 2, 6, NULL, NULL, NULL, NULL, 'JefaturaEmpresaDemo@demoSCT.cl', '2026-09-08 18:12:00', '2026-09-08 18:12:00'),
(15, 3, 1, '2026-09-08', NULL, NULL, NULL, 'UsuarioEmpresaDemo@demoSCT.cl', '2026-09-08 18:25:00', '2026-09-08 18:25:00'),
(16, 3, 2, 'Faena Piloto - Zona de corte', NULL, NULL, NULL, 'UsuarioEmpresaDemo@demoSCT.cl', '2026-09-08 18:25:00', '2026-09-08 18:25:00'),
(17, 3, 3, 'No conforme', NULL, NULL, NULL, 'UsuarioEmpresaDemo@demoSCT.cl', '2026-09-08 18:25:00', '2026-09-08 18:25:00'),
(18, 3, 4, '[\"No\"]', NULL, NULL, NULL, 'UsuarioEmpresaDemo@demoSCT.cl', '2026-09-08 18:25:00', '2026-09-08 18:25:00'),
(19, 3, 5, 'Se detectó ejecución de tarea sin protección ocular completa.', NULL, NULL, NULL, 'UsuarioEmpresaDemo@demoSCT.cl', '2026-09-08 18:25:00', '2026-09-08 18:25:00'),
(20, 3, 6, NULL, NULL, NULL, NULL, 'UsuarioEmpresaDemo@demoSCT.cl', '2026-09-08 18:25:00', '2026-09-08 18:25:00'),
(22, 4, 7, 'Conducta segura', NULL, NULL, NULL, 'GerenteEmpresaDemo@demoSCT.cl', '2026-09-08 18:35:00', '2026-09-08 18:35:00'),
(23, 4, 8, 'Equipo utiliza correctamente barandas y mantiene tránsito despejado.', NULL, NULL, NULL, 'GerenteEmpresaDemo@demoSCT.cl', '2026-09-08 18:35:00', '2026-09-08 18:35:00'),
(24, 4, 9, '2026-09-08', NULL, NULL, NULL, 'GerenteEmpresaDemo@demoSCT.cl', '2026-09-08 18:35:00', '2026-09-08 18:35:00'),
(25, 4, 10, '[\"No\"]', NULL, NULL, NULL, 'GerenteEmpresaDemo@demoSCT.cl', '2026-09-08 18:35:00', '2026-09-08 18:35:00'),
(26, 4, 11, 'Jefatura Operacional DEMO', NULL, NULL, NULL, 'GerenteEmpresaDemo@demoSCT.cl', '2026-09-08 18:35:00', '2026-09-08 18:35:00'),
(29, 5, 7, 'Mejora propuesta', NULL, NULL, NULL, 'JefaturaEmpresaDemo@demoSCT.cl', '2026-09-08 18:42:00', '2026-09-08 18:42:00'),
(30, 5, 8, 'Incorporar demarcación adicional para separar peatones y equipos móviles.', NULL, NULL, NULL, 'JefaturaEmpresaDemo@demoSCT.cl', '2026-09-08 18:42:00', '2026-09-08 18:42:00'),
(31, 5, 9, '2026-09-08', NULL, NULL, NULL, 'JefaturaEmpresaDemo@demoSCT.cl', '2026-09-08 18:42:00', '2026-09-08 18:42:00'),
(32, 5, 10, '[\"No\"]', NULL, NULL, NULL, 'JefaturaEmpresaDemo@demoSCT.cl', '2026-09-08 18:42:00', '2026-09-08 18:42:00'),
(33, 5, 11, 'Administrador Empresa DEMO', NULL, NULL, NULL, 'JefaturaEmpresaDemo@demoSCT.cl', '2026-09-08 18:42:00', '2026-09-08 18:42:00'),
(36, 6, 7, 'Condición insegura', NULL, NULL, NULL, 'UsuarioEmpresaDemo@demoSCT.cl', '2026-09-08 18:50:00', '2026-09-08 18:50:00'),
(37, 6, 8, 'Material almacenado invade parcialmente la ruta de evacuación.', NULL, NULL, NULL, 'UsuarioEmpresaDemo@demoSCT.cl', '2026-09-08 18:50:00', '2026-09-08 18:50:00'),
(38, 6, 9, '2026-09-08', NULL, NULL, NULL, 'UsuarioEmpresaDemo@demoSCT.cl', '2026-09-08 18:50:00', '2026-09-08 18:50:00'),
(39, 6, 10, '[\"Sí\"]', NULL, NULL, NULL, 'UsuarioEmpresaDemo@demoSCT.cl', '2026-09-08 18:50:00', '2026-09-08 18:50:00'),
(40, 6, 11, 'Jefatura Empresa DEMO', NULL, NULL, NULL, 'UsuarioEmpresaDemo@demoSCT.cl', '2026-09-08 18:50:00', '2026-09-08 18:50:00'),
(43, 7, 12, '2026-09-08', NULL, NULL, NULL, 'GerenteEmpresaDemo@demoSCT.cl', '2026-09-08 19:00:00', '2026-09-08 19:00:00'),
(44, 7, 13, 'Segura', NULL, NULL, NULL, 'GerenteEmpresaDemo@demoSCT.cl', '2026-09-08 19:00:00', '2026-09-08 19:00:00'),
(45, 7, 14, '1', NULL, NULL, NULL, 'GerenteEmpresaDemo@demoSCT.cl', '2026-09-08 19:00:00', '2026-09-08 19:00:00'),
(46, 7, 15, 'Área administrativa despejada y señalización visible.', NULL, NULL, NULL, 'GerenteEmpresaDemo@demoSCT.cl', '2026-09-08 19:00:00', '2026-09-08 19:00:00'),
(47, 7, 16, '[\"Confirmado\"]', NULL, NULL, NULL, 'GerenteEmpresaDemo@demoSCT.cl', '2026-09-08 19:00:00', '2026-09-08 19:00:00'),
(48, 7, 17, NULL, NULL, NULL, NULL, 'GerenteEmpresaDemo@demoSCT.cl', '2026-09-08 19:00:00', '2026-09-08 19:00:00'),
(50, 8, 12, '2026-09-08', NULL, NULL, NULL, 'JefaturaEmpresaDemo@demoSCT.cl', '2026-09-08 19:08:00', '2026-09-08 19:08:00'),
(51, 8, 13, 'Insegura', NULL, NULL, NULL, 'JefaturaEmpresaDemo@demoSCT.cl', '2026-09-08 19:08:00', '2026-09-08 19:08:00'),
(52, 8, 14, '4', NULL, NULL, NULL, 'JefaturaEmpresaDemo@demoSCT.cl', '2026-09-08 19:08:00', '2026-09-08 19:08:00'),
(53, 8, 15, 'Tránsito peatonal cercano a equipo móvil sin separación física.', NULL, NULL, NULL, 'JefaturaEmpresaDemo@demoSCT.cl', '2026-09-08 19:08:00', '2026-09-08 19:08:00'),
(54, 8, 16, '[\"Confirmado\"]', NULL, NULL, NULL, 'JefaturaEmpresaDemo@demoSCT.cl', '2026-09-08 19:08:00', '2026-09-08 19:08:00'),
(55, 8, 17, NULL, NULL, NULL, NULL, 'JefaturaEmpresaDemo@demoSCT.cl', '2026-09-08 19:08:00', '2026-09-08 19:08:00'),
(57, 9, 12, '2026-09-08', NULL, NULL, NULL, 'UsuarioEmpresaDemo@demoSCT.cl', '2026-09-08 19:16:00', '2026-09-08 19:16:00'),
(58, 9, 13, 'Oportunidad de mejora', NULL, NULL, NULL, 'UsuarioEmpresaDemo@demoSCT.cl', '2026-09-08 19:16:00', '2026-09-08 19:16:00'),
(59, 9, 14, '2', NULL, NULL, NULL, 'UsuarioEmpresaDemo@demoSCT.cl', '2026-09-08 19:16:00', '2026-09-08 19:16:00'),
(60, 9, 15, 'Se propone mejorar ubicación de contenedores para evitar cruces innecesarios.', NULL, NULL, NULL, 'UsuarioEmpresaDemo@demoSCT.cl', '2026-09-08 19:16:00', '2026-09-08 19:16:00'),
(61, 9, 16, '[\"Confirmado\"]', NULL, NULL, NULL, 'UsuarioEmpresaDemo@demoSCT.cl', '2026-09-08 19:16:00', '2026-09-08 19:16:00'),
(62, 9, 17, NULL, NULL, NULL, NULL, 'UsuarioEmpresaDemo@demoSCT.cl', '2026-09-08 19:16:00', '2026-09-08 19:16:00'),
(64, 10, 12, '2026-09-08', NULL, NULL, NULL, 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 19:24:00', '2026-09-08 19:24:00'),
(65, 10, 13, 'Segura', NULL, NULL, NULL, 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 19:24:00', '2026-09-08 19:24:00'),
(66, 10, 14, '1', NULL, NULL, NULL, 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 19:24:00', '2026-09-08 19:24:00'),
(67, 10, 15, 'Equipos de emergencia accesibles y señalizados.', NULL, NULL, NULL, 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 19:24:00', '2026-09-08 19:24:00'),
(68, 10, 16, '[\"Confirmado\"]', NULL, NULL, NULL, 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 19:24:00', '2026-09-08 19:24:00'),
(69, 10, 17, NULL, NULL, NULL, NULL, 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 19:24:00', '2026-09-08 19:24:00'),
(71, 11, 33, '2026-09-04', NULL, NULL, NULL, 'GerenteEmpresaDemo@demoSCT.cl', '2026-09-04 15:20:00', '2026-09-04 15:20:00'),
(72, 11, 34, 'Casa Matriz - Administración', NULL, NULL, NULL, 'GerenteEmpresaDemo@demoSCT.cl', '2026-09-04 15:20:00', '2026-09-04 15:20:00'),
(73, 11, 35, 'Sin observaciones', NULL, NULL, NULL, 'GerenteEmpresaDemo@demoSCT.cl', '2026-09-04 15:20:00', '2026-09-04 15:20:00'),
(74, 11, 36, '[\"Comunicación\",\"Apoyo de jefatura\"]', NULL, NULL, NULL, 'GerenteEmpresaDemo@demoSCT.cl', '2026-09-04 15:20:00', '2026-09-04 15:20:00'),
(75, 11, 37, 'Revisión DEMO completada; acciones comunicacionales implementadas.', NULL, NULL, NULL, 'GerenteEmpresaDemo@demoSCT.cl', '2026-09-04 15:20:00', '2026-09-04 15:20:00'),
(78, 12, 27, '2026-09-06', NULL, NULL, NULL, 'adminEmpresaDemo@demoSCT.cl', '2026-09-06 15:30:00', '2026-09-06 15:30:00'),
(79, 12, 28, 'Planta Operacional - preparación de materiales', NULL, NULL, NULL, 'adminEmpresaDemo@demoSCT.cl', '2026-09-06 15:30:00', '2026-09-06 15:30:00'),
(80, 12, 29, 'Por evaluar', NULL, NULL, NULL, 'adminEmpresaDemo@demoSCT.cl', '2026-09-06 15:30:00', '2026-09-06 15:30:00'),
(81, 12, 30, '[\"Control administrativo\",\"EPP\"]', NULL, NULL, NULL, 'adminEmpresaDemo@demoSCT.cl', '2026-09-06 15:30:00', '2026-09-06 15:30:00'),
(82, 12, 31, 'Escenario DEMO: falta verificar documentación y condición de controles de ingeniería.', NULL, NULL, NULL, 'adminEmpresaDemo@demoSCT.cl', '2026-09-06 15:30:00', '2026-09-06 15:30:00'),
(83, 12, 32, NULL, NULL, NULL, NULL, 'adminEmpresaDemo@demoSCT.cl', '2026-09-06 15:30:00', '2026-09-06 15:30:00'),
(85, 13, 27, '2026-09-08', NULL, NULL, NULL, 'GerenteEmpresaDemo@demoSCT.cl', '2026-09-08 19:30:00', '2026-09-08 19:30:00'),
(86, 13, 28, 'Faena Piloto - trabajo de contratista', NULL, NULL, NULL, 'GerenteEmpresaDemo@demoSCT.cl', '2026-09-08 19:30:00', '2026-09-08 19:30:00'),
(87, 13, 29, 'Sí', NULL, NULL, NULL, 'GerenteEmpresaDemo@demoSCT.cl', '2026-09-08 19:30:00', '2026-09-08 19:30:00'),
(88, 13, 30, '[\"Control de ingeniería\",\"Control administrativo\",\"EPP\"]', NULL, NULL, NULL, 'GerenteEmpresaDemo@demoSCT.cl', '2026-09-08 19:30:00', '2026-09-08 19:30:00'),
(89, 13, 31, 'Ejecución DEMO enviada para probar el flujo de revisión por un perfil gestor.', NULL, NULL, NULL, 'GerenteEmpresaDemo@demoSCT.cl', '2026-09-08 19:30:00', '2026-09-08 19:30:00'),
(90, 13, 32, NULL, NULL, NULL, NULL, 'GerenteEmpresaDemo@demoSCT.cl', '2026-09-08 19:30:00', '2026-09-08 19:30:00'),
(92, 14, 7, 'Mejora propuesta', NULL, NULL, NULL, 'adminEmpresaDemo@demoSCT.cl', '2026-08-29 15:20:00', '2026-08-29 15:20:00'),
(93, 14, 8, 'Registro DEMO usado para probar una ejecución posteriormente revisada como no aplica.', NULL, NULL, NULL, 'adminEmpresaDemo@demoSCT.cl', '2026-08-29 15:20:00', '2026-08-29 15:20:00'),
(94, 14, 9, '2026-08-29', NULL, NULL, NULL, 'adminEmpresaDemo@demoSCT.cl', '2026-08-29 15:20:00', '2026-08-29 15:20:00'),
(95, 14, 10, '[\"No\"]', NULL, NULL, NULL, 'adminEmpresaDemo@demoSCT.cl', '2026-08-29 15:20:00', '2026-08-29 15:20:00'),
(96, 14, 11, 'Administrador DEMO', NULL, NULL, NULL, 'adminEmpresaDemo@demoSCT.cl', '2026-08-29 15:20:00', '2026-08-29 15:20:00');

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

--
-- Volcado de datos para la tabla `dynamic_form_fields`
--

INSERT INTO `dynamic_form_fields` (`id_field`, `id_form`, `label`, `field_type`, `options`, `is_required`, `sort_order`) VALUES
(1, 1, 'Fecha de inspección', 'date', NULL, 1, 1),
(2, 1, 'Área inspeccionada', 'text', NULL, 1, 2),
(3, 1, 'Resultado de inspección', 'select', 'Conforme|Observación|No conforme|Crítica', 1, 3),
(4, 1, 'Uso correcto de EPP', 'checkbox', 'Sí|No', 1, 4),
(5, 1, 'Descripción del hallazgo', 'text', NULL, 1, 5),
(6, 1, 'Evidencia fotográfica', 'file', NULL, 0, 6),
(7, 2, 'Tipo de observación', 'select', 'Conducta segura|Condición insegura|Mejora propuesta', 1, 1),
(8, 2, 'Descripción', 'text', NULL, 1, 2),
(9, 2, 'Fecha', 'date', NULL, 1, 3),
(10, 2, 'Requiere acción inmediata', 'checkbox', 'Sí|No', 1, 4),
(11, 2, 'Responsable sugerido', 'text', NULL, 0, 5),
(12, 3, 'Fecha del reporte', 'date', NULL, 1, 1),
(13, 3, 'Tipo de condición', 'select', 'Segura|Insegura|Oportunidad de mejora', 1, 2),
(14, 3, 'Nivel de riesgo', 'number', NULL, 1, 3),
(15, 3, 'Descripción del reporte', 'text', NULL, 1, 4),
(16, 3, 'Confirmación de revisión', 'checkbox', 'Confirmado', 1, 5),
(17, 3, 'Evidencia opcional', 'file', NULL, 0, 6),
(18, 4, 'Equipo inspeccionado', 'select', 'Extintor|Red húmeda|Botiquín|DEA|Ducha de emergencia', 1, 1),
(19, 4, 'Cantidad revisada', 'number', NULL, 1, 2),
(20, 4, 'Fecha de inspección', 'date', NULL, 1, 3),
(21, 4, 'Estado general', 'select', 'Operativo|Observado|Fuera de servicio', 1, 4),
(22, 4, 'Observaciones', 'text', NULL, 0, 5),
(23, 4, 'Fotografía o acta', 'file', NULL, 0, 6),
(24, 5, 'Puesto de trabajo', 'text', NULL, 1, 1),
(25, 5, 'Nivel de molestia', 'select', 'Sin molestia|Leve|Moderada|Alta', 1, 2),
(26, 5, 'Comentarios', 'text', NULL, 0, 3),
(27, 6, 'Fecha de evaluación', 'date', NULL, 1, 1),
(28, 6, 'Área o tarea evaluada', 'text', NULL, 1, 2),
(29, 6, 'Exposición identificada', 'select', 'Sí|No|Por evaluar', 1, 3),
(30, 6, 'Controles implementados', 'checkbox', 'Control de ingeniería|Control administrativo|EPP|Sin control registrado', 1, 4),
(31, 6, 'Observaciones', 'text', NULL, 1, 5),
(32, 6, 'Evidencia documental', 'file', NULL, 0, 6),
(33, 7, 'Fecha de revisión', 'date', NULL, 1, 1),
(34, 7, 'Unidad o área', 'text', NULL, 1, 2),
(35, 7, 'Estado de gestión', 'select', 'Sin observaciones|Con acciones|Requiere seguimiento', 1, 3),
(36, 7, 'Áreas de acción', 'checkbox', 'Comunicación|Carga de trabajo|Apoyo de jefatura|Organización del trabajo', 1, 4),
(37, 7, 'Observaciones de seguimiento', 'text', NULL, 1, 5),
(38, 8, 'Fecha del registro', 'date', NULL, 1, 1),
(39, 8, 'Área evaluada', 'text', NULL, 1, 2),
(40, 8, 'Fuente de ruido identificada', 'text', NULL, 1, 3),
(41, 8, 'Existe medición disponible', 'select', 'Sí|No|Pendiente', 1, 4),
(42, 8, 'Observaciones PREXOR', 'text', NULL, 0, 5),
(43, 8, 'Documento de respaldo', 'file', NULL, 0, 6),
(44, 9, 'Fecha de aplicación', 'date', NULL, 1, 1),
(45, 9, 'Alcance de revisión', 'text', NULL, 1, 2),
(46, 9, 'Estado documental', 'select', 'Completo|Parcial|Pendiente', 1, 3),
(47, 9, 'Observaciones de aplicación', 'text', NULL, 0, 4);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `dynamic_form_submissions`
--

CREATE TABLE `dynamic_form_submissions` (
  `id_submission` int(11) NOT NULL,
  `id_form` int(11) NOT NULL,
  `id_company` int(11) NOT NULL,
  `id_users` varchar(50) NOT NULL,
  `status` enum('submitted','voided') NOT NULL DEFAULT 'submitted',
  `submitted_at` datetime NOT NULL,
  `created_by` varchar(50) NOT NULL,
  `date_create` datetime NOT NULL,
  `last_update` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `dynamic_form_submissions`
--

INSERT INTO `dynamic_form_submissions` (`id_submission`, `id_form`, `id_company`, `id_users`, `status`, `submitted_at`, `created_by`, `date_create`, `last_update`) VALUES
(1, 1, 4, 'adminEmpresaDemo@demoSCT.cl', 'submitted', '2026-09-08 18:00:00', 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 18:00:00', '2026-09-08 18:00:00'),
(2, 1, 4, 'JefaturaEmpresaDemo@demoSCT.cl', 'submitted', '2026-09-08 18:12:00', 'JefaturaEmpresaDemo@demoSCT.cl', '2026-09-08 18:12:00', '2026-09-08 18:12:00'),
(3, 1, 4, 'UsuarioEmpresaDemo@demoSCT.cl', 'submitted', '2026-09-08 18:25:00', 'UsuarioEmpresaDemo@demoSCT.cl', '2026-09-08 18:25:00', '2026-09-08 18:25:00'),
(4, 2, 4, 'GerenteEmpresaDemo@demoSCT.cl', 'submitted', '2026-09-08 18:35:00', 'GerenteEmpresaDemo@demoSCT.cl', '2026-09-08 18:35:00', '2026-09-08 18:35:00'),
(5, 2, 4, 'JefaturaEmpresaDemo@demoSCT.cl', 'submitted', '2026-09-08 18:42:00', 'JefaturaEmpresaDemo@demoSCT.cl', '2026-09-08 18:42:00', '2026-09-08 18:42:00'),
(6, 2, 4, 'UsuarioEmpresaDemo@demoSCT.cl', 'submitted', '2026-09-08 18:50:00', 'UsuarioEmpresaDemo@demoSCT.cl', '2026-09-08 18:50:00', '2026-09-08 18:50:00'),
(7, 3, 4, 'GerenteEmpresaDemo@demoSCT.cl', 'submitted', '2026-09-08 19:00:00', 'GerenteEmpresaDemo@demoSCT.cl', '2026-09-08 19:00:00', '2026-09-08 19:00:00'),
(8, 3, 4, 'JefaturaEmpresaDemo@demoSCT.cl', 'submitted', '2026-09-08 19:08:00', 'JefaturaEmpresaDemo@demoSCT.cl', '2026-09-08 19:08:00', '2026-09-08 19:08:00'),
(9, 3, 4, 'UsuarioEmpresaDemo@demoSCT.cl', 'submitted', '2026-09-08 19:16:00', 'UsuarioEmpresaDemo@demoSCT.cl', '2026-09-08 19:16:00', '2026-09-08 19:16:00'),
(10, 3, 4, 'adminEmpresaDemo@demoSCT.cl', 'submitted', '2026-09-08 19:24:00', 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 19:24:00', '2026-09-08 19:24:00'),
(11, 7, 4, 'GerenteEmpresaDemo@demoSCT.cl', 'submitted', '2026-09-04 15:20:00', 'GerenteEmpresaDemo@demoSCT.cl', '2026-09-04 15:20:00', '2026-09-04 15:20:00'),
(12, 6, 4, 'adminEmpresaDemo@demoSCT.cl', 'submitted', '2026-09-06 15:30:00', 'adminEmpresaDemo@demoSCT.cl', '2026-09-06 15:30:00', '2026-09-06 15:30:00'),
(13, 6, 4, 'GerenteEmpresaDemo@demoSCT.cl', 'submitted', '2026-09-08 19:30:00', 'GerenteEmpresaDemo@demoSCT.cl', '2026-09-08 19:30:00', '2026-09-08 19:30:00'),
(14, 2, 4, 'adminEmpresaDemo@demoSCT.cl', 'submitted', '2026-08-29 15:20:00', 'adminEmpresaDemo@demoSCT.cl', '2026-08-29 15:20:00', '2026-08-29 15:20:00');

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
  `identifier` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
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
(13, 'francisco.fredes@engie.com', '98.98.28.182', 1, '2026-09-08 12:22:49'),
(14, 'juanantonioconchaloyola@gmail.com', '127.0.0.1', 1, '2026-09-08 12:35:41'),
(15, 'admin.completo.test@test.helheim.cl', '127.0.0.1', 1, '2026-09-08 12:36:17'),
(16, 'admin.test@test.engie.cl', '127.0.0.1', 1, '2026-09-08 12:38:17'),
(17, 'juanantonioconchaloyola@gmail.com', '127.0.0.1', 1, '2026-09-08 13:22:39'),
(18, 'juanantonioconchaloyola@gmail.com', '127.0.0.1', 1, '2026-09-08 13:35:14'),
(19, 'malazga99@gmail.com', '127.0.0.1', 0, '2026-09-08 13:42:22'),
(20, 'malazga99@gmail.com', '127.0.0.1', 0, '2026-09-08 13:44:08'),
(21, 'fco.fredes.g@gmail.com', '127.0.0.1', 1, '2026-09-08 13:44:45'),
(22, 'fco.fredes.g@gmail.com', '127.0.0.1', 1, '2026-09-08 13:45:35'),
(23, 'malazga99@gmail.com', '127.0.0.1', 0, '2026-09-08 13:48:28'),
(24, 'juanantonioconchaloyola@gmail.com', '127.0.0.1', 0, '2026-09-08 13:56:41'),
(25, 'juanantonioconchaloyola@gmail.com', '127.0.0.1', 0, '2026-09-08 14:05:56'),
(26, 'juanantonioconchaloyola@gmail.com', '127.0.0.1', 0, '2026-09-08 14:08:03'),
(27, 'juanantonioconchaloyola@gmail.com', '127.0.0.1', 0, '2026-09-08 14:08:23'),
(28, 'fco.fredes.g@gmail.com', '127.0.0.1', 0, '2026-09-08 14:08:47'),
(29, 'juanantonioconchaloyola@gmail.com', '127.0.0.1', 0, '2026-09-08 14:35:30'),
(30, 'juanantonioconchaloyola@gmail.com', '127.0.0.1', 1, '2026-09-08 14:37:55'),
(31, 'juanantonioconchaloyola@gmail.com', '127.0.0.1', 1, '2026-09-08 14:41:05'),
(32, 'fco.fredes.g@gmail.com', '127.0.0.1', 1, '2026-09-08 14:44:10'),
(33, 'malazga99@gmail.com', '127.0.0.1', 1, '2026-09-08 14:46:44'),
(34, 'pablotroncoso@gmail.com', '127.0.0.1', 0, '2026-09-08 14:47:58'),
(35, 'pablotroncoso@gmail.com', '127.0.0.1', 1, '2026-09-08 14:48:40'),
(36, 'juanantonioconchaloyola@gmail.com', '127.0.0.1', 1, '2026-09-08 14:53:50'),
(37, 'juanantonioconchaloyola@gmail.com', '127.0.0.1', 1, '2026-09-08 14:58:01'),
(38, 'admin.completo.test@test.helheim.cl', '127.0.0.1', 1, '2026-09-08 15:00:26'),
(39, 'juanantonioconchaloyola@gmail.com', '127.0.0.1', 1, '2026-09-08 15:05:24'),
(40, 'juanantonioconchaloyola@gmail.com', '127.0.0.1', 1, '2026-09-08 15:15:29'),
(41, 'juanantonioconchaloyola@gmail.com', '127.0.0.1', 1, '2026-09-08 15:29:13'),
(42, 'juanantonioconchaloyola@gmail.com', '127.0.0.1', 1, '2026-09-08 15:42:54'),
(43, 'juanantonioconchaloyola@gmail.com', '127.0.0.1', 1, '2026-09-08 15:43:12'),
(44, 'juanantonioconchaloyola@gmail.com', '127.0.0.1', 1, '2026-09-08 15:47:31'),
(45, 'malazga99@gmail.com', '127.0.0.1', 1, '2026-09-08 15:53:29'),
(46, 'juanantonioconchaloyola@gmail.com', '127.0.0.1', 1, '2026-09-08 15:58:37'),
(47, 'juanantonioconchaloyola@gmail.com', '127.0.0.1', 1, '2026-09-08 16:02:39'),
(48, 'juanantonioconchaloyola@gmail.com', '127.0.0.1', 1, '2026-09-08 16:20:24'),
(49, 'juanantonioconchaloyola@gmail.com', '127.0.0.1', 1, '2026-09-08 16:36:56'),
(50, 'juanantonioconchaloyola@gmail.com', '127.0.0.1', 1, '2026-09-08 16:45:43'),
(51, 'juanantonioconchaloyola@gmail.com', '127.0.0.1', 1, '2026-09-08 16:47:32'),
(52, 'juanantonioconchaloyola@gmail.com', '127.0.0.1', 1, '2026-09-08 18:27:02'),
(53, 'juanantonioconchaloyola@gmail.com', '127.0.0.1', 1, '2026-09-08 18:45:40'),
(54, 'adminempresademo@demosct.cl', '127.0.0.1', 1, '2026-09-08 18:49:16'),
(55, 'gerenteempresademo@demosct.cl', '127.0.0.1', 0, '2026-09-08 18:51:22'),
(56, 'gerenteempresademo@demosct.cl', '127.0.0.1', 1, '2026-09-08 18:51:34'),
(57, 'jefaturaempresademo@demosct.cl', '127.0.0.1', 0, '2026-09-08 18:52:54'),
(58, 'jefaturaempresademo@demosct.cl', '127.0.0.1', 1, '2026-09-08 18:53:07'),
(59, 'usuarioempresademo@demosct.cl', '127.0.0.1', 1, '2026-09-08 18:54:10'),
(60, 'gerenteempresademo@demosct.cl', '127.0.0.1', 1, '2026-09-08 19:25:43'),
(61, 'juanantonioconchaloyola@gmail.com', '127.0.0.1', 1, '2026-09-08 19:26:42'),
(62, 'juanantonioconchaloyola@gmail.com', '127.0.0.1', 1, '2026-09-08 19:54:43'),
(63, 'juanantonioconchaloyola@gmail.com', '127.0.0.1', 1, '2026-09-08 20:20:18'),
(64, 'juanantonioconchaloyola@gmail.com', '127.0.0.1', 1, '2026-09-08 20:44:26'),
(65, 'juanantonioconchaloyola@gmail.com', '127.0.0.1', 1, '2026-09-08 23:11:08'),
(66, 'gerenteempresademo@demosct.cl', '127.0.0.1', 1, '2026-09-08 23:12:02'),
(67, 'juanantonioconchaloyola@gmail.com', '127.0.0.1', 1, '2026-09-08 23:29:58'),
(68, 'juanantonioconchaloyola@gmail.com', '127.0.0.1', 1, '2026-09-09 00:29:51'),
(69, 'juanantonioconchaloyola@gmail.com', '127.0.0.1', 1, '2026-09-09 01:13:32'),
(70, 'juanantonioconchaloyola@gmail.com', '127.0.0.1', 1, '2026-09-09 01:17:49'),
(71, 'juanantonioconchaloyola@gmail.com', '127.0.0.1', 1, '2026-09-09 01:27:26'),
(72, 'gerenteempresademo@demosct.cl', '127.0.0.1', 1, '2026-09-09 01:28:01'),
(73, 'gerenteempresademo@demosct.cl', '127.0.0.1', 1, '2026-09-09 13:57:06'),
(74, 'gerenteempresademo@demosct.cl', '127.0.0.1', 1, '2026-09-09 14:48:03'),
(75, 'juanantonioconchaloyola@gmail.com', '127.0.0.1', 1, '2026-09-09 14:48:59'),
(76, 'juanantonioconchaloyola@gmail.com', '127.0.0.1', 1, '2026-09-09 15:11:39'),
(77, 'gerenteempresademo@demosct.cl', '127.0.0.1', 1, '2026-09-09 15:12:02'),
(78, 'gerenteempresademo@demosct.cl', '127.0.0.1', 1, '2026-09-09 18:14:47'),
(79, 'juanantonioconchaloyola@gmail.com', '127.0.0.1', 1, '2026-09-09 18:15:29'),
(80, 'juanantonioconchaloyola@gmail.com', '127.0.0.1', 1, '2026-09-09 18:18:52'),
(81, 'gerenteempresademo@demosct.cl', '127.0.0.1', 1, '2026-09-09 19:50:31'),
(82, 'gerenteempresademo@demosct.cl', '127.0.0.1', 1, '2026-09-10 00:35:30'),
(83, 'gerenteempresademo@demosct.cl', '127.0.0.1', 1, '2026-09-10 00:38:12'),
(84, 'gerenteempresademo@demosct.cl', '127.0.0.1', 1, '2026-09-10 00:52:04'),
(85, 'juanantonioconchaloyola@gmail.com', '127.0.0.1', 1, '2026-09-10 01:01:13'),
(86, 'gerenteempresademo@demosct.cl', '127.0.0.1', 1, '2026-09-10 15:00:45'),
(87, 'gerenteempresademo@demosct.cl', '179.4.50.96', 1, '2026-09-10 15:11:13'),
(88, 'gerenteempresademo@demosct.cl', '179.4.50.96', 1, '2026-09-10 15:16:15'),
(89, 'gerenteempresademo@demosct.cl', '190.162.229.170', 1, '2026-09-10 16:32:25'),
(90, 'gerenteempresademo@demosct.cl', '179.4.50.96', 1, '2026-09-10 19:18:07'),
(91, 'gerenteempresademo@demosct.cl', '179.4.50.96', 1, '2026-09-10 19:21:02'),
(92, 'jefaturaempresademo@demosct.cl', '179.4.50.96', 1, '2026-09-10 19:21:23'),
(93, 'usuarioempresademo@demosct.cl', '179.4.50.96', 1, '2026-09-10 19:23:09'),
(94, 'adminempresademo@demosct.cl', '179.4.50.96', 1, '2026-09-10 19:23:30'),
(95, 'juanantonioconchaloyola@gmail.com', '179.4.50.96', 1, '2026-09-10 19:24:28'),
(96, 'gerenteempresademo@demosct.cl', '179.4.50.96', 1, '2026-09-10 19:40:15'),
(97, 'adminempresademo@demosct.cl', '179.4.50.96', 1, '2026-09-10 19:41:13'),
(98, 'juanantonioconchaloyola@gmail.com', '179.4.50.96', 1, '2026-09-10 19:42:20'),
(99, 'gerenteempresademo@demosct.cl', '179.4.50.96', 1, '2026-09-10 19:54:03'),
(100, 'gerenteempresademo@demosct.cl', '179.4.50.96', 1, '2026-09-10 19:57:34'),
(101, 'gerenteempresademo@demosct.cl', '179.4.50.96', 1, '2026-09-10 20:18:15'),
(102, 'gerenteempresademo@demosct.cl', '179.4.50.96', 1, '2026-09-10 20:29:20'),
(103, 'gerenteempresademo@demosct.cl', '179.4.50.96', 1, '2026-09-10 20:29:48'),
(104, 'gerenteempresademo@demosct.cl', '179.4.50.96', 1, '2026-09-10 20:36:00'),
(105, 'gerenteempresademo@demosct.cl', '179.4.50.96', 1, '2026-09-11 11:03:12'),
(106, 'jefaturaempresademo@demosct.cl', '179.4.50.96', 1, '2026-09-11 11:03:35'),
(107, 'gerenteempresademo@demosct.cl', '179.4.50.96', 1, '2026-09-11 11:10:23'),
(108, 'gerenteempresademo@demosct.cl', '179.4.50.96', 1, '2026-09-11 13:09:49'),
(109, 'gerenteempresademo@demosct.cl', '179.4.50.96', 1, '2026-09-11 16:40:05'),
(110, 'adminempresademo@demosct.cl', '179.4.50.96', 1, '2026-09-11 16:43:23'),
(111, 'juanantonioconchaloyola@gmail.com', '179.4.50.96', 1, '2026-09-11 16:44:09'),
(112, 'usuarioempresademo@demosct.cl', '179.4.50.96', 1, '2026-09-11 16:50:36');

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
(13, 'Francisco.fredes@engie.com', '$2y$10$mlB2yhiYN5O5ewasQSF9pO/0SVt4ghySpJdskqf6siQHpqvEoAJ7C', '2026-09-08 15:32:19', '2026-09-08 12:22:49', 0, '98.98.28.182', '2026-09-08 12:22:19'),
(14, 'juanantonioconchaloyola@gmail.com', '$2y$10$C8szshpSVMdqhqC3X3we4uIk/eMhb1BHMJaI6oOqIcHACeoihD.R6', '2026-09-08 17:45:36', '2026-09-08 12:35:41', 0, '127.0.0.1', '2026-09-08 12:35:36'),
(15, 'admin.completo.test@test.helheim.cl', '$2y$10$BDqZTxK3PDEavmzHQg4QleqGl8xDD/2Aa2ZDpWMussDkT/5kWEgLu', '2026-09-08 17:46:11', '2026-09-08 12:36:17', 0, '127.0.0.1', '2026-09-08 12:36:11'),
(16, 'admin.test@test.engie.cl', '$2y$10$1Kl0eidiCnj3WF3K.PA.EOsxGfpvF2O4VLweuyF.purAW.0FvyqWu', '2026-09-08 17:48:12', '2026-09-08 12:38:17', 0, '127.0.0.1', '2026-09-08 12:38:12'),
(17, 'juanantonioconchaloyola@gmail.com', '$2y$10$5zCx911mYJslnA/oLSJ36utt/EJ3vGt84TUxTQRHCHqKdz.Rrhx3G', '2026-09-08 18:32:33', '2026-09-08 13:22:39', 0, '127.0.0.1', '2026-09-08 13:22:33'),
(18, 'juanantonioconchaloyola@gmail.com', '$2y$10$qC4N6dOh4qpl905uLz2wX.uuOVcsklngTuspCVsIkC0UJbYlQVacG', '2026-09-08 18:45:06', '2026-09-08 13:35:14', 0, '127.0.0.1', '2026-09-08 13:35:06'),
(19, 'fco.fredes.g@gmail.com', '$2y$10$IjWZZJ/F3Dj.k0Z9R1yk4.HKF5b9bb6hy0brZSEcoEdNfkxvXNefi', '2026-09-08 18:54:40', '2026-09-08 13:44:45', 0, '127.0.0.1', '2026-09-08 13:44:40'),
(20, 'fco.fredes.g@gmail.com', '$2y$10$Cn0bha.Z.TslZZaLJxeW1.kAQc6xk07bYmvgTwIQuIfLDS6WDE24C', '2026-09-08 18:55:30', '2026-09-08 13:45:35', 0, '127.0.0.1', '2026-09-08 13:45:30'),
(21, 'juanantonioconchaloyola@gmail.com', '$2y$10$ZLH/pT10ouFKY5ovUIANNeazmW7HWntyo84TqeXdMhf.AKueLzcMi', '2026-09-08 19:47:43', '2026-09-08 14:37:55', 0, '127.0.0.1', '2026-09-08 14:37:43'),
(22, 'juanantonioconchaloyola@gmail.com', '$2y$10$bG9ph3p5DN8XuQU2T/KfjOY.5I61KTYzwGc.kXQ29M.RYhynKP.Si', '2026-09-08 19:50:59', '2026-09-08 14:41:05', 0, '127.0.0.1', '2026-09-08 14:40:59'),
(23, 'fco.fredes.g@gmail.com', '$2y$10$KbP5O9a2Xah3XX3kunptweSoosGCk9ISh3HL/RryxdEZhvZNf147i', '2026-09-08 19:54:05', '2026-09-08 14:44:10', 0, '127.0.0.1', '2026-09-08 14:44:05'),
(24, 'malazga99@gmail.com', '$2y$10$Q9CbI.szHOqG8PvdYa6zqO6AJydYn73ty/jWApa5i8tCr8VeiWB1y', '2026-09-08 19:56:40', '2026-09-08 14:46:44', 0, '127.0.0.1', '2026-09-08 14:46:40'),
(25, 'pablotroncoso@gmail.com', '$2y$10$alepPfWu9x/gQOOmSqnn2eM0d0nI4TGmihItGdjOcgdx/GhPR382.', '2026-09-08 19:58:36', '2026-09-08 14:48:40', 0, '127.0.0.1', '2026-09-08 14:48:36'),
(26, 'juanantonioconchaloyola@gmail.com', '$2y$10$KIwap4pYhc7.9STtSYP1w.ljgfvhHuRIy9oTvhXUnWar4828BsAKW', '2026-09-08 20:03:45', '2026-09-08 14:53:50', 0, '127.0.0.1', '2026-09-08 14:53:45'),
(27, 'juanantonioconchaloyola@gmail.com', '$2y$10$/P7aTJHHUXFGESGuadXt9.uAyVt/xRxF92udApRkIHvCPGI0gbsDG', '2026-09-08 20:07:56', '2026-09-08 14:58:01', 0, '127.0.0.1', '2026-09-08 14:57:56'),
(28, 'admin.completo.test@test.helheim.cl', '$2y$10$Y/wfK5TieL.bKNHtnACi5eLeFPFvCLn0uZOE5KBEiHaHlOPh6VQuy', '2026-09-08 20:10:19', '2026-09-08 15:00:26', 0, '127.0.0.1', '2026-09-08 15:00:19'),
(29, 'juanantonioconchaloyola@gmail.com', '$2y$10$pV/hAjDFmQjy2RyhrJ3Sn.ZTijOTf/RChkLEQO3hetOmh/Ky6PhX6', '2026-09-08 20:15:20', '2026-09-08 15:05:24', 0, '127.0.0.1', '2026-09-08 15:05:20'),
(30, 'juanantonioconchaloyola@gmail.com', '$2y$10$MqX3V4L3V7Insheqi2jsIelf9BL2hm4B5PcYr/HqVPYZYhVcWoC5W', '2026-09-08 20:25:25', '2026-09-08 15:15:29', 0, '127.0.0.1', '2026-09-08 15:15:25'),
(31, 'juanantonioconchaloyola@gmail.com', '$2y$10$nGZdhF8e8qzGNyyV.KDyyudxOtCSda5xYNZ7tTQeJtubTOawPYR.y', '2026-09-08 20:39:08', '2026-09-08 15:29:13', 0, '127.0.0.1', '2026-09-08 15:29:08'),
(32, 'juanantonioconchaloyola@gmail.com', '$2y$10$98PleIt54.QIGEekFddoSec3zQ0/JBr4/LtQxG62B83Un5rMDzRB6', '2026-09-08 20:52:48', '2026-09-08 15:42:54', 0, '127.0.0.1', '2026-09-08 15:42:48'),
(33, 'juanantonioconchaloyola@gmail.com', '$2y$10$2nDLmpo.XDHa2XsSxm.2I.jWSYDKrVEDH68VWL07YCjKmtkRQZztu', '2026-09-08 20:53:07', '2026-09-08 15:43:12', 0, '127.0.0.1', '2026-09-08 15:43:07'),
(34, 'juanantonioconchaloyola@gmail.com', '$2y$10$yBvpro/T6Mas4NL2J0JwyunOQG3UVOciSJGanLkREzBO.d2ir2w.G', '2026-09-08 20:57:25', '2026-09-08 15:47:31', 0, '127.0.0.1', '2026-09-08 15:47:25'),
(35, 'malazga99@gmail.com', '$2y$10$Bn9O2olmzZJLC8rOpIJ7ue9EBhQ853JScKnBc7e/SYELkKcqHezPS', '2026-09-08 21:03:24', '2026-09-08 15:53:29', 0, '127.0.0.1', '2026-09-08 15:53:24'),
(36, 'juanantonioconchaloyola@gmail.com', '$2y$10$2YMQST.1bxKDkvt/Rd6lkO0W50wuSp1xutt3N9TsvsG6jYoCHkRs2', '2026-09-08 21:08:31', '2026-09-08 15:58:37', 0, '127.0.0.1', '2026-09-08 15:58:31'),
(37, 'juanantonioconchaloyola@gmail.com', '$2y$10$JUNYY39WKZ3l.iYJeJD3h.oACx8rEIW4ebK6/ksZDlh2dCZF.tsg6', '2026-09-08 21:12:27', '2026-09-08 16:02:39', 0, '127.0.0.1', '2026-09-08 16:02:27'),
(38, 'juanantonioconchaloyola@gmail.com', '$2y$10$qtEmNl3..kHjSQroiuwiqumSdXfRV2eJ9kVuPPD5oqSXLLims3RjG', '2026-09-08 21:30:20', '2026-09-08 16:20:24', 0, '127.0.0.1', '2026-09-08 16:20:20'),
(39, 'juanantonioconchaloyola@gmail.com', '$2y$10$K/rtLGFqxtxN6uwPdPbF6OdmuTanakycRgFtRVxhYJlqPdlqh0UXy', '2026-09-08 21:46:42', '2026-09-08 16:36:56', 0, '127.0.0.1', '2026-09-08 16:36:42'),
(40, 'juanantonioconchaloyola@gmail.com', '$2y$10$nGG6f7XB7Fx9/ACUdKAHJ.PLRWqZs1gvrdjtaLlj1DFMO9S4sJH3a', '2026-09-08 21:55:38', '2026-09-08 16:45:43', 0, '127.0.0.1', '2026-09-08 16:45:38'),
(41, 'juanantonioconchaloyola@gmail.com', '$2y$10$4iJAyMvjtMe2llZtnJLWJucTTIs7a3FoyINhatx6oNN68IHWsEBYq', '2026-09-08 21:57:28', '2026-09-08 16:47:32', 0, '127.0.0.1', '2026-09-08 16:47:28'),
(42, 'juanantonioconchaloyola@gmail.com', '$2y$10$gZqaECnsyKwMLiXuvImvL.FPg3yR7WxiXO7q572F/CR2QQY4OQ1Wy', '2026-09-08 23:36:57', '2026-09-08 18:27:02', 0, '127.0.0.1', '2026-09-08 18:26:57'),
(43, 'juanantonioconchaloyola@gmail.com', '$2y$10$FCj4QrETZG.TGY2kV8tJz.Ng8z5Y7maMiJmcpWTtlUR6P/z68knz2', '2026-09-08 23:55:31', '2026-09-08 18:45:40', 0, '127.0.0.1', '2026-09-08 18:45:31'),
(44, 'adminEmpresaDemo@demoSCT.cl', '$2y$10$2vw1va6mC3nARj7p.f7BbezTOLF5hNCt2w2w7z5GCGe/vwGI4YJ8m', '2026-09-08 23:59:12', '2026-09-08 18:49:16', 0, '127.0.0.1', '2026-09-08 18:49:12'),
(45, 'GerenteEmpresaDemo@demoSCT.cl', '$2y$10$A/RrWrgaTJum9AB58Ql83eywZSpipqeOTlIHlZvthLSjYF79BjA0C', '2026-09-09 00:01:29', '2026-09-08 18:51:34', 0, '127.0.0.1', '2026-09-08 18:51:29'),
(46, 'JefaturaEmpresaDemo@demoSCT.cl', '$2y$10$I8YbnkXRjrJvJ1QBrM/V6uhPf/HWXHydu5OYhVKK6L7ghsTz4qY9G', '2026-09-09 00:03:01', '2026-09-08 18:53:07', 0, '127.0.0.1', '2026-09-08 18:53:01'),
(47, 'UsuarioEmpresaDemo@demoSCT.cl', '$2y$10$ePigd7cCM7rPoBF7eKPa8.WTzN1GuR/deoHQbwDd9uRRCMBg8qv1G', '2026-09-09 00:04:05', '2026-09-08 18:54:10', 0, '127.0.0.1', '2026-09-08 18:54:05'),
(48, 'juanantonioconchaloyola@gmail.com', '$2y$10$86j0UH4RhrgDm0KZddWSde9oYKXiQOwDjzRYy6r.1Z0dpoZo.L6f6', '2026-09-09 00:25:19', '2026-09-08 19:26:39', 0, '127.0.0.1', '2026-09-08 19:15:19'),
(49, 'GerenteEmpresaDemo@demoSCT.cl', '$2y$10$cfLHskg88MpNMaWOoYJNu.g9scuNoS3Vz6uF2Xn/a87.RRhhmouPq', '2026-09-09 00:26:27', '2026-09-08 19:25:43', 0, '127.0.0.1', '2026-09-08 19:16:27'),
(50, 'juanantonioconchaloyola@gmail.com', '$2y$10$6M9nEGNzq0DqUyc0RSFKH.h/yCh9MbM5fGW6QnTkCbCLHCqS7NAxy', '2026-09-09 00:36:39', '2026-09-08 19:26:42', 0, '127.0.0.1', '2026-09-08 19:26:39'),
(51, 'juanantonioconchaloyola@gmail.com', '$2y$10$L3ZRHUrGACuoiW07uYojbek4JXOFCx3L3Zmb0qXA2qZKvAs/ENJ1y', '2026-09-09 01:04:38', '2026-09-08 19:54:43', 0, '127.0.0.1', '2026-09-08 19:54:38'),
(52, 'juanantonioconchaloyola@gmail.com', '$2y$10$hFXkfD9T2dyXO2JLSeBj3e.jt.f8ReFz7SFkc8U4XHt7EYCSStkve', '2026-09-09 01:30:15', '2026-09-08 20:20:18', 0, '127.0.0.1', '2026-09-08 20:20:15'),
(53, 'juanantonioconchaloyola@gmail.com', '$2y$10$CrPT7ZYDxqtCQlacippGPebfoAhlt8hUsvzYg9NKzSUx3dE1jWuFC', '2026-09-09 01:54:21', '2026-09-08 20:44:26', 0, '127.0.0.1', '2026-09-08 20:44:21'),
(54, 'juanantonioconchaloyola@gmail.com', '$2y$10$/id5ZffcwFTtHmDpgF0X1eMSvvcRk3yWVrKXUkG.Iq1PHarHvunsm', '2026-09-09 04:21:04', '2026-09-08 23:11:08', 0, '127.0.0.1', '2026-09-08 23:11:04'),
(55, 'GerenteEmpresaDemo@demoSCT.cl', '$2y$10$/aI7pjmH92cbKrYAwulIzOshXR7FRQ6R/WPMrw1.EBshvUgcL8Awu', '2026-09-09 04:21:59', '2026-09-08 23:12:02', 0, '127.0.0.1', '2026-09-08 23:11:59'),
(56, 'juanantonioconchaloyola@gmail.com', '$2y$10$QdwFYa0UZpPPdMTZa1e3U.tZdN4XTEn3lBaKvODRUYy9DLYt9roW.', '2026-09-09 04:39:54', '2026-09-08 23:29:58', 0, '127.0.0.1', '2026-09-08 23:29:54'),
(57, 'juanantonioconchaloyola@gmail.com', '$2y$10$3BfNNmSrWNmHoAzaGCJD1.stO4tVYoggIM9mbP6RgzYZ/OYTFJieW', '2026-09-09 05:39:47', '2026-09-09 00:29:51', 0, '127.0.0.1', '2026-09-09 00:29:47'),
(58, 'juanantonioconchaloyola@gmail.com', '$2y$10$YOvirsAGT2xCbDuXdT2BMewwtjAvFSEtjPamlKc8jwWhQY8eEjLFW', '2026-09-09 06:23:28', '2026-09-09 01:13:32', 0, '127.0.0.1', '2026-09-09 01:13:28'),
(59, 'juanantonioconchaloyola@gmail.com', '$2y$10$HLyzIeL1LGz5jGdNsPXKpuZWogeyei6A..Nb0/rbrf7BflL8APYRG', '2026-09-09 06:27:46', '2026-09-09 01:17:49', 0, '127.0.0.1', '2026-09-09 01:17:46'),
(60, 'juanantonioconchaloyola@gmail.com', '$2y$10$U0ckTNiOs9SxIL09rfySWOLFw3Jl9O9VJFb.lz5AjvCSbFCRfT9Mm', '2026-09-09 06:37:22', '2026-09-09 01:27:26', 0, '127.0.0.1', '2026-09-09 01:27:22'),
(61, 'GerenteEmpresaDemo@demoSCT.cl', '$2y$10$Tj8obsHJjfO5EfsBOArOw.gZ3VTKJrfOnufjVJxGLgUiUeQ4x.GnS', '2026-09-09 06:37:57', '2026-09-09 01:28:01', 0, '127.0.0.1', '2026-09-09 01:27:57'),
(62, 'GerenteEmpresaDemo@demoSCT.cl', '$2y$10$ijWuWuf1QULaJomXAWElIOxn0g7663yDxxgUeCmcn8P/0L4EBD3M.', '2026-09-09 19:07:02', '2026-09-09 13:57:06', 0, '127.0.0.1', '2026-09-09 13:57:02'),
(63, 'GerenteEmpresaDemo@demoSCT.cl', '$2y$10$Ykx2HXZJCwci7z6NYW1yLevPurTF4/W2rFg775ypchY4aQ4m27Tuu', '2026-09-09 19:57:58', '2026-09-09 14:48:03', 0, '127.0.0.1', '2026-09-09 14:47:58'),
(64, 'juanantonioconchaloyola@gmail.com', '$2y$10$eVTmwePPPuEhmI37kQFdDuEndzsKhDzY/8TrxuV27SBvYaP7OBogq', '2026-09-09 19:58:55', '2026-09-09 14:48:59', 0, '127.0.0.1', '2026-09-09 14:48:55'),
(65, 'juanantonioconchaloyola@gmail.com', '$2y$10$wxN7CTL4u.3fAeOZji7HiOsq9ogP2EO2PQO8tV.LFvQBXRcSCssfa', '2026-09-09 20:21:35', '2026-09-09 15:11:39', 0, '127.0.0.1', '2026-09-09 15:11:35'),
(66, 'GerenteEmpresaDemo@demoSCT.cl', '$2y$10$qISZgDWSzfRTvB3XQHG20OvTTKpeCfhHjByvRlBO0FohKzedAkgX6', '2026-09-09 20:21:58', '2026-09-09 15:12:02', 0, '127.0.0.1', '2026-09-09 15:11:58'),
(67, 'GerenteEmpresaDemo@demoSCT.cl', '$2y$10$QfMQ/PNH22FGBYwQqG0yQeAvJ0TQGvJ2XJdjGW1oBpm0Q5dVnem4m', '2026-09-09 23:24:43', '2026-09-09 18:14:47', 0, '127.0.0.1', '2026-09-09 18:14:43'),
(68, 'juanantonioconchaloyola@gmail.com', '$2y$10$c9u6R2r5dhLiyJMarv8rhORLmy7xJz9A733EmdMRv75mLI/r2GxyS', '2026-09-09 23:25:24', '2026-09-09 18:15:29', 0, '127.0.0.1', '2026-09-09 18:15:24'),
(69, 'juanantonioconchaloyola@gmail.com', '$2y$10$OkTXcr1nCsYxhG/yl/AWve6Vt4OHQnez8/KByhp8mOeJgVrSE/0PC', '2026-09-09 23:28:47', '2026-09-09 18:18:52', 0, '127.0.0.1', '2026-09-09 18:18:47'),
(70, 'GerenteEmpresaDemo@demoSCT.cl', '$2y$10$KRuBRT87wtgkjXfnYzLCp.gaFSAlHqV/b5GAv7FvdTmsyc0d/MKvC', '2026-09-10 01:00:27', '2026-09-09 19:50:31', 0, '127.0.0.1', '2026-09-09 19:50:27'),
(71, 'GerenteEmpresaDemo@demoSCT.cl', '$2y$10$BHMCknf0MsE3hudJ2FRoOei9VvwrsD8AHkOS/EalCOKTazNlahvIi', '2026-09-10 05:45:26', '2026-09-10 00:35:30', 0, '127.0.0.1', '2026-09-10 00:35:26'),
(72, 'GerenteEmpresaDemo@demoSCT.cl', '$2y$10$EggatdU3krT/J2ZbC6EUQuAc4PJjQ2oUhxIx4jpWlSdGb6erjkEYi', '2026-09-10 05:48:08', '2026-09-10 00:38:12', 0, '127.0.0.1', '2026-09-10 00:38:08'),
(73, 'GerenteEmpresaDemo@demoSCT.cl', '$2y$10$2COOBDA2.GOKHqxIon4tiOLwRMx8jtgb1ieI7IJI/jy0MndlYoFtm', '2026-09-10 06:02:00', '2026-09-10 00:52:04', 0, '127.0.0.1', '2026-09-10 00:52:00'),
(74, 'juanantonioconchaloyola@gmail.com', '$2y$10$FlVubidYr2zMKOmOL/nWauPDZQKm6by5fZHWJ852zIy9HmD09Glwa', '2026-09-10 06:11:10', '2026-09-10 01:01:13', 0, '127.0.0.1', '2026-09-10 01:01:10'),
(75, 'GerenteEmpresaDemo@demoSCT.cl', '$2y$10$zuQgj5tfkD6Ll3kfLgl6VuS.kvGVcEtWxFFcp3wop52AyEC7LJ/UG', '2026-09-10 20:10:41', '2026-09-10 15:00:45', 0, '127.0.0.1', '2026-09-10 15:00:41'),
(76, 'GerenteEmpresaDemo@demoSCT.cl', '$2y$10$dS//uLKeLsgU9S0WtfxnY.t7dHWRyAcDpGCwV2.EvFFuNbILg3s9G', '2026-09-10 18:21:10', '2026-09-10 15:11:13', 0, '179.4.50.96', '2026-09-10 15:11:10'),
(77, 'GerenteEmpresaDemo@demoSCT.cl', '$2y$10$XRCGF49sO2WIQAV92qewNOe50i3mnzUgKUXB8KmIxHG.x371ljJdu', '2026-09-10 18:26:11', '2026-09-10 15:16:15', 0, '179.4.50.96', '2026-09-10 15:16:11'),
(78, 'GerenteEmpresaDemo@demoSCT.cl', '$2y$10$tR871PGf.EGOCQxbv8/sh.oVxMnvkvpCu.rSfQIlhdjF5zfZW277O', '2026-09-10 19:42:21', '2026-09-10 16:32:25', 0, '190.162.229.170', '2026-09-10 16:32:21'),
(79, 'GerenteEmpresaDemo@demoSCT.cl', '$2y$10$WGpYT1rAgZFjvjwEDiwaN.L1Fb2nhMU6ezeVhPZqUEzGyUbhkQnZK', '2026-09-10 22:28:03', '2026-09-10 19:18:07', 0, '179.4.50.96', '2026-09-10 19:18:03'),
(80, 'GerenteEmpresaDemo@demoSCT.cl', '$2y$10$2RgG/TfuVnTZQwv3EWwpY.uj/9VA5rLZk1exT6EOWGBhTSv08u4rm', '2026-09-10 22:30:57', '2026-09-10 19:21:02', 0, '179.4.50.96', '2026-09-10 19:20:57'),
(81, 'JefaturaEmpresaDemo@demoSCT.cl', '$2y$10$/wgENFKJ9upSarql.pq6MOrP.5/5qcK2SG/sNjApJzBJb2fhntCNS', '2026-09-10 22:31:19', '2026-09-10 19:21:23', 0, '179.4.50.96', '2026-09-10 19:21:19'),
(82, 'UsuarioEmpresaDemo@demoSCT.cl', '$2y$10$4.Isl5yNhShc1Hkz2t1K/.yFzhR0dgDsRcDAaIYjA7i4RwyG6wPfS', '2026-09-10 22:33:05', '2026-09-10 19:23:09', 0, '179.4.50.96', '2026-09-10 19:23:05'),
(83, 'adminEmpresaDemo@demoSCT.cl', '$2y$10$ju1IUSlradxnZqGTZk.gM.mW4FXei5gucTKNXkwT/VcOCm77i09U6', '2026-09-10 22:33:26', '2026-09-10 19:23:30', 0, '179.4.50.96', '2026-09-10 19:23:26'),
(84, 'juanantonioconchaloyola@gmail.com', '$2y$10$w5ErH41z96J29kCGiOFwN.exaGL.5meP9r6bW01Kw/H1xiKRDWDLe', '2026-09-10 22:34:09', '2026-09-10 19:24:28', 0, '179.4.50.96', '2026-09-10 19:24:09'),
(85, 'GerenteEmpresaDemo@demoSCT.cl', '$2y$10$JuGvYNg7sYJWa0zmAyWfLOK0V1nufbspwsBT9CZEdGlYCCyMpyYua', '2026-09-10 22:50:11', '2026-09-10 19:40:15', 0, '179.4.50.96', '2026-09-10 19:40:11'),
(86, 'adminEmpresaDemo@demoSCT.cl', '$2y$10$brPgTB31UGc3HaIvMrY53u8p3aU6RmGnUW28KVKDCqsKQL4lf8Q3.', '2026-09-10 22:51:10', '2026-09-10 19:41:13', 0, '179.4.50.96', '2026-09-10 19:41:10'),
(87, 'juanantonioconchaloyola@gmail.com', '$2y$10$LnuphLyOkKaGM.yRGMdvTOJBzxRzV4NzqRaPYYBu.xT4xb8tWFjrq', '2026-09-10 22:51:51', '2026-09-10 19:42:20', 0, '179.4.50.96', '2026-09-10 19:41:51'),
(88, 'GerenteEmpresaDemo@demoSCT.cl', '$2y$10$bbbNntcxJxoocc7gu9Q0fOWSgI3H4TWzXIAIIa9FPKpNZxM/Y4V2q', '2026-09-10 23:03:58', '2026-09-10 19:54:02', 0, '179.4.50.96', '2026-09-10 19:53:58'),
(89, 'GerenteEmpresaDemo@demoSCT.cl', '$2y$10$HXmfRYMqtX6vGyDheuO/VeRYHSh1hXVOOKWj9swSfArpywqzj.9Pq', '2026-09-10 23:07:31', '2026-09-10 19:57:34', 0, '179.4.50.96', '2026-09-10 19:57:31'),
(90, 'GerenteEmpresaDemo@demoSCT.cl', '$2y$10$nOP0CkBFGVqt7T9pihXuou7LulsozVOFWgbfgPQbMobiprAa.xJui', '2026-09-10 23:28:10', '2026-09-10 20:18:15', 0, '179.4.50.96', '2026-09-10 20:18:10'),
(91, 'GerenteEmpresaDemo@demoSCT.cl', '$2y$10$48q1.yXcNzYuzKlvzI9y5./QXgCvYlv2Iti2BouUWWsg8wLuf0m2u', '2026-09-10 23:39:16', '2026-09-10 20:29:20', 0, '179.4.50.96', '2026-09-10 20:29:16'),
(92, 'GerenteEmpresaDemo@demoSCT.cl', '$2y$10$aMYVk5/j.PrXhtCiE3GC6.cW3IWBuV72CSs0PY.1ZPp.NNtEWoI6y', '2026-09-10 23:39:44', '2026-09-10 20:29:48', 0, '179.4.50.96', '2026-09-10 20:29:44'),
(93, 'GerenteEmpresaDemo@demoSCT.cl', '$2y$10$2xL5A6oUep8YNreu2PftxOk2alNpEWQQAS4eyRhDdEwATYOEzecH.', '2026-09-10 23:45:56', '2026-09-10 20:36:00', 0, '179.4.50.96', '2026-09-10 20:35:56'),
(94, 'GerenteEmpresaDemo@demoSCT.cl', '$2y$10$FkTKBmI35d7OWGEjyDZK0OLxMdY2gKkp5Vyo02gKshAd8W3q4q4T6', '2026-09-11 14:13:06', '2026-09-11 11:03:12', 0, '179.4.50.96', '2026-09-11 11:03:06'),
(95, 'JefaturaEmpresaDemo@demoSCT.cl', '$2y$10$wG/duNURFrfIwL13BcNWjeAAKR4dadCn7159eBlP5I.1gon0OwWhK', '2026-09-11 14:13:32', '2026-09-11 11:03:35', 0, '179.4.50.96', '2026-09-11 11:03:32'),
(96, 'GerenteEmpresaDemo@demoSCT.cl', '$2y$10$plUak9f0pLPmkOdCaO9CKu7C3N5uHI00rrhisrCcmG/1yp5pm4kGu', '2026-09-11 14:20:18', '2026-09-11 11:10:23', 0, '179.4.50.96', '2026-09-11 11:10:18'),
(97, 'GerenteEmpresaDemo@demoSCT.cl', '$2y$10$mjLu0U3ngRAA41chmSHmBeceZaIQhg0cM8hiMfdtLi95kTbWXfTNa', '2026-09-11 16:19:46', '2026-09-11 13:09:49', 0, '179.4.50.96', '2026-09-11 13:09:46'),
(98, 'juanantonioconchaloyola@gmail.com', '$2y$10$feBS/GeG3WtdEMNvy5Kfa.SiShT6LJU.tPiFHgnyKbjpNreoB0Sdi', '2026-09-11 19:49:30', '2026-09-11 16:43:50', 0, '179.4.50.96', '2026-09-11 16:39:30'),
(99, 'GerenteEmpresaDemo@demoSCT.cl', '$2y$10$DfSAVToxSVbg/vuRvtpFZ.AyVZM/u79UtGuVkSm/aDcIK3ymZVIUq', '2026-09-11 19:49:59', '2026-09-11 16:40:05', 0, '179.4.50.96', '2026-09-11 16:39:59'),
(100, 'adminEmpresaDemo@demoSCT.cl', '$2y$10$ujkfd7hAT4muoPLQ4dHNKe95.W4RcOCbgg2CiBv5/m9JSEfp7nvKK', '2026-09-11 19:53:19', '2026-09-11 16:43:23', 0, '179.4.50.96', '2026-09-11 16:43:19'),
(101, 'juanantonioconchaloyola@gmail.com', '$2y$10$LMz0tVjIF4vKgnwjTOiH7eTnALa9qhe6BRVvCkBUxtVsb7EV3.TiK', '2026-09-11 19:53:50', '2026-09-11 16:44:09', 0, '179.4.50.96', '2026-09-11 16:43:50'),
(102, 'UsuarioEmpresaDemo@demoSCT.cl', '$2y$10$Cuu8hzKram9q6A.IspoHbex8j.BBNd2CGy0IDY.ykAYA1bW/alj/S', '2026-09-11 20:00:33', '2026-09-11 16:50:36', 0, '179.4.50.96', '2026-09-11 16:50:33');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `password_resets`
--

CREATE TABLE `password_resets` (
  `id_reset` int(11) NOT NULL,
  `id_users` varchar(50) NOT NULL,
  `token_hash` varchar(255) NOT NULL,
  `purpose` enum('activation','reset') NOT NULL DEFAULT 'reset' COMMENT 'activation=primera contraseña; reset=restablecimiento de contraseña',
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `password_resets`
--

INSERT INTO `password_resets` (`id_reset`, `id_users`, `token_hash`, `purpose`, `expires_at`, `used_at`, `ip_address`, `created_at`) VALUES
(1, 'juanantonioconchaloyola@gmail.com', 'c57149db06eaf6fda9f1977be066000633dd7defb942edd5a3d724fec2b0f082', 'reset', '2026-09-08 20:35:38', '2026-09-08 14:37:29', '127.0.0.1', '2026-09-08 14:35:38'),
(2, 'fco.fredes.g@gmail.com', '8d41e01d7cfbfbaa30445c9518ccddd6f442ca51533f2c01eb7f31529ff0cd21', 'reset', '2026-09-08 20:43:04', '2026-09-08 14:43:52', '127.0.0.1', '2026-09-08 14:43:04'),
(3, 'malazga99@gmail.com', '3ac5fd6521b200c58951ff7fa7f6c7497854e822a544296968a12b38af909c84', 'activation', '2026-09-09 19:46:02', '2026-09-08 14:46:14', '127.0.0.1', '2026-09-08 14:46:02'),
(4, 'pablotroncoso@gmail.com', 'd6eda4849be0e91156a2d27bf2ba18a81f1a60dfb080bbd103d2a179e206dac6', 'activation', '2026-09-09 19:48:06', '2026-09-08 14:48:18', '127.0.0.1', '2026-09-08 14:48:06'),
(5, 'admin.completo.test@test.helheim.cl', '8d2ff3c5e57ec0c5172a77bcbe1dfa28982df5a9923be5aff6bafa230dd22cf2', 'reset', '2026-09-08 20:59:11', '2026-09-08 15:00:11', '127.0.0.1', '2026-09-08 14:59:11');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `permissions`
--

CREATE TABLE `permissions` (
  `id_permission` int(11) NOT NULL,
  `code` varchar(80) NOT NULL COMMENT 'ej. ''workers.create'', ''events.view''',
  `description` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `permissions`
--

INSERT INTO `permissions` (`id_permission`, `code`, `description`) VALUES
(1, 'companies.manage_all', 'Administración global de empresas (compatibilidad Etapa 1/2).'),
(2, 'companies.view_all', 'Ver empresas de todo el sistema.'),
(3, 'companies.edit_all', 'Editar empresas de todo el sistema.'),
(4, 'companies.view_own', 'Ver la empresa propia.'),
(5, 'companies.edit_own', 'Editar la empresa propia.'),
(6, 'companies.create_all', 'Crear empresas en la plataforma.'),
(7, 'companies.state_all', 'Activar o desactivar empresas.'),
(8, 'users.manage', 'Gestionar usuarios (compatibilidad Etapa 1/2).'),
(9, 'users.create', 'Crear usuarios.'),
(10, 'users.assign_roles', 'Asignar roles dentro de la jerarquía permitida.'),
(11, 'users.view', 'Ver usuarios según alcance y jerarquía.'),
(12, 'users.edit', 'Editar usuarios según alcance y jerarquía.'),
(13, 'users.state', 'Activar o desactivar usuarios según alcance y jerarquía.'),
(14, 'users.access', 'Emitir enlaces seguros de activación/restablecimiento.'),
(15, 'users.photo', 'Administrar fotografía de otros usuarios según alcance.'),
(16, 'workers.manage', 'Gestionar trabajadores (compatibilidad Etapa 1/2).'),
(17, 'workers.view', 'Ver trabajadores de la empresa autorizada.'),
(18, 'workers.create', 'Crear trabajadores.'),
(19, 'workers.edit', 'Editar trabajadores.'),
(20, 'workers.state', 'Activar o desactivar trabajadores.'),
(21, 'workers.photo', 'Administrar fotografías de trabajadores.'),
(22, 'projects.manage', 'Gestionar proyectos (compatibilidad Etapa 1/2).'),
(23, 'projects.view', 'Ver proyectos.'),
(24, 'projects.create', 'Crear proyectos.'),
(25, 'projects.edit', 'Editar proyectos.'),
(26, 'projects.state', 'Activar o desactivar proyectos.'),
(27, 'projects.assign_workers', 'Asociar o desasociar trabajadores a proyectos.'),
(28, 'centers.manage', 'Gestionar centros/sedes (compatibilidad Etapa 1/2).'),
(29, 'centers.view', 'Ver centros/sedes.'),
(30, 'centers.create', 'Crear centros/sedes.'),
(31, 'centers.edit', 'Editar centros/sedes.'),
(32, 'centers.state', 'Activar o desactivar centros/sedes.'),
(33, 'events.manage', 'Gestionar eventos (compatibilidad Etapa 1/2).'),
(34, 'events.view', 'Ver eventos e incidentes.'),
(35, 'events.create', 'Reportar eventos e incidentes.'),
(36, 'events.edit', 'Editar eventos e incidentes.'),
(37, 'events.state', 'Cambiar estado de eventos e incidentes.'),
(38, 'events.evidence_upload', 'Adjuntar evidencias a eventos.'),
(39, 'events.evidence_manage', 'Eliminar o administrar evidencias de eventos.'),
(40, 'events.tracking', 'Crear y administrar seguimiento de eventos.'),
(41, 'induction.manage', 'Gestionar inducciones (compatibilidad Etapa 1/2).'),
(42, 'induction.view', 'Ver cursos, materiales y asignaciones autorizadas.'),
(43, 'induction.create', 'Crear cursos de inducción.'),
(44, 'induction.edit', 'Editar cursos de inducción.'),
(45, 'induction.state', 'Activar o desactivar cursos de inducción.'),
(46, 'induction.assign', 'Crear y consultar asignaciones de inducción.'),
(47, 'induction.materials', 'Administrar materiales de inducción.'),
(48, 'induction.questions', 'Administrar preguntas asociadas a cursos.'),
(49, 'induction.execute', 'Rendir inducciones asignadas.'),
(50, 'audits.manage', 'Gestionar auditorías (compatibilidad Etapa 2).'),
(51, 'audits.view', 'Ver auditorías y asignaciones autorizadas.'),
(52, 'audits.create', 'Crear auditorías.'),
(53, 'audits.edit', 'Editar auditorías.'),
(54, 'audits.state', 'Cambiar estado de auditorías.'),
(55, 'audits.assign', 'Asignar auditorías.'),
(56, 'audits.questions', 'Administrar preguntas asociadas a auditorías.'),
(57, 'audits.execute', 'Rendir auditorías asignadas.'),
(58, 'self_assessments.manage', 'Gestionar autoevaluaciones (compatibilidad Etapa 2).'),
(59, 'self_assessments.view', 'Ver autoevaluaciones y asignaciones autorizadas.'),
(60, 'self_assessments.create', 'Crear autoevaluaciones.'),
(61, 'self_assessments.edit', 'Editar autoevaluaciones.'),
(62, 'self_assessments.state', 'Cambiar estado de autoevaluaciones.'),
(63, 'self_assessments.assign', 'Asignar autoevaluaciones.'),
(64, 'self_assessments.questions', 'Administrar preguntas asociadas a autoevaluaciones.'),
(65, 'self_assessments.execute', 'Rendir autoevaluaciones asignadas.'),
(66, 'dynamic_forms.manage', 'Gestionar formularios dinámicos (compatibilidad Etapa 2).'),
(67, 'dynamic_forms.view', 'Ver formularios dinámicos disponibles.'),
(68, 'dynamic_forms.create', 'Crear formularios dinámicos.'),
(69, 'dynamic_forms.edit', 'Editar definición de formularios dinámicos.'),
(70, 'dynamic_forms.state', 'Activar o desactivar formularios dinámicos.'),
(71, 'dynamic_forms.fields', 'Administrar campos de formularios dinámicos.'),
(72, 'dynamic_forms.submissions', 'Consultar envíos de formularios de la empresa autorizada.'),
(73, 'dynamic_forms.submit', 'Responder formularios dinámicos disponibles.'),
(74, 'dynamic_forms.files', 'Descargar archivos de formularios cuando el envío es visible.'),
(75, 'protocols.manage', 'Gestionar protocolos (compatibilidad Etapa 2).'),
(76, 'protocols.view', 'Ver protocolos y asignaciones autorizadas.'),
(77, 'protocols.create', 'Crear protocolos parametrizables.'),
(78, 'protocols.edit', 'Editar protocolos parametrizables.'),
(79, 'protocols.state', 'Activar o desactivar protocolos.'),
(80, 'protocols.forms', 'Vincular formularios a protocolos.'),
(81, 'protocols.assign', 'Crear y administrar asignaciones de protocolos.'),
(82, 'protocols.review', 'Revisar ejecuciones de protocolos.'),
(83, 'protocols.tracking', 'Administrar acciones de seguimiento de protocolos.'),
(84, 'protocols.execute', 'Ejecutar protocolos asignados.'),
(85, 'questions.manage', 'Administrar el banco global de preguntas.'),
(86, 'dashboard.view', 'Ver Dashboard avanzado de la empresa autorizada.'),
(87, 'dashboard.global', 'Usar Dashboard avanzado con alcance multiempresa.'),
(88, 'permissions.manage', 'Administrar la matriz de permisos granulares.'),
(89, 'system.permissions.enabled', 'Marcador interno: habilita permissions/role_permissions como fuente de autorización.'),
(90, 'change_history.view', 'Ver historial de cambios de la empresa autorizada.'),
(91, 'change_history.global', 'Consultar historial de cambios con alcance multiempresa.'),
(92, 'programs.view', 'Ver programas y seguimiento mensual según alcance de empresa.'),
(93, 'programs.create', 'Crear programas para una empresa o proyecto autorizado.'),
(94, 'programs.edit', 'Editar definición y estado operativo de programas.'),
(95, 'programs.state', 'Activar o desactivar programas.'),
(96, 'programs.tracking', 'Crear y corregir seguimiento mensual de programas.');

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
  `responsible_user` varchar(50) DEFAULT NULL COMMENT 'Usuario responsable del programa dentro de la empresa',
  `start_date` date DEFAULT NULL COMMENT 'Fecha de inicio planificada del programa',
  `end_date` date DEFAULT NULL COMMENT 'Fecha de término planificada; NULL = programa sin término definido',
  `status` enum('planificado','en_curso','completado','suspendido','cancelado') NOT NULL DEFAULT 'planificado' COMMENT 'Estado operativo del ciclo de vida del programa',
  `state` int(11) NOT NULL DEFAULT 1,
  `created_by` varchar(50) NOT NULL,
  `date_create` datetime NOT NULL,
  `last_update` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `programs`
--

INSERT INTO `programs` (`id_program`, `id_company`, `id_project`, `name`, `description`, `responsible_user`, `start_date`, `end_date`, `status`, `state`, `created_by`, `date_create`, `last_update`) VALUES
(1, 4, 7, 'DEMO QA - Programa Anual de Seguridad', 'Programa de demostración para seguimiento periódico de actividades preventivas.', 'GerenteEmpresaDemo@demoSCT.cl', '2026-07-01', '2026-12-31', 'en_curso', 1, 'GerenteEmpresaDemo@demoSCT.cl', '2026-09-08 19:53:16', '2026-09-08 19:53:16'),
(2, 4, 9, 'DEMO QA - Plan de Cierre de Hallazgos', 'Programa orientado al seguimiento de acciones derivadas de auditorías.', 'adminEmpresaDemo@demoSCT.cl', '2026-08-01', '2026-11-30', 'en_curso', 1, 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 19:53:16', '2026-09-08 19:53:16'),
(3, 4, 8, 'DEMO EXT - Plan de Capacitación 2026', 'Programa de demostración para analizar progreso mensual de actividades de capacitación.', 'GerenteEmpresaDemo@demoSCT.cl', '2026-06-01', NULL, 'en_curso', 1, 'GerenteEmpresaDemo@demoSCT.cl', '2026-09-08 23:02:47', '2026-09-08 23:02:47');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `program_monthly_tracking`
--

CREATE TABLE `program_monthly_tracking` (
  `id_tracking` int(11) NOT NULL,
  `id_program` int(11) NOT NULL,
  `period_month` date NOT NULL COMMENT 'primer día del mes reportado',
  `target_percentage` decimal(5,2) DEFAULT NULL COMMENT 'Meta acumulada esperada para el mes reportado',
  `progress_percentage` decimal(5,2) NOT NULL DEFAULT 0.00,
  `comments` text DEFAULT NULL,
  `created_by` varchar(50) NOT NULL,
  `date_create` datetime NOT NULL,
  `last_update` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `program_monthly_tracking`
--

INSERT INTO `program_monthly_tracking` (`id_tracking`, `id_program`, `period_month`, `target_percentage`, `progress_percentage`, `comments`, `created_by`, `date_create`, `last_update`) VALUES
(1, 1, '2026-07-01', 30.00, 35.00, 'Inicio del programa y levantamiento de línea base.', 'GerenteEmpresaDemo@demoSCT.cl', '2026-09-08 19:53:16', '2026-09-08 19:53:16'),
(2, 1, '2026-08-01', 55.00, 58.00, 'Avance en capacitaciones y levantamiento de hallazgos.', 'JefaturaEmpresaDemo@demoSCT.cl', '2026-09-08 19:53:16', '2026-09-08 19:53:16'),
(3, 1, '2026-09-01', 75.00, 72.50, 'Seguimiento DEMO actualizado para pruebas de tendencias.', 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 19:53:16', '2026-09-08 19:53:16'),
(4, 2, '2026-08-01', 35.00, 40.00, 'Plan de cierre iniciado.', 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 19:53:16', '2026-09-08 19:53:16'),
(5, 2, '2026-09-01', 70.00, 65.00, 'Acciones correctivas en ejecución.', 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 19:53:16', '2026-09-08 19:53:16'),
(6, 3, '2026-06-01', NULL, 15.00, 'Levantamiento inicial de necesidades de capacitación.', 'GerenteEmpresaDemo@demoSCT.cl', '2026-09-08 23:02:47', '2026-09-08 23:02:47'),
(7, 3, '2026-07-01', NULL, 38.00, 'Inicio de cursos de seguridad operacional.', 'JefaturaEmpresaDemo@demoSCT.cl', '2026-09-08 23:02:47', '2026-09-08 23:02:47'),
(8, 3, '2026-08-01', NULL, 61.00, 'Capacitaciones críticas ejecutadas y seguimiento de pendientes.', 'JefaturaEmpresaDemo@demoSCT.cl', '2026-09-08 23:02:47', '2026-09-08 23:02:47'),
(9, 3, '2026-09-01', NULL, 76.00, 'Avance acumulado al inicio de septiembre.', 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 23:02:47', '2026-09-08 23:02:47');

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
(6, 3, 'Ampliación Subestación Los Andes', 'Proyecto de ampliación de capacidad de la subestación eléctrica.', 1, 'seed_test_data', '2026-09-08 08:30:00', '2026-09-08 08:30:00'),
(7, 4, 'DEMO QA - Implementación SCT', 'Proyecto activo para probar asignación de trabajadores, eventos e indicadores.', 1, 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 19:53:15', '2026-09-08 19:53:15'),
(8, 4, 'DEMO QA - Mantención Planta', 'Proyecto operacional para pruebas de seguridad, seguimiento y auditorías.', 1, 'JefaturaEmpresaDemo@demoSCT.cl', '2026-09-08 19:53:15', '2026-09-08 19:53:15'),
(9, 4, 'DEMO QA - Proyecto Auditoría', 'Proyecto destinado a escenarios de auditoría y autoevaluación.', 1, 'GerenteEmpresaDemo@demoSCT.cl', '2026-09-08 19:53:15', '2026-09-08 19:53:15'),
(10, 4, 'DEMO QA - Proyecto Cerrado', 'Proyecto inactivo para validar filtros y restricciones.', 0, 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 19:53:15', '2026-09-08 19:53:15');

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
  `version` int(11) NOT NULL DEFAULT 1,
  `authority` varchar(100) DEFAULT NULL,
  `normative_reference` varchar(255) DEFAULT NULL,
  `source_url` varchar(500) DEFAULT NULL,
  `effective_date_from` date DEFAULT NULL,
  `effective_date_until` date DEFAULT NULL,
  `parameters` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'parametrización flexible del protocolo' CHECK (json_valid(`parameters`)),
  `state` int(11) NOT NULL DEFAULT 1,
  `created_by` varchar(50) NOT NULL,
  `date_create` datetime NOT NULL,
  `last_update` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `protocols`
--

INSERT INTO `protocols` (`id_protocol`, `id_company`, `code`, `name`, `description`, `version`, `authority`, `normative_reference`, `source_url`, `effective_date_from`, `effective_date_until`, `parameters`, `state`, `created_by`, `date_create`, `last_update`) VALUES
(1, 4, 'DEMO-PREXOR', 'DEMO - Gestión de Exposición a Ruido', 'Configuración de prueba inspirada en gestión de exposición ocupacional a ruido.', 1, 'DEMO SCT', NULL, 'https://www.minsal.cl/salud-ocupacional/', NULL, NULL, '{\"entorno\": \"demo\", \"periodicidad\": \"mensual\", \"requiere_medicion\": true, \"nivel_accion_db\": 82}', 1, 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 19:53:16', '2026-09-09 00:38:19'),
(2, 4, 'DEMO-TMERT', 'DEMO - Gestión de Riesgos Musculoesqueléticos', 'Configuración de prueba para seguimiento de factores ergonómicos.', 1, 'DEMO SCT', NULL, 'https://www.minsal.cl/salud-ocupacional/', NULL, NULL, '{\"entorno\": \"demo\", \"periodicidad\": \"trimestral\", \"requiere_evaluacion_puesto\": true, \"nivel_riesgo_inicial\": \"medio\"}', 1, 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 19:53:16', '2026-09-09 00:38:19'),
(3, 4, 'DEMO-UV', 'DEMO - Exposición a Radiación UV', 'Protocolo inactivo de demostración para validar filtros por estado.', 1, 'DEMO SCT', NULL, 'https://www.minsal.cl/salud-ocupacional/', NULL, NULL, '{\"entorno\": \"demo\", \"periodicidad\": \"semestral\", \"requiere_epp\": true}', 0, 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 19:53:16', '2026-09-09 00:38:19'),
(4, 4, 'DEMO-PSICOSOCIAL', 'DEMO - Riesgo Psicosocial', 'Registro preparatorio para probar parametrización y filtros del futuro módulo de Protocolos.', 1, NULL, NULL, NULL, NULL, NULL, '{\"entorno\":\"demo\",\"periodicidad\":\"bienal\",\"requiere_encuesta\":true,\"estado_inicial\":\"pendiente\"}', 1, 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 23:02:47', '2026-09-08 23:02:47'),
(5, 4, 'DEMO-SILICE', 'DEMO - Exposición a Sílice', 'Registro preparatorio para escenarios de vigilancia ocupacional y parametrización.', 1, NULL, NULL, NULL, NULL, NULL, '{\"entorno\":\"demo\",\"periodicidad\":\"anual\",\"requiere_medicion\":true,\"nivel_riesgo\":\"alto\"}', 1, 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 23:02:47', '2026-09-08 23:02:47'),
(6, NULL, 'MINSAL-PREXOR', 'PREXOR - Vigilancia de exposición ocupacional a ruido', 'Referencia MINSAL para programas de vigilancia de pérdida auditiva por exposición ocupacional a ruido.', 1, 'Ministerio de Salud de Chile', 'PREXOR', 'https://www.minsal.cl/salud-ocupacional/', NULL, NULL, '{\"country\":\"CL\",\"regulatory_content\":\"reference_only\",\"rules_mode\":\"manual\",\"requires_professional_validation\":true}', 1, 'seed_protocolos_minsal', '2026-09-09 00:12:42', '2026-09-09 00:12:42'),
(7, NULL, 'MINSAL-TMERT-V2', 'TMERT V2 - Vigilancia de trastornos musculoesqueléticos', 'Referencia MINSAL para vigilancia ocupacional por exposición a factores de riesgo de trastornos musculoesqueléticos relacionados con el trabajo.', 2, 'Ministerio de Salud de Chile', 'Resolución Exenta N°1660, 06-12-2024', 'https://www.minsal.cl/wp-content/uploads/2015/11/Resolucion-TMERT-V2-N1660.pdf', NULL, NULL, '{\"country\":\"CL\",\"regulatory_content\":\"reference_only\",\"rules_mode\":\"manual\",\"requires_professional_validation\":true}', 1, 'seed_protocolos_minsal', '2026-09-09 00:12:42', '2026-09-09 00:12:42'),
(8, NULL, 'MINSAL-SILICE', 'Vigilancia de exposición ocupacional a sílice', 'Referencia MINSAL para vigilancia del ambiente de trabajo y de la salud de trabajadores con exposición a sílice.', 1, 'Ministerio de Salud de Chile', 'Resolución Exenta N°268/2015; modificada por Resolución Exenta N°1059/2016', 'https://www.minsal.cl/salud-ocupacional/', NULL, NULL, '{\"country\":\"CL\",\"regulatory_content\":\"reference_only\",\"rules_mode\":\"manual\",\"requires_professional_validation\":true}', 1, 'seed_protocolos_minsal', '2026-09-09 00:12:42', '2026-09-09 00:12:42'),
(9, NULL, 'MINSAL-PSICOSOCIAL', 'Vigilancia de riesgos psicosociales en el trabajo', 'Referencia MINSAL para evaluación, control y monitoreo de factores de riesgo psicosocial en los centros de trabajo.', 1, 'Ministerio de Salud de Chile', 'Protocolo de Vigilancia de Riesgos Psicosociales en el Trabajo', 'https://www.minsal.cl/salud-ocupacional/', NULL, NULL, '{\"country\":\"CL\",\"regulatory_content\":\"reference_only\",\"rules_mode\":\"manual\",\"instrument\":\"CEAL-SM/SUSESO\",\"requires_professional_validation\":true}', 1, 'seed_protocolos_minsal', '2026-09-09 00:12:42', '2026-09-09 00:12:42'),
(10, NULL, 'MINSAL-CITOSTATICOS', 'Vigilancia de trabajadores expuestos a citostáticos', 'Referencia MINSAL para vigilancia epidemiológica de trabajadores expuestos a agentes citostáticos.', 1, 'Ministerio de Salud de Chile', 'Resolución Exenta N°1093/2016', 'https://www.minsal.cl/salud-ocupacional/', NULL, NULL, '{\"country\":\"CL\",\"regulatory_content\":\"reference_only\",\"rules_mode\":\"manual\",\"requires_professional_validation\":true}', 1, 'seed_protocolos_minsal', '2026-09-09 00:12:42', '2026-09-09 00:12:42');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `protocol_assignments`
--

CREATE TABLE `protocol_assignments` (
  `id_protocol_assignment` int(11) NOT NULL,
  `id_protocol` int(11) NOT NULL,
  `id_company` int(11) NOT NULL,
  `id_company_center` int(11) DEFAULT NULL,
  `id_project` int(11) DEFAULT NULL,
  `id_worker` int(11) DEFAULT NULL,
  `responsible_user` varchar(50) NOT NULL,
  `start_at` datetime NOT NULL,
  `next_due_at` datetime NOT NULL,
  `recurrence_unit` enum('none','days','weeks','months','years') NOT NULL DEFAULT 'none',
  `recurrence_interval` int(11) DEFAULT NULL,
  `parameter_overrides` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`parameter_overrides`)),
  `notes` text DEFAULT NULL,
  `state` enum('activa','suspendida','cerrada','cancelada') NOT NULL DEFAULT 'activa',
  `created_by` varchar(50) NOT NULL,
  `date_create` datetime NOT NULL,
  `last_update` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `protocol_assignments`
--

INSERT INTO `protocol_assignments` (`id_protocol_assignment`, `id_protocol`, `id_company`, `id_company_center`, `id_project`, `id_worker`, `responsible_user`, `start_at`, `next_due_at`, `recurrence_unit`, `recurrence_interval`, `parameter_overrides`, `notes`, `state`, `created_by`, `date_create`, `last_update`) VALUES
(1, 1, 4, 8, 8, 18, 'UsuarioEmpresaDemo@demoSCT.cl', '2026-09-09 08:00:00', '2026-09-30 23:59:59', 'months', 1, '{\"demo_scenario\":\"pending_execution\"}', 'Escenario DEMO PREXOR para ejecutar desde Mis Protocolos.', 'activa', 'seed_protocolos_minsal', '2026-09-09 00:12:42', '2026-09-09 00:12:42'),
(2, 2, 4, 9, 9, 17, 'JefaturaEmpresaDemo@demoSCT.cl', '2026-09-09 08:00:00', '2026-10-15 23:59:59', 'months', 3, '{\"demo_scenario\":\"pending_execution\"}', 'Escenario DEMO TMERT para ejecutar desde Mis Protocolos.', 'activa', 'seed_protocolos_minsal', '2026-09-09 00:12:42', '2026-09-09 00:12:42'),
(3, 4, 4, 7, 7, 16, 'GerenteEmpresaDemo@demoSCT.cl', '2026-08-20 09:00:00', '2027-09-04 23:59:59', 'years', 1, '{\"demo_scenario\":\"historical_conforme\"}', '[DEMO-PROT-DATA] Psicosocial Gerente - histórico conforme y recurrente.', 'activa', 'adminEmpresaDemo@demoSCT.cl', '2026-08-20 09:00:00', '2026-09-04 17:00:00'),
(4, 5, 4, 8, 8, 20, 'adminEmpresaDemo@demoSCT.cl', '2026-08-25 09:00:00', '2027-03-06 23:59:59', 'months', 6, '{\"demo_scenario\":\"historical_observado\",\"target_worker\":\"sin_cuenta\"}', '[DEMO-PROT-DATA] Sílice Admin - histórico observado con seguimiento.', 'activa', 'adminEmpresaDemo@demoSCT.cl', '2026-08-25 09:00:00', '2026-09-06 18:00:00'),
(5, 5, 4, 9, 8, 23, 'GerenteEmpresaDemo@demoSCT.cl', '2026-09-01 09:00:00', '2026-09-08 23:59:59', 'months', 3, '{\"demo_scenario\":\"pending_review\",\"target_worker\":\"contratista_sin_cuenta\"}', '[DEMO-PROT-DATA] Sílice Gerente - ejecución pendiente de revisión.', 'activa', 'adminEmpresaDemo@demoSCT.cl', '2026-09-01 09:00:00', '2026-09-08 19:30:00'),
(6, 4, 4, 7, 9, 18, 'UsuarioEmpresaDemo@demoSCT.cl', '2026-09-01 09:00:00', '2026-09-30 23:59:59', 'months', 6, '{\"demo_scenario\":\"suspended_no_execution\"}', '[DEMO-PROT-DATA] Psicosocial Usuario - suspendido sin ejecución.', 'suspendida', 'adminEmpresaDemo@demoSCT.cl', '2026-09-01 09:00:00', '2026-09-08 12:00:00'),
(7, 2, 4, 7, 9, 19, 'adminEmpresaDemo@demoSCT.cl', '2026-08-15 09:00:00', '2026-08-30 23:59:59', 'none', NULL, '{\"demo_scenario\":\"closed_no_aplica\"}', '[DEMO-PROT-DATA] TMERT Admin - cerrado con resultado no aplica.', 'cerrada', 'adminEmpresaDemo@demoSCT.cl', '2026-08-15 09:00:00', '2026-08-30 17:00:00'),
(8, 6, 4, 8, 8, 21, 'JefaturaEmpresaDemo@demoSCT.cl', '2026-09-01 09:00:00', '2026-09-08 18:00:00', 'none', NULL, '{\"demo_scenario\":\"global_protocol_overdue\",\"regulatory_content\":\"reference_only\"}', '[DEMO-PROT-DATA] MINSAL-PREXOR global - Jefatura vencido sin ejecución.', 'activa', 'adminEmpresaDemo@demoSCT.cl', '2026-09-01 09:00:00', '2026-09-01 09:00:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `protocol_executions`
--

CREATE TABLE `protocol_executions` (
  `id_protocol_execution` int(11) NOT NULL,
  `id_protocol_assignment` int(11) NOT NULL,
  `cycle_number` int(11) NOT NULL,
  `started_at` datetime NOT NULL,
  `submitted_at` datetime NOT NULL,
  `result` enum('pendiente_revision','conforme','observado','no_conforme','no_aplica') NOT NULL DEFAULT 'pendiente_revision',
  `review_notes` text DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `reviewed_by` varchar(50) DEFAULT NULL,
  `created_by` varchar(50) NOT NULL,
  `date_create` datetime NOT NULL,
  `last_update` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `protocol_executions`
--

INSERT INTO `protocol_executions` (`id_protocol_execution`, `id_protocol_assignment`, `cycle_number`, `started_at`, `submitted_at`, `result`, `review_notes`, `reviewed_at`, `reviewed_by`, `created_by`, `date_create`, `last_update`) VALUES
(1, 3, 1, '2026-09-04 15:00:00', '2026-09-04 15:20:00', 'conforme', 'Escenario DEMO: seguimiento revisado sin observaciones pendientes.', '2026-09-04 17:00:00', 'adminEmpresaDemo@demoSCT.cl', 'GerenteEmpresaDemo@demoSCT.cl', '2026-09-04 15:00:00', '2026-09-04 17:00:00'),
(2, 4, 1, '2026-09-06 15:00:00', '2026-09-06 15:30:00', 'observado', 'Escenario DEMO: se requieren acciones de control y verificación documental.', '2026-09-06 18:00:00', 'JefaturaEmpresaDemo@demoSCT.cl', 'adminEmpresaDemo@demoSCT.cl', '2026-09-06 15:00:00', '2026-09-06 18:00:00'),
(3, 5, 1, '2026-09-08 19:00:00', '2026-09-08 19:30:00', 'pendiente_revision', NULL, NULL, NULL, 'GerenteEmpresaDemo@demoSCT.cl', '2026-09-08 19:00:00', '2026-09-08 19:30:00'),
(4, 7, 1, '2026-08-29 15:00:00', '2026-08-29 15:20:00', 'no_aplica', 'Escenario DEMO: revisión cerrada como no aplica para este alcance.', '2026-08-30 17:00:00', 'GerenteEmpresaDemo@demoSCT.cl', 'adminEmpresaDemo@demoSCT.cl', '2026-08-29 15:00:00', '2026-08-30 17:00:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `protocol_execution_submissions`
--

CREATE TABLE `protocol_execution_submissions` (
  `id_protocol_execution_submission` int(11) NOT NULL,
  `id_protocol_execution` int(11) NOT NULL,
  `id_protocol_form` int(11) NOT NULL,
  `id_submission` int(11) NOT NULL,
  `created_by` varchar(50) NOT NULL,
  `date_create` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `protocol_execution_submissions`
--

INSERT INTO `protocol_execution_submissions` (`id_protocol_execution_submission`, `id_protocol_execution`, `id_protocol_form`, `id_submission`, `created_by`, `date_create`) VALUES
(1, 1, 4, 11, 'GerenteEmpresaDemo@demoSCT.cl', '2026-09-04 15:20:00'),
(2, 2, 3, 12, 'adminEmpresaDemo@demoSCT.cl', '2026-09-06 15:30:00'),
(3, 3, 3, 13, 'GerenteEmpresaDemo@demoSCT.cl', '2026-09-08 19:30:00'),
(4, 4, 2, 14, 'adminEmpresaDemo@demoSCT.cl', '2026-08-29 15:20:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `protocol_forms`
--

CREATE TABLE `protocol_forms` (
  `id_protocol_form` int(11) NOT NULL,
  `id_protocol` int(11) NOT NULL,
  `id_company` int(11) DEFAULT NULL COMMENT 'NULL=vínculo global; valor=implementación de una empresa',
  `id_form` int(11) NOT NULL,
  `is_required` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_by` varchar(50) NOT NULL,
  `date_create` datetime NOT NULL,
  `last_update` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `protocol_forms`
--

INSERT INTO `protocol_forms` (`id_protocol_form`, `id_protocol`, `id_company`, `id_form`, `is_required`, `sort_order`, `created_by`, `date_create`, `last_update`) VALUES
(1, 1, 4, 1, 1, 1, 'seed_protocolos_minsal', '2026-09-09 00:12:42', '2026-09-09 00:12:42'),
(2, 2, 4, 2, 1, 1, 'seed_protocolos_minsal', '2026-09-09 00:12:42', '2026-09-09 00:12:42'),
(3, 5, 4, 6, 1, 1, 'adminEmpresaDemo@demoSCT.cl', '2026-09-09 00:52:19', '2026-09-09 00:52:19'),
(4, 4, 4, 7, 1, 1, 'adminEmpresaDemo@demoSCT.cl', '2026-09-09 00:52:19', '2026-09-09 00:52:19'),
(5, 1, 4, 8, 0, 2, 'adminEmpresaDemo@demoSCT.cl', '2026-09-09 00:52:19', '2026-09-09 00:52:19'),
(6, 6, 4, 9, 1, 1, 'adminEmpresaDemo@demoSCT.cl', '2026-09-09 00:52:19', '2026-09-09 00:52:19');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `protocol_tracking`
--

CREATE TABLE `protocol_tracking` (
  `id_protocol_tracking` int(11) NOT NULL,
  `id_protocol_assignment` int(11) NOT NULL,
  `id_protocol_execution` int(11) DEFAULT NULL,
  `description` text NOT NULL,
  `responsible_user` varchar(50) DEFAULT NULL,
  `commitment_date` datetime NOT NULL,
  `deadline` datetime NOT NULL,
  `status` enum('pendiente','en_curso','completado','cancelado') NOT NULL DEFAULT 'pendiente',
  `completed_at` datetime DEFAULT NULL,
  `created_by` varchar(50) NOT NULL,
  `date_create` datetime NOT NULL,
  `last_update` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `protocol_tracking`
--

INSERT INTO `protocol_tracking` (`id_protocol_tracking`, `id_protocol_assignment`, `id_protocol_execution`, `description`, `responsible_user`, `commitment_date`, `deadline`, `status`, `completed_at`, `created_by`, `date_create`, `last_update`) VALUES
(1, 3, 1, 'Cerrar comunicación interna de acciones preventivas del escenario DEMO.', 'GerenteEmpresaDemo@demoSCT.cl', '2026-09-04 17:10:00', '2026-09-07 18:00:00', 'completado', '2026-09-06 12:00:00', 'adminEmpresaDemo@demoSCT.cl', '2026-09-04 17:10:00', '2026-09-06 12:00:00'),
(2, 4, 2, 'Verificar disponibilidad y estado de controles de ingeniería del área evaluada.', 'JefaturaEmpresaDemo@demoSCT.cl', '2026-09-06 18:10:00', '2026-09-12 18:00:00', 'en_curso', NULL, 'adminEmpresaDemo@demoSCT.cl', '2026-09-06 18:10:00', '2026-09-08 12:00:00'),
(3, 4, 2, 'Adjuntar respaldo documental mediante la interfaz y cerrar la observación DEMO.', 'adminEmpresaDemo@demoSCT.cl', '2026-09-06 18:15:00', '2026-09-15 18:00:00', 'pendiente', NULL, 'adminEmpresaDemo@demoSCT.cl', '2026-09-06 18:15:00', '2026-09-06 18:15:00'),
(4, 7, 4, 'Documentar motivo de cierre no aplica del escenario DEMO.', 'adminEmpresaDemo@demoSCT.cl', '2026-08-29 16:00:00', '2026-08-30 18:00:00', 'completado', '2026-08-30 16:30:00', 'GerenteEmpresaDemo@demoSCT.cl', '2026-08-29 16:00:00', '2026-08-30 16:30:00');

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

--
-- Volcado de datos para la tabla `role_permissions`
--

INSERT INTO `role_permissions` (`id_role_group`, `id_permission`) VALUES
(1, 4),
(1, 5),
(1, 8),
(1, 9),
(1, 10),
(1, 11),
(1, 12),
(1, 13),
(1, 14),
(1, 15),
(1, 16),
(1, 17),
(1, 18),
(1, 19),
(1, 20),
(1, 21),
(1, 22),
(1, 23),
(1, 24),
(1, 25),
(1, 26),
(1, 27),
(1, 28),
(1, 29),
(1, 30),
(1, 31),
(1, 32),
(1, 33),
(1, 34),
(1, 35),
(1, 36),
(1, 37),
(1, 38),
(1, 39),
(1, 40),
(1, 41),
(1, 42),
(1, 43),
(1, 44),
(1, 45),
(1, 46),
(1, 47),
(1, 48),
(1, 49),
(1, 50),
(1, 51),
(1, 52),
(1, 53),
(1, 54),
(1, 55),
(1, 56),
(1, 57),
(1, 58),
(1, 59),
(1, 60),
(1, 61),
(1, 62),
(1, 63),
(1, 64),
(1, 65),
(1, 66),
(1, 67),
(1, 68),
(1, 69),
(1, 70),
(1, 71),
(1, 72),
(1, 73),
(1, 74),
(1, 75),
(1, 76),
(1, 77),
(1, 78),
(1, 79),
(1, 80),
(1, 81),
(1, 82),
(1, 83),
(1, 84),
(1, 85),
(1, 86),
(1, 90),
(1, 92),
(1, 93),
(1, 94),
(1, 95),
(1, 96),
(2, 4),
(2, 5),
(2, 8),
(2, 9),
(2, 10),
(2, 11),
(2, 12),
(2, 13),
(2, 14),
(2, 15),
(2, 16),
(2, 17),
(2, 18),
(2, 19),
(2, 20),
(2, 21),
(2, 22),
(2, 23),
(2, 24),
(2, 25),
(2, 26),
(2, 27),
(2, 28),
(2, 29),
(2, 30),
(2, 31),
(2, 32),
(2, 33),
(2, 34),
(2, 35),
(2, 36),
(2, 37),
(2, 38),
(2, 39),
(2, 40),
(2, 41),
(2, 42),
(2, 43),
(2, 44),
(2, 45),
(2, 46),
(2, 47),
(2, 48),
(2, 49),
(2, 50),
(2, 51),
(2, 52),
(2, 53),
(2, 54),
(2, 55),
(2, 56),
(2, 57),
(2, 58),
(2, 59),
(2, 60),
(2, 61),
(2, 62),
(2, 63),
(2, 64),
(2, 65),
(2, 66),
(2, 67),
(2, 68),
(2, 69),
(2, 70),
(2, 71),
(2, 72),
(2, 73),
(2, 74),
(2, 75),
(2, 76),
(2, 77),
(2, 78),
(2, 79),
(2, 80),
(2, 81),
(2, 82),
(2, 83),
(2, 84),
(2, 85),
(2, 86),
(2, 90),
(2, 92),
(2, 93),
(2, 94),
(2, 95),
(2, 96),
(3, 4),
(3, 5),
(3, 8),
(3, 9),
(3, 10),
(3, 11),
(3, 12),
(3, 13),
(3, 14),
(3, 15),
(3, 16),
(3, 17),
(3, 18),
(3, 19),
(3, 20),
(3, 21),
(3, 22),
(3, 23),
(3, 24),
(3, 25),
(3, 26),
(3, 27),
(3, 28),
(3, 29),
(3, 30),
(3, 31),
(3, 32),
(3, 33),
(3, 34),
(3, 35),
(3, 36),
(3, 37),
(3, 38),
(3, 39),
(3, 40),
(3, 41),
(3, 42),
(3, 43),
(3, 44),
(3, 45),
(3, 46),
(3, 47),
(3, 48),
(3, 49),
(3, 50),
(3, 51),
(3, 52),
(3, 53),
(3, 54),
(3, 55),
(3, 56),
(3, 57),
(3, 58),
(3, 59),
(3, 60),
(3, 61),
(3, 62),
(3, 63),
(3, 64),
(3, 65),
(3, 66),
(3, 67),
(3, 68),
(3, 69),
(3, 70),
(3, 71),
(3, 72),
(3, 73),
(3, 74),
(3, 75),
(3, 76),
(3, 77),
(3, 78),
(3, 79),
(3, 80),
(3, 81),
(3, 82),
(3, 83),
(3, 84),
(3, 86),
(3, 90),
(3, 92),
(3, 93),
(3, 94),
(3, 95),
(3, 96),
(4, 4),
(4, 5),
(4, 8),
(4, 9),
(4, 10),
(4, 11),
(4, 12),
(4, 13),
(4, 14),
(4, 15),
(4, 16),
(4, 17),
(4, 18),
(4, 19),
(4, 20),
(4, 21),
(4, 22),
(4, 23),
(4, 24),
(4, 25),
(4, 26),
(4, 27),
(4, 28),
(4, 29),
(4, 30),
(4, 31),
(4, 32),
(4, 33),
(4, 34),
(4, 35),
(4, 36),
(4, 37),
(4, 38),
(4, 39),
(4, 40),
(4, 41),
(4, 42),
(4, 43),
(4, 44),
(4, 45),
(4, 46),
(4, 47),
(4, 48),
(4, 49),
(4, 50),
(4, 51),
(4, 52),
(4, 53),
(4, 54),
(4, 55),
(4, 56),
(4, 57),
(4, 58),
(4, 59),
(4, 60),
(4, 61),
(4, 62),
(4, 63),
(4, 64),
(4, 65),
(4, 66),
(4, 67),
(4, 68),
(4, 69),
(4, 70),
(4, 71),
(4, 72),
(4, 73),
(4, 74),
(4, 75),
(4, 76),
(4, 77),
(4, 78),
(4, 79),
(4, 80),
(4, 81),
(4, 82),
(4, 83),
(4, 84),
(4, 86),
(4, 90),
(4, 92),
(4, 93),
(4, 94),
(4, 95),
(4, 96),
(5, 4),
(5, 11),
(5, 17),
(5, 23),
(5, 29),
(5, 34),
(5, 35),
(5, 38),
(5, 42),
(5, 49),
(5, 51),
(5, 57),
(5, 59),
(5, 65),
(5, 67),
(5, 73),
(5, 74),
(5, 76),
(5, 84),
(5, 86),
(5, 92),
(6, 4),
(6, 11),
(6, 17),
(6, 23),
(6, 29),
(6, 34),
(6, 35),
(6, 38),
(6, 42),
(6, 49),
(6, 51),
(6, 57),
(6, 59),
(6, 65),
(6, 67),
(6, 73),
(6, 74),
(6, 76),
(6, 84),
(6, 86),
(6, 92),
(8, 1),
(8, 2),
(8, 3),
(8, 4),
(8, 5),
(8, 6),
(8, 7),
(8, 8),
(8, 9),
(8, 10),
(8, 11),
(8, 12),
(8, 13),
(8, 14),
(8, 15),
(8, 16),
(8, 17),
(8, 18),
(8, 19),
(8, 20),
(8, 21),
(8, 22),
(8, 23),
(8, 24),
(8, 25),
(8, 26),
(8, 27),
(8, 28),
(8, 29),
(8, 30),
(8, 31),
(8, 32),
(8, 33),
(8, 34),
(8, 35),
(8, 36),
(8, 37),
(8, 38),
(8, 39),
(8, 40),
(8, 41),
(8, 42),
(8, 43),
(8, 44),
(8, 45),
(8, 46),
(8, 47),
(8, 48),
(8, 49),
(8, 50),
(8, 51),
(8, 52),
(8, 53),
(8, 54),
(8, 55),
(8, 56),
(8, 57),
(8, 58),
(8, 59),
(8, 60),
(8, 61),
(8, 62),
(8, 63),
(8, 64),
(8, 65),
(8, 66),
(8, 67),
(8, 68),
(8, 69),
(8, 70),
(8, 71),
(8, 72),
(8, 73),
(8, 74),
(8, 75),
(8, 76),
(8, 77),
(8, 78),
(8, 79),
(8, 80),
(8, 81),
(8, 82),
(8, 83),
(8, 84),
(8, 85),
(8, 86),
(8, 87),
(8, 88),
(8, 90),
(8, 91),
(8, 92),
(8, 93),
(8, 94),
(8, 95),
(8, 96),
(9, 1),
(9, 2),
(9, 3),
(9, 4),
(9, 5),
(9, 6),
(9, 7),
(9, 8),
(9, 9),
(9, 10),
(9, 11),
(9, 12),
(9, 13),
(9, 14),
(9, 15),
(9, 16),
(9, 17),
(9, 18),
(9, 19),
(9, 20),
(9, 21),
(9, 22),
(9, 23),
(9, 24),
(9, 25),
(9, 26),
(9, 27),
(9, 28),
(9, 29),
(9, 30),
(9, 31),
(9, 32),
(9, 33),
(9, 34),
(9, 35),
(9, 36),
(9, 37),
(9, 38),
(9, 39),
(9, 40),
(9, 41),
(9, 42),
(9, 43),
(9, 44),
(9, 45),
(9, 46),
(9, 47),
(9, 48),
(9, 49),
(9, 50),
(9, 51),
(9, 52),
(9, 53),
(9, 54),
(9, 55),
(9, 56),
(9, 57),
(9, 58),
(9, 59),
(9, 60),
(9, 61),
(9, 62),
(9, 63),
(9, 64),
(9, 65),
(9, 66),
(9, 67),
(9, 68),
(9, 69),
(9, 70),
(9, 71),
(9, 72),
(9, 73),
(9, 74),
(9, 75),
(9, 76),
(9, 77),
(9, 78),
(9, 79),
(9, 80),
(9, 81),
(9, 82),
(9, 83),
(9, 84),
(9, 85),
(9, 86),
(9, 87),
(9, 88),
(9, 90),
(9, 91),
(9, 92),
(9, 93),
(9, 94),
(9, 95),
(9, 96),
(11, 4),
(11, 8),
(11, 9),
(11, 10),
(11, 11),
(11, 12),
(11, 13),
(11, 14),
(11, 15),
(11, 16),
(11, 17),
(11, 18),
(11, 19),
(11, 20),
(11, 21),
(11, 22),
(11, 23),
(11, 24),
(11, 25),
(11, 26),
(11, 27),
(11, 28),
(11, 29),
(11, 30),
(11, 31),
(11, 32),
(11, 33),
(11, 34),
(11, 35),
(11, 36),
(11, 37),
(11, 38),
(11, 39),
(11, 40),
(11, 41),
(11, 42),
(11, 43),
(11, 44),
(11, 45),
(11, 46),
(11, 47),
(11, 48),
(11, 49),
(11, 50),
(11, 51),
(11, 52),
(11, 53),
(11, 54),
(11, 55),
(11, 56),
(11, 57),
(11, 58),
(11, 59),
(11, 60),
(11, 61),
(11, 62),
(11, 63),
(11, 64),
(11, 65),
(11, 66),
(11, 67),
(11, 68),
(11, 69),
(11, 70),
(11, 71),
(11, 72),
(11, 73),
(11, 74),
(11, 75),
(11, 76),
(11, 77),
(11, 78),
(11, 79),
(11, 80),
(11, 81),
(11, 82),
(11, 83),
(11, 84),
(11, 86),
(11, 90),
(11, 92),
(11, 93),
(11, 94),
(11, 95),
(11, 96),
(12, 4),
(12, 8),
(12, 9),
(12, 10),
(12, 11),
(12, 12),
(12, 13),
(12, 14),
(12, 15),
(12, 16),
(12, 17),
(12, 18),
(12, 19),
(12, 20),
(12, 21),
(12, 22),
(12, 23),
(12, 24),
(12, 25),
(12, 26),
(12, 27),
(12, 28),
(12, 29),
(12, 30),
(12, 31),
(12, 32),
(12, 33),
(12, 34),
(12, 35),
(12, 36),
(12, 37),
(12, 38),
(12, 39),
(12, 40),
(12, 41),
(12, 42),
(12, 43),
(12, 44),
(12, 45),
(12, 46),
(12, 47),
(12, 48),
(12, 49),
(12, 50),
(12, 51),
(12, 52),
(12, 53),
(12, 54),
(12, 55),
(12, 56),
(12, 57),
(12, 58),
(12, 59),
(12, 60),
(12, 61),
(12, 62),
(12, 63),
(12, 64),
(12, 65),
(12, 66),
(12, 67),
(12, 68),
(12, 69),
(12, 70),
(12, 71),
(12, 72),
(12, 73),
(12, 74),
(12, 75),
(12, 76),
(12, 77),
(12, 78),
(12, 79),
(12, 80),
(12, 81),
(12, 82),
(12, 83),
(12, 84),
(12, 86),
(12, 90),
(12, 92),
(12, 93),
(12, 94),
(12, 95),
(12, 96),
(14, 4),
(14, 5),
(14, 8),
(14, 9),
(14, 10),
(14, 11),
(14, 12),
(14, 13),
(14, 14),
(14, 15),
(14, 16),
(14, 17),
(14, 18),
(14, 19),
(14, 20),
(14, 21),
(14, 22),
(14, 23),
(14, 24),
(14, 25),
(14, 26),
(14, 27),
(14, 28),
(14, 29),
(14, 30),
(14, 31),
(14, 32),
(14, 33),
(14, 34),
(14, 35),
(14, 36),
(14, 37),
(14, 38),
(14, 39),
(14, 40),
(14, 41),
(14, 42),
(14, 43),
(14, 44),
(14, 45),
(14, 46),
(14, 47),
(14, 48),
(14, 49),
(14, 50),
(14, 51),
(14, 52),
(14, 53),
(14, 54),
(14, 55),
(14, 56),
(14, 57),
(14, 58),
(14, 59),
(14, 60),
(14, 61),
(14, 62),
(14, 63),
(14, 64),
(14, 65),
(14, 66),
(14, 67),
(14, 68),
(14, 69),
(14, 70),
(14, 71),
(14, 72),
(14, 73),
(14, 74),
(14, 75),
(14, 76),
(14, 77),
(14, 78),
(14, 79),
(14, 80),
(14, 81),
(14, 82),
(14, 83),
(14, 84),
(14, 85),
(14, 86),
(14, 90),
(14, 92),
(14, 93),
(14, 94),
(14, 95),
(14, 96),
(15, 1),
(15, 2),
(15, 3),
(15, 4),
(15, 5),
(15, 6),
(15, 7),
(15, 8),
(15, 9),
(15, 10),
(15, 11),
(15, 12),
(15, 13),
(15, 14),
(15, 15),
(15, 16),
(15, 17),
(15, 18),
(15, 19),
(15, 20),
(15, 21),
(15, 22),
(15, 23),
(15, 24),
(15, 25),
(15, 26),
(15, 27),
(15, 28),
(15, 29),
(15, 30),
(15, 31),
(15, 32),
(15, 33),
(15, 34),
(15, 35),
(15, 36),
(15, 37),
(15, 38),
(15, 39),
(15, 40),
(15, 41),
(15, 42),
(15, 43),
(15, 44),
(15, 45),
(15, 46),
(15, 47),
(15, 48),
(15, 49),
(15, 50),
(15, 51),
(15, 52),
(15, 53),
(15, 54),
(15, 55),
(15, 56),
(15, 57),
(15, 58),
(15, 59),
(15, 60),
(15, 61),
(15, 62),
(15, 63),
(15, 64),
(15, 65),
(15, 66),
(15, 67),
(15, 68),
(15, 69),
(15, 70),
(15, 71),
(15, 72),
(15, 73),
(15, 74),
(15, 75),
(15, 76),
(15, 77),
(15, 78),
(15, 79),
(15, 80),
(15, 81),
(15, 82),
(15, 83),
(15, 84),
(15, 85),
(15, 86),
(15, 87),
(15, 88),
(15, 90),
(15, 91),
(15, 92),
(15, 93),
(15, 94),
(15, 95),
(15, 96),
(16, 4),
(16, 5),
(16, 8),
(16, 9),
(16, 10),
(16, 11),
(16, 12),
(16, 13),
(16, 14),
(16, 15),
(16, 16),
(16, 17),
(16, 18),
(16, 19),
(16, 20),
(16, 21),
(16, 22),
(16, 23),
(16, 24),
(16, 25),
(16, 26),
(16, 27),
(16, 28),
(16, 29),
(16, 30),
(16, 31),
(16, 32),
(16, 33),
(16, 34),
(16, 35),
(16, 36),
(16, 37),
(16, 38),
(16, 39),
(16, 40),
(16, 41),
(16, 42),
(16, 43),
(16, 44),
(16, 45),
(16, 46),
(16, 47),
(16, 48),
(16, 49),
(16, 50),
(16, 51),
(16, 52),
(16, 53),
(16, 54),
(16, 55),
(16, 56),
(16, 57),
(16, 58),
(16, 59),
(16, 60),
(16, 61),
(16, 62),
(16, 63),
(16, 64),
(16, 65),
(16, 66),
(16, 67),
(16, 68),
(16, 69),
(16, 70),
(16, 71),
(16, 72),
(16, 73),
(16, 74),
(16, 75),
(16, 76),
(16, 77),
(16, 78),
(16, 79),
(16, 80),
(16, 81),
(16, 82),
(16, 83),
(16, 84),
(16, 86),
(16, 90),
(16, 92),
(16, 93),
(16, 94),
(16, 95),
(16, 96),
(17, 4),
(17, 8),
(17, 9),
(17, 10),
(17, 11),
(17, 12),
(17, 13),
(17, 14),
(17, 15),
(17, 16),
(17, 17),
(17, 18),
(17, 19),
(17, 20),
(17, 21),
(17, 22),
(17, 23),
(17, 24),
(17, 25),
(17, 26),
(17, 27),
(17, 28),
(17, 29),
(17, 30),
(17, 31),
(17, 32),
(17, 33),
(17, 34),
(17, 35),
(17, 36),
(17, 37),
(17, 38),
(17, 39),
(17, 40),
(17, 41),
(17, 42),
(17, 43),
(17, 44),
(17, 45),
(17, 46),
(17, 47),
(17, 48),
(17, 49),
(17, 50),
(17, 51),
(17, 52),
(17, 53),
(17, 54),
(17, 55),
(17, 56),
(17, 57),
(17, 58),
(17, 59),
(17, 60),
(17, 61),
(17, 62),
(17, 63),
(17, 64),
(17, 65),
(17, 66),
(17, 67),
(17, 68),
(17, 69),
(17, 70),
(17, 71),
(17, 72),
(17, 73),
(17, 74),
(17, 75),
(17, 76),
(17, 77),
(17, 78),
(17, 79),
(17, 80),
(17, 81),
(17, 82),
(17, 83),
(17, 84),
(17, 86),
(17, 90),
(17, 92),
(17, 93),
(17, 94),
(17, 95),
(17, 96),
(18, 4),
(18, 11),
(18, 17),
(18, 23),
(18, 29),
(18, 34),
(18, 35),
(18, 38),
(18, 42),
(18, 49),
(18, 51),
(18, 57),
(18, 59),
(18, 65),
(18, 67),
(18, 73),
(18, 74),
(18, 76),
(18, 84),
(18, 86),
(18, 92),
(19, 4),
(19, 5),
(19, 8),
(19, 9),
(19, 10),
(19, 11),
(19, 12),
(19, 13),
(19, 14),
(19, 15),
(19, 16),
(19, 17),
(19, 18),
(19, 19),
(19, 20),
(19, 21),
(19, 22),
(19, 23),
(19, 24),
(19, 25),
(19, 26),
(19, 27),
(19, 28),
(19, 29),
(19, 30),
(19, 31),
(19, 32),
(19, 33),
(19, 34),
(19, 35),
(19, 36),
(19, 37),
(19, 38),
(19, 39),
(19, 40),
(19, 41),
(19, 42),
(19, 43),
(19, 44),
(19, 45),
(19, 46),
(19, 47),
(19, 48),
(19, 49),
(19, 50),
(19, 51),
(19, 52),
(19, 53),
(19, 54),
(19, 55),
(19, 56),
(19, 57),
(19, 58),
(19, 59),
(19, 60),
(19, 61),
(19, 62),
(19, 63),
(19, 64),
(19, 65),
(19, 66),
(19, 67),
(19, 68),
(19, 69),
(19, 70),
(19, 71),
(19, 72),
(19, 73),
(19, 74),
(19, 75),
(19, 76),
(19, 77),
(19, 78),
(19, 79),
(19, 80),
(19, 81),
(19, 82),
(19, 83),
(19, 84),
(19, 85),
(19, 86),
(19, 90),
(19, 92),
(19, 93),
(19, 94),
(19, 95),
(19, 96),
(20, 1),
(20, 2),
(20, 3),
(20, 4),
(20, 5),
(20, 6),
(20, 7),
(20, 8),
(20, 9),
(20, 10),
(20, 11),
(20, 12),
(20, 13),
(20, 14),
(20, 15),
(20, 16),
(20, 17),
(20, 18),
(20, 19),
(20, 20),
(20, 21),
(20, 22),
(20, 23),
(20, 24),
(20, 25),
(20, 26),
(20, 27),
(20, 28),
(20, 29),
(20, 30),
(20, 31),
(20, 32),
(20, 33),
(20, 34),
(20, 35),
(20, 36),
(20, 37),
(20, 38),
(20, 39),
(20, 40),
(20, 41),
(20, 42),
(20, 43),
(20, 44),
(20, 45),
(20, 46),
(20, 47),
(20, 48),
(20, 49),
(20, 50),
(20, 51),
(20, 52),
(20, 53),
(20, 54),
(20, 55),
(20, 56),
(20, 57),
(20, 58),
(20, 59),
(20, 60),
(20, 61),
(20, 62),
(20, 63),
(20, 64),
(20, 65),
(20, 66),
(20, 67),
(20, 68),
(20, 69),
(20, 70),
(20, 71),
(20, 72),
(20, 73),
(20, 74),
(20, 75),
(20, 76),
(20, 77),
(20, 78),
(20, 79),
(20, 80),
(20, 81),
(20, 82),
(20, 83),
(20, 84),
(20, 85),
(20, 86),
(20, 87),
(20, 88),
(20, 90),
(20, 91),
(20, 92),
(20, 93),
(20, 94),
(20, 95),
(20, 96),
(21, 4),
(21, 5),
(21, 8),
(21, 9),
(21, 10),
(21, 11),
(21, 12),
(21, 13),
(21, 14),
(21, 15),
(21, 16),
(21, 17),
(21, 18),
(21, 19),
(21, 20),
(21, 21),
(21, 22),
(21, 23),
(21, 24),
(21, 25),
(21, 26),
(21, 27),
(21, 28),
(21, 29),
(21, 30),
(21, 31),
(21, 32),
(21, 33),
(21, 34),
(21, 35),
(21, 36),
(21, 37),
(21, 38),
(21, 39),
(21, 40),
(21, 41),
(21, 42),
(21, 43),
(21, 44),
(21, 45),
(21, 46),
(21, 47),
(21, 48),
(21, 49),
(21, 50),
(21, 51),
(21, 52),
(21, 53),
(21, 54),
(21, 55),
(21, 56),
(21, 57),
(21, 58),
(21, 59),
(21, 60),
(21, 61),
(21, 62),
(21, 63),
(21, 64),
(21, 65),
(21, 66),
(21, 67),
(21, 68),
(21, 69),
(21, 70),
(21, 71),
(21, 72),
(21, 73),
(21, 74),
(21, 75),
(21, 76),
(21, 77),
(21, 78),
(21, 79),
(21, 80),
(21, 81),
(21, 82),
(21, 83),
(21, 84),
(21, 86),
(21, 90),
(21, 92),
(21, 93),
(21, 94),
(21, 95),
(21, 96),
(22, 4),
(22, 8),
(22, 9),
(22, 10),
(22, 11),
(22, 12),
(22, 13),
(22, 14),
(22, 15),
(22, 16),
(22, 17),
(22, 18),
(22, 19),
(22, 20),
(22, 21),
(22, 22),
(22, 23),
(22, 24),
(22, 25),
(22, 26),
(22, 27),
(22, 28),
(22, 29),
(22, 30),
(22, 31),
(22, 32),
(22, 33),
(22, 34),
(22, 35),
(22, 36),
(22, 37),
(22, 38),
(22, 39),
(22, 40),
(22, 41),
(22, 42),
(22, 43),
(22, 44),
(22, 45),
(22, 46),
(22, 47),
(22, 48),
(22, 49),
(22, 50),
(22, 51),
(22, 52),
(22, 53),
(22, 54),
(22, 55),
(22, 56),
(22, 57),
(22, 58),
(22, 59),
(22, 60),
(22, 61),
(22, 62),
(22, 63),
(22, 64),
(22, 65),
(22, 66),
(22, 67),
(22, 68),
(22, 69),
(22, 70),
(22, 71),
(22, 72),
(22, 73),
(22, 74),
(22, 75),
(22, 76),
(22, 77),
(22, 78),
(22, 79),
(22, 80),
(22, 81),
(22, 82),
(22, 83),
(22, 84),
(22, 86),
(22, 90),
(22, 92),
(22, 93),
(22, 94),
(22, 95),
(22, 96),
(23, 4),
(23, 11),
(23, 17),
(23, 23),
(23, 29),
(23, 34),
(23, 35),
(23, 38),
(23, 42),
(23, 49),
(23, 51),
(23, 57),
(23, 59),
(23, 65),
(23, 67),
(23, 73),
(23, 74),
(23, 76),
(23, 84),
(23, 86),
(23, 92);

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
(14, 3, 6, 6, 'seguridad', 13, 'Constanza Pizarro', 5, '2026-08-19 11:00:00', 'Técnico ingresó a un área restringida sin autorización ni acompañamiento correspondiente.', 'baja', 2, 'javiera.reyes@test.engie.cl', '2026-08-19 11:00:00', '2026-08-19 11:00:00'),
(15, 4, 7, 7, 'seguridad', 18, 'Usuario DEMO', 3, '2026-09-07 19:53:15', '[DEMO-QA] Casi caída al mismo nivel en acceso principal sin lesión.', 'media', 1, 'UsuarioEmpresaDemo@demoSCT.cl', '2026-09-07 19:53:15', '2026-09-07 19:53:15'),
(16, 4, 8, 8, 'seguridad', 21, 'Diego Muñoz DEMO', 5, '2026-09-05 19:53:15', '[DEMO-QA] Operación de maquinaria sin completar el checklist previo de seguridad.', 'alta', 2, 'JefaturaEmpresaDemo@demoSCT.cl', '2026-09-05 19:53:15', '2026-09-06 19:53:15'),
(17, 4, 8, 8, 'seguridad', NULL, NULL, 4, '2026-08-27 19:53:15', '[DEMO-QA] Extintor con mantención vencida detectado en sector de bodega.', 'baja', 3, 'adminEmpresaDemo@demoSCT.cl', '2026-08-27 19:53:15', '2026-08-29 19:53:15'),
(18, 4, 9, 7, 'seguridad', 22, 'Valentina Pérez DEMO', 2, '2026-09-06 19:53:15', '[DEMO-QA] Golpe leve en mano durante manipulación de herramienta manual.', 'alta', 1, 'JefaturaEmpresaDemo@demoSCT.cl', '2026-09-06 19:53:15', '2026-09-06 19:53:15'),
(19, 4, 9, 7, 'seguridad', 23, 'Mauricio Silva DEMO', 1, '2026-09-02 19:53:15', '[DEMO-QA] Contacto eléctrico durante mantención con lesión y tiempo perdido.', 'critica', 2, 'adminEmpresaDemo@demoSCT.cl', '2026-09-02 19:53:15', '2026-09-04 19:53:15'),
(20, 4, 7, 9, 'seguridad', 24, 'Camila Reyes DEMO', 5, '2026-08-30 19:53:15', '[DEMO-QA] Uso de escalera portátil sin mantener tres puntos de apoyo.', 'media', 3, 'GerenteEmpresaDemo@demoSCT.cl', '2026-08-30 19:53:15', '2026-08-31 19:53:15'),
(21, 4, 7, 7, 'seguridad', 18, 'Usuario DEMO', 3, '2026-09-08 08:15:00', '[DEMO-EXT] Casi caída al mismo nivel por superficie húmeda en acceso principal.', 'media', 1, 'UsuarioEmpresaDemo@demoSCT.cl', '2026-09-08 08:15:00', '2026-09-08 08:15:00'),
(22, 4, 8, 8, 'seguridad', NULL, NULL, 4, '2026-09-03 10:00:00', '[DEMO-EXT] Extintor con fecha de mantención vencida detectado en bodega.', 'baja', 3, 'adminEmpresaDemo@demoSCT.cl', '2026-09-03 10:00:00', '2026-09-04 15:00:00'),
(23, 4, 8, 8, 'seguridad', 21, 'Diego Muñoz DEMO', 5, '2026-09-05 11:10:00', '[DEMO-EXT] Operación de maquinaria sin completar checklist previo de seguridad.', 'alta', 2, 'JefaturaEmpresaDemo@demoSCT.cl', '2026-09-05 11:10:00', '2026-09-07 16:00:00'),
(24, 4, 9, 7, 'seguridad', 22, 'Valentina Pérez DEMO', 2, '2026-09-06 14:35:00', '[DEMO-EXT] Golpe leve en mano durante manipulación de herramienta manual.', 'alta', 1, 'JefaturaEmpresaDemo@demoSCT.cl', '2026-09-06 14:35:00', '2026-09-06 14:35:00'),
(25, 4, 9, 7, 'seguridad', 23, 'Mauricio Silva DEMO', 1, '2026-09-02 09:45:00', '[DEMO-EXT] Contacto eléctrico durante mantención con lesión y tiempo perdido.', 'critica', 2, 'adminEmpresaDemo@demoSCT.cl', '2026-09-02 09:45:00', '2026-09-08 12:00:00'),
(26, 4, 7, 9, 'seguridad', 24, 'Camila Reyes DEMO', 3, '2026-08-30 16:20:00', '[DEMO-EXT] Herramienta cayó desde estantería sin alcanzar a trabajador.', 'media', 3, 'GerenteEmpresaDemo@demoSCT.cl', '2026-08-30 16:20:00', '2026-09-01 12:00:00'),
(27, 4, 8, 7, 'seguridad', 18, 'Usuario DEMO', 4, '2026-09-07 13:25:00', '[DEMO-EXT] Ruta de evacuación parcialmente obstruida por materiales almacenados.', 'alta', 2, 'UsuarioEmpresaDemo@demoSCT.cl', '2026-09-07 13:25:00', '2026-09-08 10:00:00'),
(28, 4, 9, 9, 'seguridad', 17, 'Jefatura DEMO', 5, '2026-09-08 15:00:00', '[DEMO-EXT] Uso de escalera portátil sin mantener tres puntos de apoyo.', 'media', 1, 'GerenteEmpresaDemo@demoSCT.cl', '2026-09-08 15:00:00', '2026-09-08 15:00:00'),
(29, 4, 7, 7, 'seguridad', 19, 'Administrador DEMO', 2, '2026-08-28 12:10:00', '[DEMO-EXT] Corte superficial durante apertura de embalaje, atendido en primeros auxilios.', 'baja', 3, 'adminEmpresaDemo@demoSCT.cl', '2026-08-28 12:10:00', '2026-08-28 17:00:00'),
(30, 4, 8, 9, 'seguridad', 20, 'Sofía Navarro DEMO', 4, '2026-09-08 17:05:00', '[DEMO-EXT] Señalética de obligación de EPP deteriorada en acceso a planta.', 'baja', 1, 'JefaturaEmpresaDemo@demoSCT.cl', '2026-09-08 17:05:00', '2026-09-08 17:05:00');

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
(12, 14, 'Se recordó al equipo el procedimiento de acceso a áreas restringidas y control de ingreso.', 'Javiera Reyes', '2026-08-19 00:00:00', '2026-08-21 23:59:59', 'javiera.reyes@test.engie.cl', '2026-08-20 12:00:00', '2026-08-20 12:00:00'),
(13, 16, 'Se instruyó detener la operación y completar capacitación y checklist antes de reiniciar.', 'Jefatura DEMO', '2026-09-06 19:53:16', '2026-09-11 19:53:16', 'JefaturaEmpresaDemo@demoSCT.cl', '2026-09-06 19:53:16', '2026-09-06 19:53:16'),
(14, 17, 'Extintor reemplazado y registro de inspección actualizado.', 'Administrador DEMO', '2026-08-28 19:53:16', '2026-08-29 19:53:16', 'adminEmpresaDemo@demoSCT.cl', '2026-08-29 19:53:16', '2026-08-29 19:53:16'),
(15, 19, 'Área aislada, investigación iniciada y revisión de procedimiento LOTO en curso.', 'Administrador DEMO', '2026-09-03 19:53:16', '2026-09-10 19:53:16', 'adminEmpresaDemo@demoSCT.cl', '2026-09-03 19:53:16', '2026-09-04 19:53:16'),
(16, 20, 'Se realizó retroalimentación inmediata y verificación posterior de técnica segura.', 'Gerente DEMO', '2026-08-31 19:53:16', '2026-09-01 19:53:16', 'GerenteEmpresaDemo@demoSCT.cl', '2026-08-31 19:53:16', '2026-08-31 19:53:16'),
(17, 22, 'Equipo retirado de servicio y reemplazado por extintor vigente.', 'Administrador DEMO', '2026-09-03 11:00:00', '2026-09-04 18:00:00', 'adminEmpresaDemo@demoSCT.cl', '2026-09-03 11:00:00', '2026-09-04 15:00:00'),
(18, 23, 'Se detuvo la operación y se solicitó completar checklist y reinducción antes de reiniciar.', 'Jefatura DEMO', '2026-09-05 12:00:00', '2026-09-09 18:00:00', 'JefaturaEmpresaDemo@demoSCT.cl', '2026-09-05 12:00:00', '2026-09-05 12:00:00'),
(19, 23, 'Checklist corregido; pendiente validación final de autorización del operador.', 'Sofía Navarro DEMO', '2026-09-07 10:00:00', '2026-09-10 18:00:00', 'JefaturaEmpresaDemo@demoSCT.cl', '2026-09-07 10:00:00', '2026-09-07 16:00:00'),
(20, 25, 'Área aislada y trabajador derivado a evaluación médica.', 'Administrador DEMO', '2026-09-02 10:00:00', '2026-09-02 14:00:00', 'adminEmpresaDemo@demoSCT.cl', '2026-09-02 10:00:00', '2026-09-02 14:00:00'),
(21, 25, 'Investigación de causa raíz iniciada y procedimiento LOTO en revisión.', 'Gerente DEMO', '2026-09-03 09:00:00', '2026-09-12 18:00:00', 'GerenteEmpresaDemo@demoSCT.cl', '2026-09-03 09:00:00', '2026-09-08 12:00:00'),
(22, 26, 'Se reorganizó la estantería y se incorporaron topes de retención.', 'Jefatura DEMO', '2026-08-31 09:00:00', '2026-09-01 18:00:00', 'JefaturaEmpresaDemo@demoSCT.cl', '2026-08-31 09:00:00', '2026-09-01 12:00:00'),
(23, 27, 'Se retiró parte del material y se asignó responsable para despeje completo.', 'Jefatura DEMO', '2026-09-07 14:00:00', '2026-09-09 12:00:00', 'JefaturaEmpresaDemo@demoSCT.cl', '2026-09-07 14:00:00', '2026-09-08 10:00:00'),
(24, 29, 'Se entregó atención de primeros auxilios y se revisó el uso de herramientas de corte.', 'Sofía Navarro DEMO', '2026-08-28 12:30:00', '2026-08-28 17:00:00', 'adminEmpresaDemo@demoSCT.cl', '2026-08-28 12:30:00', '2026-08-28 17:00:00');

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
(5, 4, 'Manual de Trabajo en Altura (documento de referencia)', 'documento', 'https://example.com/placeholder/manual-trabajo-en-altura.pdf', NULL, 1, 'seed_test_data', '2026-09-08 09:15:00'),
(6, 5, 'Bienvenida Inducción DEMO', 'texto', NULL, 'Material de demostración. Revisa conceptos de reporte de riesgos, EPP, near miss y conductas seguras antes de responder.', 1, 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 19:53:15'),
(7, 9, 'Guía DEMO para contratistas', 'texto', NULL, 'Antes de ejecutar trabajos revisa EPP, autorización para equipos, reporte de near miss y medidas para trabajo en altura.', 1, 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 23:02:46');

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
  `state` int(11) NOT NULL,
  `language` varchar(11) NOT NULL,
  `profile_photo_path` varchar(255) DEFAULT NULL COMMENT 'Ruta relativa de fotografía/avatar de la cuenta',
  `last_access` datetime NOT NULL,
  `created_by` varchar(50) NOT NULL,
  `date_create` datetime NOT NULL,
  `last_update` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id_users`, `id_company`, `id_worker`, `name`, `lastname`, `rut`, `state`, `language`, `profile_photo_path`, `last_access`, `created_by`, `date_create`, `last_update`) VALUES
('admin.completo.test@test.helheim.cl', 1, NULL, 'Diego', 'Herrera', '18102198-5', 1, 'es', NULL, '2026-09-08 15:00:26', 'seed_test_data', '2026-08-04 09:00:00', '2026-08-04 09:00:00'),
('admin.completo.test@test.tecaivot.cl', 2, NULL, 'Francisca', 'Muñoz', '18102347-3', 1, 'es', NULL, '1970-01-01 00:00:00', 'seed_test_data', '2026-08-04 09:30:00', '2026-08-04 09:30:00'),
('admin.test@test.engie.cl', 3, NULL, 'Marcela', 'Vega', '18102701-0', 1, 'es', NULL, '1970-01-01 00:00:00', 'seed_test_data', '2026-09-08 08:30:00', '2026-09-08 08:30:00'),
('admin.test@test.tecaivot.cl', 2, NULL, 'Andrés', 'Pizarro', '18102439-9', 1, 'es', NULL, '1970-01-01 00:00:00', 'seed_test_data', '2026-08-04 09:30:00', '2026-08-04 09:30:00'),
('adminEmpresaDemo@demoSCT.cl', 4, 19, 'Administrador', 'DEMO', '44444444-4', 1, 'es', NULL, '2026-09-11 16:43:23', 'seed_demo_sct', '2026-09-08 18:43:22', '2026-09-08 18:43:22'),
('barbara.contreras@test.helheim.cl', 1, 1, 'Bárbara', 'Contreras', '18100200-K', 1, 'es', NULL, '1970-01-01 00:00:00', 'seed_test_data', '2026-08-05 09:00:00', '2026-08-05 09:00:00'),
('camila.soto@test.tecaivot.cl', 2, 3, 'Camila', 'Soto', '18100308-1', 1, 'es', NULL, '1970-01-01 00:00:00', 'seed_test_data', '2026-08-05 09:30:00', '2026-08-05 09:30:00'),
('cliente.test@test.helheim.cl', 1, NULL, 'Ignacio', 'Bravo', '18102287-6', 1, 'es', NULL, '1970-01-01 00:00:00', 'seed_test_data', '2026-08-04 09:00:00', '2026-08-04 09:00:00'),
('cristobal.fuentes@test.engie.cl', 3, 6, 'Cristóbal', 'Fuentes', '18100740-0', 1, 'es', NULL, '1970-01-01 00:00:00', 'seed_test_data', '2026-09-08 09:00:00', '2026-09-08 09:00:00'),
('fco.fredes.g@gmail.com', 2, NULL, 'francisco', 'fredes', '1234', 1, 'es', NULL, '2026-09-08 14:44:10', 'phpmyadmin', '2026-08-15 11:58:40', '2026-08-15 11:58:40'),
('francisca.torres@test.engie.cl', 3, 9, 'Francisca', 'Torres', '18101131-9', 1, 'es', NULL, '1970-01-01 00:00:00', 'seed_test_data', '2026-09-08 09:00:00', '2026-09-08 09:00:00'),
('Francisco.fredes@engie.com', 3, NULL, 'francisco', 'fredes', '18102892-0', 1, 'es', NULL, '2026-09-08 01:13:19', 'phpmyadmin', '2026-09-08 01:13:19', '2026-09-08 01:13:19'),
('GerenteEmpresaDemo@demoSCT.cl', 4, 16, 'Gerente', 'DEMO', '11111111-1', 1, 'es', NULL, '2026-09-11 16:40:05', 'seed_demo_sct', '2026-09-08 18:43:22', '2026-09-10 00:52:32'),
('javiera.reyes@test.engie.cl', 3, 11, 'Javiera', 'Reyes', '18101404-0', 1, 'es', NULL, '1970-01-01 00:00:00', 'seed_test_data', '2026-09-08 09:00:00', '2026-09-08 09:00:00'),
('jefatura.test@test.helheim.cl', 1, NULL, 'Fernanda', 'Vidal', '18102243-4', 1, 'es', NULL, '1970-01-01 00:00:00', 'seed_test_data', '2026-08-04 09:00:00', '2026-08-04 09:00:00'),
('jefatura.test@test.tecaivot.cl', 2, NULL, 'Daniela', 'Contreras', '18102535-2', 1, 'es', NULL, '1970-01-01 00:00:00', 'seed_test_data', '2026-08-04 09:30:00', '2026-08-04 09:30:00'),
('JefaturaEmpresaDemo@demoSCT.cl', 4, 17, 'Jefatura', 'DEMO', '22222222-2', 1, 'es', NULL, '2026-09-11 11:03:35', 'seed_demo_sct', '2026-09-08 18:43:22', '2026-09-08 18:43:22'),
('jonathan.vera@engie.com', 3, NULL, 'Jonathan', 'Vera', '18102935-8', 1, 'es', NULL, '2026-09-08 01:11:21', 'phpmyadmin', '2026-09-08 01:11:21', '2026-09-08 01:11:21'),
('juanantonioconchaloyola@gmail.com', 1, NULL, 'antonio', 'helheim', '16725278-8', 1, 'es', 'uploads/usuarios/user_f4bcfaca519e256591fc7ccfd7734a32066c00d3302d3371653a5112e5208874_514070f45bc8a5fd.png', '2026-09-11 16:44:09', 'phpmyadmin', '2026-08-14 20:46:30', '2026-09-08 16:47:54'),
('Laura.Lira@external.engie.com', 3, NULL, 'Laura', 'Lira', '18103202-2', 1, 'es', NULL, '2026-09-08 01:12:37', 'phpmyadmin', '2026-09-08 01:12:37', '2026-09-08 01:12:37'),
('malazga99@gmail.com', 1, NULL, 'maite', 'lazcano', '20153481-k', 1, 'es', NULL, '2026-09-08 15:53:29', 'phpmyadmin', '2026-08-14 21:41:57', '2026-08-14 21:41:57'),
('pablotroncoso@gmail.com', 1, NULL, 'pablo', 'troncoso', '123456789', 1, 'es', NULL, '2026-09-08 14:48:40', 'phpmyadmin', '2026-08-14 21:43:14', '2026-08-14 21:43:14'),
('patricio.gomez@test.engie.cl', 3, 7, 'Patricio', 'Gómez', '18100834-2', 1, 'es', NULL, '1970-01-01 00:00:00', 'seed_test_data', '2026-09-08 09:00:00', '2026-09-08 09:00:00'),
('UsuarioEmpresaDemo@demoSCT.cl', 4, 18, 'Usuario', 'DEMO', '33333333-3', 1, 'es', NULL, '2026-09-11 16:50:36', 'seed_demo_sct', '2026-09-08 18:43:22', '2026-09-08 18:43:22'),
('wagner.leite@engie.com', 3, NULL, 'Wagner', 'Leite', '18103115-8', 1, 'es', NULL, '2026-09-08 01:10:12', 'phpmyadmin', '2026-09-08 01:10:12', '2026-09-08 01:10:12');

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
(1, 'juanantonioconchaloyola@gmail.com', 9, 1, 'migracion', '2026-08-20 17:42:42', '2026-09-08 16:47:54'),
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
(24, 'Laura.Lira@external.engie.com', 18, 1, 'seed_test_data', '2026-09-08 08:00:00', '2026-09-08 08:00:00'),
(25, 'GerenteEmpresaDemo@demoSCT.cl', 21, 1, 'seed_demo_sct', '2026-09-08 18:43:22', '2026-09-08 18:43:22'),
(26, 'JefaturaEmpresaDemo@demoSCT.cl', 22, 1, 'seed_demo_sct', '2026-09-08 18:43:22', '2026-09-08 18:43:22'),
(27, 'UsuarioEmpresaDemo@demoSCT.cl', 23, 1, 'seed_demo_sct', '2026-09-08 18:43:22', '2026-09-08 18:43:22'),
(28, 'adminEmpresaDemo@demoSCT.cl', 19, 1, 'seed_demo_sct', '2026-09-08 18:43:22', '2026-09-08 18:43:22');

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
(18, 3, 'trabajador', 'Trabajador en terreno', 1, 'seed_test_data', '2026-09-08 08:00:00', '2026-09-08 08:00:00'),
(19, 4, 'administrador', 'Acceso completo a la empresa', 1, 'seed_demo_sct', '2026-09-08 18:43:22', '2026-09-08 18:43:22'),
(20, 4, 'administrador_completo', 'Administrador global', 1, 'seed_demo_sct', '2026-09-08 18:43:22', '2026-09-08 18:43:22'),
(21, 4, 'cliente', 'Representante de la empresa cliente', 1, 'seed_demo_sct', '2026-09-08 18:43:22', '2026-09-08 18:43:22'),
(22, 4, 'jefatura', 'Jefatura de empresa', 1, 'seed_demo_sct', '2026-09-08 18:43:22', '2026-09-08 18:43:22'),
(23, 4, 'trabajador', 'Trabajador en terreno', 1, 'seed_demo_sct', '2026-09-08 18:43:22', '2026-09-08 18:43:22');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users_test_answers`
--

CREATE TABLE `users_test_answers` (
  `id_users_test_answers` int(11) NOT NULL,
  `id_users` varchar(50) NOT NULL COMMENT 'FK a users.id_users (email)',
  `id_company` int(11) NOT NULL,
  `id_test` int(11) NOT NULL,
  `id_user_test_assigned` int(11) DEFAULT NULL,
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

INSERT INTO `users_test_answers` (`id_users_test_answers`, `id_users`, `id_company`, `id_test`, `id_user_test_assigned`, `id_test_try`, `id_rel`, `id_question`, `id_questions_options`, `date_create`, `last_update`) VALUES
(1, 'barbara.contreras@test.helheim.cl', 1, 1, NULL, 1, 1, 1, 1, '2026-08-11 10:00:00', '2026-08-11 10:00:00'),
(2, 'barbara.contreras@test.helheim.cl', 1, 1, NULL, 1, 2, 2, 5, '2026-08-11 10:00:00', '2026-08-11 10:00:00'),
(3, 'barbara.contreras@test.helheim.cl', 1, 1, NULL, 1, 3, 3, 9, '2026-08-11 10:00:00', '2026-08-11 10:00:00'),
(4, 'barbara.contreras@test.helheim.cl', 1, 1, NULL, 1, 4, 4, 14, '2026-08-11 10:00:00', '2026-08-11 10:00:00'),
(5, 'barbara.contreras@test.helheim.cl', 1, 1, NULL, 1, 5, 5, 18, '2026-08-11 10:00:00', '2026-08-11 10:00:00'),
(6, 'barbara.contreras@test.helheim.cl', 1, 1, NULL, 1, 6, 6, 22, '2026-08-11 10:00:00', '2026-08-11 10:00:00'),
(7, 'cliente.test@test.helheim.cl', 1, 1, NULL, 1, 1, 1, 1, '2026-08-11 10:00:00', '2026-08-11 10:00:00'),
(8, 'cliente.test@test.helheim.cl', 1, 1, NULL, 1, 2, 2, 5, '2026-08-11 10:00:00', '2026-08-11 10:00:00'),
(9, 'cliente.test@test.helheim.cl', 1, 1, NULL, 1, 3, 3, 9, '2026-08-11 10:00:00', '2026-08-11 10:00:00'),
(10, 'cliente.test@test.helheim.cl', 1, 1, NULL, 1, 4, 4, 13, '2026-08-11 10:00:00', '2026-08-11 10:00:00'),
(11, 'cliente.test@test.helheim.cl', 1, 1, NULL, 1, 5, 5, 17, '2026-08-11 10:00:00', '2026-08-11 10:00:00'),
(12, 'cliente.test@test.helheim.cl', 1, 1, NULL, 1, 6, 6, 21, '2026-08-11 10:00:00', '2026-08-11 10:00:00'),
(13, 'cliente.test@test.helheim.cl', 1, 1, NULL, 2, 1, 1, 1, '2026-08-12 10:00:00', '2026-08-12 10:00:00'),
(14, 'cliente.test@test.helheim.cl', 1, 1, NULL, 2, 2, 2, 5, '2026-08-12 10:00:00', '2026-08-12 10:00:00'),
(15, 'cliente.test@test.helheim.cl', 1, 1, NULL, 2, 3, 3, 10, '2026-08-12 10:00:00', '2026-08-12 10:00:00'),
(16, 'cliente.test@test.helheim.cl', 1, 1, NULL, 2, 4, 4, 13, '2026-08-12 10:00:00', '2026-08-12 10:00:00'),
(17, 'cliente.test@test.helheim.cl', 1, 1, NULL, 2, 5, 5, 17, '2026-08-12 10:00:00', '2026-08-12 10:00:00'),
(18, 'cliente.test@test.helheim.cl', 1, 1, NULL, 2, 6, 6, 21, '2026-08-12 10:00:00', '2026-08-12 10:00:00'),
(19, 'camila.soto@test.tecaivot.cl', 2, 2, NULL, 1, 7, 1, 1, '2026-08-12 10:00:00', '2026-08-12 10:00:00'),
(20, 'camila.soto@test.tecaivot.cl', 2, 2, NULL, 1, 8, 2, 5, '2026-08-12 10:00:00', '2026-08-12 10:00:00'),
(21, 'camila.soto@test.tecaivot.cl', 2, 2, NULL, 1, 9, 3, 9, '2026-08-12 10:00:00', '2026-08-12 10:00:00'),
(22, 'camila.soto@test.tecaivot.cl', 2, 2, NULL, 1, 10, 4, 14, '2026-08-12 10:00:00', '2026-08-12 10:00:00'),
(23, 'camila.soto@test.tecaivot.cl', 2, 2, NULL, 1, 11, 5, 18, '2026-08-12 10:00:00', '2026-08-12 10:00:00'),
(24, 'camila.soto@test.tecaivot.cl', 2, 2, NULL, 1, 12, 6, 22, '2026-08-12 10:00:00', '2026-08-12 10:00:00'),
(25, 'admin.test@test.tecaivot.cl', 2, 2, NULL, 1, 7, 1, 1, '2026-08-12 10:00:00', '2026-08-12 10:00:00'),
(26, 'admin.test@test.tecaivot.cl', 2, 2, NULL, 1, 8, 2, 5, '2026-08-12 10:00:00', '2026-08-12 10:00:00'),
(27, 'admin.test@test.tecaivot.cl', 2, 2, NULL, 1, 9, 3, 9, '2026-08-12 10:00:00', '2026-08-12 10:00:00'),
(28, 'admin.test@test.tecaivot.cl', 2, 2, NULL, 1, 10, 4, 14, '2026-08-12 10:00:00', '2026-08-12 10:00:00'),
(29, 'admin.test@test.tecaivot.cl', 2, 2, NULL, 1, 11, 5, 18, '2026-08-12 10:00:00', '2026-08-12 10:00:00'),
(30, 'admin.test@test.tecaivot.cl', 2, 2, NULL, 1, 12, 6, 21, '2026-08-12 10:00:00', '2026-08-12 10:00:00'),
(31, 'patricio.gomez@test.engie.cl', 3, 3, NULL, 1, 13, 1, 1, '2026-08-16 10:00:00', '2026-08-16 10:00:00'),
(32, 'patricio.gomez@test.engie.cl', 3, 3, NULL, 1, 14, 2, 5, '2026-08-16 10:00:00', '2026-08-16 10:00:00'),
(33, 'patricio.gomez@test.engie.cl', 3, 3, NULL, 1, 15, 3, 9, '2026-08-16 10:00:00', '2026-08-16 10:00:00'),
(34, 'patricio.gomez@test.engie.cl', 3, 3, NULL, 1, 16, 4, 14, '2026-08-16 10:00:00', '2026-08-16 10:00:00'),
(35, 'patricio.gomez@test.engie.cl', 3, 3, NULL, 1, 17, 5, 18, '2026-08-16 10:00:00', '2026-08-16 10:00:00'),
(36, 'patricio.gomez@test.engie.cl', 3, 3, NULL, 1, 18, 6, 22, '2026-08-16 10:00:00', '2026-08-16 10:00:00'),
(37, 'patricio.gomez@test.engie.cl', 3, 3, NULL, 1, 19, 7, 25, '2026-08-16 10:00:00', '2026-08-16 10:00:00'),
(38, 'patricio.gomez@test.engie.cl', 3, 3, NULL, 1, 20, 8, 29, '2026-08-16 10:00:00', '2026-08-16 10:00:00'),
(39, 'patricio.gomez@test.engie.cl', 3, 3, NULL, 1, 21, 9, 33, '2026-08-16 10:00:00', '2026-08-16 10:00:00'),
(40, 'patricio.gomez@test.engie.cl', 3, 3, NULL, 1, 22, 10, 38, '2026-08-16 10:00:00', '2026-08-16 10:00:00'),
(41, 'francisca.torres@test.engie.cl', 3, 3, NULL, 1, 13, 1, 1, '2026-08-16 10:00:00', '2026-08-16 10:00:00'),
(42, 'francisca.torres@test.engie.cl', 3, 3, NULL, 1, 14, 2, 5, '2026-08-16 10:00:00', '2026-08-16 10:00:00'),
(43, 'francisca.torres@test.engie.cl', 3, 3, NULL, 1, 15, 3, 9, '2026-08-16 10:00:00', '2026-08-16 10:00:00'),
(44, 'francisca.torres@test.engie.cl', 3, 3, NULL, 1, 16, 4, 14, '2026-08-16 10:00:00', '2026-08-16 10:00:00'),
(45, 'francisca.torres@test.engie.cl', 3, 3, NULL, 1, 17, 5, 18, '2026-08-16 10:00:00', '2026-08-16 10:00:00'),
(46, 'francisca.torres@test.engie.cl', 3, 3, NULL, 1, 18, 6, 22, '2026-08-16 10:00:00', '2026-08-16 10:00:00'),
(47, 'francisca.torres@test.engie.cl', 3, 3, NULL, 1, 19, 7, 25, '2026-08-16 10:00:00', '2026-08-16 10:00:00'),
(48, 'francisca.torres@test.engie.cl', 3, 3, NULL, 1, 20, 8, 29, '2026-08-16 10:00:00', '2026-08-16 10:00:00'),
(49, 'francisca.torres@test.engie.cl', 3, 3, NULL, 1, 21, 9, 33, '2026-08-16 10:00:00', '2026-08-16 10:00:00'),
(50, 'francisca.torres@test.engie.cl', 3, 3, NULL, 1, 22, 10, 37, '2026-08-16 10:00:00', '2026-08-16 10:00:00'),
(51, 'javiera.reyes@test.engie.cl', 3, 3, NULL, 1, 13, 1, 1, '2026-08-16 10:00:00', '2026-08-16 10:00:00'),
(52, 'javiera.reyes@test.engie.cl', 3, 3, NULL, 1, 14, 2, 5, '2026-08-16 10:00:00', '2026-08-16 10:00:00'),
(53, 'javiera.reyes@test.engie.cl', 3, 3, NULL, 1, 15, 3, 9, '2026-08-16 10:00:00', '2026-08-16 10:00:00'),
(54, 'javiera.reyes@test.engie.cl', 3, 3, NULL, 1, 16, 4, 14, '2026-08-16 10:00:00', '2026-08-16 10:00:00'),
(55, 'javiera.reyes@test.engie.cl', 3, 3, NULL, 1, 17, 5, 18, '2026-08-16 10:00:00', '2026-08-16 10:00:00'),
(56, 'javiera.reyes@test.engie.cl', 3, 3, NULL, 1, 18, 6, 22, '2026-08-16 10:00:00', '2026-08-16 10:00:00'),
(57, 'javiera.reyes@test.engie.cl', 3, 3, NULL, 1, 19, 7, 26, '2026-08-16 10:00:00', '2026-08-16 10:00:00'),
(58, 'javiera.reyes@test.engie.cl', 3, 3, NULL, 1, 20, 8, 30, '2026-08-16 10:00:00', '2026-08-16 10:00:00'),
(59, 'javiera.reyes@test.engie.cl', 3, 3, NULL, 1, 21, 9, 34, '2026-08-16 10:00:00', '2026-08-16 10:00:00'),
(60, 'javiera.reyes@test.engie.cl', 3, 3, NULL, 1, 22, 10, 38, '2026-08-16 10:00:00', '2026-08-16 10:00:00'),
(61, 'javiera.reyes@test.engie.cl', 3, 3, NULL, 2, 13, 1, 1, '2026-08-17 10:00:00', '2026-08-17 10:00:00'),
(62, 'javiera.reyes@test.engie.cl', 3, 3, NULL, 2, 14, 2, 5, '2026-08-17 10:00:00', '2026-08-17 10:00:00'),
(63, 'javiera.reyes@test.engie.cl', 3, 3, NULL, 2, 15, 3, 9, '2026-08-17 10:00:00', '2026-08-17 10:00:00'),
(64, 'javiera.reyes@test.engie.cl', 3, 3, NULL, 2, 16, 4, 14, '2026-08-17 10:00:00', '2026-08-17 10:00:00'),
(65, 'javiera.reyes@test.engie.cl', 3, 3, NULL, 2, 17, 5, 18, '2026-08-17 10:00:00', '2026-08-17 10:00:00'),
(66, 'javiera.reyes@test.engie.cl', 3, 3, NULL, 2, 18, 6, 22, '2026-08-17 10:00:00', '2026-08-17 10:00:00'),
(67, 'javiera.reyes@test.engie.cl', 3, 3, NULL, 2, 19, 7, 25, '2026-08-17 10:00:00', '2026-08-17 10:00:00'),
(68, 'javiera.reyes@test.engie.cl', 3, 3, NULL, 2, 20, 8, 30, '2026-08-17 10:00:00', '2026-08-17 10:00:00'),
(69, 'javiera.reyes@test.engie.cl', 3, 3, NULL, 2, 21, 9, 34, '2026-08-17 10:00:00', '2026-08-17 10:00:00'),
(70, 'javiera.reyes@test.engie.cl', 3, 3, NULL, 2, 22, 10, 38, '2026-08-17 10:00:00', '2026-08-17 10:00:00'),
(71, 'francisca.torres@test.engie.cl', 3, 4, NULL, 1, 23, 1, 1, '2026-08-21 10:00:00', '2026-08-21 10:00:00'),
(72, 'francisca.torres@test.engie.cl', 3, 4, NULL, 1, 24, 2, 5, '2026-08-21 10:00:00', '2026-08-21 10:00:00'),
(73, 'francisca.torres@test.engie.cl', 3, 4, NULL, 1, 25, 11, 41, '2026-08-21 10:00:00', '2026-08-21 10:00:00'),
(74, 'francisca.torres@test.engie.cl', 3, 4, NULL, 1, 26, 12, 45, '2026-08-21 10:00:00', '2026-08-21 10:00:00'),
(75, 'javiera.reyes@test.engie.cl', 3, 4, NULL, 1, 23, 1, 1, '2026-08-21 10:00:00', '2026-08-21 10:00:00'),
(76, 'javiera.reyes@test.engie.cl', 3, 4, NULL, 1, 24, 2, 5, '2026-08-21 10:00:00', '2026-08-21 10:00:00'),
(77, 'javiera.reyes@test.engie.cl', 3, 4, NULL, 1, 25, 11, 41, '2026-08-21 10:00:00', '2026-08-21 10:00:00'),
(78, 'javiera.reyes@test.engie.cl', 3, 4, NULL, 1, 26, 12, 46, '2026-08-21 10:00:00', '2026-08-21 10:00:00'),
(79, 'JefaturaEmpresaDemo@demoSCT.cl', 4, 5, 17, 1, 29, 1, 1, '2026-08-29 19:53:15', '2026-08-29 19:53:15'),
(80, 'JefaturaEmpresaDemo@demoSCT.cl', 4, 5, 17, 1, 30, 2, 5, '2026-08-29 19:53:15', '2026-08-29 19:53:15'),
(81, 'JefaturaEmpresaDemo@demoSCT.cl', 4, 5, 17, 1, 31, 3, 9, '2026-08-29 19:53:15', '2026-08-29 19:53:15'),
(82, 'JefaturaEmpresaDemo@demoSCT.cl', 4, 5, 17, 1, 32, 4, 14, '2026-08-29 19:53:15', '2026-08-29 19:53:15'),
(83, 'JefaturaEmpresaDemo@demoSCT.cl', 4, 5, 17, 1, 33, 5, 18, '2026-08-29 19:53:15', '2026-08-29 19:53:15'),
(84, 'JefaturaEmpresaDemo@demoSCT.cl', 4, 5, 17, 1, 34, 6, 21, '2026-08-29 19:53:15', '2026-08-29 19:53:15'),
(86, 'GerenteEmpresaDemo@demoSCT.cl', 4, 5, 18, 1, 29, 1, 1, '2026-08-31 19:53:15', '2026-08-31 19:53:15'),
(87, 'GerenteEmpresaDemo@demoSCT.cl', 4, 5, 18, 1, 30, 2, 5, '2026-08-31 19:53:15', '2026-08-31 19:53:15'),
(88, 'GerenteEmpresaDemo@demoSCT.cl', 4, 5, 18, 1, 31, 3, 9, '2026-08-31 19:53:15', '2026-08-31 19:53:15'),
(89, 'GerenteEmpresaDemo@demoSCT.cl', 4, 5, 18, 1, 32, 4, 13, '2026-08-31 19:53:15', '2026-08-31 19:53:15'),
(90, 'GerenteEmpresaDemo@demoSCT.cl', 4, 5, 18, 1, 33, 5, 17, '2026-08-31 19:53:15', '2026-08-31 19:53:15'),
(91, 'GerenteEmpresaDemo@demoSCT.cl', 4, 5, 18, 1, 34, 6, 21, '2026-08-31 19:53:15', '2026-08-31 19:53:15'),
(93, 'GerenteEmpresaDemo@demoSCT.cl', 4, 5, 18, 2, 29, 1, 1, '2026-09-01 19:53:15', '2026-09-01 19:53:15'),
(94, 'GerenteEmpresaDemo@demoSCT.cl', 4, 5, 18, 2, 30, 2, 5, '2026-09-01 19:53:15', '2026-09-01 19:53:15'),
(95, 'GerenteEmpresaDemo@demoSCT.cl', 4, 5, 18, 2, 31, 3, 9, '2026-09-01 19:53:15', '2026-09-01 19:53:15'),
(96, 'GerenteEmpresaDemo@demoSCT.cl', 4, 5, 18, 2, 32, 4, 14, '2026-09-01 19:53:15', '2026-09-01 19:53:15'),
(97, 'GerenteEmpresaDemo@demoSCT.cl', 4, 5, 18, 2, 33, 5, 17, '2026-09-01 19:53:15', '2026-09-01 19:53:15'),
(98, 'GerenteEmpresaDemo@demoSCT.cl', 4, 5, 18, 2, 34, 6, 21, '2026-09-01 19:53:15', '2026-09-01 19:53:15'),
(100, 'JefaturaEmpresaDemo@demoSCT.cl', 4, 6, 20, 1, 36, 1, 1, '2026-09-05 19:53:15', '2026-09-05 19:53:15'),
(101, 'JefaturaEmpresaDemo@demoSCT.cl', 4, 6, 20, 1, 37, 2, 5, '2026-09-05 19:53:15', '2026-09-05 19:53:15'),
(102, 'JefaturaEmpresaDemo@demoSCT.cl', 4, 6, 20, 1, 38, 4, 14, '2026-09-05 19:53:15', '2026-09-05 19:53:15'),
(103, 'JefaturaEmpresaDemo@demoSCT.cl', 4, 6, 20, 1, 39, 5, 18, '2026-09-05 19:53:15', '2026-09-05 19:53:15'),
(104, 'JefaturaEmpresaDemo@demoSCT.cl', 4, 6, 20, 1, 40, 9, 34, '2026-09-05 19:53:15', '2026-09-05 19:53:15'),
(105, 'JefaturaEmpresaDemo@demoSCT.cl', 4, 6, 20, 1, 41, 10, 38, '2026-09-05 19:53:15', '2026-09-05 19:53:15'),
(107, 'UsuarioEmpresaDemo@demoSCT.cl', 4, 6, 21, 1, 36, 1, 1, '2026-09-06 19:53:15', '2026-09-06 19:53:15'),
(108, 'UsuarioEmpresaDemo@demoSCT.cl', 4, 6, 21, 1, 37, 2, 5, '2026-09-06 19:53:15', '2026-09-06 19:53:15'),
(109, 'UsuarioEmpresaDemo@demoSCT.cl', 4, 6, 21, 1, 38, 4, 14, '2026-09-06 19:53:15', '2026-09-06 19:53:15'),
(110, 'UsuarioEmpresaDemo@demoSCT.cl', 4, 6, 21, 1, 39, 5, 18, '2026-09-06 19:53:15', '2026-09-06 19:53:15'),
(111, 'UsuarioEmpresaDemo@demoSCT.cl', 4, 6, 21, 1, 40, 9, 33, '2026-09-06 19:53:15', '2026-09-06 19:53:15'),
(112, 'UsuarioEmpresaDemo@demoSCT.cl', 4, 6, 21, 1, 41, 10, 37, '2026-09-06 19:53:15', '2026-09-06 19:53:15'),
(114, 'adminEmpresaDemo@demoSCT.cl', 4, 6, 22, 1, 36, 1, 1, '2026-09-06 19:53:15', '2026-09-06 19:53:15'),
(115, 'adminEmpresaDemo@demoSCT.cl', 4, 6, 22, 1, 37, 2, 5, '2026-09-06 19:53:15', '2026-09-06 19:53:15'),
(116, 'adminEmpresaDemo@demoSCT.cl', 4, 6, 22, 1, 38, 4, 14, '2026-09-06 19:53:15', '2026-09-06 19:53:15'),
(117, 'adminEmpresaDemo@demoSCT.cl', 4, 6, 22, 1, 39, 5, 17, '2026-09-06 19:53:15', '2026-09-06 19:53:15'),
(118, 'adminEmpresaDemo@demoSCT.cl', 4, 6, 22, 1, 40, 9, 34, '2026-09-06 19:53:15', '2026-09-06 19:53:15'),
(119, 'adminEmpresaDemo@demoSCT.cl', 4, 6, 22, 1, 41, 10, 38, '2026-09-06 19:53:15', '2026-09-06 19:53:15'),
(121, 'adminEmpresaDemo@demoSCT.cl', 4, 6, 22, 2, 36, 1, 1, '2026-09-07 19:53:15', '2026-09-07 19:53:15'),
(122, 'adminEmpresaDemo@demoSCT.cl', 4, 6, 22, 2, 37, 2, 5, '2026-09-07 19:53:15', '2026-09-07 19:53:15'),
(123, 'adminEmpresaDemo@demoSCT.cl', 4, 6, 22, 2, 38, 4, 14, '2026-09-07 19:53:15', '2026-09-07 19:53:15'),
(124, 'adminEmpresaDemo@demoSCT.cl', 4, 6, 22, 2, 39, 5, 18, '2026-09-07 19:53:15', '2026-09-07 19:53:15'),
(125, 'adminEmpresaDemo@demoSCT.cl', 4, 6, 22, 2, 40, 9, 34, '2026-09-07 19:53:15', '2026-09-07 19:53:15'),
(126, 'adminEmpresaDemo@demoSCT.cl', 4, 6, 22, 2, 41, 10, 38, '2026-09-07 19:53:15', '2026-09-07 19:53:15'),
(128, 'JefaturaEmpresaDemo@demoSCT.cl', 4, 8, 26, 1, 50, 1, 1, '2026-09-04 19:53:15', '2026-09-04 19:53:15'),
(129, 'JefaturaEmpresaDemo@demoSCT.cl', 4, 8, 26, 1, 51, 3, 9, '2026-09-04 19:53:15', '2026-09-04 19:53:15'),
(130, 'JefaturaEmpresaDemo@demoSCT.cl', 4, 8, 26, 1, 52, 4, 14, '2026-09-04 19:53:15', '2026-09-04 19:53:15'),
(131, 'JefaturaEmpresaDemo@demoSCT.cl', 4, 8, 26, 1, 53, 6, 22, '2026-09-04 19:53:15', '2026-09-04 19:53:15'),
(132, 'JefaturaEmpresaDemo@demoSCT.cl', 4, 8, 26, 1, 54, 8, 29, '2026-09-04 19:53:15', '2026-09-04 19:53:15'),
(133, 'JefaturaEmpresaDemo@demoSCT.cl', 4, 8, 26, 1, 55, 9, 33, '2026-09-04 19:53:15', '2026-09-04 19:53:15'),
(134, 'JefaturaEmpresaDemo@demoSCT.cl', 4, 8, 26, 1, 56, 10, 38, '2026-09-04 19:53:15', '2026-09-04 19:53:15'),
(135, 'UsuarioEmpresaDemo@demoSCT.cl', 4, 8, 27, 1, 50, 1, 1, '2026-09-03 19:53:15', '2026-09-03 19:53:15'),
(136, 'UsuarioEmpresaDemo@demoSCT.cl', 4, 8, 27, 1, 51, 3, 9, '2026-09-03 19:53:15', '2026-09-03 19:53:15'),
(137, 'UsuarioEmpresaDemo@demoSCT.cl', 4, 8, 27, 1, 52, 4, 14, '2026-09-03 19:53:15', '2026-09-03 19:53:15'),
(138, 'UsuarioEmpresaDemo@demoSCT.cl', 4, 8, 27, 1, 53, 6, 21, '2026-09-03 19:53:15', '2026-09-03 19:53:15'),
(139, 'UsuarioEmpresaDemo@demoSCT.cl', 4, 8, 27, 1, 54, 8, 30, '2026-09-03 19:53:15', '2026-09-03 19:53:15'),
(140, 'UsuarioEmpresaDemo@demoSCT.cl', 4, 8, 27, 1, 55, 9, 34, '2026-09-03 19:53:15', '2026-09-03 19:53:15'),
(141, 'UsuarioEmpresaDemo@demoSCT.cl', 4, 8, 27, 1, 56, 10, 38, '2026-09-03 19:53:15', '2026-09-03 19:53:15'),
(142, 'UsuarioEmpresaDemo@demoSCT.cl', 4, 8, 27, 2, 50, 1, 1, '2026-09-04 19:53:15', '2026-09-04 19:53:15'),
(143, 'UsuarioEmpresaDemo@demoSCT.cl', 4, 8, 27, 2, 51, 3, 9, '2026-09-04 19:53:15', '2026-09-04 19:53:15'),
(144, 'UsuarioEmpresaDemo@demoSCT.cl', 4, 8, 27, 2, 52, 4, 14, '2026-09-04 19:53:15', '2026-09-04 19:53:15'),
(145, 'UsuarioEmpresaDemo@demoSCT.cl', 4, 8, 27, 2, 53, 6, 22, '2026-09-04 19:53:15', '2026-09-04 19:53:15'),
(146, 'UsuarioEmpresaDemo@demoSCT.cl', 4, 8, 27, 2, 54, 8, 30, '2026-09-04 19:53:15', '2026-09-04 19:53:15'),
(147, 'UsuarioEmpresaDemo@demoSCT.cl', 4, 8, 27, 2, 55, 9, 34, '2026-09-04 19:53:15', '2026-09-04 19:53:15'),
(148, 'UsuarioEmpresaDemo@demoSCT.cl', 4, 8, 27, 2, 56, 10, 38, '2026-09-04 19:53:15', '2026-09-04 19:53:15'),
(149, 'GerenteEmpresaDemo@demoSCT.cl', 4, 10, 30, 1, 64, 1, 1, '2026-09-03 17:20:00', '2026-09-03 17:20:00'),
(150, 'GerenteEmpresaDemo@demoSCT.cl', 4, 10, 30, 1, 65, 4, 14, '2026-09-03 17:20:00', '2026-09-03 17:20:00'),
(151, 'GerenteEmpresaDemo@demoSCT.cl', 4, 10, 30, 1, 66, 5, 18, '2026-09-03 17:20:00', '2026-09-03 17:20:00'),
(152, 'GerenteEmpresaDemo@demoSCT.cl', 4, 10, 30, 1, 67, 9, 33, '2026-09-03 17:20:00', '2026-09-03 17:20:00'),
(153, 'GerenteEmpresaDemo@demoSCT.cl', 4, 10, 30, 1, 68, 10, 37, '2026-09-03 17:20:00', '2026-09-03 17:20:00'),
(156, 'adminEmpresaDemo@demoSCT.cl', 4, 10, 32, 1, 64, 1, 1, '2026-09-06 18:00:00', '2026-09-06 18:00:00'),
(157, 'adminEmpresaDemo@demoSCT.cl', 4, 10, 32, 1, 65, 4, 14, '2026-09-06 18:00:00', '2026-09-06 18:00:00'),
(158, 'adminEmpresaDemo@demoSCT.cl', 4, 10, 32, 1, 66, 5, 18, '2026-09-06 18:00:00', '2026-09-06 18:00:00'),
(159, 'adminEmpresaDemo@demoSCT.cl', 4, 10, 32, 1, 67, 9, 34, '2026-09-06 18:00:00', '2026-09-06 18:00:00'),
(160, 'adminEmpresaDemo@demoSCT.cl', 4, 10, 32, 1, 68, 10, 38, '2026-09-06 18:00:00', '2026-09-06 18:00:00'),
(163, 'UsuarioEmpresaDemo@demoSCT.cl', 4, 10, 33, 1, 64, 1, 1, '2026-09-05 18:30:00', '2026-09-05 18:30:00'),
(164, 'UsuarioEmpresaDemo@demoSCT.cl', 4, 10, 33, 1, 65, 4, 14, '2026-09-05 18:30:00', '2026-09-05 18:30:00'),
(165, 'UsuarioEmpresaDemo@demoSCT.cl', 4, 10, 33, 1, 66, 5, 17, '2026-09-05 18:30:00', '2026-09-05 18:30:00'),
(166, 'UsuarioEmpresaDemo@demoSCT.cl', 4, 10, 33, 1, 67, 9, 34, '2026-09-05 18:30:00', '2026-09-05 18:30:00'),
(167, 'UsuarioEmpresaDemo@demoSCT.cl', 4, 10, 33, 1, 68, 10, 38, '2026-09-05 18:30:00', '2026-09-05 18:30:00'),
(170, 'UsuarioEmpresaDemo@demoSCT.cl', 4, 10, 33, 2, 64, 1, 1, '2026-09-07 18:40:00', '2026-09-07 18:40:00'),
(171, 'UsuarioEmpresaDemo@demoSCT.cl', 4, 10, 33, 2, 65, 4, 14, '2026-09-07 18:40:00', '2026-09-07 18:40:00'),
(172, 'UsuarioEmpresaDemo@demoSCT.cl', 4, 10, 33, 2, 66, 5, 18, '2026-09-07 18:40:00', '2026-09-07 18:40:00'),
(173, 'UsuarioEmpresaDemo@demoSCT.cl', 4, 10, 33, 2, 67, 9, 34, '2026-09-07 18:40:00', '2026-09-07 18:40:00'),
(174, 'UsuarioEmpresaDemo@demoSCT.cl', 4, 10, 33, 2, 68, 10, 38, '2026-09-07 18:40:00', '2026-09-07 18:40:00'),
(177, 'GerenteEmpresaDemo@demoSCT.cl', 4, 12, 35, 1, 78, 1, 1, '2026-09-04 18:10:00', '2026-09-04 18:10:00'),
(178, 'GerenteEmpresaDemo@demoSCT.cl', 4, 12, 35, 1, 79, 2, 5, '2026-09-04 18:10:00', '2026-09-04 18:10:00'),
(179, 'GerenteEmpresaDemo@demoSCT.cl', 4, 12, 35, 1, 80, 5, 18, '2026-09-04 18:10:00', '2026-09-04 18:10:00'),
(180, 'GerenteEmpresaDemo@demoSCT.cl', 4, 12, 35, 1, 81, 7, 25, '2026-09-04 18:10:00', '2026-09-04 18:10:00'),
(181, 'GerenteEmpresaDemo@demoSCT.cl', 4, 12, 35, 1, 82, 9, 33, '2026-09-04 18:10:00', '2026-09-04 18:10:00'),
(182, 'GerenteEmpresaDemo@demoSCT.cl', 4, 12, 35, 1, 83, 11, 41, '2026-09-04 18:10:00', '2026-09-04 18:10:00'),
(184, 'JefaturaEmpresaDemo@demoSCT.cl', 4, 12, 36, 1, 78, 1, 1, '2026-09-06 17:30:00', '2026-09-06 17:30:00'),
(185, 'JefaturaEmpresaDemo@demoSCT.cl', 4, 12, 36, 1, 79, 2, 5, '2026-09-06 17:30:00', '2026-09-06 17:30:00'),
(186, 'JefaturaEmpresaDemo@demoSCT.cl', 4, 12, 36, 1, 80, 5, 18, '2026-09-06 17:30:00', '2026-09-06 17:30:00'),
(187, 'JefaturaEmpresaDemo@demoSCT.cl', 4, 12, 36, 1, 81, 7, 25, '2026-09-06 17:30:00', '2026-09-06 17:30:00'),
(188, 'JefaturaEmpresaDemo@demoSCT.cl', 4, 12, 36, 1, 82, 9, 34, '2026-09-06 17:30:00', '2026-09-06 17:30:00'),
(189, 'JefaturaEmpresaDemo@demoSCT.cl', 4, 12, 36, 1, 83, 11, 42, '2026-09-06 17:30:00', '2026-09-06 17:30:00'),
(191, 'adminEmpresaDemo@demoSCT.cl', 4, 12, 38, 1, 78, 1, 1, '2026-09-05 18:00:00', '2026-09-05 18:00:00'),
(192, 'adminEmpresaDemo@demoSCT.cl', 4, 12, 38, 1, 79, 2, 5, '2026-09-05 18:00:00', '2026-09-05 18:00:00'),
(193, 'adminEmpresaDemo@demoSCT.cl', 4, 12, 38, 1, 80, 5, 18, '2026-09-05 18:00:00', '2026-09-05 18:00:00'),
(194, 'adminEmpresaDemo@demoSCT.cl', 4, 12, 38, 1, 81, 7, 26, '2026-09-05 18:00:00', '2026-09-05 18:00:00'),
(195, 'adminEmpresaDemo@demoSCT.cl', 4, 12, 38, 1, 82, 9, 34, '2026-09-05 18:00:00', '2026-09-05 18:00:00'),
(196, 'adminEmpresaDemo@demoSCT.cl', 4, 12, 38, 1, 83, 11, 42, '2026-09-05 18:00:00', '2026-09-05 18:00:00'),
(198, 'adminEmpresaDemo@demoSCT.cl', 4, 12, 38, 2, 78, 1, 1, '2026-09-07 19:00:00', '2026-09-07 19:00:00'),
(199, 'adminEmpresaDemo@demoSCT.cl', 4, 12, 38, 2, 79, 2, 5, '2026-09-07 19:00:00', '2026-09-07 19:00:00'),
(200, 'adminEmpresaDemo@demoSCT.cl', 4, 12, 38, 2, 80, 5, 18, '2026-09-07 19:00:00', '2026-09-07 19:00:00'),
(201, 'adminEmpresaDemo@demoSCT.cl', 4, 12, 38, 2, 81, 7, 25, '2026-09-07 19:00:00', '2026-09-07 19:00:00'),
(202, 'adminEmpresaDemo@demoSCT.cl', 4, 12, 38, 2, 82, 9, 34, '2026-09-07 19:00:00', '2026-09-07 19:00:00'),
(203, 'adminEmpresaDemo@demoSCT.cl', 4, 12, 38, 2, 83, 11, 42, '2026-09-07 19:00:00', '2026-09-07 19:00:00');

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
(14, 'javiera.reyes@test.engie.cl', 4, 3, '2026-08-20 09:00:00', '2026-09-05 23:59:59', 2, 'seed_test_data', '2026-08-20 09:00:00', '2026-08-20 09:00:00'),
(15, 'UsuarioEmpresaDemo@demoSCT.cl', 5, 4, '2026-09-08 19:53:15', '2026-09-22 19:53:15', 1, 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 19:53:15', '2026-09-08 19:53:15'),
(16, 'adminEmpresaDemo@demoSCT.cl', 5, 4, '2026-09-08 19:53:15', '2026-10-08 19:53:15', 1, 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 19:53:15', '2026-09-08 19:53:15'),
(17, 'JefaturaEmpresaDemo@demoSCT.cl', 5, 4, '2026-08-24 19:53:15', '2026-09-07 19:53:15', 2, 'adminEmpresaDemo@demoSCT.cl', '2026-08-24 19:53:15', '2026-08-29 19:53:15'),
(18, 'GerenteEmpresaDemo@demoSCT.cl', 5, 4, '2026-08-19 19:53:15', '2026-09-03 19:53:15', 3, 'adminEmpresaDemo@demoSCT.cl', '2026-08-19 19:53:15', '2026-09-01 19:53:15'),
(19, 'GerenteEmpresaDemo@demoSCT.cl', 6, 4, '2026-09-08 19:53:15', '2026-09-23 19:53:15', 1, 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 19:53:15', '2026-09-08 19:53:15'),
(20, 'JefaturaEmpresaDemo@demoSCT.cl', 6, 4, '2026-09-03 19:53:15', '2026-09-18 19:53:15', 1, 'adminEmpresaDemo@demoSCT.cl', '2026-09-03 19:53:15', '2026-09-05 19:53:15'),
(21, 'UsuarioEmpresaDemo@demoSCT.cl', 6, 4, '2026-09-01 19:53:15', '2026-09-13 19:53:15', 2, 'adminEmpresaDemo@demoSCT.cl', '2026-09-01 19:53:15', '2026-09-06 19:53:15'),
(22, 'adminEmpresaDemo@demoSCT.cl', 6, 4, '2026-09-01 19:53:15', '2026-09-11 19:53:15', 3, 'adminEmpresaDemo@demoSCT.cl', '2026-09-01 19:53:15', '2026-09-07 19:53:15'),
(23, 'UsuarioEmpresaDemo@demoSCT.cl', 7, 4, '2026-09-08 19:53:15', '2026-09-28 19:53:15', 1, 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 19:53:15', '2026-09-08 19:53:15'),
(24, 'GerenteEmpresaDemo@demoSCT.cl', 8, 4, '2026-09-08 19:53:15', '2026-09-20 19:53:15', 1, 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 19:53:15', '2026-09-08 19:53:15'),
(25, 'adminEmpresaDemo@demoSCT.cl', 8, 4, '2026-09-08 19:53:15', '2026-09-28 19:53:15', 1, 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 19:53:15', '2026-09-08 19:53:15'),
(26, 'JefaturaEmpresaDemo@demoSCT.cl', 8, 4, '2026-08-31 19:53:15', '2026-09-13 19:53:15', 2, 'adminEmpresaDemo@demoSCT.cl', '2026-08-31 19:53:15', '2026-09-04 19:53:15'),
(27, 'UsuarioEmpresaDemo@demoSCT.cl', 8, 4, '2026-08-30 19:53:15', '2026-09-11 19:53:15', 3, 'adminEmpresaDemo@demoSCT.cl', '2026-08-30 19:53:15', '2026-09-04 19:53:15'),
(28, 'UsuarioEmpresaDemo@demoSCT.cl', 9, 4, '2026-09-08 21:00:00', '2026-09-30 23:59:59', 1, 'JefaturaEmpresaDemo@demoSCT.cl', '2026-09-08 23:02:46', '2026-09-08 23:02:46'),
(29, 'adminEmpresaDemo@demoSCT.cl', 9, 4, '2026-09-08 21:05:00', '2026-10-05 23:59:59', 1, 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 23:02:46', '2026-09-08 23:02:46'),
(30, 'GerenteEmpresaDemo@demoSCT.cl', 10, 4, '2026-09-01 09:00:00', '2026-09-25 23:59:59', 2, 'adminEmpresaDemo@demoSCT.cl', '2026-09-01 09:00:00', '2026-09-03 17:20:00'),
(31, 'JefaturaEmpresaDemo@demoSCT.cl', 10, 4, '2026-09-08 09:00:00', '2026-09-30 23:59:59', 1, 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 23:02:46', '2026-09-08 23:02:46'),
(32, 'adminEmpresaDemo@demoSCT.cl', 10, 4, '2026-09-02 09:00:00', '2026-09-28 23:59:59', 1, 'adminEmpresaDemo@demoSCT.cl', '2026-09-02 09:00:00', '2026-09-06 18:00:00'),
(33, 'UsuarioEmpresaDemo@demoSCT.cl', 10, 4, '2026-09-01 10:00:00', '2026-09-26 23:59:59', 3, 'adminEmpresaDemo@demoSCT.cl', '2026-09-01 10:00:00', '2026-09-07 18:40:00'),
(34, 'JefaturaEmpresaDemo@demoSCT.cl', 11, 4, '2026-09-01 09:00:00', '2026-09-05 23:59:59', 1, 'adminEmpresaDemo@demoSCT.cl', '2026-09-01 09:00:00', '2026-09-01 09:00:00'),
(35, 'GerenteEmpresaDemo@demoSCT.cl', 12, 4, '2026-09-02 09:00:00', '2026-09-24 23:59:59', 2, 'adminEmpresaDemo@demoSCT.cl', '2026-09-02 09:00:00', '2026-09-04 18:10:00'),
(36, 'JefaturaEmpresaDemo@demoSCT.cl', 12, 4, '2026-09-03 09:00:00', '2026-09-25 23:59:59', 1, 'adminEmpresaDemo@demoSCT.cl', '2026-09-03 09:00:00', '2026-09-06 17:30:00'),
(37, 'UsuarioEmpresaDemo@demoSCT.cl', 12, 4, '2026-09-08 08:30:00', '2026-09-30 23:59:59', 1, 'JefaturaEmpresaDemo@demoSCT.cl', '2026-09-08 23:02:46', '2026-09-08 23:02:46'),
(38, 'adminEmpresaDemo@demoSCT.cl', 12, 4, '2026-09-01 10:00:00', '2026-09-23 23:59:59', 3, 'adminEmpresaDemo@demoSCT.cl', '2026-09-01 10:00:00', '2026-09-07 19:00:00'),
(39, 'UsuarioEmpresaDemo@demoSCT.cl', 13, 4, '2026-09-01 08:00:00', '2026-09-05 23:59:59', 1, 'JefaturaEmpresaDemo@demoSCT.cl', '2026-09-01 08:00:00', '2026-09-01 08:00:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `user_credentials`
--

CREATE TABLE `user_credentials` (
  `id_users` varchar(50) NOT NULL COMMENT 'FK a users.id_users; una fila existe solo cuando el usuario ya definió contraseña',
  `password_hash` varchar(255) DEFAULT NULL COMMENT 'Hash de contraseña elegida por el usuario; nunca RUT ni texto plano',
  `credential_status` enum('pending_activation','reset_required','active') NOT NULL DEFAULT 'pending_activation',
  `password_changed_at` datetime DEFAULT NULL,
  `legacy_password_invalidated_at` datetime DEFAULT NULL COMMENT 'Momento en que una credencial heredada fue invalidada',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `user_credentials`
--

INSERT INTO `user_credentials` (`id_users`, `password_hash`, `credential_status`, `password_changed_at`, `legacy_password_invalidated_at`, `created_at`, `updated_at`) VALUES
('admin.completo.test@test.helheim.cl', '$2y$10$yt.Z0BlXL3fHquRV2kUSvewr/cdRAno50645zflFYXwkvPmQAcDJm', 'active', '2026-09-08 15:00:11', NULL, '2026-09-08 14:04:46', '2026-09-08 15:00:11'),
('admin.completo.test@test.tecaivot.cl', NULL, 'pending_activation', NULL, NULL, '2026-09-08 14:04:46', '2026-09-08 14:04:46'),
('admin.test@test.engie.cl', NULL, 'reset_required', NULL, '2026-09-08 14:54:29', '2026-09-08 14:04:46', '2026-09-08 14:54:29'),
('admin.test@test.tecaivot.cl', NULL, 'pending_activation', NULL, NULL, '2026-09-08 14:04:46', '2026-09-08 14:04:46'),
('adminEmpresaDemo@demoSCT.cl', '$2y$10$o2m7YLZ9eGhhMUreqVNtg.rS09LHgSDmxniGjXazWaH9eaVvaVKLq', 'active', '2026-09-08 18:43:22', NULL, '2026-09-08 18:43:22', '2026-09-08 18:43:22'),
('barbara.contreras@test.helheim.cl', NULL, 'pending_activation', NULL, NULL, '2026-09-08 14:04:46', '2026-09-08 14:04:46'),
('camila.soto@test.tecaivot.cl', NULL, 'pending_activation', NULL, NULL, '2026-09-08 14:04:46', '2026-09-08 14:04:46'),
('cliente.test@test.helheim.cl', NULL, 'pending_activation', NULL, NULL, '2026-09-08 14:04:46', '2026-09-08 14:04:46'),
('cristobal.fuentes@test.engie.cl', NULL, 'pending_activation', NULL, NULL, '2026-09-08 14:04:46', '2026-09-08 14:04:46'),
('fco.fredes.g@gmail.com', '$2y$10$PKym2C3vDEYouWRGqsfIou5IsvwPkAXoxCT.q9uxf8HlzsHMgrWda', 'active', '2026-09-08 14:43:52', NULL, '2026-09-08 14:04:46', '2026-09-08 14:54:29'),
('francisca.torres@test.engie.cl', NULL, 'pending_activation', NULL, NULL, '2026-09-08 14:04:46', '2026-09-08 14:04:46'),
('Francisco.fredes@engie.com', NULL, 'reset_required', NULL, '2026-09-08 14:54:29', '2026-09-08 14:04:46', '2026-09-08 14:54:29'),
('GerenteEmpresaDemo@demoSCT.cl', '$2y$10$o2m7YLZ9eGhhMUreqVNtg.rS09LHgSDmxniGjXazWaH9eaVvaVKLq', 'active', '2026-09-08 18:43:22', NULL, '2026-09-08 18:43:22', '2026-09-08 18:43:22'),
('javiera.reyes@test.engie.cl', NULL, 'pending_activation', NULL, NULL, '2026-09-08 14:04:46', '2026-09-08 14:04:46'),
('jefatura.test@test.helheim.cl', NULL, 'pending_activation', NULL, NULL, '2026-09-08 14:04:46', '2026-09-08 14:04:46'),
('jefatura.test@test.tecaivot.cl', NULL, 'pending_activation', NULL, NULL, '2026-09-08 14:04:46', '2026-09-08 14:04:46'),
('JefaturaEmpresaDemo@demoSCT.cl', '$2y$10$o2m7YLZ9eGhhMUreqVNtg.rS09LHgSDmxniGjXazWaH9eaVvaVKLq', 'active', '2026-09-08 18:43:22', NULL, '2026-09-08 18:43:22', '2026-09-08 18:43:22'),
('jonathan.vera@engie.com', NULL, 'pending_activation', NULL, NULL, '2026-09-08 14:04:46', '2026-09-08 14:04:46'),
('juanantonioconchaloyola@gmail.com', '$2y$10$7WLZCrmLdzz7bG0T40i1oOS.vMOfbQkL7cNHvmT86ElYT5YRsQNfu', 'active', '2026-09-08 14:37:29', NULL, '2026-09-08 14:04:46', '2026-09-08 14:54:29'),
('Laura.Lira@external.engie.com', NULL, 'pending_activation', NULL, NULL, '2026-09-08 14:04:46', '2026-09-08 14:04:46'),
('malazga99@gmail.com', '$2y$10$V4UiZlS7vFYUjj260pVTJ.oi1kdwEZ5kxI6J4k2p5NGpM7CqExxtu', 'active', '2026-09-08 14:46:14', NULL, '2026-09-08 14:04:46', '2026-09-08 14:54:29'),
('pablotroncoso@gmail.com', '$2y$10$I.paWCUuZ5KkNXo52t4NMuAlyBeuLS18vMd32NHUuLtPf4HGQsdw.', 'active', '2026-09-08 14:48:18', NULL, '2026-09-08 14:04:46', '2026-09-08 14:54:29'),
('patricio.gomez@test.engie.cl', NULL, 'pending_activation', NULL, NULL, '2026-09-08 14:04:46', '2026-09-08 14:04:46'),
('UsuarioEmpresaDemo@demoSCT.cl', '$2y$10$o2m7YLZ9eGhhMUreqVNtg.rS09LHgSDmxniGjXazWaH9eaVvaVKLq', 'active', '2026-09-08 18:43:22', NULL, '2026-09-08 18:43:22', '2026-09-08 18:43:22'),
('wagner.leite@engie.com', NULL, 'pending_activation', NULL, NULL, '2026-09-08 14:04:46', '2026-09-08 14:04:46');

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
(15, 3, '18102053-9', 'Antonia', 'Fernández', 'antonia.fernandez@test.engie.cl', '+56 9 5533 4410', 'Encargada de Bodega', NULL, 1, 'seed_test_data', '2026-09-08 09:00:00', '2026-09-08 09:00:00'),
(16, 4, '11111111-1', 'Gerente', 'DEMO', 'GerenteEmpresaDemo@demoSCT.cl', NULL, 'Gerente de Empresa', NULL, 1, 'seed_demo_sct', '2026-09-08 18:43:22', '2026-09-08 18:43:22'),
(17, 4, '22222222-2', 'Jefatura', 'DEMO', 'JefaturaEmpresaDemo@demoSCT.cl', NULL, 'Jefatura Operacional', NULL, 1, 'seed_demo_sct', '2026-09-08 18:43:22', '2026-09-08 18:43:22'),
(18, 4, '33333333-3', 'Usuario', 'DEMO', 'UsuarioEmpresaDemo@demoSCT.cl', NULL, 'Usuario Operativo', NULL, 1, 'seed_demo_sct', '2026-09-08 18:43:22', '2026-09-08 18:43:22'),
(19, 4, '44444444-4', 'Administrador', 'DEMO', 'adminEmpresaDemo@demoSCT.cl', NULL, 'Administrador de Empresa', NULL, 1, 'seed_demo_sct', '2026-09-08 18:43:22', '2026-09-08 18:43:22'),
(20, 4, '55555555-5', 'Sofía', 'Navarro DEMO', 'sofia.prevencion@demoSCT.cl', '+56 9 5000 1001', 'Prevencionista de Riesgos', NULL, 1, 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 19:53:15', '2026-09-08 19:53:15'),
(21, 4, '66666666-6', 'Diego', 'Muñoz DEMO', 'diego.operador@demoSCT.cl', '+56 9 5000 1002', 'Operador de Maquinaria', NULL, 1, 'JefaturaEmpresaDemo@demoSCT.cl', '2026-09-08 19:53:15', '2026-09-08 19:53:15'),
(22, 4, '77777777-7', 'Valentina', 'Pérez DEMO', 'valentina.tecnica@demoSCT.cl', '+56 9 5000 1003', 'Técnica de Mantención', NULL, 1, 'JefaturaEmpresaDemo@demoSCT.cl', '2026-09-08 19:53:15', '2026-09-08 19:53:15'),
(23, 4, '88888888-8', 'Mauricio', 'Silva DEMO', 'mauricio.contratista@demoSCT.cl', '+56 9 5000 1004', 'Contratista Eléctrico', NULL, 1, 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 19:53:15', '2026-09-08 19:53:15'),
(24, 4, '10101010-4', 'Camila', 'Reyes DEMO', 'camila.brigada@demoSCT.cl', '+56 9 5000 1005', 'Brigadista de Emergencia', NULL, 1, 'GerenteEmpresaDemo@demoSCT.cl', '2026-09-08 19:53:15', '2026-09-08 19:53:15'),
(25, 4, '12121212-9', 'Pedro', 'Rojas DEMO', 'pedro.inactivo@demoSCT.cl', NULL, 'Trabajador Inactivo', NULL, 0, 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 19:53:15', '2026-09-08 19:53:15');

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
(15, 4, '2026-09-08 09:00:00'),
(16, 9, '2026-09-08 19:53:15'),
(17, 7, '2026-09-08 19:53:15'),
(17, 8, '2026-09-08 19:53:15'),
(18, 7, '2026-09-08 19:53:15'),
(18, 8, '2026-09-08 23:02:46'),
(18, 9, '2026-09-08 23:02:46'),
(19, 7, '2026-09-08 19:53:15'),
(20, 7, '2026-09-08 23:02:46'),
(20, 9, '2026-09-08 19:53:15'),
(21, 8, '2026-09-08 19:53:15'),
(21, 9, '2026-09-08 23:02:46'),
(22, 8, '2026-09-08 19:53:15'),
(22, 9, '2026-09-08 23:02:46'),
(23, 7, '2026-09-08 19:53:15'),
(23, 8, '2026-09-08 23:02:46'),
(24, 7, '2026-09-08 23:02:46'),
(24, 9, '2026-09-08 19:53:15');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `audits`
--
ALTER TABLE `audits`
  ADD PRIMARY KEY (`id_audits`),
  ADD UNIQUE KEY `uq_audits_assignment` (`id_user_test_assigned`),
  ADD KEY `idx_audits_company` (`id_company`),
  ADD KEY `idx_audits_test` (`id_test`),
  ADD KEY `idx_audits_auditor` (`id_users_auditor`),
  ADD KEY `idx_dash_audits_company_date_status` (`id_company`,`date_create`,`status`);

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
  ADD KEY `idx_changehistory_record` (`table_name`,`record_id`),
  ADD KEY `idx_changehistory_company_date` (`id_company`,`changed_at`),
  ADD KEY `idx_changehistory_module_date` (`module`,`changed_at`),
  ADD KEY `idx_changehistory_actor_date` (`changed_by`,`changed_at`),
  ADD KEY `idx_changehistory_action_date` (`action`,`changed_at`),
  ADD KEY `idx_changehistory_request` (`request_id`);

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
  ADD KEY `fk_dynamicforms_company` (`id_company`),
  ADD KEY `idx_dash_forms_company_state` (`id_company`,`state`);

--
-- Indices de la tabla `dynamic_form_answers`
--
ALTER TABLE `dynamic_form_answers`
  ADD PRIMARY KEY (`id_answer`),
  ADD UNIQUE KEY `uq_dynamic_answer_submission_field` (`id_submission`,`id_field`),
  ADD KEY `idx_dynamic_answer_field` (`id_field`);

--
-- Indices de la tabla `dynamic_form_fields`
--
ALTER TABLE `dynamic_form_fields`
  ADD PRIMARY KEY (`id_field`),
  ADD KEY `idx_formfields_form` (`id_form`);

--
-- Indices de la tabla `dynamic_form_submissions`
--
ALTER TABLE `dynamic_form_submissions`
  ADD PRIMARY KEY (`id_submission`),
  ADD KEY `idx_dynamic_submission_form` (`id_form`),
  ADD KEY `idx_dynamic_submission_company` (`id_company`),
  ADD KEY `idx_dynamic_submission_user` (`id_users`),
  ADD KEY `idx_dynamic_submission_submitted` (`submitted_at`),
  ADD KEY `idx_dash_submissions_company_status_date` (`id_company`,`status`,`submitted_at`);

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
  ADD UNIQUE KEY `uq_password_resets_token_hash` (`token_hash`),
  ADD KEY `idx_password_resets_user` (`id_users`),
  ADD KEY `idx_password_resets_ip_created` (`ip_address`,`created_at`);

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
  ADD KEY `fk_programs_project` (`id_project`),
  ADD KEY `idx_programs_company_status` (`id_company`,`state`,`status`),
  ADD KEY `idx_programs_company_project_status` (`id_company`,`id_project`,`state`,`status`),
  ADD KEY `idx_programs_responsible` (`responsible_user`,`state`,`status`);

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
-- Indices de la tabla `protocol_assignments`
--
ALTER TABLE `protocol_assignments`
  ADD PRIMARY KEY (`id_protocol_assignment`),
  ADD KEY `idx_protocol_assign_protocol` (`id_protocol`),
  ADD KEY `idx_protocol_assign_company` (`id_company`),
  ADD KEY `idx_protocol_assign_center` (`id_company_center`),
  ADD KEY `idx_protocol_assign_project` (`id_project`),
  ADD KEY `idx_protocol_assign_worker` (`id_worker`),
  ADD KEY `idx_protocol_assign_responsible` (`responsible_user`),
  ADD KEY `idx_protocol_assign_due` (`state`,`next_due_at`),
  ADD KEY `idx_dash_protocol_assign_company_due` (`id_company`,`state`,`next_due_at`),
  ADD KEY `idx_dash_protocol_assign_company_scope` (`id_company`,`id_project`,`id_company_center`,`state`);

--
-- Indices de la tabla `protocol_executions`
--
ALTER TABLE `protocol_executions`
  ADD PRIMARY KEY (`id_protocol_execution`),
  ADD UNIQUE KEY `uq_protocol_execution_cycle` (`id_protocol_assignment`,`cycle_number`),
  ADD KEY `idx_protocol_execution_result` (`result`,`submitted_at`),
  ADD KEY `idx_protocol_execution_reviewer` (`reviewed_by`),
  ADD KEY `idx_dash_protocol_exec_assignment_date_result` (`id_protocol_assignment`,`submitted_at`,`result`);

--
-- Indices de la tabla `protocol_execution_submissions`
--
ALTER TABLE `protocol_execution_submissions`
  ADD PRIMARY KEY (`id_protocol_execution_submission`),
  ADD UNIQUE KEY `uq_protocol_exec_form` (`id_protocol_execution`,`id_protocol_form`),
  ADD UNIQUE KEY `uq_protocol_exec_submission` (`id_submission`),
  ADD KEY `idx_protocol_execsub_protocol_form` (`id_protocol_form`);

--
-- Indices de la tabla `protocol_forms`
--
ALTER TABLE `protocol_forms`
  ADD PRIMARY KEY (`id_protocol_form`),
  ADD UNIQUE KEY `uq_protocol_form_scope` (`id_protocol`,`id_company`,`id_form`),
  ADD KEY `idx_protocol_forms_protocol` (`id_protocol`),
  ADD KEY `idx_protocol_forms_company` (`id_company`),
  ADD KEY `idx_protocol_forms_form` (`id_form`);

--
-- Indices de la tabla `protocol_tracking`
--
ALTER TABLE `protocol_tracking`
  ADD PRIMARY KEY (`id_protocol_tracking`),
  ADD KEY `idx_protocol_tracking_assignment` (`id_protocol_assignment`),
  ADD KEY `idx_protocol_tracking_execution` (`id_protocol_execution`),
  ADD KEY `idx_protocol_tracking_responsible` (`responsible_user`),
  ADD KEY `idx_protocol_tracking_status` (`status`,`deadline`),
  ADD KEY `idx_dash_protocol_tracking_assignment_status_due` (`id_protocol_assignment`,`status`,`deadline`);

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
  ADD KEY `fk_events_type` (`id_event`),
  ADD KEY `idx_dash_events_company_date` (`id_company`,`module`,`event_date`),
  ADD KEY `idx_dash_events_company_project_date` (`id_company`,`module`,`id_project`,`event_date`),
  ADD KEY `idx_dash_events_company_center_date` (`id_company`,`module`,`id_company_center`,`event_date`);

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
  ADD UNIQUE KEY `uq_testanswers_assignment_try_rel` (`id_user_test_assigned`,`id_test_try`,`id_rel`),
  ADD KEY `fk_testanswers_user` (`id_users`),
  ADD KEY `fk_testanswers_company` (`id_company`),
  ADD KEY `fk_testanswers_test` (`id_test`),
  ADD KEY `fk_testanswers_rel` (`id_rel`),
  ADD KEY `fk_testanswers_question` (`id_question`),
  ADD KEY `fk_testanswers_option` (`id_questions_options`),
  ADD KEY `idx_testanswers_assignment` (`id_user_test_assigned`,`id_test_try`);

--
-- Indices de la tabla `users_test_assigned`
--
ALTER TABLE `users_test_assigned`
  ADD PRIMARY KEY (`id_user_test_assigned`),
  ADD KEY `fk_testassigned_user` (`id_users`),
  ADD KEY `fk_testassigned_test` (`id_test`),
  ADD KEY `fk_testassigned_company` (`id_company`),
  ADD KEY `idx_dash_testassigned_company_state_date` (`id_company`,`state`,`assignamente_date`,`id_test`),
  ADD KEY `idx_dash_testassigned_company_due` (`id_company`,`state`,`deadline`,`id_test`);

--
-- Indices de la tabla `user_credentials`
--
ALTER TABLE `user_credentials`
  ADD PRIMARY KEY (`id_users`),
  ADD KEY `idx_user_credentials_status` (`credential_status`);

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
  MODIFY `id_audits` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `certificates`
--
ALTER TABLE `certificates`
  MODIFY `id_certificate` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `change_history`
--
ALTER TABLE `change_history`
  MODIFY `id_change` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `company`
--
ALTER TABLE `company`
  MODIFY `id_company` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `company_center`
--
ALTER TABLE `company_center`
  MODIFY `id_company_center` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `company_test`
--
ALTER TABLE `company_test`
  MODIFY `id_test` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de la tabla `company_test_rel_questions`
--
ALTER TABLE `company_test_rel_questions`
  MODIFY `id_rel` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=96;

--
-- AUTO_INCREMENT de la tabla `dynamic_forms`
--
ALTER TABLE `dynamic_forms`
  MODIFY `id_form` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `dynamic_form_answers`
--
ALTER TABLE `dynamic_form_answers`
  MODIFY `id_answer` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=97;

--
-- AUTO_INCREMENT de la tabla `dynamic_form_fields`
--
ALTER TABLE `dynamic_form_fields`
  MODIFY `id_field` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT de la tabla `dynamic_form_submissions`
--
ALTER TABLE `dynamic_form_submissions`
  MODIFY `id_submission` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=113;

--
-- AUTO_INCREMENT de la tabla `login_codes`
--
ALTER TABLE `login_codes`
  MODIFY `id_login_code` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=103;

--
-- AUTO_INCREMENT de la tabla `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id_reset` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id_permission` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=97;

--
-- AUTO_INCREMENT de la tabla `programs`
--
ALTER TABLE `programs`
  MODIFY `id_program` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `program_monthly_tracking`
--
ALTER TABLE `program_monthly_tracking`
  MODIFY `id_tracking` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `projects`
--
ALTER TABLE `projects`
  MODIFY `id_project` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `protocols`
--
ALTER TABLE `protocols`
  MODIFY `id_protocol` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `protocol_assignments`
--
ALTER TABLE `protocol_assignments`
  MODIFY `id_protocol_assignment` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `protocol_executions`
--
ALTER TABLE `protocol_executions`
  MODIFY `id_protocol_execution` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `protocol_execution_submissions`
--
ALTER TABLE `protocol_execution_submissions`
  MODIFY `id_protocol_execution_submission` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `protocol_forms`
--
ALTER TABLE `protocol_forms`
  MODIFY `id_protocol_form` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `protocol_tracking`
--
ALTER TABLE `protocol_tracking`
  MODIFY `id_protocol_tracking` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

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
  MODIFY `id_security_events` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT de la tabla `security_event_evidence`
--
ALTER TABLE `security_event_evidence`
  MODIFY `id_evidence` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `security_event_tracking`
--
ALTER TABLE `security_event_tracking`
  MODIFY `id_security_event_tracking` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT de la tabla `test_materials`
--
ALTER TABLE `test_materials`
  MODIFY `id_material` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `users_role`
--
ALTER TABLE `users_role`
  MODIFY `id_users_role` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT de la tabla `users_role_group`
--
ALTER TABLE `users_role_group`
  MODIFY `id_role_group` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT de la tabla `users_test_answers`
--
ALTER TABLE `users_test_answers`
  MODIFY `id_users_test_answers` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=204;

--
-- AUTO_INCREMENT de la tabla `users_test_assigned`
--
ALTER TABLE `users_test_assigned`
  MODIFY `id_user_test_assigned` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT de la tabla `workers`
--
ALTER TABLE `workers`
  MODIFY `id_worker` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `audits`
--
ALTER TABLE `audits`
  ADD CONSTRAINT `fk_audits_assignment` FOREIGN KEY (`id_user_test_assigned`) REFERENCES `users_test_assigned` (`id_user_test_assigned`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_audits_auditor` FOREIGN KEY (`id_users_auditor`) REFERENCES `users` (`id_users`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_audits_company` FOREIGN KEY (`id_company`) REFERENCES `company` (`id_company`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_audits_test` FOREIGN KEY (`id_test`) REFERENCES `company_test` (`id_test`) ON DELETE NO ACTION ON UPDATE CASCADE;

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
-- Filtros para la tabla `dynamic_form_answers`
--
ALTER TABLE `dynamic_form_answers`
  ADD CONSTRAINT `fk_dynamic_answer_field` FOREIGN KEY (`id_field`) REFERENCES `dynamic_form_fields` (`id_field`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_dynamic_answer_submission` FOREIGN KEY (`id_submission`) REFERENCES `dynamic_form_submissions` (`id_submission`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `dynamic_form_fields`
--
ALTER TABLE `dynamic_form_fields`
  ADD CONSTRAINT `fk_formfields_form` FOREIGN KEY (`id_form`) REFERENCES `dynamic_forms` (`id_form`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `dynamic_form_submissions`
--
ALTER TABLE `dynamic_form_submissions`
  ADD CONSTRAINT `fk_dynamic_submission_company` FOREIGN KEY (`id_company`) REFERENCES `company` (`id_company`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_dynamic_submission_form` FOREIGN KEY (`id_form`) REFERENCES `dynamic_forms` (`id_form`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_dynamic_submission_user` FOREIGN KEY (`id_users`) REFERENCES `users` (`id_users`) ON DELETE NO ACTION ON UPDATE CASCADE;

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
-- Filtros para la tabla `protocol_assignments`
--
ALTER TABLE `protocol_assignments`
  ADD CONSTRAINT `fk_protocol_assign_center` FOREIGN KEY (`id_company_center`) REFERENCES `company_center` (`id_company_center`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_protocol_assign_company` FOREIGN KEY (`id_company`) REFERENCES `company` (`id_company`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_protocol_assign_project` FOREIGN KEY (`id_project`) REFERENCES `projects` (`id_project`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_protocol_assign_protocol` FOREIGN KEY (`id_protocol`) REFERENCES `protocols` (`id_protocol`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_protocol_assign_responsible` FOREIGN KEY (`responsible_user`) REFERENCES `users` (`id_users`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_protocol_assign_worker` FOREIGN KEY (`id_worker`) REFERENCES `workers` (`id_worker`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Filtros para la tabla `protocol_executions`
--
ALTER TABLE `protocol_executions`
  ADD CONSTRAINT `fk_protocol_execution_assignment` FOREIGN KEY (`id_protocol_assignment`) REFERENCES `protocol_assignments` (`id_protocol_assignment`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_protocol_execution_reviewer` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id_users`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Filtros para la tabla `protocol_execution_submissions`
--
ALTER TABLE `protocol_execution_submissions`
  ADD CONSTRAINT `fk_protocol_execsub_execution` FOREIGN KEY (`id_protocol_execution`) REFERENCES `protocol_executions` (`id_protocol_execution`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_protocol_execsub_protocol_form` FOREIGN KEY (`id_protocol_form`) REFERENCES `protocol_forms` (`id_protocol_form`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_protocol_execsub_submission` FOREIGN KEY (`id_submission`) REFERENCES `dynamic_form_submissions` (`id_submission`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `protocol_forms`
--
ALTER TABLE `protocol_forms`
  ADD CONSTRAINT `fk_protocol_forms_company` FOREIGN KEY (`id_company`) REFERENCES `company` (`id_company`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_protocol_forms_form` FOREIGN KEY (`id_form`) REFERENCES `dynamic_forms` (`id_form`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_protocol_forms_protocol` FOREIGN KEY (`id_protocol`) REFERENCES `protocols` (`id_protocol`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `protocol_tracking`
--
ALTER TABLE `protocol_tracking`
  ADD CONSTRAINT `fk_protocol_tracking_assignment` FOREIGN KEY (`id_protocol_assignment`) REFERENCES `protocol_assignments` (`id_protocol_assignment`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_protocol_tracking_execution` FOREIGN KEY (`id_protocol_execution`) REFERENCES `protocol_executions` (`id_protocol_execution`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_protocol_tracking_responsible` FOREIGN KEY (`responsible_user`) REFERENCES `users` (`id_users`) ON DELETE NO ACTION ON UPDATE CASCADE;

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
  ADD CONSTRAINT `fk_testanswers_assignment` FOREIGN KEY (`id_user_test_assigned`) REFERENCES `users_test_assigned` (`id_user_test_assigned`) ON DELETE NO ACTION ON UPDATE CASCADE,
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
-- Filtros para la tabla `user_credentials`
--
ALTER TABLE `user_credentials`
  ADD CONSTRAINT `fk_usercredentials_user` FOREIGN KEY (`id_users`) REFERENCES `users` (`id_users`) ON DELETE CASCADE ON UPDATE CASCADE;

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
