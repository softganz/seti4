<?php
/**
* Project :: API GL Code
* Created 2021-12-12
* Modify  2021-12-12
*
* @return Widget
*
* @usage project/nhsi/api/gl/code
*/

class ProjectNhsoApiGlCode extends Page {
	function build() {
		$result = (Object) [
			'description' => 'GL Code',
			'count' => 0,
			'items' => [],
		];

		$result->items = mydb::select(
			'SELECT `glCode` `code`, `glParent` `parent`, `glType` `type`, `glName` `name`
			FROM %glcode%
			ORDER BY `glCode` ASC'
		)->items;

		$result->count = count($result->items);

		return $result;
	}
}
?>