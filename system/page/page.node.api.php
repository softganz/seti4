<?php
/**
* Node API :: Node API
* Created 2021-09-30
* Modify  2021-09-30
*
* @param Int $nodeId
* @param String $action
* @param Int $tranId
* @return String
*
* @usage node/api/{id}/{action}[/{tranId}]
*/

$debug = true;

import('model:node.php');

class NodeApi extends Page {
	var $nodeId;
	var $action;
	var $tranId;

	function __construct($nodeId, $action, $tranId = NULL) {
		$this->nodeId = $nodeId;
		$this->action = $action;
		$this->tranId = $tranId;
	}

	function build() {
		// debugMsg('nodeId '.$this->nodeId.' Action = '.$this->action.' TranId = '.$this->tranId);

		$nodeInfo = is_numeric($this->nodeId) ? NodeModel::get($this->nodeId) : NULL;
		$this->nodeId = $nodeId = $nodeInfo->nodeId;
		$tranId = $this->tranId;

		// debugMsg($nodeInfo, '$nodeInfo');

		if (empty($this->nodeId)) return message(['responseCode' => _HTTP_ERROR_BAD_REQUEST, 'text' => 'PROCESS ERROR']);

		// $isAccess = $nodeInfo->RIGHT & _IS_ACCESS;
		// $isEdit = $nodeInfo->RIGHT & _IS_EDITABLE;

		// if (!$isAccess) return message(['responseCode' => _HTTP_ERROR_NOT_ALLOWED, 'text' => 'Access Denied']);
		// else if (!$isEdit) return message(['responseCode' => _HTTP_ERROR_FORBIDDEN, 'text' => 'Access Denied']);

		$ret = '';

		switch ($this->action) {
			case 'bookmark':
				if (i()->ok && $tranId) {
					R::Model('reaction.add', $this->nodeId, $tranId.'.BOOKM', '{addType: "toggle"}');
					$ret = ReactionModel::get(['id' => $this->nodeId, 'bookmark' => $tranId.'.BOOKM']);
				}
				break;

			case 'like':
				if (i()->ok && $tranId) {
					$result = R::Model('reaction.add', $this->nodeId, $tranId.'.LIKE', '{addType: "toggle", count: "topic:liketimes"}');
					$ret = ReactionModel::get(['id' => $this->nodeId, 'bookmark' => $tranId.'.BOOKM']);
				}
				break;

			case 'review.save' :
				if (i()->ok && ($getRating = post('rate'))) {

					if ($nodeInfo->info->ratetimes) {
						$reviewInfo = mydb::select(
							'SELECT * FROM %topic_comments% WHERE `tpid` = :tpid AND `uid` = :uid AND `subject` = "REVIEW" LIMIT 1',
							[':tpid' => $this->nodeId, ':uid' => i()->uid]
						);

						$currentRating = $nodeInfo->info->rating*$nodeInfo->info->ratetimes;
						$currentTimes = $nodeInfo->info->ratetimes;
						if ($reviewInfo->_num_rows) {
							$currentRating -= $reviewInfo->giverating;
							$currentTimes--;
						}
						$rateTimes = $currentTimes + 1;
						$newRating = round(($currentRating + $getRating) / $rateTimes,1);
					} else {
						$newRating = $getRating;
						$rateTimes = 1;
					}

					mydb::query(
						'UPDATE %topic% SET `rating` = :rating, `ratetimes` = :ratetimes WHERE `tpid` = :tpid LIMIT 1',
						[':tpid' => $nodeId, ':rating' => $newRating, ':ratetimes' => $rateTimes]
					);
					// $ret .= mydb()->_query.'<br />';


					$data = (Object) [];
					$data->cid = post('cid') ? post('cid') : NULL;
					$data->tpid = $nodeId;
					$data->uid = i()->uid;
					$data->giverating = $getRating;
					$data->status = _PUBLISH;
					$data->subject = 'REVIEW';
					$data->comment = post('msg') ? post('msg') : '';
					$data->thread = $nodeInfo->title;
					$data->name = i()->name;
					$data->ip = ip2long(getenv('REMOTE_ADDR'));

					mydb::query(
						'INSERT INTO %topic_comments%
						(`cid`, `tpid`, `uid`, `status`, `giverating`, `subject`, `comment`, `thread`, `name`, `ip`)
						VALUES
						(:cid, :tpid, :uid, :status, :giverating, :subject, :comment, :thread, :name, :ip)
						ON DUPLICATE KEY UPDATE
						`giverating` = :giverating
						, `comment` = :comment',
						$data
					);
					// debugMsg(mydb()->_query);
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