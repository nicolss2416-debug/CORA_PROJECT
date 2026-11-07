<?php
/** Vista · Módulo de reportes (RF-14): filtros por rango de fechas, métricas y gráficos */

$totalRango = $reporte['resumen']['total'];
$etiquetasDia = array_map(fn ($fila) => date('d/m', strtotime($fila['dia'])), $reporte['porDia']);
$valoresDia   = array_map(fn ($fila) => (int) $fila['total'], $reporte['porDia']);
$etiquetasCat = array_column($porCategoria, 'nombre');
$valoresCat   = array_map('intval', array_column($porCategoria, 'total'));
?>
<!-- Barra de herramientas del reporte -->
<div class="cora-card mb-3 no-print">
    <div class="cora-card-header">
        <h5><i class="bi bi-funnel me-2"></i>Filtro por rango de fechas</h5>
        <button type="button" class="btn btn-sm cora-btn-ghost-primary" onclick="window.print()">
            <i class="bi bi-printer me-1"></i>Imprimir reporte
        </button>
    </div>
    <form method="get" action="<?= e(url('reportes')) ?>" class="row g-2 align-items-end p-3 pt-0">
        <div class="col-6 col-md-4">
            <label class="form-label cora-form-label" for="desde">Desde</label>
            <input type="date" class="form-control" id="desde" name="desde" value="<?= e($desde) ?>" max="<?= date('Y-m-d') ?>" required>
        </div>
        <div class="col-6 col-md-4">
            <label class="form-label cora-form-label" for="hasta">Hasta</label>
            <input type="date" class="form-control" id="hasta" name="hasta" value="<?= e($hasta) ?>" max="<?= date('Y-m-d') ?>" required>
        </div>
        <div class="col-12 col-md-4 d-grid">
            <button type="submit" class="btn cora-btn-primary">
                <i class="bi bi-graph-up me-1"></i>Generar reporte
            </button>
        </div>
    </form>
</div>

<!-- Métricas del rango -->
<div class="row g-3 mb-3">
    <div class="col-6 col-xl-3">
        <div class="cora-stat-card cora-stat-mini h-100">
            <div class="cora-stat-icon" style="background:#EEF2FA;color:#4A6BBA"><i class="bi bi-journal-text"></i></div>
            <div><span class="cora-stat-value"><?= number_format($totalRango) ?></span>
            <span class="cora-stat-label">Préstamos en el rango</span></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="cora-stat-card cora-stat-mini h-100">
            <div class="cora-stat-icon" style="background:#E9F7EF;color:#2E9E5B"><i class="bi bi-check2-circle"></i></div>
            <div><span class="cora-stat-value"><?= number_format($reporte['resumen']['devueltos']) ?></span>
            <span class="cora-stat-label">Devueltos</span></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="cora-stat-card cora-stat-mini h-100">
            <div class="cora-stat-icon" style="background:#FBF3E3;color:#D9A441"><i class="bi bi-arrow-repeat"></i></div>
            <div><span class="cora-stat-value"><?= number_format($reporte['resumen']['activos']) ?></span>
            <span class="cora-stat-label">Activos vigentes</span></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="cora-stat-card cora-stat-mini h-100">
            <div class="cora-stat-icon" style="background:#FDECEC;color:#C0392B"><i class="bi bi-exclamation-triangle"></i></div>
            <div><span class="cora-stat-value"><?= number_format($reporte['resumen']['vencidos']) ?></span>
            <span class="cora-stat-label">Vencidos</span></div>
        </div>
    </div>
</div>

<!-- Inventario general + gráficos -->
<div class="row g-3 mb-3">
    <div class="col-12 col-xl-8">
        <div class="cora-card h-100">
            <div class="cora-card-header">
                <h5><i class="bi bi-graph-up me-2"></i>Préstamos por día</h5>
                <span class="cora-badge cora-badge-activo">
                    <?= formato_fecha($desde) ?> – <?= formato_fecha($hasta) ?>
                </span>
            </div>
            <div class="cora-chart-box">
                <?php if ($totalRango === 0): ?>
                    <div class="cora-empty"><i class="bi bi-bar-chart"></i><p>Sin préstamos en este rango de fechas.</p></div>
                <?php else: ?>
                    <canvas id="reportePorDia"></canvas>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-4">
        <div class="cora-card mb-3">
            <div class="cora-card-header"><h5><i class="bi bi-clipboard-data me-2"></i>Estado del rango</h5></div>
            <div class="cora-chart-box"><canvas id="reporteEstados"></canvas></div>
        </div>
        <div class="cora-card">
            <div class="cora-card-header"><h5><i class="bi bi-inboxes me-2"></i>Inventario actual</h5></div>
            <ul class="list-group list-group-flush cora-lista">
                <li class="list-group-item d-flex justify-content-between">
                    <span><i class="bi bi-book me-2 cora-text-primario"></i>Títulos en catálogo</span><b><?= number_format($inventario['titulos']) ?></b>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                    <span><i class="bi bi-stack me-2 cora-text-primario"></i>Ejemplares totales</span><b><?= number_format($inventario['ejemplares']) ?></b>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                    <span><i class="bi bi-check2 me-2 cora-text-exito"></i>Ejemplares disponibles</span><b><?= number_format($inventario['disponibles']) ?></b>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                    <span><i class="bi bi-people me-2 cora-text-acento"></i>Usuarios registrados</span><b><?= number_format($inventario['usuarios']) ?></b>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                    <span><i class="bi bi-person-check me-2 cora-text-acento"></i>Usuarios activos</span><b><?= number_format($inventario['lectores']) ?></b>
                </li>
            </ul>
        </div>
    </div>
