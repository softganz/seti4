<?php
/**
* API     :: Tags API
* Created :: 2022-11-19
* Modify  :: 2022-11-19
* Version :: 2
*
* @return Array
*
* @usage api/tags/{vid}?input=tagString
*/

class TagsApi extends PageApi {
	var $vid;
	var $input;

	function __construct($vid = NULL) {
		parent::__construct([
			'vid' => $vid,
			'input' => post('input'),
		]);
	}

	function build() {
		if (!debug('process')) sendHeader('text/xml');
		$len = strlen($this->input);
		if (strpos($this->input,',')) {
			$pre=substr($this->input,0,strrpos($this->input,','));
			$this->input = trim(substr($this->input,strrpos($this->input,',')+1));
		}

		if ($len>0) {
			$tags=mydb::select('SELECT tid,name FROM %tag% WHERE `vid`=:vid AND `name` LIKE :name ORDER BY `name` ASC',':vid',$this->vid,':name',$this->input.'%');
		}

		$ret='<?xml version="1.0" encoding="utf-8" ?><results>';
		foreach ($tags->items as $tag) {
			$ret.='<rs id="'.$tag->tid.'" info="">'.($pre?$pre.' , ':'').$tag->name.'</rs>';
		}
		//	$ret.='<rs id="99" info="Input text">'.$_GET['input'].' from '.$this->vid.'</rs>';
		//	$ret.='<rs id="98" info="Input text">'.$this->.'</rs>';
		//	$ret.='<rs id="97" info="Query">'.htmlspecialchars($tags->_query).'</rs>';
			$ret.='</results>';
		die($ret);
	}
}
?>