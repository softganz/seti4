<?php
/**
* iMed :: My Psychiatry Care
* Created 2021-05-26
* Modify  2021-05-26
*
* @return Widget
*
* @usage R::View('imed.my.patient', [ref,item])
*/

$debug = true;

class ImedApiVisits extends Page {
	var $start = 0;
	var $item = 20;

	function __construct($args = []) {
		parent::__construct($args);
	}

	function build() {
		$result = [];

		$getPsnId = post('pid');
		$getUserId = post('u');
		$showItems = SG\getFirst($this->item,10);
		$uid = i()->uid;
		$isAdmin = user_access('administer imeds');

		$zones = imed_model::get_user_zone($uid,'imed');

		if ($getPsnId) {
			mydb::where('s.`pid` = :psnId', ':psnId', $getPsnId);
		}

		if ($getUserId) {
			mydb::where('s.`uid` = :uid',':uid',$uid);
		}

		if ($isAdmin) {
			// Get all record
		} else  if ($zones) {
			mydb::where('(s.`uid` = :uid OR p.`uid` = :uid OR '.R::Model('imed.person.zone.condition',$zones).')',':uid',$uid);
		} else if (i()->ok) {
			mydb::where('s.`uid` = :uid',':uid',$uid);
		} else {
			mydb::where('false');
		}

		mydb::value('$LIMIT$', 'LIMIT '.$this->start.','.$showItems);

		$stmt = 'SELECT
				a.*
			, b.`score` `adlScore`
			, q2.`q2_score` `q2Score`, q2.`q9_score` `q9Score`
			, (SELECT GROUP_CONCAT(CONCAT(`fid`,":"),`file` SEPARATOR "|") FROM %imed_files% WHERE `seq` = a.`seq` AND `type` = "photo") `photos`
			, (SELECT GROUP_CONCAT(n.`needid`,":",n.`urgency`,":",IFNULL(n.`status`,""),":",nt.`name` SEPARATOR "|") FROM %imed_need% n LEFT JOIN %tag% nt ON nt.`taggroup` = "imed:needtype" AND nt.`catid` = n.`needtype` WHERE n.`seq` = a.`seq`) `needs`
			FROM
			(SELECT
			  "service" `serviceType`
			, s.`pid` `psnId`
			, s.`seq`
			, s.`service`
			, CONCAT(p.`prename`,p.`name`," ",p.`lname`) `patientName`
			, s.`rx` `visitText`
			, FROM_UNIXTIME(s.`timedata`, "%Y-%m-%d") `visitDate`
			, FROM_UNIXTIME(s.`created`, "%Y-%m-%d %H:%i") `createDate`
			, s.`appsrc`, s.`appagent`
			, s.`weight`
			, s.`height`
			, s.`temperature`
			, s.`pulse`
			, s.`respiratoryrate`
			, s.`sbp`
			, s.`dbp`
			, s.`fbs`
			, NULL `needtype`
			, NULL `needTypeName`
			, NULL `urgency`
			, s.`uid`
			, u.`username`, u.`name` `ownerName`
			FROM %imed_service% s
				LEFT JOIN %users% u USING (`uid`)
				LEFT JOIN %db_person% p ON p.`psnid` = s.`pid`
			%WHERE%
			) a
				LEFT JOIN %imed_barthel% b USING(`seq`)
				LEFT JOIN %imed_2q9q% q2 USING(`seq`)
			ORDER BY `seq` DESC
			$LIMIT$
			';

		$dbs = mydb::select($stmt);
		// debugMsg('<pre>'.mydb()->_query.'</pre>');

		foreach ($dbs->items as $rs) {
			$rs->isEditable = $isAdmin || (i()->ok && i()->uid == $rs->uid);
			$barthel = R::Model('imed.barthel.level', $rs->adlScore);
			$rs->adlLevel = $barthel->level;
			$rs->adlText = $barthel->text;
			$rs->ownerPhoto = model::user_photo($rs->username);
			if ($rs->photos) {
				$photoList = [];
				foreach (explode('|', $rs->photos) as $photoSet) {
					list($fid,$fname) = explode(':', $photoSet);
					$photoList[] = (Object)['id'=>intval($fid), 'name'=>$fname];
				}
				$rs->photos = $photoList;
			}
			if ($rs->needs) {
				$needList = [];
				foreach (explode('|', $rs->needs) as $needItem) {
					list($needid,$urgency,$status,$needtype) = explode(':', $needItem);
					$needList[] = (Object)['id'=>intval($fid), 'urgency'=>$urgency,'status'=>$status,'type'=>$needtype];
				}
				$rs->needs = $needList;
			}
			$result[] = $rs;
		}
		return $result;
	}

}
?>