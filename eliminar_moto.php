<?php
require_once 'conexion.php';

if (isset($_GET['id']) && !empty($_GET['id'])) {
    
    $id_moto = $_GET['id'];

    try {
        $sql = "DELETE FROM motos WHERE id_moto = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $id_moto]);

        header('Location: motos.php');
        exit;

    } catch (PDOException $e) {
        echo "<h3 style='color:red;'>Error al eliminar la moto: " . $e->getMessage() . "</h3>";
        echo "<br><a href='motos.php'>Volver a Motos</a>";
        exit;
    }

} else {
    header('Location: motos.php');
    exit;
}
?>