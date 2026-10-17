<?php
// ========================================================
// ARCHIVO: navbar.php
// OBJETIVO: Barra de navegación común para todo el sistema
// ========================================================

// Contar trabajos activos en taller, entregadas, motos y clientes
$totalActivas = 0;
$totalEntregadas = 0;
$totalMotos = 0;
$totalClientes = 0;

try {
    // Trabajos en taller y entregados
    $stmtCount = $pdo->query("SELECT 
        SUM(CASE WHEN estado != 'Entregado' OR estado IS NULL THEN 1 ELSE 0 END) AS activas,
        SUM(CASE WHEN estado = 'Entregado' THEN 1 ELSE 0 END) AS entregadas
        FROM trabajos");
    $counts = $stmtCount->fetch();
    $totalActivas = $counts['activas'] ?? 0;
    $totalEntregadas = $counts['entregadas'] ?? 0;

    // Total de motos registradas
    $totalMotos = $pdo->query("SELECT COUNT(*) FROM motos")->fetchColumn();

    // Total de clientes
    $totalClientes = $pdo->query("SELECT COUNT(*) FROM clientes")->fetchColumn();
} catch (Exception $e) {}

$currentScript = basename($_SERVER['PHP_SELF']);
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4 shadow sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="trabajos.php">
            <span class="fs-4">🏍️</span>
            <span>Taller de Motos</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link <?php echo ($currentScript === 'trabajos.php' || $currentScript === 'index.php') ? 'active fw-bold' : ''; ?>" href="trabajos.php">
                        <i class="bi bi-tools me-1 text-warning"></i> Trabajos en Taller
                        <span class="badge bg-warning text-dark ms-1 rounded-pill"><?php echo $totalActivas; ?></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($currentScript === 'motos.php') ? 'active fw-bold' : ''; ?>" href="motos.php">
                        <i class="bi bi-bicycle me-1 text-info"></i> Motos (ABM)
                        <span class="badge bg-info text-dark ms-1 rounded-pill"><?php echo $totalMotos; ?></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($currentScript === 'entregadas.php') ? 'active fw-bold' : ''; ?>" href="entregadas.php">
                        <i class="bi bi-check2-circle me-1 text-success"></i> Entregadas
                        <span class="badge bg-success ms-1 rounded-pill"><?php echo $totalEntregadas; ?></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($currentScript === 'clientes.php') ? 'active fw-bold' : ''; ?>" href="clientes.php">
                        <i class="bi bi-people me-1 text-light"></i> Clientes
                        <span class="badge bg-secondary ms-1 rounded-pill"><?php echo $totalClientes; ?></span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>
