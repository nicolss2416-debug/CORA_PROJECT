<?php
/** Layout · Barra superior: menú móvil, título y menú de usuario */
?>
<header class="cora-topbar no-print">
    <div class="d-flex align-items-center gap-3">
        <button class="cora-burger" id="sidebarToggle" type="button" aria-label="Abrir menú" aria-controls="coraSidebar" aria-expanded="false">
            <i class="bi bi-list"></i>
        </button>
        <div>
            <h2 class="cora-page-title"><?= e($pageTitle ?? 'Panel') ?></h2>
            <p class="cora-page-sub mb-0"><?= e(fecha_larga_es()) ?></p>
        </div>
    </div>

    <div class="dropdown">
        <button class="cora-user-btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            <span class="cora-avatar"><?= e(iniciales(Auth::user()['nombre'] ?? '', Auth::user()['apellido'] ?? '')) ?></span>
            <span class="cora-user-text d-none d-sm-flex flex-column text-start">
                <strong><?= e(Auth::nombre()) ?></strong>
                <small><?= e(Auth::rol()) ?></small>
            </span>
        </button>
        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
            <li class="dropdown-item-text">
                <strong><?= e(Auth::nombre()) ?></strong><br>
                <small class="text-muted"><?= e(Auth::user()['email'] ?? '') ?></small>
                <div class="mt-1"><?= badge_rol(Auth::rol()) ?></div>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li>
                <form method="post" action="<?= e(url('logout')) ?>">
                    <?= csrf_field() ?>
                    <button type="submit" class="dropdown-item text-danger">
                        <i class="bi bi-box-arrow-right me-2"></i>Cerrar sesión
                    </button>
                </form>
            </li>
        </ul>
    </div>
</header>
