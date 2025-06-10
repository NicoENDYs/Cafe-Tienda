<?php
require_once '../models/MySQL.php';

$mysql = new MySQL();
$mysql->conectar();

if ($_SERVER['REQUEST_METHOD'] === 'POST'){

    $nombre = $_POST['nombre'];
    $correo = $_POST['correo'];
    $password = $_POST['password'];
    $confirmar_password = $_POST['confirmar_password'];

    if ($password !== $confirmar_password) {
        header("refresh:1;url=../views/registro.php?estado=error");
        exit();
    }

    // Verificar si el correo ya está registrado
    $resultado = $mysql->efectuarConsulta("SELECT * FROM Usuarios WHERE correo = '$correo'");
    if ($resultado->num_rows > 0) {
        header("refresh:1;url=../views/registro.php?estado=correo_existente");
        exit();
    }

    // Insertar el nuevo usuario en la base de datos
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $mysql->efectuarConsulta("INSERT INTO Usuarios (nombre, correo, contraseña, rol) VALUES ('$nombre', '$correo', '$hashed_password', 'cliente')");

    header("refresh:3;url=../views/login.php?estado=exito");
}