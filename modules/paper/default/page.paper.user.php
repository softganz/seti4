<?php
/**
* Paper   :: List Paper of user
* Created :: 2023-08-27
* Modify  :: 2025-06-24
* Version :: 6
*
* @param Int $userId
* @return Widget
*
* @usage paper/user/{userId}
*/

use Paper\Model\PaperModel;

ini_set('memory_limit', -1);

class PaperUser extends Page {
	var $userId;
	var $right;

	function __construct($userId = NULL) {
		parent::__construct([
			'userId' => $userId,
			'right' => (Object) [
				'admin' => user_access('admin paper'),
			]
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
				'title' => UserModel::get($this->userId)->name.'\'s Node List',
				'navigator' => new Nav([
					'children' => [
						$this->right->admin ? new Button([
							'type' => 'secondary',
							'text' => PaperModel::countCommentByUserId($this->userId).' Comments'
						]) : NULL, // Button
					], // children
				]), // Nav
			]), // AppBar
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
						return new Card([
							'child' => new ListTile([
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
							]), // ListTile
						]);
					},
					$topics->items
				), // children
			]), // body
		]);
	}
}
?>