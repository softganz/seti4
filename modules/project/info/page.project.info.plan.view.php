<?php
/**
* Project :: View Project Plan Information
*
* @param Object $projectInfo
* @param Int $tranId
* @return String
*/
function project_info_plan_view($self, $projectInfo, $tranId = NULL) {
	$tpid = $projectInfo->tpid;
	$formType = SG\getFirst($data->formType,'detail');
	$options = options('project');

	$data = R::Model('project.calendar.get', array('activityId'=>$tranId));

	$ret .= '<header class="header -box">'._HEADER_BACK.'<h3>'.$data->title.'</h3></header>';

	//$ret .= print_o($data,'$data');
	//$ret.=print_o($projectInfo,'$projectInfo');

	$ret .= '<h5>กิจกรรม : '.$data->title.'</h5>'
		. 'วันที่ '.sg_date($data->from_date,'d/m/Y').' - '.sg_date($data->to_date,'d/m/Y').'<br />'
		. 'งบประมาณที่ตั้งไว้ '.number_format($data->budget,2).' บาท<br /><br />'
		. '<b>กลุ่มเป้าหมาย</b> '.number_format($data->targetpreset,0).' คน<br />'
		. 'รายละเอียดกลุ่มเป้าหมาย '.nl2br($data->target).'<br /><br />'
		. '<b>รายละเอียดกิจกรรม :</b><br />'.nl2br($data->detail)
		;

	return $ret;
}
?>