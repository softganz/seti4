<?php
/**
 * project class for project management
 *
 * @package project
 * @version 0.22
 * @copyright Copyright (c) 2000-present , The SoftGanz Group By Panumas Nontapan
 * @author Panumas Nontapan <webmaster@softganz.com> , http://www.softganz.com
 * @created 2010-05-25
 * @modify 2013-03-12
 * ============================================
 * This program is free software. You can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License.
 * ============================================
 */

class project_model {

	/**
	 * Get project set
	 * @return Array
	*/
	public static function get_project_set() {
		$prset=array();
		foreach (model::get_taxonomy_tree(cfg_db('project.set.vid')) as $item) {
			$prset[$item->tid]=$item;
		}
		return $prset;
	}

	/**
	 * Is manager of topic
	 *
	 * @param Integer $tpid
	 * @param Integer $uid
	 */
	public static function is_owner_of($tpid,$uid=NULL) {
		static $items=array();
		if (!isset($uid)) $uid=i()->uid;
		if (!isset($items[$tpid])) {
			$stmt = 'SELECT `uid` FROM %topic_user% WHERE `tpid` = :tpid AND `membership` IN ("OWNER") ';
			$dbs=mydb::select($stmt,':tpid',$tpid);
			$items[$tpid]=array();
			foreach ($dbs->items as $rs) $items[$tpid][$rs->uid]=$rs->uid;
		}
		return array_key_exists($uid,$items[$tpid]);
	}

	/**
	 * Is manager of topic
	 *
	 * @param Integer $tpid
	 * @param Integer $uid
	 */
	public static function is_trainer_of($tpid,$uid=NULL) {
		static $items=array();
		if (!isset($uid)) $uid=i()->uid;
		if (!isset($items[$tpid])) {
			$stmt='SELECT `uid` FROM %topic_user% WHERE `tpid`=:tpid AND `membership` IN ("Trainer") ';
			$dbs=mydb::select($stmt,':tpid',$tpid);
			$items[$tpid]=array();
			foreach ($dbs->items as $rs) $items[$tpid][$rs->uid]=$rs->uid;
		}
		if (empty($tpid)) {
			$rs=mydb::select('SELECT `tpid`,`uid`,`membership` FROM %topic_user% WHERE `uid`=:uid AND `membership`="Trainer" LIMIT 1',':uid',$uid);
			return !$rs->_empty;
		}
		return array_key_exists($uid,$items[$tpid]);
	}

	/**
	 * Is ป้อนความสอดคล้องของโครงการ
	 *
	 * @param $tpid
	 * @ return boolean
	 */
	public static function is_inputrelation($tpid) {
		import('model:org.php');

		$stmt='SELECT tg.`tid`, tg.`taggroup`, tr.`rate1` FROM %tag% tg LEFT JOIN %project_tr% tr ON tr.`parent`=tg.`tid` WHERE tr.`tpid`=:tpid AND tr.`formid`="info" AND tr.`part`="rel" AND tr.`rate1`=1 GROUP BY `taggroup`';
		$dbs=mydb::select($stmt,':tpid',$tpid);
		$result=$dbs->_num_rows>=3;
		//print_o($dbs,'$dbs',1);
		return $result;
	}

	public static function get_topic($tpid) {
		$topic=model::get_topic_by_id($tpid);
		$topic->project=project_model::get_project($tpid);
		return $topic;
	}

