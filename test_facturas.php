<?php
require_once 'models/MySQL.php';

$mysql = new MySQL();
$mysql->conectar();
$pdo = $mysql->getConexion();

echo "<h2>Verificando pedidos entregados...</h2>";

// Verificar si hay pedidos entregados (sin apellido)
$consulta_pedidos = "SELECT p.id_pedido, p.estado, p.fecha_pedido, p.numero_mesa
                     FROM pedidos p
                     WHERE p.estado = 'entregado' OR p.estado = 'listo'";
$stmt_pedidos = $pdo->prepare($consulta_pedidos);
$stmt_pedidos->execute();
$pedidos = $stmt_pedidos->fetchAll(PDO::FETCH_ASSOC);

echo "<p>Pedidos entregados o listos encontrados: " . count($pedidos) . "</p>";

if (count($pedidos) > 0) {
    echo "<h3>Lista de pedidos entregados o listos:</h3>";
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>ID Pedido</th><th>Mesa</th><th>Fecha</th><th>Estado</th></tr>";
    
    foreach ($pedidos as $pedido) {
        echo "<tr>";
        echo "<td>" . $pedido['id_pedido'] . "</td>";
        echo "<td>" . ($pedido['numero_mesa'] ?? 'N/A') . "</td>";
        echo "<td>" . $pedido['fecha_pedido'] . "</td>";
        echo "<td>" . $pedido['estado'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// Verificar si hay ventas de pedidos entregados o listos
$consulta_ventas = "SELECT v.id_venta, v.id_pedido, v.total, v.fecha_venta
                    FROM ventas v
                    JOIN pedidos p ON v.id_pedido = p.id_pedido
                    WHERE p.estado = 'entregado' OR p.estado = 'listo'
                    ORDER BY v.fecha_venta DESC";
$stmt_ventas = $pdo->prepare($consulta_ventas);
$stmt_ventas->execute();
$ventas = $stmt_ventas->fetchAll(PDO::FETCH_ASSOC);

echo "<h3>Ventas de pedidos entregados o listos encontradas: " . count($ventas) . "</h3>";

if (count($ventas) > 0) {
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>ID Venta</th><th>ID Pedido</th><th>Total</th><th>Fecha Venta</th></tr>";
    
    foreach ($ventas as $venta) {
        echo "<tr>";
        echo "<td>" . $venta['id_venta'] . "</td>";
        echo "<td>" . $venta['id_pedido'] . "</td>";
        echo "<td>$" . number_format($venta['total'], 2) . "</td>";
        echo "<td>" . $venta['fecha_venta'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Probar generar una factura
    if (count($ventas) > 0) {
        echo "<h3>Probando generar factura...</h3>";
        
        $venta = $ventas[0]; // Tomar la primera venta
        
        // Obtener detalles del pedido
        $consulta_detalles = "SELECT dp.id_producto, dp.cantidad, dp.precio_unitario, p.nombre_producto
                              FROM detalle_pedidos dp
                              JOIN productos p ON dp.id_producto = p.id_producto
                              WHERE dp.id_pedido = :id_pedido";
        $stmt_detalles = $pdo->prepare($consulta_detalles);
        $stmt_detalles->execute([':id_pedido' => $venta['id_pedido']]);
        $detalles_pedido = $stmt_detalles->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<p>Detalles del pedido encontrados: " . count($detalles_pedido) . "</p>";
        
        if (count($detalles_pedido) > 0) {
            echo "<table border='1' style='border-collapse: collapse;'>";
            echo "<tr><th>Producto</th><th>Cantidad</th><th>Precio Unit.</th></tr>";
            
            foreach ($detalles_pedido as $detalle) {
                echo "<tr>";
                echo "<td>" . $detalle['nombre_producto'] . "</td>";
                echo "<td>" . $detalle['cantidad'] . "</td>";
                echo "<td>$" . number_format($detalle['precio_unitario'], 2) . "</td>";
                echo "</tr>";
            }
            echo "</table>";
            
            // Intentar generar la factura
            try {
                // Nueva función de factura
                function generarFacturaTest($id_venta, $id_pedido, $total, $detalles_pedido, $pdo) {
                    $consulta_pedido = "SELECT fecha_pedido, numero_mesa FROM pedidos WHERE id_pedido = :id_pedido";
                    $stmt_pedido = $pdo->prepare($consulta_pedido);
                    $stmt_pedido->execute([':id_pedido' => $id_pedido]);
                    $info_pedido = $stmt_pedido->fetch(PDO::FETCH_ASSOC);

                    $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Factura #' . $id_venta . '</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { text-align: center; margin-bottom: 30px; }
        .title { font-size: 24px; font-weight: bold; color: #333; }
        .info { margin-bottom: 20px; }
        .table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .table th, .table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .total { font-weight: bold; font-size: 16px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">CAFE TIENDA</div>
        <div>Factura de Venta</div>
    </div>
    
    <div class="info">
        <div><strong>No. Factura:</strong> ' . $id_venta . '</div>
        <div><strong>No. Pedido:</strong> ' . $id_pedido . '</div>
        <div><strong>Fecha:</strong> ' . date('d/m/Y H:i', strtotime($info_pedido['fecha_pedido'])) . '</div>
        <div><strong>Mesa:</strong> ' . ($info_pedido['numero_mesa'] ?? 'N/A') . '</div>
    </div>
    
    <table class="table">
        <thead>
            <tr>
                <th>PRODUCTO</th>
                <th>CANTIDAD</th>
                <th>PRECIO UNIT.</th>
                <th>SUBTOTAL</th>
            </tr>
        </thead>
        <tbody>';
                    
                    foreach ($detalles_pedido as $producto) {
                        $subtotal = $producto['cantidad'] * $producto['precio_unitario'];
                        $html .= '<tr>
                            <td>' . $producto['nombre_producto'] . '</td>
                            <td>' . $producto['cantidad'] . '</td>
                            <td>$' . number_format($producto['precio_unitario'], 2) . '</td>
                            <td>$' . number_format($subtotal, 2) . '</td>
                        </tr>';
                    }
                    
                    $html .= '</tbody>
    </table>
    
    <div class="total">
        <strong>TOTAL: $' . number_format($total, 2) . '</strong>
    </div>
    
    <div style="text-align: center; margin-top: 30px; color: #666;">
        <p>Gracias por su compra!</p>
        <p>Cafe Tienda - Sistema de Ventas</p>
    </div>
</body>
</html>';
                    
                    // Guardar archivo HTML
                    $filename = "assets/facturas/factura_venta_" . $id_venta . ".html";
                    file_put_contents($filename, $html);
                    
                    return $filename;
                }
                
                $filename = generarFacturaTest($venta['id_venta'], $venta['id_pedido'], $venta['total'], $detalles_pedido, $pdo);
                echo "<p style='color: green;'>✅ Factura generada exitosamente: $filename</p>";
                echo "<p><a href='$filename' target='_blank'>Ver factura</a></p>";
                
            } catch (Exception $e) {
                echo "<p style='color: red;'>❌ Error generando factura: " . $e->getMessage() . "</p>";
            }
        }
    }
} else {
    echo "<p style='color: orange;'>⚠️ No hay ventas de pedidos entregados o listos en la base de datos.</p>";
}
?> 