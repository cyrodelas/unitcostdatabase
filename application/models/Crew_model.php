<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Crew_model extends CI_Model
{
	public function all_crews()
	{
		return $this->db
			->select('c.*, COUNT(DISTINCT m.crew_member_id) AS craft_count, COALESCE(SUM(m.member_count), 0) AS total_members, COALESCE(SUM(m.member_count * rh.total_with_admin_fee), 0) AS calculated_daily_cost, MAX(rs.currency_code) AS currency_code', FALSE)
			->from('crew_master c')->join('crew_member m', 'm.crew_id = c.crew_id', 'left')
			->join('labor_rate_history rh', 'rh.labor_id = m.labor_id AND rh.is_current = 1', 'left')
			->join('labor_rate_schedule rs', 'rs.labor_rate_schedule_id = rh.labor_rate_schedule_id', 'left')
			->group_by('c.crew_id')->order_by('c.is_active', 'DESC')->order_by('c.crew_code')->get()->result();
	}

	public function find($crew_id)
	{
		$row = $this->db->where('crew_id', (int) $crew_id)->get('crew_master')->row();
		return $row ?: NULL;
	}

	public function members($crew_id)
	{
		return $this->db
			->select('m.*, l.labor_code, l.labor_name, l.is_active AS labor_is_active, cat.labor_category_name, rh.total_with_admin_fee, rs.currency_code, (m.member_count * rh.total_with_admin_fee) AS calculated_daily_cost', FALSE)
			->from('crew_member m')->join('labor_master l', 'l.labor_id = m.labor_id')
			->join('ref_labor_category cat', 'cat.labor_category_id = l.labor_category_id', 'left')
			->join('labor_rate_history rh', 'rh.labor_id = l.labor_id AND rh.is_current = 1', 'left')
			->join('labor_rate_schedule rs', 'rs.labor_rate_schedule_id = rh.labor_rate_schedule_id', 'left')
			->where('m.crew_id', (int) $crew_id)->order_by('l.labor_code')->get()->result();
	}

	public function productivity_usages($crew_id)
	{
		return $this->db
			->select('p.*, r.enterprise_cost_code, r.standard_item_name, r.revision_no, r.revision_status, ou.uom_code AS output_uom_code, du.uom_code AS duration_uom_code')
			->from('cost_item_productivity p')->join('standard_cost_item_revision r', 'r.cost_item_revision_id = p.cost_item_revision_id')
			->join('ref_uom ou', 'ou.uom_id = p.output_uom_id', 'left')->join('ref_uom du', 'du.uom_id = p.duration_uom_id', 'left')
			->where('p.crew_id', (int) $crew_id)->order_by('r.is_current', 'DESC')->order_by('r.standard_item_name')->get()->result();
	}

	public function labor_options()
	{
		return $this->db
			->select("l.labor_id AS option_id, CONCAT(l.labor_code, ' — ', l.labor_name, IF(l.is_active = 1, '', ' (Inactive)')) AS option_label", FALSE)
			->from('labor_master l')->order_by('l.is_active', 'DESC')->order_by('l.labor_code')->get()->result();
	}

	public function labor_exists($labor_id)
	{
		return $this->db->where('labor_id', (int) $labor_id)->count_all_results('labor_master') > 0;
	}

	public function code_exists($crew_code, $exclude_id = NULL)
	{
		$this->db->where('crew_code', $crew_code);
		if ($exclude_id !== NULL) $this->db->where('crew_id !=', (int) $exclude_id);
		return $this->db->count_all_results('crew_master') > 0;
	}

	public function member_exists($crew_id, $labor_id, $exclude_member_id = NULL)
	{
		$this->db->where('crew_id', (int) $crew_id)->where('labor_id', (int) $labor_id);
		if ($exclude_member_id !== NULL) $this->db->where('crew_member_id !=', (int) $exclude_member_id);
		return $this->db->count_all_results('crew_member') > 0;
	}

	public function find_member($crew_id, $crew_member_id)
	{
		$row = $this->db->where('crew_id', (int) $crew_id)->where('crew_member_id', (int) $crew_member_id)->get('crew_member')->row();
		return $row ?: NULL;
	}

	public function create(array $data)
	{
		return $this->db->insert('crew_master', $data) ? (int) $this->db->insert_id() : 0;
	}

	public function update($crew_id, array $data)
	{
		$ok=$this->db->where('crew_id', (int) $crew_id)->update('crew_master', $data);if($ok)$this->mark_build_ups_stale($crew_id);return$ok;
	}

	public function set_active($crew_id, $is_active)
	{
		$ok=$this->db->where('crew_id', (int) $crew_id)->update('crew_master', array('is_active' => $is_active ? 1 : 0));if($ok)$this->mark_build_ups_stale($crew_id);return$ok;
	}

	public function create_member(array $data)
	{
		if(!$this->db->insert('crew_member',$data))return 0;$id=(int)$this->db->insert_id();$this->mark_build_ups_stale($data['crew_id']);return$id;
	}

	public function update_member($crew_member_id, array $data)
	{
		$ok=$this->db->where('crew_member_id',(int)$crew_member_id)->update('crew_member',$data);if($ok)$this->mark_build_ups_stale($data['crew_id']);return$ok;
	}

	private function mark_build_ups_stale($crew_id)
	{
		if($this->db->table_exists('cost_item_labor_build_up'))$this->db->where('crew_id',(int)$crew_id)->where('labor_source_method','CREW_DERIVED')->update('cost_item_labor_build_up',array('is_stale'=>1));
	}
}
