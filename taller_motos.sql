-- ============================================================
-- Sistema Web de Gestión para Taller de Motos (Programación III)
-- Estructura de la base de datos + datos de ejemplo ficticios
-- Uso: crear la base `taller_motos` e importar este archivo en phpMyAdmin.
-- ============================================================

CREATE DATABASE IF NOT EXISTS `taller_motos` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `taller_motos`;

-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `taller_motos`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

CREATE TABLE `clientes` (
  `id_cliente` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `telefono` varchar(20) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `direccion` varchar(200) DEFAULT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Datos de ejemplo (ficticios)
INSERT INTO `clientes` (`id_cliente`, `nombre`, `apellido`, `telefono`, `email`, `direccion`) VALUES
(1, 'Juan', 'Pérez', '1122334455', 'juan.perez@email.com', 'Av. San Martín 1234'),
(2, 'Lucía', 'Fernández', '3794112233', 'lucia.fernandez@email.com', 'Av. Libertad 1450'),
(3, 'Martín', 'Benítez', '3794556677', 'martin.benitez@email.com', 'Calle Salta 320');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mecanicos`
--

CREATE TABLE `mecanicos` (
  `id_mecanico` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `especialidad` varchar(100) DEFAULT NULL,
  `estado` enum('Activo','Inactivo') DEFAULT 'Activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Datos de ejemplo (ficticios)
INSERT INTO `mecanicos` (`id_mecanico`, `nombre`, `telefono`, `especialidad`, `estado`) VALUES
(1, 'Carlos Méndez', '1144556677', 'Motores y Transmisión', 'Activo'),
(2, 'Benjamín Rossi', '3704998877', 'Electricidad e Inyección', 'Activo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `motos`
--

CREATE TABLE `motos` (
  `id_moto` int(11) NOT NULL,
  `id_cliente` int(11) NOT NULL,
  `marca` varchar(50) NOT NULL,
  `modelo` varchar(100) NOT NULL,
  `anio` int(11) DEFAULT NULL,
  `patente` varchar(20) NOT NULL,
  `color` varchar(30) DEFAULT NULL,
  `kilometraje` int(11) DEFAULT 0,
  `observaciones` text DEFAULT NULL,
  `estado` enum('En Espera','En Reparacion','Terminado','Entregado') DEFAULT 'En Espera',
  `falla_reportada` text DEFAULT NULL,
  `arreglo_realizado` text DEFAULT NULL,
  `repuestos` text DEFAULT NULL,
  `precio_estimado` decimal(10,2) DEFAULT 0.00,
  `precio_final` decimal(10,2) DEFAULT 0.00,
  `fecha_ingreso` datetime DEFAULT current_timestamp(),
  `fecha_prometida` date DEFAULT NULL,
  `fecha_entrega` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Datos de ejemplo (ficticios)
INSERT INTO `motos` (`id_moto`, `id_cliente`, `marca`, `modelo`, `anio`, `patente`, `color`, `kilometraje`) VALUES
(1, 2, 'Honda', 'CB 250 Twister', 2021, 'A123BCD', 'Rojo', 15400),
(2, 1, 'Yamaha', 'YBR 125', 2013, 'OBJ123', 'Negro', 23000),
(3, 3, 'Motomel', 'Skua 150', 2020, 'AF125', 'Gris', 8000),
(4, 3, 'Honda', 'Biz 125', 2019, 'GR555', 'Verde', 12000);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `repuestos`
--

CREATE TABLE `repuestos` (
  `id_repuesto` int(11) NOT NULL,
  `codigo` varchar(50) NOT NULL,
  `descripcion` varchar(200) NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL DEFAULT 0.00,
  `stock` int(11) NOT NULL DEFAULT 0,
  `stock_minimo` int(11) NOT NULL DEFAULT 2
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Datos de ejemplo (ficticios)
INSERT INTO `repuestos` (`id_repuesto`, `codigo`, `descripcion`, `precio_unitario`, `stock`, `stock_minimo`) VALUES
(1, 'FIL-HON-01', 'Filtro de Aceite Honda CB 250', 4800.00, 10, 3),
(2, 'PAS-FRE-01', 'Pastillas de Freno Delanteras Yamaha FZ', 8500.00, 6, 2),
(3, 'BUJ-NGK-02', 'Bujía NGK CPR8EA-2', 3200.00, 15, 4);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `trabajos`
--

CREATE TABLE `trabajos` (
  `id_trabajo` int(11) NOT NULL,
  `id_moto` int(11) NOT NULL,
  `falla_reportada` text NOT NULL,
  `diagnostico` text DEFAULT NULL,
  `arreglo_realizado` text DEFAULT NULL,
  `repuestos` text DEFAULT NULL,
  `precio_estimado` decimal(10,2) DEFAULT 0.00,
  `precio_final` decimal(10,2) DEFAULT 0.00,
  `fecha_ingreso` datetime DEFAULT current_timestamp(),
  `fecha_prometida` date DEFAULT NULL,
  `fecha_entrega` datetime DEFAULT NULL,
  `estado` enum('En Espera','En Reparacion','Terminado','Entregado') DEFAULT 'En Espera',
  `observaciones` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Datos de ejemplo (ficticios)
INSERT INTO `trabajos` (`id_trabajo`, `id_moto`, `falla_reportada`, `arreglo_realizado`, `repuestos`, `precio_estimado`, `precio_final`, `fecha_ingreso`, `estado`) VALUES
(1, 1, 'Cambio de aceite, filtro y regulación de válvulas', 'Afinación completa de motor y cambio de aceite', 'Aceite Motul 5100, Filtro de aceite original', 45000.00, 45000.00, '2026-10-05 10:30:00', 'En Reparacion'),
(2, 2, 'No arranca en frío, limpiar carburador', 'Limpieza y carburación completa con cambio de bujía', 'Bujía NGK CR7HSA', 28000.00, 28000.00, '2026-10-06 14:15:00', 'Terminado'),
(3, 3, 'Ruido en la transmisión y cambio de pastillas de freno', 'Cambio de kit de transmisión y pastillas traseras', 'Corona, piñón, cadena dorada reforzada y pastillas', 65000.00, 65000.00, '2026-10-01 09:00:00', 'Entregado'),
(4, 4, 'Falla en la luz delantera y guiño izquierdo', NULL, NULL, 15000.00, 0.00, '2026-10-16 08:30:00', 'En Espera');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id_cliente`);

