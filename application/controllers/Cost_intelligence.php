<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cost_intelligence extends Authorized_Controller
{
	protected $required_permission='cost_intelligence.view';

	public function __construct(){parent::__construct();$this->load->model('Cost_intelligence_model');}

	public function index()
	{
		$data=$this->Cost_intelligence_model->dashboard();$trend_labels=array();$trend_values=array();foreach($data['trends']as$trend){$trend_labels[]=$trend->period_year.' / '.($trend->division_code?:'Unclassified').' / '.($trend->uom_code?:'No UOM');$trend_values[]=round($trend->average_rate,4);}
		$this->render('cost_intelligence/index',$data+array('page_title'=>'Cost Intelligence','page_subtitle'=>'Explainable signals from governed UCD history','trend_labels'=>$trend_labels,'trend_values'=>$trend_values,'page_styles'=>array('assets/plugins/datatables/css/dataTables.bootstrap5.min.css'),'page_scripts'=>array('assets/plugins/chartjs/chart.umd.min.js','assets/plugins/datatables/js/dataTables.min.js','assets/plugins/datatables/js/dataTables.bootstrap5.min.js','assets/js/modules/cost-intelligence.js'),'breadcrumbs'=>array(array('label'=>'Cost Intelligence'))));
	}

	public function suggestions()
	{
		$text=trim((string)$this->input->get('text',TRUE));if(strlen($text)>500)$text=substr($text,0,500);$uom=$this->nullable_id('uom_id');$results=$text===''?array():$this->Cost_intelligence_model->suggestions($text,$uom,20);
		$this->render('cost_intelligence/suggestions',array('page_title'=>'Explainable Mapping Suggestions','page_subtitle'=>'Read-only candidate ranking for human review','search_text'=>$text,'selected_uom'=>$uom,'uoms'=>$this->Cost_intelligence_model->uoms(),'results'=>$results,'boq_id'=>$this->nullable_id('boq_id'),'item_id'=>$this->nullable_id('item_id'),'breadcrumbs'=>array(array('label'=>'Cost Intelligence','url'=>site_url('cost-intelligence')),array('label'=>'Mapping Suggestions'))));
	}

	private function nullable_id($key){$value=trim((string)$this->input->get($key,TRUE));return$value!==''&&ctype_digit($value)&&(int)$value>0?(int)$value:NULL;}
}
