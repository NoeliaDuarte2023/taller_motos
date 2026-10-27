<?php
require_once 'conexion.php';
require_once 'validaciones.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $id_mecanico  = $_POST['id_mecanico'] ?? '';
    // Validación de datos (ver validaciones.php)
    $errores      = [];
    $nombre       = v_nombre($_POST['nombre'] ?? '', 'El nombre del mecánico', $errores, true, 100);
    $telefono     = v_telefono($_POST['telefono'] ?? '', 'El teléfono', $errores, false);
    $especialidad = v_alfanum($_POST['especialidad'] ?? '', 'La especialidad', $errores, false, 100);
    $estado       = v_opcion($_POST['estado'] ?? '', ['Activo', 'Inactivo'], 'El estado', $errores, 'Activo');
    if (!empty($errores)) {
        v_mostrar_errores($errores, 'Datos del mecánico inválidos');
    }

    if (!empty($id_mecanico) && !empty($nombre)) {
        
        try {
            $sql = "UPDATE mecanicos 
                    SET nombre = :nombre, 
                        telefono = :telefono, 
                        especialidad = :especialidad, 
                        estado = :estado 
                    WHERE id_mecanico = :id";
            
            $stmt = $pdo->prepare($sql);
            
            $stmt->execute([
                ':nombre'       => $nombre,
                ':telefono'     => $telefono,
                ':especialidad' => $especialidad,
                ':estado'       => $estado,
                ':id'           => $id_mecanico
            ]);

            header('Location: mecanicos.php');
            exit;

        } catch (PDOException $e) {
            echo "<h3 style='color:red;'>Error al actualizar el mecánico: " . $e->getMessage() . "</h3>";
            echo "<br><a href='mecanicos.php'>Volver a Mecánicos</a>";
            exit;
        }

    } else {
        echo "<h3 style='color:orange;'>Por favor completa los campos obligatorios.</h3>";
        echo "<br><a href='mecanicos.php'>Volver a Mecánicos</a>";
        exit;
    }

} else {
    header('Location: mecanicos.php');
    exit;
}
?>