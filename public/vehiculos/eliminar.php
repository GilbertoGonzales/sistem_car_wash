<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/constantes.php';

// Iniciar buffer para evitar problemas con headers
ob_start();

$database = new Database();
$conn = $database->getConnection();

// Validar ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php?error=id");
    exit();
}

$id = $_GET['id'];

try {
    // Verificar existencia
    $stmt = $conn->prepare("SELECT id_tipo_vehiculo FROM tipo_vehiculo WHERE id_tipo_vehiculo = ?");
    $stmt->execute([$id]);
    
    if (!$stmt->fetch()) {
        header("Location: index.php?error=id");
        exit();
    }

    // Eliminar (o marcar como inactivo para eliminación lógica)
    $query = "DELETE FROM tipo_vehiculo WHERE id_tipo_vehiculo = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$id]);

    header("Location: index.php?exito=eliminado");
    exit();

} catch (PDOException $e) {
    // Si hay error por restricciones (claves foráneas), hacer eliminación lógica
    try {
        $query = "UPDATE tipo_vehiculo SET estado = 0 WHERE id_tipo_vehiculo = ?";
        $stmt = $conn->prepare($query);
        $stmt->execute([$id]);
        
        header("Location: index.php?exito=desactivado");
        exit();
    } catch (PDOException $e) {
        error_log("Error al eliminar tipo de vehículo: " . $e->getMessage());
        header("Location: index.php?error=bd");
        exit();
    }
}

// Limpiar buffer
ob_end_flush();
?>