<?php
require_once('models/MySQL.php');

$mysql = new MySQL();
$mysql->conectar();
$pdo = $mysql->getConexion(); // Obtiene la conexión PDO
// Ahora puedes usar $mysql->getConexion() para obtener el objeto mysqli

$consulta = "SELECT productos.id_producto, productos.nombre, productos.descripcion, productos.precio, productos.stock, 
            categorias.nombre AS nombre_categoria, productos.imagen_url 
            FROM productos 
            JOIN categorias ON categorias.id_categoria = productos.id_categoria where estado < '1'";

try {
    $stmt = $pdo->prepare($consulta);
    $stmt->execute();
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error al obtener los productos: " . $e->getMessage();
    $productos = [];
}

$mysql->desconectar();
?>



<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Café Artesanal - Tu lugar para disfrutar</title>
    <!-- TailwindCSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Leaflet Maps -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <!-- AOS Animation -->
    <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
    <!-- Custom Styles -->
    <link rel="stylesheet" href="./assets/css/card_estilo.css">
    <link rel="stylesheet" href="./assets/css/index_estilo.css">


</head>

<body>
    <!-- Header and Navigation -->
    <header class="hero flex flex-col justify-center items-center relative">
        <div class="absolute inset-0 z-10 flex flex-col justify-between">
            <img src="./assets/image/Cafe fondo.jpg" alt="Fondo">
            <nav class="container mx-auto px-6 py-4 flex justify-between items-center">
                <div class="flex items-center">
                    <span class="text-white text-3xl">Café<span class="accent-color">Artesanal</span></span>
                </div>
                <div class="hidden md:flex items-center space-x-4">
                    <a href="#productos" class="nav-link">Productos</a>
                    <a href="#ubicacion" class="nav-link">Nos Ubicamos</a>
                </div>
                <div class="md:hidden">
                    <button id="mobile-menu-button" class="text-white">
                        <i class="fas fa-bars text-2xl"></i>
                    </button>
                </div>
            </nav>

            <div id="mobile-menu" class="md:hidden bg-white absolute top-16 right-0 w-full z-20 shadow-lg rounded-b-lg hidden">
                <div class="flex flex-col p-4">
                    <a href="#productos" class="py-2 px-4 text-gray-800 hover:bg-gray-100 rounded">Productos</a>
                    <a href="#ubicacion" class="py-2 px-4 text-gray-800 hover:bg-gray-100 rounded">Nos Ubicamos</a>
                </div>
            </div>

            <div class="container mx-auto px-6 py-20 text-center">
                <h1 class="text-4xl md:text-6xl font-bold text-white mb-4" data-aos="fade-up">Disfruta del mejor café</h1>
                <p class="text-xl text-white mb-8" data-aos="fade-up" data-aos-delay="100">Especialidades artesanales preparadas con amor</p>
                <a href="#productos" class="btn-primary px-8 py-3 rounded-full font-medium inline-block" data-aos="fade-up" data-aos-delay="200">Ver nuestros productos</a>
            </div>
        </div>
    </header>

    <!-- Products Section -->
    <section id="productos" class="py-20 bg-white">
        <div class="container mx-auto px-6">
            <h2 class="text-3xl font-bold mb-12 section-heading" data-aos="fade-up">Nuestros Productos</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">


                <?php if (count($productos) > 0): ?>
                    <?php foreach ($productos as $producto): ?>
                        <div class="card-container">
                            <div class="card">
                                <!-- Frente de la tarjeta -->
                                <div class="card-front">
                                    <img src="<?php echo $producto['imagen_url']; ?>" alt="<?php echo $producto['nombre']; ?>" class="product-img">
                                    <h3><?php echo htmlspecialchars($producto['nombre']); ?></h3>
                                </div>

                                <!-- Reverso de la tarjeta -->
                                <div class="card-back">
                                    <div class="row">
                                        <h4 id="descripcion-titulo">Descripción</h4>
                                        <p><?php echo htmlspecialchars($producto['descripcion']); ?></p>
                                        <br>
                                        <h4 id="descripcion-titulo">Categoría</h4>
                                        <p><?php echo htmlspecialchars($producto['nombre_categoria']); ?></p>
                                        <br>
                                        <h4 id="descripcion-titulo">Stock</h4>
                                        <p><?php echo htmlspecialchars($producto['stock']); ?></p>
                                        <br>
                                        <h4 id="descripcion-titulo">Precio</h4>
                                        <p>$<?php echo htmlspecialchars($producto['precio']); ?></p>
                                        <img src="<?php echo $producto['imagen_url']; ?>" alt="<?php echo $producto['nombre']; ?>" class="product-img">
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-center mt-5 text-black">No hay Productos.</p>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Location Section -->
    <section id="ubicacion" class="py-20 bg-gray-50">
        <div class="container mx-auto px-6">
            <h2 class="text-3xl font-bold mb-12 section-heading" data-aos="fade-up">Nos <span class="accent-color">Ubicamos</span></h2>

            <div class="flex flex-col md:flex-row gap-10">
                <div class="md:w-1/2" data-aos="fade-right">
                    <div id="map"></div>
                </div>

                <div class="md:w-1/2" data-aos="fade-left">
                    <h3 class="text-2xl font-semibold mb-4 accent-color">Visítanos</h3>
                    <p class="text-gray-600 mb-6">Estamos ubicados en el corazón de la ciudad, en un ambiente acogedor donde podrás disfrutar de nuestros productos mientras compartes momentos especiales.</p>

                    <div class="space-y-4">
                        <div class="flex items-start">
                            <i class="fas fa-map-marker-alt text-xl accent-color mt-1 mr-4"></i>
                            <div>
                                <h4 class="font-semibold">Dirección</h4>
                                <p class="text-gray-600">Calle Principal #123, Centro, Ciudad</p>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <i class="fas fa-clock text-xl accent-color mt-1 mr-4"></i>
                            <div>
                                <h4 class="font-semibold">Horario</h4>
                                <p class="text-gray-600">Lunes a Viernes: 7:00 AM - 8:00 PM</p>
                                <p class="text-gray-600">Sábados y Domingos: 8:00 AM - 9:00 PM</p>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <i class="fas fa-phone-alt text-xl accent-color mt-1 mr-4"></i>
                            <div>
                                <h4 class="font-semibold">Teléfono</h4>
                                <p class="text-gray-600">(+123) 456-7890</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white pt-16 pb-8">
        <div class="container mx-auto px-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
                <div>
                    <h3 class="text-2xl mb-4">Café<span class="accent-color">Artesanal</span></h3>
                    <p class="text-gray-400 mb-6">Tu lugar para disfrutar de los mejores productos artesanales en un ambiente acogedor.</p>
                    <div class="flex space-x-4">
                        <a href="javascript:void(0)" class="social-icon accent-color"><i class="fab fa-facebook-f text-xl"></i></a>
                        <a href="javascript:void(0)" class="social-icon accent-color"><i class="fab fa-instagram text-xl"></i></a>
                        <a href="javascript:void(0)" class="social-icon accent-color"><i class="fab fa-twitter text-xl"></i></a>
                        <a href="javascript:void(0)" class="social-icon accent-color"><i class="fab fa-whatsapp text-xl"></i></a>
                    </div>
                </div>

                <div>
                    <h4 class="text-lg font-semibold mb-4">Contacto</h4>
                    <div class="space-y-3">
                        <p class="flex items-center"><i class="fas fa-map-marker-alt mr-3 accent-color"></i> Calle Principal #123, Centro, Ciudad</p>
                        <p class="flex items-center"><i class="fas fa-phone-alt mr-3 accent-color"></i> (+123) 456-7890</p>
                        <p class="flex items-center"><i class="fas fa-envelope mr-3 accent-color"></i> info@cafeartesanal.com</p>
                    </div>
                </div>

                <div>
                    <h4 class="text-lg font-semibold mb-4">Horario</h4>
                    <div class="space-y-3">
                        <p>Lunes a Viernes: <span class="accent-color">7:00 AM - 8:00 PM</span></p>
                        <p>Sábados y Domingos: <span class="accent-color">8:00 AM - 9:00 PM</span></p>
                    </div>
                </div>
            </div>

            <div class="border-t border-gray-800 mt-12 pt-8">
                <p class="text-center text-gray-400">&copy; 2025 Café Artesanal. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>

    <script src="./assets/js/index.js"></script>
</body>

</html>