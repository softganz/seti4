<?php
function r_saveup_line_tree($lid, $parent = 0, $depth = -1, $max_depth = NULL) {
	static $children, $parents, $terms;

	$depth++;

	// We cache trees, so it's not CPU-intensive to call get_tree() on a term
	// and its children, too.
	if (!isset($children[$lid])) {
		$children[$lid] = array();

		$stmt='SELECT * FROM %saveup_line% WHERE `lid`=:lid LIMIT 1';
		$isHasLine=mydb::select($stmt,':lid',$lid)->lid;
		//debugMsg('$isHasLine='.$isHasLine);

		$stmt='SELECT
							  m.`mid`
							, CONCAT(m.`firstname`," ",m.`lastname`) AS `name`
							, l.`parent`
							, m.`phone`
							, m.`cprovince` as `province`
							FROM %saveup_member% m
								INNER JOIN  %saveup_line% l ON m.`mid`=l.`mid`
							WHERE '.($isHasLine?'l.`lid`=:lid':'l.`mid`=:lid OR l.`parent`=:lid ').'
							ORDER BY CONVERT(m.`firstname` USING tis620) ASC';
		$dbs=mydb::select($stmt,':lid',$lid);
		//debugMsg(mydb()->_query);
		foreach ($dbs->items as $rs) {
			$children[$lid][$rs->parent][] = $rs->mid;
			$parents[$lid][$rs->mid][] = $rs->parent;
			$terms[$lid][$rs->mid] = $rs;
		}
		//debugMsg($children,'$children');
		//debugMsg($terms[$lid],'$terms');

		/*
		$stmt='SELECT
							  m.`mid`
							, CONCAT(m.`firstname`," ",m.`lastname`) AS `name`
							, l.`parent`
							, m.`phone`
							, m.`cprovince` as `province`
							FROM %saveup_member% m
								INNER JOIN  %saveup_line% l ON m.`mid`=l.`mid`
							WHERE l.`lid` = "'.$lid.'"
							ORDER BY m.`firstname`';
		$result = db_querytable($stmt);
		debugMsg(db_query_cmd());
		while ($term = db_fetch_object($result)) {
			$children[$lid][$term->parent][] = $term->mid;
			$parents[$lid][$term->mid][] = $term->parent;
			$terms[$lid][$term->mid] = $term;
			debugMsg($term,'$term');
		}
		*/
	}

	//$lists=sg_parseTree($items,$tree);

	/*
	$max_depth = (is_null($max_depth)) ? count($children[$lid]) : $max_depth;
	debugMsg('Get LID '.$lid.' parent '.$parent);
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
					$tree = array_merge($tree, (array)r_saveup_line_tree($lid, $child, $depth, $max_depth));
				}
		  }
		}
	}
	*/

	/*
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
			  $tree = array_merge($tree, (array)r_saveup_line_tree($lid, $child, $depth, $max_depth));
			}
		  }
		}
	}
	*/
	return $terms[$lid] ? $terms[$lid] : array();
}
?>