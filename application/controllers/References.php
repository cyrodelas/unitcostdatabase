<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class References extends Authorized_Controller
{
	protected $required_permission = 'references.view';
	private $entities = array();

	public function __construct()
	{
		parent::__construct();
		$this->load->config('reference_data');
		$this->entities = $this->config->item('reference_entities');
		$this->load->model('Reference_model');
		$this->load->library('form_validation');
	}

	public function index($type = NULL)
	{
		if ($type === NULL) {
			$this->render('references/catalog', array('page_title' => 'Reference Tables', 'page_subtitle' => 'Governed lookup and classification data grouped by business purpose', 'entities' => $this->entities, 'groups' => $this->config->item('reference_groups') ?: array(), 'breadcrumbs' => array(array('label' => 'Reference Tables'))));
			return;
		}
		$config = $this->entity($type);
		$is_paginated = !empty($config['server_paginated']);
		$search = trim((string) $this->input->get('q', TRUE));
		$page = max(1, (int) $this->input->get('page'));
		$per_page = 100;
		$total_records = $is_paginated ? $this->Reference_model->count($config, $search) : NULL;
		$total_pages = $is_paginated ? max(1, (int) ceil($total_records / $per_page)) : 1;
		if ($page > $total_pages) $page = $total_pages;
		$this->render('references/index', array(
			'page_title' => $config['title'], 'page_subtitle' => 'Reference data master', 'type' => $type, 'entity' => $config,
			'records' => $is_paginated ? $this->Reference_model->page($config, $search, $per_page, ($page - 1) * $per_page) : $this->Reference_model->all($config),
			'can_manage' => in_array('references.manage', $this->current_permissions, TRUE), 'is_paginated' => $is_paginated,
			'search' => $search, 'page' => $page, 'total_pages' => $total_pages, 'total_records' => $total_records,
			'page_styles' => array('assets/plugins/datatables/css/dataTables.bootstrap5.min.css'),
			'page_scripts' => $is_paginated ? array() : array('assets/plugins/datatables/js/dataTables.min.js', 'assets/plugins/datatables/js/dataTables.bootstrap5.min.js', 'assets/js/modules/references.js'),
			'breadcrumbs' => array(array('label' => 'Reference Tables', 'url' => site_url('references')), array('label' => $config['title'])),
		));
	}

	public function create($type)
	{
		$this->authorize('references.manage');
		$config = $this->entity($type);
		$this->handle_form($type, $config, NULL);
	}

	public function edit($type, $id)
	{
		$this->authorize('references.manage');
		$config = $this->entity($type);
		$record = $this->Reference_model->find($config, $id);
		if ($record === NULL) show_404();
		$this->handle_form($type, $config, $record);
	}

	public function status($type, $id)
	{
		$this->authorize('references.manage');
		if ($this->input->method(TRUE) !== 'POST') show_error('Method Not Allowed', 405);
		$config = $this->entity($type);
		$record = $this->Reference_model->find($config, $id);
		if ($record === NULL) show_404();
		$this->Reference_model->set_active($config, $id, !(bool) $record->is_active);
		$this->session->set_flashdata('reference_success', $config['title'].' record '.((bool) $record->is_active ? 'deactivated.' : 'activated.'));
		redirect('references/'.$type);
	}

	private function handle_form($type, array $config, $record)
	{
		$form_error = NULL;
		foreach ($config['fields'] as $name => $field) {
			$rules = array('trim');
			if ($field['required']) $rules[] = 'required';
			if (isset($field['max_length'])) $rules[] = 'max_length['.$field['max_length'].']';
			if ($field['type'] === 'lookup' && ($field['lookup']['key_type'] ?? 'integer') === 'integer') $rules[] = 'integer';
			if ($field['type'] === 'date') $rules[] = 'regex_match[/^\d{4}-\d{2}-\d{2}$/]';
			if ($field['type'] === 'integer') $rules[] = 'integer';
			if ($field['type'] === 'decimal') $rules[] = 'numeric';
			$this->form_validation->set_rules($name, $field['label'], implode('|', $rules));
		}
		if ($this->input->method(TRUE) === 'POST' && $this->form_validation->run()) {
			$data = array();
			foreach ($config['fields'] as $name => $field) {
				$value = trim((string) $this->input->post($name, TRUE));
				$is_integer_lookup = $field['type'] === 'lookup' && ($field['lookup']['key_type'] ?? 'integer') === 'integer';
				$data[$name] = $field['type'] === 'checkbox' ? ($this->input->post($name) ? 1 : 0) : ($value === '' ? NULL : (($field['type'] === 'integer' || $is_integer_lookup) ? (int) $value : $value));
			}
			$data['is_active'] = $this->input->post('is_active') ? 1 : 0;
			$id = $record->{$config['primary_key']} ?? NULL;
			$invalid_lookup = FALSE;
			foreach ($config['fields'] as $name => $field) {
				if ($field['type'] === 'lookup' && $data[$name] !== NULL && !$this->Reference_model->lookup_exists($field, $data[$name])) $invalid_lookup = TRUE;
			}
			if ($invalid_lookup) {
				$form_error = 'A selected parent reference is invalid.';
			} elseif ($this->Reference_model->duplicate_exists($config, $data, $id)) {
				$form_error = 'A record with the same identifying value already exists.';
			} else {
				$success = $record === NULL ? $this->Reference_model->create($config, $data) : $this->Reference_model->update($config, $id, $data);
				if ($success) {
					$this->session->set_flashdata('reference_success', $config['title'].' record saved successfully.');
					redirect('references/'.$type);
				}
				$form_error = 'Unable to save the record.';
			}
		}
		$options = array();
		foreach ($config['fields'] as $name => $field) if ($field['type'] === 'lookup') $options[$name] = $this->Reference_model->lookup_options($field);
		$this->render('references/form', array('page_title' => ($record ? 'Edit ' : 'Add ').$config['title'], 'page_subtitle' => $config['title'], 'type' => $type, 'entity' => $config, 'record' => $record, 'options' => $options, 'form_error' => $form_error, 'breadcrumbs' => array(array('label' => 'Reference Tables', 'url' => site_url('references')), array('label' => $config['title'], 'url' => site_url('references/'.$type)), array('label' => $record ? 'Edit' : 'Add'))));
	}

	private function entity($type)
	{
		if (!isset($this->entities[$type])) show_404();
		return $this->entities[$type];
	}
}
