<?php
/**
* My      :: Photo List
* Created :: 2021-11-27
* Modify  :: 2023-07-25
* Version :: 2
*
* @return Widget
*
* @usage my/photo
*/

import('model:file.php');

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
						$photo = FileModel::photoProperty($rs->file, $rs->folder);
						if ($photo->exists) {
							$cardStr = '<a class="sg-action" href="'.$photo->url.'" data-rel="img"><img class="photoitem -'.($photo->width>$photo->height?'wide':'tall').'" src="'.$photo->url.'" height="206" width="206" /></a>';
						} else {
							$cardStr = '<span title="'.$rs->file.'">Photo not exists</span>';
						}
						return $cardStr;
					},
					mydb::select(
						'SELECT `file`, `folder` FROM %topic_files% WHERE `uid` = :uid AND `type` = "photo"',
						[':uid' => i()->uid]
					)->items
				),
			]),
		]);
	}
}
?>