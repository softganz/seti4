<?php
/**
* Person   :: Person Model
* Created :: 2022-09-27
* Modify  :: 2025-06-27
* Version :: 3
*
* @param Array $args
* @return Object
*
* @usage new PersonModel([])
* @usage PersonModel::function($conditions, $options)
*/

class PersonModel {
	function __construct($args = []) {
	}

	public static function get($id, $options = '{}') {
		$defaults = '{value: "repairname", debug: false}';
		$options = sg_json_decode($options, $defaults);
		$debug = $options->debug;

		$rs = mydb::select(
			'SELECT
			  p.`psnId`, p.`cid`, p.`uid`
			, p.`preName`
			, p.`name` `firstName`
			, p.`lname` `lastName`
			, p.`prename`, p.`name`, p.`lname`, p.`nickname`
			, p.`sex`, p.`birth`
			, p.`phone`, p.`email`
			, p.`occupa`, cooc.`occu_desc`, p.`aptitude`, p.`interest`
			, p.`mstatus`, com.`name` `mstatus_desc`
			, p.`race`, p.`nation`, p.`religion`
			, p.`educate`, coe.`edu_desc`
			, p.`areacode`
			, p.`commune`
			, p.`house`, p.`village`, p.`tambon`, p.`ampur`, p.`changwat`
			, IFNULL(cosub.`subdistname`,p.`t_tambon`) subdistname
			, IFNULL(codist.`distname`,p.`t_ampur`) `distname`
			, IFNULL(copv.`provname`,p.`t_changwat`) `provname`
			, copv.`provname` `changwatName`
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

				LEFT JOIN %co_province% copv ON LEFT(p.`areacode`, 2) = copv.`provid`
				LEFT JOIN %co_district% codist ON codist.`distid`=CONCAT(p.`changwat`,p.`ampur`)
				LEFT JOIN %co_subdistrict% cosub ON cosub.`subdistid`=CONCAT(p.`changwat`,p.`ampur`,p.`tambon`)
				LEFT JOIN %co_village% covi ON covi.`villid`=CONCAT(p.`changwat`,p.`ampur`,p.`tambon`,IF(LENGTH(p.`village`)=1,CONCAT("0",p.`village`),p.`village`))

				LEFT JOIN %co_province% rcopv ON p.`rchangwat`=rcopv.`provid`
				LEFT JOIN %co_district% rcodist ON rcodist.`distid`=CONCAT(p.`rchangwat`,p.`rampur`)
				LEFT JOIN %co_subdistrict% rcosub ON rcosub.`subdistid`=CONCAT(p.`rchangwat`,p.`rampur`,p.`rtambon`)
				LEFT JOIN %co_village% rcovi ON rcovi.`villid`=CONCAT(p.`rchangwat`, p.`rampur`, p.`rtambon`, IF(LENGTH(p.`rvillage`)=1, CONCAT("0", p.`rvillage`), p.`rvillage`))

				LEFT JOIN %gis% g ON p.`gis`=g.`gis`
			WHERE p.`psnId` = :id LIMIT 1',
			[':id' => $id]
		);

		//debugMsg(mydb()->_query);

		if (empty($rs->_num_rows)) return NULL;


		if (!$debug) mydb::clearProp($rs);

		$rs->realname = trim($rs->name.' '.$rs->lname);
		$rs->fullName = $rs->fullname = trim($rs->prename.' '.$rs->name.' '.$rs->lname);

		$rs->address=trim($rs->house.($rs->soi?' ซอย'.$rs->soi:'').($rs->road?' ถนน'.$rs->road:'').($rs->village?' หมู่ที่ '.$rs->village:'').($rs->villname?' บ้าน'.$rs->villname:'').($rs->subdistname?' ตำบล'.$rs->subdistname:'').($rs->distname?' อำเภอ'.$rs->distname:'').($rs->provname?' จังหวัด'.$rs->provname:'').($rs->zip?' รหัสไปรษณีย์ '.$rs->zip:''));
		$rs->raddress=trim($rs->rhouse.($rs->rvillage?' หมู่ที่ '.$rs->rvillage:'').($rs->rvillname?' บ้าน'.$rs->rvillname:'').($rs->rsubdistname?' ตำบล'.$rs->rsubdistname:'').($rs->rdistname?' อำเภอ'.$rs->rdistname:'').($rs->rprovname?' จังหวัด'.$rs->rprovname:'').($rs->rzip?' รหัสไปรษณีย์ '.$rs->rzip:''));

		$result = (Object) [
			'psnId' => $rs->psnId,
			'fullname' => $rs->fullname,
			'uid' => $rs->uid,
			'RIGHT' => NULL,
			'RIGHTBIN' => NULL,
			'error' => NULL,
			'info' => $rs,
		];


		$right=0;

		$isOwner=i()->ok && $result->info->uid==i()->uid;
		$isAdmin=user_access('administer imeds');
		$isAccess=false;
		$isEdit=false;
		//user_access('administer imeds','edit own imed content',$result->info->uid) || $isOwner;
		if ($isAdmin || $isOwner) {
			$isAccess=true;
			$isEdit=true;
		} else  if (i()->ok && class_exists('ImedModel') && $zones=ImedModel::get_user_zone(i()->uid,'imed')) {
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

	// public static function items($conditions, $options = '{}') {
	// 	$defaults = '{debug: false}';
	// 	$options = \SG\json_decode($options, $defaults);
	// 	$debug = $options->debug;

	// 	if (is_string($conditions) && preg_match('/^{/',$conditions)) {
	// 		$conditions = \SG\json_decode($conditions);
	// 	} else if (is_object($conditions)) {
	// 		//
	// 	} else if (is_array($conditions)) {
	// 		$conditions = (Object) $conditions;
	// 	} else {
	// 		$conditions = (Object) ['id' => $conditions];
	// 	}

	// 	$result = (Object) [];

	// 	return $result;
	// }

	public static function save($data, $options = '{}') {
		$data = (Object) $data;
		$defaults = '{debug: false}';
		$options = \SG\json_decode($options, $defaults);
		$debug = $options->debug;

		$result = (Object) [
			'psnId' => NULL,
		];

		if (empty($data->psnId)) $data->psnId = NULL;
		if (property_exists($data, 'prename')) $data->preName = $data->prename;
		if (property_exists($data, 'firstname')) $data->firstName = $data->firstname;
		if (property_exists($data, 'lastname')) $data->lastName = $data->lastname;

		if(property_exists($data, 'preName')) $updateFields[] = '`preName` = :preName';
		if(property_exists($data, 'firstName')) $updateFields[] = '`name` = :firstName';
		if(property_exists($data, 'lastName')) $updateFields[] = '`lname` = :lastName';
		if(property_exists($data, 'cid')) $updateFields[] = '`cid` = :cid';
		if(property_exists($data, 'sex')) $updateFields[] = '`sex` = :sex';
		if(property_exists($data, 'birth')) $updateFields[] = '`birth` = :birth';
		if(property_exists($data, 'religion')) $updateFields[] = '`religion` = :religion';
		if(property_exists($data, 'zip')) $updateFields[] = '`zip` = :zip';
		if(property_exists($data, 'phone')) $updateFields[] = '`phone` = :phone';
		if(property_exists($data, 'graduated')) $updateFields[] = '`graduated` = :graduated';
		if(property_exists($data, 'faculty')) $updateFields[] = '`faculty` = :faculty';
		if(property_exists($data, 'address')) {
			$updateFields[] = '`areacode` = :areacode';
			$updateFields[] = '`house` = :house';
			$updateFields[] = '`village` = :village';
			$updateFields[] = '`tambon` = :tambon';
			$updateFields[] = '`ampur` = :ampur';
			$updateFields[] = '`changwat` = :changwat';
		}

		if (empty($data->cid)) $data->cid = NULL;

		if (empty($data->religion)) $data->religion = NULL;

		$data->email = \SG\getFirst($data->email);
		$data->areacode = \SG\getFirst($data->areacode);
		$data->hrareacode = \SG\getFirst($data->hrareacode);

		if ($data->address) {
			$addrList = \SG\explode_address($data->address,$data->areacode);
			$result->address = $addrList;
			if ($addrList['house']) $data->house = $addrList['house'];
			if ($addrList['village']) $data->village = $addrList['village'];
			if (strlen($data->areacode) == 6 && $data->village) $data->areacode .= sprintf('%02d', $data->village);
		}

		if ($data->birth && is_array($data->birth)) {
			$data->birth = $data->birth['year'].'-'.$data->birth['month'].'-'.$data->birth['date'];
		};

		$data->birth = $data->birth ? sg_date($data->birth, 'Y-m-d') : NULL;

		if (empty($data->sex)) $data->sex = NULL;

		if (empty($data->house)) $data->house = '';
		if (empty($data->village)) $data->village = '';
		if (empty($data->tambon)) $data->tambon = '';
		if (empty($data->ampur)) $data->ampur = '';
		if (empty($data->changwat)) $data->changwat = '';
		if (empty($data->zip)) $data->zip = '';

		if (empty($data->rhouse)) $data->rhouse = '';
		if (empty($data->rvillage)) $data->rvillage = '';
		if (empty($data->rtambon)) $data->rtambon = '';
		if (empty($data->rampur)) $data->rampur = '';
		if (empty($data->rchangwat)) $data->rchangwat = '';
		if (empty($data->rzip)) $data->rzip = '';

		if (empty($data->phone)) $data->phone = '';

		if (empty($data->graduated)) $data->graduated = '';
		if (empty($data->faculty)) $data->faculty = '';
		$data->userid = \SG\getFirst($data->userid);

		$data->uid = \SG\getFirst($data->uid, i()->uid, NULL);
		$data->created = date('U');
		$data->userId = \SG\getFirst($data->userId, NULL);

		$stmt = 'INSERT INTO %db_person%
			(
				  `psnId`
				, `uid`, `cid`, `preName`, `name`, `lname`, `sex`
				, `birth`, `religion`
				, `areacode`, `hrareacode`
				, `house`, `village`, `tambon`, `ampur`, `changwat`, `zip`
				, `rhouse`, `rvillage`, `rtambon`, `rampur`, `rchangwat`
				, `graduated`, `faculty`
				, `phone`, `email`
				, `created`, `userid`
			) VALUES (
				  :psnId
				, :uid, :cid, :preName, :firstName, :lastName, :sex
				, :birth, :religion
				, :areacode, :hrareacode
				, :house, :village, :tambon, :ampur, :changwat, :zip
				, :rhouse, :rvillage, :rtambon, :rampur, :rchangwat
				, :graduated, :faculty
				, :phone, :email
				, :created, :userid
			) ON DUPLICATE KEY UPDATE
			'.implode(', ' , $updateFields);

		mydb::query($stmt,$data);

		$result->_query[] = mydb()->_query;

		if (mydb()->_error) {
			$result->_error = mydb()->_error;
			return $result;
		}

		if (empty($data->psnId)) $data->psnId = mydb()->insert_id;

		$result->psnId = $data->psnId;

		return $result;
	}
}
?>