<?php
require_once __DIR__ . '/../../config/database.php'; 
$database = new Database();
$conn = $database->getConnection();

require_once __DIR__ . '/../../config/constantes.php';

// Definición de la función si no existe
if (!function_exists('formatearMoneda')) {
    function formatearMoneda($monto) {
        return 'S/ ' . number_format($monto, 2, '.', ',');
    }
}

try {
    $query = "SELECT * FROM v_balance_mensual ORDER BY mes DESC";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $balances = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <?php include '../../template/head.php'; ?>
    <title>Balance General</title>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
</head>
<body>
    <?php include '../../template/navbar.php'; ?>
    
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Balance General Mensual</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="balanceTable" class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Mes</th>
                                        <th>Ingresos Carwash</th>
                                        <th>Ingresos Tienda</th>
                                        <th>Ingresos Totales</th>
                                        <th>Egresos Totales</th>
                                        <th>Balance</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($balances as $balance): ?>
                                        <tr>
                                            <td><?php echo $balance['mes']; ?></td>
                                            <td><?php echo formatearMoneda($balance['ingresos_carwash']); ?></td>
                                            <td><?php echo formatearMoneda($balance['ingresos_tienda']); ?></td>
                                            <td><?php echo formatearMoneda($balance['ingresos_totales']); ?></td>
                                            <td><?php echo formatearMoneda($balance['egresos_totales']); ?></td>
                                            <td><?php echo formatearMoneda($balance['balance']); ?></td>
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
            $('#balanceTable').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json'
                },
                order: [[0, 'desc']]
            });
        });
    </script>
</body>
</html>