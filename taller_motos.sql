-- ========================================================
-- BASE DE DATOS: taller_motos
-- SISTEMA DE GESTIÓN Y REPARACIÓN PARA TALLER MECÁNICO
-- NORMALIZACIÓN: 1FN, 2FN y 3FN (Cero anomalías de eliminación)
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

-- --------------------------------------------------------
-- 2. Tabla de Motos (Entidad Vehículo - 1FN y 2FN)
-- Cada moto le pertenece a un cliente, independientemente 
-- de si tiene un trabajo en el taller o no.
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
  `fecha_registro` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`id_cliente`) REFERENCES `clientes`(`id_cliente`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 3. Tabla de Trabajos / Reparaciones en Taller (1FN y 2FN)
-- Registra cada orden de trabajo o servicio que ingresa al taller.
-- Si se cancela o elimina un trabajo, la moto y el cliente NO se borran.
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `trabajos` (
  `id_trabajo` INT AUTO_INCREMENT PRIMARY KEY,
  `id_moto` INT NOT NULL,
  `falla_reportada` TEXT NOT NULL,
  `diagnostico` TEXT NULL,
  `arreglo_realizado` TEXT NULL,
  `repuestos` TEXT NULL,
  `precio_estimado` DECIMAL(10,2) DEFAULT 0.00,
  `precio_final` DECIMAL(10,2) DEFAULT 0.00,
  `fecha_ingreso` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `fecha_prometida` DATE NULL,
  `fecha_entrega` DATETIME NULL,
  `estado` ENUM('En Espera', 'En Reparacion', 'Terminado', 'Entregado') DEFAULT 'En Espera',
  `observaciones` TEXT NULL,
  FOREIGN KEY (`id_moto`) REFERENCES `motos`(`id_moto`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 4. Tabla de Mecánicos
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `mecanicos` (
  `id_mecanico` INT AUTO_INCREMENT PRIMARY KEY,
  `nombre` VARCHAR(100) NOT NULL,
  `telefono` VARCHAR(20) NULL,
  `especialidad` VARCHAR(100) NULL,
  `estado` ENUM('Activo','Inactivo') DEFAULT 'Activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================================
-- DATOS DE PRUEBA CONSISTENTES
-- ========================================================

-- Clientes
INSERT INTO `clientes` (`id_cliente`, `nombre`, `apellido`, `telefono`, `email`, `direccion`) VALUES
(1, 'Juan', 'Pérez', '1122334455', 'juan.perez@email.com', 'Av. San Martín 1234'),
(2, 'Noelia Itatí', 'Duarte', '03705094824', 'noeliaduarte1521@outlook.com', 'Alberdi 268'),
(3, 'Esteban', 'Bacik', '3704586931', 'esteban.bacik@email.com', 'Alberdi 265')
ON DUPLICATE KEY UPDATE `nombre` = VALUES(`nombre`);

-- Motos (Vehículos registrados)
INSERT INTO `motos` (`id_moto`, `id_cliente`, `marca`, `modelo`, `anio`, `patente`, `color`, `kilometraje`) VALUES
(1, 2, 'Honda', 'CB 250 Twister', 2021, 'A123BCD', 'Rojo', 15400),
(2, 1, 'Yamaha', 'YBR 125', 2013, 'OBJ123', 'Negro', 23000),
(3, 3, 'Motomel', 'Skua 150', 2020, 'AF125', 'Gris', 8000),
(4, 3, 'Honda', 'Biz 125', 2019, 'GR555', 'Verde', 12000)
ON DUPLICATE KEY UPDATE `marca` = VALUES(`marca`);

-- Trabajos en Taller (Reparaciones vinculadas a motos)
INSERT INTO `trabajos` (`id_trabajo`, `id_moto`, `falla_reportada`, `arreglo_realizado`, `repuestos`, `precio_estimado`, `precio_final`, `fecha_ingreso`, `estado`) VALUES
(1, 1, 'Cambio de aceite, filtro y regulación de válvulas', 'Afinación completa de motor y cambio de aceite', 'Aceite Motul 5100, Filtro de aceite original', 45000.00, 45000.00, '2026-10-05 10:30:00', 'En Reparacion'),
(2, 2, 'No arranca en frío, limpiar carburador', 'Limpieza y carburación completa con cambio de bujía', 'Bujía NGK CR7HSA', 28000.00, 28000.00, '2026-10-06 14:15:00', 'Terminado'),
(3, 3, 'Ruido en la transmisión y cambio de pastillas de freno', 'Cambio de kit de transmisión y pastillas traseras', 'Corona, piñón, cadena dorada reforzada y pastillas', 65000.00, 65000.00, '2026-10-01 09:00:00', 'Entregado'),
(4, 4, 'Falla en la luz delantera y guiño izquierdo', NULL, NULL, 15000.00, 0.00, '2026-10-16 08:30:00', 'En Espera')
ON DUPLICATE KEY UPDATE `falla_reportada` = VALUES(`falla_reportada`);

-- Mecánicos
INSERT INTO `mecanicos` (`id_mecanico`, `nombre`, `telefono`, `especialidad`, `estado`) VALUES
(1, 'Carlos Méndez', '1144556677', 'Motores y Transmisión', 'Activo'),
(2, 'Benjamín Rossi', '3704998877', 'Electricidad e Inyección', 'Activo')
ON DUPLICATE KEY UPDATE `nombre` = VALUES(`nombre`);
