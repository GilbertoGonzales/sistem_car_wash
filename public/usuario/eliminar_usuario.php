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

if (isset($_GET['id'])) {
    $id_usuario = $_GET['id'];

    // Preparar la consulta para eliminar el usuario
    $query = "DELETE FROM usuarios WHERE id_usuario = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id_usuario);

    if ($stmt->execute()) {
        echo "Usuario eliminado correctamente.";
    } else {
        echo "Error al eliminar el usuario.";
    }

    $stmt->close();
    $conn->close();

    // Redirigir de vuelta a la página de usuarios
    header("Location: usuarios.php");
    exit();
} else {
    echo "ID de usuario no especificado.";
}
?>
