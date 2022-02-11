<?php
/**
* My :: My Document List
* Created 2021-12-27
* Modify  2021-12-27
*
* @param String $arg1
* @return Widget
*
* @usage myd/doc
*/

class MyDoc extends Page {
	function build() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'My Documents @'.i()->name,
			]),
			'sideBar' => R::View('my.menu'),
			'body' => new Table([
				'thead' => ['no'=>'', 'หัวข้อ','ประเภท','owner -nowrap'=>'เจ้าของ','atdate -date'=>'เมื่อ'],
				'children' => array_map(
					function($rs) {
						static $no = 0;
						if ($rs->type == 'project') $url = 'project/'.$rs->tpid;
						else if ($rs->type == 'project-develop') $url = 'project/proposal/'.$rs->tpid;
						else if ($rs->tpid) $url = 'paper/'.$rs->tpid;
						if (empty($url)) return NULL;
						return [
							++$no,
							'<a href="'.url($url).'">'.$rs->title.'</a>',
							$rs->type,
							($rs->topicUid == i()->uid ? 'Is Owner': $rs->membership),
							sg_date($rs->created, 'd/m/Y'),
						];
					},
					mydb::select(
						'SELECT
						u.`membership`
						, t.`tpid`, t.`title`, t.`type`, t.`uid` `topicUid`
						, t.`created`
						FROM %topic% t
							LEFT JOIN %topic_user% u ON u.`tpid` = t.`tpid` AND u.`uid` = :uid
						WHERE t.`uid` = :uid OR u.`uid` = :uid
						ORDER BY t.`tpid` DESC',
						[':uid' => i()->uid]
					)->items
				), // children
			]), // Table
		]);
	}
}
?>