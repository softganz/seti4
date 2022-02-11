<?php
/**
* Module Method
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function imed_social_child($self, $orgId = NULL) {
	$orgInfo = is_object($orgId) ? $orgId : R::Model('imed.social.get', $orgId, '{}');
	$orgId = $orgInfo->orgid;

	if (!$orgId) return message('error','ไม่มีข้อมูลของกลุ่มที่ระบุ');

	$isAdmin = $orgInfo->RIGHT & _IS_ADMIN;
	$isMember = $orgInfo->is->socialtype;
	$isRemovePatient = $isAdmin || in_array($isMember,array('MODERATOR','CM'));

	if (!($isAdmin || $isMember)) return message('error','ขออภัย ท่านไม่ได้อยู่ในกลุ่มนี้');

	R::View('imed.toolbar', $self, $orgInfo->name, 'app.social', $orgInfo);

	$stmt = 'SELECT g.*, o.`name`, COUNT(*) `members`
		FROM %imed_socialparent% g
			LEFT JOIN %db_org% o USING(`orgid`)
			LEFT JOIN %imed_socialmember% m USING(`orgid`)
		WHERE g.`parent` = :parent
		GROUP BY `orgid`
		ORDER BY CONVERT(`name` USING tis620) ASC;
		-- {sum: "members"}';
	$dbs = mydb::select($stmt, ':parent', $orgId);

	$tables = new Table();
	$tables->thead = array('no'=>'','ชื่อหน่วยงาน','amt'=>'สมาชิก');
	foreach ($dbs->items as $rs) {
		$tables->rows[] = array(++$no,'<a href="'.url('imed/app/social/'.$rs->orgid).'">'.$rs->name.'</a>', $rs->members);
	}
	$tables->tfoot[] = array('<td></td>','',$dbs->sum->members);
	$ret .= $tables->build();

	//$ret .= print_o($dbs, '$dbs');
	return $ret;
}
?>