<div class="d-flex align-items-center mb-3">
    <a href="<?= url('libros') ?>" class="btn btn-icon me-2" aria-label="Volver"><i class="bi bi-arrow-left"></i></a>
    <h1 class="h4 mb-0">Detalle del libro</h1>
</div>

<div class="cora-card">
    <div class="row g-4">
        <div class="col-md-4 col-lg-3 text-center">
            <?php $mini = false; require APP_PATH . '/views/libros/_portada.php'; ?>
        </div>
        <div class="col-md-8 col-lg-9">
            <h2 class="h4"><?= e($libro['titulo']) ?></h2>
            <p class="text-muted"><?= e($libro['autor']) ?></p>
            <dl class="row small">
                <dt class="col-sm-3">ISBN</dt><dd class="col-sm-9"><?= e($libro['isbn']) ?></dd>
                <dt class="col-sm-3">Categoría</dt><dd class="col-sm-9"><?= e($libro['categoria']) ?></dd>
                <dt class="col-sm-3">Ejemplares</dt><dd class="col-sm-9"><?= $libro['ejemplares'] ?> en total · <?= $libro['disponibles'] ?> disponibles</dd>
            </dl>
            <p><?= e($libro['descripcion']) ?></p>

            <?php if ($libro['disponibles'] > 0): ?>
                <span class="badge badge-ok mb-3">Disponible</span>
            <?php else: ?>
                <span class="badge badge-bad mb-3">Prestado</span>
            <?php endif; ?>

            <div class="d-flex flex-wrap gap-2">
                <?php if (tiene_rol('Administrador', 'Bibliotecario')): ?>
                    <a href="<?= url('prestamos&libro=' . $libro['id']) ?>"
                       class="btn btn-cora <?= $libro['disponibles'] < 1 ? 'disabled' : '' ?>"
                       <?= $libro['disponibles'] < 1 ? 'aria-disabled="true"' : '' ?>>Prestar</a>
                    <a href="<?= url('libros/editar/' . $libro['id']) ?>" class="btn btn-outline-cora">Editar</a>
                <?php endif; ?>
                <a href="<?= url('libros') ?>" class="btn btn-outline-cora">Ver en catálogo</a>
            </div>
        </div>
    </div>
</div>