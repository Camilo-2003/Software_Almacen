-- Active: 1751726581581@@127.0.0.1@3306
-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost
-- Tiempo de generación: 03-07-2025 a las 01:21:46
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `almacen`
--
CREATE DATABASE almacenl;

USE almacen;
-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `administradores`
--

CREATE TABLE `administradores` (
  `id_administrador` int(11) NOT NULL,
  `nombres` varchar(100) DEFAULT NULL,
  `apellidos` varchar(100) DEFAULT NULL,
  `correo` varchar(100) DEFAULT NULL,
  `telefono` varchar(255) DEFAULT NULL,
  `hora_ingreso` datetime NOT NULL,
  `hora_salida` datetime DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `administradores`
--

INSERT INTO `administradores` (`id_administrador`, `nombres`, `apellidos`, `correo`, `telefono`, `hora_ingreso`, `hora_salida`, `password`) VALUES
(2, 'Maria', 'Montes C', 'Maria123@gmail.com', '3122369834', '2025-07-01 02:17:11', '2025-07-01 04:11:39', '98765');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `almacenistas`
--

CREATE TABLE `almacenistas` (
  `id_almacenista` int(11) NOT NULL,
  `nombres` varchar(255) NOT NULL,
  `apellidos` varchar(255) NOT NULL,
  `correo` varchar(255) NOT NULL,
  `telefono` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `hora_ingreso` datetime NOT NULL,
  `hora_salida` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `almacenistas`
--

INSERT INTO `almacenistas` (`id_almacenista`, `nombres`, `apellidos`, `correo`, `telefono`, `password`, `hora_ingreso`, `hora_salida`) VALUES
(10, 'Maria ', 'Montes Carmona', 'Maria@gmail.com', '3122369834', '$2y$10$U9x5F1oNXiL6X9ri3PYBTupLOUUpEWOv0M6l.QDivXNTKGh7wCiny', '2025-06-30 08:52:11', '2025-06-30 09:25:15');
-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `devolucion_equipos`
--

CREATE TABLE `devolucion_equipos` (
  `id_devolucion` int(11) NOT NULL,
  `id_prestamo_equipo_detalle` int(11) NOT NULL,
  `id_responsable` int(11) NOT NULL,
  `rol_responsable` varchar(50) NOT NULL,
  `responsable` varchar(50) DEFAULT NULL,
  `estado_devolucion` enum('bueno','regular','malo','deteriorado') NOT NULL DEFAULT 'bueno',
  `fecha_devolucion` datetime NOT NULL,
  `observaciones` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `devolucion_equipos`
--

INSERT INTO `devolucion_equipos` (`id_devolucion`, `id_prestamo_equipo_detalle`, `id_responsable`, `rol_responsable`, `responsable`, `estado_devolucion`, `fecha_devolucion`, `observaciones`) VALUES
(866, 594, 2, 'administrador', 'Juan Camilo Muñoz M', 'malo', '2025-07-01 15:15:47', 'gffg'),
(867, 595, 2, 'administrador', 'Juan Camilo Muñoz M', 'malo', '2025-07-01 15:28:51', 'MALO'),
(868, 596, 2, 'administrador', 'Juan Camilo Muñoz M', 'malo', '2025-07-01 15:28:58', 'Malo'),
(869, 597, 2, 'administrador', 'Juan Camilo Muñoz M', 'malo', '2025-07-01 15:32:29', 'MALO'),
(870, 598, 2, 'administrador', 'Juan Camilo Muñoz M', 'deteriorado', '2025-07-01 15:50:41', 'un poco deteriorado'),
(871, 599, 2, 'administrador', 'Juan Camilo Muñoz M', 'malo', '2025-07-01 15:55:51', 'fhhfh'),
(872, 600, 2, 'administrador', 'Juan Camilo Muñoz M', 'deteriorado', '2025-07-01 15:56:53', 'bcvb'),
(873, 601, 2, 'administrador', 'Juan Camilo Muñoz M', 'malo', '2025-07-01 15:57:29', 'ngnn'),
(874, 602, 2, 'administrador', 'Juan Camilo Muñoz M', 'bueno', '2025-07-01 16:04:03', 'rffe'),
(875, 603, 2, 'administrador', 'Juan Camilo Muñoz M', 'deteriorado', '2025-07-01 16:04:20', 'sdsds'),
(876, 604, 2, 'administrador', 'Juan Camilo Muñoz M', 'deteriorado', '2025-07-01 16:05:17', 'ghgfgfh'),
(877, 605, 2, 'administrador', 'Juan Camilo Muñoz M', 'bueno', '2025-07-01 16:06:16', 'rrr');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `devolucion_materiales`
--

CREATE TABLE `devolucion_materiales` (
  `id_devolucion` int(11) NOT NULL,
  `id_prestamo_material` int(11) NOT NULL,
  `fecha_devolucion` datetime NOT NULL,
  `observaciones` text DEFAULT NULL,
  `estado_devolucion` enum('bueno','regular','malo','consumido') NOT NULL DEFAULT 'bueno',
  `id_responsable` int(11) NOT NULL,
  `rol_responsable` enum('almacenista','administrador') NOT NULL,
  `responsable` varchar(50) DEFAULT NULL,
  `cantidad` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `devolucion_materiales`
--

INSERT INTO `devolucion_materiales` (`id_devolucion`, `id_prestamo_material`, `fecha_devolucion`, `observaciones`, `estado_devolucion`, `id_responsable`, `rol_responsable`, `responsable`, `cantidad`) VALUES
(480, 453, '2025-07-01 09:36:28', 'mal uso', 'malo', 2, 'administrador', 'Juan Camilo Muñoz M', 5),
(481, 455, '2025-07-01 14:20:25', 'Material consumible registrado automáticamente.', 'consumido', 2, 'administrador', 'Juan Camilo Muñoz M', 1),
(482, 456, '2025-07-01 14:20:25', 'Material consumible registrado automáticamente.', 'consumido', 2, 'administrador', 'Juan Camilo Muñoz M', 1),
(503, 454, '2025-07-01 15:16:38', 'godd', 'bueno', 2, 'administrador', 'Juan Camilo Muñoz M', 1),
(504, 457, '2025-07-01 15:16:38', 'godd', 'bueno', 2, 'administrador', 'Juan Camilo Muñoz M', 1),
(505, 458, '2025-07-01 15:16:38', 'godd', 'bueno', 2, 'administrador', 'Juan Camilo Muñoz M', 1),
(506, 459, '2025-07-01 15:16:38', 'godd', 'bueno', 2, 'administrador', 'Juan Camilo Muñoz M', 1),
(507, 460, '2025-07-01 15:30:07', 'sdd', 'regular', 2, 'administrador', 'Juan Camilo Muñoz M', 1),
(508, 461, '2025-07-01 15:30:29', 'malito', 'malo', 2, 'administrador', 'Juan Camilo Muñoz M', 1),
(509, 462, '2025-07-01 15:32:02', 'BUENOOOO', 'bueno', 2, 'administrador', 'Juan Camilo Muñoz M', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `equipos`
--

CREATE TABLE `equipos` (
  `id_equipo` int(11) NOT NULL,
  `marca` varchar(50) NOT NULL,
  `serial` varchar(50) NOT NULL,
  `estado` enum('disponible','prestado','malo','deteriorado') NOT NULL DEFAULT 'disponible'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `equipos`
--

INSERT INTO `equipos` (`id_equipo`, `marca`, `serial`, `estado`) VALUES
(1, 'HP', '34350699JRE', 'disponible'),
(2, 'Lenovo', '3435069056', 'disponible'),
(3, 'Dell', '3435064999999', 'disponible'),
(6, 'Acer', '34350666661', 'disponible'),
(7, 'Lenovo', '3435063', 'disponible'),
(9, 'HP', '3435065', 'disponible'),
(10, 'Asus', '34350645', 'disponible'),
(11, 'HP', '343506995', 'disponible'),
(13, 'Dell', '34350654', 'disponible'),
(14, 'HP', '22222', 'disponible'),
(15, 'Dell', '3435061111', 'disponible'),
(16, 'HP', '343506213', 'disponible'),
(17, 'Lenovo', '223', 'disponible'),
(18, 'Lenovo', '2121212121', 'disponible'),
(20, 'HP', '34FK56', 'disponible'),
(21, 'Acer', '12321', 'disponible'),
(24, 'HP', '234353', 'disponible'),
(25, 'Dell', '2233', 'disponible'),
(26, 'HP', '212133', 'disponible'),
(28, 'Dell', '225679', 'disponible'),
(35, 'HP', 'JKl8990K', 'disponible'),
(38, 'Dell', 'TF9876', 'disponible'),
(39, 'Apple', 'JY6743', 'disponible'),
(41, 'Acer', 'W34R67', 'disponible'),
(48, 'HP', '34350699JR', 'disponible');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `historial_sesiones`
--

CREATE TABLE `historial_sesiones` (
  `id_registro` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `tipo_usuario` enum('almacenista','administrador') NOT NULL,
  `hora_ingreso` datetime NOT NULL,
  `hora_salida` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `historial_sesiones`
--

INSERT INTO `historial_sesiones` (`id_registro`, `id_usuario`, `tipo_usuario`, `hora_ingreso`, `hora_salida`) VALUES
(34, 2, 'administrador', '2025-06-24 03:05:59', '2025-06-24 03:51:57'),
(35, 2, 'administrador', '2025-06-24 03:52:15', '2025-06-24 07:10:04'),
(36, 2, 'administrador', '2025-06-24 07:10:09', '2025-06-24 14:56:03'),
(37, 2, 'administrador', '2025-06-25 07:52:42', '2025-06-25 07:52:45'),
(38, 2, 'administrador', '2025-06-25 07:56:57', '2025-06-25 07:57:06'),
(39, 2, 'administrador', '2025-06-25 08:02:28', '2025-06-25 08:02:32'),
(40, 2, 'administrador', '2025-06-25 08:03:35', '2025-06-25 08:03:39'),
(41, 2, 'administrador', '2025-06-25 08:19:23', '2025-06-25 08:19:28'),
(42, 10, 'almacenista', '2025-06-25 08:22:38', '2025-06-25 08:22:43'),
(43, 10, 'almacenista', '2025-06-25 08:25:27', '2025-06-25 06:01:38'),
(44, 10, 'almacenista', '2025-06-25 07:13:20', '2025-06-25 14:56:37'),
(45, 2, 'administrador', '2025-06-25 07:32:56', '2025-06-25 07:42:26'),
(46, 10, 'almacenista', '2025-06-25 07:42:29', '2025-06-25 07:43:20'),
(47, 2, 'administrador', '2025-06-25 07:43:24', '2025-06-25 11:15:32'),
(48, 2, 'administrador', '2025-06-26 07:35:25', '2025-06-26 07:51:22'),
(49, 10, 'almacenista', '2025-06-26 07:51:26', '2025-06-26 08:05:17'),
(50, 2, 'administrador', '2025-06-26 08:05:23', '2025-06-26 08:49:24'),
(51, 10, 'almacenista', '2025-06-26 08:49:27', '2025-06-26 09:39:10'),
(52, 2, 'administrador', '2025-06-26 12:04:40', '2025-06-26 03:55:19'),
(53, 2, 'administrador', '2025-06-26 04:01:31', '2025-06-26 04:14:31'),
(54, 10, 'almacenista', '2025-06-26 04:14:38', '2025-06-26 04:15:56'),
(55, 2, 'administrador', '2025-06-26 04:16:00', '2025-06-26 09:52:27'),
(56, 10, 'almacenista', '2025-06-26 09:52:32', '2025-06-26 09:55:21'),
(57, 2, 'administrador', '2025-06-26 09:55:30', '2025-06-26 10:00:17'),
(58, 2, 'administrador', '2025-06-27 06:06:24', '2025-06-27 08:27:11'),
(59, 2, 'administrador', '2025-06-27 08:53:13', '2025-06-27 09:09:41'),
(60, 10, 'almacenista', '2025-06-27 09:09:45', '2025-06-27 09:09:50'),
(61, 2, 'administrador', '2025-06-27 09:09:55', '2025-06-27 09:28:08'),
(62, 10, 'almacenista', '2025-06-27 09:28:13', '2025-06-27 09:32:06'),
(63, 2, 'administrador', '2025-06-27 09:32:12', '2025-06-27 09:34:35'),
(64, 10, 'almacenista', '2025-06-27 09:34:40', '2025-06-27 10:05:35'),
(65, 2, 'administrador', '2025-06-27 02:44:24', '2025-06-27 11:57:14'),
(67, 2, 'administrador', '2025-06-27 11:58:30', '2025-06-28 12:45:34'),
(68, 10, 'almacenista', '2025-06-28 12:45:38', '2025-06-28 20:47:23'),
(69, 10, 'almacenista', '2025-06-28 08:41:24', '2025-06-28 08:41:53'),
(70, 2, 'administrador', '2025-06-28 08:41:57', '2025-06-28 10:47:56'),
(71, 10, 'almacenista', '2025-06-28 10:57:21', '2025-06-28 11:01:10'),
(72, 2, 'administrador', '2025-06-28 11:01:13', '2025-06-28 11:01:18'),
(73, 10, 'almacenista', '2025-06-28 11:01:23', '2025-06-28 11:23:49'),
(74, 2, 'administrador', '2025-06-28 11:23:53', '2025-06-28 11:24:10'),
(75, 10, 'almacenista', '2025-06-28 11:24:15', '2025-06-28 11:30:31'),
(76, 2, 'administrador', '2025-06-28 11:30:34', '2025-06-28 11:30:58'),
(77, 10, 'almacenista', '2025-06-28 11:31:01', '2025-06-28 12:04:31'),
(78, 10, 'almacenista', '2025-06-28 12:42:41', '2025-06-28 12:58:51'),
(79, 2, 'administrador', '2025-06-28 01:01:45', '2025-06-28 01:06:16'),
(80, 2, 'administrador', '2025-06-28 01:39:14', '2025-06-28 03:35:46'),
(81, 10, 'almacenista', '2025-06-28 03:35:50', '2025-06-28 04:00:58'),
(82, 2, 'administrador', '2025-06-28 06:39:56', '2025-06-28 11:25:24'),
(83, 2, 'administrador', '2025-06-29 09:22:22', '2025-06-29 10:12:13'),
(84, 2, 'administrador', '2025-06-29 10:12:16', '2025-06-29 12:02:32'),
(85, 2, 'administrador', '2025-06-29 12:02:36', '2025-06-29 03:53:04'),
(86, 10, 'almacenista', '2025-06-29 03:53:10', '2025-06-29 03:53:21'),
(87, 2, 'administrador', '2025-06-29 03:53:26', '2025-06-29 04:40:24'),
(88, 2, 'administrador', '2025-06-30 08:51:14', '2025-06-30 06:54:46'),
(89, 10, 'almacenista', '2025-06-30 06:54:50', '2025-06-30 06:55:01'),
(90, 2, 'administrador', '2025-06-30 06:55:04', '2025-06-30 07:40:45'),
(91, 2, 'administrador', '2025-06-30 07:54:51', '2025-06-30 08:40:12'),
(92, 2, 'administrador', '2025-06-30 08:41:55', '2025-06-30 08:47:25'),
(93, 10, 'almacenista', '2025-06-30 08:47:30', '2025-06-30 08:51:27'),
(94, 2, 'administrador', '2025-06-30 08:51:34', '2025-06-30 08:52:07'),
(95, 10, 'almacenista', '2025-06-30 08:52:12', '2025-06-30 09:25:15'),
(96, 2, 'administrador', '2025-06-30 09:25:19', '2025-07-01 12:00:37'),
(97, 2, 'administrador', '2025-07-01 09:25:39', '2025-07-01 09:31:03'),
(98, 2, 'administrador', '2025-07-01 09:31:15', '2025-07-01 09:43:42'),
(99, 2, 'administrador', '2025-07-01 09:44:30', '2025-07-01 09:59:29'),
(100, 2, 'administrador', '2025-07-01 02:17:11', '2025-07-01 04:11:38');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `instructores`
--

CREATE TABLE `instructores` (
  `id_instructor` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `correo` varchar(100) NOT NULL,
  `telefono` varchar(15) DEFAULT NULL,
  `ambiente` varchar(50) NOT NULL,
  `estado_activo` enum('activo','inactivo') NOT NULL DEFAULT 'activo',
  `disponibilidad_prestamo` enum('disponible','no_disponible') NOT NULL DEFAULT 'disponible'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `instructores`
--

INSERT INTO `instructores` (`id_instructor`, `nombre`, `apellido`, `correo`, `telefono`, `ambiente`, `estado_activo`, `disponibilidad_prestamo`) VALUES
(1, 'Oscar', 'Arango', 'Oscar@gmail.com', '3004939690', 'C-3', 'activo', 'disponible'),
(8, 'Pedro', 'Gonzales', 'Pedro@gmail.com', '300434939697', 'C-8', 'activo', 'disponible'),
(9, 'Mario', 'Gomez', 'Mario@gmail.com', '6565566840', 'C-6', 'activo', 'disponible'),
(10, 'Maria', 'Zuluaga', 'Maria@gmail.com', '3004939690', 'C-2', 'activo', 'disponible'),
(20, 'Camilo', 'Muñoz Manco', 'camilo@gmail.com', '65655668', 'C-6', 'activo', 'disponible');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `materiales`
--

CREATE TABLE `materiales` (
  `id_material` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `tipo` enum('consumible','no consumible') NOT NULL,
  `stock` int(11) NOT NULL CHECK (`stock` >= 0),
  `estado_material` enum('disponible','en_revision','descartado') NOT NULL DEFAULT 'disponible'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `materiales`
--

INSERT INTO `materiales` (`id_material`, `nombre`, `tipo`, `stock`, `estado_material`) VALUES
(6, 'Marcador', 'no consumible', 10, 'disponible'),
(9, 'Regla', 'no consumible', 10, 'disponible'),
(10, 'borrador', 'consumible', 10, 'disponible'),
(11, 'Colores', 'consumible', 10, 'disponible'),
(12, 'Carpetas', 'no consumible', 10, 'disponible'),
(13, 'extension', 'no consumible', 10, 'disponible'),
(14, 'HDMI', 'no consumible', 10, 'disponible');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `novedades`
--

CREATE TABLE `novedades` (
  `id_novedad` int(11) NOT NULL,
  `tipo` enum('devolucion_material','devolucion_equipo') NOT NULL,
  `descripcion` text NOT NULL,
  `fecha` datetime NOT NULL DEFAULT current_timestamp(),
  `id_responsable` int(11) NOT NULL,
  `rol_responsable` varchar(20) NOT NULL,
  `nombre_responsable` varchar(100) NOT NULL,
  `id_instructor` int(11) NOT NULL,
  `nombre_instructor` varchar(100) DEFAULT NULL,
  `observaciones` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `novedades2`
--

CREATE TABLE `novedades2` (
  `id_novedad2` int(11) NOT NULL,
  `tipo_elemento` enum('equipo','material') NOT NULL DEFAULT 'equipo',
  `id_prestamo_equipo_detalle` int(11) DEFAULT NULL,
  `id_prestamo_equipo` int(11) DEFAULT NULL,
  `id_prestamo_material` int(11) DEFAULT NULL,
  `id_equipo` int(11) DEFAULT NULL,
  `id_material` int(11) DEFAULT NULL,
  `marca_equipo` varchar(100) DEFAULT NULL,
  `nombre_material` varchar(100) DEFAULT NULL,
  `id_instructor` int(11) DEFAULT NULL,
  `nombre_instructor` varchar(255) DEFAULT NULL,
  `tipo_novedad` varchar(100) NOT NULL,
  `descripcion` text NOT NULL,
  `ruta_imagen` varchar(255) DEFAULT NULL,
  `fecha_novedad` datetime NOT NULL DEFAULT current_timestamp(),
  `id_responsable_registro` int(11) DEFAULT NULL,
  `nombre_responsable_registro` varchar(255) DEFAULT NULL,
  `rol_responsable_registro` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `novedades2`
--

INSERT INTO `novedades2` (`id_novedad2`, `tipo_elemento`, `id_prestamo_equipo_detalle`, `id_prestamo_equipo`, `id_prestamo_material`, `id_equipo`, `id_material`, `marca_equipo`, `nombre_material`, `id_instructor`, `nombre_instructor`, `tipo_novedad`, `descripcion`, `ruta_imagen`, `fecha_novedad`, `id_responsable_registro`, `nombre_responsable_registro`, `rol_responsable_registro`) VALUES
(298, 'equipo', 594, 255, NULL, 21, NULL, 'Acer', NULL, 10, 'Maria Zuluaga', 'malo', 'Se devolvio sin una tecla', '/Software_Almacen/App/Uploads/Captura de pantalla (372).png', '2025-07-01 15:15:00', 2, 'Juan Camilo Muñoz M', 'administrador'),
(310, 'equipo', 595, 256, NULL, 6, NULL, 'Acer', NULL, 10, 'Maria Zuluaga', 'malo', 'MALO', '/Software_Almacen/App/Uploads/Captura de pantalla (350).png', '2025-07-01 15:28:00', 2, 'Juan Camilo Muñoz M', 'administrador'),
(311, 'equipo', 596, 256, NULL, 41, NULL, 'Acer', NULL, 10, 'Maria Zuluaga', 'malo', 'Malo', '/Software_Almacen/App/Uploads/Captura de pantalla (350).png', '2025-07-01 15:28:00', 2, 'Juan Camilo Muñoz M', 'administrador'),
(312, 'material', NULL, NULL, 460, NULL, 9, NULL, 'Regla', 1, 'Oscar Arango', 'regular', 'Regla regular', '/Software_Almacen/App/Uploads/Captura de pantalla (366).png', '2025-07-01 15:30:00', 2, 'Juan Camilo Muñoz M', 'administrador'),
(313, 'material', NULL, NULL, 461, NULL, 9, NULL, 'Regla', 1, 'Oscar Arango', 'malo', 'Regla Mala', '/Software_Almacen/App/Uploads/Captura de pantalla (356).png', '2025-07-01 15:30:00', 2, 'Juan Camilo Muñoz M', 'administrador'),
(314, 'equipo', 597, 257, NULL, 39, NULL, 'Apple', NULL, 20, 'Camilo Muñoz Manco', 'malo', 'MALO', '/Software_Almacen/App/Uploads/Captura de pantalla (362).png', '2025-07-01 15:32:00', 2, 'Juan Camilo Muñoz M', 'administrador'),
(315, 'equipo', 598, 257, NULL, 15, NULL, 'Dell', NULL, 20, 'Camilo Muñoz Manco', 'regular', 'un poco deteriorado', '/Software_Almacen/App/Uploads/Captura de pantalla (353).png', '2025-07-01 15:50:00', 2, 'Juan Camilo Muñoz M', 'administrador'),
(317, 'equipo', 600, 259, NULL, 28, NULL, 'Dell', NULL, 20, 'Camilo Muñoz Manco', 'regular', 'Deteriorado', '/Software_Almacen/App/Uploads/Captura de pantalla (358).png', '2025-07-01 15:56:00', 2, 'Juan Camilo Muñoz M', 'administrador');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `prestamo_equipos`
--

CREATE TABLE `prestamo_equipos` (
  `id_prestamo_equipo` int(11) NOT NULL,
  `id_instructor` int(11) NOT NULL,
  `id_responsable` int(11) NOT NULL,
  `rol_responsable` varchar(50) NOT NULL,
  `responsable` varchar(50) DEFAULT NULL,
  `fecha_prestamo` datetime NOT NULL,
  `estado_general_prestamo` varchar(50) DEFAULT 'activo'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `prestamo_equipos`
--

INSERT INTO `prestamo_equipos` (`id_prestamo_equipo`, `id_instructor`, `id_responsable`, `rol_responsable`, `responsable`, `fecha_prestamo`, `estado_general_prestamo`) VALUES
(255, 10, 2, 'administrador', 'Juan Camilo Muñoz M', '2025-07-01 15:13:58', 'completamente_devuelto'),
(256, 10, 2, 'administrador', 'Juan Camilo Muñoz M', '2025-07-01 15:28:21', 'completamente_devuelto'),
(257, 20, 2, 'administrador', 'Juan Camilo Muñoz M', '2025-07-01 15:32:13', 'completamente_devuelto'),
(258, 20, 2, 'administrador', 'Juan Camilo Muñoz M', '2025-07-01 15:55:42', 'completamente_devuelto'),
(259, 20, 2, 'administrador', 'Juan Camilo Muñoz M', '2025-07-01 15:56:47', 'completamente_devuelto'),
(260, 10, 2, 'administrador', 'Juan Camilo Muñoz M', '2025-07-01 15:57:22', 'completamente_devuelto'),
(261, 20, 2, 'administrador', 'Juan Camilo Muñoz M', '2025-07-01 15:58:51', 'completamente_devuelto'),
(262, 10, 2, 'administrador', 'Juan Camilo Muñoz M', '2025-07-01 16:04:11', 'completamente_devuelto'),
(263, 10, 2, 'administrador', 'Juan Camilo Muñoz M', '2025-07-01 16:05:28', 'completamente_devuelto');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `prestamo_equipos_detalle`
--

CREATE TABLE `prestamo_equipos_detalle` (
  `id_prestamo_equipo_detalle` int(11) NOT NULL,
  `id_prestamo_equipo` int(11) NOT NULL,
  `id_equipo` int(11) NOT NULL,
  `estado_item_prestamo` varchar(50) NOT NULL DEFAULT 'prestado',
  `fecha_vencimiento_item` datetime DEFAULT NULL,
  `fecha_devolucion_item` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `prestamo_equipos_detalle`
--

INSERT INTO `prestamo_equipos_detalle` (`id_prestamo_equipo_detalle`, `id_prestamo_equipo`, `id_equipo`, `estado_item_prestamo`, `fecha_vencimiento_item`, `fecha_devolucion_item`) VALUES
(594, 255, 21, 'devuelto', '2025-07-01 21:13:58', '2025-07-01 15:15:47'),
(595, 256, 6, 'devuelto', '2025-07-01 21:28:21', '2025-07-01 15:28:51'),
(596, 256, 41, 'devuelto', '2025-07-01 21:28:21', '2025-07-01 15:28:58'),
(597, 257, 39, 'devuelto', '2025-07-01 21:32:14', '2025-07-01 15:32:29'),
(598, 257, 15, 'devuelto', '2025-07-08 00:00:00', '2025-07-01 15:50:41'),
(599, 258, 25, 'devuelto', '2025-07-01 21:55:43', '2025-07-01 15:55:51'),
(600, 259, 28, 'devuelto', '2025-07-01 21:56:47', '2025-07-01 15:56:53'),
(601, 260, 3, 'devuelto', '2025-07-01 21:57:22', '2025-07-01 15:57:29'),
(602, 261, 13, 'devuelto', '2025-07-01 21:58:51', '2025-07-01 16:04:03'),
(603, 262, 13, 'devuelto', '2025-07-01 22:04:11', '2025-07-01 16:04:20'),
(604, 262, 38, 'devuelto', '2025-07-01 22:04:11', '2025-07-01 16:05:17'),
(605, 263, 10, 'devuelto', '2025-07-01 22:05:29', '2025-07-01 16:06:16');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `prestamo_materiales`
--

CREATE TABLE `prestamo_materiales` (
  `id_prestamo_material` int(11) NOT NULL,
  `id_material` int(11) NOT NULL,
  `id_instructor` int(11) NOT NULL,
  `id_responsable` int(11) NOT NULL,
  `rol_responsable` varchar(50) NOT NULL,
  `responsable` varchar(50) DEFAULT NULL,
  `cantidad` int(11) NOT NULL CHECK (`cantidad` > 0),
  `fecha_prestamo` datetime NOT NULL,
  `fecha_limite_devolucion` datetime DEFAULT NULL,
  `estado` enum('pendiente','devuelto','consumido') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `prestamo_materiales`
--

INSERT INTO `prestamo_materiales` (`id_prestamo_material`, `id_material`, `id_instructor`, `id_responsable`, `rol_responsable`, `responsable`, `cantidad`, `fecha_prestamo`, `fecha_limite_devolucion`, `estado`) VALUES
(453, 6, 9, 2, 'administrador', 'Juan Camilo Muñoz M', 5, '2025-07-01 09:35:27', '2025-07-01 15:35:27', 'devuelto'),
(454, 9, 20, 2, 'administrador', 'Juan Camilo Muñoz M', 1, '2025-07-01 14:20:25', '2025-07-01 20:20:25', 'devuelto'),
(455, 10, 20, 2, 'administrador', 'Juan Camilo Muñoz M', 1, '2025-07-01 14:20:25', NULL, 'consumido'),
(456, 11, 20, 2, 'administrador', 'Juan Camilo Muñoz M', 1, '2025-07-01 14:20:25', NULL, 'consumido'),
(457, 12, 20, 2, 'administrador', 'Juan Camilo Muñoz M', 1, '2025-07-01 14:20:25', '2025-07-01 20:20:25', 'devuelto'),
(458, 13, 20, 2, 'administrador', 'Juan Camilo Muñoz M', 1, '2025-07-01 14:20:25', '2025-07-01 20:20:25', 'devuelto'),
(459, 14, 20, 2, 'administrador', 'Juan Camilo Muñoz M', 1, '2025-07-01 14:20:25', '2025-07-01 20:20:25', 'devuelto'),
(460, 9, 1, 2, 'administrador', 'Juan Camilo Muñoz M', 1, '2025-07-01 15:29:56', '2025-07-01 21:29:56', 'devuelto'),
(461, 9, 1, 2, 'administrador', 'Juan Camilo Muñoz M', 1, '2025-07-01 15:30:16', '2025-07-01 21:30:16', 'devuelto'),
(462, 13, 8, 2, 'administrador', 'Juan Camilo Muñoz M', 1, '2025-07-01 15:31:51', '2025-07-01 21:31:51', 'devuelto');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `administradores`
--
ALTER TABLE `administradores`
  ADD PRIMARY KEY (`id_administrador`),
  ADD UNIQUE KEY `correo` (`correo`);

--
-- Indices de la tabla `almacenistas`
--
ALTER TABLE `almacenistas`
  ADD PRIMARY KEY (`id_almacenista`),
  ADD UNIQUE KEY `correo` (`correo`);

--
-- Indices de la tabla `devolucion_equipos`
--
ALTER TABLE `devolucion_equipos`
  ADD PRIMARY KEY (`id_devolucion`),
  ADD KEY `fk_devolucion_detalle` (`id_prestamo_equipo_detalle`);

--
-- Indices de la tabla `devolucion_materiales`
--
ALTER TABLE `devolucion_materiales`
  ADD PRIMARY KEY (`id_devolucion`),
  ADD KEY `id_prestamo_material` (`id_prestamo_material`);

--
-- Indices de la tabla `equipos`
--
ALTER TABLE `equipos`
  ADD PRIMARY KEY (`id_equipo`),
  ADD UNIQUE KEY `serial` (`serial`);

--
-- Indices de la tabla `historial_sesiones`
--
ALTER TABLE `historial_sesiones`
  ADD PRIMARY KEY (`id_registro`);

--
-- Indices de la tabla `instructores`
--
ALTER TABLE `instructores`
  ADD PRIMARY KEY (`id_instructor`),
  ADD UNIQUE KEY `correo` (`correo`);

--
-- Indices de la tabla `materiales`
--
ALTER TABLE `materiales`
  ADD PRIMARY KEY (`id_material`);

--
-- Indices de la tabla `novedades`
--
ALTER TABLE `novedades`
  ADD PRIMARY KEY (`id_novedad`);

--
-- Indices de la tabla `novedades2`
--
ALTER TABLE `novedades2`
  ADD PRIMARY KEY (`id_novedad2`);

--
-- Indices de la tabla `prestamo_equipos`
--
ALTER TABLE `prestamo_equipos`
  ADD PRIMARY KEY (`id_prestamo_equipo`),
  ADD KEY `fk_pec_instructor` (`id_instructor`);

--
-- Indices de la tabla `prestamo_equipos_detalle`
--
ALTER TABLE `prestamo_equipos_detalle`
  ADD PRIMARY KEY (`id_prestamo_equipo_detalle`),
  ADD KEY `idx_id_equipo` (`id_equipo`),
  ADD KEY `fk_id_prestamo_equipo` (`id_prestamo_equipo`);

--
-- Indices de la tabla `prestamo_materiales`
--
ALTER TABLE `prestamo_materiales`
  ADD PRIMARY KEY (`id_prestamo_material`),
  ADD KEY `id_material` (`id_material`),
  ADD KEY `id_instructor` (`id_instructor`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `administradores`
--
ALTER TABLE `administradores`
  MODIFY `id_administrador` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT de la tabla `almacenistas`
--
ALTER TABLE `almacenistas`
  MODIFY `id_almacenista` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT de la tabla `devolucion_equipos`
--
ALTER TABLE `devolucion_equipos`
  MODIFY `id_devolucion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=878;

--
-- AUTO_INCREMENT de la tabla `devolucion_materiales`
--
ALTER TABLE `devolucion_materiales`
  MODIFY `id_devolucion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=510;

--
-- AUTO_INCREMENT de la tabla `equipos`
--
ALTER TABLE `equipos`
  MODIFY `id_equipo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT de la tabla `historial_sesiones`
--
ALTER TABLE `historial_sesiones`
  MODIFY `id_registro` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=101;

--
-- AUTO_INCREMENT de la tabla `instructores`
--
ALTER TABLE `instructores`
  MODIFY `id_instructor` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de la tabla `materiales`
--
ALTER TABLE `materiales`
  MODIFY `id_material` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT de la tabla `novedades`
--
ALTER TABLE `novedades`
  MODIFY `id_novedad` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT de la tabla `novedades2`
--
ALTER TABLE `novedades2`
  MODIFY `id_novedad2` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=321;

--
-- AUTO_INCREMENT de la tabla `prestamo_equipos`
--
ALTER TABLE `prestamo_equipos`
  MODIFY `id_prestamo_equipo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=264;

--
-- AUTO_INCREMENT de la tabla `prestamo_equipos_detalle`
--
ALTER TABLE `prestamo_equipos_detalle`
  MODIFY `id_prestamo_equipo_detalle` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=606;

--
-- AUTO_INCREMENT de la tabla `prestamo_materiales`
--
ALTER TABLE `prestamo_materiales`
  MODIFY `id_prestamo_material` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=463;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `devolucion_equipos`
--
ALTER TABLE `devolucion_equipos`
  ADD CONSTRAINT `fk_devolucion_detalle` FOREIGN KEY (`id_prestamo_equipo_detalle`) REFERENCES `prestamo_equipos_detalle` (`id_prestamo_equipo_detalle`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `devolucion_materiales`
--
ALTER TABLE `devolucion_materiales`
  ADD CONSTRAINT `devolucion_materiales_ibfk_1` FOREIGN KEY (`id_prestamo_material`) REFERENCES `prestamo_materiales` (`id_prestamo_material`);

--
-- Filtros para la tabla `prestamo_equipos`
--
ALTER TABLE `prestamo_equipos`
  ADD CONSTRAINT `fk_pec_instructor` FOREIGN KEY (`id_instructor`) REFERENCES `instructores` (`id_instructor`);

--
-- Filtros para la tabla `prestamo_equipos_detalle`
--
ALTER TABLE `prestamo_equipos_detalle`
  ADD CONSTRAINT `fk_id_prestamo_equipo` FOREIGN KEY (`id_prestamo_equipo`) REFERENCES `prestamo_equipos` (`id_prestamo_equipo`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ped_equipo` FOREIGN KEY (`id_equipo`) REFERENCES `equipos` (`id_equipo`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `prestamo_materiales`
--
ALTER TABLE `prestamo_materiales`
  ADD CONSTRAINT `prestamo_materiales_ibfk_1` FOREIGN KEY (`id_material`) REFERENCES `materiales` (`id_material`),
  ADD CONSTRAINT `prestamo_materiales_ibfk_2` FOREIGN KEY (`id_instructor`) REFERENCES `instructores` (`id_instructor`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
