<?php
/**
 * รายงานการติดตามของพี่เลี้ยง
 *
 * @param Object $self
 * @param Object $projectInfo
 * @return String
 */
function project_operate_trainer($self,$projectInfo, $period = NULL) {
	if (!($tpid = $projectInfo->tpid)) return message('error', 'PROCESS ERROR:NO PROJECT');

	R::View('project.toolbar', $self, $projectInfo->title, $projectInfo->submodule, $projectInfo,'{showPrint: true}');

	$formid = 'follow';
	$formname = 'บันทึกการติดตาม';

	$is_edit = fasle; //($projectInfo->info->project_statuscode==1) && (user_access('administer projects') || project_model::is_trainer_of($tpid));

	$rs=project_model::get_tr($tpid,$formid,$period);

	$stmt='SELECT `period`, COUNT(*) total FROM %project_tr% WHERE `formid`="follow" AND `tpid`=:tpid GROUP BY `period` ORDER BY `period`';
	foreach (mydb::select($stmt,':tpid',$tpid)->items as $item) $period_items[$item->period]=$item->total;

	// Show form toolbar
	if (!$period) {
		$currentReport=mydb::select('SELECT `period`, `flag` FROM %project_tr% tr WHERE tr.`tpid`=:tpid AND tr.`formid`=:formid AND `part`="title" GROUP BY `period`',':tpid',$tpid,':formid',$formid);
		$ret.='<p class="toolbar">';
		if ($currentReport->_num_rows) {
			foreach ($currentReport->items as $item) {
				$ret.='<a class="btn" href="'.url('project/'.$tpid.'/operate.trainer/'.$item->period).'">'.$formname.' ครั้งที่ '.$item->period.'</a>';
				$lastPeriod=$item->period;
				$lastPeriodLock=$item->flag;
			}
			$nextPeriod=$lastPeriod+1;
			if ($is_edit && $lastPeriodLock>=_PROJECT_LOCKREPORT) $ret.=' หรือ <a class="btn" href="'.url('project/'.$tpid.'/operate.trainer/'.$nextPeriod,'act=create').'" confirm="ยืนยันการสร้าง '.$formname.' ครั้งที่ '.$nextPeriod.'?">สร้าง '.$formname.' ครั้งที่ '.$nextPeriod.'</a>';
		} else {
			$ret.='ยังไม่มี'.$formname;
			$nextPeriod=1;
			if ($is_edit) $ret.=' ต้องการสร้าง <strong>"'.$formname.'"</strong> หรือไม่?<br /><br /><a class="btn" href="'.url('project/'.$tpid.'/operate.trainer/1','act=create').'" confirm="ยืนยันการสร้าง '.$formname.'?">สร้าง '.$formname.'</a> <a class="btn" href="'.url('project/'.$tpid.'/operate.trainer').'">ไม่สร้าง</a>';
		}
		if (i()->ok && cfg('project.thaihealth.online')) $ret.=' หรือ <a href="https://webpro.thaihealth.or.th/ProjectMonitor/ProjectManage.aspx" target="_blank">ป้อนบันทึกการติดตามในระบบติดตามโครงการออนไลน์ที่ สสส.</a><br /><br /><iframe id="sssonline"class="autoHeight" src="https://webpro.thaihealth.or.th/ProjectMonitor/ProjectManage.aspx" frameborder="0" width="100%" min-height="500px" style="margin:80px 0 0 0;"></iframe>';
		$ret.='</p>';
		$ret.='<script>
$(document).ready(function() {
var frame=$("#sssonline");
frame.load(resizeIframe);   //wait for the frame to load
//    $(window).resize(resizeIframe);
//    alert(frame.contents().find("body").attr("scrollHeight"));

function resizeIframe() {
	//detect browser dimensions
	var h = $.browser.mozilla?$(window).height():$(document).height();
	//set new dimensions for the iframe
	frame.height(h);
}
});
		</script>';
		return $ret;
	} else if ($period && $_REQUEST['act']=='create' && $is_edit) {
		mydb::query('INSERT INTO %project_tr% (`tpid`, `formid`, `period`, `part`,  `flag`, `uid`, `created`) VALUES (:tpid, :formid, :period, :part, :flag, :uid, :created)', ':tpid', $tpid, ':formid', $formid, ':period', $period, ':part','title', ':flag',_PROJECT_DRAFTREPORT, ':uid', i()->uid, ':created', date('U'));
		location('project/'.$tpid.'/operate.trainer/'.$period);
		return $ret;
	} else if ($period && $_REQUEST['lock'] && user_access('administer projects')) {
		$trid=mydb::select('SELECT `trid` FROM %project_tr% tr WHERE tr.`tpid`=:tpid AND tr.`formid`=:formid AND `part`="title" AND `period`=:period LIMIT 1',':tpid',$tpid,':formid',$formid, ':period',$period)->trid;
		project_model::set_lock_report($trid);
		location('project/'.$tpid.'/operate.trainer/'.$period);
		return $ret;
	} else if ($_REQUEST['act']=='complete' && $is_edit) {
		$trid=mydb::select('SELECT `trid` FROM %project_tr% tr WHERE tr.`tpid`=:tpid AND tr.`formid`=:formid AND `part`="title" AND `period`=:period LIMIT 1',':tpid',$tpid,':formid',$formid, ':period',$period)->trid;
		mydb::query('UPDATE %project_tr% SET `flag`="'._PROJECT_COMPLETEPORT.'" WHERE `trid`=:trid LIMIT 1',':trid',$trid);
		location('project/'.$tpid.'/operate.trainer/'.$period);
		return $ret;
	}





	$irs=mydb::select('SELECT * FROM %project_tr% tr WHERE tr.`tpid`=:tpid AND tr.`formid`=:formid AND `part`="title" AND `period`=:period LIMIT 1',':tpid',$tpid,':formid',$formid, ':period', $period);
	$locked=$irs->flag>=_PROJECT_LOCKREPORT;

	if ($locked) $is_edit=false;

	$ret.='<div class="inline-edit project__report">'._NL;
	$ret.='<h3>แบบบันทึกการติดตามสนับสนุนโครงการ ครั้งที่ '.$period.'</h3>'._NL;
	$ret.='<p class="form-info" style="position:absolute;right:20px;margin-top:-10px;">รหัสโครงการ <strong>'.$projectInfo->info->prid.'</strong><br />สัญญาเลขที่ <strong>'.$projectInfo->info->agrno.'</strong></p>'._NL;
	$ret.='<p>ชื่อโครงการ <strong>'.$projectInfo->title.'</strong><br />รหัสโครงการ <strong>'.$projectInfo->info->prid.'</strong> สัญญาเลขที่ <strong>'.$projectInfo->info->agrno.'</strong><br />'._NL;
	$ret.='ระยะเวลาตามสัญญา <strong>'.($projectInfo->info->date_from?sg_date($projectInfo->info->date_from,'ว ดดด ปปปป'):'ไม่ระบุ').' - '. ($projectInfo->info->date_end?sg_date($projectInfo->info->date_end,'ว ดดด ปปปป'):'ไม่ระบุ').'</strong></p>'._NL;
	$ret.='</div>'._NL;

	$ui=new Ui();
	$ui->add('<a class="btn" href="#part1"><i class="icon -list"></i><span>ส่วนที่ 1 - การติดตาม</span></a>');
	$ui->add('<a class="btn" href="#part2"><i class="icon -list"></i><span>ส่วนที่ 2 - ความก้าวหน้า/จุดเด่น</span></a>');
	$ui->add('<a class="btn" href="#part3"><i class="icon -list"></i><span>ส่วนที่ 3 - ประเมินความเสี่ยง</span></a>');
	$ui->add('<a class="btn" href="#part4"><i class="icon -list"></i><span>ส่วนที่ 4 - ความเห็น</span></a>');
	$ui->add('<a class="btn" href="#part5"><i class="icon -list"></i><span>ส่วนที่ 5 - อื่น ๆ</span></a>');
	$ui->add('<a class="btn" href="#all"><i class="icon -module"></i><span>ทั้งหมด</span></a>');
	$ui->add('<a class="btn" href="'.url('project/'.$tpid.'/operate.trainer/'.$period).'"><i class="icon -refresh"></i><span>รีเฟรช</span></a>');
	if ($is_edit && $irs->flag!=_PROJECT_COMPLETEPORT) $ui->add('<a class="btn" href="'.url('project/'.$tpid.'/operate.trainer/'.$period,'act=complete').'"><i class="icon -save"></i><span>แจ้งรายงานเสร็จสมบูรณ์</span></a>');
	if (user_access('administer projects')) $ui->add('<a class="btn" href="'.url('project/'.$tpid.'/operate.trainer/'.$period,'lock='.($locked=='yes'?'no':'yes')).'"><i class="icon -'.($locked?'lock':'unlock').'"></i><span>'.($locked?'Lock':'UnLock').'</span></a>');
	$ret.='<nav id="form-period" class="nav -page">'.$ui->build().'</nav>';


	if (cfg('project.grantby')=='สสส.') {
		$argno=preg_replace('/[^0-9]/','',$projectInfo->info->agrno);

		//		$ret.='Arg No = '.$projectInfo->info->agrno.' => '.$argno;
		//			$ret.='<iframe src="http://61.90.149.15/ThaiHealth_phase2_test/MonitorInterface.aspx?AgreementNo='.$argno.'&MonitorNo='.$period.'" width="100%" height="400" border="0" style="border:none;"></iframe>';
	}

	if ($is_edit) {
		$inlineAttr['data-update-url']=url('project/edit/tr');
		$inlineAttr['data-period']=$period;
		if (post('debug')) $inlineAttr['data-debug']='yes';
	}
	$ret.='<div id="project-report-follow" class="inline-edit project__report" '.sg_implode_attr($inlineAttr).'>'._NL;

	$ret .= R::Page('project.operate.trainer.form',NULL,$projectInfo, $period);
	$ret.='</div><!--trainer-follow-main-->'._NL;


	$ret.='<script type="text/javascript"><!--
var period='.$period.';
var currentPart="#'.SG\getFirst($_GET['part'],'part1').'";
var debug='.(isset($_REQUEST['debug'])?'true':'false').';

function displayPart(partNo) {
if (partNo) currentPart=partNo;
if (currentPart=="#all") {
	$("#part1, #part2, #part3, #part4, #part5").css("display","block");
} else {
	$("#part1, #part2, #part3, #part4, #part5").css("display","none");
	$(currentPart).css("display","block");
}
}

$(document).ready(function() {
displayPart();
$("#form-period a").click(function() {
	$this=$(this);
	var href=$this.attr("href");
	if (href.substring(0,1)=="#") {
		displayPart(href);
		return false;
	}
});
});
--></script>';

	//$ret .= print_o($projectInfo,'$projectInfo');

	return $ret;
}
?>