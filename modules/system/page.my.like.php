<?php
/**
 * My       :: Like List
 * Author   :: Little Bear<softganz@gmail.com>
 * Created  :: 2021-11-27
 * Modified :: 2026-11-12
 * Version  :: 3
 *
 * @uses my/like
 */

use Softganz\DB;

class MyLike extends Page {
	/**
	 * Right to build
	 *
	 * @return object|true
	 */
	function rightToBuild(): object|true {
		if (!i()->ok) {
			return new SignForm();
		}
		return true;
	}

	function build() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'My Likes @' . i()->name
			]),
			'sideBar' => R::view('my.menu'),
			'body' => new Table([
				'thead' => ['no' => '', 'หัวข้อ', 'atdate -date' => 'เมื่อ'],
				'children' => array_map(
					function ($rs) {
						static $no = 0;
						$url = '';
						if ($rs->action == 'PDEV.LIKE') {
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
					DB::select([
						'SELECT r.*, t.`tpid`, t.`title`, t.`type`
						FROM %reaction% r
							LEFT JOIN %topic% t ON r.`refid` = t.`tpid`
						WHERE r.`uid` = :uid AND `action` IN ("PROJ.LIKE", "PDEV.LIKE", "TOPIC.LIKE")',
						'var' => [':uid' => i()->uid]
					])->items
				) // children
			]) // Table
		]);
	}
}
?>
