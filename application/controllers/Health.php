<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Health extends MY_Controller
{
	public function index()
	{
		$database_is_ready = (bool) $this->db
			->query('SELECT 1 AS connection_test')
			->row('connection_test');

		$this->output
			->set_content_type('application/json')
			->set_output(json_encode(array(
				'application' => 'Project Nexus UCD',
				'status' => $database_is_ready ? 'ok' : 'error',
				'database' => $database_is_ready ? 'connected' : 'unavailable',
			)));
	}
}
