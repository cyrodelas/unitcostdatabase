<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Ml_worker extends MY_Controller
{
	public function __construct(){parent::__construct();if(!$this->input->is_cli_request())show_404();$this->load->model('Ml_governance_model');$this->config->load('ml',TRUE);}
	public function run(){$result=$this->Ml_governance_model->process_next_export($this->config->item('artifact_path','ml'));if(!empty($result['error'])){fwrite(STDERR,$result['error'].PHP_EOL);exit(1);}echo$result['message'].PHP_EOL;}
}
