<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="row g-3">
	<?php foreach ($entities as $type => $entity): ?>
		<div class="col-md-6 col-xl-4">
			<a class="card reference-card h-100 text-decoration-none shadow-sm" href="<?= site_url('references/'.$type) ?>">
				<div class="card-body d-flex align-items-center gap-3">
					<span class="reference-card-icon"><i class="bi bi-tags" aria-hidden="true"></i></span>
					<div><h2 class="h6 text-body mb-0"><?= html_escape($entity['title']) ?></h2></div>
				</div>
			</a>
		</div>
	<?php endforeach; ?>
</div>
