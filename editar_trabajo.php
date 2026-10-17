<?php
// ========================================================
// ARCHIVO: editar_trabajo.php
// OBJETIVO: Ficha técnica de la orden de trabajo, edición y cambio de estado
// LENGUAJES: PHP + HTML5 + Bootstrap 5 + JavaScript
// ========================================================

require_once 'conexion.php';

$id_trabajo = intval($_GET['id'] ?? 0);
if ($id_trabajo <= 0) {
    header('Location: trabajos.php');
    exit;
}

// Consultamos el trabajo con los datos de su moto y cliente
$stmt = $pdo->prepare("SELECT t.*, 
                              m.id_moto, m.patente, m.marca, m.modelo, m.color, m.kilometraje, m.anio,
                              c.id_cliente, c.nombre AS cliente_nombre, c.apellido AS cliente_apellido, 
                              c.telefono AS cliente_telefono, c.direccion AS cliente_direccion 
                       FROM trabajos t
                       INNER JOIN motos m ON t.id_moto = m.id_moto
                       INNER JOIN clientes c ON m.id_cliente = c.id_cliente 
                       WHERE t.id_trabajo = :id");
$stmt->execute([':id' => $id_trabajo]);
$trabajo = $stmt->fetch();

if (!$trabajo) {
    echo "<h3 style='color:red;'>Trabajo no encontrado.</h3><a href='trabajos.php'>Volver</a>";
    exit;
}

$estadoActual = $trabajo['estado'] ?? 'En Espera';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ficha de Trabajo #<?php echo $trabajo['id_trabajo']; ?> - Taller de Motos</title>
    <!-- Bootstrap 5 CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">

    <!-- Barra de Navegación Común -->
    <?php include 'navbar.php'; ?>

    <div class="container mb-5">
        <div class="row justify-content-center">
            <div class="col-lg-10 col-xl-9">
                
                <div class="card shadow border-0 rounded-3">
                    
                    <!-- Encabezado de la Ficha -->
                    <div class="card-header bg-dark text-white p-4">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                            <div>
                                <span class="badge bg-warning text-dark font-monospace fs-6 px-3 py-1.5 rounded-2">
                                    <?php echo htmlspecialchars($trabajo['patente']); ?>
                                </span>
                                <h4 class="mb-0 mt-2 fw-bold text-white">
                                    <?php echo htmlspecialchars($trabajo['marca'] . ' ' . $trabajo['modelo']); ?>
                                </h4>
                                <small class="text-secondary">Dueño: <strong><?php echo htmlspecialchars($trabajo['cliente_apellido'] . ', ' . $trabajo['cliente_nombre']); ?></strong> (Tel: <?php echo htmlspecialchars($trabajo['cliente_telefono']); ?>)</small>
                            </div>
                            <div>
                                <a href="trabajos.php" class="btn btn-outline-light">
                                    <i class="bi bi-arrow-left me-1"></i> Volver a Trabajos
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-4 p-md-5">
                        
                        <form action="actualizar_trabajo.php" method="POST">
                            <input type="hidden" name="id_trabajo" value="<?php echo $trabajo['id_trabajo']; ?>">

                            <!-- ============================================== -->
                            <!-- SECCIÓN 1: ESTADO Y CONTROL DE FECHAS -->
                            <!-- ============================================== -->
                            <div class="p-3.5 bg-light rounded-3 border mb-4">
                                <h5 class="fw-bold text-primary mb-3">
                                    <i class="bi bi-flag-fill me-1"></i> Estado del Trabajo y Fechas de Taller
                                </h5>

                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold text-dark">Estado del Servicio *</label>
                                        <select name="estado" id="selectEstado" class="form-select form-select-lg fw-bold" onchange="verificarEstado()">
                                            <option value="En Espera" <?php echo ($estadoActual === 'En Espera') ? 'selected' : ''; ?>>🟡 En Espera</option>
                                            <option value="En Reparacion" <?php echo ($estadoActual === 'En Reparacion') ? 'selected' : ''; ?>>🔵 En Reparación</option>
                                            <option value="Terminado" <?php echo ($estadoActual === 'Terminado') ? 'selected' : ''; ?>>🟢 Terminado (Listo para retirar)</option>
                                            <option value="Entregado" <?php echo ($estadoActual === 'Entregado') ? 'selected' : ''; ?>>⚪ Entregado (Retirada por el cliente)</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Fecha Prometida de Entrega</label>
                                        <input type="date" name="fecha_prometida" class="form-control" 
                                               value="<?php echo !empty($trabajo['fecha_prometida']) ? $trabajo['fecha_prometida'] : ''; ?>">
                                    </div>
                                    <div class="col-md-4" id="cajaFechaEntrega">
                                        <label class="form-label fw-semibold text-success">Fecha y Hora de Entrega</label>
                                        <input type="datetime-local" name="fecha_entrega" id="inputFechaEntrega" class="form-control" 
                                               value="<?php echo !empty($trabajo['fecha_entrega']) ? date('Y-m-d\TH:i', strtotime($trabajo['fecha_entrega'])) : ''; ?>">
                                    </div>
                                </div>
                            </div>

                            <!-- ============================================== -->
                            <!-- SECCIÓN 2: DIAGNÓSTICO Y REPARACIÓN -->
                            <!-- ============================================== -->
                            <div class="p-3.5 bg-light rounded-3 border mb-4">
                                <h5 class="fw-bold text-primary mb-3">
                                    <i class="bi bi-tools me-1"></i> Detalle de Falla, Trabajo y Repuestos
                                </h5>

                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label fw-semibold text-danger">Falla Reportada por el Cliente *</label>
                                        <textarea name="falla_reportada" class="form-control" rows="2" required><?php echo htmlspecialchars($trabajo['falla_reportada'] ?? ''); ?></textarea>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold text-success">Trabajo / Arreglo Realizado</label>
                                        <textarea name="arreglo_realizado" class="form-control" rows="3" placeholder="Detallá los trabajos realizados por el mecánico..."><?php echo htmlspecialchars($trabajo['arreglo_realizado'] ?? ''); ?></textarea>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold text-secondary">Repuestos Utilizados</label>
                                        <textarea name="repuestos" class="form-control" rows="3" placeholder="Ej: Aceite 20w50, Filtro, Bujía..."><?php echo htmlspecialchars($trabajo['repuestos'] ?? ''); ?></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- ============================================== -->
                            <!-- SECCIÓN 3: PRESUPUESTO Y COBRO -->
                            <!-- ============================================== -->
                            <div class="p-3.5 bg-light rounded-3 border mb-4">
                                <h5 class="fw-bold text-primary mb-3">
                                    <i class="bi bi-cash-stack me-1"></i> Presupuesto e Importe Final
                                </h5>

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Precio Estimado Inicial ($)</label>
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input type="number" step="0.01" name="precio_estimado" class="form-control" 
                                                   value="<?php echo htmlspecialchars($trabajo['precio_estimado'] ?? '0.00'); ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold text-success">Precio Final Cobrado ($)</label>
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input type="number" step="0.01" name="precio_final" class="form-control fw-bold" 
                                                   value="<?php echo htmlspecialchars($trabajo['precio_final'] ?? '0.00'); ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Botones de Acción -->
                            <div class="d-flex justify-content-end gap-2 border-top pt-4">
                                <a href="trabajos.php" class="btn btn-secondary px-4">Cancelar</a>
                                <button type="submit" class="btn btn-primary btn-lg px-5 fw-bold shadow">
                                    <i class="bi bi-check-circle-fill me-1"></i> Guardar Cambios del Trabajo
                                </button>
                            </div>

                        </form>

                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Script para verificar estado -->
    <script>
        function verificarEstado() {
            const estado = document.getElementById('selectEstado').value;
            const inputFechaEntrega = document.getElementById('inputFechaEntrega');

            if (estado === 'Entregado' && !inputFechaEntrega.value) {
                const ahora = new Date();
                const anio = ahora.getFullYear();
                const mes = String(ahora.getMonth() + 1).padStart(2, '0');
                const dia = String(ahora.getDate()).padStart(2, '0');
                const horas = String(ahora.getHours()).padStart(2, '0');
                const minutos = String(ahora.getMinutes()).padStart(2, '0');
                inputFechaEntrega.value = `${anio}-${mes}-${dia}T${horas}:${minutos}`;
            }
        }
    </script>

    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
