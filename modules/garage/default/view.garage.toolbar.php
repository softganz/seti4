<?php
/**
* Garage Toolbar
* Created 2018-10-14
* Modify  2019-12-14
*
* @param 
* @return String
*/

$debug = true;

function view_garage_toolbar($self, $title = '', $nav = 'default', $rs = NULL, $options = '{}') {
	$defaults = '{selectTarget: "search", searchTarget: "view", debug: false}';
	$options = sg_json_decode($options, $defaults);
	$debug = $options->debug;


	if (is_string($rs) AND substr($rs,0,1)=='{') $rs=sg_json_decode($rs);

	$ret = '';

	$searchTarget = url('garage/search');
	if ($options->searchTarget == 'part') $searchTarget = url('garage/part');
	else if ($options->searchTarget == 'in') $searchTarget = url('garage/in');

	$selectTarget = url('garage/job');
	if ($options->selectTarget == 'part') $selectTarget = url('garage/part');
	else if ($options->selectTarget == 'in') $selectTarget = url('garage/job/in');

	/*
	$ui = new ui();

	$ui->add('<a href="'.url('garage').'" title="">หน้าแรก</a>');
	$ui->add('<a href="'.url('garage/job').' " title="">ระบบสั่งซ่อม</a>');
	$ui->add('<a href="'.url('garage/quot').' " title="">ระบบสั่งงาน</a>');
	$ui->add('<a href="'.url('garage/part').' " title="">ระบบอะไหล่</a>');
	$ui->add('<a href="'.url('garage/finance').' " title="">ระบบการเงิน</a>');
	$ui->add('<a href="'.url('garage/report').' " title="">วิเคราะห์</a>');
	if (user_access('administrator garages')) {
		$ui->add('<a href="'.url('garage/admin').'" title="ผู้จัดการระบบ">จัดการระบบ</a>');
	}
	$self->theme->moduleNav=$ui->build('ul','navgroup -main');
	*/

	if (!is_null($title)) $self->theme->title=$title;


	$self->theme->title='GagMan :: Garage Management System';

	$tpid=empty($rs->tpid)?NULL:$rs->tpid;
	$uid=empty($rs->uid)?NULL:$rs->uid;
	$urlRequest=q();
	$ret='';
	$sub_menu='';
	$curDate=date('Y-m-d H:i');

	$defaults='{menu:""}';
	$options=sg_json_decode($options);


	if ($nav=='stock') {
		$ret.='<form id="search" class="search-box" method="get" action="'.url('garage/stock').'" role="search"><input type="hidden" name="jid" id="jid" /><input id="search-box" class="sg-autocomplete" type="text" name="q" size="40" value="'.post('q').'" placeholder="รหัสสินค้า/อะไหล่" data-query="'.url('garage/api/repaircode').'" data-callback="'.url('garage/stock').'" data-altfld="jid"><button class="btn" type="submit"><i class="icon -material">search</i><span>ค้นหา</span></button></form>'._NL;
	} else {
		$ret.='<form id="search" class="search-box" method="get" action="'.$searchTarget.'" role="search"><input type="hidden" name="jobid" id="jobid" /><input id="search-box" class="sg-autocomplete" type="text" name="q" size="40" value="'.post('q').'" placeholder="ป้อนทะเบียนรถหรือเลข job" data-query="'.url('garage/api/job').'" data-callback="'.$selectTarget.'" data-altfld="jobid"><button class="btn" type="submit"><i class="icon -material">search</i><span>ค้นหา</span></button></form>'._NL;
	}

	if ($navBar = R::View('garage.nav.'.$nav,$rs)) {
		$ret .= '<nav class="nav -submodule -garage">'.$navBar.'</nav>';
	}
	if ($title) $self->theme->title=$title;
	$self->theme->toolbar=$ret;
	//debugMsg($ret);
	return $ret;
}
?>