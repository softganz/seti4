<?php
/**
* Get lastest photo
*
* @param Array $_GET
* @return json
*/
function flood_app_get($self,$camid) {
	sendheader('text/html');
	$q=SG\getFirst($q,trim($_GET['q'],$_POST['q']));
	$n=intval(SG\getFirst($item,$_GET['n'],$_POST['n'],20));
	$p=intval(SG\getFirst($p,$_GET['p'],$_POST['p'],1));
	//		if (empty($q)) return '[]';

	foreach (explode(',',$camid) as $camid) {

		//			$stmt='SELECT c.name, p.photo, p.created FROM %flood_photo% p LEFT JOIN %flood_cam% c USING(`camid`) WHERE `'.(is_numeric($camid)?'camid':'name').'`=:camid ORDER BY aid DESC LIMIT 1';
		//			echo $stmt;
		//			$rs=mydb::select($stmt,':camid',$camid);
		//			print_o($rs,'$rs',1);

		$rs=mydb::select('SELECT c.camid, c.name FROM %flood_cam% c WHERE `'.(is_numeric($camid)?'camid':'name').'`=:camid LIMIT 1',':camid',$camid);
		$stmt='SELECT p.photo, p.created FROM %flood_photo% p WHERE `camid`=:camid ORDER BY aid DESC LIMIT 1';
		$prs=mydb::select($stmt,':camid',$rs->camid);
		$rs->photo=$prs->photo;
		$rs->created=$prs->created;
		//			print_o($rs,'$rs',1);
		//			print_o($prs,'$prs',1);

		$dbs->items[]=(object)array('camid'=>$rs->name,'name'=>$rs->name,'photo'=>$rs->photo,'atdate'=>$rs->created);
	}
	//		print_o($dbs,'$dbs',1);

	$result=array();
	foreach ($dbs->items as $rs) {
		$result[] = array(
										'camid'=>$rs->camid,
										'photo'=>htmlspecialchars(flood_model::photo_url($rs)),
										'thumb'=>htmlspecialchars(flood_model::thumb_url($rs)),
										'created'=>sg_date($rs->atdate,'ว ดด ปปปป H:i:s')
										);
	}
	//		print_o($result,'$result',1);
	if (debug('api')) {
		$result[]=array('value'=>'query','label'=>$dbs->_query);
		$result[]=array('value'=>'num_rows','label'=>'Result is '.$dbs->_num_rows.' row(s).');
	}
	return json_encode($result);
}
?>