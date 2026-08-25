<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Elemental_cost_model extends CI_Model
{
	public function plans()
	{
		return $this->db->select('p.*,pt.project_type_code,pt.project_type_name,ms.market_segment_code,ms.market_segment_name,pr.project_code,pr.project_name,t.total_elemental_cost,t.total_cost_per_m2_gfa,t.total_cost_per_m2_site_area,t.total_cost_per_m2_saleable_area,t.total_cost_per_residential_unit')
			->from('elemental_cost_plan p')->join('ref_project_type_market_segment s','s.project_type_market_segment_id=p.project_type_market_segment_id')->join('ref_project_type pt','pt.project_type_id=s.project_type_id')->join('ref_market_segment ms','ms.market_segment_id=s.market_segment_id')->join('project_master pr','pr.project_id=p.project_id','left')->join('vw_elemental_cost_plan_total t','t.elemental_cost_plan_id=p.elemental_cost_plan_id','left')->order_by('p.updated_at','DESC')->get()->result();
	}

	public function find_plan($id)
	{
		$row=$this->db->select('p.*,pt.project_type_code,pt.project_type_name,ms.market_segment_code,ms.market_segment_name,pr.project_code,pr.project_name,t.total_elemental_cost,t.total_cost_per_m2_gfa,t.total_cost_per_m2_site_area,t.total_cost_per_m2_saleable_area,t.total_cost_per_residential_unit')
			->from('elemental_cost_plan p')->join('ref_project_type_market_segment s','s.project_type_market_segment_id=p.project_type_market_segment_id')->join('ref_project_type pt','pt.project_type_id=s.project_type_id')->join('ref_market_segment ms','ms.market_segment_id=s.market_segment_id')->join('project_master pr','pr.project_id=p.project_id','left')->join('vw_elemental_cost_plan_total t','t.elemental_cost_plan_id=p.elemental_cost_plan_id','left')->where('p.elemental_cost_plan_id',(int)$id)->get()->row();
		return$row?:NULL;
	}

	public function elements($plan_id){return$this->db->where('elemental_cost_plan_id',(int)$plan_id)->order_by('line_no')->get('vw_elemental_cost_plan_detail')->result();}
	public function summaries($plan_id){return$this->db->where('elemental_cost_plan_id',(int)$plan_id)->order_by('level1_code')->order_by('level2_code')->order_by('level3_code')->get('vw_elemental_cost_summary')->result();}
	public function find_element($plan_id,$element_id){$row=$this->db->where('elemental_cost_plan_id',(int)$plan_id)->where('elemental_cost_plan_element_id',(int)$element_id)->get('elemental_cost_plan_element')->row();return$row?:NULL;}

	public function scope_options()
	{
		return$this->db->select("s.project_type_market_segment_id AS option_id,CONCAT(pt.project_type_code,' - ',COALESCE(pt.project_type_short,pt.project_type_name),' / ',ms.market_segment_code,' - ',ms.market_segment_name) AS option_label",FALSE)->from('ref_project_type_market_segment s')->join('ref_project_type pt','pt.project_type_id=s.project_type_id')->join('ref_market_segment ms','ms.market_segment_id=s.market_segment_id')->where('s.is_active',1)->where('pt.is_active',1)->where('ms.is_active',1)->order_by('pt.project_type_code')->order_by('s.display_order')->get()->result();
	}
	public function projects(){return$this->db->select("project_id AS option_id,CONCAT(project_code,' - ',project_name) AS option_label",FALSE)->where('is_active',1)->order_by('project_code')->get('project_master')->result();}
	public function level3_options(){return$this->db->select("uniformat_level3_id AS option_id,CONCAT(level3_code,' - ',level3_name) AS option_label",FALSE)->where('is_active',1)->order_by('level3_code')->get('ref_uniformat_level3')->result();}
	public function level4_options(){return$this->db->select("uniformat_level4_id AS option_id,uniformat_level3_id,CONCAT(level4_code,' - ',level4_name) AS option_label",FALSE)->where('is_active',1)->order_by('level4_code')->get('ref_uniformat_level4')->result();}
	public function basis_options(){return$this->db->select("elemental_cost_basis_id AS option_id,basis_code,CONCAT(basis_code,' - ',basis_name,COALESCE(CONCAT(' (',basis_uom_label,')'),'')) AS option_label",FALSE)->where('is_active',1)->order_by('display_order')->get('ref_elemental_cost_basis')->result();}
	public function uom_options(){return$this->db->select("uom_id AS option_id,CONCAT(uom_code,' - ',uom_name) AS option_label",FALSE)->where('is_active',1)->order_by('uom_code')->get('ref_uom')->result();}
	public function location_options(){return$this->db->select("location_id AS option_id,CONCAT(psgc_code,' - ',location_name) AS option_label",FALSE)->where('is_active',1)->order_by('location_name')->limit(50000)->get('ref_location')->result();}

	public function reference_exists($table,$key,$id){return$this->db->where($key,(int)$id)->count_all_results($table)>0;}
	public function active_reference_exists($table,$key,$id){return$this->db->where($key,(int)$id)->where('is_active',1)->count_all_results($table)>0;}
	public function code_revision_exists($code,$revision,$exclude=NULL){$this->db->where('cost_plan_code',$code)->where('revision_no',$revision);if($exclude!==NULL)$this->db->where('elemental_cost_plan_id !=',(int)$exclude);return$this->db->count_all_results('elemental_cost_plan')>0;}
	public function level4_belongs($l4,$l3){return$this->db->where('uniformat_level4_id',(int)$l4)->where('uniformat_level3_id',(int)$l3)->where('is_active',1)->count_all_results('ref_uniformat_level4')>0;}
	public function project_matches_scope($project_id,$scope_id)
	{
		$row=$this->db->select('project_type_id,market_segment_id')->where('project_id',(int)$project_id)->get('project_master')->row();if(!$row)return FALSE;if(!$row->project_type_id||!$row->market_segment_id)return TRUE;
		return$this->db->where('project_type_market_segment_id',(int)$scope_id)->where('project_type_id',(int)$row->project_type_id)->where('market_segment_id',(int)$row->market_segment_id)->where('is_active',1)->count_all_results('ref_project_type_market_segment')>0;
	}

	public function create_plan(array$data){return$this->db->insert('elemental_cost_plan',$data)?(int)$this->db->insert_id():0;}
	public function update_plan($id,array$data){return$this->db->where('elemental_cost_plan_id',(int)$id)->where('plan_status','DRAFT')->update('elemental_cost_plan',$data);}
	public function create_element(array$data){return$this->db->insert('elemental_cost_plan_element',$data)?(int)$this->db->insert_id():0;}
	public function update_element($plan_id,$element_id,array$data){return$this->db->where('elemental_cost_plan_id',(int)$plan_id)->where('elemental_cost_plan_element_id',(int)$element_id)->update('elemental_cost_plan_element',$data);}
	public function line_exists($plan_id,$line_no,$exclude=NULL){$this->db->where('elemental_cost_plan_id',(int)$plan_id)->where('line_no',(int)$line_no);if($exclude!==NULL)$this->db->where('elemental_cost_plan_element_id !=',(int)$exclude);return$this->db->count_all_results('elemental_cost_plan_element')>0;}

	public function transition($id,$from,$to,$user_id)
	{
		$data=array('plan_status'=>$to);if($to==='APPROVED')$data+=array('approved_at'=>date('Y-m-d H:i:s'),'approved_by'=>(int)$user_id);if($to==='DRAFT')$data+=array('approved_at'=>NULL,'approved_by'=>NULL);
		return$this->db->where('elemental_cost_plan_id',(int)$id)->where('plan_status',$from)->update('elemental_cost_plan',$data)&&$this->db->affected_rows()===1;
	}

	public function rates(){return$this->db->order_by('rate_date','DESC')->order_by('elemental_rate_history_id','DESC')->get('vw_elemental_rate_history')->result();}
	public function create_rate(array$data)
	{
		if(!$this->active_reference_exists('ref_project_type_market_segment','project_type_market_segment_id',$data['project_type_market_segment_id'])||!$this->active_reference_exists('ref_uniformat_level3','uniformat_level3_id',$data['uniformat_level3_id'])||!$this->active_reference_exists('ref_elemental_cost_basis','elemental_cost_basis_id',$data['elemental_cost_basis_id']))return 0;
		if($data['uniformat_level4_id']!==NULL&&!$this->level4_belongs($data['uniformat_level4_id'],$data['uniformat_level3_id']))return 0;
		if(!$this->scope_element_allowed($data['project_type_market_segment_id'],$data['uniformat_level3_id'],$data['uniformat_level4_id']))return 0;
		if($data['project_id']!==NULL&&(!$this->active_reference_exists('project_master','project_id',$data['project_id'])||!$this->project_matches_scope($data['project_id'],$data['project_type_market_segment_id'])))return 0;
		if($data['location_id']!==NULL&&!$this->active_reference_exists('ref_location','location_id',$data['location_id']))return 0;
		$this->db->trans_begin();$this->db->where('project_type_market_segment_id',$data['project_type_market_segment_id'])->where('uniformat_level3_id',$data['uniformat_level3_id'])->where('elemental_cost_basis_id',$data['elemental_cost_basis_id']);
		if($data['uniformat_level4_id']===NULL)$this->db->where('uniformat_level4_id IS NULL',NULL,FALSE);else$this->db->where('uniformat_level4_id',$data['uniformat_level4_id']);$this->db->update('elemental_rate_history',array('is_current'=>0));$this->db->insert('elemental_rate_history',$data);$id=(int)$this->db->insert_id();if($this->db->trans_status()===FALSE){$this->db->trans_rollback();return 0;}$this->db->trans_commit();return$id;
	}
	public function confirmed_scope_count($scope_id){return$this->db->where('project_type_market_segment_id',(int)$scope_id)->where('applicability_status','ACTIVE')->count_all_results('ref_elemental_scope_element');}
	public function scope_element_allowed($scope_id,$level3_id,$level4_id)
	{
		if($this->confirmed_scope_count($scope_id)===0)return TRUE;$this->db->where('project_type_market_segment_id',(int)$scope_id)->where('uniformat_level3_id',(int)$level3_id)->where('applicability_status','ACTIVE');
		if($level4_id===NULL)$this->db->where('uniformat_level4_id IS NULL',NULL,FALSE);else$this->db->group_start()->where('uniformat_level4_id',(int)$level4_id)->or_where('uniformat_level4_id IS NULL',NULL,FALSE)->group_end();return$this->db->count_all_results('ref_elemental_scope_element')>0;
	}
	public function scope_elements()
	{
		return$this->db->select('e.*,pt.project_type_code,pt.project_type_name,ms.market_segment_code,ms.market_segment_name,l3.level3_code,l3.level3_name,l4.level4_code,l4.level4_name')->from('ref_elemental_scope_element e')->join('ref_project_type_market_segment s','s.project_type_market_segment_id=e.project_type_market_segment_id')->join('ref_project_type pt','pt.project_type_id=s.project_type_id')->join('ref_market_segment ms','ms.market_segment_id=s.market_segment_id')->join('ref_uniformat_level3 l3','l3.uniformat_level3_id=e.uniformat_level3_id')->join('ref_uniformat_level4 l4','l4.uniformat_level4_id=e.uniformat_level4_id','left')->order_by('pt.project_type_code')->order_by('ms.display_order')->order_by('e.display_order')->get()->result();
	}
	public function find_scope_element($id){$row=$this->db->where('elemental_scope_element_id',(int)$id)->get('ref_elemental_scope_element')->row();return$row?:NULL;}
	public function scope_element_exists($scope,$l3,$l4,$exclude=NULL){$this->db->where('project_type_market_segment_id',(int)$scope)->where('uniformat_level3_id',(int)$l3);if($l4===NULL)$this->db->where('uniformat_level4_id IS NULL',NULL,FALSE);else$this->db->where('uniformat_level4_id',(int)$l4);if($exclude!==NULL)$this->db->where('elemental_scope_element_id !=',(int)$exclude);return$this->db->count_all_results('ref_elemental_scope_element')>0;}
	public function create_scope_element(array$data){return$this->db->insert('ref_elemental_scope_element',$data)?(int)$this->db->insert_id():0;}
	public function update_scope_element($id,array$data){return$this->db->where('elemental_scope_element_id',(int)$id)->update('ref_elemental_scope_element',$data);}
}
