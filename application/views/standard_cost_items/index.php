<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
$divisions=array();$revision_statuses=array();$project_markets=array();
foreach($items as$item){
	if($item->division_name)$divisions[$item->division_name]=$item->division_name;
	if($item->revision_status)$revision_statuses[$item->revision_status]=$item->revision_status;
	$project_market=trim(($item->project_type_short?:$item->project_type_name).' / '.$item->market_segment_name,' /');
	if($project_market)$project_markets[$project_market]=$project_market;
}
ksort($divisions);ksort($revision_statuses);ksort($project_markets);
?>
<?php if($can_create):?><div class="mb-3"><a class="btn btn-primary" href="<?=site_url('standard-cost-items/create')?>"><i class="bi bi-plus-lg" aria-hidden="true"></i> Create Standard Cost Item</a></div><?php endif;?>
<div class="alert alert-info py-2">Standard Cost Items remain the governed resource/unit-rate library. Elemental cost plans are classified and priced directly by Project Type, Market Segment, and UniFormat element; they do not aggregate SCI revisions.<?php if(in_array('elemental_costs.view',$current_permissions??array(),TRUE)):?> <a class="alert-link" href="<?=site_url('elemental-costs')?>">Open Elemental Costing</a>.<?php endif;?></div>
<div class="card shadow-sm mb-3"><div class="card-body"><div class="row g-3">
	<div class="col-md-3"><label class="form-label" for="cost-item-project-market-filter">Project Type / Market Segment</label><select class="form-select" id="cost-item-project-market-filter" data-master-filter data-table-target="cost-item-table" data-column="2"><option value="">All classifications</option><?php foreach($project_markets as$v):?><option value="<?=html_escape($v)?>"><?=html_escape($v)?></option><?php endforeach;?></select></div>
	<div class="col-md-3"><label class="form-label" for="cost-item-division-filter">CSI Division</label><select class="form-select" id="cost-item-division-filter" data-master-filter data-table-target="cost-item-table" data-column="4"><option value="">All divisions</option><?php foreach($divisions as$v):?><option value="<?=html_escape($v)?>"><?=html_escape($v)?></option><?php endforeach;?></select></div>
	<div class="col-md-3"><label class="form-label" for="cost-item-revision-filter">Revision Status</label><select class="form-select" id="cost-item-revision-filter" data-master-filter data-table-target="cost-item-table" data-column="8"><option value="">All revision statuses</option><?php foreach($revision_statuses as$v):?><option value="<?=html_escape($v)?>"><?=html_escape($v)?></option><?php endforeach;?></select></div>
	<div class="col-md-3"><label class="form-label" for="cost-item-lifecycle-filter">Lifecycle</label><select class="form-select" id="cost-item-lifecycle-filter" data-master-filter data-table-target="cost-item-table" data-column="10"><option value="">All lifecycle states</option><option>ACTIVE</option><option>INACTIVE</option><option>ARCHIVED</option></select></div>
</div></div></div>
<div class="card shadow-sm"><div class="card-body"><div class="table-responsive"><table id="cost-item-table" class="table table-striped table-hover align-middle w-100 master-data-table"><thead><tr><th>Enterprise Code</th><th style="min-width:28rem">Standard Item</th><th>Project / Market</th><th>Revision</th><th>CSI Division</th><th>Trade</th><th>UOM</th><th>Resources (M/L/E)</th><th>Revision Status</th><th>Final Unit Rate</th><th>Lifecycle</th><th data-dt-order="disable">Action</th></tr></thead><tbody>
<?php foreach($items as$item):
	$attributes=array('Attribute class'=>$item->attribute_class_name??NULL,'Work type'=>$item->work_type??NULL,'Strength / grade'=>$item->strength_grade??NULL,'Size / dimension'=>$item->size_dimension??NULL,'Application'=>$item->application_element??NULL,'Finish'=>$item->finish_condition??NULL);
	foreach($item->attribute_values as$attribute)$attributes[$attribute['name']]=$attribute['value'];
	$attributes=array_filter($attributes,static function($value){return$value!==NULL&&trim((string)$value)!=='';});
	$project_market=trim(($item->project_type_short?:$item->project_type_name).' / '.$item->market_segment_name,' /');
?>
	<tr><td><a class="fw-semibold text-decoration-none" href="<?=site_url('standard-cost-items/'.$item->cost_item_id)?>"><?=html_escape($item->enterprise_cost_code ?: $item->cost_item_uid)?></a></td><td><div class="fw-semibold"><?=html_escape($item->standard_item_name)?></div><div class="small mt-1"><?=nl2br(html_escape($item->standard_description?:'—'))?></div><?php if($attributes):?><dl class="row small text-body-secondary mt-2 mb-0"><?php foreach($attributes as$label=>$attribute_value):?><dt class="col-sm-4"><?=html_escape($label)?></dt><dd class="col-sm-8 mb-1"><?=html_escape($attribute_value)?></dd><?php endforeach;?></dl><?php endif;?></td><td><?=html_escape($project_market?:'—')?></td><td><?=html_escape($item->revision_no)?></td><td><?=html_escape($item->division_name??'—')?></td><td><?=html_escape($item->trade_name??'—')?></td><td><?=html_escape($item->uom_code??'—')?></td><td><?=number_format($item->material_count)?> / <?=number_format($item->labor_count)?> / <?=number_format($item->equipment_count)?></td><td><span class="badge text-bg-<?=$item->revision_status==='APPROVED'?'success':'secondary'?>"><?=html_escape($item->revision_status??'—')?></span></td><td data-order="<?=html_escape($item->final_unit_rate)?>"><strong><?=number_format($item->final_unit_rate,2)?></strong></td><td><?=html_escape($item->lifecycle_status)?></td><td><a class="btn btn-outline-secondary btn-sm" href="<?=site_url('standard-cost-items/'.$item->cost_item_id)?>"><i class="bi bi-eye" aria-hidden="true"></i></a></td></tr>
<?php endforeach;?>
</tbody></table></div></div></div>
