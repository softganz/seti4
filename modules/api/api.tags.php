<?php
/**
* API     :: Tags API
* Created :: 2022-11-19
* Modify  :: 2024-04-26
* Version :: 3
*
* @return String/Array
*
* @usage api/tags/{action}/{id}?input=tagString
*/

use Softganz\DB;

class TagsApi extends PageApi {
	var $id;
	var $input;
	var $actionDefault = 'items';

	function __construct($action = NULL, $id = NULL) {
		parent::__construct([
			'action' => $action,
			'id' => SG\getFirstInt($id),
			'input' => post('input'),
		]);
	}

	function vid() {
		if (!debug('process')) sendHeader('text/xml');
		$len = strlen($this->input);
		if (strpos($this->input, ',')) {
			$pre = substr($this->input, 0, strrpos($this->input,','));
			$this->input = trim(substr($this->input, strrpos($this->input,',')+1));
		}

		if ($len > 0) {
			$tags = DB::select([
				'SELECT tid,name
				FROM %tag%
				WHERE `vid` = :vid AND `name` LIKE :name
				ORDER BY `name` ASC',
				'var' => [
					':vid' => $this->id,
					':name' => $this->input.'%'
				],
			]);
		}

		$ret = '<?xml version="1.0" encoding="utf-8" ?><results>';

		foreach ($tags->items as $tag) {
			$ret .= '<rs id="'.$tag->tid.'" info="">'.($pre?$pre.' , ':'').$tag->name.'</rs>';
		}

		$ret .= '</results>';
		die($ret);
	}

	function id() {
		// debugMsg($this, '$this');
		$tagId = SG\getFirstInt(post('id'));
		if (empty($tagId)) return error(_HTTP_ERROR_BAD_REQUEST, 'ข้อมูลไม่ครบถ้วน');

	}

	function items() {
		$childOf = post('childOf');

		$result = (Object) [
			'childOf' => NULL,
			'count' => 0,
			'items' => [],
		];

		$dbs = DB::select([
			'SELECT
			`tag`.`tid` `tagId`
			, `tag`.`name`
			, `hierarchy`.`parent`
			, `tag`.`catId` `categoryId`
			, `tag`.`catParent` `categoryParent`
			FROM %tag% `tag`
				LEFT JOIN %tag_hierarchy% `hierarchy` ON `tag`.`tid` = `hierarchy`.`tid`
			%WHERE%',
			'where' => [
				'%WHERE%' => [
					$childOf ? ['`hierarchy`.`parent` = :parent', ':parent' => $childOf] : NULL
				]
			],
		]);

		$result->count = count($dbs->items);
		$result->items = $dbs->items;

		// $result->query = mydb()->_query;

		if ($childOf) {
			$result->childOf = DB::select([
				'SELECT `tid` `tagId`, `name` FROM %tag% WHERE `tid` = :tid LIMIT 1',
				'var' => [':tid' => $childOf]
			]);
		} else {
			unset($result->childOf);
		}

		return $result;
	}
}
?>