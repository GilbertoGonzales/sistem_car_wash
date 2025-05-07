<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/constantes.php';

$database = new Database();
$conn = $database->getConnection();

// Obtener datos necesarios para los selects
try {
    // Obtener clientes activos
    $queryClientes = "SELECT id_cliente, nombre, dni FROM cliente WHERE estado = 1 ORDER BY nombre";
    $stmtClientes = $conn->query($queryClientes);
    $clientes = $stmtClientes->fetchAll(PDO::FETCH_ASSOC);

    // Obtener productos con stock disponible
    $queryProductos = "SELECT id_producto, nombre, precio_venta, stock FROM producto WHERE estado = 1 AND stock > 0 ORDER BY nombre";
    $stmtProductos = $conn->query($queryProductos);
    $productos = $stmtProductos->fetchAll(PDO::FETCH_ASSOC);

    // Obtener servicios activos con información de vehículo y nivel
    $queryServicios = "SELECT ps.id_precio_servicio, tv.nombre as tipo_vehiculo, ns.nombre as nivel_servicio, ps.precio 
                      FROM precio_servicio ps
                      JOIN tipo_vehiculo tv ON ps.id_tipo_vehiculo = tv.id_tipo_vehiculo
                      JOIN nivel_servicio ns ON ps.id_nivel_servicio = ns.id_nivel_servicio
                      WHERE ps.estado = 1
                      ORDER BY tv.nombre, ns.nombre";
    $stmtServicios = $conn->query($queryServicios);
    $servicios = $stmtServicios->fetchAll(PDO::FETCH_ASSOC);

    // Obtener servicios extras
    $queryServiciosExtras = "SELECT id_servicio_extra, nombre, precio FROM servicio_extra WHERE estado = 1 ORDER BY nombre";
    $stmtServiciosExtras = $conn->query($queryServiciosExtras);
    $serviciosExtras = $stmtServiciosExtras->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al cargar datos: " . $e->getMessage());
}

