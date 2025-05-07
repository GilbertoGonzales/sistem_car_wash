<?php
require_once __DIR__ . '/../../config/database.php';

$database = new Database();
$conn = $database->getConnection();

// Configuración de paginación
$por_pagina = 15;
$pagina = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
$inicio = ($pagina - 1) * $por_pagina;

// Obtener parámetros de filtro
$filtros = [
    'fecha_inicio' => $_GET['fecha_inicio'] ?? '',
    'fecha_fin' => $_GET['fecha_fin'] ?? '',
    'tipo_venta' => $_GET['tipo_venta'] ?? '',
    'metodo_pago' => $_GET['metodo_pago'] ?? '',
    'estado' => $_GET['estado'] ?? 'activo',
    'cliente' => $_GET['cliente'] ?? ''
];

try {
    // Construir consulta base con JOIN a clientes
    $query = "
        SELECT 
            v.id_venta,
            v.fecha_hora AS fecha_venta,
            v.numero_comprobante,
            v.subtotal,
            v.igv,
            v.total AS total_venta,
            v.tipo_venta,
            v.metodo_pago,
            v.estado,
            v.observaciones,
            c.nombre AS cliente_nombre,
            c.dni AS cliente_dni,
            c.telefono AS cliente_telefono
        FROM 
            venta v
        LEFT JOIN 
            cliente c ON v.id_cliente = c.id_cliente
        WHERE 1=1
    ";
    
    // Añadir condiciones de filtro
    $params = [];
    
    if (!empty($filtros['fecha_inicio'])) {
        $query .= " AND DATE(v.fecha_hora) >= :fecha_inicio";
        $params[':fecha_inicio'] = $filtros['fecha_inicio'];
    }
    
    if (!empty($filtros['fecha_fin'])) {
        $query .= " AND DATE(v.fecha_hora) <= :fecha_fin";
        $params[':fecha_fin'] = $filtros['fecha_fin'];
    }
    
    if (!empty($filtros['tipo_venta'])) {
        $query .= " AND v.tipo_venta = :tipo_venta";
        $params[':tipo_venta'] = $filtros['tipo_venta'];
    }
    
    if (!empty($filtros['metodo_pago'])) {
        $query .= " AND v.metodo_pago = :metodo_pago";
        $params[':metodo_pago'] = $filtros['metodo_pago'];
    }
    
    if (!empty($filtros['estado'])) {
        $query .= " AND v.estado = :estado";
        $params[':estado'] = $filtros['estado'];
    }
    
    if (!empty($filtros['cliente'])) {
        $query .= " AND (c.nombre LIKE :cliente OR c.dni LIKE :cliente)";
        $params[':cliente'] = '%' . $filtros['cliente'] . '%';
    }

    // Consulta para el total de registros (para paginación)
    $queryTotal = "SELECT COUNT(*) as total FROM ($query) as total_query";
    $stmtTotal = $conn->prepare($queryTotal);
    foreach ($params as $param => $value) {
        $stmtTotal->bindValue($param, $value);
    }
    $stmtTotal->execute();
    $totalRegistros = $stmtTotal->fetch(PDO::FETCH_ASSOC)['total'];
    $totalPaginas = ceil($totalRegistros / $por_pagina);

    // Consulta principal con paginación
    $query .= " ORDER BY v.fecha_hora DESC LIMIT :inicio, :por_pagina";
$stmt = $conn->prepare($query);

// Vincula todos los parámetros
foreach ($params as $param => $value) {
    $stmt->bindValue($param, $value);
}

// Vincula los parámetros de paginación con el tipo correcto
$stmt->bindValue(':inicio', $inicio, PDO::PARAM_INT);
$stmt->bindValue(':por_pagina', $por_pagina, PDO::PARAM_INT);

$stmt->execute();
    
    // Obtener detalle de venta si se solicita
    $detalleVenta = null;
    $detalleProductos = [];
    $detalleServicios = [];
    
    if (isset($_GET['detalle']) && is_numeric($_GET['detalle'])) {
        $id_venta = $_GET['detalle'];
        
        // Consulta para obtener detalles de productos
        $queryProductos = "
            SELECT 
                dp.id_detalle_producto,
                dp.cantidad,
                dp.precio_unitario,
                dp.subtotal,
                p.nombre AS producto_nombre,
                p.codigo AS producto_codigo,
                p.descripcion AS producto_descripcion
            FROM 
                detalle_producto dp
            JOIN 
                producto p ON dp.id_producto = p.id_producto
            WHERE 
                dp.id_venta = :id_venta
        ";
        
        $stmtProductos = $conn->prepare($queryProductos);
        $stmtProductos->bindParam(':id_venta', $id_venta);
        $stmtProductos->execute();
        $detalleProductos = $stmtProductos->fetchAll(PDO::FETCH_ASSOC);
        
        // Consulta para obtener detalles de servicios
        $queryServicios = "
            SELECT 
                ds.id_detalle_servicio,
                ds.precio AS precio_unitario,
                tv.nombre AS tipo_vehiculo,
                ns.nombre AS nivel_servicio,
                GROUP_CONCAT(se.nombre SEPARATOR ', ') AS servicios_extras
            FROM 
                detalle_servicio ds
            JOIN 
                precio_servicio ps ON ds.id_precio_servicio = ps.id_precio_servicio
            JOIN 
                tipo_vehiculo tv ON ps.id_tipo_vehiculo = tv.id_tipo_vehiculo
            JOIN 
                nivel_servicio ns ON ps.id_nivel_servicio = ns.id_nivel_servicio
            LEFT JOIN
                detalle_servicio_extra dse ON ds.id_detalle_servicio = dse.id_detalle_servicio
            LEFT JOIN
                servicio_extra se ON dse.id_servicio_extra = se.id_servicio_extra
            WHERE 
                ds.id_venta = :id_venta
            GROUP BY
                ds.id_detalle_servicio
        ";
        
        $stmtServicios = $conn->prepare($queryServicios);
        $stmtServicios->bindParam(':id_venta', $id_venta);
        $stmtServicios->execute();
        $detalleServicios = $stmtServicios->fetchAll(PDO::FETCH_ASSOC);
        
        // Buscar la venta específica
        foreach ($ventas as $venta) {
            if ($venta['id_venta'] == $id_venta) {
                $detalleVenta = $venta;
                break;
            }
        }
    }
    
} catch (PDOException $e) {
    $mensaje_error = "Error al cargar ventas: " . $e->getMessage();
    $ventas = [];
    $totalRegistros = 0;
    $totalPaginas = 1;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <?php include '../../template/head.php'; ?>
    <title>Historial de Ventas - Sistema de Lavado</title>
    <style>
        :root {
            --color-producto: #4361ee;
            --color-servicio: #4cc9f0;
            --color-mixto: #7209b7;
            --color-efectivo: #38b000;
            --color-tarjeta: #f8961e;
            --color-yape: #4cc9f0;
            --color-transferencia: #7209b7;
        }
        
        .badge-producto { background-color: var(--color-producto); }
        .badge-servicio { background-color: var(--color-servicio); }
        .badge-mixto { background-color: var(--color-mixto); }
        .badge-efectivo { background-color: var(--color-efectivo); }
        .badge-tarjeta { background-color: var(--color-tarjeta); }
        .badge-yape { background-color: var(--color-yape); }
        .badge-transferencia { background-color: var(--color-transferencia); }
        
        .card-venta {
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            border: none;
        }
        
        .card-header {
            border-radius: 10px 10px 0 0 !important;
            padding: 1.2rem;
        }
        
        .table-ventas {
            font-size: 0.9rem;
        }
        
        .table-ventas th {
            background-color: #f8f9fa;
            border-top: none;
        }
        
        .table-ventas tr:hover {
            background-color: #f8f9fa;
        }
        
        .venta-item {
            transition: all 0.2s ease;
        }
        
        .venta-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .pagination .page-item.active .page-link {
            background-color: var(--color-primary);
            border-color: var(--color-primary);
        }
        
        .pagination .page-link {
            color: var(--color-primary);
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
        
        @media (max-width: 768px) {
            .table-responsive {
                font-size: 0.8rem;
            }
            
            .btn-sm {
                padding: 0.25rem 0.5rem;
                font-size: 0.8rem;
            }
        }
    </style>
</head>
<body>
    <?php include '../../template/navbar.php'; ?>

    <div class="container-fluid mt-3 mb-5">
        <?php if(isset($mensaje_error)): ?>
            <div class="alert alert-danger"><?= $mensaje_error ?></div>
        <?php endif; ?>
        
        <!-- Vista de detalle de venta -->
        <?php if (isset($_GET['detalle']) && $detalleVenta): ?>
            <div class="row mb-3">
                <div class="col-md-12">
                    <a href="historial_ventas.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Volver al historial
                    </a>
                </div>
            </div>
            
            <div class="row">
                <div class="col-lg-8">
                    <div class="card card-venta mb-4">
                        <div class="card-header bg-primary text-white">
                            <h4 class="mb-0">
                                <i class="fas fa-receipt me-2"></i>
                                Detalle de Venta #<?= $detalleVenta['id_venta'] ?>
                                <span class="badge bg-light text-dark float-end">
                                    <?= $detalleVenta['numero_comprobante'] ?>
                                </span>
                            </h4>
                        </div>
                        <div class="card-body">
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <h5><i class="fas fa-calendar-alt me-2"></i>Información de la Venta</h5>
                                    <table class="table table-borderless">
                                        <tr>
                                            <th>Fecha:</th>
                                            <td><?= date('d/m/Y H:i:s', strtotime($detalleVenta['fecha_venta'])) ?></td>
                                        </tr>
                                        <tr>
                                            <th>Tipo:</th>
                                            <td>
                                                <span class="badge badge-<?= strtolower($detalleVenta['tipo_venta']) ?>">
                                                    <?= ucfirst($detalleVenta['tipo_venta']) ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Método Pago:</th>
                                            <td>
                                                <span class="badge badge-<?= strtolower($detalleVenta['metodo_pago']) ?>">
                                                    <?= ucfirst($detalleVenta['metodo_pago']) ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Estado:</th>
                                            <td>
                                                <?php 
                                                $badgeClass = $detalleVenta['estado'] === 'activo' ? 'badge-success' : 'badge-secondary';
                                                echo '<span class="badge ' . $badgeClass . '">' . ucfirst($detalleVenta['estado']) . '</span>';
                                                ?>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <h5><i class="fas fa-user me-2"></i>Información del Cliente</h5>
                                    <table class="table table-borderless">
                                        <tr>
                                            <th>Nombre:</th>
                                            <td><?= !empty($detalleVenta['cliente_nombre']) ? $detalleVenta['cliente_nombre'] : 'Cliente no registrado' ?></td>
                                        </tr>
                                        <tr>
                                            <th>DNI:</th>
                                            <td><?= $detalleVenta['cliente_dni'] ?? 'N/A' ?></td>
                                        </tr>
                                        <tr>
                                            <th>Teléfono:</th>
                                            <td><?= $detalleVenta['cliente_telefono'] ?? 'N/A' ?></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            
                            <?php if (!empty($detalleProductos)): ?>
                                <div class="mb-4">
                                    <h5><i class="fas fa-boxes me-2"></i>Productos</h5>
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Código</th>
                                                    <th>Producto</th>
                                                    <th class="text-end">Cantidad</th>
                                                    <th class="text-end">Precio Unit.</th>
                                                    <th class="text-end">Subtotal</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach($detalleProductos as $producto): ?>
                                                    <tr>
                                                        <td><?= $producto['producto_codigo'] ?></td>
                                                        <td>
                                                            <strong><?= $producto['producto_nombre'] ?></strong>
                                                            <?php if(!empty($producto['producto_descripcion'])): ?>
                                                                <br><small class="text-muted"><?= $producto['producto_descripcion'] ?></small>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td class="text-end"><?= $producto['cantidad'] ?></td>
                                                        <td class="text-end">S/ <?= number_format($producto['precio_unitario'], 2) ?></td>
                                                        <td class="text-end">S/ <?= number_format($producto['subtotal'], 2) ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($detalleServicios)): ?>
                                <div class="mb-4">
                                    <h5><i class="fas fa-car me-2"></i>Servicios</h5>
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Vehículo</th>
                                                    <th>Servicio</th>
                                                    <th>Extras</th>
                                                    <th class="text-end">Precio</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach($detalleServicios as $servicio): ?>
                                                    <tr>
                                                        <td><?= $servicio['tipo_vehiculo'] ?></td>
                                                        <td><?= $servicio['nivel_servicio'] ?></td>
                                                        <td>
                                                            <?php if(!empty($servicio['servicios_extras'])): ?>
                                                                <small><?= $servicio['servicios_extras'] ?></small>
                                                            <?php else: ?>
                                                                <span class="text-muted">Ninguno</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td class="text-end">S/ <?= number_format($servicio['precio_unitario'], 2) ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <?php if(!empty($detalleVenta['observaciones'])): ?>
                                <div class="mb-4">
                                    <h5><i class="fas fa-comment me-2"></i>Observaciones</h5>
                                    <div class="alert alert-light">
                                        <?= nl2br(htmlspecialchars($detalleVenta['observaciones'])) ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <div class="row">
                                <div class="col-md-6 offset-md-6">
                                    <div class="summary-card">
                                        <table class="table table-sm">
                                            <tr>
                                                <th>Subtotal:</th>
                                                <td class="text-end">S/ <?= number_format($detalleVenta['subtotal'], 2) ?></td>
                                            </tr>
                                            <tr>
                                                <th>IGV (18%):</th>
                                                <td class="text-end">S/ <?= number_format($detalleVenta['igv'], 2) ?></td>
                                            </tr>
                                            <tr class="table-active">
                                                <th>Total:</th>
                                                <td class="text-end fw-bold">S/ <?= number_format($detalleVenta['total_venta'], 2) ?></td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-4 text-center">
                                <a href="imprimir_venta.php?id=<?= $detalleVenta['id_venta'] ?>" class="btn btn-primary me-2" target="_blank">
                                    <i class="fas fa-print me-2"></i>Imprimir Comprobante
                                </a>
                                <?php if($detalleVenta['estado'] === 'activo'): ?>
                                    <button class="btn btn-danger" id="btnAnularVenta" data-id="<?= $detalleVenta['id_venta'] ?>">
                                        <i class="fas fa-ban me-2"></i>Anular Venta
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        
        <?php else: ?>
            <!-- Vista principal del historial -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="card card-venta mb-4">
                        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                            <h4 class="mb-0"><i class="fas fa-history me-2"></i>Historial de Ventas</h4>
                            <div>
                                <a href="../ventas/nueva_venta.php" class="btn btn-success btn-sm">
                                    <i class="fas fa-plus me-2"></i>Nueva Venta
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- Filtros -->
                            <div class="mb-4">
                                <form method="GET" action="">
                                    <div class="row g-3">
                                        <div class="col-md-2">
                                            <label for="fecha_inicio" class="form-label">Fecha Inicio</label>
                                            <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" value="<?= $filtros['fecha_inicio'] ?>">
                                        </div>
                                        <div class="col-md-2">
                                            <label for="fecha_fin" class="form-label">Fecha Fin</label>
                                            <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" value="<?= $filtros['fecha_fin'] ?>">
                                        </div>
                                        <div class="col-md-2">
                                            <label for="tipo_venta" class="form-label">Tipo</label>
                                            <select class="form-select" id="tipo_venta" name="tipo_venta">
                                                <option value="">Todos</option>
                                                <option value="producto" <?= $filtros['tipo_venta'] === 'producto' ? 'selected' : '' ?>>Productos</option>
                                                <option value="servicio" <?= $filtros['tipo_venta'] === 'servicio' ? 'selected' : '' ?>>Servicios</option>
                                                <option value="mixto" <?= $filtros['tipo_venta'] === 'mixto' ? 'selected' : '' ?>>Mixto</option>
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <label for="metodo_pago" class="form-label">Pago</label>
                                            <select class="form-select" id="metodo_pago" name="metodo_pago">
                                                <option value="">Todos</option>
                                                <option value="efectivo" <?= $filtros['metodo_pago'] === 'efectivo' ? 'selected' : '' ?>>Efectivo</option>
                                                <option value="tarjeta" <?= $filtros['metodo_pago'] === 'tarjeta' ? 'selected' : '' ?>>Tarjeta</option>
                                                <option value="yape" <?= $filtros['metodo_pago'] === 'yape' ? 'selected' : '' ?>>Yape/Plin</option>
                                                <option value="transferencia" <?= $filtros['metodo_pago'] === 'transferencia' ? 'selected' : '' ?>>Transferencia</option>
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <label for="estado" class="form-label">Estado</label>
                                            <select class="form-select" id="estado" name="estado">
                                                <option value="activo" <?= $filtros['estado'] === 'activo' ? 'selected' : '' ?>>Activas</option>
                                                <option value="anulado" <?= $filtros['estado'] === 'anulado' ? 'selected' : '' ?>>Anuladas</option>
                                                <option value="" <?= empty($filtros['estado']) ? 'selected' : '' ?>>Todos</option>
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <label for="cliente" class="form-label">Cliente</label>
                                            <input type="text" class="form-control" id="cliente" name="cliente" placeholder="Nombre o DNI" value="<?= $filtros['cliente'] ?>">
                                        </div>
                                        <div class="col-md-12 text-end">
                                            <button type="submit" class="btn btn-primary me-2">
                                                <i class="fas fa-search me-2"></i>Buscar
                                            </button>
                                            <a href="historial_ventas.php" class="btn btn-secondary">
                                                <i class="fas fa-sync-alt me-2"></i>Limpiar
                                            </a>
                                            <button type="button" class="btn btn-success" id="btnExportar">
                                                <i class="fas fa-file-excel me-2"></i>Exportar
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            
                            <!-- Resumen de ventas -->
                            <div class="alert alert-info mb-4">
                                <div class="row">
                                    <div class="col-md-4">
                                        <strong>Total Ventas:</strong> <?= number_format($totalRegistros) ?>
                                    </div>
                                    <div class="col-md-4">
                                        <strong>Mostrando:</strong> <?= min($por_pagina, count($ventas)) ?> por página
                                    </div>
                                    <div class="col-md-4 text-end">
                                        <strong>Página:</strong> <?= $pagina ?> de <?= $totalPaginas ?>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Tabla de ventas -->
                            <?php if (empty($ventas)): ?>
                                <div class="alert alert-warning">
                                    No se encontraron ventas con los filtros seleccionados.
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-ventas table-hover">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Fecha</th>
                                                <th>Cliente</th>
                                                <th>Tipo</th>
                                                <th>Pago</th>
                                                <th class="text-end">Total</th>
                                                <th>Estado</th>
                                                <th class="text-center">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($ventas as $venta): ?>
                                                <tr class="venta-item">
                                                    <td><?= $venta['id_venta'] ?></td>
                                                    <td>
                                                        <?= date('d/m/Y H:i', strtotime($venta['fecha_venta'])) ?>
                                                        <br>
                                                        <small class="text-muted"><?= $venta['numero_comprobante'] ?></small>
                                                    </td>
                                                    <td>
                                                        <?php if(!empty($venta['cliente_nombre'])): ?>
                                                            <strong><?= $venta['cliente_nombre'] ?></strong>
                                                            <br>
                                                            <small class="text-muted">DNI: <?= $venta['cliente_dni'] ?? 'N/A' ?></small>
                                                        <?php else: ?>
                                                            <span class="text-muted">Cliente no registrado</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <span class="badge badge-<?= strtolower($venta['tipo_venta']) ?>">
                                                            <?= ucfirst($venta['tipo_venta']) ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="badge badge-<?= strtolower($venta['metodo_pago']) ?>">
                                                            <?= ucfirst($venta['metodo_pago']) ?>
                                                        </span>
                                                    </td>
                                                    <td class="text-end">
                                                        <strong>S/ <?= number_format($venta['total_venta'], 2) ?></strong>
                                                    </td>
                                                    <td>
                                                        <?php 
                                                        $badgeClass = $venta['estado'] === 'activo' ? 'badge-success' : 'badge-secondary';
                                                        echo '<span class="badge ' . $badgeClass . '">' . ucfirst($venta['estado']) . '</span>';
                                                        ?>
                                                    </td>
                                                    <td class="text-center">
                                                        <a href="historial_ventas.php?detalle=<?= $venta['id_venta'] ?>" class="btn btn-sm btn-info" title="Ver detalle">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="imprimir_venta.php?id=<?= $venta['id_venta'] ?>" class="btn btn-sm btn-secondary" title="Imprimir" target="_blank">
                                                            <i class="fas fa-print"></i>
                                                        </a>
                                                        <?php if($venta['estado'] === 'activo'): ?>
                                                            <button class="btn btn-sm btn-danger btn-anular" title="Anular" data-id="<?= $venta['id_venta'] ?>">
                                                                <i class="fas fa-ban"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <!-- Paginación -->
                                <?php if($totalPaginas > 1): ?>
                                    <nav aria-label="Page navigation">
                                        <ul class="pagination justify-content-center">
                                            <?php if($pagina > 1): ?>
                                                <li class="page-item">
                                                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['pagina' => $pagina - 1])) ?>">
                                                        <i class="fas fa-chevron-left"></i>
                                                    </a>
                                                </li>
                                            <?php endif; ?>
                                            
                                            <?php for($i = 1; $i <= $totalPaginas; $i++): ?>
                                                <li class="page-item <?= $i == $pagina ? 'active' : '' ?>">
                                                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['pagina' => $i])) ?>"><?= $i ?></a>
                                                </li>
                                            <?php endfor; ?>
                                            
                                            <?php if($pagina < $totalPaginas): ?>
                                                <li class="page-item">
                                                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['pagina' => $pagina + 1])) ?>">
                                                        <i class="fas fa-chevron-right"></i>
                                                    </a>
                                                </li>
                                            <?php endif; ?>
                                        </ul>
                                    </nav>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <?php include '../../template/scripts.php'; ?>
    <script>
        $(document).ready(function() {
            // Validación de fechas
            $('#fecha_inicio, #fecha_fin').change(function() {
                const fechaInicio = $('#fecha_inicio').val();
                const fechaFin = $('#fecha_fin').val();
                
                if (fechaInicio && fechaFin && fechaInicio > fechaFin) {
                    alert('La fecha de inicio no puede ser mayor que la fecha fin');
                    $('#fecha_fin').val(fechaInicio);
                }
            });
            
            // Anular venta
            $('.btn-anular, #btnAnularVenta').click(function() {
                const idVenta = $(this).data('id');
                
                if (confirm('¿Está seguro que desea anular esta venta? Esta acción no se puede deshacer.')) {
                    $.post('ajax/anular_venta.php', { id: idVenta }, function(response) {
                        if (response.success) {
                            alert('Venta anulada correctamente');
                            location.reload();
                        } else {
                            alert('Error al anular venta: ' + response.message);
                        }
                    }, 'json').fail(function() {
                        alert('Error al comunicarse con el servidor');
                    });
                }
            });
            
            // Exportar a Excel
            $('#btnExportar').click(function() {
                const params = new URLSearchParams(window.location.search);
                window.location.href = 'exportar_ventas.php?' + params.toString();
            });
        });
    </script>
</body>
</html>