<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Labor_model extends CI_Model
{
	public function all_labor()
	{
		return $this->db
			->select('l.*, c.labor_category_code, c.labor_category_name, MAX(CASE WHEN rh.is_current = 1 THEN rh.daily_rate END) AS current_daily_rate, MAX(CASE WHEN rh.is_current = 1 THEN rh.total_with_admin_fee END) AS current_total_rate, MAX(CASE WHEN rh.is_current = 1 THEN rs.currency_code END) AS currency_code, COUNT(DISTINCT a.labor_source_alias_id) AS alias_count, COUNT(DISTINCT cl.cost_item_labor_id) AS usage_count', FALSE)
			->from('labor_master l')
			->join('ref_labor_category c', 'c.labor_category_id = l.labor_category_id', 'left')
			->join('labor_rate_history rh', 'rh.labor_id = l.labor_id', 'left')
			->join('labor_rate_schedule rs', 'rs.labor_rate_schedule_id = rh.labor_rate_schedule_id', 'left')
			->join('labor_source_alias a', 'a.labor_id = l.labor_id', 'left')
			->join('cost_item_labor cl', 'cl.labor_id = l.labor_id', 'left')
			->group_by('l.labor_id')->order_by('l.is_active', 'DESC')->order_by('l.labor_code')
			->get()->result();
	}

	public function find($labor_id)
	{
		$row = $this->db
			->select('l.*, c.labor_category_code, c.labor_category_name, c.description AS labor_category_description')
			->from('labor_master l')->join('ref_labor_category c', 'c.labor_category_id = l.labor_category_id', 'left')
			->where('l.labor_id', (int) $labor_id)->get()->row();
		return $row ?: NULL;
	}

	public function rate_history($labor_id)
	{
		return $this->db
			->select('rh.*, rs.schedule_code, rs.schedule_name, rs.currency_code, rs.admin_fee_percentage, rs.effective_from, rs.effective_to, rs.source_status')
			->from('labor_rate_history rh')->join('labor_rate_schedule rs', 'rs.labor_rate_schedule_id = rh.labor_rate_schedule_id', 'left')
			->where('rh.labor_id', (int) $labor_id)->order_by('rh.is_current', 'DESC')->order_by('rh.created_at', 'DESC')->get()->result();
	}

	public function rate_components($labor_id)
	{
		return $this->db
			->select('a.*, c.component_code, c.component_name, c.component_category, c.amount_basis, rh.is_current, rs.currency_code')
			->from('labor_rate_component_amount a')
			->join('labor_rate_history rh', 'rh.labor_rate_history_id = a.labor_rate_history_id')
			->join('ref_labor_rate_component c', 'c.labor_rate_component_id = a.labor_rate_component_id')
			->join('labor_rate_schedule rs', 'rs.labor_rate_schedule_id = rh.labor_rate_schedule_id', 'left')
			->where('rh.labor_id', (int) $labor_id)->order_by('rh.is_current', 'DESC')->order_by('c.component_code')->get()->result();
	}

	public function aliases($labor_id)
	{
		return $this->db->where('labor_id', (int) $labor_id)->order_by('is_active', 'DESC')->order_by('source_system')->get('labor_source_alias')->result();
	}

	public function usages($labor_id)
	{
		return $this->db
			->select('cl.*, r.enterprise_cost_code, r.standard_item_name, r.revision_no, r.revision_status')
			->from('cost_item_labor cl')->join('standard_cost_item_revision r', 'r.cost_item_revision_id = cl.cost_item_revision_id')
			->where('cl.labor_id', (int) $labor_id)->order_by('r.is_current', 'DESC')->order_by('r.standard_item_name')->get()->result();
	}

	public function category_options()
	{
		return $this->db->select("labor_category_id AS option_id, CONCAT(labor_category_code, ' — ', labor_category_name) AS option_label", FALSE)->order_by('labor_category_code')->get('ref_labor_category')->result();
	}

	public function category_exists($labor_category_id)
	{
		return $this->db->where('labor_category_id', (int) $labor_category_id)->count_all_results('ref_labor_category') > 0;
	}

	public function code_exists($labor_code, $exclude_id = NULL)
	{
		$this->db->where('labor_code', $labor_code);
		if ($exclude_id !== NULL) $this->db->where('labor_id !=', (int) $exclude_id);
		return $this->db->count_all_results('labor_master') > 0;
	}

	public function create(array $data)
	{
		return $this->db->insert('labor_master', $data) ? (int) $this->db->insert_id() : 0;
	}

	public function update($labor_id, array $data)
	{
		return $this->db->where('labor_id', (int) $labor_id)->update('labor_master', $data);
	}

	public function set_active($labor_id, $is_active)
	{
		return $this->db->where('labor_id', (int) $labor_id)->update('labor_master', array('is_active' => $is_active ? 1 : 0));
	}
}
