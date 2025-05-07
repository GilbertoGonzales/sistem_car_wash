<?php
// template/navbar.php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand" href="../../index.php">
            <i class="fas fa-car me-2"></i>Sistema Canvash
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <!-- Menú Catálogos -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-book me-1"></i>Catálogos
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="../../public/vehiculos/index.php">Tipos de Vehículo</a></li>
                        <li><a class="dropdown-item" href="../../public/servicios/index.php">Niveles de Servicio</a></li>
                        <li><a class="dropdown-item" href="../../public/extras/index.php">Servicios Extra</a></li>
                        <li><a class="dropdown-item" href="../../public/categorias_producto/index.php">Categorías de Productos</a></li>
                        <li><a class="dropdown-item" href="../../public/categorias_gasto/index.php">Categorías de Gastos</a></li>
                        <li><a class="dropdown-item" href="../../public/nivel_servicio/index.php">servicio_niveles</a></li>
                    </ul>
                </li>
                
                <!-- Menú Productos -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-boxes me-1"></i>Productos
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="../../public/productos/index.php">Lista de Productos</a></li>
                        <li><a class="dropdown-item" href="../../public/productos/stock.php">Control de Stock</a></li>
                    </ul>
                </li>
                
                <!-- Menú Ventas -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-cash-register me-1"></i>Ventas
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="../../public/ventas/nueva_venta.php">Nueva Venta</a></li>
                        <li><a class="dropdown-item" href="../../public/ventas/lista_ventas.php">Historial de Ventas</a></li>
                    </ul>
                </li>
                
                <!-- Menú Gastos -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-file-invoice-dollar me-1"></i>Gastos
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="../../public/gastos/nuevo_gasto.php">Nuevo Gasto</a></li>
                        <li><a class="dropdown-item" href="../../public/gastos/lista_gastos.php">Historial de Gastos</a></li>
                    </ul>
                </li>
                
                <!-- Menú Reportes -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-chart-pie me-1"></i>Reportes
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="../../public/reportes/ingresos_carwash.php">Ingresos Carwash</a></li>
                        <li><a class="dropdown-item" href="../../public/reportes/ingresos_tienda.php">Ingresos Tienda</a></li>
                        <li><a class="dropdown-item" href="../../public/reportes/egresos.php">Egresos</a></li>
                        <li><a class="dropdown-item" href="../../public/reportes/balance.php">Balance General</a></li>
                    </ul>
                </li>
       
            </ul>
        </div>
    </div>
</nav>