<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
$permission_lookup = array_fill_keys($current_permissions ?? array(), TRUE);
$can = static function ($permission) use ($permission_lookup) {
	return isset($permission_lookup[$permission]);
};
$current_uri = uri_string();
$menu_sections = array(
	'Master Cost Library' => array(
		array('Standard Cost Items', 'bi-journal-text', 'standard_cost_items.view', 'standard-cost-items'),
		array('Materials', 'bi-box-seam', 'materials.view', 'materials'),
		array('Equipment', 'bi-truck', 'equipment.view', 'equipment'),
		array('Labor', 'bi-people', 'labor.view', 'labor'),
		array('Crews', 'bi-person-workspace', 'crews.view', 'crews'),
	),
	'Cost Management' => array(
		array('Unit Rate Build-Up', 'bi-calculator', 'unit_rates.view', 'unit-rates'),
		array('Rate History', 'bi-clock-history', 'rates.view', 'rates'),
	),
	'Classification' => array(
		array('Reference Tables', 'bi-tags', 'references.view', 'references'),
	),
	'Projects' => array(
		array('Project Master', 'bi-buildings', 'projects.view', 'projects'),
		array('BOQ', 'bi-table', 'boq.view', 'boq'),
		array('BOQ Mapping', 'bi-signpost-split', 'boq.map', 'boq-mapping'),
	),
	'Governance' => array(
		array('Review Queue', 'bi-check2-square', 'governance.review', 'governance/review'),
		array('Approval Queue', 'bi-patch-check', 'governance.approve', 'governance/approval'),
		array('Audit Trail', 'bi-clipboard-data', 'governance.audit', 'governance/audit'),
	),
	'Cost Intelligence' => array(
		array('Benchmarking', 'bi-bar-chart-line', 'benchmarking.view', 'benchmarking'),
		array('Trends & Suggestions', 'bi-graph-up-arrow', 'cost_intelligence.view', 'cost-intelligence'),
	),
	'Administration' => array(
		array('User Role Assignments', 'bi-person-gear', 'users.manage', 'user-roles'),
		array('Roles', 'bi-shield-lock', 'roles.view', 'roles'),
		array('Permissions', 'bi-key', 'roles.view', 'permissions'),
		array('System Settings', 'bi-gear', 'system_settings.manage', NULL),
	),
);

// Keep operational work first for each role. Reference and master-data catalogs
// stay near the bottom because they support the workflows above them.
$role_codes = array();
foreach (($current_roles ?? array()) as $role) $role_codes[] = $role->role_code;
$active_role = 'DEFAULT';
foreach (array('SYS_ADMIN','UCD_ADMIN','COST_ENGINEER','REVIEWER','APPROVER','PROJECT_USER','DATA_ANALYST','EXEC_VIEWER') as $role_code) {
	if (in_array($role_code, $role_codes, TRUE)) { $active_role = $role_code; break; }
}
$section_orders = array(
	'SYS_ADMIN' => array('Administration','Governance','Projects','Cost Intelligence','Cost Management','Master Cost Library','Classification'),
	'UCD_ADMIN' => array('Governance','Projects','Administration','Cost Management','Cost Intelligence','Master Cost Library','Classification'),
	'COST_ENGINEER' => array('Projects','Cost Management','Cost Intelligence','Governance','Administration','Master Cost Library','Classification'),
	'REVIEWER' => array('Governance','Projects','Cost Intelligence','Cost Management','Administration','Master Cost Library','Classification'),
	'APPROVER' => array('Governance','Projects','Cost Intelligence','Cost Management','Administration','Master Cost Library','Classification'),
	'PROJECT_USER' => array('Projects','Cost Management','Cost Intelligence','Governance','Administration','Master Cost Library','Classification'),
	'DATA_ANALYST' => array('Cost Intelligence','Projects','Governance','Cost Management','Administration','Master Cost Library','Classification'),
	'EXEC_VIEWER' => array('Cost Intelligence','Projects','Governance','Cost Management','Administration','Master Cost Library','Classification'),
	'DEFAULT' => array('Projects','Governance','Cost Intelligence','Cost Management','Administration','Master Cost Library','Classification'),
);
$ordered_sections = array();
foreach ($section_orders[$active_role] as $section_name) {
	if (isset($menu_sections[$section_name])) $ordered_sections[$section_name] = $menu_sections[$section_name];
}
$menu_sections = $ordered_sections;
?>
<aside class="app-sidebar bg-dark shadow" data-bs-theme="dark" aria-label="Application sidebar">
	<div class="sidebar-brand">
		<a href="<?= site_url() ?>" class="brand-link text-decoration-none"><span class="brand-mark" aria-hidden="true">N</span><span class="brand-text fw-semibold">Project Nexus UCD</span></a>
	</div>
	<div class="sidebar-wrapper">
		<nav class="mt-2">
			<ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="menu" data-accordion="false">
				<?php if ($can('dashboard.view')): ?>
					<li class="nav-item"><a href="<?= site_url('dashboard') ?>" class="nav-link <?= $current_uri === '' || $current_uri === 'dashboard' ? 'active' : '' ?>"><i class="nav-icon bi bi-grid-1x2-fill" aria-hidden="true"></i><p>Dashboard</p></a></li>
				<?php endif; ?>
				<?php foreach ($menu_sections as $section => $items): ?>
					<?php $visible_items = array_values(array_filter($items, static function ($item) use ($can) { return $can($item[2]); })); ?>
					<?php if (!$visible_items) continue; ?>
					<li class="nav-header"><?= html_escape(strtoupper($section)) ?></li>
					<?php foreach ($visible_items as $item): ?>
						<?php $is_active = $item[3] !== NULL && ($current_uri === $item[3] || strpos($current_uri, $item[3].'/') === 0); ?>
						<li class="nav-item">
							<a href="<?= $item[3] !== NULL ? site_url($item[3]) : '#' ?>" class="nav-link <?= $is_active ? 'active' : '' ?> <?= $item[3] === NULL ? 'disabled' : '' ?>" <?= $item[3] === NULL ? 'tabindex="-1" aria-disabled="true"' : '' ?>>
								<i class="nav-icon bi <?= html_escape($item[1]) ?>" aria-hidden="true"></i><p><?= html_escape($item[0]) ?></p>
							</a>
						</li>
					<?php endforeach; ?>
				<?php endforeach; ?>
			</ul>
		</nav>
	</div>
</aside>
