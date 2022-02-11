<?php
R::manifest('project');
function api_project($self, $tpid = NULL) {
	$q = trim(post('q'));
	$n = intval(SG\getFirst(post('n'),20));
	$p = intval(SG\getFirst(post('p'),1));

	$result=array();

	if (empty($tpid) && empty($q)) {
		return $result;
	} else if ($tpid) {
		$result['success'] = true;
		$projectInfo = R::Model('project.get', $tpid);
		//print_o($projectInfo,1);
		$result['data'] = $projectInfo;

	} else if ($q) {
		$result['success'] = true;
		mydb::where('`title` LIKE :q',':q','%'.$q.'%');
		mydb::value('$LIMIT$', $p - 1);
		mydb::value('$ITEMS$', $n);
		$stmt = 'SELECT `tpid`, `title`
						FROM %project% p
							LEFT JOIN %topic% t USING(`tpid`)
						%WHERE%
						ORDER BY CONVERT(`title` USING tis620) ASC
					LIMIT $LIMIT$ , $ITEMS$';
		$dbs = mydb::select($stmt);
		//print_o($dbs,'$dbs',1);
		//echo mydb()->_query;

		foreach ($dbs->items as $rs) {
			//$desc = $rs->shortname ? $rs->shortname : '';

			$result[] = array(
											'id' => $rs->tpid,
											'title' => htmlspecialchars($rs->title),
											//'desc' => htmlspecialchars($desc),
										);
		}
		if ($dbs->_num_rows>=$n) $result[]=array('value'=>'...','label'=>'ยังมีอีก','desc'=>'');
	} else {
		$result['success'] = false;
		$result['error'] = array('code'=>'E001','message'=>'Invalid Parameter');
	}

	if (debug('api')) {
		$result[]=array('value'=>'query','label'=>$dbs->_query);
		$result[]=array('value'=>'num_rows','label'=>'Result is '.$dbs->_num_rows.' row(s).');
	}
	return $result;
}
?>