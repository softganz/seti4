<?php
/**
* System  :: Resend Waiting API
* Created :: 2023-11-21
* Modify  :: 2023-11-23
* Version :: 1
*
* @return Widget
*
* @usage system/api/resend
*/

class SystemApiResend extends Page {
	// var $arg1;

	// function __construct($arg1 = NULL) {
	// 	parent::__construct([
	// 		'arg1' => $arg1
	// 	]);
	// }

	function build() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'Resend Waiting API',
			]), // AppBar
			'body' => new ScrollView([
				'child' => new Table([
					'thead' => ['ID', 'Key', 'status', 'retry', 'curl'],
					'children' => array_map(
						function ($apiItem) {
							list($class, $method) = explode('::', $apiItem->apiModel);
							$apiResult = $class::$method('resend', $apiItem);

							return [
								$apiItem->apiId,
								$apiItem->apiModel,
								$apiItem->status,
								$apiItem->sendRetry,
								$apiItem->curlParam,
								// new DebugMsg($apiItem, '$apiItem')
							];
						},
						ApiModel::getWaiting()->items
					), // children
				]), // Table
			]), // ScrollView
		]);
	}
}
?>