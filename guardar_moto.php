<?php
// ========================================================
// ARCHIVO: guardar_moto.php
// OBJETIVO: Procesar el registro en 1FN y 2FN:
// 1. Cliente (en tabla clientes)
// 2. Moto (en tabla motos)
// 3. Trabajo (en tabla trabajos, si aplica)
// ========================================================

require_once 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $tipo_cliente = $_POST['tipo_cliente'] ?? 'existente';
    $esSoloMoto   = isset($_POST['solo_moto']) && $_POST['solo_moto'] == '1';
    $id_cliente   = null;

    // ========================================================
    // PASO 1: PROCESAR CLIENTE / DUEÑO
    // ========================================================
    if ($tipo_cliente === 'nuevo') {
        $nombre    = trim($_POST['nuevo_nombre'] ?? '');
        $apellido  = trim($_POST['nuevo_apellido'] ?? '');
        $telefono  = trim($_POST['nuevo_telefono'] ?? '');
        $direccion = trim($_POST['nuevo_direccion'] ?? '');

        if (empty($nombre) || empty($apellido) || empty($telefono)) {
            echo "<h3 style='color:orange;'>Por favor completá los datos obligatorios del nuevo cliente (Nombre, Apellido y Teléfono).</h3>";
            echo "<br><a href='javascript:history.back()'>Volver al formulario</a>";
            exit;
        }

        try {
            $stmtCli = $pdo->prepare("INSERT INTO clientes (nombre, apellido, telefono, direccion) VALUES (:nombre, :apellido, :telefono, :direccion)");
            $stmtCli->execute([
                ':nombre'    => $nombre,
                ':apellido'  => $apellido,
                ':telefono'  => $telefono,
                ':direccion' => $direccion
            ]);
            $id_cliente = $pdo->lastInsertId();
        } catch (PDOException $e) {
            echo "<h3 style='color:red;'>Error al registrar el cliente: " . $e->getMessage() . "</h3>";
            echo "<br><a href='javascript:history.back()'>Volver al formulario</a>";
            exit;
        }

    } else {
        $id_cliente = intval($_POST['id_cliente_existente'] ?? 0);
        if ($id_cliente <= 0) {
            echo "<h3 style='color:orange;'>Por favor seleccioná un cliente existente o elegí '+ Nuevo Cliente'.</h3>";
            echo "<br><a href='javascript:history.back()'>Volver al formulario</a>";
            exit;
        }
    }

    // ========================================================
    // PASO 2: PROCESAR LA MOTO (TABLA MOTOS - ENTIDAD VEHÍCULO)
    // ========================================================
    $tipo_moto          = $_POST['tipo_moto'] ?? 'nueva';
    $id_moto_existente  = intval($_POST['id_moto_existente'] ?? 0);
    $id_moto_final      = 0;

    if (!$esSoloMoto && $tipo_cliente === 'existente' && $tipo_moto === 'existente' && $id_moto_existente > 0) {
        // La moto ya existe en el padrón
        $id_moto_final = $id_moto_existente;
    } else {
        // Moto nueva que se incorpora al padrón
        $patente     = strtoupper(trim($_POST['patente'] ?? ''));
        $marca       = trim($_POST['marca'] ?? '');
        $modelo      = trim($_POST['modelo'] ?? '');
        $color       = trim($_POST['color'] ?? '');
        $anio        = !empty($_POST['anio']) ? intval($_POST['anio']) : null;
        $kilometraje = !empty($_POST['kilometraje']) ? intval($_POST['kilometraje']) : 0;

        if (empty($patente) || empty($marca) || empty($modelo)) {
            echo "<h3 style='color:orange;'>Por favor completá los datos obligatorios de la moto (Patente, Marca y Modelo).</h3>";
            echo "<br><a href='javascript:history.back()'>Volver</a>";
            exit;
        }

        try {
            $sqlInsertMoto = "INSERT INTO motos (id_cliente, marca, modelo, anio, patente, color, kilometraje) 
                              VALUES (:id_cliente, :marca, :modelo, :anio, :patente, :color, :kilometraje)";

            $stmtInsert = $pdo->prepare($sqlInsertMoto);
            $stmtInsert->execute([
                ':id_cliente'  => $id_cliente,
                ':marca'       => $marca,
                ':modelo'      => $modelo,
                ':anio'        => $anio,
                ':patente'     => $patente,
                ':color'       => $color,
                ':kilometraje' => $kilometraje
            ]);

            $id_moto_final = $pdo->lastInsertId();

        } catch (PDOException $e) {
            echo "<h3 style='color:red;'>Error al registrar la moto en el padrón: " . $e->getMessage() . "</h3>";
            echo "<p>Verificá si la patente ya está registrada en el sistema.</p>";
            echo "<br><a href='javascript:history.back()'>Volver</a>";
            exit;
        }
    }

    // ========================================================
    // CASO SOLO MOTO: SI SE REGISTRÓ DESDE EL ABM DE MOTOS
    // ========================================================
    if ($esSoloMoto) {
        header('Location: motos.php?guardado=ok');
        exit;
    }

    // ========================================================
    // PASO 3: PROCESAR TRABAJO / ORDEN DE REPARACIÓN (TABLA TRABAJOS)
    // ========================================================
    $falla_reportada = trim($_POST['falla_reportada'] ?? '');
    $fecha_prometida = !empty($_POST['fecha_prometida']) ? $_POST['fecha_prometida'] : null;
    $precio_estimado = !empty($_POST['precio_estimado']) ? floatval($_POST['precio_estimado']) : 0.00;
    $estado          = trim($_POST['estado'] ?? 'En Espera');

    if (empty($falla_reportada)) {
        echo "<h3 style='color:orange;'>Por favor describí la falla o el arreglo por hacer.</h3>";
        echo "<br><a href='javascript:history.back()'>Volver al formulario</a>";
        exit;
    }

    try {
        $sqlInsertTrabajo = "INSERT INTO trabajos (
                                id_moto, falla_reportada, precio_estimado, estado, fecha_ingreso, fecha_prometida
                            ) VALUES (
                                :id_moto, :falla_reportada, :precio_estimado, :estado, NOW(), :fecha_prometida
                            )";

        $stmtTrabajo = $pdo->prepare($sqlInsertTrabajo);
        $stmtTrabajo->execute([
            ':id_moto'         => $id_moto_final,
            ':falla_reportada' => $falla_reportada,
            ':precio_estimado' => $precio_estimado,
            ':estado'          => $estado,
            ':fecha_prometida' => $fecha_prometida
        ]);

        header('Location: trabajos.php?guardado=ok');
        exit;

    } catch (PDOException $e) {
        echo "<h3 style='color:red;'>Error al registrar el trabajo en taller: " . $e->getMessage() . "</h3>";
        echo "<br><a href='javascript:history.back()'>Volver</a>";
        exit;
    }

} else {
    header('Location: trabajos.php');
    exit;
}
?>
