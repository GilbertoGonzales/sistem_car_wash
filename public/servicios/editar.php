<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/constantes.php';

$database = new Database();
$conn = $database->getConnection();

// Obtener tipos de vehículo y niveles de servicio
$query_vehiculos = "SELECT id_tipo_vehiculo, nombre FROM tipo_vehiculo WHERE estado = 1";
$stmt_vehiculos = $conn->prepare($query_vehiculos);
$stmt_vehiculos->execute();
$vehiculos = $stmt_vehiculos->fetchAll(PDO::FETCH_ASSOC);

$query_niveles = "SELECT id_nivel_servicio, nombre FROM nivel_servicio WHERE estado = 1";
$stmt_niveles = $conn->prepare($query_niveles);
$stmt_niveles->execute();
$niveles = $stmt_niveles->fetchAll(PDO::FETCH_ASSOC);

// Obtener el servicio a editar
$id = $_GET['id'] ?? null;
$servicio = null;

if ($id) {
    try {
        $query = "SELECT ps.*, tv.nombre as nombre_vehiculo, ns.nombre as nombre_nivel 
                  FROM precio_servicio ps
                  JOIN tipo_vehiculo tv ON ps.id_tipo_vehiculo = tv.id_tipo_vehiculo
                  JOIN nivel_servicio ns ON ps.id_nivel_servicio = ns.id_nivel_servicio
                  WHERE ps.id_precio_servicio = :id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $servicio = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        header("Location: index.php?error=bd");
        exit();
    }
}

if (!$servicio) {
    header("Location: index.php?error=id");
    exit();
}

// Manejo del formulario de edición
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_tipo_vehiculo = $_POST['id_tipo_vehiculo'];
    $id_nivel_servicio = $_POST['id_nivel_servicio'];
    $precio = $_POST['precio'];

    if (!empty($id_tipo_vehiculo) && !empty($id_nivel_servicio) && !empty($precio)) {
        try {
            $query = "UPDATE precio_servicio 
                      SET id_tipo_vehiculo = :id_tipo_vehiculo, 
                          id_nivel_servicio = :id_nivel_servicio, 
                          precio = :precio 
                      WHERE id_precio_servicio = :id";
            $stmt = $conn->prepare($query);

            $stmt->bindParam(':id_tipo_vehiculo', $id_tipo_vehiculo);
            $stmt->bindParam(':id_nivel_servicio', $id_nivel_servicio);
            $stmt->bindParam(':precio', $precio);
            $stmt->bindParam(':id', $id);

            if ($stmt->execute()) {
                header("Location: index.php?exito=editado");
                exit();
            } else {
                header("Location: editar.php?id=$id&error=bd");
                exit();
            }
        } catch (PDOException $e) {
            header("Location: editar.php?id=$id&error=bd");
            exit();
        }
    } else {
        header("Location: editar.php?id=$id&error=campos");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <?php include '../../template/head.php'; ?>
    <title>Editar Servicio</title>
</head>
<body>
    <?php include '../../template/navbar.php'; ?>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-6 offset-md-3">
                <div class="card">
                    <div class="card-header">
                        <h4>Editar Servicio</h4>
                    </div>
                    <div class="card-body">
                        <?php if (isset($_GET['error'])): ?>
                            <div class="alert alert-danger">
                                <?php 
                                switch($_GET['error']) {
                                    case 'campos': echo "Por favor complete todos los campos"; break;
                                    case 'bd': echo "Error en la base de datos"; break;
                                    default: echo "Error desconocido";
                                }
                                ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="editar.php?id=<?= $id ?>">
                            <div class="form-group">
                                <label>Tipo de Vehículo Actual</label>
                                <input type="text" class="form-control" value="<?= htmlspecialchars($servicio['nombre_vehiculo']) ?>" readonly>
                            </div>
                            <div class="form-group">
                                <label for="id_tipo_vehiculo">Seleccionar Nuevo Tipo</label>
                                <select id="id_tipo_vehiculo" name="id_tipo_vehiculo" class="form-control" required>
                                    <option value="">Seleccione tipo de vehículo</option>
                                    <?php foreach ($vehiculos as $vehiculo): ?>
                                        <option value="<?= $vehiculo['id_tipo_vehiculo'] ?>" <?= $vehiculo['id_tipo_vehiculo'] == $servicio['id_tipo_vehiculo'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($vehiculo['nombre']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label>Nivel de Servicio Actual</label>
                                <input type="text" class="form-control" value="<?= htmlspecialchars($servicio['nombre_nivel']) ?>" readonly>
                            </div>
                            <div class="form-group">
                                <label for="id_nivel_servicio">Seleccionar Nuevo Nivel</label>
                                <select id="id_nivel_servicio" name="id_nivel_servicio" class="form-control" required>
                                    <option value="">Seleccione nivel de servicio</option>
                                    <?php foreach ($niveles as $nivel): ?>
                                        <option value="<?= $nivel['id_nivel_servicio'] ?>" <?= $nivel['id_nivel_servicio'] == $servicio['id_nivel_servicio'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($nivel['nombre']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="precio">Precio</label>
                                <input type="number" id="precio" name="precio" class="form-control" 
                                       value="<?= htmlspecialchars($servicio['precio']) ?>" required step="0.01" min="0">
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Actualizar Servicio</button>
                            <a href="index.php" class="btn btn-secondary ml-2">Cancelar</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../../template/scripts.php'; ?>
</body>
</html>