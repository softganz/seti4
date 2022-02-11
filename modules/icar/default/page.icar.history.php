<?php
/**
* History View
*
* @param Object $self
* @param Int $tpid
* @return String
*/

$debug = true;

function icar_history($self, $id) {
	$carInfo = icar_model::get_by_id($id);

	if ($carInfo->_empty) return message('error','ไม่มีข้อมูล');

	$self->theme->title = $carInfo->brandname.' <span>'.$carInfo->model.' , '.$carInfo->plate.'</span>';

	R::View('icar.toolbar', $self, NULL, NULL, $carInfo);

	$isAdmin = user_access('administer icars');
	$isShopOfficer = $carInfo->iam;
	$isAccessable = ($isAdmin || in_array($carInfo->iam, array('OWNER','MANAGER')));

	if (!$isAccessable) return message('error', 'access denied');

	$stmt = 'SELECT w.*, u.`name`
					FROM %watchdog% w
						LEFT JOIN %users% u USING(`uid`)
					WHERE `module` = "icar" AND `keyid` = :keyid
					ORDER BY `wid` DESC';
	$dbs = mydb::select($stmt, ':keyid', $id);

	$tables = new Table();
	$tables->thead = array('วันที่', 'Keyword', 'Field', 'รายละเอียด', 'ผู้แก้ไข');
	foreach ($dbs->items as $rs) {
		$tables->rows[] = array(
			$rs->date,
			$rs->keyword,
			$rs->fldname,
			$rs->message,
			$rs->name,
		);
	}
	$ret .= $tables->build();

	//$ret .= print_o($dbs);

	//$ret .= print_o($carInfo);

	return $ret;
}
?>