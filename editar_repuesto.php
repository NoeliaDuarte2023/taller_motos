<?php
require_once 'conexion.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: repuestos.php');
    exit;
}

$id_repuesto = $_GET['id'];

$stmt = $pdo->prepare("SELECT * FROM repuestos WHERE id_repuesto = :id");
$stmt->execute([':id' => $id_repuesto]);
$repuesto = $stmt->fetch();

if (!$repuesto) {
    header('Location: repuestos.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Repuesto - Taller de Motos</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4 shadow">
        <div class="container">
            <a class="navbar-brand fw-bold" href="repuestos.php">🏍️ Taller de Motos</a>
        </div>
    </nav>

    <div class="container mb-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0 fw-bold"><i class="bi bi-pencil-square me-2"></i>Editar Repuesto (Código: <?php echo htmlspecialchars($repuesto['codigo']); ?>)</h5>
                    </div>
                    <div class="card-body">
                        <form action="actualizar_repuesto.php" method="POST">
                            
                            <input type="hidden" name="id_repuesto" value="<?php echo $repuesto['id_repuesto']; ?>">

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Código del Repuesto *</label>
                                <input type="text" name="codigo" class="form-control" value="<?php echo htmlspecialchars($repuesto['codigo']); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Descripción / Nombre *</label>
                                <input type="text" name="descripcion" class="form-control" value="<?php echo htmlspecialchars($repuesto['descripcion']); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Precio Unitario ($) *</label>
                                <input type="number" step="0.01" name="precio_unitario" class="form-control" value="<?php echo htmlspecialchars($repuesto['precio_unitario']); ?>" required min="0">
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Stock Actual *</label>
                                    <input type="number" name="stock" class="form-control" value="<?php echo htmlspecialchars($repuesto['stock']); ?>" required min="0">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Stock Mínimo</label>
                                    <input type="number" name="stock_minimo" class="form-control" value="<?php echo htmlspecialchars($repuesto['stock_minimo']); ?>" min="0">
                                </div>
                            </div>

                            <div class="d-flex justify-content-between gap-2 mt-4">
                                <a href="repuestos.php" class="btn btn-secondary w-50">
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