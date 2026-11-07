<?php
/** Vista · Módulo de préstamos (módulo 8): filtros, listado y modal de registro
 *  RN-01: el modal solo ofrece libros con ejemplares disponibles */

$hoy              = date('Y-m-d');
$fechaDefecto     = date('Y-m-d', strtotime('+' . DIAS_PRESTAMO . ' days'));
$libroSeleccionado = $libroPreseleccionado ?: (int) (old('libro_id', 0));
$lectorSeleccionado = (int) (old('usuario_id', 0));
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <p class="text-muted mb-0">
        <i class="bi bi-journal-text me-1"></i>
        <?= $totalRegistros ?> préstamo<?= $totalRegistros === 1 ? '' : 's' ?> encontrado<?= $totalRegistros === 1 ? '' : 's' ?>
    </p>
    <?php if ($esStaff): ?>
        <button type="button" class="btn cora-btn-primary" data-bs-toggle="modal" data-bs-target="#modalPrestamo">
            <i class="bi bi-plus-lg me-1"></i> Nuevo préstamo
        </button>
    <?php endif; ?>
</div>

<!-- Tarjetas resumen -->
<div class="row g-3 mb-3">
    <div class="col-6 col-lg-3">
        <div class="cora-chip-card cora-chip-todos <?= $filtros['estado'] === 'Todos' ? 'activo' : '' ?>">
            <a href="<?= e(url('prestamos?estado=Todos&q=' . urlencode($filtros['q']))) ?>" class="stretched-link text-decoration-none"></a>
            <span class="cora-chip-num"><?= (int) $contadores['total'] ?></span>
            <span class="cora-chip-label"><i class="bi bi-journal-text me-1"></i>Todos</span>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="cora-chip-card cora-chip-activos <?= $filtros['estado'] === 'Activo' ? 'activo' : '' ?>">
            <a href="<?= e(url('prestamos?estado=Activo&q=' . urlencode($filtros['q']))) ?>" class="stretched-link text-decoration-none"></a>
            <span class="cora-chip-num"><?= (int) $contadores['activos'] ?></span>
            <span class="cora-chip-label"><i class="bi bi-arrow-repeat me-1"></i>Activos</span>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="cora-chip-card cora-chip-vencidos <?= $filtros['estado'] === 'Vencido' ? 'activo' : '' ?>">
            <a href="<?= e(url('prestamos?estado=Vencido&q=' . urlencode($filtros['q']))) ?>" class="stretched-link text-decoration-none"></a>
            <span class="cora-chip-num"><?= (int) $contadores['vencidos'] ?></span>
            <span class="cora-chip-label"><i class="bi bi-exclamation-triangle me-1"></i>Vencidos</span>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="cora-chip-card cora-chip-devueltos <?= $filtros['estado'] === 'Devuelto' ? 'activo' : '' ?>">
            <a href="<?= e(url('prestamos?estado=Devuelto&q=' . urlencode($filtros['q']))) ?>" class="stretched-link text-decoration-none"></a>
            <span class="cora-chip-num"><?= (int) $contadores['devueltos'] ?></span>
            <span class="cora-chip-label"><i class="bi bi-check2-circle me-1"></i>Devueltos</span>
        </div>
    </div>
</div>

<!-- Búsqueda -->
<div class="cora-card mb-3">
    <form method="get" action="<?= e(url('prestamos')) ?>" class="row g-2 align-items-end">
        <input type="hidden" name="estado" value="<?= e($filtros['estado']) ?>">
        <div class="col-12 col-md-9">
            <label class="form-label cora-form-label" for="q">Buscar</label>
            <div class="input-group">
                <span class="input-group-text cora-input-icon"><i class="bi bi-search"></i></span>
                <input type="text" class="form-control" id="q" name="q" value="<?= e($filtros['q']) ?>"
                       placeholder="Libro, autor, lector…">
            </div>
        </div>
        <div class="col-12 col-md-3 d-grid">
            <button type="submit" class="btn cora-btn-primary">
                <i class="bi bi-search me-1"></i>Buscar
            </button>
        </div>
    </form>
</div>