	/**
	 * Get project information
	 *
	 * @param Integer $tpid
	 * @return Resord Set
	 */
	public static function get_project($tpid) {
		import('model:org.php');
		$stmt = 'SELECT
			  t.`uid`, t.`orgid`, t.`title`
			, prset.`title` projectset_name
			, t.`status` `flag`
			, p.*, p.`project_status`+0 project_statuscode
			, o.`shortname` orgShortName, o.`name` `orgName`, op.`name` `orgParent`
			, covl.`villname`
			, cosd.`subdistname`
			, cod.`distname`
			, cop.`provname`
			, AsText(p.`location`) location, X(p.`location`) lat, Y(p.`location`) lnt
			, d.`tpid` `proposalId`
			FROM %project% p
				LEFT JOIN %topic% t USING(`tpid`)
				LEFT JOIN %project_dev% d ON d.`tpid`=p.`tpid`
				LEFT JOIN %db_org% o ON o.`orgid`=t.`orgid`
				LEFT JOIN %db_org% op ON op.`orgid`=o.`parent`
				LEFT JOIN %topic% prset ON prset.`tpid`=p.`projectset`
				LEFT JOIN %co_province% cop ON p.`changwat`=cop.`provid`
				LEFT JOIN %co_district% cod ON cod.distid=CONCAT(p.changwat,p.ampur)
				LEFT JOIN %co_subdistrict% cosd ON cosd.subdistid=CONCAT(p.changwat,p.ampur,p.tambon)
				LEFT JOIN %co_village% covl ON covl.villid=CONCAT(p.changwat,p.ampur,p.tambon, LPAD(p.village, 2, "0"))
			WHERE p.`tpid` = :tpid LIMIT 1';

		$rs = mydb::select($stmt,':tpid',$tpid);

		if ($rs->_empty) return NULL;

		mydb::clearprop($rs);

		$rs->areaName=SG\implode_address($rs);

		if ($rs->date_from=='0000-00-00') $rs->date_from='';
		if ($rs->date_end=='0000-00-00') $rs->date_end='';

		$rs->lockReportDate=project_model::get_lock_report_date($tpid);

		$rs->RIGHT = 0;

		$membershipList = array();

		foreach (mydb::select('SELECT * FROM %topic_user% WHERE `tpid` = :tpid',':tpid',$tpid)->items as $item) {
			$membershipList[$item->uid] = strtoupper($item->membership);
		}
		$rs->membershipType = $membershipList[i()->uid];

		/*
		$rs->isAdmin = user_access('administer projects') ;
		$rs->isOwner = project_model::is_owner_of($rs->tpid);
		$rs->isTrainer = project_model::is_trainer_of($rs->tpid);
		$rs->isRight = $isAdmin || $rs->isOwner || $rs->isTrainer;

		$rs->isEdit=false;
		$rs->isEdit=$rs->project_statuscode==1 && $rs->isRight;
		$rs->isEditDetail=$rs->isAdmin || ($rs->isEdit && $rs->flag!=_LOCKDETAIL);

		if ($rs->isAdmin) $rs->RIGHT=$rs->RIGHT | _IS_ADMIN;
		if ($rs->isOwner) $rs->RIGHT=$rs->RIGHT | _IS_OWNER;
		if ($rs->isTrainer) $rs->RIGHT=$rs->RIGHT | _IS_TRAINER;
		if ($rs->isRight) $rs->RIGHT=$rs->RIGHT | _IS_ACCESS;
		if ($rs->isEdit) $rs->RIGHT=$rs->RIGHT | _IS_EDITABLE;
		if ($rs->isEditDetail) $rs->RIGHT=$rs->RIGHT | _IS_EDITDETAIL;
		$rs->RIGHTBIN=decbin($rs->RIGHT);
		*/

		$rs->isOwner = i()->ok
										&& ($rs->uid == i()->uid || $rs->membershipType == 'OWNER');
		$rs->isTrainer = i()->ok && $rs->membershipType == 'TRAINER';
		$rs->isAdmin = user_access('administer projects')
			|| (i()->ok && $rs->membershipType == 'MANAGER')
			|| (i()->ok && $rs->orgid && OrgModel::officerType($rs->orgid, i()->uid) == 'ADMIN');
		$rs->isRight = $rs->isOwner || $rs->isTrainer || $rs->isAdmin;

		$rs->isAccess = user_access('access projects') || $rs->isRight;

		$rs->isEdit = $rs->project_statuscode == 1 && ($rs->isRight || $rs->isOwner || $rs->isTrainer);

		$rs->isEditDetail = $rs->isAdmin || ($rs->isEdit && $rs->flag != _LOCKDETAIL);

		if ($rs->isAdmin) $right = $right | _IS_ADMIN;
		if ($rs->isOwner) $right = $right | _IS_OWNER;
		if ($rs->isTrainer) $right = $right | _IS_TRAINER;
		if ($rs->isRight) $right = $right | _IS_ACCESS;
		if ($rs->isEdit) $right = $right | _IS_EDITABLE;
		if ($rs->isEditDetail) $right = $right | _IS_EDITDETAIL;


		$rs->RIGHT = $right;
		$rs->RIGHTBIN = decbin($right);

		$rs->membership = $membershipList;

		return $rs;
	}

