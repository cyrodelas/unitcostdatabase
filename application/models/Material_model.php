<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Material_model extends CI_Model
{
	public function all_materials()
	{
		return $this->db
			->select('m.*, c.material_category_name, g.material_group_name, u.uom_code, s.specification_code, COUNT(DISTINCT v.material_variant_id) AS variant_count, MIN(CASE WHEN rh.is_current = 1 THEN rh.unit_rate END) AS current_rate_min, MAX(CASE WHEN rh.is_current = 1 THEN rh.unit_rate END) AS current_rate_max', FALSE)
			->from('material_master m')
			->join('ref_material_category c', 'c.material_category_id = m.material_category_id', 'left')
			->join('ref_material_group g', 'g.material_group_id = m.material_group_id', 'left')
			->join('ref_uom u', 'u.uom_id = m.default_uom_id', 'left')
			->join('ref_specification s', 's.specification_id = m.specification_id', 'left')
			->join('material_variant v', 'v.material_id = m.material_id', 'left')
			->join('material_rate_history rh', 'rh.material_variant_id = v.material_variant_id', 'left')
			->group_by('m.material_id')
			->order_by('m.is_active', 'DESC')->order_by('m.material_code')
			->get()->result();
	}

	public function find($material_id)
	{
		$row = $this->db
			->select('m.*, c.material_category_code, c.material_category_name, g.material_group_code, g.material_group_name, u.uom_code, u.uom_name, s.specification_source, s.specification_code, s.specification_title, s.edition, ac.class_code AS attribute_class_code, ac.class_name AS attribute_class_name')
			->from('material_master m')
			->join('ref_material_category c', 'c.material_category_id = m.material_category_id', 'left')
			->join('ref_material_group g', 'g.material_group_id = m.material_group_id', 'left')
			->join('ref_uom u', 'u.uom_id = m.default_uom_id', 'left')
			->join('ref_specification s', 's.specification_id = m.specification_id', 'left')
			->join('ref_attribute_subject_class ac', 'ac.attribute_subject_class_id = m.attribute_subject_class_id', 'left')
			->where('m.material_id', (int) $material_id)->get()->row();
		return $row ?: NULL;
	}

	public function variants($material_id)
	{
		return $this->db
			->select('v.*, u.uom_code, rh.unit_rate, rh.rate_status, rs.schedule_code, rs.schedule_name, rs.currency_code')
			->from('material_variant v')
			->join('ref_uom u', 'u.uom_id = v.uom_id', 'left')
			->join('material_rate_history rh', 'rh.material_variant_id = v.material_variant_id AND rh.is_current = 1', 'left')
			->join('material_rate_schedule rs', 'rs.material_rate_schedule_id = rh.material_rate_schedule_id', 'left')
			->where('v.material_id', (int) $material_id)
			->order_by('v.is_active', 'DESC')->order_by('v.material_variant_code')
			->get()->result();
	}

	public function rate_history($material_id)
	{
		return $this->db
			->select('rh.*, v.material_variant_code, rs.schedule_code, rs.schedule_name, rs.currency_code, rs.effective_from, rs.effective_to')
			->from('material_rate_history rh')
			->join('material_variant v', 'v.material_variant_id = rh.material_variant_id')
			->join('material_rate_schedule rs', 'rs.material_rate_schedule_id = rh.material_rate_schedule_id', 'left')
			->where('v.material_id', (int) $material_id)
			->order_by('rh.is_current', 'DESC')->order_by('rh.created_at', 'DESC')
			->get()->result();
	}

	public function options($table, $key, $label_expression)
	{
		return $this->db->select($key.' AS option_id, '.$label_expression.' AS option_label', FALSE)->order_by('option_label')->get($table)->result();
	}

	public function option_exists($table, $key, $id)
	{
		return $this->db->where($key, (int) $id)->count_all_results($table) > 0;
	}

	public function code_exists($material_code, $exclude_id = NULL)
	{
		$this->db->where('material_code', $material_code);
		if ($exclude_id !== NULL) $this->db->where('material_id !=', (int) $exclude_id);
		return $this->db->count_all_results('material_master') > 0;
	}

	public function create(array $data)
	{
		return $this->db->insert('material_master', $data) ? (int) $this->db->insert_id() : 0;
	}

	public function update($material_id, array $data)
	{
		return $this->db->where('material_id', (int) $material_id)->update('material_master', $data);
	}

	public function set_active($material_id, $is_active)
	{
		return $this->db->where('material_id', (int) $material_id)->update('material_master', array('is_active' => $is_active ? 1 : 0));
	}
}
