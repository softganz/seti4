<?php
/**
* Organization type of disabled
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function org_disabledclub($self, $orgid = NULL, $action = NULL, $actionId = NULL) {
	$ret = '';

	if ($orgid) {
		$orgInfo = R::Model('org.get', $orgid);
	}

	$isAdmin = $orgInfo->RIGHT & _IS_ADMIN;
	$isOfficer = $orgInfo->RIGHT & _IS_OFFICER;
	$isRight = $isAdmin || $isOfficer;

	//$ret.=print_o($fundInfo, '$fundInfo');

	if ($orgid && !$isRight) return message('error', 'Access Denied');

	R::View('org.toolbar',$self, $orgInfo ? $orgInfo->name : 'ชมรม/สมาคมคนพิการ', 'disabledclub', $orgInfo, '{modulenav: false, searchform: false}');

	switch ($action) {

		case 'addmember' :
			$ret .= '<h3>ADD MEMBER</h3>'.message('notify','ยังไม่ได้ดำเนินการ');
			break;

		default:
			if ($action) {
				$ret .= R::View('org.disabledclub.'.$action, $orgInfo);
			} else if ($orgInfo->orgid) {
				$ret.=R::View('org.disabledclub.info', $orgInfo);
			} else {
				$ret.=R::View('org.disabledclub.home');
			}
			break;
	}


	//$ret .= print_o($orgInfo, '$orgInfo');
	return $ret;
}

/*
Import from imed disabled

OTHR.5.3.4.IDENTIFY
OTHR.5.3.4.MEMBERID

SELECT a.`pid`,p.`name`,p.`lname`,a.`part`,a.`value`,b.`part`,b.`value` FROM `sgz_imed_qt` a
LEFT JOIN `sgz_db_person` p ON p.`psnid`=a.`pid`
LEFT JOIN `sgz_imed_qt` b ON b.`pid`=a.`pid` AND b.`part`="OTHR.5.3.4.MEMBERID"
WHERE a.`part` = "OTHR.5.3.4.IDENTIFY" AND a.`value`="ชมรมคนนาทวีไม่ทอดทิ้งกัน"
ORDER BY a.`value` ASC
LIMIT 1000




INSERT INTO sgz_org_member
SELECT 1501, a.`pid`, "MEMBER", b.`value`, NULL, NULL,NULL, UNIX_TIMESTAMP()
FROM `sgz_imed_qt` a
LEFT JOIN `sgz_db_person` p ON p.`psnid`=a.`pid`
LEFT JOIN `sgz_imed_qt` b ON b.`pid`=a.`pid` AND b.`part`="OTHR.5.3.4.MEMBERID"
WHERE a.`part` = "OTHR.5.3.4.IDENTIFY" AND a.`value`="ชมรมคนนาทวีไม่ทอดทิ้งกัน"
*/
?>