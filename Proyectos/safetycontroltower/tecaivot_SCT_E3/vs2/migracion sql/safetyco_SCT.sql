-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost
-- Tiempo de generación: 09-10-2026 a las 05:37:06
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
(5, 4, 'usuarios', 'update', 'users', 'GerenteEmpresaDemo@demoSCT.cl', 'Gerente DEMO', 'language', 'pt', 'es', 'GerenteEmpresaDemo@demoSCT.cl', '2026-09-10 00:52:32', 'd19e11bd2b2c1dcf13c802d218bc90e3'),
(6, 4, 'usuarios', 'state_change', 'users', 'jefaturaempresademo@demosct.cl', 'Jefatura DEMO', 'state', '0', '1', 'SuperUsuarioDemo@demoSCT.cl', '2026-10-07 19:22:34', '1bfe50f19c01056ff2e586237792ca12'),
(7, 4, 'usuarios', 'update', 'users', 'UsuarioNuevoEmpresaDemo@demoSCT.cl', 'UsuarioNuevoEmpresaDemo@demoSCT.cl', 'mutual_code', 'achs', 'isl', 'UsuarioNuevoEmpresaDemo@demoSCT.cl', '2026-10-08 17:24:57', 'd05ca4f16cea296ac6074794e6d2d263'),
(8, 4, 'usuarios', 'update', 'users', 'UsuarioNuevoEmpresaDemo@demoSCT.cl', 'UsuarioNuevoEmpresaDemo@demoSCT.cl', 'mutual_code', 'isl', 'achs', 'UsuarioNuevoEmpresaDemo@demoSCT.cl', '2026-10-08 17:25:01', '9c0b0a692ffd744a9575564d67bc6cd9');

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
  `module_code` varchar(32) NOT NULL DEFAULT 'health_safety',
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

