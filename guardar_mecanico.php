<?php
require_once 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $nombre       = trim($_POST['nombre'] ?? '');
    $telefono     = trim($_POST['telefono'] ?? null);
    $especialidad = trim($_POST['especialidad'] ?? null);
    $estado       = $_POST['estado'] ?? 'Activo';

    if (!empty($nombre)) {
        
        try {
            $sql = "INSERT INTO mecanicos (nombre, telefono, especialidad, estado) 
                    VALUES (:nombre, :telefono, :especialidad, :estado)";
            
            $stmt = $pdo->prepare($sql);
            
            $stmt->execute([
                ':nombre'       => $nombre,
                ':telefono'     => $telefono,
                ':especialidad' => $especialidad,
                ':estado'       => $estado
            ]);

            header('Location: mecanicos.php');
            exit;

        } catch (PDOException $e) {
            echo "<h3 style='color:red;'>Error al guardar el mecánico: " . $e->getMessage() . "</h3>";
            echo "<br><a href='mecanicos.php'>Volver a Mecánicos</a>";
            exit;
        }

    } else {
        echo "<h3 style='color:orange;'>Por favor escribe al menos el nombre del mecánico.</h3>";
        echo "<br><a href='mecanicos.php'>Volver a Mecánicos</a>";
        exit;
    }

} else {
    header('Location: mecanicos.php');
    exit;
}
?>