<?php
// ========================================================
// ARCHIVO: conexion.php
// OBJETIVO: Conexión PDO a MySQL (Local XAMPP e InfinityFree)
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
    
    // Auto-actualización de estructura de la tabla motos (agrega columnas si no existen)
    try {
        $cols = $pdo->query("SHOW COLUMNS FROM motos")->fetchAll(PDO::FETCH_COLUMN);
        
        if (!in_array('estado', $cols)) {
            $pdo->exec("ALTER TABLE motos ADD COLUMN estado ENUM('En Espera', 'En Reparacion', 'Terminado', 'Entregado') DEFAULT 'En Espera'");
        }
        if (!in_array('falla_reportada', $cols)) {
            $pdo->exec("ALTER TABLE motos ADD COLUMN falla_reportada TEXT NULL");
        }
        if (!in_array('arreglo_realizado', $cols)) {
            $pdo->exec("ALTER TABLE motos ADD COLUMN arreglo_realizado TEXT NULL");
        }
        if (!in_array('repuestos', $cols)) {
            $pdo->exec("ALTER TABLE motos ADD COLUMN repuestos TEXT NULL");
        }
        if (!in_array('precio_estimado', $cols)) {
            $pdo->exec("ALTER TABLE motos ADD COLUMN precio_estimado DECIMAL(10,2) DEFAULT 0.00");
        }
        if (!in_array('precio_final', $cols)) {
            $pdo->exec("ALTER TABLE motos ADD COLUMN precio_final DECIMAL(10,2) DEFAULT 0.00");
        }
        if (!in_array('fecha_ingreso', $cols)) {
            $pdo->exec("ALTER TABLE motos ADD COLUMN fecha_ingreso DATETIME DEFAULT CURRENT_TIMESTAMP");
        }
        if (!in_array('fecha_prometida', $cols)) {
            $pdo->exec("ALTER TABLE motos ADD COLUMN fecha_prometida DATE NULL");
        }
        if (!in_array('fecha_entrega', $cols)) {
            $pdo->exec("ALTER TABLE motos ADD COLUMN fecha_entrega DATETIME NULL");
        }
    } catch (Exception $ex) {
        // Continúa normalmente si ya están creadas
    }

} catch (PDOException $e) {
    echo "<h2 style='color: red; font-family: sans-serif;'>❌ Error de conexión: " . htmlspecialchars($e->getMessage()) . "</h2>";
    exit;
}
?>