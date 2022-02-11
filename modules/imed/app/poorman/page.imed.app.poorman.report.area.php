<?php
function imed_app_poorman_report_area($self) {
	R::View('imed.toolbar',$self,'จำแนกตามพื้นที่','none');
	$ret.=R::Page('imed.report.poormanarea',NULL);

	head('<style type="text/css">
	.report-form>h3 {display:none;}
	.report-form .btn.-main {top:4px;}
	.module-imed.-app form {margin:0; padding:4px 0 0 0;}
	.form-item {margin:0;}
	.form-item.-province {padding-right:100px;}
	.report-form .form-item.-province .form-select {width:100px; margin-bottom:4px;}
	</style>');
	return $ret;
}
?>