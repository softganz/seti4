<?php
/**
* iMed :: Home Visit Card
* Created 2019-03-06
* Modify  2021-05-31
*
* @param Int $start
* @return Widget
*
* @usage imed/visits/{start}
*/

$debug = true;

import('widget:imed.visits');

class ImedVisits extends Page {
	var $psnId;
	var $start;
	var $refApp;
	var $showNavigator = true;

	function __construct($args = []) {
		parent::__construct($args);
		$this->start = SG\getFirst(post('start'), 0);
		$this->refApp = post('ref');
		$this->orgId = post('orgId');
	}

	function build() {
		$getPsnId = SG\getFirst($this->psnId, post('pid'));
		$getUserId = post('u');
		$showItems = 10;
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
			, (SELECT GROUP_CONCAT(CONCAT(`fid`,"|"),`file`) FROM %imed_files% WHERE `seq` = a.`seqId` AND `type` = "photo") `photos`
			, (SELECT GROUP_CONCAT(`needid`) FROM %imed_need% WHERE `seq` = a.`seqId`) `needItems`
			, b.`score`
			, q2.`q2_score`, q2.`q9_score`
			FROM
			(SELECT
			  "service" `servType`
			, s.`pid` `psnid`
			, s.`seq` `seqId`
			, s.`service`
			, s.`uid`
			, u.`username`, u.`name` `ownerName`
			, p.`prename`
			, CONCAT(p.`name`," ",p.`lname`) `patient_name`
			, s.`rx` `rx`
			, s.`timedata`
			, s.`created`
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
			FROM %imed_service% s
				LEFT JOIN %users% u USING (`uid`)
				LEFT JOIN %db_person% p ON p.`psnid` = s.`pid`
			%WHERE%
			) a
				LEFT JOIN %imed_barthel% b ON b.`seq` = a.`seqId`
				LEFT JOIN %imed_2q9q% q2 ON q2.`seq` = a.`seqId`
			ORDER BY `created` DESC
			$LIMIT$
			';

		$dbs = mydb::select($stmt);
		// debugMsg('<pre>'.mydb()->_query.'</pre>');

		return new Widget([
			'child' => new ImedVisitsWidget([
				'refApp' => $this->refApp,
				'children' => $dbs->items,
				'urlMore' => $dbs->_num_rows && $showItems == $dbs->_num_rows ? url('imed/visits',['start' => $this->start+$dbs->_num_rows, 'orgId' => $this->orgId, 'u' => $getUserId, 'pid' => $getPsnId, 'ref' => $this->refApp]) : NULL,
			]),
		]);
		// return new ImedVisitsWidget([
		// 	'children' => $dbs->items,
		// 	'urlMore' => $dbs->_num_rows && $showItems == $dbs->_num_rows ? url('imed/visits',['start' => $this->start+$dbs->_num_rows, 'u' => $getUserId, 'pid' => $getPsnId, 'ref' => $this->refApp]) : NULL,
		// ]);

		// return new Widget([
		// 	'children' => [
		// 		$this->_visit($dbs->items),
		// 		$dbs->_num_rows && $showItems == $dbs->_num_rows ? '<div id="more" class="imed-my-note-more" style="padding: 24px 16px 44px;">'
		// 			. '<a class="sg-action btn -primary" href="'.url('imed/visits/'.($this->start+$dbs->_num_rows), ['u' => $getUserId, 'pid' => $getPsnId, 'ref' => $this->refApp]).'" data-rel="replace:#more" style="margin:0 auto;display:block;text-align:center; padding: 16px 0;">'
		// 			. '<span>{tr:More}</span><i class="icon -material">navigate_next</i>'
		// 			. '</a>'
		// 			. '</div>'
		// 		: NULL,
		// 	],
		// ]);
	}

	function _visit($items) {
		$ui = new Ui('div', 'ui-card imed-my-note sg-inline-edit');
		$ui->addData('update-url', url('imed/edit/patient'));
		$ui->addId('imed-my-note');
		if (debug('inline')) $ui->addData('debug', 'inline');

		foreach ($items as $rs) {
			if ($rs->servType == "service") {
				$ui->add(R::View('imed.visit.render',$rs, ['ref' => $this->refApp]), '{class: "", id: "noteUnit-'.$rs->seq.'"}');
			} else {
				$ui->add(R::View('imed.need.render',$rs, ['ref' => $this->refApp]), '{class: "-urgency-'.$rs->urgency.'", id: "noteUnit-'.$rs->seq.'"}');
			}
		}

		if ($this->start == 0 && empty($items)) {
			$ui->add('<p class="-sg-text-center" style="padding: 32px 0;">ยังไม่เคยมีการเยี่ยมบ้าน</p>');
		}

		return $ui;
	}
}
?>