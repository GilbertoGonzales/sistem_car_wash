<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/constantes.php';

// Iniciar sesión para mensajes flash
session_start();

// Verificar que sea una solicitud POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['mensaje'] = "Acción no permitida";
    $_SESSION['tipo_mensaje'] = 'danger';
    header("Location: index.php");
    exit;
}

// Conexión a la base de datos
$database = new Database();
$conn = $database->getConnection();

// Validar ID
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$id || $id <= 0) {
    $_SESSION['mensaje'] = "ID de categoría inválido";
    $_SESSION['tipo_mensaje'] = 'danger';
    header("Location: index.php");
    exit;
}

try {
    // 1. Verificar que la categoría existe y obtener su nombre
    $stmt = $conn->prepare("SELECT nombre FROM categoria_gasto WHERE id_categoria_gasto = ?");
    $stmt->execute([$id]);
    
    if ($stmt->rowCount() === 0) {
        $_SESSION['mensaje'] = "La categoría no existe o ya fue eliminada";
        $_SESSION['tipo_mensaje'] = 'warning';
        header("Location: index.php");
        exit;
    }
    
    $categoria = $stmt->fetch();
    $nombreCategoria = $categoria['nombre'];
    
    // 2. Eliminar la categoría (sin verificación de relaciones)
    $stmt = $conn->prepare("DELETE FROM categoria_gasto WHERE id_categoria_gasto = ?");
    $stmt->execute([$id]);
    
    // Verificar si se eliminó correctamente
    if ($stmt->rowCount() > 0) {
        $_SESSION['mensaje'] = "Categoría eliminada: " . htmlspecialchars($nombreCategoria);
        $_SESSION['tipo_mensaje'] = 'success';
    } else {
        $_SESSION['mensaje'] = "No se pudo eliminar la categoría";
        $_SESSION['tipo_mensaje'] = 'warning';
    }

} catch (PDOException $e) {
    // Registrar el error completo en el log
    error_log("Error al eliminar categoría de gasto (ID: $id): " . $e->getMessage());
    
    // Mensaje de error genérico para el usuario
    $_SESSION['mensaje'] = "Ocurrió un error al procesar la solicitud";
    $_SESSION['tipo_mensaje'] = 'danger';
}

// Redireccionar al listado
header("Location: index.php");
exit;