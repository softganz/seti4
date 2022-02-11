<?php
/**
* Project :: Fund Setting
* Created 2019-04-01
* Modify  2020-06-11
*
* @param Object $self
* @return String
*
* @usage project/fund/setting
*/

$debug = true;

function project_fund_setting($self) {
	R::View('project.toolbar',$self,'ระบบบริหารกองทุนสุขภาพตำบล','fund');

	$isWebAdmin = user_access('administer projects');

	$ret = '<h3>Settings</h3>';

	$ui = new Ui();
	$ui->add('<a href="'.url('project/fund/setting/population').'">บันทึกข้อมูลประชากรตามทะเบียนราษฎร์</a>');

	$ret .= $ui->build();
	return $ret;
}
?>