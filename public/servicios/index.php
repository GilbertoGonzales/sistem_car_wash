<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/constantes.php';

session_start();

$database = new Database();
$conn = $database->getConnection();

// Inicializar variables
$servicios = [];
$mensaje = $_SESSION['mensaje'] ?? null;
$error = $_SESSION['error'] ?? null;

// Limpiar mensajes de sesión
unset($_SESSION['mensaje'], $_SESSION['error']);

try {
    $query = "SELECT 
                ps.id_precio_servicio,
                tv.nombre AS nombre_vehiculo, 
                ns.nombre AS nombre_nivel_servicio,
                ps.precio,
                ps.estado
              FROM precio_servicio ps
              JOIN tipo_vehiculo tv ON ps.id_tipo_vehiculo = tv.id_tipo_vehiculo
              JOIN nivel_servicio ns ON ps.id_nivel_servicio = ns.id_nivel_servicio
              ORDER BY tv.nombre, ns.nombre";
              
    $stmt = $conn->query($query);
    $servicios = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error = "Error al cargar los servicios: " . $e->getMessage();
    error_log($error);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <?php include '../../template/head.php'; ?>
    <title>Lista de Servicios - Carwash</title>
    <style>
        .table-responsive { overflow-x: auto; }
        .badge-disponible { background-color: #28a745; }
        .badge-nodisponible { background-color: #dc3545; }
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

        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">
                        <i class="fas fa-car-wash mr-2"></i>Lista de Servicios
                    </h4>
                    <a href="crear.php" class="btn btn-light">
                        <i class="fas fa-plus mr-2"></i>Nuevo Servicio
                    </a>
                </div>
            </div>
            
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th>Tipo de Vehículo</th>
                                <th>Nivel de Servicio</th>
                                <th class="text-right">Precio (S/)</th>
                                <th>Estado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($servicios)): ?>
                                <?php foreach($servicios as $servicio): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($servicio['nombre_vehiculo']) ?></td>
                                        <td><?= htmlspecialchars($servicio['nombre_nivel_servicio']) ?></td>
                                        <td class="text-right"><?= number_format($servicio['precio'], 2) ?></td>
                                        <td>
                                            <span class="badge <?= $servicio['estado'] ? 'badge-disponible' : 'badge-nodisponible' ?>">
                                                <?= $servicio['estado'] ? 'Disponible' : 'No Disponible' ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm">
                                                <a href="editar.php?id=<?= $servicio['id_precio_servicio'] ?>" 
                                                   class="btn btn-primary" 
                                                   title="Editar">
                                                   <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="eliminar.php?id=<?= $servicio['id_precio_servicio'] ?>" 
                                                   class="btn btn-danger" 
                                                   title="Eliminar"
                                                   onclick="return confirm('¿Confirmas eliminar este servicio?\n\nTipo: <?= htmlspecialchars(addslashes($servicio['nombre_vehiculo'])) ?>\nNivel: <?= htmlspecialchars(addslashes($servicio['nombre_nivel_servicio'])) ?>');">
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
                                        No hay servicios registrados
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <?php include '../../template/scripts.php'; ?>
</body>
</html>