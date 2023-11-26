<?php
/**
* Module  :: Description
* Created :: 2023-11-25
* Modify  :: 2023-11-25
* Version :: 1
*
* @param String $nodeInfo
* @return Widget
*
* @usage module/{id}/method
*/

class PaperEditDuplicate extends Page {
	var $nodeId;
	var $nodeInfo;

	function __construct($nodeInfo = NULL) {
		parent::__construct([
			'nodeId' => $nodeInfo->nodeId,
			'topicInfo' => $nodeInfo,
		]);
	}

	function build() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'Duplicate Topic',
			]), // AppBar
			'body' => new Widget([
				'children' => [
					new Button([
						'type' => 'primary',
						'class' => 'sg-action',
						'href' => url('api/paper/'.$this->nodeId.'/node.duplicate'),
						'text' => 'Start Duplicate Topic',
						'icon' => new Icon('content_copy'),
						'rel' => 'notify',
						'done' => 'reload:'.url('paper/{{nodeId}}')
					])
				], // children
			]), // Widget
		]);
	}
}
?>