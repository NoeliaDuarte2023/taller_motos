-- ========================================================
-- BASE DE DATOS: taller_motos
-- SISTEMA DE GESTIÓN Y REPARACIÓN PARA TALLER MECÁNICO
-- LENGUAJES: PHP + MySQL + Bootstrap 5 (ABM Completo)
-- ========================================================

CREATE DATABASE IF NOT EXISTS `taller_motos` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `taller_motos`;

-- --------------------------------------------------------
-- 1. Tabla de Clientes (Dueños de Vehículos)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `clientes` (
  `id_cliente` INT AUTO_INCREMENT PRIMARY KEY,
  `nombre` VARCHAR(100) NOT NULL,
  `apellido` VARCHAR(100) NOT NULL,
  `telefono` VARCHAR(20) NOT NULL,
  `email` VARCHAR(100) NULL,
  `direccion` VARCHAR(200) NULL,
  `fecha_registro` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Datos de prueba para Clientes
INSERT INTO `clientes` (`id_cliente`, `nombre`, `apellido`, `telefono`, `email`, `direccion`) VALUES
(1, 'Juan', 'Pérez', '1122334455', 'juan.perez@email.com', 'Av. San Martín 1234'),
(2, 'Noelia Itatí', 'Duarte', '03705094824', 'noeliaduarte1521@outlook.com', 'Alberdi 268'),
(3, 'Esteban', 'Bacik', '3704586931', 'esteban.bacik@email.com', 'Alberdi 265')
ON DUPLICATE KEY UPDATE `nombre` = VALUES(`nombre`);

-- --------------------------------------------------------
-- 2. Tabla de Motos (Vehículos en Taller y Entregadas)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `motos` (
  `id_moto` INT AUTO_INCREMENT PRIMARY KEY,
  `id_cliente` INT NOT NULL,
  `marca` VARCHAR(50) NOT NULL,
  `modelo` VARCHAR(100) NOT NULL,
  `anio` INT NULL,
  `patente` VARCHAR(20) NOT NULL UNIQUE,
  `color` VARCHAR(30) NULL,
  `kilometraje` INT DEFAULT 0,
  `estado` ENUM('En Espera', 'En Reparacion', 'Terminado', 'Entregado') DEFAULT 'En Espera',
  `falla_reportada` TEXT NULL,
  `arreglo_realizado` TEXT NULL,
  `repuestos` TEXT NULL,
  `precio_estimado` DECIMAL(10,2) DEFAULT 0.00,
  `precio_final` DECIMAL(10,2) DEFAULT 0.00,
  `fecha_ingreso` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `fecha_prometida` DATE NULL,
  `fecha_entrega` DATETIME NULL,
  `observaciones` TEXT NULL,
  FOREIGN KEY (`id_cliente`) REFERENCES `clientes`(`id_cliente`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Datos de prueba para Motos
INSERT INTO `motos` (`id_moto`, `id_cliente`, `marca`, `modelo`, `anio`, `patente`, `color`, `kilometraje`, `estado`, `falla_reportada`, `arreglo_realizado`, `repuestos`, `precio_estimado`, `precio_final`, `fecha_ingreso`) VALUES
(1, 2, 'Honda', 'CB 250 Twister', 2021, 'A123BCD', 'Rojo', 15400, 'En Reparacion', 'Cambio de aceite, filtro y regulación de válvulas', 'Afinación completa de motor y cambio de aceite', 'Aceite Motul 5100, Filtro de aceite original', 45000.00, 45000.00, '2026-10-05 10:30:00'),
(2, 1, 'Yamaha', 'YBR 125', 2013, 'OBJ123', 'Negro', 23000, 'Terminado', 'No arranca en frío, limpiar carburador', 'Limpieza y carburación completa con cambio de bujía', 'Bujía NGK CR7HSA', 28000.00, 28000.00, '2026-10-06 14:15:00'),
(3, 3, 'Motomel', 'Skua 150', 2020, 'AF125', 'Gris', 8000, 'Entregado', 'Ruido en la transmisión y cambio de pastillas de freno', 'Cambio de kit de transmisión y pastillas traseras', 'Corona, piñón, cadena dorada reforzada y pastillas', 65000.00, 65000.00, '2026-10-01 09:00:00')
ON DUPLICATE KEY UPDATE `marca` = VALUES(`marca`);

-- --------------------------------------------------------
-- 3. Tabla de Mecánicos
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `mecanicos` (
  `id_mecanico` INT AUTO_INCREMENT PRIMARY KEY,
  `nombre` VARCHAR(100) NOT NULL,
  `telefono` VARCHAR(20) NULL,
  `especialidad` VARCHAR(100) NULL,
  `estado` ENUM('Activo','Inactivo') DEFAULT 'Activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `mecanicos` (`id_mecanico`, `nombre`, `telefono`, `especialidad`, `estado`) VALUES
(1, 'Carlos Méndez', '1144556677', 'Motores y Transmisión', 'Activo'),
(2, 'Benjamín Rossi', '3704998877', 'Electricidad e Inyección', 'Activo')
ON DUPLICATE KEY UPDATE `nombre` = VALUES(`nombre`);
