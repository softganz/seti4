<?php
function imed_app_poorman_report($self) {
	R::View('imed.toolbar',$self,'วิเคราะห์','app.poorman');

	$ui=new Ui(NULL,'ui-menu -main -poorman');
	$ui->add('<a class="sg-action btn -primary -fill" href="'.url('imed/app/poorman/report/area').'" data-webview="จำแนกตามพื้นที่"><i class="icon -report"></i><span>จำแนกตามพื้นที่</span></a>');
	$ui->add('<a class="sg-action btn -primary -fill" href="'.url('imed/app/poorman/report/type').'" data-webview="ประเภทความยากลำบาก"><i class="icon -report"></i><span>ประเภทความยากลำบาก</span></a>');
	$ui->add('<a class="sg-action btn -primary -fill" href="'.url('imed/app/poorman/report/cause').'" data-webview="สาเหตุของความยากลำบาก"><i class="icon -report"></i><span>สาเหตุของความยากลำบาก</span></a>');
	$ui->add('<a class="sg-action btn -primary -fill" href="'.url('imed/app/poorman/report/summary').'" data-webview="สรุปแบบสอบถาม"><i class="icon -report"></i><span>สรุปแบบสอบถาม</span></a>');
	$ret.=$ui->build();
	return $ret;
}
?>