<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Controller extends CI_Controller
{
	protected $current_user = NULL;
	protected $current_permissions = array();
	protected $current_roles = array();

	public function __construct()
	{
		parent::__construct();
		if (!$this->input->is_cli_request()) {
			$this->output
				->set_header('X-Content-Type-Options: nosniff')
				->set_header('X-Frame-Options: DENY')
				->set_header('Referrer-Policy: same-origin')
				->set_header('Permissions-Policy: camera=(), microphone=(), geolocation=()')
				->set_header("Content-Security-Policy: default-src 'self'; base-uri 'self'; form-action 'self'; frame-ancestors 'none'; object-src 'none'; script-src 'self'; style-src 'self' 'unsafe-inline'; font-src 'self'; img-src 'self' data:")
				->set_header('Cache-Control: no-store, private')
				->set_header('Pragma: no-cache');
			if (!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off') {
				$this->output->set_header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
			}
		}
	}

	protected function render($content_view, array $data = array())
	{
		$data['content_view'] = $content_view;
		$data['current_user'] = $this->current_user;
		$data['current_permissions'] = $this->current_permissions;
		$data['current_roles'] = $this->current_roles;
		$html = $this->load->view('layouts/main', $data, TRUE);
		$this->output->set_output($this->normalize_legacy_symbols($html));
	}

	protected function normalize_legacy_symbols($text)
	{
		// The source dataset contains these two symbols double-encoded as UTF-8.
		// Repair presentation only; correctly encoded text and authoritative rows stay unchanged.
		return str_replace(
			array("\xC3\x83\xCB\x9C", "\xC3\x82\xC2\xB0"),
			array("\xC3\x98", "\xC2\xB0"),
			(string) $text
		);
	}
}

class Authenticated_Controller extends MY_Controller
{
	public function __construct()
	{
		parent::__construct();

		$user_id = (int) $this->session->userdata('user_id');
		if ($user_id < 1 || !$this->session->userdata('is_authenticated')) {
			$this->redirect_to_login();
		}

		$this->load->model('User_model');
		$this->current_user = $this->User_model->find_active_by_id($user_id);

		if ($this->current_user === NULL) {
			$this->session->sess_destroy();
			redirect('login');
		}

		$this->session->set_userdata(array(
			'username' => $this->current_user->username,
			'display_name' => $this->current_user->display_name,
		));

		$this->load->model('Rbac_model');
		$this->current_permissions = $this->Rbac_model->permission_codes_for_user($user_id);
		$this->current_roles = $this->Rbac_model->roles_for_user($user_id);

		$is_password_page = $this->router->class === 'account'
			&& $this->router->method === 'password';

		if ((bool) $this->current_user->must_change_password && !$is_password_page) {
			redirect('account/password');
		}
	}

	protected function authorize($permission_code)
	{
		if (!in_array($permission_code, $this->current_permissions, TRUE)) {
			show_error('You do not have permission to access this resource.', 403, 'Forbidden');
		}
	}

	private function redirect_to_login()
	{
		if ($this->input->method(TRUE) === 'GET' && uri_string() !== '') {
			$this->session->set_userdata('intended_uri', uri_string());
		}

		redirect('login');
	}
}

class Authorized_Controller extends Authenticated_Controller
{
	protected $required_permission = '';

	public function __construct()
	{
		parent::__construct();

		if ($this->required_permission === '') {
			show_error('No permission requirement is configured for this controller.', 500);
		}

		$this->authorize($this->required_permission);
	}
}
