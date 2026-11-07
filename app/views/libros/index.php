<?php
/** Vista · Catálogo de libros: búsqueda, filtros, paginación y acciones (módulos 4 y 6) */
?>
<!-- Encabezado + acción principal -->
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <p class="text-muted mb-0">
        <i class="bi bi-bookshelf me-1"></i>
        <?= $totalRegistros ?> libro<?= $totalRegistros === 1 ? '' : 's' ?> en el catálogo
    </p>
    <?php if ($puedeCrear): ?>
        <a href="<?= e(url('libros/crear')) ?>" class="btn cora-btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Registrar libro
        </a>
    <?php endif; ?>
</div>

<!-- Filtros de búsqueda (RF-08) -->
<div class="cora-card mb-3">
    <form method="get" action="<?= e(url('libros')) ?>" class="row g-2 align-items-end">
        <div class="col-12 col-md-5">
            <label class="form-label cora-form-label" for="q">Buscar</label>
            <div class="input-group">
                <span class="input-group-text cora-input-icon"><i class="bi bi-search"></i></span>
                <input type="text" class="form-control" id="q" name="q"
                       value="<?= e($filtros['q']) ?>" placeholder="Título, autor o ISBN…">
            </div>
        </div>
        <div class="col-6 col-md-4">
            <label class="form-label cora-form-label" for="categoria_id">Categoría</label>
            <select class="form-select" id="categoria_id" name="categoria_id">
                <option value="0">Todas las categorías</option>
                <?php foreach ($categorias as $categoria): ?>
                    <option value="<?= (int) $categoria['id'] ?>"
                        <?= (int) $filtros['categoria_id'] === (int) $categoria['id'] ? 'selected' : '' ?>>
                        <?= e($categoria['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-6 col-md-3 d-flex gap-2">
            <button type="submit" class="btn cora-btn-primary flex-fill">
                <i class="bi bi-funnel me-1"></i>Filtrar
            </button>
            <a href="<?= e(url('libros')) ?>" class="btn cora-btn-ghost-acento" title="Limpiar filtros">
                <i class="bi bi-x-lg"></i>
            </a>
        </div>
    </form>
</div>

<!-- Listado -->
<div class="cora-card">
    <?php if (!$libros): ?>
        <div class="cora-empty">
            <i class="bi bi-book"></i>
            <p>No se encontraron libros con ese criterio de búsqueda.</p>
            <a href="<?= e(url('libros')) ?>" class="btn btn-sm cora-btn-ghost-primary">Ver todo el catálogo</a>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table cora-table align-middle mb-0">
                <thead>
                    <tr>
                        <th style="min-width:220px">Título</th>
                        <th class="d-none d-md-table-cell">Autor</th>
                        <th class="d-none d-lg-table-cell">Categoría</th>
                        <th class="d-none d-lg-table-cell">ISBN</th>
                        <th class="text-center">Ejemplares</th>
                        <th class="text-center" style="width:170px">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($libros as $libro): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <?= portada_libro($libro, 'cora-thumb') ?>
                                    <div class="ms-1">
                                        <a href="<?= e(url('libros/detalle/' . $libro['id'])) ?>" class="cora-book-title">
                                            <?= e($libro['titulo']) ?>
                                        </a>
                                        <small class="d-md-none d-block text-muted"><?= e($libro['autor']) ?></small>
                                    </div>
                                </div>
                            </td>
                            <td class="d-none d-md-table-cell"><?= e($libro['autor']) ?></td>
                            <td class="d-none d-lg-table-cell"><span class="cora-chip"><?= e($libro['categoria']) ?></span></td>
                            <td class="d-none d-lg-table-cell"><code class="cora-code"><?= e($libro['isbn']) ?></code></td>
                            <td class="text-center">
                                <div class="fw-medium"><?= (int) $libro['cantidad_disponible'] ?> / <?= (int) $libro['cantidad_total'] ?></div>
                                <?= badge_disponibilidad((int) $libro['cantidad_disponible']) ?>
                            </td>
                            <td>
                                <div class="d-flex justify-content-center gap-1">
                                    <a href="<?= e(url('libros/detalle/' . $libro['id'])) ?>"
                                       class="cora-action cora-action-ver" title="Ver ficha del libro">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <?php if ($puedeCrear): ?>
                                        <a href="<?= e(url('libros/editar/' . $libro['id'])) ?>"
                                           class="cora-action cora-action-editar" title="Editar libro">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form method="post" action="<?= e(url('libros/eliminar/' . $libro['id'])) ?>"
                                              data-confirm="¿Eliminar el libro «<?= e($libro['titulo']) ?>» del catálogo? Esta acción no se puede deshacer.">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="cora-action cora-action-eliminar" title="Eliminar libro">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="px-3 pb-3 pt-3">
            <?= paginacion($pagina, $totalPaginas, ['q' => $filtros['q'], 'categoria_id' => $filtros['categoria_id']]) ?>
        </div>
    <?php endif; ?>
</div>
