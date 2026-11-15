<?php
/**
 * My       :: Photo List
 * Author   :: Little Bear<softganz@gmail.com>
 * Created  :: 2021-11-27
 * Modified :: 2026-11-15
 * Version  :: 5
 *
 * @uses my/photo
 */

declare(strict_types=1);

namespace My;

use My\Widgets\MySidebarWidget;

class MyPhoto extends \Page {
	/**
	 * Right to build
	 *
	 * @return object|true
	 */
	function rightToBuild(): object|true {
		if (!i()->ok) {
			return new \SignForm();
		}
		return true;
	}

	/**
	 * Build page
	 *
	 * @return object
	 */
	#[\Override]
	function build(): object {
		return new \Scaffold(
			appBar: new \AppBar(title: 'My Photos @' . i()->name),
			sideBar: new MySidebarWidget(),
			body: new \Ui([
				'type' => 'album',
				'children' => array_map(function ($rs) {
					$photo = \FileModel::photoProperty($rs->file, $rs->folder);
					if ($photo->exists) {
						$cardStr =
							'<a class="sg-action" href="'
							. $photo->url
							. '" data-rel="img"><img class="photoitem -'
							. ($photo->width > $photo->height ? 'wide' : 'tall')
							. '" src="'
							. $photo->url
							. '" height="206" width="206" /></a>';
					} else {
						$cardStr =
							'<span title="' . $rs->file . '">Photo not exists</span>';
					}
					return $cardStr;
				}, \DB::select(
					'SELECT `file`, `folder` FROM %topic_files% WHERE `uid` = :uid AND `type` = "photo"',
					var: [':uid' => i()->uid]
				)->items)
			])
		);
	}
}
?>
