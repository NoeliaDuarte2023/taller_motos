<?php
require_once 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $id_cliente = $_POST['id_cliente'] ?? '';
    $nombre     = trim($_POST['nombre'] ?? '');
    $apellido   = trim($_POST['apellido'] ?? '');
    $telefono   = trim($_POST['telefono'] ?? '');
    $email      = trim($_POST['email'] ?? null);
    $direccion  = trim($_POST['direccion'] ?? null);

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