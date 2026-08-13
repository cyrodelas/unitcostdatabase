<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php if ($this->session->flashdata('account_success')): ?>
	<div class="alert alert-success" role="alert"><?= html_escape($this->session->flashdata('account_success')) ?></div>
<?php endif; ?>
<div class="card shadow-sm">
	<div class="card-header"><h2 class="card-title mb-0">Account Details</h2></div>
	<div class="card-body">
		<dl class="row mb-0">
			<dt class="col-sm-3">Display name</dt><dd class="col-sm-9"><?= html_escape($current_user->display_name) ?></dd>
			<dt class="col-sm-3">Username</dt><dd class="col-sm-9"><?= html_escape($current_user->username) ?></dd>
			<dt class="col-sm-3">Email</dt><dd class="col-sm-9"><?= html_escape($current_user->email ?: 'Not set') ?></dd>
			<dt class="col-sm-3">Last login</dt><dd class="col-sm-9"><?= html_escape($current_user->last_login_at ?: 'Current first login') ?></dd>
		</dl>
	</div>
	<div class="card-footer">
		<a class="btn btn-primary" href="<?= site_url('account/password') ?>"><i class="bi bi-key me-2" aria-hidden="true"></i>Change Password</a>
	</div>
</div>
