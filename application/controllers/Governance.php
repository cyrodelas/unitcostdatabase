<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Governance extends Authenticated_Controller
{
	public function __construct(){parent::__construct();$this->load->model('Governance_model');}
	public function review(){$this->authorize('governance.review');$this->render('governance/queue',array('page_title'=>'Review Queue','page_subtitle'=>'Draft revisions awaiting technical review','queue_type'=>'review','items'=>$this->Governance_model->review_queue(),'breadcrumbs'=>array(array('label'=>'Governance'),array('label'=>'Review Queue'))));}
	public function approval(){$this->authorize('governance.approve');$this->render('governance/queue',array('page_title'=>'Approval Queue','page_subtitle'=>'Reviewed revisions awaiting approval or publication','queue_type'=>'approval','items'=>$this->Governance_model->approval_queue(),'breadcrumbs'=>array(array('label'=>'Governance'),array('label'=>'Approval Queue'))));}
	public function audit(){$this->authorize('governance.audit');$this->render('governance/audit',array('page_title'=>'Governance Audit Trail','page_subtitle'=>'Immutable workflow and revision events','events'=>$this->Governance_model->audit_trail(),'breadcrumbs'=>array(array('label'=>'Governance'),array('label'=>'Audit Trail'))));}
	public function action($revision_id,$action)
	{
		if($this->input->method(TRUE)!=='POST')show_error('Method Not Allowed',405);
		$comments=trim((string)$this->input->post('comments',TRUE));$actor=(int)$this->current_user->user_id;$ok=FALSE;$return='standard-cost-items';
		switch($action){
			case'submit':$this->authorize('standard_cost_items.manage');$ok=$this->Governance_model->transition($revision_id,'DRAFT','FOR_REVIEW','TECHNICAL_REVIEW','SUBMITTED',$comments,$actor);break;
			case'recommend':$this->authorize('governance.review');$this->authorize('standard_cost_items.review');$this->require_comments($comments,'Reviewer comments');$ok=$this->Governance_model->recommend($revision_id,$comments,$actor);$return='governance/review';break;
			case'return-draft':$this->authorize('governance.review');$this->authorize('standard_cost_items.review');$this->require_comments($comments,'Reviewer comments');$ok=$this->Governance_model->transition($revision_id,'FOR_REVIEW','DRAFT','TECHNICAL_REVIEW','RETURNED',$comments,$actor);$return='governance/review';break;
			case'approve':$this->authorize('governance.approve');$this->authorize('standard_cost_items.approve');$this->require_comments($comments,'Approver comments');$ok=$this->Governance_model->approve($revision_id,$comments,$actor);$return='governance/approval';break;
			case'return-review':$this->authorize('governance.approve');$this->authorize('standard_cost_items.approve');$this->require_comments($comments,'Approver comments');$ok=$this->Governance_model->return_to_review($revision_id,$comments,$actor);$return='governance/approval';break;
			case'publish':$this->authorize('governance.approve');$this->authorize('standard_cost_items.publish');$this->require_comments($comments,'Publishing comments');$ok=$this->Governance_model->publish($revision_id,$comments,$actor);$return='governance/approval';break;
			case'revise':$this->authorize('standard_cost_items.manage');$this->require_comments($comments,'A change reason');$ok=$this->Governance_model->create_revision($revision_id,$comments,$actor);break;
			default:show_404();
		}
		if(!$ok)show_error('The requested workflow transition is not valid for the current revision state.',409,'Invalid Transition');
		$this->session->set_flashdata('governance_success','Governance action completed successfully.');redirect($return);
	}
	private function require_comments($comments,$label){if($comments==='')show_error($label.' are required.',422,'Comments Required');}
}
