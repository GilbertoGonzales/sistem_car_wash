<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/constantes.php';

$database = new Database();
$conn = $database->getConnection();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);
    $estado = isset($_POST['estado']) ? 1 : 0;

    if (empty($nombre)) {
        $error = "El nombre es obligatorio";
    } else {
        try {
            $query = "INSERT INTO tipo_vehiculo (nombre, descripcion, estado, fecha_creacion) 
                     VALUES (?, ?, ?, NOW())";
            $stmt = $conn->prepare($query);
            $stmt->execute([$nombre, $descripcion, $estado]);

            header("Location: index.php?exito=creado");
            exit();
        } catch (PDOException $e) {
            $error = "Error al crear: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <?php include '../../template/head.php'; ?>
    <title>Crear Tipo de Vehículo</title>
</head>
<body>
    <?php include '../../template/navbar.php'; ?>
    
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-6 mx-auto">
                <div class="card">
                    <div class="card-header">
                        <h4>Nuevo Tipo de Vehículo</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Nombre*</label>
                                <input type="text" name="nombre" class="form-control" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Descripción</label>
                                <textarea name="descripcion" class="form-control" rows="3"></textarea>
                            </div>
                            
                            <div class="mb-3 form-check">
                                <input type="checkbox" name="estado" class="form-check-input" checked>
                                <label class="form-check-label">Activo</label>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Guardar
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