<?php
require_once('../models/MySQL.php');

session_start();

if ($_SESSION['rol'] != "mesero") {
    header("Location: ../views/login.php");
    exit();
}

if (isset($_GET['id_detalle']) && isset($_GET['id_pedido'])) {
    $mysql = new MySQL();
    $mysql->conectar();
    $pdo = $mysql->getConexion();

    $id_detalle = $_GET['id_detalle'];
    $id_pedido = $_GET['id_pedido'];

    // Eliminar el detalle
    $consulta = "DELETE FROM detalle_pedidos WHERE id_detalle_pedido = ?";
    $stmt = $pdo->prepare($consulta);
    $stmt->execute([$id_detalle]);

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