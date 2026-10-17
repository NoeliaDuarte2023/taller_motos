<?php
// ========================================================
// ARCHIVO: clientes.php
// OBJETIVO: Padrón de clientes y listado de sus vehículos (1FN y 2FN)
// LENGUAJES: PHP + HTML5 + Bootstrap 5 + JavaScript
// ========================================================

require_once 'conexion.php';

// Consultamos clientes con la cantidad de motos registradas y el detalle de cada vehículo
$sqlClientes = "SELECT c.*, 
                COUNT(m.id_moto) AS total_motos,
                GROUP_CONCAT(CONCAT(m.marca, ' ', m.modelo, ' (', m.patente, ')') SEPARATOR '||') AS detalle_motos
                FROM clientes c
                LEFT JOIN motos m ON c.id_cliente = m.id_cliente
                GROUP BY c.id_cliente
                ORDER BY c.apellido ASC, c.nombre ASC";

$stmt = $pdo->query($sqlClientes);
$clientes = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Padrón de Clientes - Taller de Motos</title>
    <!-- Bootstrap 5 CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">

    <!-- Barra de Navegación Común -->
    <?php include 'navbar.php'; ?>

    <div class="container mb-5">
        
        <!-- Encabezado con Botón a Página de Alta AFUERA DE LA GRILLA -->
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
            <div>
                <h3 class="fw-bold mb-1 text-dark">
                    <i class="bi bi-people-fill me-2 text-info"></i>Padrón de Clientes
                </h3>
                <p class="text-muted mb-0">Clientes registrados y parque de motos asociadas a cada dueño</p>
            </div>
            <div class="d-flex gap-2">
                <a href="nuevo_cliente.php" class="btn btn-primary fw-bold shadow-sm">
                    <i class="bi bi-person-plus-fill me-1"></i> + Nuevo Cliente
                </a>
            </div>
        </div>

        <?php if (isset($_GET['guardado'])): ?>
            <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                ¡Cliente registrado correctamente!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['actualizado'])): ?>
            <div class="alert alert-info alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-info-circle-fill me-2"></i>
                Datos del cliente actualizados.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['eliminado'])): ?>
            <div class="alert alert-warning alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                Cliente eliminado del sistema.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Barra de Búsqueda Rápida de Clientes -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body p-3">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 text-muted">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" id="buscadorClientes" class="form-control border-start-0 ps-0" 
                           placeholder="Buscar por apellido, nombre, teléfono, dirección o moto..." onkeyup="filtrarClientes()">
                </div>
            </div>
        </div>

        <!-- LISTADO DE CLIENTES -->
        <div class="card shadow-sm border-0">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center py-3">
                <h5 class="mb-0 fs-6 fw-bold">Clientes Registrados</h5>
                <span class="badge bg-secondary"><?php echo count($clientes); ?> Total</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>Cliente / Nombre Completo</th>
                                <th>Teléfono de Contacto</th>
                                <th>Dirección</th>
                                <th>Motos Registradas (Parque)</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tablaClientes">
                            <?php if (empty($clientes)): ?>
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <i class="bi bi-people fs-1 d-block mb-2"></i>
                                        No hay clientes registrados aún.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($clientes as $c): 
                                    $listaMotos = !empty($c['detalle_motos']) ? explode('||', $c['detalle_motos']) : [];
                                ?>
                                    <tr class="fila-cliente" data-search="<?php echo strtolower(htmlspecialchars($c['nombre'] . ' ' . $c['apellido'] . ' ' . $c['telefono'] . ' ' . ($c['direccion'] ?? '') . ' ' . ($c['detalle_motos'] ?? ''))); ?>">
                                        
                                        <!-- Nombre -->
                                        <td>
                                            <div class="fw-bold text-dark fs-6">
                                                <?php echo htmlspecialchars($c['apellido'] . ', ' . $c['nombre']); ?>
                                            </div>
                                            <small class="text-muted">ID: #<?php echo $c['id_cliente']; ?></small>
                                        </td>

                                        <!-- Teléfono -->
                                        <td>
                                            <strong class="text-dark"><i class="bi bi-telephone me-1 text-muted"></i><?php echo htmlspecialchars($c['telefono']); ?></strong>
                                            <?php if (!empty($c['email'])): ?>
                                                <small class="text-muted d-block"><?php echo htmlspecialchars($c['email']); ?></small>
                                            <?php endif; ?>
                                        </td>

                                        <!-- Dirección -->
                                        <td>
                                            <span class="text-secondary small">
                                                <i class="bi bi-geo-alt me-1 text-danger"></i>
                                                <?php echo !empty($c['direccion']) ? htmlspecialchars($c['direccion']) : '<span class="text-muted fst-italic">No informada</span>'; ?>
                                            </span>
                                        </td>

                                        <!-- Motos Registradas (Conservadas permanentemente) -->
                                        <td>
                                            <?php if (empty($listaMotos)): ?>
                                                <span class="badge bg-light text-muted border">Sin motos asociadas</span>
                                            <?php else: ?>
                                                <div class="d-flex flex-column gap-1">
                                                    <?php foreach ($listaMotos as $mInfo): ?>
                                                        <span class="badge bg-secondary text-start p-1.5 font-monospace text-truncate" style="max-width: 280px;">
                                                            <i class="bi bi-bicycle me-1 text-warning"></i><?php echo htmlspecialchars($mInfo); ?>
                                                        </span>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>

                                        <!-- Acciones -->
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm">
                                                <a href="nueva_moto.php?cliente_id=<?php echo $c['id_cliente']; ?>&solo_moto=1" class="btn btn-outline-success" title="Registrar Moto para este cliente">
                                                    <i class="bi bi-plus-circle me-1"></i> + Moto
                                                </a>
                                                <a href="editar_cliente.php?id=<?php echo $c['id_cliente']; ?>" class="btn btn-outline-warning" title="Editar Cliente">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <a href="eliminar_cliente.php?id=<?php echo $c['id_cliente']; ?>" class="btn btn-outline-danger" 
                                                   onclick="return confirm('¿Seguro que deseas eliminar este cliente?');" title="Eliminar Cliente">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </div>
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

    <!-- Script de Búsqueda Rápida de Clientes -->
    <script>
        function filtrarClientes() {
            const q = document.getElementById('buscadorClientes').value.toLowerCase();
            const filas = document.querySelectorAll('.fila-cliente');

            filas.forEach(f => {
                const s = f.getAttribute('data-search') || '';
                if (s.includes(q)) {
                    f.style.display = '';
                } else {
                    f.style.display = 'none';
                }
            });
        }
    </script>

    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
