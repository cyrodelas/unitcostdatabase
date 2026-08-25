<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
$permission_lookup = array_fill_keys($current_permissions ?? array(), TRUE);
$can = static function ($permission) use ($permission_lookup) { return isset($permission_lookup[$permission]); };
$current_uri = uri_string();

$menu_items = array(
	'standard_items' => array('Standard Cost Items', 'bi-journal-text', 'standard_cost_items.view', 'standard-cost-items'),
	'materials' => array('Materials', 'bi-box-seam', 'materials.view', 'materials'),
	'equipment' => array('Equipment', 'bi-truck', 'equipment.view', 'equipment'),
	'labor' => array('Labor', 'bi-people', 'labor.view', 'labor'),
	'crews' => array('Crews', 'bi-person-workspace', 'crews.view', 'crews'),
	'unit_rates' => array('Unit Rate Build-Up', 'bi-calculator', 'unit_rates.view', 'unit-rates'),
	'rates' => array('Rate History', 'bi-clock-history', 'rates.view', 'rates'),
	'elemental' => array('Elemental Costing', 'bi-diagram-3', 'elemental_costs.view', 'elemental-costs'),
	'references' => array('Reference Tables', 'bi-tags', 'references.view', 'references'),
	'projects' => array('Project Workspace', 'bi-buildings', 'projects.view', 'projects'),
	'boq' => array('BOQ Management', 'bi-table', 'boq.view', 'boq'),
	'boq_mapping' => array('BOQ Mapping', 'bi-signpost-split', 'boq.map', 'boq-mapping'),
	'review' => array('Technical Review Queue', 'bi-check2-square', 'governance.review', 'governance/review'),
	'approval' => array('Approval & Publication Queue', 'bi-patch-check', 'governance.approve', 'governance/approval'),
	'audit' => array('Governance Audit Trail', 'bi-clipboard-data', 'governance.audit', 'governance/audit'),
	'benchmarking' => array('Cost Benchmarking', 'bi-bar-chart-line', 'benchmarking.view', 'benchmarking'),
	'intelligence' => array('Trends & Suggestions', 'bi-graph-up-arrow', 'cost_intelligence.view', 'cost-intelligence'),
	'ml' => array('ML Governance', 'bi-cpu', 'ml.view', 'ml-governance'),
	'user_roles' => array('User Role Assignments', 'bi-person-gear', 'users.manage', 'user-roles'),
	'roles' => array('Roles', 'bi-shield-lock', 'roles.view', 'roles'),
	'permissions' => array('Permissions', 'bi-key', 'roles.view', 'permissions'),
	'settings' => array('System Settings', 'bi-gear', 'system_settings.manage', NULL),
);

$administrator_workflow = array(
	'Administration' => array('user_roles','roles','permissions','settings'),
	'Governance' => array('review','approval','audit'),
	'Project and BOQ Operations' => array('projects','boq','boq_mapping'),
	'Cost Intelligence' => array('benchmarking','intelligence','ml'),
	'Cost Management' => array('unit_rates','rates','elemental'),
	'Master Data' => array('standard_items','materials','equipment','labor','crews'),
	'Reference Data' => array('references'),
);
$ucd_administrator_workflow = array(
	'Cost Management' => array('standard_items','unit_rates','rates','elemental'),
	'Governance' => array('review','approval','audit'),
	'Project and BOQ Operations' => array('projects','boq','boq_mapping'),
	'Master Data' => array('materials','equipment','labor','crews'),
	'Reference Data' => array('references'),
);
$role_workflows = array(
	'COST_ENGINEER' => array(
		'Cost Item Development' => array('standard_items','unit_rates','rates','elemental'),
		'Project Costing' => array('projects','boq','boq_mapping'),
		'Cost Analysis' => array('benchmarking','intelligence','ml'),
	),
	'REVIEWER' => array(
		'Technical Review Process' => array('review','audit'),
		'Review Insights' => array('elemental','benchmarking','ml'),
	),
	'APPROVER' => array(
		'Approval Process' => array('approval','elemental','audit'),
		'Approval Insights' => array('benchmarking','ml'),
	),
	'PROJECT_USER' => array(
		'Project Delivery Process' => array('projects','boq','boq_mapping','elemental'),
	),
	'DATA_ANALYST' => array(
		'Cost Analysis Process' => array('elemental','benchmarking','intelligence','ml'),
		'Governance Evidence' => array('audit'),
	),
	'EXEC_VIEWER' => array(
		'Executive Insights' => array('elemental','benchmarking','intelligence'),
	),
);
$fallback_workflow = array(
	'My Process' => array('standard_items','unit_rates','rates','elemental','projects','boq','boq_mapping','review','approval','audit','benchmarking','intelligence','ml','user_roles','roles','permissions','settings'),
);

$role_codes = array();
foreach (($current_roles ?? array()) as $role) $role_codes[] = $role->role_code;
if (in_array('SYS_ADMIN', $role_codes, TRUE)) {
	$workflow = $administrator_workflow;
} elseif (in_array('UCD_ADMIN', $role_codes, TRUE)) {
	$workflow = $ucd_administrator_workflow;
} else {
	$workflow = array();
	foreach ($role_codes as $role_code) {
		if (!isset($role_workflows[$role_code])) continue;
		foreach ($role_workflows[$role_code] as $section => $keys) {
			if (!isset($workflow[$section])) $workflow[$section] = array();
			$workflow[$section] = array_values(array_unique(array_merge($workflow[$section], $keys)));
		}
	}
	if (!$workflow) $workflow = $fallback_workflow;
}
?>
<aside class="app-sidebar bg-dark shadow" data-bs-theme="dark" aria-label="Application sidebar">
	<div class="sidebar-brand">
		<a href="<?=site_url()?>" class="brand-link text-decoration-none"><span class="brand-mark" aria-hidden="true">N</span><span class="brand-text fw-semibold">Project Nexus UCD</span></a>
	</div>
	<div class="sidebar-wrapper">
		<nav class="mt-2">
			<ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="menu" data-accordion="false">
				<?php if($can('dashboard.view')):?><li class="nav-item"><a href="<?=site_url('dashboard')?>" class="nav-link <?=$current_uri===''||$current_uri==='dashboard'?'active':''?>"><i class="nav-icon bi bi-grid-1x2-fill" aria-hidden="true"></i><p>Dashboard</p></a></li><?php endif;?>
				<?php foreach($workflow as$section=>$keys):?>
					<?php $visible=array();foreach($keys as$key)if(isset($menu_items[$key])&&$can($menu_items[$key][2]))$visible[]=$menu_items[$key];if(!$visible)continue;?>
					<li class="nav-header"><?=html_escape(strtoupper($section))?></li>
					<?php foreach($visible as$item):$is_active=$item[3]!==NULL&&($current_uri===$item[3]||strpos($current_uri,$item[3].'/')===0);?>
						<li class="nav-item"><a href="<?=$item[3]!==NULL?site_url($item[3]):'#'?>" class="nav-link <?=$is_active?'active':''?> <?=$item[3]===NULL?'disabled':''?>" <?=$item[3]===NULL?'tabindex="-1" aria-disabled="true"':''?>><i class="nav-icon bi <?=html_escape($item[1])?>" aria-hidden="true"></i><p><?=html_escape($item[0])?></p></a></li>
					<?php endforeach;?>
				<?php endforeach;?>
			</ul>
		</nav>
	</div>
</aside>
