<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Project_model extends CI_Model
{
	public function all_projects()
	{
		return $this->db->select('p.*,pt.project_type_name,COALESCE(pt.project_type_name,p.project_type) AS project_type,l.location_name,COALESCE(l.location_name,p.city) AS city,COUNT(rh.rate_history_id) AS observation_count',FALSE)->from('project_master p')->join('ref_project_type pt','pt.project_type_id=p.project_type_id','left')->join('ref_location l','l.location_id=p.location_id','left')->join('cost_item_rate_history rh','rh.project_id=p.project_id','left')->group_by('p.project_id')->order_by('p.is_active','DESC')->order_by('p.project_code')->get()->result();
	}

	public function find($project_id)
	{
		$row=$this->db->select('p.*,pt.project_type_code,pt.project_type_name,COALESCE(pt.project_type_name,p.project_type) AS project_type,l.psgc_code,l.location_name,COALESCE(l.location_name,p.city) AS city,ll.level_name',FALSE)->from('project_master p')->join('ref_project_type pt','pt.project_type_id=p.project_type_id','left')->join('ref_location l','l.location_id=p.location_id','left')->join('ref_location_level ll','ll.location_level_id=l.location_level_id','left')->where('p.project_id',(int)$project_id)->get()->row();
		return $row?:NULL;
	}

	public function observations($project_id)
	{
		return $this->db->select("rh.*,r.enterprise_cost_code,r.standard_item_name,r.revision_no,l.location_name,COALESCE(l.location_name,CONCAT_WS(', ',rh.city,rh.province,rh.region)) AS location_reference",FALSE)->from('cost_item_rate_history rh')->join('standard_cost_item_revision r','r.cost_item_revision_id=rh.cost_item_revision_id','left')->join('ref_location l','l.location_id=rh.location_id','left')->where('rh.project_id',(int)$project_id)->order_by('rh.rate_date','DESC')->order_by('rh.created_at','DESC')->get()->result();
	}

	public function project_types(){return$this->db->select("project_type_id AS option_id,CONCAT(project_type_code,' — ',project_type_name) AS option_label",FALSE)->where('is_active',1)->order_by('project_type_name')->get('ref_project_type')->result();}
	public function locations(){return$this->db->select("location_id AS option_id,CONCAT(psgc_code,' — ',location_name) AS option_label",FALSE)->where('is_active',1)->order_by('location_name')->get('ref_location')->result();}
	public function reference_exists($table,$key,$id){return$this->db->where($key,(int)$id)->count_all_results($table)>0;}

	public function code_exists($code,$exclude_id=NULL)
	{
		$this->db->where('project_code',$code);if($exclude_id!==NULL)$this->db->where('project_id !=',(int)$exclude_id);
		return $this->db->count_all_results('project_master')>0;
	}

	public function create(array $data){return $this->db->insert('project_master',$data)?(int)$this->db->insert_id():0;}
	public function update($project_id,array $data){return $this->db->where('project_id',(int)$project_id)->update('project_master',$data);}
	public function set_active($project_id,$active){return $this->db->where('project_id',(int)$project_id)->update('project_master',array('is_active'=>$active?1:0));}
	public function dependencies($project_id)
	{
		$id=(int)$project_id;return(object)array('boqs'=>$this->db->where('project_id',$id)->count_all_results('boq_header'),'rates'=>$this->db->where('project_id',$id)->count_all_results('cost_item_rate_history'),'documents'=>$this->db->where('project_id',$id)->count_all_results('ucd_source_document'),'metrics'=>$this->db->where('project_id',$id)->count_all_results('ucd_project_metric'));
	}
	public function delete_project($project_id)
	{
		$id=(int)$project_id;$this->db->trans_begin();$project=$this->db->query('SELECT project_id FROM project_master WHERE project_id=? FOR UPDATE',array($id))->row();if(!$project){$this->db->trans_rollback();return FALSE;}$dependencies=$this->dependencies($id);if(array_sum((array)$dependencies)>0){$this->db->trans_rollback();return'BLOCKED';}$this->db->where('project_id',$id)->delete('project_master');if($this->db->trans_status()===FALSE||$this->db->affected_rows()!==1){$this->db->trans_rollback();return FALSE;}$this->db->trans_commit();return TRUE;
	}
}
