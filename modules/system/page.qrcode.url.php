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

class QrcodeUrl extends Page {
	var $url;

	function __construct() {
		$this->url = post('url');
	}

	function build() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'QR Code Generator',
			]),
			'body' => new Widget([
				'children' => [
					new Form([
						'action' => url('qrcode/url'),
						'method' => 'get',

						'children' => [
							'url' => [
								'type' => 'text',
								'class' => '-fill',
								'label' => 'Enter URL to generate QR Code',
								'value' => $this->url,
								'placeholder' => 'https://www.example.com',
							],
							'go' => [
								'type' => 'button',
								'value' => '<i class="icon -material">qr_code</i><span>สร้าง QR Code</span>',
								'container' => '{class: "-sg-text-right"}',
							],
						],
					]),
					$this->url ? new Container([
						'class' => 'qrcode-result',
						'child' => \SG\qrcode($this->url,'{width: 512, height: 512, domain: false, imgWidth: "200px", imgHeight: "200px"}'),
						]) : NULL,
					'<style type="text/css">
					.qrcode-result {text-align: center;}
					.qrcode-result span {display: block;}
					</style>',
				],
			]),
		]);
	}
}
?>