<?php
/**
 * Search from organization name
 *
 * @param String $q or $_GET['q'] or $_POST['q'] : Substring in name
 * @param Integer $n or $_GET['n'] or $_POST['n'] : Item for result
 * @param Integer $p or $_GET['p'] or $_POST['p'] : Page for result
 * @return json[{value:org_id, label:org_name},...]
 */
function api_village($self, $q = NULL, $n = NULL, $p = NULL) {
	$q = SG\getFirst($q, trim(post('q')));
	$n = intval(SG\getFirst($item, post('n'), 500));
	$p = intval(SG\getFirst($p, post('p'), 1));

	$result = array();

	if (empty($q)) return $result;

	$stmt = 'SELECT `villid`, `villname`, `subdistname`, `distname`, `provname`
				FROM %co_village% co
					LEFT JOIN %co_subdistrict% cos ON cos.`subdistid` = LEFT(co.`villid`,6)
					LEFT JOIN %co_district% cod ON cod.`distid` = LEFT(co.`villid`,4)
					LEFT JOIN %co_province% cop ON cop.`provid` = LEFT(co.`villid`,2)
				WHERE '.(is_numeric($q) ? 'cos.`subdistid` = :subdistid' : 'co.`villname` LIKE :q').'
				ORDER BY `villid` ASC
				LIMIT '.($p-1).','.$n;
	$dbs = mydb::select($stmt, ':q', '%'.$q.'%', ':subdistid', $q);

	foreach ($dbs->items as $rs) {
		$label = is_numeric($q) ? htmlspecialchars('ม.'.intval(substr($rs->villid,-2)).' - บ้าน'.$rs->villname) : htmlspecialchars('ตำบล'.$rs->subdistname.' อำเภอ'.$rs->distname.' จังหวัด'.$rs->provname);

		$result[] = array(
									'value' => $rs->subdistid,
									'village' => substr($rs->villid,-2),
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