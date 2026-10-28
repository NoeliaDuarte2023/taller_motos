<?php
// ========================================================
// ARCHIVO: nueva_moto.php
// OBJETIVO: Formulario unificado de Ingreso al Taller o Registro de Moto (1FN y 2FN)
// ========================================================

require_once 'conexion.php';

// 1. Consultar clientes existentes
$stmtClientes = $pdo->query("SELECT id_cliente, nombre, apellido, telefono, direccion FROM clientes ORDER BY apellido ASC, nombre ASC");
$clientes = $stmtClientes->fetchAll();

// 2. Consultar motos existentes agrupadas por cliente
$stmtMotos = $pdo->query("SELECT id_moto, id_cliente, marca, modelo, patente, color, anio, kilometraje FROM motos ORDER BY id_moto DESC");
$todasMotos = $stmtMotos->fetchAll();

$motosPorCliente = [];
foreach ($todasMotos as $m) {
    $motosPorCliente[$m['id_cliente']][] = $m;
}

// Parámetros por URL
$clientePreseleccionado = intval($_GET['cliente_id'] ?? 0);
$motoPreseleccionada    = intval($_GET['id_moto'] ?? 0);
$esSoloMoto             = isset($_GET['solo_moto']) && $_GET['solo_moto'] == '1';

