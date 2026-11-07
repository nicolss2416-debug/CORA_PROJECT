<?php
/** Vista · Error 404 / 500 — pantalla independiente del layout */
$codigo   = http_response_code() ?: 404;
$esError  = $codigo >= 500;
$titulo   = $esError ? 'Error interno del servidor' : 'Página no encontrada';
$mensaje  = $esError
    ? 'Ocurrió un problema inesperado. Verifique que el servicio de MySQL esté activo e intente de nuevo.'
    : 'La dirección que intenta acceder no existe o fue movida.';
$destino  = Auth::check() ? 'dashboard' : 'login';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $codigo ?> · <?= e(APP_NAME) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body{margin:0;min-height:100vh;display:grid;place-items:center;background:#F8F9FA;font-family:Roboto,sans-serif;color:#263238}
        .caja{max-width:520px;text-align:center;background:#fff;border-radius:16px;padding:48px 36px;box-shadow:0 10px 30px rgba(38,50,56,.08)}
        h1{font-family:Poppins,sans-serif;font-size:84px;margin:0;color:#4A6BBA;font-weight:700;line-height:1}
        h2{font-family:Poppins,sans-serif;font-size:22px;margin:12px 0 8px;font-weight:600}
        .btn{margin-top:22px;background:#4A6BBA;border:none;font-weight:500;padding:10px 28px}
        .btn:hover{background:#3A568F}
        .acento{width:64px;height:5px;border-radius:3px;background:#D9A441;margin:0 auto 8px}
    </style>
</head>
<body>
    <div class="caja">
        <h1><?= $codigo ?></h1>
        <div class="acento"></div>
        <h2><?= $titulo ?></h2>
        <p class="text-muted mb-0"><?= $mensaje ?></p>
        <a class="btn btn-primary" href="<?= e(url($destino)) ?>">
            <i class="bi bi-house-door"></i> Volver al inicio
        </a>
    </div>
</body>
</html>
