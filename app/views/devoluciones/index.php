<?php
/** Vista · Módulo de devoluciones (RF-11 · RN-03): préstamos pendientes */
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <p class="text-muted mb-0">
        <i class="bi bi-journal-arrow-up me-1"></i>
        <?= $totalRegistros ?> préstamo<?= $totalRegistros === 1 ? '' : 's' ?> pendiente<?= $totalRegistros === 1 ? '' : 's' ?> de devolución
    </p>
    <a href="<?= e(url('prestamos')) ?>" class="btn cora-btn-ghost-primary">
        <i class="bi bi-journal-text me-1"></i> Ver historial completo
    </a>
</div>

<!-- Resumen -->
<div class="row g-3 mb-3">
    <div class="col-6 col-lg-4">
        <div class="cora-chip-card cora-chip-activos">
            <span class="cora-chip-num"><?= (int) $contadores['activos'] ?></span>
            <span class="cora-chip-label"><i class="bi bi-arrow-repeat me-1"></i>Préstamos activos</span>
        </div>
    </div>
    <div class="col-6 col-lg-4">
        <div class="cora-chip-card cora-chip-vencidos">
            <span class="cora-chip-num"><?= (int) $contadores['vencidos'] ?></span>
            <span class="cora-chip-label"><i class="bi bi-exclamation-triangle me-1"></i>Vencidos sin devolver</span>
        </div>
    </div>
    <div class="col-6 col-lg-4">
        <div class="cora-chip-card cora-chip-devueltos">
            <span class="cora-chip-num"><?= (int) $contadores['devueltos'] ?></span>
            <span class="cora-chip-label"><i class="bi bi-check2-circle me-1"></i>Ya devueltos</span>
        </div>
    </div>
</div>

<!-- Búsqueda -->
<div class="cora-card mb-3">
    <form method="get" action="<?= e(url('devoluciones')) ?>" class="row g-2 align-items-end">
        <div class="col-12 col-md-9">
            <label class="form-label cora-form-label" for="q">Buscar préstamo</label>
            <div class="input-group">
                <span class="input-group-text cora-input-icon"><i class="bi bi-search"></i></span>
                <input type="text" class="form-control" id="q" name="q" value="<?= e($filtros['q']) ?>"
                       placeholder="Libro, autor o lector…">
            </div>
        </div>
        <div class="col-12 col-md-3 d-grid">
            <button type="submit" class="btn cora-btn-primary"><i class="bi bi-search me-1"></i>Buscar</button>
        </div>
    </form>
</div>

<!-- Listado de pendientes -->
<div class="cora-card">
    <?php if (!$prestamos): ?>
        <div class="cora-empty">
            <i class="bi bi-clipboard-check"></i>
            <p>No hay préstamos pendientes de devolución. ¡Todo al día!</p>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table cora-table align-middle mb-0">
                <thead>
                    <tr>
                        <th style="min-width:230px">Libro</th>
                        <th>Lector</th>
                        <th class="d-none d-md-table-cell">Prestado</th>
                        <th class="d-none d-md-table-cell">Vence</th>
                        <th class="text-center">Estado</th>
                        <th class="text-center" style="width:160px">Devolución</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($prestamos as $prestamo): ?>
                        <?php $vencido = $prestamo['estado_actual'] === 'Vencido'; ?>
                        <tr class="<?= $vencido ? 'cora-fila-vencida' : '' ?>">
                            <td>
                                <span class="cora-book-title"><?= e($prestamo['libro_titulo']) ?></span>
                                <small class="d-block text-muted"><?= e($prestamo['libro_autor']) ?></small>
                            </td>
                            <td><?= e($prestamo['lector_nombre'] . ' ' . $prestamo['lector_apellido']) ?></td>
                            <td class="d-none d-md-table-cell"><?= formato_fecha($prestamo['fecha_prestamo']) ?></td>
                            <td class="d-none d-md-table-cell">
                                <?= formato_fecha($prestamo['fecha_devolucion_esperada']) ?>
                                <?php if ($vencido): ?>
                                    <small class="d-block cora-text-peligro">
                                        <?= abs((int) $prestamo['dias_restantes']) ?> día(s) de retraso
                                    </small>
                                <?php endif; ?>
                            </td>
                            <td class="text-center"><?= badge_prestamo($prestamo['estado'], $prestamo['fecha_devolucion_esperada']) ?></td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm cora-btn-acento"
                                        data-bs-toggle="modal" data-bs-target="#modalDevolucion"
                                        data-prestamo='<?= e(json_encode([
                                            'id'       => $prestamo['id'],
                                            'libro'    => $prestamo['libro_titulo'],
                                            'lector'   => $prestamo['lector_nombre'] . ' ' . $prestamo['lector_apellido'],
                                            'prestamo' => formato_fecha($prestamo['fecha_prestamo']),
                                            'esperada' => formato_fecha($prestamo['fecha_devolucion_esperada']),
                                        ], JSON_UNESCAPED_UNICODE)) ?>'>
                                    <i class="bi bi-journal-arrow-up me-1"></i>Devolver
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="px-3 pb-3 pt-3">
            <?= paginacion($pagina, $totalPaginas, ['q' => $filtros['q']]) ?>
        </div>
    <?php endif; ?>
</div>

<!-- ═══════════ Modal: Registrar devolución (RF-11 · RN-03) ═══════════ -->
<div class="modal fade" id="modalDevolucion" tabindex="-1" aria-labelledby="tituloModalDevolucion" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content cora-modal">
            <div class="modal-header cora-modal-header cora-modal-header-acento">
                <h5 class="modal-title" id="tituloModalDevolucion">
                    <i class="bi bi-journal-arrow-up me-2"></i>Registrar devolución
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form method="post" action="<?= e(url('devoluciones/registrar')) ?>">
                <?= csrf_field() ?>
                <div class="modal-body p-4">
                    <input type="hidden" name="prestamo_id" id="dev_prestamo_id" value="">

                    <dl class="cora-dl-detalle mb-3">
                        <div><dt>Libro</dt><dd id="dev_libro">—</dd></div>
                        <div><dt>Lector</dt><dd id="dev_lector">—</dd></div>
                        <div><dt>Fecha préstamo</dt><dd id="dev_prestamo">—</dd></div>
                        <div><dt>Devolver antes</dt><dd id="dev_esperada">—</dd></div>
                    </dl>

                    <div class="mb-2">
                        <label for="dev_observaciones" class="form-label cora-form-label">Observaciones de la devolución</label>
                        <textarea class="form-control" id="dev_observaciones" name="observaciones" rows="2"
                                  maxlength="255" placeholder="Ej: Devuelto en buen estado…"></textarea>
                    </div>

                    <div class="alert cora-alert alert-info mb-0 py-2 small">
                        <i class="bi bi-info-circle-fill me-1"></i>
                        RN-03 · Al confirmar, el préstamo pasará a <b>Devuelto</b> y el ejemplar
                        volverá a estar <b>disponible</b> en el inventario. Un préstamo no puede devolverse dos veces.
                    </div>
                </div>
                <div class="modal-footer cora-modal-footer">
                    <button type="button" class="btn cora-btn-ghost-primary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn cora-btn-acento">
                        <i class="bi bi-check-lg me-1"></i>Confirmar devolución
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
