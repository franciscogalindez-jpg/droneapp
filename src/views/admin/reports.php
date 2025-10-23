<?php include 'views/partials/header.php'; ?>

<div class="container">
    <h2><?= htmlspecialchars($pageTitle) ?></h2>
    
    <div class="row">
        <!-- Usuarios por Rol -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5>Usuarios por Rol</h5>
                </div>
                <div class="card-body">
                    <canvas id="usersByRoleChart"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Drones por Estado -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5>Drones por Estado</h5>
                </div>
                <div class="card-body">
                    <canvas id="dronesByStatusChart"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Ingresos Mensuales -->
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5>Ingresos Mensuales (Últimos 12 meses)</h5>
                </div>
                <div class="card-body">
                    <canvas id="monthlyIncomeChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Scripts para inicializar los gráficos
    document.addEventListener('DOMContentLoaded', function() {
        // Usuarios por Rol
        new Chart(document.getElementById('usersByRoleChart'), {
            type: 'pie',
            data: {
                labels: <?= json_encode(array_column($reportData['users_by_role'], 'nombre_rol')) ?>,
                datasets: [{
                    data: <?= json_encode(array_column($reportData['users_by_role'], 'cantidad')) ?>,
                    backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0']
                }]
            }
        });
        
        // Drones por Estado
        new Chart(document.getElementById('dronesByStatusChart'), {
            type: 'doughnut',
            data: {
                labels: <?= json_encode(array_column($reportData['drones_by_status'], 'estado')) ?>,
                datasets: [{
                    data: <?