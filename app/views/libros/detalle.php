<?php
/** Vista · Ficha técnica del libro (módulo 7): portada, sinopsis y préstamo directo */

$disponible = (int) $libro['cantidad_disponible'];
$prestados  = (int) $libro['cantidad_total'] - $disponible;
?>
<div class="row justify-content-center">
    <div class="col-12 col-xxl-10">
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb cora-breadcrumb">
                <li class="breadcrumb-item"><a href="<?= e(url('libros')) ?>">Libros</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= e($libro['titulo']) ?></li>
            </ol>
        </nav>

        <div class="cora-card overflow-hidden">
            <div class="row g-0">
                <!-- Portada -->
                <div class="col-12 col-md-4 cora-detalle-portada">
                    <?= portada_libro($libro, 'cora-portada-detalle') ?>
                    <span class="cora-chip mt-3">
                        <i class="bi bi-tag me-1"></i><?= e($libro['categoria']) ?>
                    </span>
                </div>

                <!-- Ficha técnica -->
                <div class="col-12 col-md-8">
                    <div class="p-4">
                        <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
                            <div>
                                <h3 class="cora-detalle-titulo"><?= e($libro['titulo']) ?></h3>
                                <p class="text-muted mb-2">
                                    <i class="bi bi-person me-1"></i><?= e($libro['autor']) ?>
                                </p>
                            </div>
                            <?= badge_disponibilidad($disponible) ?>
                        </div>

                        <dl class="row cora-detalle-meta mb-4">
                            <div class="col-6 col-sm-3">
                                <dt>ISBN</dt>
                                <dd><code class="cora-code"><?= e($libro['isbn']) ?></code></dd>
                            </div>
                            <div class="col-6 col-sm-3">
                                <dt>Editorial</dt>
                                <dd><?= e($libro['editorial'] ?: '—') ?></dd>
                            </div>
                            <div class="col-6 col-sm-3">
                                <dt>Año</dt>
                                <dd><?= $libro['anio_publicacion'] ? (int) $libro['anio_publicacion'] : '—' ?></dd>
                            </div>
                            <div class="col-6 col-sm-3">
                                <dt>Ejemplares</dt>
                                <dd><?= $disponible ?> de <?= (int) $libro['cantidad_total'] ?> disponibles<br>
                                    <small class="text-muted"><?= $prestados ?> prestado<?= $prestados === 1 ? '' : 's' ?></small></dd>
                            </div>
                        </dl>

                        <!-- Préstamo directo (módulo 7) -->
                        <?php if ($esLector): ?>
                            <?php if ($disponible > 0): ?>
                                <form method="post" action="<?= e(url('prestamos/directo/' . $libro['id'])) ?>"
                                      data-confirm="¿Solicitar el préstamo de «<?= e($libro['titulo']) ?>»? Tendrá <?= DIAS_PRESTAMO ?> días para devolverlo.">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn cora-btn-primary">
                                        <i class="bi bi-journal-arrow-down me-1"></i>
                                        Solicitar préstamo (<?= DIAS_PRESTAMO ?> días)
                                    </button>
                                </form>
                            <?php else: ?>
                                <div class="alert cora-alert alert-warning mb-0" role="alert">
                                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                    RN-01 · No hay ejemplares disponibles de este libro en este momento.
                                </div>
                            <?php endif; ?>
                        <?php elseif ($puedePrestar): ?>
                            <div class="d-flex flex-wrap gap-2">
                                <?php if ($disponible > 0): ?>
                                    <a href="<?= e(url('prestamos?nuevo=1&libro_id=' . $libro['id'])) ?>" class="btn cora-btn-primary">
                                        <i class="bi bi-journal-arrow-down me-1"></i> Registrar préstamo
                                    </a>
                                <?php else: ?>
                                    <button class="btn cora-btn-primary" disabled>
                                        <i class="bi bi-slash-circle me-1"></i> Sin ejemplares (RN-01)
                                    </button>
                                <?php endif; ?>
                                <a href="<?= e(url('libros/editar/' . $libro['id'])) ?>" class="btn cora-btn-acento">
                                    <i class="bi bi-pencil me-1"></i> Editar
                                </a>
                            </div>
                        <?php else: ?>
                            <a href="<?= e(url('libros')) ?>" class="btn cora-btn-ghost-primary">
                                <i class="bi bi-arrow-left me-1"></i> Volver al catálogo
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Sinopsis -->
            <?php if (!empty($libro['sinopsis'])): ?>
                <div class="cora-detalle-sinopsis">
                    <h6 class="cora-h6"><i class="bi bi-text-paragraph me-2"></i>Sinopsis</h6>
                    <p class="mb-0"><?= nl2br(e($libro['sinopsis'])) ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
