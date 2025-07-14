<?php
require_once '../models/MySQL.php';
require_once '../assets/fpdf186/fpdf.php';

// (Eliminada la clase FPDF personalizada, solo queda el require_once y la función generarFacturaPDF)

    $mysql = new MySQL();
    $mysql->conectar();
    $pdo = $mysql->getConexion();

// Función para generar factura en formato HTML
function generarFacturaPDF($id_venta, $id_pedido, $total, $detalles_pedido, $pdo) {
    // Obtener información del pedido
    $consulta_pedido = "SELECT fecha_pedido, numero_mesa FROM pedidos WHERE id_pedido = :id_pedido AND estado = 'entregado'";
    $stmt_pedido = $pdo->prepare($consulta_pedido);
    $stmt_pedido->execute([':id_pedido' => $id_pedido]);
    $info_pedido = $stmt_pedido->fetch(PDO::FETCH_ASSOC);
    if (!$info_pedido) {
        return false;
    }

    // Obtener detalles de productos
    $consulta_productos = "SELECT p.nombre, dp.cantidad, dp.precio_unitario, (dp.cantidad * dp.precio_unitario) as subtotal
                           FROM detalle_pedidos dp
                           JOIN productos p ON dp.id_producto = p.id_producto
                           WHERE dp.id_pedido = :id_pedido";
    $stmt_productos = $pdo->prepare($consulta_productos);
    $stmt_productos->execute([':id_pedido' => $id_pedido]);
    $productos = $stmt_productos->fetchAll(PDO::FETCH_ASSOC);

    // Crear PDF
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(0, 10, 'CAFE TIENDA', 0, 1, 'C');
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 8, 'Factura de Venta', 0, 1, 'C');
    $pdf->Ln(5);

    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(40, 8, 'No. Factura: ' . $id_venta, 0, 1);
    $pdf->Cell(40, 8, 'No. Pedido: ' . $id_pedido, 0, 1);
    $pdf->Cell(40, 8, 'Fecha: ' . date('d/m/Y H:i', strtotime($info_pedido['fecha_pedido'])), 0, 1);
    $pdf->Cell(40, 8, 'Mesa: ' . ($info_pedido['numero_mesa'] ?? 'N/A'), 0, 1);
    $pdf->Ln(5);

    // Tabla de productos
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(60, 8, 'PRODUCTO', 1);
    $pdf->Cell(25, 8, 'CANTIDAD', 1);
    $pdf->Cell(35, 8, 'PRECIO UNIT.', 1);
    $pdf->Cell(35, 8, 'SUBTOTAL', 1);
    $pdf->Ln();

    $pdf->SetFont('Arial', '', 10);
    foreach ($productos as $producto) {
        $pdf->Cell(60, 8, $producto['nombre'], 1);
        $pdf->Cell(25, 8, $producto['cantidad'], 1);
        $pdf->Cell(35, 8, '$' . number_format($producto['precio_unitario'], 2), 1);
        $pdf->Cell(35, 8, '$' . number_format($producto['subtotal'], 2), 1);
        $pdf->Ln();
    }

    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(120, 10, 'TOTAL:', 1);
    $pdf->Cell(35, 10, '$' . number_format($total, 2), 1);
    $pdf->Ln(15);

    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(0, 8, 'Gracias por su compra!', 0, 1, 'C');
    $pdf->Cell(0, 8, 'Cafe Tienda - Sistema de Ventas', 0, 1, 'C');

    // Guardar PDF
    $filename = "../assets/facturas/factura_venta_" . $id_venta . ".pdf";
    $pdf->Output('F', $filename);

    return $filename;
}

