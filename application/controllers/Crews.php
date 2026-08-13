<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Crews extends Authorized_Controller
{
	protected $required_permission = 'crews.view';

	public function __construct()
	{
		parent::__construct();
		$this->load->model('Crew_model');
		$this->load->library('form_validation');
	}

	public function index()
	{
		$this->render('crews/index', array(
			'page_title' => 'Crew Master', 'page_subtitle' => 'Reusable labor crews and calculated daily cost', 'crews' => $this->Crew_model->all_crews(),
			'can_manage' => in_array('crews.manage', $this->current_permissions, TRUE),
			'page_styles' => array('assets/plugins/datatables/css/dataTables.bootstrap5.min.css'),
			'page_scripts' => array('assets/plugins/datatables/js/dataTables.min.js', 'assets/plugins/datatables/js/dataTables.bootstrap5.min.js', 'assets/js/modules/master-data.js'),
			'breadcrumbs' => array(array('label' => 'Crew Master')),
		));
	}

	public function view($crew_id)
	{
		$crew = $this->crew_or_404($crew_id);
		$members = $this->Crew_model->members($crew_id);
		$this->render('crews/view', array(
			'page_title' => $crew->crew_code, 'page_subtitle' => $crew->crew_name ?: $crew->source_trade, 'crew' => $crew, 'members' => $members,
			'productivity_usages' => $this->Crew_model->productivity_usages($crew_id), 'calculated_cost' => $this->calculated_cost($members),
			'can_manage' => in_array('crews.manage', $this->current_permissions, TRUE),
			'page_styles' => array('assets/plugins/datatables/css/dataTables.bootstrap5.min.css'),
			'page_scripts' => array('assets/plugins/datatables/js/dataTables.min.js', 'assets/plugins/datatables/js/dataTables.bootstrap5.min.js', 'assets/js/modules/master-data.js'),
			'breadcrumbs' => array(array('label' => 'Crew Master', 'url' => site_url('crews')), array('label' => $crew->crew_code)),
		));
	}

	public function create() { $this->authorize('crews.manage'); $this->handle_crew_form(NULL); }
	public function edit($crew_id) { $this->authorize('crews.manage'); $this->handle_crew_form($this->crew_or_404($crew_id)); }

	public function status($crew_id)
	{
		$this->authorize('crews.manage');
		if ($this->input->method(TRUE) !== 'POST') show_error('Method Not Allowed', 405);
		$crew = $this->crew_or_404($crew_id);
		$this->Crew_model->set_active($crew_id, !(bool) $crew->is_active);
		$this->session->set_flashdata('crew_success', 'Crew '.((bool) $crew->is_active ? 'deactivated.' : 'activated.'));
		redirect('crews/'.$crew_id);
	}

	public function add_member($crew_id)
	{
		$this->authorize('crews.manage');
		$this->handle_member_form($this->crew_or_404($crew_id), NULL);
	}

	public function edit_member($crew_id, $crew_member_id)
	{
		$this->authorize('crews.manage');
		$crew = $this->crew_or_404($crew_id);
		$member = $this->Crew_model->find_member($crew_id, $crew_member_id);
		if ($member === NULL) show_404();
		$this->handle_member_form($crew, $member);
	}

	private function handle_crew_form($crew)
	{
		$form_error = NULL;
		$this->form_validation->set_rules('crew_code', 'Crew code', 'trim|required|max_length[50]');
		$this->form_validation->set_rules('crew_name', 'Crew name', 'trim|max_length[200]');
		$this->form_validation->set_rules('source_trade', 'Source trade', 'trim|required|max_length[100]');
		if ($this->input->method(TRUE) === 'POST' && $this->form_validation->run()) {
			$data = array('crew_code' => strtoupper(trim((string) $this->input->post('crew_code', TRUE))), 'crew_name' => $this->nullable_text('crew_name'), 'source_trade' => trim((string) $this->input->post('source_trade', TRUE)), 'is_active' => $this->input->post('is_active') ? 1 : 0);
			$id = $crew->crew_id ?? NULL;
			if ($this->Crew_model->code_exists($data['crew_code'], $id)) $form_error = 'That crew code already exists.';
			else {
				$success = $crew === NULL ? $this->Crew_model->create($data) : $this->Crew_model->update($id, $data);
				if ($success) { $target_id = $crew === NULL ? $success : $id; $this->session->set_flashdata('crew_success', 'Crew saved successfully.'); redirect('crews/'.$target_id); }
				$form_error = 'Unable to save the crew.';
			}
		}
		$this->render('crews/form', array('page_title' => $crew ? 'Edit Crew' : 'Add Crew', 'page_subtitle' => 'Crew header', 'crew' => $crew, 'form_error' => $form_error, 'breadcrumbs' => array(array('label' => 'Crew Master', 'url' => site_url('crews')), array('label' => $crew ? 'Edit' : 'Add'))));
	}

	private function handle_member_form($crew, $member)
	{
		$form_error = NULL;
		$this->form_validation->set_rules('labor_id', 'Labor craft', 'trim|required|integer');
		$this->form_validation->set_rules('member_count', 'Quantity', 'trim|required|numeric|greater_than[0]');
		$this->form_validation->set_rules('source_role_name', 'Source role name', 'trim|max_length[200]');
		if ($this->input->method(TRUE) === 'POST' && $this->form_validation->run()) {
			$data = array('crew_id' => (int) $crew->crew_id, 'labor_id' => (int) $this->input->post('labor_id'), 'member_count' => (float) $this->input->post('member_count'), 'source_role_name' => $this->nullable_text('source_role_name'));
			$id = $member->crew_member_id ?? NULL;
			if (!$this->Crew_model->labor_exists($data['labor_id'])) $form_error = 'The selected labor craft is invalid.';
			elseif ($this->Crew_model->member_exists($crew->crew_id, $data['labor_id'], $id)) $form_error = 'That labor craft is already part of this crew.';
			else {
				$success = $member === NULL ? $this->Crew_model->create_member($data) : $this->Crew_model->update_member($id, $data);
				if ($success) { $this->session->set_flashdata('crew_success', 'Crew composition saved successfully.'); redirect('crews/'.$crew->crew_id); }
				$form_error = 'Unable to save the crew member.';
			}
		}
		$this->render('crews/member_form', array('page_title' => $member ? 'Edit Crew Member' : 'Add Crew Member', 'page_subtitle' => $crew->crew_code, 'crew' => $crew, 'member' => $member, 'labor_options' => $this->Crew_model->labor_options(), 'form_error' => $form_error, 'breadcrumbs' => array(array('label' => 'Crew Master', 'url' => site_url('crews')), array('label' => $crew->crew_code, 'url' => site_url('crews/'.$crew->crew_id)), array('label' => $member ? 'Edit Member' : 'Add Member'))));
	}

	private function calculated_cost(array $members)
	{
		$total = 0; $currency = NULL; $missing_rates = 0;
		foreach ($members as $member) { if ($member->calculated_daily_cost === NULL) { $missing_rates++; continue; } $total += (float) $member->calculated_daily_cost; if ($currency === NULL) $currency = $member->currency_code; elseif ($currency !== $member->currency_code) $currency = 'MIXED'; }
		return array('total' => $total, 'currency' => $currency, 'missing_rates' => $missing_rates);
	}

	private function nullable_text($field) { $value = trim((string) $this->input->post($field, TRUE)); return $value === '' ? NULL : $value; }
	private function crew_or_404($crew_id) { $crew = $this->Crew_model->find($crew_id); if ($crew === NULL) show_404(); return $crew; }
}
