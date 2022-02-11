<?php
/**
 * imed_model class for Distance Learning for Family Medicine and Primary Care
 *
 * @package imed
 * @version 1.00a1
 * @copyright Copyright (c) 2000-present , The SoftGanz Group By Panumas Nontapan
 * @author Panumas Nontapan <webmaster@softganz.com> , http://www.softganz.com
 * @created 2009-09-22
 * @modify 2012-04-14
 * ============================================
 * This program is free software. You can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License.
 * ============================================
 */

class imed_model {
	/**
	 * Back to main page link
	 *
	 * @return String
	 */
	public static function back2main() {
		return '<a class="back2main" href="'.url('imed/realtime').'">&laquo; กลับสู่หน้าหลัก</a>';
	}

	/**
	 * Get queue status text
	 *
	 * @param Integer $status
	 * @return String
	 */
	public static function queue_status($status) {
	static $items=array(_IMED_WAITING=>'รอคิว',_IMED_CALLING=>'รอพบแพทย์',_IMED_PROCESS=>'กำลังพบแพทย์',_IMED_COMPLETE=>'ตรวจเรียบร้อย');
		return $items[$status];
	}

	/**
	 * Get sex status text
	 *
	 * @param Integer $sex
	 * @return String
	 */
	public static function sex_status($sex) {
	static $items=array(1=>'ชาย',2=>'หญิง');
		return $items[$sex];
	}

	/**
	 * Get queue information
	 *
	 * @param Integer $seq
	 * @return Object
	 */
	public static function get_queue($seq) {
		$stmt='SELECT q.* , p.pcucode , p.hn , p.phn , p.cid , p.name , p.lname , p.address , p.sex , p.birth ,
						d.username dr_uname , d.name dr_name , pcu.username pcu_uname , pcu.name pcu_name
					FROM %imed_service% q
						LEFT JOIN %imed_people% p ON p.pid=q.pid
						LEFT JOIN %users% d ON d.uid=q.did
						LEFT JOIN %users% pcu ON pcu.uid=q.uid
					WHERE seq='.$seq.' LIMIT 1';
		$rs=db_query_object($stmt);
		if (!$rs->_empty) {
			$rs->gid=explode(',',mydb::select('SELECT DISTINCT `gid` FROM %imed_member% WHERE `uid`=:uid',':uid',$rs->uid)->lists->text);
			$rs->mid=explode(',',mydb::select('SELECT `uid` FROM %imed_member% WHERE `gid` IN ('.implode(',',$rs->gid).') AND `mtype` IN ('._MEMBER_MANAGER.','._MEMBER_OWNER.')')->lists->text);
		}
		return $rs;
	}

	/**
	 * Get people information
	 *
	 * @param String $name or Integer $pid
	 * @return Object
	 */
	public static function get_people($name) {
		$name=trim($name);
		$rs=new record_set();
		if (empty($name)) return $rs;
		$stmt='SELECT p.* FROM %imed_people% p ';
		if (is_numeric($name)) $stmt.='WHERE `pid`='.addslashes($name).' ';
		else if (is_string($name)) {
			list($name,$lastname)=sg::explode_name(' ',$name);
			$stmt.='WHERE `name`="'.addslashes($name).'" AND `lname`="'.addslashes($lastname).'" ';
		} else return $rs;
		$stmt.='LIMIT 1';
		$rs=db_query_object($stmt);
		return $rs;
	}

