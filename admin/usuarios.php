<?php
require_once('../models/MySQL.php');

session_start();

if ($_SESSION['rol'] != "admin") {
    header("refresh:1;url=../views/login.php");
    exit();
} 

$mysql = new MySQL();
$mysql->conectar();

try {
    // Consulta de usuarios activos (estado = 0)
    $stmt = $mysql->prepare("
        SELECT 
            id_usuario,
            nombre,
            correo,
            rol,
            creado_en
        FROM usuarios 
        WHERE estado = 0
    ");
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
    <title>Panel de Administración - Usuarios</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="../assets/css/admin_usuarios.css">
</head>
<body>
        <?php include('sidebar.php'); ?>
        <button class="menu-toggle" id="menuToggle">
            <i class="fas fa-bars"></i>
            <span style="margin-left: 10px;">Mi Tienda</span>
        </button>

        <!-- Overlay para cerrar el menú en móvil -->
        <div class="sidebar-overlay" id="sidebarOverlay"></div>

        <!-- Sidebar/Navbar -->
        <nav class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <!-- Botón cerrar para móvil -->
                <!-- <button class="sidebar-close" id="sidebarClose">
            <i class="fas fa-times"></i>
        </button> -->
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
                <!-- Dentro de la barra lateral -->
                <li class="nav-item">
                    <a class="nav-link" href="usuarios.php">
                        <i class="fas fa-users"></i> Usuarios
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link" data-section="logout">
                        <i class="fas fa-sign-out-alt"></i>
                        Cerrar Sesión
                    </a>
                </li>
            </ul>
        </nav>

    <div class="admin-container">
        <!-- Encabezado -->
        <div class="page-header">
            <h1><i class="fas fa-users"></i> GESTIÓN DE USUARIOS</h1>
            <p>Administra los usuarios del sistema</p>
        </div>

        <!-- Mensajes de estado -->
        <?php if(isset($_GET['estado'])): ?>
            <div class="alert alert-<?= $_GET['estado'] === 'exito' ? 'success' : 'danger' ?> alert-dismissible fade show" role="alert">
                <strong><?= $_GET['estado'] === 'exito' ? 'Éxito!' : 'Error!' ?></strong> 
                <?= $_GET['mensaje'] ?? '' ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Lista de usuarios -->
        <div class="section-header">
            <h2 class="section-title"><i class="fas fa-list"></i> LISTA DE USUARIOS</h2>
            <button class="btn btn-primary" id="newUserBtn">
                <i class="fas fa-plus"></i> NUEVO USUARIO
            </button>
        </div>

        <div class="user-list">
            <?php foreach ($result as $usuario): ?>
                <div class="user-card">
                    <div class="user-header">
                        <div>
                            <div class="user-name"><?= htmlspecialchars($usuario['nombre']) ?></div>
                            <div class="user-email"><?= htmlspecialchars($usuario['correo']) ?></div>
                        </div>
                        <div class="user-role <?= $usuario['rol'] == 'admin' ? 'admin' : '' ?>">
                            <?= ucfirst(htmlspecialchars($usuario['rol'])) ?>
                        </div>
                    </div>
                    <div class="user-actions">
                        <button class="btn btn-primary edit-btn" 
                                data-id="<?= $usuario['id_usuario'] ?>"
                                data-nombre="<?= htmlspecialchars($usuario['nombre']) ?>"
                                data-email="<?= htmlspecialchars($usuario['correo']) ?>"
                                data-rol="<?= htmlspecialchars($usuario['rol']) ?>">
                            <i class="fas fa-edit"></i> Editar
                        </button>
                        <a href="../controllers/EliminarUsuario.php?id=<?= $usuario['id_usuario'] ?>" 
                           class="btn btn-danger delete-btn">
                            <i class="fas fa-trash"></i> Eliminar
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Formulario de nuevo usuario (inicialmente oculto) -->
        <div id="newUserForm" style="display: none;">
            <div class="form-container">
                <h2 class="form-title">NUEVO USUARIO</h2>
                <form action="../controllers/NuevoUsuario.php" method="POST">
                    <div class="form-group">
                        <label for="name">Nombre completo</label>
                        <input type="text" id="name" name="nombre" class="form-control" placeholder="Ingrese el nombre completo" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Correo electrónico</label>
                        <input type="email" id="email" name="correo" class="form-control" placeholder="Ingrese el correo electrónico" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Contraseña</label>
                        <input type="password" id="password" name="password" class="form-control" placeholder="Cree una contraseña segura" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirmPassword">Confirmar contraseña</label>
                        <input type="password" id="confirmPassword" name="confirmar_password" class="form-control" placeholder="Confirme la contraseña" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="role">Rol</label>
                        <select id="role" name="rol" class="form-control" required>
                            <option value="">Seleccione un rol</option>
                            <option value="admin">Administrador</option>
                            <option value="mesero">Mesero</option>
                            <option value="cocina">Cocinero</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="submit-btn">GUARDAR USUARIO</button>
                </form>
            </div>
        </div>

        <!-- Formulario de edición (oculto) -->
        <div id="editUserForm" style="display: none;">
            <div class="form-container">
                <h2 class="form-title">EDITAR USUARIO</h2>
                <form action="../controllers/EditarUsuario.php" method="POST">
                    <input type="hidden" id="edit_id" name="id_usuario">
                    
                    <div class="form-group">
                        <label for="edit_name">Nombre completo</label>
                        <input type="text" id="edit_name" name="nombre" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_email">Correo electrónico</label>
                        <input type="email" id="edit_email" name="correo" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_password">Nueva contraseña (dejar vacío para no cambiar)</label>
                        <input type="password" id="edit_password" name="password" class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_confirmPassword">Confirmar nueva contraseña</label>
                        <input type="password" id="edit_confirmPassword" name="confirmar_password" class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_role">Rol</label>
                        <select id="edit_role" name="rol" class="form-control" required>
                            <option value="admin">Administrador</option>
                            <option value="mesero">Mesero</option>
                            <option value="cocina">Cocinero</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="submit-btn">ACTUALIZAR USUARIO</button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Mostrar/ocultar formulario de nuevo usuario
        document.getElementById('newUserBtn').addEventListener('click', function() {
            const form = document.getElementById('newUserForm');
            const editForm = document.getElementById('editUserForm');
            
            if (form.style.display === 'none') {
                form.style.display = 'block';
                editForm.style.display = 'none';
                form.scrollIntoView({ behavior: 'smooth' });
            } else {
                form.style.display = 'none';
            }
        });
        
        // Manejar edición de usuarios
        document.querySelectorAll('.edit-btn').forEach(button => {
            button.addEventListener('click', function() {
                const form = document.getElementById('editUserForm');
                const newForm = document.getElementById('newUserForm');
                
                // Obtener datos del usuario
                const id = this.getAttribute('data-id');
                const nombre = this.getAttribute('data-nombre');
                const email = this.getAttribute('data-email');
                const rol = this.getAttribute('data-rol');
                
                // Llenar formulario
                document.getElementById('edit_id').value = id;
                document.getElementById('edit_name').value = nombre;
                document.getElementById('edit_email').value = email;
                document.getElementById('edit_role').value = rol;
                
                // Mostrar formulario
                form.style.display = 'block';
                newForm.style.display = 'none';
                form.scrollIntoView({ behavior: 'smooth' });
            });
        });
        
        // Confirmación para eliminar usuario
        document.querySelectorAll('.delete-btn').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const url = this.getAttribute('href');
                const userName = this.closest('.user-card').querySelector('.user-name').textContent;
                
                Swal.fire({
                    title: '¿Estás seguro?',
                    text: `Vas a eliminar al usuario ${userName}`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = url;
                    }
                });
            });
        });
        
        // Validación de contraseñas en nuevo usuario
        document.querySelector('#newUserForm form').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Las contraseñas no coinciden'
                });
            }
        });
        
        // Validación de contraseñas en edición
        document.querySelector('#editUserForm form').addEventListener('submit', function(e) {
            const password = document.getElementById('edit_password').value;
            const confirmPassword = document.getElementById('edit_confirmPassword').value;
            
            if (password && password !== confirmPassword) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Las contraseñas no coinciden'
                });
            }
        });

        
    </script>
</body>
</html>