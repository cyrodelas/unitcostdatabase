<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Governance_model extends CI_Model
{
	public function review_queue()
	{
		return $this->queue_query()->where('r.revision_status', 'FOR_REVIEW')
			->where("NOT (COALESCE((SELECT h.workflow_stage FROM cost_item_approval_history h WHERE h.cost_item_revision_id=r.cost_item_revision_id ORDER BY h.approval_history_id DESC LIMIT 1),'')='TECHNICAL_REVIEW' AND COALESCE((SELECT h.action FROM cost_item_approval_history h WHERE h.cost_item_revision_id=r.cost_item_revision_id ORDER BY h.approval_history_id DESC LIMIT 1),'')='APPROVED')", NULL, FALSE)
			->order_by('r.updated_at', 'ASC')->get()->result();
	}

	public function approval_queue()
	{
		return $this->queue_query()->group_start()->where('r.revision_status', 'APPROVED')
			->or_group_start()->where('r.revision_status', 'FOR_REVIEW')
			->where("(SELECT h.workflow_stage FROM cost_item_approval_history h WHERE h.cost_item_revision_id=r.cost_item_revision_id ORDER BY h.approval_history_id DESC LIMIT 1)='TECHNICAL_REVIEW'", NULL, FALSE)
			->where("(SELECT h.action FROM cost_item_approval_history h WHERE h.cost_item_revision_id=r.cost_item_revision_id ORDER BY h.approval_history_id DESC LIMIT 1)='APPROVED'", NULL, FALSE)
			->group_end()->group_end()->order_by('r.updated_at', 'ASC')->get()->result();
	}

	private function queue_query()
	{
		return $this->db->select("i.cost_item_id,i.cost_item_uid,r.cost_item_revision_id,r.revision_no,r.enterprise_cost_code,r.standard_item_name,r.revision_status,r.updated_at,(SELECT h.comments FROM cost_item_approval_history h WHERE h.cost_item_revision_id=r.cost_item_revision_id ORDER BY h.approval_history_id DESC LIMIT 1) AS latest_comments,(SELECT COALESCE(u.display_name,u.username) FROM cost_item_approval_history h LEFT JOIN app_user u ON u.user_id=h.action_by WHERE h.cost_item_revision_id=r.cost_item_revision_id ORDER BY h.approval_history_id DESC LIMIT 1) AS latest_actor", FALSE)
			->from('standard_cost_item_revision r')->join('standard_cost_item i', 'i.cost_item_id=r.cost_item_id')->where('r.is_current', 1);
	}

	public function audit_trail()
	{
		return $this->db->select('a.*,i.cost_item_uid,r.revision_no,r.enterprise_cost_code,COALESCE(u.display_name,u.username) AS actor_name', FALSE)->from('cost_item_audit_log a')
			->join('standard_cost_item i', 'i.cost_item_id=a.cost_item_id', 'left')->join('standard_cost_item_revision r', 'r.cost_item_revision_id=a.cost_item_revision_id', 'left')->join('app_user u', 'u.user_id=a.changed_by', 'left')
			->order_by('a.changed_at', 'DESC')->order_by('a.audit_id', 'DESC')->limit(1000)->get()->result();
	}

	public function history($revision_id)
	{
		return $this->db->select('h.*,COALESCE(u.display_name,u.username,h.action_by) AS action_by', FALSE)->from('cost_item_approval_history h')->join('app_user u', 'u.user_id=h.action_by', 'left')->where('h.cost_item_revision_id', (int) $revision_id)->order_by('h.approval_history_id', 'DESC')->get()->result();
	}

	public function transition($revision_id, $expected, $new, $stage, $action, $comments, $actor)
	{
		$r = $this->db->where('cost_item_revision_id', (int) $revision_id)->where('is_current', 1)->get('standard_cost_item_revision')->row();
		if (!$r || $r->revision_status !== $expected) return FALSE;
		$this->db->trans_start();
		$update = array('revision_status'=>$new);
		if ($new === 'APPROVED') $update += array('approved_at'=>date('Y-m-d H:i:s'),'approved_by'=>(int)$actor);
		if ($new === 'DRAFT') $update += array('approved_at'=>NULL,'approved_by'=>NULL);
		$this->db->where('cost_item_revision_id',(int)$revision_id)->where('revision_status',$expected)->update('standard_cost_item_revision',$update);
		if ($this->db->affected_rows() !== 1) { $this->db->trans_rollback(); return FALSE; }
		$this->history_row($revision_id,$stage,$action,$comments,$actor);
		$this->audit_row($r->cost_item_id,$revision_id,'WORKFLOW_TRANSITION','revision_status',$expected,$new,$actor);
		$this->db->trans_complete(); return $this->db->trans_status();
	}

	public function recommend($revision_id, $comments, $actor)
	{
		$r=$this->db->where('cost_item_revision_id',(int)$revision_id)->where('is_current',1)->where('revision_status','FOR_REVIEW')->get('standard_cost_item_revision')->row();
		if(!$r)return FALSE;
		$this->db->trans_start(); $this->history_row($revision_id,'TECHNICAL_REVIEW','APPROVED',$comments,$actor); $this->audit_row($r->cost_item_id,$revision_id,'WORKFLOW_TRANSITION','workflow_stage','FOR_REVIEW','FOR_APPROVAL',$actor); $this->db->trans_complete();
		return $this->db->trans_status();
	}

	public function approve($revision_id, $comments, $actor)
	{
		$latest=$this->db->where('cost_item_revision_id',(int)$revision_id)->order_by('approval_history_id','DESC')->limit(1)->get('cost_item_approval_history')->row();
		if(!$latest||$latest->workflow_stage!=='TECHNICAL_REVIEW'||$latest->action!=='APPROVED')return FALSE;
		return $this->transition($revision_id,'FOR_REVIEW','APPROVED','COMMITTEE_APPROVAL','APPROVED',$comments,$actor);
	}

	public function return_to_review($revision_id, $comments, $actor)
	{
		$r=$this->db->where('cost_item_revision_id',(int)$revision_id)->where('is_current',1)->where('revision_status','FOR_REVIEW')->get('standard_cost_item_revision')->row();if(!$r)return FALSE;
		$this->db->trans_start();$this->history_row($revision_id,'COMMITTEE_APPROVAL','RETURNED',$comments,$actor);$this->audit_row($r->cost_item_id,$revision_id,'WORKFLOW_TRANSITION','workflow_stage','FOR_APPROVAL','FOR_REVIEW',$actor);$this->db->trans_complete();return $this->db->trans_status();
	}

	public function publish($revision_id, $comments, $actor)
	{
		$r=$this->db->where('cost_item_revision_id',(int)$revision_id)->where('is_current',1)->where('revision_status','APPROVED')->get('standard_cost_item_revision')->row();
		if(!$r||!$r->enterprise_cost_code||$r->coding_status!=='CODED')return FALSE;
		return $this->transition($revision_id,'APPROVED','PUBLISHED','PUBLISHED','PUBLISHED',$comments,$actor);
	}

	public function create_revision($revision_id, $reason, $actor)
	{
		$old=$this->db->where('cost_item_revision_id',(int)$revision_id)->where('is_current',1)->where('revision_status','PUBLISHED')->get('standard_cost_item_revision')->row_array(); if(!$old)return FALSE;
		$next=str_pad((string)((int)$old['revision_no']+1),max(2,strlen($old['revision_no'])),'0',STR_PAD_LEFT); $item_id=$old['cost_item_id'];
		unset($old['cost_item_revision_id'],$old['created_at'],$old['updated_at']);
		$old=array_merge($old,array('revision_no'=>$next,'enterprise_cost_code'=>NULL,'revision_status'=>'DRAFT','coding_status'=>'READY_FOR_CODE','change_reason'=>$reason,'supersedes_revision_id'=>(int)$revision_id,'is_current'=>1,'created_by'=>(int)$actor,'approved_at'=>NULL,'approved_by'=>NULL,'effective_to'=>NULL));
		$this->db->trans_start();
		$this->db->where('cost_item_revision_id',(int)$revision_id)->where('revision_status','PUBLISHED')->update('standard_cost_item_revision',array('revision_status'=>'SUPERSEDED','is_current'=>0,'effective_to'=>date('Y-m-d')));
		if($this->db->affected_rows()!==1){$this->db->trans_rollback();return FALSE;}
		$this->db->insert('standard_cost_item_revision',$old); $new_id=(int)$this->db->insert_id();
		$this->clone_rows('cost_item_material','cost_item_material_id',$revision_id,$new_id);
		$labor_map=$this->clone_rows('cost_item_labor','cost_item_labor_id',$revision_id,$new_id);
		$this->clone_rows('cost_item_equipment','cost_item_equipment_id',$revision_id,$new_id);
		$this->clone_rows('cost_item_resource_allowance','cost_item_resource_allowance_id',$revision_id,$new_id);
		$productivity_map=$this->clone_rows('cost_item_productivity','productivity_id',$revision_id,$new_id);
		$this->clone_rows('cost_item_psmm_classification',NULL,$revision_id,$new_id);
		$this->clone_labor_build_up($revision_id,$new_id,$labor_map,$productivity_map,$actor);
		$this->clone_code($revision_id,$new_id,$next); $this->history_row($new_id,'NEW_COST_ITEM_REQUESTED','SUBMITTED',$reason,$actor);
		$this->audit_row($item_id,$revision_id,'REVISION_SUPERSEDED','revision_status','PUBLISHED','SUPERSEDED',$actor); $this->audit_row($item_id,$new_id,'REVISION_CREATED','revision_no',NULL,$next,$actor);
		$this->db->trans_complete(); return $this->db->trans_status()?$new_id:FALSE;
	}

	private function clone_rows($table,$pk,$old_id,$new_id){$map=array();foreach($this->db->where('cost_item_revision_id',(int)$old_id)->get($table)->result_array() as $row){$old_pk=$pk?(int)$row[$pk]:NULL;if($pk)unset($row[$pk]);unset($row['created_at']);$row['cost_item_revision_id']=$new_id;$this->db->insert($table,$row);if($pk)$map[$old_pk]=(int)$this->db->insert_id();}return$map;}
	private function clone_labor_build_up($old_id,$new_id,array$labor_map,array$productivity_map,$actor){if(!$this->db->table_exists('cost_item_labor_build_up'))return;$build=$this->db->where('cost_item_revision_id',(int)$old_id)->get('cost_item_labor_build_up')->row_array();if(!$build)return;unset($build['created_at'],$build['updated_at']);$build['cost_item_revision_id']=(int)$new_id;$old_productivity=(int)$build['costing_productivity_id'];$build['costing_productivity_id']=$old_productivity&&isset($productivity_map[$old_productivity])?$productivity_map[$old_productivity]:NULL;$build['applied_by']=(int)$actor;$build['applied_at']=date('Y-m-d H:i:s');$this->db->insert('cost_item_labor_build_up',$build);if($build['labor_source_method']!=='CREW_DERIVED')return;foreach($this->db->where('cost_item_revision_id',(int)$old_id)->get('cost_item_labor_derivation')->result_array()as$row){$old_labor=(int)$row['cost_item_labor_id'];if(!isset($labor_map[$old_labor]))continue;unset($row['labor_derivation_id'],$row['created_at']);$old_p=(int)$row['productivity_id'];$row['cost_item_revision_id']=(int)$new_id;$row['cost_item_labor_id']=$labor_map[$old_labor];$row['productivity_id']=$old_p&&isset($productivity_map[$old_p])?$productivity_map[$old_p]:NULL;$row['created_by']=(int)$actor;$this->db->insert('cost_item_labor_derivation',$row);}}
	private function clone_code($old_id,$new_id,$revision){$row=$this->db->where('cost_item_revision_id',(int)$old_id)->get('cost_item_code_component')->row_array();if(!$row)return;unset($row['cost_item_code_component_id'],$row['created_at'],$row['updated_at']);$row['cost_item_revision_id']=$new_id;$row['revision_code']=$revision;$row['revision_segment']=$revision;$row['candidate_cost_code']=preg_replace('/-[^-]+$/','-'.$revision,(string)$row['candidate_cost_code']);$row['generated_cost_code']=NULL;$row['coding_status']='READY_FOR_CODE';$row['validation_message']='New revision is ready for governed code generation.';$row['generated_at']=NULL;$this->db->insert('cost_item_code_component',$row);}
	private function history_row($id,$stage,$action,$comments,$actor){$this->db->insert('cost_item_approval_history',array('cost_item_revision_id'=>(int)$id,'workflow_stage'=>$stage,'action'=>$action,'action_by'=>(int)$actor,'comments'=>$comments));}
	private function audit_row($item,$revision,$event,$field,$old,$new,$actor){$this->db->insert('cost_item_audit_log',array('cost_item_id'=>(int)$item,'cost_item_revision_id'=>(int)$revision,'event_type'=>$event,'field_name'=>$field,'old_value'=>$old,'new_value'=>$new,'changed_by'=>(int)$actor,'source_application'=>'Project Nexus UCD Web'));}
}
