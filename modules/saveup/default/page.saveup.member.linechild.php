<?php
function saveup_member_linechild($self, $parent = NULL) {

	if ($parent) $lines=__saveup_member_linechild_tree($parent);


	$tables = new Table();
	$tables->addClass('saveup-line-list');
	foreach ($lines as $line) {
		if ($line->mid==$parent) continue;
		$tables->rows[] = array(
												str_repeat('|--', $line->depth).$line->name
												. ' ('.$line->mid.')',
												'',
												$line->phone,
												$line->province,
												'<a href="'.url('saveup/member/view/'.$line->mid).'" title="ดูรายละเอียด"><i class="icon -view"></i></a>',
											);
		$ret.='</tr>';
	}
	$ret .= $tables->build();
	return $ret;
}

function __saveup_member_linechild_tree($lid, $parent = 0, $depth = -1, $max_depth = NULL) {
	static $children, $parents, $terms;

	$depth++;

	// We cache trees, so it's not CPU-intensive to call get_tree() on a term
	// and its children, too.
	if (!isset($children[$lid])) {
		$children[$lid] = array();

		$sql_cmd='SELECT
							  m.`mid`, CONCAT(m.`firstname`," ",m.`lastname`) AS `name`
							, l.`parent` , `phone`, `cprovince` as `province`
							FROM %saveup_member% m
								INNER JOIN  %saveup_line% l ON m.`mid` = l.`mid`
							WHERE m.`status` = "active" AND l.`lid` = "'.$lid.'"
							ORDER BY firstname';

		$dbs = mydb::select($sql_cmd);

		foreach ($dbs->items as $rs) {
			$children[$lid][$rs->parent][] = $rs->mid;
			$parents[$lid][$rs->mid][] = $rs->parent;
			$terms[$lid][$rs->mid] = $rs;
		}
	}
	$max_depth = (is_null($max_depth)) ? count($children[$lid]) : $max_depth;

	if ($children[$lid][$parent]) {
		foreach ($children[$lid][$parent] as $child) {
		  if ($max_depth > $depth) {
			$term = sg_clone($terms[$lid][$child]);
			$term->depth = $depth;
			// The "parent" attribute is not useful, as it would show one parent only.
			unset($term->parent);
			$term->parents = $parents[$lid][$child];
			$tree[] = $term;

			if ($children[$lid][$child]) {
			  $tree = array_merge($tree, (array)__saveup_member_linechild_tree($lid, $child, $depth, $max_depth));
			}
		  }
		}
	}
	return $tree ? $tree : array();
}


?>