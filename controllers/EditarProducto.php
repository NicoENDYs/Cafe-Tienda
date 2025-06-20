<?php
require_once '../models/MySQL.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id_producto'];
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];
    $precio = $_POST['precio'];
    $stock = $_POST['stock'];
    $categoria = $_POST['id_categoria'];
    $ruta_imagen = $_POST['imagen_actual'];

    // Validar campos obligatorios
    if (empty($nombre) || empty($descripcion) || empty($precio) || empty($stock)) {
        header("Location: ../admin/productos.php?estado=error&mensaje=Todos los campos son obligatorios");
        exit();
    }

    // Procesar imagen si se subió una nueva
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
            // Eliminar imagen anterior si existe
            $anterior = __DIR__ . '/../' . $_POST['imagen_actual'];
            if (file_exists($anterior)) {
                unlink($anterior);
            }
            $ruta_imagen = $ruta_nueva;
        }
    }

    // Conexión y actualización con PDO
    $mysql = new MySQL();
    $mysql->conectar();
    $pdo = $mysql->getConexion();

    try {
        $stmt = $pdo->prepare("UPDATE productos 
                               SET nombre = :nombre, descripcion = :descripcion, precio = :precio, stock = :stock, id_categoria = :categoria, imagen_url = :imagen
                               WHERE id_producto = :id");

        $resultado = $stmt->execute([
            ':nombre'     => $nombre,
            ':descripcion'=> $descripcion,
            ':precio'     => $precio,
            ':stock'      => $stock,
            ':categoria'  => $categoria,
            ':imagen'     => $ruta_imagen,
            ':id'         => $id
        ]);

        $mysql->desconectar();

        if ($resultado) {
            header("Location: ../admin/productos.php?estado=exito");
        } else {
            header("Location: ../admin/productos.php?estado=error&mensaje=No se pudo actualizar");
        }
    } catch (PDOException $e) {
        $mysql->desconectar();
        header("Location: ../admin/productos.php?estado=error&mensaje=" . urlencode("Error en la consulta: " . $e->getMessage()));
    }
}
?>
