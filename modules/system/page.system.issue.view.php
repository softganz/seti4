<?php
/**
* Sysgtem :: Issue View
* Created :: 2022-10-20
* Modify  :: 2022-10-20
* Version :: 1
*
* @param Object $issueInfo
* @return Widget
*
* @usage system/issue/{issueId}
*/

class SystemIssueView extends Page {
	var $issueId;
	var $issueInfo;
	var $right;

 	function __construct($issueInfo = NULL) {
		parent::__construct([
			'issueId' => $issueInfo->issueId,
			'issueInfo' => $issueInfo,
			'right' => (Object) [
				'access' => is_admin(),
			],
		]);
	}
	function build() {
		if (!$this->right->access) {
			return new ErrorMessage([
				'responseCode' => _HTTP_ERROR_FORBIDDEN,
				'text' => 'Access Denied',
			]);
		}


		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'Issue Information',
				'leading' => _HEADER_BACK,
				'boxHeader' => true,
			]), // AppBar
			'body' => new Widget([
				'children' => [
					new Table([
						'children' => array_map(
							function($item, $key) {
								return [$key, is_string($item) ? nl2br($item) : $item];
							},
							(Array) $this->issueInfo, array_keys((Array) $this->issueInfo)
						),
					]), // Table
					// new DebugMsg($this, '$this'),
				], // children
			]), // Widget
		]);
	}
}
?>