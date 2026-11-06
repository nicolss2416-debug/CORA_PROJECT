<?php
/**
 * Portada generada con CSS (no usa imágenes con derechos de autor).
 * Espera $libro y opcional $mini. Si luego subes imágenes reales,
 * reemplaza este div por <img src="...">.
 */
$colores = ['#4A6B88', '#D9A441', '#7A4E2D', '#2E7D6B', '#8E3B46', '#5B4B8A', '#3A6F8F', '#A15C38'];
$color = $colores[$libro['id'] % count($colores)];
$iniciales = mb_strtoupper(mb_substr($libro['titulo'], 0, 2));
?>
<div class="portada <?= !empty($mini) ? 'portada-mini' : 'portada-grande' ?>" style="--c: <?= $color ?>" aria-hidden="true">
    <span><?= e($iniciales) ?></span>
    <?php if (empty($mini)): ?><small><?= e($libro['titulo']) ?></small><?php endif; ?>
</div>