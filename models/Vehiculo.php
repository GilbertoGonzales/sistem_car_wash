<?php


// models/Vehiculo.php - Modelo para tipo de vehículo (ejemplo de modelo)
class Vehiculo {
    // Conexión a la base de datos y nombre de la tabla
    private $conn;
    private $table_name = "tipo_vehiculo";
    
    // Propiedades del objeto
    public $id_tipo_vehiculo;
    public $nombre;
    public $descripcion;
    public $estado;
    public $fecha_creacion;
    
    // Constructor con $db como conexión a base de datos
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Leer todos los tipos de vehículos
    public function read() {
        // Consulta para leer todos los registros
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY nombre";
        
        // Preparar declaración de consulta
        $stmt = $this->conn->prepare($query);
        
        // Ejecutar consulta
        $stmt->execute();
        
        return $stmt;
    }
    
    // Crear tipo de vehículo
    public function create() {
        // Consulta para insertar un registro
        $query = "INSERT INTO " . $this->table_name . " SET nombre=:nombre, descripcion=:descripcion, estado=:estado";
        
        // Preparar consulta
        $stmt = $this->conn->prepare($query);
        
        // Sanitizar
        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        $this->descripcion = htmlspecialchars(strip_tags($this->descripcion));
        $this->estado = isset($this->estado) ? $this->estado : 1;
        
        // Vincular valores
        $stmt->bindParam(":nombre", $this->nombre);
        $stmt->bindParam(":descripcion", $this->descripcion);
        $stmt->bindParam(":estado", $this->estado);
        
        // Ejecutar consulta
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    // Actualizar tipo de vehículo
    public function update() {
        // Consulta para actualizar un registro
        $query = "UPDATE " . $this->table_name . " 
                SET 
                    nombre = :nombre, 
                    descripcion = :descripcion, 
                    estado = :estado 
                WHERE 
                    id_tipo_vehiculo = :id_tipo_vehiculo";
        
        // Preparar consulta
        $stmt = $this->conn->prepare($query);
        
        // Sanitizar
        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        $this->descripcion = htmlspecialchars(strip_tags($this->descripcion));
        $this->estado = htmlspecialchars(strip_tags($this->estado));
        $this->id_tipo_vehiculo = htmlspecialchars(strip_tags($this->id_tipo_vehiculo));
        
        // Vincular valores
        $stmt->bindParam(":nombre", $this->nombre);
        $stmt->bindParam(":descripcion", $this->descripcion);
        $stmt->bindParam(":estado", $this->estado);
        $stmt->bindParam(":id_tipo_vehiculo", $this->id_tipo_vehiculo);
        
        // Ejecutar consulta
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    // Eliminar tipo de vehículo
    public function delete() {
        // Consulta para eliminar un registro
        $query = "DELETE FROM " . $this->table_name . " WHERE id_tipo_vehiculo = ?";
        
        // Preparar consulta
        $stmt = $this->conn->prepare($query);
        
        // Sanitizar
        $this->id_tipo_vehiculo = htmlspecialchars(strip_tags($this->id_tipo_vehiculo));
        
        // Vincular id a eliminar
        $stmt->bindParam(1, $this->id_tipo_vehiculo);
        
        // Ejecutar consulta
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    // Leer un tipo de vehículo específico
    public function readOne() {
        // Consulta para leer un registro
        $query = "SELECT * FROM " . $this->table_name . " WHERE id_tipo_vehiculo = ? LIMIT 0,1";
        
        // Preparar consulta
        $stmt = $this->conn->prepare($query);
        
        // Vincular id del registro a leer
        $stmt->bindParam(1, $this->id_tipo_vehiculo);
        
        // Ejecutar consulta
        $stmt->execute();
        
        // Obtener fila recuperada
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Establecer valores a propiedades del objeto
        if($row) {
            $this->nombre = $row['nombre'];
            $this->descripcion = $row['descripcion'];
            $this->estado = $row['estado'];
            $this->fecha_creacion = $row['fecha_creacion'];
            return true;
        }
        
        return false;
    }
}