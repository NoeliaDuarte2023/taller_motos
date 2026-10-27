<?php
require_once 'conexion.php';
require_once 'validaciones.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Validación de datos (ver validaciones.php)
    $errores      = [];
    $nombre       = v_nombre($_POST['nombre'] ?? '', 'El nombre del mecánico', $errores, true, 100);
    $telefono     = v_telefono($_POST['telefono'] ?? '', 'El teléfono', $errores, false);
    $especialidad = v_alfanum($_POST['especialidad'] ?? '', 'La especialidad', $errores, false, 100);
    $estado       = v_opcion($_POST['estado'] ?? '', ['Activo', 'Inactivo'], 'El estado', $errores, 'Activo');
    if (!empty($errores)) {
        v_mostrar_errores($errores, 'Datos del mecánico inválidos');
    }

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