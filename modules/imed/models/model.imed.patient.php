<?php
/**
* iMed Model :: Patient Model
* Created 2021-08-19
* Modify  2021-08-26
*
* @param Object $conditions
* @return Object $options
*
* @usage import('model:imed.patient')
* @usage new PatientModel([])
* @usage PatientModel::method()
*/

$debug = true;

class PatientModel {
	/*
	* Get Patient Information
	* Created 2021-08-25
	* Modify  2021-08-26
	*
	* @param Object $data
	* @param Object/Array/JSON $options
	*/
	public static function create($data, $options = '{}') {
		$defaults = '{debug: false}';
		$options = SG\json_decode($options, $defaults);
		$debug = $options->debug;

		if (empty($data->fullname)) return ['error' => 'ไม่มีชื่อ-นามสกุล'];

		$result = (Object) [
			'psnId' => NULL,
			'name' => $data->fullname,
			'error' => false,
			'complete' => false,
			'query' => [],
		];

		$noCID = preg_match('/^\?/', $data->cid);

		$data->module = SG\getFirst($data->module, 'IMED');
		$data->prename = trim($data->prename);
		$data->fullname = trim($data->fullname);
		$data->cid = $noCID ? NULL : SG\getFirst(trim($data->cid));
		$data->sex = SG\getFirst($data->sex);
		$data->address = SG\getFirst(trim($data->address));
		$data->areacode = SG\getFirst(trim($data->areacode));
		$data->hrareacode = SG\getFirst(trim($data->hrareacode));

		list($data->name,$data->lname) = sg::explode_name(' ', $data->fullname);

		// Update address
		$address = SG\explode_address($data->address, $data->areacode);
		if ($address && $address['areaCode']) $data->areacode = $address['areaCode'];
		$data->house = $data->rhouse = $address['house'];
		$data->village = $data->rvillage = $address['village'];
		$data->tambon = $data->rtambon = $address['tambonCode'];
		$data->ampur = $data->rampur = $address['ampurCode'];
		$data->changwat = $data->rchangwat = $address['changwatCode'];
		$data->zip = $data->rzip = $address['zip'];

		// ถ้ามีที่อยู่ตามทะเบียนบ้าน
		if ($data->hrareacode) {
			$registAddress = SG\explode_address($data->raddress, $data->hrareacode);
			if ($registAddress && $address['hrareaCode']) $data->hrareacode = $registAddress['areaCode'];
			$data->rhouse = $registAddress['house'];
			$data->rvillage = $registAddress['village'];
			$data->rtambon = $registAddress['tambonCode'];
			$data->rampur = $registAddress['ampurCode'];
			$data->rchangwat = $registAddress['changwatCode'];
			$data->rzip = $registAddress['zip'];
		} else {
			$data->hrareacode = $data->areacode;
		}

		if (empty($data->name) || empty($data->lname)) {
			$result->error = 'กรุณาป้อน ชื่อ และ นามสกุล โดยเว้นวรรค 1 เคาะ';
			return $result;
		} else if ($data->cid && !$noCID &&
			$dupCID = mydb::select('SELECT p.`psnid` FROM %db_person% p WHERE `cid`=:cid LIMIT 1',$data)->psnid) {
			$result->error = 'หมายเลขบัตรประชาชน "'.$data->cid.'" มีอยู่ในฐานข้อมูลแล้ว';
			if ($debug) $result->query[]=mydb()->_query;
			return $result;
		} else if ($data->name && $data->lname &&
			$dupid = mydb::select('SELECT p.`psnid` FROM %db_person% p WHERE `name` = :name AND `lname` = :lname AND `cid` = :cid LIMIT 1', $data)->psnid) {
			$result->error = 'ชื่อ "'.$data->fullname.'" มีอยู่ในฐานข้อมูลแล้ว';
			if ($debug) $result->query[] = mydb()->_query;
			return $result;
		}
		//$data->cid=post('cid');
		$data->uid = i()->uid;
		$data->created = date('U');

		$stmt = 'INSERT INTO %db_person% (
			  `module`, `uid`, `prename`, `name`, `lname`, `cid`, `sex`
			, `areacode`, `house`, `village`, `tambon`, `ampur`, `changwat`, `zip`
			, `hrareacode`, `rhouse`, `rvillage`, `rtambon`, `rampur`, `rchangwat`, `rzip`
			, `created`
			) VALUES (
			  :module, :uid, :prename, :name, :lname, :cid, :sex
			, :areacode, :house, :village, :tambon, :ampur, :changwat, :zip
			, :hrareacode, :rhouse, :rvillage, :rtambon, :rampur, :rchangwat, :rzip
			, :created
			)';

