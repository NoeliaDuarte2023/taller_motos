<?php
// ========================================================
// ARCHIVO: motos.php
// OBJETIVO: ABM Exclusivo de Motos (Requerimiento de Cátedra)
// "también debería haber donde administrar solo motos"
// LENGUAJES: PHP + HTML5 + Bootstrap 5 + JavaScript
// ========================================================

require_once 'conexion.php';

// Consultamos todas las motos registradas con los datos de sus clientes dueños
// y el último estado de trabajo (si tiene o no en taller)
$sqlMotos = "SELECT m.*, 
                    c.nombre AS cliente_nombre, c.apellido AS cliente_apellido, 
                    c.telefono AS cliente_telefono, c.direccion AS cliente_direccion,
                    (SELECT t.estado FROM trabajos t WHERE t.id_moto = m.id_moto ORDER BY t.id_trabajo DESC LIMIT 1) AS ultimo_estado,
                    (SELECT COUNT(*) FROM trabajos t WHERE t.id_moto = m.id_moto) AS total_trabajos
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
    <title>Administración de Motos - Taller de Motos</title>
    <!-- Bootstrap 5 CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .badge-patente {
            font-family: monospace;
            letter-spacing: 1px;
            font-size: 0.95rem;
            padding: 6px 10px;
            border-radius: 6px;
        }
    </style>
