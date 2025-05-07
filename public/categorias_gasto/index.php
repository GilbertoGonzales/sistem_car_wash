<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/constantes.php';

// Iniciar sesión para mensajes flash
session_start();

$database = new Database();
$conn = $database->getConnection();

// Manejo de mensajes
$mensaje = $_SESSION['mensaje'] ?? '';
$tipoMensaje = $_SESSION['tipo_mensaje'] ?? '';
unset($_SESSION['mensaje'], $_SESSION['tipo_mensaje']);

// Obtener todas las categorías de gasto
try {
    $query = "SELECT * FROM categoria_gasto ORDER BY nombre ASC";
    $stmt = $conn->query($query);
    $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $mensaje = "Error al cargar categorías: " . $e->getMessage();
    $tipoMensaje = 'danger';
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <?php include '../../template/head.php'; ?>
    <title>Categorías de Gasto</title>
    <style>
        .badge-activo { background-color: #28a745; }
        .badge-inactivo { background-color: #6c757d; }
        .action-buttons .btn { margin-right: 5px; }
        .table-responsive { overflow-x: auto; }
        .card-header { background-color: #f8f9fa; }
        .empty-state { padding: 2rem; text-align: center; }
        .empty-state i { font-size: 3rem; color: #6c757d; }
    </style>
</head>
<body>
    <?php include '../../template/navbar.php'; ?>
    
    <div class="container py-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="mb-0"><i class="fas fa-money-bill-wave"></i> Categorías de Gasto</h2>
                    <a href="crear.php" class="btn btn-success">
                        <i class="fas fa-plus-circle"></i> Nueva Categoría
                    </a>
                </div>
                
                <?php if ($mensaje): ?>
                    <div class="alert alert-<?= $tipoMensaje ?> alert-dismissible fade show">
                        <?= $mensaje ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="mb-0">Listado de Categorías</h5>
                    </div>
                    
                    <div class="card-body">
                        <?php if (empty($categorias)): ?>
                            <div class="empty-state">
                                <i class="fas fa-money-bill-wave"></i>
                                <h4>No hay categorías registradas</h4>
                                <p class="text-muted">Comience agregando una nueva categoría de gasto</p>
                                <a href="crear.php" class="btn btn-primary mt-3">
                                    <i class="fas fa-plus-circle"></i> Crear Categoría
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>ID</th>
                                            <th>Nombre</th>
                                            <th>Descripción</th>
                                            <th>Estado</th>
                                            <th class="text-center">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($categorias as $cat): ?>
                                            <tr>
                                                <td><?= $cat['id_categoria_gasto'] ?></td>
                                                <td><?= htmlspecialchars($cat['nombre']) ?></td>
                                                <td><?= !empty($cat['descripcion']) ? htmlspecialchars($cat['descripcion']) : '<span class="text-muted">N/A</span>' ?></td>
                                                <td>
                                                    <span class="badge rounded-pill <?= $cat['estado'] ? 'badge-activo' : 'badge-inactivo' ?>">
                                                        <?= $cat['estado'] ? 'Activo' : 'Inactivo' ?>
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <a href="editar.php?id=<?= $cat['id_categoria_gasto'] ?>" class="btn btn-sm btn-primary" title="Editar">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form method="POST" action="eliminar.php" class="d-inline">
                                                        <input type="hidden" name="id" value="<?= $cat['id_categoria_gasto'] ?>">
                                                        <button type="submit" class="btn btn-sm btn-danger" 
                                                                onclick="return confirm('¿Eliminar categoría \'<?= addslashes($cat['nombre']) ?>\'?')"
                                                                title="Eliminar">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (!empty($categorias)): ?>
                        <div class="card-footer bg-white">
                            <small class="text-muted">
                                Mostrando <?= count($categorias) ?> categoría(s)
                                <?php if (isset($cat['fecha_actualizacion'])): ?>
                                    | Última actualización: <?= date('d/m/Y H:i', strtotime($cat['fecha_actualizacion'])) ?>
                                <?php endif; ?>
                            </small>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php include '../../template/scripts.php'; ?>
</body>
</html>