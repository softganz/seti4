<?php
/**
* Prosthesis and Orthosis Center
*
* @param Object $self
* @param Int $orgId
* @return String
*/

$debug = true;

function imed_pocenter_all_balance($self, $stkid) {
	//R::View('imed.toolbox',$self,'iMed@ศูนย์กายอุปกรณ์', 'pocenter');


	$isAdmin = $orgInfo->RIGHT & _IS_ADMIN;
	$isMember = $orgInfo->is->socialtype;

	$ret .= '<header class="header"><nav class="nav -back"><a class="sg-action" href="'.url('imed/pocenter').'" data-rel="close"><i class="icon -material">arrow_back</i></a></nav><h3>อุปกรณ์คงเหลือ</h3></header>';

	mydb::where('s.`stkid` = :stkid', ':stkid', $stkid);
	$stmt = 'SELECT
			`stkid`
			, `balanceamt`
			, c.`name`
			, s.`orgid`
			, o.`name` `orgName`
		FROM %po_stk% s
			LEFT JOIN %imed_stkcode% c USING(`stkid`)
			LEFT JOIN %db_org% o USING(`orgid`)
		%WHERE%
		';
	$dbs = mydb::select($stmt);

	$tables = new Table();
	$tables->thead = array('รายละเอียด','balance -amt'=>'คงเหลือ');
	foreach ($dbs->items as $rs) {
		$tables->rows[] = array(
			'<a href="'.url('imed/pocenter/'.$rs->orgid).'">'.$rs->orgName.'</a>',
			$rs->balanceamt
		);
	}
	$ret .= $tables->build();
	//$ret .= print_o($dbs);
	$ret .= '<p>&nbsp;</p></div>';


	return $ret;
}
?>