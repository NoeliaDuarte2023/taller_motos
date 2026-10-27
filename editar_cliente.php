<?php
require_once 'conexion.php';

// Verificamos que venga el ID por la URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: clientes.php');
    exit;
}

$id_cliente = $_GET['id'];

// Buscamos los datos del cliente en la base de datos
$stmt = $pdo->prepare("SELECT * FROM clientes WHERE id_cliente = :id");
$stmt->execute([':id' => $id_cliente]);
$cliente = $stmt->fetch();

// Si el cliente no existe, volvemos a la lista
if (!$cliente) {
    header('Location: clientes.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Cliente - Taller de Motos</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">

    <!-- Barra de Navegación Común -->
    <?php include 'navbar.php'; ?>

    <div class="container mb-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0 fw-bold"><i class="bi bi-pencil-square me-2"></i>Editar Cliente #<?php echo $cliente['id_cliente']; ?></h5>
                    </div>
                    <div class="card-body">
                        <form action="actualizar_cliente.php" method="POST">
                            
                            <!-- Enviamos el ID oculto para saber a quién actualizar -->
                            <input type="hidden" name="id_cliente" value="<?php echo $cliente['id_cliente']; ?>">

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Nombre *</label>
                                <input type="text" name="nombre" maxlength="100" pattern="[A-Za-zÁÉÍÓÚÜÑáéíóúüñ][A-Za-zÁÉÍÓÚÜÑáéíóúüñ' .\-]{1,99}" title="Solo letras, espacios, apóstrofes y guiones (sin números)." class="form-control" value="<?php echo htmlspecialchars($cliente['nombre']); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Apellido *</label>
                                <input type="text" name="apellido" maxlength="100" pattern="[A-Za-zÁÉÍÓÚÜÑáéíóúüñ][A-Za-zÁÉÍÓÚÜÑáéíóúüñ' .\-]{1,99}" title="Solo letras, espacios, apóstrofes y guiones (sin números)." class="form-control" value="<?php echo htmlspecialchars($cliente['apellido']); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Teléfono *</label>
                                <input type="tel" name="telefono" maxlength="20" inputmode="tel" pattern="\+?[0-9 \(\)\-.]{8,20}" title="Solo números (entre 8 y 15 dígitos). Podés usar espacios, guiones, paréntesis y + al inicio." class="form-control" value="<?php echo htmlspecialchars($cliente['telefono']); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Email</label>
                                <input type="email" name="email" maxlength="100" class="form-control" value="<?php echo htmlspecialchars($cliente['email'] ?? ''); ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Dirección</label>
                                <input type="text" name="direccion" maxlength="200" pattern="(?=(?:.*[A-Za-zÀ-ÿ]){3})[A-Za-zÀ-ÿ0-9 .,°º#\/'\-]{5,200}" title="Dirección real: nombre de calle o barrio con letras y, si corresponde, número. Ej: Av. San Martín 450." class="form-control" value="<?php echo htmlspecialchars($cliente['direccion'] ?? ''); ?>">
                            </div>

                            <div class="d-flex justify-content-between gap-2 mt-4">
                                <a href="clientes.php" class="btn btn-secondary w-50">
                                    <i class="bi bi-arrow-left me-1"></i> Cancelar
                                </a>
                                <button type="submit" class="btn btn-warning w-50 fw-bold">
                                    <i class="bi bi-check-lg me-1"></i> Guardar Cambios
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