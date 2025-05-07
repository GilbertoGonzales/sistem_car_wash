<?php

require_once __DIR__ . '/../../config/database.php'; 

$database = new Database();
$conn = $database->getConnection();

require_once __DIR__ . '/../../config/constantes.php';

try {
  // Consulta para obtener los productos
  $query = "SELECT * FROM producto ORDER BY nombre";  // Cambié 'productos' a 'producto'
  $stmt = $conn->prepare($query);
  $stmt->execute();
  $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);  // Asegúrate de que esta variable sea 'productos'
} catch (PDOException $e) {
  echo "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <?php include '../../template/head.php'; ?>
    <title>Lista de Productos</title>
</head>
<body>
    <?php include '../../template/navbar.php'; ?>
    
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4>Lista de Productos</h4>
                            <a href="crear.php" class="btn btn-success">Nuevo Producto</a>
                        </div>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Descripción</th>
                                    <th>Precio</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(count($productos) > 0): ?>  <!-- Cambié 'producto' por 'productos' -->
                                    <?php foreach($productos as $producto): ?>  <!-- Cambié 'producto' por 'productos' -->
                                        <tr>
                                            <td><?php echo $producto['id_producto']; ?></td>
                                            <td><?php echo $producto['nombre']; ?></td>
                                            <td><?php echo $producto['descripcion']; ?></td>
                                            <td><?php echo $producto['precio_venta']; ?></td>  <!-- Cambié 'precio' por 'precio_venta' -->
                                            <td><?php echo $producto['estado'] ? 'Disponible' : 'No Disponible'; ?></td>
                                            <td>
                                                <!-- Corregí el parámetro del enlace, usando 'id_producto' en lugar de 'id' -->
                                                <a href="editar.php?id_producto=<?php echo $producto['id_producto']; ?>" class="btn btn-primary btn-sm">Editar</a>
                                                <a href="eliminar.php?id=<?php echo $producto['id_producto']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de que deseas eliminar este producto?')">Eliminar</a>


                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center">No hay productos registrados</td>
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
