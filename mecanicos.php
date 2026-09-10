<?php
// ========================================================
// ARCHIVO: mecanicos.php
// OBJETIVO: Listado y administración del equipo de mecánicos
// LENGUAJES: PHP + HTML5 + Bootstrap 5
// ========================================================

require_once 'conexion.php';

// Consultamos todos los mecánicos
$stmt = $pdo->query("SELECT * FROM mecanicos ORDER BY id_mecanico DESC");
$mecanicos = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Taller de Motos - Mecánicos</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">

    <!-- Barra de Navegación -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4 shadow">
        <div class="container">
            <a class="navbar-brand fw-bold" href="clientes.php">🏍️ Taller de Motos</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="clientes.php"><i class="bi bi-people me-1"></i> Clientes</a>
                <a class="nav-link" href="motos.php"><i class="bi bi-bicycle me-1"></i> Motos</a>
                <a class="nav-link" href="repuestos.php"><i class="bi bi-box-seam me-1"></i> Repuestos</a>
                <a class="nav-link active fw-bold" href="mecanicos.php"><i class="bi bi-person-badge me-1"></i> Mecánicos</a>
            </div>
        </div>
    </nav>

    <div class="container mb-5">
        
        <!-- Encabezado con Botón a Página de Alta -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-0 text-dark"><i class="bi bi-person-badge me-2 text-primary"></i>Equipo de Mecánicos</h3>
                <p class="text-muted mb-0">Administración del personal técnico del taller</p>
            </div>
            <!-- Botón que lleva a la página separada de nuevo mecánico -->
            <a href="nuevo_mecanico.php" class="btn btn-primary fw-bold shadow-sm">
                <i class="bi bi-person-plus-fill me-1"></i> + Nuevo Mecánico
            </a>
        </div>

        <!-- LISTADO DE MECÁNICOS -->
        <div class="card shadow-sm border-0">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center py-3">
                <h5 class="mb-0 fs-6">Mecánicos Registrados</h5>
                <span class="badge bg-secondary"><?php echo count($mecanicos); ?> Total</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Nombre y Apellido</th>
                                <th>Teléfono</th>
                                <th>Especialidad</th>
                                <th>Estado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($mecanicos)): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                        No hay mecánicos registrados aún. Haz clic en "+ Nuevo Mecánico" para agregar uno.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($mecanicos as $m): ?>
                                    <tr>
                                        <td class="fw-bold text-secondary">#<?php echo $m['id_mecanico']; ?></td>
                                        <td class="fw-semibold"><?php echo htmlspecialchars($m['nombre']); ?></td>
                                        <td><?php echo htmlspecialchars($m['telefono'] ?? '-'); ?></td>
                                        <td>
                                            <span class="badge bg-info text-dark">
                                                <?php echo htmlspecialchars($m['especialidad'] ?? 'General'); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($m['estado'] === 'Activo'): ?>
                                                <span class="badge bg-success">Activo</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Inactivo</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <!-- Botón Editar -->
                                            <a href="editar_mecanico.php?id=<?php echo $m['id_mecanico']; ?>" class="btn btn-sm btn-outline-warning me-1" title="Editar">
                                                <i class="bi bi-pencil"></i> Editar
                                            </a>
                                            <!-- Botón Eliminar -->
                                            <a href="eliminar_mecanico.php?id=<?php echo $m['id_mecanico']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Seguro que deseas eliminar este mecánico?');" title="Eliminar">
                                                <i class="bi bi-trash"></i> Eliminar
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

</body>
</html>