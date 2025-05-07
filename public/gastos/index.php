<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/constantes.php';

session_start();

$database = new Database();
$conn = $database->getConnection();

$gastos = [];
$mensaje = $_SESSION['mensaje'] ?? null;
$error = $_SESSION['error'] ?? null;

unset($_SESSION['mensaje'], $_SESSION['error']);

try {
    $query = "SELECT g.*, c.nombre as categoria 
              FROM gasto g
              JOIN categoria_gasto c ON g.id_categoria_gasto = c.id_categoria_gasto
              ORDER BY g.fecha_gasto DESC";
              
    $stmt = $conn->query($query);
    $gastos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error al cargar gastos: " . $e->getMessage();
    error_log($error);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <?php include '../../template/head.php'; ?>
    <title>Gastos - Carwash</title>
</head>
<body>
    <?php include '../../template/navbar.php'; ?>

    <div class="container py-4">
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
                    <h4 class="mb-0"><i class="fas fa-money-bill-wave mr-2"></i>Registro de Gastos</h4>
                    <a href="nuevo_gasto.php" class="btn btn-light">
                        <i class="fas fa-plus mr-2"></i>Nuevo Gasto
                    </a>
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
                                <th>Acciones</th>
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
                                                   onclick="return confirm('¿Confirmas eliminar este gasto?\n\nFecha: <?= date('d/m/Y', strtotime($gasto['fecha_gasto'])) ?>\nMonto: S/ <?= number_format($gasto['monto'], 2) ?>');">
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
                                        No hay gastos registrados
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