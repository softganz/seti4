<?php
/**
* iMed :: Admin Info
* Created 2020-12-16
* Modify  2020-12-16
*
* @param Object $self
* @param Int $id
* @return String
*
* @usage module/{id}/method
*/

$debug = true;

function imed_admin_info($self, $action = NULL, $mainId = NULL, $tranId = NULL) {
	$ret = '';

	$isAdmin = is_admin('imed');
	
	if (!$isAdmin) return message('error', 'Access Denied');

	switch ($action) {
		case 'zone.add':
			if ($mainId && post('areacode')) {
				$stmt = 'INSERT INTO %db_userzone%
					(`uid`, `zone`, `module`, `refid`, `right`, `addby`)
					VALUES
					(:uid, :areacode, :module, :refid, :right, :addby)
					ON DUPLICATE KEY UPDATE
					`right`=:right
					';
				mydb::query($stmt,':uid', $mainId, ':addby',i()->uid, post());
				//$ret .= mydb()->_query;
			}
			break;

		case 'zone.delete':
			if ($mainId && post('zone') && post('module') && post('refid')!='' && SG\confirm()) {
				$stmt = 'DELETE FROM %db_userzone%
					WHERE `uid` = :uid AND `zone` = :zone AND `module` = :module AND `refid` = :refid
					LIMIT 1';
				mydb::query($stmt,':uid',$mainId, post());
			}
			break;

		default:
			$ret .= 'Action not found';
			break;
	}
	return $ret;
}
?>