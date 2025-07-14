<?php
// models/MySQL.php

// Clase para gestionar la conexión a la base de datos usando PDO
class MySQL {

   // Datos de conexión
    private $ipServidor = "localhost";
    private $usuarioBase = "root";
    private $contrasena = "";
    private $nombreBaseDatos = "cafe_tienda";

    private $conexion; // Objeto PDO

    // Método para conectar a la base de datos
    public function conectar() {
        try {
            $dsn = "mysql:host={$this->ipServidor};dbname={$this->nombreBaseDatos};charset=utf8";
            $this->conexion = new PDO($dsn, $this->usuarioBase, $this->contrasena);
            $this->conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Error al conectar a la base de datos: " . $e->getMessage());
        }
    }
       /* // Datos de conexión
    private $ipServidor = "bnw0x7fkdfnqqysjavqc-mysql.services.clever-cloud.com";
    private $usuarioBase = "ulbk99vv6pck3cry";
    private $contrasena = "mT0ZkuDbIygKhNUTDj2x";
    private $nombreBaseDatos = "bnw0x7fkdfnqqysjavqc";
    private $puerto = 3306;

    private $conexion; // Objeto PDO

    // Método para conectar a la base de datos
    public function conectar()
    {
        try {
            $dsn = "mysql:host={$this->ipServidor};port={$this->puerto};dbname={$this->nombreBaseDatos};charset=utf8";
            $this->conexion = new PDO($dsn, $this->usuarioBase, $this->contrasena);
            $this->conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Error al conectar a la base de datos: " . $e->getMessage());
        }
    }*/

    // Método para desconectar
    public function desconectar() {
        $this->conexion = null; // En PDO, esto cierra la conexión
    }

    // Obtener la instancia PDO
    public function getConexion() {
        return $this->conexion;
    }

    // Preparar una consulta
    public function prepare($consulta) {
        return $this->conexion->prepare($consulta);
    }

    // Ejecutar consulta directa (NO recomendado para datos de usuario)
    public function efectuarConsulta($consulta) {
        try {
            return $this->conexion->query($consulta);
        } catch (PDOException $e) {
            echo "Error en la consulta: " . $e->getMessage();
            return false;
        }
    }

    // Escapar string (usualmente innecesario si usas consultas preparadas)
    public function escape_string($string) {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8'); // Alternativa segura para salida HTML
    }

    // Métodos para manejo de transacciones
    public function beginTransaction() {
        $this->conexion->beginTransaction();
    }

    public function commit() {
        $this->conexion->commit();
    }

    public function rollback() {
        $this->conexion->rollBack();
    }

    public function insertId() {
        return $this->conexion->lastInsertId();
    }
}
?>
