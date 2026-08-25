<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$text = static function ($label, $max, $required = TRUE) {
	return array('label' => $label, 'type' => 'text', 'max_length' => $max, 'required' => $required);
};
$textarea = static function ($label, $max) {
	return array('label' => $label, 'type' => 'textarea', 'max_length' => $max, 'required' => FALSE);
};
$lookup = static function ($label, $table, $key, $display, $required = TRUE, $key_type = 'integer') {
	return array('label' => $label, 'type' => 'lookup', 'required' => $required, 'lookup' => array('table' => $table, 'key' => $key, 'display' => $display, 'key_type' => $key_type));
};
$integer = static function ($label, $required = FALSE) { return array('label'=>$label,'type'=>'integer','required'=>$required); };
$decimal = static function ($label, $required = FALSE) { return array('label'=>$label,'type'=>'decimal','required'=>$required); };
$checkbox = static function ($label) { return array('label'=>$label,'type'=>'checkbox','required'=>FALSE); };
$entity = static function ($title,$table,$key,array$fields,array$duplicates,$timestamps=TRUE) { return array('title'=>$title,'table'=>$table,'primary_key'=>$key,'fields'=>$fields,'duplicate_fields'=>$duplicates,'timestamps'=>$timestamps); };
$simple = static function ($title, $table, $primary_key, $code_field, $code_label, $code_length, $name_field, $name_label = 'Name', $name_length = 150) use ($text) {
	return array(
		'title' => $title, 'table' => $table, 'primary_key' => $primary_key,
		'fields' => array($code_field => $text($code_label, $code_length), $name_field => $text($name_label, $name_length)),
		'duplicate_fields' => array($code_field),
	);
};

