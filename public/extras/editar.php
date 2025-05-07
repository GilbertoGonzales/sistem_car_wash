<?php
require_once __DIR__ . '/../../config/database.php';

$database = new Database();
$conn = $database->getConnection();

// Inicializar variables
$servicio = [];
$error = '';

// Verificar si se recibió ID
if (!isset($_GET['id_servicio_extra'])) {
    header("Location: index.php");
    exit();
}

$id_servicio_extra = $_GET['id_servicio_extra'];

try {
    // Obtener datos del servicio extra
    $query = "SELECT * FROM servicio_extra WHERE id_servicio_extra = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$id_servicio_extra]);
    $servicio = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$servicio) {
        header("Location: index.php");
        exit();
    }

    // Procesar formulario de actualización
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nombre = $_POST['nombre'];
        $descripcion = $_POST['descripcion'];
        $precio = $_POST['precio'];
        $estado = isset($_POST['estado']) ? 1 : 0;

        // Validaciones básicas
        if (empty($nombre) || empty($precio)) {
            $error = "Nombre y precio son campos obligatorios";
        } else {
            $query = "UPDATE servicio_extra SET 
                      nombre = ?, 
                      descripcion = ?, 
                      precio = ?, 
                      estado = ? 
                      WHERE id_servicio_extra = ?";
            
            $stmt = $conn->prepare($query);
            $stmt->execute([$nombre, $descripcion, $precio, $estado, $id_servicio_extra]);

            header("Location: index.php?success=1");
            exit();
        }
    }
} catch (PDOException $e) {
    $error = "Error en la base de datos: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <?php include '../../template/head.php'; ?>
    <title>Editar Servicio Extra</title>
</head>
<body>
    <?php include '../../template/navbar.php'; ?>
    
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Editar Servicio Extra</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="form-group">
                                <label>Nombre</label>
                                <input type="text" name="nombre" class="form-control" 
                                       value="<?php echo htmlspecialchars($servicio['nombre'] ?? ''); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label>Descripción</label>
                                <textarea name="descripcion" class="form-control"><?php 
                                    echo htmlspecialchars($servicio['descripcion'] ?? ''); 
                                ?></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label>Precio</label>
                                <input type="number" step="0.01" name="precio" class="form-control" 
                                       value="<?php echo htmlspecialchars($servicio['precio'] ?? ''); ?>" required>
                            </div>
                            
                            <div class="form-check mb-3">
                                <input type="checkbox" name="estado" class="form-check-input" id="estado" 
                                    <?php echo ($servicio['estado'] ?? 0) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="estado">Activo</label>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                            <a href="index.php" class="btn btn-secondary">Cancelar</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../../template/scripts.php'; ?>
</body>
</html>