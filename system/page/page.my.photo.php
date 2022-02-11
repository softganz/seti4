<?php
/**
* My :: Photo List
* Created 2021-11-27
* Modify  2021-11-27
*
* @return Widget
*
* @usage my/photo
*/

class MyPhoto extends Page {
	function build() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'My Photos @'.i()->name,
			]),
			'sideBar' => R::View('my.menu'),
			'body' => new Ui([
				'type' => 'album',
				'children' => array_map(
					function($rs) {
						$photo = model::get_photo_property($rs->file);
						$cardStr = '';
						if ($photo->_exists) {
							$cardStr .= '<a class="sg-action" href="'.$photo->_src.'" data-rel="img"><img class="photoitem -'.($photo->_size->width>$photo->_size->height?'wide':'tall').'" src="'.$photo->_src.'" height="206" width="206" /></a>';
						} else {
							$cardStr .= '<span title="'.$rs->file.'">Photo not exists</span>';
						}
						return $cardStr;
					},
					mydb::select(
						'SELECT * FROM %topic_files% WHERE `uid` = :uid AND `type` = "photo"',
						[':uid' => i()->uid]
					)->items
				),
			]),
		]);
	}
}
?>