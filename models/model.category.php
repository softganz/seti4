<?php
/**
* Model :: Category
* Created 2022-08-10
* Modify 	2022-08-10
*
* @param Array $args
* @return Object
*
* @usage new CategoryModel([])
* @usage CategoryModel::function($conditions, $options)
*/

class CategoryModel {
	function __construct($args = []) {
	}

	public static function get($conditions, $options = '{}') {
		$defaults = '{debug: false, result: "default", fullValue : false, order: "tg.`weight` ASC, tg.`$KEY$` ASC", selectText: ""}';
		$options = \SG\json_decode($options, $defaults);
		$debug = $options->debug;

		$result = NULL;

		if (is_object($conditions)) ;
		else if (is_array($conditions)) $conditions = (object) $conditions;
		else {
			$conditions = (Object) ['group' => $conditions];
		}
		if (empty($conditions->group)) $conditions->group = NULL;

		$key = \SG\getFirst($conditions->key, 'catId');

		$joins = [];

		if ($conditions->vid) {
			mydb::where('tg.`vid` = :vid');
			$joins[] = 'LEFT JOIN %tag_hierarchy% tp ON tp.`tid` = tg.`tid` LEFT JOIN %tag% p ON p.`tid` = tp.`parent`';
		}
		if ($conditions->group) {
			mydb::where('tg.`tagGroup` = :tagGroup', ':tagGroup', $conditions->group);
			$joins[] = 'LEFT JOIN %tag% p ON p.`tagGroup` = :tagGroup AND p.`catid` = tg.`catParent`';
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

		if ($options->debug) debugMsg(mydb()->_query).debugMsg($dbs,'$dbs');

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
}
?>