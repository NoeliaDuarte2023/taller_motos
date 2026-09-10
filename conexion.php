<?php
// Configuración de la base de datos
$host     = 'localhost';
$db       = 'taller_motos';
$user     = 'root';
$password = '';
$charset  = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$opciones = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $password, $opciones);
    // Conexión exitosa y lista para usarse
} catch (PDOException $e) {
    echo "<h2 style='color: red; font-family: sans-serif;'>❌ Error de conexión: " . $e->getMessage() . "</h2>";
    exit;
}
?>