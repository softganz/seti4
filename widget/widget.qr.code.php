<?php
/**
 * Widget   :: Qr Code Generator
 * Author   :: Little Bear<softganz@gmail.com>
 * Created  :: 2024-07-28
 * Modified :: 2026-11-12
 * Version  :: 6
 *
 * @uses import('widget:qr.code.php')
 * @uses new QrCodeWidget([
 *		'id' => 'my-qrcode-id',
 *		'class' => 'my-qrcode-class -sg-text-center',
 *		'url' => url('project/'.$this->projectId.'/info.expense/'.$this->actionId),
 *		'imgWidth' => 200,
 *		'imgHeight' => 200,
 *		'showUrl' => true,
 *		'text' => new Container(['id' => 'text', 'child' => 'อัพโหลดใบเสร็จรับเงินโดยการถ่ายภาพจากสมาร์ทโฟนโดยใช้สมาร์ทโฟนสแกนคิวอาร์โค๊ดนี้ แล้วกด "ส่งภาพใบเสร็จรับเงิน" เลือกกล้องถ่ายรูป']),
 *		'attribute' => ['style' => 'display: block; margin:0 auto;']
 *	])
 */

class QrCodeWidget extends Widget {
	protected string $widgetName = 'QrCode';
	protected string $version = '0.0.01';
	public $tagName = 'div'; // String
	public $url;
	public $domain;
	public $text;
	public $showUrl = true;
	public $width = 160;
	public $height = 160;
	public $imgWidth = '100%';
	public $imgHeight = '100%';

	function toString() {
		if (preg_match('/^(http\:|https\:)/', $this->url, $out)) {
			// Full url address
		} else {
			$this->domain = $this->domain ? $this->domain : _DOMAIN;
		}

		$this->children[] =
			'<img class="-qrcode" '
			. 'src="https://api.qrserver.com/v1/create-qr-code/?size='
			. $this->width
			. 'x'
			. $this->height
			. '&data='
			. $this->domain
			. urlencode($this->url)
			. '" '
			. 'alt="QR-Code" '
			. 'width="'
			. $this->imgWidth
			. '" '
			. 'height="'
			. $this->imgHeight
			. '" '
			. '/>';

		if ($this->showUrl) {
			$this->children[] =
				'<div class="-url">' . $this->domain . $this->url . '</div>';
		}
		if (isset($this->text)) {
			$this->children[] = $this->renderEachChildWidget($this->text, 'text');
		}

		return parent::toString();
	}
}
?>
