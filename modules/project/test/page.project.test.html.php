<?php
function project_test_html($self) {
	$result='<div><p>Hello world</p>';
	$result.='<script type="text/javascript">alert("Hello world");</script>';
	$result.=R::Page('project.report.checkweightinput',$self);
	//location('paper/173/owner/menu');
	location('project/report/exptran');
	return $result;
	die($result);
}
?>