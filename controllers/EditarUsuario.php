<?php
require_once '../models/MySQL.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id_usuario'];
    $nombre = $_POST['nombre'];
    $correo = $_POST['correo'];
    $password = $_POST['password'];
    $confirmar_password = $_POST['confirmar_password'];
    $rol = $_POST['rol'];

    // Validar campos obligatorios
    if (empty($nombre) || empty($correo) || empty($rol)) {
        header("Location: ../admin/usuarios.php?estado=error&mensaje=Nombre, correo y rol son obligatorios");
        exit();
    }

    // Validar contraseñas si se proporcionaron
    if (!empty($password) && $password !== $confirmar_password) {
        header("Location: ../admin/usuarios.php?estado=error&mensaje=Las contraseñas no coinciden");
        exit();
    }

    $mysql = new MySQL();
    $mysql->conectar();
    $pdo = $mysql->getConexion();

    try {
        // Verificar si el correo ya está registrado en otro usuario
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE correo = :correo AND id_usuario != :id");
        $stmt->bindParam(':correo', $correo, PDO::PARAM_STR);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->fetchColumn() > 0) {
            header("Location: ../admin/usuarios.php?estado=error&mensaje=El correo ya está registrado en otra cuenta");
            exit();
        }

        // Construir consulta dinámica
        $sql = "UPDATE usuarios SET 
                nombre = :nombre,
                correo = :correo,
                rol = :rol";
        
        $params = [
            ':nombre' => $nombre,
            ':correo' => $correo,
            ':rol' => $rol,
            ':id' => $id
        ];

        // Agregar contraseña si se proporcionó
        if (!empty($password)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $sql .= ", password = :password";
            $params[':password'] = $hashed_password;
        }

        $sql .= " WHERE id_usuario = :id";

        $stmt = $pdo->prepare($sql);
        $resultado = $stmt->execute($params);

        if ($resultado) {
            header("Location: ../admin/usuarios.php?estado=exito");
        } else {
            header("Location: ../admin/usuarios.php?estado=error&mensaje=Error al actualizar usuario");
        }
    } catch (PDOException $e) {
        header("Location: ../admin/usuarios.php?estado=error&mensaje=Error en el sistema: " . urlencode($e->getMessage()));
    } finally {
        $mysql->desconectar();
    }
}