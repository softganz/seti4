<?php
/**
* My :: Bookmark List
* Created 2021-11-27
* Modify  2021-11-27
*
* @return Widget
*
* @usage my/bookmark
*/

class MyBookmark extends Page {
	function build() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'My Bookmarks @'.i()->name,
			]),
			'sideBar' => R::View('my.menu'),
			'body' => new Table([
				'thead' => ['no'=>'', 'หัวข้อ','atdate -date'=>'เมื่อ'],
				'children' => array_map(
					function($rs) {
						static $no = 0;
						$url = '';
						if ($rs->action == 'PDEV.BOOKM') $url = 'project/proposal/'.$rs->tpid;
						else if ($rs->type == 'project') $url = 'project/'.$rs->tpid;
						else if ($rs->tpid) $url = 'paper/'.$rs->tpid;
						if (empty($url)) return NULL;
						return [
							++$no,
							'<a href="'.url($url).'">'.$rs->title.'</a>',
							sg_date($rs->dateact, 'd/m/Y'),
						];
					},
					mydb::select(
						'SELECT r.*, t.`tpid`, t.`title`, t.`type`
						FROM %reaction% r
							LEFT JOIN %topic% t ON r.`refid` = t.`tpid`
						WHERE r.`uid` = :uid AND `action` IN ("PROJ.BOOKM", "PDEV.BOOKM", "TOPIC.BOOK")',
						[':uid' => i()->uid]
					)->items
				), // children
			]), // Table
		]);
	}
}
?>