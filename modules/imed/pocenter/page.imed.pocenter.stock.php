<?php
/**
* Prosthesis and Orthosis Center
*
* @param Object $self
* @param Int $orgId
* @return String
*/

$debug = true;

function imed_pocenter_stock($self, $orgId = NULL) {
	$orgInfo = is_object($orgId) ? $orgId : R::Model('imed.pocenter.get', $orgId, '{}');
	$orgId = $orgInfo->orgid;

	R::View('imed.toolbar', $self, $orgInfo->name.' @ศูนย์กายอุปกรณ์', 'pocenter', $orgInfo);

	if (!$orgInfo) return message('error', 'ไม่มีข้อมูลตามที่ระบุ');
	$ret = '';

	$ui = new Ui(NULL,'ui-nav -sg-text-center');

	$ui->add('<a class="btn" href="'.url('imed/pocenter/'.$orgId.'/stock.rcv').'"><i class="icon -material">add_circle_outline</i><span>รับ</span></a>');
	$ui->add('<a class="btn" href=""><i class="icon -material">remove_circle_outline</i><span>จ่าย</span></a>');
	$ui->add('<a class="btn" href=""><i class="icon -material">undo</i><span>ยืม</span></a>');
	$ui->add('<a class="btn" href=""><i class="icon -material">redo</i><span>คืน</span></a>');
	$ui->add('<a class="btn" href=""><i class="icon -material">redo</i><span>ให้ยืม</span></a>');
	$ui->add('<a class="btn" href=""><i class="icon -material">undo</i><span>รับคืน</span></a>');

	$ret .= '<nav class="nav -page -icons">'.$ui->build().'</nav>';
	//$ret .= print_o($orgInfo,'$orgInfo');

	$ret .= '<style type="text/css">
	.nav.-icons .ui-item>a.btn {width: 48px; padding: 16px;}
	</style>';
	return $ret;
}
?>