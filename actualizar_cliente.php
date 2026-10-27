<?php
require_once 'conexion.php';
require_once 'validaciones.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $id_cliente = $_POST['id_cliente'] ?? '';
    // Validación de datos (ver validaciones.php)
    $errores   = [];
    $nombre    = v_nombre($_POST['nombre'] ?? '', 'El nombre', $errores, true, 100);
    $apellido  = v_nombre($_POST['apellido'] ?? '', 'El apellido', $errores, true, 100);
    $telefono  = v_telefono($_POST['telefono'] ?? '', 'El teléfono', $errores, true);
    $email     = v_email($_POST['email'] ?? '', $errores);
    $direccion = v_direccion($_POST['direccion'] ?? '', $errores, false);
    if (!empty($errores)) {
        v_mostrar_errores($errores, 'Datos del cliente inválidos');
    }

    if (!empty($id_cliente) && !empty($nombre) && !empty($apellido) && !empty($telefono)) {
        
        try {
            $sql = "UPDATE clientes 
                    SET nombre = :nombre, 
                        apellido = :apellido, 
                        telefono = :telefono, 
                        email = :email, 
                        direccion = :direccion 
                    WHERE id_cliente = :id";
            
            $stmt = $pdo->prepare($sql);
            
            $stmt->execute([
                ':nombre'    => $nombre,
                ':apellido'  => $apellido,
                ':telefono'  => $telefono,
                ':email'     => $email,
                ':direccion' => $direccion,
                ':id'        => $id_cliente
            ]);

            // Redireccionamos a la lista
            header('Location: clientes.php');
            exit;

        } catch (PDOException $e) {
            echo "<h3 style='color:red;'>Error al actualizar el cliente: " . $e->getMessage() . "</h3>";
            echo "<a href='clientes.php'>Volver</a>";
            exit;
        }

    } else {
        echo "<h3 style='color:orange;'>Por favor completa los campos obligatorios.</h3>";
        echo "<a href='clientes.php'>Volver</a>";
        exit;
    }

} else {
    header('Location: clientes.php');
    exit;
}
?>