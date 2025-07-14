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


// INGRESOS POR EMPLEADO (RF-14)
$consulta_employee_revenue = "
    SELECT 
        u.nombre AS name,
        u.rol AS role,
        COUNT(v.id_venta) AS sales,
        COALESCE(SUM(v.total), 0) AS revenue
    FROM usuarios u
    LEFT JOIN ventas v ON u.id_usuario = v.id_usuario
    WHERE u.estado = 0
    GROUP BY u.id_usuario, u.nombre, u.rol
    ORDER BY revenue DESC
";

$stmt = $mysql->prepare($consulta_employee_revenue);
$stmt->execute();
$employeeRevenue = [];

while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $employeeRevenue[] = [
        'name' => $fila['name'],
        'role' => ucfirst($fila['role']), // Capitalizar primera letra
        'sales' => (int)$fila['sales'],
        'revenue' => (float)$fila['revenue']
    ];
}

// MESAS ATENDIDAS POR MESERO (RF-15)
$consulta_waiter_tables = "
    SELECT 
        u.nombre AS name,
        COUNT(DISTINCT p.numero_mesa) AS tables,
        COUNT(p.id_pedido) AS orders,
        CASE 
            WHEN COUNT(DISTINCT p.numero_mesa) > 0 
            THEN ROUND(COUNT(p.id_pedido) / COUNT(DISTINCT p.numero_mesa), 2)
            ELSE 0 
        END AS average
    FROM usuarios u
    LEFT JOIN ventas v ON u.id_usuario = v.id_usuario
    LEFT JOIN pedidos p ON v.id_pedido = p.id_pedido
    WHERE u.rol IN ('mesero', 'admin') 
    AND u.estado = 0
    AND p.numero_mesa IS NOT NULL
    GROUP BY u.id_usuario, u.nombre
    HAVING COUNT(p.id_pedido) > 0
    ORDER BY orders DESC
";

$stmt = $mysql->prepare($consulta_waiter_tables);
$stmt->execute();
$waiterTables = [];

while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $waiterTables[] = [
        'name' => $fila['name'],
        'tables' => (int)$fila['tables'],
        'orders' => (int)$fila['orders'],
        'average' => (float)$fila['average']
    ];
}

