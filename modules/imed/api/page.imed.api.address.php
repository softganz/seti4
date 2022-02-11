<?php
/**
 * iMed API
 *
 * @param String $q or $_GET['q'] or $_POST['q'] : Substring in name
 * @param Integer $n or $_GET['n'] or $_POST['n'] : Item for result
 * @param Integer $p or $_GET['p'] or $_POST['p'] : Page for result
 * @return json[{value:org_id, label:org_name},...]
 */
function imed_api_address($self,$q,$n,$p) {
	sendheader('text/html');
	$q=SG\getFirst($q,trim($_GET['q'],$_POST['q']));
	$n=intval(SG\getFirst($item,$_GET['n'],$_POST['n'],50));
	$p=intval(SG\getFirst($p,$_GET['p'],$_POST['p'],1));
	if (empty($q)) {
		return '[]';
	} else if (preg_match('/(.*)(ตำบล|ต\.)(.*)/',$q,$out)) {
		$addr=trim($out[1]);
		$tambon=trim($out[3]);
	} else {
		return '[]';
	}

	$stmt='SELECT `subdistid`, `subdistname`, `distname`, `provname`
				FROM %co_subdistrict% co
					LEFT JOIN %co_district% cod ON cod.distid=LEFT(co.subdistid,4)
					LEFT JOIN %co_province% cop ON cop.provid=LEFT(co.subdistid,2)
				WHERE `subdistname` LIKE :q
				ORDER BY `subdistname` ASC
				LIMIT '.($p-1).','.$n;
				// OR `distname` LIKE :q OR `provname` LIKE :q
	$dbs=mydb::select($stmt,':q','%'.$tambon.'%');

	$result=array();
//		$result[]=array('value'=>'q','label'=>'tambon : '.$q.' : '.$tambon);

	foreach ($dbs->items as $rs) {
		$result[] = array('value'=>$rs->subdistid, 'label'=>htmlspecialchars($addr.' ตำบล'.$rs->subdistname.' อำเภอ'.$rs->distname.' จังหวัด'.$rs->provname));
	}
	if (debug('api')) {
		$result[]=array('value'=>'length','label'=>'Charactor length = '.strlen($tambon));
		$result[]=array('value'=>'query','label'=>$dbs->_query);
		$result[]=array('value'=>'num_rows','label'=>'Result is '.$dbs->_num_rows.' row(s).');
	}
	return json_encode($result);
}
?>