<?php
require_once '../models/MySQL.php';

    $mysql = new MySQL();
    $mysql->conectar();
    $pdo = $mysql->getConexion();

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

    //link de redirreccion para generar el pdf de la factura
    //aqui

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
}