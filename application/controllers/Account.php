<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Account extends Authenticated_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->library(array('form_validation', 'password_policy'));
	}

	public function index()
	{
		$this->render('account/index', array(
			'page_title' => 'My Account',
			'page_subtitle' => 'Authentication profile',
			'breadcrumbs' => array(
				array('label' => 'Home', 'url' => site_url()),
				array('label' => 'My Account'),
			),
		));
	}

	public function password()
	{
		if ($this->input->method(TRUE) === 'POST') {
			$this->form_validation->set_rules('current_password', 'Current password', 'required|max_length[4096]');
			$this->form_validation->set_rules('new_password', 'New password', 'required|max_length[4096]|callback_strong_password');
			$this->form_validation->set_rules('confirm_password', 'Confirm password', 'required|matches[new_password]');

			if ($this->form_validation->run()) {
				$current_password = (string) $this->input->post('current_password', FALSE);
				$new_password = (string) $this->input->post('new_password', FALSE);

				if (!password_verify($current_password, $this->current_user->password_hash)) {
					$this->session->set_flashdata('account_error', 'The current password is incorrect.');
				} elseif (password_verify($new_password, $this->current_user->password_hash)) {
					$this->session->set_flashdata('account_error', 'The new password must be different from the current password.');
				} else {
					$this->User_model->update_password(
						$this->current_user->user_id,
						password_hash($new_password, PASSWORD_DEFAULT)
					);
					$this->session->set_flashdata('account_success', 'Your password has been changed.');
					redirect('account');
				}
			}
		}

		$this->render('account/password', array(
			'page_title' => 'Change Password',
			'page_subtitle' => (bool) $this->current_user->must_change_password
				? 'Change the temporary password before continuing.'
				: 'Update your account password.',
			'password_policy' => $this->password_policy->description(),
			'breadcrumbs' => array(
				array('label' => 'Home', 'url' => site_url()),
				array('label' => 'Change Password'),
			),
		));
	}

	public function strong_password($password)
	{
		if ($this->password_policy->is_valid($password)) {
			return TRUE;
		}

		$this->form_validation->set_message('strong_password', $this->password_policy->description());
		return FALSE;
	}
}
