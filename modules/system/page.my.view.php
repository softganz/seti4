<?php
/**
* My :: View List
* Created 2021-12-27
* Modify  2021-12-27
*
* @return Widget
*
* @usage my/view
*/

class MyView extends Page {
	function build() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'My Views @'.i()->name,
			]),
			'sideBar' => R::View('my.menu'),
			'body' => new Table([
				'thead' => ['no'=>'', 'หัวข้อ','atdate -date'=>'เมื่อ'],
				'children' => array_map(
					function($rs) {
						static $no = 0;
						$url = '';
						if ($rs->type == 'project') $url = 'project/'.$rs->tpid;
						else if ($rs->type == 'project-develop') $url = 'project/proposal/'.$rs->tpid;
						else if ($rs->tpid) $url = 'paper/'.$rs->tpid;
						if (empty($url)) return NULL;
						return [
							++$no,
							'<a href="'.url($url).'">'.SG\getFirst($rs->title,'???').'</a>',
							sg_date($rs->dateact, 'd/m/Y'),
						];
					},
					mydb::select(
						'SELECT a.*, t.`tpid`, t.`title`, t.`type`
						FROM
						(SELECT r.`refid`, MAX(r.`dateact`) `dateact`
							FROM %reaction% r
							WHERE r.`uid` = :uid AND r.`action` IN ("TOPIC.VIEW")
							GROUP BY r.`refid`
						) a
							LEFT JOIN %topic% t ON t.`tpid` = a.`refid`
						ORDER BY a.`dateact` DESC',
						[':uid' => i()->uid]
					)->items
				), // children
			]), // Table
		]);
	}
}
?>