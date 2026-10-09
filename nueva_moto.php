<?php
// ========================================================
// ARCHIVO: nueva_moto.php
// OBJETIVO: Formulario unificado de Ingreso al Taller
// LENGUAJES: PHP + HTML5 + Bootstrap 5 + JavaScript
// ========================================================

require_once 'conexion.php';

// Consultamos todos los clientes existentes para el selector rápido
$stmtClientes = $pdo->query("SELECT id_cliente, nombre, apellido, telefono, direccion FROM clientes ORDER BY apellido ASC, nombre ASC");
$clientes = $stmtClientes->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ingreso de Moto al Taller - Nueva Ficha</title>
    <!-- Bootstrap 5 CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">

    <!-- Barra de Navegación Común -->
    <?php include 'navbar.php'; ?>

    <div class="container mb-5">
        <div class="row justify-content-center">
            <div class="col-lg-9 col-xl-8">
                
                <div class="card shadow border-0 rounded-3">
                    <div class="card-header bg-dark text-white p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h4 class="mb-1 fw-bold"><i class="bi bi-plus-circle text-success me-2"></i>Ingreso de Moto al Taller</h4>
                                <p class="text-muted small mb-0">Completá los datos del dueño, del vehículo y la falla a reparar</p>
                            </div>
                            <a href="motos.php" class="btn btn-outline-light btn-sm">
                                <i class="bi bi-arrow-left me-1"></i> Volver al Tablero
                            </a>
                        </div>
                    </div>
                    <div class="card-body p-4 p-md-5">
                        
                        <form action="guardar_moto.php" method="POST" id="formNuevaMoto">
                            
                            <!-- ============================================== -->
                            <!-- SECCIÓN 1: DATOS DEL DUEÑO / CLIENTE -->
                            <!-- ============================================== -->
                            <div class="mb-4">
                                <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                                    <h5 class="fw-bold text-primary mb-0">
                                        <i class="bi bi-person-fill me-1"></i> 1. Datos del Dueño
                                    </h5>
                                    
                                    <!-- Switch / Botones para elegir si es cliente existente o nuevo -->
                                    <div class="btn-group btn-group-sm" role="group">
                                        <input type="radio" class="btn-check" name="tipo_cliente" id="tipoExistente" value="existente" <?php echo !empty($clientes) ? 'checked' : ''; ?> onchange="cambiarTipoCliente()">
                                        <label class="btn btn-outline-primary" for="tipoExistente">
                                            <i class="bi bi-search me-1"></i> Cliente Existente
                                        </label>

                                        <input type="radio" class="btn-check" name="tipo_cliente" id="tipoNuevo" value="nuevo" <?php echo empty($clientes) ? 'checked' : ''; ?> onchange="cambiarTipoCliente()">
                                        <label class="btn btn-outline-success" for="tipoNuevo">
                                            <i class="bi bi-person-plus me-1"></i> + Nuevo Cliente
                                        </label>
                                    </div>
                                </div>

                                <!-- CAJA A: BUSCADOR DE CLIENTE EXISTENTE -->
                                <div id="seccionClienteExistente" class="p-3 bg-light rounded-3 border mb-3">
                                    <label class="form-label fw-semibold text-dark">
                                        Buscar y Seleccionar Dueño * 
                                        <small class="text-muted fw-normal">(Si ya vino antes o tiene otra moto)</small>
                                    </label>
                                    <select name="id_cliente_existente" id="selectCliente" class="form-select form-select-lg" onchange="mostrarInfoCliente()">
                                        <option value="">-- Buscar por Nombre o Apellido --</option>
                                        <?php foreach ($clientes as $cli): ?>
                                            <option value="<?php echo $cli['id_cliente']; ?>"
                                                    data-tel="<?php echo htmlspecialchars($cli['telefono']); ?>"
                                                    data-dir="<?php echo htmlspecialchars($cli['direccion'] ?? ''); ?>">
                                                <?php echo htmlspecialchars($cli['apellido'] . ', ' . $cli['nombre'] . ' (Tel: ' . $cli['telefono'] . ')'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>

                                    <!-- Tarjeta informativa del cliente seleccionado -->
                                    <div id="infoClienteSeleccionado" class="mt-2 p-2 bg-white rounded border d-none small">
                                        <span class="text-muted">Teléfono / WhatsApp:</span> <strong id="txtTelCliente" class="text-success me-3"></strong>
                                        <span class="text-muted">Dirección:</span> <strong id="txtDirCliente" class="text-dark"></strong>
                                    </div>
                                </div>

                                <!-- CAJA B: FORMULARIO DE NUEVO CLIENTE -->
                                <div id="seccionClienteNuevo" class="p-3 bg-light rounded-3 border mb-3 d-none">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Nombre *</label>
                                            <input type="text" name="nuevo_nombre" id="nuevo_nombre" class="form-control" placeholder="Ej: Lucas">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Apellido *</label>
                                            <input type="text" name="nuevo_apellido" id="nuevo_apellido" class="form-control" placeholder="Ej: Gómez">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Teléfono / WhatsApp *</label>
                                            <input type="text" name="nuevo_telefono" id="nuevo_telefono" class="form-control" placeholder="Ej: 3794123456">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Dirección / Localidad</label>
                                            <input type="text" name="nuevo_direccion" id="nuevo_direccion" class="form-control" placeholder="Ej: Av. San Martín 450">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- ============================================== -->
                            <!-- SECCIÓN 2: DATOS DEL VEHÍCULO -->
                            <!-- ============================================== -->
                            <div class="mb-4">
                                <h5 class="fw-bold text-primary border-bottom pb-2 mb-3">
                                    <i class="bi bi-bicycle me-1"></i> 2. Datos de la Moto
                                </h5>
                                
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Patente / Dominio *</label>
                                        <input type="text" name="patente" class="form-control text-uppercase font-monospace fw-bold" 
                                               placeholder="Ej: A123BCD o 123ABC" required style="letter-spacing: 1px;">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Marca *</label>
                                        <input type="text" name="marca" list="listaMarcas" class="form-control" placeholder="Ej: Honda, Yamaha..." required>
                                        <datalist id="listaMarcas">
                                            <option value="Honda">
                                            <option value="Yamaha">
                                            <option value="Motomel">
                                            <option value="Corven">
                                            <option value="Zanella">
                                            <option value="Bajaj">
                                            <option value="Gilera">
                                            <option value="Suzuki">
                                            <option value="Kawasaki">
                                            <option value="KTM">
                                            <option value="Mondial">
                                            <option value="Guerrero">
                                        </datalist>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Modelo *</label>
                                        <input type="text" name="modelo" class="form-control" placeholder="Ej: Wave 110s, YBR 125..." required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Color</label>
                                        <input type="text" name="color" class="form-control" placeholder="Ej: Rojo, Negro mate">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Año</label>
                                        <input type="number" name="anio" class="form-control" placeholder="Ej: 2022" min="1970" max="2030">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Kilometraje Actual</label>
                                        <input type="number" name="kilometraje" class="form-control" placeholder="Ej: 15400" min="0">
                                    </div>
                                </div>
                            </div>

                            <!-- ============================================== -->
                            <!-- SECCIÓN 3: FICHA DE INGRESO Y REPARACIÓN -->
                            <!-- ============================================== -->
                            <div class="mb-4">
                                <h5 class="fw-bold text-primary border-bottom pb-2 mb-3">
                                    <i class="bi bi-tools me-1"></i> 3. Diagnóstico y Arreglo por Hacer
                                </h5>
                                
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label fw-semibold text-danger">Falla Reportada / Arreglo a Realizar *</label>
                                        <textarea name="falla_reportada" class="form-control" rows="3" 
                                                  placeholder="Describí el problema con el que ingresa la moto (Ej: No arranca, cambio de aceite y filtro, regular válvulas, frenos traseros...)" required></textarea>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Fecha Prometida de Entrega</label>
                                        <input type="date" name="fecha_prometida" class="form-control" value="<?php echo date('Y-m-d', strtotime('+3 days')); ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Presupuesto Estimado ($)</label>
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input type="number" step="0.01" name="precio_estimado" class="form-control" placeholder="0.00">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Estado Inicial</label>
                                        <select name="estado" class="form-select">
                                            <option value="En Espera" selected>🟡 En Espera</option>
                                            <option value="En Reparacion">🔵 En Reparación</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Botones de Acción -->
                            <div class="d-flex justify-content-end gap-2 border-top pt-4">
                                <a href="motos.php" class="btn btn-secondary px-4">Cancelar</a>
                                <button type="submit" class="btn btn-success btn-lg px-5 fw-bold shadow">
                                    <i class="bi bi-check-circle-fill me-1"></i> Guardar Ingreso al Taller
                                </button>
                            </div>

                        </form>

                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Script para alternar entre Cliente Existente y Nuevo Cliente -->
    <script>
        function cambiarTipoCliente() {
            const esNuevo = document.getElementById('tipoNuevo').checked;
            const seccionExistente = document.getElementById('seccionClienteExistente');
            const seccionNuevo = document.getElementById('seccionClienteNuevo');
            const selectCliente = document.getElementById('selectCliente');
            const inputNombre = document.getElementById('nuevo_nombre');
            const inputApellido = document.getElementById('nuevo_apellido');
            const inputTel = document.getElementById('nuevo_telefono');

            if (esNuevo) {
                seccionExistente.classList.add('d-none');
                seccionNuevo.classList.remove('d-none');
                selectCliente.removeAttribute('required');
                inputNombre.setAttribute('required', 'required');
                inputApellido.setAttribute('required', 'required');
                inputTel.setAttribute('required', 'required');
            } else {
                seccionExistente.classList.remove('d-none');
                seccionNuevo.classList.add('d-none');
                selectCliente.setAttribute('required', 'required');
                inputNombre.removeAttribute('required');
                inputApellido.removeAttribute('required');
                inputTel.removeAttribute('required');
            }
        }

        function mostrarInfoCliente() {
            const select = document.getElementById('selectCliente');
            const infoBox = document.getElementById('infoClienteSeleccionado');
            const selectedOption = select.options[select.selectedIndex];

            if (selectedOption.value) {
                document.getElementById('txtTelCliente').innerText = selectedOption.getAttribute('data-tel') || '-';
                document.getElementById('txtDirCliente').innerText = selectedOption.getAttribute('data-dir') || '-';
                infoBox.classList.remove('d-none');
            } else {
                infoBox.classList.add('d-none');
            }
        }

        // Ejecutar al cargar la página
        window.addEventListener('DOMContentLoaded', () => {
            cambiarTipoCliente();
        });
    </script>

    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