<!-- Listado (RF-12) -->
<div class="cora-card">
    <?php if (!$prestamos): ?>
        <div class="cora-empty">
            <i class="bi bi-journal-x"></i>
            <p>No hay préstamos que coincidan con el filtro seleccionado.</p>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table cora-table align-middle mb-0">
                <thead>
                    <tr>
                        <th style="min-width:230px">Libro</th>
                        <?php if (!$esLector): ?><th>Lector</th><?php endif; ?>
                        <th class="d-none d-md-table-cell">Préstamo</th>
                        <th class="d-none d-md-table-cell">Devolver antes</th>
                        <th class="text-center">Estado</th>
                        <th class="d-none d-lg-table-cell">Devuelto</th>
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
                            <?php if (!$esLector): ?>
                                <td><?= e($prestamo['lector_nombre'] . ' ' . $prestamo['lector_apellido']) ?></td>
                            <?php endif; ?>
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
                            <td class="d-none d-lg-table-cell"><?= formato_fecha($prestamo['fecha_devolucion_real']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="px-3 pb-3 pt-3">
            <?= paginacion($pagina, $totalPaginas, ['estado' => $filtros['estado'], 'q' => $filtros['q']]) ?>
        </div>
    <?php endif; ?>
</div>

<?php if ($esStaff): ?>
<!-- ═══════════ Modal: Nuevo préstamo (RF-10 · RN-01 · RN-02) ═══════════ -->
<div class="modal fade" id="modalPrestamo" tabindex="-1" aria-labelledby="tituloModalPrestamo" aria-hidden="true"
     <?= $abrirModal ? 'data-abrir="1"' : '' ?>>
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content cora-modal">
            <div class="modal-header cora-modal-header">
                <h5 class="modal-title" id="tituloModalPrestamo">
                    <i class="bi bi-journal-arrow-down me-2"></i>Registrar nuevo préstamo
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form method="post" action="<?= e(url('prestamos/guardar')) ?>" id="formPrestamo">
                <?= csrf_field() ?>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="mp_libro" class="form-label cora-form-label">Libro <span class="cora-req">*</span></label>
                        <select class="form-select" id="mp_libro" name="libro_id" required>
                            <option value="">Seleccione un libro…</option>
                            <?php foreach ($librosDisponibles as $libro): ?>
                                <option value="<?= (int) $libro['id'] ?>"
                                    <?= (int) $libro['id'] === $libroSeleccionado ? 'selected' : '' ?>>
                                    <?= e($libro['titulo'] . ' — ' . $libro['autor']) ?>
                                    (<?= (int) $libro['cantidad_disponible'] ?> disp.)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">
                            <i class="bi bi-info-circle me-1"></i>Solo aparecen libros con ejemplares disponibles (RN-01).
                        </small>
                    </div>

                    <div class="mb-3">
                        <label for="mp_lector" class="form-label cora-form-label">Lector <span class="cora-req">*</span></label>
                        <select class="form-select" id="mp_lector" name="usuario_id" required>
                            <option value="">Seleccione un lector…</option>
                            <?php foreach ($lectoresActivos as $lector): ?>
                                <option value="<?= (int) $lector['id'] ?>"
                                    <?= (int) $lector['id'] === $lectorSeleccionado ? 'selected' : '' ?>>
                                    <?= e($lector['nombre'] . ' ' . $lector['apellido'] . ' (' . $lector['rol'] . ')') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">RN-02 · Todo préstamo se asocia a un lector activo.</small>
                    </div>

                    <div class="row g-3">
                        <div class="col-6">
                            <label for="mp_fecha_prestamo" class="form-label cora-form-label">Fecha préstamo</label>
                            <input type="date" class="form-control" id="mp_fecha_prestamo" name="fecha_prestamo"
                                   value="<?= e(old('fecha_prestamo', $hoy)) ?>" max="<?= $hoy ?>" required>
                        </div>
                        <div class="col-6">
                            <label for="mp_fecha_devolucion" class="form-label cora-form-label">Devolver antes</label>
                            <input type="date" class="form-control" id="mp_fecha_devolucion"
                                   name="fecha_devolucion_esperada" value="<?= e(old('fecha_devolucion_esperada', $fechaDefecto)) ?>"
                                   min="<?= $hoy ?>" required>
                        </div>
                    </div>

                    <div class="mt-3">
                        <label for="mp_observaciones" class="form-label cora-form-label">Observaciones</label>
                        <textarea class="form-control" id="mp_observaciones" name="observaciones" rows="2"
                                  maxlength="255" placeholder="Opcional…"><?= e(old('observaciones')) ?></textarea>
                    </div>

                    <div class="alert cora-alert alert-warning mt-3 mb-0 py-2 small">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i>
                        La disponibilidad se descuenta automáticamente al registrar el préstamo.
                    </div>
                </div>
                <div class="modal-footer cora-modal-footer">
                    <button type="button" class="btn cora-btn-ghost-primary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn cora-btn-primary">
                        <i class="bi bi-check-lg me-1"></i>Registrar préstamo
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>
