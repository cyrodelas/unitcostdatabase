<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Roles extends Authorized_Controller
{
	protected $required_permission = 'roles.view';

	public function __construct()
	{
		parent::__construct();
		$this->load->library('form_validation');
	}

	public function index()
	{
		$this->render('roles/index', array(
			'page_title' => 'Roles',
			'page_subtitle' => 'Application role master',
			'roles' => $this->Rbac_model->all_roles(),
			'can_manage_roles' => in_array('roles.manage', $this->current_permissions, TRUE),
			'breadcrumbs' => array(array('label' => 'Roles')),
		));
	}

	public function create()
	{
		$this->authorize('roles.manage');
		$form_error = NULL;

		if ($this->input->method(TRUE) === 'POST') {
			$this->set_role_rules();
			$role_code = strtoupper(trim((string) $this->input->post('role_code', TRUE)));

			if ($this->form_validation->run()) {
				if ($this->Rbac_model->role_code_exists($role_code)) {
					$form_error = 'That role code already exists.';
				} else {
					$role_id = $this->Rbac_model->create_role(array(
						'role_code' => $role_code,
						'role_name' => trim((string) $this->input->post('role_name', TRUE)),
						'description' => trim((string) $this->input->post('description', TRUE)),
						'is_system' => 0,
						'is_active' => $this->input->post('is_active') ? 1 : 0,
					));
					if ($role_id) {
						$this->session->set_flashdata('rbac_success', 'Role created successfully.');
						redirect('roles/'.$role_id.'/permissions');
					}
					$form_error = 'Unable to create the role.';
				}
			}
		}

		$this->render('roles/form', array(
			'page_title' => 'Add Role',
			'page_subtitle' => 'Create an application role',
			'role' => NULL,
			'form_error' => $form_error,
			'breadcrumbs' => array(array('label' => 'Roles', 'url' => site_url('roles')), array('label' => 'Add Role')),
		));
	}

	public function edit($role_id)
	{
		$this->authorize('roles.manage');
		$role = $this->Rbac_model->find_role($role_id);
		if ($role === NULL) {
			show_404();
		}
		$form_error = NULL;

		if ($this->input->method(TRUE) === 'POST') {
			$this->set_role_rules((bool) $role->is_system);
			$role_code = (bool) $role->is_system
				? $role->role_code
				: strtoupper(trim((string) $this->input->post('role_code', TRUE)));

			if ($this->form_validation->run()) {
				if ($this->Rbac_model->role_code_exists($role_code, $role->role_id)) {
					$form_error = 'That role code already exists.';
				} else {
					$is_active = $role->role_code === 'SYS_ADMIN' ? 1 : ($this->input->post('is_active') ? 1 : 0);
					$this->Rbac_model->update_role($role->role_id, array(
						'role_code' => $role_code,
						'role_name' => trim((string) $this->input->post('role_name', TRUE)),
						'description' => trim((string) $this->input->post('description', TRUE)),
						'is_active' => $is_active,
					));
					$this->session->set_flashdata('rbac_success', 'Role updated successfully.');
					redirect('roles');
				}
			}
		}

		$this->render('roles/form', array(
			'page_title' => 'Edit Role',
			'page_subtitle' => $role->role_name,
			'role' => $role,
			'form_error' => $form_error,
			'breadcrumbs' => array(array('label' => 'Roles', 'url' => site_url('roles')), array('label' => 'Edit Role')),
		));
	}

	public function permissions($role_id)
	{
		$this->authorize('roles.manage');
		$role = $this->Rbac_model->find_role($role_id);
		if ($role === NULL) {
			show_404();
		}

		$permissions = $this->Rbac_model->all_permissions(TRUE);
		if ($this->input->method(TRUE) === 'POST') {
			if ($role->role_code === 'SYS_ADMIN') {
				$this->session->set_flashdata('rbac_error', 'System Administrator always retains every active permission.');
			} else {
				$selected = $this->input->post('permission_ids');
				$this->Rbac_model->sync_role_permissions(
					$role->role_id,
					is_array($selected) ? $selected : array(),
					$this->current_user->user_id
				);
				$this->session->set_flashdata('rbac_success', 'Role permissions updated successfully.');
			}
			redirect('roles/'.$role->role_id.'/permissions');
		}

		$grouped_permissions = array();
		foreach ($permissions as $permission) {
			$grouped_permissions[$permission->module_name][] = $permission;
		}

		$this->render('roles/permissions', array(
			'page_title' => 'Role Permissions',
			'page_subtitle' => $role->role_name,
			'role' => $role,
			'grouped_permissions' => $grouped_permissions,
			'selected_permission_ids' => $this->Rbac_model->permission_ids_for_role($role->role_id),
			'administrator_catalog_permissions' => $this->Rbac_model->administrator_catalog_permission_codes(),
			'breadcrumbs' => array(array('label' => 'Roles', 'url' => site_url('roles')), array('label' => 'Permissions')),
		));
	}

	private function set_role_rules($system_role = FALSE)
	{
		if (!$system_role) {
			$this->form_validation->set_rules('role_code', 'Role code', 'trim|required|max_length[50]|regex_match[/^[A-Za-z][A-Za-z0-9_]{2,49}$/]');
		}
		$this->form_validation->set_rules('role_name', 'Role name', 'trim|required|max_length[150]');
		$this->form_validation->set_rules('description', 'Description', 'trim|max_length[500]');
	}
}
