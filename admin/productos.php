<?php
require_once('../models/MySQL.php');

$mysql = new MySQL();
$mysql->conectar();

try {
    // Consulta de productos con JOIN
    $stmt = $mysql->prepare("
        SELECT 
            productos.id_producto,
            productos.nombre,
            productos.descripcion,
            productos.precio,
            productos.id_categoria,
            productos.stock,
            categorias.nombre AS nombre_categoria,
            productos.imagen_url
        FROM productos 
        JOIN categorias ON categorias.id_categoria = productos.id_categoria where estado < '1'
    ");
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Consulta de categorías
    $stmtCat = $mysql->prepare("SELECT id_categoria, nombre FROM categorias");
    $stmtCat->execute();
    $categorias = $stmtCat->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error al realizar la consulta: " . $e->getMessage());
}

$mysql->desconectar();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!--Bootstrap-->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
    <!---notificaciones--->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../assets/js/notificaciones.js"></script>

    <link rel="stylesheet" href="../assets/css/admin_productos.css">
</head>

<body>
    <div class="admin-layout">
    <?php include('sidebar.php'); ?>

    <main class="main-content">
        <div class="page-header">
            <h1><i class="fas fa-box"></i> Gestión de Productos</h1>
            <p>Administra tu inventario de productos de manera eficiente</p>
        </div>

        <div class="stats-grid">
            <!-- Stats placeholder (puedes hacer consultas para llenarlas dinamicamente con PDO también) -->
        </div>

        <div class="products-section">
            <div class="section-header">
                <h2 class="section-title"><i class="fas fa-list"></i> Lista de Productos</h2>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModal">Añadir Producto</button>

                <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-body">
                                <div class="form-container">
                                    <h2>Nuevo Producto</h2>
                                    <form id="productoForm" action="../controllers/NuevoProducto.php" method="POST" enctype="multipart/form-data" novalidate>
                                        <div class="form-group">
                                            <input type="text" name="nombre" placeholder="Nombre del producto" required />
                                        </div>
                                        <div class="form-group">
                                            <textarea name="descripcion" placeholder="Descripción del producto" required></textarea>
                                        </div>
                                        <div class="form-group">
                                            <input type="number" name="precio" placeholder="Precio ($)" step="0.01" min="0" required />
                                        </div>
                                        <div class="form-group">
                                            <input type="number" name="stock" placeholder="Cantidad en stock" min="0" required />
                                        </div>

                                        <select name="id_categoria" required>
                                            <option value="">Seleccione una categoría</option>
                                            <?php foreach ($categorias as $fila): ?>
                                                <option value="<?php echo $fila['id_categoria']; ?>">
                                                    <?php echo $fila['nombre']; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>

                                        <div class="form-group file-input">
                                            <input type="file" name="imagen_url" id="fileInput" />
                                            <label for="fileInput">
                                                <span id="fileName">Seleccionar archivo</span>
                                            </label>
                                            <span class="file-name" id="fileSelectedName">Sin archivos seleccionados</span>
                                        </div>
                                        <button type="submit">Guardar Producto</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="table-container">
                <?php if (count($result) > 0): ?>
                    <table class="table" id="productsTable">
                        <thead>
                        <tr>
                            <th>Id</th>
                            <th><i class="fas fa-image"></i></th>
                            <th>Nombre</th>
                            <th>Categoría</th>
                            <th>Precio</th>
                            <th>Stock</th>
                            <th>Acciones</th>
                        </tr>
                        </thead>
                        <tbody id="productsTableBody">
                        <?php foreach ($result as $producto): ?>
                            <tr>
                                <td><strong><?php echo $producto['id_producto']; ?></strong></td>
                                <td><img src="../<?php echo $producto['imagen_url']; ?>" alt="<?php echo $producto['nombre']; ?>" class="product-img" ></td>
                                <td><strong><?php echo $producto['nombre']; ?></strong></td>
                                <td><?php echo $producto['nombre_categoria']; ?></td>
                                <td>$<?php echo number_format($producto['precio'], 2); ?></td>
                                <td><?php echo $producto['stock']; ?></td>
                                <td>
                                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalEditar<?php echo $producto['id_producto']; ?>">
                                        <i class="fas fa-edit"></i> Editar
                                    </button>

                                    <button class="btn btn-sm btn-danger"  onclick="eliminarProducto(<?php echo $producto['id_producto']; ?>)" >
                                        <i class="fas fa-trash"></i> Eliminar
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="text-center mt-5 text-black">No hay productos registrados.</p>
                <?php endif; ?>

                <!--modal de edicion-->
                <?php foreach ($result as $producto): ?>
                <div class="modal fade" id="modalEditar<?php echo $producto['id_producto']; ?>" tabindex="-1" aria-labelledby="editModalLabel<?php echo $producto['id_producto']; ?>" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-body">
                                <div class="form-container">
                                    <h2>Editar Producto</h2>
                                    <form id="editProductForm<?php echo $producto['id_producto']; ?>" action="../controllers/EditarProducto.php" method="POST" enctype="multipart/form-data">
                                        <input type="hidden" name="id_producto" value="<?php echo $producto['id_producto']; ?>">
                                        <input type="hidden" name="imagen_actual" value="<?php echo $producto['imagen_url']; ?>">

                                        <div class="form-group">
                                            <input type="text" name="nombre" value="<?php echo htmlspecialchars($producto['nombre']); ?>" placeholder="Nombre del producto" required />
                                        </div>

                                        <div class="form-group">
                                            <textarea name="descripcion" placeholder="Descripción del producto" required><?php echo htmlspecialchars($producto['descripcion']); ?></textarea>
                                        </div>

                                        <div class="form-group">
                                            <input type="number" name="precio" value="<?php echo $producto['precio']; ?>" placeholder="Precio ($)" step="0.01" min="0" required />
                                        </div>

                                        <div class="form-group">
                                            <input type="number" name="stock" value="<?php echo $producto['stock']; ?>" placeholder="Cantidad en stock" min="0" required />
                                        </div>

                                        <div class="form-group">
                                            <select name="id_categoria" required>
                                                <option value="">Selecciona una categoría</option>
                                                <?php foreach ($categorias as $fila):
                                                    $selected = ($fila['id_categoria'] == $producto['id_categoria']) ? 'selected' : '';
                                                ?>
                                                    <option value="<?php echo $fila['id_categoria']; ?>" <?php echo $selected; ?>>
                                                        <?php echo htmlspecialchars($fila['nombre']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div class="form-group file-input">
                                            <input type="file" name="imagen_url" id="edit_fileInput<?php echo $producto['id_producto']; ?>" />
                                            <label for="edit_fileInput<?php echo $producto['id_producto']; ?>">Seleccionar nueva imagen</label>
                                            <span class="file-name" id="edit_fileSelectedName<?php echo $producto['id_producto']; ?>">Sin archivos seleccionados</span>
                                            <div class="mt-2">
                                                <small>Imagen actual:</small><br>
                                                <img src="<?php echo $producto['imagen_url']; ?>" class="img-thumbnail mt-1" style="max-width: 100px;">
                                            </div>
                                        </div>

                                        <button type="submit" class="btn btn-primary mt-3">Guardar Cambios</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>
</div>

    <script src="../assets/js/admin_productos.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous"></script>
</body>
</html>