<?php
// ========================================================
// ARCHIVO: entregadas.php
// OBJETIVO: Grilla visual de motos entregadas (Historial del Taller)
// LENGUAJES: PHP + HTML5 + Bootstrap 5 + JavaScript
// ========================================================

require_once 'conexion.php';

// Consultar todas las motos que ya fueron entregadas
$sqlEntregadas = "SELECT m.*, c.nombre AS cliente_nombre, c.apellido AS cliente_apellido, c.telefono AS cliente_telefono, c.direccion AS cliente_direccion
                  FROM motos m 
                  INNER JOIN clientes c ON m.id_cliente = c.id_cliente 
                  WHERE m.estado = 'Entregado'
                  ORDER BY COALESCE(m.fecha_entrega, m.fecha_ingreso) DESC, m.id_moto DESC";

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
    <title>Motos Entregadas - Historial del Taller</title>
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
                    <i class="bi bi-check2-circle me-2 text-success"></i>Historial de Motos Entregadas
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
                    <i class="bi bi-archive fs-1 text-muted d-block mb-3"></i>
                    <h5 class="fw-bold text-dark">Aún no hay motos en el historial de entregas</h5>
                    <p class="text-muted small">Cuando una moto esté lista en el taller, cambiale el estado a <strong>"Entregado"</strong> para archivarla acá.</p>
                    <a href="motos.php" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-arrow-left me-1"></i> Ir al Tablero de Motos en Taller
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="row g-4" id="grillaEntregadas">
                <?php foreach ($entregadas as $ent): 
                    $montoFinal = floatval($ent['precio_final'] > 0 ? $ent['precio_final'] : $ent['precio_estimado']);
                    $telLimpio = preg_replace('/[^0-9]/', '', $ent['cliente_telefono']);
                ?>
                    <div class="col-md-6 col-lg-4 item-entregada"
                         data-search="<?php echo strtolower(htmlspecialchars($ent['patente'] . ' ' . $ent['marca'] . ' ' . $ent['modelo'] . ' ' . $ent['cliente_nombre'] . ' ' . $ent['cliente_apellido'] . ' ' . $ent['cliente_telefono'] . ' ' . ($ent['falla_reportada'] ?? '') . ' ' . ($ent['arreglo_realizado'] ?? '') . ' ' . ($ent['repuestos'] ?? ''))); ?>">
                        
                        <div class="card h-100 shadow-sm border-0 card-entrega bg-white">
                            
                            <!-- Header de la tarjeta -->
                            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center py-2.5 px-3">
                                <span class="badge bg-black border border-secondary badge-patente text-warning">
                                    <?php echo htmlspecialchars($ent['patente']); ?>
                                </span>
                                <span class="badge bg-success d-flex align-items-center gap-1 text-uppercase" style="font-size: 11px;">
                                    <i class="bi bi-check-circle-fill"></i> Entregada
                                </span>
                            </div>

                            <div class="card-body p-3.5 d-flex flex-col justify-content-between">
                                <div>
                                    <!-- Marca y Modelo -->
                                    <h5 class="fw-bold text-dark mb-1">
                                        <?php echo htmlspecialchars($ent['marca'] . ' ' . $ent['modelo']); ?>
                                    </h5>
                                    <p class="text-muted small mb-2">
                                        <?php if (!empty($ent['color'])) echo 'Color: ' . htmlspecialchars($ent['color']) . ' | '; ?>
                                        <?php if (!empty($ent['anio'])) echo 'Año: ' . htmlspecialchars($ent['anio']) . ' | '; ?>
                                        <?php echo number_format($ent['kilometraje'] ?? 0, 0, ',', '.') . ' km'; ?>
                                    </p>

                                    <!-- Dueño -->
                                    <div class="p-2 bg-light rounded-3 mb-3 border">
                                        <div class="fw-bold text-primary small">
                                            <i class="bi bi-person-fill me-1"></i>
                                            <?php echo htmlspecialchars($ent['cliente_nombre'] . ' ' . $ent['cliente_apellido']); ?>
                                        </div>
                                        <small class="text-muted d-block mt-0.5" style="font-size: 11px;">
                                            <i class="bi bi-telephone me-1"></i><?php echo htmlspecialchars($ent['cliente_telefono']); ?>
                                            <?php if (!empty($ent['cliente_direccion'])) echo ' • ' . htmlspecialchars($ent['cliente_direccion']); ?>
                                        </small>
                                    </div>

                                    <!-- Detalle del Trabajo Realizado -->
                                    <div class="mb-3" style="font-size: 12.5px;">
                                        <?php if (!empty($ent['arreglo_realizado'])): ?>
                                            <div class="text-dark mb-1">
                                                <strong class="text-success"><i class="bi bi-wrench-adjustable me-1"></i>Trabajo Realizado:</strong>
                                                <p class="mb-1 text-secondary ps-3"><?php echo nl2br(htmlspecialchars($ent['arreglo_realizado'])); ?></p>
                                            </div>
                                        <?php endif; ?>

                                        <?php if (!empty($ent['repuestos'])): ?>
                                            <div class="text-dark mb-1">
                                                <strong class="text-primary"><i class="bi bi-box-seam me-1"></i>Repuestos:</strong>
                                                <span class="text-secondary"><?php echo htmlspecialchars($ent['repuestos']); ?></span>
                                            </div>
                                        <?php endif; ?>

                                        <?php if (!empty($ent['falla_reportada'])): ?>
                                            <small class="text-muted d-block mt-1">
                                                <em>Ingresó por: <?php echo htmlspecialchars($ent['falla_reportada']); ?></em>
                                            </small>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div>
                                    <!-- Fechas de Ingreso y Retiro -->
                                    <div class="border-top pt-2.5 mb-3 bg-light p-2 rounded-2" style="font-size: 11.5px;">
                                        <div class="d-flex justify-content-between text-muted mb-1">
                                            <span><i class="bi bi-box-arrow-in-right text-primary me-1"></i>Ingreso al Taller:</span>
                                            <strong><?php echo !empty($ent['fecha_ingreso']) ? date('d/m/Y H:i', strtotime($ent['fecha_ingreso'])) : '-'; ?></strong>
                                        </div>
                                        <div class="d-flex justify-content-between text-success">
                                            <span><i class="bi bi-check2-circle me-1"></i>Fecha de Retiro:</span>
                                            <strong><?php echo !empty($ent['fecha_entrega']) ? date('d/m/Y H:i', strtotime($ent['fecha_entrega'])) : (!empty($ent['fecha_ingreso']) ? date('d/m/Y', strtotime($ent['fecha_ingreso'])) : 'Entregada'); ?></strong>
                                        </div>
                                    </div>

                                    <!-- Total y Botones -->
                                    <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                                        <div>
                                            <small class="text-muted d-block text-uppercase" style="font-size: 10px;">Total Cobrado</small>
                                            <span class="fs-5 fw-bold text-dark font-monospace">$ <?php echo number_format($montoFinal, 2, ',', '.'); ?></span>
                                        </div>
                                        <div class="btn-group btn-group-sm">
                                            <a href="editar_moto.php?id=<?php echo $ent['id_moto']; ?>" class="btn btn-outline-warning" title="Editar Ficha">
                                                <i class="bi bi-pencil-square"></i> Ver Ficha
                                            </a>
                                            <a href="eliminar_moto.php?id=<?php echo $ent['id_moto']; ?>" class="btn btn-outline-danger" 
                                               onclick="return confirm('¿Eliminar esta moto del historial?');" title="Eliminar">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>

    <!-- Script de Búsqueda Rápida en Entregadas -->
    <script>
        function filtrarEntregadas() {
            const query = document.getElementById('buscadorEntregadas').value.toLowerCase();
            const cards = document.querySelectorAll('.item-entregada');

            cards.forEach(card => {
                const searchData = card.getAttribute('data-search') || '';
                if (searchData.includes(query)) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        }
    </script>

    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
