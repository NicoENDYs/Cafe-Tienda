<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Café & Bebidas El Buen Sabor</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/registro_estilo.css">
    <!---notificaciones--->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../assets/js/notificaciones.js"></script>
</head>
<body>
    <div class="register-container">
        <div class="logo">
            <img src="logo-cafe.png" alt="Café & Bebidas El Buen Sabor">
        </div>
        
        <h1>Crear una cuenta</h1>

        <form method="POST" action="../controllers/procesar_registro.php" id="registerForm">
            <div class="form-group">
                <label for="nombre">Nombre completo:</label>
                <input type="text" id="nombre" name="nombre" required placeholder="Ingresa tu nombre completo">
            </div>
            
            <div class="form-group">
                <label for="correo">Correo electrónico:</label>
                <input type="email" id="correo" name="correo" required placeholder="Ingresa tu correo electrónico">
            </div>
            
            <div class="form-group">
                <label for="password">Contraseña:</label>
                <div class="password-container">
                    <input type="password" id="password" name="password" required placeholder="Crea una contraseña (mínimo 8 caracteres)" minlength="8" oninput="updatePasswordStrength()">
                    <span class="toggle-password" onclick="togglePasswordVisibility('password')">👁️</span>
                </div>
                <div class="password-strength">
                    <div class="password-strength-bar" id="passwordStrengthBar"></div>
                </div>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirmar contraseña:</label>
                <div class="password-container">
                    <input type="password" id="confirmar_password" name="confirmar_password" required placeholder="Repite tu contraseña" minlength="8">
                    <span class="toggle-password" onclick="togglePasswordVisibility('confirmar_password')">👁️</span>
                </div>
            </div>
            
            <button type="submit" class="btn">Registrarse</button>
            
            <div class="links">
                ¿Ya tienes una cuenta? <a href="../views/login.php">Inicia sesión</a>
            </div>
        </form>
    </div>
    <script src="../assets/js/esconder_password.js"></script>
    <script src="../assets/js/fortaleza_password.js"></script>
    <script src="../assets/js/validar_password.js"></script>
</body>
</html>