<?php
function r_org_gogreen_qt_get($qtref) {
	$result = (Object) [];
	$stmt='SELECT
					  q.*
					, o.`name`
				FROM %qtmast% q
					LEFT JOIN %db_org% o USING(`orgid`)
				WHERE q.`qtref`=:qtref LIMIT 1';
	$rs=mydb::select($stmt,':qtref',$qtref);
	//debugMsg(mydb()->_query);

	if ($rs->_empty) return $result;

	mydb::clearprop($rs);

	$rs->qtrefno=$rs->qtref.'/'.(sg_date($rs->qtdate,'Y')+543);

	$result=$rs;
	$result->RIGHT=$right=NULL;
	$result->RIGHTBIN='';


	$isOwner=i()->ok && $rs->uid==i()->uid;
	$isAdmin=user_access('administer imeds');
	$isAccess=false;
	$isEdit=false;
	$isZoneAdmin=false;

	if ($isAdmin || $isOwner) {
		$isAccess=true;
		$isEdit=true;
	} else {
		$isAccess=true;
		$isEdit=false;
	}


	if ($isAdmin) $right=$right | _IS_ADMIN;
	if ($isOwner) $right=$right | _IS_OWNER;
	if ($isAccess) $right=$right | _IS_ACCESS;
	if ($isEdit) $right=$right | _IS_EDITABLE;

	$result->RIGHT=$right;
	$result->RIGHTBIN=decbin($right);
	$result->right=$psnRight;

	//debugMsg($result,'$result');


	$stmt='SELECT * FROM %qttran% WHERE `qtref`=:qtref ORDER BY `qtid` ASC; -- {key:"part"}';
	$qttran=mydb::select($stmt,':qtref',$qtref)->items;

	$result->tr=$qttran;

	$result->name=$result->tr['ORG.NAME']->value;

	$result->photo=NULL;
	return $result;
}
?>