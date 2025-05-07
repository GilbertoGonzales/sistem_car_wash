<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/constantes.php';

// Iniciar sesión para mensajes flash
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$database = new Database();
$conn = $database->getConnection();

// Manejo de mensajes flash
if (isset($_SESSION['flash_message'])) {
    $mensaje = $_SESSION['flash_message']['texto'];
    $tipoMensaje = $_SESSION['flash_message']['tipo'];
    unset($_SESSION['flash_message']);
} else {
    $mensaje = '';
    $tipoMensaje = '';
}

// Obtener todas las categorías con paginación
$pagina = filter_input(INPUT_GET, 'pagina', FILTER_VALIDATE_INT) ?? 1;
$porPagina = 10; // Número de items por página

try {
    // Contar total de categorías
    $stmt = $conn->query("SELECT COUNT(*) FROM categoria_producto");
    $totalCategorias = $stmt->fetchColumn();
    $paginas = ceil($totalCategorias / $porPagina);
    
    // Validar página
    if ($pagina < 1 || $pagina > $paginas) {
        $pagina = 1;
    }
    
    $offset = ($pagina - 1) * $porPagina;
    
    // Obtener categorías paginadas
    $query = "SELECT * FROM categoria_producto ORDER BY nombre ASC LIMIT :limit OFFSET :offset";
    $stmt = $conn->prepare($query);
    $stmt->bindValue(':limit', $porPagina, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $_SESSION['flash_message'] = [
        'texto' => "Error al cargar categorías: " . (ENVIRONMENT === 'development' ? $e->getMessage() : 'Contacte al administrador'),
        'tipo' => 'danger'
    ];
    $categorias = [];
    $paginas = 1;
    $pagina = 1;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <?php include '../../template/head.php'; ?>
    <!-- En el head de tu HTML -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title>Gestión de Categorías de Producto</title>
    <style>
        .badge-activo { background-color: #28a745; }
        .badge-inactivo { background-color: #6c757d; }
        .action-buttons .btn { margin-right: 5px; }
        .table-responsive { overflow-x: auto; }
        .confirm-eliminar { color: #dc3545; font-weight: bold; }
        .pagination .page-item.active .page-link { background-color: #6c757d; border-color: #6c757d; }
        .search-box { max-width: 300px; }
        .empty-state { padding: 3rem; text-align: center; }
        .empty-state i { font-size: 3rem; color: #6c757d; margin-bottom: 1rem; }
    </style>
</head>
<body>
    <?php include '../../template/navbar.php'; ?>
    
    <div class="container py-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="mb-0"><i class="fas fa-tags"></i> Categorías de Producto</h2>
                    <a href="crear.php" class="btn btn-success">
                        <i class="fas fa-plus-circle"></i> Nueva Categoría
                    </a>
                </div>
                
                <?php if ($mensaje): ?>
                    <div class="alert alert-<?= $tipoMensaje ?> alert-dismissible fade show">
                        <?= $mensaje ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Listado de Categorías</h5>
                        <div class="d-flex">
                            <div class="input-group input-group-sm search-box">
                                <input type="text" class="form-control" placeholder="Buscar..." id="searchInput">
                                <button class="btn btn-outline-secondary" type="button" id="searchButton">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-body p-0">
                        <?php if (empty($categorias)): ?>
                            <div class="empty-state">
                                <i class="fas fa-tags"></i>
                                <h4>No hay categorías registradas</h4>
                                <p class="text-muted">Comience agregando una nueva categoría</p>
                                <a href="crear.php" class="btn btn-primary">
                                    <i class="fas fa-plus-circle"></i> Crear Categoría
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover table-bordered table-striped mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="5%">ID</th>
                                            <th width="25%">Nombre</th>
                                            <th width="40%">Descripción</th>
                                            <th width="10%">Estado</th>
                                            <th width="20%" class="text-center">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($categorias as $cat): ?>
                                            <tr>
                                                <td><?= $cat['id_categoria_producto'] ?></td>
                                                <td><?= htmlspecialchars($cat['nombre']) ?></td>
                                                <td><?= !empty($cat['descripcion']) ? htmlspecialchars($cat['descripcion']) : '<span class="text-muted">Sin descripción</span>' ?></td>
                                                <td>
                                                    <span class="badge rounded-pill <?= $cat['estado'] ? 'badge-activo' : 'badge-inactivo' ?>">
                                                        <?= $cat['estado'] ? 'Activo' : 'Inactivo' ?>
                                                    </span>
                                                </td>
                                                <td class="action-buttons text-center">
                                                    <a href="editar.php?id=<?= $cat['id_categoria_producto'] ?>" 
                                                       class="btn btn-sm btn-primary" 
                                                       title="Editar">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <<!-- Reemplaza el botón de eliminar con ESTE CÓDIGO -->
                                                    <form method="POST" action="eliminar.php" style="display: inline;">
                                                        <input type="hidden" name="id" value="<?= $cat['id_categoria_producto'] ?>">
                                                        <button type="submit" class="btn btn-sm btn-danger" 
                                                                onclick="return confirm('¿Estás seguro de eliminar \'<?= addslashes($cat['nombre']) ?>\'?')">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (!empty($categorias)): ?>
                        <div class="card-footer bg-white d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                Mostrando <?= count($categorias) ?> de <?= $totalCategorias ?> categoría(s)
                            </small>
                            
                            <nav aria-label="Page navigation">
                                <ul class="pagination pagination-sm mb-0">
                                    <li class="page-item <?= $pagina <= 1 ? 'disabled' : '' ?>">
                                        <a class="page-link" href="?pagina=<?= $pagina - 1 ?>">Anterior</a>
                                    </li>
                                    
                                    <?php for ($i = 1; $i <= $paginas; $i++): ?>
                                        <li class="page-item <?= $i == $pagina ? 'active' : '' ?>">
                                            <a class="page-link" href="?pagina=<?= $i ?>"><?= $i ?></a>
                                        </li>
                                    <?php endfor; ?>
                                    
                                    <li class="page-item <?= $pagina >= $paginas ? 'disabled' : '' ?>">
                                        <a class="page-link" href="?pagina=<?= $pagina + 1 ?>">Siguiente</a>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php include '../../template/scripts.php'; ?>
    
    <!-- Formulario oculto para eliminación -->
    <form id="formEliminar" method="POST" action="eliminar.php" style="display: none;">
        <input type="hidden" name="id" id="idEliminar">
        <input type="hidden" name="confirmacion" value="1">
    </form>
    
    <script>
    // Reemplaza completamente tu función confirmarEliminacion con esta:
function confirmarEliminacion(id, nombre) {
    // Primero verifica que SweetAlert esté cargado
    if (typeof Swal === 'undefined') {
        console.error('SweetAlert2 no está cargado!');
        if (confirm('¿Eliminar categoría "' + nombre + '"?')) {
            window.location.href = 'eliminar.php?id=' + id;
        }
        return;
    }
    
    // Usando SweetAlert2
    Swal.fire({
        title: '¿Confirmar eliminación?',
        html: `Estás por eliminar la categoría: <strong>${nombre}</strong>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        allowOutsideClick: false
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `eliminar.php?id=${id}`;
        }
    });

        
    }.then((result) => {
        if (result.isConfirmed) {
            Swal.fire(
                'Eliminado!',
                'La categoría ha sido eliminada.',
                'success'
            ).then(() => {
                location.reload(); // Recargar la página
            });
        }
    });
}
    
    // Búsqueda simple
    document.getElementById('searchButton').addEventListener('click', function() {
        const searchTerm = document.getElementById('searchInput').value.toLowerCase();
        const rows = document.querySelectorAll('tbody tr');
        
        rows.forEach(row => {
            const nombre = row.cells[1].textContent.toLowerCase();
            const descripcion = row.cells[2].textContent.toLowerCase();
            
            if (nombre.includes(searchTerm) || descripcion.includes(searchTerm)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
    
    // Permitir búsqueda con Enter
    document.getElementById('searchInput').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            document.getElementById('searchButton').click();
        }
    });
    </script>
</body>
</html>