	/**
	 * Get project transaction
	 * @parem Integer $tpid
	 * @param Integer $period
	 * @return Array of record set
	 */
	public static function get_tr($tpid='',$formid='',$period=NULL) {
		if ($formid && strpos($formid,':')) {
			list($formid, $part) = explode(':', $formid);
		}
		if ($formid && strpos($formid, ',')) {

		}

		mydb::where('tr.`tpid` = :tpid AND tr.`formid` IN ( :formid )', ':tpid', $tpid, ':formid', 'SET-STRING:'.$formid);
		if ($part) mydb::where('`part` = :part', ':part',$part);
		if ($period) mydb::where('tr.`period` = :period', ':period',$period);
		$stmt='SELECT tr.*, c.`title` activity, c.`from_date`, c.`to_date`, u.`username`, u.`name` `posterName`
						FROM %project_tr% tr
							LEFT JOIN %calendar% c ON c.`id`=tr.`calid`
							LEFT JOIN %users% u USING(`uid`)
						%WHERE%
						ORDER BY tr.`trid` ASC';
		$dbs = mydb::select($stmt,$para);
		$items = [];
		foreach ($dbs->items as $rs) {
			$items[$rs->part][$rs->trid] = $rs;
		}
		$dbs->items = $items;
		return $dbs;
	}

	/**
	 * Get period information
	 * @param Integer $tpid
	 * @param Integer $period
	 * @return Record Set
	 */
	public static function get_period($tpid, $period = NULL) {
		$stmt = 'SELECT
			t.`tpid`, t.`trid`, t.`period`, t.`flag`, t.`uid`,
			t.`date1` from_date, t.`date2` to_date, t.`num1` budget,
			t.`detail1` report_from_date, t.`detail2` report_to_date,
			t.`text1` note_owner,
			t.`text2` note_complete,
			t.`text3` note_trainer,
			t.`text4` note_hsmi,
			t.`text5` note_sss,
			t.`created`, t.`modified`, t.`modifyby`
			FROM %project_tr% t
			WHERE `tpid` = :tpid AND `formid` = "info" AND `part` = "period"'.($period?' AND `period`=:period':'')
			.' ORDER BY `period` ASC'
			.($period?' LIMIT 1':'').';
			-- {reset: false}';

		$dbs = mydb::select($stmt,':tpid',$tpid, ':period',$period);

		if ($period) {
			$result = (Object) [];
			foreach ($dbs as $k=>$v) if (substr($k,0,1)!='_') $result->{$k}=$v;
		} else {
			$result = [];
			foreach ($dbs->items as $key=>$rs) $result[$rs->period]=$rs;
		}
		return $result;
	}

	/**
	 * Get project information
	 * @param Integer $tpid
	 * @return Object
	 */
	public static function get_info($tpid) {
		if (empty($tpid)) return NULL;

		$result = (Object) [
			'project' => project_model::get_project($tpid),
			'summary' => NULL,
			'mainact' => NULL,
			'activity' => NULL,
			'calendar' => NULL,
		];

		// Get objective
		$stmt='SELECT
			o.`tpid`, o.`trid`
			, o.`refid`
			, o.`parent` objectiveType, ot.`name` `objectiveTypeName`
			, o.`text1` title, o.`text2` indicator
			, o.`uid`, o.`created`, o.`modified`, o.`modifyby`
			FROM %project_tr% o
				LEFT JOIN %tag% ot ON ot.`taggroup`="project:objtype" AND ot.`catid`=o.`parent`
			WHERE o.`tpid`=:tpid AND o.`formid`="info" AND o.`part`="objective"
			ORDER BY o.`trid` ASC';
		$dbs=mydb::select($stmt,':tpid', $tpid);
		foreach ($dbs->items as $rs) $result->objective[$rs->trid]=$rs;

		$mainact=project_model::get_main_activity($tpid,'owner');
		$result->summary=$mainact->summary;
		$result->mainact=$mainact->info;
		$result->activity=$mainact->activity;
		$result->calendar=$mainact->calendar;

		return $result;
	}

