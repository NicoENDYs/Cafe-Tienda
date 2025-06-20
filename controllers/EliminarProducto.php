<?php
require_once '../models/MySQL.php';

$mysql = new MySQL();
$mysql->conectar();

// Verificar que el ID existe y es válido
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id_producto = $_GET['id'];
    
    try {
        // 1. Primero obtenemos la ruta de la imagen para eliminarla del servidor
        $stmt = $mysql->prepare("SELECT imagen_url FROM productos WHERE id_producto = :id");
        $stmt->execute([':id' => $id_producto]);
        $producto = $stmt->fetch(PDO::FETCH_ASSOC);

        // 2. Eliminar el producto de la base de datos
        $stmt = $mysql->prepare("DELETE FROM productos WHERE id_producto = :id");
        $resultado = $stmt->execute([':id' => $id_producto]);

        // 3. Si se eliminó correctamente, borrar la imagen asociada
        if ($resultado && $stmt->rowCount() > 0) {
            if (!empty($producto['imagen_url'])) {
                $ruta_imagen = __DIR__ . '/../' . $producto['imagen_url'];
                if (file_exists($ruta_imagen)) {
                    unlink($ruta_imagen);
                }
            }
            
            // Redireccionar con mensaje de éxito
            header("Location: ../admin/productos.php?estado=exito&mensaje=Producto eliminado correctamente");
            exit();
        } else {
            header("Location: ../admin/productos.php?estado=error&mensaje=El producto no existe o ya fue eliminado");
            exit();
        }

    } catch (PDOException $e) {
        error_log("Error al eliminar producto: " . $e->getMessage());
        
        // Verificar si es error de clave foránea
        if ($e->getCode() == '23000') {
            header("Location: ../admin/productos.php?estado=error&mensaje=No se puede eliminar, el producto está asociado a ventas");
        } else {
            header("Location: ../admin/productos.php?estado=error&mensaje=Error al eliminar el producto");
        }
        exit();
    }

    $mysql->desconectar();
} else {
    header("Location: ../admin/productos.php?estado=error&mensaje=ID de producto inválido");
    exit();
}
?>