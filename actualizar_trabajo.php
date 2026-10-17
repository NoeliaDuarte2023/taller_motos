<?php
// ========================================================
// ARCHIVO: actualizar_trabajo.php
// OBJETIVO: Procesar las modificaciones de una orden de trabajo (1FN y 2FN)
// LENGUAJES: PHP + MySQL (PDO)
// ========================================================

require_once 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $id_trabajo = intval($_POST['id_trabajo'] ?? 0);

    if ($id_trabajo <= 0) {
        header('Location: trabajos.php');
        exit;
    }

    // Datos del trabajo y estado
    $estado            = trim($_POST['estado'] ?? 'En Espera');
    $falla_reportada   = trim($_POST['falla_reportada'] ?? '');
    $arreglo_realizado = trim($_POST['arreglo_realizado'] ?? '');
    $repuestos         = trim($_POST['repuestos'] ?? '');
    $precio_estimado   = !empty($_POST['precio_estimado']) ? floatval($_POST['precio_estimado']) : 0.00;
    $precio_final      = !empty($_POST['precio_final']) ? floatval($_POST['precio_final']) : 0.00;
    $fecha_prometida   = !empty($_POST['fecha_prometida']) ? $_POST['fecha_prometida'] : null;
    $fecha_entrega     = !empty($_POST['fecha_entrega']) ? str_replace('T', ' ', $_POST['fecha_entrega']) : null;

    // Si el estado es Entregado y no tenía fecha de entrega, le asignamos la fecha actual
    if ($estado === 'Entregado' && empty($fecha_entrega)) {
        $fecha_entrega = date('Y-m-d H:i:s');
    }

    try {
        $sqlTrabajo = "UPDATE trabajos SET 
                            estado = :estado,
                            falla_reportada = :falla_reportada,
                            arreglo_realizado = :arreglo_realizado,
                            repuestos = :repuestos,
                            precio_estimado = :precio_estimado,
                            precio_final = :precio_final,
                            fecha_prometida = :fecha_prometida,
                            fecha_entrega = :fecha_entrega
                       WHERE id_trabajo = :id_trabajo";

        $stmtTrabajo = $pdo->prepare($sqlTrabajo);
        $stmtTrabajo->execute([
            ':estado'            => $estado,
            ':falla_reportada'   => $falla_reportada,
            ':arreglo_realizado' => $arreglo_realizado,
            ':repuestos'         => $repuestos,
            ':precio_estimado'   => $precio_estimado,
            ':precio_final'      => $precio_final,
            ':fecha_prometida'   => $fecha_prometida,
            ':fecha_entrega'     => $fecha_entrega,
            ':id_trabajo'        => $id_trabajo
        ]);

        // Si fue entregada, redirigimos a la grilla de entregadas; sino, al panel de trabajos
        if ($estado === 'Entregado') {
            header('Location: entregadas.php?entregada=ok');
        } else {
            header('Location: trabajos.php?actualizado=ok');
        }
        exit;

    } catch (PDOException $e) {
        echo "<h3 style='color:red;'>Error al actualizar el trabajo: " . $e->getMessage() . "</h3>";
        echo "<br><a href='javascript:history.back()'>Volver</a>";
        exit;
    }

} else {
    header('Location: trabajos.php');
    exit;
}
?>
