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

        header("refresh:5;url=../index.php");

        exit();
    }
    else {
        echo "Contraseña incorrecta. Por favor, inténtalo de nuevo.";
        header("refresh:3;url=../views/login.php");
    }
}