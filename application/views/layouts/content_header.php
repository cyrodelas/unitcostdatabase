<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<main class="app-main">
	<div class="app-content-header">
		<div class="container-fluid">
			<div class="row align-items-center">
				<div class="col-sm-7">
					<h1 class="mb-0 h3"><?= html_escape($page_title ?? 'Project Nexus UCD') ?></h1>
					<?php if (!empty($page_subtitle)): ?>
						<p class="text-body-secondary mb-0"><?= html_escape($page_subtitle) ?></p>
					<?php endif; ?>
				</div>
				<div class="col-sm-5">
					<ol class="breadcrumb float-sm-end mb-0">
						<?php foreach (($breadcrumbs ?? array()) as $index => $breadcrumb): ?>
							<?php $is_last = $index === count($breadcrumbs) - 1; ?>
							<li class="breadcrumb-item<?= $is_last ? ' active' : '' ?>"<?= $is_last ? ' aria-current="page"' : '' ?>>
								<?php if (!$is_last && !empty($breadcrumb['url'])): ?>
									<a href="<?= html_escape($breadcrumb['url']) ?>"><?= html_escape($breadcrumb['label']) ?></a>
								<?php else: ?>
									<?= html_escape($breadcrumb['label']) ?>
								<?php endif; ?>
							</li>
						<?php endforeach; ?>
					</ol>
				</div>
			</div>
		</div>
	</div>
	<div class="app-content">
		<div class="container-fluid">
