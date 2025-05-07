<?php
// public/views/partials/head.php
$pagina_titulo = $pagina_titulo ?? 'Sistema Canvash';
?>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo htmlspecialchars($pagina_titulo); ?></title>

<!-- Bootstrap CSS -->
<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">

<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

<!-- CSS personalizado -->
<link rel="stylesheet" href="assets/css/styles.css">

<!-- Favicon -->
<link rel="icon" href="assets/img/favicon.ico" type="image/x-icon">

<!-- CSS específico de página -->
<?php if (isset($css_especifico)): ?>
<link rel="stylesheet" href="<?php echo $css_especifico; ?>">
<?php endif; ?>