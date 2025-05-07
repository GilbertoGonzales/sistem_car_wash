<?php

// models/Servicio.php - Modelo para nivel de servicio
class Servicio {
    // Conexión a la base de datos y nombre de la tabla
    private $conn;
    private $table_name = "nivel_servicio";
    
    // Propiedades del objeto
    public $id_nivel_servicio;
    public $nombre;
    public $descripcion;
    public $estado;
    public $fecha_creacion;
    
    // Constructor con $db como conexión a base de datos
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Leer todos los niveles de servicio
    public function read() {
        // Consulta para leer todos los registros
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY nombre";
        
        // Preparar declaración de consulta
        $stmt = $this->conn->prepare($query);
        
        // Ejecutar consulta
        $stmt->execute();
        
        return $stmt;
    }
    
    // Métodos CRUD similares a la clase Vehiculo
    // ...
}

