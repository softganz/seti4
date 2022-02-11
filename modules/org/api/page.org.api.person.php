<?php
/**
 * Search from farm name
 *
 * @param String $q or $_GET['q'] or $_POST['q'] : Substring in name
 * @param Integer $n or $_GET['n'] or $_POST['n'] : Item for result
 * @param Integer $p or $_GET['p'] or $_POST['p'] : Page for result
 * @return json[{value:org_id, label:org_name},...]
 */
function org_api_person($self,$q=NULL,$n=NULL,$p=NULL) {
	sendheader('text/html');
	if (post('id')) {
		$stmt='SELECT p.`psnid`, p.`cid`, p.`prename`, p.`name`, p.`lname`,
				p.`phone`, p.`email`,
				p.`house`, p.`village`,
				IFNULL(cosub.`subdistname`,p.`t_tambon`) subdistname,
				IFNULL(codist.`distname`,p.`t_ampur`) distname,
				IFNULL(copv.`provname`,p.`t_changwat`) provname,
				CONCAT(p.`changwat`,p.`ampur`,p.`tambon`) areacode
			FROM %db_person% p
					LEFT JOIN %co_district% codist ON codist.`distid`=CONCAT(p.`changwat`,p.`ampur`)
					LEFT JOIN %co_subdistrict% cosub ON cosub.`subdistid`=CONCAT(p.`changwat`,p.`ampur`,p.`tambon`)
					LEFT JOIN %co_village% covi ON covi.`villid`=CONCAT(p.`changwat`,p.`ampur`,p.`tambon`,IF(LENGTH(p.`village`)=1,CONCAT("0",p.`village`),p.`village`))
					LEFT JOIN %co_province% copv ON p.`changwat`=copv.`provid`
			WHERE p.`psnid`=:psnid
			LIMIT 1';
		$rs=(array)mydb::select($stmt,':psnid',post('id'));
		$rs['address']=SG\implode_address($rs);
		return $rs;
	}


	$q=SG\getFirst($q,trim(post('q')));
	$n=intval(SG\getFirst($n,post('n'),20));
	$p=intval(SG\getFirst($p,post('p'),1));
	if (empty($q)) return '[]';

	list($name,$lname)=sg::explode_name(' ',$q);

 
	mydb::where('p.`cid` LIKE :q OR p.`phone` LIKE :q OR (`name` LIKE :name '.($lname?'AND `lname` LIKE :lname':'').')',':q','%'.$q.'%', ':name','%'.$name.'%',':lname','%'.$lname.'%');
	$stmt='SELECT
					  p.`psnid`
					, p.`cid`
					, p.`prename`
					, p.`name`
					, p.`lname` lname
						-- p.`house`,
					, p.`village`
					, IFNULL(cosub.`subdistname`,p.`t_tambon`) subdistname
					, IFNULL(codist.`distname`,p.`t_ampur`) distname
					, IFNULL(copv.`provname`,p.`t_changwat`) provname
					FROM %db_person% p
						LEFT JOIN %co_district% codist ON codist.`distid`=CONCAT(p.`changwat`,p.`ampur`)
						LEFT JOIN %co_subdistrict% cosub ON cosub.`subdistid`=CONCAT(p.`changwat`,p.`ampur`,p.`tambon`)
						LEFT JOIN %co_village% covi ON covi.`villid`=CONCAT(p.`changwat`,p.`ampur`,p.`tambon`,IF(LENGTH(p.`village`)=1,CONCAT("0",p.`village`),p.`village`))
						LEFT JOIN %co_province% copv ON p.`changwat`=copv.`provid`
					%WHERE%
					ORDER BY CONVERT(p.`name` USING tis620) ASC, CONVERT(p.`lname` USING tis620) ASC
					LIMIT '.($p-1).','.$n;
	$dbs=mydb::select($stmt);
	//debugMsg(mydb()->_query);

	$orgid=org_model::get_my_org();
	$showAll=user_access('administrator orgs') || $orgid;

	$result=array();
	foreach ($dbs->items as $rs) {
		if (!$showAll) unset($rs->house,$rs->village);
		$desc=SG\implode_address($rs,'short');//, pieces)$rs->house.($rs->village?' หมู่ที่ '.$rs->village:'').($rs->subdistname?' ตำบล'.$rs->subdistname:'').($rs->distname?' อำเภอ'.$rs->distname:'').($rs->provname?' จังหวัด'.$rs->provname:'');
		$result[] = array(
									'value'=>$rs->psnid,
									'label'=>htmlspecialchars($rs->prename.' '.$rs->name.' '.$rs->lname),
									'desc'=>$desc
								);
	}
	if ($dbs->_num_rows>=$n) $result[]=array('value'=>'...','label'=>'ยังมีอีก','desc'=>'');
	if (debug('api')) {
		$result[]=array('value'=>'query','label'=>$dbs->_query);
		if ($dbs->_error) $result[]=array('value'=>'error','label'=>$dbs->_error_msg);
		$result[]=array('value'=>'num_rows','label'=>'Result is '.$dbs->_num_rows.' row(s).');
	}
	return json_encode($result);
}
?>