<?php
/**
* Node    :: Node Info API
* Created :: 2023-07-26
* Modify  :: 2025-02-25
* Version :: 2
*
* @param Int $nodeId
* @param String $action
* @param Int $tranId
* @return Array/Object
*
* @usage api/node/info/{nodeId}/{action}[/{tranId}]
*/

class NodeInfoApi extends PageApi {
	var $nodeId;
	var $action;
	var $tranId;

	function __construct($nodeId = NULL, $action = NULL, $tranId = NULL) {
		parent::__construct([
			'action' => $action,
			'tranId' => $tranId,
			'nodeInfo' => $nodeInfo = (is_numeric($nodeId) ? \NodeModel::get($nodeId) : NULL),
			'nodeId' => $nodeInfo->nodeId,
		]);
	}

	function build() {
		if (empty($this->nodeId)) return error(_HTTP_ERROR_NOT_FOUND, 'PROCESS ERROR');
		// if (!$this->right->access) return error(_HTTP_ERROR_NOT_ALLOWED, 'Access Denied');
		// if (!$this->right->edit) return error(_HTTP_ERROR_FORBIDDEN, 'Access Denied');

		return parent::build();
	}

	function bookmark() {
		$bookmarkLabel = $this->tranId;
		if (!i()->ok) return error(_HTTP_ERROR_FORBIDDEN, 'Access Denied');
		if (empty($bookmarkLabel)) return error(_HTTP_ERROR_NOT_ACCEPTABLE, 'ไม่มีข้อมูลประเภท Bookmark');
		R::Model('reaction.add', $this->nodeId, $bookmarkLabel, '{addType: "toggle"}');
		return success('บันทึกเรียบร้อย');
		// return \ReactionModel::get(['id' => $this->nodeId, 'bookmark' => $bookmarkLabel]);
	}

	function like() {
		$likeLabel = $this->tranId;
		if (!i()->ok) return error(_HTTP_ERROR_FORBIDDEN, 'Access Denied');
		if (empty($this->tranId)) return error(_HTTP_ERROR_NOT_ACCEPTABLE, 'ไม่มีข้อมูลประเภท Like');

		$result = R::Model('reaction.add', $this->nodeId, $likeLabel, '{addType: "toggle", count: "topic:liketimes"}');

		return success('บันทึกเรียบร้อย');
		// return \ReactionModel::get(['id' => $this->nodeId, 'bookmark' => $this->tranId.'.BOOKM']);
	}

	function reviewSave() {
		$getRating = SG\getFirstInt(post('rate'));
		if (!i()->ok) return error(_HTTP_ERROR_FORBIDDEN, 'Access Denied');

		if (empty($getRating)) return error(_HTTP_ERROR_NOT_ACCEPTABLE, 'ไม่มีข้อมูลการให้คะแนน');

		if ($this->nodeInfo->info->ratetimes) {
			$reviewInfo = \mydb::select(
				'SELECT * FROM %topic_comments% WHERE `tpid` = :tpid AND `uid` = :uid AND `subject` = "REVIEW" LIMIT 1',
				[':tpid' => $this->nodeId, ':uid' => i()->uid]
			);

			$currentRating = $this->nodeInfo->info->rating*$this->nodeInfo->info->ratetimes;
			$currentTimes = $this->nodeInfo->info->ratetimes;
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
			[
				':tpid' => $this->nodeId,
				':rating' => $newRating,
				':ratetimes' => $rateTimes
			]
		);
		// debugMsg(mydb()->_query);

		$data = (Object) [
			'cid' => post('cid') ? post('cid') : NULL,
			'tpid' => $this->nodeId,
			'uid' => i()->uid,
			'giverating' => $getRating,
			'status' => _PUBLISH,
			'subject' => 'REVIEW',
			'comment' => post('msg') ? post('msg') : '',
			'thread' => $this->nodeInfo->title,
			'name' => i()->name,
			'ip' => ip2long(getenv('REMOTE_ADDR')),
		];

		\mydb::query(
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
		return success('บันทึกเรียบร้อย');
	}
}
?>