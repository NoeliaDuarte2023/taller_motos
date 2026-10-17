<?php
// ========================================================
// ARCHIVO: cambiar_estado.php
// OBJETIVO: Cambio rápido de estado de una orden de trabajo
// (En Espera -> En Reparación -> Terminado/Lista -> Entregado)
// ========================================================

require_once 'conexion.php';

$id_trabajo   = intval($_GET['id'] ?? 0);
$nuevo_estado = trim($_GET['estado'] ?? '');

$estadosPermitidos = ['En Espera', 'En Reparacion', 'Terminado', 'Entregado'];

if ($id_trabajo > 0 && in_array($nuevo_estado, $estadosPermitidos)) {
    try {
        if ($nuevo_estado === 'Entregado') {
            $stmt = $pdo->prepare("UPDATE trabajos SET estado = :estado, fecha_entrega = COALESCE(fecha_entrega, NOW()) WHERE id_trabajo = :id");
        } else {
            $stmt = $pdo->prepare("UPDATE trabajos SET estado = :estado WHERE id_trabajo = :id");
        }
        $stmt->execute([':estado' => $nuevo_estado, ':id' => $id_trabajo]);

        // Redirigir según el nuevo estado
        if ($nuevo_estado === 'Entregado') {
            header('Location: entregadas.php?entregada=ok');
        } else {
            header('Location: trabajos.php?actualizado=ok');
        }
        exit;

    } catch (PDOException $e) {
        echo "<h3 style='color:red;'>Error al cambiar el estado: " . $e->getMessage() . "</h3>";
        echo "<br><a href='trabajos.php'>Volver</a>";
        exit;
    }
} else {
    header('Location: trabajos.php');
    exit;
}
?>
