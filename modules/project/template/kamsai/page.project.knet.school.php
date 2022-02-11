<?php
/**
* Project View
*
* @param Object $self
* @param Int $tpid
* @return String
*/
function project_knet_school($self, $orgId = NULL) {
	$orgInfo = is_object($orgId) ? $orgId : R::Model('school.get',$orgId);
	$orgId = $orgInfo->orgid;

	if (!$orgId) return message('error', 'ไม่มีข้อมูลองค์กรที่ระบุ');


	R::View('project.toolbar', $self, $orgInfo->name, 'knet', $orgInfo,'{showPrint: true}');


	$isAdmin = $projectInfo->info->isAdmin;
	$isEdit = $projectInfo->info->isEdit && post('mode') != 'view';
	$isEditDetail = $projectInfo->info->isEditDetail;
	$lockReportDate = $projectInfo->info->lockReportDate;


	$ret .= '<h3>โรงเรียนเครือข่าย</h3>';

	if ($isViewOnly) {
		// Do nothing
	} else if ($isEdit) {
		$ret .= '<div class="btn-floating -right-bottom"><a class="sg-action btn -floating -circle48" href="'.url('project/'.$tpid.'/knet.school.add').'" title="สร้างโรงเรียนเครือข่าย" ><i class="icon -material">add</i></a></div>';
	}


	$stmt = 'SELECT * FROM %db_org% WHERE `parent` = :parent';
	$dbs = mydb::select($stmt, ':parent', $orgId);

	$tables = new Table();

	foreach ($dbs->items as $rs) {
		$tables->rows[] = array('<a href="'.url('project/knet/'.$rs->orgid).'">'.$rs->name.'</a>');
	}

	$ret .= $tables->build();

	//$ret .= print_o($dbs);

	// รายละเอียดโครงการ
	if ($isEdit) {
		$inlineAttr['class'] = 'sg-inline-edit';
		$inlineAttr['data-update-url'] = url('project/edit/tr');
		$inlineAttr['data-tpid'] = $tpid;
		$inlineAttr['data-refresh-url'] = url('project/'.$tpid,array('debug'=>post('debug')));
		if (debug('inline')) $inlineAttr['data-debug'] = 'inline';
	}

	$ret .= '<div id="project-info" '.sg_implode_attr($inlineAttr).'>'._NL;


	$ret .= '</div><!-- project-info -->'._NL._NL;

	//$ret .= print_o($projectInfo,'$projectInfo');


	return $ret;
}
?>
