<?php
function project_info_calendar_short($self,$projectInfo, $calid = NULL) {
	$tpid = $projectInfo->tpid;

	$showBudget = $projectInfo->is->showBudget;

	$calendar = project_model::get_calendar($tpid,NULL,NULL,$calid);
	$budgetList=mydb::select('SELECT * FROM %project_tr% WHERE `formid`="develop" AND `part`="exptr" AND `tpid`=:tpid AND `calid`=:calid',':tpid',$tpid, ':calid',$calid);

	$ret .= '<header class="header">'._HEADER_BACK.'<h3>'.$calendar->title.'</h3></header>';

	$ret.='<p><b>ชื่อกิจกรรม : '.$calendar->title.'</b></p>';
	$ret.='<p>สถานที่ : '.$calendar->location.'</p>';
	$ret.='<p>วันที่ : '.($calendar->from_date ? sg_date($calendar->from_date,'ว ดดด ปปปป') : '').' - '.($calendar->to_date ? sg_date($calendar->to_date,'ว ดดด ปปปป') : '').'</p>';
	$ret.='<p>กลุ่มเป้าหมาย : '.$calendar->targetpreset.'</p>';
	$ret.='<p>รายละเอียดกิจกรรมตามแผน : '.sg_text2html($calendar->detail).'</p>';

	$ret.='<p><strong>กิจกรรมหลัก : '.$projectInfo->parentTitle.'</strong></p>';
	$ret.='<p>ผลผลิต : '.$projectInfo->output.'</p>';
	$ret.='<p>ผลลัพธ์ : '.$projectInfo->outcome.'</p>';
	$ret.='<p>ภาคีร่วมสนับสนุน : '.$projectInfo->copartner.'</p>';

	if ($showBudget) {
		$ret.='<h4>งบประมาณ</h4>';

		$tables = new Table();
		$tables->thead=array('no'=>'','รายการ','amt -money'=>'จำนวนเงิน(บาท)');
		foreach ($budgetList->items as $item) {
			$tables->rows[]=array(++$no,nl2br($item->text1),number_format($item->num4,2));
		}
		$tables->tfoot[]=array('<td></td>','รวมเงิน',number_format($calendar->budget,2));
		$ret.=$tables->build();
	}

	$ret .= '<p>สร้างกิจกรรมโดย '.$calendar->posterName.' เมื่อ '.sg_date($calendar->created_date, 'ว ดด ปปปป H:i').' น.</p>';

	//$ret.=print_o($projectInfo,'$projectInfo');
	//$ret.=print_o($budgetList,'$budgetList');
	//$ret.=print_o($plan,'$plan');
	//$ret.=print_o($info,'$info');
	return $ret;
}
?>