<?php
/**
* Project :: Tree Listing
* Created 2019-04-04
* Modify  2021-01-18
*
* @return Widget
*
* @usage project/tree
*/

import('widget:project.follow.nav.php');

class ProjectTree extends Page {
	var $userId;
	var $order;
	var $sort;

	function __construct() {
		$this->userId = post('uid');
		$this->order = post('o');
		$this->sort = post('s');
	}

	function build() {
		// if ($this->userId) {
		// 	R::View('project.toolbar',$self,'Project Tree','my');
		// } else {
		// 	R::View('project.toolbar',$self,'Project Tree');
		// }

		$isAdmin = user_access('administer projects');
		$isCreatable = user_access('create project set');


		// Select set
		if ($this->userId) mydb::where('tu.`uid` = :uid', ':uid',$this->userId);

		$orderList = array(
			'date' => 't.`tpid`',
			'title' => 'CONVERT(t.`title` USING tis620)',
		);

		mydb::value('$SORT$', $this->sort == 'd' ? 'DESC' : 'ASC');
		if ($this->order) {
			$orderBy = $orderList[$this->order];
		}
		if (empty($orderBy)) $orderBy = $orderList['title'];
		mydb::value('$ORDERBY$', $orderBy);

		$stmt = 'SELECT DISTINCT
			  t.`tpid`, t.`title`, p.`prtype`, t.`parent`
			  , p.`pryear`
			  , UPPER(tu.`membership`) `membership`
			  , COUNT(t.`tpid`)
			  , (SELECT COUNT(*) FROM %project_tr% WHERE `tpid` = p.`tpid` AND `formid` = "info" AND `part` = "activity") `totalActivity`
			  , (SELECT COUNT(*) FROM %project_tr% WHERE `tpid` = p.`tpid` AND `formid` = "activity" AND `part` = "owner") `totalAction`
			FROM %project% p
				LEFT JOIN %topic% t USING(`tpid`)
				LEFT JOIN %topic_user% tu ON tu.`tpid` = p.`tpid`
			%WHERE%
			GROUP BY `tpid`
			ORDER BY
				FIELD(`prtype`,"แผนงาน","ชุดโครงการ","โครงการ")
				, `pryear`
				, $ORDERBY$ $SORT$
			';

		$projectSet = mydb::select($stmt);

		//$ret .= mydb()->_query;
		//$ret .= print_o($projectSet, '$projectSet');

		// Create Planning & Proejct Set Tree
		foreach ($projectSet->items as $rs) {
			$tree[$rs->tpid] = $rs->parent;
			$items[$rs->tpid] = $rs;
		}
		$planningTree = sg_printTreeTable($items,sg_parseTree($items,$tree));

		//$ret .= print_o($tree,'$tree');
		//$ret .= print_o($items,'$items');

		//$ret .= print_o($planningTree,'$planningTree');

		foreach ($planningTree as $rs) {
			if ($rs->prtype == 'แผนงาน') $class = 'planning';
			else if ($rs->prtype == 'ชุดโครงการ') $class = 'set';
			else $class = 'follow';
			$ret .= '<div class="tree -'.$class.' '
				. ($rs->treeLevel ? '-level -level-'.$rs->treeLevel : '-level -level-0')
				. '">'
				. ($rs->treeLevel ? '<span class="-level-space -sp-'.$rs->treeLevel.'"><span class="-level-line"></span></span>' : '')
				. '<span class="title">'
				. '<a href="'.url('project/'.$rs->tpid).'">'.SG\getFirst($rs->title,'???').'</a>'
				. ($rs->prtype == 'โครงการ' ? ' (กิจกรรม '.$rs->totalAction.'/'.$rs->totalActivity.')' : '')
				. '</span>'
				. '<span>('.$rs->prtype.' ปี '.sg_date($rs->pryear,'ปปปป').')</span>'
				. '</div>';
		}
		//$ret .= print_o($planningTree, '$planningTree');

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'Project Tree',
				'navigator' => $this->userId ? new ProjectFollowNavWidget() : NULL,
			]), // AppBar
			'body' => new Widget([
				'children' => [
					$ret,
					$this->_script(),
				], // children
			]), // Widget
		]);
	}

	function _script() {
		head('<style type="text/css">
			.tree {position: relative; display: flex; flex-wrap: nowrap; justify-content: space-between;}

			.-level-space {display: inline-block;}
			.-level-space>.-level-line {width: 16px; display: inline-block; border-top: 1px #888 solid; margin-top: 1em;}

			.tree.-level {}
			.tree.-level:not(.-level-0) {margin-left: 16px; border-left: 2px #666 solid;}
			.tree.-level-0 {margin: 32px 0 0 0;}
			.tree.-level:not(.-level-1):before {border-left: 2px #666 solid;}
			.tree.-level-2:before {margin-left: 24px; content: "";}
			.tree.-level-3:before {margin-left: 24px; content: "";}
			.tree.-level-3 .-level-space>.-level-line {width: 32px;}
			.tree.-level-4:before {margin-left: 24px; content: "";}
			.tree.-level-4 .-level-space>.-level-line {width: 48px;}
			.tree.-level-5:before {margin-left: 24px; content: "";}
			.tree.-level-5 .-level-space>.-level-line {width: 64px;}

			.tree.-level-0 .title {font-size: 1.2em;}
			.tree.-planning .title {font-weight: bold;}
			.tree .title {padding: 4px; flex: 1 1;}
			.tree.-set .title {font-weight: bold; flex: 1 1;}
			</style>'
		);
	}
}
?>