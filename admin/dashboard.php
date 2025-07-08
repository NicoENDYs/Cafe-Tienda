<?php
require_once('../models/MySQL.php');

session_start();

if ($_SESSION['rol'] != "admin") {
    header("refresh:1;url=../views/login.php");
    exit();
}

$mysql = new MySQL();
$mysql->conectar();
//ingresos
$consulta = "SELECT SUM(total) AS total FROM ventas;";
$stmt = $mysql->prepare($consulta);
$stmt->execute();
$ventas = $stmt->fetch(PDO::FETCH_ASSOC);
$total_ventas = $ventas['total'] ?? 0;

//VENTAS MES
$consulta_ventas_mes = "
    SELECT 
        DATE_FORMAT(fecha_venta, '%b') AS month,
        SUM(total) AS revenue
    FROM ventas 
    WHERE fecha_venta >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
    GROUP BY DATE_FORMAT(fecha_venta, '%Y-%m'), DATE_FORMAT(fecha_venta, '%b')
    ORDER BY DATE_FORMAT(fecha_venta, '%Y-%m')
";

$stmt = $mysql->prepare($consulta_ventas_mes);
$stmt->execute();
$ventas_por_mes = [];

while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $ventas_por_mes[] = [
        'month' => $fila['month'],
        'revenue' => (float)$fila['revenue']
    ];
}

//TOP PRODUCTOS
$consulta_top_products = "
    SELECT 
        p.nombre AS name,
        SUM(dv.cantidad) AS sales,
        SUM(dv.subtotal) AS revenue
    FROM detalle_ventas dv
    INNER JOIN productos p ON dv.id_producto = p.id_producto
    GROUP BY dv.id_producto, p.nombre
    ORDER BY sales DESC
    LIMIT 8
";

$stmt = $mysql->prepare($consulta_top_products);
$stmt->execute();
$topProducts = [];

while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $topProducts[] = [
        'name' => $fila['name'],
        'sales' => (int)$fila['sales'],
        'revenue' => (float)$fila['revenue']
    ];
}

$mysql->desconectar();
?>

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
    <!---notificaciones--->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../assets/js/notificaciones.js"></script>
</head>

<body>
    <div class="admin-layout">
        <?php include('sidebar.php'); ?>
        <button class="menu-toggle" id="menuToggle">
            <i class="fas fa-bars"></i>
            <span style="margin-left: 10px;">Mi Tienda</span>
        </button>

        <!-- Overlay para cerrar el menú en móvil -->
        <div class="sidebar-overlay" id="sidebarOverlay"></div>

        <!-- Sidebar/Navbar -->
        <nav class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <!-- Botón cerrar para móvil -->
                <!-- <button class="sidebar-close" id="sidebarClose">
            <i class="fas fa-times"></i>
        </button> -->
                <h3><i class="fas fa-store"></i> Mi Tienda</h3>
            </div>
            <ul class="sidebar-nav">
                <li class="nav-item">
                    <a href="./dashboard.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>" data-section="dashboard">
                        <i class="fas fa-tachometer-alt"></i>
                        Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a href="./productos.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'productos.php' ? 'active' : '' ?>" data-section="productos">
                        <i class="fas fa-box"></i>
                        Productos
                    </a>
                </li>
                <!-- Dentro de la barra lateral -->
                <li class="nav-item">
                    <a class="nav-link" href="usuarios.php">
                        <i class="fas fa-users"></i> Usuarios
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link" data-section="reportes">
                        <i class="fas fa-chart-bar"></i>
                        Reportes
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link" data-section="configuracion">
                        <i class="fas fa-cog"></i>
                        Configuración
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link" data-section="logout">
                        <i class="fas fa-sign-out-alt"></i>
                        Cerrar Sesión
                    </a>
                </li>
            </ul>
        </nav>


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
            const dashboardData = {
                stats: {
                    totalRevenue: <?php echo $total_ventas; ?>,
                    totalOrders: 156,
                    totalProducts: 892,
                },
                monthlyRevenue: <?php
                                if (!empty($ventas_por_mes)) {
                                    echo json_encode($ventas_por_mes);
                                } else {
                                    // Caso en el que no hay ventas reales
                                    echo json_encode([
                                        ['month' => 'Ene', 'revenue' => 0],
                                        ['month' => 'Feb', 'revenue' => 0],
                                        ['month' => 'Mar', 'revenue' => 0],
                                        ['month' => 'Abr', 'revenue' => 0],
                                        ['month' => 'May', 'revenue' => 0],
                                        ['month' => 'Jun', 'revenue' => 0]
                                    ]);
                                }
                                ?>,
                topProducts: <?php
                                if (!empty($topProducts)) {
                                    echo json_encode($topProducts);
                                } else {
                                    // Datos de ejemplo si no hay productos vendidos
                                    echo json_encode([
                                        ['name' => 'Sin datos', 'sales' => 0, 'revenue' => 0]
                                    ]);
                                }
                                ?>,

                employeeRevenue: [{
                        name: 'Ana García',
                        role: 'Cajero',
                        sales: 45,
                        revenue: 890000
                    },
                    {
                        name: 'Carlos López',
                        role: 'Mesero',
                        sales: 38,
                        revenue: 750000
                    },
                    {
                        name: 'María Rodríguez',
                        role: 'Cajero',
                        sales: 42,
                        revenue: 820000
                    },
                    {
                        name: 'Juan Pérez',
                        role: 'Mesero',
                        sales: 31,
                        revenue: 590000
                    },
                    {
                        name: 'Laura Martín',
                        role: 'Mesero',
                        sales: 28,
                        revenue: 540000
                    },
                    {
                        name: 'Diego Silva',
                        role: 'Cocinero',
                        sales: 0,
                        revenue: 0
                    },
                    {
                        name: 'Carmen Vega',
                        role: 'Mesero',
                        sales: 25,
                        revenue: 480000
                    },
                    {
                        name: 'Roberto Cruz',
                        role: 'Cocinero',
                        sales: 0,
                        revenue: 0
                    }
                ],
                waiterTables: [{
                        name: 'Carlos López',
                        tables: 24,
                        orders: 38,
                        average: 1.58
                    },
                    {
                        name: 'María Rodríguez',
                        tables: 28,
                        orders: 42,
                        average: 1.50
                    },
                    {
                        name: 'Juan Pérez',
                        tables: 19,
                        orders: 31,
                        average: 1.63
                    },
                    {
                        name: 'Laura Martín',
                        tables: 17,
                        orders: 28,
                        average: 1.65
                    },
                    {
                        name: 'Carmen Vega',
                        tables: 15,
                        orders: 25,
                        average: 1.67
                    }
                ]
            };
        </script>
</body>

</html>