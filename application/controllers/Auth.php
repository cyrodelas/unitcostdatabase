<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends MY_Controller
{
	private $dummy_hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.';

	public function __construct()
	{
		parent::__construct();
		$this->load->model('User_model');
		$this->load->library('form_validation');
	}

	public function login()
	{
		if ($this->session->userdata('is_authenticated')) {
			redirect('');
		}

		if ($this->input->method(TRUE) === 'POST') {
			$this->form_validation->set_rules('identity', 'Username or email', 'trim|required|max_length[191]');
			$this->form_validation->set_rules('password', 'Password', 'required|max_length[4096]');

			if ($this->form_validation->run()) {
				$this->attempt_login(
					$this->input->post('identity', TRUE),
					(string) $this->input->post('password', FALSE)
				);
			}
		}

		$this->load->view('auth/login', array('page_title' => 'Sign in'));
	}

	public function logout()
	{
		if ($this->input->method(TRUE) !== 'POST') {
			show_error('Method Not Allowed', 405);
		}

		$this->session->sess_destroy();
		redirect('login');
	}

	private function attempt_login($identity, $password)
	{
		$user = $this->User_model->find_for_login($identity);

		if ($user === NULL) {
			password_verify($password, $this->dummy_hash);
			$this->login_failed();
			return;
		}

		if (!(bool) $user->is_active) {
			password_verify($password, $user->password_hash);
			$this->login_failed();
			return;
		}

		if ($user->locked_until !== NULL && strtotime($user->locked_until) > time()) {
			$this->session->set_flashdata('auth_error', 'This account is temporarily locked. Please try again later.');
			return;
		}

		if ($user->locked_until !== NULL) {
			$this->User_model->clear_expired_lockout($user->user_id);
			$user->failed_login_attempts = 0;
		}

		if (!password_verify($password, $user->password_hash)) {
			$this->User_model->record_failed_login($user->user_id, (int) $user->failed_login_attempts);
			$this->login_failed();
			return;
		}

		if (password_needs_rehash($user->password_hash, PASSWORD_DEFAULT)) {
			$this->User_model->update_password_hash(
				$user->user_id,
				password_hash($password, PASSWORD_DEFAULT)
			);
		}

		$this->User_model->record_successful_login($user->user_id);
		$this->session->sess_regenerate(TRUE);
		$this->session->set_userdata(array(
			'user_id' => (int) $user->user_id,
			'username' => $user->username,
			'display_name' => $user->display_name,
			'is_authenticated' => TRUE,
		));

		if ((bool) $user->must_change_password) {
			redirect('account/password');
		}

		$intended_uri = (string) $this->session->userdata('intended_uri');
		$this->session->unset_userdata('intended_uri');
		redirect($intended_uri !== '' ? $intended_uri : '');
	}

	private function login_failed()
	{
		$this->session->set_flashdata('auth_error', 'The username/email or password is incorrect.');
	}
}
