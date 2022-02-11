<?php
function flood_api_lastphoto($self,$camid) {
	sendheader('text/html');
	$q=SG\getFirst($q,trim($_GET['q'],$_POST['q']));
	$n=intval(SG\getFirst($item,$_GET['n'],$_POST['n'],20));
	$p=intval(SG\getFirst($p,$_GET['p'],$_POST['p'],1));
	//		if (empty($q)) return '[]';

	if (is_numeric(substr($camid,0,1))) {
		mydb::where('`camid` IN (:camid)',':camid','SET:'.$camid);
	} else {
		mydb::where('`name` IN (:camid)',':camid','SET-STRING:'.$camid);
	}
	$stmt='SELECT
					  c.`camid`
					, c.`name`
					, c.`last_photo` `photo`
					, c.`last_photo`
					, c.`last_updated`
					, c.`last_updated` `atdate`
					FROM %flood_cam% c
					%WHERE%
					';
	$dbs=mydb::select($stmt);
	//debugMsg($dbs,'$dbs');

	$result=array();
	foreach ($dbs->items as $rs) {
		$result[] = array(
										'camid'=>$rs->name,
										'photo'=>htmlspecialchars(flood_model::photo_url($rs)),
										'thumb'=>htmlspecialchars(flood_model::thumb_url($rs)),
										'created'=>sg_date($rs->last_updated,'ว ดด ปป H:i น.')
										);
	}
	//debugMsg($result,'$result');
	if (debug('api')) {
		$result[]=array('value'=>'query','label'=>$dbs->_query);
		$result[]=array('value'=>'num_rows','label'=>'Result is '.$dbs->_num_rows.' row(s).');
	}
	return json_encode($result);
}
?>