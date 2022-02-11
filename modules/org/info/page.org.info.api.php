<?php
/**
* Module :: Description
* Created 2021-10-13
* Modify  2021-10-13
*
* @param Int $orgId
* @param String $action
* @param Int $tranId
* @return String
*
* @usage module/api/{id}/{action}[/{tranId}]
*/

$debug = true;

import('model:org.php');

class OrgInfoApi extends Page {
	var $orgId;
	var $action;
	var $tranId;

	function __construct($orgId, $action, $tranId = NULL) {
		$this->action = $action;
		$this->tranId = $tranId;
		$this->orgInfo = $orgId ? OrgModel::get($orgId, '{initTemplate: true, debug: false}') : NULL;
		$this->orgId = $this->orgInfo->orgId;
	}

	function build() {
		// debugMsg('orgId '.$this->orgId.' Action = '.$this->action.' TranId = '.$this->tranId);
		$orgId = $this->orgId;
		$tranId = $this->tranId;

		$isEdit = $this->orgInfo->RIGHT & _IS_EDITABLE;


		if (empty($orgId)) return message(['responseCode' => _HTTP_ERROR_BAD_REQUEST, 'text' => 'PROCESS ERROR']);
		else if (!$isEdit) return message(['responseCode' => _HTTP_ERROR_FORBIDDEN, 'text' => 'Access Denied']);

		$ret = '';

		switch ($this->action) {
			case 'delete':
				if (SG\confirm()) {
					$ret .= 'DELETED';

					$hasDoings = mydb::select(
						'SELECT COUNT(*) `total` FROM %org_doings% WHERE `orgid` = :orgId LIMIT 1',
						':orgId', $orgId
					)->total;

					if ($hasDoings) return 'Cannot delete :: Have Transaction';

					mydb::query('DELETE FROM %org_officer% WHERE `orgid` = :orgId', ':orgId', $orgId);
					mydb::query('DELETE FROM %ibuy_shop% WHERE `shopid` = :orgId', ':orgId', $orgId);
					mydb::query('DELETE FROM %ibuy_farmland% WHERE `orgid` = :orgId', ':orgId', $orgId);
					mydb::query('DELETE FROM %db_org% WHERE `orgid` = :orgId', ':orgId', $orgId);
				}
				break;

			case 'location.save':
				// debugMsg(post(),'post()');
				if ($location = post('location')) {
					mydb::query(
						'UPDATE %db_org% SET `location` = :location WHERE `orgId` = :orgId LIMIT 1',
						[':orgId' => $orgId, ':location' => $location]
					);
					// debugMsg(mydb()->_query);
				} else {
					mydb::query(
						'UPDATE %db_org% SET `location` = NULL WHERE `orgId` = :orgId LIMIT 1',
						[':orgId' => $orgId, ':location' => $location]
					);
					// debugMsg(mydb()->_query);
				}
				break;

			case 'officer.add':
				if (post('uid') && post('membership')) {
					$stmt = 'INSERT INTO %org_officer% (`orgid`, `uid`, `membership`) VALUES (:orgid, :uid, :membership) ON DUPLICATE KEY UPDATE `membership` = :membership';
					mydb::query($stmt, ':orgid', $orgId, ':uid', post('uid'), ':membership', strtoupper(post('membership')));
					//$ret .= mydb()->_query;
				}
				break;

			case 'officer.remove':
				if ($tranId && SG\confirm()) {
					mydb::query(
						'DELETE FROM %org_officer% WHERE `uid` = :uid AND `orgid` = :orgid LIMIT 1',
						':uid', $tranId,
						':orgid', $orgId
					);
				}
				break;

			case 'member.remove':
				if ($tranId && SG\confirm()) {
					mydb::query(
						'DELETE FROM %org_morg% WHERE `psnid` = :psnid AND `orgid` = :orgid LIMIT 1',
						':psnid', $tranId,
						':orgid', $orgId
					);
				}
				break;

			case 'join.org.remove':
				if ($tranId && SG\confirm()) {
					mydb::query(
						'DELETE FROM %org_ojoin% WHERE `orgid` = :orgid AND `jorgid` = :jorgid LIMIT 1',
						':orgid', $orgId,
						':jorgid', $tranId
					);
				}
				break;


			case 'board.save':
				$data = (object) post();
				if (empty($data->id)) $data->id = NULL;

				$data->orgid = $orgId;

				if ($data->orgid && $data->position && $data->name) {
					$data->boardposition = SG\getFirst($data->boardposition);
					// Check Current Board Series
					$currentSeries = mydb::select(
						'SELECT `series` FROM %org_board% WHERE `orgid` = :orgid AND `status` = :inboard LIMIT 1',
						':orgid', $orgId, ':inboard', _INBOARD_CODE
					)->series;

					$data->series = $currentSeries ? $currentSeries : sg_date($data->datein, 'Y');

					list($data->position, $data->posno) = explode(':', $data->position);
					if (empty($data->posno)) $data->posno = NULL;

					$data->datein = sg_date($data->datein,'Y-m-d');
					$data->datedue = sg_date($data->datedue,'Y-m-d');
					$data->fromorg = SG\getFirst($data->fromorg);

					mydb::query(
						'INSERT INTO %org_board% (
						  `brdid`,`orgid`, `boardposition`, `position`, `posno`
						, `prename`, `name`
						, `fromorg`, `datein`, `datedue`
						, `series`
						) VALUES (
						  :id, :orgid, :boardposition, :position, :posno
						, :prename, :name
						, :fromorg, :datein, :datedue
						, :series
						)
						ON DUPLICATE KEY UPDATE
						  `boardposition` = :boardposition
						, `position` = :position
						, `posno` = :posno
						, `prename` = :prename
						, `name` = :name
						, `fromorg` = :fromorg
						, `datein` = :datein
						, `datedue` = :datedue
						',
						$data
					);
				}
				break;

			case 'board.out':
				// Save One Board Out
				if ($tranId && post('outcond') && post('dateout')) {
					mydb::query(
						'UPDATE %org_board%
							SET `status` = :outcond, `dateout` = :dateout
						WHERE `brdid` = :brdid AND `orgid` = :orgid
						LIMIT 1',
						':brdid', $tranId,
						':orgid', $orgId,
						':outcond', post('outcond'),
						':dateout', sg_date(post('dateout'),'Y-m-d')
					);
				}
				break;

			case 'board.beover':
				// Save All Board Out
				$outBeOverCondition = 2;
				if (post('dateout') && SG\confirm()) {
					mydb::query(
						'UPDATE %org_board%
						SET `status` = :outcond, `dateout` = :dateout
						WHERE `orgid` = :orgid AND `status` = 1',
						':orgid', $orgId,
						':outcond', $outBeOverCondition,
						':dateout', sg_date(post('dateout'), 'Y-m-d')
					);
				}
				break;

			case 'board.delete':
				if ($tranId && SG\confirm()) {
					mydb::query(
						'DELETE FROM %org_board% WHERE `brdid` = :brdid LIMIT 1',
						':brdid', $tranId
					);
				}
				break;

			default:
				return message(['responseCode' => _HTTP_ERROR_BAD_REQUEST, 'text' => 'Action not found!!!']);
				break;
		}

		return $ret;
	}
}
?>