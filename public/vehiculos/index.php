<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/constantes.php';

$database = new Database();
$conn = $database->getConnection();

// Manejo de mensajes
$mensaje = '';
if (isset($_GET['exito'])) {
    switch ($_GET['exito']) {
        case 'creado': $mensaje = '<div class="alert alert-success">Tipo de vehículo creado correctamente</div>'; break;
        case 'editado': $mensaje = '<div class="alert alert-success">Tipo de vehículo actualizado correctamente</div>'; break;
        case 'eliminado': $mensaje = '<div class="alert alert-success">Tipo de vehículo eliminado correctamente</div>'; break;
    }
}

if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'id': $mensaje = '<div class="alert alert-danger">ID inválido</div>'; break;
        case 'bd': $mensaje = '<div class="alert alert-danger">Error en la base de datos</div>'; break;
    }
}

try {
    $query = "SELECT * FROM tipo_vehiculo ORDER BY nombre";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $vehiculos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $mensaje = '<div class="alert alert-danger">Error al cargar los tipos de vehículo</div>';
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <?php include '../../template/head.php'; ?>
    <title>Tipos de Vehículo</title>
    <style>
        .estado-activo { color: #28a745; font-weight: bold; }
        .estado-inactivo { color: #dc3545; font-weight: bold; }
    </style>
</head>
<body>
    <?php include '../../template/navbar.php'; ?>
    
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12">
                <?php echo $mensaje; ?>
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4>Tipos de Vehículo</h4>
                        <a href="crear.php" class="btn btn-success">
                            <i class="fas fa-plus"></i> Nuevo Tipo
                        </a>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Descripción</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(count($vehiculos) > 0): ?>
                                    <?php foreach($vehiculos as $vehiculo): ?>
                                        <tr>
                                            <td><?= $vehiculo['id_tipo_vehiculo'] ?></td>
                                            <td><?= htmlspecialchars($vehiculo['nombre']) ?></td>
                                            <td><?= htmlspecialchars($vehiculo['descripcion']) ?></td>
                                            <td>
                                                <span class="<?= $vehiculo['estado'] ? 'estado-activo' : 'estado-inactivo' ?>">
                                                    <?= $vehiculo['estado'] ? 'Activo' : 'Inactivo' ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="editar.php?id=<?= $vehiculo['id_tipo_vehiculo'] ?>" class="btn btn-primary btn-sm">
                                                    <i class="fas fa-edit"></i> Editar
                                                </a>
                                                <a href="eliminar.php?id=<?= $vehiculo['id_tipo_vehiculo'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro?')">
                                                    <i class="fas fa-trash"></i> Eliminar
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center">No hay tipos de vehículo registrados</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../../template/scripts.php'; ?>
</body>
</html>