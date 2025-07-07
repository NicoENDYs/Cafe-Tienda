<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Café & Bebidas El Buen Sabor - Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/login_estilo.css">
    <!---notificaciones--->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../assets/js/notificaciones.js"></script>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <!-- Imagen del logo-->
            <img src="logo-cafe.png" alt="Café & Bebidas El Buen Sabor">
        </div>
        
        <h1>Iniciar Sesión</h1>
        
        <?php if (!empty($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="../controllers/procesar_login.php">
            <div class="form-group">
                <label for="correo">Correo:</label>
                <input type="text" id="correo" name="correo" required placeholder="Ingresa tu correo electrónico">
            </div>
            
            <div class="form-group">
                <label for="password">Contraseña:</label>
                <div class="password-container">
                    <input type="password" id="password" name="password" required placeholder="Ingresa tu contraseña">
                    <span class="toggle-password" onclick="togglePasswordVisibility('password')">👁️</span>
                </div>
            </div>
            
            <button type="submit" class="btn">Ingresar</button>
        </form>
    </div>
    <script src="../assets/js/esconder_password.js"></script>

</body>
</html>