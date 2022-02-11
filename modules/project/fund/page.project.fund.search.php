<?php
/**
* Project :: Fund Search
* Created 2017-10-08
* Modify  2020-06-11
*
* @param Object $self
* @return String
*
* @usage project/fund/search
*/

$debug = true;

function project_fund_search($self) {
	$q = trim(post('q'));
	$n = intval(SG\getFirst($n,post('n'),100));
	$p = intval(SG\getFirst($p,post('p'),1));
	$retType = SG\getFirst(post('r'),'u');

	R::view('project.toolbar',$self,'ค้นหากองทุน','fund');

	if (empty($q)) return '[]';
	
	$stmt='SELECT o.`orgid`, f.`fundid`, o.`name`, f.`nameampur`, f.`namechangwat`
		FROM %project_fund% f
			LEFT JOIN %db_org% o ON o.`shortname`=f.`fundid`
		WHERE f.`fundid` LIKE :q OR o.`name` LIKE :q
		ORDER BY CONVERT(`name` USING tis620) ASC
		LIMIT '.($p-1).','.$n;

	$dbs=mydb::select($stmt,':q','%'.$q.'%');

	//debugMsg(mydb()->_query);
	//debugMsg($dbs,'$dbs');
	
	$tables = new Table();
	foreach ($dbs->items as $rs) {
		$tables->rows[] = array(
			$rs->fundid,
			'<a href="'.url('project/fund/'.$rs->orgid).'">'.$rs->name.'</a>',
			'อ.'.$rs->nameampur.' จ.'.$rs->namechangwat,
		);
	}
	$ret.=$tables->build();
	//$ret.=print_o($dbs,'$dbs');
	return $ret;
}
?>