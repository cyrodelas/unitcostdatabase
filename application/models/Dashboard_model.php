<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard_model extends CI_Model
{
	public function kpis()
	{
		return array(
			'standard_cost_items' => $this->db->count_all('standard_cost_item'),
			'materials' => $this->db->where('is_active', 1)->count_all_results('material_master'),
			'equipment' => $this->db->where('is_active', 1)->count_all_results('equipment_master'),
			'labor' => $this->db->where('is_active', 1)->count_all_results('labor_master'),
			'crews' => $this->db->where('is_active', 1)->count_all_results('crew_master'),
			'published' => $this->count_current_revisions(array('revision_status' => array('PUBLISHED'))),
			'for_review' => $this->count_review_queue(),
			'for_approval' => $this->count_current_revisions(array('revision_status' => array('FOR_APPROVAL', 'PENDING_APPROVAL'))),
		);
	}

	public function current_revision_statuses()
	{
		$rows = $this->db
			->select("COALESCE(NULLIF(revision_status, ''), 'UNSPECIFIED') AS status, COUNT(*) AS item_count", FALSE)
			->where('is_current', 1)
			->group_by('revision_status')
			->order_by('item_count', 'DESC')
			->get('standard_cost_item_revision')
			->result_array();

		$labels = array();
		foreach (array_column($rows, 'status') as $status) {
			$labels[] = $this->humanize_status($status);
		}

		return array(
			'labels' => $labels,
			'values' => array_map('intval', array_column($rows, 'item_count')),
		);
	}

	public function resource_coverage()
	{
		return array(
			'labels' => array('Material build-up', 'Labor build-up', 'Equipment build-up'),
			'values' => array(
				$this->count_current_revisions_with_resource('cost_item_material'),
				$this->count_current_revisions_with_resource('cost_item_labor'),
				$this->count_current_revisions_with_resource('cost_item_equipment'),
			),
			'total_current_revisions' => (int) $this->db->where('is_current', 1)->count_all_results('standard_cost_item_revision'),
		);
	}

	public function operational_snapshot()
	{
		return array(
			'coded_current_revisions' => (int) $this->db
				->where('is_current', 1)
				->where_in('coding_status', array('CODED', 'CODED_DRAFT'))
				->count_all_results('standard_cost_item_revision'),
			'rate_observations' => (int) $this->db->count_all('cost_item_rate_history'),
			'validated_rate_observations' => (int) $this->db
				->where('is_validated', 1)
				->where('validation_status', 'VALID')
				->count_all_results('cost_item_rate_history'),
			'projects' => (int) $this->db->where('is_active', 1)->count_all_results('project_master'),
		);
	}

	private function count_current_revisions(array $filters)
	{
		$this->db->where('is_current', 1);
		foreach ($filters as $field => $values) {
			$this->db->where_in($field, $values);
		}
		return (int) $this->db->count_all_results('standard_cost_item_revision');
	}

	private function count_review_queue()
	{
		return (int) $this->db
			->where('is_current', 1)
			->group_start()
				->where_in('revision_status', array('FOR_REVIEW', 'UNDER_REVIEW'))
				->or_where('coding_status', 'PENDING_PSMM_REVIEW')
			->group_end()
			->count_all_results('standard_cost_item_revision');
	}

	private function count_current_revisions_with_resource($resource_table)
	{
		$row = $this->db
			->select('COUNT(DISTINCT revision.cost_item_revision_id) AS item_count', FALSE)
			->from('standard_cost_item_revision revision')
			->join($resource_table.' resource', 'resource.cost_item_revision_id = revision.cost_item_revision_id')
			->where('revision.is_current', 1)
			->get()
			->row();

		return (int) $row->item_count;
	}

	private function humanize_status($status)
	{
		return ucwords(strtolower(str_replace('_', ' ', $status)));
	}
}
