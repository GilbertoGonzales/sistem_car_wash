<?php

require_once __DIR__ . '/../../config/database.php';

$database = new Database();
$conn = $database->getConnection();

// Función para generar código único
function generarCodigoUnico($conn) {
    $prefix = 'PROD-';
    $query = "SELECT MAX(CAST(SUBSTRING(codigo, 6) AS UNSIGNED)) as max_code FROM producto WHERE codigo LIKE '{$prefix}%'";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $next_num = ($result['max_code']) ? $result['max_code'] + 1 : 1;
    return $prefix . str_pad($next_num, 6, '0', STR_PAD_LEFT);
}

// Obtener categorías para el select
$categorias = [];
try {
    $query = "SELECT id_categoria_producto, nombre FROM categoria_producto WHERE estado = 1 ORDER BY nombre";
    $stmt = $conn->query($query);
    $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error al obtener categorías: " . $e->getMessage();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validar y sanitizar datos
    $id_categoria_producto = filter_input(INPUT_POST, 'id_categoria_producto', FILTER_VALIDATE_INT);
    $codigo = generarCodigoUnico($conn);
    $nombre = htmlspecialchars(strip_tags($_POST['nombre']));
    $descripcion = htmlspecialchars(strip_tags($_POST['descripcion']));
    $precio_compra = filter_input(INPUT_POST, 'precio_compra', FILTER_VALIDATE_FLOAT);
    $precio_venta = filter_input(INPUT_POST, 'precio_venta', FILTER_VALIDATE_FLOAT);
    $stock = filter_input(INPUT_POST, 'stock', FILTER_VALIDATE_INT);
    $stock_minimo = filter_input(INPUT_POST, 'stock_minimo', FILTER_VALIDATE_INT);
    $estado = filter_input(INPUT_POST, 'estado', FILTER_VALIDATE_INT);

    // Validar que precios sean correctos
    if ($precio_venta <= $precio_compra) {
        echo "<script>alert('El precio de venta debe ser mayor al precio de compra');</script>";
    } else {
        try {
            $query = "INSERT INTO producto 
                        (id_categoria_producto, codigo, nombre, descripcion, precio_compra, precio_venta, stock, stock_minimo, estado) 
                      VALUES 
                        (:id_categoria_producto, :codigo, :nombre, :descripcion, :precio_compra, :precio_venta, :stock, :stock_minimo, :estado)";
            $stmt = $conn->prepare($query);

            // Vinculamos los parámetros
            $stmt->bindParam(':id_categoria_producto', $id_categoria_producto, PDO::PARAM_INT);
            $stmt->bindParam(':codigo', $codigo);
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':descripcion', $descripcion);
            $stmt->bindParam(':precio_compra', $precio_compra);
            $stmt->bindParam(':precio_venta', $precio_venta);
            $stmt->bindParam(':stock', $stock, PDO::PARAM_INT);
            $stmt->bindParam(':stock_minimo', $stock_minimo, PDO::PARAM_INT);
            $stmt->bindParam(':estado', $estado, PDO::PARAM_INT);

            if ($stmt->execute()) {
                echo "<script>alert('Producto creado con éxito'); window.location.href = 'productos.php';</script>";
                exit();
            } else {
                echo "<script>alert('Error al crear el producto');</script>";
            }
        } catch (PDOException $e) {
            echo "<script>alert('Error: " . addslashes($e->getMessage()) . "');</script>";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <?php include '../../template/head.php'; ?>
    <title>Nuevo Producto</title>
    <style>
        .form-group.required label:after {
            content: " *";
            color: red;
        }
    </style>
</head>
<body>
    <?php include '../../template/navbar.php'; ?>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4><i class="fas fa-plus-circle"></i> Crear Nuevo Producto</h4>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="crear.php" onsubmit="return validarPrecios()">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group required">
                                        <label for="id_categoria_producto">Categoría</label>
                                        <select name="id_categoria_producto" class="form-control" required>
                                            <option value="">Seleccione una categoría</option>
                                            <?php foreach ($categorias as $categoria): ?>
                                                <option value="<?= $categoria['id_categoria_producto'] ?>">
                                                    <?= htmlspecialchars($categoria['nombre']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    
                                    <div class="form-group required">
                                        <label for="nombre">Nombre del Producto</label>
                                        <input type="text" name="nombre" class="form-control" required maxlength="100">
                                    </div>
                                    
                                    <div class="form-group required">
                                        <label for="precio_compra">Precio de Compra ($)</label>
                                        <input type="number" step="0.01" min="0" name="precio_compra" class="form-control" id="precio_compra" required>
                                    </div>
                                    
                                    <div class="form-group required">
                                        <label for="precio_venta">Precio de Venta ($)</label>
                                        <input type="number" step="0.01" min="0" name="precio_venta" class="form-control" id="precio_venta" required>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="codigo">Código</label>
                                        <input type="text" class="form-control" value="<?= generarCodigoUnico($conn) ?>" readonly>
                                        <small class="text-muted">Código generado automáticamente</small>
                                    </div>
                                    
                                    <div class="form-group required">
                                        <label for="stock">Stock Inicial</label>
                                        <input type="number" name="stock" class="form-control" min="0" required>
                                    </div>
                                    
                                    <div class="form-group required">
                                        <label for="stock_minimo">Stock Mínimo</label>
                                        <input type="number" name="stock_minimo" class="form-control" min="0" required>
                                    </div>
                                    
                                    <div class="form-group required">
                                        <label for="estado">Estado</label>
                                        <select name="estado" class="form-control" required>
                                            <option value="1">Disponible</option>
                                            <option value="0">No Disponible</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group required">
                                <label for="descripcion">Descripción</label>
                                <textarea name="descripcion" class="form-control" rows="3" required maxlength="255"></textarea>
                            </div>
                            
                            <div class="form-group text-center mt-4">
                                <button type="submit" class="btn btn-success btn-lg mr-2">
                                    <i class="fas fa-save"></i> Guardar Producto
                                </button>
                                <a href="index.php" class="btn btn-secondary btn-lg">
                                    <i class="fas fa-times"></i> Cancelar
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../../template/scripts.php'; ?>
    <script>
        function validarPrecios() {
            const precioCompra = parseFloat(document.getElementById('precio_compra').value);
            const precioVenta = parseFloat(document.getElementById('precio_venta').value);
            
            if (precioVenta <= precioCompra) {
                alert('El precio de venta debe ser mayor al precio de compra');
                return false;
            }
            return true;
        }
    </script>
</body>
</html>