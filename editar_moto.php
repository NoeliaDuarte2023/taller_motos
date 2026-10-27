<?php
// ========================================================
// ARCHIVO: editar_moto.php
// OBJETIVO: Edición de datos del vehículo (ABM de Motos)
// LENGUAJES: PHP + HTML5 + Bootstrap 5 + JavaScript
// ========================================================

require_once 'conexion.php';

$id_moto = intval($_GET['id'] ?? 0);
if ($id_moto <= 0) {
    header('Location: motos.php');
    exit;
}

// Consultamos la moto
$stmt = $pdo->prepare("SELECT m.*, c.nombre AS cliente_nombre, c.apellido AS cliente_apellido 
                       FROM motos m 
                       INNER JOIN clientes c ON m.id_cliente = c.id_cliente 
                       WHERE m.id_moto = :id");
$stmt->execute([':id' => $id_moto]);
$moto = $stmt->fetch();

if (!$moto) {
    echo "<h3 style='color:red;'>Moto no encontrada.</h3><a href='motos.php'>Volver</a>";
    exit;
}

// Consultamos todos los clientes para poder cambiar de dueño si fue vendida
$clientes = $pdo->query("SELECT id_cliente, nombre, apellido, telefono FROM clientes ORDER BY apellido ASC, nombre ASC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Moto <?php echo htmlspecialchars($moto['patente']); ?> - Taller de Motos</title>
    <!-- Bootstrap 5 CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">

    <!-- Barra de Navegación Común -->
    <?php include 'navbar.php'; ?>

    <div class="container mb-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                
                <div class="card shadow border-0 rounded-3">
                    <div class="card-header bg-dark text-white p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h4 class="mb-1 fw-bold">
                                    <i class="bi bi-bicycle text-info me-2"></i>Editar Datos del Vehículo
                                </h4>
                                <span class="badge bg-warning text-dark font-monospace fs-6 mt-1">
                                    <?php echo htmlspecialchars($moto['patente']); ?>
                                </span>
                            </div>
                            <a href="motos.php" class="btn btn-outline-light btn-sm">
                                <i class="bi bi-arrow-left me-1"></i> Volver a Motos
                            </a>
                        </div>
                    </div>
                    <div class="card-body p-4 p-md-5">
                        
                        <form action="actualizar_moto.php" method="POST">
                            <input type="hidden" name="id_moto" value="<?php echo $moto['id_moto']; ?>">

                            <!-- Dueño actual -->
                            <div class="mb-4 p-3 bg-light rounded-3 border">
                                <label class="form-label fw-bold text-dark">Cliente Dueño del Vehículo *</label>
                                <select name="id_cliente" class="form-select form-select-lg" required>
                                    <?php foreach ($clientes as $cli): ?>
                                        <option value="<?php echo $cli['id_cliente']; ?>" <?php echo ($cli['id_cliente'] == $moto['id_cliente']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($cli['apellido'] . ', ' . $cli['nombre'] . ' (Tel: ' . $cli['telefono'] . ')'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="text-muted">Podés reasignar la moto si fue vendida o transferida a otro cliente.</small>
                            </div>

                            <!-- Datos del Vehículo -->
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Patente / Dominio *</label>
                                    <input type="text" name="patente" class="form-control text-uppercase font-monospace fw-bold" 
                                           value="<?php echo htmlspecialchars($moto['patente']); ?>" 
                                           pattern="^([A-Za-z]{1}[0-9]{3}[A-Za-z]{3}|[0-9]{3}[A-Za-z]{3}|[A-Za-z]{3}[0-9]{3}|[A-Za-z]{2}[0-9]{3}[A-Za-z]{2})$" 
                                           title="Formato válido: A123BCD (motos Mercosur), OBJ123 o 123ABC" 
                                           required>
                                    <small class="text-muted" style="font-size: 11px;">Formatos válidos: A123BCD, OBJ123, 123ABC</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Marca *</label>
                                    <input type="text" name="marca" maxlength="50" class="form-control" 
                                           value="<?php echo htmlspecialchars($moto['marca']); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Modelo *</label>
                                    <input type="text" name="modelo" maxlength="100" class="form-control" 
                                           value="<?php echo htmlspecialchars($moto['modelo']); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Color</label>
                                    <input type="text" name="color" maxlength="30" class="form-control" 
                                           value="<?php echo htmlspecialchars($moto['color'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Año</label>
                                    <input type="number" name="anio" class="form-control" 
                                           value="<?php echo htmlspecialchars($moto['anio'] ?? ''); ?>" min="1950" max="<?php echo date('Y') + 1; ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Kilometraje Actual</label>
                                    <input type="number" name="kilometraje" max="999999" class="form-control" 
                                           value="<?php echo htmlspecialchars($moto['kilometraje'] ?? '0'); ?>" min="0">
                                </div>
                            </div>

                            <!-- Botones -->
                            <div class="d-flex justify-content-end gap-2 border-top pt-4">
                                <a href="motos.php" class="btn btn-secondary px-4">Cancelar</a>
                                <button type="submit" class="btn btn-primary px-5 fw-bold shadow">
                                    <i class="bi bi-check-circle-fill me-1"></i> Guardar Cambios
                                </button>
                            </div>

                        </form>

                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
