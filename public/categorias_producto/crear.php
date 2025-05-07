<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/constantes.php';

$database = new Database();
$conn = $database->getConnection();

$error = '';
$valores = [
    'nombre' => '',
    'descripcion' => '',
    'estado' => 1
];

// Procesar el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $estado = isset($_POST['estado']) ? 1 : 0;

    // Validaciones
    if (empty($nombre)) {
        $error = "El nombre de la categoría es obligatorio";
    } else {
        try {
            $query = "INSERT INTO categoria_producto (nombre, descripcion, estado) 
                     VALUES (:nombre, :descripcion, :estado)";
            
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':descripcion', $descripcion);
            $stmt->bindParam(':estado', $estado, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                header("Location: index.php?exito=creado");
                exit();
            } else {
                $error = "Error al crear la categoría";
            }
        } catch (PDOException $e) {
            $error = "Error en la base de datos: " . $e->getMessage();
        }
    }

    // Mantener valores ingresados si hay error
    $valores = [
        'nombre' => $nombre,
        'descripcion' => $descripcion,
        'estado' => $estado
    ];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <?php include '../../template/head.php'; ?>
    <title>Crear Nueva Categoría</title>
    <style>
        .required-field::after {
            content: " *";
            color: #dc3545;
        }
    </style>
</head>
<body>
    <?php include '../../template/navbar.php'; ?>
    
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="mb-0"><i class="fas fa-plus-circle"></i> Nueva Categoría</h4>
                            <a href="index.php" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-arrow-left"></i> Regresar
                            </a>
                        </div>
                    </div>
                    
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show">
                                <?= $error ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" autocomplete="off">
                            <div class="mb-3">
                                <label for="nombre" class="form-label required-field">Nombre</label>
                                <input type="text" class="form-control" id="nombre" name="nombre" 
                                       value="<?= htmlspecialchars($valores['nombre']) ?>" 
                                       required maxlength="100">
                                <div class="form-text">Máximo 100 caracteres</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="descripcion" class="form-label">Descripción</label>
                                <textarea class="form-control" id="descripcion" name="descripcion" 
                                          rows="3" maxlength="255"><?= htmlspecialchars($valores['descripcion']) ?></textarea>
                                <div class="form-text">Opcional, máximo 255 caracteres</div>
                            </div>
                            
                            <div class="mb-3 form-check form-switch">
                                <input type="checkbox" class="form-check-input" id="estado" 
                                       name="estado" <?= $valores['estado'] ? 'checked' : '' ?>>
                                <label class="form-check-label" for="estado">Categoría activa</label>
                            </div>
                            
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <button type="reset" class="btn btn-outline-secondary me-md-2">
                                    <i class="fas fa-undo"></i> Limpiar
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Guardar Categoría
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
        // Validación del formulario antes de enviar
        document.querySelector('form').addEventListener('submit', function(e) {
            const nombreInput = document.getElementById('nombre');
            if (nombreInput.value.trim() === '') {
                e.preventDefault();
                nombreInput.classList.add('is-invalid');
                alert('El nombre de la categoría es obligatorio');
            }
        });
        
        // Remover clase de error al escribir
        document.getElementById('nombre').addEventListener('input', function() {
            this.classList.remove('is-invalid');
        });
    </script>
</body>
</html>