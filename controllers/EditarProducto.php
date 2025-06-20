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
    
    // Ruta original de la imagen (oculta en el formulario)
    $ruta_imagen = $_POST['imagen_actual'];

    // Verificar si se subió una nueva imagen
    if (isset($_FILES['imagen_url']) && $_FILES['imagen_url']['error'] === UPLOAD_ERR_OK) {
        $permitidos = ['image/jpeg' => '.jpg', 'image/png' => '.png'];
        $tipo = mime_content_type($_FILES['imagen_url']['tmp_name']);

        if (!array_key_exists($tipo, $permitidos)) {
            header("Location: ../admin/productos.php?estado=error&mensaje=Solo se permiten imágenes JPG y PNG");
            exit();
        }

        $ext = $permitidos[$tipo];
        $nombreUnico = 'imagen_' . date('Ymd_Hisv') . $ext;
        $ruta_nueva = 'assets/image/' . $nombreUnico;
        $rutaAbsoluta = __DIR__ . '/../' . $ruta_nueva;

        if (move_uploaded_file($_FILES['imagen_url']['tmp_name'], $rutaAbsoluta)) {
            $ruta_imagen = $ruta_nueva;

            // Eliminar imagen anterior si existe
            $anterior = __DIR__ . '/../' . $_POST['imagen_actual'];
            if (file_exists($anterior)) {
                unlink($anterior);
            }
        }
    }

    // Validación básica
    if (empty($nombre) || empty($descripcion) || empty($precio) || empty($stock)) {
        header("Location: ../admin/productos.php?estado=error&mensaje=Todos los campos son obligatorios");
        exit();
    }

    // Consulta segura con prepared statement
    $stmt = $mysql->prepare("UPDATE productos 
                             SET nombre = ?, descripcion = ?, precio = ?, stock = ?, id_categoria = ?, imagen_url = ?
                             WHERE id_producto = ?");

    if ($stmt) {
        $stmt->bind_param("ssdisis", $nombre, $descripcion, $precio, $stock, $categoria, $ruta_imagen, $id);
        $resultado = $stmt->execute();
        $stmt->close();
    } else {
        $mysql->desconectar();
        header("Location: ../admin/productos.php?estado=error&mensaje=Error en la preparación de la consulta");
        exit();
    }

    $mysql->desconectar();

    if ($resultado) {
        header("Location: ../admin/productos.php?estado=exito");
    } else {
        header("Location: ../admin/productos.php?estado=error&mensaje=No se pudo actualizar");
    }
}
?>
