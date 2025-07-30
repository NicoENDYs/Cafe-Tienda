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
    $cantidad = (int)$_POST['cantidad'];

    // 1. Verificar stock disponible
    $consulta_stock = "SELECT stock, precio FROM productos WHERE id_producto = ?";
    $stmt_stock = $pdo->prepare($consulta_stock);
    $stmt_stock->execute([$id_producto]);
    $producto = $stmt_stock->fetch(PDO::FETCH_ASSOC);

    if (!$producto || $producto['stock'] < $cantidad) {
        header("Location: ../mesero/detalle_pedido.php?id=$id_pedido&error=Stock insuficiente");
        exit();
    }

    $precio_unitario = $producto['precio'];

    // 2. Insertar detalle del pedido
    $consulta = "INSERT INTO detalle_pedidos (id_pedido, id_producto, cantidad, precio_unitario) 
                 VALUES (?, ?, ?, ?)";
    $stmt = $pdo->prepare($consulta);
    $stmt->execute([$id_pedido, $id_producto, $cantidad, $precio_unitario]);

    // 3. Actualizar stock del producto
    $actualizar_stock = "UPDATE productos SET stock = stock - ? WHERE id_producto = ?";
    $stmt_stock = $pdo->prepare($actualizar_stock);
    $stmt_stock->execute([$cantidad, $id_producto]);

    // 4. Actualizar total del pedido
    $consulta_total = "SELECT SUM(subtotal) as nuevo_total FROM detalle_pedidos WHERE id_pedido = ?";
    $stmt_total = $pdo->prepare($consulta_total);
    $stmt_total->execute([$id_pedido]);
    $total = $stmt_total->fetch(PDO::FETCH_ASSOC);

    $actualizar_pedido = "UPDATE pedidos SET total = ? WHERE id_pedido = ?";
    $stmt_actualizar = $pdo->prepare($actualizar_pedido);
    $stmt_actualizar->execute([$total['nuevo_total'], $id_pedido]);

    header("Location: ../mesero/detalle_pedido.php?id=$id_pedido&success=Producto agregado correctamente");
    exit();
}