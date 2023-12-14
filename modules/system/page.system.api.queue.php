<?php
/**
* System  :: Resend Waiting API
* Created :: 2023-12-06
* Modify  :: 2023-12-06
* Version :: 1
*
* @return Widget
*
* @usage system/api/queue
*/

class SystemApiQueue extends Page {
	var $status = 'WAITING';
	var $items = 1000;
	var $right;

	function __construct() {
		parent::__construct([
			'status' => SG\getFirst(post('status'), $this->status),
			'items' => SG\getFirstInt(post('items'), $this->items),
			'right' => (Object) [
				'access' => is_admin(),
			],
		]);
	}
	function build() {
		if (!$this->right->access) return error(_HTTP_ERROR_FORBIDDEN, 'Access Denied');

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'API Queue',
				'navigator' => new Form([
					'class' => 'sg-form form-report',
					'action' => url('system/api/queue'),
					'rel' => '#main',
					'children' => [
						'status' => [
							'type' => 'select',
							'onChange' => 'submit',
							'options' => ['WAITING' => 'WAITING', 'COMPLETE' => 'COMPLETE', 'SENDING' => 'SENDING'],
						],
						'items' => [
							'type' => 'select',
							'onChange' => 'submit',
							'value' => $this->items,
							'options' => [100 => '100 รายการ', 500 => '500 รายการ', 1000 => '1000 รายการ', 2000 => '2000 รายการ']
						],
					], // children
				]), // Form
			]), // AppBar
			'body' => new ScrollView([
				'child' => new Table([
					'thead' => ['no' => '', 'ID', 'create -date' => 'Date', 'key -nowrap' => 'Key', 'model -nowrap' => 'Model', 'status', 'retry', 'curl -nowrap' => 'curl'],
					'children' => array_map(
						function ($apiItem) {
							static $no = 0;
							// list($class, $method) = explode('::', $apiItem->apiModel);
							// $apiResult = $class::$method('resend', $apiItem);

							return [
								++$no,
								$apiItem->apiId,
								$apiItem->created,
								$apiItem->apiKey,
								$apiItem->apiModel,
								$apiItem->status,
								$apiItem->sendRetry,
								$apiItem->curlParam,
								// new DebugMsg($apiItem, '$apiItem')
							];
						},
						ApiModel::getQueue([
							'status' => $this->status,
							'options' => ['items' => $this->items]
						])->items
					), // children
				]), // Table
			]), // ScrollView
		]);
	}
}
?>