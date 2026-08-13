<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Benchmarking extends Authorized_Controller
{
	protected $required_permission='benchmarking.view';

	public function __construct(){parent::__construct();$this->load->model('Benchmarking_model');}

	public function index()
	{
		$dimensions=$this->Benchmarking_model->dimensions();$group_by=(string)$this->input->get('group_by',TRUE);if(!isset($dimensions[$group_by]))$group_by='division';$filters=array('group_by'=>$group_by,'cost_item_id'=>$this->nullable_id('cost_item_id'),'project_id'=>$this->nullable_id('project_id'),'location_id'=>$this->nullable_id('location_id'),'division_id'=>$this->nullable_id('division_id'),'trade_id'=>$this->nullable_id('trade_id'),'uom_id'=>$this->nullable_id('uom_id'),'contractor'=>trim((string)$this->input->get('contractor',TRUE)),'date_from'=>trim((string)$this->input->get('date_from',TRUE)),'date_to'=>trim((string)$this->input->get('date_to',TRUE)),'validated_only'=>$this->input->get('validated_only')?1:0);$filter_error=NULL;
		foreach(array('date_from','date_to')as$field)if($filters[$field]!==''&&!preg_match('/^\d{4}-\d{2}-\d{2}$/',$filters[$field])){$filter_error='Dates must use YYYY-MM-DD format.';$filters[$field]='';}if($filters['date_from']!==''&&$filters['date_to']!==''&&$filters['date_to']<$filters['date_from']){$filter_error='Date To cannot be earlier than Date From.';$filters['date_from']=$filters['date_to']='';}
		$result=$this->Benchmarking_model->benchmark($filters,$group_by);$chart_groups=array_slice($result['groups'],0,20);$chart_enabled=$result['summary']->uom_count===1&&$result['summary']->currency_count<=1;
		$this->render('benchmarking/index',array('page_title'=>'Cost Benchmarking','page_subtitle'=>'Historical UCD rate comparisons by governed dimensions','dimensions'=>$dimensions,'filters'=>$filters,'options'=>$this->Benchmarking_model->filter_options(),'filter_error'=>$filter_error,'groups'=>$result['groups'],'observations'=>$result['rows'],'summary'=>$result['summary'],'dimension'=>$result['dimension'],'chart_enabled'=>$chart_enabled,'chart_labels'=>array_map(static function($row){return$row->label;},$chart_groups),'chart_values'=>array_map(static function($row){return round($row->average,4);},$chart_groups),'page_styles'=>array('assets/plugins/datatables/css/dataTables.bootstrap5.min.css'),'page_scripts'=>array('assets/plugins/chartjs/chart.umd.min.js','assets/plugins/datatables/js/dataTables.min.js','assets/plugins/datatables/js/dataTables.bootstrap5.min.js','assets/js/modules/benchmarking.js'),'breadcrumbs'=>array(array('label'=>'Cost Benchmarking'))));
	}

	private function nullable_id($key){$value=trim((string)$this->input->get($key,TRUE));return$value!==''&&ctype_digit($value)&&(int)$value>0?(int)$value:NULL;}
}