// Definir las funciones necesarias
function registrarClienteSiEsNuevo($conn, $postData) {
    // Verificar si se está registrando un nuevo cliente
    if (isset($postData['id_cliente']) && $postData['id_cliente'] === 'nuevo') {
        // Validar campos obligatorios
        if (empty($postData['nuevo_cliente_nombre']) || empty($postData['nuevo_cliente_dni'])) {
            throw new Exception("Nombre y DNI son obligatorios para nuevo cliente");
        }

        // Insertar nuevo cliente
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
    
    // Si no es nuevo cliente, retornar el ID existente
    return $postData['id_cliente'];
}

function insertarVenta($conn, $postData, $id_cliente) {
    $query = "INSERT INTO venta 
              (numero_comprobante, fecha_hora, subtotal, igv, total, estado, tipo_venta, 
               observaciones, fecha_creacion, id_cliente) 
              VALUES (?, NOW(), ?, ?, ?, 1, ?, ?, NOW(), ?)";
    
    $stmt = $conn->prepare($query);
    $stmt->execute([
        generarNumeroComprobante($conn),
        $postData['subtotal'],
        $postData['igv'],
        $postData['total'],
        $postData['tipo_venta'],
        $postData['observaciones'] ?? '',
        $id_cliente
    ]);
    return $conn->lastInsertId();
}

function procesarProductos($conn, $id_venta, $postData) {
    foreach ($postData['producto_id'] as $key => $id_producto) {
        if (!empty($id_producto)) {
            // Insertar detalle producto
            $query = "INSERT INTO detalle_producto 
                      (id_venta, id_producto, cantidad, precio_unitario, subtotal, estado, fecha_creacion) 
                      VALUES (?, ?, ?, ?, ?, 1, NOW())";
            $stmt = $conn->prepare($query);
            $stmt->execute([
                $id_venta,
                $id_producto,
                $postData['producto_cantidad'][$key],
                $postData['producto_precio'][$key],
                $postData['producto_subtotal'][$key]
            ]);
            
            // Actualizar stock
            $query = "UPDATE producto SET stock = stock - ? WHERE id_producto = ?";
            $stmt = $conn->prepare($query);
            $stmt->execute([
                $postData['producto_cantidad'][$key],
                $id_producto
            ]);
        }
    }
}

function procesarServicios($conn, $id_venta, $postData) {
    foreach ($postData['servicio_id'] as $key => $id_servicio) {
        if (!empty($id_servicio)) {
            // Insertar detalle servicio principal
            $query = "INSERT INTO detalle_servicio 
                      (id_venta, id_precio_servicio, precio, estado, fecha_creacion) 
                      VALUES (?, ?, ?, 1, NOW())";
            $stmt = $conn->prepare($query);
            $stmt->execute([
                $id_venta,
                $id_servicio,
                $postData['servicio_precio'][$key]
            ]);
            $id_detalle_servicio = $conn->lastInsertId();
            
            // Procesar servicios extras si existen
            if (isset($postData['servicio_extra'][$key])) {
                foreach ($postData['servicio_extra'][$key] as $id_extra => $precio_extra) {
                    $query = "INSERT INTO detalle_servicio_extra 
                              (id_detalle_servicio, id_servicio_extra, precio, estado, fecha_creacion) 
                              VALUES (?, ?, ?, 1, NOW())";
                    $stmt = $conn->prepare($query);
                    $stmt->execute([
                        $id_detalle_servicio,
                        $id_extra,
                        $precio_extra
                    ]);
                }
            }
        }
    }
}

function generarNumeroComprobante($conn) {
    // Lógica para generar número de comprobante único
    $query = "SELECT MAX(numero_comprobante) as ultimo FROM venta";
    $stmt = $conn->query($query);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $ultimo = $row['ultimo'] ?? 'V-00000';
    $numero = intval(substr($ultimo, 2)) + 1;
    return 'V-' . str_pad($numero, 5, '0', STR_PAD_LEFT);
}

// Procesar formulario de venta
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['realizar_venta'])) {
        $conn->beginTransaction();
        try {
            // 1. Registrar cliente si es nuevo
            $id_cliente = registrarClienteSiEsNuevo($conn, $_POST);
            
            // 2. Insertar venta principal
            $id_venta = insertarVenta($conn, $_POST, $id_cliente);
            
            // 3. Procesar según tipo de venta
            if ($_POST['tipo_venta'] == 'producto' || $_POST['tipo_venta'] == 'mixto') {
                procesarProductos($conn, $id_venta, $_POST);
            }
            
            if ($_POST['tipo_venta'] == 'servicio' || $_POST['tipo_venta'] == 'mixto') {
                procesarServicios($conn, $id_venta, $_POST);
            }
            
            $conn->commit();
            
            // Redirigir para nueva venta
            header("Location: nueva_venta.php?exito=venta_creada");
            exit();
        } catch (Exception $e) {
            $conn->rollBack();
            $mensaje_error = "Error al procesar venta: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<!-- [El resto del código HTML permanece igual] -->

<!DOCTYPE html>
<html lang="es">
<head>
    <?php include '../../template/head.php'; ?>
    <title>Nueva Venta - Sistema POS</title>
    <style>
        :root {
            --color-primary: #4361ee;
            --color-secondary: #3f37c9;
            --color-success: #4cc9f0;
            --color-info: #4895ef;
            --color-warning: #f8961e;
            --color-danger: #f94144;
        }
        
        body {
            background-color: #f8f9fa;
        }
        
        .card-venta {
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            border: none;
        }
        
        .card-header {
            border-radius: 10px 10px 0 0 !important;
            padding: 1.2rem;
        }
        
        .item-box {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
            background-color: white;
        }
        
        .item-box:hover {
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            border-color: var(--color-info);
        }
        
        .bg-productos {
            border-left: 4px solid var(--color-info);
        }
        
        .bg-servicios {
            border-left: 4px solid var(--color-success);
        }
        
        .servicio-extra-item {
            padding: 8px;
            margin: 5px 0;
            background-color: #f8f9fa;
            border-radius: 5px;
            display: flex;
            align-items: center;
        }
        
        .btn-action {
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0;
        }
        
        .summary-card {
            background-color: white;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .summary-value {
            font-size: 1.2rem;
            font-weight: 600;
        }
        
        .total-card {
            background-color: #f8f9fa;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 15px;
        }
        
        .total-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--color-primary);
        }
        
        .quick-actions {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
        }
        
        .quick-btn {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 10px rgba(0,0,0,0.15);
            margin-bottom: 10px;
            cursor: pointer;
        }
        
        @media (max-width: 768px) {
            .mobile-stack {
                flex-direction: column;
            }
            
            .mobile-stack > div {
                width: 100%;
                margin-bottom: 15px;
            }
        }
    </style>
</head>
<body>
    <?php include '../../template/navbar.php'; ?>
    
    <div class="container-fluid mt-3 mb-5">
        <div class="row">
            <div class="col-lg-8">
                <div class="card card-venta mb-4">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h2 class="mb-0"><i class="fas fa-cash-register me-2"></i> Nueva Venta</h2>
                        <span class="badge bg-light text-dark" id="numeroComprobante">NUEVA-VENTA</span>
                    </div>
                    
                    <div class="card-body">
                        <?php if(isset($mensaje_error)): ?>
                            <div class="alert alert-danger"><?= $mensaje_error ?></div>
                        <?php endif; ?>
                        
                        <form id="formVenta" method="POST">
                            <!-- Sección Cliente -->
                            <div class="card mb-4">
                                <div class="card-header bg-info text-white">
                                    <h4 class="mb-0"><i class="fas fa-user me-2"></i> Datos del Cliente</h4>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="fw-bold">Seleccionar Cliente:</label>
                                                <select id="selectCliente" name="id_cliente" class="form-select" required>
                                                    <option value="">-- Seleccione un cliente --</option>
                                                    <?php foreach($clientes as $cliente): ?>
                                                        <option value="<?= $cliente['id_cliente'] ?>">
                                                            <?= htmlspecialchars($cliente['nombre']) ?> - DNI: <?= $cliente['dni'] ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                    <option value="nuevo">+ Registrar nuevo cliente</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6" id="nuevoClienteForm" style="display:none;">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>Nombre:</label>
                                                        <input type="text" name="nuevo_cliente_nombre" class="form-control" placeholder="Nombre completo">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>DNI:</label>
                                                        <input type="text" name="nuevo_cliente_dni" class="form-control" placeholder="Número de DNI">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>Teléfono:</label>
                                                        <input type="text" name="nuevo_cliente_telefono" class="form-control" placeholder="Número de teléfono">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>Email:</label>
                                                        <input type="email" name="nuevo_cliente_email" class="form-control" placeholder="Correo electrónico">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Selección de Tipo de Venta -->
                            <div class="card mb-4">
                                <div class="card-header bg-info text-white">
                                    <h4 class="mb-0"><i class="fas fa-tags me-2"></i> Tipo de Venta</h4>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <select id="tipoVenta" name="tipo_venta" class="form-select" required>
                                                <option value="">-- Seleccione tipo de venta --</option>
                                                <option value="producto">Venta de Productos</option>
                                                <option value="servicio">Venta de Servicios</option>
                                                <option value="mixto">Venta Mixta (Productos y Servicios)</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <button type="button" id="btnVentaRapida" class="btn btn-outline-primary w-100">
                                                <i class="fas fa-bolt me-2"></i>Venta Rápida
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Sección Productos -->
                            <div id="seccionProductos" class="card mb-4" style="display:none;">
                                <div class="card-header bg-productos d-flex justify-content-between align-items-center">
                                    <h4 class="mb-0"><i class="fas fa-boxes me-2"></i> Productos</h4>
                                    <button type="button" id="btnAgregarProducto" class="btn btn-sm btn-primary">
                                        <i class="fas fa-plus me-1"></i> Agregar
                                    </button>
                                </div>
                                <div class="card-body">
                                    <div id="productosContainer">
                                        <!-- Productos se añadirán aquí -->
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Sección Servicios -->
                            <div id="seccionServicios" class="card mb-4" style="display:none;">
                                <div class="card-header bg-servicios d-flex justify-content-between align-items-center">
                                    <h4 class="mb-0"><i class="fas fa-car me-2"></i> Servicios</h4>
                                    <button type="button" id="btnAgregarServicio" class="btn btn-sm btn-success">
                                        <i class="fas fa-plus me-1"></i> Agregar
                                    </button>
                                </div>
                                <div class="card-body">
                                    <div id="serviciosContainer">
                                        <!-- Servicios se añadirán aquí -->
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Observaciones -->
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h4 class="mb-0"><i class="fas fa-comment me-2"></i> Observaciones</h4>
                                </div>
                                <div class="card-body">
                                    <textarea name="observaciones" class="form-control" rows="2" placeholder="Notas adicionales sobre la venta..."></textarea>
                                </div>
                            </div>
                            
                            <input type="hidden" id="subtotal" name="subtotal" value="0">
                            <input type="hidden" id="igv" name="igv" value="0">
                            <input type="hidden" id="total" name="total" value="0">
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <!-- Resumen de Venta -->
                <div class="card card-venta mb-4">
                    <div class="card-header bg-warning text-white">
                        <h4 class="mb-0"><i class="fas fa-receipt me-2"></i> Resumen de Venta</h4>
                    </div>
                    <div class="card-body">
                        <div class="summary-card mb-3">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal:</span>
                                <span class="summary-value" id="resumenSubtotal">S/ 0.00</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>IGV (18%):</span>
                                <span class="summary-value" id="resumenIgv">S/ 0.00</span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between">
                                <span class="fw-bold">Total:</span>
                                <span class="total-value" id="resumenTotal">S/ 0.00</span>
                            </div>
                        </div>
                        
                        <!-- Método de Pago -->
                        <div class="mb-3">
                            <label class="fw-bold mb-2">Método de Pago:</label>
                            <select name="metodo_pago" class="form-select mb-3" required>
                                <option value="efectivo">Efectivo</option>
                                <option value="tarjeta">Tarjeta</option>
                                <option value="transferencia">Transferencia</option>
                                <option value="yape">Yape/Plin</option>
                            </select>
                            
                            <div class="input-group mb-3">
                                <span class="input-group-text">S/</span>
                                <input type="number" id="montoPagado" name="monto_pagado" class="form-control" placeholder="Monto recibido" required step="0.01">
                            </div>
                            
                            <div class="input-group">
                                <span class="input-group-text">Vuelto:</span>
                                <input type="number" id="vuelto" name="vuelto" class="form-control" readonly>
                            </div>
                        </div>
                        
                        <!-- Botones de Acción -->
                        <div class="d-grid gap-2">
                            <button type="submit" name="realizar_venta" form="formVenta" class="btn btn-success btn-lg">
                                <i class="fas fa-check-circle me-2"></i> Registrar Venta
                            </button>
                            <button type="button" id="btnNuevaVenta" class="btn btn-primary btn-lg">
                                <i class="fas fa-plus-circle me-2"></i> Nueva Venta
                            </button>
                            <a href="index.php" class="btn btn-secondary btn-lg">
                                <i class="fas fa-times-circle me-2"></i> Cancelar
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Productos más vendidos -->
                <div class="card card-venta">
                    <div class="card-header bg-light">
                        <h4 class="mb-0"><i class="fas fa-fire me-2"></i> Productos Populares</h4>
                    </div>
                    <div class="card-body">
                        <ul class="list-group">
                            <?php foreach(array_slice($productos, 0, 5) as $prod): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <?= htmlspecialchars($prod['nombre']) ?>
                                    <span class="badge bg-primary rounded-pill">S/<?= number_format($prod['precio_venta'], 2) ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="quick-actions">
        <div class="quick-btn bg-primary text-white" title="Agregar Producto" id="quickAddProduct">
            <i class="fas fa-box"></i>
        </div>
        <div class="quick-btn bg-success text-white" title="Agregar Servicio" id="quickAddService">
            <i class="fas fa-car"></i>
        </div>
        <div class="quick-btn bg-info text-white" title="Calcular Total" id="quickCalculate">
            <i class="fas fa-calculator"></i>
        </div>
    </div>

    <!-- Templates -->
    <template id="templateProducto">
        <div class="item-box bg-productos">
            <div class="row align-items-center">
                <div class="col-md-5">
                    <div class="form-group mb-2">
                        <select name="producto_id[]" class="form-select select-producto" required>
                            <option value="">-- Seleccione producto --</option>
                            <?php foreach($productos as $prod): ?>
                                <option value="<?= $prod['id_producto'] ?>" 
                                        data-precio="<?= $prod['precio_venta'] ?>"
                                        data-stock="<?= $prod['stock'] ?>">
                                    <?= htmlspecialchars($prod['nombre']) ?> - S/<?= number_format($prod['precio_venta'], 2) ?> (Stock: <?= $prod['stock'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group mb-2">
                        <input type="number" name="producto_precio[]" class="form-control input-precio" placeholder="Precio" readonly>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group mb-2">
                        <input type="number" name="producto_cantidad[]" class="form-control input-cantidad" min="1" value="1" placeholder="Cantidad" required>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group mb-2">
                        <input type="number" name="producto_subtotal[]" class="form-control input-subtotal" placeholder="Subtotal" readonly>
                    </div>
                </div>
                <div class="col-md-1 text-center">
                    <button type="button" class="btn btn-danger btn-action btn-eliminar">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    </template>

    <template id="templateServicio">
        <div class="item-box bg-servicios">
            <div class="row align-items-center">
                <div class="col-md-5">
                    <div class="form-group mb-2">
                        <select name="servicio_id[]" class="form-select select-servicio" required>
                            <option value="">-- Seleccione servicio --</option>
                            <?php foreach($servicios as $serv): ?>
                                <option value="<?= $serv['id_precio_servicio'] ?>" 
                                        data-precio="<?= $serv['precio'] ?>">
                                    <?= htmlspecialchars($serv['tipo_vehiculo']) ?> - <?= htmlspecialchars($serv['nivel_servicio']) ?> - S/<?= number_format($serv['precio'], 2) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group mb-2">
                        <input type="number" name="servicio_precio[]" class="form-control input-precio" placeholder="Precio" readonly>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group mb-2">
                        <label class="small text-muted">Extras:</label>
                        <div class="servicios-extras-container">
                            <?php foreach($serviciosExtras as $extra): ?>
                                <div class="form-check servicio-extra-item">
                                    <input class="form-check-input" type="checkbox" 
                                           name="servicio_extra[0][<?= $extra['id_servicio_extra'] ?>]" 
                                           value="<?= $extra['precio'] ?>"
                                           id="extra_<?= $extra['id_servicio_extra'] ?>">
                                    <label class="form-check-label small" for="extra_<?= $extra['id_servicio_extra'] ?>">
                                        <?= htmlspecialchars($extra['nombre']) ?> (S/<?= number_format($extra['precio'], 2) ?>)
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-1 text-center">
                    <button type="button" class="btn btn-danger btn-action btn-eliminar">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    </template>

    <?php include '../../template/scripts.php'; ?>
    <script>
        $(document).ready(function() {
            // Variables
            let contadorServicios = 0;
            let ventaRapidaMode = false;
            
            // Inicializar número de comprobante
            actualizarNumeroComprobante();
            
            // Mostrar/ocultar formulario de nuevo cliente
            $('#selectCliente').change(function() {
                if ($(this).val() === 'nuevo') {
                    $('#nuevoClienteForm').show();
                    $('[name="nuevo_cliente_nombre"]').prop('required', true);
                    $('[name="nuevo_cliente_dni"]').prop('required', true);
                } else {
                    $('#nuevoClienteForm').hide();
                    $('[name="nuevo_cliente_nombre"]').prop('required', false);
                    $('[name="nuevo_cliente_dni"]').prop('required', false);
                }
            });
            
            // Mostrar secciones según tipo de venta
            $('#tipoVenta').change(function() {
                const tipo = $(this).val();
                
                // Ocultar todo primero
                $('#seccionProductos, #seccionServicios').hide();
                
                if (tipo === 'producto') {
                    $('#seccionProductos').show();
                    if(!ventaRapidaMode) $('#btnAgregarProducto').click();
                } else if (tipo === 'servicio') {
                    $('#seccionServicios').show();
                    if(!ventaRapidaMode) $('#btnAgregarServicio').click();
                } else if (tipo === 'mixto') {
                    $('#seccionProductos, #seccionServicios').show();
                    if(!ventaRapidaMode) {
                        $('#btnAgregarProducto').click();
                        $('#btnAgregarServicio').click();
                    }
                }
                
                ventaRapidaMode = false;
            });
            
            // Modo venta rápida
            $('#btnVentaRapida').click(function() {
                ventaRapidaMode = true;
                $('#tipoVenta').val('producto').trigger('change');
                
                // Seleccionar primer cliente si existe
                if ($('#selectCliente option').length > 2) {
                    $('#selectCliente').val($('#selectCliente option')[1].value).trigger('change');
                }
                
                // Seleccionar primer producto
                setTimeout(() => {
                    $('.select-producto:first').val($('.select-producto:first option')[1].value).trigger('change');
                    $('.input-cantidad:first').focus().select();
                }, 100);
            });
            
            // Agregar producto
            $('#btnAgregarProducto, #quickAddProduct').click(function() {
                const template = $('#templateProducto').html();
                $('#productosContainer').append(template);
                actualizarEventosProducto();
                
                // Enfocar el nuevo select
                $('.select-producto:last').focus();
            });
            
            // Agregar servicio
            $('#btnAgregarServicio, #quickAddService').click(function() {
                const template = $('#templateServicio').html()
                    .replace(/servicio_extra\[0\]/g, `servicio_extra[${contadorServicios}]`);
                $('#serviciosContainer').append(template);
                actualizarEventosServicio();
                contadorServicios++;
                
                // Enfocar el nuevo select
                $('.select-servicio:last').focus();
            });
            
            // Actualizar eventos para productos
            function actualizarEventosProducto() {
                $('.select-producto').off('change').on('change', function() {
                    const precio = $(this).find(':selected').data('precio') || 0;
                    const stock = $(this).find(':selected').data('stock') || 0;
                    
                    $(this).closest('.item-box').find('.input-precio').val(precio.toFixed(2));
                    $(this).closest('.item-box').find('.input-cantidad').attr('max', stock).val(1);
                    calcularSubtotalProducto($(this).closest('.item-box'));
                });
                
                $('.input-cantidad').off('input').on('input', function() {
                    calcularSubtotalProducto($(this).closest('.item-box'));
                });
                
                $('.btn-eliminar').off('click').on('click', function() {
                    $(this).closest('.item-box').remove();
                    calcularTotal();
                });
                
                // Autocalcular al perder foco
                $('.input-cantidad').off('blur').on('blur', function() {
                    calcularSubtotalProducto($(this).closest('.item-box'));
                });
            }
            
            // Actualizar eventos para servicios
            function actualizarEventosServicio() {
                $('.select-servicio').off('change').on('change', function() {
                    const precio = $(this).find(':selected').data('precio') || 0;
                    $(this).closest('.item-box').find('.input-precio').val(precio.toFixed(2));
                    calcularTotal();
                });
                
                $('.servicios-extras-container input[type="checkbox"]').off('change').on('change', function() {
                    if ($(this).is(':checked')) {
                        $(this).val($(this).attr('value'));
                    } else {
                        $(this).val('0');
                    }
                    calcularTotal();
                });
                
                $('.btn-eliminar').off('click').on('click', function() {
                    $(this).closest('.item-box').remove();
                    calcularTotal();
                });
            }
            
            // Calcular subtotal para un producto
            function calcularSubtotalProducto(itemBox) {
                const precio = parseFloat(itemBox.find('.input-precio').val()) || 0;
                const cantidad = parseInt(itemBox.find('.input-cantidad').val()) || 0;
                const subtotal = precio * cantidad;
                
                itemBox.find('.input-subtotal').val(subtotal.toFixed(2));
                calcularTotal();
            }
            
            // Calcular total general
            function calcularTotal() {
                let subtotal = 0;
                
                // Sumar productos
                $('.input-subtotal').each(function() {
                    subtotal += parseFloat($(this).val()) || 0;
                });
                
                // Sumar servicios
                $('.select-servicio').each(function() {
                    const precio = $(this).find(':selected').data('precio') || 0;
                    subtotal += parseFloat(precio);
                });
                
                // Sumar servicios extras seleccionados
                $('.servicios-extras-container input[type="checkbox"]:checked').each(function() {
                    subtotal += parseFloat($(this).attr('value')) || 0;
                });
                
                const igv = subtotal * 0.18;
                const total = subtotal + igv;
                
                // Actualizar inputs hidden
                $('#subtotal').val(subtotal.toFixed(2));
                $('#igv').val(igv.toFixed(2));
                $('#total').val(total.toFixed(2));
                
                // Actualizar resumen visual
                $('#resumenSubtotal').text('S/ ' + subtotal.toFixed(2));
                $('#resumenIgv').text('S/ ' + igv.toFixed(2));
                $('#resumenTotal').text('S/ ' + total.toFixed(2));
                
                // Calcular vuelto si es pago en efectivo
                calcularVuelto();
            }
            
            // Calcular vuelto
            function calcularVuelto() {
                const total = parseFloat($('#total').val()) || 0;
                const montoPagado = parseFloat($('#montoPagado').val()) || 0;
                const vuelto = montoPagado - total;
                
                $('#vuelto').val(vuelto.toFixed(2));
            }
            
            // Actualizar número de comprobante
            function actualizarNumeroComprobante() {
                $.get('ajax/generar_numero_comprobante.php', function(data) {
                    $('#numeroComprobante').text(data.numero);
                });
            }
            
            // Eventos
            $('#montoPagado').on('input', calcularVuelto);
            $('#quickCalculate').click(calcularTotal);
            $('#btnNuevaVenta').click(function() {
                if(confirm('¿Desea iniciar una nueva venta? Se perderán los datos no guardados.')) {
                    location.reload();
                }
            });
            
            // Inicializar con eventos
            $(document).on('keydown', '.input-cantidad', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    calcularSubtotalProducto($(this).closest('.item-box'));
                    
                    // En venta rápida, agregar nuevo producto al presionar Enter
                    if(ventaRapidaMode) {
                        $('#btnAgregarProducto').click();
                    }
                }
            });
            
            // Inicializar con un producto si es venta normal
            if(!ventaRapidaMode) {
                $('#btnAgregarProducto').click();
            }
        });
    </script>
</body>
</html>