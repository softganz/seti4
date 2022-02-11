<?php
/**
* Project :: แบบประเมินคุณค่าของโครงการที่เกี่ยวข้องกับการสร้างเสริมสุขภาพ (Valuation)
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
		$projectId = $projectInfo->tpid;

		if (!$projectId) return new ErrorMessage(['code' => _HTTP_ERROR_BAD_REQUEST, 'text' => 'ไม่มีข้อมูลโครงการที่ระบุ']);

		$formid = 'valuation';

		$valuationTr = project_model::get_tr($projectId,$formid);
		$finalReportTitle = project_model::get_tr($projectId,'finalreport:title');

		$url = q();

		$titleRs = $valuationTr->items['title'] ? end($valuationTr->items['title']) : (Object) [];

		$locked = $titleRs->flag;

		$this->right->editMode = $this->right->editable && $this->action == 'edit' && !$locked;

		if ($this->right->viewOnly) {
			$floatingActionButton = NULL;
		} else if ($this->right->editMode) {
			$floatingActionButton = new FloatingActionButton([
				'children' => ['<a class="sg-action btn -primary -circle48" href="'.url('project/'.$projectId.'/eval.valuation',array('debug'=>post('debug'))).'" data-rel="#main"><i class="icon -material">done_all</i></a>'],
			]);
		} else if ($this->right->editable) {
			$floatingActionButton = new FloatingActionButton([
				'children' => ['<a class="sg-action btn -floating -circle48" href="'.url('project/'.$projectId.'/eval.valuation/edit',array('debug'=>post('debug'))).'" data-rel="#main"><i class="icon -material">edit</i></a>'],
			]);
		}

		return new Scaffold([
			'appBar' => new ProjectInfoAppBarWidget($this->projectInfo),
			// 'floatingActionButton' => $floatingActionButton,
			'body' => new Container([
				'id' => 'project-valuation',
				'class' => 'project-result'.($this->right->editMode ? ' sg-inline-edit' : ''),
				'attribute' => $this->right->editMode ? [
					'data-tpid' => $projectId,
					'data-update-url' => url('project/edit/tr'),
					'data-debug' => post('debug') ? 'yes' : NULL,
				] : NULL,

				'children' => [
					'<h2 class="title -main">แบบประเมินคุณค่าของโครงการที่เกี่ยวข้องกับการสร้างเสริมสุขภาพ</h3>'._NL,

					// Title
					new Container([
						'children' => [
							'<p>ชื่อโครงการ <strong>'.$projectInfo->title.'</strong></p>'._NL,
							'<p>'
								. ($projectInfo->info->prid ? 'รหัสโครงการ <strong>'.$projectInfo->info->prid.'</strong> ' : '')
								. ($projectInfo->info->agrno ? 'รหัสสัญญา <strong>'.$projectInfo->info->agrno.'</strong> ' : '')
								. 'ระยะเวลาโครงการ <strong>'.sg_date($projectInfo->info->date_from,'ว ดดด ปปปป').' - '.sg_date($projectInfo->info->date_end,'ว ดดด ปปปป').'</strong>'
								. '</p>'._NL,
							'<p><em>แบบประเมินคุณค่าของโครงการที่เกี่ยวข้องกับการสร้างเสริมสุขภาพ เป็นการคุณค่าที่เกิดจากโครงการในมิติต่อไปนี้</p><ul><li>ความรู้ด้านการสร้างเสริมสุขภาพและนวัตกรรมเชิงระบบสุขภาพชุมชน</li><li>การปรับเปลี่ยนพฤติกรรมที่มีผลต่อสุขภาวะ</li><li>การปรับเปลี่ยนสิ่งแวดล้อมที่เอื้อต่อสุขภาวะ</li><li>ผลกระทบเชิงบวกและนโยบายสาธารณะที่เอื้อต่อการสร้างสุขภาวะชุมชน</li><li>กระบวนการชุมชน</li><li>มิติสุขภาวะปัญญา / สุขภาวะทางจิตวิญญาณ</li></ul></em></p>',
						], // children
					]), // Container

					//
					new Container([
						'tagName' => 'section',
						'class' => 'section-5 box',
						'child' => new Table([
							'class' => 'project-valuation-form -other',
							'colgroup' => ['width="20%"','width="5%"','width="5%"','width="25%"','width="25%"','width="25%"'],
							'thead' => '<thead><tr><th rowspan="2">คุณค่าที่เกิดขึ้น<br />ประเด็น</th><th colspan="2">ผลที่เกิดขึ้น</th><th rowspan="2">รายละเอียด/การจัดการ</th><th rowspan="2">หลักฐาน/แหล่งอ้างอิง</th><th rowspan="2">แนวทางการพัฒนาต่อ</th></tr><tr><th style="width:30px;">ใช่</th><th style="width:30px;">ไม่ใช่</th></tr></thead>',
							'children' => (function($valuationTr, $formid) {
								$rows = [];
								foreach ($this->valuationItems() as $mainKey => $mainValue) {
									$rows[] = array('<td colspan="6"><h3>'.$mainValue['title'].'</h3></td>');

									foreach ($mainValue['items'] as $k => $v) {
										if (!empty($v['section'])) $rows[] = '<header>';
										if (empty($v['section'])) {
											$rows[] = array('<td colspan="6"><b>'.$v['title'].'</b></td>');
											continue;
										}

										$section = $mainKey.'.'.$v['section'];
										$irs = $valuationTr->items[$section] ? end($valuationTr->items[$section]) : (Object) [];
										unset($row);
										$row[] = '<span>'.($v['section']).'. '.$v['title'].'</span>';
										$row[] = view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'rate1', 'name'=>'rate'.$section, 'tr'=>$irs->trid, 'value'=>$irs->rate1),'1:',$this->right->editMode,'radio');
										$row[] = view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'rate1', 'name'=>'rate'.$section, 'tr'=>$irs->trid, 'value'=>$irs->rate1),'0:',$this->right->editMode,'radio');
										$row[] = view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'text1','tr'=>$irs->trid,'ret'=>'html', 'value'=>trim($irs->text1)),$irs->text1,$this->right->editMode,'textarea');
										$row[] = view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'text2','tr'=>$irs->trid, 'ret'=>'html', 'value'=>$irs->text2),$irs->text2,$this->right->editMode,'textarea');
										$row[] = view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'text3','tr'=>$irs->trid, 'ret'=>'html', 'value'=>$irs->text3),$irs->text3,$this->right->editMode,'textarea');
										$rows[] = $row;

										$rows[] = array('','config'=>array('class'=>'empty'));
									}
								}
								return $rows;
							})($valuationTr, $formid),
						]), // Container
					]), // Container

					$floatingActionButton,
					$this->script(),
				], // children
			]), // Container
		]);
	}

	function valuationItems() {
		return
		[
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
	}

	function script() {
		head('googlegraph','<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>');

		return '<style>
		.project-valuation-form td:nth-child(2), .project-valuation-form td:nth-child(3) {text-align:center;}
		.project-valuation-form thead {display:none;}
		.project-valuation-form .header th {font-weight:normal;}
		.project-valuation-form td:first-child span {background:#eee; display: block; padding: 8px; border-radius:4px; border: #ccc 1px solid;}
		.project-valuation-form td {border-bottom:none;}
		.project-valuation-form tr.empty td:first-child {background:transparent;}

		</style>
		<script type="text/javascript">
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
<?php
/**
* แบบฟอร์มการเก็บข้อมูลเพื่อการประเมินแบบมีโครงสร้าง
*
* @param Object $self
* @param Int $projectId
* @param String $action
* @param Int $actionId
* @return String
*/
function project_eval_valuation($self, $projectId, $action = NULL, $actionId = NULL) {
	$projectInfo = is_object($projectId) ? $projectId : R::Model('project.get',$projectId);
	$projectId = $projectInfo->tpid;

	if (!$projectId) return message('error', 'ไม่มีข้อมูลโครงการที่ระบุ');

	$formid = 'valuation';

	$valuationTr = project_model::get_tr($projectId,$formid);
	$finalReportTitle = project_model::get_tr($projectId,'finalreport:title');

	$url = q();


	R::View('project.toolbar', $self, $projectInfo->title, NULL, $projectInfo);

	$titleRs = $valuationTr->items['title'] ? end($valuationTr->items['title']) : (Object) [];

	$locked=$titleRs->flag;


	$isViewOnly = $action == 'view';
	$isEditable = $projectInfo->info->isRight;
	$isEditMode = $projectInfo->info->isRight && $action == 'edit' && !$locked;

	$ret.='<h2 class="title -main">แบบประเมินคุณค่าของโครงการที่เกี่ยวข้องกับการสร้างเสริมสุขภาพ</h3>'._NL;


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



	if ($isEditMode) {
		$inlineAttr['class'] = 'sg-inline-edit ';
		$inlineAttr['data-tpid'] = $projectId;
		$inlineAttr['data-update-url'] = url('project/edit/tr');
		if (post('debug')) $inlineAttr['data-debug'] = 'yes';
	}
	$inlineAttr['class'] .= 'project-result';

	$ret.='<div id="project-valuation" '.sg_implode_attr($inlineAttr).'>'._NL;


	$section='title';
	$irs = $valuationTr->items[$section] ? end($valuationTr->items[$section]) : (Object) [];

	$ret.='<p>ชื่อโครงการ <strong>'.$projectInfo->title.'</strong></p>'._NL;
	$ret.='<p>'
			. ($projectInfo->info->prid ? 'รหัสโครงการ <strong>'.$projectInfo->info->prid.'</strong> ' : '')
			. ($projectInfo->info->agrno ? 'รหัสสัญญา <strong>'.$projectInfo->info->agrno.'</strong> ' : '')
			. 'ระยะเวลาโครงการ <strong>'.sg_date($projectInfo->info->date_from,'ว ดดด ปปปป').' - '.sg_date($projectInfo->info->date_end,'ว ดดด ปปปป').'</strong>'
			. '</p>'._NL;

	// Section Activity
	$ret.='<p><em>แบบประเมินคุณค่าของโครงการที่เกี่ยวข้องกับการสร้างเสริมสุขภาพ เป็นการคุณค่าที่เกิดจากโครงการในมิติต่อไปนี้</p><ul><li>ความรู้ด้านการสร้างเสริมสุขภาพและนวัตกรรมเชิงระบบสุขภาพชุมชน</li><li>การปรับเปลี่ยนพฤติกรรมที่มีผลต่อสุขภาวะ</li><li>การปรับเปลี่ยนสิ่งแวดล้อมที่เอื้อต่อสุขภาวะ</li><li>ผลกระทบเชิงบวกและนโยบายสาธารณะที่เอื้อต่อการสร้างสุขภาวะชุมชน</li><li>กระบวนการชุมชน</li><li>มิติสุขภาวะปัญญา / สุขภาวะทางจิตวิญญาณ</li></ul></em></p>';


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


	$ret .= '<section class="section-5 box">';

	$tables = new Table();
	$tables->addClass('project-valuation-form -other');
	$tables->colgroup = array('width="20%"','width="5%"','width="5%"','width="25%"','width="25%"','width="25%"');
	$tables->thead = '<thead><tr><th rowspan="2">คุณค่าที่เกิดขึ้น<br />ประเด็น</th><th colspan="2">ผลที่เกิดขึ้น</th><th rowspan="2">รายละเอียด/การจัดการ</th><th rowspan="2">หลักฐาน/แหล่งอ้างอิง</th><th rowspan="2">แนวทางการพัฒนาต่อ</th></tr><tr><th style="width:30px;">ใช่</th><th style="width:30px;">ไม่ใช่</th></tr></thead>';

	foreach ($outputList as $mainKey=>$mainValue) {
		$tables->rows[] = array('<td colspan="6"><h3>'.$mainValue['title'].'</h3></td>');

		foreach ($mainValue['items'] as $k=>$v) {
			if (!empty($v['section'])) $tables->rows[] = '<header>';
			if (empty($v['section'])) {
				$tables->rows[] = array('<td colspan="6"><b>'.$v['title'].'</b></td>');
				continue;
			}

			$section = $mainKey.'.'.$v['section'];
			$irs = $valuationTr->items[$section] ? end($valuationTr->items[$section]) : (Object) [];
			unset($row);
			$row[] = '<span>'.($v['section']).'. '.$v['title'].'</span>';
			$row[] = view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'rate1', 'name'=>'rate'.$section, 'tr'=>$irs->trid, 'value'=>$irs->rate1),'1:',$isEditMode,'radio');
			$row[] = view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'rate1', 'name'=>'rate'.$section, 'tr'=>$irs->trid, 'value'=>$irs->rate1),'0:',$isEditMode,'radio');
			$row[] = view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'text1','tr'=>$irs->trid,'ret'=>'html', 'value'=>trim($irs->text1)),$irs->text1,$isEditMode,'textarea');
			$row[] = view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'text2','tr'=>$irs->trid, 'ret'=>'html', 'value'=>$irs->text2),$irs->text2,$isEditMode,'textarea');
			$row[] = view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'text3','tr'=>$irs->trid, 'ret'=>'html', 'value'=>$irs->text3),$irs->text3,$isEditMode,'textarea');
			$tables->rows[] = $row;

			$tables->rows[] = array('','config'=>array('class'=>'empty'));
		}
	}
	$ret .= $tables->build();

	$ret .= '</section><!-- section-5 -->';


	// ดึงค่า default จากรายละเอียดโครงการ
	$preAbstract='โครงการนี้มีวัตถุประสงค์เพื่อ';
	if ($projectInfo->objective) {
		$oi = 0;
		foreach ($projectInfo->objective as $rs) {
			$preAbstract .= ' ('.(++$oi).') '.$rs->title;
		}
	} else {
		$ret .= $projectInfo->info->objective;
	}
	$preAbstract .= _NL._NL;

	$preAbstract .= 'ผลการดำเนินงานที่สำคัญ ได้แก่';

	$oi = 0;
	foreach ($projectInfo->activity as $rs) {
		$preAbstract.=' ('.(++$oi).') '.$rs->title;
	}

	$preAbstract .= _NL._NL;
	$preAbstract .= 'ข้อเสนอแนะ ได้แก่ (1) ...';


	/*
	$section='title';
	$irs=end($finalReportTitle->items[$section]);

	$ret .= '<section class="section-7 box">';
	$ret .= '<h3>7. สรุปผลการทำโครงการ (บทคัดย่อ)<sup>*</sup></h3>';
	$ret .= View::inlineedit(
						array(
							'group'=>'finalreport:title',
							'fld'=>'text2',
							'tr'=>$irs->trid,
							'ret'=>'html',
							'button'=>'yes',
							'value'=>trim(SG\getFirst($irs->text2,$preAbstract))
						),
						SG\getFirst($irs->text2,$preAbstract),
						$isEditMode,
						'textarea'
					);


	$ret.='<p class="noprint">หมายเหตุ *<ul><li><strong>สรุปผลการทำโครงการ (บทคัดย่อ)</strong> จะนำไปใส่ในบทคัดย่อของรายงานฉบับสมบูรณ์</li><li>หากต้องการใช้ค่าเริ่มต้นของสรุปผลการทำโครงการ (บทคัดย่อ) ให้ลบข้อความในช่องสรุปผลการทำโครงการ (บทคัดย่อ) ทั้งหมด แล้วกดปุ่ม Refresh</li></ul></p>';
	$ret .= '</section><!-- section-7 -->';
*/

	if ($isViewOnly) {
		// Do nothing
	} else if ($isEditMode) {
		$ret .= '<div class="btn-floating -right-bottom"><a class="sg-action btn -primary -circle48" href="'.url('project/'.$projectId.'/eval.valuation',array('debug'=>post('debug'))).'" data-rel="#main"><i class="icon -save -white"></i></a></div>';
	} else if ($isEditable) {
		$ret .= '<div class="btn-floating -right-bottom"><a class="sg-action btn -floating -circle48" href="'.url('project/'.$projectId.'/eval.valuation/edit',array('debug'=>post('debug'))).'" data-rel="#main"><i class="icon -edit -white"></i></a></div>';
	}

	$ret.='</div>';


	head('googlegraph','<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>');

	$ret.='<style>
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

	//$ret.=print_o($valuationTr,'$valuationTr');
	return $ret;
}


?>