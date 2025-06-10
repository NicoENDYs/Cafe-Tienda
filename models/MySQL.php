<?php
// models/MySQL.php

//Clase para gestionar la conexion a la base de datos
class MySQL {

    //Datos de conexion
    private $ipServidor = "localhost";
    private $usuarioBase = "root";
    private $contrasena = "";
    private $nombreBaseDatos = "cafe_tienda";

    private $conexion; // Ahora $this->conexion será un objeto mysqli

    //Metodo para conectar a la base de datos
    public function conectar(){
        // Usamos new mysqli() para obtener un objeto mysqli
        $this->conexion = new mysqli($this->ipServidor, $this->usuarioBase, $this->contrasena, $this->nombreBaseDatos);

        //Validar si hubo un error en la conexion
        if ($this->conexion->connect_error){ // Acceder a connect_error del objeto
            die("Error al conectar a la base de datos : " . $this->conexion->connect_error);
        }

        //Establecer codificacion utf8 usando el método del objeto
        $this->conexion->set_charset("utf8");
    }

    //Metodo para desconectar la base de datos
    public function desconectar(){
        if($this->conexion){
            $this->conexion->close(); // Usar el método close() del objeto
        }
    }

    // Método para obtener el objeto de conexión mysqli
    // ESTE ES EL MÉTODO CRUCIAL QUE FALTABA
    public function getConexion() {
        return $this->conexion;
    }

    // Metodo para preparar una consulta (usando el objeto mysqli)
    public function prepare($consulta) {
        return $this->conexion->prepare($consulta);
    }

    // Metodo para ejecutar una consulta y devolver su resultaddo
    // Nota: Es mejor usar prepared statements para todas las consultas con datos de usuario
    // pero si mantienes esta, asegúrate de que no haya inyección SQL.
    public function efectuarConsulta($consulta){
        // Ya se estableció utf8 en conectar, pero si es necesario aquí:
        // $this->conexion->query("SET NAMES 'utf8'");
        // $this->conexion->query("SET CHARACTER SET 'utf8'");

        $resultado = $this->conexion->query($consulta); // Usar el método query() del objeto

        if(!$resultado){
            echo "Error en la consulta : " .$this->conexion->error; // Acceder a error del objeto
        }

        return $resultado;
    }

     public function escape_string($string) {
        if ($this->conexion) {
            return mysqli_real_escape_string($this->conexion, $string);
        }
        return $string; // Retorna sin escapar si no hay conexión, aunque esto no debería ocurrir
    }
    
    // Puedes añadir estos métodos para encapsular las transacciones
    // Si los añades, podrías llamar a $mysql->beginTransaction() en lugar de $mysql->getConexion()->beginTransaction()
    /*
    public function beginTransaction() {
        $this->conexion->begin_transaction();
    }

    public function commit() {
        $this->conexion->commit();
    }

    public function rollback() {
        $this->conexion->rollback();
    }

    public function insertId() {
        return $this->conexion->insert_id;
    }
    */
}
?>