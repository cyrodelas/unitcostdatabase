<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_model extends CI_Model
{
	private $table = 'app_user';

	public function find_for_login($identity)
	{
		$identity = mb_strtolower(trim($identity), 'UTF-8');

		return $this->db
			->group_start()
				->where('LOWER(username)', $identity)
				->or_where('LOWER(email)', $identity)
			->group_end()
			->limit(1)
			->get($this->table)
			->row();
	}

	public function find_active_by_id($user_id)
	{
		$user = $this->db
			->where('user_id', (int) $user_id)
			->where('is_active', 1)
			->limit(1)
			->get($this->table)
			->row();

		return $user ?: NULL;
	}

	public function find_by_id($user_id)
	{
		$user = $this->db
			->where('user_id', (int) $user_id)
			->limit(1)
			->get($this->table)
			->row();

		return $user ?: NULL;
	}

	public function clear_expired_lockout($user_id)
	{
		return $this->db
			->where('user_id', (int) $user_id)
			->update($this->table, array(
				'failed_login_attempts' => 0,
				'locked_until' => NULL,
			));
	}

	public function record_failed_login($user_id, $failed_attempts)
	{
		$failed_attempts++;
		$data = array('failed_login_attempts' => $failed_attempts);

		if ($failed_attempts >= 5) {
			$data['locked_until'] = date('Y-m-d H:i:s', time() + 900);
		}

		return $this->db
			->where('user_id', (int) $user_id)
			->update($this->table, $data);
	}

	public function record_successful_login($user_id)
	{
		return $this->db
			->where('user_id', (int) $user_id)
			->update($this->table, array(
				'failed_login_attempts' => 0,
				'locked_until' => NULL,
				'last_login_at' => date('Y-m-d H:i:s'),
			));
	}

	public function update_password($user_id, $password_hash)
	{
		return $this->db
			->where('user_id', (int) $user_id)
			->update($this->table, array(
				'password_hash' => $password_hash,
				'must_change_password' => 0,
				'password_changed_at' => date('Y-m-d H:i:s'),
			));
	}

	public function update_password_hash($user_id, $password_hash)
	{
		return $this->db
			->where('user_id', (int) $user_id)
			->update($this->table, array('password_hash' => $password_hash));
	}

	public function identity_exists($username, $email = NULL)
	{
		$this->db->group_start()->where('username', $username);
		if ($email !== NULL) {
			$this->db->or_where('email', $email);
		}
		return $this->db->group_end()->count_all_results($this->table) > 0;
	}

	public function create(array $user)
	{
		return $this->db->insert($this->table, $user)
			? (int) $this->db->insert_id()
			: 0;
	}
}
