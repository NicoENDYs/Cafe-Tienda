<?php
require_once '../models/MySQL.php';

$mysql = new MySQL();
$mysql->conectar();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtener datos del formulario
    $id = $_POST['id_usuario'];
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

    if (!empty($password) && strlen($password) < 6) {
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
        // Verificar si el correo ya existe (excluyendo al usuario actual)
        $stmtVerificar = $mysql->prepare("SELECT id_usuario FROM usuarios WHERE correo = :correo AND id_usuario != :id");
        $stmtVerificar->execute([
            ':correo' => $correo,
            ':id' => $id
        ]);

        if ($stmtVerificar->rowCount() > 0) {
            header("Location: ../admin/usuarios.php?estado=error&mensaje=El correo ya está registrado por otro usuario");
            exit();
        }

        // Preparar consulta base (sin password)
        $sql = "UPDATE usuarios SET nombre = :nombre, correo = :correo, rol = :rol";
        $parametros = [
            ':nombre' => $nombre,
            ':correo' => $correo,
            ':rol' => $rol,
            ':id' => $id
        ];

        // Si se proporcionó nueva contraseña
        if (!empty($password)) {
            $passwordHash = password_hash($password, PASSWORD_BCRYPT);
            $sql .= ", password = :password";
            $parametros[':password'] = $passwordHash;
        }

        $sql .= " WHERE id_usuario = :id";

        // Ejecutar consulta
        $stmt = $mysql->prepare($sql);
        $resultado = $stmt->execute($parametros);

        if ($resultado) {
            header("Location: ../admin/usuarios.php?estado=exito&mensaje=Usuario actualizado correctamente");
        } else {
            header("Location: ../admin/usuarios.php?estado=error&mensaje=No se pudo actualizar el usuario");
        }

    } catch (PDOException $e) {
        error_log("Error al actualizar usuario: " . $e->getMessage());
        header("Location: ../admin/usuarios.php?estado=error&mensaje=Error interno del servidor");
    }

    $mysql->desconectar();
} else {
    header("Location: ../admin/usuarios.php");
}
?>