<?php
require_once __DIR__ . '/../../config/database.php'; 

$database = new Database();
$conn = $database->getConnection();

function formatearMoneda($cantidad) {
    return "S/ " . number_format($cantidad, 2, ',', '.');
}

try {
    // Consulta para obtener los ingresos por mes
    $query = "
        SELECT 
            DATE_FORMAT(d.fecha_creacion, '%Y-%m') AS mes,
            SUM(d.precio) AS ingresos_totales
        FROM 
            detalle_servicio d
        WHERE 
            d.estado = 'activo'
        GROUP BY 
            mes
        ORDER BY 
            mes DESC
    ";
    
    // Preparar y ejecutar la consulta
    $stmt = $conn->prepare($query);
    $stmt->execute();
    
    // Obtener los resultados
    $ingresos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Verificar si la consulta devuelve datos
    if (empty($ingresos)) {
        echo "No se encontraron ingresos.";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <?php include '../../template/head.php'; ?>
    <title>Reporte de Ingresos - Car Wash</title>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
</head>
<body>
    <?php include '../../template/navbar.php'; ?>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Reporte de Ingresos Car Wash</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="ingresosCarwashTable" class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Mes</th>
                                        <th>Ingresos Totales</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($ingresos)): ?>
                                        <?php foreach ($ingresos as $ingreso): ?>
                                            <tr>
                                                <td><?php echo $ingreso['mes']; ?></td>
                                                <td><?php echo formatearMoneda($ingreso['ingresos_totales']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="2">No se encontraron registros de ingresos.</td>
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
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#ingresosCarwashTable').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json'
                },
                order: [[0, 'desc']]
            });
        });
    </script>
</body>
</html>
