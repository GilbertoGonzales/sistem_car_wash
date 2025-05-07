<?php
require_once __DIR__ . '/../../config/database.php';

// Debug: Mostrar todos los errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Verificar si es POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "Método no permitido";
    header("Location: index.php");
    exit;
}

// Validar ID
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if (!$id || $id <= 0) {
    $_SESSION['error'] = "ID de categoría inválido";
    header("Location: index.php");
    exit;
}

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // 1. Verificar existencia
    $check = $conn->prepare("SELECT nombre FROM categoria_producto WHERE id_categoria_producto = ?");
    $check->execute([$id]);
    
    if ($check->rowCount() === 0) {
        $_SESSION['error'] = "La categoría no existe";
        header("Location: index.php");
        exit;
    }
    
    $categoria = $check->fetch();
    
    // 2. Eliminar (sin verificar productos asociados para simplificar)
    $delete = $conn->prepare("DELETE FROM categoria_producto WHERE id_categoria_producto = ?");
    $delete->execute([$id]);
    
    // Verificar filas afectadas
    if ($delete->rowCount() > 0) {
        $_SESSION['exito'] = "Categoría '".htmlspecialchars($categoria['nombre'])."' eliminada correctamente";
    } else {
        $_SESSION['error'] = "No se pudo eliminar la categoría (ningún registro afectado)";
    }
    
} catch (PDOException $e) {
    $_SESSION['error'] = "Error en base de datos: ".$e->getMessage();
    error_log("Error al eliminar categoría: ".$e->getMessage()); // Guardar en log
}

header("Location: index.php");
exit;