<?php
// Verificar si el parámetro 'id_producto' está presente en la URL
if (isset($_GET['id_producto'])) {
    $id_producto = $_GET['id_producto'];

    // Realizar la consulta para obtener los datos del producto
    require_once __DIR__ . '/../../config/database.php';
    $database = new Database();
    $conn = $database->getConnection();

    $query = "SELECT * FROM producto WHERE id_producto = :id_producto";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id_producto', $id_producto, PDO::PARAM_INT);
    $stmt->execute();

    $producto = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$producto) {
        echo "Producto no encontrado.";
        exit();
    }
} else {
    echo "ID del producto no proporcionado.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <!-- Incluye los archivos CSS de Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Editar Producto</title>
</head>
<body>
    <div class="container mt-5">
        <h2>Editar Producto</h2>

        <!-- Formulario de edición -->
        <form method="POST" action="editar.php?id_producto=<?= $id_producto; ?>" class="mt-4">
            <div class="mb-3">
                <label for="nombre" class="form-label">Nombre del Producto</label>
                <input type="text" name="nombre" id="nombre" class="form-control" value="<?= $producto['nombre']; ?>" required>
            </div>

            <div class="mb-3">
                <label for="precio" class="form-label">Precio de Venta</label>
                <input type="number" name="precio" id="precio" class="form-control" value="<?= $producto['precio_venta']; ?>" required>
            </div>

            <!-- Otros campos del producto (puedes agregar más campos si lo necesitas) -->
            <div class="mb-3">
                <label for="descripcion" class="form-label">Descripción</label>
                <textarea name="descripcion" id="descripcion" class="form-control" rows="3"><?= $producto['descripcion']; ?></textarea>
            </div>

            <!-- Botón de actualización -->
            <button type="submit" class="btn btn-primary">Actualizar Producto</button>
            <a href="productos.php" class="btn btn-secondary">Volver a la lista</a>
        </form>
    </div>

    <!-- Incluye los archivos JS de Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
