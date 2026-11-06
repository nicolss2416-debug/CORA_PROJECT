<div class="mb-4">
    <h1 class="h4 mb-0">Bienvenido, <?= e(usuario_actual()['nombre']) ?></h1>
    <p class="text-muted small mb-0">Aquí tienes un resumen de la biblioteca</p>
</div>

<div class="row g-3 mb-4">
    <?php
    $tarjetas = [
        ['bi-book',               'Total de libros',          number_format($conteos['libros']),   'text-warning'],
        ['bi-people',             'Usuarios registrados',     $conteos['usuarios'],                'text-primary'],
        ['bi-calendar2-check',    'Préstamos activos',        $conteos['activos'],                 'text-warning'],
        ['bi-clock-history',      'Devoluciones pendientes',  $conteos['activos'],                 'text-danger'],
    ];
    foreach ($tarjetas as [$ic, $et, $val, $col]): ?>
        <div class="col-6 col-xl-3">
            <div class="stat-card">
                <i class="bi <?= $ic ?> <?= $col ?>"></i>
                <div>
                    <div class="stat-label"><?= $et ?></div>
                    <div class="stat-value"><?= $val ?></div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="cora-card h-100">
            <h2 class="h6">Préstamos por mes</h2>
            <canvas id="graficoMes" height="130"
                    data-valores='<?= json_encode($porMes) ?>'></canvas>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="cora-card h-100">
            <h2 class="h6">Préstamos recientes</h2>
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead><tr><th>Libro</th><th>Usuario</th><th>Fecha</th></tr></thead>
                    <tbody>
                    <?php foreach ($recientes as $p): ?>
                        <tr>
                            <td><?= e($p['libro']) ?></td>
                            <td><?= e($p['lector']) ?></td>
                            <td class="text-nowrap"><?= formato_fecha($p['fecha_prestamo']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>