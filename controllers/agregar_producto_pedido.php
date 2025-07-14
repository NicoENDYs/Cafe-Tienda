<?php
require_once('../models/MySQL.php');

session_start();

if ($_SESSION['rol'] != "mesero") {
    header("Location: ../views/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $mysql = new MySQL();
    $mysql->conectar();
    $pdo = $mysql->getConexion();

    $id_pedido = $_POST['id_pedido'];
    $id_producto = $_POST['id_producto'];
    $cantidad = $_POST['cantidad'];

    // Obtener precio del producto
    $consulta_producto = "SELECT precio FROM productos WHERE id_producto = ?";
    $stmt_producto = $pdo->prepare($consulta_producto);
    $stmt_producto->execute([$id_producto]);
    $producto = $stmt_producto->fetch(PDO::FETCH_ASSOC);
    $precio_unitario = $producto['precio'];

    // Insertar detalle del pedido
    $consulta = "INSERT INTO detalle_pedidos (id_pedido, id_producto, cantidad, precio_unitario) 
                 VALUES (?, ?, ?, ?)";
    $stmt = $pdo->prepare($consulta);
    $stmt->execute([$id_pedido, $id_producto, $cantidad, $precio_unitario]);

    // Actualizar total del pedido
    $consulta_total = "SELECT SUM(subtotal) as nuevo_total FROM detalle_pedidos WHERE id_pedido = ?";
    $stmt_total = $pdo->prepare($consulta_total);
    $stmt_total->execute([$id_pedido]);
    $total = $stmt_total->fetch(PDO::FETCH_ASSOC);

    $actualizar_pedido = "UPDATE pedidos SET total = ? WHERE id_pedido = ?";
    $stmt_actualizar = $pdo->prepare($actualizar_pedido);
    $stmt_actualizar->execute([$total['nuevo_total'], $id_pedido]);

    header("Location: ../mesero/detalle_pedido.php?id=$id_pedido");
    exit();
}