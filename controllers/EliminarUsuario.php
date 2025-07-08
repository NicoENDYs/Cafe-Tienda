<?php
require_once '../models/MySQL.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Validación
    if (!is_numeric($id) || intval($id) <= 0) {
        header("Location: ../admin/usuarios.php?estado=error&mensaje=ID inválido");
        exit();
    }

    $mysql = new MySQL();
    $mysql->conectar();
    $pdo = $mysql->getConexion();

    try {
        $stmt = $pdo->prepare("UPDATE usuarios SET estado = 1 WHERE id_usuario = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        header("Location: ../admin/usuarios.php?estado=exito&mensaje=Usuario eliminado");
    } catch (PDOException $e) {
        header("Location: ../admin/usuarios.php?estado=error&mensaje=" . urlencode("Error al eliminar: " . $e->getMessage()));
    } finally {
        $mysql->desconectar();
    }
} else {
    header("Location: ../admin/usuarios.php?estado=error&mensaje=ID no recibido");
    exit();
}