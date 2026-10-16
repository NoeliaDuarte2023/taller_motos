<?php
// ========================================================
// ARCHIVO: guardar_moto.php
// OBJETIVO: Procesar el ingreso guiado del trabajo
// 1. Cliente (Existente o Nuevo)
// 2. Moto (Moto Registrada o Nueva Moto)
// 3. Trabajo (Falla, Estado, Fechas e Importe)
// LENGUAJES: PHP + MySQL (PDO)
// ========================================================

require_once 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $tipo_cliente = $_POST['tipo_cliente'] ?? 'existente';
    $id_cliente   = null;

    // ========================================================
    // PASO 1: PROCESAR CLIENTE / PERSONA
    // ========================================================
    if ($tipo_cliente === 'nuevo') {
        $nombre    = trim($_POST['nuevo_nombre'] ?? '');
        $apellido  = trim($_POST['nuevo_apellido'] ?? '');
        $telefono  = trim($_POST['nuevo_telefono'] ?? '');
        $direccion = trim($_POST['nuevo_direccion'] ?? '');

        if (empty($nombre) || empty($apellido) || empty($telefono)) {
            echo "<h3 style='color:orange;'>Por favor completá los datos obligatorios del nuevo cliente (Nombre, Apellido y Teléfono).</h3>";
            echo "<br><a href='nueva_moto.php'>Volver al formulario</a>";
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
            echo "<br><a href='nueva_moto.php'>Volver al formulario</a>";
            exit;
        }

    } else {
        $id_cliente = intval($_POST['id_cliente_existente'] ?? 0);
        if ($id_cliente <= 0) {
            echo "<h3 style='color:orange;'>Por favor seleccioná un cliente existente o elegí 'Nueva Persona'.</h3>";
            echo "<br><a href='nueva_moto.php'>Volver al formulario</a>";
            exit;
        }
    }

    // ========================================================
    // PASO 3 (DATOS DEL TRABAJO A REALIZAR)
    // ========================================================
    $falla_reportada = trim($_POST['falla_reportada'] ?? '');
    $fecha_prometida = !empty($_POST['fecha_prometida']) ? $_POST['fecha_prometida'] : null;
    $precio_estimado = !empty($_POST['precio_estimado']) ? floatval($_POST['precio_estimado']) : 0.00;
    $estado          = trim($_POST['estado'] ?? 'En Espera');

    if (empty($falla_reportada)) {
        echo "<h3 style='color:orange;'>Por favor describí la falla o el arreglo por hacer.</h3>";
        echo "<br><a href='nueva_moto.php'>Volver al formulario</a>";
        exit;
    }

    // ========================================================
    // PASO 2: PROCESAR LA MOTO (¿EXISTENTE O NUEVA?)
    // ========================================================
    $tipo_moto          = $_POST['tipo_moto'] ?? 'nueva';
    $id_moto_existente  = intval($_POST['id_moto_existente'] ?? 0);
    $id_moto_final      = 0;

    // CASO A: Dejó una moto que YA estaba registrada a su nombre
    if ($tipo_cliente === 'existente' && $tipo_moto === 'existente' && $id_moto_existente > 0) {
        
        try {
            // Actualizamos la ficha de esa moto con el nuevo ingreso al taller
            $sqlUpdateMoto = "UPDATE motos SET 
                                estado = :estado,
                                falla_reportada = :falla_reportada,
                                arreglo_realizado = NULL,
                                repuestos = NULL,
                                precio_estimado = :precio_estimado,
                                precio_final = 0.00,
                                fecha_ingreso = NOW(),
                                fecha_prometida = :fecha_prometida,
                                fecha_entrega = NULL
                              WHERE id_moto = :id_moto";

            $stmtUpdate = $pdo->prepare($sqlUpdateMoto);
            $stmtUpdate->execute([
                ':estado'          => $estado,
                ':falla_reportada' => $falla_reportada,
                ':precio_estimado' => $precio_estimado,
                ':fecha_prometida' => $fecha_prometida,
                ':id_moto'         => $id_moto_existente
            ]);

            $id_moto_final = $id_moto_existente;

        } catch (PDOException $e) {
            echo "<h3 style='color:red;'>Error al registrar el nuevo trabajo en la moto: " . $e->getMessage() . "</h3>";
            echo "<br><a href='nueva_moto.php'>Volver</a>";
            exit;
        }

    } else {
        // CASO B: Es una moto NUEVA que nunca antes había ingresado
        $patente     = strtoupper(trim($_POST['patente'] ?? ''));
        $marca       = trim($_POST['marca'] ?? '');
        $modelo      = trim($_POST['modelo'] ?? '');
        $color       = trim($_POST['color'] ?? '');
        $anio        = !empty($_POST['anio']) ? intval($_POST['anio']) : null;
        $kilometraje = !empty($_POST['kilometraje']) ? intval($_POST['kilometraje']) : 0;

        if (empty($patente) || empty($marca) || empty($modelo)) {
            echo "<h3 style='color:orange;'>Por favor completá los datos obligatorios de la moto (Patente, Marca y Modelo).</h3>";
            echo "<br><a href='nueva_moto.php'>Volver</a>";
            exit;
        }

        try {
            $sqlInsertMoto = "INSERT INTO motos (
                                id_cliente, marca, modelo, anio, patente, color, kilometraje, 
                                falla_reportada, precio_estimado, estado, fecha_ingreso, fecha_prometida
                            ) VALUES (
                                :id_cliente, :marca, :modelo, :anio, :patente, :color, :kilometraje, 
                                :falla_reportada, :precio_estimado, :estado, NOW(), :fecha_prometida
                            )";

            $stmtInsert = $pdo->prepare($sqlInsertMoto);
            $stmtInsert->execute([
                ':id_cliente'       => $id_cliente,
                ':marca'            => $marca,
                ':modelo'           => $modelo,
                ':anio'             => $anio,
                ':patente'          => $patente,
                ':color'            => $color,
                ':kilometraje'      => $kilometraje,
                ':falla_reportada'  => $falla_reportada,
                ':precio_estimado'  => $precio_estimado,
                ':estado'           => $estado,
                ':fecha_prometida'  => $fecha_prometida
            ]);

            $id_moto_final = $pdo->lastInsertId();

        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                echo "<div style='font-family: sans-serif; padding: 20px; background: #fee2e2; color: #991b1b; border-radius: 8px;'>";
                echo "<h3>⚠️ Atención: Ya existe una moto registrada con la patente <strong>" . htmlspecialchars($patente) . "</strong>.</h3>";
                echo "<p>Si la moto ya existe, podés seleccionarla directamente en la opción 'Dejar una moto ya registrada a su nombre'.</p>";
                echo "<a href='nueva_moto.php' style='display: inline-block; padding: 8px 16px; background: #1e293b; color: white; text-decoration: none; border-radius: 6px;'>Volver a intentar</a>";
                echo "</div>";
            } else {
                echo "<h3 style='color:red;'>Error al guardar la moto: " . $e->getMessage() . "</h3>";
                echo "<br><a href='nueva_moto.php'>Volver</a>";
            }
            exit;
        }
    }

    // Opcional: Registrar en la tabla ordenes_trabajo si existe para consistencia con el profesor
    try {
        $stmtOt = $pdo->prepare("INSERT INTO ordenes_trabajo (id_moto, motivo_ingreso, estado, fecha_estimada_entrega, total) 
                                 VALUES (:id_moto, :motivo, 'Pendiente', :fecha_est, :total)");
        $stmtOt->execute([
            ':id_moto'   => $id_moto_final,
            ':motivo'    => $falla_reportada,
            ':fecha_est' => $fecha_prometida,
            ':total'     => $precio_estimado
        ]);
    } catch (Exception $ex) {
        // Continúa normalmente si la tabla ordenes_trabajo tiene otra estructura
    }

    // Redirigir al tablero con éxito
    header('Location: motos.php?guardado=ok');
    exit;

} else {
    header('Location: motos.php');
    exit;
}
?>
