<?php
/**
* Project :: แบบประเมินผลการดำเนินงาน (Performance/Product Evaluation)
* Created 2021-05-31
* Modify  2022-02-05
*
* @param Object $projectInfo
* @param String $action
* @return Widget
*
* @usage project/{id}/eval.success[/{action}]
*/

import('widget:project.info.appbar.php');

class ProjectEvalSuccess extends Page {
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

		$formid = 'eval-success';

		$valuationTr = project_model::get_tr($this->projectId,$formid);

		$url = q();

		$titleRs = isset($valuationTr->items['title']) ? end($valuationTr->items['title']) : NULL;

		$locked=$titleRs->flag;

		$isViewOnly = $this->action == 'view';
		$isEditable = $projectInfo->info->isRight;
		$isEdit = $projectInfo->info->isRight && $this->action == 'edit' && !$locked;

		if (post('lock') && $isAdmin && $titleRs->trid) {
			$locked=$titleRs->flag==_PROJECT_LOCKREPORT?NULL:_PROJECT_LOCKREPORT;
			$stmt='UPDATE %project_tr% SET `flag`=:flag WHERE `trid`=:trid LIMIT 1';
			mydb::query($stmt,':trid',$titleRs->trid,':flag',$locked);
			location($url);
		}


		$ret.='<h2 class="title -main">แบบการวิเคราะห์และการสังเคราะห์ปัจจัยกำหนดความสำเร็จของโครงการ</h3>'._NL;




		// $ui = new Ui();
		// $ui->add('<a href="'.url($url).'">รายงานแบบประเมิน</a>');
		// $ui->add('<a href="'.url($url,$isAdmin?array('lock'=>$locked?'no':'yes') : NULL).'" title="'.($isAdmin?'คลิกเพื่อเปลี่ยนสถานะรายงาน':'').'">สถานะรายงาน : '.($locked?'Lock':'UnLock').'</a>');
		// $ret.='<nav class="nav reportbar">'.$ui->build().'</nav>';



		if ($isEdit) {
			$inlineAttr['class'] = 'sg-inline-edit ';
			$inlineAttr['data-tpid'] = $this->projectId;
			$inlineAttr['data-update-url'] = url('project/edit/tr');
			if (post('debug')) $inlineAttr['data-debug'] = 'yes';
		}
		$inlineAttr['class'] .= 'project-eval -success';

		$ret.='<div id="project-success" '.sg_implode_attr($inlineAttr).'>'._NL;


		$section='title';
		$irs = isset($valuationTr->items[$section]) ? end($valuationTr->items[$section]) : NULL;

		$outputList = [
			'people' => [
				'title' => 'การเปลี่ยนแปลงที่เกิดกับคน กลุ่มคน เครือข่าย (เช่น มีความรู้ มีทักษะ มีความชำนาญ มีศักยภาพและขีดความสามารถเพิ่มขึ้น)',
				'field' => 'rate1,text1,text2',
				'items' => [
					['section' => '1', 'title' => ''],
				]
			],
			// 'environment' => [
			// 	'title' => 'การเปลี่ยนแปลงสภาพแวดล้อมที่เอื้อต่อสุขภาวะ (เช่น )',
			// 	'field' => 'rate1,text1,text2',
			// 	'items' => [
			// 		['section' => '1', 'title' => ''],
			// 	]
			// ],
			'output' => [
				'title' => 'การเปลี่ยนแปลงสภาพแวดล้อมที่เอื้อต่อผลสัมฤทธิ์ของโครงการ (เช่น เกิดนโยบาย เกิดข้อตกลงชุมชน เกิดมาตรการทางสังคม)',
				'field' => 'rate1,text1,text2',
				'items' => [
					['section' => '1', 'title' => ''],
				]
			],
			// 'commune' => [
			// 	'title' => 'การเปลี่ยนกลไก และกระบวนการในชุมชนที่เอื้อต่อสุขภาวะ',
			// 	'field' => 'rate1,text1,text2',
			// 	'items' => [
			// 		['section' => '1', 'title' => ''],
			// 	]
			// ],
			'commune' => [
				'title' => 'การเปลี่ยนกลไก และกระบวนการในชุมชนที่เอื้อต่อผลสัมฤทธิ์ของโครงการ (เช่น เกิดกลุ่ม ชมรม เครือข่าย เกิดกระบวนการเรียนรู้ เกิดกระบวนการมีส่วนร่วม)',
				'field' => 'rate1,text1,text2',
				'items' => [
					['section' => '1', 'title' => ''],
				]
			],
		];