</head>
<body class="bg-light">

    <!-- Barra de Navegación Común -->
    <?php include 'navbar.php'; ?>

    <div class="container mb-5">
        
        <!-- Encabezado con Botón de Alta AFUERA DE LA GRILLA (Pedido de Cátedra) -->
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
            <div>
                <h3 class="fw-bold mb-1 text-dark">
                    <i class="bi bi-bicycle me-2 text-info"></i>Administración de Motos
                </h3>
                <p class="text-muted mb-0">Padrón de vehículos registrados, asignación de dueños e historial de servicios</p>
            </div>
            <div>
                <!-- Botón de alta fuera de la grilla -->
                <a href="nueva_moto.php?solo_moto=1" class="btn btn-success fw-bold shadow-sm px-3 py-2">
                    <i class="bi bi-plus-circle-fill me-1"></i> + Registrar Nueva Moto
                </a>
            </div>
        </div>

        <?php if (isset($_GET['guardado'])): ?>
            <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                ¡Moto registrada correctamente en el padrón!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['actualizado'])): ?>
            <div class="alert alert-info alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-info-circle-fill me-2"></i>
                Datos del vehículo actualizados correctamente.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['eliminado'])): ?>
            <div class="alert alert-warning alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                Moto eliminada del sistema.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Buscador en Tiempo Real -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body p-3">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 text-muted">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" id="buscadorMotos" class="form-control border-start-0 ps-0" 
                           placeholder="Buscar por patente, marca, modelo, dueño, teléfono..." onkeyup="filtrarMotos()">
                </div>
            </div>
        </div>

        <!-- TABLA DE ADMINISTRACIÓN DE MOTOS -->
        <div class="card shadow-sm border-0">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center py-3">
                <h5 class="mb-0 fs-6 fw-bold">
                    <i class="bi bi-card-list me-2 text-info"></i>Parque de Vehículos Registrados
                </h5>
                <span class="badge bg-secondary" id="contadorFiltradas"><?php echo count($motos); ?> Motos</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="tablaMotos">
                        <thead class="table-dark">
                            <tr>
                                <th>Patente</th>
                                <th>Marca y Modelo</th>
                                <th>Año y Color</th>
                                <th>Kilometraje</th>
                                <th>Dueño / Contacto</th>
                                <th>Historial en Taller</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($motos)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <i class="bi bi-bicycle fs-1 text-muted d-block mb-2"></i>
                                        <strong>No hay motos registradas en el sistema.</strong>
                                        <p class="small text-muted mb-3">Registrá la primera moto haciendo clic en el botón superior.</p>
                                        <a href="nueva_moto.php?solo_moto=1" class="btn btn-sm btn-success">
                                            <i class="bi bi-plus-circle me-1"></i> + Registrar Moto
                                        </a>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($motos as $m): 
                                    $estado = $m['ultimo_estado'] ?? 'Sin trabajos';
                                    $badgeClass = 'bg-secondary';
                                    if ($estado === 'En Espera') $badgeClass = 'bg-warning text-dark';
                                    if ($estado === 'En Reparacion') $badgeClass = 'bg-primary text-white';
                                    if ($estado === 'Terminado') $badgeClass = 'bg-success text-white';
                                    if ($estado === 'Entregado') $badgeClass = 'bg-dark text-white';
                                ?>
                                    <tr class="fila-moto" 
                                        data-search="<?php echo strtolower(htmlspecialchars($m['patente'] . ' ' . $m['marca'] . ' ' . $m['modelo'] . ' ' . $m['color'] . ' ' . $m['anio'] . ' ' . $m['cliente_nombre'] . ' ' . $m['cliente_apellido'] . ' ' . $m['cliente_telefono'])); ?>">
                                        
                                        <!-- Patente -->
                                        <td>
                                            <span class="badge bg-dark badge-patente border border-secondary text-warning">
                                                <?php echo htmlspecialchars($m['patente']); ?>
                                            </span>
                                        </td>

                                        <!-- Marca y Modelo -->
                                        <td>
                                            <div class="fw-bold text-dark fs-6">
                                                <?php echo htmlspecialchars($m['marca'] . ' ' . $m['modelo']); ?>
                                            </div>
                                            <small class="text-muted">ID: #<?php echo $m['id_moto']; ?></small>
                                        </td>

                                        <!-- Año y Color -->
                                        <td>
                                            <span class="d-block text-dark">
                                                <strong>Año:</strong> <?php echo !empty($m['anio']) ? $m['anio'] : '<span class="text-muted">-</span>'; ?>
                                            </span>
                                            <small class="text-muted">
                                                <strong>Color:</strong> <?php echo !empty($m['color']) ? htmlspecialchars($m['color']) : '<span class="text-muted">No especificado</span>'; ?>
                                            </small>
                                        </td>

                                        <!-- Kilometraje -->
                                        <td>
                                            <span class="badge bg-light text-dark border">
                                                <i class="bi bi-speedometer2 me-1"></i><?php echo number_format($m['kilometraje'] ?? 0, 0, ',', '.'); ?> km
                                            </span>
                                        </td>

                                        <!-- Dueño / Contacto -->
                                        <td>
                                            <div class="fw-bold text-primary">
                                                <i class="bi bi-person-fill me-1"></i>
                                                <?php echo htmlspecialchars($m['cliente_apellido'] . ', ' . $m['cliente_nombre']); ?>
                                            </div>
                                            <small class="text-muted d-block">
                                                <i class="bi bi-telephone me-1"></i><?php echo htmlspecialchars($m['cliente_telefono']); ?>
                                            </small>
                                        </td>

                                        <!-- Historial en Taller -->
                                        <td>
                                            <span class="badge <?php echo $badgeClass; ?> p-1.5 px-2">
                                                <?php 
                                                    if ($estado === 'Terminado') echo '🟢 Lista para entrega';
                                                    elseif ($estado === 'Entregado') echo '⚪ Entregada';
                                                    elseif ($estado === 'En Reparacion') echo '🔵 En Reparación';
                                                    elseif ($estado === 'En Espera') echo '🟡 En Espera';
                                                    else echo '⚪ Sin trabajos';
                                                ?>
                                            </span>
                                            <small class="d-block text-muted mt-1">
                                                <?php echo $m['total_trabajos']; ?> trabajo(s) en total
                                            </small>
                                        </td>

                                        <!-- Acciones ABM Moto -->
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm">
                                                <!-- Botón Iniciar Nuevo Trabajo para esta moto -->
                                                <a href="nueva_moto.php?id_moto=<?php echo $m['id_moto']; ?>" class="btn btn-outline-primary" title="Abrir orden de trabajo en taller">
                                                    <i class="bi bi-tools"></i> Trabajo
                                                </a>
                                                <!-- Botón Editar Vehículo -->
                                                <a href="editar_moto.php?id=<?php echo $m['id_moto']; ?>" class="btn btn-outline-warning" title="Editar datos del vehículo">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <!-- Botón Eliminar Vehículo -->
                                                <a href="eliminar_moto.php?id=<?php echo $m['id_moto']; ?>" class="btn btn-outline-danger" 
                                                   onclick="return confirm('¿Seguro que deseas eliminar el registro de esta moto del padrón?');" title="Eliminar moto">
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

    <!-- Script de Búsqueda en Tiempo Real -->
    <script>
        function filtrarMotos() {
            const input = document.getElementById('buscadorMotos').value.toLowerCase();
            const filas = document.querySelectorAll('.fila-moto');
            let contador = 0;

            filas.forEach(fila => {
                const texto = fila.getAttribute('data-search') || '';
                if (texto.includes(input)) {
                    fila.style.display = '';
                    contador++;
                } else {
                    fila.style.display = 'none';
                }
            });

            const contadorEl = document.getElementById('contadorFiltradas');
            if (contadorEl) contadorEl.innerText = contador + ' Motos';
        }
    </script>

    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
