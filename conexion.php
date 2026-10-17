<?php
// ========================================================
// ARCHIVO: conexion.php
// OBJETIVO: Conexión PDO a MySQL (Local XAMPP e InfinityFree)
// y Auto-migración a 1FN / 2FN (Creación de tabla trabajos)
// ========================================================

$isLocal = in_array($_SERVER['HTTP_HOST'] ?? '', ['localhost', '127.0.0.1']) || php_sapi_name() === 'cli';

if ($isLocal) {
    // 🏠 Configuración Local (XAMPP)
    $host     = 'localhost';
    $db       = 'taller_motos';
    $user     = 'root';
    $password = '';
} else {
    // 🌐 Configuración Servidor (InfinityFree)
    $host     = 'sql303.infinityfree.com';
    $db       = 'if0_42882751_taller_motos';
    $user     = 'if0_42882751';
    $password = 'TU_PASSWORD_AQUI';
}

$charset  = 'utf8mb4';
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$opciones = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $password, $opciones);
    
    // Auto-migración y verificación de estructura para 1FN y 2FN
    try {
        // 1. Asegurar tabla clientes
        $pdo->exec("CREATE TABLE IF NOT EXISTS `clientes` (
          `id_cliente` INT AUTO_INCREMENT PRIMARY KEY,
          `nombre` VARCHAR(100) NOT NULL,
          `apellido` VARCHAR(100) NOT NULL,
          `telefono` VARCHAR(20) NOT NULL,
          `email` VARCHAR(100) NULL,
          `direccion` VARCHAR(200) NULL,
          `fecha_registro` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        // 2. Asegurar tabla motos (entidad vehículo)
        $pdo->exec("CREATE TABLE IF NOT EXISTS `motos` (
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        // 3. Asegurar tabla trabajos (órdenes de servicio - evita anomalías de eliminación)
        $pdo->exec("CREATE TABLE IF NOT EXISTS `trabajos` (
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        // Migración suave: si existen datos viejos en motos con falla_reportada, migrarlos a trabajos si trabajos está vacía
        $countTrabajos = $pdo->query("SELECT COUNT(*) FROM trabajos")->fetchColumn();
        if ($countTrabajos == 0) {
            $colsMotos = $pdo->query("SHOW COLUMNS FROM motos")->fetchAll(PDO::FETCH_COLUMN);
            if (in_array('falla_reportada', $colsMotos)) {
                $pdo->exec("INSERT INTO trabajos (id_moto, falla_reportada, arreglo_realizado, repuestos, precio_estimado, precio_final, fecha_ingreso, fecha_prometida, fecha_entrega, estado)
                            SELECT id_moto, COALESCE(falla_reportada, 'Revisión general'), arreglo_realizado, repuestos, precio_estimado, precio_final, COALESCE(fecha_ingreso, NOW()), fecha_prometida, fecha_entrega, COALESCE(estado, 'En Espera')
                            FROM motos WHERE falla_reportada IS NOT NULL OR estado IS NOT NULL");
            }
        }

    } catch (Exception $ex) {
        // Continúa normalmente
    }

} catch (PDOException $e) {
    echo "<h2 style='color: red; font-family: sans-serif;'>❌ Error de conexión: " . htmlspecialchars($e->getMessage()) . "</h2>";
    exit;
}
?>