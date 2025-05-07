<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/constantes.php';

session_start();

$database = new Database();
$conn = $database->getConnection();

$idGasto = $_GET['id'] ?? null;

if (!$idGasto) {
    $_SESSION['error'] = "ID de gasto no especificado";
    header("Location: index.php");
    exit();
}

try {
    // Verificar si el gasto existe
    $query = "SELECT * FROM gasto WHERE id_gasto = :id";
    $stmt = $conn->prepare($query);
    $stmt->execute([':id' => $idGasto]);
    $gasto = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$gasto) {
        $_SESSION['error'] = "Gasto no encontrado";
        header("Location: index.php");
        exit();
    }
    
    // Eliminar el gasto
    $conn->beginTransaction();
    $query = "DELETE FROM gasto WHERE id_gasto = :id";
    $stmt = $conn->prepare($query);
    $stmt->execute([':id' => $idGasto]);
    $conn->commit();
    
    $_SESSION['mensaje'] = "Gasto eliminado correctamente";
} catch (Exception $e) {
    $conn->rollBack();
    $_SESSION['error'] = "Error al eliminar el gasto: " . $e->getMessage();
}

header("Location: index.php");
exit();
?>