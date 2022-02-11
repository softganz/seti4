<?php
/**
* iMed :: Patient Disabled Information
* Created 2021-06-11
* Modify  2021-06-11
*
* @param Object $patientInfo
* @return Widget
*
* @usage imed/psyc/{id}/info.disabled
*/

$debug = true;

class ImedPsycInfoDisabled {
	var $patientInfo;

	function __construct($patientInfo) {
		$this->patientInfo = $patientInfo;
	}

	function build() {
		$psnInfo = $this->patientInfo;
		$psnId = $psnInfo->psnId;

		if (empty($psnInfo)) return message('error','ไม่มีข้อมูล');

		$isDisabled = $psnInfo->disabled->pid;

		$isAccess=$psnInfo->RIGHT & _IS_ACCESS;
		$isEdit=$psnInfo->RIGHT & _IS_EDITABLE;

		$currentUrl = url('imed/psyc/'.$psnId.'/info.disabled');

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

		// $ret .= '<header class="header"><h3>ข้อมูลคนพิการ'.($psnInfo->info->dischar == 1 ? ' (เสียชีวิต)' : '').'</h3><nav class="nav -page -sg-text-right">'.$ui->build().'</nav></header>';



		$tables = new Table();
		$tables->id='imed-patient-individual';

		$tables->rows[]=array('จดทะเบียนคนพิการ',
			imed_model::qt('register',$qt,$psnInfo->disabled->register,$isEdit).'<br />'
			.imed_model::qt('regdate',$qt,$psnInfo->disabled->regdate,$isEdit).'<br />'
			.imed_model::qt('PSNL.1.9.1.2',$qt,$psnInfo->qt,$isEdit)
			.imed_model::qt('PSNL.1.9.1.3',$qt,$psnInfo->qt,$isEdit).'<br />'
			.imed_model::qt('PSNL.1.9.1.4',$qt,$psnInfo->qt,$isEdit).'<br />'
			.imed_model::qt('DSBL.REGIST.CAUSE',$qt,$psnInfo->qt,$isEdit).'<br />'
		);

		$tables->rows[]=array('ประเภทความพิการ',
			imed_model::qt('DSBL.DEFECT.MENTAL',$qt,$psnInfo->defect[4]->defectid,$isEdit).'<br />'._NL
			.imed_model::qt('DSBL.DEFECT.AUTISTIC',$qt,$psnInfo->defect[7]->defectid,$isEdit).'<br />'._NL
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
					. ($isEdit ? ' <a class="sg-action" href="'.url('imed/patient/'.$psnId.'/info/disabled.defect.remove/'.$defect->defectid).'" data-rel="notify" data-done="load->replace:parent section:'.$currentUrl.'" data-title="ลบความบกพร่อง" data-confirm="ต้องการลบรายการความบกพร่อง รายการนี้จริงหรือ กรุณายืนยันการลบรายการ?"><i class="icon -material -gray">cancel</i></a>':'')
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
		$ret.='</div><!--sg-tabs-->';



		$ret .= '<script type="text/javascript">
		$(".inline-edit-field.-disabled-type").change(function() {
			var id = "#imed-care-disabled"
			$.get($(id).data("url"), function(html) {
				$(id).replaceWith(html)
			})
		})
		</script>';

		$ret .= '</section>'._NL;

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'ข้อมูลคนพิการ - '.$this->patientInfo->fullname,
				'removeOnApp' => true,
			]), // AppBar
			'children' => [
				$ret,
				// R::Page('imed.patient.disabled', NULL, $this->patientInfo),
			], // children
		]);
	}
}
?>