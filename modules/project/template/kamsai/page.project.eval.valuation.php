<?php
/**
* Project :: Valuation Eval
* Created 2021-12-14
* Modify  2021-12-14
*
* @param Object $projectInfo
* @return Widget
*
* @usage project/{id}/eval.valuation
*/

import('widget:project.info.appbar.php');

class ProjectEvalValuation extends Page {
	var $projectId;
	var $action;
	var $right;
	var $projectInfo;

	function __construct($projectInfo = NULL, $action = NULL) {
		$this->projectId = $projectInfo->projectId;
		$this->action = $action;
		$this->projectInfo = $projectInfo;
		$this->right = (Object) [
			'viewOnly' => $action == 'view',
			'editable' => $projectInfo->info->isRight,
			'editMode' => false,
		];
	}

	function build() {
		$projectInfo = $this->projectInfo;
		$projectId = $this->projectId;

		$formid='valuation';
		$info=project_model::get_tr($projectId,'info');
		$mainact=project_model::get_main_activity($projectId, 'owner');
		$valuationTr=project_model::get_tr($projectId,$formid);
		$url=q();

		$isAdmin=$projectInfo->info->isAdmin;

		$titleRs=end($valuationTr->items['title']);

		$locked=$titleRs->flag;

		if (post('lock') && $isAdmin && $titleRs->trid) {
			$locked=$titleRs->flag==_PROJECT_LOCKREPORT?NULL:_PROJECT_LOCKREPORT;
			$stmt='UPDATE %project_tr% SET `flag`=:flag WHERE `trid`=:trid LIMIT 1';
			mydb::query($stmt,':trid',$titleRs->trid,':flag',$locked);
			location($url);
		}

		$isEdit=$projectInfo->info->isEdit && !$locked;

		$weightSchool=R::model('project.weight.get',$projectId);
		$heightSchool=R::model('project.height.get',$projectId);

		$chartYear=new Table('item -center');

		$no=0;
		foreach ($weightSchool as $rs) {
			$xAxis=($rs->year+543).' '.$rs->term.'/'.$rs->period;
			$percentThin=$rs->thin*100/$rs->getweight;
			$percentRatherThin=($rs->thin+$rs->ratherthin)*100/$rs->getweight;
			$percentFat=$rs->fat*100/$rs->getweight;
			$percentGettingFat=($rs->gettingfat+$rs->fat)*100/$rs->getweight;

			$chartYear->thead['title']='ภาวะ';
			$chartYear->thead[$xAxis]=$xAxis;
			$chartYear->thead[$xAxis.':role']='';

			$chartYear->rows['เตี้ย']['string:0']='เตี้ย';
			$chartYear->rows['เตี้ย+ค่อนข้างเตี้ย']['string:0']='เตี้ย+ค่อนข้างเตี้ย';
			$chartYear->rows['ผอม']['string:0']='ผอม';
			$chartYear->rows['ผอม+ค่อนข้างผอม']['string:0']='ผอม+ค่อนข้างผอม';
			$chartYear->rows['อ้วน']['string:0']='อ้วน';
			$chartYear->rows['เริ่มอ้วน+อ้วน']['string:0']='เริ่มอ้วน+อ้วน';
			$chartYear->rows['เตี้ย']['number:'.$xAxis]=0;
			$chartYear->rows['เตี้ย']['string:'.$xAxis.':role']='0%';
			$chartYear->rows['เตี้ย+ค่อนข้างเตี้ย']['number:'.$xAxis]=0;
			$chartYear->rows['เตี้ย+ค่อนข้างเตี้ย']['string:'.$xAxis.':role']='0%';
			$chartYear->rows['ผอม']['number:'.$xAxis]=number_format($percentThin,2);
			$chartYear->rows['ผอม']['string:'.$xAxis.':role']=number_format($percentThin,2).'%';
			$chartYear->rows['ผอม+ค่อนข้างผอม']['number:'.$xAxis]=number_format($percentRatherThin,2);
			$chartYear->rows['ผอม+ค่อนข้างผอม']['string:'.$xAxis.':role']=number_format($percentRatherThin,2).'%';
			$chartYear->rows['อ้วน']['number:'.$xAxis]=number_format($percentFat,2);
			$chartYear->rows['อ้วน']['string:'.$xAxis.':role']=number_format($percentFat,2).'%';
			$chartYear->rows['เริ่มอ้วน+อ้วน']['number:'.$xAxis]=number_format($percentGettingFat,2).'%';
			$chartYear->rows['เริ่มอ้วน+อ้วน']['string:'.$xAxis.':role']=number_format($percentGettingFat,2).'%';
		}

		foreach ($heightSchool as $rs) {
			$xAxis=($rs->year+543).' '.$rs->term.'/'.$rs->period;
			$percentShort=$rs->short*100/$rs->getheight;
			$percentRatherShort=($rs->short+$rs->rathershort)*100/$rs->getheight;

			$chartYear->rows['เตี้ย']['number:'.$xAxis]=number_format($percentShort,2);
			$chartYear->rows['เตี้ย']['string:'.$xAxis.':role']=number_format($percentShort,2).'%';
			$chartYear->rows['เตี้ย+ค่อนข้างเตี้ย']['number:'.$xAxis]=number_format($percentRatherShort,2);
			$chartYear->rows['เตี้ย+ค่อนข้างเตี้ย']['string:'.$xAxis.':role']=number_format($percentRatherShort,2).'%';
		}





		if ($isEdit) {
			$inlineAttr['data-update-url']=url('project/edit/tr');
			if (post('debug')) $inlineAttr['data-debug']='yes';
		}
		$ret.='<div id="project-report-estimation" class="inline-edit project__report" '.sg_implode_attr($inlineAttr).'>'._NL;

		$section='title';
		$irs=end($valuationTr->items[$section]);

		$ret.='<p>ชื่อโครงการ <strong>โครงการศูนย์เรียนรู้ต้นแบบโรงเรียนเด็กไทยแก้มใส '.$projectInfo->title.'</strong></p>'._NL;
		$ret.='<p>รหัสโครงการ <strong>'.$projectInfo->info->prid.'</strong> รหัสสัญญา <strong>'.$projectInfo->info->agrno.'</strong> ระยะเวลาโครงการ <strong>'.sg_date($projectInfo->info->date_from,'ว ดดด ปปปป').' - '.sg_date($projectInfo->info->date_end,'ว ดดด ปปปป').'</strong></p>'._NL;

		$ret.='<p>วันที่เริ่มประเมิน '.view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'detail1','tr'=>$irs->trid,'ret'=>'date:ว ดดด ปปปป', 'value'=>$irs->detail1),$irs->detail1?sg_date($irs->detail1,'ว ดดด ปปปป'):'',$isEdit,'datepicker').'</p>';


		$section='inno';
		$guideList=model::get_category('project:activitygroup','catid');


		$ret.='<h3>1. นวัตกรรมที่เกิดขึ้นเพื่อการแลกเปลี่ยนเรียนรู้</h3>';
		$ret.='<p class="noprint">(ให้เลือกนวัตกรรมที่เกิดขึ้นในกิจกรรมตาม 8 องค์ประกอบ และอธิบายว่านวัตกรรมที่เกิดขึ้นมีลักษณะขั้นตอนอย่างไร)</p>';

		$tables = new Table();
		$tables->addClass('project-valuation-form -inno');
		$tables->colgroup=array('width="20%"','width="5%"','width="5%"','width="25%"','width="25%"','width="25%"');
		$tables->thead='<thead><tr><th rowspan="2">แนวทางการดำเนินงาน (๘ แนวทางหลัก)</th><th colspan="2">ผลที่เกิดขึ้น</th><th rowspan="2">กิจกรรมที่เป็นนวัตกรรมของโรงเรียน</th><th rowspan="2">ลักษณะ/ขั้นตอน/รายละเอียด/หลักฐาน/แหล่งอ้างอิง (บรรยาย)</th><th rowspan="2">แนวทางการพัฒนาต่อ</th></tr><tr><th style="width:30px;">มี</th><th style="width:30px;">ไม่มี</th></tr></thead>';
		foreach ($guideList as $guideId => $guideTitle) {
			$tables->rows[]='<header>';
			$sectionId=$section.'.'.$guideId;
			$irs=end($valuationTr->items[$sectionId]);

			$tables->rows[]=array(
				'<span>'.(++$no).'. '.$guideTitle.'</span>',
				view::inlineedit(array('group'=>$formid.':'.$sectionId,'fld'=>'rate1', 'name'=>'rate'.$sectionId, 'tr'=>$irs->trid, 'value'=>$irs->rate1),'1:',$isEdit,'radio'),
					view::inlineedit(array('group'=>$formid.':'.$sectionId,'fld'=>'rate1', 'name'=>'rate'.$sectionId, 'tr'=>$irs->trid, 'value'=>$irs->rate1),'0:',$isEdit,'radio'),
					view::inlineedit(array('group'=>$formid.':'.$sectionId,'fld'=>'text1','tr'=>$irs->trid,'ret'=>'html', 'value'=>trim($irs->text1)),$irs->text1,$isEdit,'textarea'),
					view::inlineedit(array('group'=>$formid.':'.$sectionId,'fld'=>'text2','tr'=>$irs->trid,'ret'=>'html', 'value'=>trim($irs->text2)),$irs->text2,$isEdit,'textarea'),
					view::inlineedit(array('group'=>$formid.':'.$sectionId,'fld'=>'text3','tr'=>$irs->trid,'ret'=>'html', 'value'=>trim($irs->text3)),$irs->text3,$isEdit,'textarea'),
				);
			$tables->rows[]=array('','config'=>array('class'=>'empty'));
		}

		$ret.=$tables->show();


		$section='title';
		$irs=end($valuationTr->items[$section]);

		$ret.='<h3>2. อะไรคือปัจจัยสำคัญที่ทำให้งานสำเร็จ(นวัตกรรม)</h3>';

		$ret.='<p>1. หน่วยงานภาคีเครือข่าย</p>';
		$ret.=view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'text2','tr'=>$irs->trid,'ret'=>'html', 'value'=>trim($irs->text2)),$irs->text2,$isEdit,'textarea');

		$ret.='<p>2. สภาพแวดล้อมที่เป็นปัจจัยเอื้อ</p>';
		$ret.=view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'text3','tr'=>$irs->trid,'ret'=>'html', 'value'=>trim($irs->text3)),$irs->text3,$isEdit,'textarea');

		$ret.='<p>3. กลไกที่ทำให้เกิดการขับเคลื่อนและทีมทำงาน</p>';
		$ret.=view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'text4','tr'=>$irs->trid,'ret'=>'html', 'value'=>trim($irs->text4)),$irs->text4,$isEdit,'textarea');

		$ret.='<p>4. กระบวนการเรียนรู้ของครู นักเรียนและแม่ครัว</p>';
		$ret.=view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'text5','tr'=>$irs->trid,'ret'=>'html', 'value'=>trim($irs->text5)),$irs->text5,$isEdit,'textarea');

		$ret.='<p>5. กระบวนการมีส่วนร่วมของผู้ปกครองและชุมชน</p>';
		$ret.=view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'text6','tr'=>$irs->trid,'ret'=>'html', 'value'=>trim($irs->text6)),$irs->text6,$isEdit,'textarea');

		$outputList['foodlun']=array(
			'title'=>'3. ผลผลิตทางการเกษตรเพื่ออาหารกลางวัน',
			'items'=>array(
				array('section'=>'1','title'=>'มีชนิดและปริมาณของผักและผลไม้ที่ผลิตในโรงเรียนเพียงพอ'),
				array('section'=>'2','title'=>'มีการเลี้ยงสัตว์เพื่อเป็นอาหารโปรตีน (ไก่ไข่/ไก่เนื้อ/เป็ด/หมู)'),
				array('section'=>'3','title'=>'มีการเลี้ยงสัตว์น้ำเพื่อเป็นอาหารโปรตีน (ปลา/กบ)'),
				array('section'=>'4','title'=>'โรงเรียนมีบริการอาหารเช้าหรือไม่'),
				array('title'=>'5. มีการจัดบริการให้เด็กบริโภคผักและผลไม้ (ต้องได้บริโภคร้อยละ 50 ของปริมาณที่แนะนำต่อวัน)'),
				array('section'=>'5.1','title'=>'เด็กอนุบาล 3-5 ปี ควรได้รับผัก 30 กรัม(2 ข้อนโต๊ะ) ผลไม้ 100 กรัม'),
				array('section'=>'5.2','title'=>'เด็กประถม 6-12 ปี ควรได้รับผักไม่น้อยกว่า 60 กรัม(4 ช้อนโต๊ะ) ผลไม้ 200 กรัม'),
				array('section'=>'5.3','title'=>'เด็กมัธยม 13-18 ปี ควรได้รับผักไม่น้อยกว่า 90 กรัม(6 ช้อนโต๊ะ) ผลไม้ 200 กรัม'),
				array('section'=>'6','title'=>'โรงเรียนมีความร่วมมือกับเครือข่ายเชื่อมโยงแหล่งผลิตอาหารที่ปลอดสารพิษในชุมชน'),
				array('section'=>'99','title'=>'อื่นๆ'),
			)
		);

		$outputList['foodman']=array(
			'title'=>'4. สถานการณ์การจัดบริการอาหารและโภชนาการของนักเรียน',
			'items'=>array(
				array('section'=>'1','title'=>'การใช้โปรแกรม Thai School Lunch ทำได้อย่างสมบูรณ์หรือไม่'),
				array('section'=>'2','title'=>'มีการติดตามภาวะโภชนาการเทอมละ 2 ครั้ง ได้อย่างครบถ้วนหรือไม่'),
				array('title'=>'3. เด็กนักเรียนมีภาวะโภชนาการเป็นไปตามตัวชี้วัดความสำเร็จของโครงการหรือไม่'
					.'<div id="year-fat" class="sg-chart -fat" data-chart-type="col" style="font-weight:normal;height:400px;"><h3>สถานการณ์ภาวะโภชนาการนักเรียน (ปีการศึกษา)</h3>'.$chartYear->show().'</div>'
					),
				array('section'=>'3.1','title'=>'ภาวะเริ่มอ้วนและอ้วนลดลง'),
				array('section'=>'3.2','title'=>'ภาวะค่อนข้างผอมและผอมลดลง'),
				array('section'=>'3.3','title'=>'ภาวะค่อนข้างเตี้ยและเตี้ยลดลง'),
				array('section'=>'4','title'=>'โรงเรียนได้มีการเฝ้าระวังและมีกิจกรรมเสริมสำหรับเด็กที่มีภาวะทุพโภชนาการ (ตามข้อ 3.1-3.3) เป็นรายบุคคลหรือไม่ อย่างไร'),
				array('section'=>'5','title'=>'ผู้ปกครองมีส่วนร่วมในการแก้ไขปัญหาด้วยหรือไม่อย่างไร'),
				array('section'=>'99','title'=>'อื่นๆ'),
			)
		);

		$tables=new table();
		$tables->addClass('project-valuation-form -other');
		$tables->colgroup=array('width="20%"','width="5%"','width="5%"','width="25%"','width="25%"','width="25%"');
		$tables->thead='<thead><tr><th rowspan="2">คุณค่าที่เกิดขึ้น<br />ประเด็น</th><th colspan="2">ผลที่เกิดขึ้น</th><th rowspan="2">รายละเอียด/การจัดการ</th><th rowspan="2">หลักฐาน/แหล่งอ้างอิง</th><th rowspan="2">แนวทางการพัฒนาต่อ</th></tr><tr><th style="width:30px;">ใช่</th><th style="width:30px;">ไม่ใช่</th></tr></thead>';
		foreach ($outputList as $mainKey=>$mainValue) {
			$tables->rows[]=array('<td colspan="6"><h3>'.$mainValue['title'].'</h3></td>');
			foreach ($mainValue['items'] as $k=>$v) {
				if (!empty($v['section'])) $tables->rows[]='<header>';
				if (empty($v['section'])) {
					$tables->rows[]=array('<td colspan="6"><b>'.$v['title'].'</b></td>');
					continue;
				}
				$section=$mainKey.'.'.$v['section'];
				$irs=end($valuationTr->items[$section]);
				unset($row);
				$row[]='<span>'.($v['section']).'. '.$v['title'].'</span>';
				$row[]=view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'rate1', 'name'=>'rate'.$section, 'tr'=>$irs->trid, 'value'=>$irs->rate1),'1:',$isEdit,'radio');
				$row[]=view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'rate1', 'name'=>'rate'.$section, 'tr'=>$irs->trid, 'value'=>$irs->rate1),'0:',$isEdit,'radio');
				$row[]=view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'text1','tr'=>$irs->trid,'ret'=>'html', 'value'=>trim($irs->text1)),$irs->text1,$isEdit,'textarea');
				$row[]=view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'text2','tr'=>$irs->trid, 'ret'=>'html', 'value'=>$irs->text2),$irs->text2,$isEdit,'textarea');
				$row[]=view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'text3','tr'=>$irs->trid, 'ret'=>'html', 'value'=>$irs->text3),$irs->text3,$isEdit,'textarea');
				$tables->rows[]=$row;

				$tables->rows[]=array('','config'=>array('class'=>'empty'));
			}
		}
		$ret .= $tables->show();

		// ดึงค่า default จากรายละเอียดโครงการ
		$preAbstract='โครงการนี้มีวัตถุประสงค์เพื่อ';
		if ($info->items['objective']) {
			$oi=0;
			foreach ($info->items['objective'] as $irs) {
				$preAbstract.=' ('.(++$oi).') '.$irs->text1;
			}
		} else $ret.=$projectInfo->info->objective;
		$preAbstract.=_NL._NL;

		$oi=0;
		$preAbstract.='กิจกรรมหลักคือ';
		foreach ($mainact->info as $mrs) {
			if (empty($mrs->trid)) continue;
			$preAbstract.=' ('.(++$oi).') '.$mrs->title;
		}
		$preAbstract.=_NL._NL;
		$preAbstract.='ผลการดำเนินงานที่สำคัญ ได้แก่';

		$oi=0;
		foreach ($mainact->info as $mrs) {
			foreach ($mainact->activity[$mrs->trid] as $key => $activity) {
				$preAbstract.=' ('.(++$oi).') '.$activity->title;
			}
		}

		$preAbstract.=_NL._NL;
		$preAbstract.='ข้อเสนอแนะ ได้แก่ (1) ...';


		$section='title';
		$irs=end($valuationTr->items[$section]);

		$ret.='<h3>5. สรุปผล (บทคัดย่อ)<sup>*</sup></h3>';
		$ret.=view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'text1','tr'=>$irs->trid,'ret'=>'html','button'=>'yes', 'value'=>trim(SG\getFirst($irs->text2,$preAbstract))),SG\getFirst($irs->text2,$preAbstract),$isEdit,'textarea');










		head('googlegraph','<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>');

		$ret.='<p class="noprint">หมายเหตุ *<ul><li><strong>สรุปผล</strong> จะนำไปใส่ในบทคัดย่อของรายงานสรุปปิดโครงการ (ส.3)</li><li>หากต้องการใช้ค่าเริ่มต้นของสรุปผล ให้ลบข้อความในช่องสรุปผลทั้งหมด แล้วกดปุ่ม Refresh</li></ul></p>';
		$ret.='</div>';

		return new Scaffold([
			'appBar' => new ProjectInfoAppBarWidget($this->projectInfo),
			// 'floatingActionButton' => $floatingActionButton,
			'body' => new Container([
				'children' => [
					new ListTile([
						'class' => 'title -main',
						'title' => '<h2>แบบฟอร์มการสังเคราะห์คุณค่าของโครงการ</h2>',
						'trailing' => new Row([
							'class' => '-no-print',
							'children' => [
								'<a class="btn -link"'.($isAdmin ? ' href="'.url($url, ['lock' => $locked ? 'no' : 'yes']).'" title="คลิกเพื่อเปลี่ยนสถานะรายงาน"' : NULL).'><i class="icon -material">'.($locked ? 'lock' : 'lock_open').'</i></a>',
							], // children
						]), // Row
					]), // ListTile
					$ret,
					$this->script(),
				], // children
			]), // Container
		]);
	}

	function script() {
		return '<style>
		.project-valuation-form td:nth-child(2), .project-valuation-form td:nth-child(3) {text-align:center;}
		.project-valuation-form thead {display:none;}
		.project-valuation-form .header th {font-weight:normal;}
		.project-valuation-form td:first-child span {background:#666; color:#fff; display: block; padding: 8px; border-radius:4px;}
		.project-valuation-form td {border-bottom:none;}
		.project-valuation-form tr.empty td:first-child {background:transparent;}
		</style>

		<script type="text/javascript">
		// Other radio group
		$(".project-valuation-form.-other input.inline-edit-field.-radio, .project-valuation-form.-inno input.inline-edit-field.-radio").each(function() {
			var $radioBtn = $(this).closest("tr").find(".inline-edit-field.-radio:checked")
			var radioValue = $radioBtn.val();
			//console.log("Tr = "+$radioBtn.data("tr")+" - radioValue="+radioValue);
			if (!(radioValue==0 || radioValue==1)) {
				$(this).closest("tr").find("span.inline-edit-field").hide();
			}
		});

		$(".project-valuation-form.-other input[type=\'radio\'], .project-valuation-form.-inno input[type=\'radio\']").change(function() {
			var rate = $(this).val()
			var $inlineInput = $(this).closest("tr").find("td>span>span")
			//console.log("radio change "+$(this).val())
			$inlineInput.show()
		});

		</script>';
	}
}
?>