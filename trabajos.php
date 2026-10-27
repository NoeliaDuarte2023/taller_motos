<?php
// ========================================================
// ARCHIVO: trabajos.php
// OBJETIVO: Panel principal de órdenes de trabajo en taller (1FN y 2FN)
// Sin dashboard (según requerimiento de cátedra), con botón externo
// LENGUAJES: PHP + HTML5 + Bootstrap 5 + JavaScript
// ========================================================

require_once 'conexion.php';

// Consultamos los trabajos activos en el taller vinculando con moto y cliente
$sqlTrabajos = "SELECT t.*, 
                       m.patente, m.marca, m.modelo, m.color, m.kilometraje, m.anio,
                       c.id_cliente, c.nombre AS cliente_nombre, c.apellido AS cliente_apellido, 
                       c.telefono AS cliente_telefono, c.direccion AS cliente_direccion
                FROM trabajos t
                INNER JOIN motos m ON t.id_moto = m.id_moto
                INNER JOIN clientes c ON m.id_cliente = c.id_cliente
                WHERE t.estado != 'Entregado'
                ORDER BY 
                    CASE 
                        WHEN t.estado = 'Terminado' THEN 1
                        WHEN t.estado = 'En Reparacion' THEN 2
                        WHEN t.estado = 'En Espera' THEN 3
                        ELSE 4
                    END,
                    t.id_trabajo DESC";

$stmtTrabajos = $pdo->query($sqlTrabajos);
$trabajos = $stmtTrabajos->fetchAll();

// Conteo por estado de los activos
$countEspera = 0;
$countReparacion = 0;
$countTerminado = 0;

