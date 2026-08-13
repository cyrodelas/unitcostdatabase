<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="row g-4">
	<div class="col-12">
		<div class="card border-0 shadow-sm">
			<div class="card-body p-4 p-lg-5">
				<div class="row align-items-center g-4">
					<div class="col-lg-8">
						<span class="badge text-bg-primary mb-3">Signed in securely</span>
						<h2 class="display-6 fw-semibold">Project Nexus UCD</h2>
						<p class="lead text-body-secondary mb-0">Welcome, <?= html_escape($current_user->display_name) ?>. Authentication and the reusable AdminLTE shell are ready for the next assigned phase.</p>
					</div>
					<div class="col-lg-4 text-lg-end">
						<a class="btn btn-outline-primary" href="<?= site_url('health') ?>">
							<i class="bi bi-heart-pulse me-2" aria-hidden="true"></i>Check application health
						</a>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="col-md-4">
		<div class="card h-100">
			<div class="card-body">
				<i class="bi bi-layout-sidebar-inset fs-2 text-primary" aria-hidden="true"></i>
				<h3 class="h5 mt-3">Reusable layout</h3>
				<p class="text-body-secondary mb-0">Header, navbar, sidebar, content header, footer, and script partials are shared across pages.</p>
			</div>
		</div>
	</div>
	<div class="col-md-4">
		<div class="card h-100">
			<div class="card-body">
				<i class="bi bi-phone fs-2 text-primary" aria-hidden="true"></i>
				<h3 class="h5 mt-3">Responsive shell</h3>
				<p class="text-body-secondary mb-0">The fixed sidebar collapses at smaller breakpoints and remains keyboard accessible.</p>
			</div>
		</div>
	</div>
	<div class="col-md-4">
		<div class="card h-100">
			<div class="card-body">
				<i class="bi bi-hdd-stack fs-2 text-primary" aria-hidden="true"></i>
				<h3 class="h5 mt-3">Local assets</h3>
				<p class="text-body-secondary mb-0">Pinned AdminLTE, Bootstrap, icons, Popper, and OverlayScrollbars assets are served locally.</p>
			</div>
		</div>
	</div>
</div>
