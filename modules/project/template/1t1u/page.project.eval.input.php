<?php
/**
* Project :: แบบประเมินปัจจัยนำเข้า (Input Evaluation)
* Created 2022-02-05
* Modify  2022-02-05
*
* @param Object $projectInfo
* @param String $action
* @return Widget
*
* @usage project/{id}/eval.input[/{action}]
*/

import('widget:project.info.appbar.php');

class ProjectEvalInput extends Page {
	var $projectId;
	var $action;
	var $projectInfo;

	function __construct($projectInfo, $action = NULL) {
		$this->projectId = $projectInfo->projectId;
		$this->projectInfo = $projectInfo;
		$this->action = $action;
	}

	function build() {
		if (!$this->projectId) return new ErrorMessage(['code' => _HTTP_ERROR_BAD_REQUEST, 'text' => 'ไม่มีข้อมูลโครงการที่ระบุ']);

		$projectInfo = $this->projectInfo;

		$formid = 'eval-input';

		$valuationTr = project_model::get_tr($this->projectId,$formid);
		//$finalReportTitle = project_model::get_tr($this->projectId,'finalreport:title');

		$url = q();


		$titleRs = isset($valuationTr->items['title']) ? end($valuationTr->items['title']) : NULL;

		$locked=$titleRs->flag;


		$isViewOnly = $this->action == 'view';
		$isEditable = $projectInfo->info->isRight;
		$isEdit = $projectInfo->info->isRight && $this->action == 'edit' && !$locked;

		$ret.='<h2 class="title -main">แบบติดตามประเมินปัจจัยนำเข้า (Input Evaluation)</h3>'._NL;


		if (post('lock') && $isAdmin && $titleRs->trid) {
			$locked=$titleRs->flag==_PROJECT_LOCKREPORT?NULL:_PROJECT_LOCKREPORT;
			$stmt='UPDATE %project_tr% SET `flag`=:flag WHERE `trid`=:trid LIMIT 1';
			mydb::query($stmt,':trid',$titleRs->trid,':flag',$locked);
			location($url);
		}


		$ui = new Ui();
		$ui->add('<a href="'.url($url).'">รายงานแบบประเมิน</a>');
		$ui->add('<a href="'.url($url,$isAdmin?array('lock'=>$locked?'no':'yes') : NULL).'" title="'.($isAdmin?'คลิกเพื่อเปลี่ยนสถานะรายงาน':'').'">สถานะรายงาน : '.($locked?'Lock':'UnLock').'</a>');
		$ret.='<nav class="nav reportbar">'.$ui->build().'</nav>';



		if ($isEdit) {
			$inlineAttr['class'] = 'sg-inline-edit ';
			$inlineAttr['data-tpid'] = $this->projectId;
			$inlineAttr['data-update-url'] = url('project/edit/tr');
			if (post('debug')) $inlineAttr['data-debug'] = 'yes';
		}
		$inlineAttr['class'] .= 'project-result';

		$ret.='<div id="project-valuation" '.sg_implode_attr($inlineAttr).'>'._NL;


		$section='title';
		$irs = isset($valuationTr->items[$section]) ? end($valuationTr->items[$section]) : NULL;

		$ret.='<p>ชื่อโครงการ <strong>'.$projectInfo->title.'</strong></p>'._NL;
		$ret.='<p>'
				. ($projectInfo->info->prid ? 'รหัสโครงการ <strong>'.$projectInfo->info->prid.'</strong> ' : '')
				. ($projectInfo->info->agrno ? 'รหัสสัญญา <strong>'.$projectInfo->info->agrno.'</strong> ' : '')
				. 'ระยะเวลาโครงการ <strong>'.sg_date($projectInfo->info->date_from,'ว ดดด ปปปป').' - '.sg_date($projectInfo->info->date_end,'ว ดดด ปปปป').'</strong>'
				. '</p>'._NL;


		$outputList['1'] = array(
			'title'=>'1. ชุมชนมีข้อมูลใดอยู่บ้างและมีการใช้ข้อมูลใดในการดำเนินโครงการ',
			'field'=>'rate1,text1,text2',
			'items'=>array(
				array('section'=>'1','title'=>'ข้อมูลด้านเศรษฐกิจ'),
				// array('section'=>'2','title'=>'ข้อมูลสถานการณ์สุขภาวะจิต'),
				array('section'=>'3','title'=>'ข้อมูลด้านสังคม/วัฒนธรรม'),
				// array('section'=>'4','title'=>'ข้อมูลสถานการณ์สุขภาวะปัญญา'),
				// array('section'=>'5','title'=>'ข้อมูลสถานการณ์สุขภาวะปัจเจก'),
				// array('section'=>'6','title'=>'ข้อมูลสถานการณ์สุขภาวะครอบครัว'),
				// array('section'=>'7','title'=>'ข้อมูลสถานการณ์สุขภาวะชุมชน'),
				array('section'=>'8','title'=>'ข้อมูลด้านทรัพยากรธรรมชาติและสิ่งแวดล้อม'),
				array('section'=>'9','title'=>'ข้อมูลด้านสุขภาพ'),

				array('section'=>'99','title'=>'อื่นๆ'),
			)
		);

		$outputList['2']=array(
			'title'=>'2. การดำเนินโครงการนี้สอดคล้องกับแผนเดิมหรือมีการทบทวนแผนที่มีอยู่เดิมต่อไปนี้อย่างไร',
			'field'=>'rate1,text1,text2',
			'items'=>array(
				array('section'=>'1','title'=>'แผนชุมชน'),
				array('section'=>'2','title'=>'แผนของท้องถิ่น'),
				array('section'=>'3','title'=>'แผนของหน่วยงานราชการ'),
				array('section'=>'99','title'=>'แผนอื่นๆ'),
			)
		);

		$outputList['3']=array(
			'title'=>'3. มีการใช้ทุนของชุมชนที่สำคัญอะไรบ้างในการดำเนินงานของโครงการ',
			'field'=>'rate1,text1,text2',
			'items'=>array(
				array('section'=>'1','title'=>'ทรัพยากรธรรมชาติที่มีอยู่ในชุมชน'),
				array('section'=>'2','title'=>'วัฒนธรรม วิถีชีวิตที่มีอยู่ในชุมชน'),
				array('section'=>'3','title'=>'ภูมิปัญญาที่มีอยู่ในชุมชน'),
				array('section'=>'4','title'=>'เศรษฐกิจของชุมชน'),
			)
		);

		$outputList['4']=array(
			'title'=>'4. คน กลุ่มคน เครือข่ายสำคัญที่มีส่วนร่วมในโครงการ',
			'field'=>'rate1,text1,text2',
			'items'=>array(
				array('section'=>'1','title'=>'คน กลุ่มคน เครือข่ายที่เป็นภาคีหลัก (หมายถึงแกนนำที่เป็นผู้ปฏิบัติการของโครงการ)'),
				array('section'=>'2','title'=>'คน กลุ่มคน เครือข่ายที่เป็นภาคียุทธศาสตร์ (หมายถึงแกนนำที่เป็นผู้ผลักดัน หรือมีอิทธิพลต่อความสำเร็จของโครงการ)'),
			)
		);

		// TODO: ข้อ 5 เปลี่ยน ที่มา => แหล่งงบประมาณและทรัพยากร
		$outputList['5']=array(
			'title'=>'5. มีการใช้งบประมาณและทรัพยากรจำนวนเท่าไหร่จากแหล่งใด',
			'field'=>'rate1,text1,text2',
			'items'=>array(
				array('section'=>'1','title'=>'งบประมาณ'),
				array('section'=>'2','title'=>'ทรัพยากรอื่น ๆ'),
			)
		);


		//

		$ret .= '<section class="section-5 box">';

		$tables = new Table([
			'class' => 'project-valuation-form -other',
			'colgroup' => ['width="30%"','width="5%"','width="5%"', 'width="30%"','width="30%"'],
			'thead' => '<thead>'
				. '<tr><th></th><th style="width:30px;">ใช้</th><th style="width:30px;">ไม่ใช้</th><th>ชนิดและจำนวน</th><th>ที่มา</th></tr>'
				. '</thead>',
		]);

		foreach ($outputList as $mainKey => $mainValue) {
			$tables->children[] = ['<td colspan="5"><h3>'.$mainValue['title'].'</h3></td>'];

			foreach ($mainValue['items'] as $k=>$v) {
				if (!empty($v['section'])) $tables->children[] = '<header>';
				if (empty($v['section'])) {
					$tables->children[] = ['<td colspan="5"><b>'.$v['title'].'</b></td>'];
					continue;
				}

				$section = $mainKey.'.'.$v['section'];
				$irs = isset($valuationTr->items[$section]) ? end($valuationTr->items[$section]) : NULL;
				unset($row);
				$row[] = '<span>'.($v['section']).'. '.$v['title'].'</span>';

				$row[] = view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'rate2', 'name'=>'rate2'.$section, 'tr'=>$irs->trid, 'value'=>$irs->rate2),'1:',$isEdit,'radio');
				$row[] = view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'rate2', 'name'=>'rate2'.$section, 'tr'=>$irs->trid, 'value'=>$irs->rate2),'0:',$isEdit,'radio');

				$row[] = view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'text1','tr'=>$irs->trid,'ret'=>'html', 'value'=>trim($irs->text1)),$irs->text1,$isEdit,'textarea');
				$row[] = view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'text2','tr'=>$irs->trid, 'ret'=>'html', 'value'=>$irs->text2),$irs->text2,$isEdit,'textarea');

				$tables->children[] = $row;

				$tables->children[] = ['', 'config' => ['class' => 'empty']];
			}
		}
		$ret .= $tables->build();

		$ret .= '</section><!-- section-5 -->';



		if ($isViewOnly) {
			// Do nothing
		} else if ($isEdit) {
			$ret .= '<div class="btn-floating -right-bottom"><a class="sg-action btn -primary -circle48" href="'.url('project/'.$this->projectId.'/eval.input',array('debug'=>post('debug'))).'" data-rel="#main"><i class="icon -save -white"></i></a></div>';
		} else if ($isEditable) {
			$ret .= '<div class="btn-floating -right-bottom"><a class="sg-action btn -floating -circle48" href="'.url('project/'.$this->projectId.'/eval.input/edit',array('debug'=>post('debug'))).'" data-rel="#main"><i class="icon -edit -white"></i></a></div>';
		}

		$ret.='</div>';

		return new Scaffold([
			'appBar' => new ProjectInfoAppBarWidget($this->projectInfo),
			'body' => new Widget([
				'children' => [
					$ret,
					$this->_script(),
					// new DebugMsg($valuationTr,'$valuationTr'),
				], // children
			]), // Widget
		]);
	}

	function _script() {
		return '<style>
		.project-valuation-form td:nth-child(2), .project-valuation-form td:nth-child(3) {text-align:center;}
		.project-valuation-form thead {display:none;}
		.project-valuation-form .header th {font-weight:normal;}
		.project-valuation-form td:first-child span {background:#eee; display: block; padding: 8px; border-radius:4px; border: #ccc 1px solid;}
		.project-valuation-form td {border-bottom:none;}
		.project-valuation-form tr.empty td:first-child {background:transparent;}

		</style>';


		$ret .= '<script type="text/javascript">
		// Other radio group
		$(".project-valuation-form.-other input.inline-edit-field.-radio").each(function() {
			var $radioBtn = $(this).closest("tr").find(".inline-edit-field.-radio:checked")
			var radioValue = $radioBtn.val();
			//console.log("Tr = "+$radioBtn.data("tr")+" - radioValue="+radioValue);
			if (!(radioValue==0 || radioValue==1)) {
				$(this).closest("tr").find("span.inline-edit-field").hide();
			}
		});

		$(".project-valuation-form.-other input[type=\'radio\']").change(function() {
			var rate = $(this).val()
			var $inlineInput = $(this).closest("tr").find("td>span>span")
			//console.log("radio change "+$(this).val())
			$inlineInput.show()
		});

		</script>';
	}
}
?>