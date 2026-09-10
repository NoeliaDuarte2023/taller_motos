<?php
// ========================================================
// ARCHIVO: clientes.php
// OBJETIVO: Listado general de clientes del taller
// LENGUAJES: PHP + HTML5 + Bootstrap 5
// ========================================================

require_once 'conexion.php';

// Consultamos todos los clientes ordenados por los más recientes
$stmt = $pdo->query("SELECT * FROM clientes ORDER BY id_cliente DESC");
$clientes = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Taller de Motos - Clientes</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">

    <!-- Barra de Navegación Superior -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4 shadow">
        <div class="container">
            <a class="navbar-brand fw-bold" href="clientes.php">🏍️ Taller de Motos</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <div class="navbar-nav ms-auto">
                    <a class="nav-link active fw-bold" href="clientes.php"><i class="bi bi-people me-1"></i> Clientes</a>
                    <a class="nav-link" href="motos.php"><i class="bi bi-bicycle me-1"></i> Motos</a>
                    <a class="nav-link" href="repuestos.php"><i class="bi bi-box-seam me-1"></i> Repuestos</a>
                    <a class="nav-link" href="mecanicos.php"><i class="bi bi-person-badge me-1"></i> Mecánicos</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mb-5">
        
        <!-- Encabezado con Botón a Página de Alta -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-0 text-dark"><i class="bi bi-people-fill me-2 text-primary"></i>Gestión de Clientes</h3>
                <p class="text-muted mb-0">Listado de clientes registrados en el sistema</p>
            </div>
            <!-- Botón que lleva a la página separada de nuevo cliente -->
            <a href="nuevo_cliente.php" class="btn btn-primary fw-bold shadow-sm">
                <i class="bi bi-person-plus-fill me-1"></i> + Nuevo Cliente
            </a>
        </div>

        <!-- LISTADO DE CLIENTES (A pantalla completa) -->
        <div class="card shadow-sm border-0">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center py-3">
                <h5 class="mb-0 fs-6">Clientes Registrados</h5>
                <span class="badge bg-secondary"><?php echo count($clientes); ?> Total</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Nombre y Apellido</th>
                                <th>Teléfono</th>
                                <th>Email</th>
                                <th>Dirección</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($clientes)): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                        No hay clientes registrados aún. Haz clic en "+ Nuevo Cliente" para agregar uno.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($clientes as $c): ?>
                                    <tr>
                                        <td class="fw-bold text-secondary">#<?php echo $c['id_cliente']; ?></td>
                                        <td class="fw-semibold"><?php echo htmlspecialchars($c['nombre'] . ' ' . $c['apellido']); ?></td>
                                        <td><?php echo htmlspecialchars($c['telefono']); ?></td>
                                        <td><?php echo htmlspecialchars($c['email'] ?? '-'); ?></td>
                                        <td><?php echo htmlspecialchars($c['direccion'] ?? '-'); ?></td>
                                        <td class="text-center">
                                            <!-- Botón Editar (Página separada) -->
                                            <a href="editar_cliente.php?id=<?php echo $c['id_cliente']; ?>" class="btn btn-sm btn-outline-warning me-1" title="Editar">
                                                <i class="bi bi-pencil"></i> Editar
                                            </a>
                                            <!-- Botón Eliminar -->
                                            <a href="eliminar_cliente.php?id=<?php echo $c['id_cliente']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Seguro que deseas eliminar este cliente?');" title="Eliminar">
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