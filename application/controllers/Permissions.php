<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Permissions extends Authorized_Controller
{
	protected $required_permission = 'roles.view';

	public function index()
	{
		$this->render('permissions/index', array(
			'page_title' => 'Permissions',
			'page_subtitle' => 'Application permission catalog',
			'permissions' => $this->Rbac_model->all_permissions(),
			'breadcrumbs' => array(array('label' => 'Permissions')),
		));
	}
}
