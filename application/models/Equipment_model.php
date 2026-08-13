<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Equipment_model extends CI_Model
{
	public function all_equipment()
	{
		return $this->db
			->select('e.*, g.equipment_group_code, g.equipment_group_name, COUNT(DISTINCT ce.cost_item_equipment_id) AS usage_count')
			->from('equipment_master e')
			->join('ref_equipment_group g', 'g.equipment_group_id = e.equipment_group_id', 'left')
			->join('cost_item_equipment ce', 'ce.equipment_id = e.equipment_id', 'left')
			->group_by('e.equipment_id')->order_by('e.is_active', 'DESC')->order_by('e.equipment_code')
			->get()->result();
	}

	public function find($equipment_id)
	{
		$row = $this->db
			->select('e.*, g.equipment_group_code, g.equipment_group_name')
			->from('equipment_master e')
			->join('ref_equipment_group g', 'g.equipment_group_id = e.equipment_group_id', 'left')
			->where('e.equipment_id', (int) $equipment_id)->get()->row();
		return $row ?: NULL;
	}

	public function usages($equipment_id)
	{
		return $this->db
			->select('ce.*, r.enterprise_cost_code, r.standard_item_name, r.revision_no, r.revision_status')
			->from('cost_item_equipment ce')
			->join('standard_cost_item_revision r', 'r.cost_item_revision_id = ce.cost_item_revision_id')
			->where('ce.equipment_id', (int) $equipment_id)
			->order_by('r.is_current', 'DESC')->order_by('r.standard_item_name')
			->get()->result();
	}

	public function group_options()
	{
		return $this->db
			->select("equipment_group_id AS option_id, CONCAT(equipment_group_code, ' — ', equipment_group_name) AS option_label", FALSE)
			->order_by('equipment_group_code')->get('ref_equipment_group')->result();
	}

	public function group_exists($equipment_group_id)
	{
		return $this->db->where('equipment_group_id', (int) $equipment_group_id)->count_all_results('ref_equipment_group') > 0;
	}

	public function code_exists($equipment_code, $exclude_id = NULL)
	{
		$this->db->where('equipment_code', $equipment_code);
		if ($exclude_id !== NULL) $this->db->where('equipment_id !=', (int) $exclude_id);
		return $this->db->count_all_results('equipment_master') > 0;
	}

	public function create(array $data)
	{
		return $this->db->insert('equipment_master', $data) ? (int) $this->db->insert_id() : 0;
	}

	public function update($equipment_id, array $data)
	{
		return $this->db->where('equipment_id', (int) $equipment_id)->update('equipment_master', $data);
	}

	public function set_active($equipment_id, $is_active)
	{
		return $this->db->where('equipment_id', (int) $equipment_id)->update('equipment_master', array('is_active' => $is_active ? 1 : 0));
	}
}
