<?php
require_once 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $codigo          = strtoupper(trim($_POST['codigo'] ?? ''));
    $descripcion     = trim($_POST['descripcion'] ?? '');
    $precio_unitario = !empty($_POST['precio_unitario']) ? floatval($_POST['precio_unitario']) : 0.00;
    $stock           = isset($_POST['stock']) ? intval($_POST['stock']) : 0;
    $stock_minimo    = isset($_POST['stock_minimo']) ? intval($_POST['stock_minimo']) : 2;

    if (!empty($codigo) && !empty($descripcion) && $precio_unitario >= 0) {
        
        try {
            $sql = "INSERT INTO repuestos (codigo, descripcion, precio_unitario, stock, stock_minimo) 
                    VALUES (:codigo, :descripcion, :precio_unitario, :stock, :stock_minimo)";
            
            $stmt = $pdo->prepare($sql);
            
            $stmt->execute([
                ':codigo'          => $codigo,
                ':descripcion'     => $descripcion,
                ':precio_unitario' => $precio_unitario,
                ':stock'           => $stock,
                ':stock_minimo'    => $stock_minimo
            ]);

            header('Location: repuestos.php');
            exit;

        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                echo "<h3 style='color:red;'>Error: Ya existe un repuesto con el código '$codigo'.</h3>";
            } else {
                echo "<h3 style='color:red;'>Error al guardar el repuesto: " . $e->getMessage() . "</h3>";
            }
            echo "<br><a href='repuestos.php'>Volver a Repuestos</a>";
            exit;
        }

    } else {
        echo "<h3 style='color:orange;'>Por favor completa todos los campos obligatorios.</h3>";
        echo "<br><a href='repuestos.php'>Volver a Repuestos</a>";
        exit;
    }

} else {
    header('Location: repuestos.php');
    exit;
}
?>