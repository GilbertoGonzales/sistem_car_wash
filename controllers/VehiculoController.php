<?php
// controllers/VehiculoController.php - Controlador para tipo de vehículo
class VehiculoController {
    private $vehiculo;
    
    public function __construct($db) {
        $this->vehiculo = new Vehiculo($db);
    }
    
    // Mostrar todos los tipos de vehículo
    public function index() {
        $stmt = $this->vehiculo->read();
        $num = $stmt->rowCount();
        
        if($num > 0) {
            $vehiculos_arr = array();
            $vehiculos_arr["records"] = array();
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                
                $vehiculo_item = array(
                    "id_tipo_vehiculo" => $id_tipo_vehiculo,
                    "nombre" => $nombre,
                    "descripcion" => $descripcion,
                    "estado" => $estado,
                    "fecha_creacion" => $fecha_creacion
                );
                
                array_push($vehiculos_arr["records"], $vehiculo_item);
            }
            
            return $vehiculos_arr;
        } else {
            return array("message" => "No se encontraron tipos de vehículo.");
        }
    }
    
    // Crear un nuevo tipo de vehículo
    public function create($data) {
        $this->vehiculo->nombre = $data['nombre'];
        $this->vehiculo->descripcion = $data['descripcion'];
        $this->vehiculo->estado = isset($data['estado']) ? $data['estado'] : 1;
        
        if($this->vehiculo->create()) {
            return array("message" => "Tipo de vehículo creado correctamente.");
        } else {
            return array("message" => "No se pudo crear el tipo de vehículo.");
        }
    }
    
    // Obtener un tipo de vehículo por ID
    public function show($id) {
        $this->vehiculo->id_tipo_vehiculo = $id;
        
        if($this->vehiculo->readOne()) {
            $vehiculo_arr = array(
                "id_tipo_vehiculo" => $this->vehiculo->id_tipo_vehiculo,
                "nombre" => $this->vehiculo->nombre,
                "descripcion" => $this->vehiculo->descripcion,
                "estado" => $this->vehiculo->estado,
                "fecha_creacion" => $this->vehiculo->fecha_creacion
            );
            
            return $vehiculo_arr;
        } else {
            return array("message" => "Tipo de vehículo no encontrado.");
        }
    }
    
    // Actualizar un tipo de vehículo
    public function update($id, $data) {
        $this->vehiculo->id_tipo_vehiculo = $id;
        $this->vehiculo->nombre = $data['nombre'];
        $this->vehiculo->descripcion = $data['descripcion'];
        $this->vehiculo->estado = $data['estado'];
        
        if($this->vehiculo->update()) {
            return array("message" => "Tipo de vehículo actualizado correctamente.");
        } else {
            return array("message" => "No se pudo actualizar el tipo de vehículo.");
        }
    }
    
    // Eliminar un tipo de vehículo
    public function delete($id) {
        $this->vehiculo->id_tipo_vehiculo = $id;
        
        if($this->vehiculo->delete()) {
            return array("message" => "Tipo de vehículo eliminado correctamente.");
        } else {
            return array("message" => "No se pudo eliminar el tipo de vehículo.");
        }
    }
}