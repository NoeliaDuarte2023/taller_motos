<?php
// ========================================================
// ARCHIVO: actualizar_moto.php
// OBJETIVO: Procesar las modificaciones de un vehículo en el padrón (ABM Motos)
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

    $patente     = strtoupper(trim($_POST['patente'] ?? ''));
    $marca       = trim($_POST['marca'] ?? '');
    $modelo      = trim($_POST['modelo'] ?? '');
    $color       = trim($_POST['color'] ?? '');
    $anio        = !empty($_POST['anio']) ? intval($_POST['anio']) : null;
    $kilometraje = !empty($_POST['kilometraje']) ? intval($_POST['kilometraje']) : 0;

    if (empty($patente) || empty($marca) || empty($modelo)) {
        echo "<h3 style='color:orange;'>Por favor completá los campos obligatorios (Patente, Marca y Modelo).</h3>";
        echo "<br><a href='javascript:history.back()'>Volver</a>";
        exit;
    }

    try {
        $sqlMoto = "UPDATE motos SET 
                        id_cliente = :id_cliente,
                        marca = :marca,
                        modelo = :modelo,
                        anio = :anio,
                        patente = :patente,
                        color = :color,
                        kilometraje = :kilometraje
                    WHERE id_moto = :id_moto";

        $stmtMoto = $pdo->prepare($sqlMoto);
        $stmtMoto->execute([
            ':id_cliente'  => $id_cliente,
            ':marca'       => $marca,
            ':modelo'      => $modelo,
            ':anio'        => $anio,
            ':patente'     => $patente,
            ':color'       => $color,
            ':kilometraje' => $kilometraje,
            ':id_moto'     => $id_moto
        ]);

        header('Location: motos.php?actualizado=ok');
        exit;

    } catch (PDOException $e) {
        echo "<h3 style='color:red;'>Error al actualizar los datos de la moto: " . $e->getMessage() . "</h3>";
        echo "<br><a href='javascript:history.back()'>Volver</a>";
        exit;
    }

} else {
    header('Location: motos.php');
    exit;
}
?>
