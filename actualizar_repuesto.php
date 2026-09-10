<?php
require_once 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $id_repuesto     = $_POST['id_repuesto'] ?? '';
    $codigo          = strtoupper(trim($_POST['codigo'] ?? ''));
    $descripcion     = trim($_POST['descripcion'] ?? '');
    $precio_unitario = !empty($_POST['precio_unitario']) ? floatval($_POST['precio_unitario']) : 0.00;
    $stock           = isset($_POST['stock']) ? intval($_POST['stock']) : 0;
    $stock_minimo    = isset($_POST['stock_minimo']) ? intval($_POST['stock_minimo']) : 2;

    if (!empty($id_repuesto) && !empty($codigo) && !empty($descripcion) && $precio_unitario >= 0) {
        
        try {
            $sql = "UPDATE repuestos 
                    SET codigo = :codigo, 
                        descripcion = :descripcion, 
                        precio_unitario = :precio_unitario, 
                        stock = :stock, 
                        stock_minimo = :stock_minimo 
                    WHERE id_repuesto = :id";
            
            $stmt = $pdo->prepare($sql);
            
            $stmt->execute([
                ':codigo'          => $codigo,
                ':descripcion'     => $descripcion,
                ':precio_unitario' => $precio_unitario,
                ':stock'           => $stock,
                ':stock_minimo'    => $stock_minimo,
                ':id'              => $id_repuesto
            ]);

            header('Location: repuestos.php');
            exit;

        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                echo "<h3 style='color:red;'>Error: Ya existe otro repuesto con el código '$codigo'.</h3>";
            } else {
                echo "<h3 style='color:red;'>Error al actualizar el repuesto: " . $e->getMessage() . "</h3>";
            }
            echo "<br><a href='repuestos.php'>Volver a Repuestos</a>";
            exit;
        }

    } else {
        echo "<h3 style='color:orange;'>Por favor completa los campos obligatorios.</h3>";
        echo "<br><a href='repuestos.php'>Volver a Repuestos</a>";
        exit;
    }

} else {
    header('Location: repuestos.php');
    exit;
}
?>