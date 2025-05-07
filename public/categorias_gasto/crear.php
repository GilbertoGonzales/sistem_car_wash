<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/constantes.php';

// Iniciar sesión para mensajes flash
session_start();

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
    } elseif (strlen($nombre) > 100) {
        $error = "El nombre no puede exceder los 100 caracteres";
    } else {
        try {
            // Verificar si ya existe
            $stmt = $conn->prepare("SELECT id_categoria_gasto FROM categoria_gasto WHERE nombre = ?");
            $stmt->execute([$nombre]);
            
            if ($stmt->rowCount() > 0) {
                $error = "Ya existe una categoría con ese nombre";
            } else {
                // Insertar nueva categoría
                $stmt = $conn->prepare("INSERT INTO categoria_gasto (nombre, descripcion, estado) VALUES (?, ?, ?)");
                $stmt->execute([$nombre, $descripcion, $estado]);
                
                $_SESSION['mensaje'] = "Categoría creada exitosamente";
                $_SESSION['tipo_mensaje'] = 'success';
                header("Location: index.php");
                exit;
            }
        } catch (PDOException $e) {
            $error = "Error al crear la categoría: " . $e->getMessage();
        }
    }
    
    // Mantener valores ingresados
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
    <title>Crear Categoría de Gasto</title>
    <style>
        .required-field::after {
            content: " *";
            color: #dc3545;
        }
        .form-check-label { cursor: pointer; }
    </style>
</head>
<body>
    <?php include '../../template/navbar.php'; ?>
    
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h4 class="mb-0"><i class="fas fa-plus-circle"></i> Nueva Categoría de Gasto</h4>
                        <a href="index.php" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Regresar
                        </a>
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
                            
                            <div class="mb-4 form-check form-switch">
                                <input type="checkbox" class="form-check-input" id="estado" 
                                       name="estado" <?= $valores['estado'] ? 'checked' : '' ?>>
                                <label class="form-check-label" for="estado">Categoría activa</label>
                            </div>
                            
                            <div class="d-flex justify-content-between">
                                <a href="index.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-times"></i> Cancelar
                                </a>
                                <div>
                                    <button type="reset" class="btn btn-outline-warning me-2">
                                        <i class="fas fa-undo"></i> Limpiar
                                    </button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Guardar
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../../template/scripts.php'; ?>
    
    <script>
    // Validación del formulario
    document.querySelector('form').addEventListener('submit', function(e) {
        const nombre = document.getElementById('nombre').value.trim();
        if (nombre === '') {
            e.preventDefault();
            document.getElementById('nombre').classList.add('is-invalid');
            alert('Por favor ingrese el nombre de la categoría');
        }
    });
    
    // Remover clase de error al escribir
    document.getElementById('nombre').addEventListener('input', function() {
        this.classList.remove('is-invalid');
    });
    </script>
</body>
</html>