<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Standard_cost_item_model extends CI_Model
{
	public function all_current()
	{
		return $this->db
			->select('i.cost_item_id, i.cost_item_uid, i.lifecycle_status, r.cost_item_revision_id, r.revision_no, r.enterprise_cost_code, r.standard_item_name, r.revision_status, r.coding_status, d.division_code, d.division_name, t.trade_name, u.uom_code, s.specification_code, (SELECT COUNT(*) FROM cost_item_material cm WHERE cm.cost_item_revision_id = r.cost_item_revision_id) AS material_count, (SELECT COUNT(*) FROM cost_item_labor cl WHERE cl.cost_item_revision_id = r.cost_item_revision_id) AS labor_count, (SELECT COUNT(*) FROM cost_item_equipment ce WHERE ce.cost_item_revision_id = r.cost_item_revision_id) AS equipment_count', FALSE)
			->from('standard_cost_item i')->join('standard_cost_item_revision r', 'r.cost_item_id = i.cost_item_id AND r.is_current = 1')
			->join('ref_csi_division d', 'd.division_id = r.division_id', 'left')->join('ref_trade t', 't.trade_id = r.trade_id', 'left')
			->join('ref_uom u', 'u.uom_id = r.uom_id', 'left')->join('ref_specification s', 's.specification_id = r.specification_id', 'left')
			->order_by('i.lifecycle_status', 'ASC')->order_by('r.enterprise_cost_code')->get()->result();
	}

	public function find_current($cost_item_id)
	{
		$row = $this->db
			->select('i.*, r.*, d.division_code, d.division_name, sec.section_code, sec.section_name, u3.level3_code, u3.level3_name, u4.level4_code, u4.level4_name, t.trade_code, t.trade_name, mg.material_group_code, mg.material_group_name, sp.specification_source, sp.specification_code, sp.specification_title, sp.edition, u.uom_code, u.uom_name, ac.class_code AS attribute_class_code, ac.class_name AS attribute_class_name')
			->from('standard_cost_item i')->join('standard_cost_item_revision r', 'r.cost_item_id = i.cost_item_id AND r.is_current = 1')
			->join('ref_csi_division d', 'd.division_id = r.division_id', 'left')->join('ref_csi_section sec', 'sec.section_id = r.section_id', 'left')
			->join('ref_uniformat_level3 u3', 'u3.uniformat_level3_id = r.uniformat_level3_id', 'left')->join('ref_uniformat_level4 u4', 'u4.uniformat_level4_id = r.uniformat_level4_id', 'left')
			->join('ref_trade t', 't.trade_id = r.trade_id', 'left')->join('ref_material_group mg', 'mg.material_group_id = r.material_group_id', 'left')
			->join('ref_specification sp', 'sp.specification_id = r.specification_id', 'left')->join('ref_uom u', 'u.uom_id = r.uom_id', 'left')
			->join('ref_attribute_subject_class ac', 'ac.attribute_subject_class_id = r.attribute_subject_class_id', 'left')
			->where('i.cost_item_id', (int) $cost_item_id)->get()->row();
		return $row ?: NULL;
	}

	public function revisions($cost_item_id)
	{
		return $this->db->where('cost_item_id', (int) $cost_item_id)->order_by('is_current', 'DESC')->order_by('created_at', 'DESC')->get('standard_cost_item_revision')->result();
	}

	public function materials($revision_id)
	{
		return $this->db->select('cm.*, m.material_code, m.material_name, v.material_variant_code, v.size_description, u.uom_code, rh.unit_rate, rs.currency_code')
			->from('cost_item_material cm')->join('material_master m', 'm.material_id = cm.material_id', 'left')->join('material_variant v', 'v.material_variant_id = cm.material_variant_id', 'left')
			->join('ref_uom u', 'u.uom_id = cm.uom_id', 'left')->join('material_rate_history rh', 'rh.material_variant_id = cm.material_variant_id AND rh.is_current = 1', 'left')
			->join('material_rate_schedule rs', 'rs.material_rate_schedule_id = rh.material_rate_schedule_id', 'left')->where('cm.cost_item_revision_id', (int) $revision_id)->order_by('cm.is_primary', 'DESC')->get()->result();
	}

	public function final_unit_rate($revision_id)
	{
		$sql = "SELECT
			COALESCE((SELECT SUM(cm.quantity_per_item_unit * rh.unit_rate)
				FROM cost_item_material cm
				LEFT JOIN material_rate_history rh ON rh.material_variant_id = cm.material_variant_id AND rh.is_current = 1
				WHERE cm.cost_item_revision_id = ?), 0)
			+ COALESCE((SELECT SUM(cl.labor_days_per_item_unit * rh.total_with_admin_fee)
				FROM cost_item_labor cl
				LEFT JOIN labor_rate_history rh ON rh.labor_id = cl.labor_id AND rh.is_current = 1
				WHERE cl.cost_item_revision_id = ?), 0)
			+ COALESCE((SELECT SUM(CASE WHEN t.allowance_type_code IN ('TOOLS_EQUIPMENT', 'OTHER_CONSUMABLES', 'NON_MATERIAL_ACTIVITY_INPUT') THEN a.amount_per_item_unit ELSE 0 END)
				FROM cost_item_resource_allowance a
				JOIN ref_resource_allowance_type t ON t.resource_allowance_type_id = a.resource_allowance_type_id
				WHERE a.cost_item_revision_id = ?), 0) AS final_unit_rate";
		$row = $this->db->query($sql, array((int) $revision_id, (int) $revision_id, (int) $revision_id))->row();
		return $row ? (float) $row->final_unit_rate : 0.0;
	}

	public function labor($revision_id)
	{
		return $this->db->select('cl.*, l.labor_code, l.labor_name, rh.total_with_admin_fee, rs.currency_code')
			->from('cost_item_labor cl')->join('labor_master l', 'l.labor_id = cl.labor_id', 'left')->join('labor_rate_history rh', 'rh.labor_id = cl.labor_id AND rh.is_current = 1', 'left')
			->join('labor_rate_schedule rs', 'rs.labor_rate_schedule_id = rh.labor_rate_schedule_id', 'left')->where('cl.cost_item_revision_id', (int) $revision_id)->order_by('l.labor_code')->get()->result();
	}

	public function equipment($revision_id)
	{
		return $this->db->select('ce.*, e.equipment_code, e.equipment_name')->from('cost_item_equipment ce')->join('equipment_master e', 'e.equipment_id = ce.equipment_id', 'left')->where('ce.cost_item_revision_id', (int) $revision_id)->order_by('e.equipment_code')->get()->result();
	}

	public function allowances($revision_id)
	{
		return $this->db->select('a.*, t.allowance_type_code, t.allowance_type_name')->from('cost_item_resource_allowance a')->join('ref_resource_allowance_type t', 't.resource_allowance_type_id = a.resource_allowance_type_id', 'left')->where('a.cost_item_revision_id', (int) $revision_id)->get()->result();
	}

	public function productivity($revision_id)
	{
		return $this->db->select('p.*, c.crew_code, c.crew_name, ou.uom_code AS output_uom_code, du.uom_code AS duration_uom_code')->from('cost_item_productivity p')->join('crew_master c', 'c.crew_id = p.crew_id', 'left')->join('ref_uom ou', 'ou.uom_id = p.output_uom_id', 'left')->join('ref_uom du', 'du.uom_id = p.duration_uom_id', 'left')->where('p.cost_item_revision_id', (int) $revision_id)->get()->result();
	}

	public function psmm_classifications($revision_id)
	{
		return $this->db->select('map.*, pc.classification_reference, pc.classification_col_1, sec.edition, sec.section_code, sec.section_title')->from('cost_item_psmm_classification map')->join('psmm_classification pc', 'pc.psmm_classification_id = map.psmm_classification_id')->join('psmm_section sec', 'sec.psmm_section_id = pc.psmm_section_id', 'left')->where('map.cost_item_revision_id', (int) $revision_id)->get()->result();
	}

	public function approval_history($revision_id)
	{
		return $this->db->where('cost_item_revision_id', (int) $revision_id)->order_by('action_date', 'DESC')->get('cost_item_approval_history')->result();
	}

	public function code_component($revision_id)
	{
		$row = $this->db->where('cost_item_revision_id', (int) $revision_id)->get('cost_item_code_component')->row();
		return $row ?: NULL;
	}

	public function options($table, $key, $label_expression)
	{
		return $this->db->select($key.' AS option_id, '.$label_expression.' AS option_label', FALSE)->order_by('option_label')->get($table)->result();
	}

	public function reference_exists($table, $key, $id)
	{
		return $this->db->where($key, (int) $id)->count_all_results($table) > 0;
	}

	public function section_belongs_to_division($section_id, $division_id)
	{
		return $this->db->where('section_id', (int) $section_id)->where('division_id', (int) $division_id)->count_all_results('ref_csi_section') > 0;
	}

	public function level4_belongs_to_level3($level4_id, $level3_id)
	{
		return $this->db->where('uniformat_level4_id', (int) $level4_id)->where('uniformat_level3_id', (int) $level3_id)->count_all_results('ref_uniformat_level4') > 0;
	}

	public function update_draft($revision_id, array $data)
	{
		return $this->db->where('cost_item_revision_id', (int) $revision_id)->where('is_current', 1)->where('revision_status', 'DRAFT')->update('standard_cost_item_revision', $data);
	}

	public function set_lifecycle($cost_item_id, $status)
	{
		return $this->db->where('cost_item_id', (int) $cost_item_id)->update('standard_cost_item', array('lifecycle_status' => $status));
	}
}
