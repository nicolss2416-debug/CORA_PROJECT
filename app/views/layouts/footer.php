</div><!-- /.cora-app -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= asset('js/app.js') ?>"></script>
<?php foreach (($extraJs ?? []) as $js): ?>
<script src="<?= str_starts_with($js, 'http') ? e($js) : BASE_URL . e($js) ?>"></script>
<?php endforeach; ?>
</body>
</html>