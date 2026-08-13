<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Crew_productivity_model extends CI_Model
{
	public function find_item($cost_item_id)
	{
		$row = $this->db->select('i.cost_item_id,i.cost_item_uid,i.lifecycle_status,r.cost_item_revision_id,r.enterprise_cost_code,r.standard_item_name,r.revision_status,r.uom_id,u.uom_code,u.uom_name')
			->from('standard_cost_item i')->join('standard_cost_item_revision r','r.cost_item_id=i.cost_item_id AND r.is_current=1')->join('ref_uom u','u.uom_id=r.uom_id','left')->where('i.cost_item_id',(int)$cost_item_id)->get()->row();
		return $row ?: NULL;
	}

	public function productivities($revision_id)
	{
		return $this->db->select('p.*,c.crew_code,c.crew_name,c.is_active AS crew_is_active,ou.uom_code AS output_uom_code,du.uom_code AS duration_uom_code,b.costing_productivity_id')
			->from('cost_item_productivity p')->join('crew_master c','c.crew_id=p.crew_id','left')->join('ref_uom ou','ou.uom_id=p.output_uom_id','left')->join('ref_uom du','du.uom_id=p.duration_uom_id','left')
			->join('cost_item_labor_build_up b','b.cost_item_revision_id=p.cost_item_revision_id','left')->where('p.cost_item_revision_id',(int)$revision_id)->order_by('p.productivity_id')->get()->result();
	}

	public function find_productivity($revision_id,$productivity_id)
	{
		$row=$this->db->where('cost_item_revision_id',(int)$revision_id)->where('productivity_id',(int)$productivity_id)->get('cost_item_productivity')->row();return$row?:NULL;
	}

	public function active_crews()
	{
		return $this->db->select("c.crew_id option_id,CONCAT(c.crew_code,' — ',COALESCE(c.crew_name,c.source_trade),' (',COUNT(m.crew_member_id),' members)') option_label",FALSE)->from('crew_master c')->join('crew_member m','m.crew_id=c.crew_id','left')->where('c.is_active',1)->group_by('c.crew_id')->having('COUNT(m.crew_member_id) >',0)->order_by('c.crew_code')->get()->result();
	}

	public function day_uom()
	{
		$row=$this->db->where('LOWER(uom_code)','d')->where('is_active',1)->limit(1)->get('ref_uom')->row();return$row?:NULL;
	}

	public function crew_exists($crew_id){return$this->db->where('crew_id',(int)$crew_id)->where('is_active',1)->count_all_results('crew_master')>0;}

	public function save_productivity($revision_id,array$data,$productivity_id=NULL)
	{
		$this->db->trans_start();
		if($productivity_id===NULL){$data['cost_item_revision_id']=(int)$revision_id;$this->db->insert('cost_item_productivity',$data);$id=(int)$this->db->insert_id();}
		else{$this->db->where('productivity_id',(int)$productivity_id)->where('cost_item_revision_id',(int)$revision_id)->update('cost_item_productivity',$data);$id=(int)$productivity_id;$this->db->where('cost_item_revision_id',(int)$revision_id)->where('costing_productivity_id',$id)->update('cost_item_labor_build_up',array('is_stale'=>1));}
		$this->db->trans_complete();return$this->db->trans_status()?$id:FALSE;
	}

	public function build_up($revision_id)
	{
		$row=$this->db->select('b.*,p.output_quantity,p.duration_quantity,p.productivity_source,p.source_reference,c.crew_code,c.crew_name,COALESCE(u.display_name,u.username) applied_by_name')
			->from('cost_item_labor_build_up b')->join('cost_item_productivity p','p.productivity_id=b.costing_productivity_id','left')->join('crew_master c','c.crew_id=b.crew_id','left')->join('app_user u','u.user_id=b.applied_by','left')->where('b.cost_item_revision_id',(int)$revision_id)->get()->row();
		return $row ?: (object)array('cost_item_revision_id'=>(int)$revision_id,'labor_source_method'=>'LEGACY_MANUAL','costing_productivity_id'=>NULL,'crew_id'=>NULL,'is_stale'=>0,'applied_at'=>NULL,'applied_by_name'=>NULL);
	}

	public function preview($revision_id,$productivity_id)
	{
		$p=$this->db->select('p.*,c.crew_code,c.crew_name,c.is_active AS crew_is_active,ou.uom_code output_uom_code,du.uom_code duration_uom_code')->from('cost_item_productivity p')->join('crew_master c','c.crew_id=p.crew_id')->join('ref_uom ou','ou.uom_id=p.output_uom_id','left')->join('ref_uom du','du.uom_id=p.duration_uom_id','left')->where('p.cost_item_revision_id',(int)$revision_id)->where('p.productivity_id',(int)$productivity_id)->get()->row();
		if(!$p)return array('error'=>'The selected productivity record is invalid.');
		if(!$p->crew_is_active)return array('error'=>'The selected crew is inactive.');
		if(strtolower((string)$p->duration_uom_code)!=='d'||(float)$p->duration_quantity<=0)return array('error'=>'Phase 22 supports governed day-based productivity only.');
		if((float)$p->output_quantity<=0)return array('error'=>'Output quantity must be greater than zero.');
		$members=$this->db->select('m.crew_member_id,m.labor_id,m.member_count,m.source_role_name,l.labor_code,l.labor_name,l.is_active AS labor_is_active,rh.total_with_admin_fee,rs.currency_code')
			->from('crew_member m')->join('labor_master l','l.labor_id=m.labor_id')->join('labor_rate_history rh','rh.labor_id=m.labor_id AND rh.is_current=1','left')->join('labor_rate_schedule rs','rs.labor_rate_schedule_id=rh.labor_rate_schedule_id','left')->where('m.crew_id',(int)$p->crew_id)->order_by('l.labor_code')->get()->result();
		if(!$members)return array('error'=>'The selected crew has no labor members.');
		$currency=NULL;$total=0.0;
		foreach($members as$m){if(!$m->labor_is_active)return array('error'=>'Crew member '.$m->labor_code.' is inactive.');if($m->total_with_admin_fee===NULL)return array('error'=>'Crew member '.$m->labor_code.' has no current governed labor rate.');if($currency===NULL)$currency=$m->currency_code;elseif($currency!==$m->currency_code)return array('error'=>'Crew member rates use mixed currencies.');$m->labor_days_per_item_unit=(float)$m->member_count*(float)$p->duration_quantity/(float)$p->output_quantity;$m->component_amount=$m->labor_days_per_item_unit*(float)$m->total_with_admin_fee;$total+=$m->component_amount;}
		return array('error'=>NULL,'productivity'=>$p,'members'=>$members,'currency'=>$currency,'total'=>$total);
	}

	public function apply_crew($revision_id,$productivity_id,$actor)
	{
		$preview=$this->preview($revision_id,$productivity_id);if($preview['error']!==NULL)return FALSE;$p=$preview['productivity'];
		$this->db->trans_start();
		$this->db->where('cost_item_revision_id',(int)$revision_id)->delete('cost_item_labor');
		foreach($preview['members']as$m){$labor=array('cost_item_revision_id'=>(int)$revision_id,'labor_id'=>(int)$m->labor_id,'crew_quantity'=>(float)$m->member_count,'labor_days_per_item_unit'=>$m->labor_days_per_item_unit,'labor_hours_per_unit'=>0,'notes'=>'Crew-derived from '.$p->crew_code.' productivity record #'.$p->productivity_id);$this->db->insert('cost_item_labor',$labor);$labor_id=(int)$this->db->insert_id();$this->db->insert('cost_item_labor_derivation',array('cost_item_revision_id'=>(int)$revision_id,'cost_item_labor_id'=>$labor_id,'productivity_id'=>(int)$p->productivity_id,'crew_member_id'=>(int)$m->crew_member_id,'labor_id'=>(int)$m->labor_id,'member_count_snapshot'=>(float)$m->member_count,'duration_days_snapshot'=>(float)$p->duration_quantity,'output_quantity_snapshot'=>(float)$p->output_quantity,'labor_days_per_item_unit_snapshot'=>$m->labor_days_per_item_unit,'labor_rate_snapshot'=>(float)$m->total_with_admin_fee,'currency_code_snapshot'=>$m->currency_code,'created_by'=>(int)$actor));}
		$data=array('labor_source_method'=>'CREW_DERIVED','costing_productivity_id'=>(int)$p->productivity_id,'crew_id'=>(int)$p->crew_id,'duration_days_snapshot'=>(float)$p->duration_quantity,'output_quantity_snapshot'=>(float)$p->output_quantity,'output_uom_id'=>(int)$p->output_uom_id,'is_stale'=>0,'applied_by'=>(int)$actor,'applied_at'=>date('Y-m-d H:i:s'));
		if($this->db->where('cost_item_revision_id',(int)$revision_id)->count_all_results('cost_item_labor_build_up'))$this->db->where('cost_item_revision_id',(int)$revision_id)->update('cost_item_labor_build_up',$data);else{$data['cost_item_revision_id']=(int)$revision_id;$this->db->insert('cost_item_labor_build_up',$data);}
		$this->db->trans_complete();return$this->db->trans_status();
	}

	public function convert_to_manual($revision_id,$actor)
	{
		$this->db->trans_start();$this->db->where('cost_item_revision_id',(int)$revision_id)->delete('cost_item_labor_derivation');$data=array('labor_source_method'=>'MANUAL','costing_productivity_id'=>NULL,'crew_id'=>NULL,'duration_days_snapshot'=>NULL,'output_quantity_snapshot'=>NULL,'output_uom_id'=>NULL,'is_stale'=>0,'applied_by'=>(int)$actor,'applied_at'=>date('Y-m-d H:i:s'));
		if($this->db->where('cost_item_revision_id',(int)$revision_id)->count_all_results('cost_item_labor_build_up'))$this->db->where('cost_item_revision_id',(int)$revision_id)->update('cost_item_labor_build_up',$data);else{$data['cost_item_revision_id']=(int)$revision_id;$this->db->insert('cost_item_labor_build_up',$data);}$this->db->trans_complete();return$this->db->trans_status();
	}

	public function mark_manual($revision_id,$actor)
	{
		$b=$this->build_up($revision_id);if($b->labor_source_method==='CREW_DERIVED')return FALSE;$data=array('labor_source_method'=>'MANUAL','is_stale'=>0,'applied_by'=>(int)$actor,'applied_at'=>date('Y-m-d H:i:s'));
		if($b->labor_source_method==='LEGACY_MANUAL'){$data['cost_item_revision_id']=(int)$revision_id;return$this->db->insert('cost_item_labor_build_up',$data);}return$this->db->where('cost_item_revision_id',(int)$revision_id)->update('cost_item_labor_build_up',$data);
	}
}