		mydb::query($stmt, $data);

		if ($debug) $result->query[] = mydb()->_query;

		if (!mydb()->_error) {
			$result->psnId = $data->psnId = mydb()->insert_id;

			mydb::query(
				'INSERT INTO %imed_patient%
				(`pid`, `uid`, `created`)
				VALUES
				(:psnId, :uid, :created)',
				$data
			);

			if ($debug) $result->query[] = mydb()->_query;
		}

		$result->complete = 'บันทึกข้อมูลเรียบร้อย';
		return $result;
	}

	/*
	* Get Patient Information
	* Created 2021-08-19
	* Modify  2021-08-22
	*
	* @param Int $psnId
	* @param Object/Array/JSON $options
	*/
	public static function get($psnId, $options = '{}') {
		$defaults = '{debug:false, data: "*"}';
		$options = sg_json_decode($options,$defaults);
		$debug = $options->debug;

		$result = (Object) [];

		$stmt = 'SELECT
			  p.`psnid`, p.`cid`, p.`uid`, p.`access`
			, p.`prename`, p.`name`, p.`lname`, p.`nickname`
			, p.`sex`, p.`birth`
			, p.`educate`, p.`phone`, p.`email`
			, p.`occupa`, cooc.`occu_desc`, p.`aptitude`, p.`interest`
			, p.`mstatus`, com.`cat_name` `mstatus_desc`
			, p.`race`, p.`nation`, p.`religion`
			, p.`dischar`, p.`ddisch`
			, coe.`edu_desc`
			, p.`commune`
			, "" `areacode`
			, p.`house`, p.`village`, p.`tambon`, p.`ampur`, p.`changwat`
			, IFNULL(cosub.`subdistname`,p.`t_tambon`) subdistname
			, IFNULL(codist.`distname`,p.`t_ampur`) `distname`
			, IFNULL(copv.`provname`,p.`t_changwat`) `provname`
			, p.`zip`
			, "" `rareacode`
			, p.`rhouse`, p.`rvillage`
			, p.`rtambon`, rcosub.`subdistname` rsubdistname
			, p.`rampur`, rcodist.`distname` rdistname
			, p.`rchangwat`, rcopv.`provname` rprovname
			, p.`rzip`
			, p.`adl`
			, p.`admit`
			, p.`remark`
			, g.`gis`
			, CONCAT(X(g.`latlng`),",",Y(g.`latlng`)) latlng, X(g.`latlng`) lat, Y(g.`latlng`) lnt
			, uc.`name` `created_by`, p.`created` `created_date`
			, p.`modify`, p.`umodify`, um.`name` `modify_by`
			FROM %db_person% p
				LEFT JOIN %users% uc ON p.`uid` = uc.`uid`
				LEFT JOIN %users% um ON p.`umodify` = um.`uid`
				LEFT JOIN %co_educate% coe ON coe.`edu_code` = p.`educate`
				LEFT JOIN %co_occu% cooc ON cooc.`occu_code` = p.`occupa`
				LEFT JOIN %co_category% com ON p.`mstatus` = com.`cat_id`

				LEFT JOIN %co_province% copv ON p.`changwat` = copv.`provid`
				LEFT JOIN %co_district% codist ON codist.`distid` = CONCAT(p.`changwat`,p.`ampur`)
				LEFT JOIN %co_subdistrict% cosub ON cosub.`subdistid` = CONCAT(p.`changwat`,p.`ampur`,p.`tambon`)
				LEFT JOIN %co_village% covi ON covi.`villid` = CONCAT(p.`changwat`,p.`ampur`,p.`tambon`,IF(LENGTH(p.`village`) = 1,CONCAT("0",p.`village`),p.`village`))

				LEFT JOIN %co_province% rcopv ON p.`rchangwat` = rcopv.`provid`
				LEFT JOIN %co_district% rcodist ON rcodist.`distid` = CONCAT(p.`rchangwat`,p.`rampur`)
				LEFT JOIN %co_subdistrict% rcosub ON rcosub.`subdistid` = CONCAT(p.`rchangwat`,p.`rampur`,p.`rtambon`)
				LEFT JOIN %co_village% rcovi ON rcovi.`villid` = CONCAT(p.`rchangwat`, p.`rampur`, p.`rtambon`, IF(LENGTH(p.`rvillage`)=1, CONCAT("0", p.`rvillage`), p.`rvillage`))

				LEFT JOIN %gis% g ON p.`gis` = g.`gis`
			WHERE p.`psnid`=:psnid
			LIMIT 1';

		$rs = mydb::select($stmt, ':psnid', $psnId);

		if (empty($rs->_num_rows)) return NULL;


		if (!$debug) mydb::clearprop($rs);

		$rs->realname = trim($rs->name.' '.$rs->lname);
		$rs->fullname = trim($rs->prename.' '.$rs->name.' '.$rs->lname);

		$rs->barthel = R::Model('imed.barthel.level', $rs->adl);

		$rs->areacode = $rs->changwat.$rs->ampur.$rs->tambon.sprintf('%02d',$rs->village);
		$rs->address = trim($rs->house
			. ($rs->soi?' ซอย'.$rs->soi:'')
			. ($rs->road?' ถนน'.$rs->road:'')
			. ($rs->village?' หมู่ที่ '.$rs->village:'')
			. ($rs->villname?' บ้าน'.$rs->villname:'')
			. ($rs->subdistname?' ตำบล'.$rs->subdistname:'')
			. ($rs->distname?' อำเภอ'.$rs->distname:'')
			. ($rs->provname?' จังหวัด'.$rs->provname:'')
			. ($rs->zip?' รหัสไปรษณีย์ '.$rs->zip:'')
		);


		$rs->rareacode = $rs->rchangwat.$rs->rampur.$rs->rtambon.sprintf('%02d',$rs->rvillage);
		$rs->raddress = trim($rs->rhouse
			. ($rs->rvillage?' หมู่ที่ '.$rs->rvillage:'')
			. ($rs->rvillname?' บ้าน'.$rs->rvillname:'')
			. ($rs->rsubdistname?' ตำบล'.$rs->rsubdistname:'')
			. ($rs->rdistname?' อำเภอ'.$rs->rdistname:'')
			. ($rs->rprovname?' จังหวัด'.$rs->rprovname:'')
			. ($rs->rzip?' รหัสไปรษณีย์ '.$rs->rzip:'')
		);

		$result->psnId = $rs->psnid;
		$result->psnid = $rs->psnid;
		$result->fullname = $rs->fullname;
		$result->realname = $rs->realname;
		$result->uid = $rs->uid;
		$result->RIGHT = NULL;
		$result->RIGHTBIN = NULL;
		$result->error = NULL;
		$result->info = $rs;
		$result->care = NULL;

		$right = 0;

		$isOwner = i()->ok && $result->info->uid == i()->uid;
		$isAdmin = user_access('administer imeds');
		$isAccess = false;
		$isEdit = false;

		$stmt = 'SELECT sp.`psnid`
			FROM %imed_socialpatient% sp
			WHERE sp.`psnid` = :psnid AND sp.`orgid` IN (SELECT `orgid` FROM %imed_socialmember% WHERE `uid` = :uid AND `status` > 0)
			LIMIT 1';
		$isInSocialGroup = mydb::select($stmt, ':psnid',$psnId, ':uid', i()->uid)->psnid;
		//debugMsg($isInSocialGroup);

		if ($result->info->access) {
			$isAccess = true;
			$isEdit = true;
		} else if ($isAdmin || $isOwner) {
			$isAccess = true;
			$isEdit = true;
		} else if ($isInSocialGroup) {
			// Is patient in social group
			$isAccess = true;
			$isEdit = true;
		} else  if ($zones = imed_model::get_user_zone(i()->uid,'imed')) {
			$psnRight = imed_model::in_my_zone($zones,$result->info->changwat,$result->info->ampur,$result->info->tambon);
			if (!$psnRight) {
				$isAccess = false;
				$isEdit = false;
			} else if (in_array($psnRight->right,array('edit','admin'))) {
				$isAccess = true;
				$isEdit = true;
			} else if (in_array($psnRight->right,array('view'))) {
				$isAccess = true;
				$isEdit = false;
			}
		} else {
			$isAccess = false;
			$isEdit = false;
		}


		if ($isAdmin) $right = $right | _IS_ADMIN;
		if ($isOwner) $right = $right | _IS_OWNER;
		if ($isAccess) $right = $right | _IS_ACCESS;
		if ($isEdit) $right = $right | _IS_EDITABLE;

		$result->RIGHT = $right;
		$result->RIGHTBIN = decbin($right);

		if (!$isAccess) $result->error = 'ข้อมูลของ <b>"'.$rs->name.' '.$rs->lname.'"</b> อยู่นอกพื้นที่การดูแลของท่าน หากข้อมูลนี้ไม่ถูกต้อง กรุณาแจ้งผู้ดูแลระบบ';


		if ($options->data == 'info') return $result;



		$stmt = 'SELECT
			  d.*
			, dislevel.`cat_name` `disabilities_level_name`
			, discharge.`cat_name` `discharge_desc`
			, begetting.`cat_name` `begetting_desc`
			, uc.`name` `created_by`
			, um.`name` `modify_by`
			FROM %imed_disabled% d
				LEFT JOIN %users% uc USING(`uid`)
				LEFT JOIN %users% um ON d.`umodify` = um.`uid`
				LEFT JOIN %co_category% dislevel ON dislevel.`cat_id` = d.`disabilities_level`
				LEFT JOIN %co_category% discharge ON discharge.`cat_id` = d.`discharge`
				LEFT JOIN %co_category% begetting ON begetting.`cat_id` = d.`begetting`
			WHERE d.`pid` = :psnid LIMIT 1';

		$result->disabled = mydb::select($stmt,':psnid',$psnId);
		if (!$debug) mydb::clearprop($result->disabled);

		$result->care = (Object) [];
		$result->defect = [];
		$result->qt = [];
		$result->visit = (Object) [];
		$result->poor = (Object) [];
		$result->need = (Object) [];

		if (!$result->disabled->pid) {
			$result->disabled = false;
			$result->care->disabled = false;
		} else {
			$result->care->disabled = true;
		}




		if ($result->disabled) {
			$stmt = 'SELECT
				  d.`defect`+0 `defectid`
				, d.*
				, `consider`+0 `considerid`
				, `kind`+0 `kindid`
				, `begin`+0 `beginid`
				, `cause`+0 `causeid`
				FROM %imed_disabled_defect% d
				WHERE `pid` = :psnid';
			foreach (mydb::select($stmt,':psnid',$psnId)->items as $drs) {
				$result->defect[$drs->defectid] = $drs;
			}
		}


		$stmt = 'SELECT * FROM %imed_care% WHERE `pid` = :psnid';
		foreach (mydb::select($stmt, ':psnid', $psnId)->items as $value) {
			if ($value->careid == _IMED_CARE_DISABLED) $result->care->disabled = true;
			else if ($value->careid == _IMED_CARE_ELDER) $result->care->elder = true;
			else if ($value->careid == _IMED_CARE_REHAB) $result->care->rehab = true;
			else if ($value->careid == _IMED_CARE_WAIT_REHAB) $result->care->waitrehab = true;
			else if ($value->careid == _IMED_CARE_POOR) $result->care->poor = true;
		}

		$stmt = 'SELECT
			  tr.`tr_id`
			, tr.`pid` `psnId`
			, tr.`pid`
			, tr.`uid`, u.`username`, u.`name` `ownerName`
			, u.`name` `poster`
			, tr.`tr_code`
			, tr.`cat_id`
			, c.`cat_name` `cat_id_name`
			, tr.`status`
			, s.`cat_name` `status_name`
			, tr.`ref_id1`
			, tr.`ref_id2`
			, tr.`gis`
			, tr.`detail1`
			, tr.`detail2`
			, tr.`detail3`
			, tr.`detail4`
			, tr.`detail5`
			, tr.`remark`
			, tr.`created`
			FROM %imed_tr% tr
				LEFT JOIN %users% u USING (`uid`)
				LEFT JOIN %co_category% c USING (`cat_id`)
				LEFT JOIN %co_category% s ON s.`cat_id` = tr.`status`
			WHERE `pid` = :psnid
			ORDER BY `created` ASC';

		$iMedTranDbs = mydb::select($stmt, ':psnid', $psnId);

		foreach ($iMedTranDbs->items as $rs) {
			$rs->createdDate = sg_date($rs->created, 'ว ดด ปปปป H:i');
			$result->{$rs->tr_code}[$rs->tr_id] = $rs;
		}

		$patientQtDbs = mydb::select(
			'SELECT * FROM %imed_qt% WHERE `pid` = :psnid ORDER BY `part` ASC, `qid` ASC',
			':psnid', $psnId
		);

		foreach ($patientQtDbs->items as $rs) {
			foreach ($rs as $k => $v) $result->qt[$rs->part][$k] = $v;
		}
		//debugMsg($result,'$result');

		$stmt = 'SELECT * FROM %bigdata% WHERE `keyname` = "imed" AND `fldname` = "person" AND `keyid` = :psnid LIMIT 1';
		$result->personJSON = mydb::select($stmt, ':psnid', $psnId)->flddata;
		$result->person = SG\json_decode($result->personJSON);


		$result->visit->count = 0;
		$result->visit->trans = mydb::select('SELECT * FROM %imed_service% WHERE `pid` = :psnid', ':psnid', $psnId)->items;
		$result->visit->count = count($result->visit->trans);

		$result->poor->count = 0;
		$result->poor->trans = mydb::select('SELECT * FROM %qtmast% WHERE `psnid` = :psnid', ':psnid', $psnId)->items;
		$result->poor->count = count($result->poor->trans);

		$result->need->count = 0;
		$result->need->trans = mydb::select('SELECT * FROM %imed_need% WHERE `psnid` = :psnid', ':psnid', $psnId)->items;
		$result->need->count = count($result->need->trans);

		return $result;
	}

	/*
	* Delete Patient and Transaction
	* Created 2021-08-19
	* Modify  2021-08-22
	*
	* @param Int $psnId
	* @return Object
	*/
	public static function delete($psnId) {
		if (!$psnId) return;

		$result = new stdClass();
		$result->process = array();
		$result->query = array();
		// Start process to move person information

		$isUseInOtherModule = false;


		if (mydb::table_exists('imed_service') && mydb::select('SELECT `pid` FROM %imed_service% WHERE `pid` = :psnid LIMIT 1', ':psnid',$psnId)->pid) {
			$result->query[] = mydb()->_query;
			$isUseInOtherModule = true;
			$result->error = 'Person in service, Please manual remove service and delete again.';
			return $result;
		}

		// Module org : Table org_dos
		if (mydb::table_exists('org_dos') && mydb::select('SELECT `psnid` FROM %org_dos% WHERE `psnid` = :psnid LIMIT 1', ':psnid',$psnId)->psnid) {
			$isUseInOtherModule = true;
		} else 	if (mydb::table_exists('qtmast') && mydb::select('SELECT `psnid` FROM %qtmast% WHERE `psnid` = :psnid LIMIT 1', ':psnid',$psnId)->psnid) {
			$isUseInOtherModule = true;
		} else 	if (mydb::table_exists('poormember') && mydb::select('SELECT `psnid` FROM %poormember% WHERE `psnid` = :psnid LIMIT 1', ':psnid',$psnId)->psnid) {
			$isUseInOtherModule = true;
		} else 	if (mydb::table_exists('person_group') && mydb::select('SELECT `psnid` FROM %person_group% WHERE `psnid` = :psnid LIMIT 1', ':psnid',$psnId)->psnid) {
			$isUseInOtherModule = true;
		}


		$result->isUseInOtherModule = $isUseInOtherModule;

		// TODO: Change psnid of module imed_barthel, imed_care, imed_disabled, imed_disabled_defect, imed_group, imed_patient, imed_patient_gis, imed_qt, imed_service, imed_tr

		// Delete barthel index
		if (mydb::table_exists('imed_barthel')) {
			$stmt = 'DELETE FROM %imed_barthel% WHERE `psnid` = :psnid';
			mydb::query($stmt, ':psnid', $psnId);
			$result->query[] = mydb()->_query;
		}

		// Delete barthel index
		if (mydb::table_exists('imed_care')) {
			$stmt = 'DELETE FROM %imed_care% WHERE `pid` = :psnid';
			mydb::query($stmt, ':psnid', $psnId);
			$result->query[] = mydb()->_query;
		}

		// Delete imed_disabled
		if (mydb::table_exists('imed_disabled')) {
			$stmt = 'DELETE FROM %imed_disabled% WHERE `pid` = :psnid';
			mydb::query($stmt, ':psnid', $psnId);
			$result->query[] = mydb()->_query;
		}

		// Delete imed_disabled_defect
		if (mydb::table_exists('imed_disabled_defect')) {
			$stmt = 'DELETE FROM %imed_disabled_defect% WHERE `pid` = :psnid';
			mydb::query($stmt, ':psnid', $psnId);
			$result->query[] = mydb()->_query;
		}

		// Delete imed_group
		if (mydb::table_exists('imed_group')) {
			$stmt = 'DELETE FROM %imed_group% WHERE `psnid` = :psnid';
			mydb::query($stmt, ':psnid', $psnId);
			$result->query[] = mydb()->_query;
		}

		// Delete imed_need
		if (mydb::table_exists('imed_need')) {
			mydb::query(
				'DELETE FROM %imed_need% WHERE `psnid` = :psnid',
				':psnid', $psnId
			);
			$result->query[] = mydb()->_query;
		}

		// Delete imed_patient
		if (mydb::table_exists('imed_patient')) {
			$stmt = 'DELETE FROM %imed_patient% WHERE `pid` = :psnid';
			mydb::query($stmt, ':psnid', $psnId);
			$result->query[] = mydb()->_query;
		}

		// Delete imed_patient_gis
		if (mydb::table_exists('imed_patient_gis')) {
			$stmt = 'DELETE FROM %imed_patient_gis% WHERE `pid` = :psnid';
			mydb::query($stmt, ':psnid', $psnId);
			$result->query[] = mydb()->_query;
		}

		// Delete imed_qt
		if (mydb::table_exists('imed_qt')) {
			$stmt = 'DELETE FROM %imed_qt% WHERE `pid` = :psnid';
			mydb::query($stmt, ':psnid', $psnId);
			$result->query[] = mydb()->_query;
		}

		// Delete imed_tr
		if (mydb::table_exists('imed_tr')) {
			$stmt = 'DELETE FROM %imed_tr% WHERE `pid` = :psnid';
			mydb::query($stmt, ':psnid', $psnId);
			$result->query[] = mydb()->_query;
		}

		// Delete imed_socialpatient
		if (mydb::table_exists('imed_socialpatient')) {
			$stmt = 'DELETE FROM %imed_socialpatient% WHERE `psnid` = :psnid';
			mydb::query($stmt, ':psnid', $psnId);
			$result->query[] = mydb()->_query;
		}

		// Delete org_dos
		if (mydb::table_exists('org_dos')) {
			$stmt = 'DELETE FROM %org_dos% WHERE `psnid` = :psnid';
			mydb::query($stmt, ':psnid', $psnId);
			$result->query[] = mydb()->_query;
		}

		// Delete org_member
		if (mydb::table_exists('org_member')) {
			$stmt = 'DELETE FROM %org_member% WHERE `psnid` = :psnid';
			mydb::query($stmt, ':psnid', $psnId);
			$result->query[] = mydb()->_query;
		}

		// Delete org_mjoin
		if (mydb::table_exists('org_mjoin')) {
			$stmt = 'DELETE FROM %org_mjoin% WHERE `psnid` = :psnid';
			mydb::query($stmt, ':psnid', $psnId);
			$result->query[] = mydb()->_query;
		}

		// Delete org_morg
		if (mydb::table_exists('org_morg')) {
			$stmt = 'DELETE FROM %org_morg% WHERE `psnid` = :psnid';
			mydb::query($stmt, ':psnid', $psnId);
			$result->query[] = mydb()->_query;
		}

		// Delete poormember
		if (mydb::table_exists('poormember')) {
			$stmt = 'DELETE FROM %poormember% WHERE `psnid` = :psnid';
			mydb::query($stmt, ':psnid', $psnId);
			$result->query[] = mydb()->_query;
		}

		// Delete po_stktr
		if (mydb::table_exists('po_stktr')) {
			$stmt = 'DELETE FROM %po_stktr% WHERE `psnid` = :psnid';
			mydb::query($stmt, ':psnid', $psnId);
			$result->query[] = mydb()->_query;
		}

		// Delete po_stktr
		if (mydb::table_exists('qtmast')) {
			$stmt = 'DELETE tr FROM %qttran% tr INNER JOIN %qtmast% m USING(`qtref`)  WHERE m.`psnid` = :psnid';
			mydb::query($stmt, ':psnid', $psnId);
			$result->query[] = mydb()->_query;

			$stmt = 'DELETE FROM %qtmast% WHERE `psnid` = :psnid';
			mydb::query($stmt, ':psnid', $psnId);
			$result->query[] = mydb()->_query;
		}

		mydb::query('DELETE FROM %bigdata% WHERE `keyname` = "imed" AND `fldname` = "person" AND `keyid` = :psnid', ':psnid', $psnId);
			$result->query[] = mydb()->_query;

		// TODO: Befor delete, Please manual remove photo
		// Remove imed_service
		/*
		if (mydb::table_exists('imed_service')) {
			$stmt = 'UPDATE IGNORE %imed_service% SET `pid` = :useid WHERE `pid` IN (:unused)';
			mydb::query($stmt, ':useid', $useid, ':unused', $unUsedId);
			$ret .= mydb()->_query.'<br />';
		}
		*/



		// Delete unuse member name from person
		if (!$isUseInOtherModule) {
			$stmt = 'DELETE FROM %db_person% WHERE `psnid` = :psnid LIMIT 1';
			mydb::query($stmt, ':psnid', $psnId);
			$result->process[] = 'Delete person id';
			$result->query[] = mydb()->_query;
		}

		return $result;
	}

	/*
	* Get Patients List From User Service
	* Created 2021-08-19
	* Modify  2021-08-22
	*
	* @param Object/Array/JSON $conditions
	* @param Object/Array/JSON $options
	* @return Array
	*/
	public static function items($conditions, $options = '{}') {
		$defaults = '{debug: false, start: 0, item: 10, order: "`visitTimes` DESC, CONVERT(`name` USING tis620) ASC"}';
		$options = SG\json_decode($options, $defaults);
		$debug = $options->debug;

		$result = NULL;

		if (is_string($conditions) && preg_match('/^{/',$conditions)) {
			$conditions = SG\json_decode($conditions);
		} else if (is_object($conditions)) {
			//
		} else if (is_array($conditions)) {
			$conditions = (Object) $conditions;
		} else {
			$conditions = (Object) ['id' => $conditions];
		}

		mydb::where('s.`pid` IS NOT NULL');
		if ($conditions->userId) {
			mydb::where('s.`uid` = :uid AND s.`pid` IS NOT NULL', ':uid', $conditions->userId);
		} else if (i()->ok) {
			mydb::where('s.`uid` = :uid AND s.`pid` IS NOT NULL', ':uid', i()->uid);
		}

		if ($options->item == '*') {
			mydb::value('$LIMIT$','');
		} else {
			mydb::value('$LIMIT$', 'LIMIT '.$options->start.','.$options->item);
		}

		mydb::value('$ORDER$', 'ORDER BY '.$options->order);

		foreach (mydb::select(
			'SELECT
				s.`pid` `psnId`
				, p.`prename`
				, CONCAT(p.`name`," ",p.`lname`) `patientName`
				, COUNT(*) `visitTimes`
				, "" `photo`
				FROM %imed_service% s
					LEFT JOIN %db_person% p ON p.`psnid` = s.`pid`
				%WHERE%
				GROUP BY `psnId`
				$ORDER$
				$LIMIT$
			'
		)->items as $rs) {
			$rs->photo = imed_model::patient_photo($rs->psnId);
			$result[] = $rs;
		}

		if ($debug) debugMsg(mydb()->_query);

		return $result;
	}

	/*
	* Get Patients List From Patient Service Count
	* Created 2021-08-26
	* Modify  2021-08-26
	*
	* @param Object/Array/JSON $conditions
	* @param Object/Array/JSON $options
	* @return Array
	*/
	public static function serviceList($conditions, $options = '{}') {
		$defaults = '{debug: false, start: 0, item: 10, order: "ip.`service` DESC, CONVERT(`name` USING tis620) ASC"}';
		$options = SG\json_decode($options, $defaults);
		$debug = $options->debug;

		$conditions = SG\paramToObject($conditions);
		$result = NULL;

		// debugMsg($conditions, '$condition');

		if ($conditions->userId) mydb::where('ip.`uid` = :userId', ':userId', $conditions->userId);

		if ($options->item == '*') {
			mydb::value('$LIMIT$','');
		} else {
			mydb::value('$LIMIT$', 'LIMIT '.$options->start.','.$options->item);
		}

		mydb::value('$ORDER$', 'ORDER BY '.$options->order);

		$stmt = 'SELECT
			ip.`pid` `psnId`
			, ip.*
			, CONCAT(p.`name`, " ", p.`lname`) `fullname`
			FROM %imed_patient% ip
				LEFT JOIN %db_person% p ON p.`psnid` = ip.`pid`
			%WHERE%
			$ORDER$
			$LIMIT$
		';

		$result = mydb::select($stmt)->items;

		if ($debug) debugMsg(mydb()->_query);

		return $result;
	}

	public static function psycRiskStatus($psnId) {
		$result = mydb::select(
			'SELECT
			CASE
				WHEN `value` <= 9 THEN "green"
				WHEN `value` BETWEEN 10 AND 18 THEN "yellow"
				WHEN `value` >= 19 THEN "red"
			END `status`
			, `value` `smiv_value`
			, `seqId`
			, `qtDate`
			FROM (
				SELECT `psnid`, `value`, `seq` `seqId`, `qtDate`
				FROM %qtmast%
				WHERE `psnId` = :psnId AND `qtform` = "SMIV"
				GROUP BY `psnid`
				ORDER BY `qtdate` DESC, `seq` DESC
			) a
			LIMIT 1
			',
			['psnId' => $psnId]
		);
		return mydb::clearprop($result);
	}
}
?>