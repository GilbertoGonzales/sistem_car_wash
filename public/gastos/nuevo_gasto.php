<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/constantes.php';

session_start();

$database = new Database();
$conn = $database->getConnection();

$categorias = [];
$error = '';

try {
    $query = "SELECT * FROM categoria_gasto WHERE estado = 1 ORDER BY nombre";
    $stmt = $conn->query($query);
    $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error al cargar categorías: " . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idCategoria = $_POST['id_categoria_gasto'];
    $fecha = $_POST['fecha_gasto'];
    $descripcion = $_POST['descripcion'];
    $monto = $_POST['monto'];
    
    try {
        $conn->beginTransaction();
        
        $query = "INSERT INTO gasto (id_categoria_gasto, fecha_gasto, descripcion, monto) 
                  VALUES (:id_categoria, :fecha, :descripcion, :monto)";
        $stmt = $conn->prepare($query);
        $stmt->execute([
            ':id_categoria' => $idCategoria,
            ':fecha' => $fecha,
            ':descripcion' => $descripcion,
            ':monto' => $monto
        ]);
        
        $conn->commit();
        $_SESSION['mensaje'] = "Gasto registrado correctamente";
        header("Location: index.php");
        exit();
    } catch (Exception $e) {
        $conn->rollBack();
        $error = "Error al registrar el gasto: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <?php include '../../template/head.php'; ?>
    <title>Nuevo Gasto - Carwash</title>
</head>
<body>
    <?php include '../../template/navbar.php'; ?>

    <div class="container py-4">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0"><i class="fas fa-money-bill-wave mr-2"></i>Registrar Nuevo Gasto</h4>
            </div>
            
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="fecha_gasto">Fecha</label>
                                <input type="date" class="form-control" id="fecha_gasto" name="fecha_gasto" 
                                       value="<?= date('Y-m-d') ?>" required>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="id_categoria_gasto">Categoría</label>
                                <select class="form-control" id="id_categoria_gasto" name="id_categoria_gasto" required>
                                    <option value="">Seleccione categoría</option>
                                    <?php foreach ($categorias as $categoria): ?>
                                        <option value="<?= $categoria['id_categoria_gasto'] ?>">
                                            <?= htmlspecialchars($categoria['nombre']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="descripcion">Descripción</label>
                        <textarea class="form-control" id="descripcion" name="descripcion" rows="3" required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="monto">Monto (S/)</label>
                        <input type="number" class="form-control" id="monto" name="monto" 
                               step="0.01" min="0" required>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="index.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left mr-2"></i>Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-2"></i>Guardar Gasto
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include '../../template/scripts.php'; ?>
</body>
</html>