if ($_SERVER["REQUEST_METHOD"] == "GET") {

    $accion = $_GET['accion'];

    if ($accion == 'entregado') {
    $id_pedido = $_GET['id'];
    
    // 1. Actualizar el estado del pedido
    $consulta = "UPDATE pedidos SET estado = 'entregado' WHERE id_pedido = :id_pedido";
    $stmt = $pdo->prepare($consulta);
    $stmt->execute([':id_pedido' => $id_pedido]);

    // 2. Insertar la venta principal
    $consulta2 = "INSERT INTO ventas (id_pedido, id_usuario, fecha_venta, total) VALUES (:id_pedido, :id_usuario, NOW(), :total)";
    $stmt2 = $pdo->prepare($consulta2);
    $stmt2->execute([':id_pedido' => $id_pedido, ':id_usuario' => $_GET['id_usuario'], ':total' => $_GET['total']]);
    
    // Obtener el ID de la venta recién insertada
    $id_venta = $pdo->lastInsertId();

    // 3. Obtener los detalles del pedido
    $consulta3 = "SELECT id_producto, cantidad, precio_unitario FROM detalle_pedidos WHERE id_pedido = :id_pedido";
    $stmt3 = $pdo->prepare($consulta3);
    $stmt3->execute([':id_pedido' => $id_pedido]);
    $detalles_pedido = $stmt3->fetchAll(PDO::FETCH_ASSOC);

    // 4. Insertar los detalles en details_ventas
    foreach ($detalles_pedido as $detalle) {
        
        $consulta4 = "INSERT INTO detalle_ventas (id_venta, id_producto, cantidad, precio_unitario) 
                      VALUES (:id_venta, :id_producto, :cantidad, :precio_unitario)";
        $stmt4 = $pdo->prepare($consulta4);
        $stmt4->execute([
            ':id_venta' => $id_venta,
            ':id_producto' => $detalle['id_producto'],
            ':cantidad' => $detalle['cantidad'],
            ':precio_unitario' => $detalle['precio_unitario'],
        ]);
    }

    // 5. Generar factura automáticamente
    try {
        $ruta_factura = generarFacturaPDF($id_venta, $id_pedido, $_GET['total'], $detalles_pedido, $pdo);
        // Insertar la ruta en la tabla facturas_generadas
        if ($ruta_factura) {
            $consulta_factura = "INSERT INTO facturas_generadas (archivo_url) VALUES (:archivo_url)";
            $stmt_factura = $pdo->prepare($consulta_factura);
            // Guardar la ruta relativa desde la raíz del proyecto
            $ruta_relativa = str_replace("../", "", $ruta_factura);
            $stmt_factura->execute([':archivo_url' => $ruta_relativa]);
        }
    } catch (Exception $e) {
        // Si hay error al generar factura, continuar sin interrumpir el flujo
        error_log("Error generando factura para venta $id_venta: " . $e->getMessage());
    }

    header("Location: ../mesero/entregar.php?estado=exito&mensaje=Pedido Entregado");
}

    if ($accion == 'listo') {
        $id_pedido = $_GET['id'];
        $consulta = "UPDATE pedidos SET estado = 'listo' WHERE id_pedido = :id_pedido";
        $stmt = $pdo->prepare($consulta);
        $stmt->execute([':id_pedido' => $id_pedido]);
        header("Location: ../cocina/dashboard.php?estado=exito&mensaje=Pedido Entregado");
    }

    if ($accion == 'confirmar') {
        // Lógica para confirmar el pedido
        $id_pedido = $_GET['id'];
        $consulta = "UPDATE pedidos SET estado = 'confirmado' WHERE id_pedido = :id_pedido";
        $stmt = $pdo->prepare($consulta);
        $stmt->execute([':id_pedido' => $id_pedido]);
        header("Location: ../mesero/dashboard.php?estado=exito&mensaje=Pedido confirmado");
    } 

    if ($accion == 'cancelar') {
        // Lógica para cancelar el pedido
        $id_pedido = $_GET['id'];
        $consulta = "UPDATE pedidos SET estado = 'cancelado' WHERE id_pedido = :id_pedido";
        $stmt = $pdo->prepare($consulta);
        $stmt->execute([':id_pedido' => $id_pedido]);
        header("Location: ../mesero/dashboard.php?estado=exito&mensaje=Pedido cancelado");
    } 

    if ($accion == 'generar_facturas_existentes') {
        // Buscar todos los pedidos entregados que ya tienen ventas
        $consulta_ventas = "SELECT v.id_venta, v.id_pedido, v.total, v.fecha_venta
                            FROM ventas v
                            JOIN pedidos p ON v.id_pedido = p.id_pedido
                            WHERE p.estado = 'entregado'
                            ORDER BY v.fecha_venta DESC";
        $stmt_ventas = $pdo->prepare($consulta_ventas);
        $stmt_ventas->execute();
        $ventas = $stmt_ventas->fetchAll(PDO::FETCH_ASSOC);

        $generadas = 0;
        $errores = 0;

        foreach ($ventas as $venta) {
            try {
                // Verificar si ya existe la factura
                $filename = "../assets/facturas/factura_venta_" . $venta['id_venta'] . ".html";
                
                if (file_exists($filename)) {
                    continue; // Ya existe, saltar
                }
                
                // Obtener detalles del pedido
                $consulta_detalles = "SELECT id_producto, cantidad, precio_unitario 
                                      FROM detalle_pedidos 
                                      WHERE id_pedido = :id_pedido";
                $stmt_detalles = $pdo->prepare($consulta_detalles);
                $stmt_detalles->execute([':id_pedido' => $venta['id_pedido']]);
                $detalles_pedido = $stmt_detalles->fetchAll(PDO::FETCH_ASSOC);
                
                // Generar factura
                generarFacturaPDF($venta['id_venta'], $venta['id_pedido'], $venta['total'], $detalles_pedido, $pdo);
                $generadas++;
                
            } catch (Exception $e) {
                error_log("Error generando factura para venta {$venta['id_venta']}: " . $e->getMessage());
                $errores++;
            }
        }

        header("Location: ../mesero/dashboard.php?estado=exito&mensaje=Se generaron $generadas facturas para pedidos entregados existentes");
    }
}