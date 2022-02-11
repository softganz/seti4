<?php
/**
* Project :: Fund Report Financial Close
* Created 2020-06-7
* Modify  2020-06-11
*
* @param Object $self
* @return String
*
* @usage project/fund/report/financial/close
*/

$debug = true;

function project_fund_report_financial_close($self) {
	$isAdmin=user_access('administer projects');

	$order='f.`changwat` ASC, f.`ampur` ASC, CONVERT(`fundname` USING tis620) ASC';
	if (post('o')=='date') $order='f.`finclosemonth` DESC';
	else if (post('o')=='name') $order='CONVERT(o.`name` USING tis620) ASC';
	else if (post('o')=='id') $order='o.`shortname` ASC';

	if ($prov) mydb::where('f.`changwat` = :prov',':prov',$prov);

	$stmt = 'SELECT
			  f.`fundid`, f.`namechangwat`, f.`nameampur`, f.`finclosemonth`
			, o.`orgid`
			, o.`name`
			, o.`shortname`
			FROM %project_fund% f
				LEFT JOIN %db_org% o USING(`orgid`)
		%WHERE%
		ORDER BY '.$order.'
		;
		';

	$dbs = mydb::select($stmt,$where['value']);

	//$ret .= mydb()->_query;

	$tables = new Table();
	$tables->thead=array('ชื่อกองทุน <a href="'.url('project/fund/report/financial/close',array('o'=>'name')).'"><i class="icon -sort"></i></a>','รหัสกองทุน <a href="'.url('project/fund/report/financial/close',array('o'=>'id')).'"><i class="icon -sort"></i></a>','จังหวัด <a href="'.url('project/fund/report/financial/close',array('o'=>'prov')).'"><i class="icon -sort"></i></a>','อำเภอ','center -status'=>'วันที่ปิดงวด <a href="'.url('project/fund/report/financial/close',array('o'=>'date')).'"><i class="icon -sort"></i></a>');

	$closeCount=0;
	foreach ($dbs->items as $rs) {
		$tables->rows[]=array(
			$isAdmin ? '<a href="'.url('project/fund/'.$rs->orgid.'/financial').'">'.$rs->name.'</a>' : $rs->name,
			$rs->shortname,
			$rs->namechangwat,
			$rs->nameampur,
			$rs->finclosemonth,
			);
		if ($rs->finclosemonth) $closeCount++;
	}

	$tables->tfoot[]=array('รวม','','','',$closeCount);

	$ret.=$tables->build();


	//$ret.=print_o($dbs,'$dbs');
	return $ret;
}
?>