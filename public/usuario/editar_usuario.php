<?php
session_start();
include 'head.php';

// Verificar que el usuario sea administrador
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    echo "No tienes permisos para acceder a esta página.";
    exit();
}

// Conexión a la base de datos
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

// Validar si se recibe el ID del usuario
$id_usuario = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id_usuario) {
    echo "ID de usuario inválido.";
    exit();
}

// Obtener los datos del usuario
$query = "SELECT * FROM usuarios WHERE id_usuario = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    echo "Usuario no encontrado.";
    exit();
}

// Definir valores predeterminados para los permisos si no existen en el array $user
$acceso_ventas = $user['acceso_ventas'] ?? 0;
$acceso_productos = $user['acceso_productos'] ?? 0;
$acceso_clientes = $user['acceso_clientes'] ?? 0;
$acceso_usuarios = $user['acceso_usuarios'] ?? 0;

// Procesar el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recoger y sanitizar datos del formulario
    $nombre = trim($_POST['nombre']);
    $usuario = trim($_POST['usuario']);
    $rol = $_POST['rol'];
    $acceso_ventas = isset($_POST['acceso_ventas']) ? 1 : 0;
    $acceso_productos = isset($_POST['acceso_productos']) ? 1 : 0;
    $acceso_clientes = isset($_POST['acceso_clientes']) ? 1 : 0;
    $acceso_usuarios = isset($_POST['acceso_usuarios']) ? 1 : 0;

    // Actualizar el usuario con o sin nueva contraseña
    if (!empty($_POST['contrasena'])) {
        $contrasena = password_hash($_POST['contrasena'], PASSWORD_BCRYPT);
        $query = "UPDATE usuarios SET nombre = ?, usuario = ?, contrasena = ?, rol = ?, acceso_ventas = ?, acceso_productos = ?, acceso_clientes = ?, acceso_usuarios = ? WHERE id_usuario = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssssiiiii", $nombre, $usuario, $contrasena, $rol, $acceso_ventas, $acceso_productos, $acceso_clientes, $acceso_usuarios, $id_usuario);
    } else {
        $query = "UPDATE usuarios SET nombre = ?, usuario = ?, rol = ?, acceso_ventas = ?, acceso_productos = ?, acceso_clientes = ?, acceso_usuarios = ? WHERE id_usuario = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssiiiii", $nombre, $usuario, $rol, $acceso_ventas, $acceso_productos, $acceso_clientes, $acceso_usuarios, $id_usuario);
    }

    // Ejecutar la consulta y manejar el resultado
    if ($stmt->execute()) {
        echo "<script>alert('Usuario actualizado exitosamente.'); window.location.href = 'usuarios.php';</script>";
    } else {
        echo "<script>alert('Error al actualizar el usuario. Intenta nuevamente.');</script>";
    }
    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Usuario</title>
    <style>
h2 {
    text-align: center;
    color: #444;
    margin-bottom: 20px;
}

/* Form container */
form {
    background-color: #ffffff;
    max-width: 600px;
    margin: 30px auto;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    display: flex;
    flex-direction: column;
    gap: 15px;
}

/* Input and select styling */
input[type="text"],
input[type="password"],
select {
    width: 100%;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
    font-size: 14px;
    box-sizing: border-box;
}

input[type="text"]:focus,
input[type="password"]:focus,
select:focus {
    border-color: #007bff;
    outline: none;
    box-shadow: 0 0 5px rgba(0, 123, 255, 0.3);
}

/* Label styling */
label {
    font-weight: bold;
    font-size: 14px;
}

/* Checkbox and permissions */
h3 {
    font-size: 16px;
    margin-top: 20px;
    color: #555;
}

input[type="checkbox"] {
    margin-right: 10px;
}

/* Buttons */
button[type="submit"],
button[type="button"] {
    width: calc(50% - 10px);
    padding: 10px;
    font-size: 16px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s ease;
    text-align: center;
    margin: 10px 0;
}

button[type="submit"] {
    background-color: #007bff;
    color: white;
}

button[type="submit"]:hover {
    background-color: #0056b3;
}

button[type="button"] {
    background-color: #dc3545;
    color: white;
}

button[type="button"]:hover {
    background-color: #c82333;
}

/* Responsive design */
@media (max-width: 768px) {
    form {
        padding: 15px;
        width: 90%;
    }

    .form-row {
        flex-direction: column;
    }

    button[type="submit"],
    button[type="button"] {
        width: 100%;
    }
}
</style>
</head>
<body>
    <h2>Editar Usuario</h2>
        <form method="post">
            <!-- Nombre del Usuario -->
            <label for="nombre">Nombre:</label>
            <input type="text" name="nombre" id="nombre" value="<?php echo htmlspecialchars($user['nombre']); ?>" required>

            <!-- Usuario -->
            <label for="usuario">Usuario:</label>
            <input type="text" name="usuario" id="usuario" value="<?php echo htmlspecialchars($user['usuario']); ?>" required>

            <!-- Contraseña -->
            <label for="contrasena">Contraseña (dejar en blanco para mantener la actual):</label>
            <input type="password" name="contrasena" id="contrasena">

            <!-- Rol -->
            <label for="rol">Rol:</label>
            <select name="rol" id="rol" required>
                <option value="admin" <?php echo $user['rol'] == 'admin' ? 'selected' : ''; ?>>Administrador</option>
                <option value="usuario" <?php echo $user['rol'] == 'usuario' ? 'selected' : ''; ?>>Usuario</option>
                <option value="vendedor" <?php echo $user['rol'] == 'vendedor' ? 'selected' : ''; ?>>Ventas</option>
            </select>

            <!-- Permisos de Acceso -->
            <h3>Permisos de Acceso</h3>
            <label>
                <input type="checkbox" name="acceso_ventas" id="acceso_ventas" <?php echo $acceso_ventas ? 'checked' : ''; ?>>
                Acceso a Ventas
            </label>
            <label>
                <input type="checkbox" name="acceso_productos" id="acceso_productos" <?php echo $acceso_productos ? 'checked' : ''; ?>>
                Acceso a Productos
            </label>
            <label>
                <input type="checkbox" name="acceso_clientes" id="acceso_clientes" <?php echo $acceso_clientes ? 'checked' : ''; ?>>
                Acceso a Clientes
            </label>
            <label>
                <input type="checkbox" name="acceso_usuarios" id="acceso_usuarios" <?php echo $acceso_usuarios ? 'checked' : ''; ?>>
                Acceso a Usuarios
            </label>
            <!-- Botones -->
        <div class="form-actions">
            <button type="submit">Actualizar Usuario</button>
            <button class="btn btn-danger" type="button" onclick="window.location.href='usuarios.php'">Volver</button>
        </div>
        </form>
    </div>
</body>
</html>
