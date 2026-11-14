<?php
/**
 * Paper   :: Get Node List
 * Created :: 2023-02-04
 * Modify  :: 2024-09-24
 * Version :: 3
 *
 * @return Object
 *
 * @usage api/paper/items
 */

import('model:node.php');

class PaperItemsApi extends PageApi {
	public $action;
	public $type;
	public $tag;
	public $page = 1;
	public $items = 10;

	function __construct($action = null) {
		parent::__construct([
			'action' => $action,
			'type' => post('type'),
			'tag' => post('tag'),
			'page' => SG\getFirstInt(post('page'), $this->page),
			'items' => intval(post('items')) > 0 ? intval(post('items')) : 10
		]);
	}

	function build() {
		$paperList = NodeModel::items([
			'type' => $this->type,
			'tags' => $this->tag,
			'options' => [
				'items' => $this->items,
				'page' => $this->page,
				'debug' => false,
				'field' => 'detail,photo,doc,tag'
			]
		]);

		if ($paperList->count) {
			$paperList->items = (array) array_values($paperList->items);
		}

		return $paperList;
	}
}
?>
