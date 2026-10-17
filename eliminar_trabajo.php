<?php
// ========================================================
// ARCHIVO: eliminar_trabajo.php
// OBJETIVO: Eliminar una orden de trabajo (1FN y 2FN)
// IMPORTANTE: NO elimina la moto ni el cliente (Cero anomalías de eliminación)
// ========================================================

require_once 'conexion.php';

if (isset($_GET['id']) && !empty($_GET['id'])) {
    
    $id_trabajo = intval($_GET['id']);

    try {
        $sql = "DELETE FROM trabajos WHERE id_trabajo = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $id_trabajo]);

        header('Location: trabajos.php?eliminado=ok');
        exit;

    } catch (PDOException $e) {
        echo "<h3 style='color:red;'>Error al eliminar el trabajo: " . $e->getMessage() . "</h3>";
        echo "<br><a href='trabajos.php'>Volver a Trabajos</a>";
        exit;
    }

} else {
    header('Location: trabajos.php');
    exit;
}
?>
