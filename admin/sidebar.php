        <!-- sidebar.php -->
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