<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script src="<?= base_url('assets/plugins/popper/js/popper.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/bootstrap/js/bootstrap.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/overlayscrollbars/js/overlayscrollbars.browser.es6.min.js') ?>"></script>
<script src="<?= base_url('assets/adminlte/js/adminlte.min.js') ?>"></script>
<script src="<?= base_url('assets/js/app.js') ?>"></script>
<?php foreach (($page_scripts ?? array()) as $page_script): ?>
	<script src="<?= base_url($page_script) ?>"></script>
<?php endforeach; ?>
</body>
</html>
