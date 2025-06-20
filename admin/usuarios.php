<?php
require_once('../models/MySQL.php');

$mysql = new MySQL();
$mysql->conectar();

try {
    // Consulta de usuarios
    $stmt = $mysql->prepare("
        SELECT 
            id_usuario,
            nombre,
            correo,
            rol,
            creado_en
        FROM usuarios
        ORDER BY creado_en DESC
    ");
    $stmt->execute();
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../assets/js/notificaciones.js"></script>
    <link rel="stylesheet" href="../assets/css/admin_usuarios.css">
</head>

<body>
    <div class="admin-layout">
        <?php include('sidebar.php'); ?>

        <main class="main-content">
            <div class="page-header">
                <h1><i class="fas fa-users"></i> Gestión de Usuarios</h1>
                <p>Administra los usuarios registrados en el sistema</p>
            </div>

            <div class="stats-grid">
                <!-- Estadísticas rápidas -->
                <div class="stat-card">
                    <div class="stat-icon bg-primary">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo count($usuarios); ?></h3>
                        <p>Usuarios totales</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon bg-success">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo count(array_filter($usuarios, fn($u) => $u['rol'] === 'admin')); ?></h3>
                        <p>Administradores</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon bg-info">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo count(array_filter($usuarios, fn($u) => $u['rol'] === 'cliente')); ?></h3>
                        <p>Clientes</p>
                    </div>
                </div>
            </div>

            <div class="products-section">
                <div class="section-header">
                    <h2 class="section-title"><i class="fas fa-list"></i> Lista de Usuarios</h2>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#nuevoUsuarioModal">
                        <i class="fas fa-plus"></i> Añadir Usuario
                    </button>
                    <!-- Modal para nuevo usuario -->
               <div class="modal fade user-modal" id="nuevoUsuarioModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-body">
                                <div class="form-container">
                                    <h2>Nuevo Usuario</h2>
                                    <form id="usuarioForm" class="user-form" action="../controllers/NuevoUsuario.php" method="POST" novalidate>
                                        <div class="form-group">
                                            <input type="text" name="nombre" placeholder="Nombre completo" required />
                                        </div>
                                        <div class="form-group">
                                            <input type="email" name="correo" placeholder="Correo electrónico" required />
                                        </div>
                                        <div class="form-group">
                                            <input type="password" name="password" placeholder="Contraseña" required />
                                        </div>
                                        <div class="form-group">
                                            <select name="rol" required>
                                                <option value="">Seleccione un rol</option>
                                                <option value="admin">Administrador</option>
                                                <option value="cliente">Cliente</option>
                                            </select>
                                        </div>
                                        <button type="submit">Guardar Usuario</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                </div>

                <div class="table-container">
                    <?php if (count($usuarios) > 0): ?>
                        <table class="table" id="usuariosTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Correo</th>
                                    <th>Rol</th>
                                    <th>Fecha Registro</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($usuarios as $usuario): ?>
                                    <tr>
                                        <td><?php echo $usuario['id_usuario']; ?></td>
                                        <td><?php echo htmlspecialchars($usuario['nombre']); ?></td>
                                        <td><?php echo htmlspecialchars($usuario['correo']); ?></td>
                                        <td>
                                            <span class="badge <?php echo $usuario['rol'] === 'admin' ? 'bg-primary' : 'bg-info'; ?>">
                                                <?php echo ucfirst($usuario['rol']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($usuario['creado_en'])); ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editarUsuarioModal<?php echo $usuario['id_usuario']; ?>">
                                                <i class="fas fa-edit"></i> Editar
                                            </button>
                                            <button class="btn btn-sm btn-danger" onclick="confirmarEliminar(<?php echo $usuario['id_usuario']; ?>)">
                                                <i class="fas fa-trash"></i> Eliminar
                                            </button>
                                        </td>
                                    </tr>

                                    <!-- Modal de edición para cada usuario -->
                                    <div class="modal fade" id="editarUsuarioModal<?php echo $usuario['id_usuario']; ?>" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-body">
                                                    <div class="form-container">
                                                        <h2>Editar Usuario</h2>
                                                        <form action="../controllers/EditarUsuario.php" method="POST">
                                                            <input type="hidden" name="id_usuario" value="<?php echo $usuario['id_usuario']; ?>">
                                                            
                                                            <div class="form-group">
                                                                <input type="text" name="nombre" value="<?php echo htmlspecialchars($usuario['nombre']); ?>" placeholder="Nombre" required>
                                                            </div>
                                                            
                                                            <div class="form-group">
                                                                <input type="email" name="correo" value="<?php echo htmlspecialchars($usuario['correo']); ?>" placeholder="Correo" required>
                                                            </div>
                                                            
                                                            <div class="form-group">
                                                                <input type="password" name="password" placeholder="Nueva contraseña (dejar en blanco para no cambiar)">
                                                            </div>
                                                            
                                                            <div class="form-group">
                                                                <select name="rol" required>
                                                                    <option value="admin" <?php echo $usuario['rol'] === 'admin' ? 'selected' : ''; ?>>Administrador</option>
                                                                    <option value="cliente" <?php echo $usuario['rol'] === 'cliente' ? 'selected' : ''; ?>>Cliente</option>
                                                                </select>
                                                            </div>
                                                            
                                                            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p class="text-center mt-5">No hay usuarios registrados.</p>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function confirmarEliminar(idUsuario) {
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
                    window.location.href = `../controllers/EliminarUsuario.php?id=${idUsuario}`;
                }
            });
        }
    </script>
</body>
</html>