function obtenerBalanceGeneral($mysql, $startDate, $endDate) {
    $balance = [
        'resumen' => [],
        'ventas_por_dia' => [],
        'productos_vendidos' => [],
        'empleados_ventas' => [],
        'metodos_pago' => [],
        'estadisticas' => []
    ];
    
    // 1. RESUMEN GENERAL
    $consulta_resumen = "
        SELECT 
            COUNT(DISTINCT v.id_venta) as total_ventas,
            COUNT(DISTINCT v.id_pedido) as total_pedidos,
            SUM(v.total) as ingresos_totales,
            AVG(v.total) as promedio_venta,
            MIN(v.total) as venta_minima,
            MAX(v.total) as venta_maxima,
            COUNT(DISTINCT DATE(v.fecha_venta)) as dias_con_ventas
        FROM ventas v
        WHERE DATE(v.fecha_venta) BETWEEN :startDate AND :endDate
    ";
    
    $stmt = $mysql->prepare($consulta_resumen);
    $stmt->bindParam(':startDate', $startDate);
    $stmt->bindParam(':endDate', $endDate);
    $stmt->execute();
    $balance['resumen'] = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // 2. VENTAS POR DÍA
    $consulta_ventas_dia = "
        SELECT 
            DATE(v.fecha_venta) as fecha,
            COUNT(v.id_venta) as num_ventas,
            SUM(v.total) as ingresos_dia,
            AVG(v.total) as promedio_dia
        FROM ventas v
        WHERE DATE(v.fecha_venta) BETWEEN :startDate AND :endDate
        GROUP BY DATE(v.fecha_venta)
        ORDER BY DATE(v.fecha_venta) ASC
    ";
    
    $stmt = $mysql->prepare($consulta_ventas_dia);
    $stmt->bindParam(':startDate', $startDate);
    $stmt->bindParam(':endDate', $endDate);
    $stmt->execute();
    
    while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $balance['ventas_por_dia'][] = [
            'fecha' => $fila['fecha'],
            'num_ventas' => (int)$fila['num_ventas'],
            'ingresos_dia' => (float)$fila['ingresos_dia'],
            'promedio_dia' => (float)$fila['promedio_dia']
        ];
    }
    
    // 3. PRODUCTOS MÁS VENDIDOS EN EL PERÍODO
    $consulta_productos_periodo = "
        SELECT 
            p.nombre,
            p.precio,
            SUM(dv.cantidad) as cantidad_vendida,
            SUM(dv.subtotal) as ingresos_producto,
            COUNT(DISTINCT dv.id_venta) as ventas_involucradas
        FROM detalle_ventas dv
        INNER JOIN productos p ON dv.id_producto = p.id_producto
        INNER JOIN ventas v ON dv.id_venta = v.id_venta
        WHERE DATE(v.fecha_venta) BETWEEN :startDate AND :endDate
        GROUP BY dv.id_producto, p.nombre, p.precio
        ORDER BY cantidad_vendida DESC
    ";
    
    $stmt = $mysql->prepare($consulta_productos_periodo);
    $stmt->bindParam(':startDate', $startDate);
    $stmt->bindParam(':endDate', $endDate);
    $stmt->execute();
    
    while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $balance['productos_vendidos'][] = [
            'nombre' => $fila['nombre'],
            'precio' => (float)$fila['precio'],
            'cantidad_vendida' => (int)$fila['cantidad_vendida'],
            'ingresos_producto' => (float)$fila['ingresos_producto'],
            'ventas_involucradas' => (int)$fila['ventas_involucradas']
        ];
    }
    
    // 4. RENDIMIENTO POR EMPLEADO EN EL PERÍODO
    $consulta_empleados_periodo = "
        SELECT 
            u.nombre,
            u.rol,
            COUNT(v.id_venta) as ventas_realizadas,
            SUM(v.total) as ingresos_generados,
            AVG(v.total) as promedio_por_venta,
            MIN(v.total) as venta_minima,
            MAX(v.total) as venta_maxima
        FROM usuarios u
        INNER JOIN ventas v ON u.id_usuario = v.id_usuario
        WHERE DATE(v.fecha_venta) BETWEEN :startDate AND :endDate
        AND u.estado = 0
        GROUP BY u.id_usuario, u.nombre, u.rol
        ORDER BY ingresos_generados DESC
    ";
    
    $stmt = $mysql->prepare($consulta_empleados_periodo);
    $stmt->bindParam(':startDate', $startDate);
    $stmt->bindParam(':endDate', $endDate);
    $stmt->execute();
    
    while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $balance['empleados_ventas'][] = [
            'nombre' => $fila['nombre'],
            'rol' => $fila['rol'],
            'ventas_realizadas' => (int)$fila['ventas_realizadas'],
            'ingresos_generados' => (float)$fila['ingresos_generados'],
            'promedio_por_venta' => (float)$fila['promedio_por_venta'],
            'venta_minima' => (float)$fila['venta_minima'],
            'venta_maxima' => (float)$fila['venta_maxima']
        ];
    }
    
    // 5. ESTADÍSTICAS ADICIONALES
    $consulta_estadisticas = "
        SELECT 
            DAYNAME(v.fecha_venta) as dia_semana,
            COUNT(v.id_venta) as ventas_dia,
            SUM(v.total) as ingresos_dia,
            HOUR(v.fecha_venta) as hora_venta,
            COUNT(*) as ventas_por_hora
        FROM ventas v
        WHERE DATE(v.fecha_venta) BETWEEN :startDate AND :endDate
        GROUP BY DAYNAME(v.fecha_venta), HOUR(v.fecha_venta)
        ORDER BY ventas_dia DESC
    ";
    
    $stmt = $mysql->prepare($consulta_estadisticas);
    $stmt->bindParam(':startDate', $startDate);
    $stmt->bindParam(':endDate', $endDate);
    $stmt->execute();
    
    while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $balance['estadisticas'][] = [
            'dia_semana' => $fila['dia_semana'],
            'ventas_dia' => (int)$fila['ventas_dia'],
            'ingresos_dia' => (float)$fila['ingresos_dia'],
            'hora_venta' => (int)$fila['hora_venta'],
            'ventas_por_hora' => (int)$fila['ventas_por_hora']
        ];
    }
    
    return $balance;
}
// Definir fechas por defecto
$startDate = isset($_GET['startDate']) ? $_GET['startDate'] : date('Y-m-01');
$endDate = isset($_GET['endDate']) ? $_GET['endDate'] : date('Y-m-d');

