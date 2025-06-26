<?php
require_once 'models/MySQL.php';

$mysql = new MySQL();
$mysql->conectar();
$pdo = $mysql->getConexion();

$consulta = "SELECT 
                c.id_categoria,
                c.nombre as categoria_nombre,
                p.id_producto,
                p.nombre as producto_nombre,
                p.descripcion,
                p.precio,
                p.stock,
                p.imagen_url
            FROM categorias c
            LEFT JOIN productos p ON c.id_categoria = p.id_categoria
            WHERE p.stock > 0
            ORDER BY c.nombre, p.nombre";

try {
    $stmt = $pdo->prepare($consulta);
    $stmt->execute();
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Organizar datos por categoría
    $categorias = [];
    foreach ($resultados as $row) {
        $categoria_id = $row['id_categoria'];

        if (!isset($categorias[$categoria_id])) {
            $categorias[$categoria_id] = [
                'id' => $categoria_id,
                'nombre' => $row['categoria_nombre'],
                'productos' => []
            ];
        }

        if ($row['id_producto']) {
            $categorias[$categoria_id]['productos'][] = [
                'id' => $row['id_producto'],
                'nombre' => $row['producto_nombre'],
                'descripcion' => $row['descripcion'],
                'precio' => $row['precio'],
                'stock' => $row['stock'],
                'imagen_url' => $row['imagen_url']
            ];
        }
    }
} catch (PDOException $e) {
    echo "Error al obtener las categorías: " . $e->getMessage();
    $categorias = [];
}
$mysql->desconectar();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Café Artesanal - Tu lugar para disfrutar del mejor café</title>
    <meta name="description" content="Disfruta del mejor café artesanal en nuestra cafetería. Especialidades preparadas con amor y los mejores granos.">

    <!-- TailwindCSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
        integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw=="
        crossorigin="anonymous" referrerpolicy="no-referrer">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Leaflet Maps -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
        crossorigin="">
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
        crossorigin=""></script>
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.4/dist/aos.css" rel="stylesheet">
    <script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/11.7.32/sweetalert2.all.min.js"></script>

    <!-- Custom Styles -->
    <link rel="stylesheet" href="./assets/css/index_estilo.css">

</head>

