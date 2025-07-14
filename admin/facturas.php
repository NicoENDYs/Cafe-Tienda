<?php
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != "admin") {
    header("refresh:1;url=../views/login.php");
    exit();
}
require_once('../models/MySQL.php');
require_once('sidebar.php');

$mysql = new MySQL();
$mysql->conectar();

// Obtener facturas
$stmt = $mysql->prepare("SELECT id, archivo_url, fecha_generacion FROM facturas_generadas ORDER BY fecha_generacion DESC");
$stmt->execute();
$facturas = $stmt->fetchAll(PDO::FETCH_ASSOC);
$mysql->desconectar();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Facturas | Admin</title>
    <link rel="stylesheet" href="../assets/css/admin_dashboard.css">
    <link rel="stylesheet" href="../assets/css/facturas_admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="admin-layout">
        <?php // Sidebar ya incluido arriba ?>
        <main class="main-content">
            <h2><i class="fas fa-file-invoice"></i> Facturas generadas</h2>
            <div class="facturas-container">
                <form id="form-facturas">
                    <label for="select-factura">Selecciona una factura:</label>
                    <select id="select-factura" name="factura">
                        <option value="">-- Selecciona --</option>
                        <?php foreach ($facturas as $factura): 
                            // Extraer el número de la factura del archivo_url
                            if (preg_match('/factura_venta_(\d+)\.pdf$/', $factura['archivo_url'], $matches)) {
                                $num = $matches[1];
                            } else {
                                $num = $factura['id'];
                            }
                            $fecha = date('Y-m-d H:i', strtotime($factura['fecha_generacion']));
                        ?>
                        <option value="<?= htmlspecialchars($factura['archivo_url']) ?>">Factura <?= $num ?> <?= $fecha ?></option>
                        <?php endforeach; ?>
                    </select>
                </form>
                <div id="factura-link" style="margin-top: 2rem;"></div>
                <div id="factura-preview" style="margin-top: 1.5rem;"></div>
            </div>
        </main>
    </div>
    <script>
    document.getElementById('select-factura').addEventListener('change', function() {
        var url = this.value;
        var linkDiv = document.getElementById('factura-link');
        var previewDiv = document.getElementById('factura-preview');
        if (url) {
            linkDiv.innerHTML = '<a href="../' + url + '" target="_blank" class="btn-factura"><i class="fas fa-file-pdf"></i> Ver PDF</a>';
            previewDiv.innerHTML = '<iframe src="../' + url + '" style="width:100%;height:600px;border:1.5px solid #bbb;border-radius:8px;" allowfullscreen></iframe>';
        } else {
            linkDiv.innerHTML = '';
            previewDiv.innerHTML = '';
        }
    });
    </script>
</body>
</html> 