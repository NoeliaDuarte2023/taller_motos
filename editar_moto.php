<?php
require_once 'conexion.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: motos.php');
    exit;
}

$id_moto = $_GET['id'];

// Consultamos la moto
$stmt = $pdo->prepare("SELECT * FROM motos WHERE id_moto = :id");
$stmt->execute([':id' => $id_moto]);
$moto = $stmt->fetch();

if (!$moto) {
    header('Location: motos.php');
    exit;
}

// Consultamos todos los clientes para el desplegable
$stmtClientes = $pdo->query("SELECT id_cliente, nombre, apellido FROM clientes ORDER BY apellido ASC");
$clientes = $stmtClientes->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Moto - Taller de Motos</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4 shadow">
        <div class="container">
            <a class="navbar-brand fw-bold" href="motos.php">🏍️ Taller de Motos</a>
        </div>
    </nav>

    <div class="container mb-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0 fw-bold"><i class="bi bi-pencil-square me-2"></i>Editar Moto (Patente: <?php echo htmlspecialchars($moto['patente']); ?>)</h5>
                    </div>
                    <div class="card-body">
                        <form action="actualizar_moto.php" method="POST">
                            
                            <!-- ID Oculto -->
                            <input type="hidden" name="id_moto" value="<?php echo $moto['id_moto']; ?>">

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Dueño / Cliente *</label>
                                <select name="id_cliente" class="form-select" required>
                                    <?php foreach ($clientes as $cli): ?>
                                        <option value="<?php echo $cli['id_cliente']; ?>" <?php echo ($cli['id_cliente'] == $moto['id_cliente']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($cli['apellido'] . ', ' . $cli['nombre']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Marca *</label>
                                <input type="text" name="marca" class="form-control" value="<?php echo htmlspecialchars($moto['marca']); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Modelo *</label>
                                <input type="text" name="modelo" class="form-control" value="<?php echo htmlspecialchars($moto['modelo']); ?>" required>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Año</label>
                                    <input type="number" name="anio" class="form-control" value="<?php echo htmlspecialchars($moto['anio'] ?? ''); ?>" min="1950" max="2030">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Patente / Placa *</label>
                                    <input type="text" name="patente" class="form-control" value="<?php echo htmlspecialchars($moto['patente']); ?>" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Color</label>
                                    <input type="text" name="color" class="form-control" value="<?php echo htmlspecialchars($moto['color'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Kilometraje</label>
                                    <input type="number" name="kilometraje" class="form-control" value="<?php echo htmlspecialchars($moto['kilometraje']); ?>" min="0">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Observaciones</label>
                                <textarea name="observaciones" class="form-control" rows="2"><?php echo htmlspecialchars($moto['observaciones'] ?? ''); ?></textarea>
                            </div>

                            <div class="d-flex justify-content-between gap-2 mt-4">
                                <a href="motos.php" class="btn btn-secondary w-50">
                                    <i class="bi bi-arrow-left me-1"></i> Cancelar
                                </a>
                                <button type="submit" class="btn btn-warning w-50 fw-bold">
                                    <i class="bi bi-check-lg me-1"></i> Guardar Cambios
                                </button>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>