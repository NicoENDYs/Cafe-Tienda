<?php
require_once '../models/MySQL.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'];
    $correo = $_POST['correo'];
    $password = $_POST['password'];
    $confirmar_password = $_POST['confirmar_password'];
    $rol = $_POST['rol'];

    // Validar campos
    if (empty($nombre) || empty($correo) || empty($password) || empty($rol)) {
        header("Location: ../admin/usuarios.php?estado=error&mensaje=Todos los campos son obligatorios");
        exit();
    }

    // Validar que las contraseñas coincidan
    if ($password !== $confirmar_password) {
        header("Location: ../admin/usuarios.php?estado=error&mensaje=Las contraseñas no coinciden");
        exit();
    }

    $mysql = new MySQL();
    $mysql->conectar();
    $pdo = $mysql->getConexion();

    try {
        // Verificar si el correo ya está registrado
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE correo = :correo");
        $stmt->bindParam(':correo', $correo, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->fetchColumn() > 0) {
            header("Location: ../admin/usuarios.php?estado=error&mensaje=El correo ya está registrado");
            exit();
        }
        
        // Hash de contraseña
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insertar el nuevo usuario
        $stmt = $pdo->prepare("
            INSERT INTO usuarios (nombre, correo, password, rol, estado)
            VALUES (:nombre, :correo, :password, :rol, 0)
        ");

        $resultado = $stmt->execute([
            ':nombre' => $nombre,
            ':correo' => $correo,
            ':password' => $hashed_password,
            ':rol' => $rol
        ]);

        if ($resultado) {
            header("Location: ../admin/usuarios.php?estado=exito");
        } else {
            header("Location: ../admin/usuarios.php?estado=error&mensaje=Error al crear usuario");
        }
    } catch (PDOException $e) {
        header("Location: ../admin/usuarios.php?estado=error&mensaje=Error en el sistema: " . urlencode($e->getMessage()));
    } finally {
        $mysql->desconectar();
    }
}