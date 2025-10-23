<?php include '../../includes/header.php'; ?>

<div class="admin-dashboard">
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <img src="../assets/images/logo-white.png" alt="Logo" width="120" class="mb-2">
                        <h5 class="text-white">Panel de Administración</h5>
                        <p class="text-muted small">Bienvenido, <?= htmlspecialchars($_SESSION['user_name']) ?></p>
                    </div>
                    
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="?page=admin">
                                <i class="bi bi-speedometer2 me-2"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="?page=admin&action=manage_users">
                                <i class="bi bi-people me-2"></i> Usuarios
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="?page=admin&action=manage_drones">
                                <i class="bi bi-drone me-2"></i> Drones
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="?page=admin&action=manage_providers">
                                <i class="bi bi-truck me-2"></i> Proveedores
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="?page=admin&action=reports">
                                <i class="bi bi-graph-up me-2"></i> Reportes
                            </a>
                        </li>
                        <li class="nav-item mt-4">
                            <a class="nav-link text-danger" href="?page=logout">
                                <i class="bi bi-box-arrow-right me-2"></i> Cerrar sesión
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Dashboard</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary">Exportar</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary">Imprimir</button>
                        </div>
                        <button type="button" class="btn btn-sm btn-primary">
                            <i class="bi bi-plus-circle me-1"></i> Nuevo
                        </button>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="row mb-4">
                    <div class="col-md-3 mb-4">
                        <div class="card bg-primary text-white shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title text-uppercase small">Usuarios</h6>
                                        <h2 class="mb-0"><?= $stats['total_users'] ?? 0 ?></h2>
                                    </div>
                                    <i class="bi bi-people fs-1 opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="card bg-success text-white shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title text-uppercase small">Drones</h6>
                                        <h2 class="mb-0"><?= $stats['total_drones'] ?? 0 ?></h2>
                                    </div>
                                    <i class="bi bi-drone fs-1 opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="card bg-info text-white shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title text-uppercase small">Transacciones</h6>
                                        <h2 class="mb-0"><?= $stats['total_transactions'] ?? 0 ?></h2>
                                    </div>
                                    <i class="bi bi-cart-check fs-1 opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="card bg-warning text-dark shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title text-uppercase small">Ingresos</h6>
                                        <h2 class="mb-0">$<?= number_format($stats['total_income'] ?? 0, 2) ?></h2>
                                    </div>
                                    <i class="bi bi-currency-dollar fs-1 opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity and Charts -->
                <div class="row">
                    <div class="col-lg-8">
                        <div class="card shadow-sm mb-4">
                            <div class="card-header">
                                <h6 class="mb-0">Actividad Reciente</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Usuario</th>
                                                <th>Acción</th>
                                                <th>Fecha</th>
                                                <th>Estado</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Datos de actividad reciente -->
                                            <tr>
                                                <td>#1254</td>
                                                <td>usuario@ejemplo.com</td>
                                                <td>Alquiler de dron</td>
                                                <td><?= date('d/m/Y H:i') ?></td>
                                                <td><span class="badge bg-success">Completado</span></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="card shadow-sm mb-4">
                            <div class="card-header">
                                <h6 class="mb-0">Estadísticas Rápidas</h6>
                            </div>
                            <div class="card-body">
                                <canvas id="quickStatsChart" height="250"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gráfico de estadísticas rápidas
    const ctx = document.getElementById('quickStatsChart').getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Alquileres', 'Ventas', 'Mantenimiento'],
            datasets: [{
                data: [65, 25, 10],
                backgroundColor: [
                    '#4361ee',
                    '#4cc9f0',
                    '#f72585'
                ],
                borderWidth: 0
            }]
        },
        options: {
            cutout: '70%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        boxWidth: 12,
                        padding: 20
                    }
                }
            }
        }
    });
});
</script>

<style>
.admin-dashboard {
    background-color: #f8f9fa;
    min-height: 100vh;
}

.sidebar {
    position: fixed;
    top: 0;
    bottom: 0;
    left: 0;
    z-index: 100;
    padding: 48px 0 0;
    box-shadow: inset -1px 0 0 rgba(0, 0, 0, 0.1);
}

.sidebar .nav-link {
    color: #adb5bd;
    padding: 0.75rem 1rem;
    border-radius: 0.25rem;
    margin: 0.25rem 1rem;
}

.sidebar .nav-link.active {
    color: #fff;
    background-color: rgba(255, 255, 255, 0.1);
}

.sidebar .nav-link:hover:not(.active) {
    color: #fff;
    background-color: rgba(255, 255, 255, 0.05);
}

main {
    padding-top: 1.5rem;
}

.card {
    border: none;
    border-radius: 0.5rem;
    overflow: hidden;
}

.card-header {
    background-color: #fff;
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    padding: 1rem 1.25rem;
}

[data-bs-theme="dark"] .sidebar {
    background-color: #212529;
    box-shadow: inset -1px 0 0 rgba(255, 255, 255, 0.1);
}

[data-bs-theme="dark"] .card {
    background-color: #2b3035;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

[data-bs-theme="dark"] .card-header {
    background-color: #2b3035;
    border-bottom-color: rgba(255, 255, 255, 0.1);
}
</style>

<?php include '../../includes/footer.php'; ?>