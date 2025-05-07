<?php
require_once __DIR__ . '/../../config/database.php'; 

$database = new Database();
$conn = $database->getConnection();

function formatearMoneda($cantidad) {
    return "S/ " . number_format($cantidad, 2, ',', '.');
}

try {
    // Consulta para obtener los egresos por producto de la tienda
    $query = "
        SELECT 
            p.nombre AS producto,
            SUM(dp.cantidad) AS cantidad_vendida,
            SUM(dp.cantidad * p.precio_compra) AS egresos_totales
        FROM 
            detalle_producto dp
        JOIN 
            producto p ON dp.id_producto = p.id_producto
        WHERE 
            dp.estado = 'activo'
        GROUP BY 
            p.nombre
        ORDER BY 
            egresos_totales DESC
    ";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $egresos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <?php include '../../template/head.php'; ?>
    <title>Egros Tienda</title>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
</head>
<body>
    <?php include '../../template/navbar.php'; ?>
    
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Egros de la Tienda</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="egresosTiendaTable" class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Producto</th>
                                        <th>Cantidad Vendida</th>
                                        <th>Egros Totales</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($egresos as $egreso): ?>
                                        <tr>
                                            <td><?php echo $egreso['producto']; ?></td>
                                            <td><?php echo $egreso['cantidad_vendida']; ?></td>
                                            <td><?php echo formatearMoneda($egreso['egresos_totales']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
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
            $('#egresosTiendaTable').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json'
                }
            });
        });
    </script>
</body>
</html>
