<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Labor extends Authorized_Controller
{
	protected $required_permission = 'labor.view';

	public function __construct()
	{
		parent::__construct();
		$this->load->model('Labor_model');
		$this->load->library('form_validation');
	}

	public function index()
	{
		$this->render('labor/index', array(
			'page_title' => 'Labor Master', 'page_subtitle' => 'Governed labor crafts and current rate visibility',
			'labor_records' => $this->Labor_model->all_labor(), 'can_manage' => in_array('labor.manage', $this->current_permissions, TRUE),
			'page_styles' => array('assets/plugins/datatables/css/dataTables.bootstrap5.min.css'),
			'page_scripts' => array('assets/plugins/datatables/js/dataTables.min.js', 'assets/plugins/datatables/js/dataTables.bootstrap5.min.js', 'assets/js/modules/master-data.js'),
			'breadcrumbs' => array(array('label' => 'Labor Master')),
		));
	}

	public function view($labor_id)
	{
		$labor = $this->labor_or_404($labor_id);
		$this->render('labor/view', array(
			'page_title' => $labor->labor_code, 'page_subtitle' => $labor->labor_name, 'labor' => $labor,
			'rates' => $this->Labor_model->rate_history($labor_id), 'components' => $this->Labor_model->rate_components($labor_id),
			'aliases' => $this->Labor_model->aliases($labor_id), 'usages' => $this->Labor_model->usages($labor_id),
			'can_manage' => in_array('labor.manage', $this->current_permissions, TRUE),
			'page_styles' => array('assets/plugins/datatables/css/dataTables.bootstrap5.min.css'),
			'page_scripts' => array('assets/plugins/datatables/js/dataTables.min.js', 'assets/plugins/datatables/js/dataTables.bootstrap5.min.js', 'assets/js/modules/master-data.js'),
			'breadcrumbs' => array(array('label' => 'Labor Master', 'url' => site_url('labor')), array('label' => $labor->labor_code)),
		));
	}

	public function create()
	{
		$this->authorize('labor.manage');
		$this->handle_form(NULL);
	}

	public function edit($labor_id)
	{
		$this->authorize('labor.manage');
		$this->handle_form($this->labor_or_404($labor_id));
	}

	public function status($labor_id)
	{
		$this->authorize('labor.manage');
		if ($this->input->method(TRUE) !== 'POST') show_error('Method Not Allowed', 405);
		$labor = $this->labor_or_404($labor_id);
		$this->Labor_model->set_active($labor_id, !(bool) $labor->is_active);
		$this->session->set_flashdata('labor_success', 'Labor craft '.((bool) $labor->is_active ? 'deactivated.' : 'activated.'));
		redirect('labor/'.$labor_id);
	}

	private function handle_form($labor)
	{
		$form_error = NULL;
		$this->form_validation->set_rules('labor_code', 'Labor code', 'trim|required|max_length[50]');
		$this->form_validation->set_rules('labor_name', 'Labor name', 'trim|required|max_length[200]');
		$this->form_validation->set_rules('labor_category_id', 'Labor category', 'trim|required|integer');
		$this->form_validation->set_rules('labor_category', 'Legacy category label', 'trim|max_length[100]');
		if ($this->input->method(TRUE) === 'POST' && $this->form_validation->run()) {
			$data = array(
				'labor_code' => strtoupper(trim((string) $this->input->post('labor_code', TRUE))),
				'labor_name' => trim((string) $this->input->post('labor_name', TRUE)),
				'labor_category_id' => (int) $this->input->post('labor_category_id'),
				'labor_category' => $this->nullable_text('labor_category'),
				'is_active' => $this->input->post('is_active') ? 1 : 0,
			);
			$id = $labor->labor_id ?? NULL;
			if (!$this->Labor_model->category_exists($data['labor_category_id'])) $form_error = 'The selected labor category is invalid.';
			elseif ($this->Labor_model->code_exists($data['labor_code'], $id)) $form_error = 'That labor code already exists.';
			else {
				$success = $labor === NULL ? $this->Labor_model->create($data) : $this->Labor_model->update($id, $data);
				if ($success) { $target_id = $labor === NULL ? $success : $id; $this->session->set_flashdata('labor_success', 'Labor craft saved successfully.'); redirect('labor/'.$target_id); }
				$form_error = 'Unable to save the labor craft.';
			}
		}
		$this->render('labor/form', array(
			'page_title' => $labor ? 'Edit Labor Craft' : 'Add Labor Craft', 'page_subtitle' => 'Governed labor master record', 'labor' => $labor,
			'categories' => $this->Labor_model->category_options(), 'form_error' => $form_error,
			'breadcrumbs' => array(array('label' => 'Labor Master', 'url' => site_url('labor')), array('label' => $labor ? 'Edit' : 'Add')),
		));
	}

	private function nullable_text($field)
	{
		$value = trim((string) $this->input->post($field, TRUE));
		return $value === '' ? NULL : $value;
	}

	private function labor_or_404($labor_id)
	{
		$labor = $this->Labor_model->find($labor_id);
		if ($labor === NULL) show_404();
		return $labor;
	}
}
