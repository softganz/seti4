<?php
/**
 * Search from organization name
 *
 * @param String $q or $_GET['q'] or $_POST['q'] : Substring in name
 * @param Integer $n or $_GET['n'] or $_POST['n'] : Item for result
 * @param Integer $p or $_GET['p'] or $_POST['p'] : Page for result
 * @return json[{value:org_id, label:org_name},...]
 */
function api_ampur($self, $q = NULL, $n = NULL, $p = NULL) {
	$q = SG\getFirst($q, trim(post('q')));
	$n = intval(SG\getFirst($item, post('n'), post('n'), 500));
	$p = intval(SG\getFirst($p, post('p'), post('p'), 1));

	$result = array();

	if (empty($q)) return $result;

	$stmt = 'SELECT `distid`, `distname`, `provname`
				FROM %co_district% co
					LEFT JOIN %co_province% cop ON cop.`provid` = LEFT(co.`distid`,2)
				WHERE
				'.(is_numeric($q) ? 'cop.`provid` = :provid' : '`distname` LIKE :q OR `provname` LIKE :q').'
					AND RIGHT(`distname`,1) != "*"
				ORDER BY CONVERT(`distname` USING tis620) ASC
				LIMIT '.($p-1).','.$n;
	$dbs = mydb::select($stmt,':q','%'.$q.'%',':provid',$q);

	foreach ($dbs->items as $rs) {
		$label = is_numeric($q) ? htmlspecialchars($rs->distname) : htmlspecialchars(' อำเภอ'.$rs->distname.' จังหวัด'.$rs->provname);

		$result[] = array(
									'value' => $rs->subdistid,
									'ampur' => substr($rs->distid, -2),
									'label' => $label
								);
	}

	if (debug('api')) {
		$result[] = array('value' => 'query','label' => mydb()->_query);
		$result[] = array('value' => 'num_rows','label' => 'Result is '.$dbs->_num_rows.' row(s).');
	}

	return $result;
}
?>