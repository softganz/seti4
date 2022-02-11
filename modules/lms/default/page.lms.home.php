<?php
/**
* LMS :: Home Page
* Created 2020-07-01
* Modify  2020-07-01
*
* @param Object $self
* @return String
*/

$debug = true;

function lms_home($self) {
	$isAdmin = user_access('administer lms');
	$currentDate = date('Y-m-d H:i:s');

	$ret = '';

	R::View('toolbar', $self, 'Learning Management System (LMS)', 'lms', NULL, '{searchform: false}');

	$ret .= '<h3>รายชื่อหลักสูตร</h3>';

	$stmt = 'SELECT * FROM %lms_course%';

	$dbs = mydb::select($stmt);

	$cardUi = new Ui('div', 'ui-card lms-home');

	foreach ($dbs->items as $rs) {
		$isRegisterDate = $rs->dateregfrom && $rs->dateregend && $currentDate >= $rs->dateregfrom && $currentDate <= $rs->dateregend;

		$cardStr = '<header class="header"><h3>หลักสูตร '.$rs->name.'</h3></header>';

		$menuUi = new Ui(NULL, 'ui-menu');
		$menuUi->addConfig('nav', '{class: "nav"}');
		if ($isAdmin) {
			$menuUi->add('<a href="'.url('lms/'.$rs->courseid.'/manage').'"><i class="icon -material">settings</i><span>จัดการหลักสูตร</span></a>');
		}

		$cardStr .= '<div class="detail sg-view -co-2">';

		$cardStr .= '<div class="-sg-view">'
			. '<h5>ชื่อหลักสูตร '.$rs->name.($rs->enname ? ' ('.$rs->enname.')' : '').'</h5>'
			. '<div>'.nl2br($rs->detail).'</div>'
			//. print_o($rs,'$rs')
			. '</div><! --sg-view -->';

		$navUi = new Ui();
		$navUi->addConfig('nav', '{class: "nav -lms-detail"}');
		$navUi->add('<a class="btn -info -fill" href="'.url('lms/'.$rs->courseid).'">รายละเอียดหลักสูตร</a>');
		if ($isRegisterDate) $navUi->add('<a class="btn -primary -fill"><i class="icon -material">how_to_reg</i><span>ลงทะเบียน</span></a>');
		$cardStr .= '<div class="-sg-view">'
			. $navUi->build()
			. $menuUi->build()
			. '</div><! --sg-view -->';

		$cardStr .= '</div><!-- detail -->';

		//$cardStr .= '<nav class="nav -card">&nbsp;</nav>';


		$cardUi->add($cardStr);
	}

	$ret .= $cardUi->build();

	// $ret .= print_o($dbs, '$dbs');

	$ret .= '<style tyle="text/css">
	.lms-home .header {background-color: #eee; margin: 0;}
	.lms-home .ui-item>.detail.sg-view {padding: 0; border-top: 1px #eee solid;}
	.lms-home>.ui-item>.detail>.-sg-view:first-child {padding-left: 16px; padding-right: 16px;}
	.lms-home .nav.-lms-detail .ui-item:not(:last-child) {margin-bottom: 16px;}
	</style>';

	return $ret;
}
?>