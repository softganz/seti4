<?php
/**
* Add member to meeting
*
* @param Integer $doid
* @param String $addtype
* @param Object $post
*/
function r_org_meeting_member_add($doid, $addtype, $post) {
	/**
	* ถ้าไม่มี orgid ให้เช็คว่ามีองค์กรซ้ำไหม ถ้าซ้ำ ให้ใช้ของเก่า ถ้าไม่ซ้ำ ให้เพิ่มใหม่
	* ถ้าชื่อซ้ำ ให้ใช้ชื่อเดิม ถ้าไม่ซ้ำให้เพิ่มชื่อใหม่
	* เพิ่มชื่อในกิจกรรมในฐานะ invite
	* ถ้ายังไม่เคยเข้าร่วมกิจกรรมมาก่อน ให้เพิ่มชื่อในฐานผู้เข้าร่วมกิจกรรมกับองค์กร
	*/

	// Add organization
	if ($post->orgname && empty($post->orgid)) {
		$isDupOrg = mydb::select('SELECT `orgid` FROM %db_org% WHERE `name` = :name LIMIT 1',':name',$post->orgname)->orgid;

		if ($isDupOrg) {
			$post->orgid=$isDupOrg;
		} else {
			$stmt = 'INSERT INTO %db_org%
				(`uid`, `name`, `created`)
				VALUES
				(:uid, :name, :created)';

			mydb::query($stmt, ':name',$post->orgname, ':created', date('U'), ':uid',i()->uid);

			if (!mydb()->_error) {
				$post->orgid = mydb()->insert_id;
				$msg[] = 'เพิ่มองค์กรใหม่ "'.$post->orgname.'"';
			}
		}
	}


	// Add person
	if ($post->fullname) {
		list($name,$lname) = sg::explode_name(' ',$post->fullname);
		$isDupName = mydb::select('SELECT `psnid` FROM %db_person% WHERE `name` = :name AND `lname` = :lname LIMIT 1',':name',$name, ':lname',$lname)->psnid;
		$msg[] = 'เพิ่มชื่อบุคคใหม่ '.$isDupName;
		if ($isDupName) {
			$post->psnid = $isDupName;
		} else {
			$post->name = $name;
			$post->lname = $lname;
			$post->cid = preg_replace('/[^0-9]/', '', trim($post->cid));
			$post->uid = SG\getFirst(i()->uid,NULL);
			$post->created = date('U');
			$addr = SG\explode_address($post->address);
			$post->house = $addr['house'];
			$post->village = $addr['village'];
			$post->zip = $addr['zip'];
			$post->tambon = substr($post->areacode,4,2);
			$post->ampur = substr($post->areacode,2,2);
			$post->changwat = substr($post->areacode,0,2);

			$stmt = 'INSERT INTO %db_person%
				(	`uid`, `prename`, `name`, `lname`, `cid`,
					`house`, `village`, `tambon`, `ampur`, `changwat`, `zip`,
					`phone`, `email`, `created`
				)
				VALUES
				(	:uid, :prename, :name, :lname, :cid,
					:house, :village, :tambon, :ampur, :changwat, :zip,
					:phone, :email, :created
				)';

			mydb::query($stmt, $post);

			if (!mydb()->_error) {
				$post->psnid = mydb()->insert_id;
				$msg[] = 'เพิ่มชื่อบุคคลใหม่ "'.$post->prename.' '.$post->name.' '.$post->lname.'"';
			} else {
				$msg[] = 'Error '.mydb()->_query;
			}
		}
	}



	$dors = mydb::select('SELECT * FROM %org_doings% WHERE `doid` = :doid LIMIT 1',':doid',$doid);

	if ($dors->doid && $post->psnid && $addtype) {

		// Add person to join meeting
		$dosData = new stdClass;
		$dosData->psnid = $post->psnid;
		$dosData->doid = $doid;
		$dosData->addtype = $addtype;
		$dosData->uid = i()->uid;
		$dosData->created = date('U');

		if (in_array($addtype,array('Invite','Register'))) {
			$stmt = 'INSERT INTO %org_dos%
				(`psnid`,`doid`, `uid`, `regtype`, `created`)
				VALUES
				(:psnid, :doid, :uid, :addtype, :created)
				ON DUPLICATE KEY UPDATE
				-- `regtype` = IFNULL(`regtype`, :addtype)
				`regtype` = :addtype
				';
		} else {
			$stmt = 'INSERT INTO %org_dos%
				(`psnid`,`doid`, `uid`, `isjoin`, `regtype`, `created`)
				VALUES
				(:psnid, :doid, :uid, 1, "Walk In", :created)
				ON DUPLICATE KEY UPDATE
				`isjoin` = 1';
		}

		mydb::query($stmt, $dosData);

		//debugMsg(mydb()->_query);
		//debugMsg($dosData,'$dosData');

		$debug .= '<strong>Add person '.$post->psnid.' to join meeting '.$post->doid.' as '.$addtype.'</strong> : '.mydb()->_query.'<br />';


		// Add person to join organization
		$stmt = 'INSERT INTO %org_mjoin%
			  (`orgid`, `psnid`, `uid`, `joindate`, `created`)
			VALUES
			  (:orgid, :psnid, :uid, :joindate, :created)
			ON DUPLICATE KEY UPDATE
			  `psnid` = :psnid ';

		mydb::query($stmt,':orgid',$dors->orgid, ':psnid',$post->psnid, ':uid',i()->uid, ':joindate',date('Y-m-d'), ':created',date('Y-m-d H:i:s'));

		$debug .= '<strong>Add person '.$post->psnid.' to join org '.$dors->orgid.'</strong> : '.mydb()->_query.'<br />';



		// Add person's organization to join organization
		if ($post->orgid) {
			$stmt = 'INSERT INTO %org_ojoin%
				  (`orgid`, `jorgid`, `uid`, `sorder`, `joindate`, `created`)
				VALUES
				  (:orgid, :jorgid, :uid, :jorgid, :joindate, :created)
				ON DUPLICATE KEY UPDATE
				  `jorgid` = :jorgid ';

			mydb::query($stmt,':orgid',$dors->orgid, ':jorgid',$post->orgid, ':uid',i()->uid, ':joindate',date('Y-m-d'), ':created',date('Y-m-d H:i:s'));

			$debug .= '<strong>Add org '.$post->orgid.' to join org '.$dors->orgid.'</strong> : '.mydb()->_query.'<br />';

			// Add person's organization to join organization
			$stmt = 'INSERT INTO %org_morg%
					(`psnid`, `orgid`, `uid`)
				VALUES
					(:psnid, :orgid, :uid)
				ON DUPLICATE KEY UPDATE
					`psnid` = :psnid ';

			mydb::query($stmt,':orgid',$post->orgid, ':psnid',$post->psnid, ':uid',i()->uid);

			$debug .= '<strong>Add person '.$post->psnid.' to member of org '.$post->orgid.'</strong> : '.mydb()->_query.'<br />';
		}
	}

	if ($msg) $post->_msg = $msg;
	if ($debug) $post->_debug = $debug;
	return $post;
}
?>