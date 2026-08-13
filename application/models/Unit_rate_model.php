<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Unit_rate_model extends CI_Model
{
	public function all_current()
	{
		$sql = "SELECT i.cost_item_id, i.cost_item_uid, r.cost_item_revision_id, r.enterprise_cost_code, r.standard_item_name, r.revision_no, r.revision_status, r.coding_status, u.uom_code,
			COALESCE(mat.material_cost,0) material_cost, COALESCE(lab.labor_cost,0) labor_cost,
			COALESCE(a.tools_equipment_cost,0) tools_equipment_cost, COALESCE(a.other_consumables_cost,0) other_consumables_cost, COALESCE(a.non_material_activity_input,0) non_material_activity_input,
			COALESCE(mat.material_cost,0)+COALESCE(lab.labor_cost,0)+COALESCE(a.tools_equipment_cost,0)+COALESCE(a.other_consumables_cost,0)+COALESCE(a.non_material_activity_input,0) final_unit_rate,
			b.unit_rate reference_baseline_unit_rate, b.currency_code baseline_currency, b.unit_rate-(COALESCE(mat.material_cost,0)+COALESCE(lab.labor_cost,0)+COALESCE(a.tools_equipment_cost,0)+COALESCE(a.other_consumables_cost,0)+COALESCE(a.non_material_activity_input,0)) reconciliation_variance,
			COALESCE(mat.missing_rates,0)+COALESCE(lab.missing_rates,0) missing_rate_count, COALESCE(eq.equipment_count,0) equipment_count
		FROM standard_cost_item i JOIN standard_cost_item_revision r ON r.cost_item_id=i.cost_item_id AND r.is_current=1
		LEFT JOIN ref_uom u ON u.uom_id=r.uom_id
		LEFT JOIN (SELECT cm.cost_item_revision_id,SUM(cm.quantity_per_item_unit*rh.unit_rate) material_cost,SUM(rh.unit_rate IS NULL) missing_rates FROM cost_item_material cm LEFT JOIN material_rate_history rh ON rh.material_variant_id=cm.material_variant_id AND rh.is_current=1 GROUP BY cm.cost_item_revision_id) mat ON mat.cost_item_revision_id=r.cost_item_revision_id
		LEFT JOIN (SELECT cl.cost_item_revision_id,SUM(cl.labor_days_per_item_unit*rh.total_with_admin_fee) labor_cost,SUM(rh.total_with_admin_fee IS NULL) missing_rates FROM cost_item_labor cl LEFT JOIN labor_rate_history rh ON rh.labor_id=cl.labor_id AND rh.is_current=1 GROUP BY cl.cost_item_revision_id) lab ON lab.cost_item_revision_id=r.cost_item_revision_id
		LEFT JOIN (SELECT ca.cost_item_revision_id,SUM(CASE WHEN t.allowance_type_code='TOOLS_EQUIPMENT' THEN ca.amount_per_item_unit ELSE 0 END) tools_equipment_cost,SUM(CASE WHEN t.allowance_type_code='OTHER_CONSUMABLES' THEN ca.amount_per_item_unit ELSE 0 END) other_consumables_cost,SUM(CASE WHEN t.allowance_type_code='NON_MATERIAL_ACTIVITY_INPUT' THEN ca.amount_per_item_unit ELSE 0 END) non_material_activity_input FROM cost_item_resource_allowance ca JOIN ref_resource_allowance_type t ON t.resource_allowance_type_id=ca.resource_allowance_type_id GROUP BY ca.cost_item_revision_id) a ON a.cost_item_revision_id=r.cost_item_revision_id
		LEFT JOIN (SELECT cost_item_revision_id,COUNT(*) equipment_count FROM cost_item_equipment GROUP BY cost_item_revision_id) eq ON eq.cost_item_revision_id=r.cost_item_revision_id
		LEFT JOIN (SELECT cost_item_revision_id,MAX(unit_rate) unit_rate,MAX(currency_code) currency_code FROM cost_item_rate_history WHERE rate_type='REFERENCE_BASELINE' AND rate_date IS NULL GROUP BY cost_item_revision_id) b ON b.cost_item_revision_id=r.cost_item_revision_id
		ORDER BY r.enterprise_cost_code";
		return $this->db->query($sql)->result();
	}

	public function find_current($cost_item_id)
	{
		$row = $this->db->select('i.cost_item_id,i.cost_item_uid,i.lifecycle_status,r.cost_item_revision_id,r.enterprise_cost_code,r.standard_item_name,r.standard_description,r.revision_no,r.revision_status,r.coding_status,u.uom_code')
			->from('standard_cost_item i')->join('standard_cost_item_revision r','r.cost_item_id=i.cost_item_id AND r.is_current=1')->join('ref_uom u','u.uom_id=r.uom_id','left')->where('i.cost_item_id',(int)$cost_item_id)->get()->row();
		return $row?:NULL;
	}

	public function material_components($revision_id)
	{
		return $this->db->select('cm.*,m.material_code,m.material_name,v.material_variant_code,v.size_description,u.uom_code,rh.unit_rate,rs.currency_code,(cm.quantity_per_item_unit*rh.unit_rate) component_amount',FALSE)->from('cost_item_material cm')->join('material_master m','m.material_id=cm.material_id')->join('material_variant v','v.material_variant_id=cm.material_variant_id','left')->join('ref_uom u','u.uom_id=cm.uom_id','left')->join('material_rate_history rh','rh.material_variant_id=cm.material_variant_id AND rh.is_current=1','left')->join('material_rate_schedule rs','rs.material_rate_schedule_id=rh.material_rate_schedule_id','left')->where('cm.cost_item_revision_id',(int)$revision_id)->order_by('cm.is_primary','DESC')->get()->result();
	}

	public function labor_components($revision_id)
	{
		return $this->db->select('cl.*,l.labor_code,l.labor_name,rh.total_with_admin_fee,rs.currency_code,(cl.labor_days_per_item_unit*rh.total_with_admin_fee) component_amount',FALSE)->from('cost_item_labor cl')->join('labor_master l','l.labor_id=cl.labor_id')->join('labor_rate_history rh','rh.labor_id=cl.labor_id AND rh.is_current=1','left')->join('labor_rate_schedule rs','rs.labor_rate_schedule_id=rh.labor_rate_schedule_id','left')->where('cl.cost_item_revision_id',(int)$revision_id)->order_by('l.labor_code')->get()->result();
	}

	public function equipment_components($revision_id)
	{
		return $this->db->select('ce.*,e.equipment_code,e.equipment_name')->from('cost_item_equipment ce')->join('equipment_master e','e.equipment_id=ce.equipment_id')->where('ce.cost_item_revision_id',(int)$revision_id)->order_by('e.equipment_code')->get()->result();
	}

	public function allowance_components($revision_id)
	{
		return $this->db->select('a.*,t.allowance_type_code,t.allowance_type_name')->from('cost_item_resource_allowance a')->join('ref_resource_allowance_type t','t.resource_allowance_type_id=a.resource_allowance_type_id')->where('a.cost_item_revision_id',(int)$revision_id)->order_by('t.allowance_type_code')->get()->result();
	}

	public function baseline($revision_id)
	{
		$row=$this->db->where('cost_item_revision_id',(int)$revision_id)->where('rate_type','REFERENCE_BASELINE')->where('rate_date',NULL)->order_by('unit_rate','DESC')->limit(1)->get('cost_item_rate_history')->row();
		return $row?:NULL;
	}

	public function find_component($type,$revision_id,$id)
	{
		$config=$this->component_config($type); if(!$config)return NULL;
		$row=$this->db->where('cost_item_revision_id',(int)$revision_id)->where($config['key'],(int)$id)->get($config['table'])->row(); return $row?:NULL;
	}

	public function duplicate_component($type,$revision_id,$foreign_id,$exclude_id=NULL)
	{
		$config=$this->component_config($type); if(!$config)return TRUE;
		$this->db->where('cost_item_revision_id',(int)$revision_id)->where($config['foreign'],$foreign_id);
		if($exclude_id!==NULL)$this->db->where($config['key'].' !=',(int)$exclude_id);
		return $this->db->count_all_results($config['table'])>0;
	}

	public function save_component($type,array $data,$id=NULL)
	{
		$config=$this->component_config($type); if(!$config)return FALSE;
		if($id===NULL)return $this->db->insert($config['table'],$data)?(int)$this->db->insert_id():0;
		return $this->db->where($config['key'],(int)$id)->update($config['table'],$data);
	}

	public function material_variants()
	{
		return $this->db->select("v.material_variant_id option_id,CONCAT(m.material_code,' — ',m.material_name,' / ',v.material_variant_code,IF(v.size_description IS NULL,'',CONCAT(' ',v.size_description))) option_label,v.material_id,v.uom_id",FALSE)->from('material_variant v')->join('material_master m','m.material_id=v.material_id')->order_by('m.material_code')->get()->result();
	}

	public function simple_options($table,$key,$label)
	{
		return $this->db->select($key.' option_id,'.$label.' option_label',FALSE)->order_by('option_label')->get($table)->result();
	}

	public function material_variant($id){$row=$this->db->where('material_variant_id',(int)$id)->get('material_variant')->row();return $row?:NULL;}
	public function reference_exists($table,$key,$id){return $this->db->where($key,(int)$id)->count_all_results($table)>0;}

	private function component_config($type)
	{
		$map=array('material'=>array('table'=>'cost_item_material','key'=>'cost_item_material_id','foreign'=>'material_id'),'labor'=>array('table'=>'cost_item_labor','key'=>'cost_item_labor_id','foreign'=>'labor_id'),'equipment'=>array('table'=>'cost_item_equipment','key'=>'cost_item_equipment_id','foreign'=>'equipment_id'),'allowance'=>array('table'=>'cost_item_resource_allowance','key'=>'cost_item_resource_allowance_id','foreign'=>'resource_allowance_type_id'));
		return $map[$type]??NULL;
	}
}
