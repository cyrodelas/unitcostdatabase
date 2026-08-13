<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Reference_model extends CI_Model
{
	public function all(array $config)
	{
		$this->select_with_lookups($config);
		$first_field = array_key_first($config['fields']);
		return $this->db->order_by('r.is_active', 'DESC')->order_by('r.'.$first_field)->get()->result();
	}

	public function find(array $config, $id)
	{
		$row = $this->db->where($config['primary_key'], (int) $id)->get($config['table'])->row();
		return $row ?: NULL;
	}

	public function count(array $config, $search = '')
	{
		$this->db->from($config['table'].' r');
		$this->apply_search($config, $search);
		return (int) $this->db->count_all_results();
	}

	public function page(array $config, $search, $limit, $offset)
	{
		$this->select_with_lookups($config);
		$this->apply_search($config, $search);
		$first_field = array_key_first($config['fields']);
		return $this->db->order_by('r.is_active', 'DESC')->order_by('r.'.$first_field)
			->limit((int) $limit, (int) $offset)->get()->result();
	}

	public function lookup_options(array $field)
	{
		$lookup = $field['lookup'];
		return $this->db->select($lookup['key'].' AS option_id, '.$lookup['display'].' AS option_label', FALSE)
			->order_by('option_label')->get($lookup['table'])->result();
	}

	public function lookup_exists(array $field, $id)
	{
		$lookup = $field['lookup'];
		$value = ($lookup['key_type'] ?? 'integer') === 'integer' ? (int) $id : (string) $id;
		return $this->db->where($lookup['key'], $value)->count_all_results($lookup['table']) > 0;
	}

	public function duplicate_exists(array $config, array $data, $exclude_id = NULL)
	{
		$sets = array_merge(array($config['duplicate_fields']), $config['duplicate_alternates'] ?? array());
		foreach ($sets as $fields) {
			foreach ($fields as $field) {
				$this->db->where($field, $data[$field] ?? NULL);
			}
			if ($exclude_id !== NULL) $this->db->where($config['primary_key'].' !=', (int) $exclude_id);
			if ($this->db->count_all_results($config['table']) > 0) return TRUE;
		}
		return FALSE;
	}

	public function create(array $config, array $data)
	{
		return $this->db->insert($config['table'], $data) ? (int) $this->db->insert_id() : 0;
	}

	public function update(array $config, $id, array $data)
	{
		return $this->db->where($config['primary_key'], (int) $id)->update($config['table'], $data);
	}

	public function set_active(array $config, $id, $is_active)
	{
		return $this->db->where($config['primary_key'], (int) $id)->update($config['table'], array('is_active' => $is_active ? 1 : 0));
	}

	private function select_with_lookups(array $config)
	{
		$this->db->select('r.*')->from($config['table'].' r');
		foreach ($config['fields'] as $name => $field) {
			if ($field['type'] !== 'lookup') continue;
			$lookup = $field['lookup'];
			$this->db->select($lookup['display'].' AS '.$name.'_display', FALSE);
			$this->db->join($lookup['table'], $lookup['table'].'.'.$lookup['key'].' = r.'.$name, 'left');
		}
	}

	private function apply_search(array $config, $search)
	{
		if ($search === '') return;
		$this->db->group_start();
		$first = TRUE;
		foreach ($config['fields'] as $name => $field) {
			$method = $first ? 'like' : 'or_like';
			$this->db->{$method}('r.'.$name, $search);
			$first = FALSE;
		}
		$this->db->group_end();
	}
}
