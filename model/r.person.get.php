<?php
function r_person_get($id, $options = '{}') {
	$defaults = '{value: "repairname", debug: false}';
	$options = sg_json_decode($options, $defaults);
	$debug = $options->debug;
	
	$result = NULL;
	
	$stmt = 'SELECT
						  p.`psnid`, p.`cid`, p.`uid`, p.`prename`, p.`name`, p.`lname`, p.`nickname`
						, p.`sex`, p.`birth`
						, p.`phone`, p.`email`
						, p.`occupa`, cooc.`occu_desc`, p.`aptitude`, p.`interest`
						, p.`mstatus`, com.`name` `mstatus_desc`
						, p.`race`, p.`nation`, p.`religion`
						, p.`educate`, coe.`edu_desc`
						, p.`commune`
						, p.`house`, p.`village`, p.`tambon`, p.`ampur`, p.`changwat`
						, IFNULL(cosub.`subdistname`,p.`t_tambon`) subdistname
						, IFNULL(codist.`distname`,p.`t_ampur`) `distname`
						, IFNULL(copv.`provname`,p.`t_changwat`) `provname`
						, p.`zip`
						, p.`rhouse`, p.`rvillage`
						, p.`rtambon`, rcosub.`subdistname` rsubdistname
						, p.`rampur`, rcodist.`distname` rdistname
						, p.`rchangwat`, rcopv.`provname` rprovname
						, p.`rzip`
						, p.`website`
						, p.`remark`
						, g.`gis`
						, CONCAT(X(g.`latlng`),",",Y(g.`latlng`)) latlng, X(g.`latlng`) lat, Y(g.`latlng`) lnt
						, uc.`name` `created_by`, p.`created` `created_date`
						, p.`modify`, p.`umodify`, um.`name` `modify_by`
					FROM %db_person% p
						LEFT JOIN %users% uc ON p.`uid`=uc.`uid`
						LEFT JOIN %users% um ON p.`umodify`=um.`uid`
						LEFT JOIN %co_educate% coe ON coe.`edu_code`=p.`educate`
						LEFT JOIN %co_occu% cooc ON cooc.`occu_code`=p.`occupa`
						LEFT JOIN %tag% com ON com.`taggroup`="mstatus" AND p.`mstatus`=com.`catid`

						LEFT JOIN %co_province% copv ON p.`changwat`=copv.`provid`
						LEFT JOIN %co_district% codist ON codist.`distid`=CONCAT(p.`changwat`,p.`ampur`)
						LEFT JOIN %co_subdistrict% cosub ON cosub.`subdistid`=CONCAT(p.`changwat`,p.`ampur`,p.`tambon`)
						LEFT JOIN %co_village% covi ON covi.`villid`=CONCAT(p.`changwat`,p.`ampur`,p.`tambon`,IF(LENGTH(p.`village`)=1,CONCAT("0",p.`village`),p.`village`))

						LEFT JOIN %co_province% rcopv ON p.`rchangwat`=rcopv.`provid`
						LEFT JOIN %co_district% rcodist ON rcodist.`distid`=CONCAT(p.`rchangwat`,p.`rampur`)
						LEFT JOIN %co_subdistrict% rcosub ON rcosub.`subdistid`=CONCAT(p.`rchangwat`,p.`rampur`,p.`rtambon`)
						LEFT JOIN %co_village% rcovi ON rcovi.`villid`=CONCAT(p.`rchangwat`, p.`rampur`, p.`rtambon`, IF(LENGTH(p.`rvillage`)=1, CONCAT("0", p.`rvillage`), p.`rvillage`))

						LEFT JOIN %gis% g ON p.`gis`=g.`gis`
					WHERE p.`psnid`=:id LIMIT 1';
	$rs=mydb::select($stmt, ':id',$id);

	//debugMsg(mydb()->_query);

	if (empty($rs->_num_rows)) return NULL;


	if (!$debug) mydb::clearprop($rs);

	$rs->realname=trim($rs->name.' '.$rs->lname);
	$rs->fullname=trim($rs->prename.' '.$rs->name.' '.$rs->lname);

	$rs->address=trim($rs->house.($rs->soi?' ซอย'.$rs->soi:'').($rs->road?' ถนน'.$rs->road:'').($rs->village?' หมู่ที่ '.$rs->village:'').($rs->villname?' บ้าน'.$rs->villname:'').($rs->subdistname?' ตำบล'.$rs->subdistname:'').($rs->distname?' อำเภอ'.$rs->distname:'').($rs->provname?' จังหวัด'.$rs->provname:'').($rs->zip?' รหัสไปรษณีย์ '.$rs->zip:''));
	$rs->raddress=trim($rs->rhouse.($rs->rvillage?' หมู่ที่ '.$rs->rvillage:'').($rs->rvillname?' บ้าน'.$rs->rvillname:'').($rs->rsubdistname?' ตำบล'.$rs->rsubdistname:'').($rs->rdistname?' อำเภอ'.$rs->rdistname:'').($rs->rprovname?' จังหวัด'.$rs->rprovname:'').($rs->rzip?' รหัสไปรษณีย์ '.$rs->rzip:''));

	$result->psnid=$rs->psnid;
	$result->fullname=$rs->fullname;
	$result->uid=$rs->uid;
	$result->RIGHT=NULL;
	$result->RIGHTBIN=NULL;
	$result->error=NULL;
	$result->info=$rs;


	$right=0;

	$isOwner=i()->ok && $result->info->uid==i()->uid;
	$isAdmin=user_access('administer imeds');
	$isAccess=false;
	$isEdit=false;
	//user_access('administer imeds','edit own imed content',$result->info->uid) || $isOwner;
	if ($isAdmin || $isOwner) {
		$isAccess=true;
		$isEdit=true;
	} else  if ($zones=R::Model('imed.zone.get',i()->uid,'imed')) {
		$psnRight=R::Model('imed.zone.right',$zones,$rs->changwat,$rs->ampur,$rs->tambon);
		if (!$psnRight) {
			$isAccess=false;
			$isEdit=false;
		} else if (in_array($psnRight->right,array('edit','admin'))) {
			$isAccess=true;
			$isEdit=true;
		} else if (in_array($psnRight->right,array('view'))) {
			$isAccess=true;
			$isEdit=false;
		}
	} else {
		$isAccess=false;
		$isEdit=false;
	}


	if ($isAdmin) $right=$right | _IS_ADMIN;
	if ($isOwner) $right=$right | _IS_OWNER;
	if ($isAccess) $right=$right | _IS_ACCESS;
	if ($isEdit) $right=$right | _IS_EDITABLE;

	$result->RIGHT=$right;
	$result->RIGHTBIN=decbin($right);

	if (!$isAccess) $result->error='ข้อมูลของ <b>"'.$rs->name.' '.$rs->lname.'"</b> อยู่นอกพื้นที่การดูแลของท่าน หากข้อมูลนี้ไม่ถูกต้อง กรุณาแจ้งผู้ดูแลระบบ';

	if ($debug) debugMsg($result,'$result');
	return $result;
}
?>