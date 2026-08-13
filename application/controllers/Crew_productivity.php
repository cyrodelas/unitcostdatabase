<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Crew_productivity extends Authorized_Controller
{
	protected $required_permission='unit_rates.view';

	public function __construct(){parent::__construct();$this->load->model('Crew_productivity_model');$this->load->library('form_validation');}

	public function create($cost_item_id){$this->authorize('unit_rates.manage');$this->handle_form($this->draft_item($cost_item_id),NULL);}
	public function edit($cost_item_id,$productivity_id){$this->authorize('unit_rates.manage');$item=$this->draft_item($cost_item_id);$p=$this->Crew_productivity_model->find_productivity($item->cost_item_revision_id,$productivity_id);if(!$p)show_404();$this->handle_form($item,$p);}

	public function apply($cost_item_id,$productivity_id)
	{
		$this->authorize('unit_rates.manage');$item=$this->draft_item($cost_item_id);$preview=$this->Crew_productivity_model->preview($item->cost_item_revision_id,$productivity_id);if($preview['error']!==NULL)show_error($preview['error'],422,'Crew Application Unavailable');
		if($this->input->method(TRUE)==='POST'){
			if($this->input->post('confirm_replace')!=='1')show_error('Confirm replacement of the current labor components.',422,'Confirmation Required');
			if(!$this->Crew_productivity_model->apply_crew($item->cost_item_revision_id,$productivity_id,$this->current_user->user_id))show_error('Unable to apply the crew-derived labor build-up.',500);
			$this->session->set_flashdata('unit_rate_success','Crew productivity applied. Labor components were regenerated from the crew snapshot.');redirect('unit-rates/'.$cost_item_id);
		}
		$this->render('crew_productivity/apply',array('page_title'=>'Apply Crew Productivity','page_subtitle'=>$item->enterprise_cost_code?:$item->cost_item_uid,'item'=>$item,'preview'=>$preview,'breadcrumbs'=>array(array('label'=>'Unit Rate Build-Up','url'=>site_url('unit-rates')),array('label'=>$item->enterprise_cost_code?:$item->cost_item_uid,'url'=>site_url('unit-rates/'.$cost_item_id)),array('label'=>'Apply Crew'))));
	}

	public function manual($cost_item_id)
	{
		$this->authorize('unit_rates.manage');if($this->input->method(TRUE)!=='POST')show_error('Method Not Allowed',405);$item=$this->draft_item($cost_item_id);
		if(!$this->Crew_productivity_model->convert_to_manual($item->cost_item_revision_id,$this->current_user->user_id))show_error('Unable to convert the labor build-up.',500);
		$this->session->set_flashdata('unit_rate_success','Labor build-up converted to Manual. Existing labor rows were retained and may now be edited.');redirect('unit-rates/'.$cost_item_id);
	}

	private function handle_form($item,$p)
	{
		$form_error=NULL;$this->form_validation->set_rules('crew_id','Crew','trim|required|integer');$this->form_validation->set_rules('output_quantity','Output quantity','trim|required|numeric|greater_than[0]');$this->form_validation->set_rules('duration_quantity','Duration in days','trim|required|numeric|greater_than[0]');$this->form_validation->set_rules('productivity_source','Productivity source','trim|required|max_length[100]');$this->form_validation->set_rules('source_reference','Source reference','trim|required|max_length[255]');$this->form_validation->set_rules('effective_date','Effective date','trim|required|regex_match[/^\d{4}-\d{2}-\d{2}$/]');$this->form_validation->set_rules('notes','Notes','trim');$day=$this->Crew_productivity_model->day_uom();if(!$day)$form_error='The governed day UOM is unavailable.';
		if($this->input->method(TRUE)==='POST'&&$this->form_validation->run()&&$form_error===NULL){$crew=(int)$this->input->post('crew_id');if(!$this->Crew_productivity_model->crew_exists($crew))$form_error='The selected crew is invalid or inactive.';else{$data=array('output_quantity'=>(float)$this->input->post('output_quantity'),'output_uom_id'=>(int)$item->uom_id,'duration_quantity'=>(float)$this->input->post('duration_quantity'),'duration_uom_id'=>(int)$day->uom_id,'crew_id'=>$crew,'duration_hours'=>NULL,'crew_description'=>NULL,'productivity_source'=>trim((string)$this->input->post('productivity_source',TRUE)),'source_reference'=>trim((string)$this->input->post('source_reference',TRUE)),'effective_date'=>$this->input->post('effective_date',TRUE),'is_benchmark'=>$this->input->post('is_benchmark')?1:0,'notes'=>$this->nullable_text('notes'));$id=$this->Crew_productivity_model->save_productivity($item->cost_item_revision_id,$data,$p?$p->productivity_id:NULL);if($id){$this->session->set_flashdata('unit_rate_success','Productivity record saved successfully.'.($p?' Reapply it if it is the selected costing record.':''));redirect('unit-rates/'.$item->cost_item_id);}else$form_error='Unable to save the productivity record.';}}
		$this->render('crew_productivity/form',array('page_title'=>$p?'Edit Crew Productivity':'Add Crew Productivity','page_subtitle'=>$item->enterprise_cost_code?:$item->cost_item_uid,'item'=>$item,'productivity'=>$p,'crews'=>$this->Crew_productivity_model->active_crews(),'day_uom'=>$day,'form_error'=>$form_error,'breadcrumbs'=>array(array('label'=>'Unit Rate Build-Up','url'=>site_url('unit-rates')),array('label'=>$item->enterprise_cost_code?:$item->cost_item_uid,'url'=>site_url('unit-rates/'.$item->cost_item_id)),array('label'=>$p?'Edit Productivity':'Add Productivity'))));
	}

	private function draft_item($id){$item=$this->Crew_productivity_model->find_item($id);if(!$item)show_404();if($item->revision_status!=='DRAFT')show_error('Only the current Draft revision can be changed.',409,'Revision Locked');return$item;}
	private function nullable_text($field){$v=trim((string)$this->input->post($field,TRUE));return$v===''?NULL:$v;}
}
