<?php
// ========================================================
// ARCHIVO: nueva_moto.php
// OBJETIVO: Formulario exclusivo para registrar una nueva moto
// LENGUAJES: PHP + HTML5 + Bootstrap 5
// ========================================================

require_once 'conexion.php';

// Consultamos todos los clientes para cargarlos en el select desplegable
$stmtClientes = $pdo->query("SELECT id_cliente, nombre, apellido FROM clientes ORDER BY apellido ASC");
$clientes = $stmtClientes->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Moto - Taller de Motos</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">

    <!-- Barra de Navegación -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4 shadow">
        <div class="container">
            <a class="navbar-brand fw-bold" href="clientes.php">🏍️ Taller de Motos</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="clientes.php"><i class="bi bi-people me-1"></i> Clientes</a>
                <a class="nav-link active fw-bold" href="motos.php"><i class="bi bi-bicycle me-1"></i> Motos</a>
                <a class="nav-link" href="repuestos.php"><i class="bi bi-box-seam me-1"></i> Repuestos</a>
                <a class="nav-link" href="mecanicos.php"><i class="bi bi-person-badge me-1"></i> Mecánicos</a>
            </div>
        </div>
    </nav>

    <div class="container mb-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-7">
                
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-primary text-white py-3">
                        <h5 class="mb-0 fw-bold"><i class="bi bi-plus-circle me-2"></i>Registrar Nueva Moto</h5>
                    </div>
                    <div class="card-body p-4">
                        
                        <form action="guardar_moto.php" method="POST">
                            
                            <!-- Selección del Dueño / Cliente -->
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Dueño / Cliente *</label>
                                <select name="id_cliente" class="form-select" required>
                                    <option value="">-- Seleccionar Cliente --</option>
                                    <?php foreach ($clientes as $cli): ?>
                                        <option value="<?php echo $cli['id_cliente']; ?>">
                                            <?php echo htmlspecialchars($cli['apellido'] . ', ' . $cli['nombre']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Marca *</label>
                                    <input type="text" name="marca" class="form-control" placeholder="Ej: Honda, Yamaha" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Modelo *</label>
                                    <input type="text" name="modelo" class="form-control" placeholder="Ej: CB 250 Twister" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Año</label>
                                    <input type="number" name="anio" class="form-control" placeholder="Ej: 2022" min="1950" max="2030">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Patente / Placa *</label>
                                    <input type="text" name="patente" class="form-control" placeholder="Ej: A123BCD" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Color</label>
                                    <input type="text" name="color" class="form-control" placeholder="Ej: Rojo">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Kilometraje</label>
                                    <input type="number" name="kilometraje" class="form-control" placeholder="Ej: 15000" min="0">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Observaciones / Detalles</label>
                                <textarea name="observaciones" class="form-control" rows="2" placeholder="Detalles de carrocería, rayones, etc..."></textarea>
                            </div>

                            <div class="d-flex justify-content-between gap-2 mt-4 pt-3 border-top">
                                <a href="motos.php" class="btn btn-secondary w-50">
                                    <i class="bi bi-arrow-left me-1"></i> Volver a la Lista
                                </a>
                                <button type="submit" class="btn btn-success w-50 fw-bold">
                                    <i class="bi bi-save me-1"></i> Guardar Moto
                                </button>
                            </div>
                        </form>

                    </div>
                </div>

            </div>
        </div>
    </div>

</body>
</html>