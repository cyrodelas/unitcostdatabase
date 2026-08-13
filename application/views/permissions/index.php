<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="card shadow-sm">
	<div class="card-header"><h2 class="card-title mb-0">Permission Catalog</h2></div>
	<div class="card-body p-0">
		<div class="table-responsive">
			<table class="table table-hover align-middle mb-0">
				<thead><tr><th>Module</th><th>Permission</th><th>Code</th><th>Description</th><th>Status</th></tr></thead>
				<tbody>
				<?php foreach ($permissions as $permission): ?>
					<tr><td><?= html_escape($permission->module_name) ?></td><td><?= html_escape($permission->permission_name) ?></td><td><code><?= html_escape($permission->permission_code) ?></code></td><td><?= html_escape($permission->description ?: '') ?></td><td><span class="badge <?= $permission->is_active ? 'text-bg-success' : 'text-bg-secondary' ?>"><?= $permission->is_active ? 'Active' : 'Inactive' ?></span></td></tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	</div>
</div>
