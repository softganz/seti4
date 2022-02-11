<?php
/**
* Project People Quatation Home
*
* @param Object $self
* @param Int $tpid
* @return String
*/

function project_qt_people_home($self, $tpid=NULL) {
	$projectInfo=R::Model('project.get', $tpid, '{data: "info"}');

	$isEdit = $projectInfo->RIGHT & _IS_EDITABLE;

	if ($isEdit) {
		$ret.='<div class="btn-floating -right-bottom"><a class="?-sg-action btn -floating -circle48" href="'.url('project/qt/'.$tpid.'/people/new').'" data-rel="#main"><i class="icon -addbig -white"></i></a></div>';
	}

	$stmt = 'SELECT * FROM %qtmast% WHERE `tpid` = :tpid AND `qtform` = :qtform ORDER BY `qtref` DESC';
	$dbs = mydb::select($stmt, ':tpid', $tpid, ':qtform', _PROJECT_QTFORM_PEOPLE);

	$tables = new Table();
	$tables->thead = array('center' => 'เลขที่แบบสอบถาม', 'date' => 'วันที่บันทึกข้อมูล', 'icons -c1' => '');
	foreach ($dbs->items as $rs) {
		$tables->rows[] = array(
												$rs->qtref.'/'.sg_date($rs->qtdate,'ปปปป'),
												$rs->qtdate ? sg_date($rs->qtdate, 'd/m/ปปปป') : '',
												'<a class="-sg-action" href="'.url('project/qt/'.$rs->tpid.'/people/view/'.$rs->qtref).'" data-rel="box"><i class="icon -viewdoc"></i></a>',
											);
	}

	$ret .= $tables->build();

	//$ret.=print_o($dbs,'$dbs');

	return $ret;
}
?>