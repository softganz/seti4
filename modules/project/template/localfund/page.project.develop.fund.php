<?php
function project_develop_fund($self) {
	R::View('project.toolbar',$self,'รายชื่อกองทุนตำบล','develop');

	$stmt='SELECT * FROM %project_fund% ';
	$dbs=mydb::select($stmt);

	$tables = new Table();
	$tables->thead=array('เขต','จังหวัด','อำเภอ','ชื่อกองทุน');
	foreach ($dbs->items as $rs) {
		$tables->rows[]=array($rs->nameareaid,$rs->namechangwat,$rs->nameampur,$rs->fundname);
	}
	$ret.=$tables->build();
	//$ret.=print_o($dbs,'$dbs');
	return $ret;
}
?>