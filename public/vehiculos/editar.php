<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/constantes.php';

$database = new Database();
$conn = $database->getConnection();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php?error=id");
    exit();
}

$id = $_GET['id'];
$error = '';

try {
    // Obtener datos actuales
    $stmt = $conn->prepare("SELECT * FROM tipo_vehiculo WHERE id_tipo_vehiculo = ?");
    $stmt->execute([$id]);
    $vehiculo = $stmt->fetch();

    if (!$vehiculo) {
        header("Location: index.php?error=id");
        exit();
    }

    // Procesar actualización
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);
    $estado = isset($_POST['estado']) ? 1 : 0;

    if (empty($nombre)) {
        $error = "El nombre es obligatorio";
    } else {
        $query = "UPDATE tipo_vehiculo SET 
                 nombre = ?, 
                 descripcion = ?, 
                 estado = ?
                 WHERE id_tipo_vehiculo = ?";
        
        $stmt = $conn->prepare($query);
        $stmt->execute([$nombre, $descripcion, $estado, $id]);

        header("Location: index.php?exito=editado");
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
    <title>Editar Tipo de Vehículo</title>
</head>
<body>
    <?php include '../../template/navbar.php'; ?>
    
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-6 mx-auto">
                <div class="card">
                    <div class="card-header">
                        <h4>Editar Tipo de Vehículo</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Nombre*</label>
                                <input type="text" name="nombre" class="form-control" 
                                       value="<?= htmlspecialchars($vehiculo['nombre']) ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Descripción</label>
                                <textarea name="descripcion" class="form-control" rows="3"><?= 
                                    htmlspecialchars($vehiculo['descripcion']) 
                                ?></textarea>
                            </div>
                            
                            <div class="mb-3 form-check">
                                <input type="checkbox" name="estado" class="form-check-input" 
                                    <?= $vehiculo['estado'] ? 'checked' : '' ?>>
                                <label class="form-check-label">Activo</label>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Guardar Cambios
                            </button>
                            <a href="index.php" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../../template/scripts.php'; ?>
</body>
</html>