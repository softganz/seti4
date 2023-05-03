<?php
/**
* System  :: Re-location to new location
* Created :: 2023-04-25
* Modify  :: 2023-04-25
* Version :: 1
*
* @param String $url
* @return Widget
*
* @usage system/location/to/$url
*/

class SystemLocationTo extends Page {
	var $url;

	function __construct($url = NULL) {
		parent::__construct([
			'url' => $url
		]);
	}

	function build() {
		$q = q();
		$newUrl = 'https://'.$this->url.'/'.$q;
		print_o(post(), 'post()',1);
		echo '$newUrl = '.$newUrl.'<br />';
		echo url('https://'.$this->url, post());
		echo '<br />';
		location('https://'.$this->url, post());
	}
}
?>