        <!-- sidebar.php -->

        <style>
            
/* ===== ESTILOS PARA DESKTOP (SIDEBAR) ===== */
.sidebar {
    width: 280px;
    background: linear-gradient(180deg, var(--primary-color) 0%, var(--primary-dark) 100%);
    color: white;
    position: fixed;
    height: 100vh;
    top: 0;
    left: 0;
    overflow-y: auto;
    box-shadow: 4px 0 10px rgba(0, 0, 0, 0.1);
    z-index: 1000;
    transition: transform 0.3s ease;
}

.sidebar-header {
    padding: 2rem 1.5rem;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    text-align: center;
}

.sidebar-header h3 {
    font-weight: 600;
    font-size: 1.4rem;
    margin-bottom: 0.5rem;
}

.sidebar-header p {
    font-size: 0.9rem;
    opacity: 0.8;
}

.sidebar-nav {
    padding: 1rem 0;
}

.nav-item {
    margin-bottom: 0.5rem;
}

.nav-link {
    display: flex;
    align-items: center;
    padding: 1rem 1.5rem;
    color: rgba(255, 255, 255, 0.8);
    text-decoration: none;
    transition: all 0.3s ease;
    position: relative;
}

.nav-link:hover,
.nav-link.active {
    background-color: rgba(255, 255, 255, 0.1);
    color: white;
    transform: translateX(5px);
}

.nav-link.active::after {
    content: "";
    position: absolute;
    right: 0;
    top: 0;
    bottom: 0;
    width: 4px;
    background-color: var(--secondary-color);
}

.nav-link i {
    margin-right: 0.75rem;
    font-size: 1.1rem;
    width: 20px;
    text-align: center;
}

/* Botón hamburguesa (oculto en desktop) */
.menu-toggle {
    display: none;
    background: var(--primary-color);
    color: white;
    border: none;
    padding: 1rem;
    font-size: 1.5rem;
    cursor: pointer;
    position: fixed;
    top: 0;
    left: 0;
    z-index: 1001;
    width: 100%;
    text-align: left;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.menu-toggle:hover {
    background: var(--primary-dark);
}

/* Layout principal */
.admin-layout {
    display: flex;
    min-height: 100vh;
}

.main-content {
    flex: 1;
    padding: 2rem;
    margin-left: 280px;
    background-color: var(--gray-100);
    transition: margin-left 0.3s ease;
}

/* ===== RESPONSIVE DESIGN - MOBILE ===== */
@media (max-width: 768px) {
    /* Mostrar botón hamburguesa */
    .menu-toggle {
        display: block;
    }
    
    /* Transformar sidebar en navbar móvil */
    .sidebar {
        transform: translateX(-100%);
        width: 100%;
        height: 100vh;
        top: 0;
        left: 0;
    }
    
    .sidebar.open {
        transform: translateX(0);
    }
    
    /* Overlay para cerrar el menú */
    .sidebar-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 999;
    }
    
    .sidebar-overlay.active {
        display: block;
    }
    
    /* Ajustar contenido principal */
    .main-content {
        margin-left: 0;
        padding-top: 5rem; /* Espacio para el botón hamburguesa */
        padding-left: 1rem;
        padding-right: 1rem;
    }
    
    /* Ajustar header del sidebar para móvil */
    .sidebar-header {
        padding-top: 4rem; /* Espacio para el botón */
        position: relative;
    }
    
    /* Botón cerrar en el sidebar móvil */
    .sidebar-close {
        position: absolute;
        top: 1rem;
        right: 1.5rem;
        background: none;
        border: none;
        color: white;
        font-size: 1.5rem;
        cursor: pointer;
        opacity: 0.8;
        transition: opacity 0.3s ease;
    }
    
    .sidebar-close:hover {
        opacity: 1;
    }
    
    /* Ajustar navegación para móvil */
    .nav-link {
        padding: 1.5rem 1.5rem;
        font-size: 1.1rem;
    }
    
    .nav-link:hover,
    .nav-link.active {
        transform: none;
        background-color: rgba(255, 255, 255, 0.2);
    }
}

        </style>
        <nav class="sidebar">
            <div class="sidebar-header">
                <h3><i class="fas fa-store"></i> Mi Tienda</h3>
            </div>
            <ul class="sidebar-nav">
                <li class="nav-item">
                    <a href="./dashboard.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>" data-section="dashboard">
                        <i class="fas fa-tachometer-alt"></i>
                        Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a href="./productos.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'productos.php' ? 'active' : '' ?>" data-section="productos">
                        <i class="fas fa-box"></i>
                        Productos
                    </a>
                </li>   
                <li class="nav-item">
                    <a class="nav-link" href="usuarios.php">
                        <i class="fas fa-users"></i> Usuarios
                    </a>
                </li>
            </ul>
        </nav>


        <script>

            
        // JavaScript para el menú responsive
document.addEventListener('DOMContentLoaded', function() {
    const menuToggle = document.getElementById('menuToggle');
    const sidebar = document.getElementById('sidebar');
    const sidebarClose = document.getElementById('sidebarClose');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    const navLinks = document.querySelectorAll('.nav-link');

    // Abrir menú
    menuToggle.addEventListener('click', function() {
        sidebar.classList.add('open');
        sidebarOverlay.classList.add('active');
        document.body.style.overflow = 'hidden'; // Prevenir scroll del body
    });

    // Cerrar menú con botón X
    sidebarClose.addEventListener('click', function() {
        closeSidebar();
    });

    // Cerrar menú con overlay
    sidebarOverlay.addEventListener('click', function() {
        closeSidebar();
    });

    // Cerrar menú al hacer clic en un enlace (solo en móvil)
    navLinks.forEach(function(link) {
        link.addEventListener('click', function() {
            if (window.innerWidth <= 768) {
                closeSidebar();
            }
        });
    });

    // Función para cerrar el sidebar
    function closeSidebar() {
        sidebar.classList.remove('open');
        sidebarOverlay.classList.remove('active');
        document.body.style.overflow = ''; // Restaurar scroll del body
    }

    // Cerrar menú con tecla Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && sidebar.classList.contains('open')) {
            closeSidebar();
        }
    });

    // Manejar redimensionamiento de ventana
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768) {
            closeSidebar();
        }
    });
});
        </script>