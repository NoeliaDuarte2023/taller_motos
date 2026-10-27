<?php
// ========================================================
// ARCHIVO: guardar_moto.php
// OBJETIVO: Procesar el registro en 1FN y 2FN:
// 1. Cliente (en tabla clientes)
// 2. Moto (en tabla motos)
// 3. Trabajo (en tabla trabajos, si aplica)
// ========================================================

require_once 'conexion.php';
require_once 'validaciones.php';

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
    
    $tipo_cliente = $_POST['tipo_cliente'] ?? 'existente';
    $esSoloMoto   = isset($_POST['solo_moto']) && $_POST['solo_moto'] == '1';
    $id_cliente   = null;

    // ========================================================
    // PASO 0: VALIDAR TODO ANTES DE GUARDAR NADA
    // (así no queda un cliente o una moto a medias si hay un error)
    // ========================================================
    $errores = [];

    if ($tipo_cliente === 'nuevo') {
        $_POST['nuevo_nombre']    = v_nombre($_POST['nuevo_nombre'] ?? '', 'El nombre', $errores, true, 100);
        $_POST['nuevo_apellido']  = v_nombre($_POST['nuevo_apellido'] ?? '', 'El apellido', $errores, true, 100);
        $_POST['nuevo_telefono']  = v_telefono($_POST['nuevo_telefono'] ?? '', 'El teléfono', $errores, true);
        $_POST['nuevo_direccion'] = v_direccion($_POST['nuevo_direccion'] ?? '', $errores, false);
    }

    $eligioMotoExistente = !$esSoloMoto && $tipo_cliente === 'existente'
                           && ($_POST['tipo_moto'] ?? 'nueva') === 'existente'
                           && intval($_POST['id_moto_existente'] ?? 0) > 0;

    if (!$eligioMotoExistente) {
        $_POST['marca']       = v_alfanum($_POST['marca'] ?? '', 'La marca', $errores, true, 50);
        $_POST['modelo']      = v_alfanum($_POST['modelo'] ?? '', 'El modelo', $errores, true, 100);
        $_POST['color']       = (string) v_alfanum($_POST['color'] ?? '', 'El color', $errores, false, 30);
        $_POST['anio']        = v_anio($_POST['anio'] ?? '', $errores);
        $_POST['kilometraje'] = v_entero($_POST['kilometraje'] ?? '', 'El kilometraje', $errores, 0, 999999, 0);

        $patenteCheck = trim($_POST['patente'] ?? '');
        if ($patenteCheck === '') {
            $errores[] = "La patente es obligatoria.";
        } else {
            $patenteOk = validarFormatoPatente($patenteCheck);
            if (!$patenteOk) {
                $errores[] = "La patente '" . $patenteCheck . "' no tiene un formato válido (ejemplos: A123BCD, OBJ123, 123ABC o AB123CD).";
            } else {
                $stmtDup0 = $pdo->prepare("SELECT id_moto FROM motos WHERE patente = :patente");
                $stmtDup0->execute([':patente' => $patenteOk]);
                if ($stmtDup0->fetch()) {
                    $errores[] = "La patente $patenteOk ya está registrada en el sistema.";
                }
            }
        }
    }

    if (!$esSoloMoto) {
        $_POST['falla_reportada'] = v_texto($_POST['falla_reportada'] ?? '', 'La falla o arreglo a realizar', $errores, true, 3, 1000);
        $_POST['fecha_prometida'] = v_fecha($_POST['fecha_prometida'] ?? '', 'La fecha prometida', $errores, FECHA_INICIO_SISTEMA, date('Y-m-d', strtotime('+1 year')));
        $_POST['precio_estimado'] = v_decimal($_POST['precio_estimado'] ?? '', 'El precio estimado', $errores, 0, 99999999.99);
        $_POST['estado']          = v_opcion($_POST['estado'] ?? '', ['En Espera', 'En Reparacion', 'Terminado', 'Entregado'], 'El estado', $errores, 'En Espera');
    }

    if (!empty($errores)) {
        v_mostrar_errores($errores, 'No se guardó nada: hay datos inválidos');
    }

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
        // Verificar que la moto existente realmente exista en la base de datos
        $stmtVerifMoto = $pdo->prepare("SELECT id_moto FROM motos WHERE id_moto = :id");
        $stmtVerifMoto->execute([':id' => $id_moto_existente]);
        if (!$stmtVerifMoto->fetch()) {
            echo "<div style='font-family: sans-serif; max-width: 600px; margin: 40px auto; padding: 25px; border: 2px solid #dc3545; border-radius: 10px; background-color: #fff8f8;'>";
            echo "<h3 style='color: #dc3545; margin-top: 0;'>❌ Error: Moto no encontrada</h3>";
            echo "<p>La moto seleccionada no existe en la base de datos.</p>";
            echo "<br><a href='javascript:history.back()' style='display: inline-block; padding: 10px 20px; background: #0d6efd; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;'>Volver</a>";
            echo "</div>";
            exit;
        }
        $id_moto_final = $id_moto_existente;
    } else {
        // Moto nueva que se incorpora al padrón
        $patenteBruta = trim($_POST['patente'] ?? '');
        $marca        = trim($_POST['marca'] ?? '');
        $modelo       = trim($_POST['modelo'] ?? '');
        $color        = trim($_POST['color'] ?? '');
        $anio         = !empty($_POST['anio']) ? intval($_POST['anio']) : null;
        $kilometraje  = !empty($_POST['kilometraje']) ? intval($_POST['kilometraje']) : 0;

        if (empty($patenteBruta) || empty($marca) || empty($modelo)) {
            echo "<div style='font-family: sans-serif; max-width: 600px; margin: 40px auto; padding: 25px; border: 2px solid #fd7e14; border-radius: 10px; background-color: #fffaf5;'>";
            echo "<h3 style='color: #fd7e14; margin-top: 0;'>⚠️ Campos obligatorios incompletos</h3>";
            echo "<p>Por favor completá los datos obligatorios de la moto (Patente, Marca y Modelo).</p>";
            echo "<br><a href='javascript:history.back()' style='display: inline-block; padding: 10px 20px; background: #0d6efd; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;'>Volver</a>";
            echo "</div>";
            exit;
        }

        // 1. VALIDACIÓN ESTRICTA DEL FORMATO DE LA PATENTE (Argentina / Mercosur)
        $patente = validarFormatoPatente($patenteBruta);
        if (!$patente) {
            echo "<div style='font-family: sans-serif; max-width: 650px; margin: 40px auto; padding: 25px; border: 2px solid #dc3545; border-radius: 10px; background-color: #fff8f8;'>";
            echo "<h3 style='color: #dc3545; margin-top: 0;'>❌ Patente Inválida</h3>";
            echo "<p>El texto ingresado como patente ('<strong>" . htmlspecialchars($patenteBruta) . "</strong>') no tiene un formato válido.</p>";
            echo "<p>El sistema no acepta patentes inexistentes o con formato erróneo. Debe respetar el formato oficial:</p>";
            echo "<ul>";
            echo "<li><strong>A123BCD</strong>: Motos Mercosur (1 letra + 3 números + 3 letras)</li>";
            echo "<li><strong>OBJ123</strong>: Motos o autos clásico (3 letras + 3 números)</li>";
            echo "<li><strong>123ABC</strong>: Motos clásico (3 números + 3 letras)</li>";
            echo "<li><strong>AB123CD</strong>: Autos Mercosur (2 letras + 3 números + 2 letras)</li>";
            echo "</ul>";
            echo "<br><a href='javascript:history.back()' style='display: inline-block; padding: 10px 20px; background: #0d6efd; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;'>Volver e ingresar patente válida</a>";
            echo "</div>";
            exit;
        }

        // 2. VALIDACIÓN DE NO DUPLICIDAD DE PATENTE
        $stmtDup = $pdo->prepare("SELECT id_moto, marca, modelo FROM motos WHERE patente = :patente");
        $stmtDup->execute([':patente' => $patente]);
        $motoDup = $stmtDup->fetch();
        if ($motoDup) {
            echo "<div style='font-family: sans-serif; max-width: 650px; margin: 40px auto; padding: 25px; border: 2px solid #dc3545; border-radius: 10px; background-color: #fff8f8;'>";
            echo "<h3 style='color: #dc3545; margin-top: 0;'>❌ Patente ya registrada</h3>";
            echo "<p>La patente <strong>" . htmlspecialchars($patente) . "</strong> ya se encuentra registrada en el sistema asignada a: <strong>" . htmlspecialchars($motoDup['marca'] . ' ' . $motoDup['modelo']) . "</strong>.</p>";
            echo "<p>No se pueden registrar dos vehículos con la misma patente.</p>";
            echo "<br><a href='javascript:history.back()' style='display: inline-block; padding: 10px 20px; background: #0d6efd; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;'>Volver</a>";
            echo "</div>";
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
            echo "<div style='font-family: sans-serif; max-width: 600px; margin: 40px auto; padding: 25px; border: 2px solid #dc3545; border-radius: 10px; background-color: #fff8f8;'>";
            echo "<h3 style='color: #dc3545;'>Error al registrar la moto en el padrón</h3>";
            echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
            echo "<br><a href='javascript:history.back()' style='display: inline-block; padding: 10px 20px; background: #0d6efd; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;'>Volver</a>";
            echo "</div>";
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
        $fecha_entrega = ($estado === 'Entregado') ? date('Y-m-d H:i:s') : null;

        $sqlInsertTrabajo = "INSERT INTO trabajos (
                                id_moto, falla_reportada, precio_estimado, estado, fecha_ingreso, fecha_prometida, fecha_entrega
                            ) VALUES (
                                :id_moto, :falla_reportada, :precio_estimado, :estado, NOW(), :fecha_prometida, :fecha_entrega
                            )";

        $stmtTrabajo = $pdo->prepare($sqlInsertTrabajo);
        $stmtTrabajo->execute([
            ':id_moto'         => $id_moto_final,
            ':falla_reportada' => $falla_reportada,
            ':precio_estimado' => $precio_estimado,
            ':estado'          => $estado,
            ':fecha_prometida' => $fecha_prometida,
            ':fecha_entrega'   => $fecha_entrega
        ]);

        if ($estado === 'Entregado') {
            header('Location: entregadas.php?entregada=ok');
        } else {
            header('Location: trabajos.php?guardado=ok');
        }
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
