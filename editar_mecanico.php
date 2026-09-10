<?php
require_once 'conexion.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: mecanicos.php');
    exit;
}

$id_mecanico = $_GET['id'];

$stmt = $pdo->prepare("SELECT * FROM mecanicos WHERE id_mecanico = :id");
$stmt->execute([':id' => $id_mecanico]);
$mecanico = $stmt->fetch();

if (!$mecanico) {
    header('Location: mecanicos.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Mecánico - Taller de Motos</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4 shadow">
        <div class="container">
            <a class="navbar-brand fw-bold" href="mecanicos.php">🏍️ Taller de Motos</a>
        </div>
    </nav>

    <div class="container mb-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0 fw-bold"><i class="bi bi-pencil-square me-2"></i>Editar Mecánico #<?php echo $mecanico['id_mecanico']; ?></h5>
                    </div>
                    <div class="card-body">
                        <form action="actualizar_mecanico.php" method="POST">
                            
                            <input type="hidden" name="id_mecanico" value="<?php echo $mecanico['id_mecanico']; ?>">

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Nombre y Apellido *</label>
                                <input type="text" name="nombre" class="form-control" value="<?php echo htmlspecialchars($mecanico['nombre']); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Teléfono</label>
                                <input type="tel" name="telefono" class="form-control" value="<?php echo htmlspecialchars($mecanico['telefono'] ?? ''); ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Especialidad</label>
                                <input type="text" name="especialidad" class="form-control" value="<?php echo htmlspecialchars($mecanico['especialidad'] ?? ''); ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Estado</label>
                                <select name="estado" class="form-select">
                                    <option value="Activo" <?php echo ($mecanico['estado'] === 'Activo') ? 'selected' : ''; ?>>Activo</option>
                                    <option value="Inactivo" <?php echo ($mecanico['estado'] === 'Inactivo') ? 'selected' : ''; ?>>Inactivo</option>
                                </select>
                            </div>

                            <div class="d-flex justify-content-between gap-2 mt-4">
                                <a href="mecanicos.php" class="btn btn-secondary w-50">
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