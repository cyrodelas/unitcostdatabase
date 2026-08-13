<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Rbac_model extends CI_Model
{
	public function permission_codes_for_user($user_id)
	{
		$rows = $this->db
			->distinct()
			->select('p.permission_code')
			->from('app_permission p')
			->join('app_role_permission rp', 'rp.permission_id = p.permission_id')
			->join('app_role r', 'r.role_id = rp.role_id')
			->join('app_user_role ur', 'ur.role_id = r.role_id')
			->where('ur.user_id', (int) $user_id)
			->where('r.is_active', 1)
			->where('p.is_active', 1)
			->order_by('p.permission_code')
			->get()
			->result_array();

		return array_column($rows, 'permission_code');
	}

	public function roles_for_user($user_id)
	{
		return $this->db
			->select('r.role_id, r.role_code, r.role_name')
			->from('app_role r')
			->join('app_user_role ur', 'ur.role_id = r.role_id')
			->where('ur.user_id', (int) $user_id)
			->where('r.is_active', 1)
			->order_by('r.role_name')
			->get()
			->result();
	}

	public function all_roles($active_only = FALSE)
	{
		$this->db
			->select('r.*, COUNT(DISTINCT rp.permission_id) AS permission_count, COUNT(DISTINCT ur.user_id) AS user_count')
			->from('app_role r')
			->join('app_role_permission rp', 'rp.role_id = r.role_id', 'left')
			->join('app_user_role ur', 'ur.role_id = r.role_id', 'left')
			->group_by('r.role_id')
			->order_by('r.is_active', 'DESC')
			->order_by('r.role_name');

		if ($active_only) {
			$this->db->where('r.is_active', 1);
		}

		return $this->db->get()->result();
	}

	public function find_role($role_id)
	{
		$role = $this->db->where('role_id', (int) $role_id)->get('app_role')->row();
		return $role ?: NULL;
	}

	public function role_code_exists($role_code, $exclude_role_id = NULL)
	{
		$this->db->where('role_code', $role_code);
		if ($exclude_role_id !== NULL) {
			$this->db->where('role_id !=', (int) $exclude_role_id);
		}
		return $this->db->count_all_results('app_role') > 0;
	}

	public function create_role(array $role)
	{
		return $this->db->insert('app_role', $role) ? (int) $this->db->insert_id() : 0;
	}

	public function update_role($role_id, array $role)
	{
		return $this->db->where('role_id', (int) $role_id)->update('app_role', $role);
	}

	public function all_permissions($active_only = FALSE)
	{
		$this->db->order_by('module_name')->order_by('permission_name');
		if ($active_only) {
			$this->db->where('is_active', 1);
		}
		return $this->db->get('app_permission')->result();
	}

	public function permission_ids_for_role($role_id)
	{
		$rows = $this->db
			->select('permission_id')
			->where('role_id', (int) $role_id)
			->get('app_role_permission')
			->result_array();

		return array_map('intval', array_column($rows, 'permission_id'));
	}

	public function sync_role_permissions($role_id, array $permission_ids, $granted_by)
	{
		$valid_ids = array();
		if ($permission_ids) {
			$rows = $this->db
				->select('permission_id')
				->where('is_active', 1)
				->where_in('permission_id', array_map('intval', $permission_ids))
				->get('app_permission')
				->result_array();
			$valid_ids = array_map('intval', array_column($rows, 'permission_id'));
		}

		$this->db->trans_start();
		$this->db->where('role_id', (int) $role_id)->delete('app_role_permission');
		foreach ($valid_ids as $permission_id) {
			$this->db->insert('app_role_permission', array(
				'role_id' => (int) $role_id,
				'permission_id' => $permission_id,
				'granted_by' => (int) $granted_by,
			));
		}
		$this->db->trans_complete();

		return $this->db->trans_status();
	}

	public function users_with_roles()
	{
		return $this->db
			->select("u.user_id, u.username, u.email, u.display_name, u.is_active, GROUP_CONCAT(r.role_name ORDER BY r.role_name SEPARATOR ', ') AS role_names", FALSE)
			->from('app_user u')
			->join('app_user_role ur', 'ur.user_id = u.user_id', 'left')
			->join('app_role r', 'r.role_id = ur.role_id', 'left')
			->group_by('u.user_id')
			->order_by('u.display_name')
			->get()
			->result();
	}

	public function role_ids_for_user($user_id)
	{
		$rows = $this->db->select('role_id')->where('user_id', (int) $user_id)->get('app_user_role')->result_array();
		return array_map('intval', array_column($rows, 'role_id'));
	}

	public function would_remove_last_system_administrator($user_id, array $new_role_ids)
	{
		$system_role = $this->db->where('role_code', 'SYS_ADMIN')->get('app_role')->row();
		if ($system_role === NULL || in_array((int) $system_role->role_id, array_map('intval', $new_role_ids), TRUE)) {
			return FALSE;
		}

		$currently_assigned = $this->db
			->where('user_id', (int) $user_id)
			->where('role_id', (int) $system_role->role_id)
			->count_all_results('app_user_role') > 0;

		if (!$currently_assigned) {
			return FALSE;
		}

		return $this->db
			->where('role_id', (int) $system_role->role_id)
			->count_all_results('app_user_role') <= 1;
	}

	public function sync_user_roles($user_id, array $role_ids, $assigned_by)
	{
		$rows = $this->db
			->select('role_id')
			->where('is_active', 1)
			->where_in('role_id', array_map('intval', $role_ids))
			->get('app_role')
			->result_array();
		$valid_ids = array_map('intval', array_column($rows, 'role_id'));

		$this->db->trans_start();
		$this->db->where('user_id', (int) $user_id)->delete('app_user_role');
		foreach ($valid_ids as $role_id) {
			$this->db->insert('app_user_role', array(
				'user_id' => (int) $user_id,
				'role_id' => $role_id,
				'assigned_by' => (int) $assigned_by,
			));
		}
		$this->db->trans_complete();

		return $this->db->trans_status();
	}
}
