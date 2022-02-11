<?php
/**
* iMedCare :: Hello Widget
* Created 2021-08-04
* Modify  2021-08-04
*
* @param Array $args
* @return Widget
*
* @usage new HelloWidget([])
*/

$debug = true;

class HelloWidget extends Widget {
	var $title;
	var $name;
	var $address;

	function __construct($args = []) {
		parent::__construct($args);
	}

	function build() {
		return new Container([
			'class' => 'imed-care-banner',
			'children' => [
				'<div class="-logo"></div>',
				'<div class="-info">',
				'<span class="-welcome">'.($this->title ? $this->title : 'สวัสดี').'</span><span class="-name">'.($this->name ? $this->name : '').'</span>',
				'<span class="-address">'.($this->address ? 'ที่อยู่ '.$this->address : '').'</span>',
				'</div>',
			],

		]);
	}
}
?>
