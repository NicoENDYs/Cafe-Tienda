<?php
require_once('../models/MySQL.php');

$mysql = new MySQL();
$mysql->conectar();

$sql = "SELECT productos.id_producto,productos.nombre,productos.descripcion,productos.precio,productos.id_categoria,productos.stock,categorias.nombre as nombre_categoria,productos.imagen_url 
FROM `productos` 
JOIN categorias on categorias.id_categoria = productos.id_categoria ";
$result = $mysql->efectuarConsulta($sql);

$consulta = "SELECT id_categoria , nombre FROM categorias";
$categorias = $mysql->efectuarConsulta($consulta);

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
        <!-- SIDEBAR -->
        <nav class="sidebar">
            <div class="sidebar-header">
                <h3><i class="fas fa-store"></i> Mi Tienda</h3>
                <p>Panel de Administración</p>
            </div>
            <ul class="sidebar-nav">
                <li class="nav-item">
                    <a href="./dashboard.php" class="nav-link" data-section="dashboard">
                        <i class="fas fa-tachometer-alt"></i>
                        Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a href="./productos.php" class="nav-link active" data-section="productos">
                        <i class="fas fa-box"></i>
                        Productos
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link" data-section="usuarios">
                        <i class="fas fa-users"></i>
                        Usuarios
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link" data-section="reportes">
                        <i class="fas fa-chart-bar"></i>
                        Reportes
                    </a>
                </li>
            </ul>
        </nav>

        <!-- CONTENIDO PRINCIPAL -->
        <main class="main-content">
            <!-- ENCABEZADO -->
            <div class="page-header">
                <h1><i class="fas fa-box"></i> Gestión de Productos</h1>
                <p>Administra tu inventario de productos de manera eficiente</p>
            </div>

            <!-- ESTADÍSTICAS -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon primary">
                        <i class="fas fa-box"></i>
                    </div>
                    <div class="stat-content">
                        <h3 id="totalProducts">24</h3>
                        <p>Total Productos</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon success">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-content">
                        <h3 id="activeProducts">18</h3>
                        <p>Productos Activos</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon danger">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="stat-content">
                        <h3 id="lowStock">3</h3>
                        <p>Stock Bajo</p>
                    </div>
                </div>
            </div>

            <!-- SECCIÓN DE PRODUCTOS -->
            <div class="products-section">
                <div class="section-header">
                    <h2 class="section-title">
                        <i class="fas fa-list"></i>
                        Lista de Productos
                    </h2>
                    <!-- Button trigger modal -->
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModal">
                        Añadir Producto
                    </button>

                    <!-- Modal para agregar producto -->
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
                                                <?php 
                                                // Volvemos a consultar las categorías para el modal de agregar
                                                $mysql->conectar();
                                                $consulta_categorias = $mysql->efectuarConsulta("SELECT id_categoria , nombre FROM categorias");
                                                while($fila = $consulta_categorias->fetch_assoc()): ?>
                                                    <option value="<?php echo $fila['id_categoria']; ?>">
                                                        <?php echo $fila['nombre']; ?>
                                                    </option>
                                                <?php endwhile; 
                                                $mysql->desconectar();
                                                ?>
                                            </select>

                                            <div class="form-group file-input">
                                                <input type="file" name="imagen_url" id="fileInput" placeholder="URL de la imagen (opcional)" />
                                                <label for="fileInput">
                                                    <span id="fileName">Seleccionar archivo</span>
                                                </label>
                                                <span class="file-name" id="fileSelectedName">Sin archivos seleccionados</span>
                                            </div>
                                            
                                            <button type="submit">
                                                Guardar Producto
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-container">
                    <?php if (mysqli_num_rows($result) > 0): ?>
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
                                <?php while ($producto = mysqli_fetch_assoc($result)): ?>
                                    <tr>
                                        <td><strong><?php echo $producto['id_producto']; ?></strong></td>
                                        <td><img src="<?php echo $producto['imagen_url']; ?>" alt="<?php echo $producto['nombre']; ?>" class="product-img"></td>
                                        <td><strong><?php echo $producto['nombre']; ?></strong></td>
                                        <td><?php echo $producto['nombre_categoria']; ?></td>
                                        <td>$<?php echo number_format($producto['precio'], 2); ?></td>
                                        <td><?php echo $producto['stock']; ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalEditar<?php echo $producto['id_producto']; ?>">
                                                <i class="fas fa-edit"></i> Editar
                                            </button>
                                            <button class="btn btn-sm btn-danger" onclick="deleteProduct(<?php echo $producto['id_producto']; ?>)">
                                                <i class="fas fa-trash"></i> Eliminar
                                            </button>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p class="text-center mt-5 text-black">No hay productos registrados.</p>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <!-- Modales de edición para cada producto -->
    <?php 
    // Volvemos a consultar los productos para los modales de edición
    $mysql->conectar();
    $result_edit = $mysql->efectuarConsulta($sql);
    while ($producto = mysqli_fetch_assoc($result_edit)): 
    ?>
        <div class="modal fade" id="modalEditar<?php echo $producto['id_producto']; ?>" tabindex="-1" aria-labelledby="editModalLabel<?php echo $producto['id_producto']; ?>" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-body">
                        <div class="form-container">
                            <h2>Editar Producto</h2>
                            <form id="editProductForm<?php echo $producto['id_producto']; ?>" action="../controllers/EditarProducto.php" method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="id_producto" value="<?php echo $producto['id_producto']; ?>">
                                
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

                                <select name="id_categoria" required>
                                    <?php 

                                    $consulta_categorias = $mysql->efectuarConsulta("SELECT * FROM categorias");
                                    
                                    while($fila = $consulta_categorias->fetch_assoc()): 
                                        // Verificamos si la clave existe antes de usarla
                                        $selected = (isset($producto['id_categoria']) && $fila['id_categoria'] == $producto['id_categoria']) ? 'selected' : '';
                                    ?>
                                    
                                        <option value="<?php echo $fila['id_categoria']; ?>" <?php echo $selected; ?>>
                                            <?php echo $fila['nombre']; ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>

                                <br><br>

                                <div class="form-group file-input">
                                    <input type="file" name="imagen_url" id="edit_fileInput<?php echo $producto['id_producto']; ?>" />
                                    <label for="edit_fileInput<?php echo $producto['id_producto']; ?>">
                                        <span id="edit_fileName<?php echo $producto['id_producto']; ?>">Cambiar imagen</span>
                                    </label>
                                    <span class="file-name" id="edit_fileSelectedName<?php echo $producto['id_producto']; ?>">Sin archivos seleccionados</span>
                                    <div id="currentImageContainer" class="mt-2">
                                        <input type="hidden" name="imagen_actual" value="<?php echo $producto['imagen_url']; ?>">
                                        <small>Imagen actual:</small>
                                        <img src="<?php echo $producto['imagen_url']; ?>" class="img-thumbnail mt-1" style="max-width: 100px;">
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary">
                                    Guardar Cambios
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endwhile; ?>

    <script src="../assets/js/admin_productos.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous"></script>
    
    <script>
        // Script para mostrar el nombre del archivo seleccionado en los modales de edición
        document.addEventListener('DOMContentLoaded', function() {
            <?php 
            $mysql->conectar();
            $result_edit = $mysql->efectuarConsulta($sql);
            while ($producto = mysqli_fetch_assoc($result_edit)): 
            ?>
                document.getElementById('edit_fileInput<?php echo $producto['id_producto']; ?>').addEventListener('change', function(e) {
                    const fileName = e.target.files[0] ? e.target.files[0].name : 'Sin archivos seleccionados';
                    document.getElementById('edit_fileSelectedName<?php echo $producto['id_producto']; ?>').textContent = fileName;
                });
            <?php endwhile; 
            $mysql->desconectar();
            ?>
        });

        // Función para eliminar producto con confirmación
        function deleteProduct(id) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: "¡No podrás revertir esto!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '../controllers/EliminarProducto.php?id=' + id;
                }
            });
        }
    </script>
</body>
</html>