	public static function get_patient_type($id) {
		$result['disabled']=false;
		$result['chronic']=false;
		$result['elder']=false;
		$stmt='SELECT d.* FROM %imed_disabled% d WHERE d.`pid`=:pid AND `discharge` IS NULL LIMIT 1';
		$rs=mydb::select($stmt,':pid',$id);
		if ($rs->_num_rows) $result['disabled']=true;
		$rs=mydb::select('SELECT * FROM %imed_care% WHERE `pid`=:pid AND `careid`=2 AND `status`=1 LIMIT 1',':pid',$id);
		if ($rs->_num_rows) $result['elder']=true;
		$rs=mydb::select('SELECT * FROM %imed_qt% WHERE `pid`=:pid AND `part` LIKE "โรคประจำตัว-%" AND `value`!="" LIMIT 1',':pid',$id);
		if ($rs->_num_rows) $result['chronic']=true;
		//$result[]=print_o($rs,'$rs');
		return $result;
	}
	/**
	 * Get patient information
	 *
	 * @param Integer $id
	 * @return Data Set
	 */
	public static function get_patient($id) {
		$stmt='SELECT
			0 `RIGHT`, 0 `RIGHTBIN`, "" `error`
			, p.psnid, p.cid, p.uid, p.prename, p.name, p.lname, p.nickname, p.sex, p.birth,
			p.educate, p.phone, p.email, p.occupa, cooc.occu_desc, p.aptitude, p.interest,
			p.mstatus, com.cat_name mstatus_desc,
			coe.edu_desc,
			p.`commune`,
			p.house, p.village, p.tambon, p.ampur, p.changwat,
			IFNULL(cosub.subdistname,p.t_tambon) subdistname,
			IFNULL(codist.distname,p.t_ampur) distname,
			IFNULL(copv.provname,p.t_changwat) provname,
			p.zip,

			p.rhouse, p.rvillage,
			p.rtambon, rcosub.subdistname rsubdistname,
			p.rampur, rcodist.distname rdistname,
			p.rchangwat, rcopv.provname rprovname,
			p.rzip,

			p.remark,
			g.gis, CONCAT(X(g.`latlng`),",",Y(g.`latlng`)) latlng, X(g.`latlng`) lat, Y(g.`latlng`) lnt,
			uc.name created_by, p.created created_date, p.modify, p.umodify, um.name modify_by

		FROM %db_person% p
			LEFT JOIN %users% uc ON p.uid=uc.uid
			LEFT JOIN %users% um ON p.umodify=um.uid
			LEFT JOIN %co_educate% coe ON coe.edu_code=p.educate
			LEFT JOIN %co_occu% cooc ON cooc.occu_code=p.occupa
			LEFT JOIN %co_category% com ON p.mstatus=com.cat_id

			LEFT JOIN %co_province% copv ON p.changwat=copv.provid
			LEFT JOIN %co_district% codist ON codist.distid=CONCAT(p.changwat,p.ampur)
			LEFT JOIN %co_subdistrict% cosub ON cosub.subdistid=CONCAT(p.changwat,p.ampur,p.tambon)
			LEFT JOIN %co_village% covi ON covi.villid=CONCAT(p.changwat,p.ampur,p.tambon,IF(LENGTH(p.village)=1,CONCAT("0",p.village),p.village))

			LEFT JOIN %co_province% rcopv ON p.rchangwat=rcopv.provid
			LEFT JOIN %co_district% rcodist ON rcodist.distid=CONCAT(p.rchangwat,p.rampur)
			LEFT JOIN %co_subdistrict% rcosub ON rcosub.subdistid=CONCAT(p.rchangwat,p.rampur,p.rtambon)
			LEFT JOIN %co_village% rcovi ON rcovi.villid = CONCAT(p.rchangwat, p.rampur, p.rtambon, IF(LENGTH(p.rvillage)=1, CONCAT("0", p.rvillage), p.rvillage))

			LEFT JOIN %gis% g ON p.gis=g.gis
		WHERE p.`psnid`=:pid LIMIT 1';
		$rs=mydb::select($stmt,':pid',$id);
		if ($rs->_num_rows) {
			$rs->address=trim($rs->house.($rs->soi?' ซอย'.$rs->soi:'').($rs->road?' ถนน'.$rs->road:'').($rs->village?' หมู่ที่ '.$rs->village:'').($rs->villname?' บ้าน'.$rs->villname:'').($rs->subdistname?' ตำบล'.$rs->subdistname:'').($rs->distname?' อำเภอ'.$rs->distname:'').($rs->provname?' จังหวัด'.$rs->provname:'').($rs->zip?' รหัสไปรษณีย์ '.$rs->zip:''));
			$rs->raddress=trim($rs->rhouse.($rs->rvillage?' หมู่ที่ '.$rs->rvillage:'').($rs->rvillname?' บ้าน'.$rs->rvillname:'').($rs->rsubdistname?' ตำบล'.$rs->rsubdistname:'').($rs->rdistname?' อำเภอ'.$rs->rdistname:'').($rs->rprovname?' จังหวัด'.$rs->rprovname:'').($rs->rzip?' รหัสไปรษณีย์ '.$rs->rzip:''));


			$rs->RIGHT=NULL;
			$rs->RIGHTBIN=NULL;
			$rs->error=NULL;

			$right=0;

			$isOwner=i()->ok && $rs->uid==i()->uid;
			$isAdmin=user_access('administer imeds');
			$isAccess=false;
			$isEdit=false;
			//user_access('administer imeds','edit own imed content',$rs->uid) || $isOwner;
			if ($isAdmin || $isOwner) {
				$isAccess=true;
				$isEdit=true;
			} else  if ($zones=imed_model::get_user_zone(i()->uid,'imed')) {
				$psnRight=imed_model::in_my_zone($zones,$rs->changwat,$rs->ampur,$rs->tambon);
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

			$rs->RIGHT=$right;
			$rs->RIGHTBIN=decbin($right);

			if (!$isAccess) $rs->error='ข้อมูลของ <b>"'.$rs->name.' '.$rs->lname.'"</b> อยู่นอกพื้นที่การดูแลของท่าน หากข้อมูลนี้ไม่ถูกต้อง กรุณาแจ้งผู้ดูแลระบบ';

			$stmt='SELECT d.*,
					dislevel.cat_name disabilities_level_name,
					discharge.cat_name discharge_desc,
					begetting.cat_name begetting_desc,
					uc.name created_by, um.name modify_by
				FROM %imed_disabled% d
					LEFT JOIN %users% uc ON d.uid=uc.uid
					LEFT JOIN %users% um ON d.umodify=um.uid
					LEFT JOIN %co_category% dislevel ON dislevel.cat_id=d.disabilities_level
					LEFT JOIN %co_category% discharge ON discharge.cat_id=d.discharge
					LEFT JOIN %co_category% begetting ON begetting.cat_id=d.begetting
				WHERE d.`pid`=:pid LIMIT 1';
			$rs->disabled=mydb::select($stmt,':pid',$id);

			if ($rs->disabled->_num_rows) {
				$stmt='SELECT d.`defect`+0 `defectid`, d.*, `consider`+0 `considerid`, `kind`+0 `kindid`, `begin`+0 `beginid`, `cause`+0 `causeid` FROM %imed_disabled_defect% d WHERE pid=:pid';
				foreach (mydb::select($stmt,':pid',$id)->items as $drs) $rs->disabled->defect[$drs->defectid]=$drs;
			}

			// Get carer-ผู้ดูแล- list
			// $stmt='SELECT
			// 	tr.`tr_id`, tr.`cat_id`
			// 	, tr.`detail1` `name`, tr.`detail2` `relation`, tr.`detail3` `education`
			// 	, tr.`remark` `address`, tr.`created`
			// 	, c.`cat_name` `cat_id_name`, s.`cat_name` `status_name`
			// 	, u.`name` `poster`
			// 	FROM %imed_tr% tr
			// 		LEFT JOIN %co_category% c USING (`cat_id`)
			// 		LEFT JOIN %co_category% s ON s.`cat_id` = tr.`status`
			// 		LEFT JOIN %users% u USING (`uid`)
			// 	WHERE `pid` = :pid && `tr_code` = "carer"
			// 	ORDER BY `created` ASC';
			// $rs->carer=mydb::select($stmt,':pid',$id)->items;

			$stmt = 'SELECT
				tr.tr_id, tr.pid, tr.uid
				, u.name poster
				, tr.tr_code, tr.cat_id, c.cat_name cat_id_name
				, tr.status,s.cat_name status_name
				, tr.ref_id1, tr.ref_id2, tr.gis, tr.detail1, tr.detail2, tr.detail3
				,  tr.remark, tr.created
				FROM %imed_tr% tr
					LEFT JOIN %users% u USING (uid)
					LEFT JOIN %co_category% c USING (cat_id)
					LEFT JOIN %co_category% s ON s.cat_id=tr.status
				WHERE `pid`=:pid
				ORDER BY `created` ASC';
			$dbs=mydb::select($stmt,':pid',$id);
			foreach ($dbs->items as $trs) {
				$rs->{$trs->tr_code}[$trs->tr_id]=$trs;
			}

			$stmt='SELECT * FROM %imed_qt% WHERE `pid`=:pid ORDER BY `part` ASC, `qid` ASC';
			$dbs=mydb::select($stmt,':pid',$id);
			foreach ($dbs->items as $trs) {
				foreach ($trs as $k=>$v) $rs->qt[$trs->part][$k]=$v;
			}
			//			$rs->q=$dbs;
		}
		return $rs;
	}

	/**
	 * Is member of group or type
	 *
	 * @param Integer $type or String $groupname
	 * @param Integer $uid
	 */
	public static function is_member_of($id,$uid=NULL) {
		static $groups=array();
		static $types=array();
		if (is_numeric($id)) $type=$id;
		else if (is_string($id)) $groupname=$id;

		if (!isset($uid)) $uid = i()->uid;
		if (empty($uid)) return false;

		if (!isset($types[$uid])) {
			$stmt='SELECT DISTINCT `mtype` FROM %imed_member% WHERE uid=:uid';
			$dbs=mydb::select($stmt,':uid',$uid);
			$types[$uid]=array();
			foreach ($dbs->items as $rs) $types[$uid][$rs->mtype]=$rs->mtype;
		}

		if ($groupname && !isset($groups[$groupname])) {
			$gid=mydb::select('SELECT `gid` FROM %imed_group% WHERE `groupname`=:groupname LIMIT 1',':groupname',$groupname)->gid;
			$stmt='SELECT `uid`,`mtype` FROM %imed_member% WHERE `gid`=:gid';
			$dbs=mydb::select($stmt,':gid',$gid);
			$groups[$groupname]=array();
			foreach ($dbs->items as $rs) $groups[$groupname][$rs->uid]=$rs->mtype;
		}
		if ($groupname) {
		//			echo 'return is member of group '.$groupname.'='.$groups[$groupname][$uid].'<br />';
			return $groups[$groupname][$uid];
		} else if ($type) {
		//			echo 'return is member of type '.$type.'='.$types[$uid][$type].'<br />';
			return $types[$uid][$type];
		} else {
		//			echo 'return is member ='.print_r($types[$uid],1).'<br />';
			return $types[$uid];
		}
	}

	/**
	 * Get member type name
	 *
	 * @param Integer $mtype
	 * @return String
	 */
	public static function get_membertype_name($mtype) {
		if ($mtype==_MEMBER_MANAGER) $ret='ผู้จัดการ';
		else if ($mtype==_MEMBER_OWNER) $ret='เจ้าของ';
		else if ($mtype==_MEMBER_BANNED) $ret='ห้ามใช้งาน';
		else if ($mtype==_MEMBER_DOCTOR) $ret='แพทย์';
		else if ($mtype==_MEMBER) $ret='สถานีอนามัย';
		return $ret;
	}

	/**
	 * Get category
	 *
	 * @param String $cat_group
	 * @param Array
	 */
	public static function get_category($cat_group,$cat_id=NULL,$cat_name=NULL,$option=NULL) {
		$default=NULL;
		if ($cat_group=='education') {
			$dbs=mydb::select('SELECT * FROM %co_educate%');
			foreach ($dbs->items as $rs) $ret[$rs->edu_code]=$rs->edu_desc;
		} else if ($cat_group=='occupation') {
			$dbs=mydb::select('SELECT * FROM %co_occu%');
			foreach ($dbs->items as $rs) $ret[$rs->occu_code]=$rs->occu_desc;
		} else if ($cat_group=='religion') {
			$dbs=mydb::select('SELECT * FROM %co_religion%');
			foreach ($dbs->items as $rs) $ret[$rs->reli_code]=$rs->reli_desc;
		} else {
			$dbs=mydb::select('SELECT * FROM %co_category% WHERE `cat_group`=:cat_group ORDER BY CONVERT(`cat_name` USING tis620) ASC',':cat_group',$cat_group);
			foreach ($dbs->items as $rs) {
				$ret[$rs->cat_id]=$rs->cat_name;
				if (!$default && $rs->cat_default) $default=$rs->cat_id;
			}
		}
		if ($option=='default') $ret=$default;
		return $ret;
	}

	/**
	 * Get service seq information
	 *
	 * @param Int $seq
	 * @return Record Set
	 */
	public static function get_seq($seq) {
		// Select service for unit render
		$stmt='SELECT s.*, u.username, u.name , CONCAT(p.name," ",p.lname) patient_name, GROUP_CONCAT(`file`) photos
			FROM %imed_service% s
				LEFT JOIN %users% u USING (uid)
				LEFT JOIN %db_person% p ON p.psnid=s.pid
				LEFT JOIN %imed_files% f ON f.seq=s.seq AND f.type="photo"
			WHERE s.`seq`=:seq LIMIT 1';
		$rs=mydb::select($stmt,':seq',$seq);
		return $rs;
	}


	/**
	 * Get disabled person photo
	 *
	 * @param String $id
	 * @return String
	 */
	public static function patient_photo($id=NULL) {
		$photo_file = 'upload/imed/profile-'.$id.'.jpg';
		if (file_exists($photo_file)) {
			$time = filemtime($photo_file);
			return _URL.$photo_file.'?'.$time;
		} else {
			return _img.'photography.png';
		}
		return $photo;
	}

	/**
	 * Get imed upload photo
	 *
	 * @param String $file
	 * @return String
	 */
	public static function upload_photo($file = NULL, $options = '{}') {
		$photoInfo = model::get_photo_property($file, 'upload/imed/photo');
		return $photoInfo;
	}

	/** Delete patient and all information
	 *
	 * @param Integer $id
	 * @return Boolean
	 */
	public static function delete_patient($id) {
		$rs=imed_model::get_patient($id);
		if ($rs->_empty) return false;
		$result->complete=false;
		$result->error=false;

		// Delete imed_tr
		$stmt='DELETE FROM %imed_care% WHERE `pid`=:pid';
		mydb::query($stmt,':pid',$id);
		$result->query[]=mydb()->_query;

		// Delete imed_qt
		$stmt='DELETE FROM %imed_qt% WHERE `pid`=:pid';
		mydb::query($stmt,':pid',$id);
		$result->query[]=mydb()->_query;

		// Delete imed_tr
		$stmt='DELETE FROM %imed_tr% WHERE `pid`=:pid';
		mydb::query($stmt,':pid',$id);
		$result->query[]=mydb()->_query;

		// Delete patient gis
		$stmt='DELETE FROM %imed_patient_gis% WHERE `pid`=:pid';
		mydb::query($stmt,':pid',$id);
		$result->query[]=mydb()->_query;

		// Delete gis
		if ($rs->gis) {
			$stmt='DELETE FROM %gis% WHERE `gis`=:gis LIMIT 1';
			mydb::query($stmt,':gis',$rs->gis);
			$result->query[]=mydb()->_query;
		}

		// Delete disabled defect
		$stmt='DELETE FROM %imed_disabled_defect% WHERE `pid`=:pid';
		mydb::query($stmt,':pid',$id);
		$result->query[]=mydb()->_query;

		// Delete service upload file
		$filesdb=mydb::select('SELECT f.file FROM %imed_service% s LEFT JOIN %imed_files% f USING(`seq`) WHERE `pid`=:pid AND f.file IS NOT NULL',':pid',$id);
		foreach ($filesdb->items as $frs) {
			if ($frs->file) {
				$fileloc='upload/imed/photo/'.$frs->file;
				if (file_exists($fileloc) && is_file($fileloc)) {
					unlink($fileloc);
					$result->files[]=$fileloc;
				}
			}
		}

		// Delete service file
		$stmt='DELETE f.* FROM %imed_files% f LEFT JOIN %imed_service% s USING (`seq`) WHERE s.pid=:pid';
		mydb::query($stmt,':pid',$id);
		$result->query[]=mydb()->_query;

		// Delete service
		$stmt='DELETE FROM %imed_service% WHERE `pid`=:pid';
		mydb::query($stmt,':pid',$id);
		$result->query[]=mydb()->_query;

		// Delete patient profile photo
		$profile_photo='upload/imed/profile-'.$id.'.jpg';
		if (file_exists($profile_photo) && is_file($profile_photo)) {
			unlink($profile_photo);
			$result->files[]=$profile_photo;
		}

		// Delete disabled
		$stmt='DELETE FROM %imed_disabled% WHERE `pid`=:pid';
		mydb::query($stmt,':pid',$id);
		$result->query[]=mydb()->_query;

		// Delete patient
		$stmt='DELETE FROM %imed_patient% WHERE `pid`=:pid';
		mydb::query($stmt,':pid',$id);
		$result->query[]=mydb()->_query;

		// Delete person
		$stmt='DELETE FROM %db_person% WHERE `psnid`=:psnid LIMIT 1';
		mydb::query($stmt,':psnid',$id);
		$result->query[]=mydb()->_query;

		$result->complete=true;
		return $result;
	}

	/**
	 * Create input
	 *
	 */
	public static function make_box($inputType,$key,$items=array(),$rs,$is_edit) {
		$i=1;
		$ret='';
		foreach ($items as $v) {
			$k=$inputType=='radio'?$key:$key.'.'.($i++);
			unset($posttext);
			if (is_array($v)) {
				$posttext=$v[1];
				$v=$v[0];
			}
			switch ($inputType) {
				case 'radio' :
					$ret.='<input type="radio" name="'.$k.'" '.($is_edit?'':'disabled="disabled" ').'group="qt" fld="'.$k.'" tr="'.$rs->qt[$k]['qid'].'" value="'.$v.'" '.($rs->qt[$k]['value']==$v ? ' checked="checked"':'').' /> '.$v.$posttext.'<br />'._NL;
					break;
				case 'checkbox' :
					$ret.='<input type="checkbox" name="'.$k.'" '.($is_edit?'':'disabled="disabled" ').'group="qt" fld="'.$k.'" tr="'.$rs->qt[$k]['qid'].'" value="'.$v.'" '.($rs->qt[$k]['value']!='' ? ' checked="checked"':'').' /> '.$v.$posttext.'<br />'._NL;

					break;
			}
		}
		return $ret;
	}

	public static function side_panel($submenu) {
		$ui=new ui();
		$ret.='<div id="sidePanel"><div id="panelHandle"><h2>เมนู</h2></div>
		<div id="panelContent">
		'.$submenu.'<hr />'.$ui->build('ul').'
		</div>
		</div>';
		return $ret;
	}

	public static function get_user_zone($uid,$module=NULL,$refid=NULL,$options='{}') {
		$defaults='{debug:false}';
		$options=sg_json_decode($options,$defaults);
		$debug=$options->debug;

		$zones=array();
		mydb::where('`uid`=:uid',':uid',$uid);
		if ($module) mydb::where('`module`=:module',':module',$module);
		if ($refid) mydb::where('`refid`=:refid',':refid',$refid);
		$stmt='SELECT z.`zone`, z.`module`, z.`refid`, z.`right`, s.`subdistname`, cod.`distname`, cop.`provname` FROM %db_userzone% z
				LEFT JOIN %co_subdistrict% s ON s.`subdistid`=z.`zone`
				LEFT JOIN %co_district% cod ON cod.`distid`=SUBSTR(z.`zone`,1,4)
				LEFT JOIN %co_province% cop ON cop.`provid`=SUBSTR(z.`zone`,1,2)
			%WHERE%
			ORDER BY z.`zone` ASC;
			';
		$dbs=mydb::select($stmt);

		// Return all zone
		if (empty($module) && empty($refid)) {
			foreach ($dbs->items as $rs) $zones[]=$rs;
		} else if ($module) {
			foreach ($dbs->items as $rs) $zones[$rs->zone]=$rs;
		} else {
			foreach ($dbs->items as $rs) $zones[$rs->zone]=$rs;
		}
		return $zones;
	}

	public static function in_my_zone($zones,$changwat,$ampur,$tambon) {
		$right=NULL;
		if ($changwat && $ampur && $tambon && array_key_exists($changwat.$ampur.$tambon,$zones)) {
			$right=$zones[$changwat.$ampur.$tambon];
		} 	else if ($changwat && $ampur && array_key_exists($changwat.$ampur,$zones)) {
			$right=$zones[$changwat.$ampur];
		} else if ($changwat && array_key_exists($changwat,$zones)) {
			$right=$zones[$changwat];
		}
		//		echo $changwat.','.$ampur.','.$tambon.print_o($zones,'$zones',1).print_o($right,'$right',1);
		return $right;
	}

	/**
	* Show qt item to inline edit
	*
	* @param Array $qtList
	* @return String
	*/
	public static function qt($key, $qtList, $data, $isEdit, $textShow = NULL) {
		$qtItem = $qtList[$key];

		if (!$qtItem) return '<span class="notify">ไม่มีแบบสอบถามรหัส '.$key.'</span>';

		//debugMsg('key = '.$key.'<br />'.print_o($qtItem,'$qtItem').print_o($data[$key],'$data'));

		$para = array();
		$part = '';

		$para['fld'] = $qtItem['fld'] ? $qtItem['fld'] : $key;

		if ($qtItem['group'] == 'qt') {
			$text = $data[$key]['value'];
			$part = $key;
			if (!isset($data[$key]) || (i()->ok && $data[$key]['ucreated'] == i()->uid)) $isEdit = true;
		} else if ($qtItem['fld']) {
			$text = $data->{$qtItem['fld']};
		} else if (isset($textShow)) {
			$text = $textShow;
		} else if (is_array($data) || is_object($data)) {
		} else {
			$text = $data;
		}


		if ($qtItem['group']) $para['group'] = $qtItem['group'].($part ? ':'.$part : '');
		if ($qtItem['group'] == 'qt') $para['tr'] = $data[$key]['qid'];
		if ($qtItem['class']) $para['class'] = $qtItem['class'];
		if ($qtItem['button']) $para['button'] = $qtItem['button'];
		if ($qtItem['ret']) $para['ret'] = $qtItem['ret'];
		if ($qtItem['callback']) $para['callback'] = $qtItem['callback'];
		if ($qtItem['tr']) $para['tr'] = $qtItem['tr'];
		if ($qtItem['desc']) $para['desc'] = $qtItem['desc'];
		if ($qtItem['onblur']) $para['onblur'] = $qtItem['onblur'];
		if ($qtItem['options']) $para['options'] = $qtItem['options'];
		if ($qtItem['done']) $para['done'] = $qtItem['done'];

		// $para['value'] = $data;
		$para['value'] = $text;

		if (in_array($qtItem['type'],array('radio','checkbox'))) {
			$options = is_string($qtItem['option']) ? explode(',', $qtItem['option']) : $qtItem['option'];
			if ($qtItem['pretext']) $ret.=$qtItem['pretext'].($qtItem['pretext']?'<sup class="tooltip" title="'.$key.'">?</sup> ':'');
			foreach ($options as $k => $label) {
				$para['value'] = $text;
				//$ret.='<br />key='.$key.print_o($para,'$para').print_o($qtItem,'$qtItem');//.print_o($data[$key],'$data');
				if ($qtItem['display'] == 'block') $para['container']['class'] = '-block';

				$ret .= view::inlineedit(
					$para,
					$label,
					$isEdit,
					$qtItem['type']
				);
			}
			//$ret=rtrim($ret,'<br />'._NL);
		} else {
			$ret .= $qtItem['pretext']
				.($qtItem['pretext']?'<sup class="tooltip" title="'.$key.'">?</sup> ':'')
				.view::inlineedit(
					$para,
					$text,
					$isEdit,
					$qtItem['type'],
					$qtItem['option']
				)
				.(isset($qtItem['posttext'])?' '.$qtItem['posttext']:'');
			// $ret.='<br />key='.$key.print_o($qtItem,'$qtItem');
			//$ret.='<br />'.print_o($para,'$para');
		}
		//$ret.=htmlspecialchars($ret);
		$ret.=_NL;
		return $ret;
	}

} // end of class imed_model
?>