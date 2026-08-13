<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="row justify-content-center">
	<div class="col-lg-7 col-xl-6">
		<div class="card shadow-sm">
			<div class="card-header"><h2 class="card-title mb-0">Change Password</h2></div>
			<div class="card-body">
				<?php if ($this->session->flashdata('account_error')): ?>
					<div class="alert alert-danger" role="alert"><?= html_escape($this->session->flashdata('account_error')) ?></div>
				<?php endif; ?>
				<?= validation_errors('<div class="alert alert-danger" role="alert">', '</div>') ?>
				<p class="text-body-secondary"><?= html_escape($password_policy) ?></p>
				<?= form_open('account/password', array('novalidate' => 'novalidate')) ?>
					<div class="mb-3">
						<label class="form-label" for="current_password">Current password</label>
						<input class="form-control" type="password" id="current_password" name="current_password" autocomplete="current-password" required>
					</div>
					<div class="mb-3">
						<label class="form-label" for="new_password">New password</label>
						<input class="form-control" type="password" id="new_password" name="new_password" autocomplete="new-password" required>
					</div>
					<div class="mb-3">
						<label class="form-label" for="confirm_password">Confirm new password</label>
						<input class="form-control" type="password" id="confirm_password" name="confirm_password" autocomplete="new-password" required>
					</div>
					<button class="btn btn-primary" type="submit">Update Password</button>
				<?= form_close() ?>
			</div>
		</div>
	</div>
</div>
