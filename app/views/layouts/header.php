<?php
/** Layout · Cabecera del documento y recursos globales (Bootstrap 5 · Poppins · Roboto) */
$paginaTitulo = $pageTitle ?? APP_NAME;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= e(APP_TAGLINE) ?>">
    <title><?= e($paginaTitulo) ?> · <?= e(APP_NAME) ?></title>

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="<?= e(asset('img/favicon.svg')) ?>">

    <!-- Bootstrap 5 + Iconos -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <!-- Tipografías: Poppins (títulos) · Roboto (textos y botones) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">

    <!-- Estilos del sistema de diseño CORA -->
    <link href="<?= e(asset('css/style.css')) ?>" rel="stylesheet">
</head>
<body>
