<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Unit_rates extends Authorized_Controller
{
	protected $required_permission='unit_rates.view';
	private $types=array('material','labor','equipment','allowance');

	public function __construct(){parent::__construct();$this->load->model('Unit_rate_model');$this->load->model('Crew_productivity_model');$this->load->library('form_validation');}

	public function index()
	{
		$this->render('unit_rates/index',array('page_title'=>'Unit Rate Build-Up','page_subtitle'=>'Current resource quantities, rates, additions, and reconciled unit rates','items'=>$this->Unit_rate_model->all_current(),'page_styles'=>array('assets/plugins/datatables/css/dataTables.bootstrap5.min.css'),'page_scripts'=>array('assets/plugins/datatables/js/dataTables.min.js','assets/plugins/datatables/js/dataTables.bootstrap5.min.js','assets/js/modules/master-data.js'),'breadcrumbs'=>array(array('label'=>'Unit Rate Build-Up'))));
	}

	public function view($cost_item_id)
	{
		$item=$this->item_or_404($cost_item_id);$rid=$item->cost_item_revision_id;
		$materials=$this->Unit_rate_model->material_components($rid);$labor=$this->Unit_rate_model->labor_components($rid);$equipment=$this->Unit_rate_model->equipment_components($rid);$allowances=$this->Unit_rate_model->allowance_components($rid);$baseline=$this->Unit_rate_model->baseline($rid);
		$this->render('unit_rates/view',array('page_title'=>$item->enterprise_cost_code?:$item->cost_item_uid,'page_subtitle'=>$item->standard_item_name,'item'=>$item,'materials'=>$materials,'labor'=>$labor,'equipment'=>$equipment,'allowances'=>$allowances,'baseline'=>$baseline,'totals'=>$this->totals($materials,$labor,$equipment,$allowances,$baseline),'build_up'=>$this->Crew_productivity_model->build_up($rid),'productivities'=>$this->Crew_productivity_model->productivities($rid),'can_manage'=>in_array('unit_rates.manage',$this->current_permissions,TRUE)&&$item->revision_status==='DRAFT','page_styles'=>array('assets/plugins/datatables/css/dataTables.bootstrap5.min.css'),'page_scripts'=>array('assets/plugins/datatables/js/dataTables.min.js','assets/plugins/datatables/js/dataTables.bootstrap5.min.js','assets/js/modules/master-data.js'),'breadcrumbs'=>array(array('label'=>'Unit Rate Build-Up','url'=>site_url('unit-rates')),array('label'=>$item->enterprise_cost_code?:$item->cost_item_uid))));
	}

	public function add($cost_item_id,$type)
	{
		$this->authorize('unit_rates.manage');$item=$this->editable_item($cost_item_id);$this->handle_resource_form($item,$this->valid_type($type),NULL);
	}

	public function edit($cost_item_id,$type,$component_id)
	{
		$this->authorize('unit_rates.manage');$item=$this->editable_item($cost_item_id);$type=$this->valid_type($type);$component=$this->Unit_rate_model->find_component($type,$item->cost_item_revision_id,$component_id);if($component===NULL)show_404();$this->handle_resource_form($item,$type,$component);
	}

	public function delete($cost_item_id,$type,$component_id)
	{
		$this->authorize('unit_rates.manage');$item=$this->editable_item($cost_item_id);$type=$this->valid_type($type);$component=$this->Unit_rate_model->find_component($type,$item->cost_item_revision_id,$component_id);if($component===NULL)show_404();if($type==='labor'&&$this->Crew_productivity_model->build_up($item->cost_item_revision_id)->labor_source_method==='CREW_DERIVED')show_error('Crew-derived labor is locked. Convert the build-up to Manual before deleting labor rows.',409,'Crew-Derived Labor');
		if($this->input->method(TRUE)==='POST'){$this->db->trans_begin();$deleted=$this->Unit_rate_model->delete_component($type,$item->cost_item_revision_id,$component_id);$labor_updated=$type!=='labor'||($deleted&&$this->Crew_productivity_model->mark_manual($item->cost_item_revision_id,$this->current_user->user_id));if(!$deleted||!$labor_updated||$this->db->trans_status()===FALSE){$this->db->trans_rollback();show_error('The component could not be deleted.',409,'Deletion Failed');}$this->db->trans_commit();log_message('info','Unit-rate component deleted: revision='.(int)$item->cost_item_revision_id.' type='.$type.' component='.(int)$component_id.' actor='.(int)$this->current_user->user_id);$this->session->set_flashdata('unit_rate_success',ucfirst($type).' component deleted successfully.');redirect('unit-rates/'.$item->cost_item_id);}if($this->input->method(TRUE)!=='GET')show_error('Method Not Allowed',405);
		$this->render('unit_rates/delete',array('page_title'=>'Delete '.ucfirst($type).' Component','page_subtitle'=>$item->enterprise_cost_code?:$item->cost_item_uid,'item'=>$item,'type'=>$type,'component_label'=>$this->Unit_rate_model->component_label($type,$item->cost_item_revision_id,$component_id),'breadcrumbs'=>array(array('label'=>'Unit Rate Build-Up','url'=>site_url('unit-rates')),array('label'=>$item->enterprise_cost_code?:$item->cost_item_uid,'url'=>site_url('unit-rates/'.$item->cost_item_id)),array('label'=>'Delete '.ucfirst($type)))));
	}

	private function handle_resource_form($item,$type,$component)
	{
		$form_error=NULL;if($type==='labor'&&$this->Crew_productivity_model->build_up($item->cost_item_revision_id)->labor_source_method==='CREW_DERIVED')show_error('Crew-derived labor is locked. Convert the build-up to Manual before editing labor rows.',409,'Crew-Derived Labor');$foreign_fields=array('material'=>'material_variant_id','labor'=>'labor_id','equipment'=>'equipment_id','allowance'=>'resource_allowance_type_id');$foreign=$foreign_fields[$type];
		$this->form_validation->set_rules($foreign,ucwords(str_replace('_',' ',$foreign)),'trim|required|integer');
		if($type==='material'){$this->form_validation->set_rules('quantity_per_item_unit','Quantity per item unit','trim|required|numeric|greater_than[0]');$this->form_validation->set_rules('uom_id','UOM','trim|required|integer');$this->form_validation->set_rules('waste_percentage','Waste percentage','trim|required|numeric|greater_than_equal_to[0]');}
		elseif($type==='labor'){$this->form_validation->set_rules('crew_quantity','Crew quantity','trim|required|numeric|greater_than[0]');$this->form_validation->set_rules('labor_days_per_item_unit','Labor days per item unit','trim|required|numeric|greater_than_equal_to[0]');$this->form_validation->set_rules('labor_hours_per_unit','Labor hours per unit','trim|required|numeric|greater_than_equal_to[0]');}
		elseif($type==='equipment'){$this->form_validation->set_rules('equipment_quantity','Equipment quantity','trim|required|numeric|greater_than[0]');$this->form_validation->set_rules('equipment_hours_per_unit','Equipment hours per unit','trim|required|numeric|greater_than_equal_to[0]');}
		else $this->form_validation->set_rules('amount_per_item_unit','Amount per item unit','trim|required|numeric|greater_than_equal_to[0]');
		$this->form_validation->set_rules('notes','Notes','trim|max_length[500]');

		if($this->input->method(TRUE)==='POST'&&$this->form_validation->run()){
			$data=array('cost_item_revision_id'=>(int)$item->cost_item_revision_id);$duplicate_value=NULL;
			if($type==='material'){$variant=$this->Unit_rate_model->material_variant($this->input->post('material_variant_id'));if($variant===NULL)$form_error='The selected material variant is invalid.';else{$data+=array('material_id'=>(int)$variant->material_id,'material_variant_id'=>(int)$variant->material_variant_id,'quantity_per_item_unit'=>(float)$this->input->post('quantity_per_item_unit'),'uom_id'=>(int)$this->input->post('uom_id'),'waste_percentage'=>(float)$this->input->post('waste_percentage'),'is_primary'=>$this->input->post('is_primary')?1:0,'notes'=>$this->nullable_text('notes'));$duplicate_value=$data['material_id'];if(!$this->Unit_rate_model->reference_exists('ref_uom','uom_id',$data['uom_id']))$form_error='The selected UOM is invalid.';}}
			elseif($type==='labor'){$data+=array('labor_id'=>(int)$this->input->post('labor_id'),'crew_quantity'=>(float)$this->input->post('crew_quantity'),'labor_days_per_item_unit'=>(float)$this->input->post('labor_days_per_item_unit'),'labor_hours_per_unit'=>(float)$this->input->post('labor_hours_per_unit'),'notes'=>$this->nullable_text('notes'));$duplicate_value=$data['labor_id'];if(!$this->Unit_rate_model->reference_exists('labor_master','labor_id',$data['labor_id']))$form_error='The selected labor craft is invalid.';elseif($data['labor_days_per_item_unit']==0.0&&$data['labor_hours_per_unit']==0.0)$form_error='Labor days or labor hours must be greater than zero.';}
			elseif($type==='equipment'){$data+=array('equipment_id'=>(int)$this->input->post('equipment_id'),'equipment_quantity'=>(float)$this->input->post('equipment_quantity'),'equipment_hours_per_unit'=>(float)$this->input->post('equipment_hours_per_unit'),'notes'=>$this->nullable_text('notes'));$duplicate_value=$data['equipment_id'];if(!$this->Unit_rate_model->reference_exists('equipment_master','equipment_id',$data['equipment_id']))$form_error='The selected equipment is invalid.';}
			else{$data+=array('resource_allowance_type_id'=>(int)$this->input->post('resource_allowance_type_id'),'amount_per_item_unit'=>(float)$this->input->post('amount_per_item_unit'),'notes'=>$this->nullable_text('notes'));$duplicate_value=$data['resource_allowance_type_id'];if(!$this->Unit_rate_model->reference_exists('ref_resource_allowance_type','resource_allowance_type_id',$data['resource_allowance_type_id']))$form_error='The selected allowance type is invalid.';}
			$id=$component===NULL?NULL:$component->{$this->component_key($type)};
			if($form_error===NULL&&$this->Unit_rate_model->duplicate_component($type,$item->cost_item_revision_id,$duplicate_value,$id))$form_error='That resource already exists in this build-up.';
			if($form_error===NULL&&$this->Unit_rate_model->save_component($type,$data,$id)){if($type==='labor')$this->Crew_productivity_model->mark_manual($item->cost_item_revision_id,$this->current_user->user_id);$this->session->set_flashdata('unit_rate_success',ucfirst($type).' component saved successfully.');redirect('unit-rates/'.$item->cost_item_id);}
			elseif($form_error===NULL)$form_error='Unable to save the component.';
		}

		$this->render('unit_rates/resource_form',array('page_title'=>($component?'Edit ':'Add ').ucfirst($type).' Component','page_subtitle'=>$item->enterprise_cost_code?:$item->cost_item_uid,'item'=>$item,'type'=>$type,'component'=>$component,'options'=>$this->resource_options(),'form_error'=>$form_error,'breadcrumbs'=>array(array('label'=>'Unit Rate Build-Up','url'=>site_url('unit-rates')),array('label'=>$item->enterprise_cost_code?:$item->cost_item_uid,'url'=>site_url('unit-rates/'.$item->cost_item_id)),array('label'=>ucfirst($type)))));
	}

	private function resource_options()
	{
		$m=$this->Unit_rate_model;return array('variants'=>$m->material_variants(),'uoms'=>$m->simple_options('ref_uom','uom_id',"CONCAT(uom_code,' — ',uom_name)"),'labor'=>$m->simple_options('labor_master','labor_id',"CONCAT(labor_code,' — ',labor_name)"),'equipment'=>$m->simple_options('equipment_master','equipment_id',"CONCAT(equipment_code,' — ',equipment_name)"),'allowances'=>$m->simple_options('ref_resource_allowance_type','resource_allowance_type_id',"CONCAT(allowance_type_code,' — ',allowance_type_name)"));
	}

	private function totals($materials,$labor,$equipment,$allowances,$baseline)
	{
		$t=array('material'=>0.0,'labor'=>0.0,'tools'=>0.0,'consumables'=>0.0,'non_material'=>0.0,'missing_rates'=>0,'equipment_count'=>count($equipment));
		foreach($materials as$r){if($r->component_amount===NULL)$t['missing_rates']++;else$t['material']+=(float)$r->component_amount;}
		foreach($labor as$r){if($r->component_amount===NULL)$t['missing_rates']++;else$t['labor']+=(float)$r->component_amount;}
		foreach($allowances as$r){if($r->allowance_type_code==='TOOLS_EQUIPMENT')$t['tools']+=(float)$r->amount_per_item_unit;elseif($r->allowance_type_code==='OTHER_CONSUMABLES')$t['consumables']+=(float)$r->amount_per_item_unit;elseif($r->allowance_type_code==='NON_MATERIAL_ACTIVITY_INPUT')$t['non_material']+=(float)$r->amount_per_item_unit;}
		$t['direct']=$t['material']+$t['labor'];$t['additions']=$t['tools']+$t['consumables']+$t['non_material'];$t['final']=$t['direct']+$t['additions'];$t['baseline']=$baseline?(float)$baseline->unit_rate:NULL;$t['variance']=$t['baseline']===NULL?NULL:$t['baseline']-$t['final'];return $t;
	}

	private function valid_type($type){if(!in_array($type,$this->types,TRUE))show_404();return$type;}
	private function component_key($type){return array('material'=>'cost_item_material_id','labor'=>'cost_item_labor_id','equipment'=>'cost_item_equipment_id','allowance'=>'cost_item_resource_allowance_id')[$type];}
	private function nullable_text($field){$v=trim((string)$this->input->post($field,TRUE));return$v===''?NULL:$v;}
	private function item_or_404($id){$item=$this->Unit_rate_model->find_current($id);if($item===NULL)show_404();return$item;}
	private function editable_item($id){$item=$this->item_or_404($id);if($item->revision_status!=='DRAFT')show_error('Only current Draft build-ups can be changed.',409,'Revision Locked');return$item;}
}
