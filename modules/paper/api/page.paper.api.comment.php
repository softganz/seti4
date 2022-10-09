<?php
/**
* Paper API :: API Comment
* Created 2021-09-02
* Modify  2021-09-02
*
* @param Int $mainId
* @param String $action
* @param Int $commentId
* @return String
*
* @usage module/api/{id}/{action}[/{commentId}]
*/

$debug = true;

class PaperApiComment extends Page {
	var $tpid;
	var $action;
	var $commentId;
	var $topicInfo;

	function __construct($tpid, $action, $commentId = NULL) {
		$this->topicInfo = R::Model('paper.get', $tpid);
		$this->tpid = $this->topicInfo->tpid;
		$this->action = $action;
		$this->commentId = $commentId;
	}

	function build() {
		// debugMsg('mainId '.$this->tpid.' Action = '.$this->action.' commentId = '.$this->commentId);

		if (empty($this->tpid)) return message(['responseCode' => _HTTP_ERROR_BAD_REQUEST, 'text' => 'PROCESS ERROR']);

		$commentId = $this->commentId;

		$commentInfo = paper_model::get_comment_by_id($commentId);

		$isAdmin = user_access('administer contents,administer papers');
		$isEdit = $isAdmin || (i()->ok && $commentInfo->uid == i()->uid);

		if (!$isEdit) return message(['responseCode' => _HTTP_ERROR_FORBIDDEN, 'text' => 'access denied']);

		$ret = '';

		switch ($this->action) {
			case 'delete':
				if (SG\confirm()) {
					$ret .= 'Comment deleted';
					$result = R::Model('paper.comment.delete',$commentId);
				}
				break;

			default:
				return new ErrorMessage(['responseCode' => _HTTP_ERROR_FORBIDDEN, 'text' => 'Action not found!!!']);
				break;
		}

		return $ret;
	}
}
?>