	/**
	 * Get project main activity
	 * @param
	 * @return Array Set
	 */
	public static function get_main_activity($tpid=NULL, $prowner=NULL, $period=NULL, $trid=NULL) {
		$result = (Object) [
			'summary' => (Object) [],
			'info' => [],
			'activity' => [],
		];

		// Get main activity
		$stmt='SELECT
				  a.`tpid`
				, a.`trid`
				, a.`uid`
				, a.`sorder`
				, a.`parent` `objectiveId`
				, o.`text1` `objectiveTitle`
				, a.`flag`
				, a.`num1` `budget`
				, a.`num2` `target`
				, a.`num3` `targetChild`
				, a.`num4` `targetTeen`
				, a.`num5` `targetWorker`
				, a.`num6` `targetElder`
				, a.`num7` `targetDisabled`
				, a.`num8` `targetWoman`
				, a.`num9` `targetMuslim`
				, a.`num10` `targetWorkman`
				, a.`num11` `targetOtherman`
				, a.`detail1` `title`
				, a.`text1` `desc`
				, a.`text2` `indicator`
				, a.`detail2` `timeprocess`
				, a.`date1` `fromdate`
				, a.`date2` `todate`
				, a.`text3` `output`
				, a.`text6` `outcome`
				, a.`text4` `copartner`
				, a.`text5` `budgetdetail`
				, a.`detail3` `targetOtherDesc`
				, 0 `totalCalendar`
				, 0 `totalActitity`
				, 0 `totalBudget`
				, 0 `totalExpense`
				, a.`created`
				, a.`modified`
				, a.`modifyby`
				, a.`parent`
				, IFNULL(GROUP_CONCAT(po.`parent`),a.`parent`) parentObjectiveId
				, GROUP_CONCAT(CONCAT(po.`trid`,"=",po.`parent`) SEPARATOR "|") parentObjectiveList
				, GROUP_CONCAT(CONCAT(po.`parent`,"=",IFNULL(pot.`text1`,"")) SEPARATOR "|") parentObjective
			FROM %project_tr% a
				LEFT JOIN %project_tr% o ON o.`trid`=a.`parent`
				LEFT JOIN %project_tr% po ON po.`tpid`=a.`tpid` AND po.`gallery`=a.`trid` AND po.`formid`="info" AND po.`part`="actobj"
				LEFT JOIN %project_tr% pot ON pot.`trid`=po.`parent`
			WHERE '.($trid?'a.`trid`=:trid':'a.`tpid`=:tpid')
				.' AND a.`formid`="info" AND a.`part`="mainact"
			GROUP BY a.`trid`
			ORDER BY a.`sorder` ASC, `objectiveId` ASC';
		$mainActivityDbs=mydb::select($stmt,':tpid',$tpid,':trid',$trid);

		if ($trid) return $mainActivityDbs->items[0];

		foreach ($mainActivityDbs->items as $rs) {
			$result->info[$rs->trid]=$rs;
			$result->summary->target+=$rs->target;
			$result->summary->budget+=$rs->budget;
		}

		$options=array('owner'=>$prowner,'period'=>$period);
		$activitys=R::Model('project.activity.get.bytpid',$tpid,$options);
		//debugMsg($activitys,'$activitys');
		//$activitys=project_model::get_activity($tpid,$prowner,$period);

		foreach ($activitys->items as $rs) {
			$result->summary->expense += $rs->exp_total;
			$result->summary->activity++;

			$result->activity[$rs->mainact][] = $rs;
			if (!isset($result->info[$rs->mainact])) $result->info[$rs->mainact] = (Object) [];
			$result->info[$rs->mainact]->totalExpense += $rs->exp_total;
			$result->info[$rs->mainact]->totalActitity++;
		}

		$calendar=project_model::get_calendar($tpid,NULL,$prowner);
		foreach ($calendar->items as $rs) {
			$result->calendar[$rs->mainact][] = $rs;
			$result->summary->calendar++;
			$result->summary->totalBudget += $rs->budget;

			if (!isset($result->info[$rs->mainact])) $result->info[$rs->mainact] = (Object) [];
			$result->info[$rs->mainact]->totalCalendar++;
			$result->info[$rs->mainact]->totalBudget += $rs->budget;
		}

		return $result;
	}

