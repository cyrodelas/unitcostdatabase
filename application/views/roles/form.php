<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php $is_system = $role !== NULL && (bool) $role->is_system; ?>
<div class="row justify-content-center">
	<div class="col-xl-8">
		<div class="card shadow-sm">
			<div class="card-header"><h2 class="card-title mb-0"><?= html_escape($page_title) ?></h2></div>
			<div class="card-body">
				<?php if ($form_error): ?><div class="alert alert-danger" role="alert"><?= html_escape($form_error) ?></div><?php endif; ?>
				<?= validation_errors('<div class="alert alert-danger" role="alert">', '</div>') ?>
				<?= form_open(current_url(), array('novalidate' => 'novalidate')) ?>
					<div class="mb-3">
						<label class="form-label" for="role_code">Role code</label>
						<input class="form-control text-uppercase" id="role_code" name="role_code" maxlength="50" value="<?= set_value('role_code', $role->role_code ?? '') ?>" <?= $is_system ? 'readonly' : 'required' ?>>
						<div class="form-text">Letters, numbers, and underscores; at least three characters.</div>
					</div>
					<div class="mb-3">
						<label class="form-label" for="role_name">Role name</label>
						<input class="form-control" id="role_name" name="role_name" maxlength="150" value="<?= set_value('role_name', $role->role_name ?? '') ?>" required>
					</div>
					<div class="mb-3">
						<label class="form-label" for="description">Description</label>
						<textarea class="form-control" id="description" name="description" maxlength="500" rows="3"><?= set_value('description', $role->description ?? '') ?></textarea>
					</div>
					<div class="form-check mb-4">
						<input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" <?= set_checkbox('is_active', '1', $role === NULL || (bool) $role->is_active) ?> <?= $role !== NULL && $role->role_code === 'SYS_ADMIN' ? 'disabled' : '' ?>>
						<label class="form-check-label" for="is_active">Active</label>
					</div>
					<button class="btn btn-primary" type="submit">Save Role</button>
					<a class="btn btn-outline-secondary" href="<?= site_url('roles') ?>">Cancel</a>
				<?= form_close() ?>
			</div>
		</div>
	</div>
</div>
