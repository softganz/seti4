<?php
/**
* Module Method
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function imed_social_report($self, $orgInfo = NULL) {
	$orgId = $orgInfo->orgid;

	R::View('imed.toolbar',$self,'@'.i()->name,'app');

	$ui = new Ui();

	//$ret .= '<header class="header"><h3>'.i()->name.'</h3><nav class="nav">'.$ui->build().'</nav></header>';

	if (!i()->ok) return R::View('signform', '{time:-1}');

	$ret .= '<div id="imed-app" class="card-item" style="flex: 1 0 100%">';

	$mainUi = new Ui(NULL, 'ui-menu');

	$mainUi->add('<h3>รายงาน</h3>');
	$mainUi->add('<a class="sg-action" href="'.url('imed/social/'.$orgId.'/report.visit.summary').'" data-rel="#imed-app" data-webview="รายงานสรุปผลปฎิบัติงานประจำเดือน" data-width="480" data-height="80%"><i class="icon"></i><span>รายงานสรุปผลปฎิบัติงานประจำเดือน</span></a>');
	$mainUi->add('<a class="sg-action" href="'.url('imed/social/'.$orgId.'/report.visit.month').'" data-rel="#imed-app" data-webview="รายงานรายละเอียดผลปฎิบัติงาน" data-width="480" data-height="80%"><i class="icon"></i><span>รายงานรายละเอียดผลปฎิบัติงาน</span></a>');

	$ret .= $mainUi->build();

	/*
	$ret .= '<div class="imed-sidebar -status -no-print">';
	$ret .= $ui->build();
	$ret .= '</div>';
	*/

	$ret .= '</div>';
	return $ret;
}
?>