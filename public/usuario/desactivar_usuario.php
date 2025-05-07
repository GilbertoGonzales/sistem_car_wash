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

    // Primero, obtenemos el estado actual del usuario
    $query = "SELECT estado FROM usuarios WHERE id_usuario = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        $estado_actual = $user['estado'];

        // Cambiamos el estado: si está activo (1), lo desactivamos (0); si está inactivo (0), lo activamos (1)
        $nuevo_estado = ($estado_actual == 1) ? 0 : 1;

        // Preparamos la consulta para actualizar el estado
        $query_update = "UPDATE usuarios SET estado = ? WHERE id_usuario = ?";
        $stmt_update = $conn->prepare($query_update);
        $stmt_update->bind_param("ii", $nuevo_estado, $id_usuario);

        if ($stmt_update->execute()) {
            echo ($nuevo_estado == 1) ? "Usuario activado correctamente." : "Usuario desactivado correctamente.";
        } else {
            echo "Error al actualizar el estado del usuario.";
        }

        $stmt_update->close();
    } else {
        echo "Usuario no encontrado.";
    }

    $stmt->close();
    $conn->close();

    // Redirigimos de vuelta a la página de usuarios
    header("Location: usuarios.php");
    exit();
} else {
    echo "ID de usuario no especificado.";
}
?>
