<?php
// ========================================================
// ARCHIVO: entregadas.php
// OBJETIVO: Grilla visual de trabajos entregados (Historial del Taller en 1FN y 2FN)
// LENGUAJES: PHP + HTML5 + Bootstrap 5 + JavaScript
// ========================================================

require_once 'conexion.php';

// Consultar todas las órdenes de trabajo que ya fueron entregadas
$sqlEntregadas = "SELECT t.*, 
                         m.patente, m.marca, m.modelo, m.color, m.kilometraje, m.anio,
                         c.id_cliente, c.nombre AS cliente_nombre, c.apellido AS cliente_apellido, 
                         c.telefono AS cliente_telefono, c.direccion AS cliente_direccion
                  FROM trabajos t
                  INNER JOIN motos m ON t.id_moto = m.id_moto 
                  INNER JOIN clientes c ON m.id_cliente = c.id_cliente 
                  WHERE t.estado = 'Entregado'
                  ORDER BY COALESCE(t.fecha_entrega, t.fecha_ingreso) DESC, t.id_trabajo DESC";

$stmtEntregadas = $pdo->query($sqlEntregadas);
$entregadas = $stmtEntregadas->fetchAll();

// Total facturado histórico
$totalFacturado = 0;
foreach ($entregadas as $ent) {
    $totalFacturado += floatval($ent['precio_final'] > 0 ? $ent['precio_final'] : $ent['precio_estimado']);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Entregadas - Taller de Motos</title>
    <!-- Bootstrap 5 CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .badge-patente {
            font-family: monospace;
            letter-spacing: 1px;
            font-size: 0.95rem;
            padding: 5px 10px;
            border-radius: 6px;
        }
        .card-entrega {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            border-radius: 12px;
        }
        .card-entrega:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.1) !important;
        }
    </style>
