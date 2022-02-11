<?php
/**
* แบบฟอร์มการเก็บข้อมูลเพื่อการประเมินแบบมีโครงสร้าง
*
* @param Object $self
* @param Object $topic
* @param Object $para
* @param Object $body
* @return String
*/

// @deprecate
// Use : project/{id}/valuation

function project_form_estimation($self,$topic,$para,$body) {
	location('project/'.$topic->tpid.'/eval.valuation');

	$tpid=$topic->tpid;
	$formid='valuation';
	$rs=project_model::get_tr($tpid,$formid);
	$url=q();

	$isAdmin=user_access('administer projects');

	$locked=property('project:'.$formid.'.locked:'.$tpid);
	if ($_REQUEST['lock'] && user_access('administer projects')) $locked=property('project:'.$formid.'.locked:'.$tpid,$_REQUEST['lock']);

	if ($locked=='yes') {
		$is_edit=false;
	} else if (date('Y-m-d')<='2013-05-13') {
		$is_edit=($topic->project->project_statuscode==1) && (user_access('administer projects') || project_model::is_owner_of($tpid) || project_model::is_trainer_of($tpid));
	} else {
		$is_edit=($topic->project->project_statuscode==1) && (user_access('administer projects') || project_model::is_trainer_of($tpid));
	}

	$ui=new ui();
	$ui->add('<a href="'.url($url).'">รายงานแบบประเมิน</a>');
	$ui->add('<a href="'.url($url,$isAdmin ? 'lock='.($locked=='yes'?'no':'yes') : '').'" title="'.($isAdmin?'คลิกเพื่อเปลี่ยนสถานะรายงาน':'').'">สถานะรายงาน : '.($locked=='yes'?'Lock':'UnLock').'</a>');
	$ret.='<div class="reportbar">'.$ui->build('ul').'</div>';

	$section='title';
	$irs=end($rs->items[$section]);

	if ($is_edit) {
		$inlineAttr['data-update-url']=url('project/edit/tr');
		if (post('debug')) $inlineAttr['data-debug']='yes';
	}
	$ret.='<div id="project-report-estimation" class="inline-edit project__report" '.sg_implode_attr($inlineAttr).'>'._NL;

//	$ret.='<div class="inline-edit" id="owner-estimation" url="'.url('project/edit/tr').'">'._NL;
	$ret.='<h3>แบบฟอร์มการเก็บข้อมูลเพื่อการประเมินแบบมีโครงสร้าง</h3>'._NL;

	$ret.='<p>ชื่อโครงการ <strong>'.$topic->title.'</strong></p>'._NL;
	$ret.='<p>รหัสโครงการ <strong>'.$topic->project->prid.'</strong> รหัสสัญญา <strong>'.$topic->project->agrno.'</strong> ระยะเวลาโครงการ <strong>'.sg_date($topic->project->date_from,'ว ดดด ปปปป').' - '.sg_date($topic->project->date_end,'ว ดดด ปปปป').'</strong></p>'._NL;

	// Section Activity
	$ret.='<h4>แบบประเมินคุณค่าของโครงการที่เกี่ยวข้องกับการสร้างเสริมสุขภาพ</h4>'._NL;
	$ret.='<p>เป็นการคุณค่าที่เกิดจากโครงการในมิติต่อไปนี้</p><ul>
<li>ความรู้ด้านการสร้างเสริมสุขภาพและนวัตกรรมเชิงระบบสุขภาพชุมชน</li>
<li>การปรับเปลี่ยนพฤติกรรมที่มีผลต่อสุขภาวะ</li>
<li>การปรับเปลี่ยนสิ่งแวดล้อมที่เอื้อต่อสุขภาวะ</li>
<li>ผลกระทบเชิงบวกและนโยบายสาธารณะที่เอื้อต่อการสร้างสุขภาวะชุมชน</li>
<li>กระบวนการชุมชน</li>
<li>มิติสุขภาวะปัญญา / สุขภาวะทางจิตวิญญาณ</li>
</ul>';

	$outputList = [
		'inno' => [
			'title' => '1. เกิดความรู้ หรือ นวัตกรรมชุมชน',
			'items' => [
					['section' => '1','title' => 'ความรู้ใหม่ / องค์ความรู้ใหม่'],
					['section' => '2','title' => 'สิ่งประดิษฐ์ / ผลผลิตใหม่'],
					['section' => '3','title' => 'กระบวนการใหม่'],
					['section' => '4','title' => 'วิธีการทำงาน / การจัดการใหม่'],
					['section' => '5','title' => 'การเกิดกลุ่ม / โครงสร้างในชุมชนใหม่'],
					['section' => '6','title' => 'แหล่งเรียนรู้ใหม่'],
					['section' => '99','title' => 'อื่นๆ'],
			],
		],
		'behavior' => [
			'title' => '2. เกิดการปรับเปลี่ยนพฤติกรรมที่เอื้อต่อสุขภาพ',
			'items' => [
				['section' => '1','title' => 'การดูแลสุขอนามัยส่วนบุคคล'],
				['section' => '2','title' => 'การบริโภค'],
				['section' => '3','title' => 'การออกกำลังกาย'],
				['section' => '4','title' => 'การลด ละ เลิก อบายมุข เช่น การพนัน เหล้า บุหรี่'],
				['section' => '5','title' => 'การลดพฤติกรรมเสี่ยง เช่น พฤติกรรมเสี่ยงทางเพศ การขับรถโดยประมาท'],
				['section' => '6','title' => 'การจัดการอารมณ์ / ความเครียด'],
				['section' => '7','title' => 'การดำรงชีวิต / วิถีชีวิต เช่น การใช้ภูมิปัญญาท้องถิ่น / สมุนไพรในการดูแลสุขภาพตนเอง'],
				['section' => '8','title' => 'พฤติกรรมการจัดการตนเอง ครอบครัว ชุมชน'],
				['section' => '9','title' => 'อื่นๆ'],
			],
		],
		'environment' => [
			'title' => '3. การสร้างสภาพแวดล้อมที่เอื้อต่อสุขภาพ (กายภาพ สังคม และเศรษฐกิจ)',
			'items' => [
				['section' => '1','title' => 'กายภาพ  เช่น  มีการจัดการขยะ  ป่า  น้ำ  การใช้สารเคมีเกษตร  และการสร้างสิ่งแวดล้อมในครัวเรือนที่ถูกสุขลักษณะ'],
				['section' => '2','title' => 'สังคม เช่น มีความปลอดภัยในชีวิตและทรัพย์สิน ลดการเกิดอุบัติเหตุ ครอบครัวอบอุ่น การจัดสภาพแวดล้อมที่เอื้อต่อเด็ก เยาวชน และกลุ่มวัยต่าง ๆ มีพื้นที่สาธารณะ/พื้นที่ทางสังคม เพื่อเอื้อต่อการส่งเสริมสุขภาพของคนในชุมชน มีการใช้ศาสนา/วัฒนธรรมเป็นฐานการพัฒนา'],
				['section' => '3','title' => 'เศรษฐกิจสร้างสรรค์สังคม /สร้างอาชีพ / เพิ่มรายได้'],
				['section' => '4','title' => 'มีการบริการสุขภาพทางเลือก และมีช่องทางการเข้าถึงระบบบริการสุขภาพ'],
				['section' => '5','title' => 'อื่นๆ'],
			],
		],
		'publicpolicy' => [
			'title' => '4. การพัฒนานโยบายสาธารณะที่เอื้อต่อสุขภาวะ',
			'items' => [
				['section' => '1','title' => 'มีกฎ / กติกา ของกลุ่ม ชุมชน'],
				['section' => '2','title' => 'มีมาตรการทางสังคมของกลุ่ม ชุมชน'],
				['section' => '3','title' => 'มีธรรมนูญของชุมชน'],
				['section' => '4','title' => 'อื่นๆ เช่น ออกเป็นข้อบัญญัติท้องถิ่น ฯลฯ'],
			],
		],
		'social' => [
			'title' => '5. เกิดกระบวนการชุมชน',
			'items' => [
				['section' => '1','title' => 'เกิดการเชื่อมโยงประสานงานระหว่างกลุ่ม / เครือข่าย (ใน และหรือนอกชุมชน)'],
				['section' => '2','title' => 'การเรียนรู้การแก้ปัญหาชุมชน (การประเมินปัญหา การวางแผน การปฏิบัติการ และการประเมิน)'],
				['section' => '3','title' => 'การใช้ประโยชน์จากทุนในชุมชน เช่น การระดมทุน การใช้ทรัพยากรบุคคลในชุมชน'],
				['section' => '4','title' => 'มีการขับเคลื่อนการดำเนินงานของกลุ่มและชุมชนที่เกิดจากโครงการอย่างต่อเนื่อง'],
				['section' => '5','title' => 'เกิดกระบวนการจัดการความรู้ในชุมชน'],
				['section' => '6','title' => 'เกิดทักษะในการจัดการโครงการ เช่น การใช้ข้อมูลในการตัดสินใจ การทำแผนปฏิบัติการ'],
				['section' => '7','title' => 'อื่นๆ'],
			],
		],
		'spirite' => [
			'title' => '6. มิติสุขภาวะปัญญา / สุขภาวะทางจิตวิญญาณ',
			'items' => [
				['section' => '1','title' => 'ความรู้สึกภาคภูมิใจในตัวเอง / กลุ่ม / ชุมชน'],
				['section' => '2','title' => 'การเห็นประโยชน์ส่วนรวมและส่วนตนอย่างสมดุล'],
				['section' => '3','title' => 'การใช้ชีวิตอย่างเรียบง่าย และพอเพียง'],
				['section' => '4','title' => 'ชุมชนมีความเอื้ออาทร'],
				['section' => '5','title' => 'มีการตัดสินใจโดยใช้ฐานปัญญา'],
				['section' => '6','title' => 'อื่นๆ'],
			],
		],
	];

	$tables = new Table();
	$tables->id='project-form-estimation';
	$tables->colgroup=array('width="20%"','width="5%"','width="5%"','width="25%"','width="25%"','width="25%"');
	$tables->thead='<thead><tr><th rowspan="2">คุณค่าที่เกิดขึ้น<br />ประเด็น</th><th colspan="2">ผลที่เกิดขึ้น</th><th rowspan="2">รายละเอียด</th><th rowspan="2">หลักฐาน/แหล่งอ้างอิง</th><th rowspan="2">แนวทางการพัฒนาต่อ</th></tr><tr><th style="width:30px;">มี</th><th style="width:30px;">ไม่มี</th></tr></thead>';
	foreach ($outputList as $mainKey=>$mainValue) {
		$irs=end($rs->items[$mainKey]);
		$tables->rows[]=array('<td colspan="6"><strong>'.$mainValue['title'].'</strong></td>',$irs->rate1==1?'&#10004;':'',$irs->rate1==2?'&#10004;':'',sg_text2html($irs->text1),sg_text2html($irs->text2),sg_text2html($irs->text3));
		foreach ($mainValue['items'] as $k=>$v) {
			$section=$v['section'];
			$irs=end($rs->items[$section]);
			unset($row);
			$row[]=($k+1).'. '.$v['title'];
			if ($mainKey=='5.7') {
				$row[]='';
				$row[]='';
				$row[]='<td colspan="3">'.view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'text1','tr'=>$irs->trid,'ret'=>'html','button'=>'yes', 'value'=>trim($irs->text1)),$irs->text1,$is_edit,'textarea').'</td>';
			} else {
				$row[]=view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'rate1', 'name'=>'rate'.$section, 'tr'=>$irs->trid, 'value'=>$irs->rate1),'1:',$is_edit,'radio');
				//'<input type="radio" name="rate'.$section.'" '.($is_edit?'':'disabled="disabled" ').'data-group="'.$formid.':'.$section.'" data-fld="rate1" data-tr="'.$irs->trid.'" class="inline-edit-field" data-type="radio" value="1" '.(isset($irs->rate1) && $irs->rate1==1 ? ' checked="checked"':'').' />';
				$row[]=view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'rate1', 'name'=>'rate'.$section, 'tr'=>$irs->trid, 'value'=>$irs->rate1),'0:',$is_edit,'radio');
				//'<input type="radio" name="rate'.$section.'" '.($is_edit?'':'disabled="disabled" ').'data-group="'.$formid.':'.$section.'" data-fld="rate1" data-tr="'.$irs->trid.'" class="inline-edit-field" data-type="radio" value="0" '.(isset($irs->rate1) && $irs->rate1==0 ? ' checked="checked"':'').' />';
				$row[]=view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'text1','tr'=>$irs->trid,'ret'=>'html','button'=>'yes', 'value'=>trim($irs->text1)),$irs->text1,$is_edit,'textarea');
				$row[]=view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'text2','tr'=>$irs->trid, 'ret'=>'html','button'=>'yes', 'value'=>$irs->text2),$irs->text2,$is_edit,'textarea');
				$row[]=view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'text3','tr'=>$irs->trid, 'ret'=>'html','button'=>'yes', 'value'=>$irs->text3),$irs->text3,$is_edit,'textarea');
			}
			$tables->rows[]=$row;
		}
	}
	$ret .= $tables->build();
	$ret.='** สรุปภาพรวมโครงการ/รายละเอียด จะนำไปใส่ในบทคัดย่อของรายงาน ส.3 ';
	$ret.='</div>';
$ret.='<style>#project-form-estimation td:nth-child(2),#project-form-estimation td:nth-child(3) {text-align:center;}</style>';
	return $ret;
}
?>