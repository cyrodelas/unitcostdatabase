<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Password_policy
{
	public function is_valid($password)
	{
		return strlen($password) >= 12
			&& preg_match('/[a-z]/', $password)
			&& preg_match('/[A-Z]/', $password)
			&& preg_match('/[0-9]/', $password)
			&& preg_match('/[^a-zA-Z0-9]/', $password);
	}

	public function description()
	{
		return 'Use at least 12 characters with uppercase, lowercase, number, and symbol.';
	}
}
