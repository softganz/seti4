<?php
/**
* Paper   :: List Paper of user
* Created :: 2023-08-27
* Modify  :: 2025-05-24
* Version :: 4
*
* @param Int $userId
* @return Widget
*
* @usage paper/user/{userId}
*/

ini_set('memory_limit', -1);

class PaperUser extends Page {
	function __construct($userId = NULL) {
		parent::__construct([
			'userId' => $userId,
		]);
	}

	function build() {
		head('<meta name="robots" content="noindex,nofollow">');
		
		$topics = NodeModel::items([
			'user' => $this->userId,
			'type' => '*',
			'options' => [
				'debug' => false,
				'field' => '',
				'page' => 1,
				'items' => 100000,
				'order' => 'nodeId',
			],
		]);

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'User Node List',
			]),
			'body' => new Widget([
				'children' => array_map(
					function($node) {
						$url = '';
						if ($node->type === 'project') {
							$url = url('project/'.$node->nodeId);
						} else if ($node->type === 'project-develop') {
							$url = url('project/proposal/'.$node->nodeId);
						} else {
							$url = url('paper/'.$node->nodeId);
						}
						return new ListTile([
							'class' => 'sg-action',
							'title' => $node->title,
							'subtitle' => $node->type,
							'leading' => new Icon('topic'),
							'trailing' => new Button([
								'type' => 'link',
								'text' => 'View',
								'href' => $url,
								'icon' => new Icon('chevron_right'),
								'iconPosition' => 'right',
							]),
							'href' => $url,
						]);
					},
					$topics->items
				), // children
			]), // body
		]);
	}


}
?>