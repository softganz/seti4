<?php
/**
* Module :: Description
* Created 2021-11-22
* Modify  2021-11-22
*
* @param String $arg1
* @return Widget
*
* @usage module/{id}/method
*/

class PaperEdit extends Page {
	var $tpid;
	var $topicInfo;

	function __construct($topicInfo = NULL) {
		parent::__construct([
			'tpid' => $topicInfo->tpid,
			'topicInfo' => $topicInfo,
		]);
	}

	function build() {
		if (empty($this->tpid)) return error(_HTTP_ERROR_NOT_FOUND, 'ไม่มีข้อมูลตามที่ระบุ');

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => $this->topicInfo->title,
			]),
			'sideBar' => new PaperEditMenuWidget(['nodeId' => $this->tpid]),
			'body' => new Container([
				'class' => '-sg-paddingnorm',
				'children' => [
					'<h3>Papar property</h3>
					Topic id : '.$this->tpid.'<br />
					Topic url : '.cfg('domain').url('paper/'.$this->tpid).'<br />
					Title : '.$this->topicInfo->tpid->title.'<br />
					Content type : '.$this->topicInfo->info->type.' => '.$this->topicInfo->info->type_name.'<br />
					Status : '.$this->topicInfo->info->status.'<br />
					Create by : '.($this->topicInfo->tpid->uid?'<a href="'.url('profile/'.$this->topicInfo->tpid->uid).'">'.$this->topicInfo->info->owner.'</a>':$this->topicInfo->info->owner).'<br />

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