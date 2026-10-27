<?php
// Incluimos la conexión
require_once 'conexion.php';
require_once 'validaciones.php';

// Verificamos que los datos hayan llegado por método POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Recibimos y limpiamos los datos del formulario
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

    // Validación básica de campos obligatorios
    if (!empty($nombre) && !empty($apellido) && !empty($telefono)) {
        
        try {
            // Consulta SQL preparada para evitar inyecciones SQL
            $sql = "INSERT INTO clientes (nombre, apellido, telefono, email, direccion) 
                    VALUES (:nombre, :apellido, :telefono, :email, :direccion)";
            
            $stmt = $pdo->prepare($sql);
            
            // Ejecutamos la inserción con los datos
            $stmt->execute([
                ':nombre'    => $nombre,
                ':apellido'  => $apellido,
                ':telefono'  => $telefono,
                ':email'     => $email,
                ':direccion' => $direccion
            ]);

            // Redireccionamos a la lista de clientes con mensaje de éxito
            header('Location: clientes.php?msg=guardado');
            exit;

        } catch (PDOException $e) {
            echo "<h3 style='color:red;'>Error al guardar el cliente: " . $e->getMessage() . "</h3>";
            echo "<a href='clientes.php'>Volver</a>";
            exit;
        }

    } else {
        echo "<h3 style='color:orange;'>Por favor completa los campos obligatorios (Nombre, Apellido, Teléfono).</h3>";
        echo "<a href='clientes.php'>Volver</a>";
        exit;
    }
} else {
    // Si intentan entrar directo a este archivo sin enviar formulario, los mandamos a clientes.php
    header('Location: clientes.php');
    exit;
}
?>