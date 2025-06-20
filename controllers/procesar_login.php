<?php
require_once '../models/MySQL.php';
session_start();

$mysql = new MySQL();
$mysql->conectar();
$pdo = $mysql->getConexion();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $correo = $_POST['correo'] ?? '';
    $password = $_POST['password'] ?? '';

    try {
        $stmt = $pdo->prepare("SELECT * FROM Usuarios WHERE correo = :correo");
        $stmt->bindParam(':correo', $correo, PDO::PARAM_STR);
        $stmt->execute();
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario && password_verify($password, $usuario['password'])) {
            $_SESSION['nombre'] = $usuario['nombre'];
            $_SESSION['correo'] = $usuario['correo'];
            $_SESSION['rol'] = $usuario['rol'];

            if ($usuario['rol'] === 'admin') {
                $_SESSION['admin'] = true;
                header("Location: ../admin/dashboard.php");
            } else {
                $_SESSION['admin'] = false;
                header("Location: ../index.php");
            }

            exit();
        } else {
            header("Location: ../views/login.php?estado=error_password");
            exit();
        }

    } catch (PDOException $e) {
        // En producción, guarda el error en un log
        header("Location: ../views/login.php?estado=error_db");
        exit();
    }

    $mysql->desconectar();
}
?>
