<?php
require_once 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $id_mecanico  = $_POST['id_mecanico'] ?? '';
    $nombre       = trim($_POST['nombre'] ?? '');
    $telefono     = trim($_POST['telefono'] ?? null);
    $especialidad = trim($_POST['especialidad'] ?? null);
    $estado       = $_POST['estado'] ?? 'Activo';

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