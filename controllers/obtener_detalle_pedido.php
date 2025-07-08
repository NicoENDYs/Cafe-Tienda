<?php
require_once('../models/MySQL.php');

session_start();

// Verificar que el usuario esté logueado y tenga el rol correcto
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != "cocina") {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit();
}

// Verificar que se haya enviado el ID del pedido
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID de pedido no proporcionado']);
    exit();
}

$id_pedido = intval($_GET['id']);

try {
    $mysql = new MySQL();
    $mysql->conectar();
    $pdo = $mysql->getConexion();

    // Obtener información del pedido
    $consultaPedido = "SELECT * FROM pedidos WHERE id_pedido = ?";
    $stmtPedido = $pdo->prepare($consultaPedido);
    $stmtPedido->execute([$id_pedido]);
    $pedido = $stmtPedido->fetch(PDO::FETCH_ASSOC);

    if (!$pedido) {
        echo json_encode(['success' => false, 'message' => 'Pedido no encontrado']);
        exit();
    }

    // Obtener productos del pedido con sus detalles
    $consultaProductos = "
        SELECT 
            dp.cantidad,
            dp.precio_unitario,
            dp.subtotal,
            p.nombre,
            p.descripcion,
            c.nombre as categoria
        FROM detalle_pedidos dp
        INNER JOIN productos p ON dp.id_producto = p.id_producto
        INNER JOIN categorias c ON p.id_categoria = c.id_categoria
        WHERE dp.id_pedido = ?
        ORDER BY p.nombre
    ";
    
    $stmtProductos = $pdo->prepare($consultaProductos);
    $stmtProductos->execute([$id_pedido]);
    $productos = $stmtProductos->fetchAll(PDO::FETCH_ASSOC);

    // Formatear la fecha para mostrar mejor
    $pedido['fecha_pedido'] = date('d/m/Y H:i', strtotime($pedido['fecha_pedido']));

    echo json_encode([
        'success' => true,
        'pedido' => $pedido,
        'productos' => $productos
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error interno del servidor: ' . $e->getMessage()]);
}
?>