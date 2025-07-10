<?php
require('../assets/fpdf186/fpdf.php');
require_once('../models/MySQL.php');

$mysql = new MySQL();
$mysql->conectar();

// Consultar productos desde la base de datos
$consulta = "SELECT id_producto, nombre, stock FROM productos";
$stmt = $mysql->prepare($consulta);
$stmt->execute();
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$productos) {
    return false;
}

// Crear PDF
$pdf = new FPDF();
$pdf->AddPage();

// Encabezado
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, 'CAFE TIENDA', 0, 1, 'C');
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 8, 'Reporte de Stock de Productos', 0, 1, 'C');
$pdf->Ln(10);

// Fecha
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 8, 'Fecha de reporte: ' . date('d/m/Y H:i'), 0, 1);
$pdf->Ln(5);

// Encabezado de tabla
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(30, 8, 'ID PRODUCTO', 1);
$pdf->Cell(100, 8, 'NOMBRE', 1);
$pdf->Cell(30, 8, 'STOCK', 1);
$pdf->Ln();

// Cuerpo de tabla
$pdf->SetFont('Arial', '', 10);
foreach ($productos as $producto) {
    $pdf->Cell(30, 8, $producto['id_producto'], 1);
    $pdf->Cell(100, 8, utf8_decode($producto['nombre']), 1);
    $pdf->Cell(30, 8, $producto['stock'], 1);
    $pdf->Ln();
}

// Guardar PDF
$filename = "../assets/reportes/stock_" . date('Ymd_His') . ".pdf";
$pdf->Output('F', $filename);

header("Location: ../admin/productos.php?estado=exito");
