-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 30-09-2026 a las 00:50:10
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
-- Base de datos: `tarjeta_virtual`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

CREATE TABLE `clientes` (
  `id_cliente` int(11) NOT NULL,
  `nombre_cliente` varchar(50) NOT NULL,
  `apellido_cliente` varchar(50) NOT NULL,
  `dni_cliente` varchar(20) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `fecha_registro` timestamp NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `clientes`
--

INSERT INTO `clientes` (`id_cliente`, `nombre_cliente`, `apellido_cliente`, `dni_cliente`, `email`, `telefono`, `password`, `fecha_registro`) VALUES
(1, 'Celi', 'Freytas', NULL, 'celifreytas@gmail.com', NULL, '$2y$10$KIKQpdmKQHMpt.uqtZ1TSef7HdUBlgBIzAoSOfTUuyJSI9R.D.wOe', '2026-05-10 22:38:27'),
(2, 'fasdf', 'fasdf', '534543', 'fsdf@fdf.com', '534253245', '$2y$10$HOqAjwZ2yq20f5hg3MO/x.sBFEKf9OShCpKqWJ.KHOAw6EqBPWtB2', '2026-05-10 23:08:28'),
(3, 'Soy', 'yo', '11111111', 'soy@yo.com', '+543516291269', '$2y$10$QMXuLrnrW4OdUqLMd8J40e.Vafh/A7DJrkfUaZIVPyfk3yF0GBOma', '2026-06-29 18:32:47');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `config_pagos`
--

CREATE TABLE `config_pagos` (
  `id_config` int(11) NOT NULL,
  `tarjeta_id` int(11) NOT NULL,
  `mensaje` text DEFAULT NULL,
  `tarifas` text DEFAULT NULL,
  `datos_bancarios` varchar(255) DEFAULT NULL,
  `link_mp` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `config_pagos`
--

INSERT INTO `config_pagos` (`id_config`, `tarjeta_id`, `mensaje`, `tarifas`, `datos_bancarios`, `link_mp`) VALUES
(8, 33, 'La tarjeta la podes pagar transfiriendo', '[{\"nombre\":\"Adultos\",\"monto\":5000},{\"nombre\":\"menores (6 años)\",\"monto\":2000}]', 'AA.BB.CC', ''),
(11, 53, 'A la tajeta la podés pagar transfiriendo a...', '[{\"nombre\":\"Adultos\",\"monto\":5000},{\"nombre\":\"Menores\",\"monto\":2000}]', 'MI.CUENTA', 'linkMercadoPago'),
(12, 53, 'A la tajeta la podés pagar transfiriendo a...', '[{\"nombre\":\"Adultos\",\"monto\":5000},{\"nombre\":\"Menores\",\"monto\":2000}]', 'MI.CUENTA', 'linkMercadoPago'),
(13, 53, 'A la tajeta la podés pagar transfiriendo a...', '[{\"nombre\":\"Adultos\",\"monto\":5000},{\"nombre\":\"Menores\",\"monto\":2000}]', 'MI.CUENTA', 'linkMercadoPago'),
(14, 53, 'A la tajeta la podés pagar transfiriendo a...', '[{\"nombre\":\"Adultos\",\"monto\":5000},{\"nombre\":\"Menores\",\"monto\":2000}]', 'MI.CUENTA', 'linkMercadoPago');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `costos`
--

CREATE TABLE `costos` (
  `id_costo` int(11) NOT NULL,
  `modulo_key` varchar(50) DEFAULT NULL,
  `nombre_amigable` varchar(100) DEFAULT NULL,
  `precio` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `costos`
--

INSERT INTO `costos` (`id_costo`, `modulo_key`, `nombre_amigable`, `precio`) VALUES
(1, 'base', 'Precio Base', 5000.00),
(2, 'mod_cuenta_regresiva', 'Cuenta Regresiva', 1000.00),
(3, 'mod_musica', 'Música', 1000.00),
(4, 'mod_regalos', 'Regalos', 2000.00),
(5, 'mod_dress_code', 'Dress Code', 500.00),
(6, 'mod_compartir_fotos', 'Módulo Compartir fotos', 1200.00),
(7, 'mod_rsvp_interno', 'Reporte de confirmación', 2000.00),
(8, 'mod_muro_deseos', 'Dejar mensaje al/agasajado', 1800.00),
(9, 'mod_playlist', 'Playlist colaborativa', 900.00),
(10, 'mod_linea_tiempo', 'Historia en imágenes', 2200.00),
(11, 'mod_ubi_calen', 'Ubicación y calendario', 800.00),
(12, 'mod_book_fotos', 'Book de fotos', 1200.00),
(13, 'mod_pagar_evento', 'Pagar evento', 1400.00),
(14, 'Mod_rsvp_whatsapp', 'Confirmación por whatsapp', 600.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estilos_visuales`
--

CREATE TABLE `estilos_visuales` (
  `id_estilos_visuales` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `estilos_visuales`
--

INSERT INTO `estilos_visuales` (`id_estilos_visuales`, `nombre`) VALUES
(2, 'divertido'),
(6, 'elegante'),
(1, 'infantil'),
(5, 'minimalista'),
(4, 'romántico'),
(3, 'vintage');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `frases_sugeridas`
--

CREATE TABLE `frases_sugeridas` (
  `id_frase` int(11) NOT NULL,
  `tipo_evento_id` int(11) NOT NULL,
  `texto_frase` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `frases_sugeridas`
--

INSERT INTO `frases_sugeridas` (`id_frase`, `tipo_evento_id`, `texto_frase`) VALUES
(1, 2, 'Un año más de historias, risas y momentos para celebrar juntos.'),
(2, 2, 'La mejor parte de cumplir años es compartirlo con quienes hacen la vida especial.'),
(3, 2, 'Reservá la fecha: la torta, la música y la diversión ya están confirmadas.'),
(4, 1, 'Dos caminos se unen para escribir una misma historia. Si! nos casamos!'),
(5, 1, 'Nos casamos! Tu presencia será el mejor regalo para este día inolvidable.'),
(6, 1, 'Te lo esperabas? Queremos brindar con vos por el amor y que seas testigo de nuestra unión.'),
(7, 6, 'Un día de fe, amor y bendiciones para compartir en familia.'),
(8, 6, 'Con mucha alegría celebramos este paso tan especial en su vida.'),
(9, 6, 'Acompañanos en un momento lleno de luz y esperanza.'),
(10, 4, 'La pasión entra en juego. ¡Que comience la competencia!'),
(11, 4, 'Talento, esfuerzo y espíritu deportivo en una jornada única.'),
(12, 4, 'Preparados para competir, superarse y dejarlo todo en la cancha.');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `linea_tiempo_hitos`
--

CREATE TABLE `linea_tiempo_hitos` (
  `id_hito` int(11) NOT NULL,
  `tarjeta_id` int(11) NOT NULL,
  `fecha_anio` varchar(50) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `orden` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `linea_tiempo_hitos`
--

INSERT INTO `linea_tiempo_hitos` (`id_hito`, `tarjeta_id`, `fecha_anio`, `titulo`, `descripcion`, `foto`, `orden`) VALUES
(6, 33, '2015', 'Cuando nos conocimos...', 'Eramos tan chicos', 'lt_6a4d9dcb59d16.png', 0),
(7, 33, '2020', 'Ya nos elegíamos...', 'EL viaje nos unió', 'lt_6a4d9dfd918f1.png', 1),
(8, 33, '2025', 'Decidimos ser un solo camino', 'A planear nuesra boda!', 'lt_6a4d9e7d0b0ea.png', 2),
(9, 53, '2015', 'Cuando recién nos conocimos', '', 'lt_6a7bb0ee1316d.png', 0),
(10, 53, '2017', 'Nuestro primer viaje', '', 'lt_6a7bb1180c4fe.png', 1),
(11, 53, '2010', 'mi adolescencia', 'locuras', 'lt_6a962331e09b8.jpg', 0),
(12, 53, '2015', 'Pensativa', '', 'lt_6a96237b174eb.jpg', 3);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `muro_deseos`
--

CREATE TABLE `muro_deseos` (
  `id_deseo` int(11) NOT NULL,
  `tarjeta_id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `mensaje` text NOT NULL,
  `estado` enum('pendiente','aprobado') DEFAULT 'aprobado',
  `fecha_creacion` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `muro_deseos`
--

INSERT INTO `muro_deseos` (`id_deseo`, `tarjeta_id`, `nombre`, `mensaje`, `estado`, `fecha_creacion`) VALUES
(5, 33, 'Celi', 'Muchas felicidades!', 'aprobado', '2026-06-07 21:07:36'),
(6, 33, 'Lucrecia', 'hola', 'aprobado', '2026-07-01 16:42:41');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pagos_evento`
--

CREATE TABLE `pagos_evento` (
  `id_pago` int(11) NOT NULL,
  `tarjeta_id` int(11) NOT NULL,
  `nombre_invitado` varchar(100) NOT NULL,
  `concepto` varchar(150) NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `comprobante_img` varchar(255) DEFAULT NULL,
  `estado` enum('pendiente','aprobado','rechazado') DEFAULT 'pendiente',
  `fecha_pago` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pagos_evento`
--

INSERT INTO `pagos_evento` (`id_pago`, `tarjeta_id`, `nombre_invitado`, `concepto`, `monto`, `comprobante_img`, `estado`, `fecha_pago`) VALUES
(4, 33, 'celi', '1 adulto', 5000.00, 'uploads/comprobantes/pago_33_1783471321_6a4d9cd9267af.png', 'pendiente', '2026-07-07 21:42:01'),
(5, 33, 'Juana', '1 mayor y 1 menor', 7000.00, 'uploads/comprobantes/pago_33_1783631189_6a500d5570e46.png', 'pendiente', '2026-07-09 18:06:29');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `planes`
--

CREATE TABLE `planes` (
  `id_planes` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `planes`
--

INSERT INTO `planes` (`id_planes`) VALUES
(1),
(2),
(3);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `plantillas`
--

CREATE TABLE `plantillas` (
  `id_plantilla` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `plantillas`
--

INSERT INTO `plantillas` (`id_plantilla`, `nombre`, `descripcion`) VALUES
(1, 'Plantilla Base Deportiva/Social', 'Estructura con foto flotante superior y mapa inferior');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `playlist_sugerencias`
--

CREATE TABLE `playlist_sugerencias` (
  `id_sugerencia` int(11) NOT NULL,
  `tarjeta_id` int(11) NOT NULL,
  `cancion` varchar(255) NOT NULL,
  `artista` varchar(255) NOT NULL,
  `nombre_invitado` varchar(255) DEFAULT NULL,
  `fecha_sugerencia` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rsvp_acompanantes`
--

CREATE TABLE `rsvp_acompanantes` (
  `id_acompanante` int(11) NOT NULL,
  `id_rsvp` int(11) NOT NULL,
  `nombre_acompanante` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `rsvp_acompanantes`
--

INSERT INTO `rsvp_acompanantes` (`id_acompanante`, `id_rsvp`, `nombre_acompanante`) VALUES
(4, 4, 'Pedro'),
(5, 4, 'Ana'),
(6, 5, 'Jose'),
(7, 5, 'Ana');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rsvp_respuestas`
--

CREATE TABLE `rsvp_respuestas` (
  `id_rsvp` int(11) NOT NULL,
  `tarjeta_id` int(11) NOT NULL,
  `estado` enum('confirmado','ausente') NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `telefono` varchar(50) DEFAULT NULL,
  `cantidad_acompanantes` int(11) DEFAULT 0,
  `mensaje` text DEFAULT NULL,
  `fecha_respuesta` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `rsvp_respuestas`
--

INSERT INTO `rsvp_respuestas` (`id_rsvp`, `tarjeta_id`, `estado`, `nombre`, `email`, `telefono`, `cantidad_acompanantes`, `mensaje`, `fecha_respuesta`) VALUES
(4, 33, 'confirmado', 'Celi', NULL, '3516291269', 2, '', '2026-06-07 21:09:33'),
(5, 53, 'confirmado', 'orlando', NULL, '3516291269', 2, 'no', '2026-08-18 17:59:30');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tarjetas`
--

CREATE TABLE `tarjetas` (
  `id_tarjeta` int(11) NOT NULL,
  `id_cliente` int(11) NOT NULL,
  `id_plantilla` int(11) NOT NULL,
  `tipo_evento_id` int(11) NOT NULL,
  `estilo_visual_id` int(11) NOT NULL,
  `mod_cuenta_regresiva` tinyint(1) DEFAULT 0,
  `mod_musica` tinyint(1) DEFAULT 0,
  `mod_regalos` tinyint(1) DEFAULT 0,
  `mod_dress_code` tinyint(1) DEFAULT 0,
  `mod_linea_tiempo` tinyint(1) DEFAULT 0,
  `mod_book_fotos` tinyint(1) DEFAULT 0,
  `mod_compartir_fotos` tinyint(1) DEFAULT 0,
  `mod_rsvp_interno` tinyint(1) DEFAULT 0,
  `mod_muro_deseos` tinyint(1) DEFAULT 0,
  `mod_playlist` tinyint(1) DEFAULT 0,
  `nombres_portada` varchar(255) DEFAULT NULL,
  `fecha_evento` date DEFAULT NULL,
  `hora_evento` time DEFAULT NULL,
  `direccion_maps` text DEFAULT NULL,
  `link_musica` varchar(255) DEFAULT NULL,
  `datos_bancarios` text DEFAULT NULL,
  `mensaje_regalo` text DEFAULT NULL,
  `dress_code_texto` text DEFAULT NULL,
  `book_texto` varchar(255) DEFAULT NULL,
  `link_galeria_externa` varchar(255) DEFAULT NULL,
  `telefono_whatsapp` varchar(50) DEFAULT NULL,
  `fecha_limite_rsvp` date DEFAULT NULL,
  `frase_portada` varchar(255) DEFAULT NULL,
  `textos_personalizados` text DEFAULT NULL,
  `estado_pago` varchar(20) DEFAULT 'pendiente',
  `mod_rsvp_whatsapp` int(25) DEFAULT NULL,
  `playlist_modo` enum('formulario','enlace') NOT NULL DEFAULT 'formulario',
  `playlist_url` varchar(255) DEFAULT NULL,
  `linea_tiempo_estilo` enum('vertical','horizontal') NOT NULL DEFAULT 'vertical',
  `muro_moderacion` tinyint(1) NOT NULL DEFAULT 0,
  `mod_pagar_evento` tinyint(1) DEFAULT 0,
  `momento2_nombre` varchar(100) DEFAULT NULL,
  `momento2_fecha` date DEFAULT NULL,
  `momento2_hora` time DEFAULT NULL,
  `momento2_direccion_maps` text DEFAULT NULL,
  `mod_ubi_calen` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tarjetas`
--

INSERT INTO `tarjetas` (`id_tarjeta`, `id_cliente`, `id_plantilla`, `tipo_evento_id`, `estilo_visual_id`, `mod_cuenta_regresiva`, `mod_musica`, `mod_regalos`, `mod_dress_code`, `mod_linea_tiempo`, `mod_book_fotos`, `mod_compartir_fotos`, `mod_rsvp_interno`, `mod_muro_deseos`, `mod_playlist`, `nombres_portada`, `fecha_evento`, `hora_evento`, `direccion_maps`, `link_musica`, `datos_bancarios`, `mensaje_regalo`, `dress_code_texto`, `book_texto`, `link_galeria_externa`, `telefono_whatsapp`, `fecha_limite_rsvp`, `frase_portada`, `textos_personalizados`, `estado_pago`, `mod_rsvp_whatsapp`, `playlist_modo`, `playlist_url`, `linea_tiempo_estilo`, `muro_moderacion`, `mod_pagar_evento`, `momento2_nombre`, `momento2_fecha`, `momento2_hora`, `momento2_direccion_maps`, `mod_ubi_calen`) VALUES
(33, 1, 1, 1, 6, 1, 0, 1, 1, 1, 1, 1, 1, 1, 1, 'Celi y Juan', '2026-08-29', '21:00:00', 'http://maps.app.goo.gl/', 'https://www.youtube.com/watch?v=4EPIarNSvxI&list=PLiA0M6kdGQbDAVhL45eKV91VChBFpdqOD', 'LOBO.ARTE.CUBA', 'El mejor regalo es tu presencia, pero si querés ayudarnos...', 'Deportivo/relajado - Look deportivo y cómodo.', 'Mis mejores momentos', 'https://photos.app.goo.gl/qajqUiiF5jCDrVvZA', NULL, '2026-08-15', 'Tu presencia será el mejor regalo para este día inolvidable.', '{\"titulo_rsvp\":\"Confirmación de asistencia\",\"subtitulo_rsvp\":\"Tu respuesta nos ayuda a organizarnos mejor!\",\"muro_titulo\":\"Mensaje para ellos...\"}', 'pendiente', 1, 'enlace', 'https://youtube...', 'horizontal', 1, 1, NULL, NULL, NULL, NULL, 1),
(44, 1, 1, 6, 2, 1, 0, 1, 0, 0, 0, 1, 1, 1, 1, 'Sofia', '2026-09-20', '12:00:00', 'https://maps.app.goo.gl/BJMwtuYjsubdVbie8', NULL, 'LOBO.ARTE.CUBA', 'El mejor regalo es tu presencia, pero si querés ayudarnos...', NULL, NULL, 'https://photos.app.goo.gl/qajqUiiF5jCDrVvZA', NULL, NULL, 'Acompañanos en un momento lleno de luz y esperanza.', NULL, 'pendiente', 1, 'formulario', NULL, '', 0, 1, NULL, NULL, NULL, NULL, 0),
(46, 1, 1, 6, 1, 1, 0, 1, 0, 0, 1, 1, 1, 1, 1, 'Camila', '2026-09-20', '20:00:00', 'https://maps.app.goo.gl/BJMwtuYjsubdVbie8', NULL, 'LOBO.ARTE.CUBA', 'El mejor regalo es tu presencia, pero si querés ayudarnos...', NULL, 'Un poco de mi...', 'https://photos.app.goo.gl/qajqUiiF5jCDrVvZA', '3516291269', NULL, 'Un día de fe, amor y bendiciones para compartir en familia.', NULL, 'pendiente', 1, 'formulario', NULL, '', 0, 1, NULL, NULL, NULL, NULL, 1),
(50, 1, 1, 1, 6, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 'Pedro y Ana', '2026-09-30', '21:00:00', 'https://maps.app.goo.gl/BJMwtuYjsubdVbie8', 'https://www.youtube.com/watch?v=0pepSaUkfMM&list=RD0pepSaUkfMM&start_radio=1', 'LOBO.ARTE.CUBA', 'El mejor regalo es tu presencia, pero si querés ayudarnos...', 'Colores tierra', 'Un poco de mi...', 'https://photos.app.goo.gl/qajqUiiF5jCDrVvZA', NULL, NULL, NULL, NULL, 'pendiente', 1, 'formulario', NULL, 'vertical', 0, 1, NULL, NULL, NULL, NULL, 1),
(51, 1, 1, 2, 2, 1, 0, 1, 0, 0, 0, 0, 0, 1, 0, 'Jose', '2026-09-30', '22:00:00', NULL, NULL, 'LOBO.ARTE.CUBA', 'El mejor regalo es tu presencia, pero si querés ayudarnos...', NULL, NULL, NULL, '3516291269', NULL, 'Vení y la vamos a pasar genial!', NULL, 'pendiente', 1, 'formulario', NULL, 'vertical', 0, 0, NULL, NULL, NULL, NULL, 0),
(52, 1, 1, 2, 2, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 'Pedro', '2026-09-30', '20:00:00', 'https://maps.app.goo.gl/BJMwtuYjsubdVbie8', 'https://www.youtube.com/watch?v=0pepSaUkfMM&list=RD0pepSaUkfMM&start_radio=1', 'LOBO.ARTE.CUBA', 'El mejor regalo es tu presencia, pero si querés ayudarnos...', 'Deportivo/relajado - Look deportivo y cómodo.', 'Mis mejores momentos', 'https://photos.app.goo.gl/qajqUiiF5jCDrVvZA', '3516291269', '2026-09-10', 'Un año más de historias, risas y momentos para celebrar juntos.', NULL, 'pendiente', 1, 'enlace', 'https://youtube...', 'vertical', 0, 1, NULL, NULL, NULL, NULL, 1),
(53, 1, 1, 1, 6, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 'juan y juana', '2026-10-10', '21:00:00', 'https://maps.app.goo.gl/BJMwtuYjsubdVbie8', 'https://www.youtube.com/watch?v=OvwA7lyFED0&list=RDOvwA7lyFED0&start_radio=1&rv=6rk-zpAj7w8', 'LOBO.ARTE.CUBA', 'El mejor regalo es tu presencia, pero si querés ayudarnos...', 'Elegante Sport - Elegante pero relajado; ideal para eventos sociales.', 'Mis mejores momentos', 'https://photos.app.goo.gl/qajqUiiF5jCDrVvZA', '3516291269', '2026-09-01', 'Nos casamos! Tu presencia será el mejor regalo para este día inolvidable.', NULL, 'pendiente', 1, 'formulario', 'https://youtube...', 'horizontal', 0, 1, NULL, NULL, NULL, NULL, 1),
(56, 1, 1, 2, 2, 1, 1, 0, 1, 0, 0, 0, 0, 1, 0, 'Jose', '2026-10-15', '20:00:00', 'https://maps.app.goo.gl/BJMwtuYjsubdVbie8', 'https://www.youtube.com/watch?v=0pepSaUkfMM&list=RD0pepSaUkfMM&start_radio=1', NULL, NULL, 'Cómodo y moderno, sin perder la presentación.', NULL, NULL, '3516291269', NULL, 'Reservá la fecha: la torta, la música y la diversión ya están confirmadas.', NULL, 'pendiente', 1, 'formulario', NULL, 'vertical', 0, 0, NULL, NULL, NULL, NULL, 1),
(57, 1, 1, 1, 2, 0, 1, 0, 0, 0, 0, 0, 1, 1, 0, 'juan y juana', '2026-12-12', '20:00:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Dos caminos se unen para escribir una misma historia. Si! nos casamos!', NULL, 'pendiente', 1, 'formulario', NULL, 'vertical', 0, 0, NULL, NULL, NULL, NULL, 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipos_evento`
--

CREATE TABLE `tipos_evento` (
  `id_tipos_evento` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `label_portada` varchar(100) DEFAULT NULL,
  `placeholder_portada` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tipos_evento`
--

INSERT INTO `tipos_evento` (`id_tipos_evento`, `nombre`, `label_portada`, `placeholder_portada`) VALUES
(1, 'boda', 'Nombres de los Novios', 'Ej: Ana y Juan'),
(2, 'cumpleaños', 'Nombre del Cumpleañero/a', 'Ej: Mis 50 años'),
(4, 'deportivo', 'Nombre del Torneo', 'Ej: Copa San Ignacio'),
(6, 'bautismo', 'Nombre del bautizado/a', 'Ej: El bautismo de Sofía');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id_cliente`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indices de la tabla `config_pagos`
--
ALTER TABLE `config_pagos`
  ADD PRIMARY KEY (`id_config`),
  ADD KEY `fk_config_tarjeta` (`tarjeta_id`);

--
-- Indices de la tabla `costos`
--
ALTER TABLE `costos`
  ADD PRIMARY KEY (`id_costo`),
  ADD UNIQUE KEY `modulo_key` (`modulo_key`);

--
-- Indices de la tabla `estilos_visuales`
--
ALTER TABLE `estilos_visuales`
  ADD PRIMARY KEY (`id_estilos_visuales`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `frases_sugeridas`
--
ALTER TABLE `frases_sugeridas`
  ADD PRIMARY KEY (`id_frase`),
  ADD KEY `fk_frases_tipo` (`tipo_evento_id`);

--
-- Indices de la tabla `linea_tiempo_hitos`
--
ALTER TABLE `linea_tiempo_hitos`
  ADD PRIMARY KEY (`id_hito`),
  ADD KEY `fk_lt_tarjeta` (`tarjeta_id`);

--
-- Indices de la tabla `muro_deseos`
--
ALTER TABLE `muro_deseos`
  ADD PRIMARY KEY (`id_deseo`),
  ADD KEY `fk_muro_tarjeta` (`tarjeta_id`);

--
-- Indices de la tabla `pagos_evento`
--
ALTER TABLE `pagos_evento`
  ADD PRIMARY KEY (`id_pago`),
  ADD KEY `fk_pago_tarjeta` (`tarjeta_id`);

--
-- Indices de la tabla `planes`
--
ALTER TABLE `planes`
  ADD PRIMARY KEY (`id_planes`);

--
-- Indices de la tabla `plantillas`
--
ALTER TABLE `plantillas`
  ADD PRIMARY KEY (`id_plantilla`);

--
-- Indices de la tabla `playlist_sugerencias`
--
ALTER TABLE `playlist_sugerencias`
  ADD PRIMARY KEY (`id_sugerencia`),
  ADD KEY `playlist_fk_tarjeta` (`tarjeta_id`);

--
-- Indices de la tabla `rsvp_acompanantes`
--
ALTER TABLE `rsvp_acompanantes`
  ADD PRIMARY KEY (`id_acompanante`),
  ADD KEY `rsvp_acomp_fk_rsvp` (`id_rsvp`);

--
-- Indices de la tabla `rsvp_respuestas`
--
ALTER TABLE `rsvp_respuestas`
  ADD PRIMARY KEY (`id_rsvp`),
  ADD KEY `idx_tarjeta_estado` (`tarjeta_id`,`estado`);

--
-- Indices de la tabla `tarjetas`
--
ALTER TABLE `tarjetas`
  ADD PRIMARY KEY (`id_tarjeta`),
  ADD KEY `fk_tarjetas_plantilla` (`id_plantilla`),
  ADD KEY `fk_tarjetas_tipo` (`tipo_evento_id`),
  ADD KEY `fk_tarjetas_estilo` (`estilo_visual_id`);

--
-- Indices de la tabla `tipos_evento`
--
ALTER TABLE `tipos_evento`
  ADD PRIMARY KEY (`id_tipos_evento`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id_cliente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `config_pagos`
--
ALTER TABLE `config_pagos`
  MODIFY `id_config` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de la tabla `costos`
--
ALTER TABLE `costos`
  MODIFY `id_costo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de la tabla `estilos_visuales`
--
ALTER TABLE `estilos_visuales`
  MODIFY `id_estilos_visuales` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `frases_sugeridas`
--
ALTER TABLE `frases_sugeridas`
  MODIFY `id_frase` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT de la tabla `linea_tiempo_hitos`
--
ALTER TABLE `linea_tiempo_hitos`
  MODIFY `id_hito` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `muro_deseos`
--
ALTER TABLE `muro_deseos`
  MODIFY `id_deseo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `pagos_evento`
--
ALTER TABLE `pagos_evento`
  MODIFY `id_pago` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `planes`
--
ALTER TABLE `planes`
  MODIFY `id_planes` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `plantillas`
--
ALTER TABLE `plantillas`
  MODIFY `id_plantilla` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `playlist_sugerencias`
--
ALTER TABLE `playlist_sugerencias`
  MODIFY `id_sugerencia` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `rsvp_acompanantes`
--
ALTER TABLE `rsvp_acompanantes`
  MODIFY `id_acompanante` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `rsvp_respuestas`
--
ALTER TABLE `rsvp_respuestas`
  MODIFY `id_rsvp` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `tarjetas`
--
ALTER TABLE `tarjetas`
  MODIFY `id_tarjeta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT de la tabla `tipos_evento`
--
ALTER TABLE `tipos_evento`
  MODIFY `id_tipos_evento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `config_pagos`
--
ALTER TABLE `config_pagos`
  ADD CONSTRAINT `fk_config_tarjeta` FOREIGN KEY (`tarjeta_id`) REFERENCES `tarjetas` (`id_tarjeta`) ON DELETE CASCADE;

--
-- Filtros para la tabla `frases_sugeridas`
--
ALTER TABLE `frases_sugeridas`
  ADD CONSTRAINT `fk_frases_tipo` FOREIGN KEY (`tipo_evento_id`) REFERENCES `tipos_evento` (`id_tipos_evento`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `linea_tiempo_hitos`
--
ALTER TABLE `linea_tiempo_hitos`
  ADD CONSTRAINT `fk_lt_tarjeta` FOREIGN KEY (`tarjeta_id`) REFERENCES `tarjetas` (`id_tarjeta`) ON DELETE CASCADE;

--
-- Filtros para la tabla `muro_deseos`
--
ALTER TABLE `muro_deseos`
  ADD CONSTRAINT `fk_muro_tarjeta` FOREIGN KEY (`tarjeta_id`) REFERENCES `tarjetas` (`id_tarjeta`) ON DELETE CASCADE;

--
-- Filtros para la tabla `pagos_evento`
--
ALTER TABLE `pagos_evento`
  ADD CONSTRAINT `fk_pago_tarjeta` FOREIGN KEY (`tarjeta_id`) REFERENCES `tarjetas` (`id_tarjeta`) ON DELETE CASCADE;

--
-- Filtros para la tabla `playlist_sugerencias`
--
ALTER TABLE `playlist_sugerencias`
  ADD CONSTRAINT `playlist_fk_tarjeta` FOREIGN KEY (`tarjeta_id`) REFERENCES `tarjetas` (`id_tarjeta`) ON DELETE CASCADE;

--
-- Filtros para la tabla `rsvp_acompanantes`
--
ALTER TABLE `rsvp_acompanantes`
  ADD CONSTRAINT `rsvp_acomp_fk_rsvp` FOREIGN KEY (`id_rsvp`) REFERENCES `rsvp_respuestas` (`id_rsvp`) ON DELETE CASCADE;

--
-- Filtros para la tabla `rsvp_respuestas`
--
ALTER TABLE `rsvp_respuestas`
  ADD CONSTRAINT `rsvp_resp_fk_tarjeta` FOREIGN KEY (`tarjeta_id`) REFERENCES `tarjetas` (`id_tarjeta`) ON DELETE CASCADE;

--
-- Filtros para la tabla `tarjetas`
--
ALTER TABLE `tarjetas`
  ADD CONSTRAINT `fk_tarjetas_estilo` FOREIGN KEY (`estilo_visual_id`) REFERENCES `estilos_visuales` (`id_estilos_visuales`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_tarjetas_plantilla` FOREIGN KEY (`id_plantilla`) REFERENCES `plantillas` (`id_plantilla`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_tarjetas_tipo` FOREIGN KEY (`tipo_evento_id`) REFERENCES `tipos_evento` (`id_tipos_evento`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
