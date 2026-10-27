<?php
// ========================================================
// ARCHIVO: actualizar_trabajo.php
// OBJETIVO: Procesar las modificaciones de una orden de trabajo (1FN y 2FN)
// LENGUAJES: PHP + MySQL (PDO)
// ========================================================

require_once 'conexion.php';
require_once 'validaciones.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $id_trabajo = intval($_POST['id_trabajo'] ?? 0);

    if ($id_trabajo <= 0) {
        header('Location: trabajos.php');
        exit;
    }

    // Datos del trabajo y estado, con validación (ver validaciones.php)
    $errores = [];
    $estado            = v_opcion($_POST['estado'] ?? '', ['En Espera', 'En Reparacion', 'Terminado', 'Entregado'], 'El estado', $errores, 'En Espera');
    $falla_reportada   = v_texto($_POST['falla_reportada'] ?? '', 'La falla reportada', $errores, true, 3, 1000);
    $arreglo_realizado = (string) v_texto($_POST['arreglo_realizado'] ?? '', 'El arreglo realizado', $errores, false, 3, 2000);
    $repuestos         = (string) v_texto($_POST['repuestos'] ?? '', 'Los repuestos', $errores, false, 3, 1000);
    $precio_estimado   = v_decimal($_POST['precio_estimado'] ?? '', 'El precio estimado', $errores, 0, 99999999.99);
    $precio_final      = v_decimal($_POST['precio_final'] ?? '', 'El precio final', $errores, 0, 99999999.99);
    $fecha_prometida   = v_fecha($_POST['fecha_prometida'] ?? '', 'La fecha prometida', $errores, FECHA_INICIO_SISTEMA, date('Y-m-d', strtotime('+5 years')));

    // La fecha de entrega no puede ser anterior al ingreso del trabajo (puede ser futura)
    $stmtIng = $pdo->prepare("SELECT fecha_ingreso FROM trabajos WHERE id_trabajo = :id");
    $stmtIng->execute([':id' => $id_trabajo]);
    $fila = $stmtIng->fetch();
    if (!$fila) {
        header('Location: trabajos.php');
        exit;
    }
    $fecha_entrega = v_fecha_hora($_POST['fecha_entrega'] ?? '', 'La fecha de entrega', $errores, $fila['fecha_ingreso'], date('Y-m-d H:i:s', strtotime('+5 years')));

    if (!empty($errores)) {
        v_mostrar_errores($errores, 'Datos del trabajo inválidos');
    }

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
