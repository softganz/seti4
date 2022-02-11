<?php
/**
* Project Model :: Get Follow List
* Created 2021-01-01
* Modify  2021-01-01
*
* @param Object $conditions
* @param Object $options
* @return Object Data Set
*
* @usage R::Model("project.follows", $condition, $options)
*/

$debug = true;

function r_project_follows($conditions, $options = '{}') {
	$defaults = '{debug: false, start: 0, items: 50, order: "p.`tpid`", sort: "ASC", key: null, value: null}';
	$options = sg_json_decode($options, $defaults);
	$debug = $options->debug;

	$result = new MyDbResult;

	if (is_string($conditions) && preg_match('/^{/',$conditions)) {
		$conditions = SG\json_decode($conditions);
	} else if (is_object($conditions)) {
		//
	} else if (is_array($conditions)) {
		$conditions = (Object) $conditions;
	} else {
		$conditions = (Object) ['id' => $conditions];
	}

	if ($debug) debugMsg($conditions, '$conditions');

	if ($conditions->projectType == 'All') {
		// Get All Type
	} else if ($conditions->projectType) {
		mydb::where('p.`prtype` IN ( :projectType)', ':projectType', 'SET-STRING:'.$conditions->projectType);
	} else {
		mydb::where('p.`prtype` = :projectType', ':projectType', 'โครงการ');
	}

	if ($conditions->userId == 'member') {
		mydb::where('tu.`uid` = :userId', ':userId', i()->uid);
	} else if ($conditions->userId) {
		mydb::where('t.`uid` = :userId', ':userId', $conditions->userId);
	}

	if ($conditions->childOf) {
		mydb::where('t.`parent` IN ( :parent )', ':parent', 'SET:'.$conditions->childOf);
	}

	if ($conditions->orgId) mydb::where('t.`orgid` = :orgId', ':orgId', $conditions->orgId);

	if ($conditions->childOfOrg) {
		mydb::where('o.`parent` IN ( :orgParent )', ':orgParent', 'SET:'.$conditions->childOfOrg);
	}

	// 'กำลังดำเนินโครงการ','ดำเนินการเสร็จสิ้น','ยุติโครงการ','ระงับโครงการ'
	if ($conditions->status == 'process') {
		mydb::where('p.`project_status` IN ("กำลังดำเนินโครงการ")');
	} else if ($conditions->status == 'done') {
		mydb::where('p.`project_status` IN ("ดำเนินการเสร็จสิ้น")');
	} else if ($conditions->status == 'block') {
		mydb::where('p.`project_status` IN ("ระงับโครงการ")');
	} else if ($conditions->status == 'stop') {
		mydb::where('p.`project_status` IN ("ยุติโครงการ")');
	} else if ($conditions->status == 'all') {
	} else {
		mydb::where('p.`project_status` IN ("กำลังดำเนินโครงการ", "ดำเนินการเสร็จสิ้น")');
	}

	if ($conditions->changwat) {
		mydb::where('t.`areacode` LIKE :changwat', ':changwat', $conditions->changwat.'%');
	}

	if ($conditions->budgetYear) {
		mydb::where('p.`pryear` = :budgetYear', ':budgetYear', $conditions->budgetYear);
	}

	if ($conditions->ownerType) {
		mydb::where('p.`ownertype` IN ( :ownerType )', ':ownerType', 'SET-STRING:'.$conditions->ownerType);
	}

	if ($conditions->title) {
		//mydb::where('(t.`title` LIKE :title)', ':title', '%'.$conditions->title.'%');
		$q = preg_replace('/\s+/', ' ', $conditions->title);
		if (preg_match('/^code:(\w.*)/', $q, $out)) {
			mydb::where('p.`tpid` = :tpid', ':tpid', $out[1]);
		} else {
			$searchList = explode('+', $q);
			//debugMsg('$q = '.$q);
			//debugMsg($searchList, '$searchList');
			$qLists = array();
			foreach ($searchList as $key => $str) {
				$str = trim($str);
				if ($str == '') continue;
				$qLists[] = '(t.`title` RLIKE :q'.$key.')';

				//$str=mysqli_real_escape_string($str);
				$str = preg_replace('/([.*?+\[\]{}^$|(\)])/','\\\\\1',$str);
				$str = preg_replace('/(\\\[.*?+\[\]{}^$|(\)\\\])/','\\\\\1',$str);

				// this comment for correct sublimetext syntax highlight
				// $str=preg_replace('/(\\[.*?+\[\]{}^$|(\)\\])/','\\\\\1',$str);

				// Replace space and comma with OR condition
				mydb::where(NULL, ':q'.$key, str_replace([' ',','], '|', $str));
			}
			if ($qLists) mydb::where('('.(is_numeric($q) ? 'p.`tpid` = :q OR ' : '').implode(' AND ', $qLists).')', ':q', $q);
		}

	} else if ($conditions->search) {
		mydb::where('(t.`title` LIKE :title OR p.`agrno` LIKE :title OR p.`prid` LIKE :title)', ':title', '%'.$conditions->search.'%');
	}

	mydb::value('$ORDER$', 'ORDER BY '.$options->order.' '.$options->sort);
	mydb::value('$LIMIT$', $options->items == '*' ? '' : 'LIMIT '.$options->sta.' '.$options->items);

	$stmt = 'SELECT
		p.`tpid` `projectId`
		, t.`title`
		, t.`orgid` `orgId`
		, p.*
		, t.`areacode`
		, CONCAT(X(p.`location`), ",", Y(p.`location`)) `location`
		, o.`name` `orgName`
		, cop.`provname` `changwatName`
		, u.`username`, u.`name` `ownerName`
		, (SELECT COUNT(*) FROM %topic% t WHERE t.`parent` = p.`tpid`) `childCount`
		, DATE_FORMAT(t.`created`, "%Y-%m-%d %H:%i:%s") `created`
		FROM %project% p
			LEFT JOIN %topic% t USING(`tpid`)
			LEFT JOIN %users% u ON u.`uid` = t.`uid`
			LEFT JOIN %db_org% o ON o.`orgid` = t.`orgid`
			LEFT JOIN %co_province% cop ON cop.`provid` = LEFT(o.`areacode`, 2)
			LEFT JOIN %topic_user% tu USING(`tpid`)
		%WHERE%
		GROUP BY `projectId`
		$ORDER$
		$LIMIT$;
		-- '
		. json_encode(
			[
				'key' => $options->key,
				'value' => $options->value,
				'sum' => 'budget',
			]
		);

	$result = mydb::select($stmt);

	if ($debug) debugMsg(mydb()->_query);

	return $result;
}
?>