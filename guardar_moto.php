<?php
require_once 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Recibimos los datos del formulario
    $id_cliente   = trim($_POST['id_cliente'] ?? '');
    $marca        = trim($_POST['marca'] ?? '');
    $modelo       = trim($_POST['modelo'] ?? '');
    $anio         = !empty($_POST['anio']) ? intval($_POST['anio']) : null;
    $patente      = strtoupper(trim($_POST['patente'] ?? '')); // Lo guardamos en mayúsculas
    $color        = trim($_POST['color'] ?? null);
    $kilometraje  = !empty($_POST['kilometraje']) ? intval($_POST['kilometraje']) : 0;
    $observaciones = trim($_POST['observaciones'] ?? null);

    // Validamos campos obligatorios
    if (!empty($id_cliente) && !empty($marca) && !empty($modelo) && !empty($patente)) {
        
        try {
            // Consulta SQL preparada para insertar la moto
            $sql = "INSERT INTO motos (id_cliente, marca, modelo, anio, patente, color, kilometraje, observaciones) 
                    VALUES (:id_cliente, :marca, :modelo, :anio, :patente, :color, :kilometraje, :observaciones)";
            
            $stmt = $pdo->prepare($sql);
            
            $stmt->execute([
                ':id_cliente'    => $id_cliente,
                ':marca'         => $marca,
                ':modelo'        => $modelo,
                ':anio'          => $anio,
                ':patente'       => $patente,
                ':color'         => $color,
                ':kilometraje'   => $kilometraje,
                ':observaciones' => $observaciones
            ]);

            // Redireccionamos a la lista de motos
            header('Location: motos.php');
            exit;

        } catch (PDOException $e) {
            // Si la patente ya existe (error de clave única 23000 / 1062)
            if ($e->getCode() == 23000) {
                echo "<h3 style='color:red;'>Error: Ya existe una moto registrada con esa misma patente.</h3>";
            } else {
                echo "<h3 style='color:red;'>Error al guardar la moto: " . $e->getMessage() . "</h3>";
            }
            echo "<br><a href='motos.php'>Volver a Motos</a>";
            exit;
        }

    } else {
        echo "<h3 style='color:orange;'>Por favor completa todos los campos obligatorios (Cliente, Marca, Modelo, Patente).</h3>";
        echo "<br><a href='motos.php'>Volver a Motos</a>";
        exit;
    }

} else {
    header('Location: motos.php');
    exit;
}
?>