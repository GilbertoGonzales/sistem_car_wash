<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/constantes.php';

$database = new Database();
$conn = $database->getConnection();

// Obtener tipos de vehículo activos
$query_vehiculos = "SELECT id_tipo_vehiculo, nombre FROM tipo_vehiculo WHERE estado = 1";
$stmt_vehiculos = $conn->prepare($query_vehiculos);
$stmt_vehiculos->execute();
$vehiculos = $stmt_vehiculos->fetchAll(PDO::FETCH_ASSOC);

// Obtener niveles de servicio activos
$query_niveles = "SELECT id_nivel_servicio, nombre FROM nivel_servicio WHERE estado = 1";
$stmt_niveles = $conn->prepare($query_niveles);
$stmt_niveles->execute();
$niveles = $stmt_niveles->fetchAll(PDO::FETCH_ASSOC);

// Manejo del formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_tipo_vehiculo = $_POST['id_tipo_vehiculo'];
    $id_nivel_servicio = $_POST['id_nivel_servicio'];
    $precio = $_POST['precio'];

    if (!empty($id_tipo_vehiculo) && !empty($id_nivel_servicio) && !empty($precio)) {
        try {
            $query = "INSERT INTO precio_servicio (id_tipo_vehiculo, id_nivel_servicio, precio, estado, fecha_creacion) 
                      VALUES (:id_tipo_vehiculo, :id_nivel_servicio, :precio, 1, NOW())";
            $stmt = $conn->prepare($query);

            $stmt->bindParam(':id_tipo_vehiculo', $id_tipo_vehiculo);
            $stmt->bindParam(':id_nivel_servicio', $id_nivel_servicio);
            $stmt->bindParam(':precio', $precio);

            if ($stmt->execute()) {
                header("Location: index.php?exito=creado");
                exit();
            } else {
                header("Location: crear.php?error=bd");
                exit();
            }
        } catch (PDOException $e) {
            header("Location: crear.php?error=bd");
            exit();
        }
    } else {
        header("Location: crear.php?error=campos");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <?php include '../../template/head.php'; ?>
    <title>Crear Nuevo Servicio</title>
</head>
<body>
    <?php include '../../template/navbar.php'; ?>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-6 offset-md-3">
                <div class="card">
                    <div class="card-header">
                        <h4>Crear Nuevo Servicio</h4>
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
                        
                        <form method="POST" action="crear.php">
                            <div class="form-group">
                                <label for="id_tipo_vehiculo">Tipo de Vehículo</label>
                                <select id="id_tipo_vehiculo" name="id_tipo_vehiculo" class="form-control" required>
                                    <option value="">Seleccione tipo de vehículo</option>
                                    <?php foreach ($vehiculos as $vehiculo): ?>
                                        <option value="<?= $vehiculo['id_tipo_vehiculo'] ?>">
                                            <?= htmlspecialchars($vehiculo['nombre']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="id_nivel_servicio">Nivel de Servicio</label>
                                <select id="id_nivel_servicio" name="id_nivel_servicio" class="form-control" required>
                                    <option value="">Seleccione nivel de servicio</option>
                                    <?php foreach ($niveles as $nivel): ?>
                                        <option value="<?= $nivel['id_nivel_servicio'] ?>">
                                            <?= htmlspecialchars($nivel['nombre']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="precio">Precio</label>
                                <input type="number" id="precio" name="precio" class="form-control" required step="0.01" min="0">
                            </div>
                            <button type="submit" class="btn btn-success">Crear Servicio</button>
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