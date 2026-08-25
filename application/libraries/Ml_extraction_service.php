<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ml_extraction_service
{
	private $ci;
	private $fields=array('item_reference','section_reference','item_description','source_uom_text','quantity_text','unit_rate_text','line_amount_text','notes');

	public function __construct()
	{
		$this->ci=&get_instance();
		$this->ci->config->load('ml',TRUE);
	}

	public function predict($batch,array $rows,$model)
	{
		if(!$model)return$this->fallback('No active extraction model is registered.');
		$url=trim((string)$this->ci->config->item('service_url','ml'));
		if($url==='')return$this->fallback('The private ML inference service is not configured.');
		$payload=array('schema_version'=>'extraction-v1','model'=>array('code'=>$model->model_code,'version'=>$model->version_tag),'batch'=>array('id'=>(int)$batch->boq_import_batch_id,'file_type'=>$batch->file_type,'source_sheet'=>$batch->source_sheet),'rows'=>array());
		foreach($rows as$row){$item=array('source_row_no'=>(int)$row->source_row_no);foreach($this->fields as$field)$item[$field]=$row->$field;$payload['rows'][]=$item;}
		$json=json_encode($payload,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);$started=microtime(TRUE);$ch=curl_init($url);$headers=array('Content-Type: application/json','Accept: application/json');$token=trim((string)$this->ci->config->item('service_token','ml'));if($token!=='')$headers[]='Authorization: Bearer '.$token;
		curl_setopt_array($ch,array(CURLOPT_POST=>TRUE,CURLOPT_POSTFIELDS=>$json,CURLOPT_HTTPHEADER=>$headers,CURLOPT_RETURNTRANSFER=>TRUE,CURLOPT_CONNECTTIMEOUT_MS=>(int)$this->ci->config->item('connect_timeout_ms','ml'),CURLOPT_TIMEOUT_MS=>(int)$this->ci->config->item('inference_timeout_ms','ml'),CURLOPT_SSL_VERIFYPEER=>TRUE,CURLOPT_SSL_VERIFYHOST=>2));$body=curl_exec($ch);$error=curl_error($ch);$status=(int)curl_getinfo($ch,CURLINFO_RESPONSE_CODE);curl_close($ch);$latency=(int)round((microtime(TRUE)-$started)*1000);
		if($body===FALSE||$status<200||$status>=300)return$this->fallback('ML inference was unavailable; deterministic extraction was retained.'.($error!==''?' '.$error:''),$latency,hash('sha256',$json));
		$decoded=json_decode($body,TRUE);if(!is_array($decoded)||($decoded['schema_version']??'')!=='extraction-v1'||!isset($decoded['predictions'])||!is_array($decoded['predictions']))return$this->fallback('ML inference returned an invalid response; deterministic extraction was retained.',$latency,hash('sha256',$json));
		$allowed_rows=array();foreach($rows as$row)$allowed_rows[(int)$row->source_row_no]=TRUE;$predictions=array();$seen=array();foreach($decoded['predictions']as$prediction){$source=(int)($prediction['source_row_no']??0);if(!$source||!isset($allowed_rows[$source])||isset($seen[$source])||!is_array($prediction['fields']??NULL))continue;$fields=array();foreach($this->fields as$field)if(array_key_exists($field,$prediction['fields'])&&(is_scalar($prediction['fields'][$field])||$prediction['fields'][$field]===NULL))$fields[$field]=$prediction['fields'][$field]===NULL?NULL:trim((string)$prediction['fields'][$field]);if(!$fields)continue;$confidence=array();foreach((array)($prediction['confidence']??array())as$field=>$score)if(in_array($field,$this->fields,TRUE)&&is_numeric($score)&&$score>=0&&$score<=1)$confidence[$field]=(float)$score;$predictions[]=array('source_row_no'=>$source,'fields'=>$fields,'confidence'=>$confidence?:NULL);$seen[$source]=TRUE;}
		if(!$predictions)return$this->fallback('ML inference produced no valid proposals; deterministic extraction was retained.',$latency,hash('sha256',$json));
		return array('method'=>'ML_SERVICE','predictions'=>$predictions,'latency_ms'=>$latency,'request_sha256'=>hash('sha256',$json),'fallback_reason'=>NULL);
	}

	private function fallback($reason,$latency=NULL,$sha=NULL){return array('method'=>'DETERMINISTIC_FALLBACK','predictions'=>array(),'latency_ms'=>$latency,'request_sha256'=>$sha,'fallback_reason'=>substr($reason,0,1000));}
}
