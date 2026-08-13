<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php if ($this->session->flashdata('rbac_success')): ?><div class="alert alert-success" role="alert"><?= html_escape($this->session->flashdata('rbac_success')) ?></div><?php endif; ?>
<?php if ($this->session->flashdata('rbac_error')): ?><div class="alert alert-warning" role="alert"><?= html_escape($this->session->flashdata('rbac_error')) ?></div><?php endif; ?>
<div class="card shadow-sm">
	<div class="card-header"><h2 class="card-title mb-0"><?= html_escape($role->role_name) ?></h2></div>
	<div class="card-body">
		<?php if ($role->role_code === 'SYS_ADMIN'): ?>
			<div class="alert alert-info" role="alert">System Administrator always receives every active permission.</div>
		<?php endif; ?>
		<?= form_open(current_url()) ?>
			<div class="row g-3">
				<?php foreach ($grouped_permissions as $module => $permissions): ?>
					<div class="col-md-6 col-xl-4">
						<fieldset class="border rounded p-3 h-100">
							<legend class="float-none w-auto px-2 fs-6 fw-semibold"><?= html_escape($module) ?></legend>
							<?php foreach ($permissions as $permission): ?>
								<div class="form-check mb-2">
									<input class="form-check-input" type="checkbox" name="permission_ids[]" value="<?= (int) $permission->permission_id ?>" id="permission_<?= (int) $permission->permission_id ?>" <?= in_array((int) $permission->permission_id, $selected_permission_ids, TRUE) ? 'checked' : '' ?> <?= $role->role_code === 'SYS_ADMIN' ? 'disabled' : '' ?>>
									<label class="form-check-label" for="permission_<?= (int) $permission->permission_id ?>"><strong><?= html_escape($permission->permission_name) ?></strong><span class="d-block small text-body-secondary"><?= html_escape($permission->permission_code) ?></span></label>
								</div>
							<?php endforeach; ?>
						</fieldset>
					</div>
				<?php endforeach; ?>
			</div>
			<div class="mt-4">
				<?php if ($role->role_code !== 'SYS_ADMIN'): ?><button class="btn btn-primary" type="submit">Save Permissions</button><?php endif; ?>
				<a class="btn btn-outline-secondary" href="<?= site_url('roles') ?>">Back to Roles</a>
			</div>
		<?= form_close() ?>
	</div>
</div>
