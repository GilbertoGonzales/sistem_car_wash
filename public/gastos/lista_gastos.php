<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/constantes.php';

session_start();

$database = new Database();
$conn = $database->getConnection();

// Inicialización de variables
$gastos = [];
$categorias = [];
$mensaje = $_SESSION['mensaje'] ?? null;
$error = $_SESSION['error'] ?? null;
$filtros = [
    'fecha_desde' => $_GET['fecha_desde'] ?? '',
    'fecha_hasta' => $_GET['fecha_hasta'] ?? '',
    'id_categoria' => $_GET['id_categoria'] ?? '',
    'busqueda' => $_GET['busqueda'] ?? ''
];

// Limpiar mensajes de sesión
unset($_SESSION['mensaje'], $_SESSION['error']);

// Paginación
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$registros_por_pagina = 10;
$offset = ($pagina_actual - 1) * $registros_por_pagina;

try {
    // Obtener categorías para el filtro
    $queryCategorias = "SELECT * FROM categoria_gasto WHERE estado = 1 ORDER BY nombre";
    $stmtCategorias = $conn->query($queryCategorias);
    $categorias = $stmtCategorias->fetchAll(PDO::FETCH_ASSOC);

    // Construir consulta con filtros
    $query = "SELECT SQL_CALC_FOUND_ROWS g.*, c.nombre as categoria 
              FROM gasto g
              JOIN categoria_gasto c ON g.id_categoria_gasto = c.id_categoria_gasto
              WHERE 1=1";
    
    $params = [];
    
    // Aplicar filtros
    if (!empty($filtros['fecha_desde'])) {
        $query .= " AND g.fecha_gasto >= :fecha_desde";
        $params[':fecha_desde'] = $filtros['fecha_desde'];
    }
    
    if (!empty($filtros['fecha_hasta'])) {
        $query .= " AND g.fecha_gasto <= :fecha_hasta";
        $params[':fecha_hasta'] = $filtros['fecha_hasta'];
    }
    
    if (!empty($filtros['id_categoria'])) {
        $query .= " AND g.id_categoria_gasto = :id_categoria";
        $params[':id_categoria'] = $filtros['id_categoria'];
    }
    
    if (!empty($filtros['busqueda'])) {
        $query .= " AND (g.descripcion LIKE :busqueda OR c.nombre LIKE :busqueda)";
        $params[':busqueda'] = '%' . $filtros['busqueda'] . '%';
    }
    
    $query .= " ORDER BY g.fecha_gasto DESC, g.id_gasto DESC
               LIMIT :offset, :limit";
    
    // Preparar y ejecutar consulta
    $stmt = $conn->prepare($query);
    
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $registros_por_pagina, PDO::PARAM_INT);
    $stmt->execute();
    
    $gastos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Obtener total de registros para paginación
    $total_registros = $conn->query("SELECT FOUND_ROWS()")->fetchColumn();
    $total_paginas = ceil($total_registros / $registros_por_pagina);

} catch (PDOException $e) {
    $error = "Error al cargar gastos: " . $e->getMessage();
    error_log($error);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <?php include '../../template/head.php'; ?>
    <title>Lista de Gastos - Carwash</title>
    <style>
        .table-responsive { overflow-x: auto; }
        .badge-total { background-color: #6c757d; }
        .filter-card { margin-bottom: 20px; }
    </style>
</head>
<body>
    <?php include '../../template/navbar.php'; ?>

    <div class="container py-4">
        <!-- Mensajes de feedback -->
        <?php if ($mensaje): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?= htmlspecialchars($mensaje) ?>
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?= htmlspecialchars($error) ?>
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        <?php endif; ?>

        <div class="card shadow filter-card">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="fas fa-filter mr-2"></i>Filtros</h5>
            </div>
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label for="fecha_desde" class="form-label">Desde</label>
                        <input type="date" class="form-control" id="fecha_desde" name="fecha_desde" 
                               value="<?= htmlspecialchars($filtros['fecha_desde']) ?>">
                    </div>
                    <div class="col-md-3">
                        <label for="fecha_hasta" class="form-label">Hasta</label>
                        <input type="date" class="form-control" id="fecha_hasta" name="fecha_hasta" 
                               value="<?= htmlspecialchars($filtros['fecha_hasta']) ?>">
                    </div>
                    <div class="col-md-3">
                        <label for="id_categoria" class="form-label">Categoría</label>
                        <select class="form-select" id="id_categoria" name="id_categoria">
                            <option value="">Todas</option>
                            <?php foreach ($categorias as $categoria): ?>
                                <option value="<?= $categoria['id_categoria_gasto'] ?>" 
                                    <?= $filtros['id_categoria'] == $categoria['id_categoria_gasto'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($categoria['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="busqueda" class="form-label">Buscar</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="busqueda" name="busqueda" 
                                   placeholder="Descripción o categoría" 
                                   value="<?= htmlspecialchars($filtros['busqueda']) ?>">
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="d-flex justify-content-end">
                            <a href="lista_gastos.php" class="btn btn-secondary me-2">
                                <i class="fas fa-sync-alt mr-2"></i>Limpiar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter mr-2"></i>Filtrar
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="mb-0"><i class="fas fa-money-bill-wave mr-2"></i>Historial de Gastos</h4>
                    <div>
                        <span class="badge badge-total">
                            Total: <?= number_format($total_registros ?? 0, 0) ?> registros
                        </span>
                        <a href="nuevo_gasto.php" class="btn btn-light ml-3">
                            <i class="fas fa-plus mr-2"></i>Nuevo
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th>Fecha</th>
                                <th>Categoría</th>
                                <th>Descripción</th>
                                <th class="text-right">Monto (S/)</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($gastos)): ?>
                                <?php foreach($gastos as $gasto): ?>
                                    <tr>
                                        <td><?= date('d/m/Y', strtotime($gasto['fecha_gasto'])) ?></td>
                                        <td><?= htmlspecialchars($gasto['categoria']) ?></td>
                                        <td><?= htmlspecialchars($gasto['descripcion']) ?></td>
                                        <td class="text-right"><?= number_format($gasto['monto'], 2) ?></td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm">
                                                <a href="editar.php?id=<?= $gasto['id_gasto'] ?>" 
                                                   class="btn btn-primary" title="Editar">
                                                   <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="eliminar.php?id=<?= $gasto['id_gasto'] ?>" 
                                                   class="btn btn-danger" title="Eliminar"
                                                   onclick="return confirm('¿Confirmas eliminar este gasto?\n\nFecha: <?= date('d/m/Y', strtotime($gasto['fecha_gasto'])) ?>\nCategoría: <?= htmlspecialchars(addslashes($gasto['categoria'])) ?>\nMonto: S/ <?= number_format($gasto['monto'], 2) ?>');">
                                                   <i class="fas fa-trash-alt"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        <i class="fas fa-info-circle fa-2x mb-3"></i><br>
                                        No se encontraron gastos
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <?php if ($total_paginas > 1): ?>
                <nav aria-label="Paginación">
                    <ul class="pagination justify-content-center">
                        <?php if ($pagina_actual > 1): ?>
                            <li class="page-item">
                                <a class="page-link" 
                                   href="?<?= http_build_query(array_merge($filtros, ['pagina' => 1])) ?>" 
                                   aria-label="Primera">
                                    <span aria-hidden="true">&laquo;&laquo;</span>
                                </a>
                            </li>
                            <li class="page-item">
                                <a class="page-link" 
                                   href="?<?= http_build_query(array_merge($filtros, ['pagina' => $pagina_actual - 1])) ?>" 
                                   aria-label="Anterior">
                                    <span aria-hidden="true">&laquo;</span>
                                </a>
                            </li>
                        <?php endif; ?>

                        <?php 
                        $inicio = max(1, $pagina_actual - 2);
                        $fin = min($total_paginas, $pagina_actual + 2);
                        
                        for ($i = $inicio; $i <= $fin; $i++): ?>
                            <li class="page-item <?= $i == $pagina_actual ? 'active' : '' ?>">
                                <a class="page-link" 
                                   href="?<?= http_build_query(array_merge($filtros, ['pagina' => $i])) ?>">
                                   <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($pagina_actual < $total_paginas): ?>
                            <li class="page-item">
                                <a class="page-link" 
                                   href="?<?= http_build_query(array_merge($filtros, ['pagina' => $pagina_actual + 1])) ?>" 
                                   aria-label="Siguiente">
                                    <span aria-hidden="true">&raquo;</span>
                                </a>
                            </li>
                            <li class="page-item">
                                <a class="page-link" 
                                   href="?<?= http_build_query(array_merge($filtros, ['pagina' => $total_paginas])) ?>" 
                                   aria-label="Última">
                                    <span aria-hidden="true">&raquo;&raquo;</span>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include '../../template/scripts.php'; ?>
    <script>
        $(document).ready(function() {
            // Inicializar datepicker si es necesario
            $('[data-toggle="datepicker"]').datepicker({
                format: 'yyyy-mm-dd',
                autoclose: true
            });
            
            // Confirmación antes de eliminar
            $('.btn-eliminar').click(function() {
                return confirm('¿Estás seguro de eliminar este gasto?');
            });
        });
    </script>
</body>
</html>