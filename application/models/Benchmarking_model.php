<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Benchmarking_model extends CI_Model
{
	private $group_dimensions=array(
		'cost_item'=>array('label'=>'Cost Item','key'=>'r.cost_item_id','name'=>"CONCAT(COALESCE(r.enterprise_cost_code,i.cost_item_uid),' — ',r.standard_item_name)"),
		'project'=>array('label'=>'Project','key'=>'rh.project_id','name'=>"COALESCE(CONCAT(p.project_code,' — ',p.project_name),'Not recorded')"),
		'location'=>array('label'=>'Location','key'=>'COALESCE(rh.location_id,p.location_id)','name'=>"COALESCE(l.location_name,rh.city,p.city,'Not recorded')"),
		'period'=>array('label'=>'Period','key'=>'COALESCE(pp.price_period_id,YEAR(rh.rate_date),0)','name'=>"COALESCE(pp.period_label,CAST(YEAR(rh.rate_date) AS CHAR),'Not recorded')"),
		'contractor'=>array('label'=>'Contractor / Vendor','key'=>"COALESCE(NULLIF(rh.contractor_supplier,''),'Not recorded')",'name'=>"COALESCE(NULLIF(rh.contractor_supplier,''),'Not recorded')"),
		'division'=>array('label'=>'CSI Division','key'=>'r.division_id','name'=>"COALESCE(CONCAT(d.division_code,' — ',d.division_name),'Not recorded')"),
		'trade'=>array('label'=>'Trade','key'=>'r.trade_id','name'=>"COALESCE(CONCAT(t.trade_code,' — ',t.trade_name),'Not recorded')"),
	);

	public function dimensions(){return$this->group_dimensions;}

	public function benchmark(array$filters,$group_by)
	{
		$rows=$this->observation_query($filters)->order_by('rh.unit_rate')->get()->result();$groups=array();$dimension=$this->group_dimensions[$group_by]??$this->group_dimensions['division'];
		foreach($rows as$row){$key=(string)$row->group_key.'|'.(string)$row->uom_code.'|'.(string)$row->currency_code;if(!isset($groups[$key]))$groups[$key]=array('label'=>$row->group_name,'uom'=>$row->uom_code?:'Not recorded','currency'=>$row->currency_code?:'Not recorded','values'=>array(),'projects'=>array(),'locations'=>array(),'contractors'=>array(),'first_date'=>NULL,'last_date'=>NULL);$group=&$groups[$key];$group['values'][]=(float)$row->unit_rate;if($row->project_id)$group['projects'][$row->project_id]=TRUE;if($row->effective_location_id)$group['locations'][$row->effective_location_id]=TRUE;if($row->contractor_supplier)$group['contractors'][$row->contractor_supplier]=TRUE;if($row->rate_date){if($group['first_date']===NULL||$row->rate_date<$group['first_date'])$group['first_date']=$row->rate_date;if($group['last_date']===NULL||$row->rate_date>$group['last_date'])$group['last_date']=$row->rate_date;}unset($group);}
		$result=array();foreach($groups as$group){sort($group['values'],SORT_NUMERIC);$count=count($group['values']);$group['count']=$count;$group['minimum']=$count?$group['values'][0]:NULL;$group['maximum']=$count?$group['values'][$count-1]:NULL;$group['average']=$count?array_sum($group['values'])/$count:NULL;$group['median']=$this->percentile($group['values'],0.5);$group['p25']=$this->percentile($group['values'],0.25);$group['p75']=$this->percentile($group['values'],0.75);$group['project_count']=count($group['projects']);$group['location_count']=count($group['locations']);$group['contractor_count']=count($group['contractors']);unset($group['values'],$group['projects'],$group['locations'],$group['contractors']);$result[]=(object)$group;}
		usort($result,static function($a,$b){return$b->count<=>$a->count?:strcmp($a->label,$b->label);});return array('rows'=>$rows,'groups'=>$result,'summary'=>$this->summary($rows),'dimension'=>$dimension);
	}

	private function observation_query(array$filters)
	{
		$dimension=$this->group_dimensions[$filters['group_by']]??$this->group_dimensions['division'];$this->db->select("rh.*,r.cost_item_id,r.enterprise_cost_code,r.standard_item_name,r.revision_no,r.revision_status,i.cost_item_uid,d.division_code,d.division_name,t.trade_code,t.trade_name,u.uom_code,p.project_code,p.project_name,COALESCE(rh.location_id,p.location_id) AS effective_location_id,COALESCE(l.location_name,rh.city,p.city) AS location_name,pp.period_label,{$dimension['key']} AS group_key,{$dimension['name']} AS group_name",FALSE)
			->from('cost_item_rate_history rh')->join('standard_cost_item_revision r','r.cost_item_revision_id=rh.cost_item_revision_id')->join('standard_cost_item i','i.cost_item_id=r.cost_item_id')->join('ref_csi_division d','d.division_id=r.division_id','left')->join('ref_trade t','t.trade_id=r.trade_id','left')->join('ref_uom u','u.uom_id=r.uom_id','left')->join('project_master p','p.project_id=rh.project_id','left')->join('ref_location l','l.location_id=COALESCE(rh.location_id,p.location_id)','left')->join('ref_price_period pp','pp.price_period_id=rh.price_period_id','left')->where('rh.unit_rate IS NOT NULL',NULL,FALSE);
		foreach(array('cost_item_id'=>'r.cost_item_id','project_id'=>'rh.project_id','location_id'=>'COALESCE(rh.location_id,p.location_id)','division_id'=>'r.division_id','trade_id'=>'r.trade_id','uom_id'=>'r.uom_id')as$key=>$column)if($filters[$key]!==NULL)$this->db->where($column,$filters[$key],FALSE);
		if($filters['contractor']!=='')$this->db->where('rh.contractor_supplier',$filters['contractor']);if($filters['date_from']!=='')$this->db->where('rh.rate_date >=',$filters['date_from']);if($filters['date_to']!=='')$this->db->where('rh.rate_date <=',$filters['date_to']);if($filters['validated_only'])$this->db->group_start()->where('rh.is_validated',1)->or_where('rh.validation_status','VALID')->group_end();return$this->db;
	}

	private function summary(array$rows)
	{
		$values=array();$projects=array();$locations=array();$contractors=array();$currencies=array();$uoms=array();$dated=0;$validated=0;foreach($rows as$row){$values[]=(float)$row->unit_rate;if($row->project_id)$projects[$row->project_id]=TRUE;if($row->effective_location_id)$locations[$row->effective_location_id]=TRUE;if($row->contractor_supplier)$contractors[$row->contractor_supplier]=TRUE;if($row->currency_code)$currencies[$row->currency_code]=TRUE;if($row->uom_code)$uoms[$row->uom_code]=TRUE;if($row->rate_date)$dated++;if($row->is_validated||$row->validation_status==='VALID')$validated++;}sort($values,SORT_NUMERIC);$count=count($values);return(object)array('count'=>$count,'minimum'=>$count?$values[0]:NULL,'maximum'=>$count?$values[$count-1]:NULL,'average'=>$count?array_sum($values)/$count:NULL,'median'=>$this->percentile($values,.5),'p25'=>$this->percentile($values,.25),'p75'=>$this->percentile($values,.75),'project_count'=>count($projects),'location_count'=>count($locations),'contractor_count'=>count($contractors),'currency_count'=>count($currencies),'uom_count'=>count($uoms),'dated_count'=>$dated,'validated_count'=>$validated);
	}

	private function percentile(array$values,$percentile){$count=count($values);if(!$count)return NULL;if($count===1)return$values[0];$position=($count-1)*$percentile;$lower=(int)floor($position);$upper=(int)ceil($position);if($lower===$upper)return$values[$lower];$weight=$position-$lower;return$values[$lower]+(($values[$upper]-$values[$lower])*$weight);}

	public function filter_options()
	{
		$locations=$this->db->select("l.location_id AS option_id,CONCAT(l.psgc_code,' — ',l.location_name) AS option_label",FALSE)->from('ref_location l')->join('cost_item_rate_history rh','rh.location_id=l.location_id')->distinct()->order_by('option_label')->get()->result();
		return array('cost_items'=>$this->options('standard_cost_item_revision r','r.cost_item_id',"CONCAT(COALESCE(r.enterprise_cost_code,i.cost_item_uid),' — ',r.standard_item_name)",array('standard_cost_item i'=>'i.cost_item_id=r.cost_item_id'),'r.is_current=1'),'projects'=>$this->options('project_master','project_id',"CONCAT(project_code,' — ',project_name)"),'locations'=>$locations,'divisions'=>$this->options('ref_csi_division','division_id',"CONCAT(division_code,' — ',division_name)"),'trades'=>$this->options('ref_trade','trade_id',"CONCAT(trade_code,' — ',trade_name)"),'uoms'=>$this->options('ref_uom','uom_id',"CONCAT(uom_code,' — ',uom_name)"),'contractors'=>$this->db->select('contractor_supplier AS option_id,contractor_supplier AS option_label')->where('contractor_supplier IS NOT NULL',NULL,FALSE)->where("contractor_supplier != ''",NULL,FALSE)->distinct()->order_by('contractor_supplier')->get('cost_item_rate_history')->result());
	}

	private function options($table,$key,$label,array$joins=array(),$where=NULL){$query=$this->db->select($key.' AS option_id,'.$label.' AS option_label',FALSE)->from($table);foreach($joins as$join=>$on)$query->join($join,$on);if($where)$query->where($where,NULL,FALSE);return$query->order_by('option_label')->get()->result();}
}
