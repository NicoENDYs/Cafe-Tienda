<?php
require_once '../models/MySQL.php';

    $mysql = new MySQL();
    $mysql->conectar();
    $pdo = $mysql->getConexion();

if ($_SERVER["REQUEST_METHOD"] == "GET") {

    $accion = $_GET['accion'];

    if ($accion == 'entregado') {
        $id_pedido = $_GET['id'];
        $consulta = "UPDATE pedidos SET estado = 'entregado' WHERE id_pedido = :id_pedido";
        $stmt = $pdo->prepare($consulta);
        $stmt->execute([':id_pedido' => $id_pedido]);
        header("Location: ../mesero/entregar.php?estado=exito&mensaje=Pedido Entregado");
    }

    if ($accion == 'confirmar') {
        // Lógica para confirmar el pedido
        $id_pedido = $_GET['id'];
        $consulta = "UPDATE pedidos SET estado = 'confirmado' WHERE id_pedido = :id_pedido";
        $stmt = $pdo->prepare($consulta);
        $stmt->execute([':id_pedido' => $id_pedido]);
        header("Location: ../mesero/dashboard.php?estado=exito&mensaje=Pedido confirmado");
    } elseif ($accion == 'cancelar') {
        // Lógica para cancelar el pedido
        $id_pedido = $_GET['id'];
        $consulta = "UPDATE pedidos SET estado = 'cancelado' WHERE id_pedido = :id_pedido";
        $stmt = $pdo->prepare($consulta);
        $stmt->execute([':id_pedido' => $id_pedido]);
        header("Location: ../mesero/dashboard.php?estado=exito&mensaje=Pedido cancelado");
    } else {
        header("Location: ../mesero/dashboard.php?estado=error&mensaje=Acción no válida");
    }
}