<?php
/**
* iMed :: Care Service Menu
* Created 2021-05-26
* Modify  2021-07-13
*
* @return Widget
*
* @usage imed/care/service/menu
*/

$debug = true;

import('package:imed/care/models/model.service.menu.php');

class ImedCareOurMenu extends Page {
	var $serviceId;

	function __construct($serviceId = NULL) {
		$this->serviceId = $serviceId;
	}

	function build() {
		if ($this->serviceId) return $this->_serviceDetail();

		return new Scaffold([
			'appBar' => new AppBar(['title' => 'เมนูการให้บริการ']),
			'body' => new Row([
				'class' => 'imed-care-menu -block',
				'children' => (function() {
					$result = [];
					foreach (ServiceMenuModel::items() as $value) {
						$result[] = '<a class="sg-action" href="'.url('imed/care/our/menu/'.$value->serviceId).'" data-rel="box" data-width="480"><i class="icon -imed-care"><img src="https://communeinfo.com/'.$value->icon.'" /></i><span>'.$value->name.'</span></a>';
					}
					return $result;
				})(), // children
			]),
		]); // Scaffold
	}

	function _serviceDetail() {
		$serviceInfo = ServiceMenuModel::get($this->serviceId);
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => $serviceInfo->name,
				'leading' => _HEADER_BACK,
				'boxHeader' => true,
			]), // AppBar
			'body' => new Container([
				'children' => [
					new Column([
						'children' => [
							'<img src="https://communeinfo.com/'.$serviceInfo->icon.'" height="300" style="max-height: 300px; display: block; margin: 0 auto 32px; padding-top: 32px; border-radius: 50%;" />',
							'<h3 class="-sg-text-center">'.$serviceInfo->name.'</h3>',
							'<strong>'.$serviceInfo->detail.'</strong>',
							nl2br($serviceInfo->description),
						], // Children
					]), // Column
				], // children
			]), // Container
		]);
	}
}
?>