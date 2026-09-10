<?php
// Incluimos la conexión
require_once 'conexion.php';

// Verificamos si recibimos el ID por la URL (GET)
if (isset($_GET['id']) && !empty($_GET['id'])) {
    
    $id_cliente = $_GET['id'];

    try {
        // Preparamos la consulta para eliminar el cliente
        $sql = "DELETE FROM clientes WHERE id_cliente = :id";
        $stmt = $pdo->prepare($sql);
        
        // Ejecutamos pasando el ID
        $stmt->execute([':id' => $id_cliente]);

        // Redireccionamos a la lista de clientes
        header('Location: clientes.php');
        exit;

    } catch (PDOException $e) {
        echo "<h3 style='color:red;'>Error al eliminar el cliente: " . $e->getMessage() . "</h3>";
        echo "<a href='clientes.php'>Volver</a>";
        exit;
    }

} else {
    // Si no enviaron ningún ID, volvemos a la lista
    header('Location: clientes.php');
    exit;
}
?>