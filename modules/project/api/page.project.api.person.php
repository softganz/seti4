<?php
/**
 * Project Person API
 *
 * @param String $q or $_GET['q'] or $_POST['q'] : Substring in name
 * @param Integer $n or $_GET['n'] or $_POST['n'] : Item for result
 * @param Integer $p or $_GET['p'] or $_POST['p'] : Page for result
 * @return json[{value:org_id, label:org_name},...]
 */
function project_api_person($self, $q = NULL, $n = NULL, $p = NULL) {
	sendheader('text/html');
	$q = trim(SG\getFirst($q, post('q')));
	$n = intval(SG\getFirst($n, post('n'), 20));
	$p = intval(SG\getFirst($p, post('p'), 1));
	if (empty($q) || !i()->ok || _HOST != _REFERER) return '[]';

	list($name,$lname) = sg::explode_name(' ', $q);
	
	$isAdmin=user_access('access administrator pages,administer projects');
	$isAccessPerson=user_access('access projects person');
	//$zones=imed_model::get_user_zone(i()->uid);

	//$zones=imed_model::get_user_zone(i()->uid);
	//echo '<br /><br /><br /><br /><br /><br /><br />';
	//print_o($zones,'$zones',1);

	mydb::where('(p.`cid` LIKE :name OR (`name` LIKE :name '.($lname?'AND `lname` LIKE :lname':'').'))',':name','%'.$name.'%', ':lname','%'.$lname.'%');

	if ($isAdmin || $isAccessPerson) {

	} else  if ($zones) {
		foreach ($zones as $zone) {
			if (strlen($zone->zone)==6) {
				$zoneTambon[]=$zone->zone;
			} else if (strlen($zone->zone)==4) {
				$zoneAmpur[]=$zone->zone;
			} else if (strlen($zone->zone)==2) {
				$zoneProvince[]=$zone->zone;
			}
		}
		if ($zoneProvince) $zoneCondition[]='p.changwat IN ("'.implode('","', $zoneProvince).'")';
		if ($zoneAmpur) $zoneCondition[]='CONCAT(p.changwat,p.ampur) IN ("'.implode('","', $zoneAmpur).'")';
		if ($zoneTambon) $zoneCondition[]='CONCAT(p.changwat,p.ampur,p.tambon) IN ("'.implode('","', $zoneTambon).'")';
		mydb::where('('.'p.`uid`=:uid'.' OR '.implode(' OR ',$zoneCondition).')',':uid',i()->uid);
	} else {
		mydb::where('p.`uid`=:uid',':uid',i()->uid);
	}


	$stmt = 'SELECT
						  p.`uid`, p.`psnid`, p.`cid`
						, p.`prename`, p.`name`, p.`lname` lname
						, p.`sex`, p.`religion`, p.`birth`
						, p.`phone`
						, p.`email`
						, p.`house`, p.`village`
						, p.`tambon` `tambonId`
						, p.`ampur` `ampurId`
						, p.`changwat` `changwatId`
						, IFNULL(cosub.`subdistname`,p.`t_tambon`) `tambonName`
						, IFNULL(codist.`distname`,p.`t_ampur`) `ampurName`
						, IFNULL(copv.`provname`,p.`t_changwat`) `changwatName`
						, p.`zip`
					FROM %db_person% p
						LEFT JOIN %co_district% codist ON codist.`distid`=CONCAT(p.`changwat`,p.`ampur`)
						LEFT JOIN %co_subdistrict% cosub ON cosub.`subdistid`=CONCAT(p.`changwat`,p.`ampur`,p.`tambon`)
						LEFT JOIN %co_village% covi ON covi.`villid`=CONCAT(p.`changwat`,p.`ampur`,p.`tambon`,IF(LENGTH(p.`village`)=1,CONCAT("0",p.`village`),p.`village`))
						LEFT JOIN %co_province% copv ON p.`changwat`=copv.`provid`
					%WHERE%
					ORDER BY p.`name` ASC
					LIMIT '.($p-1).','.$n;

	$dbs = mydb::select($stmt);
	//print_o($dbs,'$dbs',1);

	$result = array();
	foreach ($dbs->items as $rs) {
		$address = $rs->house.($rs->village?' หมู่ที่ '.$rs->village:'')
			.($rs->tambonName?' ต.'.$rs->tambonName:'')
			.($rs->ampurName?' อ.'.$rs->ampurName:'')
			.($rs->changwatName?' จ.'.$rs->changwatName:'');
		$desc = ($rs->cid?'CID : '.$rs->cid.' ':'')
			.'ที่อยู่ '.$address;

		$result[] = array(
									'value' => $rs->psnid,
									'label' => htmlspecialchars($rs->name.' '.$rs->lname),
									'desc' => htmlspecialchars($desc),
									'preName' => htmlspecialchars($rs->prename),
									'firstName' => htmlspecialchars($rs->name),
									'lastName' => htmlspecialchars($rs->lname),
									'cid' => htmlspecialchars($rs->cid),
									'sex' => htmlspecialchars($rs->sex),
									'religion' => htmlspecialchars($rs->religion),
									'phone' => htmlspecialchars($rs->phone),
									'tambonId' => htmlspecialchars($rs->tambonId),
									'ampurId' => htmlspecialchars($rs->ampurId),
									'changwatId' => htmlspecialchars($rs->changwatId),
									'tambonName' => htmlspecialchars($rs->tambonName),
									'ampurName' => htmlspecialchars($rs->ampurName),
									'changwatName' => htmlspecialchars($rs->changwatName),
									'zip' => htmlspecialchars($rs->zip),
									'address' => htmlspecialchars($address),
								);
	}
	if (debug('api')) {
		$result[]=array('value'=>'query','label'=>$dbs->_query);
		if ($dbs->_error) $result[]=array('value'=>'error','label'=>$dbs->_error_msg);
		$result[]=array('value'=>'num_rows','label'=>'Result is '.$dbs->_num_rows.' row(s).');
	}
	return json_encode($result);
}
?>