foreach ($trabajos as $t) {
    if ($t['estado'] === 'En Espera' || empty($t['estado'])) $countEspera++;
    if ($t['estado'] === 'En Reparacion') $countReparacion++;
    if ($t['estado'] === 'Terminado') $countTerminado++;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trabajos en Taller - Panel de Control</title>
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
        .filter-btn.active {
            font-weight: bold;
            box-shadow: 0 2px 4px rgba(0,0,0,0.15);
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
                    <i class="bi bi-tools me-2 text-warning"></i>Trabajos en Taller
                </h3>
                <p class="text-muted mb-0">Control de órdenes de reparación, diagnósticos y entregas pendientes</p>
            </div>
            <div>
                <!-- Botón de alta fuera de la grilla -->
                <a href="nueva_moto.php" class="btn btn-primary fw-bold shadow-sm px-3 py-2">
                    <i class="bi bi-plus-circle-fill me-1"></i> + Nuevo Ingreso al Taller
                </a>
            </div>
        </div>

        <?php if (isset($_GET['eliminado'])): ?>
            <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                <strong>¡Trabajo eliminado correctamente!</strong> La moto y el cliente se conservaron en la base de datos (sin anomalías de eliminación).
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['actualizado'])): ?>
            <div class="alert alert-info alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-info-circle-fill me-2"></i>
                Trabajo actualizado con éxito.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- NOTA DE CÁTEDRA: Se retiró el bloque de dashboard cards según lo solicitado -->

        <!-- Barra de Búsqueda en Tiempo Real y Filtros -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body p-3">
                <div class="row g-3 align-items-center">
                    <!-- Input de Búsqueda -->
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 text-muted">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" id="buscador" class="form-control border-start-0 ps-0" 
                                   placeholder="Buscar por patente, dueño, teléfono, marca o arreglo..." onkeyup="filtrarTrabajos()">
                        </div>
                    </div>
                    <!-- Botones de Filtro por Estado -->
                    <div class="col-md-6 text-md-end">
                        <div class="btn-group w-100 w-md-auto" role="group">
                            <button type="button" class="btn btn-outline-dark btn-sm filter-btn active" id="btnFiltroTodos" onclick="filtrarPorEstado('TODOS', this)">
                                Todos (<?php echo count($trabajos); ?>)
                            </button>
                            <button type="button" class="btn btn-outline-warning btn-sm filter-btn text-dark" id="btnFiltroEspera" onclick="filtrarPorEstado('En Espera', this)">
                                🟡 En Espera (<?php echo $countEspera; ?>)
                            </button>
                            <button type="button" class="btn btn-outline-primary btn-sm filter-btn" id="btnFiltroReparacion" onclick="filtrarPorEstado('En Reparacion', this)">
                                🔵 En Reparación (<?php echo $countReparacion; ?>)
                            </button>
                            <button type="button" class="btn btn-outline-success btn-sm filter-btn" id="btnFiltroListas" onclick="filtrarPorEstado('Terminado', this)">
                                🟢 Listas para Entrega (<?php echo $countTerminado; ?>)
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- TABLA DE TRABAJOS EN REPARACIÓN -->
        <div class="card shadow-sm border-0">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center py-3">
                <h5 class="mb-0 fs-6 fw-bold">
                    <i class="bi bi-card-checklist me-2 text-warning"></i>Órdenes de Trabajo Activas
                </h5>
                <span class="badge bg-secondary" id="contadorFiltradas"><?php echo count($trabajos); ?> Registros</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="tablaTrabajos">
                        <thead class="table-dark">
                            <tr>
                                <th>Patente / Vehículo</th>
                                <th>Dueño & Contacto</th>
                                <th>Falla / Motivo por Hacer</th>
                                <th>Arreglo Realizado / Repuestos</th>
                                <th>Fechas & Presupuesto</th>
                                <th>Estado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($trabajos)): ?>
                                <tr id="filaVacia">
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <i class="bi bi-check-circle fs-1 text-success d-block mb-2"></i>
                                        <strong>¡No hay trabajos pendientes en el taller!</strong>
                                        <p class="small text-muted mb-3">El taller está al día. Hacé clic abajo para registrar un nuevo ingreso.</p>
                                        <a href="nueva_moto.php" class="btn btn-sm btn-primary">
                                            <i class="bi bi-plus-circle me-1"></i> Ingresar Nuevo Trabajo
                                        </a>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($trabajos as $t): 
                                    $estado = $t['estado'] ?? 'En Espera';
                                    $badgeClass = 'bg-secondary';
                                    $badgeIcon = 'bi-clock';
                                    if ($estado === 'En Espera') { $badgeClass = 'bg-warning text-dark'; $badgeIcon = 'bi-hourglass-split'; }
                                    if ($estado === 'En Reparacion') { $badgeClass = 'bg-primary text-white'; $badgeIcon = 'bi-gear-fill'; }
                                    if ($estado === 'Terminado') { $badgeClass = 'bg-success text-white'; $badgeIcon = 'bi-check-circle-fill'; }
                                    if ($estado === 'Entregado') { $badgeClass = 'bg-secondary text-white'; $badgeIcon = 'bi-check2-all'; }
                                ?>
                                    <tr class="fila-trabajo" data-estado="<?php echo htmlspecialchars($estado); ?>"
                                        data-search="<?php echo strtolower(htmlspecialchars($t['patente'] . ' ' . $t['marca'] . ' ' . $t['modelo'] . ' ' . $t['cliente_nombre'] . ' ' . $t['cliente_apellido'] . ' ' . $t['cliente_telefono'] . ' ' . ($t['falla_reportada'] ?? '') . ' ' . ($t['arreglo_realizado'] ?? ''))); ?>">
                                        
                                        <!-- Patente y Vehículo -->
                                        <td>
                                            <a href="historial_moto.php?id=<?php echo (int)$t['id_moto']; ?>" class="text-decoration-none" title="Ver historial de esta moto"><span class="badge bg-dark badge-patente border border-secondary text-warning"><?php echo htmlspecialchars($t['patente']); ?> <i class="bi bi-clock-history ms-1"></i></span></a>
                                            <div class="fw-bold mt-1 text-dark">
                                                <?php echo htmlspecialchars($t['marca'] . ' ' . $t['modelo']); ?>
                                            </div>
                                            <small class="text-muted">
                                                <?php if (!empty($t['color'])) echo 'Color: ' . htmlspecialchars($t['color']) . ' | '; ?>
                                                <?php echo number_format($t['kilometraje'] ?? 0, 0, ',', '.') . ' km'; ?>
                                            </small>
                                        </td>

                                        <!-- Dueño & Teléfono -->
                                        <td>
                                            <div class="fw-bold text-primary">
                                                <i class="bi bi-person-fill me-1"></i>
                                                <?php echo htmlspecialchars($t['cliente_nombre'] . ' ' . $t['cliente_apellido']); ?>
                                            </div>
                                            <small class="text-muted d-block mt-0.5">
                                                <i class="bi bi-telephone me-1"></i><?php echo htmlspecialchars($t['cliente_telefono']); ?>
                                            </small>
                                            <?php if (!empty($t['cliente_direccion'])): ?>
                                                <small class="text-muted d-block text-truncate" style="max-width: 160px;">
                                                    <i class="bi bi-geo-alt me-1"></i><?php echo htmlspecialchars($t['cliente_direccion']); ?>
                                                </small>
                                            <?php endif; ?>
                                        </td>

                                        <!-- Falla Reportada -->
                                        <td>
                                            <div class="p-2 bg-light rounded border border-1" style="max-width: 220px; font-size: 0.85rem;">
                                                <strong class="text-danger d-block mb-1"><i class="bi bi-exclamation-triangle me-1"></i>Falla / Motivo:</strong>
                                                <?php echo !empty($t['falla_reportada']) ? nl2br(htmlspecialchars($t['falla_reportada'])) : '<span class="text-muted">Sin especificar</span>'; ?>
                                            </div>
                                        </td>

                                        <!-- Arreglo Realizado y Repuestos -->
                                        <td>
                                            <div style="max-width: 220px; font-size: 0.85rem;">
                                                <?php if (!empty($t['arreglo_realizado'])): ?>
                                                    <strong class="text-success d-block"><i class="bi bi-check2-circle me-1"></i>Trabajo:</strong>
                                                    <span><?php echo nl2br(htmlspecialchars($t['arreglo_realizado'])); ?></span>
                                                <?php else: ?>
                                                    <span class="text-muted fst-italic">Pendiente de realización</span>
                                                <?php endif; ?>

                                                <?php if (!empty($t['repuestos'])): ?>
                                                    <div class="mt-1 small text-secondary">
                                                        <strong><i class="bi bi-box-seam me-1"></i>Repuestos:</strong> <?php echo htmlspecialchars($t['repuestos']); ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </td>

                                        <!-- Fechas & Importe -->
                                        <td>
                                            <small class="d-block text-muted">
                                                <i class="bi bi-box-arrow-in-right text-primary me-1"></i>
                                                <strong>Ingreso:</strong> 
                                                <?php echo !empty($t['fecha_ingreso']) ? date('d/m/Y', strtotime($t['fecha_ingreso'])) : '-'; ?>
                                            </small>
                                            <?php if (!empty($t['fecha_prometida'])): ?>
                                                <small class="d-block text-danger">
                                                    <i class="bi bi-calendar-event me-1"></i>
                                                    <strong>Pactada:</strong> <?php echo date('d/m/Y', strtotime($t['fecha_prometida'])); ?>
                                                </small>
                                            <?php endif; ?>
                                            <?php if (($t['precio_final'] ?? 0) > 0 || ($t['precio_estimado'] ?? 0) > 0): ?>
                                                <span class="badge bg-light text-dark border mt-1 font-monospace">
                                                    $ <?php echo number_format($t['precio_final'] > 0 ? $t['precio_final'] : $t['precio_estimado'], 2, ',', '.'); ?>
                                                </span>
                                            <?php endif; ?>
                                        </td>

                                        <!-- Estado Actual con Selector Rápido -->
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-sm <?php echo ($estado === 'Terminado') ? 'btn-success text-white' : (($estado === 'En Reparacion') ? 'btn-primary text-white' : 'btn-warning text-dark'); ?> dropdown-toggle fw-semibold py-1.5 px-2.5 shadow-sm d-inline-flex align-items-center gap-1" 
                                                        type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Hacé clic para cambiar de estado rápidamente">
                                                    <i class="bi <?php echo $badgeIcon; ?>"></i>
                                                    <?php 
                                                        if ($estado === 'Terminado') echo '🟢 Lista';
                                                        elseif ($estado === 'En Reparacion') echo '🔵 En Reparación';
                                                        elseif ($estado === 'En Espera') echo '🟡 En Espera';
                                                        else echo htmlspecialchars($estado);
                                                    ?>
                                                </button>
                                                <ul class="dropdown-menu shadow border-0">
                                                    <li><h6 class="dropdown-header small text-uppercase">Cambiar estado a:</h6></li>
                                                    <li>
                                                        <a class="dropdown-item py-1.5" href="cambiar_estado.php?id=<?php echo $t['id_trabajo']; ?>&estado=En Espera">
                                                            🟡 <strong>En Espera</strong>
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item py-1.5" href="cambiar_estado.php?id=<?php echo $t['id_trabajo']; ?>&estado=En Reparacion">
                                                            🔵 <strong>En Reparación</strong>
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item py-1.5 text-success" href="cambiar_estado.php?id=<?php echo $t['id_trabajo']; ?>&estado=Terminado">
                                                            🟢 <strong>Marcar como LISTA</strong>
                                                        </a>
                                                    </li>
                                                    <li><hr class="dropdown-divider my-1"></li>
                                                    <li>
                                                        <a class="dropdown-item py-1.5 text-primary fw-bold" href="cambiar_estado.php?id=<?php echo $t['id_trabajo']; ?>&estado=Entregado"
                                                           onclick="return confirm('¿Confirmás que la moto fue entregada al cliente? Pasará a la pestaña Entregadas.');">
                                                            ⚪ <strong>Marcar como ENTREGADA ➔</strong>
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>

                                        <!-- Acciones (ABM Trabajo) -->
                                        <td class="text-center">
                                            <div class="btn-group-vertical btn-group-sm">
                                                <!-- Botón Editar / Ficha de Trabajo -->
                                                <a href="editar_trabajo.php?id=<?php echo $t['id_trabajo']; ?>" class="btn btn-warning fw-semibold" title="Editar / Cambiar Estado">
                                                    <i class="bi bi-pencil-square me-1"></i> Ficha Trabajo
                                                </a>
                                                <!-- Botón Eliminar Trabajo (NO elimina la moto ni el cliente) -->
                                                <a href="eliminar_trabajo.php?id=<?php echo $t['id_trabajo']; ?>" class="btn btn-outline-danger btn-sm" 
                                                   onclick="return confirm('¿Seguro que deseas eliminar esta orden de trabajo? La moto y el cliente permanecerán guardados.');" title="Eliminar solo este trabajo">
                                                    <i class="bi bi-trash"></i> Eliminar
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

    <!-- Script de Búsqueda y Filtrado en Tiempo Real -->
    <script>
        let estadoFiltroActual = 'TODOS';

        function filtrarTrabajos() {
            const input = document.getElementById('buscador').value.toLowerCase();
            const filas = document.querySelectorAll('.fila-trabajo');
            let contador = 0;

            filas.forEach(fila => {
                const texto = fila.getAttribute('data-search') || '';
                const estado = fila.getAttribute('data-estado') || '';

                const coincideTexto = texto.includes(input);
                const coincideEstado = (estadoFiltroActual === 'TODOS') || (estado === estadoFiltroActual);

                if (coincideTexto && coincideEstado) {
                    fila.style.display = '';
                    contador++;
                } else {
                    fila.style.display = 'none';
                }
            });

            const contadorEl = document.getElementById('contadorFiltradas');
            if (contadorEl) contadorEl.innerText = contador + ' Registros';
        }

        function filtrarPorEstado(estado, boton) {
            estadoFiltroActual = estado;
            
            // Actualizar clase activa en botones
            document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
            if (boton) boton.classList.add('active');

            filtrarTrabajos();
        }

        window.addEventListener('DOMContentLoaded', () => {
            const urlParams = new URLSearchParams(window.location.search);
            const filtroParam = urlParams.get('filtro');
            if (filtroParam === 'Terminado') {
                const btn = document.getElementById('btnFiltroListas');
                if (btn) filtrarPorEstado('Terminado', btn);
            } else if (filtroParam === 'En Reparacion') {
                const btn = document.getElementById('btnFiltroReparacion');
                if (btn) filtrarPorEstado('En Reparacion', btn);
            } else if (filtroParam === 'En Espera') {
                const btn = document.getElementById('btnFiltroEspera');
                if (btn) filtrarPorEstado('En Espera', btn);
            }
        });
    </script>

    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
