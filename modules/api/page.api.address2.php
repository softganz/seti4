<?php
/**
 * Search from address, tambom
 *
 * @param String $q or $_GET['q'] or $_POST['q'] : Substring in name
 * @param Integer $n or $_GET['n'] or $_POST['n'] : Item for result
 * @param Integer $p or $_GET['p'] or $_POST['p'] : Page for result
 * @return json[{value:org_id, label:org_name},...]
 */
function api_address2($self, $q = '', $n = NULL, $p = NULL) {
	$q = preg_replace('/  /',' ',trim(SG\getFirst($q,post('q'),post('query'))));
	$n = intval(SG\getFirst($item,post('n'),50));
	$p = intval(SG\getFirst($p,post('p'),1));
	if (empty($q)) return '[]';

	$addressOut = SG\explode_address($q);
	//debugMsg($addressOut,'$addressOut');

	$searchText = SG\getFirst($addressOut['tambon'],$addressOut['ampur'],$addressOut['changwat'], $q);

	if (empty($searchText)) return '[]';

	$house = $addressOut['house'].($addressOut['village']?' ม.'.$addressOut['village']:'');

	//print_o($addressOut,'$addressOut',1);

	/*
	if (preg_match('/(.*)(ตำบล|ต\.)(.*)/',$q,$address)) {
	//		} else if ($address=preg_split('/(ต.|อ.)/',$q)) {
		$addr=trim($address[1]);
		list($searchText)=explode(' ',trim($address[3]));
		if (preg_match('/([0-9]{5})$/',$q,$zout)) $zip=$zout[1];
	} else {
		return '[]';
	}
	print_o($address,'$address',1);
	//			foreach ($address as $k=>$v) $result[]=array('value'=>'out['.$k.']','label'=>$k.'='.$v);
	*/

	mydb::value('$LIMIT$', 'LIMIT '.($p-1).','.$n);
	$stmt='SELECT * FROM
				(
				SELECT
					2 `is_tambon`
					, `subdistid` `areacode`
					, SUBSTRING(`subdistid`, 5, 2) `tambonId`
					, `subdistname` `tambonName`
					, SUBSTRING(`distid`, 3, 2) `ampurId`
					, `distname` `ampurName`
					, `provid` `changwatId`
					, `provname` `changwatName`
				FROM %co_subdistrict% co
					LEFT JOIN %co_district% cod ON cod.`distid` = LEFT(co.`subdistid`,4)
					LEFT JOIN %co_province% cop ON cop.`provid` = LEFT(co.`subdistid`,2)
				WHERE `subdistname` LIKE :q AND RIGHT(`subdistname`,1) != "*"
				-- Select changwat
				UNION
					SELECT
						0 `is_changwat`
						, `provid`
						, NULL
						, NULL
						, NULL
						, NULL
						, `provid`
						, `provname`
					FROM %co_province%
					WHERE `provname` LIKE :q
					-- Select ampur
				UNION
					SELECT
						1 `is_ampur`
						, `distid`
						, NULL
						, NULL
						, `distid`
						, `distname`
						, `provid`
						, `provname`
					FROM %co_district% co
						LEFT JOIN %co_province% cop ON cop.`provid` = LEFT(co.`distid`,2)
					WHERE `distname` LIKE :q AND RIGHT(`distname`,1) != "*"
				-- Select all tambon in ampur
				UNION
					SELECT
						3 `is_tambon`
						, `subdistid`
						, SUBSTRING(`subdistid`, 5, 2) `tambonId`
						, `subdistname`
						, SUBSTRING(`distid`, 3, 2) `ampurId`
						, `distname`
						, `provid` `changwatId`
						, `provname`
					FROM %co_subdistrict% co
						LEFT JOIN %co_district% cod ON cod.`distid` = LEFT(co.`subdistid`,4)
						LEFT JOIN %co_province% cop ON cop.`provid` = LEFT(co.`subdistid`,2)
					WHERE (`distname` LIKE :q  AND RIGHT(`distname`,1) != "*")
				) a
				GROUP BY `areacode`
				ORDER BY
					`is_tambon` ASC
					, CONVERT(`changwatName` USING tis620) ASC
					, CONVERT(`ampurName` USING tis620) ASC
					, CONVERT(`tambonName` USING tis620) ASC
				$LIMIT$;
				-- {key: "areacode"}
				';
	$dbs = mydb::select($stmt,':q','%'.$searchText.'%');

	//debugMsg('<pre>'.mydb()->_query.'</pre>');
	//print_o($address,'$address',1);
	//print_o($dbs,'$dbs',1);
	//		$result=array();
	foreach ($dbs->items as $rs) {
		$address = SG\implode_address(array('house'=>$house)+(array)$rs);//$house.' ตำบล'.$rs->subdistname.' อำเภอ'.$rs->distname.' จังหวัด'.$rs->provname.($zip?' '.$zip:'');
		$label = $address;//SG\implode_address($rs);// $house.' ตำบล'.$rs->subdistname.' อำเภอ'.$rs->distname.' จังหวัด'.$rs->provname;
		$areacode = $rs->areacode
								. (strlen($rs->areacode)==6 && $addressOut['village'] != '' ? str_pad($addressOut['village'],2,'0',STR_PAD_LEFT) : '');
		$arr[] = htmlspecialchars($label);
		$result[] = array(
										'value' => $areacode,
										'label' => htmlspecialchars($label),
										'address' => htmlspecialchars($address),
										'changwatId' => $rs->changwatId,
										'ampurId' => $rs->ampurId,
										'tambonId' => $rs->tambonId,
										'changwatName' => htmlspecialchars($rs->provname),
										'ampurName' => htmlspecialchars($rs->distname),
										'tambonName' => htmlspecialchars($rs->subdistname),
									);
	}
	//print_o($result,'$result',1);
	if (debug('api')) {
		$result[]=array('value'=>'length','label'=>'Charactor length = '.strlen($searchText));
		$result[]=array('value'=>'query','label'=>$dbs->_query);
		$result[]=array('value'=>'num_rows','label'=>'Result is '.$dbs->_num_rows.' row(s).');
		$result[]=array('value'=>'tambon','label'=>$searchText);
		foreach ($address as $k=>$v) $result[]=array('value'=>'out['.$k.']','label'=>$k.'='.$v);
	}

	$result = (object) array('query'=>$q, "suggestions"=>$arr);
	return $result;
}
?>