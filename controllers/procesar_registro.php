<?php
require_once '../models/MySQL.php';

$mysql = new MySQL();
$mysql->conectar();
$pdo = $mysql->getConexion();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nombre = $_POST['nombre'] ?? '';
    $correo = $_POST['correo'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirmar_password = $_POST['confirmar_password'] ?? '';

    if ($password !== $confirmar_password) {
        header("Location: ../views/registro.php?estado=error");
        exit();
    }

    try {
        // Verificar si el correo ya está registrado
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM Usuarios WHERE correo = :correo");
        $stmt->bindParam(':correo', $correo, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->fetchColumn() > 0) {
            header("Location: ../views/registro.php?estado=correo_existente");
            exit();
        }

        // Insertar el nuevo usuario
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO Usuarios (nombre, correo, password, rol) VALUES (:nombre, :correo, :password, 'cliente')");
        $stmt->bindParam(':nombre', $nombre, PDO::PARAM_STR);
        $stmt->bindParam(':correo', $correo, PDO::PARAM_STR);
        $stmt->bindParam(':password', $hashed_password, PDO::PARAM_STR);
        $stmt->execute();

        header("Location: ../views/login.php?estado=exito");
        exit();

    } catch (PDOException $e) {
        // En producción, guarda este error en logs
        header("Location: ../views/registro.php?estado=error_db");
        exit();
    }

    $mysql->desconectar();
}
