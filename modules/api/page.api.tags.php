<?php
function api_tags($self,$vid=NULL) {
	if (!debug('process')) sendHeader('text/xml');

	$input = unescape($_GET['input']);
	
	$len = strlen($input);
	if (strpos($input,',')) {
		$pre=substr($input,0,strrpos($input,','));
		$input=trim(substr($input,strrpos($input,',')+1));
	}
	
	if ($len>0) {
		$tags=mydb::select('SELECT tid,name FROM %tag% WHERE `vid`=:vid AND `name` LIKE :name ORDER BY `name` ASC',':vid',$vid,':name',$input.'%');
	}
	
	$ret='<?xml version="1.0" encoding="utf-8" ?><results>';
	foreach ($tags->items as $tag) {
		$ret.='<rs id="'.$tag->tid.'" info="">'.($pre?$pre.' , ':'').$tag->name.'</rs>';
	}
	//	$ret.='<rs id="99" info="Input text">'.$_GET['input'].' from '.$vid.'</rs>';
	//	$ret.='<rs id="98" info="Input text">'.$input.'</rs>';
	//	$ret.='<rs id="97" info="Query">'.htmlspecialchars($tags->_query).'</rs>';
		$ret.='</results>';
	die($ret);
}
?>