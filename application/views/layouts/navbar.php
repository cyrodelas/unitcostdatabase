<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<nav class="app-header navbar navbar-expand bg-body" aria-label="Primary navigation">
	<div class="container-fluid">
		<ul class="navbar-nav">
			<li class="nav-item">
				<button class="nav-link btn btn-link" type="button" data-lte-toggle="sidebar" aria-label="Toggle sidebar">
					<i class="bi bi-list" aria-hidden="true"></i>
				</button>
			</li>
			<li class="nav-item d-none d-md-block">
				<a class="nav-link" href="<?= site_url() ?>">Project Nexus UCD</a>
			</li>
		</ul>
		<ul class="navbar-nav ms-auto align-items-center">
			<li class="nav-item">
				<button id="theme-toggle" class="nav-link btn btn-link" type="button" aria-label="Toggle color theme">
					<i class="bi bi-moon-stars" aria-hidden="true"></i>
				</button>
			</li>
			<li class="nav-item dropdown">
				<button class="nav-link dropdown-toggle btn btn-link" type="button" data-bs-toggle="dropdown" aria-expanded="false">
					<i class="bi bi-person-circle me-1" aria-hidden="true"></i>
					<span class="d-none d-sm-inline"><?= html_escape($current_user->display_name ?? 'Account') ?></span>
				</button>
				<ul class="dropdown-menu dropdown-menu-end">
					<?php if (!empty($current_roles)): ?><li><h6 class="dropdown-header"><?= html_escape(implode(', ', array_map(static function ($role) { return $role->role_name; }, $current_roles))) ?></h6></li><?php endif; ?>
					<li><a class="dropdown-item" href="<?= site_url('account') ?>"><i class="bi bi-person me-2" aria-hidden="true"></i>My Account</a></li>
					<li><a class="dropdown-item" href="<?= site_url('account/password') ?>"><i class="bi bi-key me-2" aria-hidden="true"></i>Change Password</a></li>
					<li><hr class="dropdown-divider"></li>
					<li>
						<?= form_open('logout', array('class' => 'px-2')) ?>
							<button class="dropdown-item" type="submit"><i class="bi bi-box-arrow-right me-2" aria-hidden="true"></i>Sign Out</button>
						<?= form_close() ?>
					</li>
				</ul>
			</li>
		</ul>
	</div>
</nav>
