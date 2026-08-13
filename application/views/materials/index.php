<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
$category_filters = array(); $group_filters = array();
foreach ($materials as $item) {
	if ($item->material_category_name) $category_filters[$item->material_category_name] = $item->material_category_name;
	if ($item->material_group_name) $group_filters[$item->material_group_name] = $item->material_group_name;
}
ksort($category_filters); ksort($group_filters);
?>
<?php if ($this->session->flashdata('material_success')): ?><div class="alert alert-success" role="alert"><?= html_escape($this->session->flashdata('material_success')) ?></div><?php endif; ?>
<div class="card shadow-sm mb-3"><div class="card-body"><div class="row g-3 align-items-end">
	<div class="col-md-4"><label class="form-label" for="material-category-filter">Category</label><select class="form-select" id="material-category-filter" data-master-filter data-table-target="materials-table" data-column="2"><option value="">All categories</option><?php foreach ($category_filters as $value): ?><option value="<?= html_escape($value) ?>"><?= html_escape($value) ?></option><?php endforeach; ?></select></div>
	<div class="col-md-4"><label class="form-label" for="material-group-filter">Group</label><select class="form-select" id="material-group-filter" data-master-filter data-table-target="materials-table" data-column="3"><option value="">All groups</option><?php foreach ($group_filters as $value): ?><option value="<?= html_escape($value) ?>"><?= html_escape($value) ?></option><?php endforeach; ?></select></div>
	<div class="col-md-2"><label class="form-label" for="material-status-filter">Status</label><select class="form-select" id="material-status-filter" data-master-filter data-table-target="materials-table" data-column="7"><option value="">All statuses</option><option>Active</option><option>Inactive</option></select></div>
	<div class="col-md-2"><?php if ($can_manage): ?><a class="btn btn-primary w-100" href="<?= site_url('materials/create') ?>"><i class="bi bi-plus-lg" aria-hidden="true"></i> Add Material</a><?php endif; ?></div>
</div></div></div>
<div class="card shadow-sm"><div class="card-body"><div class="table-responsive">
	<table id="materials-table" class="table table-striped table-hover align-middle w-100 master-data-table">
		<thead><tr><th>Code</th><th>Material</th><th>Category</th><th>Group</th><th>Default UOM</th><th>Variants</th><th>Current Rate Range</th><th>Status</th><th data-dt-order="disable">Actions</th></tr></thead>
		<tbody><?php foreach ($materials as $material): ?><tr>
			<td><a class="fw-semibold text-decoration-none" href="<?= site_url('materials/'.$material->material_id) ?>"><?= html_escape($material->material_code) ?></a></td>
			<td><?= html_escape($material->material_name) ?></td><td><?= html_escape($material->material_category_name ?? '—') ?></td><td><?= html_escape($material->material_group_name ?? '—') ?></td><td><?= html_escape($material->uom_code ?? '—') ?></td>
			<td data-order="<?= (int) $material->variant_count ?>"><?= number_format($material->variant_count) ?></td>
			<td data-order="<?= $material->current_rate_min !== NULL ? html_escape($material->current_rate_min) : '' ?>"><?php if ($material->current_rate_min === NULL): ?>—<?php elseif ($material->current_rate_min === $material->current_rate_max): ?><?= number_format($material->current_rate_min, 2) ?><?php else: ?><?= number_format($material->current_rate_min, 2) ?> – <?= number_format($material->current_rate_max, 2) ?><?php endif; ?></td>
			<td><span class="badge text-bg-<?= $material->is_active ? 'success' : 'secondary' ?>"><?= $material->is_active ? 'Active' : 'Inactive' ?></span></td>
			<td class="text-nowrap"><a class="btn btn-outline-secondary btn-sm" href="<?= site_url('materials/'.$material->material_id) ?>" aria-label="View"><i class="bi bi-eye" aria-hidden="true"></i></a><?php if ($can_manage): ?> <a class="btn btn-outline-primary btn-sm" href="<?= site_url('materials/'.$material->material_id.'/edit') ?>" aria-label="Edit"><i class="bi bi-pencil" aria-hidden="true"></i></a><?php endif; ?></td>
		</tr><?php endforeach; ?></tbody>
	</table>
</div></div></div>
