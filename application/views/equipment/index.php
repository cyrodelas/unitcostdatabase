<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
$group_filters = array(); $scope_filters = array();
foreach ($equipment as $item) {
	if ($item->equipment_group_name) $group_filters[$item->equipment_group_name] = $item->equipment_group_name;
	if ($item->equipment_scope) $scope_filters[$item->equipment_scope] = $item->equipment_scope;
}
ksort($group_filters); ksort($scope_filters);
?>
<?php if ($this->session->flashdata('equipment_success')): ?><div class="alert alert-success" role="alert"><?= html_escape($this->session->flashdata('equipment_success')) ?></div><?php endif; ?>
<div class="card shadow-sm mb-3"><div class="card-body"><div class="row g-3 align-items-end">
	<div class="col-md-4"><label class="form-label" for="equipment-group-filter">Group</label><select class="form-select" id="equipment-group-filter" data-master-filter data-table-target="equipment-table" data-column="2"><option value="">All groups</option><?php foreach ($group_filters as $value): ?><option value="<?= html_escape($value) ?>"><?= html_escape($value) ?></option><?php endforeach; ?></select></div>
	<div class="col-md-3"><label class="form-label" for="equipment-scope-filter">Scope</label><select class="form-select" id="equipment-scope-filter" data-master-filter data-table-target="equipment-table" data-column="3"><option value="">All scopes</option><?php foreach ($scope_filters as $value): ?><option value="<?= html_escape($value) ?>"><?= html_escape(ucwords(strtolower(str_replace('_', ' ', $value)))) ?></option><?php endforeach; ?></select></div>
	<div class="col-md-3"><label class="form-label" for="equipment-status-filter">Status</label><select class="form-select" id="equipment-status-filter" data-master-filter data-table-target="equipment-table" data-column="6"><option value="">All statuses</option><option>Active</option><option>Inactive</option></select></div>
	<div class="col-md-2"><?php if ($can_manage): ?><a class="btn btn-primary w-100" href="<?= site_url('equipment/create') ?>"><i class="bi bi-plus-lg" aria-hidden="true"></i> Add Equipment</a><?php endif; ?></div>
</div></div></div>
<div class="card shadow-sm"><div class="card-body"><div class="table-responsive">
	<table id="equipment-table" class="table table-striped table-hover align-middle w-100 master-data-table"><thead><tr><th>Code</th><th>Equipment</th><th>Group</th><th>Scope</th><th>Category</th><th>Cost Item Uses</th><th>Status</th><th data-dt-order="disable">Actions</th></tr></thead><tbody>
	<?php foreach ($equipment as $item): ?><tr><td><a class="fw-semibold text-decoration-none" href="<?= site_url('equipment/'.$item->equipment_id) ?>"><?= html_escape($item->equipment_code) ?></a></td><td><?= html_escape($item->equipment_name) ?></td><td><?= html_escape($item->equipment_group_name ?? '—') ?></td><td><?= html_escape($item->equipment_scope ?? '—') ?></td><td><?= html_escape($item->equipment_category ?? '—') ?></td><td data-order="<?= (int) $item->usage_count ?>"><?= number_format($item->usage_count) ?></td><td><span class="badge text-bg-<?= $item->is_active ? 'success' : 'secondary' ?>"><?= $item->is_active ? 'Active' : 'Inactive' ?></span></td><td class="text-nowrap"><a class="btn btn-outline-secondary btn-sm" href="<?= site_url('equipment/'.$item->equipment_id) ?>" aria-label="View"><i class="bi bi-eye" aria-hidden="true"></i></a><?php if ($can_manage): ?> <a class="btn btn-outline-primary btn-sm" href="<?= site_url('equipment/'.$item->equipment_id.'/edit') ?>" aria-label="Edit"><i class="bi bi-pencil" aria-hidden="true"></i></a><?php endif; ?></td></tr><?php endforeach; ?>
	</tbody></table>
</div></div></div>
