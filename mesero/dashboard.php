
<?php
require_once('../models/MySQL.php');

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
    <link rel="stylesheet" href="../assets/css/mesero_dashboard.css">
        <!---notificaciones--->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../assets/js/notificaciones.js"></script>
    
</head>

<body>
<div class="admin-layout">
        <nav class="sidebar">
            <div class="sidebar-header">
                <h3><i class="fas fa-store"></i>Pedidos</h3>
            </div>
        </nav>

    <div class="main-content">
        <div class="row">
            <div class="col">
                <h2>Top Productos Vendidos</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Ventas</th>
                            <th>Ingresos</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!---se renderizaran los pedidos--->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>


</body>
</html>