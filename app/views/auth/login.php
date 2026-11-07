<?php
/** Vista · Inicio de sesión (RF-01) — pantalla independiente del layout */
$correo = old('email');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión · <?= e(APP_NAME) ?></title>
    <link rel="icon" type="image/svg+xml" href="<?= e(asset('img/favicon.svg')) ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="<?= e(asset('css/style.css')) ?>" rel="stylesheet">
</head>
<body class="cora-login-body">

<div class="cora-login-container">
    <!-- Panel de marca (azul institucional) -->
    <section class="cora-login-brand">
        <img src="<?= e(asset('img/logo.svg')) ?>" alt="Logo Biblioteca CORA" width="84" height="84">
        <h1>Biblioteca <span>CORA</span></h1>
        <p class="cora-brand-tag">Sistema Web de Gestión Bibliotecaria</p>

        <ul class="cora-brand-list">
            <li><i class="bi bi-bookshelf"></i> Administra el catálogo y sus ejemplares</li>
            <li><i class="bi bi-journal-arrow-down"></i> Controla préstamos y devoluciones</li>
            <li><i class="bi bi-graph-up-arrow"></i> Consulta reportes y estadísticas</li>
        </ul>

        <p class="cora-brand-copy">Barrio Chapinero · Servicio a la comunidad<br>Proyecto académico SENA – ADSO</p>
    </section>

    <!-- Formulario de acceso -->
    <section class="cora-login-form">
        <div class="w-100" style="max-width: 420px;">
            <div class="cora-login-card">
                <h2>¡Bienvenido(a) de nuevo!</h2>
                <p class="text-muted">Inicie sesión con su cuenta para continuar</p>

                <?php foreach (Flash::all() as $mensaje): ?>
                    <?php
                    $clases = ['exito' => 'success', 'error' => 'danger', 'advertencia' => 'warning', 'informacion' => 'info'];
                    $iconos = ['exito' => 'bi-check-circle-fill', 'error' => 'bi-x-octagon-fill', 'advertencia' => 'bi-exclamation-triangle-fill', 'informacion' => 'bi-info-circle-fill'];
                    $clase  = $clases[$mensaje['tipo']] ?? 'info';
                    ?>
                    <div class="alert cora-alert alert-<?= $clase ?> alert-dismissible fade show" role="alert">
                        <i class="bi <?= $iconos[$mensaje['tipo']] ?? 'bi-info-circle-fill' ?> me-2"></i>
                        <?= e($mensaje['mensaje']) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                    </div>
                <?php endforeach; ?>

                <form method="post" action="<?= e(url('login')) ?>" novalidate>
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label for="email" class="form-label">Correo electrónico</label>
                        <div class="input-group">
                            <span class="input-group-text cora-input-icon"><i class="bi bi-envelope"></i></span>
                            <input type="email" class="form-control <?= isset($errores['email']) ? 'is-invalid' : '' ?>"
                                   id="email" name="email" value="<?= e($correo) ?>"
                                   placeholder="usuario@cora.com" autocomplete="username" autofocus required>
                        </div>
                        <?php if (isset($errores['email'])): ?>
                            <div class="invalid-feedback d-block"><?= e($errores['email']) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label">Contraseña</label>
                        <div class="input-group">
                            <span class="input-group-text cora-input-icon"><i class="bi bi-key"></i></span>
                            <input type="password" class="form-control <?= isset($errores['password']) ? 'is-invalid' : '' ?>"
                                   id="password" name="password" placeholder="••••••••"
                                   autocomplete="current-password" required>
                            <button class="btn btn-outline-secondary cora-pass-toggle" type="button" data-bs-toggle="password"
                                    data-target="#password" aria-label="Mostrar contraseña">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        <?php if (isset($errores['password'])): ?>
                            <div class="invalid-feedback d-block"><?= e($errores['password']) ?></div>
                        <?php endif; ?>
                    </div>

                    <button type="submit" class="btn cora-btn-primary w-100">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Ingresar
                    </button>
                </form>
            </div>

            <p class="text-center text-muted small mt-4 mb-0">
                <i class="bi bi-shield-lock me-1"></i>Acceso controlado por roles: Administrador · Bibliotecario · Lector
            </p>
        </div>
    </section>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= e(asset('js/app.js')) ?>"></script>
</body>
</html>
