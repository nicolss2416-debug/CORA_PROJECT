<?php
/** Layout · Mensajes de estado (módulo 10): éxito, error, advertencia, información */

$estilos = [
    'exito'       => ['clase' => 'success',   'icono' => 'bi-check-circle-fill'],
    'error'       => ['clase' => 'danger',    'icono' => 'bi-x-octagon-fill'],
    'advertencia' => ['clase' => 'warning',   'icono' => 'bi-exclamation-triangle-fill'],
    'informacion' => ['clase' => 'info',      'icono' => 'bi-info-circle-fill'],
];
?>
<div class="cora-alerts no-print" aria-live="polite">
    <?php foreach (Flash::all() as $mensaje): ?>
        <?php $estilo = $estilos[$mensaje['tipo']] ?? $estilos['informacion']; ?>
        <div class="alert cora-alert alert-<?= $estilo['clase'] ?> alert-dismissible fade show" role="alert">
            <i class="bi <?= $estilo['icono'] ?> me-2"></i>
            <?= e($mensaje['mensaje']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    <?php endforeach; ?>
</div>
