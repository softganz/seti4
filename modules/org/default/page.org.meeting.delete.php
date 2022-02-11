<?php
function org_meeting_delete($self,$orgId, $doid) {
	$orgInfo = is_object($orgId) ? $orgId : R::Model('org.get',$orgId, '{initTemplate: true}');
	$orgId = $orgInfo->orgid;

	$isEdit = $orgInfo->RIGHT & _IS_OFFICER;

	if ($isEdit) {
		$stmt = 'SELECT d.*,
								(SELECT COUNT(*) FROM %org_dos% jd WHERE jd.`doid`=d.`doid` AND jd.`isjoin`=1) joins
							FROM %org_doings% d
							WHERE d.`doid` = :doid
							LIMIT 1';

		$rs = mydb::select($stmt,':doid',$doid);


		if ($isEdit && $rs->join == 0) {
			mydb::query('DELETE FROM %org_doings% WHERE `doid` = :doid LIMIT 1',':doid',$doid);
			$ret .= 'ลบรายการเรียบร้อย';
		}
	}
	return $ret;
}
?>