INSERT INTO `company_test` (`id_test`, `name`, `type`, `module_code`, `description`, `version`, `state`, `attempts_allowed`, `approval_percentage`, `effective_date_from`, `effective_date_until`, `created_by`, `date_created`, `last_update`, `id_company`) VALUES
(1, 'Inducción General Helheim', 'induccion', 'health_safety', 'Curso de inducción general en seguridad para el personal de Helheim en la plataforma SCT.', 1, 1, 2, 70, '2026-08-01 00:00:00', '2026-12-31 23:59:59', 'seed_test_data', '2026-08-01 10:00:00', '2026-08-01 10:00:00', 1),
(2, 'Inducción General Tecaivot', 'induccion', 'health_safety', 'Curso de inducción general en seguridad para el personal de Tecaivot.', 1, 1, 2, 70, '2026-08-01 00:00:00', '2026-12-31 23:59:59', 'seed_test_data', '2026-08-01 10:30:00', '2026-08-01 10:30:00', 2),
(3, 'Inducción de Seguridad - Contratistas ENGIE', 'induccion', 'health_safety', 'Inducción obligatoria de seguridad y salud ocupacional para contratistas que ingresan a instalaciones de ENGIE.', 1, 1, 2, 80, '2026-08-01 00:00:00', '2026-12-31 23:59:59', 'seed_test_data', '2026-09-08 09:15:00', '2026-09-08 09:15:00', 3),
(4, 'Inducción Trabajo en Altura', 'induccion', 'health_safety', 'Curso específico sobre procedimientos seguros para trabajo en altura en instalaciones de ENGIE.', 1, 1, 2, 75, '2026-08-01 00:00:00', '2026-12-31 23:59:59', 'seed_test_data', '2026-09-08 09:15:00', '2026-09-08 09:15:00', 3),
(5, 'DEMO QA - Inducción General', 'induccion', 'health_safety', 'Inducción de seguridad de la Empresa DEMO para probar asignaciones, intentos y resultados.', 1, 1, 2, 70, '2026-08-09 19:53:15', '2027-03-07 19:53:15', 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 19:53:15', '2026-09-08 19:53:15', 4),
(6, 'DEMO QA - Auditoría Seguridad Operacional', 'auditoria', 'health_safety', 'Auditoría DEMO para validar flujo pendiente, en curso, aprobada y reprobada.', 1, 1, 2, 80, '2026-08-09 19:53:15', '2027-03-07 19:53:15', 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 19:53:15', '2026-09-08 19:53:15', 4),
(7, 'DEMO QA - Auditoría Trabajo en Altura', 'auditoria', 'health_safety', 'Auditoría específica para validar trabajo en altura y uso de EPP.', 1, 1, 1, 80, '2026-08-09 19:53:15', '2027-03-07 19:53:15', 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 19:53:15', '2026-09-08 19:53:15', 4),
(8, 'DEMO QA - Autoevaluación Cultura Preventiva', 'autoevaluacion', 'health_safety', 'Autoevaluación DEMO de cultura preventiva y uso de la plataforma.', 1, 1, 2, 70, '2026-08-09 19:53:15', '2027-03-07 19:53:15', 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 19:53:15', '2026-09-08 19:53:15', 4),
(9, 'DEMO EXT - Inducción Contratistas', 'induccion', 'health_safety', 'Inducción adicional para probar ejecución real, intentos y generación efectiva de certificado desde la aplicación.', 1, 1, 2, 80, '2026-09-01 00:00:00', '2026-12-31 23:59:59', 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 23:02:46', '2026-09-08 23:02:46', 4),
(10, 'DEMO EXT - Auditoría Orden y Aseo', 'auditoria', 'health_safety', 'Auditoría adicional para validar estados pendiente, en curso, cumple y no cumple utilizando los perfiles DEMO.', 1, 1, 2, 80, '2026-09-01 00:00:00', '2026-12-31 23:59:59', 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 23:02:46', '2026-09-08 23:02:46', 4),
(11, 'DEMO EXT - Auditoría Plazo Vencido', 'auditoria', 'health_safety', 'Auditoría vigente con una asignación cuyo plazo ya venció; debe quedar visible pero no ejecutable.', 1, 1, 1, 80, '2026-09-01 00:00:00', '2026-12-31 23:59:59', 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 23:02:46', '2026-09-08 23:02:46', 4),
(12, 'DEMO EXT - Autoevaluación Riesgos Críticos', 'autoevaluacion', 'health_safety', 'Autoevaluación adicional para probar pendiente, en curso, aprobación y reprobación con los cuatro perfiles DEMO.', 1, 1, 2, 80, '2026-09-01 00:00:00', '2026-12-31 23:59:59', 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 23:02:46', '2026-09-08 23:02:46', 4),
(13, 'DEMO EXT - Autoevaluación Plazo Vencido', 'autoevaluacion', 'health_safety', 'Evaluación vigente cuya asignación al Usuario DEMO ya venció; sirve para validar el bloqueo por deadline.', 1, 1, 1, 75, '2026-09-01 00:00:00', '2026-12-31 23:59:59', 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 23:02:46', '2026-09-08 23:02:46', 4),
(14, 'DEMO EXT - Autoevaluación Inactiva', 'autoevaluacion', 'health_safety', 'Plantilla inactiva destinada a validar filtros, activación y edición antes de la primera asignación.', 1, 0, 1, 70, '2026-09-01 00:00:00', '2026-12-31 23:59:59', 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 23:02:46', '2026-09-08 23:02:46', 4);

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
-- Estructura de tabla para la tabla `contractor_companies`
--

CREATE TABLE `contractor_companies` (
  `id_contractor_company` int(11) NOT NULL,
  `id_company` int(11) NOT NULL,
  `rut` varchar(20) NOT NULL,
  `business_name` varchar(150) NOT NULL,
  `trade_name` varchar(150) DEFAULT NULL,
  `state` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` varchar(50) NOT NULL,
  `date_create` datetime NOT NULL,
  `last_update` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `contractor_companies`
--

INSERT INTO `contractor_companies` (`id_contractor_company`, `id_company`, `rut`, `business_name`, `trade_name`, `state`, `created_by`, `date_create`, `last_update`) VALUES
(1, 4, '76.123.456-7', 'Servicios Industriales Andes SpA', 'Andes Industrial', 1, 'migration_e3_vs1_contractors', '2026-10-08 16:21:03', '2026-10-08 16:21:03'),
(2, 4, '77.234.567-8', 'Mantenciones del Pacífico Ltda.', 'MTP Pacífico', 1, 'migration_e3_vs1_contractors', '2026-10-08 16:21:03', '2026-10-08 16:21:03'),
(3, 4, '78.345.678-9', 'Ingeniería y Montajes Norte SpA', 'IM Norte', 1, 'migration_e3_vs1_contractors', '2026-10-08 16:21:03', '2026-10-08 16:21:03');

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
(87, 'UsuarioEmpresaDemo@demoSCT.cl', '127.0.0.1', 1, '2026-10-07 18:09:03'),
(88, 'UsuarioEmpresaDemo@demoSCT.cl', '127.0.0.1', 1, '2026-10-07 18:37:04'),
(89, 'UsuarioEmpresaDemo@demoSCT.cl', '127.0.0.1', 1, '2026-10-07 18:37:21'),
(90, 'UsuarioEmpresaDemo@demoSCT.cl', '127.0.0.1', 1, '2026-10-07 18:40:20'),
(91, 'gerenteempresademo@demosct.cl', '127.0.0.1', 1, '2026-10-07 18:40:38'),
(92, 'UsuarioEmpresaDemo@demoSCT.cl', '127.0.0.1', 1, '2026-10-07 18:53:09'),
(93, 'UsuarioEmpresaDemo@demoSCT.cl', '127.0.0.1', 1, '2026-10-07 18:55:21'),
(94, 'adminEmpresaDemo@demoSCT.cl', '127.0.0.1', 1, '2026-10-07 18:58:56'),
(95, 'ContratistaEmpresaDemo@demoSCT.cl', '127.0.0.1', 1, '2026-10-07 18:59:51'),
(96, 'jefaturaempresademo@demosct.cl', '127.0.0.1', 0, '2026-10-07 19:00:42'),
(97, 'jefaturaempresademo@demosct.cl', '127.0.0.1', 0, '2026-10-07 19:00:51'),
(98, 'ParamedicoEmpresaDemo@demoSCT.cl', '127.0.0.1', 1, '2026-10-07 19:01:56'),
(99, 'SuperUsuarioDemo@demoSCT.cl', '127.0.0.1', 1, '2026-10-07 19:02:22'),
(100, 'TrabajadorEmpresaDemo@demoSCT.cl', '127.0.0.1', 1, '2026-10-07 19:02:46'),
(101, 'TrabajadorEmpresaDemo@demoSCT.cl', '127.0.0.1', 1, '2026-10-07 19:14:49'),
(102, 'UsuarioEmpresaDemo@demoSCT.cl', '127.0.0.1', 1, '2026-10-07 19:15:26'),
(103, 'TrabajadorEmpresaDemo@demoSCT.cl', '127.0.0.1', 1, '2026-10-07 19:18:31'),
(104, 'UsuarioEmpresaDemo@demoSCT.cl', '127.0.0.1', 1, '2026-10-07 19:20:30'),
(105, 'SuperUsuarioDemo@demoSCT.cl', '127.0.0.1', 1, '2026-10-07 19:22:14'),
(106, 'JefaturaEmpresaDemo@demoSCT.cl', '127.0.0.1', 1, '2026-10-07 19:26:02'),
(107, 'JefaturaEmpresaDemo@demoSCT.cl', '127.0.0.1', 1, '2026-10-07 19:49:03'),
(108, 'UsuarioEmpresaDemo@demoSCT.cl', '127.0.0.1', 1, '2026-10-07 19:49:20'),
(109, 'trabajadorempresademo@demosct.cl', '127.0.0.1', 0, '2026-10-07 19:49:29'),
(110, 'UsuarioEmpresaDemo@demoSCT.cl', '127.0.0.1', 1, '2026-10-07 20:52:48'),
(137, 'UsuarioEmpresaDemo@demoSCT.cl', '127.0.0.1', 1, '2026-10-08 19:42:59'),
(138, 'adminEmpresaDemo@demoSCT.cl', '127.0.0.1', 1, '2026-10-08 19:45:00'),
(139, 'adminEmpresaDemo@demoSCT.cl', '127.0.0.1', 1, '2026-10-08 19:53:18'),
(140, 'UsuarioNuevoEmpresaDemo@demoSCT.cl', '127.0.0.1', 1, '2026-10-08 19:54:37'),
(141, 'UsuarioEmpresaDemo@demoSCT.cl', '127.0.0.1', 1, '2026-10-08 19:54:54'),
(142, 'JefaturaEmpresaDemo@demoSCT.cl', '127.0.0.1', 1, '2026-10-08 19:56:08'),
(143, 'adminEmpresaDemo@demoSCT.cl', '127.0.0.1', 1, '2026-10-08 19:57:33'),
(144, 'UsuarioNuevoEmpresaDemo@demoSCT.cl', '127.0.0.1', 1, '2026-10-08 20:20:05'),
(145, 'adminEmpresaDemo@demoSCT.cl', '127.0.0.1', 1, '2026-10-08 20:29:38'),
(146, 'UsuarioEmpresaDemo@demoSCT.cl', '127.0.0.1', 1, '2026-10-08 20:30:02'),
(147, 'UsuarioNuevoEmpresaDemo@demoSCT.cl', '127.0.0.1', 1, '2026-10-08 20:47:06'),
(148, 'UsuarioEmpresaDemo@demoSCT.cl', '127.0.0.1', 1, '2026-10-08 20:50:45'),
(149, 'UsuarioEmpresaDemo@demoSCT.cl', '127.0.0.1', 1, '2026-10-08 23:03:18'),
(150, 'UsuarioNuevoEmpresaDemo@demoSCT.cl', '127.0.0.1', 1, '2026-10-08 23:03:45'),
(151, 'GerenteEmpresaDemo@demoSCT.cl', '127.0.0.1', 1, '2026-10-08 23:05:38'),
(152, 'UsuarioEmpresaDemo@demoSCT.cl', '127.0.0.1', 1, '2026-10-08 23:17:03'),
(153, 'UsuarioNuevoEmpresaDemo@demoSCT.cl', '127.0.0.1', 1, '2026-10-08 23:20:17'),
(154, 'adminEmpresaDemo@demoSCT.cl', '127.0.0.1', 1, '2026-10-08 23:25:24'),
(155, 'JefaturaEmpresaDemo@demoSCT.cl', '127.0.0.1', 1, '2026-10-08 23:25:54'),
(156, 'GerenteEmpresaDemo@demoSCT.cl', '127.0.0.1', 1, '2026-10-08 23:26:29'),
(157, 'adminEmpresaDemo@demoSCT.cl', '127.0.0.1', 1, '2026-10-08 23:59:10'),
(158, 'JefaturaEmpresaDemo@demoSCT.cl', '127.0.0.1', 1, '2026-10-08 23:59:57'),
(159, 'adminEmpresaDemo@demoSCT.cl', '127.0.0.1', 1, '2026-10-09 00:06:14'),
(160, 'GerenteEmpresaDemo@demoSCT.cl', '127.0.0.1', 1, '2026-10-09 00:07:00'),
(161, 'adminEmpresaDemo@demoSCT.cl', '127.0.0.1', 1, '2026-10-09 00:09:28'),
(162, 'JefaturaEmpresaDemo@demoSCT.cl', '127.0.0.1', 1, '2026-10-09 00:10:41'),
(163, 'adminEmpresaDemo@demoSCT.cl', '127.0.0.1', 1, '2026-10-09 00:13:07'),
(164, 'JefaturaEmpresaDemo@demoSCT.cl', '127.0.0.1', 1, '2026-10-09 00:15:36'),
(165, 'adminEmpresaDemo@demoSCT.cl', '127.0.0.1', 1, '2026-10-09 00:16:05'),
(166, 'UsuarioEmpresaDemo@demoSCT.cl', '127.0.0.1', 1, '2026-10-09 00:33:26');

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
(76, 'UsuarioEmpresaDemo@demoSCT.cl', '$2y$10$nmgfOcML9oaSJ7ki/W1iwOBE1q0wQVSQe48Ud0ilIuydJyxLkGcH2', '2026-10-07 18:18:59', '2026-10-07 18:09:03', 0, '127.0.0.1', '2026-10-07 18:08:59'),
(77, 'UsuarioEmpresaDemo@demoSCT.cl', '$2y$10$vWkwzgjOreewpp0OwJm4c.dzYgQzg4bqP68luyF9uKT9o8rmHbD8C', '2026-10-07 18:47:01', '2026-10-07 18:37:04', 0, '127.0.0.1', '2026-10-07 18:37:01'),
(78, 'UsuarioEmpresaDemo@demoSCT.cl', '$2y$10$SXkWaULvpq1QSj5vE6r9A.uh22nJhoJdkFxFPjvXFQOobrZ3AKlAW', '2026-10-07 18:47:16', '2026-10-07 18:37:20', 0, '127.0.0.1', '2026-10-07 18:37:16'),
(79, 'UsuarioEmpresaDemo@demoSCT.cl', '$2y$10$3.aS3nCDLNeUARoGjbSEx.8COjpHX5tViRZyjHJw7rpHpKz6HAXMa', '2026-10-07 18:50:15', '2026-10-07 18:40:20', 0, '127.0.0.1', '2026-10-07 18:40:15'),
(80, 'GerenteEmpresaDemo@demoSCT.cl', '$2y$10$gCR9fiYnq2up/7bfqwA0IuWoSflIjvoLgRzpBQ0Ku8rclWwR7wHIa', '2026-10-07 23:50:35', '2026-10-07 18:40:38', 0, '127.0.0.1', '2026-10-07 18:40:35'),
(81, 'UsuarioEmpresaDemo@demoSCT.cl', '$2y$10$SWQBw28LOh3GXi0IS9CyE.hW5/yUzEeGsUh9R8XiDbM0M08.FYK/W', '2026-10-07 19:03:03', '2026-10-07 18:53:09', 0, '127.0.0.1', '2026-10-07 18:53:03'),
(82, 'UsuarioEmpresaDemo@demoSCT.cl', '$2y$10$aT1gRX9t8bk5K5yb9NNab.N7abHYo2PlXjcdtUtqHD7KZbMWWd9Ry', '2026-10-07 19:05:17', '2026-10-07 18:55:21', 0, '127.0.0.1', '2026-10-07 18:55:17'),
(83, 'adminEmpresaDemo@demoSCT.cl', '$2y$10$Ae.pg3GIi.7fXUXcPJAbx.4AhePJJkenvZLu6MJsXKdkNYzCX/t.S', '2026-10-07 19:08:53', '2026-10-07 18:58:56', 0, '127.0.0.1', '2026-10-07 18:58:53'),
(84, 'ContratistaEmpresaDemo@demoSCT.cl', '$2y$10$2CYgbScBJjMFmmi1RTy0WOjwTw7sZqipbXjf3t6NNtBm1dE4qd9Fq', '2026-10-07 19:09:48', '2026-10-07 18:59:51', 0, '127.0.0.1', '2026-10-07 18:59:48'),
(85, 'ParamedicoEmpresaDemo@demoSCT.cl', '$2y$10$yiFGsttT62Y/dQE9z9JX9OqVVl/ki98GdpYzKDOfDWNPjkdBv0nfO', '2026-10-07 19:11:53', '2026-10-07 19:01:56', 0, '127.0.0.1', '2026-10-07 19:01:53'),
(86, 'SuperUsuarioDemo@demoSCT.cl', '$2y$10$QU12DJr3ie8unakM3HBV0OM1bwTPVlB1ovFI1MoHnXE3tg6O6blB.', '2026-10-07 19:12:19', '2026-10-07 19:02:22', 0, '127.0.0.1', '2026-10-07 19:02:19'),
(87, 'TrabajadorEmpresaDemo@demoSCT.cl', '$2y$10$qmneE0SB/ewM6BRw4Rk9Z.2OsYAqTZxgwGk2A5vRs20VLhpHFhkNS', '2026-10-07 19:12:43', '2026-10-07 19:02:46', 0, '127.0.0.1', '2026-10-07 19:02:43'),
(88, 'TrabajadorEmpresaDemo@demoSCT.cl', '$2y$10$Ie3PYTsIsrYhMv6Ub6TRR.wfCKrWC1ANFYBVEdcTW0lKvtELpKKwW', '2026-10-07 19:24:43', '2026-10-07 19:14:49', 0, '127.0.0.1', '2026-10-07 19:14:43'),
(89, 'UsuarioEmpresaDemo@demoSCT.cl', '$2y$10$0CPHZdkGq29zVqM0a1EdBOS/jG..3/y0rXB9FwTJynIwWUsNbY9/K', '2026-10-07 19:25:21', '2026-10-07 19:15:26', 0, '127.0.0.1', '2026-10-07 19:15:21'),
(90, 'TrabajadorEmpresaDemo@demoSCT.cl', '$2y$10$iSA03zFPLliO1Uhpkn875e9MpFWy65WXeQyCfwJTjvVICa3tjrHOC', '2026-10-07 19:28:25', '2026-10-07 19:18:31', 0, '127.0.0.1', '2026-10-07 19:18:25'),
(91, 'UsuarioEmpresaDemo@demoSCT.cl', '$2y$10$KC0LPAjU.bGCY.vjVOH1KOoqjRiK34IuFAZxT52SEg8LW4NaaTC9u', '2026-10-07 19:30:26', '2026-10-07 19:20:30', 0, '127.0.0.1', '2026-10-07 19:20:26'),
(92, 'SuperUsuarioDemo@demoSCT.cl', '$2y$10$NSa1RYLFQPpmUbwpGNZ8ruvsvQ9Mmldt0MM8wmcKeFSxqd.wHnNNG', '2026-10-07 19:32:10', '2026-10-07 19:22:14', 0, '127.0.0.1', '2026-10-07 19:22:10'),
(93, 'JefaturaEmpresaDemo@demoSCT.cl', '$2y$10$SHVg9aSZOEB7TpRVA7mg8.31rQzmCuN5tyhUl4W6XEgu9kBe9xTsa', '2026-10-07 19:33:28', '2026-10-07 19:23:31', 0, '127.0.0.1', '2026-10-07 19:23:28'),
(94, 'JefaturaEmpresaDemo@demoSCT.cl', '$2y$10$B9a/eYaofbGYWo7EevKM0uc0DB.CYX.tRTV7qppyJgecjMG0aRCs.', '2026-10-07 19:35:06', '2026-10-07 19:25:09', 0, '127.0.0.1', '2026-10-07 19:25:06'),
(95, 'JefaturaEmpresaDemo@demoSCT.cl', '$2y$10$kND7nN8f21InRIOM4JDOIuczqedM3QvnGMhzcFREeJDK36Pag68qS', '2026-10-07 19:35:59', '2026-10-07 19:26:02', 0, '127.0.0.1', '2026-10-07 19:25:59'),
(96, 'JefaturaEmpresaDemo@demoSCT.cl', '$2y$10$RYlnI5ephN/nPbmgRmQMpuEI6KqKoblOwNaG/zLMTbEM.AS45.N2m', '2026-10-07 19:58:59', '2026-10-07 19:49:03', 0, '127.0.0.1', '2026-10-07 19:48:59'),
(97, 'UsuarioEmpresaDemo@demoSCT.cl', '$2y$10$SKGZdj83S1ZDbwmUVeWq5ehWBfaBdj6fmJ.Sf2eje8iMngsPalShG', '2026-10-07 19:59:17', '2026-10-07 19:49:20', 0, '127.0.0.1', '2026-10-07 19:49:17'),
(98, 'UsuarioEmpresaDemo@demoSCT.cl', '$2y$10$oRkZ8qQAKvjodm2jDNLZGuXgJ3eauGTpaPc/IjHlZPyUKpv1g5VO2', '2026-10-07 21:02:46', '2026-10-07 20:52:48', 0, '127.0.0.1', '2026-10-07 20:52:46'),
(126, 'UsuarioNuevoEmpresaDemo@demoSCT.cl', '$2y$10$IgDym8ogZucDyia6E4e5wOgyzzpiOyGNCvw.Blwd5HKpAlKFY9I7u', '2026-10-08 19:52:41', '2026-10-08 19:54:34', 0, '127.0.0.1', '2026-10-08 19:42:41'),
(127, 'UsuarioEmpresaDemo@demoSCT.cl', '$2y$10$LHB7/6j35penthkEJKwpueo2mSVBULUtXiLhbGmhfpDp36E9yg8AO', '2026-10-08 19:52:56', '2026-10-08 19:42:59', 0, '127.0.0.1', '2026-10-08 19:42:56'),
(128, 'adminEmpresaDemo@demoSCT.cl', '$2y$10$Iq8drnBpF4zYBjZ0I2PRI.Y2ZveAKLeAHL10NBYcDcb56IHNgM4CK', '2026-10-08 19:54:56', '2026-10-08 19:45:00', 0, '127.0.0.1', '2026-10-08 19:44:56'),
(129, 'JefaturaEmpresaDemo@demoSCT.cl', '$2y$10$3FDmVw0jgbzsAFDBFF719eFaIhd09zIjun5ogn0hykh60cMtaDdnm', '2026-10-08 20:03:03', '2026-10-08 19:56:04', 0, '127.0.0.1', '2026-10-08 19:53:03'),
(130, 'adminEmpresaDemo@demoSCT.cl', '$2y$10$vlqWG4WX1Uyh1LHJdhdAdO4tHHlKLndQHGWUti8ADS3dOICfEQlkS', '2026-10-08 20:03:15', '2026-10-08 19:53:18', 0, '127.0.0.1', '2026-10-08 19:53:15'),
(131, 'UsuarioNuevoEmpresaDemo@demoSCT.cl', '$2y$10$tMDJH4RRLp/HSSTB.Ximyet7O/4TOwjIl34wdJ6nshmMoKUrA3JaG', '2026-10-08 20:04:34', '2026-10-08 19:54:37', 0, '127.0.0.1', '2026-10-08 19:54:34'),
(132, 'UsuarioEmpresaDemo@demoSCT.cl', '$2y$10$tDsGgb/6HBif9T/tyhzi1uvM9Mv0TZLO2D179W5Q6UCwegeIMZNvi', '2026-10-08 20:04:51', '2026-10-08 19:54:54', 0, '127.0.0.1', '2026-10-08 19:54:51'),
(133, 'ContratistaEmpresaDemo@demoSCT.cl', '$2y$10$.1W.3wUkSGH.r0yCjtXz/.0UgOdC46KJAJK/VyLOAj0On3k6kHWEe', '2026-10-08 20:05:12', NULL, 0, '127.0.0.1', '2026-10-08 19:55:12'),
(134, 'JefaturaEmpresaDemo@demoSCT.cl', '$2y$10$QzOIS.ujdQCRX0CDAdwmeu/RtYj38RmWmp7kxiFIf2Mv4g8yjxrxC', '2026-10-08 20:06:04', '2026-10-08 19:56:08', 0, '127.0.0.1', '2026-10-08 19:56:04'),
(135, 'adminEmpresaDemo@demoSCT.cl', '$2y$10$VSUnoPBjRv3059R8eKNhRuHBiB89P1U9bir3MSzatJ8/OSy7F48im', '2026-10-08 20:07:30', '2026-10-08 19:57:33', 0, '127.0.0.1', '2026-10-08 19:57:30'),
(136, 'UsuarioNuevoEmpresaDemo@demoSCT.cl', '$2y$10$1SxHKq/6eD7XQ3TWn2RVxO7pAEIhznwsJLc0CO/O4AzIKobcziMfK', '2026-10-08 20:30:01', '2026-10-08 20:20:05', 0, '127.0.0.1', '2026-10-08 20:20:01'),
(137, 'adminEmpresaDemo@demoSCT.cl', '$2y$10$k7D8JLZR7rciGQ34toU9deSHtLZxVQDuiHHy1qVKguHvkLFxoXYt.', '2026-10-08 20:39:33', '2026-10-08 20:29:38', 0, '127.0.0.1', '2026-10-08 20:29:33'),
(138, 'UsuarioEmpresaDemo@demoSCT.cl', '$2y$10$zbKm4rDSzkU.VXqz9dai3OImbfP3wsaHtsdA8yH3x6pSq3iQnllJa', '2026-10-08 20:39:57', '2026-10-08 20:30:02', 0, '127.0.0.1', '2026-10-08 20:29:57'),
(139, 'UsuarioNuevoEmpresaDemo@demoSCT.cl', '$2y$10$LUDQ86YNCwjX7uXPJxEFWu6Tsi8cDMtFzoaQnjZUQ9gIqgpi9lql2', '2026-10-08 20:57:02', '2026-10-08 20:47:06', 0, '127.0.0.1', '2026-10-08 20:47:02'),
(140, 'UsuarioEmpresaDemo@demoSCT.cl', '$2y$10$FWWV1m8HDFd5M2ne3B8z3uXobKSTWIavgLkIphHR2PJsPW22/8Wqi', '2026-10-08 21:00:41', '2026-10-08 20:50:45', 0, '127.0.0.1', '2026-10-08 20:50:41'),
(141, 'UsuarioEmpresaDemo@demoSCT.cl', '$2y$10$n0sQmTsL/D3GzqTGkk4x6.htlhUESvbwOzW2nyBXpMogTK/af95zC', '2026-10-08 23:13:15', '2026-10-08 23:03:18', 0, '127.0.0.1', '2026-10-08 23:03:15'),
(142, 'UsuarioNuevoEmpresaDemo@demoSCT.cl', '$2y$10$G3V3j0/EAXt0k3ubZ/YdPuRHlbMpVN5o4lXX0c7A1iMj5aUzT4WaW', '2026-10-08 23:13:42', '2026-10-08 23:03:45', 0, '127.0.0.1', '2026-10-08 23:03:42'),
(143, 'GerenteEmpresaDemo@demoSCT.cl', '$2y$10$fg.ma3ZXo7Ta/w/3xajw/.HN8d8HupAZdFyfvsvXcIOPK3oLim7B2', '2026-10-08 23:15:35', '2026-10-08 23:05:38', 0, '127.0.0.1', '2026-10-08 23:05:35'),
(144, 'adminEmpresaDemo@demoSCT.cl', '$2y$10$.sBCNR87XVIxUvqtInTjIOIk9c2Rlo8OHJO2ajHlRj4x8719sFvfy', '2026-10-08 23:26:46', '2026-10-08 23:25:21', 0, '127.0.0.1', '2026-10-08 23:16:46'),
(145, 'UsuarioEmpresaDemo@demoSCT.cl', '$2y$10$XogD6k8KWOcaD963TSHs9elZP0zQroGI1B7caqTrYNSoVMNRZVfUC', '2026-10-08 23:27:00', '2026-10-08 23:17:03', 0, '127.0.0.1', '2026-10-08 23:17:00'),
(146, 'UsuarioNuevoEmpresaDemo@demoSCT.cl', '$2y$10$YEZKhsu.Te4Cm9mCoJEvWOI3YKHXQFpoj7TXSjoa3uJyACCDYsFKG', '2026-10-08 23:30:12', '2026-10-08 23:20:17', 0, '127.0.0.1', '2026-10-08 23:20:12'),
(147, 'adminEmpresaDemo@demoSCT.cl', '$2y$10$svp5N5J08d27OGJzbkMXZefdL8/Ml3e45SDFn4/FpYUhdqTEyflhO', '2026-10-08 23:35:21', '2026-10-08 23:25:24', 0, '127.0.0.1', '2026-10-08 23:25:21'),
(148, 'JefaturaEmpresaDemo@demoSCT.cl', '$2y$10$L49hcbzoHmRBKBEwv0KsLOi9HC3AZt77uZLBUdrJnsYe6Ugcxc.ne', '2026-10-08 23:35:49', '2026-10-08 23:25:54', 0, '127.0.0.1', '2026-10-08 23:25:49'),
(149, 'GerenteEmpresaDemo@demoSCT.cl', '$2y$10$JJ77wMz8rReZiAs7x3hqgOLbuQlZAI.qLb7uXSnbl1xZsAC2n36iy', '2026-10-08 23:36:22', '2026-10-08 23:26:29', 0, '127.0.0.1', '2026-10-08 23:26:22'),
(150, 'adminEmpresaDemo@demoSCT.cl', '$2y$10$mVTLhbtfpIL5VHY46KX0rO24jQXcB9Hr9Cn8BpyvUS6F1BNOB6QTa', '2026-10-09 00:09:06', '2026-10-08 23:59:10', 0, '127.0.0.1', '2026-10-08 23:59:06'),
(151, 'JefaturaEmpresaDemo@demoSCT.cl', '$2y$10$aZs7bGAH8qXaCSsbz0Zph.SpQfCfB4aUXIg8/27yX1rlcIVueikLi', '2026-10-09 00:09:54', '2026-10-08 23:59:57', 0, '127.0.0.1', '2026-10-08 23:59:54'),
(152, 'adminEmpresaDemo@demoSCT.cl', '$2y$10$wnBkVwlx4jiMigNxAZW3t.qjFs5L4TIn1Cs3Dz.KXtJnabX.UySne', '2026-10-09 00:16:09', '2026-10-09 00:06:14', 0, '127.0.0.1', '2026-10-09 00:06:09'),
(153, 'GerenteEmpresaDemo@demoSCT.cl', '$2y$10$PoeSGQfuLG.gx/Kgr2BLh.geNv5NV2B6cH6jqXDO1oVOexZGm5sJa', '2026-10-09 00:16:56', '2026-10-09 00:07:00', 0, '127.0.0.1', '2026-10-09 00:06:56'),
(154, 'adminEmpresaDemo@demoSCT.cl', '$2y$10$/fHYAe5pRBrYyK1nERhXe.aZagqUDWppY3.mGkr8iJXzYYh6Op/sK', '2026-10-09 00:19:24', '2026-10-09 00:09:28', 0, '127.0.0.1', '2026-10-09 00:09:24'),
(155, 'JefaturaEmpresaDemo@demoSCT.cl', '$2y$10$y4tK1WbNrsLZubOGgmYTIenKXwjgH.6IMtfWrv.i/efxf7C97ii7q', '2026-10-09 00:20:33', '2026-10-09 00:10:41', 0, '127.0.0.1', '2026-10-09 00:10:33'),
(156, 'adminEmpresaDemo@demoSCT.cl', '$2y$10$qpzbmxa8O5CeK6rNWZeLb.AX2dJU2FzhqW3rwFGlJ7BcuqDuRzBp6', '2026-10-09 00:23:03', '2026-10-09 00:13:07', 0, '127.0.0.1', '2026-10-09 00:13:03'),
(157, 'JefaturaEmpresaDemo@demoSCT.cl', '$2y$10$2DfXpn0Q.rfPZtwjjTGR7emzzGe1Ab9XBX054eK10/q1FKF0C9wy.', '2026-10-09 00:25:32', '2026-10-09 00:15:36', 0, '127.0.0.1', '2026-10-09 00:15:32'),
(158, 'adminEmpresaDemo@demoSCT.cl', '$2y$10$vRtxI0mNrnFktZLmaoTa4.pGMB1Ks6OmiGwIbK64r.79C.Cwh65MK', '2026-10-09 00:26:01', '2026-10-09 00:16:05', 0, '127.0.0.1', '2026-10-09 00:16:01'),
(159, 'UsuarioEmpresaDemo@demoSCT.cl', '$2y$10$ysOfCagQxva/O2w2T/1ZPuNQ7rx5SNsZNyDzXuDv4/2o05v0UEzcq', '2026-10-09 00:43:22', '2026-10-09 00:33:26', 0, '127.0.0.1', '2026-10-09 00:33:22');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `onboarding_assessment_answers`
--

CREATE TABLE `onboarding_assessment_answers` (
  `id_answer` int(11) NOT NULL,
  `id_attempt` int(11) NOT NULL,
  `id_question` int(11) NOT NULL,
  `id_option` int(11) NOT NULL,
  `is_correct` tinyint(1) NOT NULL,
  `answered_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `onboarding_assessment_answers`
--

INSERT INTO `onboarding_assessment_answers` (`id_answer`, `id_attempt`, `id_question`, `id_option`, `is_correct`, `answered_at`) VALUES
(121, 10, 19, 587, 1, '2026-10-08 19:54:04'),
(122, 10, 57, 739, 1, '2026-10-08 19:54:04'),
(123, 10, 12, 559, 1, '2026-10-08 19:54:04'),
(124, 10, 24, 607, 1, '2026-10-08 19:54:04'),
(125, 10, 7, 538, 0, '2026-10-08 19:54:04'),
(126, 10, 15, 571, 1, '2026-10-08 19:54:04'),
(127, 10, 17, 578, 0, '2026-10-08 19:54:04'),
(128, 10, 28, 623, 1, '2026-10-08 19:54:04'),
(129, 10, 70, 791, 1, '2026-10-08 19:54:04'),
(130, 10, 4, 524, 0, '2026-10-08 19:54:04'),
(131, 10, 42, 678, 0, '2026-10-08 19:54:04'),
(132, 10, 8, 540, 0, '2026-10-08 19:54:04'),
(133, 10, 34, 646, 0, '2026-10-08 19:54:04'),
(134, 10, 25, 609, 0, '2026-10-08 19:54:04'),
(135, 10, 58, 741, 0, '2026-10-08 19:54:04'),
(136, 11, 32, 636, 0, '2026-10-08 19:57:13'),
(137, 11, 35, 649, 0, '2026-10-08 19:57:13'),
(138, 11, 56, 732, 0, '2026-10-08 19:57:13'),
(139, 11, 22, 598, 0, '2026-10-08 19:57:13'),
(140, 11, 30, 628, 0, '2026-10-08 19:57:13'),
(141, 11, 50, 711, 1, '2026-10-08 19:57:13'),
(142, 11, 33, 641, 0, '2026-10-08 19:57:13'),
(143, 11, 63, 760, 0, '2026-10-08 19:57:13'),
(144, 11, 17, 577, 0, '2026-10-08 19:57:13'),
(145, 11, 43, 680, 0, '2026-10-08 19:57:13'),
(146, 11, 39, 666, 0, '2026-10-08 19:57:13'),
(147, 11, 31, 633, 0, '2026-10-08 19:57:13'),
(148, 11, 18, 580, 0, '2026-10-08 19:57:13'),
(149, 11, 36, 653, 0, '2026-10-08 19:57:13'),
(150, 11, 1, 512, 0, '2026-10-08 19:57:13'),
(151, 13, 9, 546, 0, '2026-10-08 23:06:29'),
(152, 13, 40, 668, 0, '2026-10-08 23:06:29'),
(153, 13, 62, 756, 0, '2026-10-08 23:06:29'),
(154, 13, 68, 782, 0, '2026-10-08 23:06:29'),
(155, 13, 70, 791, 1, '2026-10-08 23:06:29'),
(156, 13, 34, 646, 0, '2026-10-08 23:06:29'),
(157, 13, 31, 633, 0, '2026-10-08 23:06:29'),
(158, 13, 47, 697, 0, '2026-10-08 23:06:29'),
(159, 13, 48, 701, 0, '2026-10-08 23:06:29'),
(160, 13, 46, 695, 1, '2026-10-08 23:06:29'),
(161, 13, 2, 518, 0, '2026-10-08 23:06:29'),
(162, 13, 64, 765, 0, '2026-10-08 23:06:29'),
(163, 13, 53, 721, 0, '2026-10-08 23:06:29'),
(164, 13, 17, 579, 1, '2026-10-08 23:06:29'),
(165, 13, 22, 599, 1, '2026-10-08 23:06:29'),
(166, 14, 32, 639, 1, '2026-10-09 00:35:48'),
(167, 14, 10, 551, 1, '2026-10-09 00:35:48'),
(168, 14, 15, 571, 1, '2026-10-09 00:35:48'),
(169, 14, 17, 579, 1, '2026-10-09 00:35:48'),
(170, 14, 31, 635, 1, '2026-10-09 00:35:48'),
(171, 14, 56, 735, 1, '2026-10-09 00:35:48'),
(172, 14, 30, 631, 1, '2026-10-09 00:35:48'),
(173, 14, 44, 687, 1, '2026-10-09 00:35:48'),
(174, 14, 4, 527, 1, '2026-10-09 00:35:48'),
(175, 14, 5, 531, 1, '2026-10-09 00:35:48'),
(176, 14, 40, 671, 1, '2026-10-09 00:35:48'),
(177, 14, 3, 523, 1, '2026-10-09 00:35:48'),
(178, 14, 68, 783, 1, '2026-10-09 00:35:48'),
(179, 14, 61, 755, 1, '2026-10-09 00:35:48'),
(180, 14, 7, 539, 1, '2026-10-09 00:35:48');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `onboarding_assessment_attempts`
--

CREATE TABLE `onboarding_assessment_attempts` (
  `id_attempt` int(11) NOT NULL,
  `id_users` varchar(50) NOT NULL,
  `status` enum('in_progress','completed','cancelled') NOT NULL DEFAULT 'in_progress',
  `score` decimal(5,2) DEFAULT NULL,
  `started_at` datetime NOT NULL DEFAULT current_timestamp(),
  `completed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `onboarding_assessment_attempts`
--

INSERT INTO `onboarding_assessment_attempts` (`id_attempt`, `id_users`, `status`, `score`, `started_at`, `completed_at`) VALUES
(10, 'adminEmpresaDemo@demoSCT.cl', 'completed', 46.67, '2026-10-08 19:45:29', '2026-10-08 19:54:04'),
(11, 'JefaturaEmpresaDemo@demoSCT.cl', 'completed', 6.67, '2026-10-08 19:56:40', '2026-10-08 19:57:13'),
(13, 'GerenteEmpresaDemo@demoSCT.cl', 'completed', 26.67, '2026-10-08 23:06:14', '2026-10-08 23:06:29'),
(14, 'UsuarioEmpresaDemo@demoSCT.cl', 'completed', 100.00, '2026-10-09 00:34:03', '2026-10-09 00:35:48');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `onboarding_assessment_attempt_questions`
--

CREATE TABLE `onboarding_assessment_attempt_questions` (
  `id_attempt_question` int(11) NOT NULL,
  `id_attempt` int(11) NOT NULL,
  `id_question` int(11) NOT NULL,
  `sort_order` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `onboarding_assessment_attempt_questions`
--

INSERT INTO `onboarding_assessment_attempt_questions` (`id_attempt_question`, `id_attempt`, `id_question`, `sort_order`) VALUES
(136, 10, 19, 1),
(137, 10, 57, 2),
(138, 10, 12, 3),
(139, 10, 24, 4),
(140, 10, 7, 5),
(141, 10, 15, 6),
(142, 10, 17, 7),
(143, 10, 28, 8),
(144, 10, 70, 9),
(145, 10, 4, 10),
(146, 10, 42, 11),
(147, 10, 8, 12),
(148, 10, 34, 13),
(149, 10, 25, 14),
(150, 10, 58, 15),
(151, 11, 32, 1),
(152, 11, 35, 2),
(153, 11, 56, 3),
(154, 11, 22, 4),
(155, 11, 30, 5),
(156, 11, 50, 6),
(157, 11, 33, 7),
(158, 11, 63, 8),
(159, 11, 17, 9),
(160, 11, 43, 10),
(161, 11, 39, 11),
(162, 11, 31, 12),
(163, 11, 18, 13),
(164, 11, 36, 14),
(165, 11, 1, 15),
(181, 13, 9, 1),
(182, 13, 40, 2),
(183, 13, 62, 3),
(184, 13, 68, 4),
(185, 13, 70, 5),
(186, 13, 34, 6),
(187, 13, 31, 7),
(188, 13, 47, 8),
(189, 13, 48, 9),
(190, 13, 46, 10),
(191, 13, 2, 11),
(192, 13, 64, 12),
(193, 13, 53, 13),
(194, 13, 17, 14),
(195, 13, 22, 15),
(196, 14, 32, 1),
(197, 14, 10, 2),
(198, 14, 15, 3),
(199, 14, 17, 4),
(200, 14, 31, 5),
(201, 14, 56, 6),
(202, 14, 30, 7),
(203, 14, 44, 8),
(204, 14, 4, 9),
(205, 14, 5, 10),
(206, 14, 40, 11),
(207, 14, 3, 12),
(208, 14, 68, 13),
(209, 14, 61, 14),
(210, 14, 7, 15);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `onboarding_assessment_draft_answers`
--

CREATE TABLE `onboarding_assessment_draft_answers` (
  `id_attempt` int(11) NOT NULL,
  `id_question` int(11) NOT NULL,
  `id_option` int(11) NOT NULL,
  `saved_at` datetime NOT NULL DEFAULT current_timestamp(),
  `last_update` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `onboarding_questions`
--

CREATE TABLE `onboarding_questions` (
  `id_question` int(11) NOT NULL,
  `question_code` varchar(20) NOT NULL,
  `question` text NOT NULL,
  `state` tinyint(1) NOT NULL DEFAULT 1,
  `date_create` datetime NOT NULL DEFAULT current_timestamp(),
  `last_update` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `onboarding_questions`
--

INSERT INTO `onboarding_questions` (`id_question`, `question_code`, `question`, `state`, `date_create`, `last_update`) VALUES
(1, 'SST001', '¿Qué debes hacer si detectas una condición insegura en tu lugar de trabajo?', 1, '2026-10-08 15:12:02', '2026-10-08 15:12:02'),
(2, 'SST002', '¿Qué significa la sigla EPP?', 1, '2026-10-08 15:12:02', '2026-10-08 15:12:02'),
(3, 'SST003', '¿Cuál es el objetivo principal del EPP?', 1, '2026-10-08 15:12:02', '2026-10-08 15:12:02'),
(4, 'SST004', '¿Qué debe hacerse con un EPP dañado?', 1, '2026-10-08 15:12:02', '2026-10-08 15:12:02'),
(5, 'SST005', '¿Qué es un casi accidente o near miss?', 1, '2026-10-08 15:12:02', '2026-10-08 15:12:02'),
(6, 'SST006', '¿Qué corresponde hacer con un casi accidente?', 1, '2026-10-08 15:12:02', '2026-10-08 15:12:02'),
(7, 'SST007', 'Antes de iniciar una tarea no rutinaria, ¿qué es recomendable?', 1, '2026-10-08 15:12:02', '2026-10-08 15:12:02'),
(8, 'SST008', 'Si existe un riesgo grave e inminente no controlado, ¿qué debe hacer el trabajador?', 1, '2026-10-08 15:12:02', '2026-10-08 15:12:02'),
(9, 'SST009', '¿Qué ayuda a prevenir caídas al mismo nivel?', 1, '2026-10-08 15:12:02', '2026-10-08 15:12:02'),
(10, 'SST010', 'Si se derrama líquido en una zona de tránsito, ¿qué corresponde?', 1, '2026-10-08 15:12:02', '2026-10-08 15:12:02'),
(11, 'SST011', '¿Qué debe mantenerse libre en todo momento?', 1, '2026-10-08 15:12:02', '2026-10-08 15:12:02'),
(12, 'SST012', 'Al escuchar una alarma de evacuación, ¿qué corresponde?', 1, '2026-10-08 15:12:02', '2026-10-08 15:12:02'),
(13, 'SST013', '¿Para qué sirve el punto de encuentro en una evacuación?', 1, '2026-10-08 15:12:02', '2026-10-08 15:12:02'),
(14, 'SST014', 'Si descubres un incendio incipiente, ¿qué debes hacer primero?', 1, '2026-10-08 15:12:02', '2026-10-08 15:12:02'),
(15, 'SST015', '¿Cuándo es apropiado usar un extintor portátil?', 1, '2026-10-08 15:12:02', '2026-10-08 15:12:02'),
(16, 'SST016', 'Si una salida de emergencia está bloqueada, ¿qué corresponde?', 1, '2026-10-08 15:12:02', '2026-10-08 15:12:02'),
(17, 'SST017', 'Ante una persona lesionada, ¿cuál es una acción inicial segura?', 1, '2026-10-08 15:12:02', '2026-10-08 15:12:02'),
(18, 'SST018', '¿Quién debe realizar primeros auxilios especializados?', 1, '2026-10-08 15:12:02', '2026-10-08 15:12:02'),
(19, 'SST019', 'Para levantar manualmente una carga, ¿qué práctica es más segura?', 1, '2026-10-08 15:12:02', '2026-10-08 15:12:02'),
(20, 'SST020', 'Si una carga es demasiado pesada para una persona, ¿qué debe hacer?', 1, '2026-10-08 15:12:02', '2026-10-08 15:12:02'),
(21, 'SST021', '¿Qué ayuda a reducir molestias en trabajo de oficina?', 1, '2026-10-08 15:12:02', '2026-10-08 15:12:02'),
(22, 'SST022', '¿Cómo debería ubicarse normalmente la pantalla del computador?', 1, '2026-10-08 15:12:02', '2026-10-08 15:12:02'),
(23, 'SST023', 'Si un equipo eléctrico está defectuoso y no estás autorizado para repararlo, ¿qué debes hacer?', 1, '2026-10-08 15:12:02', '2026-10-08 15:12:02'),
(24, 'SST024', '¿Por qué no deben usarse enchufes o extensiones dañadas?', 1, '2026-10-08 15:12:02', '2026-10-08 15:12:02'),
(25, 'SST025', '¿Para qué sirve el bloqueo y etiquetado de energías peligrosas (LOTO)?', 1, '2026-10-08 15:12:02', '2026-10-08 15:12:02'),
(26, 'SST026', '¿Quién puede retirar normalmente un bloqueo personal LOTO?', 1, '2026-10-08 15:12:02', '2026-10-08 15:12:02'),
(27, 'SST027', 'Antes de usar una escalera portátil, ¿qué debe revisarse?', 1, '2026-10-08 15:12:02', '2026-10-08 15:12:02'),
(28, 'SST028', '¿Qué práctica es insegura al utilizar una escalera?', 1, '2026-10-08 15:12:02', '2026-10-08 15:12:02'),
(29, 'SST029', 'Antes de un trabajo con riesgo de caída de altura, ¿qué corresponde?', 1, '2026-10-08 15:12:02', '2026-10-08 15:12:02'),
(30, 'SST030', '¿Qué debe hacerse con una herramienta manual defectuosa?', 1, '2026-10-08 15:12:02', '2026-10-08 15:12:02'),
(31, 'SST031', '¿Para qué sirven las guardas de una máquina?', 1, '2026-10-08 15:12:02', '2026-10-08 15:12:02'),
(32, 'SST032', '¿Es correcto retirar una guarda de máquina para trabajar más rápido?', 1, '2026-10-08 15:12:02', '2026-10-08 15:12:02'),
(33, 'SST033', 'Antes de usar un producto químico desconocido, ¿qué corresponde?', 1, '2026-10-08 15:12:02', '2026-10-08 15:12:02'),
(34, 'SST034', '¿Qué documento informa peligros, almacenamiento, primeros auxilios y derrames de un producto químico?', 1, '2026-10-08 15:12:02', '2026-10-08 15:12:02'),
(35, 'SST035', 'Si un envase químico no tiene identificación legible, ¿qué debes hacer?', 1, '2026-10-08 15:12:02', '2026-10-08 15:12:02'),
(36, 'SST036', '¿Por qué es importante la ventilación al trabajar con vapores, gases o polvo?', 1, '2026-10-08 15:12:02', '2026-10-08 15:12:02'),
(37, 'SST037', 'Si una tarea genera ruido elevado, ¿qué medida corresponde?', 1, '2026-10-08 15:12:02', '2026-10-08 15:12:02'),
(38, 'SST038', 'Antes de usar protectores auditivos, ¿qué debe verificarse?', 1, '2026-10-08 15:12:02', '2026-10-08 15:12:02'),
(39, 'SST039', '¿Cuándo debe utilizarse protección respiratoria?', 1, '2026-10-08 15:12:02', '2026-10-08 15:12:02'),
(40, 'SST040', '¿Puede cualquier mascarilla proteger frente a cualquier contaminante?', 1, '2026-10-08 15:12:02', '2026-10-08 15:12:02'),
(41, 'SST041', 'Antes de realizar soldadura o corte, ¿qué debe verificarse?', 1, '2026-10-08 15:12:02', '2026-10-08 15:12:02'),
(42, 'SST042', '¿Qué caracteriza a un espacio confinado?', 1, '2026-10-08 15:12:02', '2026-10-08 15:12:02'),
(43, 'SST043', '¿Debe ingresarse a un espacio confinado sin autorización ni evaluación previa?', 1, '2026-10-08 15:12:02', '2026-10-08 15:12:02'),
(44, 'SST044', 'Al conducir un vehículo de la empresa, ¿qué conducta es segura?', 1, '2026-10-08 15:12:03', '2026-10-08 15:12:03'),
(45, 'SST045', 'Si un conductor siente somnolencia intensa, ¿qué debe hacer?', 1, '2026-10-08 15:12:03', '2026-10-08 15:12:03'),
(46, 'SST046', 'En zonas con grúas horquilla, ¿qué debe hacer un peatón?', 1, '2026-10-08 15:12:03', '2026-10-08 15:12:03'),
(47, 'SST047', '¿Es seguro pasar bajo una carga suspendida?', 1, '2026-10-08 15:12:03', '2026-10-08 15:12:03'),
(48, 'SST048', '¿Qué beneficio tiene mantener orden y aseo?', 1, '2026-10-08 15:12:03', '2026-10-08 15:12:03'),
(49, 'SST049', '¿Dónde deben almacenarse los materiales?', 1, '2026-10-08 15:12:03', '2026-10-08 15:12:03'),
(50, 'SST050', 'Antes de usar un arnés contra caídas, ¿qué corresponde?', 1, '2026-10-08 15:12:03', '2026-10-08 15:12:03'),
(51, 'SST051', '¿Cuál describe mejor la jerarquía de controles?', 1, '2026-10-08 15:12:03', '2026-10-08 15:12:03'),
(52, 'SST052', 'Si es posible eliminar completamente un peligro, ¿qué tipo de control es?', 1, '2026-10-08 15:12:03', '2026-10-08 15:12:03'),
(53, 'SST053', '¿Para qué sirve un procedimiento de trabajo seguro?', 1, '2026-10-08 15:12:03', '2026-10-08 15:12:03'),
(54, 'SST054', 'Si no tienes capacitación o autorización para una tarea, ¿qué debes hacer?', 1, '2026-10-08 15:12:03', '2026-10-08 15:12:03'),
(55, 'SST055', 'Si un equipo tiene una falla que puede afectar la seguridad, ¿qué corresponde?', 1, '2026-10-08 15:12:03', '2026-10-08 15:12:03'),
(56, 'SST056', '¿Para qué sirve una charla de seguridad antes de una tarea?', 1, '2026-10-08 15:12:03', '2026-10-08 15:12:03'),
(57, 'SST057', 'Si cambian las condiciones de una tarea ya evaluada, ¿qué corresponde?', 1, '2026-10-08 15:12:03', '2026-10-08 15:12:03'),
(58, 'SST058', '¿Para qué sirve una señal o barrera de seguridad?', 1, '2026-10-08 15:12:03', '2026-10-08 15:12:03'),
(59, 'SST059', 'Si una zona está delimitada por riesgo y no estás autorizado, ¿qué debes hacer?', 1, '2026-10-08 15:12:03', '2026-10-08 15:12:03'),
(60, 'SST060', '¿Por qué el EPP se considera una última barrera de control?', 1, '2026-10-08 15:12:03', '2026-10-08 15:12:03'),
(61, 'SST061', 'En una jornada con altas temperaturas, ¿qué ayuda a prevenir estrés térmico?', 1, '2026-10-08 15:12:03', '2026-10-08 15:12:03'),
(62, 'SST062', 'En labores con exposición solar, ¿qué medida es apropiada?', 1, '2026-10-08 15:12:03', '2026-10-08 15:12:03'),
(63, 'SST063', 'En ambientes fríos, ¿qué medida es adecuada?', 1, '2026-10-08 15:12:03', '2026-10-08 15:12:03'),
(64, 'SST064', '¿Por qué la fatiga es un riesgo de seguridad?', 1, '2026-10-08 15:12:03', '2026-10-08 15:12:03'),
(65, 'SST065', 'Si aparecen síntomas que podrían estar relacionados con el trabajo, ¿qué corresponde?', 1, '2026-10-08 15:12:03', '2026-10-08 15:12:03'),
(66, 'SST066', '¿Qué es una enfermedad profesional u ocupacional?', 1, '2026-10-08 15:12:03', '2026-10-08 15:12:03'),
(67, 'SST067', '¿Por qué es importante participar en vigilancia de salud ocupacional cuando corresponda?', 1, '2026-10-08 15:12:03', '2026-10-08 15:12:03'),
(68, 'SST068', '¿Qué medida básica ayuda a prevenir contagios de enfermedades transmisibles?', 1, '2026-10-08 15:12:03', '2026-10-08 15:12:03'),
(69, 'SST069', '¿Qué debe hacerse con residuos peligrosos o especiales?', 1, '2026-10-08 15:12:03', '2026-10-08 15:12:03'),
(70, 'SST070', 'Ante un derrame químico que no estás capacitado para controlar, ¿qué corresponde?', 1, '2026-10-08 15:12:03', '2026-10-08 15:12:03');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `onboarding_question_options`
--

CREATE TABLE `onboarding_question_options` (
  `id_option` int(11) NOT NULL,
  `id_question` int(11) NOT NULL,
  `option_text` varchar(500) NOT NULL,
  `is_correct` tinyint(1) NOT NULL DEFAULT 0,
  `sort_order` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `onboarding_question_options`
--

INSERT INTO `onboarding_question_options` (`id_option`, `id_question`, `option_text`, `is_correct`, `sort_order`) VALUES
(512, 1, 'Pedir a otro trabajador que asuma el riesgo', 0, 4),
(513, 1, 'Esperar al final de la semana', 0, 3),
(514, 1, 'Ignorarla mientras no ocurra un accidente', 0, 2),
(515, 1, 'Reportarla y aplicar los controles definidos', 1, 1),
(516, 2, 'Elemento de Prevención Pública', 0, 4),
(517, 2, 'Estándar de Protección Primaria', 0, 3),
(518, 2, 'Equipo de Producción Preventiva', 0, 2),
(519, 2, 'Elemento de Protección Personal', 1, 1),
(520, 3, 'Evitar la necesidad de capacitación', 0, 4),
(521, 3, 'Reemplazar los procedimientos seguros', 0, 3),
(522, 3, 'Eliminar todos los peligros del proceso', 0, 2),
(523, 3, 'Reducir la exposición a riesgos residuales', 1, 1),
(524, 4, 'Prestárselo a otro trabajador', 0, 4),
(525, 4, 'Repararlo de manera improvisada', 0, 3),
(526, 4, 'Seguir utilizándolo hasta fin de turno', 0, 2),
(527, 4, 'Retirarlo de uso y solicitar reemplazo', 1, 1),
(528, 5, 'Una emergencia exclusivamente ambiental', 0, 4),
(529, 5, 'Una situación normal del trabajo', 0, 3),
(530, 5, 'Un accidente con licencia médica', 0, 2),
(531, 5, 'Un evento sin daño que pudo haber causado lesiones o pérdidas', 1, 1),
(532, 6, 'Esperar a que ocurra nuevamente', 0, 4),
(533, 6, 'Comentarlo sólo entre compañeros', 0, 3),
(534, 6, 'No reportarlo porque no hubo daño', 0, 2),
(535, 6, 'Reportarlo para analizarlo y prevenir recurrencias', 1, 1),
(536, 7, 'Evitar revisar el procedimiento', 0, 4),
(537, 7, 'Usar cualquier herramienta disponible', 0, 3),
(538, 7, 'Comenzar rápido para ahorrar tiempo', 0, 2),
(539, 7, 'Identificar peligros y verificar controles', 1, 1),
(540, 8, 'Delegar la tarea sin informar', 0, 4),
(541, 8, 'Trabajar más rápido', 0, 3),
(542, 8, 'Continuar hasta terminar', 0, 2),
(543, 8, 'Detener la tarea y comunicar la situación', 1, 1),
(544, 9, 'Usar el teléfono mientras se camina', 0, 4),
(545, 9, 'Dejar cables cruzando vías de tránsito', 0, 3),
(546, 9, 'Correr por las áreas de trabajo', 0, 2),
(547, 9, 'Mantener pasillos limpios, secos y despejados', 1, 1),
(548, 10, 'Ignorarlo si es poca cantidad', 0, 4),
(549, 10, 'Cubrirlo con papel y continuar', 0, 3),
(550, 10, 'Dejarlo hasta el aseo programado', 0, 2),
(551, 10, 'Señalizar, controlar el área y gestionar la limpieza', 1, 1),
(552, 11, 'Las oficinas administrativas', 0, 4),
(553, 11, 'Los estacionamientos de visitas', 0, 3),
(554, 11, 'Sólo la puerta principal', 0, 2),
(555, 11, 'Las rutas y salidas de emergencia', 1, 1),
(556, 12, 'Esperar hasta ver humo', 0, 4),
(557, 12, 'Usar siempre el ascensor', 0, 3),
(558, 12, 'Volver por objetos personales', 0, 2),
(559, 12, 'Seguir las instrucciones y dirigirse al punto de encuentro', 1, 1),
(560, 13, 'Para estacionar vehículos', 0, 4),
(561, 13, 'Para recibir herramientas', 0, 3),
(562, 13, 'Para guardar equipos', 0, 2),
(563, 13, 'Para verificar y contabilizar a las personas evacuadas', 1, 1),
(564, 14, 'Abrir todas las puertas y ventanas', 0, 4),
(565, 14, 'Intentar apagarlo siempre sin avisar', 0, 3),
(566, 14, 'Ocultarlo para evitar alarma', 0, 2),
(567, 14, 'Dar la alarma y actuar sólo si estás capacitado y es seguro', 1, 1),
(568, 15, 'Sin verificar el tipo de fuego', 0, 4),
(569, 15, 'Después de volver a entrar a un área evacuada', 0, 3),
(570, 15, 'En cualquier incendio, aunque haya humo intenso', 0, 2),
(571, 15, 'Cuando el fuego es incipiente, existe salida segura y la persona está capacitada', 1, 1),
(572, 16, 'Esperar a la próxima inspección', 0, 4),
(573, 16, 'Colocar más objetos cerca', 0, 3),
(574, 16, 'No hacer nada si existe otra salida', 0, 2),
(575, 16, 'Reportar y gestionar su liberación de inmediato', 1, 1),
(576, 17, 'Dejarla sola', 0, 4),
(577, 17, 'Darle medicamentos personales', 0, 3),
(578, 17, 'Moverla siempre de inmediato', 0, 2),
(579, 17, 'Verificar que la escena sea segura y pedir ayuda', 1, 1),
(580, 18, 'Cualquier visitante', 0, 4),
(581, 18, 'Sólo el jefe directo', 0, 3),
(582, 18, 'Cualquier persona sin formación', 0, 2),
(583, 18, 'Personal capacitado dentro de sus competencias', 1, 1),
(584, 19, 'Levantar rápido para terminar antes', 0, 4),
(585, 19, 'Girar el tronco mientras se levanta', 0, 3),
(586, 19, 'Doblar la espalda con piernas rectas', 0, 2),
(587, 19, 'Evaluar peso y ruta, acercar la carga al cuerpo y usar técnica adecuada', 1, 1),
(588, 20, 'Lanzarla para moverla', 0, 4),
(589, 20, 'Arrastrarla sin evaluar', 0, 3),
(590, 20, 'Levantarla igualmente', 0, 2),
(591, 20, 'Solicitar ayuda o usar asistencia mecánica', 1, 1),
(592, 21, 'Sentarse lejos del escritorio', 0, 4),
(593, 21, 'Trabajar con la pantalla muy baja', 0, 3),
(594, 21, 'Mantener la misma postura toda la jornada', 0, 2),
(595, 21, 'Ajustar el puesto, variar postura y realizar pausas', 1, 1),
(596, 22, 'De forma que obligue a girar el cuello', 0, 4),
(597, 22, 'En el suelo', 0, 3),
(598, 22, 'Detrás del usuario', 0, 2),
(599, 22, 'Frente al usuario, a una altura y distancia cómodas', 1, 1),
(600, 23, 'Mojarlo para enfriarlo', 0, 4),
(601, 23, 'Seguir usándolo hasta que falle', 0, 3),
(602, 23, 'Abrirlo y repararlo', 0, 2),
(603, 23, 'Retirarlo de uso o desconectarlo si es seguro y reportarlo', 1, 1),
(604, 24, 'Sólo porque se ven mal', 0, 4),
(605, 24, 'Porque reducen la productividad', 0, 3),
(606, 24, 'Porque consumen más internet', 0, 2),
(607, 24, 'Porque pueden causar choque eléctrico o incendio', 1, 1),
(608, 25, 'Reemplazar herramientas', 0, 4),
(609, 25, 'Identificar al trabajador más antiguo', 0, 3),
(610, 25, 'Aumentar la velocidad del mantenimiento', 0, 2),
(611, 25, 'Evitar liberación inesperada de energía durante una intervención', 1, 1),
(612, 26, 'Un visitante', 0, 4),
(613, 26, 'Cualquier supervisor sin registro', 0, 3),
(614, 26, 'Cualquier compañero', 0, 2),
(615, 26, 'La persona autorizada que lo instaló o según procedimiento formal de excepción', 1, 1),
(616, 27, 'Que no tenga etiquetas', 0, 4),
(617, 27, 'Que sea la más alta disponible', 0, 3),
(618, 27, 'Sólo su color', 0, 2),
(619, 27, 'Su estado, apoyo, estabilidad y adecuación a la tarea', 1, 1),
(620, 28, 'Mantener una posición controlada', 0, 4),
(621, 28, 'Apoyarla sobre superficie estable', 0, 3),
(622, 28, 'Revisarla antes de usar', 0, 2),
(623, 28, 'Usar una escalera dañada o apoyada de forma inestable', 1, 1),
(624, 29, 'Confiar sólo en la experiencia', 0, 4),
(625, 29, 'Trabajar sin protección si son pocos minutos', 0, 3),
(626, 29, 'Elegir protección después de subir', 0, 2),
(627, 29, 'Evaluar el riesgo y aplicar los controles de protección contra caídas', 1, 1),
(628, 30, 'Prestársela a otro turno', 0, 4),
(629, 30, 'Ocultar el daño', 0, 3),
(630, 30, 'Seguir usándola con cuidado', 0, 2),
(631, 30, 'Retirarla de uso y reportarla', 1, 1),
(632, 31, 'Para reemplazar la capacitación', 0, 4),
(633, 31, 'Para aumentar el ruido', 0, 3),
(634, 31, 'Para decorar el equipo', 0, 2),
(635, 31, 'Para impedir o reducir el contacto con partes peligrosas', 1, 1),
(636, 32, 'Sí, si nadie está mirando', 0, 4),
(637, 32, 'Sí, durante turnos nocturnos', 0, 3),
(638, 32, 'Sí, si el operador tiene experiencia', 0, 2),
(639, 32, 'No, salvo intervención autorizada y controlada', 1, 1),
(640, 33, 'Probarlo con las manos', 0, 4),
(641, 33, 'Mezclarlo con agua', 0, 3),
(642, 33, 'Olerlo para identificarlo', 0, 2),
(643, 33, 'Revisar etiqueta, ficha de datos de seguridad y controles requeridos', 1, 1),
(644, 34, 'El plano de estacionamientos', 0, 4),
(645, 34, 'La liquidación de sueldo', 0, 3),
(646, 34, 'La lista de asistencia', 0, 2),
(647, 34, 'La ficha de datos de seguridad (SDS/HDS)', 1, 1),
(648, 35, 'Probarlo en pequeña cantidad', 0, 4),
(649, 35, 'Cambiarlo a una botella de bebida', 0, 3),
(650, 35, 'Usarlo igual', 0, 2),
(651, 35, 'No utilizarlo y reportarlo para identificación o disposición segura', 1, 1),
(652, 36, 'Para evitar usar iluminación', 0, 4),
(653, 36, 'Para reducir el peso de herramientas', 0, 3),
(654, 36, 'Sólo para mejorar la temperatura', 0, 2),
(655, 36, 'Porque ayuda a controlar contaminantes en el aire', 1, 1),
(656, 37, 'Aumentar el tiempo de exposición', 0, 4),
(657, 37, 'Quitarse la protección para escuchar', 0, 3),
(658, 37, 'Gritar para comunicarse', 0, 2),
(659, 37, 'Aplicar controles y usar protección auditiva cuando corresponda', 1, 1),
(660, 38, 'Que hayan sido recortados', 0, 4),
(661, 38, 'Que puedan compartirse sin higiene', 0, 3),
(662, 38, 'Que estén mojados', 0, 2),
(663, 38, 'Que estén en buen estado y se utilicen correctamente', 1, 1),
(664, 39, 'Cuando el trabajador la elija sin evaluación', 0, 4),
(665, 39, 'Sólo después de sentir síntomas', 0, 3),
(666, 39, 'Siempre en oficinas', 0, 2),
(667, 39, 'Cuando la evaluación de riesgos y el procedimiento lo exijan', 1, 1),
(668, 40, 'Sí, si se usa menos de una hora', 0, 4),
(669, 40, 'Sí, si es de color oscuro', 0, 3),
(670, 40, 'Sí, todas protegen igual', 0, 2),
(671, 40, 'No, debe seleccionarse según el peligro y la exposición', 1, 1),
(672, 41, 'Que todas las puertas estén cerradas', 0, 4),
(673, 41, 'Que haya material combustible cerca', 0, 3),
(674, 41, 'Sólo la hora de inicio', 0, 2),
(675, 41, 'Controles, autorización o permiso requerido y condiciones del área', 1, 1),
(676, 42, 'Un comedor lleno', 0, 4),
(677, 42, 'Todo lugar al aire libre', 0, 3),
(678, 42, 'Cualquier oficina pequeña', 0, 2),
(679, 42, 'Acceso limitado y posibles peligros que requieren controles específicos', 1, 1),
(680, 43, 'Sólo de noche', 0, 4),
(681, 43, 'Sí, si entran dos personas', 0, 3),
(682, 43, 'Sí, si el trabajo dura poco', 0, 2),
(683, 43, 'No', 1, 1),
(684, 44, 'No reportar fallas', 0, 4),
(685, 44, 'Aumentar velocidad si hay atraso', 0, 3),
(686, 44, 'Usar el teléfono mientras conduces', 0, 2),
(687, 44, 'Usar cinturón, respetar límites y evitar distracciones', 1, 1),
(688, 45, 'Usar el teléfono para mantenerse despierto', 0, 4),
(689, 45, 'Aumentar la velocidad', 0, 3),
(690, 45, 'Abrir una ventana y continuar indefinidamente', 0, 2),
(691, 45, 'Detenerse en un lugar seguro y aplicar el control de fatiga', 1, 1),
(692, 46, 'Pasar bajo cargas elevadas', 0, 4),
(693, 46, 'Acercarse por detrás del equipo', 0, 3),
(694, 46, 'Caminar por cualquier zona', 0, 2),
(695, 46, 'Usar rutas peatonales, mantener distancia y respetar señalización', 1, 1),
(696, 47, 'Sí, si parece estable', 0, 4),
(697, 47, 'Sí, con casco', 0, 3),
(698, 47, 'Sí, si se hace rápido', 0, 2),
(699, 47, 'No', 1, 1),
(700, 48, 'Permite guardar materiales en salidas', 0, 4),
(701, 48, 'Elimina la necesidad de inspecciones', 0, 3),
(702, 48, 'Sólo mejora la apariencia', 0, 2),
(703, 48, 'Reduce caídas, golpes, incendios y obstrucciones', 1, 1),
(704, 49, 'Sobre superficies inestables', 0, 4),
(705, 49, 'Delante de extintores', 0, 3),
(706, 49, 'En cualquier pasillo', 0, 2),
(707, 49, 'En lugares definidos y estables, sin bloquear accesos ni emergencias', 1, 1),
(708, 50, 'Prestarlo sin inspección', 0, 4),
(709, 50, 'Cortar correas incómodas', 0, 3),
(710, 50, 'Usarlo sin revisarlo', 0, 2),
(711, 50, 'Inspeccionarlo y verificar ajuste y compatibilidad', 1, 1),
(712, 51, 'Confiar en la experiencia', 0, 4),
(713, 51, 'Usar sólo señalización', 0, 3),
(714, 51, 'Usar primero EPP y después eliminar el peligro', 0, 2),
(715, 51, 'Priorizar eliminación, sustitución e ingeniería antes de depender sólo del EPP', 1, 1),
(716, 52, 'Capacitación', 0, 4),
(717, 52, 'Advertencia', 0, 3),
(718, 52, 'EPP', 0, 2),
(719, 52, 'Eliminación', 1, 1),
(720, 53, 'Permitir omitir capacitación', 0, 4),
(721, 53, 'Reemplazar toda supervisión', 0, 3),
(722, 53, 'Aumentar trámites', 0, 2),
(723, 53, 'Definir cómo ejecutar una actividad con sus controles preventivos', 1, 1),
(724, 54, 'Pedir otra herramienta', 0, 4),
(725, 54, 'Improvisar', 0, 3),
(726, 54, 'Realizarla mirando a otros', 0, 2),
(727, 54, 'No ejecutarla hasta contar con competencia o autorización requerida', 1, 1),
(728, 55, 'Aumentar la carga para probarlo', 0, 4),
(729, 55, 'Ocultar la falla', 0, 3),
(730, 55, 'Seguir utilizándolo', 0, 2),
(731, 55, 'Retirarlo o detenerlo según procedimiento y reportar la falla', 1, 1),
(732, 56, 'Para asignar vacaciones', 0, 4),
(733, 56, 'Para reemplazar procedimientos', 0, 3),
(734, 56, 'Sólo para registrar asistencia', 0, 2),
(735, 56, 'Para comunicar riesgos, cambios, controles y responsabilidades', 1, 1),
(736, 57, 'Cambiar de trabajador sin informar', 0, 4),
(737, 57, 'Eliminar señalización', 0, 3),
(738, 57, 'Continuar porque ya estaba autorizada', 0, 2),
(739, 57, 'Detener y reevaluar riesgos y controles', 1, 1),
(740, 58, 'Para permitir acceso general', 0, 4),
(741, 58, 'Para reemplazar siempre una protección física', 0, 3),
(742, 58, 'Para decorar el área', 0, 2),
(743, 58, 'Para advertir, restringir o guiar frente a un peligro', 1, 1),
(744, 59, 'Entrar por otro lado', 0, 4),
(745, 59, 'Mover la barrera', 0, 3),
(746, 59, 'Cruzar si no ves actividad', 0, 2),
(747, 59, 'Respetar la delimitación y solicitar autorización si corresponde', 1, 1),
(748, 60, 'Porque sólo sirve fuera del trabajo', 0, 4),
(749, 60, 'Porque siempre falla', 0, 3),
(750, 60, 'Porque es innecesario', 0, 2),
(751, 60, 'Porque protege a la persona pero no elimina el peligro en su origen', 1, 1),
(752, 61, 'Trabajar sin descanso', 0, 4),
(753, 61, 'Usar más ropa sin evaluación', 0, 3),
(754, 61, 'Evitar beber agua', 0, 2),
(755, 61, 'Hidratación, pausas y controles de exposición', 1, 1),
(756, 62, 'Mirar directamente al sol', 0, 4),
(757, 62, 'Trabajar sin protección para acostumbrarse', 0, 3),
(758, 62, 'Ignorar la radiación en días nublados', 0, 2),
(759, 62, 'Usar las medidas definidas como sombra, ropa adecuada y protector solar cuando corresponda', 1, 1),
(760, 63, 'Permanecer inmóvil mucho tiempo', 0, 4),
(761, 63, 'No informar entumecimiento', 0, 3),
(762, 63, 'Usar ropa húmeda', 0, 2),
(763, 63, 'Usar vestimenta apropiada y controlar el tiempo de exposición', 1, 1),
(764, 64, 'Sólo afecta a conductores', 0, 4),
(765, 64, 'Mejora la concentración', 0, 3),
(766, 64, 'Sólo afecta el ánimo', 0, 2),
(767, 64, 'Puede reducir atención, reacción y capacidad de decisión', 1, 1),
(768, 65, 'Automedicarse sin informar', 0, 4),
(769, 65, 'Esperar indefinidamente', 0, 3),
(770, 65, 'Ocultarlos', 0, 2),
(771, 65, 'Informarlos y seguir el canal de salud y seguridad definido', 1, 1),
(772, 66, 'Una enfermedad que no debe reportarse', 0, 4),
(773, 66, 'Sólo una lesión traumática inmediata', 0, 3),
(774, 66, 'Cualquier enfermedad fuera del horario laboral', 0, 2),
(775, 66, 'Una condición de salud asociada a exposiciones o factores del trabajo', 1, 1),
(776, 67, 'Sólo por estadística', 0, 4),
(777, 67, 'Para reducir vacaciones', 0, 3),
(778, 67, 'Para reemplazar medidas preventivas', 0, 2),
(779, 67, 'Para detectar tempranamente efectos relacionados con exposiciones laborales', 1, 1),
(780, 68, 'Evitar limpiar superficies', 0, 4),
(781, 68, 'Ocultar síntomas', 0, 3),
(782, 68, 'Compartir elementos personales', 0, 2),
(783, 68, 'Higiene de manos y medidas sanitarias definidas', 1, 1),
(784, 69, 'Arrojarlos al desagüe', 0, 4),
(785, 69, 'Dejarlos en pasillos', 0, 3),
(786, 69, 'Mezclarlos con basura común', 0, 2),
(787, 69, 'Gestionarlos en recipientes y circuitos definidos', 1, 1),
(788, 70, 'Ignorarlo', 0, 4),
(789, 70, 'Agregar otro producto sin información', 0, 3),
(790, 70, 'Limpiarlo con las manos', 0, 2),
(791, 70, 'Aislar, informar y activar el procedimiento de respuesta', 1, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `onboarding_question_option_translations`
--

CREATE TABLE `onboarding_question_option_translations` (
  `id_option` int(11) NOT NULL,
  `language_code` varchar(5) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL,
  `option_text` varchar(500) NOT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `date_create` datetime NOT NULL DEFAULT current_timestamp(),
  `last_update` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `onboarding_question_option_translations`
--

INSERT INTO `onboarding_question_option_translations` (`id_option`, `language_code`, `option_text`, `updated_by`, `date_create`, `last_update`) VALUES
(512, 'en', 'Ask another worker to take the risk', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(512, 'es', 'Pedir a otro trabajador que asuma el riesgo', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(512, 'fr', 'Demander à un autre travailleur d\'assumer le risque', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(512, 'pt', 'Pedir a outro trabalhador que assuma o risco', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(512, 'zh', '让另一名员工承担该风险', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(513, 'en', 'Wait until the end of the week', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(513, 'es', 'Esperar al final de la semana', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(513, 'fr', 'Attendre la fin de la semaine', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(513, 'pt', 'Esperar até o fim da semana', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(513, 'zh', '等到周末再处理', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(514, 'en', 'Ignore it until an accident occurs', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(514, 'es', 'Ignorarla mientras no ocurra un accidente', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(514, 'fr', 'L\'ignorer tant qu\'aucun accident ne se produit', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(514, 'pt', 'Ignorá-la enquanto não ocorrer um acidente', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(514, 'zh', '只要尚未发生事故就忽略', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(515, 'en', 'Report it and apply the defined controls', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(515, 'es', 'Reportarla y aplicar los controles definidos', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(515, 'fr', 'La signaler et appliquer les mesures de contrôle définies', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(515, 'pt', 'Relatá-la e aplicar os controles definidos', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(515, 'zh', '立即报告并执行规定的控制措施', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(516, 'en', 'Public Prevention Element', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(516, 'es', 'Elemento de Prevención Pública', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(516, 'fr', 'Élément de prévention publique', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(516, 'pt', 'Elemento de Prevenção Pública', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(516, 'zh', '公共预防用品', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(517, 'en', 'Primary Protection Standard', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(517, 'es', 'Estándar de Protección Primaria', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(517, 'fr', 'Norme de protection primaire', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(517, 'pt', 'Padrão de Proteção Primária', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(517, 'zh', '一级防护标准', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(518, 'en', 'Preventive Production Equipment', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(518, 'es', 'Equipo de Producción Preventiva', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(518, 'fr', 'Équipement de production préventive', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(518, 'pt', 'Equipamento de Produção Preventiva', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(518, 'zh', '预防性生产设备', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(519, 'en', 'Personal Protective Equipment', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(519, 'es', 'Elemento de Protección Personal', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(519, 'fr', 'Équipement de protection individuelle', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(519, 'pt', 'Equipamento de Proteção Individual', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(519, 'zh', '个人防护装备', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(520, 'en', 'Avoid the need for training', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(520, 'es', 'Evitar la necesidad de capacitación', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(520, 'fr', 'Éviter le besoin de formation', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(520, 'pt', 'Evitar a necessidade de treinamento', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(520, 'zh', '避免进行培训', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(521, 'en', 'Replace safe work procedures', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(521, 'es', 'Reemplazar los procedimientos seguros', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(521, 'fr', 'Remplacer les procédures de travail sûres', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(521, 'pt', 'Substituir os procedimentos seguros', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(521, 'zh', '替代安全作业程序', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(522, 'en', 'Eliminate all process hazards', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(522, 'es', 'Eliminar todos los peligros del proceso', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(522, 'fr', 'Éliminer tous les dangers du processus', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(522, 'pt', 'Eliminar todos os perigos do processo', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(522, 'zh', '消除流程中的所有危险', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(523, 'en', 'Reduce exposure to residual risks', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(523, 'es', 'Reducir la exposición a riesgos residuales', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(523, 'fr', 'Réduire l\'exposition aux risques résiduels', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(523, 'pt', 'Reduzir a exposição a riscos residuais', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(523, 'zh', '降低对剩余风险的暴露', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(524, 'en', 'Lend it to another worker', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(524, 'es', 'Prestárselo a otro trabajador', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(524, 'fr', 'Le prêter à un autre travailleur', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(524, 'pt', 'Emprestá-lo a outro trabalhador', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(524, 'zh', '借给其他员工使用', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(525, 'en', 'Repair it in an improvised way', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(525, 'es', 'Repararlo de manera improvisada', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(525, 'fr', 'Le réparer de manière improvisée', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(525, 'pt', 'Repará-lo de forma improvisada', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(525, 'zh', '临时自行修理后继续使用', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(526, 'en', 'Keep using it until the end of the shift', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(526, 'es', 'Seguir utilizándolo hasta fin de turno', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(526, 'fr', 'Continuer à l\'utiliser jusqu\'à la fin du poste', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(526, 'pt', 'Continuar usando até o fim do turno', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(526, 'zh', '继续使用到班次结束', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(527, 'en', 'Remove it from service and request a replacement', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(527, 'es', 'Retirarlo de uso y solicitar reemplazo', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(527, 'fr', 'Le retirer du service et demander son remplacement', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(527, 'pt', 'Retirá-lo de uso e solicitar substituição', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(527, 'zh', '停止使用并申请更换', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(528, 'en', 'An exclusively environmental emergency', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(528, 'es', 'Una emergencia exclusivamente ambiental', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(528, 'fr', 'Une urgence exclusivement environnementale', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(528, 'pt', 'Uma emergência exclusivamente ambiental', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(528, 'zh', '仅涉及环境的紧急事件', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(529, 'en', 'A normal work situation', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(529, 'es', 'Una situación normal del trabajo', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(529, 'fr', 'Une situation normale de travail', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(529, 'pt', 'Uma situação normal de trabalho', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(529, 'zh', '正常的工作情况', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(530, 'en', 'An accident resulting in medical leave', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(530, 'es', 'Un accidente con licencia médica', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(530, 'fr', 'Un accident entraînant un arrêt médical', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(530, 'pt', 'Um acidente com afastamento médico', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(530, 'zh', '导致病假的事故', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(531, 'en', 'An event with no harm that could have caused injury or loss', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(531, 'es', 'Un evento sin daño que pudo haber causado lesiones o pérdidas', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(531, 'fr', 'Un événement sans dommage qui aurait pu causer des blessures ou des pertes', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(531, 'pt', 'Um evento sem dano que poderia ter causado lesões ou perdas', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(531, 'zh', '未造成伤害，但本可能导致受伤或损失的事件', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(532, 'en', 'Wait for it to happen again', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(532, 'es', 'Esperar a que ocurra nuevamente', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(532, 'fr', 'Attendre qu\'il se reproduise', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(532, 'pt', 'Esperar que aconteça novamente', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(532, 'zh', '等它再次发生', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(533, 'en', 'Discuss it only with coworkers', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(533, 'es', 'Comentarlo sólo entre compañeros', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(533, 'fr', 'En parler uniquement entre collègues', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(533, 'pt', 'Comentá-lo apenas entre colegas', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(533, 'zh', '只在同事之间讨论', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(534, 'en', 'Do not report it because no harm occurred', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(534, 'es', 'No reportarlo porque no hubo daño', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(534, 'fr', 'Ne pas le signaler puisqu\'il n\'y a pas eu de dommage', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(534, 'pt', 'Não relatá-lo porque não houve dano', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(534, 'zh', '因为没有造成伤害所以不用报告', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(535, 'en', 'Report it so it can be analyzed and recurrence prevented', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(535, 'es', 'Reportarlo para analizarlo y prevenir recurrencias', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(535, 'fr', 'Le signaler afin de l\'analyser et de prévenir sa répétition', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(535, 'pt', 'Relatá-lo para análise e prevenção de recorrências', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(535, 'zh', '报告并分析，以防止再次发生', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(536, 'en', 'Avoid reviewing the procedure', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(536, 'es', 'Evitar revisar el procedimiento', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(536, 'fr', 'Éviter de consulter la procédure', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(536, 'pt', 'Evitar revisar o procedimento', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(536, 'zh', '避免查看作业程序', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(537, 'en', 'Use any available tool', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(537, 'es', 'Usar cualquier herramienta disponible', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(537, 'fr', 'Utiliser n\'importe quel outil disponible', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(537, 'pt', 'Usar qualquer ferramenta disponível', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(537, 'zh', '使用任何可用工具', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(538, 'en', 'Start quickly to save time', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(538, 'es', 'Comenzar rápido para ahorrar tiempo', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(538, 'fr', 'Commencer rapidement pour gagner du temps', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(538, 'pt', 'Começar rapidamente para economizar tempo', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(538, 'zh', '尽快开始以节省时间', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(539, 'en', 'Identify hazards and verify controls', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(539, 'es', 'Identificar peligros y verificar controles', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(539, 'fr', 'Identifier les dangers et vérifier les mesures de contrôle', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(539, 'pt', 'Identificar perigos e verificar controles', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(539, 'zh', '识别危险并确认控制措施', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(540, 'en', 'Delegate the task without reporting it', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(540, 'es', 'Delegar la tarea sin informar', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(540, 'fr', 'Déléguer la tâche sans informer', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(540, 'pt', 'Delegar a tarefa sem informar', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(540, 'zh', '不报告就把任务交给别人', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(541, 'en', 'Work faster', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(541, 'es', 'Trabajar más rápido', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(541, 'fr', 'Travailler plus vite', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(541, 'pt', 'Trabalhar mais rápido', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(541, 'zh', '加快工作速度', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(542, 'en', 'Continue until the task is finished', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(542, 'es', 'Continuar hasta terminar', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(542, 'fr', 'Continuer jusqu\'à la fin', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(542, 'pt', 'Continuar até terminar', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(542, 'zh', '继续工作直到完成', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(543, 'en', 'Stop the task and communicate the situation', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(543, 'es', 'Detener la tarea y comunicar la situación', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(543, 'fr', 'Arrêter la tâche et signaler la situation', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(543, 'pt', 'Interromper a tarefa e comunicar a situação', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(543, 'zh', '停止作业并报告情况', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(544, 'en', 'Use the phone while walking', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(544, 'es', 'Usar el teléfono mientras se camina', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(544, 'fr', 'Utiliser le téléphone en marchant', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(544, 'pt', 'Usar o telefone enquanto caminha', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(544, 'zh', '边走边使用手机', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(545, 'en', 'Leave cables across traffic routes', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(545, 'es', 'Dejar cables cruzando vías de tránsito', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(545, 'fr', 'Laisser des câbles traverser les voies de circulation', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(545, 'pt', 'Deixar cabos atravessando vias de circulação', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(545, 'zh', '让电缆横穿通道', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(546, 'en', 'Run through work areas', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(546, 'es', 'Correr por las áreas de trabajo', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(546, 'fr', 'Courir dans les zones de travail', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(546, 'pt', 'Correr pelas áreas de trabalho', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(546, 'zh', '在工作区域奔跑', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(547, 'en', 'Keep walkways clean, dry and clear', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(547, 'es', 'Mantener pasillos limpios, secos y despejados', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(547, 'fr', 'Maintenir les passages propres, secs et dégagés', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(547, 'pt', 'Manter corredores limpos, secos e desobstruídos', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(547, 'zh', '保持通道清洁、干燥且畅通', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(548, 'en', 'Ignore it if the amount is small', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(548, 'es', 'Ignorarlo si es poca cantidad', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(548, 'fr', 'L\'ignorer si la quantité est faible', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(548, 'pt', 'Ignorar se for pouca quantidade', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(548, 'zh', '如果量少就忽略', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(549, 'en', 'Cover it with paper and continue', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(549, 'es', 'Cubrirlo con papel y continuar', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(549, 'fr', 'Le couvrir de papier et continuer', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(549, 'pt', 'Cobrir com papel e continuar', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(549, 'zh', '用纸盖住后继续工作', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(550, 'en', 'Leave it until scheduled cleaning', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(550, 'es', 'Dejarlo hasta el aseo programado', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(550, 'fr', 'Le laisser jusqu\'au nettoyage programmé', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(550, 'pt', 'Deixar até a limpeza programada', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(550, 'zh', '留到计划清洁时再处理', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(551, 'en', 'Mark and control the area and arrange cleanup', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(551, 'es', 'Señalizar, controlar el área y gestionar la limpieza', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(551, 'fr', 'Signaler et sécuriser la zone puis organiser le nettoyage', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(551, 'pt', 'Sinalizar, controlar a área e providenciar a limpeza', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(551, 'zh', '设置警示、控制区域并安排清理', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(552, 'en', 'Administrative offices', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(552, 'es', 'Las oficinas administrativas', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(552, 'fr', 'Les bureaux administratifs', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(552, 'pt', 'Os escritórios administrativos', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(552, 'zh', '行政办公室', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(553, 'en', 'Visitor parking areas', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(553, 'es', 'Los estacionamientos de visitas', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(553, 'fr', 'Les places de stationnement visiteurs', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(553, 'pt', 'Os estacionamentos de visitantes', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(553, 'zh', '访客停车位', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(554, 'en', 'Only the main entrance', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(554, 'es', 'Sólo la puerta principal', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(554, 'fr', 'Uniquement la porte principale', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(554, 'pt', 'Somente a porta principal', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(554, 'zh', '只有主入口', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(555, 'en', 'Emergency routes and exits', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(555, 'es', 'Las rutas y salidas de emergencia', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(555, 'fr', 'Les voies et sorties de secours', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(555, 'pt', 'As rotas e saídas de emergência', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(555, 'zh', '紧急疏散路线和出口', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(556, 'en', 'Wait until you see smoke', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(556, 'es', 'Esperar hasta ver humo', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(556, 'fr', 'Attendre de voir de la fumée', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(556, 'pt', 'Esperar até ver fumaça', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(556, 'zh', '等看到烟雾再行动', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(557, 'en', 'Always use the elevator', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(557, 'es', 'Usar siempre el ascensor', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(557, 'fr', 'Toujours utiliser l\'ascenseur', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(557, 'pt', 'Usar sempre o elevador', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(557, 'zh', '始终使用电梯', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(558, 'en', 'Go back for personal belongings', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(558, 'es', 'Volver por objetos personales', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(558, 'fr', 'Retourner chercher des effets personnels', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(558, 'pt', 'Voltar para buscar objetos pessoais', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(558, 'zh', '返回去取个人物品', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(559, 'en', 'Follow instructions and go to the assembly point', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(559, 'es', 'Seguir las instrucciones y dirigirse al punto de encuentro', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(559, 'fr', 'Suivre les instructions et rejoindre le point de rassemblement', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(559, 'pt', 'Seguir as instruções e dirigir-se ao ponto de encontro', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(559, 'zh', '按照指示前往集合点', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(560, 'en', 'To park vehicles', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(560, 'es', 'Para estacionar vehículos', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(560, 'fr', 'À garer des véhicules', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(560, 'pt', 'Para estacionar veículos', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(560, 'zh', '停放车辆', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(561, 'en', 'To receive tools', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(561, 'es', 'Para recibir herramientas', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(561, 'fr', 'À recevoir des outils', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(561, 'pt', 'Para receber ferramentas', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(561, 'zh', '领取工具', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(562, 'en', 'To store equipment', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(562, 'es', 'Para guardar equipos', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(562, 'fr', 'À stocker du matériel', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(562, 'pt', 'Para guardar equipamentos', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(562, 'zh', '存放设备', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(563, 'en', 'To verify and account for evacuated people', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(563, 'es', 'Para verificar y contabilizar a las personas evacuadas', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(563, 'fr', 'À vérifier et comptabiliser les personnes évacuées', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(563, 'pt', 'Para verificar e contabilizar as pessoas evacuadas', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(563, 'zh', '核实并清点已疏散人员', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(564, 'en', 'Open all doors and windows', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(564, 'es', 'Abrir todas las puertas y ventanas', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(564, 'fr', 'Ouvrir toutes les portes et fenêtres', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(564, 'pt', 'Abrir todas as portas e janelas', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(564, 'zh', '打开所有门窗', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(565, 'en', 'Always try to extinguish it without notifying anyone', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(565, 'es', 'Intentar apagarlo siempre sin avisar', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(565, 'fr', 'Toujours essayer de l\'éteindre sans prévenir', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(565, 'pt', 'Tentar apagá-lo sempre sem avisar', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(565, 'zh', '无论如何都先灭火且不通知他人', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(566, 'en', 'Hide it to avoid causing alarm', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(566, 'es', 'Ocultarlo para evitar alarma', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(566, 'fr', 'Le cacher pour éviter l\'alarme', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(566, 'pt', 'Escondê-lo para evitar alarme', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(566, 'zh', '隐藏火情以免引起恐慌', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(567, 'en', 'Raise the alarm and act only if you are trained and it is safe', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(567, 'es', 'Dar la alarma y actuar sólo si estás capacitado y es seguro', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(567, 'fr', 'Donner l\'alarme et n\'intervenir que si vous êtes formé et que cela est sûr', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(567, 'pt', 'Acionar o alarme e agir apenas se estiver treinado e for seguro', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(567, 'zh', '发出警报，并且只有在受过培训且安全时才进行处置', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(568, 'en', 'Without checking the type of fire', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(568, 'es', 'Sin verificar el tipo de fuego', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(568, 'fr', 'Sans vérifier le type de feu', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(568, 'pt', 'Sem verificar o tipo de fogo', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(568, 'zh', '不确认火灾类型就使用', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(569, 'en', 'After re-entering an evacuated area', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(569, 'es', 'Después de volver a entrar a un área evacuada', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(569, 'fr', 'Après être retourné dans une zone évacuée', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(569, 'pt', 'Depois de retornar a uma área evacuada', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(569, 'zh', '重新进入已经疏散的区域后', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(570, 'en', 'In any fire, even with heavy smoke', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(570, 'es', 'En cualquier incendio, aunque haya humo intenso', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(570, 'fr', 'Lors de tout incendie, même en présence de fumée dense', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(570, 'pt', 'Em qualquer incêndio, mesmo com muita fumaça', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(570, 'zh', '任何火灾都可以，即使烟雾很大', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(571, 'en', 'When the fire is incipient, there is a safe exit and the person is trained', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(571, 'es', 'Cuando el fuego es incipiente, existe salida segura y la persona está capacitada', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(571, 'fr', 'Lorsque le feu débute, qu\'une sortie sûre est disponible et que la personne est formée', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(571, 'pt', 'Quando o fogo é inicial, há uma saída segura e a pessoa está treinada', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(571, 'zh', '火势处于初期、有安全退路且使用者受过培训时', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(572, 'en', 'Wait until the next inspection', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(572, 'es', 'Esperar a la próxima inspección', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(572, 'fr', 'Attendre la prochaine inspection', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(572, 'pt', 'Esperar a próxima inspeção', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(572, 'zh', '等下次检查再处理', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(573, 'en', 'Place more objects nearby', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(573, 'es', 'Colocar más objetos cerca', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(573, 'fr', 'Placer davantage d\'objets à proximité', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(573, 'pt', 'Colocar mais objetos próximos', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(573, 'zh', '在附近再放更多物品', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(574, 'en', 'Do nothing if another exit exists', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(574, 'es', 'No hacer nada si existe otra salida', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(574, 'fr', 'Ne rien faire s\'il existe une autre sortie', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(574, 'pt', 'Não fazer nada se houver outra saída', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(574, 'zh', '如果还有其他出口就不用处理', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(575, 'en', 'Report it and arrange for it to be cleared immediately', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(575, 'es', 'Reportar y gestionar su liberación de inmediato', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(575, 'fr', 'La signaler et faire en sorte qu\'elle soit dégagée immédiatement', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(575, 'pt', 'Relatar e providenciar sua liberação imediatamente', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(575, 'zh', '立即报告并安排清除障碍', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(576, 'en', 'Leave the person alone', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(576, 'es', 'Dejarla sola', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(576, 'fr', 'La laisser seule', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(576, 'pt', 'Deixá-la sozinha', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(576, 'zh', '把伤者单独留下', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(577, 'en', 'Give the person your own medication', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(577, 'es', 'Darle medicamentos personales', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(577, 'fr', 'Lui donner vos propres médicaments', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(577, 'pt', 'Dar medicamentos pessoais', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(577, 'zh', '给伤者服用自己的药物', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(578, 'en', 'Always move the person immediately', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(578, 'es', 'Moverla siempre de inmediato', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(578, 'fr', 'Toujours la déplacer immédiatement', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(578, 'pt', 'Sempre movê-la imediatamente', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(578, 'zh', '总是立即移动伤者', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(579, 'en', 'Check that the scene is safe and call for help', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(579, 'es', 'Verificar que la escena sea segura y pedir ayuda', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(579, 'fr', 'Vérifier que la zone est sûre et demander de l\'aide', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(579, 'pt', 'Verificar se o local é seguro e pedir ajuda', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(579, 'zh', '先确认现场安全并请求帮助', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(580, 'en', 'Any visitor', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(580, 'es', 'Cualquier visitante', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(580, 'fr', 'N\'importe quel visiteur', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(580, 'pt', 'Qualquer visitante', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(580, 'zh', '任何访客', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(581, 'en', 'Only the direct supervisor', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(581, 'es', 'Sólo el jefe directo', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(581, 'fr', 'Uniquement le supérieur direct', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(581, 'pt', 'Somente o chefe direto', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(581, 'zh', '只有直属主管', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(582, 'en', 'Anyone without training', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(582, 'es', 'Cualquier persona sin formación', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(582, 'fr', 'Toute personne sans formation', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(582, 'pt', 'Qualquer pessoa sem treinamento', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(582, 'zh', '任何未经培训的人', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(583, 'en', 'Trained personnel acting within their competence', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(583, 'es', 'Personal capacitado dentro de sus competencias', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(583, 'fr', 'Du personnel formé agissant dans le cadre de ses compétences', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(583, 'pt', 'Pessoal treinado dentro de suas competências', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(583, 'zh', '在其能力范围内开展工作的受训人员', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(584, 'en', 'Lift quickly to finish sooner', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(584, 'es', 'Levantar rápido para terminar antes', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(584, 'fr', 'Soulever rapidement pour finir plus tôt', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(584, 'pt', 'Levantar rapidamente para terminar antes', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(584, 'zh', '快速抬起以尽快结束', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(585, 'en', 'Twist the torso while lifting', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(585, 'es', 'Girar el tronco mientras se levanta', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(585, 'fr', 'Tourner le tronc pendant le levage', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(585, 'pt', 'Girar o tronco enquanto levanta', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(585, 'zh', '搬起时扭转躯干', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(586, 'en', 'Bend the back with straight legs', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(586, 'es', 'Doblar la espalda con piernas rectas', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(586, 'fr', 'Plier le dos en gardant les jambes droites', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(586, 'pt', 'Dobrar as costas com as pernas retas', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(586, 'zh', '双腿伸直、弯腰搬起', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(587, 'en', 'Assess the weight and route, keep the load close to the body and use proper technique', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(587, 'es', 'Evaluar peso y ruta, acercar la carga al cuerpo y usar técnica adecuada', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(587, 'fr', 'Évaluer le poids et le trajet, garder la charge près du corps et utiliser une technique adaptée', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(587, 'pt', 'Avaliar o peso e a rota, manter a carga próxima ao corpo e usar técnica adequada', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(587, 'zh', '评估重量和路线，将物体靠近身体并采用正确技术', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(588, 'en', 'Throw it to move it', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(588, 'es', 'Lanzarla para moverla', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(588, 'fr', 'La lancer pour la déplacer', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(588, 'pt', 'Lançá-la para movê-la', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(588, 'zh', '通过抛掷来移动', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(589, 'en', 'Drag it without assessing the risk', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(589, 'es', 'Arrastrarla sin evaluar', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(589, 'fr', 'La traîner sans évaluation', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(589, 'pt', 'Arrastá-la sem avaliar', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(589, 'zh', '不评估就直接拖动', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(590, 'en', 'Lift it anyway', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(590, 'es', 'Levantarla igualmente', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(590, 'fr', 'La soulever quand même', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(590, 'pt', 'Levantá-la mesmo assim', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(590, 'zh', '仍然独自搬起', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(591, 'en', 'Ask for help or use mechanical assistance', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(591, 'es', 'Solicitar ayuda o usar asistencia mecánica', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(591, 'fr', 'Demander de l\'aide ou utiliser une assistance mécanique', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(591, 'pt', 'Solicitar ajuda ou usar assistência mecânica', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(591, 'zh', '寻求帮助或使用机械辅助设备', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(592, 'en', 'Sit far away from the desk', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(592, 'es', 'Sentarse lejos del escritorio', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(592, 'fr', 'S\'asseoir loin du bureau', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(592, 'pt', 'Sentar longe da mesa', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(592, 'zh', '坐得离桌子很远', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(593, 'en', 'Work with the screen very low', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(593, 'es', 'Trabajar con la pantalla muy baja', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(593, 'fr', 'Travailler avec l\'écran très bas', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(593, 'pt', 'Trabalhar com a tela muito baixa', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(593, 'zh', '把屏幕放得很低', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(594, 'en', 'Keep the same posture all day', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(594, 'es', 'Mantener la misma postura toda la jornada', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(594, 'fr', 'Garder la même posture toute la journée', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(594, 'pt', 'Manter a mesma postura durante toda a jornada', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(594, 'zh', '整天保持同一姿势', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(595, 'en', 'Adjust the workstation, vary posture and take breaks', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(595, 'es', 'Ajustar el puesto, variar postura y realizar pausas', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(595, 'fr', 'Régler le poste, varier la posture et faire des pauses', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(595, 'pt', 'Ajustar o posto, variar a postura e fazer pausas', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(595, 'zh', '调整工作站、变换姿势并适当休息', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(596, 'en', 'In a position that forces the neck to turn', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(596, 'es', 'De forma que obligue a girar el cuello', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(596, 'fr', 'De façon à obliger à tourner le cou', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(596, 'pt', 'De forma que obrigue a girar o pescoço', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(596, 'zh', '放在迫使颈部扭转的位置', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(597, 'en', 'On the floor', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(597, 'es', 'En el suelo', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(597, 'fr', 'Au sol', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(597, 'pt', 'No chão', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(597, 'zh', '放在地面上', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(598, 'en', 'Behind the user', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(598, 'es', 'Detrás del usuario', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(598, 'fr', 'Derrière l\'utilisateur', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(598, 'pt', 'Atrás do usuário', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(598, 'zh', '放在使用者身后', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(599, 'en', 'In front of the user at a comfortable height and distance', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(599, 'es', 'Frente al usuario, a una altura y distancia cómodas', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(599, 'fr', 'Face à l\'utilisateur, à une hauteur et une distance confortables', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(599, 'pt', 'À frente do usuário, em altura e distância confortáveis', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(599, 'zh', '位于使用者正前方，保持舒适的高度和距离', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(600, 'en', 'Wet it to cool it down', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(600, 'es', 'Mojarlo para enfriarlo', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(600, 'fr', 'Le mouiller pour le refroidir', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(600, 'pt', 'Molhá-lo para resfriar', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(600, 'zh', '用水冷却设备', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(601, 'en', 'Keep using it until it fails', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(601, 'es', 'Seguir usándolo hasta que falle', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37');
INSERT INTO `onboarding_question_option_translations` (`id_option`, `language_code`, `option_text`, `updated_by`, `date_create`, `last_update`) VALUES
(601, 'fr', 'Continuer à l\'utiliser jusqu\'à la panne', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(601, 'pt', 'Continuar usando até falhar', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(601, 'zh', '继续使用直到完全损坏', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(602, 'en', 'Open it and repair it', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(602, 'es', 'Abrirlo y repararlo', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(602, 'fr', 'L\'ouvrir et le réparer', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(602, 'pt', 'Abrir e reparar', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(602, 'zh', '自行拆开维修', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(603, 'en', 'Remove it from service or disconnect it if safe and report it', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(603, 'es', 'Retirarlo de uso o desconectarlo si es seguro y reportarlo', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(603, 'fr', 'Le retirer du service ou le débrancher si cela est sûr, puis le signaler', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(603, 'pt', 'Retirá-lo de uso ou desconectá-lo se for seguro e relatá-lo', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(603, 'zh', '停止使用，若安全则断开电源，并进行报告', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(604, 'en', 'Only because they look bad', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(604, 'es', 'Sólo porque se ven mal', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(604, 'fr', 'Uniquement parce qu\'elles sont inesthétiques', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(604, 'pt', 'Apenas porque têm aparência ruim', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(604, 'zh', '只是因为外观不好', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(605, 'en', 'Because they reduce productivity', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(605, 'es', 'Porque reducen la productividad', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(605, 'fr', 'Parce qu\'elles réduisent la productivité', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(605, 'pt', 'Porque reduzem a produtividade', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(605, 'zh', '因为会降低生产率', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(606, 'en', 'Because they use more internet', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(606, 'es', 'Porque consumen más internet', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(606, 'fr', 'Parce qu\'elles consomment davantage d\'internet', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(606, 'pt', 'Porque consomem mais internet', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(606, 'zh', '因为会消耗更多网络流量', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(607, 'en', 'Because they can cause electric shock or fire', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(607, 'es', 'Porque pueden causar choque eléctrico o incendio', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(607, 'fr', 'Parce qu\'elles peuvent provoquer un choc électrique ou un incendie', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(607, 'pt', 'Porque podem causar choque elétrico ou incêndio', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(607, 'zh', '因为可能造成触电或火灾', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(608, 'en', 'Replace tools', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(608, 'es', 'Reemplazar herramientas', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(608, 'fr', 'À remplacer les outils', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(608, 'pt', 'Substituir ferramentas', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(608, 'zh', '替代工具', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(609, 'en', 'Identify the most senior worker', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(609, 'es', 'Identificar al trabajador más antiguo', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(609, 'fr', 'À identifier le travailleur le plus ancien', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(609, 'pt', 'Identificar o trabalhador mais antigo', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(609, 'zh', '识别工龄最长的员工', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(610, 'en', 'Increase maintenance speed', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(610, 'es', 'Aumentar la velocidad del mantenimiento', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(610, 'fr', 'À augmenter la vitesse de maintenance', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(610, 'pt', 'Aumentar a velocidade da manutenção', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(610, 'zh', '提高维修速度', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(611, 'en', 'Prevent unexpected release of energy during an intervention', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(611, 'es', 'Evitar liberación inesperada de energía durante una intervención', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(611, 'fr', 'À empêcher une libération imprévue d\'énergie pendant une intervention', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(611, 'pt', 'Evitar a liberação inesperada de energia durante uma intervenção', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(611, 'zh', '防止检修期间能源意外释放', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(612, 'en', 'A visitor', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(612, 'es', 'Un visitante', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(612, 'fr', 'Un visiteur', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(612, 'pt', 'Um visitante', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(612, 'zh', '访客', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(613, 'en', 'Any supervisor without a record', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(613, 'es', 'Cualquier supervisor sin registro', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(613, 'fr', 'N\'importe quel superviseur sans enregistrement', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(613, 'pt', 'Qualquer supervisor sem registro', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(613, 'zh', '没有记录的任何主管', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(614, 'en', 'Any coworker', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(614, 'es', 'Cualquier compañero', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(614, 'fr', 'N\'importe quel collègue', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(614, 'pt', 'Qualquer colega', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(614, 'zh', '任何同事', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(615, 'en', 'The authorized person who installed it, or as defined by a formal exception procedure', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(615, 'es', 'La persona autorizada que lo instaló o según procedimiento formal de excepción', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(615, 'fr', 'La personne autorisée qui l\'a posé, ou selon une procédure formelle d\'exception', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(615, 'pt', 'A pessoa autorizada que o instalou ou conforme procedimento formal de exceção', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(615, 'zh', '安装该锁的授权人员，或按照正式例外程序处理', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(616, 'en', 'That it has no labels', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(616, 'es', 'Que no tenga etiquetas', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(616, 'fr', 'Qu\'elle ne porte aucune étiquette', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(616, 'pt', 'Que não tenha etiquetas', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(616, 'zh', '确认没有任何标签', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(617, 'en', 'That it is the tallest one available', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(617, 'es', 'Que sea la más alta disponible', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(617, 'fr', 'Qu\'elle soit la plus haute disponible', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(617, 'pt', 'Que seja a mais alta disponível', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(617, 'zh', '确认它是现有梯子中最高的', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(618, 'en', 'Only its color', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(618, 'es', 'Sólo su color', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(618, 'fr', 'Uniquement sa couleur', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(618, 'pt', 'Somente sua cor', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(618, 'zh', '只检查颜色', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(619, 'en', 'Its condition, support, stability and suitability for the task', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(619, 'es', 'Su estado, apoyo, estabilidad y adecuación a la tarea', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(619, 'fr', 'Son état, son appui, sa stabilité et son adéquation à la tâche', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(619, 'pt', 'Seu estado, apoio, estabilidade e adequação à tarefa', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(619, 'zh', '梯子的状况、支撑、稳定性以及是否适合该任务', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(620, 'en', 'Maintaining a controlled position', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(620, 'es', 'Mantener una posición controlada', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(620, 'fr', 'Maintenir une position contrôlée', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(620, 'pt', 'Manter uma posição controlada', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(620, 'zh', '保持受控姿势', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(621, 'en', 'Placing it on a stable surface', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(621, 'es', 'Apoyarla sobre superficie estable', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(621, 'fr', 'La placer sur une surface stable', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(621, 'pt', 'Apoiá-la sobre uma superfície estável', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(621, 'zh', '将梯子放在稳定表面上', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(622, 'en', 'Inspecting it before use', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(622, 'es', 'Revisarla antes de usar', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(622, 'fr', 'L\'inspecter avant utilisation', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(622, 'pt', 'Inspecioná-la antes do uso', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(622, 'zh', '使用前检查梯子', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(623, 'en', 'Using a damaged ladder or one positioned unstably', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(623, 'es', 'Usar una escalera dañada o apoyada de forma inestable', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(623, 'fr', 'Utiliser une échelle endommagée ou installée de manière instable', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(623, 'pt', 'Usar uma escada danificada ou apoiada de forma instável', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(623, 'zh', '使用损坏或支撑不稳的梯子', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(624, 'en', 'Rely only on experience', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(624, 'es', 'Confiar sólo en la experiencia', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(624, 'fr', 'Se fier uniquement à l\'expérience', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(624, 'pt', 'Confiar apenas na experiência', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(624, 'zh', '只依靠经验', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(625, 'en', 'Work without protection if it will only take a few minutes', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(625, 'es', 'Trabajar sin protección si son pocos minutos', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(625, 'fr', 'Travailler sans protection si cela ne dure que quelques minutes', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(625, 'pt', 'Trabalhar sem proteção se durar poucos minutos', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(625, 'zh', '如果只工作几分钟就不使用防护', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(626, 'en', 'Choose protection after climbing up', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(626, 'es', 'Elegir protección después de subir', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(626, 'fr', 'Choisir la protection après être monté', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(626, 'pt', 'Escolher a proteção depois de subir', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(626, 'zh', '上去之后再选择防护措施', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(627, 'en', 'Assess the risk and apply fall-protection controls', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(627, 'es', 'Evaluar el riesgo y aplicar los controles de protección contra caídas', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(627, 'fr', 'Évaluer le risque et appliquer les mesures de protection contre les chutes', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(627, 'pt', 'Avaliar o risco e aplicar controles de proteção contra quedas', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(627, 'zh', '评估风险并采取防坠落控制措施', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(628, 'en', 'Lend it to another shift', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(628, 'es', 'Prestársela a otro turno', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(628, 'fr', 'Le prêter à une autre équipe', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(628, 'pt', 'Emprestá-la a outro turno', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(628, 'zh', '借给下一班使用', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(629, 'en', 'Hide the damage', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(629, 'es', 'Ocultar el daño', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(629, 'fr', 'Cacher le défaut', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(629, 'pt', 'Esconder o dano', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(629, 'zh', '隐藏损坏情况', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(630, 'en', 'Keep using it carefully', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(630, 'es', 'Seguir usándola con cuidado', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(630, 'fr', 'Continuer à l\'utiliser prudemment', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(630, 'pt', 'Continuar usando com cuidado', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(630, 'zh', '小心使用即可', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(631, 'en', 'Remove it from service and report it', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(631, 'es', 'Retirarla de uso y reportarla', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(631, 'fr', 'Le retirer du service et le signaler', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(631, 'pt', 'Retirá-la de uso e relatá-la', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(631, 'zh', '停止使用并报告', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(632, 'en', 'Replace training', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(632, 'es', 'Para reemplazar la capacitación', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(632, 'fr', 'À remplacer la formation', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(632, 'pt', 'Substituir o treinamento', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(632, 'zh', '替代培训', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(633, 'en', 'Increase noise', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(633, 'es', 'Para aumentar el ruido', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(633, 'fr', 'À augmenter le bruit', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(633, 'pt', 'Aumentar o ruído', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(633, 'zh', '增加噪声', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(634, 'en', 'Decorate the equipment', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(634, 'es', 'Para decorar el equipo', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(634, 'fr', 'À décorer l\'équipement', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(634, 'pt', 'Decorar o equipamento', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(634, 'zh', '装饰设备', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(635, 'en', 'Prevent or reduce contact with hazardous parts', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(635, 'es', 'Para impedir o reducir el contacto con partes peligrosas', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(635, 'fr', 'À empêcher ou réduire le contact avec les parties dangereuses', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(635, 'pt', 'Impedir ou reduzir o contato com partes perigosas', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(635, 'zh', '防止或减少与危险部件接触', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(636, 'en', 'Yes, if nobody is watching', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(636, 'es', 'Sí, si nadie está mirando', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(636, 'fr', 'Oui, si personne ne regarde', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(636, 'pt', 'Sim, se ninguém estiver olhando', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(636, 'zh', '可以，只要没人看到', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(637, 'en', 'Yes, during night shifts', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(637, 'es', 'Sí, durante turnos nocturnos', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(637, 'fr', 'Oui, pendant les postes de nuit', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(637, 'pt', 'Sim, durante turnos noturnos', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(637, 'zh', '可以，在夜班期间', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(638, 'en', 'Yes, if the operator is experienced', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(638, 'es', 'Sí, si el operador tiene experiencia', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(638, 'fr', 'Oui, si l\'opérateur est expérimenté', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(638, 'pt', 'Sim, se o operador tiver experiência', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(638, 'zh', '可以，只要操作员有经验', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(639, 'en', 'No, except during an authorized and controlled intervention', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(639, 'es', 'No, salvo intervención autorizada y controlada', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(639, 'fr', 'Non, sauf lors d\'une intervention autorisée et maîtrisée', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(639, 'pt', 'Não, exceto em intervenção autorizada e controlada', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(639, 'zh', '不可以，除非是经授权并受控的检修作业', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(640, 'en', 'Test it with your hands', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(640, 'es', 'Probarlo con las manos', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(640, 'fr', 'Le tester avec les mains', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(640, 'pt', 'Testá-lo com as mãos', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(640, 'zh', '用手试一下', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(641, 'en', 'Mix it with water', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(641, 'es', 'Mezclarlo con agua', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(641, 'fr', 'Le mélanger avec de l\'eau', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(641, 'pt', 'Misturá-lo com água', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(641, 'zh', '与水混合', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(642, 'en', 'Smell it to identify it', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(642, 'es', 'Olerlo para identificarlo', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(642, 'fr', 'Le sentir pour l\'identifier', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(642, 'pt', 'Cheirá-lo para identificar', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(642, 'zh', '通过闻气味来识别', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(643, 'en', 'Review the label, safety data sheet and required controls', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(643, 'es', 'Revisar etiqueta, ficha de datos de seguridad y controles requeridos', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(643, 'fr', 'Consulter l\'étiquette, la fiche de données de sécurité et les mesures de contrôle requises', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(643, 'pt', 'Revisar o rótulo, a ficha de dados de segurança e os controles exigidos', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(643, 'zh', '查看标签、安全数据表以及所需控制措施', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(644, 'en', 'The parking plan', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(644, 'es', 'El plano de estacionamientos', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(644, 'fr', 'Le plan de stationnement', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(644, 'pt', 'O plano de estacionamento', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(644, 'zh', '停车场平面图', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(645, 'en', 'The payslip', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(645, 'es', 'La liquidación de sueldo', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(645, 'fr', 'Le bulletin de salaire', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(645, 'pt', 'O contracheque', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(645, 'zh', '工资单', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(646, 'en', 'The attendance list', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(646, 'es', 'La lista de asistencia', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(646, 'fr', 'La feuille de présence', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(646, 'pt', 'A lista de presença', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(646, 'zh', '考勤表', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(647, 'en', 'The Safety Data Sheet (SDS)', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(647, 'es', 'La ficha de datos de seguridad (SDS/HDS)', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(647, 'fr', 'La fiche de données de sécurité (FDS/SDS)', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(647, 'pt', 'A Ficha de Dados de Segurança (FDS/SDS)', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(647, 'zh', '安全数据表（SDS）', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(648, 'en', 'Test a small amount', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(648, 'es', 'Probarlo en pequeña cantidad', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(648, 'fr', 'En tester une petite quantité', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(648, 'pt', 'Testar uma pequena quantidade', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(648, 'zh', '先试用少量', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(649, 'en', 'Transfer it to a beverage bottle', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(649, 'es', 'Cambiarlo a una botella de bebida', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(649, 'fr', 'Le transvaser dans une bouteille de boisson', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(649, 'pt', 'Transferi-la para uma garrafa de bebida', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(649, 'zh', '倒入饮料瓶中', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(650, 'en', 'Use it anyway', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(650, 'es', 'Usarlo igual', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(650, 'fr', 'L\'utiliser quand même', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(650, 'pt', 'Usá-la mesmo assim', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(650, 'zh', '照常使用', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(651, 'en', 'Do not use it and report it for identification or safe disposal', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(651, 'es', 'No utilizarlo y reportarlo para identificación o disposición segura', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(651, 'fr', 'Ne pas l\'utiliser et le signaler pour identification ou élimination sûre', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(651, 'pt', 'Não utilizá-la e relatá-la para identificação ou descarte seguro', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(651, 'zh', '不要使用，并报告以便确认身份或安全处置', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(652, 'en', 'To avoid using lighting', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(652, 'es', 'Para evitar usar iluminación', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(652, 'fr', 'Pour éviter d\'utiliser l\'éclairage', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(652, 'pt', 'Para evitar usar iluminação', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(652, 'zh', '为了避免使用照明', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(653, 'en', 'To reduce the weight of tools', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(653, 'es', 'Para reducir el peso de herramientas', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(653, 'fr', 'Pour réduire le poids des outils', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(653, 'pt', 'Para reduzir o peso das ferramentas', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(653, 'zh', '为了减轻工具重量', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(654, 'en', 'Only to improve temperature', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(654, 'es', 'Sólo para mejorar la temperatura', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(654, 'fr', 'Uniquement pour améliorer la température', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(654, 'pt', 'Somente para melhorar a temperatura', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(654, 'zh', '只是为了改善温度', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(655, 'en', 'Because it helps control airborne contaminants', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(655, 'es', 'Porque ayuda a controlar contaminantes en el aire', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(655, 'fr', 'Parce qu\'elle aide à maîtriser les contaminants dans l\'air', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(655, 'pt', 'Porque ajuda a controlar contaminantes no ar', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(655, 'zh', '因为通风有助于控制空气中的污染物', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(656, 'en', 'Increase exposure time', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(656, 'es', 'Aumentar el tiempo de exposición', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(656, 'fr', 'Augmenter le temps d\'exposition', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(656, 'pt', 'Aumentar o tempo de exposição', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(656, 'zh', '延长暴露时间', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(657, 'en', 'Remove hearing protection to hear better', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(657, 'es', 'Quitarse la protección para escuchar', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(657, 'fr', 'Retirer la protection pour mieux entendre', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(657, 'pt', 'Retirar a proteção para ouvir', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(657, 'zh', '摘掉听力防护用品以便听清', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(658, 'en', 'Shout to communicate', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(658, 'es', 'Gritar para comunicarse', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(658, 'fr', 'Crier pour communiquer', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(658, 'pt', 'Gritar para se comunicar', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(658, 'zh', '通过大声喊叫来交流', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(659, 'en', 'Apply controls and use hearing protection when required', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(659, 'es', 'Aplicar controles y usar protección auditiva cuando corresponda', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(659, 'fr', 'Appliquer les mesures de contrôle et porter une protection auditive lorsque nécessaire', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(659, 'pt', 'Aplicar controles e usar proteção auditiva quando necessário', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(659, 'zh', '采取控制措施，并在需要时使用听力防护用品', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(660, 'en', 'That they have been trimmed', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(660, 'es', 'Que hayan sido recortados', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(660, 'fr', 'Qu\'elles ont été découpées', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(660, 'pt', 'Que tenham sido recortados', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(660, 'zh', '确认已经被剪裁', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(661, 'en', 'That they can be shared without hygiene measures', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(661, 'es', 'Que puedan compartirse sin higiene', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(661, 'fr', 'Qu\'elles peuvent être partagées sans hygiène', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(661, 'pt', 'Que possam ser compartilhados sem higiene', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(661, 'zh', '确认可以不经清洁直接共用', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(662, 'en', 'That they are wet', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(662, 'es', 'Que estén mojados', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(662, 'fr', 'Qu\'elles sont mouillées', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(662, 'pt', 'Que estejam molhados', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(662, 'zh', '确认它们是湿的', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(663, 'en', 'That they are in good condition and used correctly', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(663, 'es', 'Que estén en buen estado y se utilicen correctamente', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(663, 'fr', 'Qu\'elles sont en bon état et correctement utilisées', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(663, 'pt', 'Que estejam em bom estado e sejam usados corretamente', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(663, 'zh', '确认其状态良好并正确佩戴', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(664, 'en', 'Whenever the worker chooses it without an assessment', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(664, 'es', 'Cuando el trabajador la elija sin evaluación', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(664, 'fr', 'Lorsque le travailleur la choisit sans évaluation', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(664, 'pt', 'Quando o trabalhador escolher sem avaliação', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(664, 'zh', '员工未经评估自行决定使用时', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(665, 'en', 'Only after symptoms appear', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(665, 'es', 'Sólo después de sentir síntomas', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(665, 'fr', 'Seulement après l\'apparition de symptômes', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(665, 'pt', 'Somente depois de sentir sintomas', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(665, 'zh', '只有出现症状以后才使用', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(666, 'en', 'Always in offices', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(666, 'es', 'Siempre en oficinas', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(666, 'fr', 'Toujours dans les bureaux', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(666, 'pt', 'Sempre em escritórios', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(666, 'zh', '在办公室始终使用', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(667, 'en', 'When required by the risk assessment and procedure', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(667, 'es', 'Cuando la evaluación de riesgos y el procedimiento lo exijan', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(667, 'fr', 'Lorsque l\'évaluation des risques et la procédure l\'exigent', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(667, 'pt', 'Quando a avaliação de riscos e o procedimento exigirem', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(667, 'zh', '风险评估和作业程序要求使用时', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(668, 'en', 'Yes, if used for less than one hour', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(668, 'es', 'Sí, si se usa menos de una hora', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(668, 'fr', 'Oui, s\'il est utilisé moins d\'une heure', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(668, 'pt', 'Sim, se usada por menos de uma hora', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(668, 'zh', '可以，只要使用时间少于一小时', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(669, 'en', 'Yes, if it is dark-colored', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(669, 'es', 'Sí, si es de color oscuro', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(669, 'fr', 'Oui, s\'il est de couleur foncée', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(669, 'pt', 'Sim, se for de cor escura', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(669, 'zh', '可以，只要颜色较深', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(670, 'en', 'Yes, they all provide the same protection', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(670, 'es', 'Sí, todas protegen igual', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(670, 'fr', 'Oui, tous offrent la même protection', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(670, 'pt', 'Sim, todas protegem igual', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(670, 'zh', '可以，所有口罩的防护效果都一样', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(671, 'en', 'No, it must be selected according to the hazard and exposure', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(671, 'es', 'No, debe seleccionarse según el peligro y la exposición', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(671, 'fr', 'Non, il doit être choisi en fonction du danger et de l\'exposition', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(671, 'pt', 'Não, deve ser selecionada de acordo com o perigo e a exposição', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(671, 'zh', '不能，必须根据危险和暴露情况选择合适的防护用品', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(672, 'en', 'That all doors are closed', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(672, 'es', 'Que todas las puertas estén cerradas', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(672, 'fr', 'Que toutes les portes soient fermées', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(672, 'pt', 'Que todas as portas estejam fechadas', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(672, 'zh', '确认所有门都关闭', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(673, 'en', 'That combustible material is nearby', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(673, 'es', 'Que haya material combustible cerca', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(673, 'fr', 'Qu\'il y ait des matières combustibles à proximité', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(673, 'pt', 'Que haja material combustível por perto', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(673, 'zh', '确认附近有可燃材料', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(674, 'en', 'Only the start time', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(674, 'es', 'Sólo la hora de inicio', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(674, 'fr', 'Uniquement l\'heure de début', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(674, 'pt', 'Somente o horário de início', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(674, 'zh', '只检查开始时间', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(675, 'en', 'Controls, required authorization or permit, and area conditions', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(675, 'es', 'Controles, autorización o permiso requerido y condiciones del área', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(675, 'fr', 'Les mesures de contrôle, l\'autorisation ou le permis requis et les conditions de la zone', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(675, 'pt', 'Controles, autorização ou permissão exigida e condições da área', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(675, 'zh', '控制措施、所需授权或许可证以及作业区域条件', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(676, 'en', 'A crowded dining room', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(676, 'es', 'Un comedor lleno', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(676, 'fr', 'Une salle à manger pleine', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(676, 'pt', 'Um refeitório cheio', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(676, 'zh', '拥挤的餐厅', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(677, 'en', 'Any outdoor location', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(677, 'es', 'Todo lugar al aire libre', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(677, 'fr', 'Tout espace extérieur', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(677, 'pt', 'Todo local ao ar livre', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(677, 'zh', '所有室外场所', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(678, 'en', 'Any small office', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(678, 'es', 'Cualquier oficina pequeña', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(678, 'fr', 'N\'importe quel petit bureau', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(678, 'pt', 'Qualquer escritório pequeno', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(678, 'zh', '任何较小的办公室', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(679, 'en', 'Limited access and potential hazards that require specific controls', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(679, 'es', 'Acceso limitado y posibles peligros que requieren controles específicos', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(679, 'fr', 'Un accès limité et des dangers possibles nécessitant des mesures spécifiques', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(679, 'pt', 'Acesso limitado e possíveis perigos que exigem controles específicos', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(679, 'zh', '进出受限，并可能存在需要专项控制的危险', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(680, 'en', 'Only at night', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(680, 'es', 'Sólo de noche', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(680, 'fr', 'Uniquement la nuit', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(680, 'pt', 'Somente à noite', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(680, 'zh', '只有夜间可以', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(681, 'en', 'Yes, if two people enter', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(681, 'es', 'Sí, si entran dos personas', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(681, 'fr', 'Oui, si deux personnes entrent', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(681, 'pt', 'Sim, se entrarem duas pessoas', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(681, 'zh', '可以，只要两个人一起进入', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(682, 'en', 'Yes, if the work will be brief', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(682, 'es', 'Sí, si el trabajo dura poco', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(682, 'fr', 'Oui, si le travail est court', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(682, 'pt', 'Sim, se o trabalho durar pouco', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(682, 'zh', '可以，只要作业时间短', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(683, 'en', 'No', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(683, 'es', 'No', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(683, 'fr', 'Non', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(683, 'pt', 'Não', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(683, 'zh', '不可以', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(684, 'en', 'Do not report faults', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(684, 'es', 'No reportar fallas', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(684, 'fr', 'Ne pas signaler les défaillances', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(684, 'pt', 'Não relatar falhas', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(684, 'zh', '不报告车辆故障', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(685, 'en', 'Increase speed if you are late', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(685, 'es', 'Aumentar velocidad si hay atraso', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(685, 'fr', 'Accélérer en cas de retard', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(685, 'pt', 'Aumentar a velocidade se estiver atrasado', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(685, 'zh', '如果迟到就提高车速', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(686, 'en', 'Use the phone while driving', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(686, 'es', 'Usar el teléfono mientras conduces', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(686, 'fr', 'Utiliser le téléphone en conduisant', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(686, 'pt', 'Usar o telefone enquanto dirige', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(686, 'zh', '驾驶时使用手机', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(687, 'en', 'Wear a seat belt, obey speed limits and avoid distractions', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(687, 'es', 'Usar cinturón, respetar límites y evitar distracciones', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(687, 'fr', 'Porter la ceinture, respecter les limitations et éviter les distractions', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(687, 'pt', 'Usar cinto, respeitar limites e evitar distrações', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(687, 'zh', '系好安全带、遵守限速并避免分心', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(688, 'en', 'Use the phone to stay awake', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(688, 'es', 'Usar el teléfono para mantenerse despierto', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(688, 'fr', 'Utiliser le téléphone pour rester éveillé', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(688, 'pt', 'Usar o telefone para se manter acordado', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(688, 'zh', '使用手机让自己保持清醒', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(689, 'en', 'Increase speed', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(689, 'es', 'Aumentar la velocidad', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(689, 'fr', 'Augmenter la vitesse', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(689, 'pt', 'Aumentar a velocidade', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(689, 'zh', '提高车速', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(690, 'en', 'Open a window and continue indefinitely', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(690, 'es', 'Abrir una ventana y continuar indefinidamente', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(690, 'fr', 'Ouvrir une fenêtre et continuer indéfiniment', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(690, 'pt', 'Abrir uma janela e continuar indefinidamente', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(690, 'zh', '打开车窗后一直继续驾驶', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(691, 'en', 'Stop in a safe place and apply the fatigue-control procedure', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37');
INSERT INTO `onboarding_question_option_translations` (`id_option`, `language_code`, `option_text`, `updated_by`, `date_create`, `last_update`) VALUES
(691, 'es', 'Detenerse en un lugar seguro y aplicar el control de fatiga', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(691, 'fr', 'S\'arrêter dans un endroit sûr et appliquer les mesures de gestion de la fatigue', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(691, 'pt', 'Parar em local seguro e aplicar o controle de fadiga', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(691, 'zh', '在安全地点停车并执行疲劳管理措施', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(692, 'en', 'Walk under raised loads', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(692, 'es', 'Pasar bajo cargas elevadas', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(692, 'fr', 'Passer sous des charges levées', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(692, 'pt', 'Passar sob cargas elevadas', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(692, 'zh', '从升起的载荷下方通过', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(693, 'en', 'Approach the equipment from behind', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(693, 'es', 'Acercarse por detrás del equipo', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(693, 'fr', 'S\'approcher par l\'arrière de l\'engin', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(693, 'pt', 'Aproximar-se por trás do equipamento', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(693, 'zh', '从设备后方靠近', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(694, 'en', 'Walk through any area', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(694, 'es', 'Caminar por cualquier zona', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(694, 'fr', 'Marcher dans n\'importe quelle zone', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(694, 'pt', 'Caminhar por qualquer área', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(694, 'zh', '在任何区域随意行走', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(695, 'en', 'Use pedestrian routes, keep a safe distance and follow signage', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(695, 'es', 'Usar rutas peatonales, mantener distancia y respetar señalización', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(695, 'fr', 'Utiliser les voies piétonnes, garder ses distances et respecter la signalisation', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(695, 'pt', 'Usar rotas de pedestres, manter distância e respeitar a sinalização', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(695, 'zh', '走人行通道、保持安全距离并遵守标识', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(696, 'en', 'Yes, if it looks stable', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(696, 'es', 'Sí, si parece estable', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(696, 'fr', 'Oui, si elle semble stable', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(696, 'pt', 'Sim, se parecer estável', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(696, 'zh', '安全，只要看起来稳定', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(697, 'en', 'Yes, if wearing a hard hat', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(697, 'es', 'Sí, con casco', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(697, 'fr', 'Oui, avec un casque', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(697, 'pt', 'Sim, usando capacete', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(697, 'zh', '安全，只要戴安全帽', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(698, 'en', 'Yes, if you move quickly', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(698, 'es', 'Sí, si se hace rápido', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(698, 'fr', 'Oui, si l\'on passe rapidement', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(698, 'pt', 'Sim, se passar rápido', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(698, 'zh', '安全，只要快速通过', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(699, 'en', 'No', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(699, 'es', 'No', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(699, 'fr', 'Non', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(699, 'pt', 'Não', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(699, 'zh', '不安全', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(700, 'en', 'It allows materials to be stored in exits', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(700, 'es', 'Permite guardar materiales en salidas', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(700, 'fr', 'Permettre de stocker du matériel dans les sorties', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(700, 'pt', 'Permite guardar materiais nas saídas', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(700, 'zh', '可以在出口处存放材料', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(701, 'en', 'It eliminates the need for inspections', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(701, 'es', 'Elimina la necesidad de inspecciones', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(701, 'fr', 'Supprimer le besoin d\'inspections', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(701, 'pt', 'Elimina a necessidade de inspeções', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(701, 'zh', '可以取消检查', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(702, 'en', 'It only improves appearance', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(702, 'es', 'Sólo mejora la apariencia', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(702, 'fr', 'Seulement améliorer l\'apparence', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(702, 'pt', 'Apenas melhora a aparência', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(702, 'zh', '只改善外观', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(703, 'en', 'It reduces falls, impacts, fires and obstructions', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(703, 'es', 'Reduce caídas, golpes, incendios y obstrucciones', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(703, 'fr', 'Réduire les chutes, les chocs, les incendies et les obstructions', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(703, 'pt', 'Reduz quedas, impactos, incêndios e obstruções', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(703, 'zh', '减少跌倒、撞击、火灾和通道堵塞', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(704, 'en', 'On unstable surfaces', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(704, 'es', 'Sobre superficies inestables', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(704, 'fr', 'Sur des surfaces instables', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(704, 'pt', 'Sobre superfícies instáveis', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(704, 'zh', '不稳定的表面上', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(705, 'en', 'In front of fire extinguishers', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(705, 'es', 'Delante de extintores', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(705, 'fr', 'Devant les extincteurs', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(705, 'pt', 'Na frente de extintores', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(705, 'zh', '灭火器前方', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(706, 'en', 'In any walkway', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(706, 'es', 'En cualquier pasillo', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(706, 'fr', 'Dans n\'importe quel passage', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(706, 'pt', 'Em qualquer corredor', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(706, 'zh', '任何通道都可以', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(707, 'en', 'In designated, stable locations without blocking access or emergency equipment', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(707, 'es', 'En lugares definidos y estables, sin bloquear accesos ni emergencias', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(707, 'fr', 'Dans des emplacements définis et stables, sans bloquer les accès ni les équipements d\'urgence', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(707, 'pt', 'Em locais definidos e estáveis, sem bloquear acessos nem emergências', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(707, 'zh', '存放在指定且稳定的位置，不得堵塞通道或应急设施', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(708, 'en', 'Lend it without inspection', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(708, 'es', 'Prestarlo sin inspección', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(708, 'fr', 'Le prêter sans inspection', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(708, 'pt', 'Emprestá-lo sem inspeção', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(708, 'zh', '未经检查借给他人', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(709, 'en', 'Cut uncomfortable straps', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(709, 'es', 'Cortar correas incómodas', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(709, 'fr', 'Couper les sangles inconfortables', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(709, 'pt', 'Cortar tiras desconfortáveis', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(709, 'zh', '剪掉不舒服的带子', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(710, 'en', 'Use it without inspection', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(710, 'es', 'Usarlo sin revisarlo', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(710, 'fr', 'L\'utiliser sans inspection', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(710, 'pt', 'Usá-lo sem revisão', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(710, 'zh', '不检查直接使用', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(711, 'en', 'Inspect it and verify fit and compatibility', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(711, 'es', 'Inspeccionarlo y verificar ajuste y compatibilidad', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(711, 'fr', 'L\'inspecter et vérifier son réglage et sa compatibilité', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(711, 'pt', 'Inspecioná-lo e verificar ajuste e compatibilidade', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(711, 'zh', '检查安全带并确认其调节和兼容性', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(712, 'en', 'Rely on experience', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(712, 'es', 'Confiar en la experiencia', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(712, 'fr', 'Se fier à l\'expérience', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(712, 'pt', 'Confiar na experiência', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(712, 'zh', '依靠经验', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(713, 'en', 'Use only signage', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(713, 'es', 'Usar sólo señalización', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(713, 'fr', 'Utiliser uniquement la signalisation', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(713, 'pt', 'Usar apenas sinalização', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(713, 'zh', '只使用警示标识', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(714, 'en', 'Use PPE first and eliminate the hazard later', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(714, 'es', 'Usar primero EPP y después eliminar el peligro', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(714, 'fr', 'Utiliser d\'abord les EPI puis éliminer le danger', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(714, 'pt', 'Usar primeiro EPI e depois eliminar o perigo', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(714, 'zh', '先使用 PPE，之后再消除危险', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(715, 'en', 'Prioritize elimination, substitution and engineering controls before relying only on PPE', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(715, 'es', 'Priorizar eliminación, sustitución e ingeniería antes de depender sólo del EPP', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(715, 'fr', 'Prioriser l\'élimination, la substitution et les mesures techniques avant de dépendre uniquement des EPI', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(715, 'pt', 'Priorizar eliminação, substituição e controles de engenharia antes de depender apenas do EPI', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(715, 'zh', '优先采用消除、替代和工程控制，再考虑仅依赖 PPE', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(716, 'en', 'Training', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(716, 'es', 'Capacitación', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(716, 'fr', 'Formation', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(716, 'pt', 'Treinamento', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(716, 'zh', '培训', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(717, 'en', 'Warning', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(717, 'es', 'Advertencia', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(717, 'fr', 'Avertissement', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(717, 'pt', 'Advertência', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(717, 'zh', '警示', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(718, 'en', 'PPE', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(718, 'es', 'EPP', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(718, 'fr', 'EPI', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(718, 'pt', 'EPI', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(718, 'zh', 'PPE', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(719, 'en', 'Elimination', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(719, 'es', 'Eliminación', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(719, 'fr', 'Élimination', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(719, 'pt', 'Eliminação', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(719, 'zh', '消除', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(720, 'en', 'Allow training to be omitted', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(720, 'es', 'Permitir omitir capacitación', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(720, 'fr', 'À permettre d\'omettre la formation', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(720, 'pt', 'Permitir omitir treinamento', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(720, 'zh', '允许省略培训', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(721, 'en', 'Replace all supervision', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(721, 'es', 'Reemplazar toda supervisión', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(721, 'fr', 'À remplacer toute supervision', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(721, 'pt', 'Substituir toda supervisão', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(721, 'zh', '替代所有监督', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(722, 'en', 'Increase paperwork', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(722, 'es', 'Aumentar trámites', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(722, 'fr', 'À augmenter les formalités', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(722, 'pt', 'Aumentar a burocracia', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(722, 'zh', '增加手续', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(723, 'en', 'Define how to perform an activity with its preventive controls', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(723, 'es', 'Definir cómo ejecutar una actividad con sus controles preventivos', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(723, 'fr', 'À définir comment réaliser une activité avec ses mesures de prévention', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(723, 'pt', 'Definir como executar uma atividade com seus controles preventivos', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(723, 'zh', '规定如何在落实预防控制措施的情况下开展作业', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(724, 'en', 'Ask for another tool', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(724, 'es', 'Pedir otra herramienta', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(724, 'fr', 'Demander un autre outil', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(724, 'pt', 'Pedir outra ferramenta', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(724, 'zh', '要求换一件工具', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(725, 'en', 'Improvise', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(725, 'es', 'Improvisar', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(725, 'fr', 'Improviser', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(725, 'pt', 'Improvisar', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(725, 'zh', '临时凑合操作', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(726, 'en', 'Perform it by watching others', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(726, 'es', 'Realizarla mirando a otros', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(726, 'fr', 'L\'exécuter en regardant les autres', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(726, 'pt', 'Executá-la observando outras pessoas', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(726, 'zh', '观察别人后自行操作', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(727, 'en', 'Do not perform it until you have the required competence or authorization', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(727, 'es', 'No ejecutarla hasta contar con competencia o autorización requerida', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(727, 'fr', 'Ne pas l\'exécuter avant d\'avoir la compétence ou l\'autorisation requise', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(727, 'pt', 'Não executá-la até ter a competência ou autorização exigida', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(727, 'zh', '在具备所需能力或取得授权前不要执行该任务', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(728, 'en', 'Increase the load to test it', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(728, 'es', 'Aumentar la carga para probarlo', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(728, 'fr', 'Augmenter la charge pour le tester', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(728, 'pt', 'Aumentar a carga para testá-lo', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(728, 'zh', '增加负荷进行测试', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(729, 'en', 'Hide the fault', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(729, 'es', 'Ocultar la falla', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(729, 'fr', 'Cacher la défaillance', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(729, 'pt', 'Esconder a falha', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(729, 'zh', '隐藏故障', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(730, 'en', 'Keep using it', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(730, 'es', 'Seguir utilizándolo', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(730, 'fr', 'Continuer à l\'utiliser', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(730, 'pt', 'Continuar utilizando', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(730, 'zh', '继续使用', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(731, 'en', 'Remove or stop it according to procedure and report the fault', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(731, 'es', 'Retirarlo o detenerlo según procedimiento y reportar la falla', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(731, 'fr', 'Le retirer ou l\'arrêter selon la procédure et signaler la défaillance', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(731, 'pt', 'Retirá-lo ou pará-lo conforme o procedimento e relatar a falha', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(731, 'zh', '按照程序停止或停用设备，并报告故障', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(732, 'en', 'Assign vacations', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(732, 'es', 'Para asignar vacaciones', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(732, 'fr', 'À attribuer les congés', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(732, 'pt', 'Definir férias', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(732, 'zh', '安排休假', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(733, 'en', 'Replace procedures', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(733, 'es', 'Para reemplazar procedimientos', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(733, 'fr', 'À remplacer les procédures', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(733, 'pt', 'Substituir procedimentos', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(733, 'zh', '替代作业程序', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(734, 'en', 'Only record attendance', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(734, 'es', 'Sólo para registrar asistencia', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(734, 'fr', 'Uniquement à enregistrer la présence', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(734, 'pt', 'Apenas registrar presença', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(734, 'zh', '只用于记录出勤', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(735, 'en', 'Communicate risks, changes, controls and responsibilities', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(735, 'es', 'Para comunicar riesgos, cambios, controles y responsabilidades', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(735, 'fr', 'À communiquer les risques, changements, mesures de contrôle et responsabilités', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(735, 'pt', 'Comunicar riscos, mudanças, controles e responsabilidades', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(735, 'zh', '沟通风险、变化、控制措施和责任', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(736, 'en', 'Change the worker without reporting it', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(736, 'es', 'Cambiar de trabajador sin informar', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(736, 'fr', 'Changer de travailleur sans informer', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(736, 'pt', 'Trocar de trabalhador sem informar', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(736, 'zh', '不报告就更换作业人员', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(737, 'en', 'Remove signage', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(737, 'es', 'Eliminar señalización', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(737, 'fr', 'Retirer la signalisation', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(737, 'pt', 'Retirar a sinalização', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(737, 'zh', '撤除警示标识', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(738, 'en', 'Continue because it was already authorized', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(738, 'es', 'Continuar porque ya estaba autorizada', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(738, 'fr', 'Continuer parce qu\'elle était déjà autorisée', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(738, 'pt', 'Continuar porque já estava autorizada', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(738, 'zh', '因为之前已获授权所以继续作业', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(739, 'en', 'Stop and reassess risks and controls', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(739, 'es', 'Detener y reevaluar riesgos y controles', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(739, 'fr', 'Arrêter et réévaluer les risques et les mesures de contrôle', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(739, 'pt', 'Parar e reavaliar riscos e controles', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(739, 'zh', '停止作业并重新评估风险和控制措施', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(740, 'en', 'To allow general access', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(740, 'es', 'Para permitir acceso general', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(740, 'fr', 'À autoriser l\'accès général', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(740, 'pt', 'Permitir acesso geral', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(740, 'zh', '允许所有人进入', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(741, 'en', 'To always replace physical protection', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(741, 'es', 'Para reemplazar siempre una protección física', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(741, 'fr', 'À toujours remplacer une protection physique', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(741, 'pt', 'Substituir sempre uma proteção física', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(741, 'zh', '始终替代实体防护设施', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(742, 'en', 'To decorate the area', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(742, 'es', 'Para decorar el área', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(742, 'fr', 'À décorer la zone', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(742, 'pt', 'Decorar a área', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(742, 'zh', '装饰区域', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(743, 'en', 'To warn, restrict or guide people in relation to a hazard', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(743, 'es', 'Para advertir, restringir o guiar frente a un peligro', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(743, 'fr', 'À avertir, restreindre ou guider face à un danger', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(743, 'pt', 'Advertir, restringir ou orientar diante de um perigo', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(743, 'zh', '针对危险进行警示、限制或引导', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(744, 'en', 'Enter from another side', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(744, 'es', 'Entrar por otro lado', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(744, 'fr', 'Entrer par un autre côté', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(744, 'pt', 'Entrar por outro lado', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(744, 'zh', '从另一侧进入', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(745, 'en', 'Move the barrier', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(745, 'es', 'Mover la barrera', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(745, 'fr', 'Déplacer la barrière', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(745, 'pt', 'Mover a barreira', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(745, 'zh', '移动隔离栏', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(746, 'en', 'Cross it if you see no activity', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(746, 'es', 'Cruzar si no ves actividad', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(746, 'fr', 'La traverser si vous ne voyez aucune activité', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(746, 'pt', 'Atravessar se não houver atividade visível', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(746, 'zh', '如果看不到作业活动就穿过', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(747, 'en', 'Respect the restriction and request authorization if appropriate', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(747, 'es', 'Respetar la delimitación y solicitar autorización si corresponde', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(747, 'fr', 'Respecter la délimitation et demander une autorisation si nécessaire', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(747, 'pt', 'Respeitar a delimitação e solicitar autorização quando aplicável', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(747, 'zh', '遵守隔离要求，并在需要时申请授权', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(748, 'en', 'Because it is only useful outside work', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(748, 'es', 'Porque sólo sirve fuera del trabajo', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(748, 'fr', 'Parce qu\'ils ne servent qu\'en dehors du travail', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(748, 'pt', 'Porque só serve fora do trabalho', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(748, 'zh', '因为 PPE 只在工作场所以外有用', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(749, 'en', 'Because it always fails', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(749, 'es', 'Porque siempre falla', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(749, 'fr', 'Parce qu\'ils échouent toujours', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(749, 'pt', 'Porque sempre falha', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(749, 'zh', '因为 PPE 总会失效', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(750, 'en', 'Because it is unnecessary', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(750, 'es', 'Porque es innecesario', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(750, 'fr', 'Parce qu\'ils sont inutiles', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(750, 'pt', 'Porque é desnecessário', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(750, 'zh', '因为 PPE 没有必要', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(751, 'en', 'Because it protects the person but does not eliminate the hazard at its source', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(751, 'es', 'Porque protege a la persona pero no elimina el peligro en su origen', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(751, 'fr', 'Parce qu\'ils protègent la personne mais n\'éliminent pas le danger à la source', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(751, 'pt', 'Porque protege a pessoa, mas não elimina o perigo na origem', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(751, 'zh', '因为它保护个人，但并不能从源头消除危险', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(752, 'en', 'Work without breaks', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(752, 'es', 'Trabajar sin descanso', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(752, 'fr', 'Travailler sans pause', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(752, 'pt', 'Trabalhar sem descanso', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(752, 'zh', '不休息持续工作', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(753, 'en', 'Wear more clothing without an assessment', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(753, 'es', 'Usar más ropa sin evaluación', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(753, 'fr', 'Porter davantage de vêtements sans évaluation', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(753, 'pt', 'Usar mais roupas sem avaliação', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(753, 'zh', '未经评估就穿更多衣服', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(754, 'en', 'Avoid drinking water', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(754, 'es', 'Evitar beber agua', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(754, 'fr', 'Éviter de boire de l\'eau', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(754, 'pt', 'Evitar beber água', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(754, 'zh', '避免喝水', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(755, 'en', 'Hydration, breaks and exposure controls', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(755, 'es', 'Hidratación, pausas y controles de exposición', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(755, 'fr', 'L\'hydratation, les pauses et les mesures de contrôle de l\'exposition', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(755, 'pt', 'Hidratação, pausas e controles de exposição', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(755, 'zh', '补充水分、安排休息并控制暴露', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(756, 'en', 'Look directly at the sun', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(756, 'es', 'Mirar directamente al sol', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(756, 'fr', 'Regarder directement le soleil', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(756, 'pt', 'Olhar diretamente para o sol', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(756, 'zh', '直视太阳', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(757, 'en', 'Work without protection to get used to it', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(757, 'es', 'Trabajar sin protección para acostumbrarse', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(757, 'fr', 'Travailler sans protection pour s\'habituer', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(757, 'pt', 'Trabalhar sem proteção para se acostumar', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(757, 'zh', '不使用防护，让身体逐渐适应', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(758, 'en', 'Ignore radiation on cloudy days', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(758, 'es', 'Ignorar la radiación en días nublados', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(758, 'fr', 'Ignorer le rayonnement les jours nuageux', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(758, 'pt', 'Ignorar a radiação em dias nublados', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(758, 'zh', '阴天时忽略紫外线辐射', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(759, 'en', 'Use defined measures such as shade, appropriate clothing and sunscreen when required', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(759, 'es', 'Usar las medidas definidas como sombra, ropa adecuada y protector solar cuando corresponda', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(759, 'fr', 'Appliquer les mesures définies telles que l\'ombre, des vêtements adaptés et une protection solaire lorsque nécessaire', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(759, 'pt', 'Usar as medidas definidas, como sombra, roupa adequada e protetor solar quando necessário', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(759, 'zh', '按照规定采取遮阳、合适衣物和必要时使用防晒用品等措施', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(760, 'en', 'Remain still for long periods', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(760, 'es', 'Permanecer inmóvil mucho tiempo', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(760, 'fr', 'Rester immobile très longtemps', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(760, 'pt', 'Ficar imóvel por muito tempo', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(760, 'zh', '长时间保持不动', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(761, 'en', 'Do not report numbness', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(761, 'es', 'No informar entumecimiento', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(761, 'fr', 'Ne pas signaler les engourdissements', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(761, 'pt', 'Não informar dormência', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(761, 'zh', '出现麻木也不报告', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(762, 'en', 'Wear wet clothing', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(762, 'es', 'Usar ropa húmeda', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(762, 'fr', 'Porter des vêtements mouillés', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(762, 'pt', 'Usar roupas molhadas', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(762, 'zh', '穿湿衣服', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(763, 'en', 'Wear suitable clothing and control exposure time', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(763, 'es', 'Usar vestimenta apropiada y controlar el tiempo de exposición', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(763, 'fr', 'Porter des vêtements appropriés et contrôler le temps d\'exposition', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(763, 'pt', 'Usar vestimenta apropriada e controlar o tempo de exposição', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(763, 'zh', '穿着合适衣物并控制暴露时间', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(764, 'en', 'It only affects drivers', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(764, 'es', 'Sólo afecta a conductores', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(764, 'fr', 'Elle n\'affecte que les conducteurs', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(764, 'pt', 'Só afeta motoristas', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(764, 'zh', '只会影响驾驶员', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(765, 'en', 'It improves concentration', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(765, 'es', 'Mejora la concentración', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(765, 'fr', 'Elle améliore la concentration', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(765, 'pt', 'Melhora a concentração', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(765, 'zh', '会提高注意力', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(766, 'en', 'It only affects mood', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(766, 'es', 'Sólo afecta el ánimo', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(766, 'fr', 'Elle n\'affecte que l\'humeur', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(766, 'pt', 'Só afeta o humor', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(766, 'zh', '只会影响情绪', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(767, 'en', 'It can reduce attention, reaction time and decision-making ability', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(767, 'es', 'Puede reducir atención, reacción y capacidad de decisión', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(767, 'fr', 'Elle peut réduire l\'attention, le temps de réaction et la capacité de décision', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(767, 'pt', 'Pode reduzir a atenção, a reação e a capacidade de decisão', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(767, 'zh', '它可能降低注意力、反应能力和决策能力', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(768, 'en', 'Self-medicate without reporting it', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(768, 'es', 'Automedicarse sin informar', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(768, 'fr', 'S\'automédiquer sans le signaler', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(768, 'pt', 'Automedicar-se sem informar', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(768, 'zh', '不报告并自行用药', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(769, 'en', 'Wait indefinitely', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(769, 'es', 'Esperar indefinidamente', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(769, 'fr', 'Attendre indéfiniment', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(769, 'pt', 'Esperar indefinidamente', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(769, 'zh', '无限期等待', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(770, 'en', 'Hide them', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(770, 'es', 'Ocultarlos', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(770, 'fr', 'Les cacher', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(770, 'pt', 'Escondê-los', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(770, 'zh', '隐藏症状', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(771, 'en', 'Report them and follow the defined health and safety channel', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(771, 'es', 'Informarlos y seguir el canal de salud y seguridad definido', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(771, 'fr', 'Les signaler et suivre le canal de santé et sécurité défini', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(771, 'pt', 'Informá-los e seguir o canal definido de saúde e segurança', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(771, 'zh', '进行报告，并按照规定的健康与安全渠道处理', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(772, 'en', 'An illness that should not be reported', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(772, 'es', 'Una enfermedad que no debe reportarse', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(772, 'fr', 'Une maladie qui ne doit pas être signalée', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(772, 'pt', 'Uma doença que não deve ser relatada', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(772, 'zh', '一种不需要报告的疾病', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(773, 'en', 'Only an immediate traumatic injury', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(773, 'es', 'Sólo una lesión traumática inmediata', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(773, 'fr', 'Uniquement une lésion traumatique immédiate', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(773, 'pt', 'Somente uma lesão traumática imediata', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(773, 'zh', '只有立即发生的创伤性损伤', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(774, 'en', 'Any illness occurring outside working hours', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(774, 'es', 'Cualquier enfermedad fuera del horario laboral', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(774, 'fr', 'Toute maladie survenant en dehors des heures de travail', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(774, 'pt', 'Qualquer doença fora do horário de trabalho', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(774, 'zh', '工作时间以外发生的任何疾病', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(775, 'en', 'A health condition associated with work exposures or work-related factors', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(775, 'es', 'Una condición de salud asociada a exposiciones o factores del trabajo', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(775, 'fr', 'Un problème de santé associé à des expositions ou facteurs liés au travail', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(775, 'pt', 'Uma condição de saúde associada a exposições ou fatores do trabalho', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(775, 'zh', '与工作暴露或工作因素相关的健康问题', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(776, 'en', 'Only for statistics', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(776, 'es', 'Sólo por estadística', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(776, 'fr', 'Uniquement pour les statistiques', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(776, 'pt', 'Somente para estatística', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(776, 'zh', '只是为了统计', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(777, 'en', 'To reduce vacation time', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(777, 'es', 'Para reducir vacaciones', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(777, 'fr', 'Pour réduire les congés', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(777, 'pt', 'Para reduzir férias', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(777, 'zh', '用来减少休假', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(778, 'en', 'To replace preventive measures', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(778, 'es', 'Para reemplazar medidas preventivas', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(778, 'fr', 'Pour remplacer les mesures de prévention', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(778, 'pt', 'Para substituir medidas preventivas', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(778, 'zh', '用来替代预防措施', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(779, 'en', 'To detect early effects related to workplace exposures', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(779, 'es', 'Para detectar tempranamente efectos relacionados con exposiciones laborales', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(779, 'fr', 'Pour détecter précocement les effets liés aux expositions professionnelles', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(779, 'pt', 'Para detectar precocemente efeitos relacionados a exposições no trabalho', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(779, 'zh', '有助于及早发现与职业暴露相关的健康影响', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(780, 'en', 'Avoid cleaning surfaces', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(780, 'es', 'Evitar limpiar superficies', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(780, 'fr', 'Éviter de nettoyer les surfaces', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(780, 'pt', 'Evitar limpar superfícies', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(780, 'zh', '避免清洁表面', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(781, 'en', 'Hide symptoms', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37');
INSERT INTO `onboarding_question_option_translations` (`id_option`, `language_code`, `option_text`, `updated_by`, `date_create`, `last_update`) VALUES
(781, 'es', 'Ocultar síntomas', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(781, 'fr', 'Cacher les symptômes', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(781, 'pt', 'Esconder sintomas', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(781, 'zh', '隐瞒症状', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(782, 'en', 'Share personal items', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(782, 'es', 'Compartir elementos personales', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(782, 'fr', 'Partager des objets personnels', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(782, 'pt', 'Compartilhar itens pessoais', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(782, 'zh', '共用个人用品', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(783, 'en', 'Hand hygiene and defined health measures', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(783, 'es', 'Higiene de manos y medidas sanitarias definidas', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(783, 'fr', 'L\'hygiène des mains et les mesures sanitaires définies', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(783, 'pt', 'Higiene das mãos e medidas sanitárias definidas', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(783, 'zh', '保持手部卫生并执行规定的卫生措施', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(784, 'en', 'Pour it down the drain', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(784, 'es', 'Arrojarlos al desagüe', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(784, 'fr', 'Les jeter dans les canalisations', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(784, 'pt', 'Despejá-los no ralo', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(784, 'zh', '倒入排水系统', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(785, 'en', 'Leave it in walkways', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(785, 'es', 'Dejarlos en pasillos', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(785, 'fr', 'Les laisser dans les passages', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(785, 'pt', 'Deixá-los em corredores', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(785, 'zh', '留在通道中', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(786, 'en', 'Mix it with general waste', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(786, 'es', 'Mezclarlos con basura común', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(786, 'fr', 'Les mélanger aux déchets ordinaires', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(786, 'pt', 'Misturá-los ao lixo comum', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(786, 'zh', '与普通垃圾混合', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(787, 'en', 'Manage it using designated containers and defined disposal routes', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(787, 'es', 'Gestionarlos en recipientes y circuitos definidos', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(787, 'fr', 'Les gérer dans les contenants et filières prévus', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(787, 'pt', 'Gerenciá-los em recipientes e fluxos definidos', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(787, 'zh', '使用规定的容器和处理流程进行管理', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(788, 'en', 'Ignore it', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(788, 'es', 'Ignorarlo', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(788, 'fr', 'L\'ignorer', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(788, 'pt', 'Ignorar', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(788, 'zh', '忽略不处理', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(789, 'en', 'Add another product without information', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(789, 'es', 'Agregar otro producto sin información', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(789, 'fr', 'Ajouter un autre produit sans information', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(789, 'pt', 'Adicionar outro produto sem informação', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(789, 'zh', '在不了解信息的情况下加入其他产品', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(790, 'en', 'Clean it with your hands', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(790, 'es', 'Limpiarlo con las manos', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(790, 'fr', 'Le nettoyer avec les mains', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(790, 'pt', 'Limpar com as mãos', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(790, 'zh', '徒手清理', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(791, 'en', 'Isolate the area, report it and activate the response procedure', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(791, 'es', 'Aislar, informar y activar el procedimiento de respuesta', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(791, 'fr', 'Isoler la zone, informer et déclencher la procédure d\'intervention', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(791, 'pt', 'Isolar, informar e acionar o procedimento de resposta', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37'),
(791, 'zh', '隔离区域、报告并启动应急响应程序', 'migration-e3-vs1', '2026-10-08 19:19:37', '2026-10-08 19:19:37');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `onboarding_question_translations`
--

CREATE TABLE `onboarding_question_translations` (
  `id_question` int(11) NOT NULL,
  `language_code` varchar(5) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL,
  `question_text` text NOT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `date_create` datetime NOT NULL DEFAULT current_timestamp(),
  `last_update` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `onboarding_question_translations`
--

INSERT INTO `onboarding_question_translations` (`id_question`, `language_code`, `question_text`, `updated_by`, `date_create`, `last_update`) VALUES
(1, 'en', 'What should you do if you detect an unsafe condition in your workplace?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(1, 'es', '¿Qué debes hacer si detectas una condición insegura en tu lugar de trabajo?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(1, 'fr', 'Que devez-vous faire si vous détectez une situation dangereuse sur votre lieu de travail ?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(1, 'pt', 'O que você deve fazer ao identificar uma condição insegura no local de trabalho?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(1, 'zh', '如果你在工作场所发现不安全状况，应当怎么做？', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(2, 'en', 'What does the acronym PPE mean?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(2, 'es', '¿Qué significa la sigla EPP?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(2, 'fr', 'Que signifie le sigle EPI ?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(2, 'pt', 'O que significa a sigla EPI?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(2, 'zh', 'PPE（个人防护装备）是指什么？', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(3, 'en', 'What is the main purpose of PPE?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(3, 'es', '¿Cuál es el objetivo principal del EPP?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(3, 'fr', 'Quel est l\'objectif principal des EPI ?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(3, 'pt', 'Qual é o principal objetivo do EPI?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(3, 'zh', 'PPE 的主要目的是什么？', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(4, 'en', 'What should be done with damaged PPE?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(4, 'es', '¿Qué debe hacerse con un EPP dañado?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(4, 'fr', 'Que faut-il faire avec un EPI endommagé ?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(4, 'pt', 'O que deve ser feito com um EPI danificado?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(4, 'zh', '损坏的 PPE 应如何处理？', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(5, 'en', 'What is a near miss?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(5, 'es', '¿Qué es un casi accidente o near miss?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(5, 'fr', 'Qu\'est-ce qu\'un quasi-accident ou near miss ?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(5, 'pt', 'O que é um quase acidente ou near miss?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(5, 'zh', '什么是险情或未遂事故（near miss）？', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(6, 'en', 'What should be done with a near miss?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(6, 'es', '¿Qué corresponde hacer con un casi accidente?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(6, 'fr', 'Que faut-il faire après un quasi-accident ?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(6, 'pt', 'O que deve ser feito com um quase acidente?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(6, 'zh', '发生未遂事故后应怎么做？', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(7, 'en', 'Before starting a non-routine task, what is recommended?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(7, 'es', 'Antes de iniciar una tarea no rutinaria, ¿qué es recomendable?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(7, 'fr', 'Avant de commencer une tâche non routinière, que recommande-t-on ?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(7, 'pt', 'Antes de iniciar uma tarefa não rotineira, o que é recomendável?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(7, 'zh', '开始非常规任务前，建议先做什么？', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(8, 'en', 'If there is an uncontrolled serious and imminent risk, what should the worker do?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(8, 'es', 'Si existe un riesgo grave e inminente no controlado, ¿qué debe hacer el trabajador?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(8, 'fr', 'S\'il existe un risque grave et imminent non maîtrisé, que doit faire le travailleur ?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(8, 'pt', 'Se existir um risco grave e iminente não controlado, o que o trabalhador deve fazer?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(8, 'zh', '如果存在未受控制的严重且迫在眉睫的风险，员工应怎么做？', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(9, 'en', 'What helps prevent same-level falls?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(9, 'es', '¿Qué ayuda a prevenir caídas al mismo nivel?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(9, 'fr', 'Qu\'est-ce qui aide à prévenir les chutes de plain-pied ?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(9, 'pt', 'O que ajuda a prevenir quedas no mesmo nível?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(9, 'zh', '什么措施有助于防止同一平面跌倒？', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(10, 'en', 'If liquid is spilled in a traffic area, what should be done?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(10, 'es', 'Si se derrama líquido en una zona de tránsito, ¿qué corresponde?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(10, 'fr', 'Si un liquide est renversé dans une zone de circulation, que faut-il faire ?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(10, 'pt', 'Se houver derramamento de líquido em uma área de circulação, o que deve ser feito?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(10, 'zh', '如果通行区域发生液体泄漏，应怎么做？', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(11, 'en', 'What must be kept clear at all times?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(11, 'es', '¿Qué debe mantenerse libre en todo momento?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(11, 'fr', 'Qu\'est-ce qui doit rester dégagé en permanence ?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(11, 'pt', 'O que deve permanecer livre em todo momento?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(11, 'zh', '什么区域必须始终保持畅通？', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(12, 'en', 'When you hear an evacuation alarm, what should you do?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(12, 'es', 'Al escuchar una alarma de evacuación, ¿qué corresponde?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(12, 'fr', 'Lorsque vous entendez une alarme d\'évacuation, que devez-vous faire ?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(12, 'pt', 'Ao ouvir um alarme de evacuação, o que deve ser feito?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(12, 'zh', '听到疏散警报时，应怎么做？', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(13, 'en', 'What is the purpose of an assembly point during an evacuation?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(13, 'es', '¿Para qué sirve el punto de encuentro en una evacuación?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(13, 'fr', 'À quoi sert le point de rassemblement lors d\'une évacuation ?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(13, 'pt', 'Para que serve o ponto de encontro em uma evacuação?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(13, 'zh', '疏散时设置集合点的目的是什么？', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(14, 'en', 'If you discover an incipient fire, what should you do first?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(14, 'es', 'Si descubres un incendio incipiente, ¿qué debes hacer primero?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(14, 'fr', 'Si vous découvrez un début d\'incendie, que devez-vous faire en premier ?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(14, 'pt', 'Se você detectar um princípio de incêndio, o que deve fazer primeiro?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(14, 'zh', '如果发现初起火灾，首先应做什么？', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(15, 'en', 'When is it appropriate to use a portable fire extinguisher?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(15, 'es', '¿Cuándo es apropiado usar un extintor portátil?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(15, 'fr', 'Quand est-il approprié d\'utiliser un extincteur portatif ?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(15, 'pt', 'Quando é apropriado usar um extintor portátil?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(15, 'zh', '什么时候适合使用便携式灭火器？', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(16, 'en', 'If an emergency exit is blocked, what should be done?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(16, 'es', 'Si una salida de emergencia está bloqueada, ¿qué corresponde?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(16, 'fr', 'Si une sortie de secours est bloquée, que faut-il faire ?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(16, 'pt', 'Se uma saída de emergência estiver bloqueada, o que deve ser feito?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(16, 'zh', '如果紧急出口被堵塞，应怎么做？', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(17, 'en', 'When faced with an injured person, what is a safe initial action?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(17, 'es', 'Ante una persona lesionada, ¿cuál es una acción inicial segura?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(17, 'fr', 'Face à une personne blessée, quelle est une première action sûre ?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(17, 'pt', 'Diante de uma pessoa lesionada, qual é uma ação inicial segura?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(17, 'zh', '发现有人受伤时，哪一项是安全的初始措施？', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(18, 'en', 'Who should provide specialized first aid?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(18, 'es', '¿Quién debe realizar primeros auxilios especializados?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(18, 'fr', 'Qui doit effectuer les premiers secours spécialisés ?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(18, 'pt', 'Quem deve prestar primeiros socorros especializados?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(18, 'zh', '谁应实施专业急救？', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(19, 'en', 'When manually lifting a load, which practice is safer?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(19, 'es', 'Para levantar manualmente una carga, ¿qué práctica es más segura?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(19, 'fr', 'Pour soulever manuellement une charge, quelle pratique est la plus sûre ?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(19, 'pt', 'Ao levantar manualmente uma carga, qual prática é mais segura?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(19, 'zh', '人工搬运物体时，哪种做法更安全？', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(20, 'en', 'If a load is too heavy for one person, what should they do?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(20, 'es', 'Si una carga es demasiado pesada para una persona, ¿qué debe hacer?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(20, 'fr', 'Si une charge est trop lourde pour une seule personne, que faut-il faire ?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(20, 'pt', 'Se uma carga for pesada demais para uma pessoa, o que deve ser feito?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(20, 'zh', '如果物体对一个人来说太重，应怎么做？', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(21, 'en', 'What helps reduce discomfort in office work?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(21, 'es', '¿Qué ayuda a reducir molestias en trabajo de oficina?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(21, 'fr', 'Qu\'est-ce qui aide à réduire l\'inconfort lors du travail de bureau ?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(21, 'pt', 'O que ajuda a reduzir desconfortos no trabalho de escritório?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(21, 'zh', '什么有助于减少办公室工作中的不适？', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(22, 'en', 'How should a computer screen normally be positioned?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(22, 'es', '¿Cómo debería ubicarse normalmente la pantalla del computador?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(22, 'fr', 'Comment l\'écran d\'ordinateur doit-il normalement être positionné ?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(22, 'pt', 'Como a tela do computador deve ser posicionada normalmente?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(22, 'zh', '电脑屏幕通常应如何放置？', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(23, 'en', 'If electrical equipment is defective and you are not authorized to repair it, what should you do?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(23, 'es', 'Si un equipo eléctrico está defectuoso y no estás autorizado para repararlo, ¿qué debes hacer?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(23, 'fr', 'Si un équipement électrique est défectueux et que vous n\'êtes pas autorisé à le réparer, que devez-vous faire ?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(23, 'pt', 'Se um equipamento elétrico estiver defeituoso e você não estiver autorizado a repará-lo, o que deve fazer?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(23, 'zh', '如果电气设备存在故障，而你无权维修，应怎么做？', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(24, 'en', 'Why should damaged plugs or extension cords not be used?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(24, 'es', '¿Por qué no deben usarse enchufes o extensiones dañadas?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(24, 'fr', 'Pourquoi ne faut-il pas utiliser des prises ou rallonges endommagées ?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(24, 'pt', 'Por que tomadas ou extensões danificadas não devem ser usadas?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(24, 'zh', '为什么不能使用损坏的插头或延长线？', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(25, 'en', 'What is the purpose of lockout/tagout (LOTO) for hazardous energy?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(25, 'es', '¿Para qué sirve el bloqueo y etiquetado de energías peligrosas (LOTO)?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(25, 'fr', 'À quoi sert le verrouillage et l\'étiquetage des énergies dangereuses (LOTO) ?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(25, 'pt', 'Para que serve o bloqueio e etiquetagem de energias perigosas (LOTO)?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(25, 'zh', '危险能源上锁挂牌（LOTO）的作用是什么？', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(26, 'en', 'Who can normally remove a personal LOTO lock?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(26, 'es', '¿Quién puede retirar normalmente un bloqueo personal LOTO?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(26, 'fr', 'Qui peut normalement retirer un verrou personnel LOTO ?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(26, 'pt', 'Quem pode normalmente retirar um bloqueio pessoal LOTO?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(26, 'zh', '通常谁可以移除个人 LOTO 锁？', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(27, 'en', 'Before using a portable ladder, what should be checked?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(27, 'es', 'Antes de usar una escalera portátil, ¿qué debe revisarse?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(27, 'fr', 'Avant d\'utiliser une échelle portative, que faut-il vérifier ?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(27, 'pt', 'Antes de usar uma escada portátil, o que deve ser verificado?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(27, 'zh', '使用便携式梯子前，应检查什么？', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(28, 'en', 'Which practice is unsafe when using a ladder?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(28, 'es', '¿Qué práctica es insegura al utilizar una escalera?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(28, 'fr', 'Quelle pratique est dangereuse lors de l\'utilisation d\'une échelle ?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(28, 'pt', 'Qual prática é insegura ao usar uma escada?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(28, 'zh', '使用梯子时，哪种做法是不安全的？', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(29, 'en', 'Before work involving a risk of falling from height, what should be done?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(29, 'es', 'Antes de un trabajo con riesgo de caída de altura, ¿qué corresponde?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(29, 'fr', 'Avant un travail présentant un risque de chute de hauteur, que faut-il faire ?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(29, 'pt', 'Antes de um trabalho com risco de queda de altura, o que deve ser feito?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(29, 'zh', '进行存在高处坠落风险的作业前，应怎么做？', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(30, 'en', 'What should be done with a defective hand tool?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(30, 'es', '¿Qué debe hacerse con una herramienta manual defectuosa?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(30, 'fr', 'Que faut-il faire avec un outil à main défectueux ?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(30, 'pt', 'O que deve ser feito com uma ferramenta manual defeituosa?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(30, 'zh', '有缺陷的手工具应如何处理？', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(31, 'en', 'What are machine guards for?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(31, 'es', '¿Para qué sirven las guardas de una máquina?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(31, 'fr', 'À quoi servent les protecteurs d\'une machine ?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(31, 'pt', 'Para que servem as proteções de uma máquina?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(31, 'zh', '机器防护装置的作用是什么？', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(32, 'en', 'Is it correct to remove a machine guard to work faster?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(32, 'es', '¿Es correcto retirar una guarda de máquina para trabajar más rápido?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(32, 'fr', 'Est-il correct de retirer un protecteur de machine pour travailler plus vite ?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(32, 'pt', 'É correto retirar uma proteção da máquina para trabalhar mais rápido?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(32, 'zh', '为了提高速度，可以拆除机器防护装置吗？', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(33, 'en', 'Before using an unfamiliar chemical product, what should you do?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(33, 'es', 'Antes de usar un producto químico desconocido, ¿qué corresponde?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(33, 'fr', 'Avant d\'utiliser un produit chimique inconnu, que faut-il faire ?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(33, 'pt', 'Antes de usar um produto químico desconhecido, o que deve ser feito?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(33, 'zh', '使用不熟悉的化学品前，应怎么做？', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(34, 'en', 'Which document provides information on hazards, storage, first aid and spills for a chemical product?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(34, 'es', '¿Qué documento informa peligros, almacenamiento, primeros auxilios y derrames de un producto químico?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(34, 'fr', 'Quel document indique les dangers, le stockage, les premiers secours et la conduite à tenir en cas de déversement d\'un produit chimique ?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(34, 'pt', 'Qual documento informa perigos, armazenamento, primeiros socorros e derramamentos de um produto químico?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(34, 'zh', '哪份文件包含化学品的危险、储存、急救和泄漏处置信息？', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(35, 'en', 'If a chemical container has no legible identification, what should you do?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(35, 'es', 'Si un envase químico no tiene identificación legible, ¿qué debes hacer?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(35, 'fr', 'Si un contenant chimique n\'a pas d\'identification lisible, que devez-vous faire ?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(35, 'pt', 'Se uma embalagem de produto químico não tiver identificação legível, o que você deve fazer?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(35, 'zh', '如果化学品容器上的标识无法辨认，应怎么做？', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(36, 'en', 'Why is ventilation important when working with vapors, gases or dust?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(36, 'es', '¿Por qué es importante la ventilación al trabajar con vapores, gases o polvo?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(36, 'fr', 'Pourquoi la ventilation est-elle importante lors d\'un travail avec des vapeurs, gaz ou poussières ?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(36, 'pt', 'Por que a ventilação é importante ao trabalhar com vapores, gases ou poeira?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(36, 'zh', '在有蒸气、气体或粉尘的环境中作业时，为什么通风很重要？', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(37, 'en', 'If a task generates high noise levels, what should be done?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(37, 'es', 'Si una tarea genera ruido elevado, ¿qué medida corresponde?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(37, 'fr', 'Si une tâche génère un niveau de bruit élevé, quelle mesure faut-il prendre ?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(37, 'pt', 'Se uma tarefa gerar ruído elevado, qual medida deve ser tomada?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(37, 'zh', '如果某项作业产生较高噪声，应采取什么措施？', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(38, 'en', 'Before using hearing protectors, what should be checked?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(38, 'es', 'Antes de usar protectores auditivos, ¿qué debe verificarse?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(38, 'fr', 'Avant d\'utiliser des protections auditives, que faut-il vérifier ?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(38, 'pt', 'Antes de usar protetores auditivos, o que deve ser verificado?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(38, 'zh', '使用听力防护用品前，应检查什么？', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(39, 'en', 'When should respiratory protection be used?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(39, 'es', '¿Cuándo debe utilizarse protección respiratoria?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(39, 'fr', 'Quand faut-il utiliser une protection respiratoire ?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(39, 'pt', 'Quando deve ser usada proteção respiratória?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(39, 'zh', '什么时候应使用呼吸防护用品？', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(40, 'en', 'Can any mask protect against any contaminant?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(40, 'es', '¿Puede cualquier mascarilla proteger frente a cualquier contaminante?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(40, 'fr', 'N\'importe quel masque peut-il protéger contre n\'importe quel contaminant ?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(40, 'pt', 'Qualquer máscara pode proteger contra qualquer contaminante?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(40, 'zh', '任何口罩都能防护所有污染物吗？', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(41, 'en', 'Before welding or cutting, what should be checked?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(41, 'es', 'Antes de realizar soldadura o corte, ¿qué debe verificarse?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(41, 'fr', 'Avant des travaux de soudage ou de découpe, que faut-il vérifier ?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(41, 'pt', 'Antes de realizar soldagem ou corte, o que deve ser verificado?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(41, 'zh', '进行焊接或切割作业前，应检查什么？', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(42, 'en', 'What characterizes a confined space?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(42, 'es', '¿Qué caracteriza a un espacio confinado?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(42, 'fr', 'Qu\'est-ce qui caractérise un espace confiné ?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(42, 'pt', 'O que caracteriza um espaço confinado?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(42, 'zh', '密闭空间通常具有什么特征？', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(43, 'en', 'Should you enter a confined space without authorization or prior assessment?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(43, 'es', '¿Debe ingresarse a un espacio confinado sin autorización ni evaluación previa?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(43, 'fr', 'Faut-il entrer dans un espace confiné sans autorisation ni évaluation préalable ?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(43, 'pt', 'Deve-se entrar em um espaço confinado sem autorização nem avaliação prévia?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(43, 'zh', '可以在没有授权和事先评估的情况下进入密闭空间吗？', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(44, 'en', 'When driving a company vehicle, which behavior is safe?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(44, 'es', 'Al conducir un vehículo de la empresa, ¿qué conducta es segura?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(44, 'fr', 'Lors de la conduite d\'un véhicule de l\'entreprise, quel comportement est sûr ?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(44, 'pt', 'Ao dirigir um veículo da empresa, qual conduta é segura?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(44, 'zh', '驾驶公司车辆时，哪种行为是安全的？', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(45, 'en', 'If a driver feels very drowsy, what should they do?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(45, 'es', 'Si un conductor siente somnolencia intensa, ¿qué debe hacer?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(45, 'fr', 'Si un conducteur ressent une forte somnolence, que doit-il faire ?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(45, 'pt', 'Se um motorista sentir muita sonolência, o que deve fazer?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(45, 'zh', '如果驾驶员感到严重困倦，应怎么做？', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(46, 'en', 'In areas where forklifts operate, what should a pedestrian do?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(46, 'es', 'En zonas con grúas horquilla, ¿qué debe hacer un peatón?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(46, 'fr', 'Dans les zones où circulent des chariots élévateurs, que doit faire un piéton ?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(46, 'pt', 'Em áreas com empilhadeiras, o que um pedestre deve fazer?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(46, 'zh', '在叉车作业区域，行人应怎么做？', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(47, 'en', 'Is it safe to pass under a suspended load?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(47, 'es', '¿Es seguro pasar bajo una carga suspendida?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(47, 'fr', 'Est-il sûr de passer sous une charge suspendue ?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(47, 'pt', 'É seguro passar sob uma carga suspensa?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(47, 'zh', '从悬吊载荷下方通过安全吗？', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(48, 'en', 'What is a benefit of maintaining good housekeeping?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(48, 'es', '¿Qué beneficio tiene mantener orden y aseo?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(48, 'fr', 'Quel est l\'un des avantages du maintien de l\'ordre et de la propreté ?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(48, 'pt', 'Qual é um benefício de manter ordem e limpeza?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(48, 'zh', '保持良好整理整顿和清洁有什么好处？', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(49, 'en', 'Where should materials be stored?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(49, 'es', '¿Dónde deben almacenarse los materiales?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(49, 'fr', 'Où les matériaux doivent-ils être stockés ?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(49, 'pt', 'Onde os materiais devem ser armazenados?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(49, 'zh', '材料应存放在哪里？', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(50, 'en', 'Before using a fall-arrest harness, what should be done?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(50, 'es', 'Antes de usar un arnés contra caídas, ¿qué corresponde?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(50, 'fr', 'Avant d\'utiliser un harnais antichute, que faut-il faire ?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(50, 'pt', 'Antes de usar um cinturão/arnês contra quedas, o que deve ser feito?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(50, 'zh', '使用防坠落安全带前，应怎么做？', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(51, 'en', 'Which best describes the hierarchy of controls?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(51, 'es', '¿Cuál describe mejor la jerarquía de controles?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(51, 'fr', 'Quelle proposition décrit le mieux la hiérarchie des mesures de prévention ?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(51, 'pt', 'Qual opção descreve melhor a hierarquia de controles?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(51, 'zh', '哪一项最能说明控制措施的层级？', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(52, 'en', 'If a hazard can be completely removed, what type of control is it?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(52, 'es', 'Si es posible eliminar completamente un peligro, ¿qué tipo de control es?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(52, 'fr', 'S\'il est possible d\'éliminer complètement un danger, de quel type de mesure s\'agit-il ?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(52, 'pt', 'Se for possível eliminar completamente um perigo, que tipo de controle é esse?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(52, 'zh', '如果能够彻底消除某项危险，这属于哪类控制措施？', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(53, 'en', 'What is the purpose of a safe work procedure?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(53, 'es', '¿Para qué sirve un procedimiento de trabajo seguro?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(53, 'fr', 'À quoi sert une procédure de travail sûr ?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(53, 'pt', 'Para que serve um procedimento de trabalho seguro?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(53, 'zh', '安全作业程序的作用是什么？', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(54, 'en', 'If you are not trained or authorized for a task, what should you do?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(54, 'es', 'Si no tienes capacitación o autorización para una tarea, ¿qué debes hacer?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(54, 'fr', 'Si vous n\'êtes ni formé ni autorisé pour une tâche, que devez-vous faire ?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(54, 'pt', 'Se você não tiver treinamento ou autorização para uma tarefa, o que deve fazer?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(54, 'zh', '如果你未接受某项任务所需的培训或授权，应怎么做？', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(55, 'en', 'If equipment has a fault that may affect safety, what should be done?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(55, 'es', 'Si un equipo tiene una falla que puede afectar la seguridad, ¿qué corresponde?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(55, 'fr', 'Si un équipement présente une défaillance pouvant affecter la sécurité, que faut-il faire ?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(55, 'pt', 'Se um equipamento tiver uma falha que pode afetar a segurança, o que deve ser feito?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(55, 'zh', '如果设备故障可能影响安全，应怎么做？', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(56, 'en', 'What is the purpose of a safety briefing before a task?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(56, 'es', '¿Para qué sirve una charla de seguridad antes de una tarea?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(56, 'fr', 'À quoi sert un briefing de sécurité avant une tâche ?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(56, 'pt', 'Para que serve uma conversa de segurança antes de uma tarefa?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(56, 'zh', '作业前安全交底的目的是什么？', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(57, 'en', 'If the conditions of an already assessed task change, what should be done?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(57, 'es', 'Si cambian las condiciones de una tarea ya evaluada, ¿qué corresponde?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(57, 'fr', 'Si les conditions d\'une tâche déjà évaluée changent, que faut-il faire ?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(57, 'pt', 'Se as condições de uma tarefa já avaliada mudarem, o que deve ser feito?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(57, 'zh', '如果已经评估过的作业条件发生变化，应怎么做？', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(58, 'en', 'What is a safety sign or barrier used for?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(58, 'es', '¿Para qué sirve una señal o barrera de seguridad?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(58, 'fr', 'À quoi sert un panneau ou une barrière de sécurité ?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(58, 'pt', 'Para que serve uma placa ou barreira de segurança?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(58, 'zh', '安全标志或隔离栏的作用是什么？', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(59, 'en', 'If an area is restricted because of a hazard and you are not authorized, what should you do?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(59, 'es', 'Si una zona está delimitada por riesgo y no estás autorizado, ¿qué debes hacer?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(59, 'fr', 'Si une zone est délimitée en raison d\'un risque et que vous n\'êtes pas autorisé, que devez-vous faire ?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(59, 'pt', 'Se uma área estiver delimitada por risco e você não estiver autorizado, o que deve fazer?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(59, 'zh', '如果某区域因危险而被隔离，而你未获授权，应怎么做？', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(60, 'en', 'Why is PPE considered the last line of control?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(60, 'es', '¿Por qué el EPP se considera una última barrera de control?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(60, 'fr', 'Pourquoi les EPI sont-ils considérés comme la dernière barrière de contrôle ?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(60, 'pt', 'Por que o EPI é considerado a última barreira de controle?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(60, 'zh', '为什么 PPE 被视为最后一道控制屏障？', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(61, 'en', 'During a workday with high temperatures, what helps prevent heat stress?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(61, 'es', 'En una jornada con altas temperaturas, ¿qué ayuda a prevenir estrés térmico?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(61, 'fr', 'Lors d\'une journée de travail sous fortes températures, qu\'est-ce qui aide à prévenir le stress thermique ?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(61, 'pt', 'Em uma jornada com altas temperaturas, o que ajuda a prevenir estresse térmico?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(61, 'zh', '在高温环境下工作时，什么措施有助于预防热应激？', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(62, 'en', 'For work involving sun exposure, which measure is appropriate?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(62, 'es', 'En labores con exposición solar, ¿qué medida es apropiada?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(62, 'fr', 'Pour des travaux avec exposition au soleil, quelle mesure est appropriée ?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(62, 'pt', 'Em atividades com exposição solar, qual medida é apropriada?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(62, 'zh', '进行日晒暴露作业时，哪项措施合适？', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(63, 'en', 'In cold environments, which measure is appropriate?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(63, 'es', 'En ambientes fríos, ¿qué medida es adecuada?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(63, 'fr', 'Dans un environnement froid, quelle mesure est adaptée ?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(63, 'pt', 'Em ambientes frios, qual medida é adequada?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(63, 'zh', '在寒冷环境中，哪项措施合适？', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(64, 'en', 'Why is fatigue a safety risk?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(64, 'es', '¿Por qué la fatiga es un riesgo de seguridad?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(64, 'fr', 'Pourquoi la fatigue constitue-t-elle un risque pour la sécurité ?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(64, 'pt', 'Por que a fadiga é um risco de segurança?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(64, 'zh', '为什么疲劳是一项安全风险？', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(65, 'en', 'If symptoms appear that may be work-related, what should be done?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(65, 'es', 'Si aparecen síntomas que podrían estar relacionados con el trabajo, ¿qué corresponde?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(65, 'fr', 'Si des symptômes pouvant être liés au travail apparaissent, que faut-il faire ?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(65, 'pt', 'Se surgirem sintomas que possam estar relacionados ao trabalho, o que deve ser feito?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(65, 'zh', '如果出现可能与工作有关的症状，应怎么做？', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(66, 'en', 'What is an occupational disease?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(66, 'es', '¿Qué es una enfermedad profesional u ocupacional?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(66, 'fr', 'Qu\'est-ce qu\'une maladie professionnelle ?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(66, 'pt', 'O que é uma doença ocupacional ou profissional?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(66, 'zh', '什么是职业病或职业性疾病？', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(67, 'en', 'Why is it important to participate in occupational health surveillance when required?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(67, 'es', '¿Por qué es importante participar en vigilancia de salud ocupacional cuando corresponda?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(67, 'fr', 'Pourquoi est-il important de participer à la surveillance de la santé au travail lorsqu\'elle est requise ?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(67, 'pt', 'Por que é importante participar da vigilância de saúde ocupacional quando aplicável?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(67, 'zh', '为什么在需要时参加职业健康监护很重要？', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(68, 'en', 'Which basic measure helps prevent the spread of communicable diseases?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(68, 'es', '¿Qué medida básica ayuda a prevenir contagios de enfermedades transmisibles?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(68, 'fr', 'Quelle mesure de base aide à prévenir la transmission des maladies transmissibles ?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(68, 'pt', 'Qual medida básica ajuda a prevenir o contágio de doenças transmissíveis?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(68, 'zh', '哪项基本措施有助于防止传染性疾病传播？', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(69, 'en', 'What should be done with hazardous or special waste?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(69, 'es', '¿Qué debe hacerse con residuos peligrosos o especiales?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(69, 'fr', 'Que faut-il faire avec les déchets dangereux ou spéciaux ?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(69, 'pt', 'O que deve ser feito com resíduos perigosos ou especiais?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(69, 'zh', '危险废物或特殊废物应如何处理？', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(70, 'en', 'If there is a chemical spill that you are not trained to control, what should you do?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(70, 'es', 'Ante un derrame químico que no estás capacitado para controlar, ¿qué corresponde?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(70, 'fr', 'Face à un déversement chimique que vous n\'êtes pas formé à maîtriser, que devez-vous faire ?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(70, 'pt', 'Diante de um derramamento químico que você não está treinado para controlar, o que deve ser feito?', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34'),
(70, 'zh', '发生你未受训且无法控制的化学品泄漏时，应怎么做？', 'migration-e3-vs1', '2026-10-08 19:19:34', '2026-10-08 19:19:34');

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
(96, 'programs.tracking', 'Crear y corregir seguimiento mensual de programas.'),
(97, 'health_profile.view_emergency', 'Consultar antecedentes de salud necesarios para responder a una emergencia.');

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
(1, 89),
(1, 90),
(1, 92),
(1, 93),
(1, 94),
(1, 95),
(1, 96),
(1, 97),
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
(2, 89),
(2, 90),
(2, 92),
(2, 93),
(2, 94),
(2, 95),
(2, 96),
(2, 97),
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
(3, 85),
(3, 86),
(3, 88),
(3, 89),
(3, 90),
(3, 92),
(3, 93),
(3, 94),
(3, 95),
(3, 96),
(3, 97),
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
(4, 85),
(4, 86),
(4, 88),
(4, 89),
(4, 90),
(4, 92),
(4, 93),
(4, 94),
(4, 95),
(4, 96),
(4, 97),
(5, 42),
(5, 49),
(5, 89),
(6, 42),
(6, 49),
(6, 89),
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
(8, 89),
(8, 90),
(8, 91),
(8, 92),
(8, 93),
(8, 94),
(8, 95),
(8, 96),
(8, 97),
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
(9, 89),
(9, 90),
(9, 91),
(9, 92),
(9, 93),
(9, 94),
(9, 95),
(9, 96),
(9, 97),
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
(11, 89),
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
(12, 89),
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
(14, 89),
(14, 90),
(14, 92),
(14, 93),
(14, 94),
(14, 95),
(14, 96),
(14, 97),
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
(15, 89),
(15, 90),
(15, 91),
(15, 92),
(15, 93),
(15, 94),
(15, 95),
(15, 96),
(15, 97),
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
(16, 85),
(16, 86),
(16, 88),
(16, 89),
(16, 90),
(16, 92),
(16, 93),
(16, 94),
(16, 95),
(16, 96),
(16, 97),
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
(17, 89),
(17, 90),
(17, 92),
(17, 93),
(17, 94),
(17, 95),
(17, 96),
(18, 42),
(18, 49),
(18, 89),
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
(19, 89),
(19, 90),
(19, 92),
(19, 93),
(19, 94),
(19, 95),
(19, 96),
(19, 97),
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
(20, 89),
(20, 90),
(20, 91),
(20, 92),
(20, 93),
(20, 94),
(20, 95),
(20, 96),
(20, 97),
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
(21, 85),
(21, 86),
(21, 88),
(21, 89),
(21, 90),
(21, 92),
(21, 93),
(21, 94),
(21, 95),
(21, 96),
(21, 97),
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
(22, 89),
(22, 90),
(22, 92),
(22, 93),
(22, 94),
(22, 95),
(22, 96),
(23, 42),
(23, 49),
(23, 89),
(24, 4),
(24, 17),
(24, 23),
(24, 29),
(24, 34),
(24, 42),
(24, 49),
(24, 51),
(24, 57),
(24, 59),
(24, 65),
(24, 67),
(24, 73),
(24, 74),
(24, 76),
(24, 86),
(24, 89),
(24, 92),
(25, 4),
(25, 17),
(25, 23),
(25, 29),
(25, 34),
(25, 42),
(25, 49),
(25, 51),
(25, 57),
(25, 59),
(25, 65),
(25, 67),
(25, 73),
(25, 74),
(25, 76),
(25, 86),
(25, 89),
(25, 92),
(26, 4),
(26, 17),
(26, 23),
(26, 29),
(26, 34),
(26, 42),
(26, 49),
(26, 51),
(26, 57),
(26, 59),
(26, 65),
(26, 67),
(26, 73),
(26, 74),
(26, 76),
(26, 86),
(26, 89),
(26, 92),
(27, 4),
(27, 17),
(27, 23),
(27, 29),
(27, 34),
(27, 42),
(27, 49),
(27, 51),
(27, 57),
(27, 59),
(27, 65),
(27, 67),
(27, 73),
(27, 74),
(27, 76),
(27, 86),
(27, 89),
(27, 92),
(31, 11),
(31, 42),
(31, 49),
(31, 89),
(32, 11),
(32, 42),
(32, 49),
(32, 89),
(33, 11),
(33, 42),
(33, 49),
(33, 89),
(34, 11),
(34, 42),
(34, 49),
(34, 89),
(38, 11),
(38, 67),
(38, 73),
(38, 74),
(38, 89),
(39, 11),
(39, 67),
(39, 73),
(39, 74),
(39, 89),
(40, 11),
(40, 67),
(40, 73),
(40, 74),
(40, 89),
(41, 11),
(41, 67),
(41, 73),
(41, 74),
(41, 89);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `schema_migrations`
--

CREATE TABLE `schema_migrations` (
  `version` varchar(64) NOT NULL,
  `name` varchar(190) NOT NULL,
  `checksum` varchar(128) DEFAULT NULL,
  `executed_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `schema_migrations`
--

INSERT INTO `schema_migrations` (`version`, `name`, `checksum`, `executed_at`) VALUES
('2026-10-07-e3-vs1', 'E3-VS1 roles, permisos y arquitectura', NULL, '2026-10-07 18:08:04'),
('2026-10-07-e3-vs1-user-worker-jefatura', 'E3-VS1: usuario=trabajador con acceso; jefatura restaurada', NULL, '2026-10-07 19:47:29'),
('2026-10-08-e3-vs1-contractors-onboarding-back', 'E3-VS1: contratistas normalizados y retorno en onboarding', NULL, '2026-10-08 16:21:03'),
('2026-10-08-e3-vs1-mutuality-canonical', 'E3-VS1 funcional: mutualidad canónica ACHS/ISL/IST/Mutual', NULL, '2026-10-08 16:10:35'),
('2026-10-08-e3-vs1-onboarding', 'E3-VS1 onboarding obligatorio + evaluación SST', NULL, '2026-10-08 15:12:03'),
('2026-10-08-e3-vs1-onboarding-assessment-draft', 'E3-VS1: borrador persistente de respuestas del assessment inicial', NULL, '2026-10-08 18:07:14'),
('2026-10-08-e3-vs1-onboarding-health-user-experience', 'E3-VS1: salud integrada al onboarding y experiencia de evaluaciones Usuario', NULL, '2026-10-08 16:55:23'),
('2026-10-08-e3-vs1-onboarding-question-i18n', 'E3-VS1: 70 preguntas onboarding en 5 idiomas y administración multidioma', NULL, '2026-10-08 19:19:39'),
('2026-10-08-e3-vs1-profile-completion-extended', 'E3-VS1: antecedentes extendidos del perfil inicial', NULL, '2026-10-08 15:48:40'),
('2026-10-08-e3-vs1-user-management-scope', 'E3-VS1: alcance global/empresa/asignados para gestión de usuarios', NULL, '2026-10-08 23:58:02');

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
  `mutual_code` varchar(20) DEFAULT NULL,
  `last_access` datetime NOT NULL,
  `created_by` varchar(50) NOT NULL,
  `date_create` datetime NOT NULL,
  `last_update` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id_users`, `id_company`, `id_worker`, `name`, `lastname`, `rut`, `state`, `language`, `profile_photo_path`, `mutual_code`, `last_access`, `created_by`, `date_create`, `last_update`) VALUES
('admin.completo.test@test.helheim.cl', 1, NULL, 'Diego', 'Herrera', '18102198-5', 1, 'es', NULL, NULL, '2026-09-08 15:00:26', 'seed_test_data', '2026-08-04 09:00:00', '2026-08-04 09:00:00'),
('admin.completo.test@test.tecaivot.cl', 2, NULL, 'Francisca', 'Muñoz', '18102347-3', 1, 'es', NULL, NULL, '1970-01-01 00:00:00', 'seed_test_data', '2026-08-04 09:30:00', '2026-08-04 09:30:00'),
('admin.test@test.engie.cl', 3, NULL, 'Marcela', 'Vega', '18102701-0', 1, 'es', NULL, NULL, '1970-01-01 00:00:00', 'seed_test_data', '2026-09-08 08:30:00', '2026-09-08 08:30:00'),
('admin.test@test.tecaivot.cl', 2, NULL, 'Andrés', 'Pizarro', '18102439-9', 1, 'es', NULL, NULL, '1970-01-01 00:00:00', 'seed_test_data', '2026-08-04 09:30:00', '2026-08-04 09:30:00'),
('adminEmpresaDemo@demoSCT.cl', 4, 19, 'Administrador', 'DEMO', '44444444-4', 1, 'es', NULL, 'achs', '2026-10-09 00:16:05', 'seed_demo_sct', '2026-09-08 18:43:22', '2026-10-08 19:53:38'),
('barbara.contreras@test.helheim.cl', 1, 1, '', '', '', 1, 'es', NULL, NULL, '1970-01-01 00:00:00', 'seed_test_data', '2026-08-05 09:00:00', '2026-10-09 00:33:11'),
('camila.soto@test.tecaivot.cl', 2, 3, '', '', '', 1, 'es', NULL, NULL, '1970-01-01 00:00:00', 'seed_test_data', '2026-08-05 09:30:00', '2026-10-09 00:33:11'),
('cliente.test@test.helheim.cl', 1, NULL, 'Ignacio', 'Bravo', '18102287-6', 1, 'es', NULL, NULL, '1970-01-01 00:00:00', 'seed_test_data', '2026-08-04 09:00:00', '2026-08-04 09:00:00'),
('ContratistaEmpresaDemo@demoSCT.cl', 4, NULL, 'Contratista', 'DEMO', '77777777-7', 1, 'es', NULL, NULL, '2026-10-07 18:59:51', 'migration_e3_vs1', '2026-10-07 18:08:04', '2026-10-07 18:08:04'),
('cristobal.fuentes@test.engie.cl', 3, 6, '', '', '', 1, 'es', NULL, NULL, '1970-01-01 00:00:00', 'seed_test_data', '2026-09-08 09:00:00', '2026-10-09 00:33:11'),
('fco.fredes.g@gmail.com', 2, NULL, 'francisco', 'fredes', '1234', 1, 'es', NULL, NULL, '2026-09-08 14:44:10', 'phpmyadmin', '2026-08-15 11:58:40', '2026-08-15 11:58:40'),
('francisca.torres@test.engie.cl', 3, 9, '', '', '', 1, 'es', NULL, NULL, '1970-01-01 00:00:00', 'seed_test_data', '2026-09-08 09:00:00', '2026-10-09 00:33:11'),
('Francisco.fredes@engie.com', 3, NULL, '', '', '', 1, 'es', NULL, NULL, '2026-09-08 01:13:19', 'phpmyadmin', '2026-09-08 01:13:19', '2026-10-09 00:33:11'),
('GerenteEmpresaDemo@demoSCT.cl', 4, 16, 'Gerente', 'DEMO', '11111111-1', 1, 'es', NULL, 'achs', '2026-10-09 00:07:00', 'seed_demo_sct', '2026-09-08 18:43:22', '2026-10-08 23:06:13'),
('javiera.reyes@test.engie.cl', 3, 11, '', '', '', 1, 'es', NULL, NULL, '1970-01-01 00:00:00', 'seed_test_data', '2026-09-08 09:00:00', '2026-10-09 00:33:11'),
('jefatura.test@test.helheim.cl', 1, NULL, '', '', '', 1, 'es', NULL, NULL, '1970-01-01 00:00:00', 'seed_test_data', '2026-08-04 09:00:00', '2026-10-09 00:33:11'),
('jefatura.test@test.tecaivot.cl', 2, NULL, '', '', '', 1, 'es', NULL, NULL, '1970-01-01 00:00:00', 'seed_test_data', '2026-08-04 09:30:00', '2026-10-09 00:33:11'),
('JefaturaEmpresaDemo@demoSCT.cl', 4, 17, 'Jefatura', 'DEMO', '22222222-2', 1, 'es', NULL, 'achs', '2026-10-09 00:15:36', 'seed_demo_sct', '2026-09-08 18:43:22', '2026-10-08 19:56:40'),
('jonathan.vera@engie.com', 3, NULL, 'Jonathan', 'Vera', '18102935-8', 1, 'es', NULL, NULL, '2026-09-08 01:11:21', 'phpmyadmin', '2026-09-08 01:11:21', '2026-09-08 01:11:21'),
('juanantonioconchaloyola@gmail.com', 1, NULL, 'antonio', 'helheim', '16725278-8', 1, 'es', 'uploads/usuarios/user_f4bcfaca519e256591fc7ccfd7734a32066c00d3302d3371653a5112e5208874_514070f45bc8a5fd.png', NULL, '2026-09-10 01:01:13', 'phpmyadmin', '2026-08-14 20:46:30', '2026-09-08 16:47:54'),
('Laura.Lira@external.engie.com', 3, NULL, '', '', '', 1, 'es', NULL, NULL, '2026-09-08 01:12:37', 'phpmyadmin', '2026-09-08 01:12:37', '2026-10-09 00:33:11'),
('malazga99@gmail.com', 1, NULL, 'maite', 'lazcano', '20153481-k', 1, 'es', NULL, NULL, '2026-09-08 15:53:29', 'phpmyadmin', '2026-08-14 21:41:57', '2026-08-14 21:41:57'),
('pablotroncoso@gmail.com', 1, NULL, 'pablo', 'troncoso', '123456789', 1, 'es', NULL, NULL, '2026-09-08 14:48:40', 'phpmyadmin', '2026-08-14 21:43:14', '2026-08-14 21:43:14'),
('ParamedicoEmpresaDemo@demoSCT.cl', 4, NULL, 'Paramédico', 'DEMO', '66666666-6', 1, 'es', NULL, NULL, '2026-10-07 19:01:56', 'migration_e3_vs1', '2026-10-07 18:08:04', '2026-10-07 18:08:04'),
('patricio.gomez@test.engie.cl', 3, 7, '', '', '', 1, 'es', NULL, NULL, '1970-01-01 00:00:00', 'seed_test_data', '2026-09-08 09:00:00', '2026-10-09 00:33:11'),
('SuperUsuarioDemo@demoSCT.cl', 4, NULL, 'SuperUsuario', 'DEMO', '55555555-5', 1, 'es', NULL, NULL, '2026-10-07 19:22:14', 'migration_e3_vs1', '2026-10-07 18:08:04', '2026-10-07 18:08:04'),
('TrabajadorEmpresaDemo@demoSCT.cl', 4, NULL, 'Trabajador', 'DEMO', '88888888-8', 0, 'es', NULL, NULL, '2026-10-07 19:18:31', 'migration_e3_vs1', '2026-10-07 18:08:04', '2026-10-08 15:48:23'),
('UsuarioEmpresaDemo@demoSCT.cl', 4, 18, 'Usuario', 'DEMO', '1234578-5', 1, 'es', NULL, 'achs', '2026-10-09 00:33:26', 'seed_demo_sct', '2026-09-08 18:43:22', '2026-10-09 00:34:03'),
('UsuarioNuevoEmpresaDemo@demoSCT.cl', 4, 26, 'Usuario2', 'DEMO2', '1234587-5', 1, 'en', NULL, NULL, '2026-10-08 23:20:17', 'migration_e3_vs1_onboarding', '2026-10-08 15:12:03', '2026-10-09 00:33:11'),
('wagner.leite@engie.com', 3, NULL, 'Wagner', 'Leite', '18103115-8', 1, 'es', NULL, NULL, '2026-09-08 01:10:12', 'phpmyadmin', '2026-09-08 01:10:12', '2026-09-08 01:10:12');

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
(8, 'barbara.contreras@test.helheim.cl', 6, 0, 'seed_test_data', '2026-08-05 09:00:00', '2026-10-07 19:47:29'),
(9, 'camila.soto@test.tecaivot.cl', 5, 0, 'seed_test_data', '2026-08-05 09:30:00', '2026-10-07 19:47:29'),
(10, 'cristobal.fuentes@test.engie.cl', 25, 1, 'seed_test_data', '2026-09-08 09:00:00', '2026-10-07 18:08:04'),
(11, 'patricio.gomez@test.engie.cl', 18, 0, 'seed_test_data', '2026-09-08 09:00:00', '2026-10-07 19:47:29'),
(12, 'francisca.torres@test.engie.cl', 18, 0, 'seed_test_data', '2026-09-08 09:00:00', '2026-10-07 19:47:29'),
(13, 'javiera.reyes@test.engie.cl', 25, 1, 'seed_test_data', '2026-09-08 09:00:00', '2026-10-07 18:08:04'),
(14, 'admin.completo.test@test.helheim.cl', 9, 1, 'seed_test_data', '2026-08-04 09:00:00', '2026-08-04 09:00:00'),
(15, 'jefatura.test@test.helheim.cl', 26, 1, 'seed_test_data', '2026-08-04 09:00:00', '2026-10-07 18:08:04'),
(16, 'cliente.test@test.helheim.cl', 4, 1, 'seed_test_data', '2026-08-04 09:00:00', '2026-08-04 09:00:00'),
(17, 'admin.completo.test@test.tecaivot.cl', 8, 1, 'seed_test_data', '2026-08-04 09:30:00', '2026-08-04 09:30:00'),
(18, 'admin.test@test.tecaivot.cl', 1, 1, 'seed_test_data', '2026-08-04 09:30:00', '2026-08-04 09:30:00'),
(19, 'jefatura.test@test.tecaivot.cl', 24, 1, 'seed_test_data', '2026-08-04 09:30:00', '2026-10-07 18:08:04'),
(20, 'admin.test@test.engie.cl', 14, 1, 'seed_test_data', '2026-09-08 08:30:00', '2026-09-08 08:30:00'),
(21, 'Francisco.fredes@engie.com', 25, 1, 'seed_test_data', '2026-09-08 08:00:00', '2026-10-07 18:08:04'),
(22, 'jonathan.vera@engie.com', 16, 1, 'seed_test_data', '2026-09-08 08:00:00', '2026-09-08 08:00:00'),
(23, 'wagner.leite@engie.com', 14, 1, 'seed_test_data', '2026-09-08 08:00:00', '2026-09-08 08:00:00'),
(24, 'Laura.Lira@external.engie.com', 18, 0, 'seed_test_data', '2026-09-08 08:00:00', '2026-10-07 19:47:29'),
(25, 'GerenteEmpresaDemo@demoSCT.cl', 21, 1, 'seed_demo_sct', '2026-09-08 18:43:22', '2026-09-08 18:43:22'),
(26, 'JefaturaEmpresaDemo@demoSCT.cl', 27, 0, 'seed_demo_sct', '2026-09-08 18:43:22', '2026-10-08 15:48:14'),
(27, 'UsuarioEmpresaDemo@demoSCT.cl', 27, 1, 'seed_demo_sct', '2026-09-08 18:43:22', '2026-10-08 15:48:14'),
(28, 'adminEmpresaDemo@demoSCT.cl', 19, 1, 'seed_demo_sct', '2026-09-08 18:43:22', '2026-09-08 18:43:22'),
(29, 'SuperUsuarioDemo@demoSCT.cl', 20, 1, 'migration_e3_vs1', '2026-10-07 18:08:04', '2026-10-07 18:08:04'),
(30, 'ParamedicoEmpresaDemo@demoSCT.cl', 34, 1, 'migration_e3_vs1', '2026-10-07 18:08:04', '2026-10-07 18:08:04'),
(31, 'ContratistaEmpresaDemo@demoSCT.cl', 41, 1, 'migration_e3_vs1', '2026-10-07 18:08:04', '2026-10-07 18:08:04'),
(32, 'TrabajadorEmpresaDemo@demoSCT.cl', 23, 0, 'migration_e3_vs1', '2026-10-07 18:08:04', '2026-10-08 15:48:23'),
(33, 'barbara.contreras@test.helheim.cl', 26, 1, 'migration_e3_vs1_user_worker', '2026-10-07 19:47:29', '2026-10-07 19:47:29'),
(34, 'camila.soto@test.tecaivot.cl', 24, 1, 'migration_e3_vs1_user_worker', '2026-10-07 19:47:29', '2026-10-07 19:47:29'),
(35, 'patricio.gomez@test.engie.cl', 25, 1, 'migration_e3_vs1_user_worker', '2026-10-07 19:47:29', '2026-10-07 19:47:29'),
(36, 'francisca.torres@test.engie.cl', 25, 1, 'migration_e3_vs1_user_worker', '2026-10-07 19:47:29', '2026-10-07 19:47:29'),
(37, 'Laura.Lira@external.engie.com', 25, 1, 'migration_e3_vs1_user_worker', '2026-10-07 19:47:29', '2026-10-07 19:47:29'),
(38, 'TrabajadorEmpresaDemo@demoSCT.cl', 27, 0, 'migration_e3_vs1_user_worker', '2026-10-07 19:47:29', '2026-10-08 15:48:23'),
(40, 'JefaturaEmpresaDemo@demoSCT.cl', 27, 0, 'migration_e3_vs1_user_worker', '2026-10-07 19:47:29', '2026-10-08 15:48:14'),
(41, 'UsuarioNuevoEmpresaDemo@demoSCT.cl', 27, 1, 'migration_e3_vs1_onboarding', '2026-10-08 15:12:03', '2026-10-08 15:12:03'),
(42, 'JefaturaEmpresaDemo@demoSCT.cl', 22, 1, 'migration_e3_vs1_user_worker', '2026-10-08 15:48:23', '2026-10-08 15:48:23');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users_role_group`
--

CREATE TABLE `users_role_group` (
  `id_role_group` int(11) NOT NULL,
  `id_company` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` varchar(190) NOT NULL,
  `state` int(11) NOT NULL,
  `create_by` varchar(50) NOT NULL,
  `date_create` datetime NOT NULL,
  `last_update` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `users_role_group`
--

INSERT INTO `users_role_group` (`id_role_group`, `id_company`, `name`, `description`, `state`, `create_by`, `date_create`, `last_update`) VALUES
(1, 2, 'administrador_cliente', 'Administrador cliente de empresa', 1, 'migracion', '2026-08-20 17:42:42', '2026-10-07 18:08:04'),
(2, 1, 'administrador_cliente', 'Administrador cliente de empresa', 1, 'migracion', '2026-08-20 17:42:42', '2026-10-07 18:08:04'),
(3, 2, 'gerente', 'Gerente de empresa: control total de su empresa', 1, 'migracion', '2026-08-20 17:42:42', '2026-10-07 18:08:04'),
(4, 1, 'gerente', 'Gerente de empresa: control total de su empresa', 1, 'migracion', '2026-08-20 17:42:42', '2026-10-07 18:08:04'),
(5, 2, 'trabajador', 'LEGACY: trabajador es entidad workers; acceso se representa con rol usuario', 0, 'migracion', '2026-08-20 17:42:42', '2026-10-08 15:48:23'),
(6, 1, 'trabajador', 'LEGACY: trabajador es entidad workers; acceso se representa con rol usuario', 0, 'migracion', '2026-08-20 17:42:42', '2026-10-08 15:48:23'),
(8, 2, 'superusuario', 'Acceso global a todo SCT', 1, 'migracion_safetyco', '2026-09-03 18:45:55', '2026-10-07 18:08:04'),
(9, 1, 'superusuario', 'Acceso global a todo SCT', 1, 'migracion_safetyco', '2026-09-03 18:45:55', '2026-10-07 18:08:04'),
(11, 2, 'jefatura', 'Jefatura de empresa', 1, 'migracion_safetyco', '2026-09-03 18:45:55', '2026-10-08 15:48:23'),
(12, 1, 'jefatura', 'Jefatura de empresa', 1, 'migracion_safetyco', '2026-09-03 18:45:55', '2026-10-08 15:48:23'),
(13, 3, 'Representante de la empresa cliente', 'perfil de prueba mvp', 1, 'phpmyadmin', '2026-09-08 01:06:24', '2026-09-08 01:06:24'),
(14, 3, 'administrador_cliente', 'Administrador cliente de empresa', 1, 'seed_test_data', '2026-09-08 08:00:00', '2026-10-07 18:08:04'),
(15, 3, 'superusuario', 'Acceso global a todo SCT', 1, 'seed_test_data', '2026-09-08 08:00:00', '2026-10-07 18:08:04'),
(16, 3, 'gerente', 'Gerente de empresa: control total de su empresa', 1, 'seed_test_data', '2026-09-08 08:00:00', '2026-10-07 18:08:04'),
(17, 3, 'jefatura', 'Jefatura de empresa', 1, 'seed_test_data', '2026-09-08 08:00:00', '2026-10-08 15:48:23'),
(18, 3, 'trabajador', 'LEGACY: trabajador es entidad workers; acceso se representa con rol usuario', 0, 'seed_test_data', '2026-09-08 08:00:00', '2026-10-08 15:48:23'),
(19, 4, 'administrador_cliente', 'Administrador cliente de empresa', 1, 'seed_demo_sct', '2026-09-08 18:43:22', '2026-10-07 18:08:04'),
(20, 4, 'superusuario', 'Acceso global a todo SCT', 1, 'seed_demo_sct', '2026-09-08 18:43:22', '2026-10-07 18:08:04'),
(21, 4, 'gerente', 'Gerente de empresa: control total de su empresa', 1, 'seed_demo_sct', '2026-09-08 18:43:22', '2026-10-07 18:08:04'),
(22, 4, 'jefatura', 'Jefatura de empresa', 1, 'seed_demo_sct', '2026-09-08 18:43:22', '2026-10-08 15:48:23'),
(23, 4, 'trabajador', 'LEGACY: trabajador es entidad workers; acceso se representa con rol usuario', 0, 'seed_demo_sct', '2026-09-08 18:43:22', '2026-10-08 15:48:23'),
(24, 2, 'usuario', 'Trabajador con cuenta de acceso a SCT', 1, 'migration_e3_vs1', '2026-10-07 18:08:04', '2026-10-08 15:48:23'),
(25, 3, 'usuario', 'Trabajador con cuenta de acceso a SCT', 1, 'migration_e3_vs1', '2026-10-07 18:08:04', '2026-10-08 15:48:23'),
(26, 1, 'usuario', 'Trabajador con cuenta de acceso a SCT', 1, 'migration_e3_vs1', '2026-10-07 18:08:04', '2026-10-08 15:48:23'),
(27, 4, 'usuario', 'Trabajador con cuenta de acceso a SCT', 1, 'migration_e3_vs1', '2026-10-07 18:08:04', '2026-10-08 15:48:23'),
(31, 2, 'paramedico', 'Paramédico: perfil propio e inducción', 1, 'migration_e3_vs1', '2026-10-07 18:08:04', '2026-10-07 18:08:04'),
(32, 3, 'paramedico', 'Paramédico: perfil propio e inducción', 1, 'migration_e3_vs1', '2026-10-07 18:08:04', '2026-10-07 18:08:04'),
(33, 1, 'paramedico', 'Paramédico: perfil propio e inducción', 1, 'migration_e3_vs1', '2026-10-07 18:08:04', '2026-10-07 18:08:04'),
(34, 4, 'paramedico', 'Paramédico: perfil propio e inducción', 1, 'migration_e3_vs1', '2026-10-07 18:08:04', '2026-10-07 18:08:04'),
(38, 2, 'contratista', 'Contratista: documentos de contratistas/subcontratistas', 1, 'migration_e3_vs1', '2026-10-07 18:08:04', '2026-10-07 18:08:04'),
(39, 3, 'contratista', 'Contratista: documentos de contratistas/subcontratistas', 1, 'migration_e3_vs1', '2026-10-07 18:08:04', '2026-10-07 18:08:04'),
(40, 1, 'contratista', 'Contratista: documentos de contratistas/subcontratistas', 1, 'migration_e3_vs1', '2026-10-07 18:08:04', '2026-10-07 18:08:04'),
(41, 4, 'contratista', 'Contratista: documentos de contratistas/subcontratistas', 1, 'migration_e3_vs1', '2026-10-07 18:08:04', '2026-10-07 18:08:04');

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
('ContratistaEmpresaDemo@demoSCT.cl', '$2y$10$o2m7YLZ9eGhhMUreqVNtg.rS09LHgSDmxniGjXazWaH9eaVvaVKLq', 'active', '2026-10-07 18:08:04', NULL, '2026-10-07 18:08:04', '2026-10-07 18:08:04'),
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
('ParamedicoEmpresaDemo@demoSCT.cl', '$2y$10$o2m7YLZ9eGhhMUreqVNtg.rS09LHgSDmxniGjXazWaH9eaVvaVKLq', 'active', '2026-10-07 18:08:04', NULL, '2026-10-07 18:08:04', '2026-10-07 18:08:04'),
('patricio.gomez@test.engie.cl', NULL, 'pending_activation', NULL, NULL, '2026-09-08 14:04:46', '2026-09-08 14:04:46'),
('SuperUsuarioDemo@demoSCT.cl', '$2y$10$o2m7YLZ9eGhhMUreqVNtg.rS09LHgSDmxniGjXazWaH9eaVvaVKLq', 'active', '2026-10-07 18:08:04', NULL, '2026-10-07 18:08:04', '2026-10-07 18:08:04'),
('TrabajadorEmpresaDemo@demoSCT.cl', '$2y$10$o2m7YLZ9eGhhMUreqVNtg.rS09LHgSDmxniGjXazWaH9eaVvaVKLq', 'active', '2026-10-07 18:08:04', NULL, '2026-10-07 18:08:04', '2026-10-07 18:08:04'),
('UsuarioEmpresaDemo@demoSCT.cl', '$2y$10$o2m7YLZ9eGhhMUreqVNtg.rS09LHgSDmxniGjXazWaH9eaVvaVKLq', 'active', '2026-09-08 18:43:22', NULL, '2026-09-08 18:43:22', '2026-09-08 18:43:22'),
('UsuarioNuevoEmpresaDemo@demoSCT.cl', '$2y$10$o2m7YLZ9eGhhMUreqVNtg.rS09LHgSDmxniGjXazWaH9eaVvaVKLq', 'active', '2026-09-08 18:43:22', NULL, '2026-10-08 15:12:03', '2026-10-08 15:12:03'),
('wagner.leite@engie.com', NULL, 'pending_activation', NULL, NULL, '2026-09-08 14:04:46', '2026-09-08 14:04:46');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `user_data_consent`
--

CREATE TABLE `user_data_consent` (
  `id_consent` int(11) NOT NULL,
  `id_users` varchar(50) NOT NULL,
  `policy_version` varchar(50) NOT NULL,
  `policy_language` varchar(5) NOT NULL DEFAULT 'es',
  `policy_hash` char(64) NOT NULL,
  `accepted_at` datetime NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `user_data_consent`
--

INSERT INTO `user_data_consent` (`id_consent`, `id_users`, `policy_version`, `policy_language`, `policy_hash`, `accepted_at`, `ip_address`, `user_agent`) VALUES
(10, 'adminEmpresaDemo@demoSCT.cl', 'SCT-UNIFIED-CONSENT-v2.1', 'es', 'b7d8c1a0d477753a2c352c5236f9788fe44ace7febcbfb8e6cabf5f053be3faa', '2026-10-08 19:45:29', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36'),
(11, 'JefaturaEmpresaDemo@demoSCT.cl', 'SCT-UNIFIED-CONSENT-v2.1', 'es', 'b7d8c1a0d477753a2c352c5236f9788fe44ace7febcbfb8e6cabf5f053be3faa', '2026-10-08 19:56:40', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36'),
(13, 'GerenteEmpresaDemo@demoSCT.cl', 'SCT-UNIFIED-CONSENT-v2.1', 'es', 'b7d8c1a0d477753a2c352c5236f9788fe44ace7febcbfb8e6cabf5f053be3faa', '2026-10-08 23:06:13', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36'),
(14, 'UsuarioEmpresaDemo@demoSCT.cl', 'SCT-UNIFIED-CONSENT-v2.1', 'es', 'b7d8c1a0d477753a2c352c5236f9788fe44ace7febcbfb8e6cabf5f053be3faa', '2026-10-09 00:34:03', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `user_onboarding`
--

CREATE TABLE `user_onboarding` (
  `id_users` varchar(50) NOT NULL,
  `profile_completed_at` datetime DEFAULT NULL,
  `assessment_completed_at` datetime DEFAULT NULL,
  `assessment_score` decimal(5,2) DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `date_create` datetime NOT NULL DEFAULT current_timestamp(),
  `last_update` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `user_onboarding`
--

INSERT INTO `user_onboarding` (`id_users`, `profile_completed_at`, `assessment_completed_at`, `assessment_score`, `completed_at`, `date_create`, `last_update`) VALUES
('adminEmpresaDemo@demoSCT.cl', '2026-10-08 19:45:29', '2026-10-08 19:54:04', 46.67, '2026-10-08 19:54:04', '2026-10-08 19:45:01', '2026-10-08 19:54:04'),
('GerenteEmpresaDemo@demoSCT.cl', '2026-10-08 23:06:13', '2026-10-08 23:06:29', 26.67, '2026-10-08 23:06:29', '2026-10-08 23:05:38', '2026-10-08 23:06:29'),
('JefaturaEmpresaDemo@demoSCT.cl', '2026-10-08 19:56:40', '2026-10-08 19:57:13', 6.67, '2026-10-08 19:57:13', '2026-10-08 19:56:08', '2026-10-08 19:57:13'),
('UsuarioEmpresaDemo@demoSCT.cl', '2026-10-09 00:34:03', '2026-10-09 00:35:48', 100.00, '2026-10-09 00:35:48', '2026-10-09 00:33:26', '2026-10-09 00:35:48');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `user_profile_details`
--

CREATE TABLE `user_profile_details` (
  `id_users` varchar(50) NOT NULL,
  `id_company` int(11) NOT NULL,
  `id_worker` int(11) DEFAULT NULL,
  `confirmed_project_id` int(11) DEFAULT NULL,
  `hired_by_contractor` tinyint(1) NOT NULL DEFAULT 0,
  `id_contractor_company` int(11) DEFAULT NULL,
  `contractor_name` varchar(150) DEFAULT NULL,
  `years_experience_current_role` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `emergency_contact_name` varchar(150) NOT NULL,
  `emergency_contact_phone` varchar(30) NOT NULL,
  `emergency_contact_relation` varchar(30) NOT NULL,
  `confirmed_at` datetime NOT NULL,
  `created_by` varchar(50) NOT NULL,
  `date_create` datetime NOT NULL,
  `last_update` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `user_profile_details`
--

INSERT INTO `user_profile_details` (`id_users`, `id_company`, `id_worker`, `confirmed_project_id`, `hired_by_contractor`, `id_contractor_company`, `contractor_name`, `years_experience_current_role`, `emergency_contact_name`, `emergency_contact_phone`, `emergency_contact_relation`, `confirmed_at`, `created_by`, `date_create`, `last_update`) VALUES
('adminEmpresaDemo@demoSCT.cl', 4, 19, 7, 0, NULL, NULL, 4, 'Contacto emergencia 1', '+56935444513', 'partner', '2026-10-08 19:53:38', 'adminEmpresaDemo@demoSCT.cl', '2026-10-08 19:45:29', '2026-10-08 19:53:38'),
('GerenteEmpresaDemo@demoSCT.cl', 4, 16, 9, 0, NULL, NULL, 6, 'Contacto emergencia 1', '+56935444513', 'friend', '2026-10-08 23:06:13', 'GerenteEmpresaDemo@demoSCT.cl', '2026-10-08 23:06:13', '2026-10-08 23:06:13'),
('JefaturaEmpresaDemo@demoSCT.cl', 4, 17, 7, 0, NULL, NULL, 2, 'Contacto emergencia 1', '935444513', 'partner', '2026-10-08 19:56:40', 'JefaturaEmpresaDemo@demoSCT.cl', '2026-10-08 19:56:40', '2026-10-08 19:56:40'),
('UsuarioEmpresaDemo@demoSCT.cl', 4, 18, NULL, 0, NULL, NULL, 8, 'Contacto emergencia 1', '935444513', 'partner', '2026-10-09 00:34:03', 'UsuarioEmpresaDemo@demoSCT.cl', '2026-10-09 00:34:03', '2026-10-09 00:34:03');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `user_supervision_assignments`
--

CREATE TABLE `user_supervision_assignments` (
  `id_user_supervision_assignment` int(11) NOT NULL,
  `id_company` int(11) NOT NULL,
  `supervisor_user_id` varchar(50) NOT NULL,
  `target_user_id` varchar(50) NOT NULL,
  `state` int(11) NOT NULL DEFAULT 1,
  `assigned_by` varchar(50) NOT NULL,
  `date_create` datetime NOT NULL DEFAULT current_timestamp(),
  `last_update` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
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

--
-- Volcado de datos para la tabla `workers`
--

INSERT INTO `workers` (`id_worker`, `id_company`, `rut`, `name`, `lastname`, `email`, `phone`, `position`, `photo_path`, `state`, `created_by`, `date_create`, `last_update`) VALUES
(1, 1, '18100200-K', 'Bárbara', 'Contreras', 'barbara.contreras@test.helheim.cl', NULL, NULL, NULL, 1, 'seed_test_data', '2026-08-05 09:00:00', '2026-10-08 19:42:25'),
(2, 1, '18100265-4', 'Matías', 'Rojas', 'matias.rojas@test.helheim.cl', '+56 9 5511 2202', 'Analista de Calidad (QA)', NULL, 1, 'seed_test_data', '2026-08-05 09:00:00', '2026-08-05 09:00:00'),
(3, 2, '18100308-1', 'Camila', 'Soto', 'camila.soto@test.tecaivot.cl', NULL, NULL, NULL, 1, 'seed_test_data', '2026-08-05 09:30:00', '2026-10-08 19:42:25'),
(4, 2, '18100534-3', 'Rodrigo', 'Salinas', 'rodrigo.salinas@test.tecaivot.cl', '+56 9 5522 3302', 'Técnico de Soporte TI', NULL, 1, 'seed_test_data', '2026-08-05 09:30:00', '2026-08-05 09:30:00'),
(5, 2, '18100641-2', 'Valentina', 'Rojas', 'valentina.rojas@test.tecaivot.cl', '+56 9 5522 3303', 'Desarrolladora Backend', NULL, 1, 'seed_test_data', '2026-08-05 09:30:00', '2026-08-05 09:30:00'),
(6, 3, '18100740-0', 'Cristóbal', 'Fuentes', 'cristobal.fuentes@test.engie.cl', NULL, NULL, NULL, 1, 'seed_test_data', '2026-09-08 09:00:00', '2026-10-08 19:42:25'),
(7, 3, '18100834-2', 'Patricio', 'Gómez', 'patricio.gomez@test.engie.cl', NULL, NULL, NULL, 1, 'seed_test_data', '2026-09-08 09:00:00', '2026-10-08 19:42:25'),
(8, 3, '18100906-3', 'Nicolás', 'Vargas', 'nicolas.vargas@test.engie.cl', '+56 9 5533 4403', 'Electricista', NULL, 1, 'seed_test_data', '2026-09-08 09:00:00', '2026-09-08 09:00:00'),
(9, 3, '18101131-9', 'Francisca', 'Torres', 'francisca.torres@test.engie.cl', NULL, NULL, NULL, 1, 'seed_test_data', '2026-09-08 09:00:00', '2026-10-08 19:42:25'),
(10, 3, '18101194-7', 'Sebastián', 'Morales', 'sebastian.morales@test.engie.cl', '+56 9 5533 4405', 'Soldador Calificado', NULL, 1, 'seed_test_data', '2026-09-08 09:00:00', '2026-09-08 09:00:00'),
(11, 3, '18101404-0', 'Javiera', 'Reyes', 'javiera.reyes@test.engie.cl', NULL, NULL, NULL, 1, 'seed_test_data', '2026-09-08 09:00:00', '2026-10-08 19:42:25'),
(12, 3, '18101630-2', 'Felipe', 'Castillo', 'felipe.castillo@test.engie.cl', '+56 9 5533 4407', 'Operador Maquinaria Pesada', NULL, 1, 'seed_test_data', '2026-09-08 09:00:00', '2026-09-08 09:00:00'),
(13, 3, '18101806-2', 'Constanza', 'Pizarro', 'constanza.pizarro@test.engie.cl', '+56 9 5533 4408', 'Técnico Instrumentista', NULL, 1, 'seed_test_data', '2026-09-08 09:00:00', '2026-09-08 09:00:00'),
(14, 3, '18101865-8', 'Tomás', 'Espinoza', 'tomas.espinoza@test.engie.cl', '+56 9 5533 4409', 'Operador Grúa Horquilla', NULL, 1, 'seed_test_data', '2026-09-08 09:00:00', '2026-09-08 09:00:00'),
(15, 3, '18102053-9', 'Antonia', 'Fernández', 'antonia.fernandez@test.engie.cl', '+56 9 5533 4410', 'Encargada de Bodega', NULL, 1, 'seed_test_data', '2026-09-08 09:00:00', '2026-09-08 09:00:00'),
(16, 4, '11111111-1', 'Gerente', 'DEMO', 'GerenteEmpresaDemo@demoSCT.cl', '935444514', 'Gerente de Empresa', NULL, 1, 'seed_demo_sct', '2026-09-08 18:43:22', '2026-10-08 23:06:13'),
(17, 4, '22222222-2', 'Jefatura', 'DEMO', 'JefaturaEmpresaDemo@demoSCT.cl', '935444514', 'Jefatura Operacional', NULL, 1, 'seed_demo_sct', '2026-09-08 18:43:22', '2026-10-08 19:56:40'),
(18, 4, '1234578-5', 'Usuario', 'DEMO', 'UsuarioEmpresaDemo@demoSCT.cl', '935444514', 'Usuario Operativo', NULL, 1, 'seed_demo_sct', '2026-09-08 18:43:22', '2026-10-09 00:34:03'),
(19, 4, '44444444-4', 'Administrador', 'DEMO', 'adminEmpresaDemo@demoSCT.cl', '935444514', 'Administrador de Empresa', NULL, 1, 'seed_demo_sct', '2026-09-08 18:43:22', '2026-10-08 19:53:38'),
(20, 4, '55555555-5', 'Sofía', 'Navarro DEMO', 'sofia.prevencion@demoSCT.cl', '+56 9 5000 1001', 'Prevencionista de Riesgos', NULL, 1, 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 19:53:15', '2026-09-08 19:53:15'),
(21, 4, '66666666-6', 'Diego', 'Muñoz DEMO', 'diego.operador@demoSCT.cl', '+56 9 5000 1002', 'Operador de Maquinaria', NULL, 1, 'JefaturaEmpresaDemo@demoSCT.cl', '2026-09-08 19:53:15', '2026-09-08 19:53:15'),
(22, 4, '77777777-7', 'Valentina', 'Pérez DEMO', 'valentina.tecnica@demoSCT.cl', '+56 9 5000 1003', 'Técnica de Mantención', NULL, 1, 'JefaturaEmpresaDemo@demoSCT.cl', '2026-09-08 19:53:15', '2026-09-08 19:53:15'),
(23, 4, '88888888-8', 'Mauricio', 'Silva DEMO', 'mauricio.contratista@demoSCT.cl', '+56 9 5000 1004', 'Contratista Eléctrico', NULL, 1, 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 19:53:15', '2026-09-08 19:53:15'),
(24, 4, '10101010-4', 'Camila', 'Reyes DEMO', 'camila.brigada@demoSCT.cl', '+56 9 5000 1005', 'Brigadista de Emergencia', NULL, 1, 'GerenteEmpresaDemo@demoSCT.cl', '2026-09-08 19:53:15', '2026-09-08 19:53:15'),
(25, 4, '12121212-9', 'Pedro', 'Rojas DEMO', 'pedro.inactivo@demoSCT.cl', NULL, 'Trabajador Inactivo', NULL, 0, 'adminEmpresaDemo@demoSCT.cl', '2026-09-08 19:53:15', '2026-09-08 19:53:15'),
(26, 4, '1234587-5', 'Usuario2', 'DEMO2', 'UsuarioNuevoEmpresaDemo@demoSCT.cl', '935444514', 'Usuario Operativo', NULL, 1, 'migration_e3_vs1_onboarding', '2026-10-08 15:12:03', '2026-10-08 23:04:47');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `worker_health_declaration`
--

CREATE TABLE `worker_health_declaration` (
  `id_health_declaration` int(11) NOT NULL,
  `id_company` int(11) NOT NULL,
  `id_users` varchar(50) NOT NULL,
  `id_health_profile` int(11) NOT NULL,
  `accepted_at` datetime NOT NULL,
  `declaration_version` varchar(20) NOT NULL,
  `declaration_hash` char(64) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_by` varchar(50) NOT NULL,
  `date_create` datetime NOT NULL,
  `last_update` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `worker_health_declaration`
--

INSERT INTO `worker_health_declaration` (`id_health_declaration`, `id_company`, `id_users`, `id_health_profile`, `accepted_at`, `declaration_version`, `declaration_hash`, `ip_address`, `created_by`, `date_create`, `last_update`) VALUES
(12, 4, 'UsuarioEmpresaDemo@demoSCT.cl', 12, '2026-10-09 00:34:03', 'v1.0', 'b7d8c1a0d477753a2c352c5236f9788fe44ace7febcbfb8e6cabf5f053be3faa', '127.0.0.1', 'UsuarioEmpresaDemo@demoSCT.cl', '2026-10-09 00:34:03', '2026-10-09 00:34:03');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `worker_health_profile`
--

CREATE TABLE `worker_health_profile` (
  `id_health_profile` int(11) NOT NULL,
  `id_company` int(11) NOT NULL,
  `id_users` varchar(50) NOT NULL,
  `id_worker` int(11) DEFAULT NULL,
  `is_current` tinyint(1) NOT NULL DEFAULT 1,
  `phone` varchar(30) DEFAULT NULL,
  `health_system` varchar(20) NOT NULL,
  `emergency_name_1` varchar(150) NOT NULL,
  `emergency_relation_1` varchar(30) NOT NULL,
  `emergency_phone_1` varchar(30) NOT NULL,
  `emergency_name_2` varchar(150) DEFAULT NULL,
  `emergency_relation_2` varchar(30) DEFAULT NULL,
  `emergency_phone_2` varchar(30) DEFAULT NULL,
  `conditions_json` longtext NOT NULL,
  `condition_other_enc` longtext DEFAULT NULL,
  `medication_choice` varchar(20) NOT NULL,
  `medications_text_enc` longtext DEFAULT NULL,
  `medications_emergency_enc` longtext DEFAULT NULL,
  `allergies_json` longtext NOT NULL,
  `allergy_details_enc` longtext DEFAULT NULL,
  `severe_reaction` varchar(20) DEFAULT NULL,
  `severe_reaction_info_enc` longtext DEFAULT NULL,
  `occupational_choice` varchar(30) NOT NULL,
  `occupational_diseases_json` longtext DEFAULT NULL,
  `occupational_other_enc` longtext DEFAULT NULL,
  `restriction_choice` varchar(20) NOT NULL,
  `restriction_details_enc` longtext DEFAULT NULL,
  `created_by` varchar(50) NOT NULL,
  `date_create` datetime NOT NULL,
  `last_update` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `worker_health_profile`
--

INSERT INTO `worker_health_profile` (`id_health_profile`, `id_company`, `id_users`, `id_worker`, `is_current`, `phone`, `health_system`, `emergency_name_1`, `emergency_relation_1`, `emergency_phone_1`, `emergency_name_2`, `emergency_relation_2`, `emergency_phone_2`, `conditions_json`, `condition_other_enc`, `medication_choice`, `medications_text_enc`, `medications_emergency_enc`, `allergies_json`, `allergy_details_enc`, `severe_reaction`, `severe_reaction_info_enc`, `occupational_choice`, `occupational_diseases_json`, `occupational_other_enc`, `restriction_choice`, `restriction_details_enc`, `created_by`, `date_create`, `last_update`) VALUES
(12, 4, 'UsuarioEmpresaDemo@demoSCT.cl', 18, 1, '935444514', 'achs', 'Contacto emergencia 1', 'partner', '935444513', NULL, NULL, NULL, '[\"none\"]', NULL, 'no', NULL, NULL, '[\"none\"]', NULL, NULL, NULL, 'no', '[]', NULL, 'no', NULL, 'UsuarioEmpresaDemo@demoSCT.cl', '2026-10-09 00:34:03', '2026-10-09 00:34:03');

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
(24, 9, '2026-09-08 19:53:15'),
(26, 7, '2026-10-08 15:48:40'),
(26, 8, '2026-10-08 15:48:40'),
(26, 9, '2026-10-08 15:48:40');

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
-- Indices de la tabla `contractor_companies`
--
ALTER TABLE `contractor_companies`
  ADD PRIMARY KEY (`id_contractor_company`),
  ADD UNIQUE KEY `uq_contractor_company_rut` (`id_company`,`rut`),
  ADD KEY `idx_contractor_company_name` (`id_company`,`business_name`),
  ADD KEY `idx_contractor_company_trade_name` (`id_company`,`trade_name`);

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
-- Indices de la tabla `onboarding_assessment_answers`
--
ALTER TABLE `onboarding_assessment_answers`
  ADD PRIMARY KEY (`id_answer`),
  ADD UNIQUE KEY `uq_onboarding_answer_attempt_question` (`id_attempt`,`id_question`),
  ADD KEY `fk_onboarding_answer_question` (`id_question`),
  ADD KEY `fk_onboarding_answer_option` (`id_option`);

--
-- Indices de la tabla `onboarding_assessment_attempts`
--
ALTER TABLE `onboarding_assessment_attempts`
  ADD PRIMARY KEY (`id_attempt`),
  ADD KEY `idx_onboarding_attempt_user_status` (`id_users`,`status`);

--
-- Indices de la tabla `onboarding_assessment_attempt_questions`
--
ALTER TABLE `onboarding_assessment_attempt_questions`
  ADD PRIMARY KEY (`id_attempt_question`),
  ADD UNIQUE KEY `uq_onboarding_attempt_question` (`id_attempt`,`id_question`),
  ADD UNIQUE KEY `uq_onboarding_attempt_order` (`id_attempt`,`sort_order`),
  ADD KEY `fk_onboarding_attemptq_question` (`id_question`);

--
-- Indices de la tabla `onboarding_assessment_draft_answers`
--
ALTER TABLE `onboarding_assessment_draft_answers`
  ADD PRIMARY KEY (`id_attempt`,`id_question`),
  ADD KEY `idx_onboarding_draft_option` (`id_option`),
  ADD KEY `fk_onboarding_draft_question` (`id_question`);

--
-- Indices de la tabla `onboarding_questions`
--
ALTER TABLE `onboarding_questions`
  ADD PRIMARY KEY (`id_question`),
  ADD UNIQUE KEY `uq_onboarding_question_code` (`question_code`);

--
-- Indices de la tabla `onboarding_question_options`
--
ALTER TABLE `onboarding_question_options`
  ADD PRIMARY KEY (`id_option`),
  ADD KEY `idx_onboarding_option_question` (`id_question`);

--
-- Indices de la tabla `onboarding_question_option_translations`
--
ALTER TABLE `onboarding_question_option_translations`
  ADD PRIMARY KEY (`id_option`,`language_code`),
  ADD KEY `idx_onboarding_option_translation_language` (`language_code`);

--
-- Indices de la tabla `onboarding_question_translations`
--
ALTER TABLE `onboarding_question_translations`
  ADD PRIMARY KEY (`id_question`,`language_code`),
  ADD KEY `idx_onboarding_question_translation_language` (`language_code`);

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
-- Indices de la tabla `schema_migrations`
--
ALTER TABLE `schema_migrations`
  ADD PRIMARY KEY (`version`);

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
-- Indices de la tabla `user_data_consent`
--
ALTER TABLE `user_data_consent`
  ADD PRIMARY KEY (`id_consent`),
  ADD KEY `idx_consent_user_version` (`id_users`,`policy_version`);

--
-- Indices de la tabla `user_onboarding`
--
ALTER TABLE `user_onboarding`
  ADD PRIMARY KEY (`id_users`);

--
-- Indices de la tabla `user_profile_details`
--
ALTER TABLE `user_profile_details`
  ADD PRIMARY KEY (`id_users`),
  ADD KEY `idx_user_profile_details_company` (`id_company`),
  ADD KEY `idx_user_profile_details_worker` (`id_worker`),
  ADD KEY `idx_user_profile_details_project` (`confirmed_project_id`),
  ADD KEY `idx_user_profile_details_contractor` (`id_contractor_company`);

--
-- Indices de la tabla `user_supervision_assignments`
--
ALTER TABLE `user_supervision_assignments`
  ADD PRIMARY KEY (`id_user_supervision_assignment`),
  ADD UNIQUE KEY `uq_user_supervision_pair` (`supervisor_user_id`,`target_user_id`),
  ADD KEY `idx_user_supervision_company` (`id_company`),
  ADD KEY `idx_user_supervision_target` (`target_user_id`,`state`),
  ADD KEY `idx_user_supervision_supervisor` (`supervisor_user_id`,`state`);

--
-- Indices de la tabla `workers`
--
ALTER TABLE `workers`
  ADD PRIMARY KEY (`id_worker`),
  ADD UNIQUE KEY `uq_worker_rut_company` (`rut`,`id_company`),
  ADD KEY `idx_workers_company` (`id_company`);

--
-- Indices de la tabla `worker_health_declaration`
--
ALTER TABLE `worker_health_declaration`
  ADD PRIMARY KEY (`id_health_declaration`),
  ADD KEY `idx_health_decl_user` (`id_users`,`accepted_at`),
  ADD KEY `idx_health_decl_profile` (`id_health_profile`),
  ADD KEY `fk_health_decl_company` (`id_company`);

--
-- Indices de la tabla `worker_health_profile`
--
ALTER TABLE `worker_health_profile`
  ADD PRIMARY KEY (`id_health_profile`),
  ADD KEY `idx_health_user_current` (`id_users`,`is_current`),
  ADD KEY `idx_health_company` (`id_company`),
  ADD KEY `idx_health_worker` (`id_worker`);

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
  MODIFY `id_change` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

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
-- AUTO_INCREMENT de la tabla `contractor_companies`
--
ALTER TABLE `contractor_companies`
  MODIFY `id_contractor_company` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=167;

--
-- AUTO_INCREMENT de la tabla `login_codes`
--
ALTER TABLE `login_codes`
  MODIFY `id_login_code` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=160;

--
-- AUTO_INCREMENT de la tabla `onboarding_assessment_answers`
--
ALTER TABLE `onboarding_assessment_answers`
  MODIFY `id_answer` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=181;

--
-- AUTO_INCREMENT de la tabla `onboarding_assessment_attempts`
--
ALTER TABLE `onboarding_assessment_attempts`
  MODIFY `id_attempt` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de la tabla `onboarding_assessment_attempt_questions`
--
ALTER TABLE `onboarding_assessment_attempt_questions`
  MODIFY `id_attempt_question` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=211;

--
-- AUTO_INCREMENT de la tabla `onboarding_questions`
--
ALTER TABLE `onboarding_questions`
  MODIFY `id_question` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=141;

--
-- AUTO_INCREMENT de la tabla `onboarding_question_options`
--
ALTER TABLE `onboarding_question_options`
  MODIFY `id_option` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1023;

--
-- AUTO_INCREMENT de la tabla `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id_reset` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id_permission` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=98;

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
  MODIFY `id_users_role` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT de la tabla `users_role_group`
--
ALTER TABLE `users_role_group`
  MODIFY `id_role_group` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

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
-- AUTO_INCREMENT de la tabla `user_data_consent`
--
ALTER TABLE `user_data_consent`
  MODIFY `id_consent` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de la tabla `user_supervision_assignments`
--
ALTER TABLE `user_supervision_assignments`
  MODIFY `id_user_supervision_assignment` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `workers`
--
ALTER TABLE `workers`
  MODIFY `id_worker` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT de la tabla `worker_health_declaration`
--
ALTER TABLE `worker_health_declaration`
  MODIFY `id_health_declaration` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `worker_health_profile`
--
ALTER TABLE `worker_health_profile`
  MODIFY `id_health_profile` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

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
-- Filtros para la tabla `contractor_companies`
--
ALTER TABLE `contractor_companies`
  ADD CONSTRAINT `fk_contractor_company_company` FOREIGN KEY (`id_company`) REFERENCES `company` (`id_company`) ON DELETE NO ACTION ON UPDATE CASCADE;

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
-- Filtros para la tabla `onboarding_assessment_answers`
--
ALTER TABLE `onboarding_assessment_answers`
  ADD CONSTRAINT `fk_onboarding_answer_attempt` FOREIGN KEY (`id_attempt`) REFERENCES `onboarding_assessment_attempts` (`id_attempt`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_onboarding_answer_option` FOREIGN KEY (`id_option`) REFERENCES `onboarding_question_options` (`id_option`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_onboarding_answer_question` FOREIGN KEY (`id_question`) REFERENCES `onboarding_questions` (`id_question`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `onboarding_assessment_attempts`
--
ALTER TABLE `onboarding_assessment_attempts`
  ADD CONSTRAINT `fk_onboarding_attempt_user` FOREIGN KEY (`id_users`) REFERENCES `users` (`id_users`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `onboarding_assessment_attempt_questions`
--
ALTER TABLE `onboarding_assessment_attempt_questions`
  ADD CONSTRAINT `fk_onboarding_attemptq_attempt` FOREIGN KEY (`id_attempt`) REFERENCES `onboarding_assessment_attempts` (`id_attempt`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_onboarding_attemptq_question` FOREIGN KEY (`id_question`) REFERENCES `onboarding_questions` (`id_question`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `onboarding_assessment_draft_answers`
--
ALTER TABLE `onboarding_assessment_draft_answers`
  ADD CONSTRAINT `fk_onboarding_draft_attempt` FOREIGN KEY (`id_attempt`) REFERENCES `onboarding_assessment_attempts` (`id_attempt`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_onboarding_draft_option` FOREIGN KEY (`id_option`) REFERENCES `onboarding_question_options` (`id_option`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_onboarding_draft_question` FOREIGN KEY (`id_question`) REFERENCES `onboarding_questions` (`id_question`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `onboarding_question_options`
--
ALTER TABLE `onboarding_question_options`
  ADD CONSTRAINT `fk_onboarding_option_question` FOREIGN KEY (`id_question`) REFERENCES `onboarding_questions` (`id_question`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `onboarding_question_option_translations`
--
ALTER TABLE `onboarding_question_option_translations`
  ADD CONSTRAINT `fk_onboarding_option_translation_option` FOREIGN KEY (`id_option`) REFERENCES `onboarding_question_options` (`id_option`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `onboarding_question_translations`
--
ALTER TABLE `onboarding_question_translations`
  ADD CONSTRAINT `fk_onboarding_question_translation_question` FOREIGN KEY (`id_question`) REFERENCES `onboarding_questions` (`id_question`) ON DELETE CASCADE ON UPDATE CASCADE;

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
-- Filtros para la tabla `user_data_consent`
--
ALTER TABLE `user_data_consent`
  ADD CONSTRAINT `fk_consent_user` FOREIGN KEY (`id_users`) REFERENCES `users` (`id_users`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `user_onboarding`
--
ALTER TABLE `user_onboarding`
  ADD CONSTRAINT `fk_user_onboarding_user` FOREIGN KEY (`id_users`) REFERENCES `users` (`id_users`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `user_profile_details`
--
ALTER TABLE `user_profile_details`
  ADD CONSTRAINT `fk_user_profile_details_company` FOREIGN KEY (`id_company`) REFERENCES `company` (`id_company`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_user_profile_details_contractor` FOREIGN KEY (`id_contractor_company`) REFERENCES `contractor_companies` (`id_contractor_company`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_user_profile_details_project` FOREIGN KEY (`confirmed_project_id`) REFERENCES `projects` (`id_project`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_user_profile_details_user` FOREIGN KEY (`id_users`) REFERENCES `users` (`id_users`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_user_profile_details_worker` FOREIGN KEY (`id_worker`) REFERENCES `workers` (`id_worker`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `user_supervision_assignments`
--
ALTER TABLE `user_supervision_assignments`
  ADD CONSTRAINT `fk_user_supervision_company` FOREIGN KEY (`id_company`) REFERENCES `company` (`id_company`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_user_supervision_supervisor` FOREIGN KEY (`supervisor_user_id`) REFERENCES `users` (`id_users`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_user_supervision_target` FOREIGN KEY (`target_user_id`) REFERENCES `users` (`id_users`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `workers`
--
ALTER TABLE `workers`
  ADD CONSTRAINT `fk_workers_company` FOREIGN KEY (`id_company`) REFERENCES `company` (`id_company`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Filtros para la tabla `worker_health_declaration`
--
ALTER TABLE `worker_health_declaration`
  ADD CONSTRAINT `fk_health_decl_company` FOREIGN KEY (`id_company`) REFERENCES `company` (`id_company`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_health_decl_profile` FOREIGN KEY (`id_health_profile`) REFERENCES `worker_health_profile` (`id_health_profile`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_health_decl_user` FOREIGN KEY (`id_users`) REFERENCES `users` (`id_users`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Filtros para la tabla `worker_health_profile`
--
ALTER TABLE `worker_health_profile`
  ADD CONSTRAINT `fk_health_profile_company` FOREIGN KEY (`id_company`) REFERENCES `company` (`id_company`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_health_profile_user` FOREIGN KEY (`id_users`) REFERENCES `users` (`id_users`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_health_profile_worker` FOREIGN KEY (`id_worker`) REFERENCES `workers` (`id_worker`) ON DELETE SET NULL ON UPDATE CASCADE;

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
