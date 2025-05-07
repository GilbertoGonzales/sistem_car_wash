<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/constantes.php';

$database = new Database();
$conn = $database->getConnection();

$mensaje = '';
$nivel = null;

// Verificar si se recibió un ID válido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php?error=id');
    exit;
}

try {
    // Obtener el nivel de servicio a editar
    $query = "SELECT * FROM nivel_servicio WHERE id_nivel_servicio = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$_GET['id']]);
    $nivel = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$nivel) {
        header('Location: index.php?error=id');
        exit;
    }

    // Procesar el formulario de actualización
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nombre = $_POST['nombre'] ?? '';
        $descripcion = $_POST['descripcion'] ?? '';
        $estado = isset($_POST['estado']) ? 1 : 0;

        $updateQuery = "UPDATE nivel_servicio SET 
                        nombre = ?, 
                        descripcion = ?, 
                        estado = ? 
                        WHERE id_nivel_servicio = ?";
        
        $stmt = $conn->prepare($updateQuery);
        if ($stmt->execute([$nombre, $descripcion, $estado, $_GET['id']])) {
            header('Location: index.php?exito=editado');
            exit;
        } else {
            $mensaje = '<div class="alert alert-danger">Error al actualizar el nivel de servicio</div>';
        }
    }
} catch (PDOException $e) {
    $mensaje = '<div class="alert alert-danger">Error en la base de datos: ' . $e->getMessage() . '</div>';
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <?php include '../../template/head.php'; ?>
    <title>Editar Nivel de Servicio</title>
</head>
<body>
    <?php include '../../template/navbar.php'; ?>
    
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card">
                    <div class="card-header">
                        <h4>Editar Nivel de Servicio</h4>
                    </div>
                    <div class="card-body">
                        <?php echo $mensaje; ?>
                        <form method="POST">
                            <div class="mb-3">
                                <label for="nombre" class="form-label">Nombre</label>
                                <input type="text" class="form-control" id="nombre" name="nombre" 
                                       value="<?= htmlspecialchars($nivel['nombre'] ?? '') ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="descripcion" class="form-label">Descripción</label>
                                <textarea class="form-control" id="descripcion" name="descripcion" 
                                          rows="3"><?= htmlspecialchars($nivel['descripcion'] ?? '') ?></textarea>
                            </div>
                            
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="estado" name="estado" 
                                       <?= ($nivel['estado'] ?? 0) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="estado">Activo</label>
                            </div>
                            
                            <div class="d-flex justify-content-between">
                                <a href="index.php" class="btn btn-secondary">Cancelar</a>
                                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../../template/scripts.php'; ?>
</body>
</html>