<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iniciar sesión · <?= APP_NAME ?></title>
    <link rel="icon" type="image/svg+xml" href="<?= asset('img/logo.svg') ?>">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= asset('css/style.css') ?>" rel="stylesheet">
</head>
<body class="login-body">
<div class="login-wrap">
    <section class="login-hero d-none d-md-flex">
        <img src="<?= asset('img/logo.svg') ?>" alt="" width="84" height="84">
        <h1>Biblioteca CORA</h1>
        <p><?= APP_SLOGAN ?></p>
    </section>

    <section class="login-card">
        <div class="d-md-none text-center mb-3">
            <img src="<?= asset('img/logo.svg') ?>" alt="Logo" width="56" height="56">
            <h2 class="h5 mt-2 mb-0">Biblioteca CORA</h2>
        </div>

        <h2 class="h4 mb-1">Iniciar sesión</h2>
        <p class="text-muted small mb-4">Accede a tu cuenta para continuar</p>

        <?php require APP_PATH . '/views/layouts/alert.php'; ?>

        <form action="<?= url('auth/login') ?>" method="post" novalidate id="formLogin">
            <?= csrf_campo() ?>

            <label for="correo" class="form-label">Correo electrónico</label>
            <div class="input-group mb-3">
                <span class="input-group-text"><i class="bi bi-person"></i></span>
                <input type="email" class="form-control" id="correo" name="correo" placeholder="Correo electrónico" required autocomplete="username">
                <div class="invalid-feedback">Ingresa un correo válido.</div>
            </div>

            <label for="clave" class="form-label">Contraseña</label>
            <div class="input-group mb-3">
                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                <input type="password" class="form-control" id="clave" name="clave" placeholder="Contraseña" required autocomplete="current-password">
                <button class="btn btn-outline-secondary" type="button" id="verClave" aria-label="Mostrar contraseña"><i class="bi bi-eye"></i></button>
                <div class="invalid-feedback">Ingresa tu contraseña.</div>
            </div>

            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" id="recordar" name="recordar">
                <label class="form-check-label small" for="recordar">Recordarme</label>
            </div>

            <button type="submit" class="btn btn-cora w-100">Iniciar sesión</button>
        </form>

        <div class="demo-box mt-4">
            <strong>Cuentas de prueba</strong>
            <ul class="mb-0 small">
                <li>Administrador: admin@cora.com / admin123</li>
                <li>Bibliotecario: biblio@cora.com / biblio123</li>
                <li>Lector: lector@cora.com / lector123</li>
            </ul>
        </div>
    </section>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= asset('js/login.js') ?>"></script>
</body>
</html>