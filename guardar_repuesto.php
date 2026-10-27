<?php
require_once 'conexion.php';
require_once 'validaciones.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Validación de datos (ver validaciones.php)
    $errores         = [];
    $codigo          = v_codigo($_POST['codigo'] ?? '', 'El código', $errores);
    $descripcion     = v_texto($_POST['descripcion'] ?? '', 'La descripción', $errores, true, 3, 150);
    $precio_unitario = v_decimal($_POST['precio_unitario'] ?? '', 'El precio unitario', $errores, 0, 99999999.99);
    $stock           = v_entero($_POST['stock'] ?? '', 'El stock', $errores, 0, 1000000, 0);
    $stock_minimo    = v_entero($_POST['stock_minimo'] ?? '', 'El stock mínimo', $errores, 0, 1000000, 2);
    if (!empty($errores)) {
        v_mostrar_errores($errores, 'Datos del repuesto inválidos');
    }

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