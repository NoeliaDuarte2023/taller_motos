<?php
require_once 'conexion.php';

if (isset($_GET['id']) && !empty($_GET['id'])) {
    
    $id_repuesto = $_GET['id'];

    try {
        $sql = "DELETE FROM repuestos WHERE id_repuesto = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $id_repuesto]);

        header('Location: repuestos.php');
        exit;

    } catch (PDOException $e) {
        echo "<h3 style='color:red;'>Error al eliminar el repuesto: " . $e->getMessage() . "</h3>";
        echo "<br><a href='repuestos.php'>Volver a Repuestos</a>";
        exit;
    }

} else {
    header('Location: repuestos.php');
    exit;
}
?>