<?php
// ========================================================
// ARCHIVO: actualizar_moto.php
// OBJETIVO: Procesar las modificaciones de un vehículo en el padrón (ABM Motos)
// LENGUAJES: PHP + MySQL (PDO)
// ========================================================

require_once 'conexion.php';

// Función para validar formato oficial de patente (Motos y Autos Argentina / Mercosur)
if (!function_exists('validarFormatoPatente')) {
    function validarFormatoPatente($patente) {
        $p = preg_replace('/[^A-Z0-9]/', '', strtoupper($patente));
        
        // 1. Moto Mercosur: A123BCD (1 letra + 3 dígitos + 3 letras)
        if (preg_match('/^[A-Z]{1}[0-9]{3}[A-Z]{3}$/', $p)) return $p;
        // 2. Moto clásica / tradicional: 123ABC (3 dígitos + 3 letras)
        if (preg_match('/^[0-9]{3}[A-Z]{3}$/', $p)) return $p;
        // 3. Clásica tradicional: ABC123 (3 letras + 3 dígitos)
        if (preg_match('/^[A-Z]{3}[0-9]{3}$/', $p)) return $p;
        // 4. Auto Mercosur: AB123CD (2 letras + 3 dígitos + 2 letras)
        if (preg_match('/^[A-Z]{2}[0-9]{3}[A-Z]{2}$/', $p)) return $p;

        return false;
    }
}

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    
    $id_moto    = intval($_POST['id_moto'] ?? 0);
    $id_cliente = intval($_POST['id_cliente'] ?? 0);

    if ($id_moto <= 0 || $id_cliente <= 0) {
        header('Location: motos.php');
        exit;
    }

    $patenteBruta = trim($_POST['patente'] ?? '');
    $marca        = trim($_POST['marca'] ?? '');
    $modelo       = trim($_POST['modelo'] ?? '');
    $color        = trim($_POST['color'] ?? '');
    $anio         = !empty($_POST['anio']) ? intval($_POST['anio']) : null;
    $kilometraje  = !empty($_POST['kilometraje']) ? intval($_POST['kilometraje']) : 0;

    if (empty($patenteBruta) || empty($marca) || empty($modelo)) {
        echo "<div style='font-family: sans-serif; max-width: 600px; margin: 40px auto; padding: 25px; border: 2px solid #fd7e14; border-radius: 10px; background-color: #fffaf5;'>";
        echo "<h3 style='color: #fd7e14; margin-top: 0;'>⚠️ Campos incompletos</h3>";
        echo "<p>Por favor completá los campos obligatorios (Patente, Marca y Modelo).</p>";
        echo "<br><a href='javascript:history.back()' style='display: inline-block; padding: 10px 20px; background: #0d6efd; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;'>Volver</a>";
        echo "</div>";
        exit;
    }

    // Validación de formato de patente
    $patente = validarFormatoPatente($patenteBruta);
    if (!$patente) {
        echo "<div style='font-family: sans-serif; max-width: 650px; margin: 40px auto; padding: 25px; border: 2px solid #dc3545; border-radius: 10px; background-color: #fff8f8;'>";
        echo "<h3 style='color: #dc3545; margin-top: 0;'>❌ Patente Inválida</h3>";
        echo "<p>El texto ingresado como patente ('<strong>" . htmlspecialchars($patenteBruta) . "</strong>') no tiene un formato válido.</p>";
        echo "<p>Debe respetar el formato oficial (Ejemplos: A123BCD, OBJ123, 123ABC o AB123CD).</p>";
        echo "<br><a href='javascript:history.back()' style='display: inline-block; padding: 10px 20px; background: #0d6efd; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;'>Volver e ingresar patente válida</a>";
        echo "</div>";
        exit;
    }

    // Validación de no duplicidad con otra moto
    $stmtDup = $pdo->prepare("SELECT id_moto FROM motos WHERE patente = :patente AND id_moto != :id");
    $stmtDup->execute([':patente' => $patente, ':id' => $id_moto]);
    if ($stmtDup->fetch()) {
        echo "<div style='font-family: sans-serif; max-width: 650px; margin: 40px auto; padding: 25px; border: 2px solid #dc3545; border-radius: 10px; background-color: #fff8f8;'>";
        echo "<h3 style='color: #dc3545; margin-top: 0;'>❌ Patente ya registrada</h3>";
        echo "<p>La patente <strong>" . htmlspecialchars($patente) . "</strong> ya pertenece a otra moto registrada en el sistema.</p>";
        echo "<br><a href='javascript:history.back()' style='display: inline-block; padding: 10px 20px; background: #0d6efd; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;'>Volver</a>";
        echo "</div>";
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
