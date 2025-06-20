<?php
require_once '../models/MySQL.php';

$mysql = new MySQL();
$mysql->conectar();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtener datos del formulario
    $id = $_POST['id_producto'];
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];
    $precio = $_POST['precio'];
    $stock = $_POST['stock'];
    $categoria = $_POST['id_categoria'];
    
    // Obtener la ruta actual de la imagen (desde un campo hidden)
    $ruta_imagen = $_POST['imagen_actual']; // Asegúrate de agregar este campo al formulario

    // Manejo de la nueva imagen si se subió
    if (isset($_FILES['imagen_url']) && $_FILES['imagen_url']['error'] === UPLOAD_ERR_OK) {
        $permitidos = ['image/jpeg' => '.jpg', 'image/png' => '.png'];
        $tipo = mime_content_type($_FILES['imagen_url']['tmp_name']);
    
        if (!array_key_exists($tipo, $permitidos)) {
            header("Location: ../admin/productos.php?estado=error&mensaje=Solo se permiten imágenes JPG y PNG");
            exit();
        }
    
        // Generar nombre único
        $ext = $permitidos[$tipo];
        $nombreUnico = 'imagen_' . date('Ymd_Hisv') . $ext;
        $ruta_nueva = 'assets/image/' . $nombreUnico;
        $rutaAbsoluta = __DIR__ . '/../' . $ruta_nueva;
    
        if (move_uploaded_file($_FILES['imagen_url']['tmp_name'], $rutaAbsoluta)) {
            // Si se subió correctamente la nueva imagen, usamos esta ruta
            $ruta_imagen = $ruta_nueva;
            
            // Opcional: Eliminar la imagen anterior si existe
            if (file_exists(__DIR__ . '/../' . $_POST['imagen_actual'])) {
                unlink(__DIR__ . '/../' . $_POST['imagen_actual']);
            }
        }
    }

    // Validación de campos obligatorios
    if (empty($nombre) || empty($descripcion) || empty($precio) || empty($stock)) {
        header("Location: ../admin/productos.php?estado=error&mensaje=Todos los campos son obligatorios");
        exit();
    }

    $resultado = $mysql->efectuarConsulta("UPDATE productos SET nombre = '$nombre', descripcion = '$descripcion', precio = '$precio', stock = '$stock', id_categoria = '$categoria', imagen_url = '$ruta_imagen' WHERE id_producto = '$id'");

    if ($resultado) {
        header("Location: ../admin/productos.php?estado=exito");
    } else {
        header("Location: ../admin/nuevo_producto.php?estado=error");
    }
}