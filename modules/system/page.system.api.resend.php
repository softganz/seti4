<?php
/**
* System  :: Resend Waiting API
* Created :: 2023-11-21
* Modify  :: 2023-12-27
* Version :: 2
*
* @return Widget
*
* @usage system/api/resend
*/

class SystemApiResend extends Page {
	var $send;
	var $right;

	function __construct($arg1 = NULL) {
		parent::__construct([
			'send' => post('send'),
			'right' => (Object) [
				'access' => is_admin(),
			],
		]);
	}

	function build() {
		if (!$this->right->access) return error(_HTTP_ERROR_FORBIDDEN, 'Access Denied');

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'Resend Waiting API',
				'trailing' => new Row([
					'child' => new Button([
						'type' => 'danger',
						'class' => 'sg-action',
						'href' => url('system/api/resend', ['send' => 'yes']),
						'text' => 'SEND',
						'icon' => new icon('send'),
						'rel' => '#main',
					]), // Button
				]), // Row
			]), // AppBar
			'body' => new ScrollView([
				'child' => new Table([
					'thead' => ['ID', 'Key', 'status', 'retry', 'curl'],
					'children' => array_map(
						function ($apiItem) {
							list($class, $method) = explode('::', $apiItem->apiModel);
							// if ($this->send) debugMsg($apiItem, '$apiItem');
							if ($class && $method && $this->send) $apiResult = $class::$method('resend', $apiItem);

							return [
								$apiItem->apiId,
								$apiItem->apiModel,
								$apiItem->status,
								$apiItem->sendRetry,
								$this->right->access ? $apiItem->curlParam : '',
								// new DebugMsg($apiItem, '$apiItem')
							];
						},
						ApiModel::getWaiting(['updateStatus' => $this->send ? true : false])->items
					), // children
				]), // Table
			]), // ScrollView
		]);
	}
}
?>