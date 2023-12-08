<?php
/**
* Module  :: Description
* Created :: 2023-12-06
* Modify  :: 2023-12-06
* Version :: 1
*
* @param Int $mainId
* @param String $action
* @param Int $tranId
* @return Array/Object
*
* @usage module/api/{id}/{action}[/{tranId}]
*/

class InlineeditApi extends PageApi {
	var $action;

	function __construct($action = NULL) {
		parent::__construct([
			'action' => $action,
		]);
	}

	function foo() {
		$post = (Object) post();
		return ['value' => $post->value, 'post' => $post];
	}
}
?>