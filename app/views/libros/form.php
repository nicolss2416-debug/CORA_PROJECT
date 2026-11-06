<?php
// Valor del campo: primero lo que escribió antes (si hubo error), luego el del libro, luego vacío
$v = fn(string $c) => isset($_SESSION['viejo']) ? viejo($c) : e($libro[$c] ?? '');
?>
<div class="d-flex align-items-center mb-3">
    <a href="<?= url('libros') ?>" class="btn btn-icon me-2" aria-label="Volver"><i class="bi bi-arrow-left"></i></a>
    <h1 class="h4 mb-0"><?= e($titulo) ?></h1>
</div>

<div class="cora-card">
    <form action="<?= url($accion) ?>" method="post" class="needs-validation" novalidate>
        <?= csrf_campo() ?>
        <div class="row g-3">
            <div class="col-md-6">
                <label for="titulo" class="form-label">Título <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="titulo" name="titulo" value="<?= $v('titulo') ?>" placeholder="Ingrese el título" required minlength="2" maxlength="150">
                <div class="invalid-feedback">El título es obligatorio.</div>
            </div>
            <div class="col-md-6">
                <label for="autor" class="form-label">Autor <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="autor" name="autor" value="<?= $v('autor') ?>" placeholder="Ingrese el autor" required minlength="2" maxlength="120">
                <div class="invalid-feedback">El autor es obligatorio.</div>
            </div>
            <div class="col-md-6">
                <label for="categoria" class="form-label">Categoría <span class="text-danger">*</span></label>
                <select class="form-select" id="categoria" name="categoria" required>
                    <option value="">Seleccione categoría</option>
                    <?php foreach ($categorias as $c): ?>
                        <option <?= $v('categoria') === e($c) ? 'selected' : '' ?>><?= e($c) ?></option>
                    <?php endforeach; ?>
                </select>
                <div class="invalid-feedback">Selecciona una categoría.</div>
            </div>
            <div class="col-md-6">
                <label for="isbn" class="form-label">ISBN <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="isbn" name="isbn" value="<?= $v('isbn') ?>" placeholder="Ingrese el ISBN" required pattern="[0-9\-Xx]{10,17}">
                <div class="invalid-feedback">Ingresa un ISBN válido (10 a 17 caracteres, números y guiones).</div>
            </div>
            <div class="col-md-6">
                <label for="ejemplares" class="form-label">Cantidad de ejemplares <span class="text-danger">*</span></label>
                <input type="number" class="form-control" id="ejemplares" name="ejemplares" value="<?= $v('ejemplares') ?>" placeholder="Ingrese la cantidad" required min="1" max="999">
                <div class="invalid-feedback">Ingresa una cantidad mayor a 0.</div>
            </div>
            <div class="col-12">
                <label for="descripcion" class="form-label">Descripción</label>
                <textarea class="form-control" id="descripcion" name="descripcion" rows="3" maxlength="500" placeholder="Breve descripción del libro"><?= $v('descripcion') ?></textarea>
            </div>
        </div>
        <div class="d-flex gap-2 mt-4">
            <button type="submit" class="btn btn-cora">Guardar</button>
            <a href="<?= url('libros') ?>" class="btn btn-outline-cora">Cancelar</a>
        </div>
    </form>
</div>