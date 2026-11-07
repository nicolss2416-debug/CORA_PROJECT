<?php
/** Vista · Panel principal: contadores + gráficos + últimos movimientos */

$coloresTarjeta = [
    'primario' => ['fondo' => '#EEF2FA', 'icono' => '#4A6BBA'],
    'acento'   => ['fondo' => '#FBF3E3', 'icono' => '#D9A441'],
    'exito'    => ['fondo' => '#E9F7EF', 'icono' => '#2E9E5B'],
    'peligro'  => ['fondo' => '#FDECEC', 'icono' => '#C0392B'],
];

/* Series para los gráficos */
$etiquetasMes    = array_column($porMes, 'etiqueta');
$valoresMes      = array_column($porMes, 'total');
$etiquetasCategoria = array_column($porCategoria, 'nombre');
$valoresCategoria   = array_map('intval', array_column($porCategoria, 'total'));
?>

<!-- Tarjetas de contadores -->
<div class="row g-3 mb-4">
    <?php foreach ($tarjetas as $tarjeta): ?>
        <?php $color = $coloresTarjeta[$tarjeta['color']]; ?>
        <div class="col-6 col-lg-3">
            <a href="<?= e(url($tarjeta['enlace'])) ?>" class="text-decoration-none">
                <div class="cora-stat-card h-100">
                    <div class="cora-stat-icon" style="background: <?= $color['fondo'] ?>; color: <?= $color['icono'] ?>">
                        <i class="bi <?= $tarjeta['icono'] ?>"></i>
                    </div>
                    <div>
                        <span class="cora-stat-value"><?= number_format((float) $tarjeta['valor']) ?></span>
                        <span class="cora-stat-label"><?= e($tarjeta['label']) ?></span>
                    </div>
                </div>
            </a>
        </div>
    <?php endforeach; ?>
</div>

<!-- Indicadores secundarios (personal de la biblioteca) -->
<?php if (!$esLector): ?>
<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="cora-card">
            <div class="row text-center g-0">
                <div class="col-6 col-md-3 cora-mini-stat">
                    <span class="cora-mini-value"><?= number_format($contadores['ejemplares']) ?></span>
                    <span class="cora-mini-label"><i class="bi bi-stack me-1"></i>Ejemplares en inventario</span>
                </div>
                <div class="col-6 col-md-3 cora-mini-stat">
                    <span class="cora-mini-value"><?= number_format($contadores['disponibles']) ?></span>
                    <span class="cora-mini-label"><i class="bi bi-check2-circle me-1"></i>Ejemplares disponibles</span>
                </div>
                <div class="col-6 col-md-3 cora-mini-stat">
                    <span class="cora-mini-value cora-text-exito"><?= number_format($contadores['devueltos']) ?></span>
                    <span class="cora-mini-label"><i class="bi bi-journal-check me-1"></i>Préstamos devueltos</span>
                </div>
                <div class="col-6 col-md-3 cora-mini-stat">
                    <span class="cora-mini-value"><?= number_format($contadores['lectores']) ?></span>
                    <span class="cora-mini-label"><i class="bi bi-person-check me-1"></i>Usuarios activos</span>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Gráficos de estadísticas -->
<div class="row g-3 mb-4">
    <div class="col-12 col-xl-8">
        <div class="cora-card h-100">
            <div class="cora-card-header">
                <h5><i class="bi bi-bar-chart-line me-2"></i>Préstamos por mes</h5>
                <span class="cora-badge cora-badge-activo">Últimos 6 meses</span>
            </div>
            <div class="cora-chart-box"><canvas id="graficoPrestamosMes"></canvas></div>
        </div>
    </div>
    <div class="col-12 col-xl-4">
        <div class="cora-card h-100">
            <div class="cora-card-header">
                <h5><i class="bi bi-pie-chart me-2"></i>Libros por categoría</h5>
            </div>
            <div class="cora-chart-box"><canvas id="graficoCategorias"></canvas></div>
        </div>
    </div>
</div>

<!-- Últimos movimientos -->
<div class="row g-3">
    <div class="col-12">
        <div class="cora-card">
            <div class="cora-card-header">
                <h5><i class="bi bi-clock-history me-2"></i><?= $esLector ? 'Mis últimos préstamos' : 'Últimos préstamos registrados' ?></h5>
                <a href="<?= e(url('prestamos')) ?>" class="btn btn-sm cora-btn-ghost-primary">
                    Ver todos <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>

            <?php if (!$ultimos): ?>
                <div class="cora-empty">
                    <i class="bi bi-journal-x"></i>
                    <p>Aún no hay préstamos registrados.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table cora-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Libro</th>
                                <th><?= $esLector ? 'Fecha préstamo' : 'Lector' ?></th>
                                <th class="d-none d-md-table-cell">Devolución esperada</th>
                                <th class="text-center">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ultimos as $prestamo): ?>
                                <tr>
                                    <td>
                                        <span class="cora-book-title"><?= e($prestamo['libro_titulo']) ?></span>
                                        <small class="d-block text-muted"><?= e($prestamo['libro_autor']) ?></small>
                                    </td>
                                    <td>
                                        <?php if ($esLector): ?>
                                            <?= formato_fecha($prestamo['fecha_prestamo']) ?>
                                        <?php else: ?>
                                            <?= e($prestamo['lector_nombre'] . ' ' . $prestamo['lector_apellido']) ?>
                                        <?php endif; ?>
                                    </td>
                                    <td class="d-none d-md-table-cell"><?= formato_fecha($prestamo['fecha_devolucion_esperada']) ?></td>
                                    <td class="text-center"><?= badge_prestamo($prestamo['estado'], $prestamo['fecha_devolucion_esperada']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
/* ── Gráficos del dashboard (Chart.js) ── */
document.addEventListener('DOMContentLoaded', function () {
    const AZUL = '#4A6BBA', DORADO = '#D9A441';

    if (document.getElementById('graficoPrestamosMes')) {
        new Chart(document.getElementById('graficoPrestamosMes'), {
            type: 'bar',
            data: {
                labels: <?= json_encode($etiquetasMes, JSON_UNESCAPED_UNICODE) ?>,
                datasets: [{
                    label: 'Préstamos',
                    data: <?= json_encode($valoresMes) ?>,
                    backgroundColor: AZUL,
                    hoverBackgroundColor: '#3A568F',
                    borderRadius: 8,
                    maxBarThickness: 42
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { precision: 0, color: '#607D8B' }, grid: { color: '#EEF1F4' } },
                    x: { ticks: { color: '#607D8B' }, grid: { display: false } }
                }
            }
        });
    }

    if (document.getElementById('graficoCategorias')) {
        new Chart(document.getElementById('graficoCategorias'), {
            type: 'doughnut',
            data: {
                labels: <?= json_encode($etiquetasCategoria, JSON_UNESCAPED_UNICODE) ?>,
                datasets: [{
                    data: <?= json_encode($valoresCategoria) ?>,
                    backgroundColor: [AZUL, DORADO, '#2E9E5B', '#5B7FC7', '#C0392B', '#8A6D2F'],
                    borderWidth: 2,
                    borderColor: '#FFFFFF'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '62%',
                plugins: { legend: { position: 'bottom', labels: { color: '#263238', boxWidth: 12, padding: 12 } } }
            }
        });
    }
});
</script>
