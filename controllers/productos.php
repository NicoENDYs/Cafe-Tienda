<?php
require_once __DIR__ . './models/MySql.php';

header('Content-Type: application/json; charset=UTF-8');

$mysql = new Mysql;
$mysql->conectar();


$query = "SELECT productos.id_producto, productos.nombre, productos.imagen_url, productos.precio, productos.descripcion, productos.stock, categorias.nombre AS categoria_nombre
        FROM productos 
        JOIN categorias ON productos.id_categoria = categorias.id_categoria;";

$resultado = $mysql->efectuarConsulta($query);

$products = [];
while ($row = mysqli_fetch_assoc($resultado)) {
    $products[] = [
        'id' => $row['id_producto'],
        'name' => $row['nombre'],
        'category' => $row['categoria_nombre'], // ahora es el nombre
        'image' => $row['imagen_url'],
        'price' => $row['precio'],
        'short_description' => $row['descripcion'],
        'stock' => $row['stock'],
    ];
}

$mysql->desconectar();
echo json_encode($products);
?>