
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

$consulta = "select * from pedidos where estado = 'listo'";
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
    <link rel="stylesheet" href="../assets/css/mesero_dashboard.css">
        <!---notificaciones--->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../assets/js/notificaciones.js"></script>
    
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
            <h1><i class="fas fa-utensils"></i> Pedidos Listos</h1>
            <p>Lleva los pedidos a la mesa correspondiente.</p>
        </div>

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
                                <button class="btn-custom confirmar" onclick="redirigir2('entregado', <?php echo $row['id_pedido']?>, <?php echo $_SESSION['id_usuario']; ?>, <?php echo $row['total']; ?>)">
                                    <i class="fas fa-edit"></i> Entregado
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
<script src="../assets/js/redirigir.js"></script>

</body>
</html>