	/**
	 * Get project activity
	 * @param
	 * @return Data Set
	 */
	public static function get_activity($tpid=NULL, $prowner=NULL, $period=NULL,$para=array()) {
		if (is_array($tpid)) {
			$para=$tpid;
			unset($tpid);
			if (isset($para['tpid'])) $tpid=$para['tpid'];
		}
		if (empty($tpid)) return;

		$order=SG\getFirst($para['order'],'tr.`date1` ASC');

		$where=array();
		if ($tpid) $where=sg::add_condition($where, 'tr.tpid IN (:tpid)', 'tpid','SET:'.$tpid);
		$where=sg::add_condition($where, 'tr.formid=:formid', 'formid','activity');
		if ($para['trid']) $where=sg::add_condition($where, 'tr.trid=:trid', 'trid',$para['trid']);
		if (is_string($prowner)) $where=sg::add_condition($where, 'tr.`part`=:prowner', 'prowner',$prowner);
		else if (is_numeric($prowner)) $where=sg::add_condition($where, 'tr.`part`=:prowner', 'prowner',$prowner==_PROJECT_OWNER_ACTIVITY?'owner':'trainer');
		else if (is_array($prowner)) {
			$activityArray=$prowner;
			unset($prowner);
			$where=sg::add_condition($where,'tr.trid IN (:activityArray)','activityArray',$activityArray);
		}
		//, '`part`=:prowner', ':prowner',$prowner==1?'owner':'trainer'
		if ($period) {
			$periodInfo=project_model::get_period($tpid,$period);
			$fromDate=$periodInfo->report_from_date?$periodInfo->report_from_date:$periodInfo->from_date;
			$toDate=$periodInfo->report_to_date?$periodInfo->report_to_date:$periodInfo->to_date;
			$where=sg::add_condition($where, 'tr.`date1` BETWEEN "'.$fromDate.'" AND "'.$toDate.'"');
		}

		if ($para['owner']) $where=sg::add_condition($where,'tr.`uid` IN (:uid)','uid',is_array($para['owner'])?implode(',',$para['owner']):$para['owner']);
		if ($para['year']) $where=sg::add_condition($where,'YEAR(tr.`date1`)=:year','year',$para['year']);
		if ($para['month']) $where=sg::add_condition($where,'tr.`date1` BETWEEN "'.$para['month'].'-01" AND "'.$para['month'].'-30"');

		if ($para['changwat']) $where=sg::add_condition($where,'pv.`changwat`=:changwat','changwat',$para['changwat']);
		if ($para['ampur']) $where=sg::add_condition($where,'pv.`ampur`=:ampur','ampur',$para['ampur']);
		if ($para['tambon']) $where=sg::add_condition($where,'pv.`tambon`=:tambon','tambon',$para['tambon']);
		/*
		num1=ค่าตอบแทน, num2=ค่าจ้าง, num3=ค่าใช้สอย, num4=ค่าวัสดุ, num5=ค่าสาธารณูปโภค, num6=อื่น ๆ
		*/
		$stmt='SELECT
			tr.`tpid`, tr.`trid`, tr.`parent`, tr.`calid`, tr.`gallery`,
			tr.`formid`, tr.`period`, tr.`part`, tr.`flag`, tr.`uid`,
			c.`title`,
			t.`title` projectTitle,
			tr.`rate1` rate,
			tr.`date1` action_date,
			tr.`detail1` action_time,
			a.`budget`,
			a.`mainact`,
			m.`detail1` mainact_detail,
			a.`targetpreset`,
			a.`target` target,
			tr.`text3` targetPresetDetail,
			tr.`num8` targetjoin,
			tr.`text9` targetjoindetail,
			tr.`detail3` objective,
			tr.`text1` goal_do,
			tr.`text2` real_do,
			m.`text3` `presetOutputOutcome`,
			tr.`text4` real_work,
			tr.`text5` problem,
			tr.`text6` recommendation,
			tr.`text7` support,
			tr.`text8` followerrecommendation,
			tr.`detail2` followername,
			tr.`num1` exp_meed,
			tr.`num2` exp_wage,
			tr.`num3` exp_supply,
			tr.`num4` exp_material,
			tr.`num5` exp_utilities,
			tr.`num6` exp_other,
			tr.`num7` exp_total,
			c.`detail` goal_dox,
			u.`username`, u.`name` ownerName,
			GROUP_CONCAT(DISTINCT p.`fid`, "|" , p.`file`) photos
		FROM %project_tr% tr
			LEFT JOIN %topic% t ON t.`tpid`=tr.`tpid`
			LEFT JOIN %users% u ON u.`uid`=tr.`uid`
			LEFT JOIN %calendar% c ON c.`id`=tr.`calid`
			LEFT JOIN %project_activity% a ON a.`calid`=tr.`calid`
			LEFT JOIN %project_tr% m ON m.`trid`=a.`mainact`
			LEFT JOIN %topic_files% p
				ON tr.`gallery` IS NOT NULL
				AND p.`tpid`=tr.`tpid`
				AND p.`gallery`=tr.`gallery`
				AND p.`type`="photo"
			'.($para['changwat'] ? 'LEFT JOIN %project_prov% pv ON pv.`tpid`=tr.`tpid`' : '').'
		'.($where?'WHERE '.implode(' AND ',$where['cond']):'').'
		GROUP BY tr.trid
		ORDER BY '.$order.';-- {key:"trid"}';
		$dbs=mydb::select($stmt,$where['value']);
		return $dbs;
	}

