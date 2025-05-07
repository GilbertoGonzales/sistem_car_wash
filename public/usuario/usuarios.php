<?php
session_start();  // Iniciar sesión siempre antes de cualquier salida (HTML, etc.)

// Verifica si el usuario no está logueado
if (!isset($_SESSION['id_usuario'])) {
    // Redirige si el usuario no está logueado
    if ($_SERVER['PHP_SELF'] !== '/index.php') {
        header("Location: index.php");
        exit();
    }
}

// Verifica si el usuario tiene rol 'admin'
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    echo "No tienes permisos para acceder a esta página.";
    exit();
}

// Configuración de la base de datos
$host = "localhost";
$user = "root";
$password = "admin";
$database = "sistema-posventa";

// Conexión a la base de datos
$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Lógica de búsqueda de usuarios
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['buscar'])) {
    $nombre = $_POST['nombre_buscar'];
    $query = "SELECT * FROM usuarios WHERE nombre LIKE ?";
    $stmt = $conn->prepare($query);
    $param = "%$nombre%";
    $stmt->bind_param("s", $param);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    // Mostrar todos los usuarios si no hay búsqueda
    $query = "SELECT * FROM usuarios";
    $result = $conn->query($query);
}

// Lógica para desactivar/activar usuario
if (isset($_GET['accion']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $accion = $_GET['accion'] === 'desactivar' ? 0 : 1;

    $query = "UPDATE usuarios SET estado = ? WHERE id_usuario = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $accion, $id);

    if ($stmt->execute()) {
        // Redirige para actualizar la página y reflejar el cambio de estado
        header("Location: index.php.php");
        exit();
    } else {
        echo "<p style='color: red; text-align: center;'>Error al actualizar el usuario.</p>";
    }
}

include 'head.php';  // Incluye el encabezado (verifica que 'head.php' no tenga salida antes de este punto)
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Usuarios</title>
    <style>
        /* Aquí van los estilos */
        /* ... */
    </style>
</head>
<body>
    <div class="container">
        <h2>Gestión de Usuarios</h2>
        <form method="post">
            <label for="nombre_buscar">Buscar por nombre:</label>
            <input type="text" name="nombre_buscar" id="nombre_buscar">
            <input type="submit" name="buscar" value="Buscar">
        </form>
        <a href="crear_usuario.php">Añadir Usuario</a>

        <table>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Usuario</th>
                    <th>Rol</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo $row['nombre']; ?></td>
                        <td><?php echo $row['usuario']; ?></td>
                        <td><?php echo $row['rol']; ?></td>
                        <td>
                            <a href="editar_usuario.php?id=<?php echo $row['id_usuario']; ?>" class="btn btn-warning btn-sm">
                                <i class="fas fa-pencil-alt"></i>
                            </a>
                            <a href="usuarios.php?accion=<?php echo $row['estado'] == 1 ? 'desactivar' : 'activar'; ?>&id=<?php echo $row['id_usuario']; ?>" 
                               class="btn btn-sm <?php echo $row['estado'] == 1 ? 'btn-deactivate' : 'btn-activate'; ?>"
                               onclick="return confirm('¿Está seguro de que desea <?php echo $row['estado'] == 1 ? 'desactivar' : 'activar'; ?> este usuario?');">
                                <i class="fas <?php echo $row['estado'] == 1 ? 'fa-lock' : 'fa-lock-open'; ?>"></i>
                            </a>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</body>
</html>

<?php
if (isset($stmt)) {
    $stmt->close();
} else {
    $result->close();
}
$conn->close();
?>
