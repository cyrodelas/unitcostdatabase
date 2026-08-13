<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends Authorized_Controller
{
	protected $required_permission = 'dashboard.view';

	public function index()
	{
		$this->load->model('Dashboard_model');
		$kpis = $this->Dashboard_model->kpis();
		$cards = array(
			array('key' => 'standard_cost_items', 'label' => 'Standard Cost Items', 'icon' => 'bi-journal-text', 'color' => 'primary', 'permission' => 'standard_cost_items.view'),
			array('key' => 'materials', 'label' => 'Active Materials', 'icon' => 'bi-box-seam', 'color' => 'success', 'permission' => 'materials.view'),
			array('key' => 'equipment', 'label' => 'Active Equipment', 'icon' => 'bi-truck', 'color' => 'warning', 'permission' => 'equipment.view'),
			array('key' => 'labor', 'label' => 'Active Labor Crafts', 'icon' => 'bi-people', 'color' => 'info', 'permission' => 'labor.view'),
			array('key' => 'crews', 'label' => 'Active Crews', 'icon' => 'bi-person-workspace', 'color' => 'secondary', 'permission' => 'crews.view'),
			array('key' => 'published', 'label' => 'Published Items', 'icon' => 'bi-cloud-check', 'color' => 'success', 'permission' => 'standard_cost_items.view'),
			array('key' => 'for_review', 'label' => 'Items for Review', 'icon' => 'bi-search', 'color' => 'warning', 'permission' => 'governance.review'),
			array('key' => 'for_approval', 'label' => 'Items for Approval', 'icon' => 'bi-patch-check', 'color' => 'danger', 'permission' => 'governance.approve'),
		);

		$visible_cards = array();
		foreach ($cards as $card) {
			if (in_array($card['permission'], $this->current_permissions, TRUE)) {
				$card['value'] = $kpis[$card['key']];
				$visible_cards[] = $card;
			}
		}

		$this->render('dashboard/index', array(
			'page_title' => 'Dashboard',
			'page_subtitle' => 'Current UCD library and governance snapshot',
			'cards' => $visible_cards,
			'chart_data' => array(
				'revision_status' => $this->Dashboard_model->current_revision_statuses(),
				'resource_coverage' => $this->Dashboard_model->resource_coverage(),
			),
			'operational_snapshot' => $this->Dashboard_model->operational_snapshot(),
			'page_scripts' => array(
				'assets/plugins/chartjs/chart.umd.min.js',
				'assets/js/modules/dashboard.js',
			),
			'breadcrumbs' => array(array('label' => 'Dashboard')),
		));
	}
}
