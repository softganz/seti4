<?php
/**
 * Category :: Get Category
 * Created  :: 2019-11-01
 * Modify   :: 2019-11-01
 * Version  :: 2
 *
 * @param Object $conditions
 * @param Object $options
 * @return Object Data Set
 *		$options->result = default,group,tree
 */

$debug = true;

function r_category_get($conditions, $key = 'tid', $options = '{}') {
	$defaults = '{debug: false, result: "default", fullValue : false, order: "tg.`weight` ASC, tg.`$KEY$` ASC", selectText: ""}';
	$options = \SG\json_decode($options, $defaults);
	$debug = $options->debug;

	$result = NULL;

	if (is_object($conditions)) ;
	else if (is_array($conditions)) $conditions = (object) $conditions;
	else {
		$taggroup = $conditions;
		$conditions = (Object) ['taggroup' => $taggroup];
	}
	if (empty($conditions->taggroup)) $conditions->taggroup = NULL;

	$joins = [];

	if ($conditions->vid) {
		mydb::where('tg.`vid` = :vid');
		$joins[] = 'LEFT JOIN %tag_hierarchy% tp ON tp.`tid` = tg.`tid` LEFT JOIN %tag% p ON p.`tid` = tp.`parent`';
	}
	if ($conditions->taggroup) {
		mydb::where('tg.`taggroup` = :taggroup');
		$joins[] = 'LEFT JOIN %tag% p ON p.`taggroup` = :taggroup AND p.`catid` = tg.`catparent`';
	}

	if ($conditions->process) {
		mydb::where('tg.`process` = :process', ':process');
	}

	if ($options->condition) mydb::where($options->condition, true);

	mydb::value('$KEY$',$key);
	mydb::value('$JOIN$', implode(_NL, $joins), false);
	mydb::value('$ORDER$', $options->order);

	$stmt = 'SELECT
		  tg.`$KEY$` `catkey`
		, IFNULL(tg.`catparent`, p.`tid`) `parent`
		, tg.*
		, p.`name` `parentName`
		FROM %tag% tg
		$JOIN$
		%WHERE%
		ORDER BY $ORDER$ ;';

	$dbs = mydb::select($stmt, $conditions);

	if ($options->debug) {
		debugMsg($options, '$options');
		debugMsg(mydb()->_query).debugMsg($dbs,'$dbs');
	}

	$result = [];
	if ($options->selectText) $result[''] = $options->selectText;

	if ($options->result == "group") {
		foreach ($dbs->items as $rs) {
			if (empty($rs->parentName)) $result[$rs->name] = [];
		}
		foreach ($dbs->items as $rs) {
			if (empty($rs->parentName)) continue;
			$result[$rs->parentName][$rs->catkey] = $options->fullValue ? $rs : $rs->name;
		}
	} else if ($options->result == "tree") {
		// Create Planning & Proejct Set Tree
		foreach ($dbs->items as $rs) {
			$tree[$rs->catkey] = $rs->parent;
			$items[$rs->catkey] = $rs;
		}
		$categoryTree = sg_printTreeTable($items,sg_parseTree($items,$tree));
		if ($options->debug) debugMsg($categoryTree,'$categoryTree');

		foreach ($categoryTree as $rs) {
			$result[$rs->catkey.':'.$rs->parent] = ($rs->treeLevel ? str_repeat('--',$rs->treeLevel) : '').$rs->name;
		}
	} else {
		foreach ($dbs->items as $rs) {
			$result[$rs->catkey] = $options->fullValue ? $rs : $rs->name;
		}
	}
	if ($options->debug) debugMsg($result,'$result');
	return $result;
}
?>