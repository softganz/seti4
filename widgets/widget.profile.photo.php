<?php
/**
* Widget :: Profile Photo
* Created 2021-12-24
* Modify 	2021-12-24
*
* @param Array $args
* @return Widget
*
* @usage new ProfilePhotoWidget([])
*/

$debug = true;

class ProfilePhotoWidget extends Widget {
	var $photo;

	// function __construct($args = []) {
	// 	parent::__construct($args);
	// }

	function build() {
		return new Card([
			'class' => 'profile-photo-wrapper',
			'children' => [
				'<div class="-profile-photo">'
					. '<img class="member-photo NO-CACHE" src="'.$this->photo.'" width="100%" height="100%" />'
					. '</div>',
				$this->_renderChildren($this->children()),
			], // children
		]);
	}
}
?>