<?php
function project_money_home($self,$projectInfo) {
	$tpid=$projectInfo->tpid;

	$stmt='SELECT * FROM %org_doings% WHERE `tpid`=:tpid';

	$dbs=mydb::select($stmt,':tpid',$tpid);

	$tables = new Table();
	$tables->thead=array('date'=>'วันที่','กิจกรรม');
	foreach ($dbs->items as $rs) {
		$tables->rows[]=array(sg_date($rs->atdate,'ว ดด ปปปป'),'<a href="'.url('project/money/'.$tpid.'/dopaid/'.$rs->doid).'">'.$rs->doings.'</a>');
	}

	$ret.=$tables->build();

	//$ret.=print_o($dbs);

	//$ret.=print_o($projectInfo,'$projectInfo');
	return $ret;
}
?>