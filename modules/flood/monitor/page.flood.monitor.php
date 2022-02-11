<?php
/**
* Module Method
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function flood_monitor($self,$report = NULL) {
	$para=para(func_get_args());
	if (!$report) $report='main';
	if ($report!='center') {
		$msgs[]='ระบบประเมินสถานการณ์เพื่อการเตือนภัยน้ำท่วม จังหวัดสงขลา';
		$msgs[]='ระบบประเมินสถานการณ์เพื่อการเตือนภัยน้ำท่วม จังหวัดสงขลา';

		$self->theme->pretext='<div class="sg-slider flood--slider"><ul><li>'.implode('</li><li>', $msgs).'</li></ul></div>';
	}
	/*
	$basin=post('basin');
	$ret.='<div class="xsg-tabs"><ul class="tabs"><li class="'.($basin=='UPT'?'active':'').'"><a href="'.url('flood/monitor/'.$report,array('basin'=>'UPT')).'">ลุ่มน้ำคลองอู่ตะเภา</a></li><li class="'.($basin=='NWT'?'active':'').'"><a href="'.url('flood/monitor/'.$report,array('basin'=>'NWT')).'">ลุ่มน้ำคลองนาทวี</a></li><li class="'.($basin=='PMT'?'active':'').'"><a href="'.url('flood/monitor/'.$report,array('basin'=>'PMT')).'">ลุ่มน้ำคลองภูมี</a></li><li class="'.($basin=='MBT'?'active':'').'" ><a href="'.url('flood/monitor/'.$report,array('basin'=>'MBT')).'">ลุ่มน้ำคลองมำบัง</a></li></ul></div>'._NL;
	*/
	$ret.=R::Page('flood.monitor.'.$report,$self,$para);
	return $ret;
}
?>