<body>
    <!-- Header and Navigation -->
    <header class="hero flex flex-col justify-center items-center relative">
        <div class="absolute inset-0 z-10 flex flex-col justify-between">
            <!-- Navigation -->
            <nav class="container mx-auto px-6 py-4 flex justify-between items-center">
                <div class="flex items-center">
                    <span class="text-white text-3xl font-bold">
                        Café<span class="accent-color">Artesanal</span>
                    </span>
                </div>

                <!-- Desktop Navigation -->
                <div class="hidden md:flex items-center space-x-6">
                    <a href="#inicio" class="nav-link">Inicio</a>
                    <a href="#productos" class="nav-link">Productos</a>
                    <a href="#ubicacion" class="nav-link">Ubicación</a>
                    <a href="#contacto" class="nav-link">Contacto</a>
                </div>

                <!-- Mobile Menu Button -->
                <div class="md:hidden">
                    <button id="mobile-menu-button" class="text-white focus:outline-none">
                        <i class="fas fa-bars text-2xl"></i>
                    </button>
                </div>
            </nav>

            <!-- Mobile Menu -->
            <div id="mobile-menu" class="md:hidden bg-white absolute top-16 right-0 left-0 z-20 shadow-lg rounded-b-lg hidden transform transition-all duration-300">
                <div class="flex flex-col p-4 space-y-2">
                    <a href="#inicio" class="py-3 px-4 text-gray-800 hover:bg-gray-100 rounded transition-colors">
                        <i class="fas fa-home mr-2"></i>Inicio
                    </a>
                    <a href="#productos" class="py-3 px-4 text-gray-800 hover:bg-gray-100 rounded transition-colors">
                        <i class="fas fa-coffee mr-2"></i>Productos
                    </a>
                    <a href="#ubicacion" class="py-3 px-4 text-gray-800 hover:bg-gray-100 rounded transition-colors">
                        <i class="fas fa-map-marker-alt mr-2"></i>Ubicación
                    </a>
                    <a href="#contacto" class="py-3 px-4 text-gray-800 hover:bg-gray-100 rounded transition-colors">
                        <i class="fas fa-envelope mr-2"></i>Contacto
                    </a>
                </div>
            </div>

            <!-- Hero Content -->
            <div id="inicio" class="container mx-auto px-6 py-20 text-center flex-1 flex flex-col justify-center">
                <h1 class="text-4xl md:text-6xl font-bold text-white mb-6" data-aos="fade-up">
                    Disfruta del mejor <span class="accent-color">café</span>
                </h1>
                <p class="text-xl md:text-2xl text-white mb-8 max-w-2xl mx-auto" data-aos="fade-up" data-aos-delay="100">
                    Especialidades artesanales preparadas con amor y los mejores granos seleccionados
                </p>
                <div class="space-y-4 md:space-y-0 md:space-x-4 md:flex md:justify-center" data-aos="fade-up" data-aos-delay="200">
                    <a href="#productos" class="btn-primary px-8 py-4 rounded-full font-medium inline-block">
                        <i class="fas fa-coffee mr-2"></i>Ver nuestros productos
                    </a>
                    <a href="#ubicacion" class="btn-primary px-8 py-4 rounded-full font-medium inline-block bg-transparent border-2 border-white hover:bg-white hover:text-gray-800">
                        <i class="fas fa-map-marker-alt mr-2"></i>Cómo llegar
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Products Section -->
    <section id="productos" class="py-20 bg-white">
        <div class="container mx-auto px-6">
            <h2 class="text-3xl md:text-4xl font-bold mb-4 section-heading text-center" data-aos="fade-up">
                Nuestros Productos
            </h2>
            <p class="text-gray-600 text-center mb-16 max-w-2xl mx-auto" data-aos="fade-up" data-aos-delay="100">
                Descubre nuestra selección de cafés premium, bebidas especiales y acompañamientos perfectos para cada momento del día
            </p>
            <div id="categorias-container">
                <?php if (!empty($categorias)): ?>
                    <?php foreach ($categorias as $categoria): ?>
                        <div class="categoria">
                            <div class="categoria-header" onclick="toggleCategoria('categoria-<?= $categoria['id'] ?>', this)">
                                <div>
                                    <span class="icono">▶</span>
                                    <?= htmlspecialchars($categoria['nombre']) ?>
                                    <span class="contador-productos"><?= count($categoria['productos']) ?> productos</span>
                                </div>
                            </div>
                            <div id="categoria-<?= $categoria['id'] ?>" class="productos">
                                <?php if (!empty($categoria['productos'])): ?>
                                    <?php foreach ($categoria['productos'] as $producto): ?>
                                        <div class="producto">
                                            <div class="producto-info">
                                                <div class="producto-nombre">
                                                    <?= htmlspecialchars($producto['nombre']) ?>
                                                </div>
                                                <?php if (!empty($producto['descripcion'])): ?>
                                                    <div class="producto-descripcion">
                                                        <?= htmlspecialchars($producto['descripcion']) ?>
                                                    </div>
                                                <?php endif; ?>
                                                <div class="producto-stock">
                                                    Stock: <?= $producto['stock'] ?> unidades
                                                </div>
                                            </div>
                                            <div class="producto-controles">
                                                <div class="producto-precio">
                                                    $<?= number_format($producto['precio'], 2) ?>
                                                </div>
                                                <div class="controles-cantidad">
                                                    <button class="btn-cantidad" onclick="cambiarCantidad(<?= $producto['id'] ?>, -1, <?= $producto['stock'] ?>)">-</button>
                                                    <div class="contador-cantidad" id="cantidad-<?= $producto['id'] ?>">0</div>
                                                    <button class="btn-cantidad" onclick="cambiarCantidad(<?= $producto['id'] ?>, 1, <?= $producto['stock'] ?>)">+</button>
                                                    <button class="btn-agregar" onclick="agregarAlCarrito(<?= $producto['id'] ?>, '<?= htmlspecialchars($producto['nombre']) ?>', <?= $producto['precio'] ?>, <?= $producto['stock'] ?>)">
                                                        Agregar
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="no-productos">
                                        No hay productos disponibles en esta categoría
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-productos">
                        <h3>No se encontraron productos</h3>
                        <p>No hay productos disponibles en este momento.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Carrito de Compras -->
    <section id="carrito" class="py-16 bg-gray-100">
        <div class="container mx-auto px-6">
            <h2 class="text-3xl md:text-4xl font-bold mb-4 section-heading text-center" data-aos="fade-up">
                Tu Pedido
            </h2>
            <p class="text-gray-600 text-center mb-8 max-w-2xl mx-auto" data-aos="fade-up" data-aos-delay="100">
                Revisa los productos que has seleccionado antes de confirmar tu pedido
            </p>
            
            <div class="bg-white rounded-lg shadow-lg p-6" data-aos="fade-up">
                <div id="lista-carrito" class="mb-6">
                    <!-- Los productos del carrito se mostrarán aquí -->
                    <div id="carrito-vacio" class="text-center py-8">
                        <i class="fas fa-shopping-cart text-4xl text-gray-300 mb-4"></i>
                        <p class="text-gray-500">Tu carrito está vacío</p>
                    </div>
                    <div id="carrito-productos" class="hidden">
                        <!-- Contenido dinámico -->
                    </div>
                </div>
                
                <div id="resumen-carrito" class="bg-gray-50 p-4 rounded-lg hidden">
                    <div class="flex justify-between mb-2">
                        <span class="font-medium">Subtotal:</span>
                        <span id="subtotal">$0.00</span>
                    </div>
                    <div class="flex justify-between mb-4">
                        <span class="font-medium">Total:</span>
                        <span id="total" class="text-xl font-bold accent-color">$0.00</span>
                    </div>
                    
                    <div class="flex flex-col md:flex-row gap-4">
                        <button id="vaciar-carrito" class="btn-secondary flex-1 py-3 rounded-lg">
                            <i class="fas fa-trash-alt mr-2"></i> Vaciar Carrito
                        </button>
                        <button id="confirmar-pedido" class="btn-primary flex-1 py-3 rounded-lg">
                            <i class="fas fa-check-circle mr-2"></i> Confirmar Pedido
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Location Section -->
    <section id="ubicacion" class="py-20 bg-gray-50">
        <div class="container mx-auto px-6">
            <h2 class="text-3xl md:text-4xl font-bold mb-4 section-heading text-center" data-aos="fade-up">
                Nos Ubicamos
            </h2>
            <p class="text-gray-600 text-center mb-16 max-w-2xl mx-auto" data-aos="fade-up" data-aos-delay="100">
                Visítanos en nuestro acogedor local donde podrás disfrutar de la mejor experiencia cafetera
            </p>

            <div class="flex flex-col lg:flex-row gap-12 items-center">
                <div class="lg:w-1/2 w-full" data-aos="fade-right">
                    <div id="map" class="rounded-lg shadow-lg"></div>
                </div>

                <div class="lg:w-1/2 w-full" data-aos="fade-left">
                    <h3 class="text-2xl font-semibold mb-6 accent-color">Información de Contacto</h3>
                    <p class="text-gray-600 mb-8 leading-relaxed">
                        Estamos ubicados en el corazón de la ciudad, en un ambiente acogedor donde podrás disfrutar
                        de nuestros productos mientras compartes momentos especiales con familia y amigos.
                    </p>

                    <div class="space-y-6">
                        <div class="flex items-start group">
                            <div class="bg-orange-100 p-3 rounded-full mr-4 group-hover:bg-orange-200 transition-colors">
                                <i class="fas fa-map-marker-alt text-xl accent-color"></i>
                            </div>
                            <div>
                                <h4 class="font-semibold text-lg mb-1">Dirección</h4>
                                <p class="text-gray-600">Calle Principal #123, Centro, Ciudad</p>
                            </div>
                        </div>

                        <div class="flex items-start group">
                            <div class="bg-orange-100 p-3 rounded-full mr-4 group-hover:bg-orange-200 transition-colors">
                                <i class="fas fa-clock text-xl accent-color"></i>
                            </div>
                            <div>
                                <h4 class="font-semibold text-lg mb-1">Horario de Atención</h4>
                                <p class="text-gray-600">Lunes a Viernes: 7:00 AM - 8:00 PM</p>
                                <p class="text-gray-600">Sábados y Domingos: 8:00 AM - 9:00 PM</p>
                            </div>
                        </div>

                        <div class="flex items-start group">
                            <div class="bg-orange-100 p-3 rounded-full mr-4 group-hover:bg-orange-200 transition-colors">
                                <i class="fas fa-phone-alt text-xl accent-color"></i>
                            </div>
                            <div>
                                <h4 class="font-semibold text-lg mb-1">Teléfono</h4>
                                <p class="text-gray-600">(+123) 456-7890</p>
                            </div>
                        </div>

                        <div class="flex items-start group">
                            <div class="bg-orange-100 p-3 rounded-full mr-4 group-hover:bg-orange-200 transition-colors">
                                <i class="fas fa-envelope text-xl accent-color"></i>
                            </div>
                            <div>
                                <h4 class="font-semibold text-lg mb-1">Email</h4>
                                <p class="text-gray-600">info@cafeartesanal.com</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer id="contacto" class="bg-gray-900 text-white pt-16 pb-8">
        <div class="container mx-auto px-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10">
                <!-- Logo and Description -->
                <div class="lg:col-span-2">
                    <h3 class="text-3xl font-bold mb-4">
                        Café<span class="accent-color">Artesanal</span>
                    </h3>
                    <p class="text-gray-400 mb-6 leading-relaxed">
                        Tu lugar para disfrutar de los mejores productos artesanales en un ambiente acogedor.
                        Cada taza cuenta una historia, cada momento es especial.
                    </p>
                    <div class="flex space-x-4">
                        <a href="#" class="social-icon bg-gray-800 p-3 rounded-full hover:bg-gray-700">
                            <i class="fab fa-facebook-f text-lg"></i>
                        </a>
                        <a href="#" class="social-icon bg-gray-800 p-3 rounded-full hover:bg-gray-700">
                            <i class="fab fa-instagram text-lg"></i>
                        </a>
                        <a href="#" class="social-icon bg-gray-800 p-3 rounded-full hover:bg-gray-700">
                            <i class="fab fa-twitter text-lg"></i>
                        </a>
                        <a href="#" class="social-icon bg-gray-800 p-3 rounded-full hover:bg-gray-700">
                            <i class="fab fa-whatsapp text-lg"></i>
                        </a>
                    </div>
                </div>

                <!-- Contact Info -->
                <div>
                    <h4 class="text-lg font-semibold mb-6 accent-color">Contacto</h4>
                    <div class="space-y-4">
                        <p class="flex items-center text-gray-300">
                            <i class="fas fa-map-marker-alt mr-3 accent-color w-4"></i>
                            Calle Principal #123, Centro, Ciudad
                        </p>
                        <p class="flex items-center text-gray-300">
                            <i class="fas fa-phone-alt mr-3 accent-color w-4"></i>
                            (+123) 456-7890
                        </p>
                        <p class="flex items-center text-gray-300">
                            <i class="fas fa-envelope mr-3 accent-color w-4"></i>
                            info@cafeartesanal.com
                        </p>
                    </div>
                </div>

                <!-- Hours -->
                <div>
                    <h4 class="text-lg font-semibold mb-6 accent-color">Horario</h4>
                    <div class="space-y-3 text-gray-300">
                        <p><span class="font-medium">Lunes - Viernes:</span><br>7:00 AM - 8:00 PM</p>
                        <p><span class="font-medium">Sábados - Domingos:</span><br>8:00 AM - 9:00 PM</p>
                    </div>
                </div>
            </div>

            <div class="border-t border-gray-800 mt-12 pt-8">
                <div class="flex flex-col md:flex-row justify-between items-center">
                    <p class="text-center text-gray-400 mb-4 md:mb-0">
                        &copy; <?php echo date('Y'); ?> Café Artesanal. Todos los derechos reservados.
                    </p>
                    <div class="flex space-x-6 text-sm text-gray-400">
                        <a href="#" class="hover:text-white transition-colors">Política de Privacidad</a>
                        <a href="#" class="hover:text-white transition-colors">Términos de Servicio</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Back to Top Button -->
    <button id="back-to-top" class="fixed bottom-6 right-6 bg-orange-500 hover:bg-orange-600 text-white p-3 rounded-full shadow-lg transition-all duration-300 opacity-0 invisible">
        <i class="fas fa-arrow-up"></i>
    </button>

    <!-- Scripts -->
    <script src="./assets/js/index.js"></script>
    <script>
    // Objeto para almacenar las cantidades de cada producto
    const cantidades = {};
    let carrito = [];

    // Cargar carrito desde localStorage al iniciar
    document.addEventListener('DOMContentLoaded', function() {
        cargarCarrito();
        actualizarCarritoUI();
        inicializarMapa();
        manejarVisibilidadCarrito();

        // Error handling for images
        const images = document.querySelectorAll('img');
        images.forEach(img => {
            img.addEventListener('error', function() {
                this.src = './assets/img/default-product.jpg';
            });
        });
    });

    function toggleCategoria(id, header) {
        const productos = document.getElementById(id);
        const icono = header.querySelector('.icono');

        if (productos.classList.contains('mostrar')) {
            productos.classList.remove('mostrar');
            header.classList.remove('activa');
            setTimeout(() => {
                productos.style.display = 'none';
            }, 300);
        } else {
            productos.style.display = 'block';
            setTimeout(() => {
                productos.classList.add('mostrar');
                header.classList.add('activa');
            }, 10);
        }
    }

    // Función para cambiar la cantidad de un producto
    function cambiarCantidad(idProducto, cambio, stock) {
        if (!cantidades[idProducto]) {
            cantidades[idProducto] = 0;
        }

        const nuevaCantidad = cantidades[idProducto] + cambio;

        // Validar que no sea negativo y no supere el stock
        if (nuevaCantidad >= 0 && nuevaCantidad <= stock) {
            cantidades[idProducto] = nuevaCantidad;
            document.getElementById('cantidad-' + idProducto).textContent = nuevaCantidad;

            // Deshabilitar botón de restar si la cantidad es 0
            const btnMenos = document.querySelector(`button[onclick="cambiarCantidad(${idProducto}, -1, ${stock})"]`);
            btnMenos.disabled = nuevaCantidad === 0;

            // Deshabilitar botón de sumar si alcanza el stock máximo
            const btnMas = document.querySelector(`button[onclick="cambiarCantidad(${idProducto}, 1, ${stock})"]`);
            btnMas.disabled = nuevaCantidad >= stock;

            // Mostrar mensaje si alcanza el límite de stock
            if (nuevaCantidad >= stock && cambio > 0) {
                Swal.fire({
                    icon: 'info',
                    title: 'Stock límite alcanzado',
                    html: `Solo disponemos de <strong>${stock} unidades</strong> de este producto.`,
                    timer: 3000,
                    timerProgressBar: true,
                    showConfirmButton: false,
                    position: 'top-end',
                    toast: true,
                    background: '#fff3cd',
                    iconColor: '#ffc107'
                });
            }
        }
    }

    // Función para agregar al carrito con validación de stock
    function agregarAlCarrito(idProducto, nombreProducto, precio, stock) {
        const cantidad = cantidades[idProducto] || 0;

        if (cantidad === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Oops...',
                text: 'Selecciona una cantidad mayor a 0',
                confirmButtonColor: '#d33',
                confirmButtonText: 'Entendido'
            });
            return;
        }

        // Buscar si el producto ya está en el carrito
        const productoExistente = carrito.find(item => item.id === idProducto);
        
        if (productoExistente) {
            // Verificar que no supere el stock al actualizar
            const nuevaCantidadTotal = productoExistente.cantidad + cantidad;
            if (nuevaCantidadTotal > stock) {
                Swal.fire({
                    icon: 'error',
                    title: 'Stock insuficiente',
                    html: `No puedes agregar ${cantidad} unidades porque ya tienes ${productoExistente.cantidad} en tu carrito.<br>Stock disponible: ${stock}`,
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Entendido'
                });
                return;
            }
            // Actualizar cantidad si ya existe
            productoExistente.cantidad = nuevaCantidadTotal;
        } else {
            // Agregar nuevo producto al carrito
            carrito.push({
                id: idProducto,
                nombre: nombreProducto,
                precio: precio,
                cantidad: cantidad,
                stock: stock // Almacenamos el stock en el objeto del carrito
            });
        }

        // Guardar en localStorage
        guardarCarrito();

        // Mostrar confirmación
        const total = cantidad * precio;
        Swal.fire({
            icon: 'success',
            title: '¡Producto agregado!',
            html: `
                <p><strong>${nombreProducto}</strong></p>
                <p>Cantidad: ${cantidad}</p>
                <p>Precio unitario: $${precio.toFixed(2)}</p>
                <p>Total: <strong>$${total.toFixed(2)}</strong></p>
            `,
            confirmButtonColor: '#28a745',
            confirmButtonText: 'Aceptar',
            footer: '<a href="#carrito">Ver mi pedido</a>'
        });

        // Reset de la cantidad después de agregar
        cantidades[idProducto] = 0;
        document.getElementById('cantidad-' + idProducto).textContent = '0';

        // Rehabilitar botones después del reset
        const btnMenos = document.querySelector(`button[onclick*="cambiarCantidad(${idProducto}, -1"]`);
        const btnMas = document.querySelector(`button[onclick*="cambiarCantidad(${idProducto}, 1"]`);
        if (btnMenos) btnMenos.disabled = true;
        if (btnMas) btnMas.disabled = false;

        // Actualizar UI del carrito
        actualizarCarritoUI();
    }

    // Guardar carrito en localStorage
    function guardarCarrito() {
        localStorage.setItem('carritoCafeArtesanal', JSON.stringify(carrito));
    }

    // Cargar carrito desde localStorage
    function cargarCarrito() {
        const carritoGuardado = localStorage.getItem('carritoCafeArtesanal');
        if (carritoGuardado) {
            carrito = JSON.parse(carritoGuardado);
        }
    }

    // Actualizar la interfaz del carrito
    function actualizarCarritoUI() {
        const carritoProductos = document.getElementById('carrito-productos');
        const carritoVacio = document.getElementById('carrito-vacio');
        const resumenCarrito = document.getElementById('resumen-carrito');
        
        if (carrito.length === 0) {
            carritoProductos.classList.add('hidden');
            carritoVacio.classList.remove('hidden');
            resumenCarrito.classList.add('hidden');
            return;
        }

        carritoVacio.classList.add('hidden');
        carritoProductos.classList.remove('hidden');
        resumenCarrito.classList.remove('hidden');
        
        // Generar HTML de los productos
        let html = '';
        let subtotal = 0;
        
        carrito.forEach((producto, index) => {
            const totalProducto = producto.precio * producto.cantidad;
            subtotal += totalProducto;
            
            html += `
                <div class="producto-carrito border-b border-gray-200 py-4">
                    <div class="flex justify-between items-start mb-2">
                        <div>
                            <h4 class="font-medium">${producto.nombre}</h4>
                            <p class="text-sm text-gray-600">$${producto.precio.toFixed(2)} c/u</p>
                            <p class="text-xs text-gray-500">Disponible: ${producto.stock} unidades</p>
                        </div>
                        <button onclick="eliminarDelCarrito(${index})" class="text-red-500 hover:text-red-700">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="flex justify-between items-center">
                        <div class="flex items-center">
                            <button onclick="actualizarCantidadCarrito(${index}, -1)" class="px-2 py-1 bg-gray-200 rounded-l">
                                -
                            </button>
                            <span class="px-3 py-1 bg-gray-100">${producto.cantidad}</span>
                            <button onclick="actualizarCantidadCarrito(${index}, 1)" class="px-2 py-1 bg-gray-200 rounded-r">
                                +
                            </button>
                        </div>
                        <span class="font-medium">$${totalProducto.toFixed(2)}</span>
                    </div>
                </div>
            `;
        });

        carritoProductos.innerHTML = html;
        
        // Actualizar totales
        document.getElementById('subtotal').textContent = `$${subtotal.toFixed(2)}`;
        document.getElementById('total').textContent = `$${subtotal.toFixed(2)}`;
        
        // Mostrar/ocultar sección carrito según contenido
        manejarVisibilidadCarrito();
    }

    // Eliminar producto del carrito
    function eliminarDelCarrito(index) {
        Swal.fire({
            title: '¿Eliminar producto?',
            text: '¿Estás seguro de que quieres eliminar este producto de tu pedido?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                carrito.splice(index, 1);
                guardarCarrito();
                actualizarCarritoUI();
                
                Swal.fire(
                    'Eliminado',
                    'El producto ha sido removido de tu pedido.',
                    'success'
                );
            }
        });
    }

    // Actualizar cantidad en el carrito con validación de stock
    function actualizarCantidadCarrito(index, cambio) {
        const producto = carrito[index];
        const nuevaCantidad = producto.cantidad + cambio;
        
        // Validar que no sea negativo y no supere el stock
        if (nuevaCantidad > 0 && nuevaCantidad <= producto.stock) {
            producto.cantidad = nuevaCantidad;
            guardarCarrito();
            actualizarCarritoUI();
        } else if (nuevaCantidad > producto.stock) {
            Swal.fire({
                icon: 'error',
                title: 'Stock insuficiente',
                text: `No hay suficiente stock disponible (máximo: ${producto.stock})`,
                timer: 3000,
                timerProgressBar: true,
                showConfirmButton: false,
                position: 'top-end',
                toast: true
            });
        } else {
            // Si la cantidad es 0 o menor, eliminar del carrito
            eliminarDelCarrito(index);
        }
    }

    // Vaciar el carrito completo
    document.getElementById('vaciar-carrito').addEventListener('click', function() {
        Swal.fire({
            title: '¿Vaciar carrito?',
            text: '¿Estás seguro de que quieres eliminar todos los productos de tu pedido?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, vaciar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                carrito = [];
                guardarCarrito();
                actualizarCarritoUI();
                
                Swal.fire(
                    'Carrito vaciado',
                    'Todos los productos han sido removidos de tu pedido.',
                    'success'
                );
            }
        });
    });

    // Confirmar pedido con validación final de stock
    document.getElementById('confirmar-pedido').addEventListener('click', function() {
        if (carrito.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Carrito vacío',
                text: 'No hay productos en tu pedido para confirmar',
                confirmButtonColor: '#3085d6',
                confirmButtonText: 'Entendido'
            });
            return;
        }

        // Verificar stock antes de confirmar
        const productosSinStock = carrito.filter(producto => {
            return producto.cantidad > producto.stock;
        });

        if (productosSinStock.length > 0) {
            const listaProductos = productosSinStock.map(p => 
                `<li>${p.nombre} (Pedido: ${p.cantidad}, Stock: ${p.stock})</li>`
            ).join('');
            
            Swal.fire({
                icon: 'error',
                title: 'Problema con el stock',
                html: `Los siguientes productos no tienen suficiente stock:<ul>${listaProductos}</ul>`,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Entendido'
            });
            return;
        }

        // Mostrar resumen del pedido
        const total = carrito.reduce((sum, producto) => sum + (producto.precio * producto.cantidad), 0);
        
        Swal.fire({
            title: 'Confirmar Pedido',
            html: `
                <p>¿Estás seguro de que quieres confirmar tu pedido?</p>
                <div class="my-4 p-3 bg-gray-100 rounded">
                    <p class="font-medium">Resumen:</p>
                    <ul class="list-disc pl-5 mt-2">
                        ${carrito.map(p => `<li>${p.nombre} (${p.cantidad} x $${p.precio.toFixed(2)})</li>`).join('')}
                    </ul>
                    <p class="mt-2 font-bold">Total: $${total.toFixed(2)}</p>
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, confirmar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                // Aquí iría el código para enviar el pedido al servidor
                // Por ahora solo limpiaremos el carrito
                
                carrito = [];
                guardarCarrito();
                actualizarCarritoUI();
                
                Swal.fire(
                    '¡Pedido Confirmado!',
                    'Tu pedido ha sido recibido y está siendo procesado.',
                    'success'
                );
            }
        });
    });

    // Mostrar/ocultar sección carrito según contenido
    function manejarVisibilidadCarrito() {
        const seccionCarrito = document.getElementById('carrito');
        if (carrito.length > 0) {
            seccionCarrito.classList.remove('hidden');
        } else {
            seccionCarrito.classList.add('hidden');
        }
    }

    // Inicializar mapa
    function inicializarMapa() {
        const map = L.map('map').setView([19.4326, -99.1332], 15);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);
        
        L.marker([19.4326, -99.1332]).addTo(map)
            .bindPopup('Café Artesanal<br>¡Visítanos!')
            .openPopup();
    }

    // Back to top functionality
    const backToTopButton = document.getElementById('back-to-top');

    window.addEventListener('scroll', () => {
        if (window.pageYOffset > 300) {
            backToTopButton.classList.remove('opacity-0', 'invisible');
        } else {
            backToTopButton.classList.add('opacity-0', 'invisible');
        }
    });

    backToTopButton.addEventListener('click', () => {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
</script>
</body>
</html>