</div>

<!-- Top 5 libros y lectores -->
<div class="row g-3 mb-3">
    <div class="col-12 col-lg-6">
        <div class="cora-card h-100">
            <div class="cora-card-header"><h5><i class="bi bi-trophy me-2"></i>Libros más prestados</h5></div>
            <?php if (!$reporte['topLibros']): ?>
                <div class="cora-empty"><i class="bi bi-book"></i><p>Sin datos en el rango seleccionado.</p></div>
            <?php else: ?>
                <ul class="list-group list-group-flush cora-lista">
                    <?php foreach ($reporte['topLibros'] as $i => $libroTop): ?>
                        <li class="list-group-item d-flex align-items-center gap-3">
                            <span class="cora-rank <?= $i === 0 ? 'cora-rank-oro' : '' ?>"><?= $i + 1 ?></span>
                            <div class="flex-grow-1">
                                <span class="cora-book-title d-block"><?= e($libroTop['titulo']) ?></span>
                                <small class="text-muted"><?= e($libroTop['autor']) ?></small>
                            </div>
                            <span class="cora-badge cora-badge-activo"><?= (int) $libroTop['total'] ?> préstamos</span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
    <div class="col-12 col-lg-6">
        <div class="cora-card h-100">
            <div class="cora-card-header"><h5><i class="bi bi-award me-2"></i>Lectores más activos</h5></div>
            <?php if (!$reporte['topLectores']): ?>
                <div class="cora-empty"><i class="bi bi-person"></i><p>Sin datos en el rango seleccionado.</p></div>
            <?php else: ?>
                <ul class="list-group list-group-flush cora-lista">
                    <?php foreach ($reporte['topLectores'] as $i => $lectorTop): ?>
                        <li class="list-group-item d-flex align-items-center gap-3">
                            <span class="cora-rank <?= $i === 0 ? 'cora-rank-oro' : '' ?>"><?= $i + 1 ?></span>
                            <div class="flex-grow-1">
                                <span class="cora-book-title d-block"><?= e($lectorTop['nombre'] . ' ' . $lectorTop['apellido']) ?></span>
                                <small class="text-muted"><?= e($lectorTop['email']) ?></small>
                            </div>
                            <span class="cora-badge cora-badge-disponible"><?= (int) $lectorTop['total'] ?> préstamos</span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Distribución por categoría -->
<div class="row g-3">
    <div class="col-12">
        <div class="cora-card">
            <div class="cora-card-header"><h5><i class="bi bi-pie-chart me-2"></i>Libros por categoría</h5></div>
            <div class="cora-chart-box"><canvas id="reporteCategorias"></canvas></div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const AZUL = '#4A6BBA', DORADO = '#D9A441';

    if (document.getElementById('reportePorDia')) {
        new Chart(document.getElementById('reportePorDia'), {
            type: 'line',
            data: {
                labels: <?= json_encode($etiquetasDia, JSON_UNESCAPED_UNICODE) ?>,
                datasets: [{
                    label: 'Préstamos',
                    data: <?= json_encode($valoresDia) ?>,
                    borderColor: AZUL,
                    backgroundColor: 'rgba(74,107,186,.14)',
                    pointBackgroundColor: DORADO,
                    pointBorderColor: '#fff',
                    pointRadius: 4,
                    fill: true,
                    tension: .35
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { precision: 0, color: '#607D8B' }, grid: { color: '#EEF1F4' } },
                    x: { ticks: { color: '#607D8B', maxRotation: 45 }, grid: { display: false } }
                }
            }
        });
    }

    if (document.getElementById('reporteEstados')) {
        new Chart(document.getElementById('reporteEstados'), {
            type: 'doughnut',
            data: {
                labels: ['Devueltos', 'Activos', 'Vencidos'],
                datasets: [{
                    data: [<?= (int) $reporte['resumen']['devueltos'] ?>, <?= (int) $reporte['resumen']['activos'] ?>, <?= (int) $reporte['resumen']['vencidos'] ?>],
                    backgroundColor: ['#2E9E5B', AZUL, '#C0392B'],
                    borderWidth: 2, borderColor: '#fff'
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false, cutout: '60%',
                plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, padding: 10 } } }
            }
        });
    }

    if (document.getElementById('reporteCategorias')) {
        new Chart(document.getElementById('reporteCategorias'), {
            type: 'bar',
            data: {
                labels: <?= json_encode($etiquetasCat, JSON_UNESCAPED_UNICODE) ?>,
                datasets: [{
                    label: 'Libros',
                    data: <?= json_encode($valoresCat) ?>,
                    backgroundColor: [AZUL, DORADO, '#2E9E5B', '#5B7FC7', '#C0392B', '#8A6D2F'],
                    borderRadius: 8, maxBarThickness: 46
                }]
            },
            options: {
                indexAxis: 'y', responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { beginAtZero: true, ticks: { precision: 0, color: '#607D8B' }, grid: { color: '#EEF1F4' } },
                    y: { ticks: { color: '#263238' }, grid: { display: false } }
                }
            }
        });
    }
});
</script>
