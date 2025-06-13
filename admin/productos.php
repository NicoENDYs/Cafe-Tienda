<?php
require_once('../models/MySQL.php');

$mysql = new MySQL();
$mysql->conectar();

$sql = "SELECT productos.id_producto,productos.nombre,productos.descripcion,productos.precio,productos.stock,categorias.nombre as nombre_categoria,productos.imagen_url 
FROM `productos` 
JOIN categorias on categorias.id_categoria = productos.id_categoria ";
$result = $mysql->efectuarConsulta($sql);

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

                    <!-- Modal -->
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

                                            <div class="form-group">
                                                <input type="number" name="id_categoria" placeholder="ID de Categoría" min="1" required />
                                            </div>

                                            <div class="form-group">
                                                <input type="url" name="imagen_url" placeholder="URL de la imagen (opcional)" />
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

                            <?php while ($producto = mysqli_fetch_assoc($result)): ?>
                                <tbody id="productsTableBody">

                                    <!-- Los productos se cargarán aquí dinámicamente -->
                                    <td><strong><?php echo $producto['id_producto']; ?></strong></td>
                                    <td><img src="<?php echo $producto['imagen_url']; ?>" alt="<?php echo $producto['nombre']; ?>" class="product-img"></td>
                                    <td><strong><?php echo $producto['nombre']; ?></strong></td>
                                    <td><?php echo $producto['nombre_categoria']; ?></td>
                                    <td>$<?php echo number_format($producto['precio'], 2); ?></td>
                                    <td><?php echo $producto['stock']; ?></td>

                                    <td>
                                        <button class="btn btn-sm btn-primary" onclick="editProduct(<?php echo $producto['id_producto']; ?>)"><i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger " onclick="deleteProduct(<?php echo $producto['id_producto']; ?>)"><i class="fas fa-trash"></i>
                                        </button>
                                    </td>

                                </tbody>
                            <?php endwhile; ?>
                        </table>
                    <?php else: ?>
                        <p class="text-center mt-5 text-black">No hay productos registrados.</p>
                    <?php endif; ?>
                    </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
    >

    <script src="../assets/js/admin_productos.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous"></script>
</body>

</html>