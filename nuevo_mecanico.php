<?php
// ========================================================
// ARCHIVO: nuevo_mecanico.php
// OBJETIVO: Formulario exclusivo para registrar un nuevo mecánico
// LENGUAJES: PHP + HTML5 + Bootstrap 5
// ========================================================
require_once 'conexion.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Mecánico - Taller de Motos</title>
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
                <a class="nav-link" href="motos.php"><i class="bi bi-bicycle me-1"></i> Motos</a>
                <a class="nav-link" href="repuestos.php"><i class="bi bi-box-seam me-1"></i> Repuestos</a>
                <a class="nav-link active fw-bold" href="mecanicos.php"><i class="bi bi-person-badge me-1"></i> Mecánicos</a>
            </div>
        </div>
    </nav>

    <div class="container mb-5">
        <div class="row justify-content-center">
            <div class="col-md-7 col-lg-6">
                
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-primary text-white py-3">
                        <h5 class="mb-0 fw-bold"><i class="bi bi-person-plus-fill me-2"></i>Registrar Nuevo Mecánico</h5>
                    </div>
                    <div class="card-body p-4">
                        
                        <form action="guardar_mecanico.php" method="POST">
                            
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Nombre y Apellido *</label>
                                <input type="text" name="nombre" class="form-control" placeholder="Ej: Carlos Méndez" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Teléfono</label>
                                <input type="tel" name="telefono" class="form-control" placeholder="Ej: 1144556677">
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Especialidad</label>
                                <input type="text" name="especialidad" class="form-control" placeholder="Ej: Motores, Electricidad, Frenos">
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Estado</label>
                                <select name="estado" class="form-select">
                                    <option value="Activo" selected>Activo</option>
                                    <option value="Inactivo">Inactivo</option>
                                </select>
                            </div>

                            <div class="d-flex justify-content-between gap-2 mt-4 pt-3 border-top">
                                <a href="mecanicos.php" class="btn btn-secondary w-50">
                                    <i class="bi bi-arrow-left me-1"></i> Volver a la Lista
                                </a>
                                <button type="submit" class="btn btn-success w-50 fw-bold">
                                    <i class="bi bi-save me-1"></i> Guardar Mecánico
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