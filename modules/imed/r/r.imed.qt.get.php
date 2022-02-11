<?php
function r_imed_qt_get($qtref) {
	$result=NULL;
	$stmt='SELECT
					  q.*
					, p.`uid` `psnuid`
					, p.`house`, p.`village`, p.`tambon`, p.`ampur`, p.`changwat`
					, IFNULL(cosub.`subdistname`,p.`t_tambon`) subdistname
					, IFNULL(codist.`distname`,p.`t_ampur`) `distname`
					, IFNULL(copv.`provname`,p.`t_changwat`) `provname`
					, p.`zip`
					, u.`username` `username`
					, u.`name` `posterName`
				FROM %qtmast% q
					LEFT JOIN %users% u USING(`uid`)
					LEFT JOIN %db_person% p USING(`psnid`)
					LEFT JOIN %co_province% copv ON p.`changwat`=copv.`provid`
					LEFT JOIN %co_district% codist ON codist.`distid`=CONCAT(p.`changwat`,p.`ampur`)
					LEFT JOIN %co_subdistrict% cosub ON cosub.`subdistid`=CONCAT(p.`changwat`,p.`ampur`,p.`tambon`)
					LEFT JOIN %co_village% covi ON covi.`villid`=CONCAT(p.`changwat`,p.`ampur`,p.`tambon`,IF(LENGTH(p.`village`)=1,CONCAT("0",p.`village`),p.`village`))
				WHERE q.`qtref`=:qtref LIMIT 1';
	$rs=mydb::select($stmt,':qtref',$qtref);

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

	$stmt = 'SELECT sp.`psnid`
					FROM %imed_socialpatient% sp
					WHERE sp.`psnid` = :psnid AND sp.`orgid` IN (SELECT `orgid` FROM %imed_socialmember% WHERE `uid` = :uid AND `status` > 0)
					LIMIT 1';
	$isInSocialGroup = mydb::select($stmt, ':psnid',$result->psnid, ':uid', i()->uid)->psnid;

	if ($isAdmin) {
		$isAccess=true;
		$isEdit=true;
	} else if ($isOwner) {
		$isAccess=true;
		$isEdit=true;
	}	else if ($isInSocialGroup) {
		// Is patient in social group
		$isAccess = true;
		$isEdit = false;
	} else  if ($zones = imed_model::get_user_zone(i()->uid,'imed.poorman')) {
		//debugMsg($zones,'$zones');
		$psnRight=imed_model::in_my_zone($zones,$rs->changwat,$rs->ampur,$rs->tambon);
		if (!$psnRight && !$isOwner) {
			$isAccess=false;
			$isEdit=false;
		} else if ($psnRight->right=='admin') {
			$isAccess=true;
			$isEdit=true;
			$isAdmin=true;
		} else if ($psnRight->right=='edit') {
			$isAccess=true;
			$isEdit=true;
		} else if ($psnRight->right=='view') {
			$isAccess=true;
			$isEdit=false;
		} else if ($isOwner) {
			$isAccess=true;
			$isEdit=true;
		}
	} elseif (i()->ok && $rs->psnuid == i()->uid) {
		$isAccess = true;
		$isEdit = false;
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

	list($qttran['PSNL.BIRTH.YEAR'],$qttran['PSNL.BIRTH.MONTH'],$qttran['PSNL.BIRTH.DATE'])=explode('-',$qttran['PSNL.BIRTH']->value);
	$result->tr=$qttran;

	$photoList=mydb::select('SELECT f.*,s.`pid` `psnid` FROM %imed_files% f LEFT JOIN %imed_service% s USING(`seq`) WHERE f.`seq`=:seq',':seq',$rs->seq)->items;
	$result->photo=$photoList;
	return $result;
}
?>