	/**
	 * Get calendar of project
	 * @param
	 * @return Data Set
	 */
	public static function get_calendar($tpid=NULL,$period=NULL,$owner=NULL,$calid=NULL) {
		if ($owner=='owner') $calowner=1;
		else if ($owner==2) $calowner=2;
		if ($period) {
			$periodRs=end(project_model::get_tr($tpid,'info:period',$period)->items['period']);
			$periodStr='c.from_date BETWEEN "'.$periodRs->date1.'" AND "'.$periodRs->date2.'"';
		}

		$stmt='SELECT c.`id` calid, c.*
			, u.`name` `posterName`
			, IFNULL(ac.`detail1`,c.`title`) `title`
			, a.`mainact`, a.`budget`, a.`targetpreset`, a.`target`
			, tr.`trid` activityId, tr.`num7` exp_total
		FROM %calendar% c
			LEFT JOIN %users% u ON u.`uid` = c.`owner`
			LEFT JOIN %project_activity% a ON a.`calid`=c.`id`
			LEFT JOIN %project_tr% ac ON ac.`formid`="info" AND ac.`part`="activity" AND ac.`calid`=c.`id`
			LEFT JOIN %project_tr% tr ON tr.`calid`=c.`id` AND tr.`formid`="activity" AND tr.`part`="owner"
			WHERE c.`tpid`=:tpid '.($periodStr?' AND ('.$periodStr.')':'').($owner?' AND a.`calowner`=:calowner':'').($calid?' AND c.`id`=:calid':'').'
			GROUP BY calid
			ORDER BY c.`from_date` ASC'
			.($calid?' LIMIT 1':'');
		$dbs=mydb::select($stmt,':tpid',$tpid, ':calowner',$calowner,':calid',$calid);
		return $dbs;
	}

	public static function get_develop_data($tpid) {
		$data=array();
		$stmt='SELECT `fldname`,`flddata` FROM %bigdata% WHERE `keyid`=:tpid AND `keyname`="project.develop" ORDER BY `fldname` ASC';
		foreach (mydb::select($stmt,':tpid',$tpid)->items as $item) {
			$data[$item->fldname]=$item->flddata;
		}
		return $data;
	}

