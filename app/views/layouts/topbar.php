<header class="cora-topbar">
    <button class="btn btn-light d-lg-none" id="abrirMenu" aria-label="Abrir menú"><i class="bi bi-list fs-5"></i></button>

    <form class="cora-search" action="<?= BASE_URL ?>index.php" method="get" role="search">
        <input type="hidden" name="url" value="libros">
        <i class="bi bi-search"></i>
        <input type="search" name="q" class="form-control" placeholder="Buscar libros..." aria-label="Buscar libros">
    </form>

    <div class="dropdown ms-auto">
        <button class="btn cora-user dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
            <span class="avatar"><?= e(mb_strtoupper(mb_substr(usuario_actual()['nombre'] ?? '?', 0, 1))) ?></span>
            <span class="d-none d-sm-inline"><?= e(usuario_actual()['nombre'] ?? '') ?></span>
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
            <li><span class="dropdown-item-text small text-muted"><?= e(usuario_actual()['rol'] ?? '') ?></span></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="<?= url('auth/logout') ?>"><i class="bi bi-box-arrow-left me-2"></i>Cerrar sesión</a></li>
        </ul>
    </div>
</header>