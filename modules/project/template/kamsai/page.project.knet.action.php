<?php
/**
* Project View
*
* @param Object $self
* @param Int $orgId
* @return String
*/
function project_knet_action($self, $orgId = NULL) {
	$orgInfo = is_object($orgId) ? $orgId : R::Model('school.get',$orgId);
	$orgId = $orgInfo->orgid;

	if (!$orgId) return message('error', 'ไม่มีข้อมูลองค์กรที่ระบุ');


	R::View('project.toolbar', $self, $orgInfo->name, 'knet', $orgInfo,'{showPrint: true}');


	$isAdmin = $orgInfo->RIGHT & _IS_ADMIN;
	$isEdit = ($isAdmin || $orgInfo->officers[i()->uid] == 'ADMIN') && post('mode') != 'view';

	$ret .= '<div class="project-knet -container">';


	// Show action
	$ret .= '<section class="project-knet -action -sg-box"><h3>โครงการ</h3>';
	$stmt = 'SELECT p.`tpid`, t.`title`, p.`date_from` FROM %project% p LEFT JOIN %topic% t USING(`tpid`) WHERE t.`orgid` = :orgid';
	$dbs = mydb::select($stmt, ':orgid', $orgId);

	$tables = new Table();

	foreach ($dbs->items as $rs) {
		$tables->rows[] = array(
												$rs->date_from ? sg_date($rs->date_from,'j/n/ปปปป') : '',
												'<a href="'.url('project/'.$rs->tpid).'">'.$rs->title.'</a>'
											);
	}

	$ret .= $tables->build();

	$ret .= '<h3>กิจกรรม</h3>';

	$stmt = 'SELECT
					p.`tpid`, a.`trid` `actionId`, a.`date1` `actionDate`, c.`title`
					FROM %project% p
						LEFT JOIN %topic% t USING(`tpid`)
						LEFT JOIN %project_tr% a ON a.`tpid` = p.`tpid` AND a.`formid` = "activity" AND a.`part` = "owner"
						LEFT JOIN %calendar% c ON c.`id` = a.`calid`
					WHERE t.`orgid` = :orgid
					ORDER BY a.`date1` ASC';
	$dbs = mydb::select($stmt, ':orgid', $orgId);

	$tables = new Table();

	foreach ($dbs->items as $rs) {
		$tables->rows[] = array(
			$rs->actionDate ? sg_date($rs->actionDate,'j/n/ปปปป') : '',
			'<a class="sg-action" href="'.url('project/'.$rs->tpid.'/action.view/'.$rs->actionId).'" data-rel="box">'.$rs->title.'</a>'
		);
	}

	$ret .= $tables->build();
	$ret .= '</section>';


	$ret .= '</div><!-- project-knet -container -->';


	//$ret .= print_o($orgInfo,'$orgInfo');


	return $ret;
}
?>
