<?php
require_once('../models/MySQL.php');

session_start();

// Verificar autenticación y rol
if ($_SESSION['rol'] != "mesero") {
    header("Location: ../views/login.php");
    exit();
}

// Verificar que se recibieron los parámetros necesarios
if (!isset($_GET['id_detalle']) || !isset($_GET['id_pedido'])) {
    header("Location: ../mesero/dashboard.php?error=Parámetros incorrectos");
    exit();
}

$mysql = new MySQL();
$mysql->conectar();
$pdo = $mysql->getConexion();

// Obtener los parámetros
$id_detalle = (int)$_GET['id_detalle'];
$id_pedido = (int)$_GET['id_pedido'];

try {
    // Iniciar transacción para asegurar la integridad de los datos
    $pdo->beginTransaction();

    // 1. Obtener información del detalle antes de eliminar
    $consulta_detalle = "SELECT id_producto, cantidad FROM detalle_pedidos WHERE id_detalle_pedido = ?";
    $stmt_detalle = $pdo->prepare($consulta_detalle);
    $stmt_detalle->execute([$id_detalle]);
    $detalle = $stmt_detalle->fetch(PDO::FETCH_ASSOC);

    if (!$detalle) {
        throw new Exception("El detalle del pedido no existe");
    }

    // 2. Eliminar el detalle del pedido
    $consulta_eliminar = "DELETE FROM detalle_pedidos WHERE id_detalle_pedido = ?";
    $stmt_eliminar = $pdo->prepare($consulta_eliminar);
    $stmt_eliminar->execute([$id_detalle]);

    // 3. Restaurar el stock del producto
    $consulta_restaurar = "UPDATE productos SET stock = stock + ? WHERE id_producto = ?";
    $stmt_restaurar = $pdo->prepare($consulta_restaurar);
    $stmt_restaurar->execute([$detalle['cantidad'], $detalle['id_producto']]);

    // 4. Recalcular el total del pedido
    $consulta_total = "SELECT SUM(subtotal) as nuevo_total FROM detalle_pedidos WHERE id_pedido = ?";
    $stmt_total = $pdo->prepare($consulta_total);
    $stmt_total->execute([$id_pedido]);
    $total = $stmt_total->fetch(PDO::FETCH_ASSOC);

    // Si no hay más productos, el total será NULL
    $nuevo_total = $total['nuevo_total'] ?? 0;

    // 5. Actualizar el total del pedido
    $consulta_actualizar = "UPDATE pedidos SET total = ? WHERE id_pedido = ?";
    $stmt_actualizar = $pdo->prepare($consulta_actualizar);
    $stmt_actualizar->execute([$nuevo_total, $id_pedido]);

    // Confirmar todas las operaciones
    $pdo->commit();

    // Redirigir con mensaje de éxito
    header("Location: ../mesero/detalle_pedido.php?id=$id_pedido&success=Producto eliminado correctamente");
    exit();

} catch (Exception $e) {
    // Revertir todas las operaciones en caso de error
    $pdo->rollBack();
    
    // Redirigir con mensaje de error
    header("Location: ../mesero/detalle_pedido.php?id=$id_pedido&error=Error al eliminar el producto: " . urlencode($e->getMessage()));
    exit();
}