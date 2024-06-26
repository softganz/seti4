<?php
/**
* Paper   :: Get Node List
* Created :: 2023-02-04
* Modify  :: 2024-05-15
* Version :: 2
*
* @return Object
*
* @usage api/paper/items
*/

import('model:node.php');

class PaperItemsApi extends PageApi {
	var $action;
	var $type;
	var $tag;
	var $items = 10;

	function __construct($action = NULL) {
		parent::__construct([
			'action' => $action,
			'type' => post('type'),
			'tag' => post('tag'),
			'items' => intval(post('items')) > 0 ? intval(post('items')) : 10,
		]);
	}

	function build() {
		$paperList = NodeModel::items([
			'type' => $this->type,
			'tags' => $this->tag,
			'options' => ['items' => $this->items, 'debug' => false, 'field' => 'detail,photo,doc,tag'],
		]);

		if ($paperList->count) {
			$paperList->items = (Array) array_values($paperList->items);
		}

		return $paperList;
	}
}
?>