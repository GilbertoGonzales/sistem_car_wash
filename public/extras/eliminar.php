<?php
// Iniciar buffer de salida
ob_start();

require_once __DIR__ . '/../../config/database.php';

// Verificar si se recibió ID
if (!isset($_GET['id_servicio_extra']) || !is_numeric($_GET['id_servicio_extra'])) {
    header("Location: index.php?error=invalid_id");
    exit();
}

$id_servicio_extra = (int)$_GET['id_servicio_extra'];
$database = new Database();
$conn = $database->getConnection();

try {
    // Verificar si el servicio existe
    $stmt = $conn->prepare("SELECT id_servicio_extra FROM servicio_extra WHERE id_servicio_extra = ?");
    $stmt->execute([$id_servicio_extra]);
    
    if (!$stmt->fetch()) {
        header("Location: index.php?error=not_found");
        exit();
    }

    // Intentar eliminar
    $stmt = $conn->prepare("DELETE FROM servicio_extra WHERE id_servicio_extra = ?");
    $stmt->execute([$id_servicio_extra]);

    // Verificar si se eliminó
    if ($stmt->rowCount() > 0) {
        header("Location: index.php?success=deleted");
    } else {
        // Si no se eliminó, intentar desactivar
        $stmt = $conn->prepare("UPDATE servicio_extra SET estado = 0 WHERE id_servicio_extra = ?");
        $stmt->execute([$id_servicio_extra]);
        header("Location: index.php?success=deactivated");
    }
    exit();

} catch (PDOException $e) {
    // Registrar error en log
    error_log("Error al eliminar servicio: " . $e->getMessage());
    
    // Redirigir con mensaje de error
    header("Location: index.php?error=db_error");
    exit();
}

// Limpiar buffer y desactivarlo
ob_end_flush();
?>