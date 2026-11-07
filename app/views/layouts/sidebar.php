<?php
/** Layout · Sidebar responsivo con control de acceso por rol (módulo 3) */

$slugActivo = strtolower(explode('/', trim($_GET['url'] ?? ($_SERVER['PATH_INFO'] ?? ''), '/'))[0] ?? 'dashboard');
if ($slugActivo === 'inicio' || $slugActivo === '') {
    $slugActivo = 'dashboard';
}

/* Definición del menú: cada módulo indica los roles autorizados */
$menu = [
    ['slug' => 'dashboard',    'icono' => 'bi-house-door-fill',      'etiqueta' => 'Inicio',       'roles' => ['Administrador', 'Bibliotecario', 'Lector']],
    ['slug' => 'libros',       'icono' => 'bi-bookshelf',            'etiqueta' => 'Libros',       'roles' => ['Administrador', 'Bibliotecario', 'Lector']],
    ['slug' => 'usuarios',     'icono' => 'bi-people-fill',          'etiqueta' => 'Usuarios',     'roles' => ['Administrador', 'Bibliotecario']],
    ['slug' => 'prestamos',    'icono' => 'bi-journal-arrow-down',   'etiqueta' => 'Préstamos',    'roles' => ['Administrador', 'Bibliotecario', 'Lector']],
    ['slug' => 'devoluciones', 'icono' => 'bi-journal-arrow-up',     'etiqueta' => 'Devoluciones', 'roles' => ['Administrador', 'Bibliotecario']],
    ['slug' => 'reportes',     'icono' => 'bi-graph-up-arrow',       'etiqueta' => 'Reportes',     'roles' => ['Administrador', 'Bibliotecario']],
];

$rolActual = Auth::rol();
?>
<!-- Backdrop móvil -->
<div class="cora-sidebar-backdrop" id="sidebarBackdrop"></div>

<aside class="cora-sidebar" id="coraSidebar" aria-label="Menú de navegación principal">
    <div class="cora-sidebar-brand">
        <img src="<?= e(asset('img/logo.svg')) ?>" alt="Logo Biblioteca CORA" width="44" height="44">
        <div>
            <h1>Biblioteca <span>CORA</span></h1>
            <p>Gestión bibliotecaria</p>
        </div>
    </div>

    <nav class="cora-nav">
        <span class="cora-nav-title">Menú principal</span>
        <?php foreach ($menu as $item): ?>
            <?php if (!in_array($rolActual, $item['roles'], true)) { continue; } ?>
            <a href="<?= e(url($item['slug'])) ?>"
               class="cora-nav-link <?= $slugActivo === $item['slug'] ? 'activo' : '' ?>">
                <i class="bi <?= $item['icono'] ?>"></i>
                <span><?= e($item['etiqueta']) ?></span>
                <?php if ($item['slug'] === 'prestamos' && isset($contadores) && !$esLector && $contadores['vencidos'] > 0): ?>
                    <span class="badge cora-badge cora-badge-vencido ms-auto"><?= (int) $contadores['vencidos'] ?></span>
                <?php endif; ?>
            </a>
        <?php endforeach; ?>

        <span class="cora-nav-title mt-2">Sesión</span>
        <a href="<?= e(url('logout')) ?>" class="cora-nav-link cora-nav-logout" data-confirm="¿Está seguro(a) que desea cerrar la sesión?">
            <i class="bi bi-box-arrow-right"></i>
            <span>Cerrar sesión</span>
        </a>
    </nav>

    <div class="cora-sidebar-footer">
        <i class="bi bi-info-circle me-1"></i>
        <?= e(APP_NAME) ?> · v<?= e(APP_VERSION) ?>
    </div>
</aside>
