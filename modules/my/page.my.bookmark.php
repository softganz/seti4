<?php
/**
 * My       :: Bookmark List
 * Author   :: Little Bear<softganz@gmail.com>
 * Created  :: 2021-11-27
 * Modified :: 2026-11-15
 * Version  :: 4
 *
 * @uses my/bookmark
 */

declare(strict_types=1);

namespace My;

use My\Widgets\MySidebarWidget;

class MyBookmark extends \Page {
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
	 * Undocumented function
	 *
	 * @return object
	 */
	function build(): object {
		return new \Scaffold(
			appBar: new \AppBar(title: 'My Bookmarks @' . i()->name),
			sideBar: new MySidebarWidget(),
			body: new \Table(
				header: ['no' => '', 'หัวข้อ', 'atdate -date' => 'เมื่อ'],
				children: array_map(
					function ($rs) {
						static $no = 0;
						$url = '';
						if ($rs->action == 'PDEV.BOOKM') {
							$url = 'project/proposal/' . $rs->tpid;
						} elseif ($rs->type == 'project') {
							$url = 'project/' . $rs->tpid;
						} elseif ($rs->tpid) {
							$url = 'paper/' . $rs->tpid;
						}
						if (empty($url)) {
							return null;
						}
						return [
							++$no,
							'<a href="' . url($url) . '">' . $rs->title . '</a>',
							sg_date($rs->dateact, 'd/m/Y')
						];
					},
					\DB::select(
						'SELECT r.*, t.`tpid`, t.`title`, t.`type`
						FROM %reaction% r
							LEFT JOIN %topic% t ON r.`refid` = t.`tpid`
						WHERE r.`uid` = :uid AND `action` IN ("PROJ.BOOKM", "PDEV.BOOKM", "TOPIC.BOOK")',
						var: [':uid' => i()->uid]
					)->items
				) // children
			) // Table
		);
	}
}
?>
