<?php
require_once '../models/MySQL.php';

$mysql = new MySQL();
$mysql->conectar();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtener datos del formulario
    $nombre = $_POST['nombre'];
    $correo = $_POST['correo'];
    $password = $_POST['password'];
    $rol = $_POST['rol'];

    // Validación básica
    $errores = [];

    if (empty($nombre)) {
        $errores[] = "El nombre es obligatorio";
    }

    if (empty($correo) || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $errores[] = "Correo electrónico no válido";
    }

    if (empty($password) || strlen($password) < 6) {
        $errores[] = "La contraseña debe tener al menos 6 caracteres";
    }

    if (empty($rol) || !in_array($rol, ['admin', 'cliente'])) {
        $errores[] = "Rol no válido";
    }

    // Si hay errores, redireccionar
    if (!empty($errores)) {
        $mensajeError = urlencode(implode("\\n", $errores));
        header("Location: ../admin/usuarios.php?estado=error&mensaje=$mensajeError");
        exit();
    }

    try {
        // Verificar si el correo ya existe
        $stmtVerificar = $mysql->prepare("SELECT id_usuario FROM usuarios WHERE correo = :correo");
        $stmtVerificar->execute([':correo' => $correo]);

        if ($stmtVerificar->rowCount() > 0) {
            header("Location: ../admin/usuarios.php?estado=error&mensaje=El correo ya está registrado");
            exit();
        }

        // Hash de la contraseña
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);

        // Insertar nuevo usuario
        $stmtInsert = $mysql->prepare("
            INSERT INTO usuarios (nombre, correo, password, rol)
            VALUES (:nombre, :correo, :password, :rol)
        ");

        $resultado = $stmtInsert->execute([
            ':nombre' => $nombre,
            ':correo' => $correo,
            ':password' => $passwordHash,
            ':rol' => $rol
        ]);

        if ($resultado) {
            header("Location: ../admin/usuarios.php?estado=exito&mensaje=Usuario creado correctamente");
        } else {
            header("Location: ../admin/usuarios.php?estado=error&mensaje=Error al crear el usuario");
        }

    } catch (PDOException $e) {
        error_log("Error al insertar usuario: " . $e->getMessage());
        header("Location: ../admin/usuarios.php?estado=error&mensaje=Error interno del servidor");
    }

    $mysql->desconectar();
} else {
    header("Location: ../admin/usuarios.php");
}
?>