<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Boq_import_parser
{
	public function parse($path,$extension)
	{
		$extension=strtolower($extension);
		if($extension==='csv')return array('sheet'=>'','rows'=>$this->csv($path));
		if($extension==='xlsx')return $this->xlsx($path);
		throw new RuntimeException('Only CSV and XLSX files are supported.');
	}

	private function csv($path)
	{
		$handle=fopen($path,'rb');if(!$handle)throw new RuntimeException('Unable to read the CSV file.');$rows=array();
		while(($row=fgetcsv($handle))!==FALSE){if(isset($row[0]))$row[0]=preg_replace('/^\xEF\xBB\xBF/','',$row[0]);$rows[]=$row;if(count($rows)>10001){fclose($handle);throw new RuntimeException('The file exceeds the 10,000-row limit.');}}
		fclose($handle);return$rows;
	}

	private function xlsx($path)
	{
		$zip=new ZipArchive();if($zip->open($path)!==TRUE)throw new RuntimeException('Unable to open the XLSX workbook.');
		$expanded=0;for($i=0;$i<$zip->numFiles;$i++){$stat=$zip->statIndex($i);$expanded+=(int)($stat['size']??0);if($expanded>25*1024*1024){$zip->close();throw new RuntimeException('The expanded workbook exceeds the 25 MB safety limit.');}}
		$shared=array();$xml=$zip->getFromName('xl/sharedStrings.xml');if($xml!==FALSE){$doc=$this->xml($xml);foreach($doc->si as$si)$shared[]=$this->rich_text($si);}
		$workbook=$zip->getFromName('xl/workbook.xml');$relationships=$zip->getFromName('xl/_rels/workbook.xml.rels');if($workbook===FALSE||$relationships===FALSE){$zip->close();throw new RuntimeException('The workbook structure is incomplete.');}
		$rels=array();$rel_doc=$this->xml($relationships);foreach($rel_doc->Relationship as$rel)$rels[(string)$rel['Id']]=(string)$rel['Target'];
		$wb=$this->xml($workbook);$wb->registerXPathNamespace('r','http://schemas.openxmlformats.org/officeDocument/2006/relationships');$best=NULL;$fallback=NULL;
		foreach($wb->sheets->sheet as$sheet){$attributes=$sheet->attributes('r',TRUE);$target=$rels[(string)$attributes['id']]??'';$target=preg_replace('#^/?xl/#','',$target);$part='xl/'.ltrim($target,'/');$sheet_xml=$zip->getFromName($part);if($sheet_xml===FALSE)continue;$rows=$this->worksheet_rows($sheet_xml,$shared);if($fallback===NULL)$fallback=array('sheet'=>(string)$sheet['name'],'rows'=>$rows);$normalized=$this->normalize_boq_sheet($rows,(string)$sheet['name']);if($normalized!==NULL&&($best===NULL||$normalized['score']>$best['score']))$best=$normalized;
		}
		$zip->close();if($best!==NULL){unset($best['score']);return$best;}if($fallback!==NULL)return$fallback;throw new RuntimeException('The workbook has no readable worksheet.');
	}

	private function worksheet_rows($sheet_xml,array$shared)
	{
		$doc=$this->xml($sheet_xml);$rows=array();foreach($doc->sheetData->row as$row){$values=array();foreach($row->c as$c){$ref=(string)$c['r'];preg_match('/^[A-Z]+/i',$ref,$m);$index=$this->column_index($m[0]??'A');$type=(string)$c['t'];$value=(string)$c->v;if($type==='s')$value=$shared[(int)$value]??'';elseif($type==='inlineStr')$value=$this->rich_text($c->is);elseif($type==='b')$value=$value==='1'?'1':'0';$values[$index]=$this->clean_text($value);}if($values){$max=max(array_keys($values));$dense=array_fill(0,$max+1,'');foreach($values as$i=>$value)$dense[$i]=$value;$rows[]=array('source_row'=>(int)$row['r'],'values'=>$dense);}if(count($rows)>10001)throw new RuntimeException('The file exceeds the 10,000-row limit.');}return$rows;
	}

	private function xml($xml)
	{
		if (stripos($xml, '<!DOCTYPE') !== FALSE || stripos($xml, '<!ENTITY') !== FALSE) throw new RuntimeException('The workbook contains unsupported XML declarations.');
		$previous = libxml_use_internal_errors(TRUE);
		$document = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NONET | LIBXML_COMPACT);
		libxml_clear_errors();
		libxml_use_internal_errors($previous);
		if ($document === FALSE) throw new RuntimeException('The workbook contains invalid XML.');
		return $document;
	}

	private function normalize_boq_sheet(array$source_rows,$sheet_name)
	{
		$header_position=NULL;$columns=array();foreach(array_slice($source_rows,0,60)as$position=>$row){$labels=array();foreach($row['values']as$index=>$value){$label=$this->label($value);if($label!=='')$labels[$index]=$label;}$description=$this->find_label($labels,'DESCRIPTION');$unit=$this->find_label($labels,'UNIT');$quantity=$this->find_label($labels,'QUANTITY');$amount=$this->find_label($labels,'AMOUNT',TRUE);if($description!==NULL&&$unit!==NULL&&$quantity!==NULL&&$amount!==NULL){$header_position=$position;$columns=array('description'=>$description,'unit'=>$unit,'quantity'=>$quantity,'amount'=>$amount,'item'=>$this->find_label($labels,'ITEM'),'unit_rate'=>$this->find_label($labels,'UNIT RATE'));break;}}
		if($header_position===NULL)return NULL;$normalized=array(array('item_reference','section_reference','item_description','source_uom_text','quantity','unit_rate','line_amount','notes','source_row_no'));$section_stack=array();$parsed_total=0.0;$unpriced=0;$declared_total=NULL;
		foreach(array_slice($source_rows,$header_position+1)as$row){$values=$row['values'];$description_values=array();for($i=$columns['description'];$i<$columns['unit'];$i++){if(trim((string)($values[$i]??''))!=='')$description_values[$i]=trim((string)$values[$i]);}$description=$description_values?end($description_values):'';$description_column=$description_values?array_key_last($description_values):$columns['description'];$uom=$this->normalize_source_uom($values[$columns['unit']]??'');$quantity=$this->number($values[$columns['quantity']]??'');$amount_raw=trim((string)($values[$columns['amount']]??''));$amount=$this->number($amount_raw);$is_total=$description!==''&&preg_match('/\b(SUB\s*-?\s*TOTAL|GRAND\s+TOTAL|TOTAL(?:\s+[A-Z]+)*\s+(?:AMOUNT|COST)|DIRECT\s+COST)\b/i',$description);
			if($is_total&&$amount!==NULL)$declared_total=$amount;if($description===''||$uom===''||$quantity===NULL||$is_total){if($description!==''&&!$is_total)$this->update_section_stack($section_stack,$values,$columns,$description_values);continue;}
			$item_reference=$columns['item']!==NULL?trim((string)($values[$columns['item']]??'')):'';if($item_reference===''&&$columns['item']!==NULL&&$columns['item']>0){$previous=trim((string)($values[$columns['item']-1]??''));if($previous!==''&&preg_match('/^\d+(?:\.\d+)*$/',$previous))$item_reference=$previous;}
			$unit_rate=NULL;if($columns['unit_rate']!==NULL)$unit_rate=$this->number($values[$columns['unit_rate']]??'');if($amount!==NULL&&$quantity!=0)$unit_rate=$amount/$quantity;$notes='Source worksheet: '.$sheet_name.'; source row: '.$row['source_row'];if($amount===NULL&&$amount_raw!=='')$notes.='; source amount: '.$amount_raw;
			if($amount===NULL)$unpriced++;else$parsed_total+=round($amount,2);$normalized[]=array($this->limit($item_reference,100),$this->limit(implode(' > ',array_values(array_filter($section_stack,'strlen'))),255),$description,$this->limit($uom,50),$this->decimal($quantity),$unit_rate===NULL?'':$this->decimal($unit_rate),$amount===NULL?'':$this->decimal($amount),$notes,$row['source_row']);
		}
		$count=count($normalized)-1;$variance=$declared_total===NULL?NULL:$parsed_total-$declared_total;return$count<1?NULL:array('sheet'=>$sheet_name,'rows'=>$normalized,'score'=>$count,'profile'=>array('source_declared_total'=>$declared_total,'parsed_line_total'=>$parsed_total,'total_variance'=>$variance,'unpriced_rows'=>$unpriced,'normalization_notes'=>'Automatically selected and normalized the most detailed recognized BOQ worksheet. Formula cells use workbook-cached values; formulas are not evaluated.'));
	}

	private function update_section_stack(array&$stack,array$values,array$columns,array$description_values)
	{
		if(!$description_values)return;$column=array_key_last($description_values);$text=end($description_values);$item=$columns['item']!==NULL?trim((string)($values[$columns['item']]??'')):'';$level=max(0,$column-$columns['description']);if($column===$columns['description']&&$item!==''&&preg_match('/^(?:DIV\.?\s*)?[A-Z0-9]+(?:[.\-][A-Z0-9]+)*\.?$/i',$item)){$stack=array(trim($item.' — '.$text));return;}if($column===$columns['description']&&isset($stack[0]))$level=1;$stack[$level]=$text;foreach(array_keys($stack)as$key)if($key>$level)unset($stack[$key]);ksort($stack);
	}

	private function find_label(array$labels,$needle,$last=FALSE)
	{
		$found=NULL;foreach($labels as$index=>$label)if($label===$needle||strpos($label,$needle)===0){$found=$index;if(!$last)break;}return$found;
	}

	private function label($value){return strtoupper(trim(preg_replace('/\s+/',' ',(string)$value)));}
	private function number($value){$value=trim((string)$value);if($value==='')return NULL;$clean=str_replace(array(',',' '),'',$value);return is_numeric($clean)?(float)$clean:NULL;}
	private function decimal($value){return rtrim(rtrim(number_format((float)$value,6,'.',''),'0'),'.');}
	private function limit($value,$length){return function_exists('mb_substr')?mb_substr((string)$value,0,$length,'UTF-8'):substr((string)$value,0,$length);}
	private function normalize_source_uom($value){$value=$this->clean_text($value);$value=str_replace(array('²','³'),array('2','3'),$value);return trim($value);}
	private function clean_text($value){$value=str_replace(array("\xE2\xB2","\xE2\xB3","\xC2\xA0"),array('2','3',' '),(string)$value);if(!preg_match('//u',$value)&&function_exists('iconv'))$value=iconv('UTF-8','UTF-8//IGNORE',$value);return trim((string)$value);}

	private function rich_text($node){$text=(string)$node->t;if(isset($node->r))foreach($node->r as$r)$text.=(string)$r->t;return$text;}
	private function column_index($letters){$n=0;foreach(str_split(strtoupper($letters))as$c)$n=$n*26+(ord($c)-64);return$n-1;}
}
