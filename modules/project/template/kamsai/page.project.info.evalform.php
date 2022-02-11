<?php
/**
* Project Set View
*
* @param Object $self
* @param Int $tpid
* @return String
*/

$debug = true;

function project_info_evalform($self, $tpid) {
	$projectInfo = is_object($tpid) ? $tpid : R::Model('project.get',$tpid, '{data: "info", initTemplate: true}');
	$tpid = $projectInfo->tpid;

	$ret = '';

	R::View('project.toolbar',$self, $projectInfo->title, $projectInfo->submodule, $projectInfo);

	$ret .= '<h3>แบบประเมินศูนย์เรียนรู้</h3>';

	$ui = new Ui(NULL,'ui-menu');

	$ui->add('<a href="'.url('paper/'.$tpid).'/eval.kamsaiindicator" title="ประเมินศูนย์เรียนรู้"><i class="icon -material">assessment</i><span>1. แบบฟอร์มการประเมิน "โรงเรียนต้นแบบเด็กไทยแก้มใส"</span></a>');
	$ui->add('<a href="'.url('project/'.$tpid.'/eval.valuation').'" title="ประเมินคุณค่า"><i class="icon -material">assessment</i><span>2. แบบฟอร์มการสังเคราะห์คุณค่าของโครงการ</span></a>');
	$ui->add('<a href="'.url('project/'.$tpid).'/eval.kamsaisum" title="สรุปผลการประเมิน"><i class="icon -material">assessment</i><span>3. แบบฟอร์มสรุปผลการประเมิน "ศูนย์เรียนรู้ต้นแบบเด็กไทยแก้มใสเพื่อคัดเลือกเป็น The Smart Learning Center"</span></a>');

	$ret .= '<nav class="nav -no-print">'.$ui->build().'</nav>';

	$ret .= '<h3>แบบประเมินโครงการ</h3>';

	$ui = new Ui(NULL,'ui-menu');

	$ui->add('<a class="" href="'.url('project/'.$tpid.'/eval.input').'"><i class="icon -material">assessment</i><span>1. แบบติดตามประเมินปัจจัยนำเข้า (Input Evaluation)</span></a>');

	$ui->add('<a class="" href="'.url('project/'.$tpid.'/eval.process').'"><i class="icon -material">assessment</i><span>2. แบบการติดตามประเมินผลการดำเนินกิจกรรมของโครงการ (Process Evaluation)</span></a>');

	$ui->add('<a class="" href="'.url('project/'.$tpid.'/eval.indicator').'"><i class="icon -material">assessment</i><span>3. แบบประเมินผลการดำเนินงาน (Performance/Product Evaluation)</span></a>');

	$ui->add('<a class="" href="'.url('project/'.$tpid.'/eval.success').'"><i class="icon -material">assessment</i><span>4. แบบการวิเคราะห์และการสังเคราะห์ปัจจัยกำหนดความสำเร็จของโครงการ</span></a>');

	$ui->add('<a class="" href="'.url('project/'.$tpid.'/eval.valuation').'"><i class="icon -material">assessment</i><span>5. แบบประเมินคุณค่าของโครงการที่เกี่ยวข้องกับการสร้างเสริมสุขภาพ</span></a>');


	$ret .= '<nav class="nav -no-print">'.$ui->build().'</nav>';

	//$ret .= print_o($projectInfo,'$projectInfo');
	return $ret;
}
?>