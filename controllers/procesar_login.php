<?php
require_once '../models/MySQL.php';

$mysql = new MySQL();
$mysql->conectar();

// Iniciar sesión
session_start();

// Procesar el formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = trim($_POST['correo']);
    $password = $_POST['password'];
    
    // Validar campos
    if (empty($correo) || empty($password)) {
        $_SESSION['error'] = "Por favor, complete todos los campos.";
        header("Location: ../views/login.php");
        exit();
    }
    
    // Buscar usuario en la base de datos
    try {
        $stmt = $conn->prepare("SELECT * FROM usuarios WHERE correo = $correo");
        
        
        if ($stmt->rowCount() === 1) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Verificar contraseña
            if (password_verify($password, $user['password'])) {
                // Iniciar sesión
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_nombre'] = $user['nombre'];
                $_SESSION['user_rol'] = $user['rol'];
                
                // Redirigir según el rol
                if ($user['rol'] === 'admin') {
                    header("Location: ../views/admin/dashboard.php");
                } else {
                    header("Location: ../views/usuario/inicio.php");
                }
                exit();
            } else {
                $_SESSION['error'] = "Usuario o contraseña incorrectos.";
            }
        } else {
            $_SESSION['error'] = "Usuario o contraseña incorrectos.";
        }
    } catch(PDOException $e) {
        $_SESSION['error'] = "Error al procesar el login. Por favor, intente nuevamente.";
    }
    
    // Si hay error, redirigir de vuelta al login
    header("Location: ../views/login.php");
    exit();
} else {
    // Si no es POST, redirigir al login
    header("Location: ../views/login.php");
    exit();
}
?>