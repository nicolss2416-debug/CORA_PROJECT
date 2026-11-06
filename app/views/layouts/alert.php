<?php
// Mensajes flash: error / warning / info / success (pantalla 10 del diseño)
$estilos = [
    'error'   => ['danger',  'bi-x-circle-fill',            'Error'],
    'warning' => ['warning', 'bi-exclamation-triangle-fill', 'Advertencia'],
    'info'    => ['info',    'bi-info-circle-fill',          'Información'],
    'success' => ['success', 'bi-check-circle-fill',         'Éxito'],
];
foreach (flash_obtener() as $m):
    [$clase, $icono, $rotulo] = $estilos[$m['tipo']] ?? $estilos['info'];
?>
    <div class="alert alert-<?= $clase ?> cora-alert alert-dismissible fade show" role="alert">
        <i class="bi <?= $icono ?>"></i>
        <div><strong><?= $rotulo ?></strong><br><?= e($m['mensaje']) ?></div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
<?php endforeach; ?>