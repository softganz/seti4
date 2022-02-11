<?php
/**
* Organization type of disabled
*
* @param Object $orgInfo
* @return String
*/

$debug = true;

function view_org_disabledclub_member($orgInfo = NULL) {
	$orderList = array(
								'name' => 'CONVERT(p.`name` USING tis620) ASC, CONVERT(p.`lname` USING tis620) ASC',
								'id' => 'm.`memberid` ASC',
								'in' => 'm.`datein` ASC'
							);
	$orderBy = $orderList[SG\getFirst(post('o'),'name')];
	$ret = '';

	$orgid = $orgInfo->orgid;

	$isAdmin = $orgInfo->RIGHT & _IS_ADMIN;
	$isOfficer = $orgInfo->RIGHT & _IS_OFFICER;

	//R::View('org.toolbar',$self, $orgInfo ? $orgInfo->name : '', 'disabledclub', $orgInfo, '{modulenav: false, searchform: false}');

	if ($isOfficer || $isAdmin) {
		$ret .= R::View('button.floating',url('org/disabledclub/'.$orgid.'/addmember'),'{title:"ลงทะเบียนสมาชิกใหม่"}');
	}


	mydb::value('$ORDER', $orderBy);

	$stmt = 'SELECT p.`prename`, p.`name`, p.`lname`, m.*
					FROM %org_member% m
						LEFT JOIN %db_person% p USING(`psnid`)
					WHERE m.`orgid` = :orgid AND `mtype` = "MEMBER"
					ORDER BY $ORDER';
	$dbs = mydb::select($stmt, ':orgid', $orgid);

	$tables = new Table();
	$tables->thead = array('no' => '', 'ชื่อ - นามสกุล', 'member-id -center' => 'เลขที่สมาชิก', 'date-in -date' => 'วันที่เริ่มเป็นสมาชิก');
	$no = 0;
	foreach ($dbs->items as $rs) {
		$tables->rows[] = array(
												++$no,
												$rs->prename.' '.$rs->name.' '.$rs->lname,
												$rs->memberid,
												$rs->datein,
											);
	}

	$ret .= $tables->build();

	//$ret .= print_o($dbs,'$dbs');

	//$ret .= print_o($orgInfo, '$orgInfo');
	return $ret;
}
?>