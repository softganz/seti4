<?php
/**
* Prosthesis and Orthosis Center
*
* @param Object $self
* @param Int $orgId
* @return String
*/

$debug = true;

function imed_pocenter_home($self, $orgId = NULL) {
	$ret .= R::View('imed.toolbox',$self,'iMed@ศูนย์กายอุปกรณ์', 'pocenter');


	$isAdmin = $orgInfo->RIGHT & _IS_ADMIN;
	$isMember = $orgInfo->is->socialtype;

	$ret .= '<div class="imed-sidebar">'.R::View('imed.menu.main')->build().'</div>';


	$ret .= '<div id="imed-app" class="imed-app">'._NL;


	$headerUi = new Ui();
	//$headerUi->add('<a href=""><i class="icon -material">view_list</i><span class="-hidden">คงเหลือ</span></a>');

	$ret .= '<header class="header -imed-pocenter"><nav class="nav -back"><a class="" href="'.url('imed').'"><i class="icon -material">arrow_back</i></a></nav><h3>ศูนย์กายอุปกรณ์</h3><nav class="nav">'.$headerUi->build().'</header>';


	$ret .= '<div><h3>อุปกรณ์คงเหลือ</h3>';

	$stmt = 'SELECT
			`stkid`
			, SUM(`balanceamt`) `balanceamt`
			, c.`name`
		FROM %po_stk% s
			LEFT JOIN %imed_stkcode% c USING(`stkid`)
		GROUP BY `stkid`';
	$dbs = mydb::select($stmt);

	$tables = new Table();
	$tables->thead = array('รายละเอียด','balance -amt'=>'คงเหลือ');
	foreach ($dbs->items as $rs) {
		$tables->rows[] = array('<a class="sg-action" href="'.url('imed/pocenter/all/balance/'.$rs->stkid).'" data-rel="box">'.$rs->name.'</a>', $rs->balanceamt);
	}
	$ret .= $tables->build();
	//$ret .= print_o($dbs);
	$ret .= '<p>&nbsp;</p></div>';


	$stmt = 'SELECT o.`orgid`, o.`name`, o.`house`, o.`areacode`
					FROM %org_service% s
						LEFT JOIN %db_org% o USING(`orgid`)
					WHERE s.`servname` = "POCENTER"
					ORDER BY CONVERT(o.`name` USING tis620) ASC';
	$dbs = mydb::select($stmt);
	//$ret .= print_o($dbs);

	$cardUi = new Ui(NULL, 'ui-card -sg-flex -co-2');
	foreach ($dbs->items as $rs) {
		$cardStr = '<a href="'.url('imed/pocenter/'.$rs->orgid).'"><span>';
		$cardStr .= '<h3>'.$rs->name.'</h3>';
		$cardStr .= '<img src="//img.softganz.com/img/disabledonfloor.jpg" width="100%" />';
		$cardStr .= '</span></a>';
		$cardStr .= '<span>'.$rs->house.'</span>';
		$cardStr .= '<nav class="nav -card"><a class="btn -link -fill" href="'.url('imed/pocenter/'.$rs->orgid).'"><i class="icon -material">pageview</i><span>VIEW INFO</span></a></nav>';
		$cardUi->add($cardStr);
	}
	if ($dbs->count() % 2) $cardUi->add('&nbsp;', '{class: "-empty"}');
	$ret .= $cardUi->build();

	$ret .= '</div><!-- imed-app -->';


	return $ret;
}
?>