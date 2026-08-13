<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Boq_mapping_model extends CI_Model
{
	public function boqs()
	{
		return $this->db->select("b.boq_id,b.boq_code,b.boq_name,b.boq_status,b.is_active,p.project_code,p.project_name,COUNT(DISTINCT i.boq_item_id) AS item_count,COUNT(DISTINCT m.boq_item_mapping_id) AS mapped_count,SUM(CASE WHEN m.mapping_status='CONFIRMED' THEN 1 ELSE 0 END) AS confirmed_count", FALSE)
			->from('boq_header b')->join('project_master p','p.project_id=b.project_id')
			->join('boq_item i','i.boq_id=b.boq_id AND i.is_active=1','left')
			->join('boq_item_mapping m','m.boq_item_id=i.boq_item_id','left')
			->group_by('b.boq_id')->order_by('b.is_active','DESC')->order_by('b.boq_code')->get()->result();
	}

	public function boq($boq_id)
	{
		$row=$this->db->select('b.*,p.project_code,p.project_name')->from('boq_header b')->join('project_master p','p.project_id=b.project_id')->where('b.boq_id',(int)$boq_id)->get()->row();
		return $row?:NULL;
	}

	public function items($boq_id)
	{
		return $this->db->select('i.*,u.uom_code,m.mapping_status,c.boq_mapping_candidate_id,r.cost_item_revision_id,r.enterprise_cost_code,r.standard_item_name,r.revision_no,r.revision_status,si.cost_item_id,si.cost_item_uid')
			->from('boq_item i')->join('ref_uom u','u.uom_id=i.uom_id','left')->join('boq_item_mapping m','m.boq_item_id=i.boq_item_id','left')
			->join('boq_mapping_candidate c','c.boq_mapping_candidate_id=m.boq_mapping_candidate_id','left')->join('standard_cost_item_revision r','r.cost_item_revision_id=c.cost_item_revision_id','left')
			->join('standard_cost_item si','si.cost_item_id=r.cost_item_id','left')->where('i.boq_id',(int)$boq_id)->order_by('i.line_no')->get()->result();
	}

	public function item($boq_id,$item_id)
	{
		$row=$this->db->select('i.*,u.uom_code,u.uom_name')->from('boq_item i')->join('ref_uom u','u.uom_id=i.uom_id','left')->where('i.boq_id',(int)$boq_id)->where('i.boq_item_id',(int)$item_id)->get()->row();
		return $row?:NULL;
	}

	public function candidates($item_id)
	{
		return $this->db->select('c.*,r.cost_item_revision_id,r.revision_no,r.enterprise_cost_code,r.standard_item_name,r.standard_description,r.revision_status,r.uom_id,u.uom_code,si.cost_item_id,si.cost_item_uid,si.lifecycle_status,m.boq_item_mapping_id,m.mapping_status')
			->from('boq_mapping_candidate c')->join('standard_cost_item_revision r','r.cost_item_revision_id=c.cost_item_revision_id')->join('standard_cost_item si','si.cost_item_id=r.cost_item_id')->join('ref_uom u','u.uom_id=r.uom_id','left')
			->join('boq_item_mapping m','m.boq_mapping_candidate_id=c.boq_mapping_candidate_id AND m.boq_item_id=c.boq_item_id','left')->where('c.boq_item_id',(int)$item_id)
			->order_by('m.boq_item_mapping_id IS NULL','ASC',FALSE)->order_by('c.is_active','DESC')->order_by('c.created_at','DESC')->get()->result();
	}

	public function mapping($item_id)
	{
		$row=$this->db->select('m.*,c.cost_item_revision_id,r.enterprise_cost_code,r.standard_item_name,r.revision_no,r.revision_status,si.cost_item_id,si.cost_item_uid,COALESCE(mu.display_name,mu.username) AS mapped_by_name,COALESCE(cu.display_name,cu.username) AS confirmed_by_name',FALSE)
			->from('boq_item_mapping m')->join('boq_mapping_candidate c','c.boq_mapping_candidate_id=m.boq_mapping_candidate_id')->join('standard_cost_item_revision r','r.cost_item_revision_id=c.cost_item_revision_id')->join('standard_cost_item si','si.cost_item_id=r.cost_item_id')
			->join('app_user mu','mu.user_id=m.mapped_by','left')->join('app_user cu','cu.user_id=m.confirmed_by','left')->where('m.boq_item_id',(int)$item_id)->get()->row();
		return $row?:NULL;
	}

	public function history($item_id)
	{
		return $this->db->select('h.*,r.enterprise_cost_code,r.standard_item_name,COALESCE(u.display_name,u.username) AS action_by_name',FALSE)->from('boq_item_mapping_history h')
			->join('boq_mapping_candidate c','c.boq_mapping_candidate_id=h.boq_mapping_candidate_id','left')->join('standard_cost_item_revision r','r.cost_item_revision_id=c.cost_item_revision_id','left')->join('app_user u','u.user_id=h.action_by','left')
			->where('h.boq_item_id',(int)$item_id)->order_by('h.boq_item_mapping_history_id','DESC')->get()->result();
	}

	public function search_cost_items($search,$boq_uom_id,$limit=50)
	{
		$this->db->select('r.cost_item_revision_id,r.revision_no,r.enterprise_cost_code,r.standard_item_name,r.standard_description,r.revision_status,r.uom_id,u.uom_code,si.cost_item_id,si.cost_item_uid,si.lifecycle_status,CASE WHEN r.uom_id IS NOT NULL AND r.uom_id='.(int)$boq_uom_id.' THEN 1 ELSE 0 END AS uom_match',FALSE)
			->from('standard_cost_item_revision r')->join('standard_cost_item si','si.cost_item_id=r.cost_item_id')->join('ref_uom u','u.uom_id=r.uom_id','left')->where('r.is_current',1)->where('si.lifecycle_status','ACTIVE');
		if($search!=='')$this->db->group_start()->like('r.enterprise_cost_code',$search)->or_like('r.standard_item_name',$search)->or_like('r.standard_description',$search)->or_like('si.cost_item_uid',$search)->group_end();
		return $this->db->order_by('uom_match','DESC')->order_by('r.standard_item_name')->limit((int)$limit)->get()->result();
	}

	public function add_candidate($item_id,$revision_id,$actor)
	{
		if(!$this->valid_item_revision($item_id,$revision_id))return FALSE;
		$existing=$this->db->where('boq_item_id',(int)$item_id)->where('cost_item_revision_id',(int)$revision_id)->get('boq_mapping_candidate')->row();
		$this->db->trans_start();
		if($existing){
			$this->db->where('boq_mapping_candidate_id',$existing->boq_mapping_candidate_id)->update('boq_mapping_candidate',array('is_active'=>1,'updated_by'=>(int)$actor));
			$candidate_id=(int)$existing->boq_mapping_candidate_id;$action=$existing->is_active?'CANDIDATE_ADDED':'CANDIDATE_RESTORED';
		}else{
			$this->db->insert('boq_mapping_candidate',array('boq_item_id'=>(int)$item_id,'cost_item_revision_id'=>(int)$revision_id,'candidate_source'=>'MANUAL','is_active'=>1,'created_by'=>(int)$actor,'updated_by'=>(int)$actor));
			$candidate_id=(int)$this->db->insert_id();$action='CANDIDATE_ADDED';
		}
		$this->history_row($item_id,$candidate_id,$action,NULL,NULL,NULL,$actor);$this->db->trans_complete();return$this->db->trans_status();
	}

	public function toggle_candidate($item_id,$candidate_id,$actor)
	{
		$c=$this->db->where('boq_item_id',(int)$item_id)->where('boq_mapping_candidate_id',(int)$candidate_id)->get('boq_mapping_candidate')->row();if(!$c)return FALSE;
		if($c->is_active&&$this->db->where('boq_item_id',(int)$item_id)->where('boq_mapping_candidate_id',(int)$candidate_id)->count_all_results('boq_item_mapping'))return FALSE;
		$active=$c->is_active?0:1;$this->db->trans_start();$this->db->where('boq_mapping_candidate_id',(int)$candidate_id)->update('boq_mapping_candidate',array('is_active'=>$active,'updated_by'=>(int)$actor));
		$this->history_row($item_id,$candidate_id,$active?'CANDIDATE_RESTORED':'CANDIDATE_DISABLED',NULL,NULL,NULL,$actor);$this->db->trans_complete();return$this->db->trans_status();
	}

	public function select_candidate($item_id,$candidate_id,$notes,$actor)
	{
		$c=$this->db->where('boq_item_id',(int)$item_id)->where('boq_mapping_candidate_id',(int)$candidate_id)->where('is_active',1)->get('boq_mapping_candidate')->row();if(!$c)return FALSE;
		$old=$this->db->where('boq_item_id',(int)$item_id)->get('boq_item_mapping')->row();$now=date('Y-m-d H:i:s');$this->db->trans_start();
		$data=array('boq_mapping_candidate_id'=>(int)$candidate_id,'mapping_status'=>'PROPOSED','mapping_method'=>'MANUAL','mapping_notes'=>$notes,'mapped_by'=>(int)$actor,'mapped_at'=>$now,'confirmed_by'=>NULL,'confirmed_at'=>NULL,'updated_by'=>(int)$actor);
		if($old)$this->db->where('boq_item_mapping_id',$old->boq_item_mapping_id)->update('boq_item_mapping',$data);else{$data['boq_item_id']=(int)$item_id;$this->db->insert('boq_item_mapping',$data);}
		$this->history_row($item_id,$candidate_id,$old?'REPLACED':'SELECTED',$old?$old->mapping_status:NULL,'PROPOSED',$notes,$actor);$this->db->trans_complete();return$this->db->trans_status();
	}

	public function mapping_action($item_id,$action,$comments,$actor)
	{
		$map=array('confirm'=>array('CONFIRMED','CONFIRMED'),'reject'=>array('REJECTED','REJECTED'),'reopen'=>array('PROPOSED','REOPENED'));if(!isset($map[$action]))return FALSE;
		$current=$this->db->where('boq_item_id',(int)$item_id)->get('boq_item_mapping')->row();if(!$current)return FALSE;
		$allowed=$action==='confirm'?array('PROPOSED'):($action==='reject'?array('PROPOSED','CONFIRMED'):array('REJECTED','CONFIRMED'));if(!in_array($current->mapping_status,$allowed,TRUE))return FALSE;
		$new=$map[$action][0];$data=array('mapping_status'=>$new,'mapping_notes'=>$comments!==NULL?$comments:$current->mapping_notes,'updated_by'=>(int)$actor);
		if($new==='CONFIRMED')$data+=array('confirmed_by'=>(int)$actor,'confirmed_at'=>date('Y-m-d H:i:s'));else$data+=array('confirmed_by'=>NULL,'confirmed_at'=>NULL);
		$this->db->trans_start();$this->db->where('boq_item_mapping_id',$current->boq_item_mapping_id)->where('mapping_status',$current->mapping_status)->update('boq_item_mapping',$data);
		if($this->db->affected_rows()!==1){$this->db->trans_rollback();return FALSE;}$this->history_row($item_id,$current->boq_mapping_candidate_id,$map[$action][1],$current->mapping_status,$new,$comments,$actor);$this->db->trans_complete();return$this->db->trans_status();
	}

	private function valid_item_revision($item_id,$revision_id)
	{
		$item=$this->db->where('boq_item_id',(int)$item_id)->where('is_active',1)->count_all_results('boq_item');
		$revision=$this->db->select('r.cost_item_revision_id')->from('standard_cost_item_revision r')->join('standard_cost_item i','i.cost_item_id=r.cost_item_id')->where('r.cost_item_revision_id',(int)$revision_id)->where('r.is_current',1)->where('i.lifecycle_status','ACTIVE')->count_all_results();
		return$item>0&&$revision>0;
	}

	private function history_row($item,$candidate,$action,$old,$new,$comments,$actor)
	{
		$this->db->insert('boq_item_mapping_history',array('boq_item_id'=>(int)$item,'boq_mapping_candidate_id'=>$candidate?(int)$candidate:NULL,'mapping_action'=>$action,'old_status'=>$old,'new_status'=>$new,'comments'=>$comments,'action_by'=>(int)$actor));
	}
}