	public static function lock_period($tpid=NULL, $period=NULL, $lock=NULL) {
		if ($tpid && $period) {
			if ($lock=='auto') $lock='func.IF(`flag`='._PROJECT_LOCKREPORT.','._PROJECT_DRAFTREPORT.','._PROJECT_LOCKREPORT.')';
			mydb::query('UPDATE %project_tr% SET `flag`=:lock WHERE `tpid`=:tpid AND `formid`="info" AND `part`="period" AND `period`=:period',':tpid',$tpid, ':period',$period,':lock',$lock);
			$ret=mydb::select('SELECT `flag` FROM %project_tr% WHERE `tpid`=:tpid AND `formid`="info" AND `part`="period" AND `period`=:period LIMIT 1',':tpid',$tpid, ':period',$period)->flag;
		}
		return $ret;
	}

	public static function set_lock_report($trid=NULL) {
		mydb::query('UPDATE %project_tr% SET `flag`=IF(`flag`='._PROJECT_LOCKREPORT.','._PROJECT_DRAFTREPORT.','._PROJECT_LOCKREPORT.') WHERE `trid`=:trid',':trid',$trid);
		$ret=mydb::select('SELECT `flag` FROM %project_tr% WHERE `trid`=:trid LIMIT 1',':trid',$trid)->flag;
		return $ret;
	}

	public static function get_lock_report_date($tpid=NULL) {
		static $locks=array();
		if (!array_key_exists($tpid, $locks)) {
			$rs=mydb::select('SELECT * FROM %project_tr% WHERE `tpid`=:tpid AND ((`formid`="info" AND `part`="period") || (`formid`="ง.1" AND `part`="title")) AND `flag`>='._PROJECT_LOCKREPORT.' ORDER BY `period` DESC LIMIT 1',':tpid',$tpid);
			$ret=NULL;
			if ($rs->detail2) $ret=$rs->detail2;
			else if ($rs->date2) $ret=$rs->date2;
			$locks[$tpid]=$ret;
		} else $ret=$locks[$tpid];
		return $ret;
	}

	public static function explode_body($str) {
		preg_match_all('/(^[a-zA-Z0-9\-].*?)=(.*?)\n/ms',$str,$matches);
		$result=array();
		foreach ($matches[1] as $k=>$v) $result[trim($v)]=trim($matches[2][$k]);
		return $result;
	}

	public static function create_exptr($exp) {
		if (!property_exists($exp,'detail')) $exp->detail='';
		if (!property_exists($exp,'created')) $exp->created=date('U');
		if (!property_exists($exp,'modified')) $exp->modified=date('U');
		if (!property_exists($exp,'modifyby')) $exp->modifyby=i()->uid;
		if (!property_exists($exp,'flag')) $exp->flag=0;

		$stmt='INSERT INTO %project_tr%
			(`trid`, `tpid`, `parent`, `gallery`, `flag`, `formid`, `part`, `num1`, `num2`, `num3`, `num4`, `detail1`, `text1`, `uid`, `created`)
			VALUES
			(:expid, :tpid, :mainactid, :expcode, :flag, "develop","exptr",:amt,:unitprice,:times,:total,:unitname,:detail,:uid,:created)
			ON DUPLICATE KEY
			UPDATE `gallery`=:expcode, `num1`=:amt, `num2`=:unitprice, `num3`=:times, `num4`=:total, `detail1`=:unitname, `text1`=:detail, `modified`=:modified, `modifyby`=:modifyby';

		mydb::query($stmt,$exp);
		return mydb()->_error;
	}


	/**
	 * Set project toolbar
	 * @param $self
	 * @param String $title
	 * @param RecordSet $rs
	 * @param Object $para
	 */
	public static function set_toolbar($self,$title=NULL,$rs=NULL,$para=NULL) {
		$ret=R::View('project.toolbar',$self,$title,NULL,$rs,$para);
		return $ret;
	}

	/**
	 * Init application as main page
	 * @param $self
	 */
	public static function init_app_mainpage($self=NULL) {
		define('_AJAX',false);

		cfg('navigator',R::Page('project.app.nav.main',$self));
		cfg('web.footer','&copy; Copyright SoftGanz.');

		set_theme('app');
		cfg('theme.stylesheet.para','?v='.date('U'));
	}

} // end of class project_model
?>