<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth_cli extends MY_Controller
{
	public function __construct()
	{
		parent::__construct();
		if (!$this->input->is_cli_request()) {
			show_404();
		}
		$this->load->model('User_model');
		$this->load->library('password_policy');
	}

	public function create()
	{
		$username = trim((string) getenv('UCD_INITIAL_USERNAME'));
		$email = getenv('UCD_INITIAL_EMAIL');
		$email = ($email === FALSE || $email === '') ? NULL : mb_strtolower(trim($email), 'UTF-8');
		$display_name = trim((string) getenv('UCD_INITIAL_DISPLAY_NAME'));
		$password = getenv('UCD_INITIAL_PASSWORD');

		if ($username === '' || $display_name === '' || $password === FALSE || $password === '') {
			fwrite(STDERR, "Set UCD_INITIAL_USERNAME, UCD_INITIAL_DISPLAY_NAME, UCD_INITIAL_PASSWORD, and optionally UCD_INITIAL_EMAIL; then run: php index.php auth_cli create\n");
			exit(1);
		}

		if (!preg_match('/^[a-zA-Z0-9._-]{3,100}$/', $username)) {
			fwrite(STDERR, "Username must be 3-100 characters using letters, numbers, dot, underscore, or hyphen.\n");
			exit(1);
		}

		if ($email !== NULL && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
			fwrite(STDERR, "Email address is invalid.\n");
			exit(1);
		}

		if (!$this->password_policy->is_valid($password)) {
			fwrite(STDERR, $this->password_policy->description()."\n");
			exit(1);
		}

		if ($this->User_model->identity_exists($username, $email)) {
			fwrite(STDERR, "Username or email already exists.\n");
			exit(1);
		}

		$user_id = $this->User_model->create(array(
			'username' => $username,
			'email' => $email,
			'display_name' => $display_name,
			'password_hash' => password_hash($password, PASSWORD_DEFAULT),
			'is_active' => 1,
			'must_change_password' => 1,
		));

		if ($user_id < 1) {
			fwrite(STDERR, "Unable to create user.\n");
			exit(1);
		}

		echo "Created user {$username} (ID {$user_id}). Password change is required at first login.\n";
	}
}
