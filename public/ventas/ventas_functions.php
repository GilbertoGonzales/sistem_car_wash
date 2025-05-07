<?php
// ventas_functions.php
function obtenerProductos($conn) {
    $sql = "SELECT p.*, c.nombre as categoria 
            FROM producto p
            JOIN categoria_producto c ON p.id_categoria = c.id_categoria
            WHERE p.estado = 1 AND p.stock > 0";
    $stmt = $conn->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function obtenerServicios($conn) {
    $sql = "SELECT ps.*, tv.nombre as tipo_vehiculo, ns.nombre as nivel_servicio 
            FROM precio_servicio ps
            JOIN tipo_vehiculo tv ON ps.id_tipo_vehiculo = tv.id_tipo_vehiculo
            JOIN nivel_servicio ns ON ps.id_nivel_servicio = ns.id_nivel_servicio
            WHERE ps.estado = 1";
    $stmt = $conn->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function obtenerServiciosExtras($conn) {
    $sql = "SELECT * FROM servicio_extra WHERE estado = 1";
    $stmt = $conn->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function obtenerClientes($conn) {
    $sql = "SELECT * FROM cliente WHERE estado = 1 ORDER BY nombre";
    $stmt = $conn->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function registrarClienteSiEsNuevo($conn, $postData) {
    if (isset($postData['id_cliente']) && $postData['id_cliente'] === 'nuevo') {
        if (empty($postData['nuevo_cliente_nombre']) || empty($postData['nuevo_cliente_dni'])) {
            throw new Exception("Nombre y DNI son obligatorios para nuevo cliente");
        }

        $query = "INSERT INTO cliente (nombre, dni, telefono, email, direccion, estado, fecha_creacion) 
                  VALUES (?, ?, ?, ?, ?, 1, NOW())";
        $stmt = $conn->prepare($query);
        $stmt->execute([
            $postData['nuevo_cliente_nombre'],
            $postData['nuevo_cliente_dni'],
            $postData['nuevo_cliente_telefono'] ?? null,
            $postData['nuevo_cliente_email'] ?? null,
            $postData['nuevo_cliente_direccion'] ?? null
        ]);
        
        return $conn->lastInsertId();
    }
    
    return $postData['id_cliente'];
}