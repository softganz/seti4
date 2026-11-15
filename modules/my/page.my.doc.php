<?php
/**
 * My       :: My Document List
 * Author   :: Little Bear<softganz@gmail.com>
 * Created  :: 2021-12-27
 * Modified :: 2026-11-15
 * Version  :: 5
 *
 * @uses myd/doc
 */

declare(strict_types=1);

namespace My;

use My\Widgets\MySidebarWidget;

class MyDoc extends \Page {
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
			appBar: new \AppBar(title: 'My Documents @' . i()->name),
			sideBar: new MySidebarWidget(),
			body: new \Table(
				header: [
					'no' => '',
					'หัวข้อ',
					'ประเภท',
					'owner -nowrap' => 'เจ้าของ',
					'atdate -date' => 'เมื่อ'
				],
				children: array_map(
					function ($rs) {
						static $no = 0;
						if ($rs->type == 'project') {
							$url = 'project/' . $rs->tpid;
						} elseif ($rs->type == 'project-develop') {
							$url = 'project/proposal/' . $rs->tpid;
						} elseif ($rs->tpid) {
							$url = 'paper/' . $rs->tpid;
						}
						if (empty($url)) {
							return null;
						}
						return [
							++$no,
							'<a href="' . url($url) . '">' . $rs->title . '</a>',
							$rs->type,
							$rs->topicUid == i()->uid ? 'Is Owner' : $rs->membership,
							sg_date($rs->created, 'd/m/Y')
						];
					},
					\DB::select(
						'SELECT
						u.`membership`
						, t.`tpid`, t.`title`, t.`type`, t.`uid` `topicUid`
						, t.`created`
						FROM %topic% t
							LEFT JOIN %topic_user% u ON u.`tpid` = t.`tpid` AND u.`uid` = :uid
						WHERE t.`uid` = :uid OR u.`uid` = :uid
						ORDER BY t.`tpid` DESC',
						var: [':uid' => i()->uid]
					)->items
				) // children
			) // Table
		);
	}
}
?>
