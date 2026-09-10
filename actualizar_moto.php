<?php
require_once 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $id_moto       = $_POST['id_moto'] ?? '';
    $id_cliente    = trim($_POST['id_cliente'] ?? '');
    $marca         = trim($_POST['marca'] ?? '');
    $modelo        = trim($_POST['modelo'] ?? '');
    $anio          = !empty($_POST['anio']) ? intval($_POST['anio']) : null;
    $patente       = strtoupper(trim($_POST['patente'] ?? ''));
    $color         = trim($_POST['color'] ?? null);
    $kilometraje   = !empty($_POST['kilometraje']) ? intval($_POST['kilometraje']) : 0;
    $observaciones = trim($_POST['observaciones'] ?? null);

    if (!empty($id_moto) && !empty($id_cliente) && !empty($marca) && !empty($modelo) && !empty($patente)) {
        
        try {
            $sql = "UPDATE motos 
                    SET id_cliente = :id_cliente, 
                        marca = :marca, 
                        modelo = :modelo, 
                        anio = :anio, 
                        patente = :patente, 
                        color = :color, 
                        kilometraje = :kilometraje, 
                        observaciones = :observaciones 
                    WHERE id_moto = :id";
            
            $stmt = $pdo->prepare($sql);
            
            $stmt->execute([
                ':id_cliente'    => $id_cliente,
                ':marca'         => $marca,
                ':modelo'        => $modelo,
                ':anio'          => $anio,
                ':patente'       => $patente,
                ':color'         => $color,
                ':kilometraje'   => $kilometraje,
                ':observaciones' => $observaciones,
                ':id'            => $id_moto
            ]);

            header('Location: motos.php');
            exit;

        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                echo "<h3 style='color:red;'>Error: Ya existe otra moto con esa misma patente.</h3>";
            } else {
                echo "<h3 style='color:red;'>Error al actualizar la moto: " . $e->getMessage() . "</h3>";
            }
            echo "<br><a href='motos.php'>Volver a Motos</a>";
            exit;
        }

    } else {
        echo "<h3 style='color:orange;'>Por favor completa los campos obligatorios.</h3>";
        echo "<br><a href='motos.php'>Volver a Motos</a>";
        exit;
    }

} else {
    header('Location: motos.php');
    exit;
}
?>