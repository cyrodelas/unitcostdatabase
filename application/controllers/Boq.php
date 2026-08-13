<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Boq extends Authorized_Controller
{
	protected $required_permission='boq.view';
	public function __construct(){parent::__construct();$this->load->model('Boq_model');$this->load->library(array('form_validation','Boq_import_parser'));}
	public function index(){$this->render('boq/index',array('page_title'=>'BOQ Management','page_subtitle'=>'Project bills of quantities and governed imports','boqs'=>$this->Boq_model->all(),'can_manage'=>in_array('boq.manage',$this->current_permissions,TRUE),'page_styles'=>array('assets/plugins/datatables/css/dataTables.bootstrap5.min.css'),'page_scripts'=>array('assets/plugins/datatables/js/dataTables.min.js','assets/plugins/datatables/js/dataTables.bootstrap5.min.js','assets/js/modules/master-data.js'),'breadcrumbs'=>array(array('label'=>'BOQ'))));}
	public function view($id){$boq=$this->boq_or_404($id);$this->render('boq/view',array('page_title'=>$boq->boq_code,'page_subtitle'=>$boq->boq_name,'boq'=>$boq,'items'=>$this->Boq_model->items($id),'batches'=>$this->Boq_model->batches($id),'can_manage'=>in_array('boq.manage',$this->current_permissions,TRUE),'page_styles'=>array('assets/plugins/datatables/css/dataTables.bootstrap5.min.css'),'page_scripts'=>array('assets/plugins/datatables/js/dataTables.min.js','assets/plugins/datatables/js/dataTables.bootstrap5.min.js','assets/js/modules/master-data.js'),'breadcrumbs'=>array(array('label'=>'BOQ','url'=>site_url('boq')),array('label'=>$boq->boq_code))));}
	public function create(){$this->authorize('boq.manage');$this->header_form(NULL);}
	public function edit($id){$this->authorize('boq.manage');$this->header_form($this->boq_or_404($id));}
	public function status($id){$this->authorize('boq.manage');if($this->input->method(TRUE)!=='POST')show_error('Method Not Allowed',405);$boq=$this->boq_or_404($id);$this->Boq_model->status($id,!(bool)$boq->is_active,$this->current_user->user_id);$this->success('BOQ '.($boq->is_active?'deactivated.':'activated.'),'boq/'.$id);}
	public function delete($id)
	{
		$this->authorize('boq.manage');
		if($this->input->method(TRUE)!=='POST')show_error('Method Not Allowed',405);
		$boq=$this->boq_or_404($id);
		if(!in_array($boq->boq_status,array('DRAFT','VALIDATED'),TRUE))show_error('Only Draft or Validated BOQs can be deleted.',409,'BOQ Deletion Blocked');
		$confirmation=trim((string)$this->input->post('confirmation',TRUE));
		if(!hash_equals((string)$boq->boq_code,$confirmation))show_error('Enter the exact BOQ code to confirm permanent deletion.',422,'Confirmation Required');
		$stored_files=$this->Boq_model->delete_boq($id);
		if($stored_files===FALSE)show_error('The BOQ could not be deleted.',500,'Deletion Failed');
		$this->remove_boq_uploads($stored_files);
		log_message('info','BOQ deleted: id='.(int)$id.' code='.$boq->boq_code.' actor='.(int)$this->current_user->user_id);
		$this->success('BOQ '.$boq->boq_code.' and its dependent import/mapping records were permanently deleted.','boq');
	}
	public function add_item($id){$this->authorize('boq.manage');$this->item_form($this->boq_or_404($id),NULL);}
	public function edit_item($id,$item_id){$this->authorize('boq.manage');$boq=$this->boq_or_404($id);$this->item_form($boq,$this->item_or_404($id,$item_id));}
	public function item_status($id,$item_id){$this->authorize('boq.manage');if($this->input->method(TRUE)!=='POST')show_error('Method Not Allowed',405);$boq=$this->boq_or_404($id);$this->editable($boq);$item=$this->item_or_404($id,$item_id);$this->Boq_model->item_status($item_id,!(bool)$item->is_active,$this->current_user->user_id);$this->success('BOQ item '.($item->is_active?'deactivated.':'activated.'),'boq/'.$id);}

	public function import($id)
	{
		$this->authorize('boq.manage');$boq=$this->boq_or_404($id);$this->editable($boq);$error=NULL;$file=NULL;
		if($this->input->method(TRUE)==='POST')try{$file=$this->store_upload();$parsed=$this->boq_import_parser->parse($file['path'],strtolower(pathinfo($file['original'],PATHINFO_EXTENSION)));$batch=$this->Boq_model->stage_import($id,$file,strtoupper(pathinfo($file['original'],PATHINFO_EXTENSION)),$parsed['sheet'],$parsed['rows'],$this->current_user->user_id,$parsed['profile']??array());redirect('boq/'.$id.'/imports/'.$batch);}catch(Exception$e){if($file&&isset($file['path'])&&strpos(realpath(dirname($file['path'])),realpath(APPPATH.'cache/boq_uploads'))===0&&is_file($file['path']))unlink($file['path']);$error=$e->getMessage();}
		$this->render('boq/import',array('page_title'=>'Import BOQ Items','page_subtitle'=>$boq->boq_code,'boq'=>$boq,'form_error'=>$error,'breadcrumbs'=>array(array('label'=>'BOQ','url'=>site_url('boq')),array('label'=>$boq->boq_code,'url'=>site_url('boq/'.$id)),array('label'=>'Import'))));
	}

	public function batch($id,$batch_id)
	{
		$boq=$this->boq_or_404($id);$batch=$this->Boq_model->batch($id,$batch_id);if(!$batch)show_404();$this->render('boq/batch',array('page_title'=>'Import Batch #'.$batch_id,'page_subtitle'=>$boq->boq_code,'boq'=>$boq,'batch'=>$batch,'rows'=>$this->Boq_model->staged($batch_id),'can_manage'=>in_array('boq.manage',$this->current_permissions,TRUE),'breadcrumbs'=>array(array('label'=>'BOQ','url'=>site_url('boq')),array('label'=>$boq->boq_code,'url'=>site_url('boq/'.$id)),array('label'=>'Import Batch'))));
	}

	public function commit($id,$batch_id)
	{
		$this->authorize('boq.manage');if($this->input->method(TRUE)!=='POST')show_error('Method Not Allowed',405);$boq=$this->boq_or_404($id);$this->editable($boq);if(!$this->Boq_model->commit_batch($id,$batch_id,$this->current_user->user_id))show_error('Only a validated Ready batch can be imported.',409,'Import Not Ready');$this->success('Validated BOQ items imported successfully.','boq/'.$id);
	}

	private function header_form($boq)
	{
		$error=NULL;$this->form_validation->set_rules('project_id','Project','trim|required|integer');$this->form_validation->set_rules('boq_code','BOQ code','trim|required|max_length[50]');$this->form_validation->set_rules('boq_name','BOQ name','trim|required|max_length[255]');$this->form_validation->set_rules('description','Description','trim');$this->form_validation->set_rules('document_reference','Document reference','trim|max_length[255]');$this->form_validation->set_rules('currency_code','Currency','trim|required|exact_length[3]|alpha');$this->form_validation->set_rules('revision_no','Revision','trim|max_length[20]');$this->form_validation->set_rules('boq_status','BOQ status','trim|required|in_list[DRAFT,VALIDATED,ACTIVE,ARCHIVED]');
		if($this->input->method(TRUE)==='POST'&&$this->form_validation->run()){$actor=(int)$this->current_user->user_id;$data=array('project_id'=>(int)$this->input->post('project_id'),'boq_code'=>strtoupper(trim((string)$this->input->post('boq_code',TRUE))),'boq_name'=>trim((string)$this->input->post('boq_name',TRUE)),'description'=>$this->null_post('description'),'document_reference'=>$this->null_post('document_reference'),'currency_code'=>strtoupper(trim((string)$this->input->post('currency_code',TRUE))),'revision_no'=>$this->null_post('revision_no'),'boq_status'=>$this->input->post('boq_status',TRUE),'is_active'=>$this->input->post('is_active')?1:0,'updated_by'=>$actor);if(!$boq)$data['created_by']=$actor;$id=$boq->boq_id??NULL;if(!$this->Boq_model->project_exists($data['project_id']))$error='The selected project is invalid.';elseif($this->Boq_model->code_exists($data['boq_code'],$id))$error='That BOQ code already exists.';else{$ok=$boq?$this->Boq_model->update($id,$data):$this->Boq_model->create($data);if($ok)$this->success('BOQ saved successfully.','boq/'.($boq?$id:$ok));$error='Unable to save the BOQ.';}}
		$this->render('boq/form',array('page_title'=>$boq?'Edit BOQ':'Add BOQ','page_subtitle'=>'BOQ header','boq'=>$boq,'projects'=>$this->Boq_model->projects(),'form_error'=>$error,'breadcrumbs'=>array(array('label'=>'BOQ','url'=>site_url('boq')),array('label'=>$boq?'Edit':'Add'))));
	}

	private function item_form($boq,$item)
	{
		$this->editable($boq);$error=NULL;$this->form_validation->set_rules('line_no','Line number','trim|required|integer|greater_than[0]');$this->form_validation->set_rules('item_reference','Item reference','trim|max_length[100]');$this->form_validation->set_rules('section_reference','Section reference','trim|max_length[255]');$this->form_validation->set_rules('item_description','Description','trim|required');$this->form_validation->set_rules('uom_id','UOM','trim|integer');$this->form_validation->set_rules('source_uom_text','Source UOM','trim|max_length[50]');$this->form_validation->set_rules('quantity','Quantity','trim|required|numeric|greater_than_equal_to[0]');$this->form_validation->set_rules('unit_rate','Unit rate','trim|numeric|greater_than_equal_to[0]');$this->form_validation->set_rules('line_amount','Line amount','trim|numeric|greater_than_equal_to[0]');$this->form_validation->set_rules('notes','Notes','trim');
		if($this->input->method(TRUE)==='POST'&&$this->form_validation->run()){$actor=(int)$this->current_user->user_id;$uom=$this->input->post('uom_id');$data=array('boq_id'=>(int)$boq->boq_id,'line_no'=>(int)$this->input->post('line_no'),'item_reference'=>$this->null_post('item_reference'),'section_reference'=>$this->null_post('section_reference'),'item_description'=>trim((string)$this->input->post('item_description',TRUE)),'uom_id'=>$uom===''?NULL:(int)$uom,'source_uom_text'=>$this->null_post('source_uom_text'),'quantity'=>$this->input->post('quantity'),'unit_rate'=>$this->number_post('unit_rate'),'line_amount'=>$this->number_post('line_amount'),'notes'=>$this->null_post('notes'),'is_active'=>$this->input->post('is_active')?1:0,'updated_by'=>$actor);if(!$item)$data['created_by']=$actor;$item_id=$item->boq_item_id??NULL;if($data['uom_id']&&!$this->Boq_model->uom_exists($data['uom_id']))$error='The selected UOM is invalid.';elseif($this->Boq_model->line_exists($boq->boq_id,$data['line_no'],$item_id))$error='That line number already exists in this BOQ.';elseif($data['unit_rate']!==NULL&&$data['line_amount']!==NULL&&abs(($data['quantity']*$data['unit_rate'])-$data['line_amount'])>0.01)$error='Line amount must equal quantity × unit rate.';else{$ok=$item?$this->Boq_model->update_item($item_id,$data):$this->Boq_model->create_item($data);if($ok)$this->success('BOQ item saved successfully.','boq/'.$boq->boq_id);$error='Unable to save the BOQ item.';}}
		$this->render('boq/item_form',array('page_title'=>$item?'Edit BOQ Item':'Add BOQ Item','page_subtitle'=>$boq->boq_code,'boq'=>$boq,'item'=>$item,'uoms'=>$this->Boq_model->uoms(),'form_error'=>$error,'breadcrumbs'=>array(array('label'=>'BOQ','url'=>site_url('boq')),array('label'=>$boq->boq_code,'url'=>site_url('boq/'.$boq->boq_id)),array('label'=>$item?'Edit Item':'Add Item'))));
	}

	private function store_upload(){if(empty($_FILES['boq_file'])||$_FILES['boq_file']['error']!==UPLOAD_ERR_OK)throw new RuntimeException('Select a CSV or XLSX file to upload.');$f=$_FILES['boq_file'];if($f['size']>10*1024*1024)throw new RuntimeException('The file exceeds the 10 MB limit.');$original=basename($f['name']);$ext=strtolower(pathinfo($original,PATHINFO_EXTENSION));if(!in_array($ext,array('csv','xlsx'),TRUE))throw new RuntimeException('Only CSV and XLSX files are supported.');$mime=(new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);$allowed=$ext==='csv'?array('text/plain','text/csv','application/csv','application/vnd.ms-excel'):array('application/zip','application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');if(!in_array($mime,$allowed,TRUE))throw new RuntimeException('The uploaded file content does not match its extension.');if($ext==='xlsx'){$zip=new ZipArchive();if($zip->open($f['tmp_name'])!==TRUE||$zip->locateName('[Content_Types].xml')===FALSE){if($zip instanceof ZipArchive)$zip->close();throw new RuntimeException('The XLSX workbook structure is invalid.');}$zip->close();}$dir=APPPATH.'cache/boq_uploads/';$stored=bin2hex(random_bytes(16)).'.'.$ext;$path=$dir.$stored;if(!move_uploaded_file($f['tmp_name'],$path))throw new RuntimeException('Unable to store the uploaded file.');return array('original'=>$original,'stored'=>$stored,'path'=>$path,'sha256'=>hash_file('sha256',$path));}
	private function remove_boq_uploads(array$stored_files){$dir=realpath(APPPATH.'cache/boq_uploads');if($dir===FALSE)return;foreach($stored_files as$file){$name=(string)$file;if($name===''||basename($name)!==$name)continue;$path=$dir.DIRECTORY_SEPARATOR.$name;if(is_file($path)&&!unlink($path))log_message('error','Unable to remove deleted BOQ upload: '.$name);}}
	private function editable($boq){if(!in_array($boq->boq_status,array('DRAFT','VALIDATED'),TRUE))show_error('Only Draft or Validated BOQs can be changed.',409,'BOQ Locked');}
	private function boq_or_404($id){$b=$this->Boq_model->find($id);if(!$b)show_404();return$b;}
	private function item_or_404($boq,$id){$i=$this->Boq_model->item($boq,$id);if(!$i)show_404();return$i;}
	private function null_post($key){$v=trim((string)$this->input->post($key,TRUE));return$v===''?NULL:$v;}
	private function number_post($key){$v=trim((string)$this->input->post($key,TRUE));return$v===''?NULL:$v;}
	private function success($message,$route){$this->session->set_flashdata('boq_success',$message);redirect($route);}
}
