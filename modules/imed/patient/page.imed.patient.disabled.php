<?php
/**
* Vew personal disabled information
*
* @param Integer $psnId
* @return String
*/
function imed_patient_disabled($self, $psnId = NULL) {
	$psnId = SG\getFirst($psnId,post('id'));

	$psnInfo = is_object($psnId) ? $psnId : R::Model('imed.patient.get', $psnId, '{}');
	$psnId = $psnInfo->psnId;

	if (empty($psnInfo)) return message('error','ไม่มีข้อมูล');

	$isDisabled = $psnInfo->disabled->pid;

	$isAccess=$psnInfo->RIGHT & _IS_ACCESS;
	$isEdit=$psnInfo->RIGHT & _IS_EDITABLE;

	$currentUrl = url('imed/patient/'.$psnId.'/disabled');

	if (!$isAccess) return message('error',$psnInfo->error);

	//$ret.=print_o($psnInfo,'$psnInfo');

	if (!$isDisabled) {
		$ret .= '<div class="-sg-text-center" style="padding: 32px 0;">'
			. '<p class="notify"><strong>'.$psnInfo->fullname.'</strong> ไม่ได้อยู่ในกลุ่มของคนพิการ</p>'
			. ($isEdit ? '<p style="padding: 32px 0;">ต้องการเพิ่ม <strong>'.$psnInfo->fullname.'</strong> เข้าไว้ในกลุ่มคนพิการหรือไม่?</p><p><a class="sg-action btn -primary" href="'.url('imed/patient/'.$psnId.'/info/disabled.add').'" data-rel="notify" data-done="load->replace:parent div:'.$currentUrl.'"><i class="icon -addbig -white"></i><span>เพิ่มเข้ากลุ่มคนพิการ</span></a></p>' : '')
			. '</div>';
		return $ret;
	}


	if (post('type')=='short') {
		$ret.='<div class="popup-profile"><a href="'.url('imed', ['pid' => $psnId]).'" role="patient" data-pid="'.$psnId.'" tooltip-uri="'.url('imed/patient/individual/'.$psnId,'type=short').'"><img src="'.imed_model::patient_photo($psnId).'" class="disabled-info-photo" /><span class="name">'.$psnInfo->fullname.'</span></a><span class="address">ที่อยู่ '.$psnInfo->info->address.'</span></div>';
		return $ret;
	}


	$ui = new Ui();
	$dropUi = new Ui();
	if ($isEdit) {
		if ($psnInfo->disabled) {
			$dropUi->add('<a class="sg-action" href="'.url('imed/patient/'.$psnId.'/info/disabled.remove').'" data-rel="notify" data-done="load->replace:parent section:'.url('imed/patient/disabled/'.$psnId).'" data-title="ลบชื่อออกจากกลุ่มคนพิการ" data-confirm="ต้องการลบชื่อออกจากกลุ่มคนพิการ กรุณายืนยัน?"><i class="icon -material">cancel</i><span>ลบออกจากกลุ่มคนพิการ</span></a>');
		}
	}
	if ($dropUi->count()) $ui->add(sg_dropbox($dropUi->build()));

	$liketitle=$isEdit?'คลิกเพื่อแก้ไข':'';
	$editclass=$isEdit?'editable':'';
	$emptytext=$isEdit?'<span style="color:#999;">แก้ไข</span>':'';

	$inlineAttr = array();

	if ($isEdit) {
		$inlineAttr['class'] = 'sg-inline-edit';
		$inlineAttr['data-update-url'] = url('imed/edit/patient');
		$inlineAttr['data-psnid'] = $psnId;
		if (debug('inline')) $inlineAttr['data-debug'] = 'inline';
	}

	$inlineAttr['data-url'] = $currentUrl;

	include_once 'modules/imed/assets/qt.individual.php';

	$ret .= '<section id="imed-care-disabled" '.sg_implode_attr($inlineAttr).'>'._NL;

	$ret .= '<header class="header"><h3>ข้อมูลคนพิการ'.($psnInfo->info->dischar == 1 ? ' (เสียชีวิต)' : '').'</h3><nav class="nav -page -sg-text-right">'.$ui->build().'</nav></header>';



	$tables = new Table();
	$tables->id='imed-patient-individual';

	$tables->rows[] = array(
		'วันที่บันทึกข้อมูล',
		$psnInfo->disabled->created ? sg_date($psnInfo->disabled->created,'ว ดดด ปปปป H:i:s'):''
	);

	$tables->rows[]=array(
		'Discharge',
		imed_model::qt('discharge',$qt,$psnInfo->disabled->discharge_desc,$isEdit)
		.imed_model::qt('dischargedate',$qt,$psnInfo->disabled->dischargedate,$isEdit)
		.($isEdit?'<p class="notify">หากผู้ป่วยเสียชีวิต กรุณาระบุรายละเอียดและวันที่ ในช่องด้านบน</p>':'')
	);

	$tables->rows[]=array('จดทะเบียนคนพิการ',
		imed_model::qt('register',$qt,$psnInfo->disabled->register,$isEdit).'<br />'
		.imed_model::qt('regdate',$qt,$psnInfo->disabled->regdate,$isEdit).'<br />'
		.imed_model::qt('PSNL.1.9.1.2',$qt,$psnInfo->qt,$isEdit)
		.imed_model::qt('PSNL.1.9.1.3',$qt,$psnInfo->qt,$isEdit).'<br />'
		.imed_model::qt('PSNL.1.9.1.4',$qt,$psnInfo->qt,$isEdit).'<br />'
		.imed_model::qt('DSBL.REGIST.CAUSE',$qt,$psnInfo->qt,$isEdit).'<br />'
	);

	$tables->rows[]=array('เอกสารประจำตัว',
		imed_model::qt('PSNL.1.9.1.1',$qt,$psnInfo->qt,$isEdit).': '._NL
		.imed_model::qt('PSNL.CARD.DISABLED.TYPE',$qt,$psnInfo->qt,$isEdit).'<br />'._NL
		.imed_model::qt('PSNL.1.9.2.1',$qt,$psnInfo->qt,$isEdit).'<br />'._NL
		.imed_model::qt('PSNL.CARD.ELDER',$qt,$psnInfo->qt,$isEdit).'<br />'._NL
		.imed_model::qt('PSNL.1.9.4.1',$qt,$psnInfo->qt,$isEdit).'<br />'._NL
		.imed_model::qt('PSNL.1.9.5.1',$qt,$psnInfo->qt,$isEdit)._NL
		.imed_model::qt('PSNL.CARD.IDENTIFY',$qt,$psnInfo->qt,$isEdit).'<br />'._NL
	);

	$tables->rows[]=array('ประเภทความพิการ',
		imed_model::qt('DSBL.DEFECT.VISUAL',$qt,$psnInfo->defect[1]->defectid,$isEdit).'<br />'._NL
		.imed_model::qt('DSBL.DEFECT.HEARING',$qt,$psnInfo->defect[2]->defectid,$isEdit).'<br />'._NL
		.imed_model::qt('DSBL.DEFECT.MOVEMENT',$qt,$psnInfo->defect[3]->defectid,$isEdit).'<br />'._NL
		.imed_model::qt('DSBL.DEFECT.MENTAL',$qt,$psnInfo->defect[4]->defectid,$isEdit).'<br />'._NL
		.imed_model::qt('DSBL.DEFECT.INTELLECTUAL',$qt,$psnInfo->defect[5]->defectid,$isEdit).'<br />'._NL
		.imed_model::qt('DSBL.DEFECT.LEARNING',$qt,$psnInfo->defect[6]->defectid,$isEdit).'<br />'._NL
		.imed_model::qt('DSBL.DEFECT.AUTISTIC',$qt,$psnInfo->defect[7]->defectid,$isEdit).'<br />'._NL
		.'<p>หมายเหตุ :  <ul><li>ประเภทความพิการและลักษณะความพิการ ที่ระบุในบัตรประจำตัวคนพิการ</li><li>แบ่งประเภทความพิการตาม พรบ.   ส่งเสริมและพัฒนาคุณภาพชีวิตคนพิการ พ.ศ.๒๕๕๐</li><li>การยกเลิกประเภทความพิการ จะทำการลบรายละเอียดที่เกี่ยวกับความพิการประเภทนั้นทั้งหมด</li></ul></p>'
	);

	$tables->rows[]=array(
		'สาเหตุความพิการ',
		imed_model::qt('begetting',$qt,$psnInfo->disabled->begetting_desc,$isEdit).'<br />'
		.imed_model::qt('DSBL.2.1.2',$qt,$psnInfo->qt,$isEdit).'<br />'
		.imed_model::qt('DSBL.2.1.3',$qt,$psnInfo->qt,$isEdit).' ปี<br />'
	);

	$tables->rows[]=array(
		'ระดับความพิการ',
		imed_model::qt('disabilities_level',$qt,$psnInfo->disabled->disabilities_level_name,$isEdit)
	);

	$tables->rows[]=array(
		'สื่อสารได้หรือไม่',
		imed_model::qt('comunicate',$qt,$psnInfo->disabled->comunicate,$isEdit)
	);

	$ret .= $tables->build();

	$tables = new Table();
	$tables->addClass('info');
	$tables->id='imed-patient-defect';
	$tables->caption='ข้อมูลความบกพร่อง';
	$tables->rows[0]=array('ประเภทความบกพร่อง');
	$tables->rows[1]=array('ลักษณะความบกพร่อง-พิการ');
	$tables->rows[2]=array('รายละเอียดความบกพร่อง');
	$tables->rows[3]=array('การเกิดความบกพร่อง-พิการ');
	$tables->rows[4]=array('การเกิดความบกพร่อง-พิการ - ภายหลังเมื่ออายุกี่ปี');
	$tables->rows[5]=array('สาเหตุของความบกพร่อง-พิการ');
	$tables->rows[6]=array('สาเหตุของความบกพร่อง-พิการ - โรคอื่น ๆ ระบุ');

	foreach ($psnInfo->defect as $defectid=>$defect) {
		//$ret.=print_o($defect,'$defect');
		$defectDetail[$defectid] = new Table();
		$defectDetail[$defectid]->addClass('info');
		$defectDetail[$defectid]->id='imed-patient-defect';
		$defectDetail[$defectid]->caption='ข้อมูลความบกพร่อง';
		$defectDetail[$defectid]->rows[] = array(
			'ประเภทความบกพร่อง',
			view::inlineedit(
				array(
					'group'=>'defect',
					'fld'=>'defect',
					'tr'=>$defectid),
				$defect->defect
				. ($isEdit ? ' <a class="sg-action" href="'.url('imed/api/patient/'.$psnId.'/disabled.defect.remove/'.$defect->defectid).'" data-rel="notify" data-done="load->replace:parent section:'.$currentUrl.'" data-title="ลบความบกพร่อง" data-confirm="ต้องการลบรายการความบกพร่อง รายการนี้จริงหรือ กรุณายืนยันการลบรายการ?"><i class="icon -material -gray">cancel</i></a>':'')
			)
			. ($defect->level?' Level '.$defect->level:'')
		);

		//$defectDetail[$defectid]->rows[]=array('ลักษณะความบกพร่อง-พิการ',view::inlineedit(array('group'=>'defect','fld'=>'kind','tr'=>$defectid),$defect->kind,$isEdit,'select','ตาบอด,ตาเลือนราง,หูหนวก,หูตึง,สื่อความหมาย,การเคลื่อนไหว,ร่างกาย,พัฒนาการ,สติปัญญา'));
		//$defectDetail[$defectid]->rows[]=array('รายละเอียดความบกพร่อง',view::inlineedit(array('group'=>'defect','fld'=>'detail','tr'=>$defectid),$defect->detail,$isEdit));
		$defectDetail[$defectid]->rows[]=array('การเกิดความบกพร่อง-พิการ',view::inlineedit(array('group'=>'defect','fld'=>'begin','tr'=>$defectid),$defect->begin,$isEdit,'select','ตั้งแต่เกิด,ภายหลัง'));
		$defectDetail[$defectid]->rows[]=array('การเกิดความบกพร่อง-พิการ - ภายหลังเมื่ออายุกี่ปี',view::inlineedit(array('group'=>'defect','fld'=>'year','tr'=>$defectid),$defect->year,$isEdit));
		$defectDetail[$defectid]->rows[]=array('สาเหตุของความบกพร่อง-พิการ',view::inlineedit(array('group'=>'defect','fld'=>'cause','tr'=>$defectid),$defect->cause,$isEdit,'select','พันธุกรรม,อุบัติเหตุ,ไม่ทราบสาเหตุ,โรคติดเชื้อ,โรคอื่น ๆ'));
		$defectDetail[$defectid]->rows[]=array('สาเหตุของความบกพร่อง-พิการ - โรคอื่น ๆ ระบุ',view::inlineedit(array('group'=>'defect','fld'=>'disease','tr'=>$defectid),$defect->disease,$isEdit));
	}




	$ret.='<p><b>ประเภทความพิการ	/ข้อมูลความบกพร่อง</b></p>';
	$ret.='<div class="sg-tabs" style="padding:2px;">';
	$ret.='<ul class="tabs">';
	$firstSee=reset(array_keys($psnInfo->defect));
	foreach ($psnInfo->defect as $defectid=>$defect) {
		$ret.='<li class="'.($defectid==$firstSee?'-active' : '-hidden').'"><a href="#see'.$defectid.'">'.$defect->defect.'</a></li>';
	}
	$ret.='</ul>';

	//$ret.=print_o($psnInfo->defect);

	$defectSee[1]='<!--ทางการเห็น-->'
		.imed_model::qt('DSBL.SEE.ไม่มีลูกตาทั้งสองข้าง',$qt,$psnInfo->qt,$isEdit).'<br />'
		.imed_model::qt('DSBL.SEE.ไม่มีลูกตาดำทั้งสองข้าง',$qt,$psnInfo->qt,$isEdit).'<br />'
		.imed_model::qt('DSBL.SEE.ลูกตาสีขาวขุ่นทั้งสองข้าง',$qt,$psnInfo->qt,$isEdit).'<br />'
		.imed_model::qt('DSBL.SEE.ลูกตาฝ่อทั้งสองข้าง',$qt,$psnInfo->qt,$isEdit).'<br />'
		.'<hr />'
		.imed_model::qt('DSBL.SEE.ตาบอดสนิทสองข้าง',$qt,$psnInfo->qt,$isEdit).'<br />'
		.imed_model::qt('DSBL.SEE.สายตาเลือนรางสองข้าง',$qt,$psnInfo->qt,$isEdit).'<br />'
		;
	$defectSee[2]='<!--ทางการได้ยินหรือสื่อความหมาย-->'
		.imed_model::qt('DSBL.SEE.ไม่มีรูหูทั้งสองข้าง',$qt,$psnInfo->qt,$isEdit).'<br />'
		.imed_model::qt('DSBL.SEE.หูหนวกสองข้าง',$qt,$psnInfo->qt,$isEdit).'<br />'
		.imed_model::qt('DSBL.SEE.หูตึงสองข้าง',$qt,$psnInfo->qt,$isEdit).'<br />'
		.imed_model::qt('DSBL.SEE.เป็นใบ้',$qt,$psnInfo->qt,$isEdit).'<br />';
	$defectSee[3]='<!--ทางการเคลื่อนไหวหรือทางร่างกาย-->'
		.imed_model::qt('DSBL.SEE.แขนและขาซีกซ้ายอ่อนแรง',$qt,$psnInfo->qt,$isEdit).'<br />'
		.imed_model::qt('DSBL.SEE.แขนและขาซีกขวาอ่อนแรง',$qt,$psnInfo->qt,$isEdit).'<br />'
		.imed_model::qt('DSBL.SEE.แขนและขาซีกซ้ายขยับไม่ได้',$qt,$psnInfo->qt,$isEdit).'<br />'
		.imed_model::qt('DSBL.SEE.แขนและขาซีกขวาขยับไม่ได้',$qt,$psnInfo->qt,$isEdit).'<br />'
		.imed_model::qt('DSBL.SEE.แขนและขาทั้งสองข้างขยับไม่ได้',$qt,$psnInfo->qt,$isEdit).'<br />'
		.'<hr />'
		.imed_model::qt('DSBL.SEE.ขาข้างซ้ายอ่อนแรง',$qt,$psnInfo->qt,$isEdit).'<br />'
		.imed_model::qt('DSBL.SEE.ขาข้างขวาอ่อนแรง',$qt,$psnInfo->qt,$isEdit).'<br />'
		.imed_model::qt('DSBL.SEE.ขาข้างซ้ายขยับไม่ได้',$qt,$psnInfo->qt,$isEdit).'<br />'
		.imed_model::qt('DSBL.SEE.ขาข้างขวาขยับไม่ได้',$qt,$psnInfo->qt,$isEdit).'<br />'
		.imed_model::qt('DSBL.SEE.ขาทั้งสองข้างขยับไม่ได้',$qt,$psnInfo->qt,$isEdit).'<br />'
		.'<hr />'
		.imed_model::qt('DSBL.SEE.แขนข้างซ้ายอ่อนแรง',$qt,$psnInfo->qt,$isEdit).'<br />'
		.imed_model::qt('DSBL.SEE.แขนข้างขวาอ่อนแรง',$qt,$psnInfo->qt,$isEdit).'<br />'
		.imed_model::qt('DSBL.SEE.แขนข้างซ้ายขยับไม่ได้',$qt,$psnInfo->qt,$isEdit).'<br />'
		.imed_model::qt('DSBL.SEE.แขนข้างขวาขยับไม่ได้',$qt,$psnInfo->qt,$isEdit).'<br />'
		.imed_model::qt('DSBL.SEE.แขนทั้งสองข้างขยับไม่ได้',$qt,$psnInfo->qt,$isEdit).'<br />'
		.'<hr />'
		.'สมองพิการ (ซี.พี.)<br />'
		.imed_model::qt('DSBL.SEE.กล้ามเนื้อเกร็ง',$qt,$psnInfo->qt,$isEdit).'<br />'
		.imed_model::qt('DSBL.SEE.อ่อนแรง',$qt,$psnInfo->qt,$isEdit).'<br />'
		.imed_model::qt('DSBL.SEE.มีข้อยึดติด',$qt,$psnInfo->qt,$isEdit).'<br />'
		.'<hr />'
		.imed_model::qt('DSBL.SEE.แขนขาดข้างขวา',$qt,$psnInfo->qt,$isEdit).'<br />'
		.imed_model::qt('DSBL.SEE.แขนขาดข้างซ้าย',$qt,$psnInfo->qt,$isEdit).'<br />'
		.'<hr />'
		.imed_model::qt('DSBL.SEE.ขาขาดข้างขวา',$qt,$psnInfo->qt,$isEdit).'<br />'
		.imed_model::qt('DSBL.SEE.ขาขาดข้างซ้าย',$qt,$psnInfo->qt,$isEdit).'<br />'
		;
	$defectSee[4]='<!--ทางจิตใจหรือพฤติกรรม-->'
		.imed_model::qt('DSBL.SEE.ประสาทหลอน',$qt,$psnInfo->qt,$isEdit).'<br />'
		.imed_model::qt('DSBL.SEE.หูแว่ว',$qt,$psnInfo->qt,$isEdit).'<br />'
		.imed_model::qt('DSBL.SEE.หลงผิดหวาดระแวง',$qt,$psnInfo->qt,$isEdit).'<br />'
		.imed_model::qt('DSBL.SEE.พูดคนเดียว',$qt,$psnInfo->qt,$isEdit).'<br />'
		;
	$defectSee[5]='<!--ทางสติปัญญา-->'
		.imed_model::qt('DSBL.SEE.กลุ่มอาการดาวน์',$qt,$psnInfo->qt,$isEdit).'<br />'
		;
	$defectSee[6]='<!--ทางการเรียนรู้-->'
		.'บกพร่องด้านการเรียนรู้ไม่เหมาะกับอายุจริงหรือแอลดี<br />'
		.imed_model::qt('DSBL.SEE.ด้านการอ่าน',$qt,$psnInfo->qt,$isEdit).'<br />'
		.imed_model::qt('DSBL.SEE.ด้านการเขียน',$qt,$psnInfo->qt,$isEdit).'<br />'
		.imed_model::qt('DSBL.SEE.ด้านการคำนวณ',$qt,$psnInfo->qt,$isEdit).'<br />'
		.imed_model::qt('DSBL.SEE.ด้านการเรียนรู้',$qt,$psnInfo->qt,$isEdit).'<br />'
		;
	$defectSee[7]='<!--ทางออทิสติก-->'
		.imed_model::qt('DSBL.SEE.บกพร่องในการใช้ท่าทาง',$qt,$psnInfo->qt,$isEdit).'<br />'
		.imed_model::qt('DSBL.SEE.ทำกิริยาซ้ำ',$qt,$psnInfo->qt,$isEdit).'<br />'
		;

	foreach ($psnInfo->defect as $defectid=>$defect) {
		$ret.='<div id="see'.$defectid.'" class="'.($defectid==$firstSee?'':'-hidden').'">';
		$ret.='<div style="width:50%;float:left;">'.$defectDetail[$defectid]->build().'</div>';
		$ret.='<div style="width:45%;margin-left:5%;float:left;"><p><b>ลักษณะความพิการที่เห็น</b></p>'.$defectSee[$defectid].'<hr />อื่น ๆ ระบุ'.imed_model::qt('DSBL.SEE.other',$qt,$psnInfo->qt,$isEdit).'</div>';
		$ret.='<br clear="all" />';
		$ret.='</div>';
	}
	$ret.='';
	$ret.='<br clear="all" /><strong>กรณีมีผู้ดูแล</strong> ผู้ดูแลให้การดูแลคนพิการในเรื่องใดบ้าง โปรดบรรยาย'
					.imed_model::qt('DSBL.NEEDCARE',$qt,$psnInfo->qt,$isEdit);
	$ret.='</div><!--sg-tabs-->';

	//$ret.=print_o($defectDetail,'$defectDetail');


	$ret.='<p><b>คนพิการควรได้รับการช่วยเหลือเพิ่มเติม</b></p>';
	$ret.='<div class="sg-tabs" id="tabs-help" style="padding:2px;border:1px #ccc solid;">';
	$ret.='<ul class="tabs"><li class="-active"><a href="#help1"><i class="icon -forward"></i>ทางกายและการเคลื่อนไหว</a></li><li><a href="#help2"><i class="icon -forward"></i>ทางการมองเห็น</a></li><li><a href="#help3"><i class="icon -forward"></i>ทางการได้ยินหรือสื่อความหมาย</a></li><li><a href="#help4"><i class="icon -forward"></i>สติปัญญา, ออทิสติก, และจิตและพฤติกรรม</a></li></ul>';

	$ret.='<p>';
	$ret.='<b>รายละเอียดที่ควรได้รับการช่วยเหลือเพิ่มเติม</b><br />';
	$ret.=imed_model::qt('DSBL.SHOULDHELP.ทำความสะอาดร่างกาย',$qt,$psnInfo->qt,$isEdit).'<br />'._NL;
	$ret.='</p>';

	$ret.='<div id="help1">';
	$ret.=imed_model::qt('DSBL.SHOULDHELP.ทำแผลกดทับ',$qt,$psnInfo->qt,$isEdit).'<br />'._NL;
	$ret.=imed_model::qt('DSBL.SHOULDHELP.เปลี่ยนสายสวนปัสสาวะ',$qt,$psnInfo->qt,$isEdit).'<br />'._NL;
	$ret.=imed_model::qt('DSBL.SHOULDHELP.เปลี่ยนสายให้อาหารทางจมูก',$qt,$psnInfo->qt,$isEdit).'<br />'._NL;
	$ret.=imed_model::qt('DSBL.SHOULDHELP.ช่วยฝึกกายภาพบำบัดหรือฝึกให้ทำด้วยตนเอง',$qt,$psnInfo->qt,$isEdit).'<br />'._NL;
	$ret.=imed_model::qt('DSBL.SHOULDHELP.ฝึกการช่วยเหลือตัวเองในกิจวัตรประจำวัน',$qt,$psnInfo->qt,$isEdit)._NL;
	$ret.=imed_model::qt('DSBL.SHOULDHELP.ฝึกการช่วยเหลือตัวเองในกิจวัตรประจำวัน.อื่น ๆ',$qt,$psnInfo->qt,$isEdit).'<br />'._NL;
	$ret.=imed_model::qt('DSBL.SHOULDHELP.การฝึกทักษะเพื่อการดำรงชีวิตตามความต้องการ',$qt,$psnInfo->qt,$isEdit).'<br />'._NL;
	$ret.='</div>';

	$ret.='<div id="help2" class="-hidden">';
	$ret.=imed_model::qt('DSBL.SHOULDHELP.ฝึกทักษะ O and M',$qt,$psnInfo->qt,$isEdit).'<br />'._NL;
	$ret.='</div>';

	$ret.='<div id="help3" class="-hidden">';
	$ret.=imed_model::qt('DSBL.SHOULDHELP.ฝึกทักษะการพูด',$qt,$psnInfo->qt,$isEdit).'<br />'._NL;
	$ret.=imed_model::qt('DSBL.SHOULDHELP.ฝึกการใช้ภาษามือ',$qt,$psnInfo->qt,$isEdit).'<br />'._NL;
	$ret.='</div>';

	$ret.='<div id="help4" class="-hidden">';
	$ret.=imed_model::qt('DSBL.SHOULDHELP.ฝึกการช่วยเหลือตัวเองในกิจวัตรประจำวัน',$qt,$psnInfo->qt,$isEdit)._NL;
	$ret.=imed_model::qt('DSBL.SHOULDHELP.ฝึกการช่วยเหลือตัวเองในกิจวัตรประจำวัน.อื่น ๆ',$qt,$psnInfo->qt,$isEdit).'<br />'._NL;
	$ret.=imed_model::qt('DSBL.SHOULDHELP.ฝึกวิธีการดูแลคนพิการให้สมาชิกครอบครัว',$qt,$psnInfo->qt,$isEdit).'<br />'._NL;
	$ret.=imed_model::qt('DSBL.SHOULDHELP.การดูแลเรื่องการกินยาจิตเวชให้ต่อเนื่อง',$qt,$psnInfo->qt,$isEdit).'<br />'._NL;
	$ret.='</div>';

	$ret.='<p>';
	$ret.=imed_model::qt('DSBL.SHOULDHELP.ส่งต่อคนพิการเข้ารับการตรวจร่างกายหรือฟื้นฟูจากแพทย์เฉพาะทาง',$qt,$psnInfo->qt,$isEdit).'<br />'._NL;
	$ret.=imed_model::qt('DSBL.SHOULDHELP.อื่นๆ',$qt,$psnInfo->qt,$isEdit)._NL;
	$ret.=imed_model::qt('DSBL.SHOULDHELP.อื่นๆ.ระบุ',$qt,$psnInfo->qt,$isEdit).'<br />'._NL;
	$ret.='</p>';
	$ret.='<br clear="all" />';
	$ret.='</div>';
	$ret.='<style type="text/css">
	#tabs-help>ul.tabs {width:230px; float:left;margin:0 16px 0 0; border:none;}
	#tabs-help>ul.tabs>li {display: block;border:none;}
	#tabs-help>ul.tabs>li.active {border:none;}
	#tabs-help>ul.tabs>li>a {margin:0;border:none; background:#eee;padding:8px;display:block;}
		#tabs-help>ul.tabs>li.active>a {background:#ccc;border:none;}
	#tabs-help>div, #tabs-help>p {margin:0 0 0 240px;padding:0;border:none;}
	</style>';

	unset($tables->rows);
	$tables->caption='ส่วนที่ 4 ข้อมูลผู้ดูแลและได้รับการช่วยเหลือ';

	$tables->rows[]=array(
		'ผู้ดูแลประจำคนพิการ',
		imed_model::qt('DSBL.HELPER.WHO',$qt,$psnInfo->qt,$isEdit)
		.imed_model::qt('DSBL.HELPER.MEMBER',$qt,$psnInfo->qt,$isEdit)
		.imed_model::qt('DSBL.HELPER.OTHER',$qt,$psnInfo->qt,$isEdit)
	);

	/*
	//ความเป็นอยู่ที่พบ
	$key='OTHR.5.8.1';
	$items[$key]=array(	'ช่วยเหลือตัวเองในกิจวัตรประจำวันได้',
		'ช่วยเหลือตัวเองในกิจวัตรประจำวันได้บ้างโดยมีคนช่วยพยุง',
		array('ช่วยเหลือตัวเองไม่ได้',' ผู้ดูแลประจำชื่อ '.view::show_field(array('group'=>'qt','fld'=>$key.'.1','tr'=>$psnInfo->qt[$key.'.1']['qid'],'cssclass'=>'inlineedit-block','button'=>$button),$psnInfo->qt[$key.'.1']['value'],$isEdit))
	);
	$str='การช่วยเหลือตัวเอง<br />'.imed_model::make_box('radio',$key,$items[$key],$psnInfo,$isEdit);

	$key='OTHR.5.8.1.2';
	$items[$key]=array(	'สมาชิกครอบครัว',
		'อพมก./อพม./อสม./อผส.',
		'จ้างคนนอกครอบครัว',
	);
	$str.='ความสัมพันธ์ของผู้ดูแลประจำกับคนพิการเป็น<br />'.imed_model::make_box('radio',$key,$items[$key],$psnInfo,$isEdit);

	$tables->rows[]=array('<p><strong>5.8 ความเป็นอยู่ที่พบ</strong></p>',$str);
	*/

	$ret .= $tables->build();

	$tables2 = new Table();
	$tables2->id = 'imed-patient-carer';
	$tables2->caption = 'ผู้ดูแลคนพิการ';


	$tables2->thead = array(
		'no'=>'',
		'ประเภท',
		'ชื่อ-สกุล',
		'วันที่เริ่ม',
		'สถานะ',
		'tool -hover-parent' => $isEdit ? '<a class="sg-action" href="'.url('imed/patient/'.$psnId.'/info/carer.add').'" data-rel="none" data-done="load->replace:#imed-care-disabled"><i class="icon -material">add_circle_outline</i></a>' : '',
		''
	);

	$no = 0;

	foreach ($psnInfo->carer as $item) {
		$tables2->rows[]=array(
			++$no,
			'<!--ประเภท-->'.view::inlineedit(array('group'=>'carer','fld'=>'cat_id','tr'=>$item->tr_id),$item->cat_id_name,$isEdit,'select',imed_model::get_category('carer')),
			'<!--ชื่อ สกุล-->'.view::inlineedit(array('group'=>'carer','fld'=>'detail1','tr'=>$item->tr_id),$item->detail1,$isEdit,'text'),
			'<!--วันที่เริ่ม-->'.view::inlineedit(array('group'=>'carer','fld'=>'created','tr'=>$item->tr_id,'class'=>'w-2','ret'=>'date:ว ดด ปปปป'),sg_date($item->created,'d/m/Y'),$isEdit,'datepicker'),
			'<!--สถานะ-->'.view::inlineedit(array('group'=>'carer','fld'=>'status','tr'=>$item->tr_id),$item->status_name,$isEdit,'select',imed_model::get_category('carerstate')),
			($isEdit ? '<nav class="nav -icons -hover"><a class="sg-action" href="'.url('imed/patient/'.$psnId.'/info/carer.remove/'.$item->tr_id).'" data-rel="notify" data-done="remove:tr.carer-'.$item->tr_id.'" data-title="ลบผู้ดูแล" data-confirm="ต้องการลบรายการผู้ดูแล รายการนี้จริงหรือ กรุณายืนยัน?"><i class="icon -material -gray">cancel</i></a></nav>' : ''),
			'<a href="javascript:void(0)" title="post by '.$item->poster.'">?</a>',
			'config' => array('class'=>'carer-'.$item->tr_id)
		);

		$tables2->rows[]=array(
			'<td></td>',
			'<td colspan="6">'
			.'อายุ (ปี) '.view::inlineedit(array('group'=>'carer', 'fld'=>'ref_id1', 'tr'=>$item->tr_id, 'class'=>'w-1'), $item->ref_id1, $isEdit, 'text').'<br />'
			.'ความสัมพันธ์ '.view::inlineedit(array('group'=>'carer', 'fld'=>'detail2', 'tr'=>$item->tr_id, 'class'=>'w-6'), $item->detail2, $isEdit, 'text').'<br />'
			.'การศึกษา '.view::inlineedit(array('group'=>'carer','fld'=>'detail3','tr'=>$item->tr_id,'class'=>'w-6'),$item->detail3,$isEdit,'text').'<br />'
			.'โทรศัพท์ '.view::inlineedit(array('group'=>'carer','fld'=>'remark','tr'=>$item->tr_id,'class'=>'w-6'),$item->remark,$isEdit,'text').'<br />',
			'config' => array('class'=>'carer-'.$item->tr_id)
		);
	}
	$ret .= $tables2->build();
	$ret.='<p><ul><li>กรณีไม่มีผู้ดูแลหลัก ใส่ชื่อผู้ใหญ่บ้าน / อสม.ที่ดูแล</li><li>กรณีมีผู้ช่วยดูแลคนพิการ บุคคลที่ผ่านการอบรมและรับรองจาก พม./อสม./เจ้าหน้าที่สาธารณสุข ฯลฯ</li></ul></p>';

	$tables2 = new Table();
	$tables2->colgroup=array('width="40%"','width="20%"','width="30%"');
	$tables2->thead=array('คุณสมบัติของผู้ดูแลคนพิการ','ไม่เคย/เคย','จากใครหรือที่ใด (ระบุ)');
	$tables2->rows[]=array(
		'ได้รับการอบรมเรื่องการดูแลคนพิการ',
		imed_model::qt('OTHR.5.7',$qt,$psnInfo->qt,$isEdit),
		imed_model::qt('DSBL.HELPER.QUALITY.TRAINING.BY',$qt,$psnInfo->qt,$isEdit)
	);

	$tables2->rows[]=array(
		'ได้รับข้อมูลข่าวสารหรือการช่วยเหลือที่เป็นประโยชน์ต่อการดูแลคนพิการ',
		imed_model::qt('DSBL.3.5',$qt,$psnInfo->qt,$isEdit),
		imed_model::qt('DSBL.3.5.1',$qt,$psnInfo->qt,$isEdit)
	);
	$ret .= $tables2->build();
/*
	//ได้รับข้อมูลข่าวสาร
	$tables->rows[]=array('ได้รับข้อมูลข่าวสารที่เป็นประโยชน์ต่อคนพิการ',
		'<input type="radio" name="DSBL.3.5" '.($isEdit?'':'disabled="disabled" ').'group="qt" fld="DSBL.3.5" tr="'.$psnInfo->qt['DSBL.3.5']['qid'].'" value="ไม่เคย" '.($psnInfo->qt['DSBL.3.5']['value']=='ไม่เคย' ? ' checked="checked"':'').' /> ไม่เคย '
		.'<input type="radio" name="DSBL.3.5" '.($isEdit?'':'disabled="disabled" ').'group="qt" fld="DSBL.3.5" tr="'.$psnInfo->qt['DSBL.3.5']['qid'].'" value="เคย" '.($psnInfo->qt['DSBL.3.5']['value']=='เคย' ? ' checked="checked"':'').' /> เคย ผ่านทางไดบ้าง ระบุ '.view::show_field(array('group'=>'qt','fld'=>'DSBL.3.5.1','tr'=>$psnInfo->qt['DSBL.3.5.1']['qid'],'cssclass'=>'inlineedit-block','button'=>$button),$psnInfo->qt['DSBL.3.5.1']['value'],$isEdit)
		);
*/
	unset($tables->rows,$tables->caption);

	$tables->rows[]=array('ปัญหาของผู้ดูแล ',
												imed_model::qt('OTHR.5.7.1',$qt,$psnInfo->qt,$isEdit)
												);
	$tables->rows[]=array('ความต้องการของผู้ดูแล ',
												imed_model::qt('DSBL.HELPER.WANT',$qt,$psnInfo->qt,$isEdit)
												);
	$ret .= $tables->build();

	unset($tables->rows);
	$tables->caption='ส่วนที่ 5 ข้อมูลด้านเศรษฐกิจ  และสวัสดิการอื่น ๆ';

	$tables->rows[]=array(
		'ประกอบอาชีพ',
		imed_model::qt('occupa',$qt,$psnInfo->info->occu_desc,$isEdit)
		.imed_model::qt('ECON.4.4.11.1',$qt,$psnInfo->qt,$isEdit)
	);
	$tables->rows[]=array(
		'ความสามารถพิเศษของคนพิการหรือผู้ดูแล',
		imed_model::qt('interest',$qt,$psnInfo->info->interest,$isEdit)
	);

	$tables->rows[]=array(
		'เคยฝึกอาชีพหรือไม่',
		imed_model::qt('ECON.4.1',$qt,$psnInfo->qt,$isEdit)
		.imed_model::qt('ECON.4.1.1',$qt,$psnInfo->qt,$isEdit)
	);
	$tables->rows[]=array(
		'ต้องการฝึกอาชีพหรือไม่',
		imed_model::qt('ECON.4.2',$qt,$psnInfo->qt,$isEdit)
		.imed_model::qt('ECON.4.2.1',$qt,$psnInfo->qt,$isEdit)
	);
	$tables->rows[]=array(
		'รายได้ของคนพิการ',
		imed_model::qt('ECON.4.5',$qt,$psnInfo->qt,$isEdit)
		.'<p>หมายเหตุ : รายละเอียดที่เกี่ยวกับรายได้ เช่น ลูก หลานส่งเงินให้ใช้สม่ำเสมอ แต่ไม่นับเบี้ยความพิการ/เบี้ยผู้สูงอายุ /หรือมีมากกว่า 1 อาชีพ</p>'
	);
	$tables->rows[]=array(
		'รายได้ของครอบครัวโดยเฉลี่ยต่อเดือน',
		imed_model::qt('ECON.4.6',$qt,$psnInfo->qt,$isEdit).' บาท<br />'
		.imed_model::qt('ECON.INCOME.ENOUGH',$qt,$psnInfo->qt,$isEdit)
	);
	$tables->rows[]=array(
		'ใช้จ่ายไปในเรื่องอะไรบ้าง',
		imed_model::qt('ECON.EXPENSE.ABOUT.MEDICAL',$qt,$psnInfo->qt,$isEdit).'<br />'
		.imed_model::qt('ECON.EXPENSE.ABOUT.PRIVATE',$qt,$psnInfo->qt,$isEdit).'<br />'
		.imed_model::qt('ECON.EXPENSE.ABOUT.FOOD',$qt,$psnInfo->qt,$isEdit).'<br />'
		.imed_model::qt('ECON.EXPENSE.ABOUT.COMMODITY',$qt,$psnInfo->qt,$isEdit).'<br />'
		.imed_model::qt('ECON.EXPENSE.ABOUT.OTHER',$qt,$psnInfo->qt,$isEdit).'<br />'
		.imed_model::qt('ECON.4.8',$qt,$psnInfo->qt,$isEdit).'<br />'
	);
	//ภาระหนี้สิน
	$tables->rows[]=array(
		'คนพิการและครอบครัวมีภาระหนี้สินหรือไม่',
		imed_model::qt('ECON.4.7',$qt,$psnInfo->qt,$isEdit)
		.imed_model::qt('ECON.4.7.1',$qt,$psnInfo->qt,$isEdit).'<br />'
		.imed_model::qt('ECON.4.7.2',$qt,$psnInfo->qt,$isEdit).'<br />'
	);
	$ret .= $tables->build();

	$tables = new Table();
	//ได้รับความช่วยเหลือ
	$tables->rows[]=array(
		'<p><strong>5.9 บริการ/สวัสดิการที่เคยได้รับหรือสวัสดิการที่รับอยู่ในปัจจุบัน รวมทั้งเคยได้รับความช่วยเหลือใดบ้าง</strong></p>'
		.imed_model::qt('OTHR.5.1.1',$qt,$psnInfo->qt,$isEdit).'<br />'
		.imed_model::qt('OTHR.SERVICE.NEEDEQUITY',$qt,$psnInfo->qt,$isEdit)
		.imed_model::qt('OTHR.SERVICE.NEEDEQUITY.IDENTIFY',$qt,$psnInfo->qt,$isEdit).'<br />'
		.imed_model::qt('OTHR.5.1.2',$qt,$psnInfo->qt,$isEdit).'<br />'
		.imed_model::qt('OTHR.5.1.3',$qt,$psnInfo->qt,$isEdit).'<br />'
		.imed_model::qt('OTHR.5.1.4',$qt,$psnInfo->qt,$isEdit).'<br />'
		.imed_model::qt('OTHR.5.1.5',$qt,$psnInfo->qt,$isEdit).'<br />'
		.imed_model::qt('OTHR.5.1.6',$qt,$psnInfo->qt,$isEdit)
			.imed_model::qt('OTHR.5.1.6.1',$qt,$psnInfo->qt,$isEdit).'<br />'
		.imed_model::qt('OTHR.5.1.7',$qt,$psnInfo->qt,$isEdit)
			.imed_model::qt('OTHR.5.1.7.1',$qt,$psnInfo->qt,$isEdit)
			.imed_model::qt('OTHR.SERVICE.LOAN.AMOUNT',$qt,$psnInfo->qt,$isEdit).'<br />'
		.imed_model::qt('OTHR.5.1.8',$qt,$psnInfo->qt,$isEdit).'<br />'
		.imed_model::qt('OTHR.5.1.9',$qt,$psnInfo->qt,$isEdit).'<br />'
		.imed_model::qt('OTHR.SERVICE.MONEY.BY',$qt,$psnInfo->qt,$isEdit).'<br />'
		.imed_model::qt('OTHR.SERVICE.MONEY.FREQUENTLY',$qt,$psnInfo->qt,$isEdit).'<br />'
		.imed_model::qt('OTHR.5.1.10',$qt,$psnInfo->qt,$isEdit).'<br />'
		.imed_model::qt('OTHR.5.1.11',$qt,$psnInfo->qt,$isEdit).'<br />'
	);
	$tables->rows[]=array(
		'<p><strong>ท่านมีปัญหาและความต้องการในด้านบริการ/สวัสดิการ</strong></p>'
		.'ปัญหา<br />'.imed_model::qt('OTHR.SERVICE.PROBLEM',$qt,$psnInfo->qt,$isEdit).'<br />'
		.'ความต้องการ<br />'.imed_model::qt('OTHR.SERVICE.WANT',$qt,$psnInfo->qt,$isEdit)
	);

	$tables->rows[]=array(
		'<p><strong>5.10 การเข้าร่วมกิจกรรมทางสังคม</strong></p>'
		.imed_model::qt('OTHR.5.3.3',$qt,$psnInfo->qt,$isEdit).'<br />'
		.imed_model::qt('OTHR.5.3.2',$qt,$psnInfo->qt,$isEdit).'<br />'
		.imed_model::qt('OTHR.5.3.7',$qt,$psnInfo->qt,$isEdit).'<br />'
		.imed_model::qt('OTHR.5.3.8',$qt,$psnInfo->qt,$isEdit).':<br />&nbsp;&nbsp;&nbsp;'
			.imed_model::qt('OTHR.5.3.8.IDENTIFY',$qt,$psnInfo->qt,$isEdit).'<br />'
		.imed_model::qt('OTHR.5.3.6',$qt,$psnInfo->qt,$isEdit).'<br />'
		.imed_model::qt('OTHR.5.3.4',$qt,$psnInfo->qt,$isEdit).':<br />&nbsp;&nbsp;&nbsp;'
			.imed_model::qt('OTHR.5.3.4.IDENTIFY',$qt,$psnInfo->qt,$isEdit).' '
			.imed_model::qt('OTHR.5.3.4.MEMBERID',$qt,$psnInfo->qt,$isEdit).'<br />'
		.imed_model::qt('OTHR.5.3.9',$qt,$psnInfo->qt,$isEdit).':<br />&nbsp;&nbsp;&nbsp;'
			.imed_model::qt('OTHR.5.3.9.IDENTIFY',$qt,$psnInfo->qt,$isEdit).'<br />'
		.imed_model::qt('OTHR.5.3.10',$qt,$psnInfo->qt,$isEdit).'<br />'
		.imed_model::qt('OTHR.5.3.11',$qt,$psnInfo->qt,$isEdit).'<br />'
		.imed_model::qt('OTHR.5.3.12',$qt,$psnInfo->qt,$isEdit).'<br />'
		.imed_model::qt('OTHR.5.3.99',$qt,$psnInfo->qt,$isEdit)
			.imed_model::qt('OTHR.5.3.99.IDENTIFY',$qt,$psnInfo->qt,$isEdit).'<br />'
		.'<p><strong>ท่านมีปัญหาและความต้องการในด้านกิจกรรมทางสังคมในด้านใด</strong></p>'
		.imed_model::qt('OTHR.SOCIAL.PROBLEM',$qt,$psnInfo->qt,$isEdit)
		.imed_model::qt('OTHR.SOCIAL.WANT',$qt,$psnInfo->qt,$isEdit)
	);

	$tables->rows[]=array(
		'<p><strong>5.11 การใช้ประโยชน์จากสิ่งอำนวยความสะดวกสาธารณะ</strong></p>'
		.imed_model::qt('OTHR.5.4.3',$qt,$psnInfo->qt,$isEdit).'<br />'
		.imed_model::qt('OTHR.5.4.4',$qt,$psnInfo->qt,$isEdit).'<br />'
		.imed_model::qt('OTHR.5.4.1',$qt,$psnInfo->qt,$isEdit).'<br />'
		.imed_model::qt('OTHR.5.4.2',$qt,$psnInfo->qt,$isEdit).'<br />'
		.imed_model::qt('OTHR.6.5.1',$qt,$psnInfo->qt,$isEdit).'<br />'
		.imed_model::qt('OTHR.6.5.2',$qt,$psnInfo->qt,$isEdit).'<br />'
		.imed_model::qt('OTHR.6.5.3',$qt,$psnInfo->qt,$isEdit).'<br />'
		.'<p><strong>ท่านมีปัญหาและความต้องการในด้านสิ่งอำนวยความสะดวก   การเข้าถึงและใช้ประโยชน์บริการสาธารณะ ในด้านใด</strong></p>'
		.imed_model::qt('OTHR.PUBLICSERVICE.PROBLEM',$qt,$psnInfo->qt,$isEdit)
		.imed_model::qt('OTHR.PUBLICSERVICE.WANT',$qt,$psnInfo->qt,$isEdit)
	);

	$tables->rows[]=array(
		'<p><strong>5.12 การใช้ประโยชน์จากสิ่งอำนวยความสะดวกสาธารณะ</strong></p>'
		.imed_model::qt('OTHR.6.4.1',$qt,$psnInfo->qt,$isEdit).'<br />'
		.imed_model::qt('OTHR.6.4.2',$qt,$psnInfo->qt,$isEdit).'<br />'
		.imed_model::qt('OTHR.6.4.3',$qt,$psnInfo->qt,$isEdit).'<br />'
		.imed_model::qt('OTHR.6.4.4',$qt,$psnInfo->qt,$isEdit).'<br />'
		.imed_model::qt('OTHR.6.4.5',$qt,$psnInfo->qt,$isEdit).'<br />'
		.imed_model::qt('OTHR.6.4.6',$qt,$psnInfo->qt,$isEdit).'<br />'
		.imed_model::qt('OTHR.6.4.7',$qt,$psnInfo->qt,$isEdit).'<br />'
		.imed_model::qt('OTHR.6.4.8',$qt,$psnInfo->qt,$isEdit).'<br />'
		.imed_model::qt('OTHR.6.4.9',$qt,$psnInfo->qt,$isEdit)
			.imed_model::qt('OTHR.6.4.9.1',$qt,$psnInfo->qt,$isEdit).'<br />'
		.'<p><strong>ท่านมีปัญหาและความต้องการในด้านสวัสดิการจากชุมชน/สังคม</strong></p>'
		.imed_model::qt('OTHR.WELFARECOMMUNITY.PROBLEM',$qt,$psnInfo->qt,$isEdit)
		.imed_model::qt('OTHR.WELFARECOMMUNITY.WANT',$qt,$psnInfo->qt,$isEdit)
	);

	$tables->rows[]=array(
		'<p><strong>ผู้เก็บข้อมูลมีความเห็นว่าคนพิการควรได้รับการช่วยเหลือเพิ่มเติม ดังนี้</strong></p>'
		.imed_model::qt('OTHR.6.2.1',$qt,$psnInfo->qt,$isEdit).'<br />'
		.imed_model::qt('OTHR.6.2.2',$qt,$psnInfo->qt,$isEdit).'<br />'
		.imed_model::qt('OTHR.6.2.4',$qt,$psnInfo->qt,$isEdit).'<br />'
		.imed_model::qt('OTHR.6.2.5',$qt,$psnInfo->qt,$isEdit)
			.imed_model::qt('OTHR.6.2.5.1',$qt,$psnInfo->qt,$isEdit).'<br />'
	);

	$tables->rows[]=array(
		'<p><strong>ข้อคิดเห็นอื่น ๆ จากการสังเกตและการประเมินของผู้เก็บข้อมูล</strong></p>'
		.imed_model::qt('OTHR.6.6.1',$qt,$psnInfo->qt,$isEdit).'<br />'
		.imed_model::qt('OTHR.6.6.2',$qt,$psnInfo->qt,$isEdit).'<br />'
		.imed_model::qt('OTHR.6.6.3',$qt,$psnInfo->qt,$isEdit).'<br />'
	);

	$ret .= $tables->build();


	/*
	// ข้อมูลชุดเก่า
	$ret.='<hr /><h2>ข้อมูลชุดเก่า</h2>';

	$tables = new Table();
	$tables->id='imed-patient-info';


	//สภาพบ้าน
	$key='OTHR.5.6';
	$items[$key]=array(
		'บ้านชั้นเดียว ติดพื้น มั่นคง แข็งแรง',
		'บ้านชั้นเดียว ไม่มั่นคงแข็งแรง',
		'บ้านชั้นเดียว ยกพื้นใต้ถุนสูง มั่นคงแข็งแรง',
		'บ้านชั้นเดียว ยกพื้นใต้ถุนสูง ไม่มั่นคงแข็งแรง',
		'บ้านสองขั้น มั่นคงแข็งแรง',
		'บ้านสองชั้น ไม่มั่นคงแข็งแรง',
		'บ้านตึกสูงกว่า 2 ชั้น',
		'ตึกแถว ห้องแถว',
		array('อื่น ๆ',' ระบุ '.view::show_field(array('group'=>'qt','fld'=>$key.'.1','tr'=>$psnInfo->qt[$key.'.1']['qid'],'cssclass'=>'inlineedit-block','button'=>$button),$psnInfo->qt[$key.'.1']['value'],$isEdit))
	);
	unset($str,$i);
	foreach ($items[$key] as $v) {
		$k=$key;
		unset($posttext);
		if (is_array($v)) {
			$posttext=$v[1];
			$v=$v[0];
		}
		$str.='<input type="radio" name="'.$k.'" '.($isEdit?'':'disabled="disabled" ').'group="qt" fld="'.$k.'" tr="'.$psnInfo->qt[$k]['qid'].'" value="'.$v.'" '.($psnInfo->qt[$k]['value']==$v ? ' checked="checked"':'').' /> '.$v.$posttext.'<br />'._NL;
	}
	$tables->rows[]=array('<p><strong>5.6 สภาพบ้าน</strong></p>'.$str);

	$ret .= $tables->build();

	$tables = new Table();
	$tables->id='imed-patient-info';
	$tables->caption='ส่วนที่ 6 ความเห็นของผู้สัมภาษณ์ เจ้าหน้าที่ดูแลคนพิการ ที่ผู้พิการพึงได้รับ';

	//การฟื้นฟูสมรรถภาพ
	$key='OTHR.6.1.3';
	$items[$key]=array('รถเข็น','ไม้ค้ำยัน','ไม้เท้า','อุปกรณ์เทียม เช่น ขา/แขน/เข่า/มือ',array('อื่น ๆ',' ระบุ'.view::show_field(array('group'=>'qt','fld'=>$key.'.5.1','tr'=>$psnInfo->qt[$key.'.5.1']['qid'],'cssclass'=>'inlineedit-block','button'=>$button),$psnInfo->qt[$key.'.5.1']['value'],$isEdit)),);
	$key='OTHR.6.1';
	$items[$key]=array(
		'การดูแลสุขอนามัย เช่น แผลกดทับ เช็ดตัวทำความสะอาดร่างกาย อาหารเสริม หรืออื่น ๆ',
		'ช่วยฝึกกายภาพบำบัดหรือฝึกให้ทำด้วยตนเอง',
		array('อุปกรณ์เครื่องช่วยคนพิการที่ต้องใช้',' <br />&nbsp;&nbsp;'.str_replace('<br />','',imed_model::make_box('checkbox','OTHR.6.1.3',$items['OTHR.6.1.3'],$psnInfo,$isEdit))),
		array('ฝึกการช่วยเหลือตัวเองในกิจวัตรประจำวัน',' เรื่อง ระบุ'.view::show_field(array('group'=>'qt','fld'=>$key.'.4.1','tr'=>$psnInfo->qt[$key.'.4.1']['qid'],'cssclass'=>'inlineedit-block','button'=>$button),$psnInfo->qt[$key.'.4.1']['value'],$isEdit)),
		'ฝึกทักษะการพูด',
		'ฝึกการใช้ภาษามือ',
		'ฝึกทักษะ O&M (การใช้ไม้เท้าขาวของคนตาบอด)',
		'การดูแลเรื่องการกินยาจิตเวชให้ต่อเนื่อง',
		'ฝึกวิธีการดูแลคนพิการให้สมาชิกครอบครัว',
		array('อื่น ๆ',' ระบุ'.view::show_field(array('group'=>'qt','fld'=>$key.'.10.1','tr'=>$psnInfo->qt[$key.'.10.1']['qid'],'cssclass'=>'inlineedit-block','button'=>$button),$psnInfo->qt[$key.'.10.1']['value'],$isEdit)),
	);

	$tables->rows[]=array('<p><strong>6.1 การฟื้นฟูสมรรถภาพ</strong></p>'.imed_model::make_box('checkbox',$key,$items[$key],$psnInfo,$isEdit));


	// การส่งเสริมการมีส่วนร่วม
	$key='OTHR.6.3';
	$items[$key]=array(
		'การรวมกลุ่มคนพิการ',
		array('การเข้าร่วมเป็นคณะกรรมการต่าง ๆ ในชุมชน/สังคม',' ระบุ'.view::show_field(array('group'=>'qt','fld'=>$key.'.2.1','tr'=>$psnInfo->qt[$key.'.2.1']['qid'],'cssclass'=>'inlineedit-block','button'=>$button),$psnInfo->qt[$key.'.2.1']['value'],$isEdit)),
	);
	$tables->rows[]=array('<p><strong>6.3 การส่งเสริมการมีส่วนร่วม</strong></p>'.imed_model::make_box('checkbox',$key,$items[$key],$psnInfo,$isEdit));

	$ret .= $tables->build();
	*/

	$ret.='<p><small>สร้างโดย '.$psnInfo->disabled->created_by.' เมื่อ '.sg_date($psnInfo->disabled->created,'ว ดด ปปปป H:i').($psnInfo->disabled->modify?' แก้ไขล่าสุดโดย '.$psnInfo->disabled->modify_by.' เมื่อ '.sg_date($psnInfo->disabled->modify,'ว ดด ปปปป H:i'):'').'</small></p>';

	//$ret.=print_o($psnInfo,'$psnInfo');

	$ret .= '<script type="text/javascript">
	function reloadPage($this, data) {
		let id = "#imed-care-disabled"
		$.get($(id).data("url"), function(html) {
			$(id).replaceWith(html)
		})
	}
	</script>';

	$ret .= '</section>'._NL;
	return $ret;
}
?>