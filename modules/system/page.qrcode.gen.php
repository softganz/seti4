<?php
/**
* Module :: Description
* Created 2021-09-22
* Modify  2021-09-22
*
* @param String $arg1
* @return Widget
*
* @usage module/{id}/method
*/

$debug = true;

class QrcodeGen extends Page {
	var $url;

	function __construct() {
		$this->url = post('url');
	}

	function build() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'QR Code',
			]),
			'body' => new Widget([
				'children' => [
					\SG\qrcode($this->url,'{width: 512, height: 512, domain: "'._DOMAIN.'", imgWidth: "200px", imgHeight: "200px"}'),
					''
				],
			]),
		]);
	}
}
?>