<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/constantes.php';

$database = new Database();
$conn = $database->getConnection();

// Verificar si se recibió un ID válido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php?error=id');
    exit;
}

try {
    // Primero verificamos que el registro existe
    $checkQuery = "SELECT id_nivel_servicio FROM nivel_servicio WHERE id_nivel_servicio = ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->execute([$_GET['id']]);
    
    if ($stmt->rowCount() === 0) {
        header('Location: index.php?error=id');
        exit;
    }

    // Eliminar el registro (opción 1: eliminación física)
    $deleteQuery = "DELETE FROM nivel_servicio WHERE id_nivel_servicio = ?";
    
    // Opción 2: eliminación lógica (recomendado para mantener historial)
    // $deleteQuery = "UPDATE nivel_servicio SET estado = 0 WHERE id_nivel_servicio = ?";
    
    $stmt = $conn->prepare($deleteQuery);
    
    if ($stmt->execute([$_GET['id']])) {
        header('Location: index.php?exito=eliminado');
    } else {
        header('Location: index.php?error=bd');
    }
    exit;

} catch (PDOException $e) {
    header('Location: index.php?error=bd');
    exit;
}