		$ret .= '<section class="section-5 box">';

		$tables = new Table([
			'class' => 'project-valuation-form -other',
			'showHeader' => false,
			'thead' => ['การเปลี่ยนแปลงทั้งที่คาดการณ์ไว้และไม่ได้คาดการณ์ไว้ล่วงหน้า','รายละเอียด','หลักฐาน','detail -hover-parent'=>'แนวทางการพัฒนาต่อ'],
		]);

		foreach ($outputList as $mainKey=>$mainValue) {
			$tables->rows[] = array('<td colspan="4"><h3>'.$mainValue['title'].'</h3></td>');
			$tables->rows[] = '<header>';
			$section = $mainKey.'.'.$v['section'];

			foreach ($valuationTr->items[$mainKey] as $rs) {
				$menu = '';
				if ($isEdit) $menu = '<nav class="nav -icons -hover -no-print"><a class="sg-action" href="'.url('project/'.$this->projectId.'/info/tran.remove/'.$rs->trid).'" data-rel="none" data-title="ลบรายการ" data-confirm="ต้องการลบรายการนี้ กรุณายืนยัน?" data-removeparent="tr"><i class="icon -cancel"></i></a></nav>';
				unset($row);
				$row[] = view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'text1','tr'=>$rs->trid,'ret'=>'html', 'value'=>trim($rs->text1)),$rs->text1,$isEdit,'textarea');
				$row[] = view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'text2','tr'=>$rs->trid, 'ret'=>'html', 'value'=>$rs->text2),$rs->text2,$isEdit,'textarea');
				$row[] = view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'text3','tr'=>$rs->trid, 'ret'=>'html', 'value'=>$rs->text2),$rs->text3,$isEdit,'textarea');
				$row[] = view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'text4','tr'=>$rs->trid, 'ret'=>'html', 'value'=>$rs->text2),$rs->text4,$isEdit,'textarea')
					. $menu;
				$tables->rows[] = $row;
			}
			if ($isEdit) {
				$tables->rows[] = array('<td colspan="4" class="-sg-text-right"><a class="sg-action" href="'.url('project/'.$this->projectId.'/info/tran.add/eval-success,'.$mainKey).'" data-rel="#main" data-ret="'.url('project/'.$this->projectId.'/eval.success/edit').'"><i class="icon -add"></i></a></td>');
			}


		}
		$ret .= $tables->build();

		$ret .= '</section><!-- section-5 -->';



		if ($isViewOnly) {
			// Do nothing
		} else if ($isEdit) {
			$ret .= '<div class="btn-floating -right-bottom"><a class="sg-action btn -primary -circle48" href="'.url('project/'.$this->projectId.'/eval.success',array('debug'=>post('debug'))).'" data-rel="#main"><i class="icon -save -white"></i></a></div>';
		} else if ($isEditable) {
			$ret .= '<div class="btn-floating -right-bottom"><a class="sg-action btn -floating -circle48" href="'.url('project/'.$this->projectId.'/eval.success/edit',array('debug'=>post('debug'))).'" data-rel="#main"><i class="icon -edit -white"></i></a></div>';
		}

		$ret.='</div>';


		return new Scaffold([
			'appBar' => new ProjectInfoAppBarWidget($this->projectInfo),
			'body' => new Widget([
				'children' => [
					$ret,
					$this->_script(),
				], // children
			]), // Widget
		]);
	}

	function _script() {
		return '<style>
		.project-eval.-success td {width: 25%;}
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