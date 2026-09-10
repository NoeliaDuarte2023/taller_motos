<?php
// ========================================================
// ARCHIVO: motos.php
// OBJETIVO: Listado general de motos registradas y sus dueños
// LENGUAJES: PHP + HTML5 + Bootstrap 5
// ========================================================

require_once 'conexion.php';

// Consulta SQL con INNER JOIN para vincular cada moto con su cliente (dueño)
$sqlMotos = "SELECT m.*, c.nombre AS cliente_nombre, c.apellido AS cliente_apellido 
             FROM motos m 
             INNER JOIN clientes c ON m.id_cliente = c.id_cliente 
             ORDER BY m.id_moto DESC";
$stmtMotos = $pdo->query($sqlMotos);
$motos = $stmtMotos->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Taller de Motos - Motos</title>
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
                <a class="nav-link active fw-bold" href="motos.php"><i class="bi bi-bicycle me-1"></i> Motos</a>
                <a class="nav-link" href="repuestos.php"><i class="bi bi-box-seam me-1"></i> Repuestos</a>
                <a class="nav-link" href="mecanicos.php"><i class="bi bi-person-badge me-1"></i> Mecánicos</a>
            </div>
        </div>
    </nav>

    <div class="container mb-5">
        
        <!-- Encabezado con Botón a Página de Alta -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-0 text-dark"><i class="bi bi-bicycle me-2 text-primary"></i>Gestión de Motos</h3>
                <p class="text-muted mb-0">Listado de vehículos registrados en el taller</p>
            </div>
            <!-- Botón que lleva a la página separada de nueva moto -->
            <a href="nueva_moto.php" class="btn btn-primary fw-bold shadow-sm">
                <i class="bi bi-plus-circle me-1"></i> + Nueva Moto
            </a>
        </div>

        <!-- LISTADO DE MOTOS -->
        <div class="card shadow-sm border-0">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center py-3">
                <h5 class="mb-0 fs-6">Motos Registradas</h5>
                <span class="badge bg-secondary"><?php echo count($motos); ?> Total</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>Patente</th>
                                <th>Vehículo (Marca / Modelo)</th>
                                <th>Dueño (Cliente)</th>
                                <th>Año / Kilometraje</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($motos)): ?>
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                        No hay motos registradas aún. Haz clic en "+ Nueva Moto" para agregar una.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($motos as $m): ?>
                                    <tr>
                                        <td>
                                            <span class="badge bg-dark fs-6 font-monospace"><?php echo htmlspecialchars($m['patente']); ?></span>
                                        </td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($m['marca'] . ' ' . $m['modelo']); ?></strong>
                                            <?php if (!empty($m['color'])): ?>
                                                <br><small class="text-muted">Color: <?php echo htmlspecialchars($m['color']); ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <i class="bi bi-person me-1 text-primary"></i>
                                            <?php echo htmlspecialchars($m['cliente_nombre'] . ' ' . $m['cliente_apellido']); ?>
                                        </td>
                                        <td>
                                            <small class="d-block">Año: <?php echo htmlspecialchars($m['anio'] ?? '-'); ?></small>
                                            <small class="text-muted"><?php echo number_format($m['kilometraje'], 0, ',', '.'); ?> km</small>
                                        </td>
                                        <td class="text-center">
                                            <!-- Botón Editar -->
                                            <a href="editar_moto.php?id=<?php echo $m['id_moto']; ?>" class="btn btn-sm btn-outline-warning me-1" title="Editar">
                                                <i class="bi bi-pencil"></i> Editar
                                            </a>
                                            <!-- Botón Eliminar -->
                                            <a href="eliminar_moto.php?id=<?php echo $m['id_moto']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Seguro que deseas eliminar esta moto?');" title="Eliminar">
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