<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/constantes.php';

session_start();

$database = new Database();
$conn = $database->getConnection();

$idGasto = $_GET['id'] ?? null;
$gasto = null;
$categorias = [];
$error = '';

if (!$idGasto) {
    header("Location: index.php");
    exit();
}

try {
    // Obtener categorías
    $query = "SELECT * FROM categoria_gasto WHERE estado = 1 ORDER BY nombre";
    $stmt = $conn->query($query);
    $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Obtener gasto a editar
    $query = "SELECT * FROM gasto WHERE id_gasto = :id";
    $stmt = $conn->prepare($query);
    $stmt->execute([':id' => $idGasto]);
    $gasto = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$gasto) {
        $_SESSION['error'] = "Gasto no encontrado";
        header("Location: index.php");
        exit();
    }
} catch (PDOException $e) {
    $error = "Error al cargar datos: " . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idCategoria = $_POST['id_categoria_gasto'];
    $fecha = $_POST['fecha_gasto'];
    $descripcion = $_POST['descripcion'];
    $monto = $_POST['monto'];
    
    try {
        $conn->beginTransaction();
        
        $query = "UPDATE gasto SET 
                  id_categoria_gasto = :id_categoria,
                  fecha_gasto = :fecha,
                  descripcion = :descripcion,
                  monto = :monto
                  WHERE id_gasto = :id";
                  
        $stmt = $conn->prepare($query);
        $stmt->execute([
            ':id_categoria' => $idCategoria,
            ':fecha' => $fecha,
            ':descripcion' => $descripcion,
            ':monto' => $monto,
            ':id' => $idGasto
        ]);
        
        $conn->commit();
        $_SESSION['mensaje'] = "Gasto actualizado correctamente";
        header("Location: index.php");
        exit();
    } catch (Exception $e) {
        $conn->rollBack();
        $error = "Error al actualizar el gasto: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <?php include '../../template/head.php'; ?>
    <title>Editar Gasto - Carwash</title>
</head>
<body>
    <?php include '../../template/navbar.php'; ?>

    <div class="container py-4">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0"><i class="fas fa-money-bill-wave mr-2"></i>Editar Gasto</h4>
            </div>
            
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                
                <?php if ($gasto): ?>
                    <form method="POST">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="fecha_gasto">Fecha</label>
                                    <input type="date" class="form-control" id="fecha_gasto" name="fecha_gasto" 
                                           value="<?= htmlspecialchars($gasto['fecha_gasto']) ?>" required>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="id_categoria_gasto">Categoría</label>
                                    <select class="form-control" id="id_categoria_gasto" name="id_categoria_gasto" required>
                                        <option value="">Seleccione categoría</option>
                                        <?php foreach ($categorias as $categoria): ?>
                                            <option value="<?= $categoria['id_categoria_gasto'] ?>" 
                                                <?= $categoria['id_categoria_gasto'] == $gasto['id_categoria_gasto'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($categoria['nombre']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="descripcion">Descripción</label>
                            <textarea class="form-control" id="descripcion" name="descripcion" rows="3" required><?= 
                                htmlspecialchars($gasto['descripcion']) ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="monto">Monto (S/)</label>
                            <input type="number" class="form-control" id="monto" name="monto" 
                                   step="0.01" min="0" value="<?= htmlspecialchars($gasto['monto']) ?>" required>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="index.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left mr-2"></i>Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-2"></i>Guardar Cambios
                            </button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include '../../template/scripts.php'; ?>
</body>
</html>