$config['reference_entities'] = array(
	'csi-divisions' => $simple('CSI Divisions', 'ref_csi_division', 'division_id', 'division_code', 'Division code', 2, 'division_name', 'Division name'),
	'csi-sections' => array(
		'title' => 'CSI Sections', 'table' => 'ref_csi_section', 'primary_key' => 'section_id',
		'fields' => array(
			'division_id' => $lookup('CSI division', 'ref_csi_division', 'division_id', "CONCAT(division_code, ' — ', division_name)"),
			'section_code' => $text('Section code', 20), 'section_name' => $text('Section name', 200),
		),
		'duplicate_fields' => array('section_code'),
	),
	'uniformat-level-1' => $simple('UniFormat Level 1', 'ref_uniformat_level1', 'uniformat_level1_id', 'level1_code', 'Level 1 code', 10, 'level1_name', 'Level 1 name', 200),
	'uniformat-level-2' => array(
		'title' => 'UniFormat Level 2', 'table' => 'ref_uniformat_level2', 'primary_key' => 'uniformat_level2_id',
		'fields' => array('uniformat_level1_id' => $lookup('Level 1', 'ref_uniformat_level1', 'uniformat_level1_id', "CONCAT(level1_code, ' — ', level1_name)"), 'level2_code' => $text('Level 2 code', 10), 'level2_name' => $text('Level 2 name', 200)),
		'duplicate_fields' => array('level2_code'),
	),
	'uniformat-level-3' => array(
		'title' => 'UniFormat Level 3', 'table' => 'ref_uniformat_level3', 'primary_key' => 'uniformat_level3_id',
		'fields' => array('uniformat_level2_id' => $lookup('Level 2', 'ref_uniformat_level2', 'uniformat_level2_id', "CONCAT(level2_code, ' — ', level2_name)"), 'level3_code' => $text('Level 3 code', 10), 'level3_name' => $text('Level 3 name', 200)),
		'duplicate_fields' => array('level3_code'),
	),
	'uniformat-level-4' => array(
		'title' => 'UniFormat Level 4', 'table' => 'ref_uniformat_level4', 'primary_key' => 'uniformat_level4_id',
		'fields' => array('uniformat_level3_id' => $lookup('Level 3', 'ref_uniformat_level3', 'uniformat_level3_id', "CONCAT(level3_code, ' — ', level3_name)"), 'level4_code' => $text('Level 4 code', 10), 'level4_name' => $text('Level 4 name', 255), 'domain_type' => $text('Domain type', 20, FALSE)),
		'duplicate_fields' => array('level4_code'),
	),
	'specifications' => array(
		'title' => 'Specifications', 'table' => 'ref_specification', 'primary_key' => 'specification_id',
		'fields' => array('specification_source' => $text('Source', 50), 'specification_code' => $text('Specification code', 50), 'specification_title' => $text('Title', 255), 'edition' => $text('Edition', 50, FALSE), 'effective_date' => array('label' => 'Effective date', 'type' => 'date', 'required' => FALSE), 'expiry_date' => array('label' => 'Expiry date', 'type' => 'date', 'required' => FALSE)),
		'duplicate_fields' => array('specification_source', 'specification_code', 'edition'),
	),
	'specification-segments' => array(
		'title' => 'Specification Code Segments', 'table' => 'ref_specification_code_segment', 'primary_key' => 'specification_code_segment_id',
		'fields' => array('specification_id' => $lookup('Specification', 'ref_specification', 'specification_id', "CONCAT(specification_source, ' ', specification_code, ' — ', specification_title)"), 'enterprise_specification_segment' => $text('Enterprise segment', 30), 'segment_status' => $text('Segment status', 30), 'remarks' => $textarea('Remarks', 500)),
		'duplicate_fields' => array('specification_id'),
		'duplicate_alternates' => array(array('enterprise_specification_segment')),
	),
	'trades' => $entity('Trades', 'ref_trade', 'trade_id', array('division_id' => $lookup('Trade division', 'ref_division', 'division_id', "CONCAT(division_code, ' - ', division_name)", FALSE), 'trade_code' => $text('Trade code', 20), 'trade_name' => $text('Trade name', 150)), array('trade_code')),
	'units-of-measure' => array(
		'title' => 'Units of Measure', 'table' => 'ref_uom', 'primary_key' => 'uom_id',
		'fields' => array('uom_code' => $text('UOM code', 20), 'uom_name' => $text('UOM name', 100), 'quantity_type' => $text('Quantity type', 50, FALSE)),
		'duplicate_fields' => array('uom_code'),
	),
	'material-categories' => array(
		'title' => 'Material Categories', 'table' => 'ref_material_category', 'primary_key' => 'material_category_id',
		'fields' => array('material_category_code' => $text('Category code', 40), 'material_category_name' => $text('Category name', 150), 'description' => $textarea('Description', 500)),
		'duplicate_fields' => array('material_category_code'),
	),
	'material-groups' => $simple('Material Groups', 'ref_material_group', 'material_group_id', 'material_group_code', 'Group code', 20, 'material_group_name', 'Group name'),
	'equipment-groups' => $simple('Equipment Groups', 'ref_equipment_group', 'equipment_group_id', 'equipment_group_code', 'Group code', 30, 'equipment_group_name', 'Group name'),
	'labor-categories' => array(
		'title' => 'Labor Categories', 'table' => 'ref_labor_category', 'primary_key' => 'labor_category_id',
		'fields' => array('labor_category_code' => $text('Category code', 20), 'labor_category_name' => $text('Category name', 150), 'description' => $textarea('Description', 500)),
		'duplicate_fields' => array('labor_category_code'),
	),
	'labor-rate-components' => array(
		'title' => 'Labor Rate Components', 'table' => 'ref_labor_rate_component', 'primary_key' => 'labor_rate_component_id',
		'fields' => array('component_code' => $text('Component code', 50), 'component_name' => $text('Component name', 150), 'component_category' => $text('Component category', 50, FALSE), 'amount_basis' => $text('Amount basis', 50, FALSE)),
		'duplicate_fields' => array('component_code'),
	),
	'resource-allowance-types' => array(
		'title' => 'Resource Allowance Types', 'table' => 'ref_resource_allowance_type', 'primary_key' => 'resource_allowance_type_id',
		'fields' => array('allowance_type_code' => $text('Allowance code', 40), 'allowance_type_name' => $text('Allowance name', 150), 'description' => $textarea('Description', 500)),
		'duplicate_fields' => array('allowance_type_code'),
	),
	'attribute-data-types' => $entity('Attribute Data Types','ref_attribute_data_type','attribute_data_type_id',array('data_type_code'=>$text('Code',20),'data_type_name'=>$text('Name',100)),array('data_type_code'),FALSE),
	'attribute-groups' => $entity('Attribute Groups','ref_attribute_group','attribute_group_id',array('group_code'=>$text('Group code',20),'group_name'=>$text('Group name',150),'sort_order'=>$integer('Sort order')),array('group_code')),
	'attribute-subject-classes' => $entity('Attribute Subject Classes','ref_attribute_subject_class','attribute_subject_class_id',array('class_code'=>$text('Class code',50),'class_name'=>$text('Class name',200),'parent_class_id'=>$lookup('Parent class','ref_attribute_subject_class','attribute_subject_class_id',"CONCAT(class_code, ' — ', class_name)",FALSE),'subject_scope'=>$text('Subject scope',30,FALSE),'sort_order'=>$integer('Sort order')),array('class_code')),
	'attribute-definitions' => $entity('Attribute Definitions','ref_attribute_definition','attribute_id',array('attribute_code'=>$text('Attribute code',80),'attribute_name'=>$text('Attribute name',200),'attribute_group_id'=>$lookup('Attribute group','ref_attribute_group','attribute_group_id',"CONCAT(group_code, ' — ', group_name)",FALSE),'attribute_data_type_id'=>$lookup('Data type','ref_attribute_data_type','attribute_data_type_id',"CONCAT(data_type_code, ' — ', data_type_name)",FALSE),'attribute_scope'=>$text('Scope',30,FALSE),'default_uom_id'=>$lookup('Default UOM','ref_uom','uom_id',"CONCAT(uom_code, ' — ', uom_name)",FALSE),'default_unit_symbol'=>$text('Unit symbol',40,FALSE),'decimal_places'=>$integer('Decimal places'),'description'=>$textarea('Description',1000),'source_basis'=>$text('Source basis',50,FALSE),'external_property_set'=>$text('External property set',150,FALSE),'external_property_name'=>$text('External property name',150,FALSE)),array('attribute_code')),
	'attribute-options' => $entity('Attribute Options','ref_attribute_option','attribute_option_id',array('attribute_id'=>$lookup('Attribute','ref_attribute_definition','attribute_id',"CONCAT(attribute_code, ' — ', attribute_name)"),'option_code'=>$text('Option code',80),'option_label'=>$text('Option label',200),'sort_order'=>$integer('Sort order')),array('attribute_id','option_code'),FALSE),
	'city-classes' => $entity('City Classes','ref_city_class','city_class_id',array('city_class_code'=>$text('Class code',20),'city_class_name'=>$text('Class name',150)),array('city_class_code')),
	'confidence-bands' => $entity('Confidence Bands','ref_confidence_band','confidence_band_id',array('confidence_code'=>$text('Code',20),'confidence_name'=>$text('Name',50),'minimum_score'=>$decimal('Minimum score'),'maximum_score'=>$decimal('Maximum score'),'is_recommendable'=>$checkbox('Recommendable'),'definition_origin'=>$text('Definition origin',30,FALSE),'description'=>$textarea('Description',500),'sort_order'=>$integer('Sort order')),array('confidence_code'),FALSE),
	'cost-markup-types' => $entity('Cost Markup Types','ref_cost_markup_type','markup_type_id',array('markup_type_code'=>$text('Code',30),'markup_type_name'=>$text('Name',150),'markup_category'=>$text('Category',50,FALSE),'description'=>$textarea('Description',500),'calculation_sequence'=>$integer('Calculation sequence'),'definition_origin'=>$text('Definition origin',30,FALSE)),array('markup_type_code')),
	'countries' => $entity('Countries','ref_country','country_id',array('iso_alpha2'=>$text('ISO alpha-2',2),'iso_alpha3'=>$text('ISO alpha-3',3),'country_name'=>$text('Country name',150),'currency_code'=>$text('Currency code',3,FALSE)),array('iso_alpha2')),
	'divisions' => $entity('Trade Divisions','ref_division','division_id',array('division_code'=>$text('Division code',20),'division_name'=>$text('Division name',150),'division_description'=>$textarea('Description',500),'sort_order'=>$integer('Sort order')),array('division_code')),
	'income-classifications' => $entity('Income Classifications','ref_income_classification','income_classification_id',array('income_classification_code'=>$text('Code',50),'income_classification_name'=>$text('Name',100),'sort_order'=>$integer('Sort order')),array('income_classification_code')),
	'income-classification-rules' => $entity('Income Classification Rules','ref_income_classification_rule','income_classification_rule_id',array('location_level_code'=>$lookup('Location level','ref_location_level','level_code',"CONCAT(level_code, ' - ', level_name)",FALSE,'string'),'income_classification_id'=>$lookup('Income classification','ref_income_classification','income_classification_id',"CONCAT(income_classification_code, ' - ', income_classification_name)",FALSE),'minimum_average_annual_income'=>$decimal('Minimum annual income'),'maximum_average_annual_income'=>$decimal('Maximum annual income'),'currency_code'=>$text('Currency code',3,FALSE),'source_basis'=>$text('Source basis',255,FALSE)),array('location_level_code','income_classification_id','minimum_average_annual_income')),
	'island-groups' => $entity('Island Groups','ref_island_group','island_group_id',array('island_group_code'=>$text('Code',20),'island_group_name'=>$text('Name',100),'sort_order'=>$integer('Sort order')),array('island_group_code')),
	'location-levels' => $entity('Location Levels','ref_location_level','location_level_id',array('level_code'=>$text('Level code',30),'level_name'=>$text('Level name',100),'source_level_code'=>$text('Source level code',30,FALSE),'hierarchy_order'=>$integer('Hierarchy order'),'is_administrative'=>$checkbox('Administrative')),array('level_code')),
	'location-releases' => $entity('Location Releases','ref_location_release','location_release_id',array('release_code'=>$text('Release code',50),'release_name'=>$text('Release name',255),'as_of_date'=>array('label'=>'As-of date','type'=>'date','required'=>FALSE),'release_date'=>array('label'=>'Release date','type'=>'date','required'=>FALSE),'source_authority'=>$text('Source authority',150,FALSE),'source_status'=>$text('Source status',50,FALSE),'is_current'=>$checkbox('Current release')),array('release_code')),
	'location-statuses' => $entity('Location Statuses','ref_location_status','location_status_id',array('location_status_code'=>$text('Status code',30),'location_status_name'=>$text('Status name',100),'source_value'=>$text('Source value',50,FALSE)),array('location_status_code')),
	'locations' => $entity('Locations','ref_location','location_id',array('country_id'=>$lookup('Country','ref_country','country_id',"CONCAT(iso_alpha3, ' — ', country_name)",FALSE),'parent_location_id'=>$integer('Parent location ID'),'location_level_id'=>$lookup('Location level','ref_location_level','location_level_id',"CONCAT(level_code, ' — ', level_name)",FALSE),'location_release_id'=>$lookup('Location release','ref_location_release','location_release_id',"CONCAT(release_code, ' — ', release_name)",FALSE),'island_group_id'=>$lookup('Island group','ref_island_group','island_group_id',"CONCAT(island_group_code, ' — ', island_group_name)",FALSE),'psgc_code'=>$text('PSGC code',10),'correspondence_code'=>$text('Correspondence code',9,FALSE),'location_name'=>$text('Location name',255),'old_name'=>$text('Old name',255,FALSE),'city_class_id'=>$lookup('City class','ref_city_class','city_class_id',"CONCAT(city_class_code, ' — ', city_class_name)",FALSE),'income_classification_id'=>$lookup('Income classification','ref_income_classification','income_classification_id',"CONCAT(income_classification_code, ' — ', income_classification_name)",FALSE),'urban_rural_id'=>$lookup('Urban/rural','ref_urban_rural_classification','urban_rural_id',"CONCAT(urban_rural_code, ' — ', urban_rural_name)",FALSE),'source_geographic_level'=>$text('Source geographic level',30,FALSE),'source_status'=>$text('Source status',50,FALSE),'income_class_retained_flag'=>$checkbox('Income class retained'),'source_income_classification'=>$text('Source income classification',50,FALSE),'source_urban_rural'=>$text('Source urban/rural',20,FALSE),'population_2024'=>$integer('Population 2024'),'population_2024_source_value'=>$text('Population source value',50,FALSE),'location_status_id'=>$lookup('Location status','ref_location_status','location_status_id',"CONCAT(location_status_code, ' — ', location_status_name)",FALSE)),array('psgc_code')),
	'location-aliases' => $entity('Location Aliases','ref_location_alias','location_alias_id',array('location_id'=>$integer('Location ID',TRUE),'alias_name'=>$text('Alias name',255),'alias_type'=>$text('Alias type',30,FALSE),'normalized_alias'=>$text('Normalized alias',255,FALSE)),array('location_id','alias_name')),
	'markup-calculation-methods' => $entity('Markup Calculation Methods','ref_markup_calculation_method','markup_calculation_method_id',array('calculation_method_code'=>$text('Code',30),'calculation_method_name'=>$text('Name',100),'method_category'=>$text('Category',30,FALSE),'sort_order'=>$integer('Sort order')),array('calculation_method_code'),FALSE),
	'price-period-types' => $entity('Price Period Types','ref_price_period_type','price_period_type_id',array('period_type_code'=>$text('Code',20),'period_type_name'=>$text('Name',50),'sort_order'=>$integer('Sort order')),array('period_type_code'),FALSE),
	'price-periods' => $entity('Price Periods','ref_price_period','price_period_id',array('period_code'=>$text('Period code',20),'price_period_type_id'=>$lookup('Period type','ref_price_period_type','price_period_type_id',"CONCAT(period_type_code, ' — ', period_type_name)",FALSE),'period_year'=>$integer('Year'),'period_quarter'=>$integer('Quarter'),'period_month'=>$integer('Month'),'period_start'=>array('label'=>'Period start','type'=>'date','required'=>FALSE),'period_end'=>array('label'=>'Period end','type'=>'date','required'=>FALSE),'period_label'=>$text('Label',50,FALSE),'sort_key'=>$integer('Sort key')),array('period_code')),
	'project-categories' => $entity('Project Categories','ref_project_category','project_category_id',array('project_category_code'=>$text('Code',20),'project_category_name'=>$text('Name',100)),array('project_category_code')),
	'project-groups' => $entity('Project Groups','ref_project_group','project_group_id',array('project_group_code'=>$text('Code',30),'project_group_name'=>$text('Name',100)),array('project_group_code')),
	'project-types' => $entity('Project Types','ref_project_type','project_type_id',array('project_type_code'=>$text('Code',50),'project_type_name'=>$text('Name',255),'project_type_short'=>$text('Short name',100,FALSE),'abbreviation'=>$text('Abbreviation',30,FALSE),'project_category_id'=>$lookup('Category','ref_project_category','project_category_id',"CONCAT(project_category_code, ' — ', project_category_name)",FALSE),'project_group_id'=>$lookup('Group','ref_project_group','project_group_id',"CONCAT(project_group_code, ' — ', project_group_name)",FALSE)),array('project_type_code')),
	'market-segments' => $entity('Market Segments','ref_market_segment','market_segment_id',array('market_segment_code'=>$text('Code',20),'market_segment_name'=>$text('Name',100),'display_order'=>$integer('Display order',TRUE),'description'=>$textarea('Description',500)),array('market_segment_code')),
	'project-type-market-segments' => $entity('Project Type–Market Segments','ref_project_type_market_segment','project_type_market_segment_id',array('project_type_id'=>$lookup('Project type','ref_project_type','project_type_id',"CONCAT(project_type_code, ' — ', project_type_name)"),'market_segment_id'=>$lookup('Market segment','ref_market_segment','market_segment_id',"CONCAT(market_segment_code, ' — ', market_segment_name)"),'display_order'=>$integer('Display order',TRUE),'is_default'=>$checkbox('Default for project type')),array('project_type_id','market_segment_id')),
	'elemental-cost-bases' => $entity('Elemental Cost Bases','ref_elemental_cost_basis','elemental_cost_basis_id',array('basis_code'=>$text('Basis code',30),'basis_name'=>$text('Basis name',120),'basis_uom_label'=>$text('Basis UOM label',40,FALSE),'description'=>$textarea('Description',500),'display_order'=>$integer('Display order',TRUE)),array('basis_code')),
	'rate-adjustment-types' => $entity('Rate Adjustment Types','ref_rate_adjustment_type','rate_adjustment_type_id',array('adjustment_type_code'=>$text('Code',30),'adjustment_type_name'=>$text('Name',150),'adjustment_category'=>$text('Category',50,FALSE),'description'=>$textarea('Description',500),'sort_order'=>$integer('Sort order'),'definition_origin'=>$text('Definition origin',30,FALSE)),array('adjustment_type_code'),FALSE),
	'rate-bases' => $entity('Rate Bases','ref_rate_basis','rate_basis_id',array('rate_basis_code'=>$text('Code',30),'rate_basis_name'=>$text('Name',100),'description'=>$textarea('Description',500)),array('rate_basis_code'),FALSE),
	'rate-cost-components' => $entity('Rate Cost Components','ref_rate_cost_component','rate_cost_component_id',array('component_code'=>$text('Code',40),'component_name'=>$text('Name',150),'component_category'=>$text('Category',50,FALSE),'description'=>$textarea('Description',500),'sort_order'=>$integer('Sort order'),'is_direct_cost'=>$checkbox('Direct cost')),array('component_code'),FALSE),
	'rate-source-types' => $entity('Rate Source Types','ref_rate_source_type','rate_source_type_id',array('source_type_code'=>$text('Code',50),'source_type_name'=>$text('Name',150),'source_category'=>$text('Category',50,FALSE),'description'=>$textarea('Description',500),'is_rate_evidence'=>$checkbox('Rate evidence'),'definition_origin'=>$text('Definition origin',30,FALSE)),array('source_type_code')),
	'urban-rural-classifications' => $entity('Urban/Rural Classifications','ref_urban_rural_classification','urban_rural_id',array('urban_rural_code'=>$text('Code',20),'urban_rural_name'=>$text('Name',100),'source_code'=>$text('Source code',10,FALSE)),array('urban_rural_code')),
	'validation-statuses' => $entity('Validation Statuses','ref_validation_status','validation_status_id',array('validation_status_code'=>$text('Code',30),'validation_status_name'=>$text('Name',100),'sort_order'=>$integer('Sort order'),'is_valid_evidence'=>$checkbox('Valid evidence'),'description'=>$textarea('Description',500)),array('validation_status_code'),FALSE),
);

$config['reference_entities']['locations']['server_paginated'] = TRUE;
$config['reference_entities']['market-segments']['duplicate_alternates'] = array(array('market_segment_name'));

$config['reference_groups'] = array(
	'Classification and Standards' => array('csi-divisions','csi-sections','uniformat-level-1','uniformat-level-2','uniformat-level-3','uniformat-level-4','specifications','specification-segments','divisions','trades'),
	'Resources and Attributes' => array('units-of-measure','material-categories','material-groups','equipment-groups','labor-categories','labor-rate-components','resource-allowance-types','attribute-data-types','attribute-groups','attribute-subject-classes','attribute-definitions','attribute-options'),
	'Projects and Market Segments' => array('project-categories','project-groups','project-types','market-segments','project-type-market-segments'),
	'Elemental Costing' => array('elemental-cost-bases'),
	'Locations and Demographics' => array('countries','island-groups','city-classes','income-classifications','income-classification-rules','location-levels','location-releases','location-statuses','locations','location-aliases','urban-rural-classifications'),
	'Rates and Cost Governance' => array('confidence-bands','cost-markup-types','markup-calculation-methods','price-period-types','price-periods','rate-adjustment-types','rate-bases','rate-cost-components','rate-source-types','validation-statuses'),
);
