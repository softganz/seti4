<?php
function r_org_seedfund_get($id) {
	$stmt='SELECT n.*, u.`name`
					FROM %org_seedfundneed% n
						LEFT JOIN %users% u USING(`uid`)
					WHERE `sfnid`=:id
					LIMIT 1';
	$rs=mydb::select($stmt,':id',$id);

	if ($rs->_empty) return null;

	$rs=mydb::clearprop($rs);

	$rs->isAdmin=is_admin('org');
	$rs->isOfficer=false;
	$rs->isOrgAdmin=false;
	$rs->isOwner=false;
	$rs->isEdit=false;
	$rs->officers=array();

	foreach (mydb::select('SELECT * FROM %org_officer% WHERE `orgid`=:orgid',':orgid',$rs->orgid)->items as $item) {
		$rs->officers[$item->uid]=$item->membership;
	}

	if (i()->ok) {
		$rs->isOfficer=array_key_exists(i()->uid, $rs->officers);
		$rs->isOrgAdmin=$rs->isOfficer && $rs->officers[i()->uid]=='admin';
		$rs->isOwner=i()->uid==$rs->uid || ($rs->isOfficer && in_array($rs->officers[i()->uid],array('admin','officer')));
		$rs->isEdit=$rs->isAdmin || $rs->isOwner;
	}
	//debugMsg($rs,'$rs');
	return $rs;
}
?>