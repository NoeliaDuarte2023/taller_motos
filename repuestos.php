<?php
// ========================================================
// ARCHIVO: repuestos.php
// OBJETIVO: Inventario de repuestos y control de stock
// LENGUAJES: PHP + HTML5 + Bootstrap 5
// ========================================================

require_once 'conexion.php';

// Consulta de repuestos ordenados alfabéticamente
$stmt = $pdo->query("SELECT * FROM repuestos ORDER BY descripcion ASC");
$repuestos = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Taller de Motos - Repuestos</title>
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
                <a class="nav-link active fw-bold" href="repuestos.php"><i class="bi bi-box-seam me-1"></i> Repuestos</a>
                <a class="nav-link" href="mecanicos.php"><i class="bi bi-person-badge me-1"></i> Mecánicos</a>
            </div>
        </div>
    </nav>

    <div class="container mb-5">
        
        <!-- Encabezado con Botón a Página de Alta -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-0 text-dark"><i class="bi bi-box-seam me-2 text-primary"></i>Inventario de Repuestos</h3>
                <p class="text-muted mb-0">Control de stock, precios y repuestos disponibles</p>
            </div>
            <!-- Botón que lleva a la página separada de nuevo repuesto -->
            <a href="nuevo_repuesto.php" class="btn btn-primary fw-bold shadow-sm">
                <i class="bi bi-plus-circle me-1"></i> + Nuevo Repuesto
            </a>
        </div>

        <!-- LISTADO DE REPUESTOS -->
        <div class="card shadow-sm border-0">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center py-3">
                <h5 class="mb-0 fs-6">Repuestos en Stock</h5>
                <span class="badge bg-secondary"><?php echo count($repuestos); ?> Total</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>Código</th>
                                <th>Descripción / Nombre</th>
                                <th>Precio Unitario</th>
                                <th>Stock</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($repuestos)): ?>
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                        No hay repuestos registrados aún. Haz clic en "+ Nuevo Repuesto" para agregar uno.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($repuestos as $r): ?>
                                    <tr>
                                        <td class="fw-bold font-monospace"><span class="badge bg-secondary"><?php echo htmlspecialchars($r['codigo']); ?></span></td>
                                        <td class="fw-semibold"><?php echo htmlspecialchars($r['descripcion']); ?></td>
                                        <td class="fw-bold text-success">$<?php echo number_format($r['precio_unitario'], 2, ',', '.'); ?></td>
                                        <td>
                                            <?php if ($r['stock'] <= $r['stock_minimo']): ?>
                                                <span class="badge bg-danger" title="Poco stock">
                                                    <i class="bi bi-exclamation-triangle-fill"></i> <?php echo $r['stock']; ?> un. (Bajo)
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-success"><?php echo $r['stock']; ?> un.</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <!-- Botón Editar -->
                                            <a href="editar_repuesto.php?id=<?php echo $r['id_repuesto']; ?>" class="btn btn-sm btn-outline-warning me-1" title="Editar">
                                                <i class="bi bi-pencil"></i> Editar
                                            </a>
                                            <!-- Botón Eliminar -->
                                            <a href="eliminar_repuesto.php?id=<?php echo $r['id_repuesto']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Seguro que deseas eliminar este repuesto?');" title="Eliminar">
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