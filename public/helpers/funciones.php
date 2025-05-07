<?php
/**
 * Archivo de funciones helpers para el Sistema de Carwash
 */

/**
 * Formatea un valor numérico a formato de moneda peruana (S/ X.XX)
 */
function formatearMoneda($monto) {
    return 'S/ ' . number_format((float)$monto, 2);
}

/**
 * Formatea una fecha al formato DD/MM/YYYY
 */
function formatearFecha($fecha) {
    if (empty($fecha)) {
        return ''; // Asegura que si la fecha está vacía, retorne un string vacío
    }
    return date('d/m/Y', strtotime($fecha));
}


/**
 * Redirecciona a una URL con un mensaje en la sesión
 */
function redirigirConMensaje($url, $mensaje, $esError = false) {
    if (!isset($_SESSION)) {
        session_start();
    }
    
    if ($esError) {
        $_SESSION['error'] = $mensaje;
    } else {
        $_SESSION['mensaje'] = $mensaje;
    }
    header("Location: $url");
    exit();
}

/**
 * Obtiene los datos para mostrar en el dashboard
 */
function obtenerDatosDashboard($conn) {
    if (!$conn instanceof PDO) {
        throw new InvalidArgumentException("Conexión a BD no válida");
    }
    
    $datos = [
        'ventas_hoy' => 0,
        'ingresos_carwash' => 0,
        'ingresos_tienda' => 0,
        'gastos_mes' => 0,
        'stock_bajo' => []
    ];
    
    try {
        // Ventas de hoy
        $query = "SELECT SUM(total) as total FROM venta 
                 WHERE DATE(fecha_hora) = CURDATE() AND estado = 'COMPLETADO'";
        $stmt = $conn->query($query);
        $datos['ventas_hoy'] = (float)($stmt->fetchColumn() ?? 0);
        
        // Ingresos carwash este mes
        $query = "SELECT SUM(ds.precio) + SUM(IFNULL(dse.precio, 0)) as total
                 FROM venta v
                 JOIN detalle_servicio ds ON v.id_venta = ds.id_venta
                 LEFT JOIN detalle_servicio_extra dse ON ds.id_detalle_servicio = dse.id_detalle_servicio
                 WHERE MONTH(v.fecha_hora) = MONTH(CURDATE()) 
                 AND YEAR(v.fecha_hora) = YEAR(CURDATE())
                 AND v.estado = 'COMPLETADO'";
        $stmt = $conn->query($query);
        $datos['ingresos_carwash'] = (float)($stmt->fetchColumn() ?? 0);
        
        // Ingresos tienda este mes
        $query = "SELECT SUM(dp.subtotal) as total
                 FROM venta v
                 JOIN detalle_producto dp ON v.id_venta = dp.id_venta
                 WHERE MONTH(v.fecha_hora) = MONTH(CURDATE()) 
                 AND YEAR(v.fecha_hora) = YEAR(CURDATE())
                 AND v.estado = 'COMPLETADO'";
        $stmt = $conn->query($query);
        $datos['ingresos_tienda'] = (float)($stmt->fetchColumn() ?? 0);
        
        // Gastos del mes
        $query = "SELECT SUM(monto) as total FROM gasto
                 WHERE MONTH(fecha_gasto) = MONTH(CURDATE()) 
                 AND YEAR(fecha_gasto) = YEAR(CURDATE())
                 AND estado = 1";
        $stmt = $conn->query($query);
        $datos['gastos_mes'] = (float)($stmt->fetchColumn() ?? 0);
        
        // Productos con stock bajo
        $query = "SELECT p.nombre, p.stock, p.stock_minimo, cp.nombre as categoria
                 FROM producto p
                 JOIN categoria_producto cp ON p.id_categoria_producto = cp.id_categoria_producto
                 WHERE p.stock <= p.stock_minimo AND p.estado = 1";
        $stmt = $conn->query($query);
        $datos['stock_bajo'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $datos;
    } catch (PDOException $e) {
        error_log("Error en obtenerDatosDashboard: " . $e->getMessage());
        throw new Exception("Error al obtener datos del dashboard");
    }
}

/**
 * Obtiene las ventas según un período específico
 */
function obtenerVentasPorPeriodo($conn, $periodo) {
    if (!$conn instanceof PDO) {
        throw new InvalidArgumentException("Conexión a BD no válida");
    }
    
    try {
        switch ($periodo) {
            case 'hoy':
                $query = "SELECT SUM(total) as total FROM venta 
                         WHERE DATE(fecha_hora) = CURDATE() AND estado = 'COMPLETADO'";
                break;
            case 'semana':
                $query = "SELECT SUM(total) as total FROM venta 
                         WHERE YEARWEEK(fecha_hora) = YEARWEEK(CURDATE()) 
                         AND estado = 'COMPLETADO'";
                break;
            case 'mes':
                $query = "SELECT SUM(total) as total FROM venta 
                         WHERE MONTH(fecha_hora) = MONTH(CURDATE()) 
                         AND YEAR(fecha_hora) = YEAR(CURDATE())
                         AND estado = 'COMPLETADO'";
                break;
            case 'año':
                $query = "SELECT SUM(total) as total FROM venta 
                         WHERE YEAR(fecha_hora) = YEAR(CURDATE())
                         AND estado = 'COMPLETADO'";
                break;
            default:
                return 0;
        }
        
        $stmt = $conn->query($query);
        return (float)($stmt->fetchColumn() ?? 0);
    } catch (PDOException $e) {
        error_log("Error en obtenerVentasPorPeriodo: " . $e->getMessage());
        return 0;
    }
}

/**
 * Obtiene los productos más vendidos
 */
function obtenerProductosMasVendidos($conn, $limite = 5) {
    if (!$conn instanceof PDO) {
        throw new InvalidArgumentException("Conexión a BD no válida");
    }
    
    try {
        $query = "SELECT p.nombre, SUM(dp.cantidad) as total_vendido, 
                 SUM(dp.subtotal) as ingreso_total
                 FROM detalle_producto dp
                 JOIN producto p ON dp.id_producto = p.id_producto
                 JOIN venta v ON dp.id_venta = v.id_venta
                 WHERE v.estado = 'COMPLETADO'
                 AND MONTH(v.fecha_hora) = MONTH(CURDATE())
                 AND YEAR(v.fecha_hora) = YEAR(CURDATE())
                 GROUP BY p.id_producto
                 ORDER BY total_vendido DESC
                 LIMIT ?";
        
        $stmt = $conn->prepare($query);
        $stmt->execute([$limite]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error en obtenerProductosMasVendidos: " . $e->getMessage());
        return [];
    }
}

/**
 * Obtiene los servicios más solicitados
 */
function obtenerServiciosMasSolicitados($conn, $limite = 5) {
    if (!$conn instanceof PDO) {
        throw new InvalidArgumentException("Conexión a BD no válida");
    }
    
    try {
        $query = "SELECT s.nombre, COUNT(ds.id_servicio) as total_servicios, 
                 SUM(ds.precio) as ingreso_total
                 FROM detalle_servicio ds
                 JOIN servicio s ON ds.id_servicio = s.id_servicio
                 JOIN venta v ON ds.id_venta = v.id_venta
                 WHERE v.estado = 'COMPLETADO'
                 AND MONTH(v.fecha_hora) = MONTH(CURDATE())
                 AND YEAR(v.fecha_hora) = YEAR(CURDATE())
                 GROUP BY s.id_servicio
                 ORDER BY total_servicios DESC
                 LIMIT ?";
        
        $stmt = $conn->prepare($query);
        $stmt->execute([$limite]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error en obtenerServiciosMasSolicitados: " . $e->getMessage());
        return [];
    }
}

/**
 * Obtiene datos para el gráfico de ventas por día
 */
function obtenerVentasPorDia($conn, $dias = 7) {
    if (!$conn instanceof PDO) {
        throw new InvalidArgumentException("Conexión a BD no válida");
    }
    
    try {
        $query = "SELECT DATE(fecha_hora) as fecha, SUM(total) as total
                 FROM venta
                 WHERE fecha_hora >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
                 AND estado = 'COMPLETADO'
                 GROUP BY DATE(fecha_hora)
                 ORDER BY fecha";
        
        $stmt = $conn->prepare($query);
        $stmt->execute([$dias]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error en obtenerVentasPorDia: " . $e->getMessage());
        return [];
    }
}


/**
 * Verifica si una fecha está dentro del mes actual
 */
function esMesActual($fecha) {
    if (empty($fecha)) return false;
    return date('m-Y', strtotime($fecha)) === date('m-Y');
}

/**
 * Verifica si una fecha es hoy
 */
function esHoy($fecha) {
    if (empty($fecha)) return false;
    return date('Y-m-d', strtotime($fecha)) === date('Y-m-d');
}

/**
 * Calcula el balance entre ingresos y gastos
 */
function calcularBalance($ingresos, $gastos) {
    return (float)$ingresos - (float)$gastos;
}

/**
 * Obtiene el estado de un balance (positivo, negativo o neutro)
 */
function obtenerEstadoBalance($balance) {
    $balance = (float)$balance;
    if ($balance > 0) {
        return 'positivo';
    } elseif ($balance < 0) {
        return 'negativo';
    }
    return 'neutro';
}

/**
 * Sanea un texto para prevenir inyección HTML o SQL
 */
function sanearTexto($texto) {
    return htmlspecialchars(trim($texto), ENT_QUOTES, 'UTF-8');
}

/**
 * Verifica si una cadena es una fecha válida en formato Y-m-d
 */
function esFechaValida($fecha) {
    if (empty($fecha)) return false;
    $dateTime = DateTime::createFromFormat('Y-m-d', $fecha);
    return $dateTime && $dateTime->format('Y-m-d') === $fecha;
}