--
-- Indices de la tabla `mecanicos`
--
ALTER TABLE `mecanicos`
  ADD PRIMARY KEY (`id_mecanico`);

--
-- Indices de la tabla `motos`
--
ALTER TABLE `motos`
  ADD PRIMARY KEY (`id_moto`),
  ADD UNIQUE KEY `uk_patente` (`patente`),
  ADD KEY `idx_id_cliente` (`id_cliente`);

--
-- Indices de la tabla `repuestos`
--
ALTER TABLE `repuestos`
  ADD PRIMARY KEY (`id_repuesto`),
  ADD UNIQUE KEY `uk_codigo` (`codigo`);

--
-- Indices de la tabla `trabajos`
--
ALTER TABLE `trabajos`
  ADD PRIMARY KEY (`id_trabajo`),
  ADD KEY `id_moto` (`id_moto`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id_cliente` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `mecanicos`
--
ALTER TABLE `mecanicos`
  MODIFY `id_mecanico` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `motos`
--
ALTER TABLE `motos`
  MODIFY `id_moto` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `repuestos`
--
ALTER TABLE `repuestos`
  MODIFY `id_repuesto` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `trabajos`
--
ALTER TABLE `trabajos`
  MODIFY `id_trabajo` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `motos`
--
ALTER TABLE `motos`
  ADD CONSTRAINT `fk_motos_cliente` FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id_cliente`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `trabajos`
--
ALTER TABLE `trabajos`
  ADD CONSTRAINT `fk_trabajos_motos` FOREIGN KEY (`id_moto`) REFERENCES `motos` (`id_moto`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
