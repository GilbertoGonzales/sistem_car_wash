<?php
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

session_start();

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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'];
    $usuario = $_POST['usuario'];
    $password = password_hash($_POST['contrasena'], PASSWORD_DEFAULT);
    $rol = $_POST['rol'];

    $query = "INSERT INTO usuarios (nombre, usuario, contrasena, rol, estado) VALUES (?, ?, ?, ?, 1)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssss", $nombre, $usuario, $password, $rol);

    if ($stmt->execute()) {
        echo "Usuario añadido correctamente.";
        header("Location: usuarios.php");
        exit();
    } else {
        echo "Error al añadir el usuario.";
    }

    $stmt->close();
    $conn->close();
}

include 'head.php'; 
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Usuario</title>
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
        <h2>Crear Usuario</h2>
        <form method="post">
            <!-- Nombre del Usuario -->
            <label for="nombre">Nombre:</label>
            <input type="text" name="nombre" id="nombre" required>

            <!-- Usuario -->
            <label for="usuario">Usuario:</label>
            <input type="text" name="usuario" id="usuario" required>

            <!-- Contraseña -->
            <label for="contrasena">Contraseña:</label>
            <input type="password" name="contrasena" id="contrasena" required>

            <!-- Rol -->
            <label for="rol">Rol:</label>
            <select name="rol" id="rol" required>
                <option value="admin">Administrador</option>
                <option value="usuario">Usuario</option>
                <option value="vendedor">Ventas</option>
            </select>
            <!-- Botones -->
        <div class="form-actions">
            <button type="submit">Agregar Usuario</button>
            <button class="btn btn-danger" type="button" onclick="window.location.href='usuarios.php'">Cancelar</button>
        </div>
        </form>
</body>
</html>

