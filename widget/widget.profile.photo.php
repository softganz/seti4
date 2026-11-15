<?php
/**
 * Widget   :: Profile Photo
 * Author   :: Little Bear<softganz@gmail.com>
 * Created  :: 2021-12-24
 * Modified	:: 2026-07-29
 * Version  :: 2
 *
 * @param array $args
 *
 * @uses new ProfilePhotoWidget([])
 */

class ProfilePhotoWidget extends \Widget {
	public string $photo;

	function __construct(string $photo, mixed $children = null) {
		parent::__construct();
		$this->photo = $photo;
		$this->children = $children;
	}

	function build() {
		return new Card([
			'class' => 'profile-photo-wrapper',
			'children' => [
				'<div class="-profile-photo">'
				. '<img class="member-photo NO-CACHE" src="'
				. $this->photo
				. '" width="100%" height="100%" />'
				. '</div>',
				$this->renderChildren($this->children())
			] // children
		]);
	}
}
?>
