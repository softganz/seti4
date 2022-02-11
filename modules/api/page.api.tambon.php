<?php
/**
 * Search from organization name
 *
 * @param String $q or $_GET['q'] or $_POST['q'] : Substring in name
 * @param Integer $n or $_GET['n'] or $_POST['n'] : Item for result
 * @param Integer $p or $_GET['p'] or $_POST['p'] : Page for result
 * @return json[{value:org_id, label:org_name},...]
 */
function api_tambon($self, $q = NULL, $n = NULL, $p = NULL) {
	$q = SG\getFirst($q, trim(post('q')));
	$n = intval(SG\getFirst($n, post('n'), 500));
	$p = intval(SG\getFirst($p, post('p'), 1));

	$result = array();

	if (empty($q)) return $result;

	if (is_numeric($q)) {
		mydb::where('cod.`distid` = :distid', ':distid', $q);
	} else {
		mydb::where('`subdistname` LIKE :q OR `distname` LIKE :q OR `provname` LIKE :q', ':q', '%'.$q.'%');
	}
	mydb::where('LEFT(`subdistname`,1) != "*" AND RIGHT(`subdistname`,1) != "*"');

	$stmt = 'SELECT `subdistid`, `subdistname`, `distname`, `provname`
		FROM %co_subdistrict% co
			LEFT JOIN %co_district% cod ON cod.`distid`=LEFT(co.`subdistid`,4)
			LEFT JOIN %co_province% cop ON cop.`provid`=LEFT(co.`subdistid`,2)
		%WHERE%
		ORDER BY CONVERT(`subdistname` USING tis620) ASC
		LIMIT '.($p-1).','.$n;

	$dbs = mydb::select($stmt);

	foreach ($dbs->items as $rs) {
		$label = is_numeric($q) ? htmlspecialchars($rs->subdistname) : htmlspecialchars('ตำบล'.$rs->subdistname.' อำเภอ'.$rs->distname.' จังหวัด'.$rs->provname);

		$result[] = array(
			'value' => $rs->subdistid,
			'tambon' => substr($rs->subdistid,-2),
			'label' => $label
		);
	}

	if (debug('api')) {
		$result[] = array('value' => 'query','label' => $dbs->_query);
		$result[] = array('value' => 'num_rows','label' => 'Result is '.$dbs->_num_rows.' row(s).');
	}
	return $result;
}
?>