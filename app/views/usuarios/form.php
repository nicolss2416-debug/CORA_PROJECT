<?php
/** Vista · Formulario de usuario (crear / editar) — RF-03 y RF-05 */

$esEditar = $modo === 'editar';
$accion   = $esEditar ? url('usuarios/actualizar/' . $usuario['id']) : url('usuarios/guardar');
$valor    = fn (string $campo, $defecto = '') => old($campo, $esEditar ? ($usuario[$campo] ?? $defecto) : $defecto);
?>
<div class="row justify-content-center">
    <div class="col-12 col-xl-8">
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb cora-breadcrumb">
                <li class="breadcrumb-item"><a href="<?= e(url('usuarios')) ?>">Usuarios</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= $esEditar ? 'Editar' : 'Registrar' ?></li>
            </ol>
        </nav>

        <form method="post" action="<?= e($accion) ?>" novalidate>
            <?= csrf_field() ?>
            <div class="cora-card">
                <div class="cora-card-header">
                    <h5>
                        <i class="bi <?= $esEditar ? 'bi-person-gear' : 'bi-person-plus' ?> me-2"></i>
                        <?= $esEditar ? 'Editar usuario · ' . e($usuario['nombre'] . ' ' . $usuario['apellido']) : 'Registrar nuevo usuario' ?>
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label for="nombre" class="form-label cora-form-label">Nombre <span class="cora-req">*</span></label>
                            <input type="text" class="form-control" id="nombre" name="nombre" required
                                   value="<?= e($valor('nombre')) ?>" placeholder="Ej: Laura Fernanda" maxlength="80">
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="apellido" class="form-label cora-form-label">Apellido <span class="cora-req">*</span></label>
                            <input type="text" class="form-control" id="apellido" name="apellido" required
                                   value="<?= e($valor('apellido')) ?>" placeholder="Ej: Ruiz" maxlength="80">
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="email" class="form-label cora-form-label">Correo electrónico <span class="cora-req">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text cora-input-icon"><i class="bi bi-envelope"></i></span>
                                <input type="email" class="form-control" id="email" name="email" required
                                       value="<?= e($valor('email')) ?>" placeholder="usuario@cora.com" maxlength="120">
                            </div>
                        </div>

                        <div class="col-6 col-md-6">
                            <label for="telefono" class="form-label cora-form-label">Teléfono</label>
                            <div class="input-group">
                                <span class="input-group-text cora-input-icon"><i class="bi bi-telephone"></i></span>
                                <input type="tel" class="form-control" id="telefono" name="telefono"
                                       value="<?= e($valor('telefono')) ?>" placeholder="3104567890" maxlength="20">
                            </div>
                        </div>

                        <div class="col-6 col-md-4">
                            <label for="rol" class="form-label cora-form-label">Rol <span class="cora-req">*</span></label>
                            <select class="form-select" id="rol" name="rol" required>
                                <?php foreach (['Administrador' => 'Administrador', 'Bibliotecario' => 'Bibliotecario', 'Lector' => 'Lector'] as $rolOpcion): ?>
                                    <option value="<?= $rolOpcion ?>" <?= $valor('rol', 'Lector') === $rolOpcion ? 'selected' : '' ?>>
                                        <?= $rolOpcion ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-6 col-md-4">
                            <label for="estado" class="form-label cora-form-label">Estado</label>
                            <select class="form-select" id="estado" name="estado">
                                <option value="Activo"   <?= $valor('estado', 'Activo') === 'Activo'   ? 'selected' : '' ?>>Activo</option>
                                <option value="Inactivo" <?= $valor('estado', 'Activo') === 'Inactivo' ? 'selected' : '' ?>>Inactivo</option>
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="password" class="form-label cora-form-label">
                                Contraseña <?= $esEditar ? '' : '<span class="cora-req">*</span>' ?>
                            </label>
                            <input type="password" class="form-control" id="password" name="password"
                                   autocomplete="new-password" <?= $esEditar ? '' : 'required' ?>>
                            <?php if ($esEditar): ?>
                                <small class="text-muted">Déjela vacía para conservar la contraseña actual.</small>
                            <?php else: ?>
                                <small class="text-muted">Mínimo 6 caracteres. Se almacena cifrada (RNF-02).</small>
                            <?php endif; ?>
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="password_confirm" class="form-label cora-form-label">
                                Confirmar contraseña <?= $esEditar ? '' : '<span class="cora-req">*</span>' ?>
                            </label>
                            <input type="password" class="form-control" id="password_confirm" name="password_confirm"
                                   autocomplete="new-password" <?= $esEditar ? '' : 'required' ?>>
                        </div>
                    </div>
                </div>
                <div class="cora-card-footer">
                    <a href="<?= e(url('usuarios')) ?>" class="btn cora-btn-ghost-primary">
                        <i class="bi bi-arrow-left me-1"></i>Cancelar
                    </a>
                    <button type="submit" class="btn cora-btn-primary">
                        <i class="bi bi-check-lg me-1"></i><?= $esEditar ? 'Guardar cambios' : 'Registrar usuario' ?>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
