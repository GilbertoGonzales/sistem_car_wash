<?php
// public/views/partials/scripts.php
?>
<!-- jQuery first, then Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>

<!-- Chart.js para gráficos -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>

<!-- Scripts personalizados -->
<script src="assets/js/main.js"></script>

<!-- Scripts específicos de página -->
<?php if (isset($js_especifico)): ?>
<script src="<?php echo $js_especifico; ?>"></script>
<?php endif; ?>

<!-- Inicialización de componentes -->
<script>
$(document).ready(function() {
    // Activar tooltips
    $('[data-toggle="tooltip"]').tooltip();
    
    // Activar popovers
    $('[data-toggle="popover"]').popover();
    
    <!-- Asegúrate de tener esto antes de cerrar el body -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>