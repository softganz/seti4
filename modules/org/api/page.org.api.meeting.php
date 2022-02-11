<?php
/**
 * Search from meeting calendar
 *
 * @param String $q or $_GET['q'] or $_POST['q'] : Substring in name
 * @param Integer $n or $_GET['n'] or $_POST['n'] : Item for result
 * @param Integer $p or $_GET['p'] or $_POST['p'] : Page for result
 * @return json[{value:org_id, label:org_name},...]
 */

import('model:org.php');

function org_api_meeting($self,$q='',$n=NULL,$p=NULL) {
	sendheader('text/html');
	$q=SG\getFirst(trim($q),trim(post('q')));
	$n=intval(SG\getFirst($n,post('n'),20));
	$p=intval(SG\getFirst($p,post('p'),1));

	if (empty($q)) return '[]';

	$officers = OrgModel::officerOfUser(i()->uid);

	if (empty($officers)) return '[]';

	$stmt='SELECT
			  do.`doid`, do.`doings`
			, do.`calid`, do.`uid`, do.`tpid`, do.`orgid`
			, c.`from_date`
			, DATE_FORMAT(c.`from_time`,"%H:%i") from_time
			, c.`location`
		FROM %org_doings% do
			LEFT JOIN %calendar% c ON c.`id`=do.`calid`
			LEFT JOIN %topic% t ON t.`tpid`=do.`tpid`
		WHERE
			(do.`uid`=:uid OR t.`orgid` IN (:orgid))
			AND do.`doings` LIKE :q
		--	AND c.`id` NOT IN (SELECT `calid` FROM %org_doings% WHERE calid IS NOT NULL)
		ORDER BY CONVERT(c.`title` USING tis620) ASC
		LIMIT '.($p-1).','.$n;

	$dbs=mydb::select($stmt,':q','%'.$q.'%', ':uid', i()->uid, ':orgid','SET:'.implode(',',array_keys($officers->items)));
	//debugMsg($dbs,'$dbs');

	$result=array();
	foreach ($dbs->items as $rs) {
		$desc='@'.sg_date($rs->from_date,'d/m/Y').' '.$rs->from_time.' น. ณ '.$rs->location;
		$result[] = array(
			'value'=>$rs->id,
			'label'=>htmlspecialchars($rs->doings),
			'atDate'=>sg_date($rs->from_date,'d/m/Y'),
			'atTime'=>$rs->from_time,
			'location'=>htmlspecialchars($rs->location),
			'desc'=>htmlspecialchars($desc)
		);
	}
	if ($dbs->_num_rows>=$n) $result[]=array('value'=>'...','label'=>'ยังมีอีก','desc'=>'');
	if (debug('api')) {
		$result[]=array('value'=>'query','label'=>$dbs->_query);
		if ($dbs->_error) $result[]=array('value'=>'error','label'=>$dbs->_error_msg);
		$result[]=array('value'=>'num_rows','label'=>'Result is '.$dbs->_num_rows.' row(s).');
	}
	if (debug('html')) return print_o($result,'$result');
	return $result;
}
?>