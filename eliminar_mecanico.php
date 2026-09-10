<?php
require_once 'conexion.php';

if (isset($_GET['id']) && !empty($_GET['id'])) {
    
    $id_mecanico = $_GET['id'];

    try {
        $sql = "DELETE FROM mecanicos WHERE id_mecanico = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $id_mecanico]);

        header('Location: mecanicos.php');
        exit;

    } catch (PDOException $e) {
        echo "<h3 style='color:red;'>Error al eliminar el mecánico: " . $e->getMessage() . "</h3>";
        echo "<br><a href='mecanicos.php'>Volver a Mecánicos</a>";
        exit;
    }

} else {
    header('Location: mecanicos.php');
    exit;
}
?>