$balance_general = obtenerBalanceGeneral($mysql, $startDate, $endDate);
// Agregar el balance general a los datos de JavaScript


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
    <style>
        .modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.7);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 2000;
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.modal-content {
    background: white;
    border-radius: 20px;
    width: 90%;
    max-width: 1200px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    animation: slideIn 0.3s ease;
}

@keyframes slideIn {
    from { transform: translateY(-50px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 2rem;
    border-bottom: 1px solid var(--gray-200);
    background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
    color: white;
    border-radius: 20px 20px 0 0;
}

.modal-header h2 {
    margin: 0;
    font-size: 1.8rem;
    font-weight: 600;
}

.close-modal {
    font-size: 2rem;
    cursor: pointer;
    color: white;
    opacity: 0.8;
    transition: opacity 0.3s ease;
}

.close-modal:hover {
    opacity: 1;
}

.modal-body {
    padding: 2rem;
}

.balance-tabs {
    display: flex;
    gap: 1rem;
    margin-bottom: 2rem;
    border-bottom: 1px solid var(--gray-200);
    flex-wrap: wrap;
}

.tab-btn {
    padding: 1rem 2rem;
    background: none;
    border: none;
    cursor: pointer;
    font-weight: 500;
    color: var(--gray-700);
    border-bottom: 3px solid transparent;
    transition: all 0.3s ease;
    border-radius: 8px 8px 0 0;
}

.tab-btn:hover {
    background: var(--gray-100);
    color: var(--primary-color);
}

.tab-btn.active {
    color: var(--primary-color);
    border-bottom-color: var(--primary-color);
    background: var(--gray-100);
}

.tab-content {
    display: none;
    animation: fadeIn 0.3s ease;
}

.tab-content.active {
    display: block;
}

.balance-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.balance-card {
    background: linear-gradient(135deg, var(--white), var(--gray-100));
    border-radius: 15px;
    padding: 2rem;
    text-align: center;
    box-shadow: 0 8px 25px rgba(var(--accent-rgb), 0.1);
    border-left: 4px solid var(--primary-color);
    transition: transform 0.3s ease;
}

.balance-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(var(--accent-rgb), 0.2);
}

.balance-card h3 {
    color: var(--primary-color);
    font-size: 1.1rem;
    margin-bottom: 1rem;
    font-weight: 600;
}

.balance-value {
    font-size: 2.5rem;
    font-weight: bold;
    color: var(--dark-color);
    margin-bottom: 0.5rem;
}

.table-responsive {
    overflow-x: auto;
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

.balance-table {
    width: 100%;
    border-collapse: collapse;
    background: white;
    border-radius: 10px;
    overflow: hidden;
}

.balance-table th {
    background: var(--primary-color);
    color: white;
    padding: 1.5rem 1rem;
    font-weight: 600;
    text-align: left;
}

.balance-table td {
    padding: 1rem;
    border-bottom: 1px solid var(--gray-200);
    transition: background 0.3s ease;
}

.balance-table tr:hover {
    background: var(--gray-100);
}

.role-badge {
    background: var(--secondary-color);
    color: var(--primary-dark);
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 500;
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
    padding: 2rem;
    border-top: 1px solid var(--gray-200);
    background: var(--gray-100);
    border-radius: 0 0 20px 20px;
}

.btn-export {
    background: linear-gradient(135deg, var(--success-color), #4caf50);
    color: white;
    border: none;
    padding: 1rem 2rem;
    border-radius: 10px;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-export:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(76, 175, 80, 0.4);
}

.btn-close {
    background: var(--gray-700);
    color: white;
    border: none;
    padding: 1rem 2rem;
    border-radius: 10px;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-close:hover {
    background: var(--gray-800);
    transform: translateY(-2px);
}

/* Botón para mostrar balance general */
.balance-btn {
    background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
    color: white;
    border: none;
    padding: 12px 25px;
    border-radius: 25px;
    cursor: pointer;
    font-weight: 600;
    margin: 10px;
    transition: all 0.3s ease;
    display: none;
}

.balance-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(var(--accent-rgb), 0.4);
}

/* Responsive para móviles */
@media (max-width: 768px) {
    .modal-content {
        width: 95%;
        max-height: 95vh;
    }
    
    .modal-header {
        padding: 1.5rem;
    }
    
    .modal-header h2 {
        font-size: 1.5rem;
    }
    
    .modal-body {
        padding: 1.5rem;
    }
    
    .balance-tabs {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .tab-btn {
        padding: 0.75rem 1rem;
        text-align: left;
    }
    
    .balance-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .balance-card {
        padding: 1.5rem;
    }
    
    .balance-value {
        font-size: 2rem;
    }
    
    .modal-footer {
        flex-direction: column;
        gap: 1rem;
    }
    
    .btn-export,
    .btn-close {
        width: 100%;
        justify-content: center;
    }
}

@media (max-width: 480px) {
    .balance-table th,
    .balance-table td {
        padding: 0.75rem 0.5rem;
        font-size: 0.9rem;
    }
    
    .balance-value {
        font-size: 1.8rem;
    }
}
    </style>
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
                    <a href="../controllers/LogOut.php" class="nav-link" data-section="logout">
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
                
            </div>
            <div class="date-selector">
    <input type="date" id="startDate" class="date-input">
    <input type="date" id="endDate" class="date-input">
    <button class="refresh-btn" onclick="updateDashboard()">🔄 Actualizar</button>
    <!-- NUEVO BOTÓN PARA BALANCE GENERAL -->
    <button class="balance-btn" id="balanceBtn" onclick="mostrarBalanceGeneral()">
        📊 Balance General
    </button>
</div>

<!-- También necesitas agregar este script al final de tu dashboard.php -->
<script>
// Mostrar el botón de balance general después de actualizar
document.addEventListener('DOMContentLoaded', function() {
    initializeDates();
    updateDashboard();
    
    // Mostrar botón de balance después de cargar
    setTimeout(() => {
        const balanceBtn = document.getElementById('balanceBtn');
        if (balanceBtn) {
            balanceBtn.style.display = 'inline-block';
        }
    }, 1500);
});
</script>

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

                employeeRevenue: <?php
                                if (!empty($employeeRevenue)) {
                                    echo json_encode($employeeRevenue);
                                } else {
                                    echo json_encode([
                                        ['name' => 'Sin datos', 'role' => 'N/A', 'sales' => 0, 'revenue' => 0]
                                    ]);
                                }
                                ?>,
                waiterTables: <?php
                                if (!empty($waiterTables)) {
                                    echo json_encode($waiterTables);
                                } else {
                                    echo json_encode([
                                        ['name' => 'Sin datos', 'tables' => 0, 'orders' => 0, 'average' => 0]
                                    ]);
                                }
                                ?>
            };

            const balanceGeneral = <?php echo json_encode($balance_general); ?>;

// Función para mostrar el balance general
function mostrarBalanceGeneral() {
    const modalHTML = `
        <div id="balanceModal" class="modal-overlay">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>📊 Balance General de Ventas</h2>
                    <span class="close-modal">&times;</span>
                </div>
                <div class="modal-body">
                    <div class="balance-tabs">
                        <button class="tab-btn active" data-tab="resumen">Resumen</button>
                        <button class="tab-btn" data-tab="diario">Ventas Diarias</button>
                        <button class="tab-btn" data-tab="productos">Productos</button>
                        <button class="tab-btn" data-tab="empleados">Empleados</button>
                    </div>
                    
                    <div class="tab-content active" id="resumen">
                        <div class="balance-grid">
                            <div class="balance-card">
                                <h3>Total Ventas</h3>
                                <div class="balance-value">${balanceGeneral.resumen.total_ventas || 0}</div>
                            </div>
                            <div class="balance-card">
                                <h3>Ingresos Totales</h3>
                                <div class="balance-value">${formatCurrency(balanceGeneral.resumen.ingresos_totales || 0)}</div>
                            </div>
                            <div class="balance-card">
                                <h3>Promedio por Venta</h3>
                                <div class="balance-value">${formatCurrency(balanceGeneral.resumen.promedio_venta || 0)}</div>
                            </div>
                            <div class="balance-card">
                                <h3>Días con Ventas</h3>
                                <div class="balance-value">${balanceGeneral.resumen.dias_con_ventas || 0}</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="tab-content" id="diario">
                        <div class="table-responsive">
                            <table class="balance-table">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Ventas</th>
                                        <th>Ingresos</th>
                                        <th>Promedio</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${balanceGeneral.ventas_por_dia.map(dia => `
                                        <tr>
                                            <td>${formatDate(dia.fecha)}</td>
                                            <td>${dia.num_ventas}</td>
                                            <td>${formatCurrency(dia.ingresos_dia)}</td>
                                            <td>${formatCurrency(dia.promedio_dia)}</td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <div class="tab-content" id="productos">
                        <div class="table-responsive">
                            <table class="balance-table">
                                <thead>
                                    <tr>
                                        <th>Producto</th>
                                        <th>Cantidad</th>
                                        <th>Ingresos</th>
                                        <th>Precio Unit.</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${balanceGeneral.productos_vendidos.map(producto => `
                                        <tr>
                                            <td>${producto.nombre}</td>
                                            <td>${producto.cantidad_vendida}</td>
                                            <td>${formatCurrency(producto.ingresos_producto)}</td>
                                            <td>${formatCurrency(producto.precio)}</td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <div class="tab-content" id="empleados">
                        <div class="table-responsive">
                            <table class="balance-table">
                                <thead>
                                    <tr>
                                        <th>Empleado</th>
                                        <th>Rol</th>
                                        <th>Ventas</th>
                                        <th>Ingresos</th>
                                        <th>Promedio</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${balanceGeneral.empleados_ventas.map(empleado => `
                                        <tr>
                                            <td>${empleado.nombre}</td>
                                            <td><span class="role-badge">${empleado.rol}</span></td>
                                            <td>${empleado.ventas_realizadas}</td>
                                            <td>${formatCurrency(empleado.ingresos_generados)}</td>
                                            <td>${formatCurrency(empleado.promedio_por_venta)}</td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer"> 
                    <button class="btn-close" onclick="cerrarModal()">Cerrar</button>
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHTML);
    
    // Event listeners para tabs
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const tabName = this.dataset.tab;
            
            // Remover active de todos los tabs
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
            
            // Activar tab seleccionado
            this.classList.add('active');
            document.getElementById(tabName).classList.add('active');
        });
    });
    
    // Cerrar modal
    document.querySelector('.close-modal').addEventListener('click', cerrarModal);
    document.querySelector('.modal-overlay').addEventListener('click', function(e) {
        if (e.target === this) cerrarModal();
    });
}

function cerrarModal() {
    const modal = document.getElementById('balanceModal');
    if (modal) {
        modal.remove();
    }
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('es-CO', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

function exportarBalance() {
    // Aquí puedes implementar la exportación a PDF
    alert('Función de exportación en desarrollo');
}

// Actualizar la función updateDashboard para incluir el botón de balance
function updateDashboard() {
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;

    if (!startDate || !endDate) {
        alert('Por favor selecciona un rango de fechas válido');
        return;
    }

    // Mostrar indicador de carga
    document.querySelectorAll('.loading').forEach(el => {
        el.textContent = 'Actualizando datos...';
    });

    // Simular llamada AJAX (en un proyecto real, aquí irían las llamadas a PHP)
    setTimeout(() => {
        updateStats();
        createMonthlyRevenueChart();
        createTopProductsChart();
        updateEmployeeRevenueTable();
        updateWaiterTablesTable();
        
        // Mostrar botón de balance general
        const balanceBtn = document.getElementById('balanceBtn');
        if (balanceBtn) {
            balanceBtn.style.display = 'inline-block';
        }
    }, 1000);
}
        </script>
</body>

</html>