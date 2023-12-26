<?php
/**
* Paper   :: Edit Paper
* Created :: 2021-11-22
* Modify  :: 2023-12-26
* Version :: 2
*
* @param String $topicInfo
* @return Widget
*
* @usage paper/{nodeId}/edit
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

	function build() {
		if (empty($this->nodeId)) return error(_HTTP_ERROR_NOT_FOUND, 'ไม่มีข้อมูลตามที่ระบุ');
		if (!$this->right->edit) return error(_HTTP_ERROR_FORBIDDEN, 'Access Denied');

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => $this->topicInfo->title,
			]),
			'sideBar' => new PaperEditMenuWidget(['nodeId' => $this->nodeId]),
			'body' => new Container([
				'class' => '-sg-paddingnorm',
				'children' => [
					'<h3>Papar property</h3>
					Topic id : '.$this->nodeId.'<br />
					Topic url : '.cfg('domain').url('paper/'.$this->nodeId).'<br />
					Title : '.$this->topicInfo->title.'<br />
					Content type : '.$this->topicInfo->info->type.' => '.$this->topicInfo->info->type_name.'<br />
					Status : '.$this->topicInfo->info->status.'<br />
					Create by : '.($this->topicInfo->uid?'<a href="'.url('profile/'.$this->topicInfo->uid).'">'.$this->topicInfo->info->owner.'</a>':$this->topicInfo->info->owner).'<br />

					Created date :'.$this->topicInfo->info->created.'<br />
					Changed date :'.$this->topicInfo->info->changed.'<br />
					Sticky :'.$this->topicInfo->info->sticky.'<br />
					Promote :'.$this->topicInfo->info->promote.'<br />
					Rating : '.$this->topicInfo->info->rating.'<br />
					Liked : '.$this->topicInfo->info->liketimes.'<br />
					Photo :'.count($this->topicInfo->photos).'<br />
					View :'.$this->topicInfo->info->view.' views'.($this->topicInfo->info->last_view ? ' @'.$this->topicInfo->info->last_view : '').'<br />
					Comment :'.$this->topicInfo->info->reply.' replies'.($this->topicInfo->info->last_reply ? ' @'.$this->topicInfo->info->last_reply : '').'<br />
					',
				],
			]),
		]);
	}
}
?>