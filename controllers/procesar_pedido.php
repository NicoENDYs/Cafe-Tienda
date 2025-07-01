<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Solo permitir método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

require_once '../models/MySQL.php';

// Obtener datos del JSON enviado
$input = json_decode(file_get_contents('php://input'), true);

// Validar que se recibieron los datos necesarios
if (!$input || !isset($input['productos']) || empty($input['productos'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Datos de pedido no válidos']);
    exit;
}

$productos = $input['productos'];
$numeroMesa = isset($input['numero_mesa']) ? (int)$input['numero_mesa'] : null;

try {
    $mysql = new MySQL();
    $mysql->conectar();
    $pdo = $mysql->getConexion();
    
    // Iniciar transacción
    $pdo->beginTransaction();
    
    // Calcular total y validar stock
    $total = 0;
    $productosValidados = [];
    
    foreach ($productos as $producto) {
        // Validar estructura del producto
        if (!isset($producto['id']) || !isset($producto['cantidad']) || !isset($producto['precio'])) {
            throw new Exception('Estructura de producto inválida');
        }
        
        $idProducto = (int)$producto['id'];
        $cantidad = (int)$producto['cantidad'];
        $precioUnitario = (float)$producto['precio'];
        
        // Verificar que el producto existe y tiene suficiente stock
        $stmt = $pdo->prepare("SELECT stock, precio FROM productos WHERE id_producto = ? AND estado = 0");
        $stmt->execute([$idProducto]);
        $productoDb = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$productoDb) {
            throw new Exception("Producto con ID {$idProducto} no encontrado");
        }
        
        if ($productoDb['stock'] < $cantidad) {
            throw new Exception("Stock insuficiente para el producto ID {$idProducto}. Disponible: {$productoDb['stock']}, Solicitado: {$cantidad}");
        }
        
        // Usar el precio de la base de datos por seguridad
        $precioReal = (float)$productoDb['precio'];
        $subtotal = $precioReal * $cantidad;
        $total += $subtotal;
        
        $productosValidados[] = [
            'id' => $idProducto,
            'cantidad' => $cantidad,
            'precio_unitario' => $precioReal,
            'subtotal' => $subtotal,
            'stock_disponible' => $productoDb['stock']
        ];
    }
    
    // Crear el pedido principal
    $stmt = $pdo->prepare("INSERT INTO pedidos (total, numero_mesa) VALUES (?, ?)");
    $stmt->execute([$total, $numeroMesa]);
    $idPedido = $pdo->lastInsertId();
    
    // Insertar detalles del pedido y actualizar stock
    foreach ($productosValidados as $producto) {
        // Insertar detalle del pedido
        $stmt = $pdo->prepare("INSERT INTO detalle_pedidos (id_pedido, id_producto, cantidad, precio_unitario) VALUES (?, ?, ?, ?)");
        $stmt->execute([
            $idPedido,
            $producto['id'],
            $producto['cantidad'],
            $producto['precio_unitario']
        ]);
        
        // Actualizar stock del producto
        $stmt = $pdo->prepare("UPDATE productos SET stock = stock - ? WHERE id_producto = ?");
        $stmt->execute([$producto['cantidad'], $producto['id']]);
    }
    
    // Confirmar transacción
    $pdo->commit();
    
    // Respuesta exitosa
    echo json_encode([
        'success' => true,
        'message' => 'Pedido creado exitosamente',
        'data' => [
            'id_pedido' => $idPedido,
            'total' => $total,
            'estado' => 'pendiente',
            'fecha_pedido' => date('Y-m-d H:i:s'),
            'numero_mesa' => $numeroMesa,
            'productos' => $productosValidados
        ]
    ]);
    
} catch (Exception $e) {
    // Revertir transacción en caso de error
    if ($pdo->inTransaction()) {
        $pdo->rollback();
    }
    
    error_log("Error al procesar pedido: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'error' => 'Error al procesar el pedido: ' . $e->getMessage()
    ]);
    
} finally {
    if (isset($mysql)) {
        $mysql->desconectar();
    }
}
?>