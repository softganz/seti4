<?php
/**
 * My       :: View List
 * Author   :: Little Bear<softganz@gmail.com>
 * Created  :: 2021-12-27
 * Modified :: 2026-11-15
 * Version  :: 4
 *
 * @uses my/view
 */

declare(strict_types=1);

namespace My;

use My\Widgets\MySidebarWidget;

class MyView extends \Page {
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
			appBar: new \AppBar(title: 'My Views @' . i()->name),
			sideBar: new MySidebarWidget(),
			body: new \Table(
				header: ['no' => '', 'หัวข้อ', 'atdate -date' => 'เมื่อ'],
				children: array_map(
					function ($rs) {
						static $no = 0;
						$url = '';
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
							'<a href="'
							. url($url)
							. '">'
							. \SG\getFirst($rs->title, '???')
							. '</a>',
							sg_date($rs->dateact, 'd/m/Y')
						];
					},
					\DB::select(
						'SELECT a.*, t.`tpid`, t.`title`, t.`type`
						FROM
						(SELECT r.`refid`, MAX(r.`dateact`) `dateact`
							FROM %reaction% r
							WHERE r.`uid` = :uid AND r.`action` IN ("TOPIC.VIEW")
							GROUP BY r.`refid`
						) a
							LEFT JOIN %topic% t ON t.`tpid` = a.`refid`
						ORDER BY a.`dateact` DESC',
						var: [':uid' => i()->uid]
					)->items
				) // children
			) // Table
		);
	}
}
?>
