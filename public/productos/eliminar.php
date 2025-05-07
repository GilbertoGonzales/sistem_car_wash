<?php
// Verificar si el parámetro 'id' está presente en la URL
if (isset($_GET['id'])) {
    $id_producto = $_GET['id'];

    // Conectar a la base de datos
    require_once __DIR__ . '/../../config/database.php';
    $database = new Database();
    $conn = $database->getConnection();

    try {
        // Preparar la consulta para eliminar el producto
        $query = "DELETE FROM producto WHERE id_producto = :id_producto";
        $stmt = $conn->prepare($query);

        // Vincular el parámetro
        $stmt->bindParam(':id_producto', $id_producto, PDO::PARAM_INT);

        // Ejecutar la consulta
        if ($stmt->execute()) {
            // Redirigir a la página de productos después de eliminar
            header("Location: productos.php?mensaje=Producto eliminado con éxito");
            exit();
        } else {
            echo "Error al eliminar el producto.";
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
} else {
    echo "ID del producto no proporcionado.";
    exit();
}
?>
