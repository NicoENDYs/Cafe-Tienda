<?php
require_once '../models/MySQL.php';

session_start();

$mysql = new MySQL();
$mysql->conectar();

if ($_SERVER['REQUEST_METHOD'] == 'POST'){

    $correo = $_POST['correo'];
    $password = $_POST['password'];

    $resultado = $mysql->efectuarConsulta("SELECT * FROM Usuarios WHERE correo = '$correo';");
    $usuario = $resultado->fetch_assoc();

    if (password_verify($password, $usuario['password'])) {

        $_SESSION['nombre'] = $usuario['nombre'];
        $_SESSION['correo'] = $usuario['correo'];
        $_SESSION['rol'] = $usuario['rol'];

        if ($usuario['rol'] == 'admin') {
            $_SESSION['admin'] = true;
            header("Location: ../admin/dashboard.php");
        } else {
            $_SESSION['admin'] = false;
            header("Location: ../index.php");
        }

        exit();
    }
    else {
        header("Location: ../views/login.php?estado=error_password");
        exit();
    }
}