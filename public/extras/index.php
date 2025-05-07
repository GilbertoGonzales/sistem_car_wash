<?php
require_once __DIR__ . '/../../config/database.php';

$database = new Database();
$conn = $database->getConnection();

$servicios_extra = []; // Inicializar la variable para evitar errores con count()

// Manejo de mensajes de retroalimentación
$success = $_GET['success'] ?? null;
$deleted = $_GET['deleted'] ?? null;
$deactivated = $_GET['deactivated'] ?? null;

try {
    // Consulta para obtener los servicios extras de la tabla servicio_extra
    $query = "SELECT id_servicio_extra, nombre, descripcion, precio, estado, fecha_creacion 
              FROM servicio_extra ORDER BY fecha_creacion DESC";  // Ordena por fecha_creacion
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $servicios_extra = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = "Error en la consulta: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <?php include '../../template/head.php'; ?>
    <title>Lista de Servicios Extras</title>
    <style>
        .status-active {
            color: #28a745;
            font-weight: bold;
        }
        .status-inactive {
            color: #dc3545;
            font-weight: bold;
        }
        .table-actions {
            white-space: nowrap;
        }
    </style>
</head>
<body>
    <?php include '../../template/navbar.php'; ?>
    
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4>Lista de Servicios Extras</h4>
                            <a href="crear.php" class="btn btn-success">
                                <i class="fas fa-plus"></i> Nuevo Servicio Extra
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if (isset($error_message)): ?>
                            <div class="alert alert-danger"><?php echo $error_message; ?></div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success">Servicio extra actualizado correctamente</div>
                        <?php elseif ($deleted): ?>
                            <div class="alert alert-success">Servicio extra eliminado correctamente</div>
                        <?php elseif ($deactivated): ?>
                            <div class="alert alert-warning">El servicio extra no pudo ser eliminado (tiene relaciones), se ha desactivado</div>
                        <?php endif; ?>

                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-hover">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Descripción</th>
                                        <th>Precio</th>
                                        <th>Estado</th>
                                        <th>Fecha Creación</th>
                                        <th class="table-actions">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(count($servicios_extra) > 0): ?>
                                        <?php foreach($servicios_extra as $servicio): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($servicio['nombre']); ?></td>
                                                <td><?php echo htmlspecialchars($servicio['descripcion']); ?></td>
                                                <td>S/ <?php echo number_format($servicio['precio'], 2); ?></td>
                                                <td>
                                                    <span class="<?php echo $servicio['estado'] ? 'status-active' : 'status-inactive'; ?>">
                                                        <?php echo $servicio['estado'] ? 'Activo' : 'Inactivo'; ?>
                                                    </span>
                                                </td>
                                                <td><?php echo date('d/m/Y', strtotime($servicio['fecha_creacion'])); ?></td>
                                                <td class="table-actions">
                                                    <a href="editar.php?id_servicio_extra=<?php echo $servicio['id_servicio_extra']; ?>" 
                                                       class="btn btn-primary btn-sm" title="Editar">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="eliminar.php?id_servicio_extra=<?php echo $servicio['id_servicio_extra']; ?>" 
                                                       class="btn btn-danger btn-sm" 
                                                       title="Eliminar"
                                                       onclick="return confirm('¿Estás seguro de que deseas <?php echo $servicio['estado'] ? 'desactivar' : 'eliminar'; ?> este servicio extra?')">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center">No hay servicios extras registrados</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../../template/scripts.php'; ?>
    
    <script>
        // Confirmación antes de eliminar con mensaje contextual
        document.querySelectorAll('.btn-danger').forEach(button => {
            button.addEventListener('click', function(e) {
                const isActive = this.getAttribute('title') === 'Desactivar';
                const message = isActive 
                    ? '¿Estás seguro de desactivar este servicio extra?' 
                    : '¿Estás seguro de eliminar permanentemente este servicio extra?';
                
                if (!confirm(message)) {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>