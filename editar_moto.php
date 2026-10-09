<?php
// ========================================================
// ARCHIVO: editar_moto.php
// OBJETIVO: Ficha técnica completa, edición y cambio de estado
// LENGUAJES: PHP + HTML5 + Bootstrap 5 + JavaScript
// ========================================================

require_once 'conexion.php';

$id_moto = intval($_GET['id'] ?? 0);
if ($id_moto <= 0) {
    header('Location: motos.php');
    exit;
}

// Consultamos la moto con los datos de su cliente
$stmt = $pdo->prepare("SELECT m.*, c.nombre AS cliente_nombre, c.apellido AS cliente_apellido, c.telefono AS cliente_telefono, c.direccion AS cliente_direccion 
                       FROM motos m 
                       INNER JOIN clientes c ON m.id_cliente = c.id_cliente 
                       WHERE m.id_moto = :id");
$stmt->execute([':id' => $id_moto]);
$moto = $stmt->fetch();

if (!$moto) {
    echo "<h3 style='color:red;'>Moto no encontrada.</h3><a href='motos.php'>Volver</a>";
    exit;
}

$estadoActual = $moto['estado'] ?? 'En Espera';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ficha de Moto #<?php echo $moto['id_moto']; ?> - Taller de Motos</title>
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
                                    <?php echo htmlspecialchars($moto['patente']); ?>
                                </span>
                                <h4 class="mb-0 mt-2 fw-bold text-white">
                                    <?php echo htmlspecialchars($moto['marca'] . ' ' . $moto['modelo']); ?>
                                </h4>
                                <small class="text-secondary">Dueño: <strong><?php echo htmlspecialchars($moto['cliente_apellido'] . ', ' . $moto['cliente_nombre']); ?></strong></small>
                            </div>
                            <div>
                                <a href="motos.php" class="btn btn-outline-light">
                                    <i class="bi bi-arrow-left me-1"></i> Volver al Tablero
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-4 p-md-5">
                        
                        <form action="actualizar_moto.php" method="POST">
                            <input type="hidden" name="id_moto" value="<?php echo $moto['id_moto']; ?>">
                            <input type="hidden" name="id_cliente" value="<?php echo $moto['id_cliente']; ?>">

                            <!-- ============================================== -->
                            <!-- SECCIÓN 1: ESTADO Y CONTROL DE ENTREGA -->
                            <!-- ============================================== -->
                            <div class="p-3.5 bg-light rounded-3 border mb-4">
                                <h5 class="fw-bold text-primary mb-3">
                                    <i class="bi bi-flag-fill me-1"></i> Estado Actual y Fechas de Servicio
                                </h5>

                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold text-dark">Estado de la Moto *</label>
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
                                               value="<?php echo !empty($moto['fecha_prometida']) ? $moto['fecha_prometida'] : ''; ?>">
                                    </div>
                                    <div class="col-md-4" id="cajaFechaEntrega">
                                        <label class="form-label fw-semibold text-success">Fecha de Retiro / Entrega Real</label>
                                        <input type="datetime-local" name="fecha_entrega" class="form-control" 
                                               value="<?php echo !empty($moto['fecha_entrega']) ? date('Y-m-d\TH:i', strtotime($moto['fecha_entrega'])) : ''; ?>">
                                        <small class="text-muted d-block" style="font-size: 11px;">Se completa automáticamente al entregar</small>
                                    </div>
                                </div>
                            </div>

                            <!-- ============================================== -->
                            <!-- SECCIÓN 2: TRABAJO REALIZADO, REPUESTOS Y PRECIOS -->
                            <!-- ============================================== -->
                            <div class="mb-4">
                                <h5 class="fw-bold text-primary border-bottom pb-2 mb-3">
                                    <i class="bi bi-wrench me-1"></i> Diagnóstico, Reparación y Repuestos
                                </h5>

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold text-danger">Falla Reportada (Motivo de Ingreso)</label>
                                        <textarea name="falla_reportada" class="form-control" rows="3" placeholder="Falla informada por el cliente..."><?php echo htmlspecialchars($moto['falla_reportada'] ?? $moto['observaciones'] ?? ''); ?></textarea>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold text-success">Arreglo Realizado (Trabajo de Taller)</label>
                                        <textarea name="arreglo_realizado" class="form-control" rows="3" placeholder="Describí los arreglos efectuados, mano de obra, afinación, cambio de piezas..."><?php echo htmlspecialchars($moto['arreglo_realizado'] ?? ''); ?></textarea>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Repuestos Utilizados / Piezas Cambiadas</label>
                                        <input type="text" name="repuestos" class="form-control" 
                                               placeholder="Ej: Aceite Motul 5100 1L, Bujía NGK CR7HSA, Pastillas de freno delanteras..." 
                                               value="<?php echo htmlspecialchars($moto['repuestos'] ?? ''); ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Presupuesto Estimado Inicial ($)</label>
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input type="number" step="0.01" name="precio_estimado" class="form-control" 
                                                   value="<?php echo htmlspecialchars($moto['precio_estimado'] ?? '0.00'); ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold text-success">Precio Final / Total a Cobrar ($)</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-success text-white fw-bold">$</span>
                                            <input type="number" step="0.01" name="precio_final" class="form-control fw-bold fs-5 text-dark" 
                                                   value="<?php echo htmlspecialchars($moto['precio_final'] ?? $moto['precio_estimado'] ?? '0.00'); ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- ============================================== -->
                            <!-- SECCIÓN 3: DATOS DEL VEHÍCULO Y DUEÑO -->
                            <!-- ============================================== -->
                            <div class="mb-4">
                                <h5 class="fw-bold text-primary border-bottom pb-2 mb-3">
                                    <i class="bi bi-card-checklist me-1"></i> Datos del Vehículo y del Cliente
                                </h5>

                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">Patente *</label>
                                        <input type="text" name="patente" class="form-control text-uppercase font-monospace fw-bold" 
                                               value="<?php echo htmlspecialchars($moto['patente']); ?>" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">Marca *</label>
                                        <input type="text" name="marca" class="form-control" 
                                               value="<?php echo htmlspecialchars($moto['marca']); ?>" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">Modelo *</label>
                                        <input type="text" name="modelo" class="form-control" 
                                               value="<?php echo htmlspecialchars($moto['modelo']); ?>" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">Color</label>
                                        <input type="text" name="color" class="form-control" 
                                               value="<?php echo htmlspecialchars($moto['color'] ?? ''); ?>">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">Año</label>
                                        <input type="number" name="anio" class="form-control" 
                                               value="<?php echo htmlspecialchars($moto['anio'] ?? ''); ?>">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">Kilometraje</label>
                                        <input type="number" name="kilometraje" class="form-control" 
                                               value="<?php echo htmlspecialchars($moto['kilometraje'] ?? 0); ?>">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">Teléfono del Dueño</label>
                                        <input type="text" name="cliente_telefono" class="form-control" 
                                               value="<?php echo htmlspecialchars($moto['cliente_telefono']); ?>" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">Dirección del Dueño</label>
                                        <input type="text" name="cliente_direccion" class="form-control" 
                                               value="<?php echo htmlspecialchars($moto['cliente_direccion'] ?? ''); ?>">
                                    </div>
                                </div>
                            </div>

                            <!-- Botones de Acción -->
                            <div class="d-flex justify-content-between align-items-center border-top pt-4">
                                <a href="eliminar_moto.php?id=<?php echo $moto['id_moto']; ?>" class="btn btn-outline-danger" 
                                   onclick="return confirm('¿Seguro que deseas eliminar el registro de esta moto?');">
                                    <i class="bi bi-trash me-1"></i> Eliminar Registro
                                </a>
                                <div class="d-flex gap-2">
                                    <a href="motos.php" class="btn btn-secondary px-4">Cancelar</a>
                                    <button type="submit" class="btn btn-primary btn-lg px-5 fw-bold shadow">
                                        <i class="bi bi-save me-1"></i> Guardar Cambios
                                    </button>
                                </div>
                            </div>

                        </form>

                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Script de validación de estado -->
    <script>
        function verificarEstado() {
            const estado = document.getElementById('selectEstado').value;
            const cajaFecha = document.getElementById('cajaFechaEntrega');
            const inputFecha = document.querySelector('input[name="fecha_entrega"]');

            if (estado === 'Entregado') {
                if (!inputFecha.value) {
                    const ahora = new Date();
                    const anio = ahora.getFullYear();
                    const mes = String(ahora.getMonth() + 1).padStart(2, '0');
                    const dia = String(ahora.getDate()).padStart(2, '0');
                    const horas = String(ahora.getHours()).padStart(2, '0');
                    const min = String(ahora.getMinutes()).padStart(2, '0');
                    inputFecha.value = `${anio}-${mes}-${dia}T${horas}:${min}`;
                }
            }
        }
    </script>

    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