// Si viene preseleccionada una moto, buscar su cliente dueño
if ($motoPreseleccionada > 0 && $clientePreseleccionado === 0) {
    foreach ($todasMotos as $m) {
        if ($m['id_moto'] == $motoPreseleccionada) {
            $clientePreseleccionado = intval($m['id_cliente']);
            break;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $esSoloMoto ? 'Registrar Moto' : 'Ingreso al Taller'; ?> - Taller de Motos</title>
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
                                <h4 class="mb-1 fw-bold">
                                    <?php if ($esSoloMoto): ?>
                                        <i class="bi bi-bicycle text-info me-2"></i>Registrar Nueva Moto en el Padrón
                                    <?php else: ?>
                                        <i class="bi bi-tools text-warning me-2"></i>Ingreso de Trabajo al Taller
                                    <?php endif; ?>
                                </h4>
                                <p class="text-muted small mb-0">
                                    <?php echo $esSoloMoto ? 'Carga del vehículo asociado a su cliente dueño' : 'Carga de cliente, vehículo y orden de servicio a realizar'; ?>
                                </p>
                            </div>
                            <a href="<?php echo $esSoloMoto ? 'motos.php' : 'trabajos.php'; ?>" class="btn btn-outline-light btn-sm">
                                <i class="bi bi-arrow-left me-1"></i> Volver
                            </a>
                        </div>
                    </div>
                    <div class="card-body p-4 p-md-5">
                        
                        <form action="guardar_moto.php" method="POST" id="formNuevoTrabajo">
                            <input type="hidden" name="solo_moto" value="<?php echo $esSoloMoto ? '1' : '0'; ?>">
                            
                            <!-- ============================================== -->
                            <!-- SECCIÓN: DATOS DEL CLIENTE / DUEÑO -->
                            <!-- ============================================== -->
                            <div class="mb-4">
                                <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                                    <h5 class="fw-bold text-dark mb-0">
                                        <i class="bi bi-person-fill text-primary me-1"></i> Dueño de la Moto (Cliente)
                                    </h5>
                                    
                                    <div class="btn-group btn-group-sm" role="group">
                                        <input type="radio" class="btn-check" name="tipo_cliente" id="tipoExistente" value="existente" <?php echo !empty($clientes) ? 'checked' : ''; ?> onchange="cambiarTipoCliente()">
                                        <label class="btn btn-outline-primary" for="tipoExistente">
                                            <i class="bi bi-search me-1"></i> Cliente Registrado
                                        </label>

                                        <input type="radio" class="btn-check" name="tipo_cliente" id="tipoNuevo" value="nuevo" <?php echo empty($clientes) ? 'checked' : ''; ?> onchange="cambiarTipoCliente()">
                                        <label class="btn btn-outline-success" for="tipoNuevo">
                                            <i class="bi bi-person-plus me-1"></i> + Nuevo Cliente
                                        </label>
                                    </div>
                                </div>

                                <!-- SELECCIONAR CLIENTE EXISTENTE -->
                                <div id="seccionClienteExistente" class="p-3 bg-light rounded-3 border mb-3">
                                    <label class="form-label fw-semibold text-dark">
                                        Seleccionar Cliente:
                                    </label>
                                    <select name="id_cliente_existente" id="selectCliente" class="form-select form-select-lg" onchange="alSeleccionarCliente()">
                                        <option value="">-- Buscar por Nombre o Apellido --</option>
                                        <?php foreach ($clientes as $cli): ?>
                                            <option value="<?php echo $cli['id_cliente']; ?>"
                                                    <?php echo ($clientePreseleccionado === intval($cli['id_cliente'])) ? 'selected' : ''; ?>
                                                    data-tel="<?php echo htmlspecialchars($cli['telefono']); ?>"
                                                    data-dir="<?php echo htmlspecialchars($cli['direccion'] ?? ''); ?>">
                                                <?php echo htmlspecialchars($cli['apellido'] . ', ' . $cli['nombre'] . ' (Tel: ' . $cli['telefono'] . ')'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>

                                    <div id="infoClienteSeleccionado" class="mt-2 p-2 bg-white rounded border d-none small">
                                        <span class="text-muted">Teléfono:</span> <strong id="txtTelCliente" class="text-success me-3"></strong>
                                        <span class="text-muted">Dirección:</span> <strong id="txtDirCliente" class="text-dark"></strong>
                                    </div>
                                </div>

                                <!-- FORMULARIO NUEVO CLIENTE -->
                                <div id="seccionClienteNuevo" class="p-3 bg-light rounded-3 border mb-3 d-none">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Nombre *</label>
                                            <input type="text" name="nuevo_nombre" maxlength="100" pattern="[A-Za-zÁÉÍÓÚÜÑáéíóúüñ][A-Za-zÁÉÍÓÚÜÑáéíóúüñ' .\-]{1,99}" title="Solo letras, espacios, apóstrofes y guiones (sin números)." id="nuevo_nombre" class="form-control" placeholder="Ej: Lucas">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Apellido *</label>
                                            <input type="text" name="nuevo_apellido" maxlength="100" pattern="[A-Za-zÁÉÍÓÚÜÑáéíóúüñ][A-Za-zÁÉÍÓÚÜÑáéíóúüñ' .\-]{1,99}" title="Solo letras, espacios, apóstrofes y guiones (sin números)." id="nuevo_apellido" class="form-control" placeholder="Ej: Gómez">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Teléfono *</label>
                                            <input type="text" name="nuevo_telefono" maxlength="20" inputmode="tel" pattern="\+?[0-9 \(\)\-.]{8,20}" title="Solo números (entre 8 y 15 dígitos). Podés usar espacios, guiones, paréntesis y + al inicio." id="nuevo_telefono" class="form-control" placeholder="Ej: 3794123456">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Dirección</label>
                                            <input type="text" name="nuevo_direccion" maxlength="200" pattern="(?=(?:.*[A-Za-zÀ-ÿ]){3})[A-Za-zÀ-ÿ0-9 .,°º#\/'\-]{5,200}" title="Dirección real: nombre de calle o barrio con letras y, si corresponde, número. Ej: Av. San Martín 450." id="nuevo_direccion" class="form-control" placeholder="Ej: Av. San Martín 450">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- ============================================== -->
                            <!-- SECCIÓN: DATOS DE LA MOTO (VEHÍCULO) -->
                            <!-- ============================================== -->
                            <div class="mb-4">
                                <div class="border-bottom pb-2 mb-3">
                                    <h5 class="fw-bold text-dark mb-0">
                                        <i class="bi bi-bicycle text-primary me-1"></i> Datos de la Moto
                                    </h5>
                                </div>

                                <div class="p-3 bg-light rounded-3 border mb-3">
                                    
                                    <?php if (!$esSoloMoto): ?>
                                        <!-- Selector de motos para el cliente cuando entra a taller -->
                                        <div id="cajaSelectorMotosRegistradas" class="d-none mb-3">
                                            <label class="form-label fw-semibold text-dark">Moto a ingresar:</label>
                                            <select id="selectMotoDelCliente" class="form-select form-select-lg mb-2" onchange="alCambiarOpcionMoto()">
                                                <!-- Opciones dinámicas generadas por JS -->
                                            </select>
                                            <input type="hidden" name="tipo_moto" id="inputTipoMoto" value="existente">
                                            <input type="hidden" name="id_moto_existente" id="inputIdMotoExistente" value="<?php echo $motoPreseleccionada; ?>">
                                        </div>
                                    <?php else: ?>
                                        <input type="hidden" name="tipo_moto" value="nueva">
                                    <?php endif; ?>

                                    <!-- Campos para cargar datos del vehículo -->
                                    <div id="cajaFormularioMotoNueva">
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <label class="form-label fw-semibold">Patente / Dominio *</label>
                                                <input type="text" name="patente" id="inputPatente" class="form-control text-uppercase font-monospace fw-bold" 
                                                       placeholder="Ej: A123BCD o OBJ123" 
                                                       pattern="^([A-Za-z]{1}[0-9]{3}[A-Za-z]{3}|[0-9]{3}[A-Za-z]{3}|[A-Za-z]{3}[0-9]{3}|[A-Za-z]{2}[0-9]{3}[A-Za-z]{2})$" 
                                                       title="Formato válido: A123BCD (motos Mercosur), OBJ123 o 123ABC" 
                                                       style="letter-spacing: 1px;" required>
                                                <small class="text-muted" style="font-size: 11px;">Formatos: A123BCD, OBJ123, 123ABC</small>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label fw-semibold">Marca *</label>
                                                <input type="text" name="marca" maxlength="50" id="inputMarca" list="listaMarcas" class="form-control" placeholder="Ej: Honda, Yamaha..." required>
                                                <datalist id="listaMarcas">
                                                    <option value="Honda">
                                                    <option value="Yamaha">
                                                    <option value="Motomel">
                                                    <option value="Corven">
                                                    <option value="Zanella">
                                                    <option value="Bajaj">
                                                    <option value="Gilera">
                                                    <option value="Suzuki">
                                                </datalist>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label fw-semibold">Modelo *</label>
                                                <input type="text" name="modelo" maxlength="100" id="inputModelo" class="form-control" placeholder="Ej: Wave 110s, YBR 125..." required>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label fw-semibold">Color</label>
                                                <input type="text" name="color" maxlength="30" id="inputColor" class="form-control" placeholder="Ej: Rojo, Negro">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label fw-semibold">Año</label>
                                                <input type="number" name="anio" id="inputAnio" class="form-control" placeholder="Ej: 2022" min="1950" max="<?php echo date('Y') + 1; ?>">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label fw-semibold">Kilometraje Actual</label>
                                                <input type="number" name="kilometraje" max="999999" id="inputKm" class="form-control" placeholder="Ej: 15400" min="0">
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>

                            <?php if (!$esSoloMoto): ?>
                                <!-- ============================================== -->
                                <!-- SECCIÓN: ORDEN DE TRABAJO A REALIZAR (1FN Y 2FN) -->
                                <!-- ============================================== -->
                                <div class="mb-4">
                                    <h5 class="fw-bold text-dark border-bottom pb-2 mb-3">
                                        <i class="bi bi-wrench-adjustable text-primary me-1"></i> Trabajo a Realizar
                                    </h5>
                                    
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <label class="form-label fw-semibold text-danger">Falla Reportada / Arreglo por Hacer *</label>
                                            <textarea name="falla_reportada" class="form-control" rows="3" 
                                                      placeholder="Describí el problema reportado (Ej: No arranca en frío, cambio de transmisión, frenos traseros...)" required></textarea>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">Fecha Prometida de Entrega</label>
                                            <input type="date" name="fecha_prometida" class="form-control" min="2026-08-01" max="<?php echo date('Y-m-d', strtotime('+1 year')); ?>" value="<?php echo date('Y-m-d', strtotime('+3 days')); ?>">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">Presupuesto Estimado ($)</label>
                                            <div class="input-group">
                                                <span class="input-group-text">$</span>
                                                <input type="number" step="0.01" name="precio_estimado" min="0" max="99999999.99" class="form-control" placeholder="0.00">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">Estado del Trabajo</label>
                                            <select name="estado" class="form-select fw-semibold">
                                                <option value="En Espera" selected>🟡 En Espera</option>
                                                <option value="En Reparacion">🔵 En Reparación</option>
                                                <option value="Terminado">🟢 Terminado (Lista para entregar)</option>
                                                <option value="Entregado">⚪ Entregado (Ya retirada por el cliente)</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Botones de Acción -->
                            <div class="d-flex justify-content-end gap-2 border-top pt-4">
                                <a href="<?php echo $esSoloMoto ? 'motos.php' : 'trabajos.php'; ?>" class="btn btn-secondary px-4">Cancelar</a>
                                <button type="submit" class="btn <?php echo $esSoloMoto ? 'btn-success' : 'btn-primary'; ?> btn-lg px-5 fw-bold shadow">
                                    <i class="bi bi-check-circle-fill me-1"></i> 
                                    <?php echo $esSoloMoto ? 'Guardar Moto en el Padrón' : 'Guardar Ingreso al Taller'; ?>
                                </button>
                            </div>

                        </form>

                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- JavaScript para selector inteligente -->
    <script>
        const motosPorCliente = <?php echo json_encode($motosPorCliente); ?>;
        const esSoloMoto = <?php echo $esSoloMoto ? 'true' : 'false'; ?>;
        const motoPreseleccionada = <?php echo $motoPreseleccionada; ?>;

        function cambiarTipoCliente() {
            const esNuevo = document.getElementById('tipoNuevo').checked;
            const seccionExistente = document.getElementById('seccionClienteExistente');
            const seccionNuevo = document.getElementById('seccionClienteNuevo');
            const selectCliente = document.getElementById('selectCliente');

            if (esNuevo) {
                seccionExistente.classList.add('d-none');
                seccionNuevo.classList.remove('d-none');
                selectCliente.removeAttribute('required');
                
                mostrarFormularioMotoNueva(true);
                const caja = document.getElementById('cajaSelectorMotosRegistradas');
                if (caja) caja.classList.add('d-none');
                const tipoMoto = document.getElementById('inputTipoMoto');
                if (tipoMoto) tipoMoto.value = 'nueva';
            } else {
                seccionExistente.classList.remove('d-none');
                seccionNuevo.classList.add('d-none');
                selectCliente.setAttribute('required', 'required');
                alSeleccionarCliente();
            }
        }

        function alSeleccionarCliente() {
            const select = document.getElementById('selectCliente');
            const infoBox = document.getElementById('infoClienteSeleccionado');
            const cajaSelector = document.getElementById('cajaSelectorMotosRegistradas');
            const selectMotos = document.getElementById('selectMotoDelCliente');
            const selectedOption = select.options[select.selectedIndex];
            const idCliente = selectedOption.value;

            if (idCliente) {
                document.getElementById('txtTelCliente').innerText = selectedOption.getAttribute('data-tel') || '-';
                document.getElementById('txtDirCliente').innerText = selectedOption.getAttribute('data-dir') || '-';
                infoBox.classList.remove('d-none');

                if (!esSoloMoto) {
                    const motosDelCliente = motosPorCliente[idCliente] || [];

                    if (motosDelCliente.length > 0) {
                        selectMotos.innerHTML = '';
                        motosDelCliente.forEach(m => {
                            const opt = document.createElement('option');
                            opt.value = m.id_moto;
                            opt.textContent = `${m.marca} ${m.modelo} (Patente: ${m.patente})`;
                            if (motoPreseleccionada > 0 && m.id_moto == motoPreseleccionada) {
                                opt.selected = true;
                            }
                            selectMotos.appendChild(opt);
                        });

                        const optNueva = document.createElement('option');
                        optNueva.value = 'NUEVA';
                        optNueva.textContent = '+ Agregar otra moto para este cliente...';
                        selectMotos.appendChild(optNueva);

                        cajaSelector.classList.remove('d-none');
                        alCambiarOpcionMoto();

                    } else {
                        cajaSelector.classList.add('d-none');
                        document.getElementById('inputTipoMoto').value = 'nueva';
                        mostrarFormularioMotoNueva(true);
                    }
                }

            } else {
                infoBox.classList.add('d-none');
                if (cajaSelector) cajaSelector.classList.add('d-none');
                mostrarFormularioMotoNueva(true);
            }
        }

        function alCambiarOpcionMoto() {
            if (esSoloMoto) return;
            const selectMotos = document.getElementById('selectMotoDelCliente');
            if (!selectMotos) return;
            const valor = selectMotos.value;

            if (valor === 'NUEVA') {
                document.getElementById('inputTipoMoto').value = 'nueva';
                document.getElementById('inputIdMotoExistente').value = '';
                mostrarFormularioMotoNueva(true);
            } else {
                document.getElementById('inputTipoMoto').value = 'existente';
                document.getElementById('inputIdMotoExistente').value = valor;
                mostrarFormularioMotoNueva(false);
            }
        }

        function mostrarFormularioMotoNueva(mostrar) {
            const caja = document.getElementById('cajaFormularioMotoNueva');
            const inPatente = document.getElementById('inputPatente');
            const inMarca = document.getElementById('inputMarca');
            const inModelo = document.getElementById('inputModelo');

            if (!caja) return;

            if (mostrar) {
                caja.classList.remove('d-none');
                inPatente.setAttribute('required', 'required');
                inMarca.setAttribute('required', 'required');
                inModelo.setAttribute('required', 'required');
            } else {
                caja.classList.add('d-none');
                inPatente.removeAttribute('required');
                inMarca.removeAttribute('required');
                inModelo.removeAttribute('required');
            }
        }

        window.addEventListener('DOMContentLoaded', () => {
            cambiarTipoCliente();
            
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('cliente_id') || urlParams.get('id_moto')) {
                alSeleccionarCliente();
            }
        });
    </script>

    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="validar_campos.js"></script>
</body>
</html>
