<?php
/**
 * Paper   :: Edit Paper
 * Author  :: Little Bear<softganz@gmail.com>
 * Created :: 2021-11-22
 * Modify  :: 2026-05-29
 * Version :: 3
 *
 * @param String $topicInfo
 * @return Widget
 *
 * @example paper/{nodeId}/edit
 */

class PaperEdit extends Page {
	var $nodeId;
	var $right;
	var $topicInfo;

	function __construct($topicInfo = NULL) {
		parent::__construct([
			'nodeId' => $topicInfo->nodeId,
			'topicInfo' => $topicInfo,
			'right' => $topicInfo->right,
		]);
	}

	function rightToBuild() {
		if (empty($this->nodeId)) return error(_HTTP_ERROR_NOT_FOUND, 'ไม่มีข้อมูลตามที่ระบุ');
		if (!$this->right->edit) return error(_HTTP_ERROR_FORBIDDEN, _ERROR_MSG_ACCESS_DENIED);

		return true;
	}
	
	function build() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => $this->topicInfo->title,
				'leading' => new Button([
					'type' => 'link',
					'href' => Url::link('paper/' . $this->nodeId),
					'icon' => new Icon('arrow_back'),
				]),
			]),
			'sideBar' => new PaperEditMenuWidget(['nodeId' => $this->nodeId]),
			'body' => new Column([
				'class' => '-sg-paddingnorm',
				'children' => [
					'<h3>Papar property</h3>',
					'ID : ' . $this->nodeId,
					'Url : ' . cfg('domain') . Url::link('paper/' . $this->nodeId),
					'Title : ' . $this->topicInfo->title,
					'Content type : ' . $this->topicInfo->info->type . ' => ' . $this->topicInfo->info->type_name,
					'Status : ' . $this->topicInfo->info->status,
					'Create by : ' . ($this->topicInfo->uid ? '<a href="' . Url::link('profile/' . $this->topicInfo->uid) . '">' . $this->topicInfo->info->owner.'</a>' : $this->topicInfo->info->owner),

					'Created date : ' . $this->topicInfo->info->created,
					'Changed date : ' . $this->topicInfo->info->changed,
					'Sticky : ' . $this->topicInfo->info->sticky,
					'Promote : ' . $this->topicInfo->info->promote,
					'Rating : ' . $this->topicInfo->info->rating,
					'Liked : ' . $this->topicInfo->info->liketimes,
					'Photo : ' . count($this->topicInfo->photos),
					'View : ' . $this->topicInfo->info->view . ' views' . ($this->topicInfo->info->last_view ? ' @' . $this->topicInfo->info->last_view : ''),
					'Comment : ' . $this->topicInfo->info->reply . ' replies' . ($this->topicInfo->info->last_reply ? ' @' . $this->topicInfo->info->last_reply : ''),
				],
			]),
		]);
	}
}
?>