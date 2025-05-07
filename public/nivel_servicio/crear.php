<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/constantes.php';

$database = new Database();
$conn = $database->getConnection();

$mensaje = '';

// Procesar el formulario de creación
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'] ?? '';
    $descripcion = $_POST['descripcion'] ?? '';
    $estado = isset($_POST['estado']) ? 1 : 0;

    try {
        $insertQuery = "INSERT INTO nivel_servicio (nombre, descripcion, estado, fecha_creacion) 
                        VALUES (?, ?, ?, NOW())";
        
        $stmt = $conn->prepare($insertQuery);
        
        if ($stmt->execute([$nombre, $descripcion, $estado])) {
            header('Location: index.php?exito=creado');
            exit;
        } else {
            $mensaje = '<div class="alert alert-danger">Error al crear el nivel de servicio</div>';
        }
    } catch (PDOException $e) {
        $mensaje = '<div class="alert alert-danger">Error en la base de datos: ' . $e->getMessage() . '</div>';
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <?php include '../../template/head.php'; ?>
    <title>Crear Nuevo Nivel de Servicio</title>
</head>
<body>
    <?php include '../../template/navbar.php'; ?>
    
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card">
                    <div class="card-header">
                        <h4>Crear Nuevo Nivel de Servicio</h4>
                    </div>
                    <div class="card-body">
                        <?php echo $mensaje; ?>
                        <form method="POST">
                            <div class="mb-3">
                                <label for="nombre" class="form-label">Nombre <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nombre" name="nombre" required>
                                <div class="form-text">Ejemplo: Lavado Premium, Lavado Básico</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="descripcion" class="form-label">Descripción</label>
                                <textarea class="form-control" id="descripcion" name="descripcion" rows="3"></textarea>
                            </div>
                            
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="estado" name="estado" checked>
                                <label class="form-check-label" for="estado">Activo</label>
                            </div>
                            
                            <div class="d-flex justify-content-between">
                                <a href="index.php" class="btn btn-secondary">Cancelar</a>
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-save me-1"></i> Guardar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../../template/scripts.php'; ?>
    
    <script>
        // Validación básica del formulario
        document.querySelector('form').addEventListener('submit', function(e) {
            const nombre = document.getElementById('nombre').value.trim();
            
            if (!nombre) {
                e.preventDefault();
                alert('El campo Nombre es obligatorio');
                document.getElementById('nombre').focus();
            }
        });
    </script>
</body>
</html>