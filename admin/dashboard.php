<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Administrativo</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/admin_dashboard.css">
</head>
<body>
     <div class="admin-layout">
        <?php include('sidebar.php'); ?>
        
        <div class="main-content">
            <div class="header">
                <h1>☕ Dashboard Administrativo</h1>
                <p>Café & Bebidas El Buen Sabor - Panel de Control</p>
                <div class="date-selector">
                    <input type="date" id="startDate" class="date-input">
                    <input type="date" id="endDate" class="date-input">
                    <button class="refresh-btn" onclick="updateDashboard()">🔄 Actualizar</button>
                </div>
            </div>

        <!-- Tarjetas de Estadísticas -->
        <div class="stats-grid">
            <div class="stat-card revenue">
                <div class="stat-value" id="totalRevenue">$0</div>
                <div class="stat-label">Ingresos Totales</div>  
            </div>
            <div class="stat-card orders">
                <div class="stat-value" id="totalOrders">0</div>
                <div class="stat-label">Pedidos Completados</div>
            </div>
            <div class="stat-card products">
                <div class="stat-value" id="totalProducts">0</div>
                <div class="stat-label">Productos Vendidos</div>
            </div>
            <div class="stat-card employees">
                <div class="stat-value" id="activeEmployees">0</div>
                <div class="stat-label">Empleados Activos</div>
            </div>
        </div>

        <!-- Gráficos -->
        <div class="charts-grid">
            <!-- RF-12: Recaudo Mensual -->
            <div class="chart-container">
                <h3 class="chart-title">📊 Recaudo Mensual</h3>
                <div class="chart-wrapper">
                    <canvas id="monthlyRevenueChart"></canvas>
                </div>
            </div>

            <!-- RF-13: Productos Más Vendidos -->
            <div class="chart-container">
                <h3 class="chart-title">🏆 Productos Más Vendidos</h3>
                <div class="chart-wrapper">
                    <canvas id="topProductsChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Tablas de Datos -->
        <div class="tables-grid">
            <!-- RF-14: Ingresos por Empleado -->
            <div class="table-container">
                <h3 class="table-title">💰 Ingresos por Empleado</h3>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Empleado</th>
                            <th>Rol</th>
                            <th>Ventas</th>
                            <th>Ingresos</th>
                        </tr>
                    </thead>
                    <tbody id="employeeRevenueTable">
                        <tr>
                            <td colspan="4" class="loading">Cargando datos...</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- RF-15: Mesas Atendidas por Mesero -->
            <div class="table-container">
                <h3 class="table-title">🍽️ Mesas Atendidas por Mesero</h3>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Mesero</th>
                            <th>Mesas</th>
                            <th>Pedidos</th>
                            <th>Promedio</th>
                        </tr>
                    </thead>
                    <tbody id="waiterTablesTable">
                        <tr>
                            <td colspan="4" class="loading">Cargando datos...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script src="../assets/js/admin_dashboard.js"></script>
    <script>
        // Inicializar el dashboard al cargar la página
        document.addEventListener('DOMContentLoaded', function() {
            updateDashboard();
        });
    </script>
</body>

</html>