<?php
require_once '../models/MySQL.php';

    $mysql = new MySQL();
    $mysql->conectar();
    $pdo = $mysql->getConexion();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtener datos del formulario
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];
    $precio = $_POST['precio'];
    $stock = $_POST['stock'];
    $categoria = $_POST['id_categoria'];

    $ruta_imagen = null;

    // Manejo de imagen subida
    if (isset($_FILES['imagen_url']) && $_FILES['imagen_url']['error'] === UPLOAD_ERR_OK) {
        $permitidos = ['image/jpeg' => '.jpg', 'image/png' => '.png'];
        $tipo = mime_content_type($_FILES['imagen_url']['tmp_name']);

        if (!array_key_exists($tipo, $permitidos)) {
            header("Location: ../controllers/NuevoProducto.php?estado=error&mensaje=Tipo de imagen no permitido");
            exit();
        }

        // Nombre único para la imagen
        $ext = $permitidos[$tipo];
        $nombreUnico = 'imagen_' . date('Ymd_Hisv') . $ext;
        $ruta_imagen = 'assets/image/' . $nombreUnico;
        $rutaAbsoluta = __DIR__ . '/../' . $ruta_imagen;

        // Mover imagen
        if (!move_uploaded_file($_FILES['imagen_url']['tmp_name'], $rutaAbsoluta)) {
            header("Location: ../controllers/NuevoProducto.php?estado=error&mensaje=Error al subir la imagen");
            exit();
        }
    }

    // Validación de campos obligatorios
    if (empty($nombre) || empty($descripcion) || empty($precio) || empty($stock)) {
        header("Location: ../controllers/NuevoProducto.php?estado=error&mensaje=Todos los campos son obligatorios");
        exit();
    }

    try {
        // Consulta preparada usando tu clase PDO
        $stmt = $mysql->prepare("
            INSERT INTO productos (nombre, descripcion, precio, stock, id_categoria, imagen_url, estado)
            VALUES (:nombre, :descripcion, :precio, :stock, :id_categoria, :imagen_url, :estado)
        ");

        $resultado = $stmt->execute([
            ':nombre' => $nombre,
            ':descripcion' => $descripcion,
            ':precio' => $precio,
            ':stock' => $stock,
            ':id_categoria' => $categoria,
            ':imagen_url' => $ruta_imagen,
            ':estado' => 0 // Estado 0 para indicar que el producto está activo
        ]);

        if ($resultado) {
            header("Location: ../admin/productos.php?estado=exito");
        } else {
            header("Location: ../controllers/NuevoProducto.php?estado=error&mensaje=No se pudo insertar el producto");
        }

    } catch (PDOException $e) {
        error_log("Error al insertar producto: " . $e->getMessage());
        header("Location: ../controllers/NuevoProducto.php?estado=error&mensaje=Error interno");
    }

    $mysql->desconectar();
}
?>
