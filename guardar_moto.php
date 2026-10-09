<?php
// ========================================================
// ARCHIVO: guardar_moto.php
// OBJETIVO: Procesar y guardar el ingreso de la moto y cliente
// LENGUAJES: PHP + MySQL (PDO)
// ========================================================

require_once 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $tipo_cliente = $_POST['tipo_cliente'] ?? 'existente';
    $id_cliente   = null;

    // 1. GESTIONAR EL CLIENTE
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
            // Insertamos el nuevo cliente
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
            echo "<h3 style='color:orange;'>Por favor seleccioná un cliente existente o cambiá a la opción de 'Nuevo Cliente'.</h3>";
            echo "<br><a href='nueva_moto.php'>Volver al formulario</a>";
            exit;
        }
    }

    // 2. RECIBIR DATOS DE LA MOTO Y REPARACIÓN
    $patente         = strtoupper(trim($_POST['patente'] ?? ''));
    $marca           = trim($_POST['marca'] ?? '');
    $modelo          = trim($_POST['modelo'] ?? '');
    $color           = trim($_POST['color'] ?? '');
    $anio            = !empty($_POST['anio']) ? intval($_POST['anio']) : null;
    $kilometraje     = !empty($_POST['kilometraje']) ? intval($_POST['kilometraje']) : 0;
    
    $falla_reportada = trim($_POST['falla_reportada'] ?? '');
    $fecha_prometida = !empty($_POST['fecha_prometida']) ? $_POST['fecha_prometida'] : null;
    $precio_estimado = !empty($_POST['precio_estimado']) ? floatval($_POST['precio_estimado']) : 0.00;
    $estado          = trim($_POST['estado'] ?? 'En Espera');

    // Validación de campos obligatorios
    if (empty($patente) || empty($marca) || empty($modelo) || empty($falla_reportada)) {
        echo "<h3 style='color:orange;'>Por favor completá todos los campos obligatorios de la moto (Patente, Marca, Modelo y Falla).</h3>";
        echo "<br><a href='nueva_moto.php'>Volver al formulario</a>";
        exit;
    }

    // 3. INSERTAR LA MOTO EN LA BASE DE DATOS
    try {
        $sqlMoto = "INSERT INTO motos (
                        id_cliente, marca, modelo, anio, patente, color, kilometraje, 
                        falla_reportada, precio_estimado, estado, fecha_ingreso, fecha_prometida
                    ) VALUES (
                        :id_cliente, :marca, :modelo, :anio, :patente, :color, :kilometraje, 
                        :falla_reportada, :precio_estimado, :estado, NOW(), :fecha_prometida
                    )";

        $stmtMoto = $pdo->prepare($sqlMoto);
        $stmtMoto->execute([
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

        // Redirigir al tablero de motos con éxito
        header('Location: motos.php?guardado=ok');
        exit;

    } catch (PDOException $e) {
        // En caso de que la patente ya exista en la base de datos
        if ($e->getCode() == 23000) {
            echo "<div style='font-family: sans-serif; padding: 20px; background: #fee2e2; color: #991b1b; border-radius: 8px;'>";
            echo "<h3>⚠️ Atención: Ya existe una moto registrada con la patente <strong>" . htmlspecialchars($patente) . "</strong>.</h3>";
            echo "<p>Podés buscarla en el listado para editar su estado y registrar una nueva reparación.</p>";
            echo "<a href='motos.php' style='display: inline-block; padding: 8px 16px; background: #1e293b; color: white; text-decoration: none; border-radius: 6px;'>Ir al Tablero de Motos</a>";
            echo "</div>";
        } else {
            echo "<h3 style='color:red;'>Error al guardar la moto: " . $e->getMessage() . "</h3>";
            echo "<br><a href='nueva_moto.php'>Volver al formulario</a>";
        }
        exit;
    }

} else {
    header('Location: motos.php');
    exit;
}
?>
