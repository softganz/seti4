<?php
/**
 * Model   :: Category
 * Author  :: Little Bear<softganz@gmail.com>
 * Created :: 2022-08-10
 * Modify  :: 2026-05-28
 * Version :: 4
 *
 * @usage new CategoryModel([])
 * @usage CategoryModel::function($conditions, $options)
 */

use Softganz\DB;

class CategoryModel {
	public static function get($conditions, $options = '{}'): array {
		$defaults = '{debug: false, result: "default", key: "catId", fullValue : false, order: "tg.`weight` ASC, tg.`$KEY$` ASC", selectText: "", condition: null}';
		$options = SG\json_decode($options, $defaults);
		$debug = $options->debug;

		if (is_object($conditions)) ;
		else if (is_array($conditions)) $conditions = (Object) $conditions;
		else {
			$conditions = (Object) ['group' => $conditions];
		}
		if (empty($conditions->group)) $conditions->group = null;

		$fullValue = $conditions->fullValue ?? $options->fullValue;

		$dbs = DB::select([
			'SELECT
			  tg.`$KEY$` `catkey`
			, `tg`.`catId`
			, IFNULL(tg.`catparent`, p.`tid`) `parent`
			, tg.*
			, p.`name` `parentName`
			FROM %tag% tg
			$JOIN$
			%WHERE%
			ORDER BY $ORDER$',
			'%WHERE%' => [
				$conditions->vid ? ['tg.`vid` = :vid', ':vid' => $conditions->vid] : null,
				$conditions->group ? ['tg.`tagGroup` = :tagGroup', ':tagGroup' => $conditions->group] : null,
				$conditions->process ? ['tg.`process` = :process', ':process' => $conditions->process] : null,
				$options->condition ? ['$CONDITION$'] : null,
			],
			'var' => [
				'$CONDITION$' => $options->condition,
				'$JOIN$' => implode(_NL, [
					$conditions->vid ? 'LEFT JOIN %tag_hierarchy% tp ON tp.`tid` = tg.`tid` LEFT JOIN %tag% p ON p.`tid` = tp.`parent`' : null,
					$conditions->group ? 'LEFT JOIN %tag% p ON p.`tagGroup` = :tagGroup AND p.`catid` = tg.`catParent`' : null
				]),
				'$ORDER$' => $options->order,
				'$KEY$' => SG\getFirst($conditions->key, 'catId'),
			],
		]);

		if ($options->debug) debugMsg(R('query')) . debugMsg($dbs, '$dbs');

		$result = [];
		if ($options->selectText) $result[''] = $options->selectText;

		if ($options->result === "group") {
			foreach ($dbs->items as $rs) {
				if (empty($rs->parentName)) $result[$rs->name] = [];
			}
			foreach ($dbs->items as $rs) {
				if (empty($rs->parentName)) continue;
				$result[$rs->parentName][$rs->catkey] = $fullValue ? $rs : $rs->name;
			}
		} else if ($options->result === "tree") {
			// Create Planning & Proejct Set Tree
			$$tree = [];
			$items = [];
			foreach ($dbs->items as $rs) {
				$tree[$rs->catkey] = $rs->parent;
				$items[$rs->catkey] = $rs;
			}
			$categoryTree = sg_printTreeTable($items,sg_parseTree($items,$tree));
			if ($options->debug) debugMsg($categoryTree,'$categoryTree');

			foreach ($categoryTree as $rs) {
				$result[$rs->catkey . ':' . $rs->parent] = ($rs->treeLevel ? str_repeat('--', $rs->treeLevel) : '') . $rs->name;
			}
		} else {
			foreach ($dbs->items as $rs) {
				$result[$rs->catkey] = $fullValue ? $rs : $rs->name;
			}
		}
		if ($options->debug) debugMsg($result, '$result');
		return $result;
	}
}
?>
