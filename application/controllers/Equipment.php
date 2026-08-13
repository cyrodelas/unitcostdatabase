<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Equipment extends Authorized_Controller
{
	protected $required_permission = 'equipment.view';

	public function __construct()
	{
		parent::__construct();
		$this->load->model('Equipment_model');
		$this->load->library('form_validation');
	}

	public function index()
	{
		$this->render('equipment/index', array(
			'page_title' => 'Equipment Master', 'page_subtitle' => 'Governed equipment resources and cost-item usage',
			'equipment' => $this->Equipment_model->all_equipment(), 'can_manage' => in_array('equipment.manage', $this->current_permissions, TRUE),
			'page_styles' => array('assets/plugins/datatables/css/dataTables.bootstrap5.min.css'),
			'page_scripts' => array('assets/plugins/datatables/js/dataTables.min.js', 'assets/plugins/datatables/js/dataTables.bootstrap5.min.js', 'assets/js/modules/master-data.js'),
			'breadcrumbs' => array(array('label' => 'Equipment Master')),
		));
	}

	public function view($equipment_id)
	{
		$equipment = $this->equipment_or_404($equipment_id);
		$this->render('equipment/view', array(
			'page_title' => $equipment->equipment_code, 'page_subtitle' => $equipment->equipment_name, 'equipment' => $equipment,
			'usages' => $this->Equipment_model->usages($equipment_id), 'can_manage' => in_array('equipment.manage', $this->current_permissions, TRUE),
			'page_styles' => array('assets/plugins/datatables/css/dataTables.bootstrap5.min.css'),
			'page_scripts' => array('assets/plugins/datatables/js/dataTables.min.js', 'assets/plugins/datatables/js/dataTables.bootstrap5.min.js', 'assets/js/modules/master-data.js'),
			'breadcrumbs' => array(array('label' => 'Equipment Master', 'url' => site_url('equipment')), array('label' => $equipment->equipment_code)),
		));
	}

	public function create()
	{
		$this->authorize('equipment.manage');
		$this->handle_form(NULL);
	}

	public function edit($equipment_id)
	{
		$this->authorize('equipment.manage');
		$this->handle_form($this->equipment_or_404($equipment_id));
	}

	public function status($equipment_id)
	{
		$this->authorize('equipment.manage');
		if ($this->input->method(TRUE) !== 'POST') show_error('Method Not Allowed', 405);
		$equipment = $this->equipment_or_404($equipment_id);
		$this->Equipment_model->set_active($equipment_id, !(bool) $equipment->is_active);
		$this->session->set_flashdata('equipment_success', 'Equipment '.((bool) $equipment->is_active ? 'deactivated.' : 'activated.'));
		redirect('equipment/'.$equipment_id);
	}

	private function handle_form($equipment)
	{
		$form_error = NULL;
		$this->form_validation->set_rules('equipment_code', 'Equipment code', 'trim|required|max_length[50]');
		$this->form_validation->set_rules('equipment_name', 'Equipment name', 'trim|required|max_length[200]');
		$this->form_validation->set_rules('equipment_group_id', 'Equipment group', 'trim|required|integer');
		$this->form_validation->set_rules('equipment_scope', 'Equipment scope', 'trim|required|max_length[30]');
		$this->form_validation->set_rules('equipment_category', 'Equipment category', 'trim|max_length[100]');

		if ($this->input->method(TRUE) === 'POST' && $this->form_validation->run()) {
			$data = array(
				'equipment_code' => strtoupper(trim((string) $this->input->post('equipment_code', TRUE))),
				'equipment_name' => trim((string) $this->input->post('equipment_name', TRUE)),
				'equipment_group_id' => (int) $this->input->post('equipment_group_id'),
				'equipment_scope' => strtoupper(trim((string) $this->input->post('equipment_scope', TRUE))),
				'equipment_category' => $this->nullable_text('equipment_category'),
				'is_active' => $this->input->post('is_active') ? 1 : 0,
			);
			$id = $equipment->equipment_id ?? NULL;
			if (!$this->Equipment_model->group_exists($data['equipment_group_id'])) $form_error = 'The selected equipment group is invalid.';
			elseif ($this->Equipment_model->code_exists($data['equipment_code'], $id)) $form_error = 'That equipment code already exists.';
			else {
				$success = $equipment === NULL ? $this->Equipment_model->create($data) : $this->Equipment_model->update($id, $data);
				if ($success) { $target_id = $equipment === NULL ? $success : $id; $this->session->set_flashdata('equipment_success', 'Equipment saved successfully.'); redirect('equipment/'.$target_id); }
				$form_error = 'Unable to save the equipment.';
			}
		}

		$this->render('equipment/form', array(
			'page_title' => $equipment ? 'Edit Equipment' : 'Add Equipment', 'page_subtitle' => 'Equipment master record', 'equipment' => $equipment,
			'groups' => $this->Equipment_model->group_options(), 'form_error' => $form_error,
			'breadcrumbs' => array(array('label' => 'Equipment Master', 'url' => site_url('equipment')), array('label' => $equipment ? 'Edit' : 'Add')),
		));
	}

	private function nullable_text($field)
	{
		$value = trim((string) $this->input->post($field, TRUE));
		return $value === '' ? NULL : $value;
	}

	private function equipment_or_404($equipment_id)
	{
		$equipment = $this->Equipment_model->find($equipment_id);
		if ($equipment === NULL) show_404();
		return $equipment;
	}
}
