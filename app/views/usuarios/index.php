<?php
/** Vista · Gestión de usuarios: búsqueda, filtros y control de estados (módulo 5) */
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <p class="text-muted mb-0">
        <i class="bi bi-people me-1"></i>
        <?= $totalRegistros ?> usuario<?= $totalRegistros === 1 ? '' : 's' ?> registrado<?= $totalRegistros === 1 ? '' : 's' ?>
    </p>
    <?php if ($esAdmin): ?>
        <a href="<?= e(url('usuarios/crear')) ?>" class="btn cora-btn-primary">
            <i class="bi bi-person-plus me-1"></i> Registrar usuario
        </a>
    <?php endif; ?>
</div>

<!-- Filtros (RF-04) -->
<div class="cora-card mb-3">
    <form method="get" action="<?= e(url('usuarios')) ?>" class="row g-2 align-items-end">
        <div class="col-12 col-md-5">
            <label class="form-label cora-form-label" for="q">Buscar</label>
            <div class="input-group">
                <span class="input-group-text cora-input-icon"><i class="bi bi-search"></i></span>
                <input type="text" class="form-control" id="q" name="q"
                       value="<?= e($filtros['q']) ?>" placeholder="Nombre, apellido o correo…">
            </div>
        </div>
        <div class="col-6 col-md-3">
            <label class="form-label cora-form-label" for="rol">Rol</label>
            <select class="form-select" id="rol" name="rol">
                <option value="">Todos</option>
                <?php foreach (['Administrador', 'Bibliotecario', 'Lector'] as $rolOpcion): ?>
                    <option value="<?= $rolOpcion ?>" <?= $filtros['rol'] === $rolOpcion ? 'selected' : '' ?>>
                        <?= $rolOpcion ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-6 col-md-2">
            <label class="form-label cora-form-label" for="estado">Estado</label>
            <select class="form-select" id="estado" name="estado">
                <option value="">Todos</option>
                <option value="Activo"   <?= $filtros['estado'] === 'Activo'   ? 'selected' : '' ?>>Activo</option>
                <option value="Inactivo" <?= $filtros['estado'] === 'Inactivo' ? 'selected' : '' ?>>Inactivo</option>
            </select>
        </div>
        <div class="col-12 col-md-2 d-flex gap-2">
            <button type="submit" class="btn cora-btn-primary flex-fill">
                <i class="bi bi-funnel me-1"></i>Filtrar
            </button>
            <a href="<?= e(url('usuarios')) ?>" class="btn cora-btn-ghost-acento" title="Limpiar filtros">
                <i class="bi bi-x-lg"></i>
            </a>
        </div>
    </form>
</div>

<!-- Listado -->
<div class="cora-card">
    <?php if (!$usuarios): ?>
        <div class="cora-empty">
            <i class="bi bi-people"></i>
            <p>No se encontraron usuarios con ese criterio.</p>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table cora-table align-middle mb-0">
                <thead>
                    <tr>
                        <th style="min-width:220px">Usuario</th>
                        <th class="d-none d-md-table-cell">Contacto</th>
                        <th class="text-center">Rol</th>
                        <th class="text-center">Estado</th>
                        <th class="d-none d-lg-table-cell">Registro</th>
                        <?php if ($esAdmin): ?><th class="text-center" style="width:170px">Acciones</th><?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $usuario): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="cora-avatar"><?= e(iniciales($usuario['nombre'], $usuario['apellido'])) ?></span>
                                    <div>
                                        <span class="cora-book-title"><?= e($usuario['nombre'] . ' ' . $usuario['apellido']) ?></span>
                                        <small class="d-block text-muted d-md-none"><?= e($usuario['email']) ?></small>
                                    </div>
                                </div>
                            </td>
                            <td class="d-none d-md-table-cell">
                                <?= e($usuario['email']) ?><br>
                                <small class="text-muted"><?= e($usuario['telefono'] ?: 'Sin teléfono') ?></small>
                            </td>
                            <td class="text-center"><?= badge_rol($usuario['rol']) ?></td>
                            <td class="text-center"><?= badge_estado_usuario($usuario['estado']) ?></td>
                            <td class="d-none d-lg-table-cell"><?= formato_fecha($usuario['fecha_registro']) ?></td>
                            <?php if ($esAdmin): ?>
                                <td>
                                    <div class="d-flex justify-content-center gap-1">
                                        <a href="<?= e(url('usuarios/editar/' . $usuario['id'])) ?>"
                                           class="cora-action cora-action-editar" title="Editar usuario">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form method="post" action="<?= e(url('usuarios/estado/' . $usuario['id'])) ?>"
                                              data-confirm="¿Cambiar el estado de «<?= e($usuario['nombre'] . ' ' . $usuario['apellido']) ?>» a <?= $usuario['estado'] === 'Activo' ? 'Inactivo' : 'Activo' ?>?">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="cora-action <?= $usuario['estado'] === 'Activo' ? 'cora-action-advertencia' : 'cora-action-ver' ?>"
                                                    title="<?= $usuario['estado'] === 'Activo' ? 'Desactivar' : 'Activar' ?>">
                                                <i class="bi <?= $usuario['estado'] === 'Activo' ? 'bi-pause-circle' : 'bi-play-circle' ?>"></i>
                                            </button>
                                        </form>
                                        <form method="post" action="<?= e(url('usuarios/eliminar/' . $usuario['id'])) ?>"
                                              data-confirm="¿Eliminar definitivamente a «<?= e($usuario['nombre'] . ' ' . $usuario['apellido']) ?>» del sistema?">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="cora-action cora-action-eliminar" title="Eliminar usuario">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="px-3 pb-3 pt-3">
            <?= paginacion($pagina, $totalPaginas, $filtros) ?>
        </div>
    <?php endif; ?>
</div>
