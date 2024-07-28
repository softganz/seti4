<?php
/**
* Widget  :: Qr Code Generator
* Created :: 2024-07-28
* Modify  :: 2024-07-28
* Version :: 1
*
* @param Array $args
* @return Widget
*
* @usage import('widget:qr.code.php')
* @usage new QrCodeWidget([])
	new QrCodeWidget([
		'id' => 'my-qrcode-id',
		'class' => 'my-qrcode-class -sg-text-center',
		'url' => url('project/'.$this->projectId.'/info.expense/'.$this->actionId),
		'imgWidth' => 200,
		'imgHeight' => 200,
		'showUrl' => true,
		'text' => new Container(['id' => 'text', 'child' => 'อัพโหลดใบเสร็จรับเงินโดยการถ่ายภาพจากสมาร์ทโฟนโดยใช้สมาร์ทโฟนสแกนคิวอาร์โค๊ดนี้ แล้วกด "ส่งภาพใบเสร็จรับเงิน" เลือกกล้องถ่ายรูป']),
		'attribute' => ['style' => 'display: block; margin:0 auto;']
	])
*/

class QrCodeWidget extends Widget {
	var $widgetName = 'QrCode';
	var $version = '0.0.01';
	var $tagName = 'div'; // String
	var $url;
	var $domain;
	var $text;
	var $showUrl = true;
	var $width = 160;
	var $height = 160;
	var $imgWidth = '100%';
	var $imgHeight = '100%';
	var $attribute = [];

	function toString() {
		if (preg_match('/^(http\:|https\:)/', $this->url, $out)) {
			// Full url address
		} else {
			$this->domain = $this->domain ? $this->domain : _DOMAIN;
		}

		$this->children[] = '<img class="-qrcode" '
			. 'src="https://api.qrserver.com/v1/create-qr-code/?size='.$this->width.'x'.$this->height.'&data='.$this->domain.urlencode($this->url)
			. 'alt="QR-Code" '
			. 'width="'.$this->imgWidth.'" '
			. 'height="'.$this->imgHeight.'"'
			. ' />';

		if ($this->showUrl) $this->children[] = '<div class="-url">'.$this->domain.$this->url.'</div>';
		if (isset($this->text)) $this->children[] = $this->_renderEachChildWidget('text', $this->text);

		return parent::toString();
	}
}
?>