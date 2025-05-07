<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/constantes.php';

$database = new Database();
$conn = $database->getConnection();

// Manejo de mensajes
$mensaje = '';
if (isset($_GET['exito'])) {
    switch ($_GET['exito']) {
        case 'creado': $mensaje = '<div class="alert alert-success">Nivel de servicio creado correctamente</div>'; break;
        case 'editado': $mensaje = '<div class="alert alert-success">Nivel de servicio actualizado correctamente</div>'; break;
        case 'eliminado': $mensaje = '<div class="alert alert-success">Nivel de servicio eliminado correctamente</div>'; break;
    }
}

if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'id': $mensaje = '<div class="alert alert-danger">ID inválido</div>'; break;
        case 'bd': $mensaje = '<div class="alert alert-danger">Error en la base de datos</div>'; break;
    }
}

try {
    $query = "SELECT * FROM nivel_servicio ORDER BY nombre";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $niveles = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $mensaje = '<div class="alert alert-danger">Error al cargar los niveles de servicio</div>';
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <?php include '../../template/head.php'; ?>
    <title>Niveles de Servicio</title>
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
                        <h4>Niveles de Servicio</h4>
                        <a href="crear.php" class="btn btn-success">
                            <i class="fas fa-plus"></i> Nuevo Nivel
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
                                    <th>Fecha Creación</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(count($niveles) > 0): ?>
                                    <?php foreach($niveles as $nivel): ?>
                                        <tr>
                                            <td><?= $nivel['id_nivel_servicio'] ?></td>
                                            <td><?= htmlspecialchars($nivel['nombre']) ?></td>
                                            <td><?= htmlspecialchars($nivel['descripcion']) ?></td>
                                            <td>
                                                <span class="<?= $nivel['estado'] ? 'estado-activo' : 'estado-inactivo' ?>">
                                                    <?= $nivel['estado'] ? 'Activo' : 'Inactivo' ?>
                                                </span>
                                            </td>
                                            <td><?= htmlspecialchars($nivel['fecha_creacion']) ?></td>
                                            <td>
                                                <a href="editar.php?id=<?= $nivel['id_nivel_servicio'] ?>" class="btn btn-primary btn-sm">
                                                    <i class="fas fa-edit"></i> Editar
                                                </a>
                                                <a href="eliminar.php?id=<?= $nivel['id_nivel_servicio'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro?')">
                                                    <i class="fas fa-trash"></i> Eliminar
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center">No hay niveles de servicio registrados</td>
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