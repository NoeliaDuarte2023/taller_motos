<?php
// ========================================================
// ARCHIVO: historial_moto.php
// OBJETIVO: Muestra todo el historial de una moto (todos sus trabajos)
// Se abre al hacer clic en la patente.
// ========================================================

require_once 'conexion.php';

$id = filter_var($_GET['id'] ?? '', FILTER_VALIDATE_INT);
if (!$id || $id < 1) {
    header('Location: motos.php');
    exit;
}

$stmt = $pdo->prepare("SELECT m.*, c.id_cliente, c.nombre AS cliente_nombre, c.apellido AS cliente_apellido,
                              c.telefono AS cliente_telefono, c.direccion AS cliente_direccion
                       FROM motos m
                       INNER JOIN clientes c ON m.id_cliente = c.id_cliente
                       WHERE m.id_moto = :id");
$stmt->execute([':id' => $id]);
$moto = $stmt->fetch();
if (!$moto) {
    header('Location: motos.php');
    exit;
}

$stmtT = $pdo->prepare("SELECT * FROM trabajos WHERE id_moto = :id ORDER BY fecha_ingreso DESC, id_trabajo DESC");
$stmtT->execute([':id' => $id]);
$trabajos = $stmtT->fetchAll();

$totalGastado = 0;
foreach ($trabajos as $t) {
    if ($t['estado'] === 'Entregado') $totalGastado += (float)$t['precio_final'];
}

function h($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function fmt($f, $conHora = false) {
    if (empty($f)) return '—';
    return date($conHora ? 'd/m/Y H:i' : 'd/m/Y', strtotime($f));
}
function colorEstado($e) {
    switch ($e) {
        case 'En Espera':     return 'secondary';
        case 'En Reparacion': return 'warning text-dark';
        case 'Terminado':     return 'info text-dark';
        case 'Entregado':     return 'success';
        default:              return 'light text-dark';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de <?php echo h($moto['patente']); ?> - Taller de Motos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .badge-patente { font-family: monospace; letter-spacing: 1px; font-size: 1.1rem; padding: 6px 12px; }
    </style>
</head>
<body class="bg-light">

    <?php include 'navbar.php'; ?>

    <div class="container py-4">

        <a href="motos.php" class="btn btn-outline-secondary btn-sm mb-3">
            <i class="bi bi-arrow-left me-1"></i> Volver a Motos
        </a>

        <!-- Datos de la moto -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
                    <div>
                        <span class="badge bg-dark badge-patente border border-secondary text-warning"><?php echo h($moto['patente']); ?></span>
                        <h4 class="fw-bold mt-2 mb-1"><?php echo h($moto['marca'] . ' ' . $moto['modelo']); ?></h4>
                        <div class="text-muted">
                            Año <?php echo h($moto['anio']); ?> · Color <?php echo h($moto['color']); ?> ·
                            <?php echo number_format((int)$moto['kilometraje'], 0, ',', '.'); ?> km
                        </div>
                    </div>
                    <div class="text-md-end">
                        <div class="fw-bold"><i class="bi bi-person-fill me-1"></i><?php echo h($moto['cliente_nombre'] . ' ' . $moto['cliente_apellido']); ?></div>
                        <div class="text-muted small"><i class="bi bi-telephone me-1"></i><?php echo h($moto['cliente_telefono']); ?></div>
                        <div class="text-muted small"><i class="bi bi-geo-alt me-1"></i><?php echo h($moto['cliente_direccion']); ?></div>
                    </div>
                </div>
                <hr>
                <div class="row text-center">
                    <div class="col-6">
                        <div class="fs-4 fw-bold"><?php echo count($trabajos); ?></div>
                        <div class="text-muted small">Trabajos en total</div>
                    </div>
                    <div class="col-6">
                        <div class="fs-4 fw-bold">$<?php echo number_format($totalGastado, 2, ',', '.'); ?></div>
                        <div class="text-muted small">Total de trabajos entregados</div>
                    </div>
                </div>
            </div>
        </div>

        <h5 class="fw-bold mb-3"><i class="bi bi-clock-history me-2"></i>Historial de trabajos</h5>

        <?php if (empty($trabajos)): ?>
            <div class="alert alert-info">Esta moto todavía no tiene trabajos registrados.</div>
        <?php endif; ?>

        <?php foreach ($trabajos as $t): ?>
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <span class="fw-bold">Trabajo #<?php echo (int)$t['id_trabajo']; ?> · Ingreso: <?php echo fmt($t['fecha_ingreso'], true); ?></span>
                    <span class="badge bg-<?php echo colorEstado($t['estado']); ?>"><?php echo h($t['estado']); ?></span>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="text-muted small">Falla reportada</div>
                            <div><?php echo nl2br(h($t['falla_reportada'])); ?></div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small">Arreglo realizado</div>
                            <div><?php echo !empty($t['arreglo_realizado']) ? nl2br(h($t['arreglo_realizado'])) : '—'; ?></div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small">Repuestos</div>
                            <div><?php echo !empty($t['repuestos']) ? nl2br(h($t['repuestos'])) : '—'; ?></div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small">Diagnóstico</div>
                            <div><?php echo !empty($t['diagnostico']) ? nl2br(h($t['diagnostico'])) : '—'; ?></div>
                        </div>
                    </div>
                    <hr>
                    <div class="row small">
                        <div class="col-6 col-md-3"><span class="text-muted">Prometida:</span> <?php echo fmt($t['fecha_prometida']); ?></div>
                        <div class="col-6 col-md-3"><span class="text-muted">Entregada:</span> <?php echo fmt($t['fecha_entrega'], true); ?></div>
                        <div class="col-6 col-md-3"><span class="text-muted">Estimado:</span> $<?php echo number_format((float)$t['precio_estimado'], 2, ',', '.'); ?></div>
                        <div class="col-6 col-md-3"><span class="text-muted">Final:</span> $<?php echo number_format((float)$t['precio_final'], 2, ',', '.'); ?></div>
                    </div>
                    <div class="mt-2 text-end">
                        <a href="editar_trabajo.php?id=<?php echo (int)$t['id_trabajo']; ?>" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-pencil me-1"></i>Editar trabajo
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
