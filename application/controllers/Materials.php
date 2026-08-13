<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Materials extends Authorized_Controller
{
	protected $required_permission = 'materials.view';

	public function __construct()
	{
		parent::__construct();
		$this->load->model('Material_model');
		$this->load->library('form_validation');
	}

	public function index()
	{
		$this->render('materials/index', array(
			'page_title' => 'Material Master', 'page_subtitle' => 'Materials, variants, and current rate visibility',
			'materials' => $this->Material_model->all_materials(),
			'can_manage' => in_array('materials.manage', $this->current_permissions, TRUE),
			'page_styles' => array('assets/plugins/datatables/css/dataTables.bootstrap5.min.css'),
			'page_scripts' => array('assets/plugins/datatables/js/dataTables.min.js', 'assets/plugins/datatables/js/dataTables.bootstrap5.min.js', 'assets/js/modules/master-data.js'),
			'breadcrumbs' => array(array('label' => 'Material Master')),
		));
	}

	public function view($material_id)
	{
		$material = $this->material_or_404($material_id);
		$this->render('materials/view', array(
			'page_title' => $material->material_code, 'page_subtitle' => $material->material_name, 'material' => $material,
			'variants' => $this->Material_model->variants($material_id), 'rates' => $this->Material_model->rate_history($material_id),
			'can_manage' => in_array('materials.manage', $this->current_permissions, TRUE),
			'page_styles' => array('assets/plugins/datatables/css/dataTables.bootstrap5.min.css'),
			'page_scripts' => array('assets/plugins/datatables/js/dataTables.min.js', 'assets/plugins/datatables/js/dataTables.bootstrap5.min.js', 'assets/js/modules/master-data.js'),
			'breadcrumbs' => array(array('label' => 'Material Master', 'url' => site_url('materials')), array('label' => $material->material_code)),
		));
	}

	public function create()
	{
		$this->authorize('materials.manage');
		$this->handle_form(NULL);
	}

	public function edit($material_id)
	{
		$this->authorize('materials.manage');
		$this->handle_form($this->material_or_404($material_id));
	}

	public function status($material_id)
	{
		$this->authorize('materials.manage');
		if ($this->input->method(TRUE) !== 'POST') show_error('Method Not Allowed', 405);
		$material = $this->material_or_404($material_id);
		$this->Material_model->set_active($material_id, !(bool) $material->is_active);
		$this->session->set_flashdata('material_success', 'Material '.((bool) $material->is_active ? 'deactivated.' : 'activated.'));
		redirect('materials/'.$material_id);
	}

	private function handle_form($material)
	{
		$form_error = NULL;
		$this->form_validation->set_rules('material_code', 'Material code', 'trim|required|max_length[50]');
		$this->form_validation->set_rules('material_name', 'Material name', 'trim|required|max_length[255]');
		$this->form_validation->set_rules('material_category_id', 'Material category', 'trim|required|integer');
		foreach (array('material_group_id' => 'Material group', 'default_uom_id' => 'Default UOM', 'specification_id' => 'Specification', 'attribute_subject_class_id' => 'Attribute subject class') as $field => $label) $this->form_validation->set_rules($field, $label, 'trim|integer');
		if ($this->input->method(TRUE) === 'POST' && $this->form_validation->run()) {
			$data = array(
				'material_code' => strtoupper(trim((string) $this->input->post('material_code', TRUE))),
				'material_name' => trim((string) $this->input->post('material_name', TRUE)),
				'material_category_id' => (int) $this->input->post('material_category_id'),
				'material_group_id' => $this->nullable_id('material_group_id'), 'default_uom_id' => $this->nullable_id('default_uom_id'), 'specification_id' => $this->nullable_id('specification_id'),
				'attribute_subject_class_id' => $this->nullable_id('attribute_subject_class_id'),
				'is_active' => $this->input->post('is_active') ? 1 : 0,
			);
			$id = $material->material_id ?? NULL;
			if (!$this->valid_references($data)) $form_error = 'One or more selected reference values are invalid.';
			elseif ($this->Material_model->code_exists($data['material_code'], $id)) $form_error = 'That material code already exists.';
			else {
				$success = $material === NULL ? $this->Material_model->create($data) : $this->Material_model->update($id, $data);
				if ($success) { $target_id = $material === NULL ? $success : $id; $this->session->set_flashdata('material_success', 'Material saved successfully.'); redirect('materials/'.$target_id); }
				$form_error = 'Unable to save the material.';
			}
		}
		$this->render('materials/form', array('page_title' => $material ? 'Edit Material' : 'Add Material', 'page_subtitle' => 'Material master record', 'material' => $material, 'options' => $this->form_options(), 'form_error' => $form_error, 'breadcrumbs' => array(array('label' => 'Material Master', 'url' => site_url('materials')), array('label' => $material ? 'Edit' : 'Add'))));
	}

	private function form_options()
	{
		return array(
			'categories' => $this->Material_model->options('ref_material_category', 'material_category_id', "CONCAT(material_category_code, ' — ', material_category_name)"),
			'groups' => $this->Material_model->options('ref_material_group', 'material_group_id', "CONCAT(material_group_code, ' — ', material_group_name)"),
			'uoms' => $this->Material_model->options('ref_uom', 'uom_id', "CONCAT(uom_code, ' — ', uom_name)"),
			'specifications' => $this->Material_model->options('ref_specification', 'specification_id', "CONCAT(specification_source, ' ', specification_code, ' — ', specification_title)"),
			'attribute_classes' => $this->Material_model->options('ref_attribute_subject_class', 'attribute_subject_class_id', "CONCAT(class_code, ' - ', class_name)"),
		);
	}

	private function valid_references(array $data)
	{
		$map = array('material_category_id' => array('ref_material_category', 'material_category_id'), 'material_group_id' => array('ref_material_group', 'material_group_id'), 'default_uom_id' => array('ref_uom', 'uom_id'), 'specification_id' => array('ref_specification', 'specification_id'), 'attribute_subject_class_id' => array('ref_attribute_subject_class', 'attribute_subject_class_id'));
		foreach ($map as $field => $reference) if ($data[$field] !== NULL && !$this->Material_model->option_exists($reference[0], $reference[1], $data[$field])) return FALSE;
		return TRUE;
	}

	private function nullable_id($field)
	{
		$value = trim((string) $this->input->post($field, TRUE));
		return $value === '' ? NULL : (int) $value;
	}

	private function material_or_404($material_id)
	{
		$material = $this->Material_model->find($material_id);
		if ($material === NULL) show_404();
		return $material;
	}
}
