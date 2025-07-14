<?php
require_once('../models/MySQL.php');

session_start();

if ($_SESSION['rol'] != "mesero") {
    header("refresh:1;url=../views/login.php");
    exit();
} 

$mysql = new MySQL();
$mysql->conectar();
$pdo = $mysql->getConexion();

// Obtener ID del pedido desde la URL
$id_pedido = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Obtener información del pedido
$consulta_pedido = "SELECT * FROM pedidos WHERE id_pedido = ?";
$stmt_pedido = $pdo->prepare($consulta_pedido);
$stmt_pedido->execute([$id_pedido]);
$pedido = $stmt_pedido->fetch(PDO::FETCH_ASSOC);

// Obtener productos del pedido
$consulta_detalle = "SELECT dp.*, p.nombre, p.descripcion 
                     FROM detalle_pedidos dp
                     JOIN productos p ON dp.id_producto = p.id_producto
                     WHERE dp.id_pedido = ?";
$stmt_detalle = $pdo->prepare($consulta_detalle);
$stmt_detalle->execute([$id_pedido]);
$detalles = $stmt_detalle->fetchAll(PDO::FETCH_ASSOC);

// Obtener todos los productos disponibles para agregar
$consulta_productos = "SELECT * FROM productos WHERE estado = 0";
$productos = $pdo->query($consulta_productos)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle del Pedido #<?php echo $id_pedido; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/mesero_dashboard.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
<div class="admin-layout">
    <nav class="sidebar">
        <div class="sidebar-header">
            <h3><i class="fas fa-store"></i>Pedidos por:</h3>
        </div>
        <ul class="sidebar-nav">
            <li class="nav-item">
                <a href="../mesero/dashboard.php" class="nav-link" data-section="confirmar">
                    <i class="fas fa-circle-check"></i>
                    Confirmar
                </a>
            </li>
            <li class="nav-item">
                <a href="../mesero/entregar.php" class="nav-link" data-section="entregar">
                    <i class="fas fa-utensils"></i>
                    Entregar
                </a>
            </li>
        </ul>
    </nav>

    <div class="main-content">
        <div class="page-header">
            <h1><i class="fas fa-list"></i> Detalle del Pedido #<?php echo $id_pedido; ?></h1>
            <p>Mesa: <?php echo $pedido['numero_mesa']; ?> | Estado: <?php echo ucfirst($pedido['estado']); ?></p>
        </div>
        
        <div class="row">
            <div class="col">
                <div class="card">
                    <div class="card-header">
                        <h3>Productos en el pedido</h3>
                    </div>
                    <div class="card-body">
                        <table>
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th>Descripción</th>
                                    <th>Cantidad</th>
                                    <th>Precio Unitario</th>
                                    <th>Subtotal</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($detalles as $detalle): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($detalle['nombre']); ?></td>
                                    <td><?php echo htmlspecialchars($detalle['descripcion']); ?></td>
                                    <td><?php echo $detalle['cantidad']; ?></td>
                                    <td>$<?php echo number_format($detalle['precio_unitario'], 2); ?></td>
                                    <td>$<?php echo number_format($detalle['subtotal'], 2); ?></td>
                                    <td>
                                        <button class="btn-custom eliminar" 
                                                onclick="eliminarProducto(<?php echo $detalle['id_detalle_pedido']; ?>, <?php echo $id_pedido; ?>)">
                                            <i class="fas fa-trash"></i> Eliminar
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <tr class="total-row">
                                    <td colspan="4" class="text-right"><strong>Total:</strong></td>
                                    <td><strong>$<?php echo number_format($pedido['total'], 2); ?></strong></td>
                                    <td></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card mt-4">
                    <div class="card-header">
                        <h3>Agregar Productos</h3>
                    </div>
                    <div class="card-body">
                        <form id="formAgregarProducto" action="../controllers/agregar_producto_pedido.php" method="POST">
                            <input type="hidden" name="id_pedido" value="<?php echo $id_pedido; ?>">
                            <div class="form-group">
                                <label for="id_producto">Producto:</label>
                                <select name="id_producto" id="id_producto" class="form-control" required>
                                    <option value="">Seleccione un producto</option>
                                    <?php foreach ($productos as $producto): ?>
                                    <option value="<?php echo $producto['id_producto']; ?>">
                                        <?php echo htmlspecialchars($producto['nombre']); ?> - $<?php echo number_format($producto['precio'], 2); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="cantidad">Cantidad:</label>
                                <input type="number" name="cantidad" id="cantidad" class="form-control" min="1" value="1" required>
                            </div>
                            <button type="submit" class="btn-custom confirmar">
                                <i class="fas fa-plus"></i> Agregar Producto
                            </button>
                        </form>
                    </div>
                </div>

                <div class="actions mt-4">
                    <button class="btn-custom confirmar" onclick="confirmarPedido(<?php echo $id_pedido; ?>)">
                        <i class="fas fa-check-circle"></i> Confirmar Pedido
                    </button>
                    <button class="btn-custom cancelar" onclick="window.location.href='dashboard.php'">
                        <i class="fas fa-arrow-left"></i> Volver
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function eliminarProducto(id_detalle, id_pedido) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: "¡No podrás revertir esto!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, eliminar!'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `../controllers/eliminar_producto_pedido.php?id_detalle=${id_detalle}&id_pedido=${id_pedido}`;
        }
    });
}

function confirmarPedido(id_pedido) {
    Swal.fire({
        title: 'Confirmar Pedido',
        text: "¿Estás seguro de confirmar este pedido?",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, confirmar!'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `../controllers/confirmar_pedido.php?id_pedido=${id_pedido}`;
        }
    });
}
</script>
</body>
</html>