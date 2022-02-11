<?php
/**
* Project school
*
* @param Object $self
* @return String
*/
function project_school($self) {
	$self->theme->title='โรงเรียน';

	$stmt='SELECT * FROM %db_org% WHERE `sector`=9 ORDER BY CONVERT(`name` USING tis620) ASC';
	$dbs=mydb::select($stmt);

	$tables = new Table();
	$tables->thead=array('ชื่อโรงเรียน','ที่อยู่');
	foreach ($dbs->items as $rs) {
		$tables->rows[]=array($rs->name,$rs->address);
	}
	$ret.=$tables->build();

	//$ret.=print_o($dbs,'$dbs');
	return $ret;
}
?>