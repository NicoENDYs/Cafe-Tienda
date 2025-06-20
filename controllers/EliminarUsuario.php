<?php
require_once '../models/MySQL.php';

if (isset($_GET['id'])) {
    $mysql = new MySQL();
    $mysql->conectar();

    try {
        $stmt = $mysql->prepare("DELETE FROM usuarios WHERE id_usuario = ?");
        $stmt->execute([$_GET['id']]);
        
        if ($stmt->rowCount() > 0) {
            header("Location: ../admin/usuarios.php?estado=exito&mensaje=Usuario eliminado correctamente");
        } else {
            header("Location: ../admin/usuarios.php?estado=error&mensaje=El usuario no existe");
        }
    } catch (PDOException $e) {
        error_log("Error al eliminar usuario: " . $e->getMessage());
        header("Location: ../admin/usuarios.php?estado=error&mensaje=Error al eliminar");
    }

    $mysql->desconectar();
} else {
    header("Location: ../admin/usuarios.php");
}