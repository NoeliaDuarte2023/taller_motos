<?php
// ========================================================
// ARCHIVO: actualizar_moto.php
// OBJETIVO: Procesar las modificaciones de la moto y estado
// LENGUAJES: PHP + MySQL (PDO)
// ========================================================

require_once 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $id_moto    = intval($_POST['id_moto'] ?? 0);
    $id_cliente = intval($_POST['id_cliente'] ?? 0);

    if ($id_moto <= 0 || $id_cliente <= 0) {
        header('Location: motos.php');
        exit;
    }

    // Datos de la moto
    $patente           = strtoupper(trim($_POST['patente'] ?? ''));
    $marca             = trim($_POST['marca'] ?? '');
    $modelo            = trim($_POST['modelo'] ?? '');
    $color             = trim($_POST['color'] ?? '');
    $anio              = !empty($_POST['anio']) ? intval($_POST['anio']) : null;
    $kilometraje       = !empty($_POST['kilometraje']) ? intval($_POST['kilometraje']) : 0;
    
    // Datos de la reparación y estado
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

    // Datos del cliente
    $cliente_telefono  = trim($_POST['cliente_telefono'] ?? '');
    $cliente_direccion = trim($_POST['cliente_direccion'] ?? '');

    try {
        // 1. Actualizar datos del cliente (Teléfono y Dirección)
        if (!empty($cliente_telefono)) {
            $stmtCli = $pdo->prepare("UPDATE clientes SET telefono = :telefono, direccion = :direccion WHERE id_cliente = :id_cliente");
            $stmtCli->execute([
                ':telefono'   => $cliente_telefono,
                ':direccion'  => $cliente_direccion,
                ':id_cliente' => $id_cliente
            ]);
        }

        // 2. Actualizar la moto
        $sqlMoto = "UPDATE motos SET 
                        marca = :marca,
                        modelo = :modelo,
                        anio = :anio,
                        patente = :patente,
                        color = :color,
                        kilometraje = :kilometraje,
                        estado = :estado,
                        falla_reportada = :falla_reportada,
                        arreglo_realizado = :arreglo_realizado,
                        repuestos = :repuestos,
                        precio_estimado = :precio_estimado,
                        precio_final = :precio_final,
                        fecha_prometida = :fecha_prometida,
                        fecha_entrega = :fecha_entrega
                    WHERE id_moto = :id_moto";

        $stmtMoto = $pdo->prepare($sqlMoto);
        $stmtMoto->execute([
            ':marca'             => $marca,
            ':modelo'            => $modelo,
            ':anio'              => $anio,
            ':patente'           => $patente,
            ':color'             => $color,
            ':kilometraje'       => $kilometraje,
            ':estado'            => $estado,
            ':falla_reportada'   => $falla_reportada,
            ':arreglo_realizado' => $arreglo_realizado,
            ':repuestos'         => $repuestos,
            ':precio_estimado'   => $precio_estimado,
            ':precio_final'      => $precio_final,
            ':fecha_prometida'   => $fecha_prometida,
            ':fecha_entrega'     => $fecha_entrega,
            ':id_moto'           => $id_moto
        ]);

        // Si fue entregada, redirigimos a la grilla de entregadas; sino, al tablero principal
        if ($estado === 'Entregado') {
            header('Location: entregadas.php?entregada=ok');
        } else {
            header('Location: motos.php?actualizado=ok');
        }
        exit;

    } catch (PDOException $e) {
        echo "<h3 style='color:red;'>Error al actualizar los datos: " . $e->getMessage() . "</h3>";
        echo "<br><a href='editar_moto.php?id=" . $id_moto . "'>Volver a la ficha</a>";
        exit;
    }

} else {
    header('Location: motos.php');
    exit;
}
?>
