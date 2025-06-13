<?php
require_once '../models/MySQL.php';

$mysql = new MySQL();
$mysql->conectar();

if ($_SERVER['REQUEST_METHOD'] == 'POST'){

    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];
    $precio = $_POST['precio'];
    $stock = $_POST['stock'];
    $categoria = $_POST['id_categoria'];

    if ($_FILES['imagen_url']['error'] === UPLOAD_ERR_OK) {
        $permitidos = ['image/jpeg' => '.jpg', 'image/png' => '.png'];
        $tipo = mime_content_type($_FILES['imagen_url']['tmp_name']);
    
        if (!array_key_exists($tipo, $permitidos)) {
            die("Solo se permiten imágenes JPG y PNG.");
        }
    
        // Generar nombre único y guardar la imagen
        $ext = $permitidos[$tipo];
        $nombreUnico = 'imagen_' . date('Ymd_Hisv') . $ext;
        $ruta = 'assets/image/' . $nombreUnico;
        $rutaAbsoluta = __DIR__ . '/../' . $ruta;
        
        if (!move_uploaded_file($_FILES['imagen_url']['tmp_name'], $rutaAbsoluta)) {
            die("Error al subir la imagen.");
        }
    } else {
        die("Debe seleccionar una imagen.");
    }

    // Insertar el nuevo producto en la base de datos
    $resultado = $mysql->efectuarConsulta("INSERT INTO Productos (nombre, descripcion, precio, stock, id_categoria, imagen_url) VALUES ('$nombre', '$descripcion', '$precio', '$stock', '$categoria', '../$ruta');");

    if ($resultado) {
        header("Location: ../admin/productos.php?estado=exito");
    } else {
        header("Location: ../admin/nuevo_producto.php?estado=error");
    }
    
} else {
    header("Location: ../admin/nuevo_producto.php?estado=error");

}