</head>
<body class="bg-light">

    <!-- Barra de Navegación Común -->
    <?php include 'navbar.php'; ?>

    <div class="container mb-5">
        
        <!-- Encabezado y Métricas -->
        <div class="row align-items-center mb-4 g-3">
            <div class="col-md-7">
                <h3 class="fw-bold mb-1 text-dark">
                    <i class="bi bi-check2-circle me-2 text-success"></i>Historial de Trabajos Entregados
                </h3>
                <p class="text-muted mb-0">Registro y archivo de todos los vehículos reparados y retirados</p>
            </div>
            <div class="col-md-5 text-md-end">
                <div class="d-inline-flex gap-3 bg-white p-2 px-3 rounded-3 shadow-sm border">
                    <div class="text-start">
                        <small class="text-muted text-uppercase d-block" style="font-size: 11px;">Motos Entregadas</small>
                        <strong class="fs-5 text-success"><?php echo count($entregadas); ?></strong>
                    </div>
                    <div class="vr"></div>
                    <div class="text-start">
                        <small class="text-muted text-uppercase d-block" style="font-size: 11px;">Total Facturado</small>
                        <strong class="fs-5 text-dark">$ <?php echo number_format($totalFacturado, 2, ',', '.'); ?></strong>
                    </div>
                </div>
            </div>
        </div>

        <?php if (isset($_GET['entregada'])): ?>
            <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-check2-all me-2"></i>
                <strong>¡Servicio completado!</strong> La moto ha sido entregada al cliente y archivada en este historial.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Buscador Rápido -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body p-3">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 text-muted">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" id="buscadorEntregadas" class="form-control border-start-0 ps-0" 
                           placeholder="Buscar por patente, nombre de cliente, teléfono, marca o arreglo hecho..." onkeyup="filtrarEntregadas()">
                </div>
            </div>
        </div>

        <!-- GRILLA DE TARJETAS DE MOTOS ENTREGADAS -->
        <?php if (empty($entregadas)): ?>
            <div class="card shadow-sm border-0 text-center py-5">
                <div class="card-body">
                    <i class="bi bi-inbox fs-1 text-muted d-block mb-3"></i>
                    <h5 class="fw-bold text-dark">No hay registros de trabajos entregados aún</h5>
                    <p class="text-muted mb-3">Cuando cambies el estado de una moto a "Entregado", aparecerá registrada en esta sección.</p>
                    <a href="trabajos.php" class="btn btn-primary btn-sm">
                        <i class="bi bi-arrow-left me-1"></i> Ir a Trabajos en Taller
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="row g-4" id="grillaEntregadas">
                <?php foreach ($entregadas as $ent): 
                    $precio = ($ent['precio_final'] > 0) ? $ent['precio_final'] : $ent['precio_estimado'];
                    $textoBusqueda = strtolower($ent['patente'] . ' ' . $ent['marca'] . ' ' . $ent['modelo'] . ' ' . $ent['cliente_nombre'] . ' ' . $ent['cliente_apellido'] . ' ' . $ent['cliente_telefono'] . ' ' . ($ent['arreglo_realizado'] ?? '') . ' ' . ($ent['falla_reportada'] ?? ''));
                ?>
                    <div class="col-md-6 col-lg-4 item-entrega" data-search="<?php echo htmlspecialchars($textoBusqueda); ?>">
                        <div class="card h-100 shadow-sm border-0 card-entrega bg-white">
                            
                            <!-- Header de la tarjeta -->
                            <div class="card-header bg-white border-bottom pt-3 pb-2 d-flex justify-content-between align-items-start">
                                <div>
                                    <a href="historial_moto.php?id=<?php echo (int)$ent['id_moto']; ?>" class="text-decoration-none" title="Ver historial de esta moto"><span class="badge bg-dark badge-patente border border-secondary text-warning"><?php echo htmlspecialchars($ent['patente']); ?> <i class="bi bi-clock-history ms-1"></i></span></a>
                                    <h5 class="fw-bold mb-0 mt-2 text-dark">
                                        <?php echo htmlspecialchars($ent['marca'] . ' ' . $ent['modelo']); ?>
                                    </h5>
                                </div>
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1.5 fw-semibold">
                                    <i class="bi bi-check2-all me-1"></i> Entregada
                                </span>
                            </div>

                            <!-- Cuerpo de la tarjeta -->
                            <div class="card-body pb-2">
                                
                                <!-- Dueño -->
                                <div class="mb-3 p-2 bg-light rounded-2 border border-1">
                                    <small class="text-muted d-block" style="font-size: 11px;">CLIENTE</small>
                                    <strong class="text-primary d-block">
                                        <i class="bi bi-person-fill me-1"></i>
                                        <?php echo htmlspecialchars($ent['cliente_apellido'] . ', ' . $ent['cliente_nombre']); ?>
                                    </strong>
                                    <small class="text-muted">
                                        <i class="bi bi-telephone me-1"></i><?php echo htmlspecialchars($ent['cliente_telefono']); ?>
                                    </small>
                                </div>

                                <!-- Arreglo Realizado -->
                                <div class="mb-2">
                                    <small class="text-muted d-block fw-semibold mb-1" style="font-size: 11px;">TRABAJO REALIZADO:</small>
                                    <p class="mb-1 text-dark small" style="min-height: 40px;">
                                        <?php echo !empty($ent['arreglo_realizado']) ? nl2br(htmlspecialchars($ent['arreglo_realizado'])) : (!empty($ent['falla_reportada']) ? nl2br(htmlspecialchars($ent['falla_reportada'])) : '<span class="text-muted italic">Sin detalle informado</span>'); ?>
                                    </p>
                                </div>

                                <!-- Repuestos si los hubo -->
                                <?php if (!empty($ent['repuestos'])): ?>
                                    <div class="mb-2 small text-secondary">
                                        <strong><i class="bi bi-box-seam me-1"></i>Repuestos:</strong> <?php echo htmlspecialchars($ent['repuestos']); ?>
                                    </div>
                                <?php endif; ?>

                                <!-- Fechas -->
                                <div class="border-top pt-2 mt-2 d-flex justify-content-between text-muted" style="font-size: 12px;">
                                    <span>
                                        <i class="bi bi-calendar-check me-1 text-success"></i>
                                        Retiro: <?php echo !empty($ent['fecha_entrega']) ? date('d/m/Y', strtotime($ent['fecha_entrega'])) : date('d/m/Y', strtotime($ent['fecha_ingreso'])); ?>
                                    </span>
                                    <span>
                                        <i class="bi bi-box-arrow-in-right me-1 text-muted"></i>
                                        Ingresó: <?php echo !empty($ent['fecha_ingreso']) ? date('d/m/Y', strtotime($ent['fecha_ingreso'])) : '-'; ?>
                                    </span>
                                </div>

                            </div>

                            <!-- Footer de la tarjeta con importe y botón de reapertura/edición -->
                            <div class="card-footer bg-light border-top d-flex justify-content-between align-items-center py-2.5">
                                <div>
                                    <small class="text-muted d-block" style="font-size: 10px;">TOTAL COBRADO</small>
                                    <strong class="fs-5 text-dark font-monospace">$ <?php echo number_format($precio, 2, ',', '.'); ?></strong>
                                </div>
                                <div class="btn-group btn-group-sm">
                                    <a href="editar_trabajo.php?id=<?php echo $ent['id_trabajo']; ?>" class="btn btn-outline-secondary btn-sm" title="Ver / Editar ficha">
                                        <i class="bi bi-eye me-1"></i> Ver Ficha
                                    </a>
                                </div>
                            </div>

                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>

    <!-- Script de Búsqueda de Entregadas -->
    <script>
        function filtrarEntregadas() {
            const input = document.getElementById('buscadorEntregadas').value.toLowerCase();
            const tarjetas = document.querySelectorAll('.item-entrega');

            tarjetas.forEach(t => {
                const search = t.getAttribute('data-search') || '';
                if (search.includes(input)) {
                    t.style.display = '';
                } else {
                    t.style.display = 'none';
                }
            });
        }
    </script>

    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
