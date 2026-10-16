<?php
// ========================================================
// ARCHIVO: motos.php
// OBJETIVO: Tablero principal de motos en reparación en el taller
// LENGUAJES: PHP + HTML5 + Bootstrap 5 + JavaScript
// ========================================================

require_once 'conexion.php';

// Consultamos todas las motos registradas junto a sus clientes
$sqlMotos = "SELECT m.*, c.nombre AS cliente_nombre, c.apellido AS cliente_apellido, c.telefono AS cliente_telefono, c.direccion AS cliente_direccion
             FROM motos m 
             INNER JOIN clientes c ON m.id_cliente = c.id_cliente 
             ORDER BY 
                CASE 
                    WHEN m.estado = 'Terminado' THEN 1
                    WHEN m.estado = 'En Reparacion' THEN 2
                    WHEN m.estado = 'En Espera' THEN 3
                    ELSE 4
                END,
                m.id_moto DESC";

$stmtMotos = $pdo->query($sqlMotos);
$motos = $stmtMotos->fetchAll();

// Conteo por estado
$countEspera = 0;
$countReparacion = 0;
$countTerminado = 0;
$countEntregado = 0;

foreach ($motos as $m) {
    if ($m['estado'] === 'En Espera' || empty($m['estado'])) $countEspera++;
    if ($m['estado'] === 'En Reparacion') $countReparacion++;
    if ($m['estado'] === 'Terminado') $countTerminado++;
    if ($m['estado'] === 'Entregado') $countEntregado++;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Motos en Taller - Panel de Control</title>
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
        
        <!-- Encabezado y Estadísticas Rápidas -->
        <div class="row align-items-center mb-4 g-3">
            <div class="col-12">
                <h3 class="fw-bold mb-1 text-dark">
                    <i class="bi bi-speedometer2 me-2 text-primary"></i>Motos en Taller
                </h3>
                <p class="text-muted mb-0">Control de reparaciones, diagnósticos y entregas pendientes</p>
            </div>
        </div>

        <!-- Tarjetas de Resumen Rápido -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm border-start border-4 border-warning bg-white h-100">
                    <div class="card-body p-3">
                        <span class="text-muted small fw-semibold text-uppercase">🟡 En Espera</span>
                        <h4 class="fw-bold mb-0 text-dark"><?php echo $countEspera; ?></h4>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm border-start border-4 border-primary bg-white h-100">
                    <div class="card-body p-3">
                        <span class="text-muted small fw-semibold text-uppercase">🔵 En Reparación</span>
                        <h4 class="fw-bold mb-0 text-dark"><?php echo $countReparacion; ?></h4>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm border-start border-4 border-success bg-white h-100">
                    <div class="card-body p-3">
                        <span class="text-muted small fw-semibold text-uppercase">🟢 Listas para Entrega</span>
                        <h4 class="fw-bold mb-0 text-success"><?php echo $countTerminado; ?></h4>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm border-start border-4 border-dark bg-white h-100">
                    <div class="card-body p-3">
                        <span class="text-muted small fw-semibold text-uppercase">📦 Total en Taller</span>
                        <h4 class="fw-bold mb-0 text-dark"><?php echo count($motos); ?></h4>
                    </div>
                </div>
            </div>
        </div>

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
                                   placeholder="Buscar por patente, dueño, teléfono, marca o arreglo..." onkeyup="filtrarMotos()">
                        </div>
                    </div>
                    <!-- Botones de Filtro por Estado -->
                    <div class="col-md-6 text-md-end">
                        <div class="btn-group w-100 w-md-auto" role="group">
                            <button type="button" class="btn btn-outline-dark btn-sm filter-btn active" onclick="filtrarPorEstado('TODOS', this)">
                                Todas (<?php echo count($motos); ?>)
                            </button>
                            <button type="button" class="btn btn-outline-warning btn-sm filter-btn text-dark" onclick="filtrarPorEstado('En Espera', this)">
                                🟡 En Espera (<?php echo $countEspera; ?>)
                            </button>
                            <button type="button" class="btn btn-outline-primary btn-sm filter-btn" onclick="filtrarPorEstado('En Reparacion', this)">
                                🔵 En Reparación (<?php echo $countReparacion; ?>)
                            </button>
                            <button type="button" class="btn btn-outline-success btn-sm filter-btn" onclick="filtrarPorEstado('Terminado', this)">
                                🟢 Listas (<?php echo $countTerminado; ?>)
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm filter-btn" onclick="filtrarPorEstado('Entregado', this)">
                                ⚪ Entregadas (<?php echo $countEntregado; ?>)
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- TABLA DE MOTOS EN REPARACIÓN -->
        <div class="card shadow-sm border-0">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center py-3">
                <h5 class="mb-0 fs-6 fw-bold">
                    <i class="bi bi-tools me-2 text-warning"></i>Motos Actualmente en el Taller
                </h5>
                <span class="badge bg-secondary" id="contadorFiltradas"><?php echo count($motos); ?> Registros</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="tablaMotos">
                        <thead class="table-dark">
                            <tr>
                                <th>Patente / Vehículo</th>
                                <th>Dueño & Contacto</th>
                                <th>Falla / Arreglo por Hacer</th>
                                <th>Arreglo Realizado / Repuestos</th>
                                <th>Fechas</th>
                                <th>Estado</th>
                                <th class="text-center">Acciones (ABM)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($motos)): ?>
                                <tr id="filaVacia">
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <i class="bi bi-check-circle fs-1 text-success d-block mb-2"></i>
                                        <strong>¡No hay motos en reparación en este momento!</strong>
                                        <p class="small text-muted mb-3">El taller está al día. Hacé clic abajo para registrar un nuevo ingreso.</p>
                                        <a href="nueva_moto.php" class="btn btn-sm btn-primary">
                                            <i class="bi bi-plus-circle me-1"></i> Ingresar Nueva Moto
                                        </a>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($motos as $m): 
                                    $estado = $m['estado'] ?? 'En Espera';
                                    $badgeClass = 'bg-secondary';
                                    $badgeIcon = 'bi-clock';
                                    if ($estado === 'En Espera') { $badgeClass = 'bg-warning text-dark'; $badgeIcon = 'bi-hourglass-split'; }
                                    if ($estado === 'En Reparacion') { $badgeClass = 'bg-primary text-white'; $badgeIcon = 'bi-gear-fill'; }
                                    if ($estado === 'Terminado') { $badgeClass = 'bg-success text-white'; $badgeIcon = 'bi-check-circle-fill'; }
                                    if ($estado === 'Entregado') { $badgeClass = 'bg-secondary text-white'; $badgeIcon = 'bi-check2-all'; }
                                ?>
                                    <tr class="fila-moto" data-estado="<?php echo htmlspecialchars($estado); ?>"
                                        data-search="<?php echo strtolower(htmlspecialchars($m['patente'] . ' ' . $m['marca'] . ' ' . $m['modelo'] . ' ' . $m['cliente_nombre'] . ' ' . $m['cliente_apellido'] . ' ' . $m['cliente_telefono'] . ' ' . ($m['falla_reportada'] ?? '') . ' ' . ($m['arreglo_realizado'] ?? ''))); ?>">
                                        
                                        <!-- Patente y Vehículo -->
                                        <td>
                                            <span class="badge bg-dark badge-patente border border-secondary text-warning">
                                                <?php echo htmlspecialchars($m['patente']); ?>
                                            </span>
                                            <div class="fw-bold mt-1 text-dark">
                                                <?php echo htmlspecialchars($m['marca'] . ' ' . $m['modelo']); ?>
                                            </div>
                                            <small class="text-muted">
                                                <?php if (!empty($m['color'])) echo 'Color: ' . htmlspecialchars($m['color']) . ' | '; ?>
                                                <?php echo number_format($m['kilometraje'] ?? 0, 0, ',', '.') . ' km'; ?>
                                            </small>
                                        </td>

                                        <!-- Dueño & Teléfono -->
                                        <td>
                                            <div class="fw-bold text-primary">
                                                <i class="bi bi-person-fill me-1"></i>
                                                <?php echo htmlspecialchars($m['cliente_nombre'] . ' ' . $m['cliente_apellido']); ?>
                                            </div>
                                            <small class="text-muted d-block mt-0.5">
                                                <i class="bi bi-telephone me-1"></i><?php echo htmlspecialchars($m['cliente_telefono']); ?>
                                            </small>
                                            <?php if (!empty($m['cliente_direccion'])): ?>
                                                <small class="text-muted d-block text-truncate" style="max-width: 160px;">
                                                    <i class="bi bi-geo-alt me-1"></i><?php echo htmlspecialchars($m['cliente_direccion']); ?>
                                                </small>
                                            <?php endif; ?>
                                        </td>

                                        <!-- Falla Reportada -->
                                        <td>
                                            <div class="p-2 bg-light rounded border border-1" style="max-width: 220px; font-size: 0.85rem;">
                                                <strong class="text-danger d-block mb-1"><i class="bi bi-exclamation-triangle me-1"></i>Falla / Motivo:</strong>
                                                <?php echo !empty($m['falla_reportada']) ? nl2br(htmlspecialchars($m['falla_reportada'])) : (!empty($m['observaciones']) ? nl2br(htmlspecialchars($m['observaciones'])) : '<span class="text-muted">Sin especificar</span>'); ?>
                                            </div>
                                        </td>

                                        <!-- Arreglo Realizado y Repuestos -->
                                        <td>
                                            <div style="max-width: 220px; font-size: 0.85rem;">
                                                <?php if (!empty($m['arreglo_realizado'])): ?>
                                                    <strong class="text-success d-block"><i class="bi bi-check2-circle me-1"></i>Trabajo:</strong>
                                                    <span><?php echo nl2br(htmlspecialchars($m['arreglo_realizado'])); ?></span>
                                                <?php else: ?>
                                                    <span class="text-muted italic">Pendiente de realización</span>
                                                <?php endif; ?>

                                                <?php if (!empty($m['repuestos'])): ?>
                                                    <div class="mt-1 small text-secondary">
                                                        <strong><i class="bi bi-box-seam me-1"></i>Repuestos:</strong> <?php echo htmlspecialchars($m['repuestos']); ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </td>

                                        <!-- Fechas -->
                                        <td>
                                            <small class="d-block text-muted">
                                                <i class="bi bi-box-arrow-in-right text-primary me-1"></i>
                                                <strong>Ingreso:</strong> 
                                                <?php echo !empty($m['fecha_ingreso']) ? date('d/m/Y', strtotime($m['fecha_ingreso'])) : '-'; ?>
                                            </small>
                                            <?php if (!empty($m['fecha_prometida'])): ?>
                                                <small class="d-block text-danger">
                                                    <i class="bi bi-calendar-event me-1"></i>
                                                    <strong>Pactada:</strong> <?php echo date('d/m/Y', strtotime($m['fecha_prometida'])); ?>
                                                </small>
                                            <?php endif; ?>
                                            <?php if (($m['precio_final'] ?? 0) > 0 || ($m['precio_estimado'] ?? 0) > 0): ?>
                                                <span class="badge bg-light text-dark border mt-1 font-monospace">
                                                    $ <?php echo number_format($m['precio_final'] > 0 ? $m['precio_final'] : $m['precio_estimado'], 2, ',', '.'); ?>
                                                </span>
                                            <?php endif; ?>
                                        </td>

                                        <!-- Estado Actual -->
                                        <td>
                                            <span class="badge <?php echo $badgeClass; ?> p-2 d-inline-flex align-items-center gap-1">
                                                <i class="bi <?php echo $badgeIcon; ?>"></i>
                                                <?php echo htmlspecialchars($estado); ?>
                                            </span>
                                        </td>

                                        <!-- Acciones ABM -->
                                        <td class="text-center">
                                            <div class="btn-group-vertical btn-group-sm">
                                                <!-- Botón Agregar otra moto a este dueño -->
                                                <a href="nueva_moto.php?cliente_id=<?php echo $m['id_cliente']; ?>&nueva_moto=1" class="btn btn-success fw-semibold" title="Agregar otra moto a este cliente">
                                                    <i class="bi bi-plus-circle me-1"></i> + Moto
                                                </a>
                                                <!-- Botón Editar / Ficha -->
                                                <a href="editar_moto.php?id=<?php echo $m['id_moto']; ?>" class="btn btn-warning fw-semibold" title="Editar / Cambiar Estado">
                                                    <i class="bi bi-pencil-square me-1"></i> Editar Ficha
                                                </a>
                                                <!-- Botón Eliminar -->
                                                <a href="eliminar_moto.php?id=<?php echo $m['id_moto']; ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('¿Seguro que deseas eliminar el registro de esta moto?');" title="Eliminar">
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

        function filtrarMotos() {
            const input = document.getElementById('buscador').value.toLowerCase();
            const filas = document.querySelectorAll('.fila-moto');
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

            document.getElementById('contadorFiltradas').innerText = contador + ' Registros';
        }

        function filtrarPorEstado(estado, boton) {
            estadoFiltroActual = estado;
            
            // Actualizar clase activa en botones
            document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
            boton.classList.add('active');

            filtrarMotos();
        }
    </script>

    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
