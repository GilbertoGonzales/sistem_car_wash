// views/vehiculos/index.php - Vista para listar tipos de vehículo
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tipos de Vehículos - Sistema Carwash</title>
    <link rel="stylesheet" href="../../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../assets/css/styles.css">
</head>
<body>
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Tipos de Vehículos 
                            <a href="create.php" class="btn btn-primary float-end">Nuevo Tipo</a>
                        </h4>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Descripción</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($vehiculos["records"] as $vehiculo): ?>
                                <tr>
                                    <td><?= $vehiculo["id_tipo_vehiculo"] ?></td>
                                    <td><?= $vehiculo["nombre"] ?></td>
                                    <td><?= $vehiculo["descripcion"] ?></td>
                                    <td>
                                        <?php if($vehiculo["estado"] == 1): ?>
                                            <span class="badge bg-success">Activo</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Inactivo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="edit.php?id=<?= $vehiculo["id_tipo_vehiculo"] ?>" class="btn btn-success btn-sm">Editar</a>
                                        <a href="delete.php?id=<?= $vehiculo["id_tipo_vehiculo"] ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Está seguro de eliminar este registro?')">Eliminar</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="../../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php