<?php
/**
* Project Widget :: Rateing/Like/Bookmark Status
* Created 2021-05-31
* Modify 	2021-09-30
*
* @param Array $args
* @return Widget
*
* @usage new ProjectLikeStatusWidget([])
*/

$debug = true;

import('model:reaction.php');

class ProjectLikeStatusWidget extends Widget {
	var $projectId;
	var $action = 'PROJ';
	var $projectInfo;

	function __construct($args = []) {
		parent::__construct($args);
		$this->projectId = $this->projectInfo->projectId;
	}

	function build() {
		$isAdmin = is_admin('project');
		$isTrainer = $this->projectInfo->RIGHT & _IS_TRAINER;

		$nodeInfo = ReactionModel::get(['id' => $this->projectId, 'bookmark' => $this->action.'.BOOKM']);
		$views = $nodeInfo->views;
		$ratings = $nodeInfo->rating;
		$likeTotals = $nodeInfo->likeTimes;

		$myAction = i()->ok ? ReactionModel::items(['id' => $this->projectId, 'userId' => i()->uid, 'action' => $this->action.'.%']) : [];

		if ($nodeInfo->approve == 'MASTER') {
			$status = '<i class="icon -material -green">verified</i><span>ต้นแบบ</span>';
			if ($isAdmin) $approveLink = '<a class="sg-action btn -link" href="'.url('project/info/api/'.$this->projectId.'*/approve.learn').'" data-change="learn" data-rel="notify" data-options=\'{"silent": true}\' data-before="callback:reactionChange">'.$status.'</a>';
		} else if ($nodeInfo->approve == 'USE') {
			$status = '<i class="icon -material -yellow">recommend</i><span>นำไปใช้</span>';
			if ($isAdmin) $approveLink = '<a class="sg-action btn -link" href="'.url('project/info/api/'.$this->projectId.'*/approve.master').'" data-change="master" data-rel="notify" data-options=\'{"silent": true}\' data-before="callback:reactionChange">'.$status.'</a>';
			else if ($isTrainer) $approveLink = '<a class="sg-action btn -link" href="'.url('project/info/api/'.$this->projectId.'*/approve.learn').'" data-change="learn" data-rel="notify" data-options=\'{"silent": true}\' data-before="callback:reactionChange">'.$status.'</a>';
		} else if ($nodeInfo->approve == 'LEARN') {
			$status = '<i class="icon -material -gray">flaky</i><span>เรียนรู้</span>';
			if ($isAdmin) $approveLink = '<a class="sg-action btn -link" href="'.url('project/info/api/'.$this->projectId.'*/approve.use').'" data-change="use" data-rel="notify" data-options=\'{"silent": true}\' data-before="callback:reactionChange">'.$status.'</a>';
			else if ($isTrainer) $approveLink = '<a class="sg-action btn -link" href="'.url('project/info/api/'.$this->projectId.'*/approve.use').'" data-change="use" data-rel="notify" data-options=\'{"silent": true}\' data-before="callback:reactionChange">'.$status.'</a>';
		}

		if (!$approveLink) $approveLink = '<a class="btn -link">'.$status.'</a>';

		return new Widget([
			'children' => [
				new Row([
					'mainAxisAlignment' => 'end',
					'class' => 'ui-like-status',
					'style' => 'padding: 4px;',
					'children' => [
						$this->projectId ? $approveLink : NULL,

						$this->projectId ? '<a'.(i()->ok ? ' class="sg-action btn -link" href="'.url('node/'.$this->projectId.'/review').'" data-rel="box" data-width="480" title="คลิกเพื่อรีวิวและให้คะแนน"' : ' class="btn -link"').' rel="nofollow"><i class="icon -material rating-star '.($ratings != '' ? '-rate-'.round($ratings) : '').'">stars</i><span>'.$ratings.' Stars</span></a>' : NULL,

						$this->projectId ? '<a'.(i()->ok ? ' class="sg-action btn -link" href="'.url('node/api/'.$this->projectId.'/like/'.$this->action).'" data-rel="notify" data-options=\'{"silent": true}\' data-done="function:(e,data){e.find(\'.-count\').text(data.likeTimes); e.find(\'.icon\').toggleClass(\'-active\');}"' : ' class="btn -link"').' title="'.number_format($likeTotals).' People Like this" rel="nofollow"><i class="icon -material '.($this->isReaction($this->action.'.LIKE',$myAction) ? '-active' : '').'">thumb_up</i><span><span class="-count">'.($likeTotals > 0 ? $likeTotals : '').'</span> Like'.($likeTotals > 1 ? 's' : '').'</span></a>' : NULL,

						$this->projectId ? '<a'.(i()->ok ? ' class="sg-action btn -link" href="'.url('node/api/'.$this->projectId.'/bookmark/'.$this->action).'" data-rel="notify" data-options=\'{"silent": true}\' data-done="function:(e,data){e.find(\'.icon\').toggleClass(\'-active\');}"' : ' class="btn -link"').' title="'.number_format($nodeInfo->bookmarks).' Peoples Bookmark this" rel="nofollow"><i class="icon -material '.($this->isReaction($this->action.'.BOOKM',$myAction) ? '-active' : '').'">favorite</i><span class="">Bookmark</span></a>' : NULL,
					], // children
				]), // Row
				$this->_script(),
			], // children
		]);
	}

	function isReaction($action, $myAction) {
		$found = false;
		foreach ($myAction->items as $item) {
			if ($item->action == $action) {
				$found = true;
				break;
			}
		}
		return $found;
	}

	function _script() {
		head('<script type="text/javascript">
		function reactionChange($this, data) {
			if ($this.data("change") == "learn") {
				$this.attr("href","'.url('project/info/api/'.$this->projectId.'*/approve.learn').'")
				$this.data("change", "use")
				$this.find(".icon").removeClass("-green").addClass("-gray").text("flaky")
				$this.find("span").text("เรียนรู้")
			} else if ($this.data("change") == "use") {
				$this.attr("href","'.url('project/info/api/'.$this->projectId.'*/approve.master').'")
				$this.data("change", "master")
				$this.find(".icon").removeClass("-gray").addClass("-yellow").text("recommend")
				$this.find("span").text("นำไปใช้")
			} else if ($this.data("change") == "master") {
				$this.attr("href","'.url('project/info/api/'.$this->projectId.'*/approve.learn').'")
				$this.data("change", "learn")
				$this.find(".icon").removeClass("-yellow").addClass("-green").text("verified")
				$this.find("span").text("ต้นแบบ")
			}
		}
		</script>');
		return '';
	}
}
?>