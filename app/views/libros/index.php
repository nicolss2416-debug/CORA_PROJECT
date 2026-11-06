<?php $puedeGestionar = tiene_rol('Administrador', 'Bibliotecario'); ?>
<div class="d-flex flex-wrap gap-2 align-items-center mb-3">
    <h1 class="h4 mb-0"><?= tiene_rol('Lector') ? 'Catálogo' : 'Libros' ?></h1>
    <?php if ($puedeGestionar): ?>
        <a href="<?= url('libros/crear') ?>" class="btn btn-cora ms-auto"><i class="bi bi-plus-lg"></i> Nuevo libro</a>
    <?php endif; ?>
</div>

<form class="row g-2 mb-3" method="get" action="<?= BASE_URL ?>index.php">
    <input type="hidden" name="url" value="libros">
    <div class="col-12 col-md-8">
        <input type="search" name="q" value="<?= e($q) ?>" class="form-control" placeholder="Buscar por título, autor o categoría...">
    </div>
    <div class="col-8 col-md-3">
        <select name="categoria" class="form-select" aria-label="Filtrar por categoría">
            <option value="">Todas las categorías</option>
            <?php foreach ($categorias as $c): ?>
                <option <?= $categoria === $c ? 'selected' : '' ?>><?= e($c) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-4 col-md-1 d-grid"><button class="btn btn-outline-cora">Buscar</button></div>
</form>

<?php if (!$libros): ?>
    <div class="cora-card text-center text-muted py-5">
        <i class="bi bi-search fs-1"></i>
        <p class="mb-0 mt-2">No se encontraron libros con ese criterio.</p>
    </div>
<?php else: ?>
<div class="cora-card p-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 tabla-libros">
            <thead>
            <tr>
                <th>Portada</th><th>Título</th><th class="d-none d-md-table-cell">Autor</th>
                <th class="d-none d-lg-table-cell">Categoría</th><th>Disponibilidad</th>
                <th class="text-end">Acciones</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($libros as $l): ?>
                <tr>
                    <td><?php $libro = $l; $mini = true; require APP_PATH . '/views/libros/_portada.php'; ?></td>
                    <td>
                        <a class="fw-medium text-decoration-none" href="<?= url('libros/ver/' . $l['id']) ?>"><?= e($l['titulo']) ?></a>
                        <div class="small text-muted d-md-none"><?= e($l['autor']) ?></div>
                    </td>
                    <td class="d-none d-md-table-cell"><?= e($l['autor']) ?></td>
                    <td class="d-none d-lg-table-cell"><?= e($l['categoria']) ?></td>
                    <td>
                        <?php if ($l['disponibles'] > 0): ?>
                            <span class="badge badge-ok">Disponible (<?= $l['disponibles'] ?>)</span>
                        <?php else: ?>
                            <span class="badge badge-bad">Prestado</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-end text-nowrap">
                        <a href="<?= url('libros/ver/' . $l['id']) ?>" class="btn btn-sm btn-icon" title="Ver detalle"><i class="bi bi-eye"></i></a>
                        <?php if ($puedeGestionar): ?>
                            <a href="<?= url('libros/editar/' . $l['id']) ?>" class="btn btn-sm btn-icon" title="Editar"><i class="bi bi-pencil"></i></a>
                        <?php endif; ?>
                        <?php if (tiene_rol('Administrador')): ?>
                            <form action="<?= url('libros/eliminar/' . $l['id']) ?>" method="post" class="d-inline form-eliminar" data-nombre="<?= e($l['titulo']) ?>">
                                <?= csrf_campo() ?>
                                <button class="btn btn-sm btn-icon text-danger" title="Eliminar"><i class="bi bi-trash"></i></button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<p class="small text-muted mt-2"><?= count($libros) ?> libro(s) encontrado(s)</p>
<?php endif; ?>