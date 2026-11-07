<?php
/** Layout · Pie de página y scripts globales */
?>
</div><!-- /.cora-content -->
</main>

<footer class="cora-footer no-print">
    <span><?= e(APP_NAME) ?> © <?= date('Y') ?> · Proyecto académico SENA – ADSO</span>
    <span class="d-none d-md-inline">Desarrollado con PHP 8 · MySQL · Bootstrap 5 · MVC</span>
</footer>

<!-- Bootstrap 5 (bundle con Popper) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- Chart.js para los gráficos del dashboard y reportes -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<!-- Comportamientos de la interfaz CORA -->
<script src="<?= e(asset('js/app.js')) ?>"></script>
</body>
</html>
