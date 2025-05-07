<?php
require_once __DIR__ . '/../../config/database.php';

$database = new Database();
$conn = $database->getConnection();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Datos del servicio extra
    $id_tipo_vehiculo = $_POST['id_tipo_vehiculo'];
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];
    $precio = $_POST['precio'];
    $estado = $_POST['estado'];

    // Fecha y hora actuales
    $fecha_creacion = date('Y-m-d H:i:s');
    $fecha_actualizacion = $fecha_creacion;

    try {
        // Iniciar transacción
        $conn->beginTransaction();

        // Insertar el servicio extra
        $query = "INSERT INTO servicio_extra 
                 (nombre, descripcion, precio, estado, fecha_creacion, fecha_actualizacion) 
                 VALUES 
                 (:nombre, :descripcion, :precio, :estado, :fecha_creacion, :fecha_actualizacion)";
        $stmt = $conn->prepare($query);

        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':descripcion', $descripcion);
        $stmt->bindParam(':precio', $precio);
        $stmt->bindParam(':estado', $estado);
        $stmt->bindParam(':fecha_creacion', $fecha_creacion);
        $stmt->bindParam(':fecha_actualizacion', $fecha_actualizacion);

        if ($stmt->execute()) {
            $id_servicio_extra = $conn->lastInsertId();

            // Insertar la relación con el tipo de vehículo
            $query_relacion = "INSERT INTO servicios_extra_tipo_vehiculo 
                              (id_servicio_extra, id_tipo_vehiculo) 
                              VALUES (:id_servicio_extra, :id_tipo_vehiculo)";
            $stmt_relacion = $conn->prepare($query_relacion);
            $stmt_relacion->bindParam(':id_servicio_extra', $id_servicio_extra);
            $stmt_relacion->bindParam(':id_tipo_vehiculo', $id_tipo_vehiculo);
            
            if ($stmt_relacion->execute()) {
                $conn->commit();
                header("Location: index.php?exito=creado");
                exit();
            } else {
                $conn->rollBack();
                header("Location: crear.php?error=relacion");
                exit();
            }
        } else {
            $conn->rollBack();
            header("Location: crear.php?error=servicio");
            exit();
        }
    } catch (PDOException $e) {
        $conn->rollBack();
        header("Location: crear.php?error=bd&mensaje=" . urlencode($e->getMessage()));
        exit();
    }
}

// Obtener tipos de vehículo
$query = "SELECT id_tipo_vehiculo, nombre FROM tipo_vehiculo WHERE estado = 1";
$stmt = $conn->prepare($query);
$stmt->execute();
$tipos_vehiculo = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <?php include '../../template/head.php'; ?>
    <title>Nuevo Servicio Extra</title>
</head>
<body>
    <?php include '../../template/navbar.php'; ?>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4>Crear Nuevo Servicio Extra</h4>
                    </div>
                    <div class="card-body">
                        <?php if (isset($_GET['error'])): ?>
                            <div class="alert alert-danger">
                                <?php 
                                switch($_GET['error']) {
                                    case 'relacion': echo "Error al crear la relación con el vehículo"; break;
                                    case 'servicio': echo "Error al crear el servicio"; break;
                                    case 'bd': echo "Error de base de datos: " . htmlspecialchars($_GET['mensaje'] ?? ''); break;
                                    default: echo "Error desconocido";
                                }
                                ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="crear.php">
                            <div class="form-group">
                                <label for="id_tipo_vehiculo">Tipo de Vehículo</label>
                                <select name="id_tipo_vehiculo" class="form-control" required>
                                    <option value="">Seleccione un tipo de vehículo</option>
                                    <?php foreach ($tipos_vehiculo as $tipo): ?>
                                        <option value="<?= $tipo['id_tipo_vehiculo'] ?>">
                                            <?= htmlspecialchars($tipo['nombre']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="nombre">Nombre</label>
                                <input type="text" name="nombre" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="descripcion">Descripción</label>
                                <textarea name="descripcion" class="form-control" required></textarea>
                            </div>
                            <div class="form-group">
                                <label for="precio">Precio</label>
                                <input type="number" step="0.01" name="precio" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="estado">Estado</label>
                                <select name="estado" class="form-control" required>
                                    <option value="1">Disponible</option>
                                    <option value="0">No Disponible</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-success">Crear Servicio Extra</button>
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