<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/constantes.php';

$database = new Database();
$conn = $database->getConnection();

$error = '';
$categoria = null;

// Verificar ID válido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php?error=id");
    exit();
}

$id = $_GET['id'];

// Obtener datos actuales de la categoría
try {
    $stmt = $conn->prepare("SELECT * FROM categoria_producto WHERE id_categoria_producto = ?");
    $stmt->execute([$id]);
    $categoria = $stmt->fetch();

    if (!$categoria) {
        header("Location: index.php?error=id");
        exit();
    }
} catch (PDOException $e) {
    $error = "Error al cargar la categoría: " . $e->getMessage();
}

// Procesar actualización
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $estado = isset($_POST['estado']) ? 1 : 0;

    // Validaciones
    if (empty($nombre)) {
        $error = "El nombre de la categoría es obligatorio";
    } else {
        try {
            // Query actualizada (sin fecha_actualizacion o con ella, según tu BD)
            $query = "UPDATE categoria_producto SET 
                     nombre = :nombre, 
                     descripcion = :descripcion, 
                     estado = :estado
                     WHERE id_categoria_producto = :id";
            
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':descripcion', $descripcion);
            $stmt->bindParam(':estado', $estado, PDO::PARAM_INT);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                header("Location: index.php?exito=editado");
                exit();
            } else {
                $error = "Error al actualizar la categoría";
            }
        } catch (PDOException $e) {
            $error = "Error en la base de datos: " . $e->getMessage();
        }
    }

    // Actualizar datos para mostrar en el formulario
    $categoria['nombre'] = $nombre;
    $categoria['descripcion'] = $descripcion;
    $categoria['estado'] = $estado;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <?php include '../../template/head.php'; ?>
    <title>Editar Categoría</title>
    <style>
        .required-field::after {
            content: " *";
            color: #dc3545;
        }
        .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid rgba(0,0,0,.125);
        }
    </style>
</head>
<body>
    <?php include '../../template/navbar.php'; ?>
    
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="mb-0"><i class="fas fa-edit"></i> Editar Categoría</h4>
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
                            <input type="hidden" name="id" value="<?= $id ?>">
                            
                            <div class="mb-3">
                                <label for="nombre" class="form-label required-field">Nombre</label>
                                <input type="text" class="form-control" id="nombre" name="nombre" 
                                       value="<?= htmlspecialchars($categoria['nombre']) ?>" 
                                       required maxlength="100">
                                <div class="form-text">Máximo 100 caracteres</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="descripcion" class="form-label">Descripción</label>
                                <textarea class="form-control" id="descripcion" name="descripcion" 
                                          rows="3" maxlength="255"><?= htmlspecialchars($categoria['descripcion']) ?></textarea>
                                <div class="form-text">Opcional, máximo 255 caracteres</div>
                            </div>
                            
                            <div class="mb-4 form-check form-switch">
                                <input type="checkbox" class="form-check-input" id="estado" 
                                       name="estado" <?= $categoria['estado'] ? 'checked' : '' ?>>
                                <label class="form-check-label" for="estado">Categoría activa</label>
                            </div>
                            
                            <div class="d-flex justify-content-between">
                                <a href="index.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-times"></i> Cancelar
                                </a>
                                <div>
                                    <button type="reset" class="btn btn-outline-warning me-2">
                                        <i class="fas fa-undo"></i> Restablecer
                                    </button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Guardar Cambios
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                    
                    <div class="card-footer bg-white text-muted small">
                        <div class="row">
                            <div class="col-md-6">
                                <i class="fas fa-calendar-plus"></i> Creada: 
                                <?= date('d/m/Y H:i', strtotime($categoria['fecha_creacion'])) ?>
                            </div>
                            <?php if (!empty($categoria['fecha_actualizacion'])): ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../../template/scripts.php'; ?>
    
    <script>
        // Validación del formulario
        document.querySelector('form').addEventListener('submit', function(e) {
            const nombreInput = document.getElementById('nombre');
            if (nombreInput.value.trim() === '') {
                e.preventDefault();
                nombreInput.classList.add('is-invalid');
                alert('Por favor complete el nombre de la categoría');
            }
        });
        
        // Remover clase de error al escribir
        document.getElementById('nombre').addEventListener('input', function() {
            this.classList.remove('is-invalid');
        });
    </script>
</body>
</html>