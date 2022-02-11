<?php
/**
* Module Method
* Created 2019-11-01
* Modify  2019-11-01
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function green_standard_set($self, $landId) {
	$isAdmin = user_access('administer ibuys');

	if (!$isAdmin) return message('error', 'Access denied');

	if (post('std')) {
		$stmt = 'UPDATE %ibuy_farmland% SET `standard` = :standard, `stdextend` = :ext, `approved` = :approved WHERE `landid` = :landid LIMIT 1';
		mydb::query($stmt, ':landid', $landId, ':standard', post('std'), ':ext', post('ext'), ':approved', post('apd'));
		return $ret;
	}

	$ret = '<header class="header -box">'._HEADER_BACK.'<h3>กำหนดมาตรฐาน</h3></header>';

	$ui = new Ui(NULL, 'ui-menu');

	$ui->add('<a class="sg-action" href="'.url('green/standard/set/'.$landId, array('std' => 'PGS', 'ext' => 'SGS', 'apd' => 'Inprogress')).'" data-rel="notify" data-done="close | load"><i class="icon -material"></i><span>SGS PGS - Inprogress</span></a>');
	$ui->add('<a class="sg-action" href="'.url('green/standard/set/'.$landId, array('std' => 'PGS', 'ext' => 'SGS', 'apd' => 'Waiting')).'" data-rel="notify" data-done="close | load"><i class="icon -material"></i><span>SGS PGS - Waiting</span></a>');

	$ui->add('<a class="sg-action" href="'.url('green/standard/set/'.$landId, array('std' => 'PGS', 'ext' => 'SGS', 'apd' => 'Approve')).'" data-rel="notify" data-done="close | load"><i class="icon -material -green">check_circle</i><span>SGS PGS - ผ่านได้มาตรฐาน</span></a>');
	$ui->add('<a class="sg-action" href="'.url('green/standard/set/'.$landId, array('std' => 'PGS', 'ext' => 'SGS', 'apd' => 'ApproveWithCondition')).'" data-rel="notify" data-done="close | load"><i class="icon -material">check_circle_outline</i><span>SGS PGS - ผ่านแบบมีเงื่อนไข</span></a>');

	$ui->add('<a class="sg-action" href="'.url('green/standard/set/'.$landId, array('std' => 'PGS', 'ext' => 'SGS', 'apd' => 'Reject')).'" data-rel="notify" data-done="close | load"><i class="icon -material"></i><span>SGS PGS - Reject</span></a>');
	$ui->add('<a class="sg-action" href="'.url('green/standard/set/'.$landId, array('std' => 'PGS', 'ext' => 'SGS', 'apd' => 'Cancel')).'" data-rel="notify" data-done="close | load"><i class="icon -material"></i><span>SGS PGS - Cancel</span></a>');


	$ui->add('<a class="sg-action" href="'.url('green/standard/set/'.$landId, array('std' => 'GAP', 'apd' => 'Inprogress')).'" data-rel="notify" data-done="close | load"><i class="icon -material"></i><span>GAP - Inprogress</span></a>');
	$ui->add('<a class="sg-action" href="'.url('green/standard/set/'.$landId, array('std' => 'GAP', 'apd' => 'Waiting')).'" data-rel="notify" data-done="close | load"><i class="icon -material"></i><span>GAP - Waiting</span></a>');

	$ui->add('<a class="sg-action" href="'.url('green/standard/set/'.$landId, array('std' => 'GAP', 'apd' => 'Approve')).'" data-rel="notify" data-done="close | load"><i class="icon -material -green">check_circle</i><span>GAP - ผ่านได้มาตรฐาน</span></a>');
	$ui->add('<a class="sg-action" href="'.url('green/standard/set/'.$landId, array('std' => 'GAP', 'apd' => 'ApproveWithCondition')).'" data-rel="notify" data-done="close | load"><i class="icon -material">check_circle_outline</i><span>GAP - ผ่านแบบมีเงื่อนไข</span></a>');

	$ui->add('<a class="sg-action" href="'.url('green/standard/set/'.$landId, array('std' => 'GAP', 'apd' => 'Reject')).'" data-rel="notify" data-done="close | load"><i class="icon -material"></i><span>GAP - Reject</span></a>');
	$ui->add('<a class="sg-action" href="'.url('green/standard/set/'.$landId, array('std' => 'GAP', 'apd' => 'Cancel')).'" data-rel="notify" data-done="close | load"><i class="icon -material"></i><span>GAP - Cancel</span></a>');

	$ret .= $ui->build();

	return $ret;
}
?>