<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Rate_model extends CI_Model
{
	public function current_material_rates()
	{
		return $this->db->select('rh.*,v.material_variant_code,v.size_description,m.material_code,m.material_name,s.schedule_code,s.schedule_name,s.currency_code,s.effective_from,s.effective_to,s.is_active AS schedule_is_active')->from('material_rate_history rh')->join('material_variant v','v.material_variant_id=rh.material_variant_id')->join('material_master m','m.material_id=v.material_id')->join('material_rate_schedule s','s.material_rate_schedule_id=rh.material_rate_schedule_id')->where('rh.is_current',1)->order_by('m.material_code')->get()->result();
	}

	public function material_history()
	{
		return $this->db->select('rh.*,v.material_variant_code,v.size_description,m.material_code,m.material_name,s.schedule_code,s.schedule_name,s.currency_code,s.effective_from,s.effective_to,s.is_active AS schedule_is_active')->from('material_rate_history rh')->join('material_variant v','v.material_variant_id=rh.material_variant_id')->join('material_master m','m.material_id=v.material_id')->join('material_rate_schedule s','s.material_rate_schedule_id=rh.material_rate_schedule_id')->order_by('s.effective_from','DESC')->order_by('rh.created_at','DESC')->get()->result();
	}

	public function current_labor_rates()
	{
		return $this->db->select('rh.*,l.labor_code,l.labor_name,s.schedule_code,s.schedule_name,s.currency_code,s.admin_fee_percentage,s.effective_from,s.effective_to,s.source_status,s.is_active AS schedule_is_active')->from('labor_rate_history rh')->join('labor_master l','l.labor_id=rh.labor_id')->join('labor_rate_schedule s','s.labor_rate_schedule_id=rh.labor_rate_schedule_id')->where('rh.is_current',1)->order_by('l.labor_code')->get()->result();
	}

	public function labor_history()
	{
		return $this->db->select('rh.*,l.labor_code,l.labor_name,s.schedule_code,s.schedule_name,s.currency_code,s.admin_fee_percentage,s.effective_from,s.effective_to,s.source_status,s.is_active AS schedule_is_active')->from('labor_rate_history rh')->join('labor_master l','l.labor_id=rh.labor_id')->join('labor_rate_schedule s','s.labor_rate_schedule_id=rh.labor_rate_schedule_id')->order_by('s.effective_from','DESC')->order_by('rh.created_at','DESC')->get()->result();
	}

	public function cost_item_history()
	{
		return $this->db->select('h.*,r.cost_item_id,r.enterprise_cost_code,r.standard_item_name,r.revision_no,p.project_code,p.project_name')->from('cost_item_rate_history h')->join('standard_cost_item_revision r','r.cost_item_revision_id=h.cost_item_revision_id')->join('project_master p','p.project_id=h.project_id','left')->order_by('h.rate_date','DESC')->order_by('h.created_at','DESC')->get()->result();
	}

	public function options($type)
	{
		if($type==='material')return $this->db->select("v.material_variant_id option_id,CONCAT(m.material_code,' — ',m.material_name,' / ',v.material_variant_code,IF(v.size_description IS NULL,'',CONCAT(' ',v.size_description))) option_label",FALSE)->from('material_variant v')->join('material_master m','m.material_id=v.material_id')->order_by('m.material_code')->get()->result();
		if($type==='labor')return $this->db->select("labor_id option_id,CONCAT(labor_code,' — ',labor_name) option_label",FALSE)->order_by('labor_code')->get('labor_master')->result();
		if($type==='cost_item')return $this->db->select("cost_item_revision_id option_id,CONCAT(COALESCE(enterprise_cost_code,'No Code'),' — ',standard_item_name,' / Rev ',revision_no) option_label",FALSE)->where('is_current',1)->order_by('enterprise_cost_code')->get('standard_cost_item_revision')->result();
		return array();
	}

	public function projects(){return $this->db->select("project_id option_id,CONCAT(project_code,' — ',project_name) option_label",FALSE)->order_by('project_code')->get('project_master')->result();}
	public function reference_options($table,$key,$code,$name){return$this->db->select($key." option_id,CONCAT(".$code.",' — ',".$name.") option_label",FALSE)->where('is_active',1)->order_by($name)->get($table)->result();}
	public function pending_validation_status_id(){$r=$this->db->where('validation_status_code','PENDING')->get('ref_validation_status')->row();return$r?(int)$r->validation_status_id:NULL;}
	public function exists($table,$key,$id){return $this->db->where($key,(int)$id)->count_all_results($table)>0;}
	public function schedule_code_exists($type,$code){return $this->db->where('schedule_code',$code)->count_all_results($type.'_rate_schedule')>0;}

	public function append_material(array $schedule,array $rate)
	{
		$this->db->trans_start();$this->db->insert('material_rate_schedule',$schedule);$rate['material_rate_schedule_id']=(int)$this->db->insert_id();$this->db->where('material_variant_id',$rate['material_variant_id'])->where('is_current',1)->update('material_rate_history',array('is_current'=>0));$this->db->insert('material_rate_history',$rate);$this->db->trans_complete();return $this->db->trans_status();
	}

	public function append_labor(array $schedule,array $rate)
	{
		$this->db->trans_start();$this->db->insert('labor_rate_schedule',$schedule);$rate['labor_rate_schedule_id']=(int)$this->db->insert_id();$this->db->where('labor_id',$rate['labor_id'])->where('is_current',1)->update('labor_rate_history',array('is_current'=>0));$this->db->insert('labor_rate_history',$rate);$this->db->trans_complete();return $this->db->trans_status();
	}

	public function append_cost_item(array $rate){return $this->db->insert('cost_item_rate_history',$rate)?(int)$this->db->insert_id():0;}
}
