<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php if ($this->session->flashdata('rbac_success')): ?><div class="alert alert-success" role="alert"><?= html_escape($this->session->flashdata('rbac_success')) ?></div><?php endif; ?>
<div class="card shadow-sm">
	<div class="card-header"><h2 class="card-title mb-0">User Role Assignments</h2></div>
	<div class="card-body p-0">
		<div class="table-responsive">
			<table class="table table-hover align-middle mb-0">
				<thead><tr><th>User</th><th>Username</th><th>Roles</th><th>Status</th><th class="text-end">Action</th></tr></thead>
				<tbody>
				<?php foreach ($users as $user): ?>
					<tr>
						<td><strong><?= html_escape($user->display_name) ?></strong><div class="small text-body-secondary"><?= html_escape($user->email ?: 'No email') ?></div></td>
						<td><?= html_escape($user->username) ?></td>
						<td><?= html_escape($user->role_names ?: 'No roles assigned') ?></td>
						<td><span class="badge <?= $user->is_active ? 'text-bg-success' : 'text-bg-secondary' ?>"><?= $user->is_active ? 'Active' : 'Inactive' ?></span></td>
						<td class="text-end"><a class="btn btn-outline-primary btn-sm" href="<?= site_url('user-roles/'.$user->user_id) ?>">Assign Roles</a></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	</div>
</div>
