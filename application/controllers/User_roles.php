<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_roles extends Authorized_Controller
{
	protected $required_permission = 'users.manage';

	public function index()
	{
		$this->render('user_roles/index', array(
			'page_title' => 'User Role Assignments',
			'page_subtitle' => 'Assign application access roles',
			'users' => $this->Rbac_model->users_with_roles(),
			'breadcrumbs' => array(array('label' => 'User Role Assignments')),
		));
	}

	public function edit($user_id)
	{
		$user = $this->User_model->find_by_id($user_id);
		if ($user === NULL) {
			show_404();
		}
		$form_error = NULL;

		if ($this->input->method(TRUE) === 'POST') {
			$selected = $this->input->post('role_ids');
			$selected = is_array($selected) ? array_map('intval', $selected) : array();

			if (!$selected) {
				$form_error = 'Select at least one active role.';
			} elseif ($this->Rbac_model->would_remove_last_system_administrator($user->user_id, $selected)) {
				$form_error = 'The final System Administrator assignment cannot be removed.';
			} else {
				$this->Rbac_model->sync_user_roles($user->user_id, $selected, $this->current_user->user_id);
				$this->session->set_flashdata('rbac_success', 'User roles updated successfully.');
				redirect('user-roles');
			}
		}

		$this->render('user_roles/edit', array(
			'page_title' => 'Assign User Roles',
			'page_subtitle' => $user->display_name,
			'user' => $user,
			'roles' => $this->Rbac_model->all_roles(TRUE),
			'selected_role_ids' => $this->Rbac_model->role_ids_for_user($user->user_id),
			'form_error' => $form_error,
			'breadcrumbs' => array(array('label' => 'User Roles', 'url' => site_url('user-roles')), array('label' => 'Assign Roles')),
		));
	}
}
