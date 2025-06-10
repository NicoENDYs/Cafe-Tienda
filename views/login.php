<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Café & Bebidas El Buen Sabor - Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/login_estilo.css">
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
                <label for="nombre">Usuario:</label>
                <input type="text" id="nombre" name="nombre" required placeholder="Ingresa tu usuario">
            </div>
            
            <div class="form-group">
                <label for="password">Contraseña:</label>
                <div class="password-container">
                    <input type="password" id="password" name="password" required placeholder="Ingresa tu contraseña">
                    <span class="toggle-password" onclick="togglePasswordVisibility()">👁️</span>
                </div>
            </div>
            
            <button type="submit" class="btn">Ingresar</button>
            
            <div class="links">
                <a href="forgot-password.php">¿Olvidaste tu contraseña?</a>
                <a href="help.php">¿Necesitas ayuda?</a>
            </div>
            
            <div class="divider">o</div>
            
            <a href="register.php" class="register-btn">Crear una cuenta nueva</a>
        </form>
    </div>
    <script src="../assets/js/esconder_password.js"></script>
</body>
</html>