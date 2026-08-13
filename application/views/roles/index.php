<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php if ($this->session->flashdata('rbac_success')): ?>
	<div class="alert alert-success" role="alert"><?= html_escape($this->session->flashdata('rbac_success')) ?></div>
<?php endif; ?>
<div class="card shadow-sm">
	<div class="card-header d-flex align-items-center">
		<h2 class="card-title mb-0">Role Master</h2>
		<?php if ($can_manage_roles): ?>
			<a class="btn btn-primary btn-sm ms-auto" href="<?= site_url('roles/create') ?>"><i class="bi bi-plus-lg me-1" aria-hidden="true"></i>Add Role</a>
		<?php endif; ?>
	</div>
	<div class="card-body p-0">
		<div class="table-responsive">
			<table class="table table-hover align-middle mb-0">
				<thead><tr><th>Role</th><th>Code</th><th class="text-center">Users</th><th class="text-center">Permissions</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
				<tbody>
				<?php foreach ($roles as $role): ?>
					<tr>
						<td><strong><?= html_escape($role->role_name) ?></strong><?php if ($role->is_system): ?><span class="badge text-bg-secondary ms-2">System</span><?php endif; ?><div class="small text-body-secondary"><?= html_escape($role->description ?: '') ?></div></td>
						<td><code><?= html_escape($role->role_code) ?></code></td>
						<td class="text-center"><?= (int) $role->user_count ?></td>
						<td class="text-center"><?= (int) $role->permission_count ?></td>
						<td><span class="badge <?= $role->is_active ? 'text-bg-success' : 'text-bg-secondary' ?>"><?= $role->is_active ? 'Active' : 'Inactive' ?></span></td>
						<td class="text-end">
							<?php if ($can_manage_roles): ?>
								<a class="btn btn-outline-primary btn-sm" href="<?= site_url('roles/'.$role->role_id.'/permissions') ?>">Permissions</a>
								<a class="btn btn-outline-secondary btn-sm" href="<?= site_url('roles/'.$role->role_id.'/edit') ?>">Edit</a>
							<?php else: ?>
								<span class="text-body-secondary small">View only</span>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	</div>
</div>
