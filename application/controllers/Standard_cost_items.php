<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Standard_cost_items extends Authorized_Controller
{
	protected $required_permission = 'standard_cost_items.view';

	public function __construct()
	{
		parent::__construct();
		$this->load->model('Standard_cost_item_model');
		$this->load->model('Governance_model');
		$this->load->library('form_validation');
	}

	public function index()
	{
		$this->render('standard_cost_items/index', array(
			'page_title' => 'Standard Cost Items', 'page_subtitle' => 'Enterprise cost codes, classifications, revisions, and governance state',
			'items' => $this->Standard_cost_item_model->all_current(),
			'page_styles' => array('assets/plugins/datatables/css/dataTables.bootstrap5.min.css'),
			'page_scripts' => array('assets/plugins/datatables/js/dataTables.min.js', 'assets/plugins/datatables/js/dataTables.bootstrap5.min.js', 'assets/js/modules/master-data.js'),
			'breadcrumbs' => array(array('label' => 'Standard Cost Items')),
		));
	}

	public function view($cost_item_id)
	{
		$item = $this->item_or_404($cost_item_id);
		$revision_id = $item->cost_item_revision_id;
		$this->render('standard_cost_items/view', array(
			'page_title' => $item->enterprise_cost_code ?: $item->cost_item_uid, 'page_subtitle' => $item->standard_item_name, 'item' => $item,
			'final_unit_rate' => $this->Standard_cost_item_model->final_unit_rate($revision_id),
			'revisions' => $this->Standard_cost_item_model->revisions($cost_item_id), 'materials' => $this->Standard_cost_item_model->materials($revision_id),
			'labor' => $this->Standard_cost_item_model->labor($revision_id), 'equipment' => $this->Standard_cost_item_model->equipment($revision_id),
			'allowances' => $this->Standard_cost_item_model->allowances($revision_id), 'productivity' => $this->Standard_cost_item_model->productivity($revision_id),
			'psmm_classifications' => $this->Standard_cost_item_model->psmm_classifications($revision_id),
			'approval_history' => $this->Governance_model->history($revision_id), 'code_component' => $this->Standard_cost_item_model->code_component($revision_id),
			'can_manage' => in_array('standard_cost_items.manage', $this->current_permissions, TRUE),
			'page_styles' => array('assets/plugins/datatables/css/dataTables.bootstrap5.min.css'),
			'page_scripts' => array('assets/plugins/datatables/js/dataTables.min.js', 'assets/plugins/datatables/js/dataTables.bootstrap5.min.js', 'assets/js/modules/master-data.js'),
			'breadcrumbs' => array(array('label' => 'Standard Cost Items', 'url' => site_url('standard-cost-items')), array('label' => $item->enterprise_cost_code ?: $item->cost_item_uid)),
		));
	}

	public function edit($cost_item_id)
	{
		$this->authorize('standard_cost_items.manage');
		$item = $this->item_or_404($cost_item_id);
		if ($item->revision_status !== 'DRAFT') show_error('Only current draft revisions can be edited.', 409, 'Revision Locked');
		$form_error = NULL;
		$required_text = array('standard_item_name' => array('Standard item name', 255), 'standard_description' => array('Standard description', NULL));
		foreach ($required_text as $field => $definition) $this->form_validation->set_rules($field, $definition[0], 'trim|required'.($definition[1] ? '|max_length['.$definition[1].']' : ''));
		foreach (array('work_type' => 150, 'strength_grade' => 100, 'size_dimension' => 150, 'application_element' => 200, 'finish_condition' => 200) as $field => $max) $this->form_validation->set_rules($field, ucwords(str_replace('_', ' ', $field)), 'trim|max_length['.$max.']');
		foreach (array('measurement_basis', 'included_work', 'excluded_work', 'change_reason') as $field) $this->form_validation->set_rules($field, ucwords(str_replace('_', ' ', $field)), 'trim');
		foreach (array('division_id', 'section_id', 'uniformat_level3_id', 'trade_id', 'uom_id') as $field) $this->form_validation->set_rules($field, ucwords(str_replace('_', ' ', $field)), 'trim|required|integer');
		foreach (array('uniformat_level4_id', 'material_group_id', 'specification_id', 'attribute_subject_class_id') as $field) $this->form_validation->set_rules($field, ucwords(str_replace('_', ' ', $field)), 'trim|integer');
		foreach (array('effective_from', 'effective_to') as $field) $this->form_validation->set_rules($field, ucwords(str_replace('_', ' ', $field)), 'trim|regex_match[/^\d{4}-\d{2}-\d{2}$/]');

		if ($this->input->method(TRUE) === 'POST' && $this->form_validation->run()) {
			$data = $this->revision_data();
			if (!$this->valid_references($data)) $form_error = 'One or more selected classifications or references are invalid.';
			elseif (!$this->Standard_cost_item_model->section_belongs_to_division($data['section_id'], $data['division_id'])) $form_error = 'The CSI section does not belong to the selected division.';
			elseif ($data['uniformat_level4_id'] !== NULL && !$this->Standard_cost_item_model->level4_belongs_to_level3($data['uniformat_level4_id'], $data['uniformat_level3_id'])) $form_error = 'The UniFormat level 4 value does not belong to the selected level 3 value.';
			elseif ($data['effective_from'] && $data['effective_to'] && $data['effective_to'] < $data['effective_from']) $form_error = 'Effective To cannot be earlier than Effective From.';
			elseif ($this->Standard_cost_item_model->update_draft($item->cost_item_revision_id, $data)) { $this->session->set_flashdata('cost_item_success', 'Draft standard cost item updated successfully.'); redirect('standard-cost-items/'.$cost_item_id); }
			else $form_error = 'Unable to update the draft revision.';
		}

		$this->render('standard_cost_items/form', array('page_title' => 'Edit Standard Cost Item', 'page_subtitle' => $item->enterprise_cost_code ?: $item->cost_item_uid, 'item' => $item, 'options' => $this->form_options(), 'form_error' => $form_error, 'breadcrumbs' => array(array('label' => 'Standard Cost Items', 'url' => site_url('standard-cost-items')), array('label' => $item->enterprise_cost_code ?: $item->cost_item_uid, 'url' => site_url('standard-cost-items/'.$cost_item_id)), array('label' => 'Edit Draft'))));
	}

	public function lifecycle($cost_item_id)
	{
		$this->authorize('standard_cost_items.manage');
		if ($this->input->method(TRUE) !== 'POST') show_error('Method Not Allowed', 405);
		$item = $this->item_or_404($cost_item_id);
		$status = $item->lifecycle_status === 'ACTIVE' ? 'INACTIVE' : 'ACTIVE';
		$this->Standard_cost_item_model->set_lifecycle($cost_item_id, $status);
		$this->session->set_flashdata('cost_item_success', 'Lifecycle status changed to '.$status.'.');
		redirect('standard-cost-items/'.$cost_item_id);
	}

	private function revision_data()
	{
		$data = array();
		foreach (array('standard_item_name', 'standard_description', 'work_type', 'strength_grade', 'size_dimension', 'application_element', 'finish_condition', 'measurement_basis', 'included_work', 'excluded_work', 'change_reason', 'effective_from', 'effective_to') as $field) $data[$field] = $this->nullable_text($field);
		foreach (array('division_id', 'section_id', 'uniformat_level3_id', 'uniformat_level4_id', 'trade_id', 'material_group_id', 'specification_id', 'uom_id', 'attribute_subject_class_id') as $field) $data[$field] = $this->nullable_id($field);
		return $data;
	}

	private function valid_references(array $data)
	{
		$map = array('division_id' => array('ref_csi_division', 'division_id'), 'section_id' => array('ref_csi_section', 'section_id'), 'uniformat_level3_id' => array('ref_uniformat_level3', 'uniformat_level3_id'), 'uniformat_level4_id' => array('ref_uniformat_level4', 'uniformat_level4_id'), 'trade_id' => array('ref_trade', 'trade_id'), 'material_group_id' => array('ref_material_group', 'material_group_id'), 'specification_id' => array('ref_specification', 'specification_id'), 'uom_id' => array('ref_uom', 'uom_id'), 'attribute_subject_class_id' => array('ref_attribute_subject_class', 'attribute_subject_class_id'));
		foreach ($map as $field => $reference) if ($data[$field] !== NULL && !$this->Standard_cost_item_model->reference_exists($reference[0], $reference[1], $data[$field])) return FALSE;
		return TRUE;
	}

	private function form_options()
	{
		$m = $this->Standard_cost_item_model;
		return array(
			'divisions' => $m->options('ref_csi_division', 'division_id', "CONCAT(division_code, ' — ', division_name)"), 'sections' => $m->options('ref_csi_section', 'section_id', "CONCAT(section_code, ' — ', section_name)"),
			'uniformat3' => $m->options('ref_uniformat_level3', 'uniformat_level3_id', "CONCAT(level3_code, ' — ', level3_name)"), 'uniformat4' => $m->options('ref_uniformat_level4', 'uniformat_level4_id', "CONCAT(level4_code, ' — ', level4_name)"),
			'trades' => $m->options('ref_trade', 'trade_id', "CONCAT(trade_code, ' — ', trade_name)"), 'material_groups' => $m->options('ref_material_group', 'material_group_id', "CONCAT(material_group_code, ' — ', material_group_name)"),
			'specifications' => $m->options('ref_specification', 'specification_id', "CONCAT(specification_source, ' ', specification_code, ' — ', specification_title)"), 'uoms' => $m->options('ref_uom', 'uom_id', "CONCAT(uom_code, ' — ', uom_name)"),
			'attribute_classes' => $m->options('ref_attribute_subject_class', 'attribute_subject_class_id', "CONCAT(class_code, ' - ', class_name)"),
		);
	}

	private function nullable_text($field) { $value = trim((string) $this->input->post($field, TRUE)); return $value === '' ? NULL : $value; }
	private function nullable_id($field) { $value = trim((string) $this->input->post($field, TRUE)); return $value === '' ? NULL : (int) $value; }
	private function item_or_404($cost_item_id) { $item = $this->Standard_cost_item_model->find_current($cost_item_id); if ($item === NULL) show_404(); return $item; }
}
