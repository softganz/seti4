<?php
function project_develop_proposal($self, $tpid, $action = NULL) {
	$devInfo = is_object($tpid) ? $tpid : R::Model('project.develop.get',$tpid);
	$tpid = $devInfo->tpid;

	if (empty($tpid))
		return $ret.message('error','ขออภัย : ไม่มีโครงการที่กำลังพัฒนาอยู่ในระบบ');

	$isAdmin=$info->RIGHT & _IS_ADMIN;
	$isTrainer=$info->RIGHT & _IS_TRAINER;
	$isEditable=$info->RIGHT & _IS_EDITABLE;
	$isFullView=$info->RIGHT & _IS_RIGHT;

	if ($isEditable && $action!='edit') {
		$ret.='<div class="btn-floating -right-bottom"><a class="sg-action btn -floating -circle48" href="'.url('project/develop/proposal/'.$tpid.'/edit',array('debug'=>post('debug'))).'" data-rel="#main"><i class="icon -edit -white"></i></a></div>';
	}


	$isEdit=$action=='edit' && $isEditable;

	R::View('project.toolbar',$self,$info->title,'develop',$info);


	if ($isEdit) {
		head('<script>var tpid='.$tpid.'</script>');

		$inlinePara['class']=' inline-edit';
		$inlinePara['data-tpid']=$tpid;
		$inlinePara['data-update-url']=url('project/develop/update/'.$tpid);
		if (post('debug')) $inlinePara['data-debug']='yes';
	}

	foreach ($inlinePara as $k => $v) {
		$inlineStr.=$k.'="'.$v.'" ';
	}

	$ret.='<div id="project-develop" '.$inlineStr.'>'._NL;

	$ret.='<section id="project-cover" class="box project-cover">';
	$ret.='<h2 class="title">แบบเสนอโครงการ</h2>';
	$ret.='<p>รหัสโครงการ …………………………………………</p>';
	$ret.='<p>ชื่อโครงการ/กิจกรรม <b>'.$devInfo->info->title.'</b></p>';
	$ret.='<p>ชื่อองค์กร <b>'.$devInfo->data['org-name'].'</b></p>';
	$ret.='<p>กลุ่มคน<br />'.sg_text2html($devInfo->data['owner-name-all']).'';

	$ret.='<p>วันอนุมัติ …………………………………………</p>';
	$ret.='ระยะเวลาดำเนินโครงการ	 ตั้งแต่ วันที่ '.sg_date($devInfo->info->date_from,'ว ดดด ปปปป').' ถึง '.sg_date($devInfo->info->date_end,'ว ดดด ปปปป').'</p>';

	$ret.='งบประมาณ จำนวน '.number_format($devInfo->info->budget,2).' บาท</p>';

	$ret.='</section><!-- project-cover -->';

	$ret.='<hr class="pagebreak" />';

	$ret.='<section id="project-detail" class="box">';
	$ret.='<h3>หลักการและเหตุผล</h3>'.sg_text2html($devInfo->data['project-problem']).'';

	$ret.='<h3>สถานการณ์ปัญหา</h3>';
	$tables = new Table();
	$tables->thead=array('no'=>'','สถานการณ์ปัญหา','size -amt'=>'ขนาด');
	foreach ($devInfo->problem as $rs) {
		$tables->rows[]=array(
											++$no,
											$rs->refid?
											$rs->problem
											:
											$rs->problem
											,
											number_format($rs->problemsize,2),
										);
	}
	$ret.=$tables->build();


	/*
	$ret.='<h3>วิธีดำเนินการ</h3>';

	$ret.='<ol>';
	foreach ($devInfo->activity as $mainact) {
		if (empty($mainact->trid)) continue;
		$ret.='<li>'.$mainact->title.'</li>';
	}
	$ret.='</ol>';
	*/

	$ret.='<h3>วัตถุประสงค์/ตัวชี้วัด</h3>';
	$objectiveNo=0;
	$tables = new Table();
	$tables->colgroup=array('no'=>'width="5%"','objective'=>'width="40%"','indicator'=>'width="40%"', 'problemsize -amt' => 'width="5%"','targetsize -amt'=>'width="5%"');
	$tables->thead=array(
									'no'=>'',
									'วัตถุประสงค์',
									'ตัวชี้วัดความสำเร็จ',
									'ขนาด',
									'เป้าหมาย 1 ปี',
									);

	foreach ($devInfo->objective as $objective) {
		$tables->rows[]=array(
											++$objectiveNo,
											$objective->title,
											$objective->indicatorDetail,
											$objective->problemsize != '' ? $objective->problemsize : '',
											$objective->targetsize,
										);
	}
	$ret.=$tables->build();


	$ret .= '<h3>วิธีดำเนินการ/กิจกรรม</h3>';

	$activityIdx = 0;
	foreach ($devInfo->activity as $activity) {
		if (empty($activity->trid)) continue;
		$ret .= '<h4>'.(++$activityIdx).'. '.$activity->title.'</h4>';
		$ret .= '<b>รายละเอียด</b><p>'.nl2br($activity->desc).'</p>';
		if ($activity->fromdate) {
			$ret .= '<p><b>ระยะเวลาดำเนินงาน </b> '.sg_date($activity->fromdate, 'ว ดดด ปปปป');
			if ($activity->todate) $ret .= ' - '.sg_date($activity->todate, 'ว ดดด ปปปป');
			$ret .= '</p>';
		}
		if ($activity->output) $ret .= '<b>ผลที่คาดว่าจะได้รับ</b><p>'.nl2br($activity->output).'</p>';
		$ret .= '<p><b>งบประมาณ '.number_format($activity->budget,2).' บาท</b></p>';
		if ($activity->copartner) $ret .= '<b>ภาคีร่วมสนับสนุน</b><p>'.nl2br($activity->copartner).'</p>';
	}

	$ret.='<h3>ระยะเวลาดำเนินการ</h3><p>ระยะเวลาดำเนินโครงการ	 ตั้งแต่ วันที่ '.sg_date($devInfo->info->date_from,'ว ดดด ปปปป').' ถึง '.sg_date($devInfo->info->date_end,'ว ดดด ปปปป').'</p>';

	if ($devInfo->info->area != '') {
		$ret.='<h3>สถานที่ดำเนินการ</h3><p>'.$devInfo->info->area.'</p>';
	}

	$ret.='<h3>งบประมาณ</h3><p>จากงบประมาณกองทุนหลักประกันสุขภาพ'.$devInfo->info->orgName.'  จำนวน '.number_format($devInfo->info->budget,2).' บาท</b> รายละเอียดดังในวิธีดำเนินการ/กิจกรรม ด้านบน';
	if ($devInfo->data['budget-remark']) $ret .= '<p><b>หมายเหตุ : </b>'.$devInfo->data['budget-remark'].'</p>';

	//$ret.=R::Page('project.develop.plan.single',NULL,$tpid);

	if ($devInfo->data['project-coorg'] != '') {
		$ret .= '<h3>องค์กรภาคีที่ร่วมดำเนินงาน</h3>'
				. sg_text2html($devInfo->data['project-coorg']);
	}

	if ($devInfo->data['project-evaluation'] != '') {
		$ret .= '<h3>การติดตาม/การประเมินผล</h3>'
				. sg_text2html($devInfo->data['project-evaluation']);
	}

	if ($devInfo->data['project-continuously'] != '') {
		$ret .= '<h3>แนวทางการพัฒนาเพื่อให้เกิดความต่อเนื่องยั่งยืนและการขยายผล</h3>'
				. sg_text2html($devInfo->data['project-continuously']);
	}

	if ($devInfo->data['project-colearning'] != '') {
		$ret .= '<h3>การแลกเปลี่ยนเรียนรู้/การเผยแพร่ผลการดำเนินโครงการ</h3>'
				. sg_text2html($devInfo->data['project-colearning']);
	}

	if ($devInfo->data['project-cofunding'] != '') {
		$ret .= '<h3>การขอทุนจากแหล่งอื่น</h3>'
				. sg_text2html($devInfo->data['project-cofunding']);
	}

	if ($devInfo->data['conversion-human'] != '') {
		$ret .= '<h3>ผลที่คาดว่าจะได้รับ</h3>'
				. sg_text2html($devInfo->data['conversion-human']);
	}


	$ret .= '<p style="width:500px;margin:32px 0 0 auto; text-align:center;">ลงชื่อ  . . . . . . . . . . . . . . . . . . . . . . . . . . . <span style="display:inline-block;width:14em; text-align: left;">ผู้เสนอแผนงาน/โครงการ/กิจกรรม</span><br /><br />
       ( . . . . . . . . . . . . . . . . . . . . . . . . . . . .)<span style="display:inline-block;width:12em;"></span><br /><br />
ตำแหน่ง  . . . . . . . . . . . . . . . . . . . . . . . . . . . <span style="display:inline-block;width:13em;"></span><br /><br />
วันที่-เดือน-พ.ศ.  . . . . . . . . . . . . . . . . . .<span style="display:inline-block;width:13em;"></span></p>';

	$ret.='</section><!-- project-detail -->';




	$ret.='<hr class="pagebreak" />';
	$ret.='<section id="project-result" class="box project-result">';
	$ret.='<h3>ส่วนที่ 2 : ผลการพิจารณาแผนงาน/โครงการ/กิจกรรม (สำหรับเจ้าหน้าที่ ที่ได้รับมอบหมายลงรายละเอียด)</h3>';

	$ret.='<p class="text-indent">ตามมติการประชุมคณะกรรมการ  . . . . . . . . . . . . . .  . . . . . . . . . . . . . . . . . . . . . . . . . . . . <br />ครั้งที่  . . . . . . . . . . . / . . . . . . . . . เมื่อวันที่  . . . . . . . . . . . . . . . . . . .  ผลการพิจารณาแผนงาน/โครงการ/กิจกรรม ดังนี้</p>
		<p class="text-indent"><input type="checkbox" readonly="readonly" disabled="disabled" /> อนุมัติงบประมาณ เพื่อสนับสนุนแผนงาน/โครงการ/กิจกรรม จำนวน  . . . . . . . . . . . . . . บาท<br />
		เพราะ . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . </p>
		<p class="text-indent"><input type="checkbox" readonly="readonly" disabled="disabled" /> ไม่อนุมัติงบประมาณ เพื่อสนับสนุนแผนงาน/โครงการ/กิจกรรม<br />
		เพราะ . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . </p>
		  <p class="text-indent">หมายเหตุเพิ่มเติม (ถ้ามี) . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . </p>
		 <p class="text-indent">ให้รายงานผลความสำเร็จของแผนงาน/โครงการ/กิจกรรม ตามแบบฟอร์ม ภายในวันที่  . . . . . . . . . . . . . . . . . . . . . . . . . . . .</p>
		 <p style="width:400px;margin:32px 0 0 auto; text-align:center;">ลงชื่อ  . . . . . . . . . . . . . . . . . . . . . . . . . . . .&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br /><br />
       ( . . . . . . . . . . . . . . . . . . . . . . . . . . . .)<br /><br />
ตำแหน่ง  . . . . . . . . . . . . . . . . . . . . . . . . . . . .&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br /><br />
วันที่-เดือน-พ.ศ.  . . . . . . . . . . . . . . . . . .&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</p>';
	$ret.='</section><!-- project-2 -->';

	$ret.='</div><!-- project-develop -->'._NL;

	//$ret.=print_o($devInfo,'$devInfo');

	$ret.='<style type="text/css">
	.project-develop-plan-add {display:none;}
	abbr.checkbox {display:block; padding:4px 0;}
	h4, h5 {background:#eee; margin:8px 0;}
	.project-cover .title {text-align: center; padding-bottom:32px;}
	.project-cover p {padding:8px 0;}
	.project-result p {padding:16px 0;}
	.project-result p.text-indent {text-indent:1cm;}

	@media print {
		.project-cover p.-hidden {display: block;}
		.module-project .box {padding:0; margin:0; box-shadow:none; border:none;}
		.module-project .box h3,
		.module-project .box h4,
		.module-project .box h5 {color:#000; background:transparent; padding:8px 0;}
	}
	</style>';

	$ret .= '<script type="text/javascript">
	$(".inline-edit-field.-category").change(function() {
		var $this = $(this)
		var coverId = "#cover-category-" + $this.val()
		$(".cover-category").prop("checked", false)
		$(coverId).prop("checked", true)
	});
	$(".inline-edit-field.-ownergroup").change(function() {
		var $this = $(this)
		var coverId = "#cover-ownergroup-" + $this.val()
		$(".cover-ownergroup").prop("checked", false)
		$(coverId).prop("checked", true)
	});

	</script>';
	return $ret;
}
?>