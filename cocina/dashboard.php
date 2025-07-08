
<?php
require_once('../models/MySQL.php');

session_start();

if ($_SESSION['rol'] != "cocina") {
header("refresh:1;url=../views/login.php");
exit();
} 

$mysql = new MySQL();
$mysql->conectar();
$pdo = $mysql->getConexion();

$consulta = "select * from pedidos where estado = 'confirmado'";
$stmt = $pdo->prepare($consulta);
$stmt->execute(); 

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Administrativo</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/cocina_dashboard.css">
        <!---notificaciones--->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../assets/js/notificaciones.js"></script>
    
</head>

<body>
<div class="admin-layout">
        <nav class="sidebar">
            <div class="sidebar-header">
                <h3><i class="fas fa-store"></i>Cocina:</h3>
            </div>
        <!-- <ul class="sidebar-nav">    
        <li class="nav-item">
            <a href="../mesero/dashboard.php" class="nav-link" data-section="confirmar">
                <i class="fas fa-cog"></i>
                Confirmar
            </a>
        </li>
        <li class="nav-item">
            <a href="../mesero/entregar.php" class="nav-link" data-section="entregar">
                <i class="fas fa-cog"></i>
                Entregar
            </a>
        </li>
    </ul> -->
        </nav>  

    <div class="main-content">
        <div class="row">
            <div class="col">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>TOTAL</th>
                            <th>ESTADO</th>
                            <th>FECHA</th>
                            <th>NUMERO MESA</th>
                            <th>ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!---se renderizaran los pedidos--->
                        <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                            <tr>
                                <td><?php echo $row['id_pedido']; ?></td>
                                <td><?php echo $row['total']; ?></td>
                                <td><?php echo $row['estado']; ?></td>
                                <td><?php echo $row['fecha_pedido']; ?></td>
                                <td><?php echo $row['numero_mesa']; ?></td>
                                <td>
                                    <button class="btn-ver-pedido" onclick="verDetallePedido(<?php echo $row['id_pedido']; ?>)">
                                        <i class="fas fa-eye"></i>Ver Pedido
                                    </button>   
                                    <button class="btn-custom confirmar" onclick="redirigir('listo', <?php echo $row['id_pedido']; ?>)">
                                    <i class="fas fa-edit"></i> listo
                                </button>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>


<!-- Modal para mostrar detalles del pedido -->
<div id="modalPedido" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 class="modal-title">Detalles del Pedido</h2>
            <span class="close" onclick="cerrarModal()">&times;</span>
        </div>
        <div id="contenidoModal">
            <div class="loading">
                <i class="fas fa-spinner fa-spin"></i> Cargando...
            </div>
        </div>
    </div>
</div>

<script>
function verDetallePedido(idPedido) {
    // Mostrar modal
    document.getElementById('modalPedido').style.display = 'block';
    
    // Mostrar loading
    document.getElementById('contenidoModal').innerHTML = `
        <div class="loading">
            <i class="fas fa-spinner fa-spin"></i> Cargando detalles del pedido...
        </div>
    `;
    
    // Hacer petición AJAX para obtener detalles
    fetch(`../controllers/obtener_detalle_pedido.php?id=${idPedido}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                mostrarDetallePedido(data.pedido, data.productos);
            } else {
                document.getElementById('contenidoModal').innerHTML = `
                    <div class="error">
                        <i class="fas fa-exclamation-triangle"></i> Error: ${data.message}
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('contenidoModal').innerHTML = `
                <div class="error">
                    <i class="fas fa-exclamation-triangle"></i> Error al cargar los detalles del pedido
                </div>
            `;
        });
}

function mostrarDetallePedido(pedido, productos) {
    let html = `
        <div class="pedido-info">
            <h3>Información del Pedido #${pedido.id_pedido}</h3>
            <div class="info-item">
                <span>Mesa:</span>
                <strong>${pedido.numero_mesa}</strong>
            </div>
            <div class="info-item">
                <span>Estado:</span>
                <strong>${pedido.estado}</strong>
            </div>
            <div class="info-item">
                <span>Fecha:</span>
                <strong>${pedido.fecha_pedido}</strong>
            </div>
        </div>
        
        <h3>Productos del Pedido:</h3>
        <div class="productos-lista">
    `;
    
    productos.forEach(producto => {
        html += `
            <div class="producto-item">
                <div class="producto-info">
                    <div class="producto-nombre">${producto.nombre}</div>
                    <div class="producto-descripcion">${producto.descripcion || 'Sin descripción'}</div>
                    <div class="producto-categoria">${producto.categoria}</div>
                </div>
                <div>
                    <span class="producto-cantidad">x${producto.cantidad}</span>
                    <span class="producto-precio">$${parseFloat(producto.subtotal).toFixed(2)}</span>
                </div>
            </div>
        `;
    });
    
    html += `
        </div>
        <div class="total-pedido">
            <h3>Total: $${parseFloat(pedido.total).toFixed(2)}</h3>
        </div>
    `;
    
    document.getElementById('contenidoModal').innerHTML = html;
}

function cerrarModal() {
    document.getElementById('modalPedido').style.display = 'none';
}

// Cerrar modal al hacer clic fuera de ella
window.onclick = function(event) {
    let modal = document.getElementById('modalPedido');
    if (event.target == modal) {
        modal.style.display = 'none';
    }
}
</script>

<script src="../assets/js/redirigir.js"></script>

</body>
</html>