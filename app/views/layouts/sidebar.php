<?php
// Menú según rol (RNF-02: acceso por rol)
$actual = strtolower(explode('/', trim($_GET['url'] ?? 'dashboard', '/'))[0]);

$menu = [];
if (tiene_rol('Administrador', 'Bibliotecario')) {
    $menu[] = ['dashboard',    'Inicio',       'bi-house-door'];
}
$menu[] = ['libros', tiene_rol('Lector') ? 'Catálogo' : 'Libros', 'bi-book'];
if (tiene_rol('Administrador', 'Bibliotecario')) {
    $menu[] = ['usuarios',     'Usuarios',     'bi-people'];
}
$menu[] = ['prestamos', tiene_rol('Lector') ? 'Mis préstamos' : 'Préstamos', 'bi-journal-arrow-up'];
if (tiene_rol('Administrador', 'Bibliotecario')) {
    $menu[] = ['devoluciones', 'Devoluciones', 'bi-journal-arrow-down'];
    $menu[] = ['reportes',     'Reportes',     'bi-bar-chart-line'];
}
?>
<aside class="cora-sidebar" id="sidebar">
    <div class="cora-brand">
        <img src="<?= asset('img/logo.svg') ?>" alt="Logo <?= APP_NAME ?>" width="40" height="40">
        <span>Biblioteca <strong>CORA</strong></span>
        <button class="btn-close btn-close-white d-lg-none ms-auto" id="cerrarMenu" aria-label="Cerrar menú"></button>
    </div>

    <nav class="cora-nav" aria-label="Menú principal">
        <?php foreach ($menu as [$ctrl, $texto, $icono]): ?>
            <a href="<?= url($ctrl) ?>" class="<?= $actual === $ctrl ? 'active' : '' ?>">
                <i class="bi <?= $icono ?>"></i> <?= $texto ?>
            </a>
        <?php endforeach; ?>
        <a href="<?= url('auth/logout') ?>" class="mt-auto"><i class="bi bi-box-arrow-left"></i> Cerrar sesión</a>
    </nav>
</aside>
<div class="cora-overlay" id="overlay"></div>
