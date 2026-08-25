<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Projects extends Authorized_Controller
{
	protected $required_permission='projects.view';
	public function __construct(){parent::__construct();$this->load->model('Project_model');$this->load->library('form_validation');}

	public function index()
	{
		$this->render('projects/index',array('page_title'=>'Project Master','page_subtitle'=>'Project context for governed cost history','projects'=>$this->Project_model->all_projects(),'can_manage'=>in_array('projects.manage',$this->current_permissions,TRUE),'page_styles'=>array('assets/plugins/datatables/css/dataTables.bootstrap5.min.css'),'page_scripts'=>array('assets/plugins/datatables/js/dataTables.min.js','assets/plugins/datatables/js/dataTables.bootstrap5.min.js','assets/js/modules/master-data.js'),'breadcrumbs'=>array(array('label'=>'Project Master'))));
	}

	public function view($project_id)
	{
		$project=$this->project_or_404($project_id);
		$this->render('projects/view',array('page_title'=>$project->project_code,'page_subtitle'=>$project->project_name,'project'=>$project,'observations'=>$this->Project_model->observations($project_id),'dependencies'=>$this->Project_model->dependencies($project_id),'can_manage'=>in_array('projects.manage',$this->current_permissions,TRUE),'page_styles'=>array('assets/plugins/datatables/css/dataTables.bootstrap5.min.css'),'page_scripts'=>array('assets/plugins/datatables/js/dataTables.min.js','assets/plugins/datatables/js/dataTables.bootstrap5.min.js','assets/js/modules/master-data.js'),'breadcrumbs'=>array(array('label'=>'Project Master','url'=>site_url('projects')),array('label'=>$project->project_code))));
	}

	public function create(){$this->authorize('projects.manage');$this->handle_form(NULL);}
	public function edit($project_id){$this->authorize('projects.manage');$this->handle_form($this->project_or_404($project_id));}
	public function status($project_id){$this->authorize('projects.manage');if($this->input->method(TRUE)!=='POST')show_error('Method Not Allowed',405);$project=$this->project_or_404($project_id);$this->Project_model->set_active($project_id,!(bool)$project->is_active);$this->session->set_flashdata('project_success','Project '.($project->is_active?'deactivated.':'activated.'));redirect('projects/'.$project_id);}
	public function delete($project_id)
	{
		$this->authorize('projects.manage');if($this->input->method(TRUE)!=='POST')show_error('Method Not Allowed',405);$project=$this->project_or_404($project_id);
		$confirmation=trim((string)$this->input->post('confirmation',TRUE));if(!hash_equals((string)$project->project_code,$confirmation))show_error('Enter the exact project code to confirm permanent deletion.',422,'Confirmation Required');
		$result=$this->Project_model->delete_project($project_id);if($result==='BLOCKED')show_error('This project has BOQs, rate observations, source documents, or metrics. Deactivate it or resolve those governed dependencies first.',409,'Project Deletion Blocked');if($result!==TRUE)show_error('The project could not be deleted.',500,'Deletion Failed');
		log_message('info','Project deleted: id='.(int)$project_id.' code='.$project->project_code.' actor='.(int)$this->current_user->user_id);$this->session->set_flashdata('project_success','Project '.$project->project_code.' was permanently deleted.');redirect('projects');
	}

	private function handle_form($project)
	{
		$form_error=NULL;
		$this->form_validation->set_rules('project_code','Project code','trim|required|max_length[50]');
		$this->form_validation->set_rules('project_name','Project name','trim|required|max_length[255]');
		$this->form_validation->set_rules('project_type_id','Project type','trim|integer');$this->form_validation->set_rules('market_segment_id','Market segment','trim|integer');$this->form_validation->set_rules('location_id','Location','trim|integer');
		foreach(array('building_type'=>'Building type','city'=>'City','province'=>'Province','region'=>'Region','country'=>'Country')as$field=>$label)$this->form_validation->set_rules($field,$label,'trim|max_length[100]');
		$this->form_validation->set_rules('gross_floor_area','Gross floor area','trim|numeric|greater_than_equal_to[0]');
		$this->form_validation->set_rules('floor_count','Floor count','trim|integer|greater_than_equal_to[0]');
		$this->form_validation->set_rules('start_date','Start date','trim|callback_valid_date');
		$this->form_validation->set_rules('completion_date','Completion date','trim|callback_valid_date');
		if($this->input->method(TRUE)==='POST'&&$this->form_validation->run()){
			$data=array('project_code'=>strtoupper(trim((string)$this->input->post('project_code',TRUE))),'project_name'=>trim((string)$this->input->post('project_name',TRUE)),'project_type_id'=>$this->nullable_integer('project_type_id'),'market_segment_id'=>$this->nullable_integer('market_segment_id'),'location_id'=>$this->nullable_integer('location_id'),'project_type'=>NULL,'building_type'=>$this->nullable_text('building_type'),'city'=>$this->nullable_text('city'),'province'=>$this->nullable_text('province'),'region'=>$this->nullable_text('region'),'country'=>$this->nullable_text('country'),'gross_floor_area'=>$this->nullable_number('gross_floor_area'),'floor_count'=>$this->nullable_integer('floor_count'),'start_date'=>$this->nullable_text('start_date'),'completion_date'=>$this->nullable_text('completion_date'),'is_active'=>$this->input->post('is_active')?1:0);
			$id=$project->project_id??NULL;
			if($data['project_type_id']!==NULL&&!$this->Project_model->reference_exists('ref_project_type','project_type_id',$data['project_type_id']))$form_error='The selected project type is invalid.';
			elseif(($data['project_type_id']===NULL)!==($data['market_segment_id']===NULL))$form_error='Project Type and Market Segment must be selected together.';
			elseif($data['project_type_id']!==NULL&&!$this->Project_model->project_market_pair_exists($data['project_type_id'],$data['market_segment_id']))$form_error='The selected Market Segment is not applicable to the selected Project Type.';
			elseif($data['location_id']!==NULL&&!$this->Project_model->reference_exists('ref_location','location_id',$data['location_id']))$form_error='The selected location is invalid.';
			elseif($this->Project_model->code_exists($data['project_code'],$id))$form_error='That project code already exists.';
			elseif($data['start_date']&&$data['completion_date']&&$data['completion_date']<$data['start_date'])$form_error='Completion date cannot be earlier than start date.';
			else{$success=$project===NULL?$this->Project_model->create($data):$this->Project_model->update($id,$data);if($success){$target=$project===NULL?$success:$id;$this->session->set_flashdata('project_success','Project saved successfully.');redirect('projects/'.$target);}$form_error='Unable to save the project.';}
		}
		$this->render('projects/form',array('page_title'=>$project?'Edit Project':'Add Project','page_subtitle'=>'Project master record','project'=>$project,'project_types'=>$this->Project_model->project_types(),'market_segments'=>$this->Project_model->project_market_segments(),'locations'=>$this->Project_model->locations(),'form_error'=>$form_error,'page_scripts'=>array('assets/js/modules/cost-item-classification.js'),'breadcrumbs'=>array(array('label'=>'Project Master','url'=>site_url('projects')),array('label'=>$project?'Edit':'Add'))));
	}

	public function valid_date($value){if($value==='')return TRUE;$d=DateTime::createFromFormat('Y-m-d',$value);$valid=$d&&$d->format('Y-m-d')===$value;if(!$valid)$this->form_validation->set_message('valid_date','The {field} field must be a valid date.');return $valid;}
	private function nullable_text($field){$v=trim((string)$this->input->post($field,TRUE));return $v===''?NULL:$v;}
	private function nullable_number($field){$v=trim((string)$this->input->post($field,TRUE));return $v===''?NULL:$v;}
	private function nullable_integer($field){$v=trim((string)$this->input->post($field,TRUE));return $v===''?NULL:(int)$v;}
	private function project_or_404($id){$p=$this->Project_model->find($id);if($p===NULL)show_404();return$p;}
}
