<?php
require_once '../../config/database.php';
$database = new Database();
$conn = $database->getConnection();
require_once '../helpers/funciones.php';

try {
    // Obtener productos con su stock actual
    $query = "SELECT p.id_producto, p.nombre, p.stock, p.stock_minimo, cp.nombre as categoria
              FROM producto p
              JOIN categoria_producto cp ON p.id_categoria_producto = cp.id_categoria_producto
              WHERE p.estado = 1
              ORDER BY p.stock ASC";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn->beginTransaction();
        
        foreach ($_POST['productos'] as $id => $cantidad) {
            $query = "UPDATE producto SET stock = :cantidad WHERE id_producto = :id";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':cantidad', $cantidad);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
        }
        
        $conn->commit();
        $_SESSION['mensaje'] = "Stock actualizado correctamente";
        header("Location: stock.php");
        exit();
    } catch (PDOException $e) {
        $conn->rollBack();
        $_SESSION['error'] = "Error al actualizar el stock: " . $e->getMessage();
        header("Location: stock.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <?php include '../../template/head.php'; ?>
    <title>Control de Stock</title>
</head>
<body>
    <?php include '../../template/navbar.php'; ?>
    
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Control de Stock</h4>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Producto</th>
                                        <th>Categoría</th>
                                        <th>Stock Actual</th>
                                        <th>Stock Mínimo</th>
                                        <th>Nuevo Stock</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($productos as $producto): ?>
                                        <tr class="<?php echo $producto['stock'] <= $producto['stock_minimo'] ? 'table-warning' : ''; ?>">
                                            <td><?php echo $producto['nombre']; ?></td>
                                            <td><?php echo $producto['categoria']; ?></td>
                                            <td><?php echo $producto['stock']; ?></td>
                                            <td><?php echo $producto['stock_minimo']; ?></td>
                                            <td>
                                                <input type="number" name="productos[<?php echo $producto['id_producto']; ?>]" 
                                                       value="<?php echo $producto['stock']; ?>" min="0" class="form-control">
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            <button type="submit" class="btn btn-primary">Actualizar Stock</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../../template/scripts.php'; ?>
</body>
</html>