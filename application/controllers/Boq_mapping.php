<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Boq_mapping extends Authorized_Controller
{
	protected $required_permission='boq.map';

	public function __construct()
	{
		parent::__construct();$this->load->model('Boq_mapping_model');
	}

	public function index()
	{
		$this->render('boq_mapping/index',array('page_title'=>'BOQ-to-UCD Mapping','page_subtitle'=>'Manual mapping progress by BOQ','boqs'=>$this->Boq_mapping_model->boqs(),'page_styles'=>array('assets/plugins/datatables/css/dataTables.bootstrap5.min.css'),'page_scripts'=>array('assets/plugins/datatables/js/dataTables.min.js','assets/plugins/datatables/js/dataTables.bootstrap5.min.js','assets/js/modules/master-data.js'),'breadcrumbs'=>array(array('label'=>'BOQ Mapping'))));
	}

	public function view($boq_id)
	{
		$boq=$this->boq_or_404($boq_id);$this->render('boq_mapping/view',array('page_title'=>'Map '.$boq->boq_code,'page_subtitle'=>$boq->boq_name,'boq'=>$boq,'items'=>$this->Boq_mapping_model->items($boq_id),'page_styles'=>array('assets/plugins/datatables/css/dataTables.bootstrap5.min.css'),'page_scripts'=>array('assets/plugins/datatables/js/dataTables.min.js','assets/plugins/datatables/js/dataTables.bootstrap5.min.js','assets/js/modules/master-data.js'),'breadcrumbs'=>array(array('label'=>'BOQ Mapping','url'=>site_url('boq-mapping')),array('label'=>$boq->boq_code))));
	}

	public function item($boq_id,$item_id)
	{
		$boq=$this->boq_or_404($boq_id);$item=$this->item_or_404($boq_id,$item_id);$search=trim((string)$this->input->get('q',TRUE));
		$this->render('boq_mapping/item',array('page_title'=>'Map BOQ Line '.$item->line_no,'page_subtitle'=>$boq->boq_code,'boq'=>$boq,'item'=>$item,'mapping'=>$this->Boq_mapping_model->mapping($item_id),'candidates'=>$this->Boq_mapping_model->candidates($item_id),'results'=>$this->Boq_mapping_model->search_cost_items($search,$item->uom_id,50),'search'=>$search,'history'=>$this->Boq_mapping_model->history($item_id),'breadcrumbs'=>array(array('label'=>'BOQ Mapping','url'=>site_url('boq-mapping')),array('label'=>$boq->boq_code,'url'=>site_url('boq-mapping/'.$boq_id)),array('label'=>'Line '.$item->line_no))));
	}

	public function add_candidate($boq_id,$item_id)
	{
		$this->post_only();$this->boq_or_404($boq_id);$this->item_or_404($boq_id,$item_id);$revision_id=(int)$this->input->post('cost_item_revision_id');
		if($revision_id<1||!$this->Boq_mapping_model->add_candidate($item_id,$revision_id,$this->current_user->user_id))show_error('The selected current standard cost item is invalid.',422,'Invalid Candidate');
		$this->success('Candidate added.',$boq_id,$item_id);
	}

	public function candidate_status($boq_id,$item_id,$candidate_id)
	{
		$this->post_only();$this->boq_or_404($boq_id);$this->item_or_404($boq_id,$item_id);
		if(!$this->Boq_mapping_model->toggle_candidate($item_id,$candidate_id,$this->current_user->user_id))show_error('A selected candidate cannot be disabled.',409,'Candidate In Use');
		$this->success('Candidate availability updated.',$boq_id,$item_id);
	}

	public function select($boq_id,$item_id,$candidate_id)
	{
		$this->post_only();$this->boq_or_404($boq_id);$this->item_or_404($boq_id,$item_id);$notes=$this->nullable_post('mapping_notes');
		if(!$this->Boq_mapping_model->select_candidate($item_id,$candidate_id,$notes,$this->current_user->user_id))show_error('The candidate is invalid or inactive.',422,'Invalid Selection');
		$this->success('Mapping proposed. Review and confirm it when ready.',$boq_id,$item_id);
	}

	public function action($boq_id,$item_id,$action)
	{
		$this->post_only();$this->boq_or_404($boq_id);$this->item_or_404($boq_id,$item_id);if(!in_array($action,array('confirm','reject','reopen'),TRUE))show_404();$comments=$this->nullable_post('comments');
		if($action==='reject'&&$comments===NULL)show_error('Rejection comments are required.',422,'Comments Required');
		if(!$this->Boq_mapping_model->mapping_action($item_id,$action,$comments,$this->current_user->user_id))show_error('The requested mapping transition is not valid.',409,'Invalid Transition');
		$this->success('Mapping status updated.',$boq_id,$item_id);
	}

	private function boq_or_404($id){$row=$this->Boq_mapping_model->boq($id);if(!$row)show_404();return$row;}
	private function item_or_404($boq,$id){$row=$this->Boq_mapping_model->item($boq,$id);if(!$row)show_404();return$row;}
	private function post_only(){if($this->input->method(TRUE)!=='POST')show_error('Method Not Allowed',405);}
	private function nullable_post($key){$value=trim((string)$this->input->post($key,TRUE));return$value===''?NULL:$value;}
	private function success($message,$boq,$item){$this->session->set_flashdata('mapping_success',$message);redirect('boq-mapping/'.$boq.'/items/'.$item);}
}
