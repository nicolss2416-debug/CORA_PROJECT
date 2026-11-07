<?php
/** Vista · Formulario de libro (crear / editar) — RF-06 y RF-09
 *  $modo: 'crear' | 'editar'   ·   $libro: array o null */

$esEditar  = $modo === 'editar';
$accion    = $esEditar ? url('libros/actualizar/' . $libro['id']) : url('libros/guardar');
$valor     = fn (string $campo, $defecto = '') => old($campo, $esEditar ? ($libro[$campo] ?? $defecto) : $defecto);
$valorInt  = fn (string $campo, int $defecto = 0) => (int) old($campo, $esEditar ? ($libro[$campo] ?? $defecto) : $defecto);
?>
<div class="row justify-content-center">
    <div class="col-12 col-xl-9">
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb cora-breadcrumb">
                <li class="breadcrumb-item"><a href="<?= e(url('libros')) ?>">Libros</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= $esEditar ? 'Editar' : 'Registrar' ?></li>
            </ol>
        </nav>

        <form method="post" action="<?= e($accion) ?>" novalidate>
            <?= csrf_field() ?>
            <div class="cora-card">
                <div class="cora-card-header">
                    <h5>
                        <i class="bi <?= $esEditar ? 'bi-pencil-square' : 'bi-plus-circle' ?> me-2"></i>
                        <?= $esEditar ? 'Editar información del libro' : 'Registrar nuevo libro' ?>
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="titulo" class="form-label cora-form-label">Título <span class="cora-req">*</span></label>
                            <input type="text" class="form-control" id="titulo" name="titulo" required
                                   value="<?= e($valor('titulo')) ?>" placeholder="Ej: Cien años de soledad" maxlength="150">
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="autor" class="form-label cora-form-label">Autor <span class="cora-req">*</span></label>
                            <input type="text" class="form-control" id="autor" name="autor" required
                                   value="<?= e($valor('autor')) ?>" placeholder="Ej: Gabriel García Márquez" maxlength="120">
                        </div>

                        <div class="col-6 col-md-6">
                            <label for="categoria_id" class="form-label cora-form-label">Categoría <span class="cora-req">*</span></label>
                            <select class="form-select" id="categoria_id" name="categoria_id" required>
                                <option value="">Seleccione…</option>
                                <?php foreach ($categorias as $categoria): ?>
                                    <option value="<?= (int) $categoria['id'] ?>"
                                        <?= $valorInt('categoria_id') === (int) $categoria['id'] ? 'selected' : '' ?>>
                                        <?= e($categoria['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-6 col-md-4">
                            <label for="isbn" class="form-label cora-form-label">ISBN <span class="cora-req">*</span></label>
                            <input type="text" class="form-control" id="isbn" name="isbn" required
                                   value="<?= e($valor('isbn')) ?>" placeholder="978-0-13-235088-4" maxlength="20">
                        </div>

                        <div class="col-6 col-md-4">
                            <label for="anio_publicacion" class="form-label cora-form-label">Año de publicación</label>
                            <input type="number" class="form-control" id="anio_publicacion" name="anio_publicacion" min="1000"
                                   max="<?= (int) date('Y') ?>" value="<?= e($valor('anio_publicacion')) ?>" placeholder="<?= (int) date('Y') - 5 ?>">
                        </div>

                        <div class="col-6 col-md-4">
                            <label for="cantidad_total" class="form-label cora-form-label">Cantidad de ejemplares <span class="cora-req">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text cora-input-icon"><i class="bi bi-stack"></i></span>
                                <input type="number" class="form-control" id="cantidad_total" name="cantidad_total"
                                       min="1" max="999" required value="<?= $valorInt('cantidad_total', 1) ?: 1 ?>">
                            </div>
                            <?php if ($esEditar): ?>
                                <small class="text-muted">
                                    Prestados actualmente: <?= (int) $libro['cantidad_total'] - (int) $libro['cantidad_disponible'] ?>
                                </small>
                            <?php endif; ?>
                        </div>

                        <div class="col-12">
                            <label for="editorial" class="form-label cora-form-label">Editorial</label>
                            <input type="text" class="form-control" id="editorial" name="editorial"
                                   value="<?= e($valor('editorial')) ?>" placeholder="Ej: Sudamericana" maxlength="100">
                        </div>

                        <div class="col-12">
                            <label for="sinopsis" class="form-label cora-form-label">Sinopsis</label>
                            <textarea class="form-control" id="sinopsis" name="sinopsis" rows="4"
                                      placeholder="Breve resumen del contenido del libro…"><?= e($valor('sinopsis')) ?></textarea>
                        </div>
                    </div>
                </div>
                <div class="cora-card-footer">
                    <a href="<?= e(url('libros')) ?>" class="btn cora-btn-ghost-primary">
                        <i class="bi bi-arrow-left me-1"></i>Cancelar
                    </a>
                    <button type="submit" class="btn cora-btn-primary">
                        <i class="bi bi-check-lg me-1"></i><?= $esEditar ? 'Guardar cambios' : 'Registrar libro' ?>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
