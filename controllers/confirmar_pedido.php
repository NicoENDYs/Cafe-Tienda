<?php
require_once('../models/MySQL.php');

session_start();

if ($_SESSION['rol'] != "mesero") {
    header("Location: ../views/login.php");
    exit();
}

if (isset($_GET['id_pedido'])) {
    $mysql = new MySQL();
    $mysql->conectar();
    $pdo = $mysql->getConexion();

    $id_pedido = $_GET['id_pedido'];

    // Verificar que el pedido tenga productos
    $consulta_detalle = "SELECT COUNT(*) as total FROM detalle_pedidos WHERE id_pedido = ?";
    $stmt_detalle = $pdo->prepare($consulta_detalle);
    $stmt_detalle->execute([$id_pedido]);
    $resultado = $stmt_detalle->fetch(PDO::FETCH_ASSOC);

    if ($resultado['total'] > 0) {
        // Actualizar estado del pedido
        $consulta = "UPDATE pedidos SET estado = 'confirmado' WHERE id_pedido = ?";
        $stmt = $pdo->prepare($consulta);
        $stmt->execute([$id_pedido]);

        header("Location: ../mesero/dashboard.php?success=Pedido confirmado correctamente");
    } else {
        header("Location: ../mesero/detalle_pedido.php?id=$id_pedido&error=El pedido no puede estar vacío");
    }
    exit();
}