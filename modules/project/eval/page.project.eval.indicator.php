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
* @usage project/{id}/eval.indicator[/{action}]
*/

import('widget:project.info.appbar.php');

class ProjectEvalIndicator extends Page {
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


		$isViewOnly = $this->action == 'view';
		$isEditable = $projectInfo->info->isRight;
		$isEdit = $projectInfo->info->isRight && $this->action == 'edit';

		$ret .= '<h2 class="title -main">แบบประเมินผลการดำเนินงาน (Performance/Product Evaluation)</h2>';

		if ($isViewOnly) {
			// Do nothing
		} else if ($isEdit) {
			$ret .= '<div class="btn-floating -right-bottom"><a class="sg-action btn -primary -circle48" href="'.url('project/'.$this->projectId.'/eval.indicator',array('debug'=>post('debug'))).'" data-rel="#main"><i class="icon -save -white"></i></a></div>';
		} else if ($isEditable) {
			$ret.='<div class="btn-floating -right-bottom"><a class="sg-action btn -floating -circle48" href="'.url('project/'.$this->projectId.'/eval.indicator/edit',array('debug'=>post('debug'))).'" data-rel="#main"><i class="icon -edit -white"></i></a></div>';
		}


		if ($isEdit) {
			$inlineAttr['class'] = 'sg-inline-edit ';
			$inlineAttr['data-tpid'] = $this->projectId;
			$inlineAttr['data-update-url'] = url('project/edit/tr');
			if (post('debug')) $inlineAttr['data-debug'] = 'yes';
		}
		$inlineAttr['class'] .= 'project-result';

		$ret.='<div id="project-result" '.sg_implode_attr($inlineAttr).'>'._NL;

		$ret .= '<section class="project-result-objective box">';

		$tables = new Table();

		$tables->colgroup = array(
			'no' => '',
			'objective' => 'width="25%"',
			'sit -amt -nowrap' => '',
			'target -amt -nowrap' => '',
			'output -amt -nowrap' => '',
			'outcome' => 'width="25%"',
			'impact ' => 'width="25%"',
			'exp' => 'width="20%"',
		);
		$tables->thead = array(
			'&nbsp;',
			'เป้าประสงค์หรือวัตถุประสงค์/ตัวชี้วัด(Indicator)',
			'สถานการณ์',
			'เป้าหมาย',
			'ผลผลิต<br />(Output)',
			'ผลลัพธ์<br />(Outcome)',
			'ผลกระทบ<br />(Impact)',
			'อธิบายข้อสังเกตที่สำคัญ',
		);

		$no=0;

		foreach ($projectInfo->objective as $rs) {
			$tables->rows[] = [
				++$no,
				$rs->title.'<br />'
				. 'ตัวชี้วัด : '.$rs->indicatorDetail,
				$rs->problemsize,
				$rs->targetsize,
				view::inlineedit(
					array(
						'group'=>'info:objective:'.$rs->trid,
						'fld'=>'num3',
						'class'=>'-fill',
						'tr'=>$rs->trid,
						'ret'=>'numeric',
						'options' => '{placeholder:"0.00"}',
					),
					$rs->outputSize ? number_format($rs->outputSize,2) : '',
					$isEdit
				),

				view::inlineedit(
					array(
						'group'=>'info:objective:'.$rs->trid,
						'fld'=>'text5',
						'class'=>'-fill',
						'tr'=>$rs->trid,
						'ret'=>'html',
						'options' => '{placeholder:"อธิบาย"}',
					),
					$rs->outcomeDetail,
					$isEdit,
					'textarea'
				),
				view::inlineedit(
					array(
						'group'=>'info:objective:'.$rs->trid,
						'fld'=>'text6',
						'class'=>'-fill',
						'tr'=>$rs->trid,
						'ret'=>'html',
						'options' => '{placeholder:"อธิบาย"}',
					),
					$rs->impactDetail,
					$isEdit,
					'textarea'
				),
				view::inlineedit(
					array(
						'group'=>'info:objective:'.$rs->trid,
						'fld'=>'text3',
						'class'=>'-fill',
						'tr'=>$rs->trid,
						'ret'=>'html',
						'options' => '{placeholder:"อธิบาย"}',
					),
					$rs->noticeDetail,
					$isEdit,
					'textarea'
				),
			];
		}
		$ret.=$tables->build();
		$ret .= '</section><!-- project-result-objective -->';


		if ($isViewOnly) {
			// Do nothing
		} else if ($isEdit) {
			$ret .= '<div class="btn-floating -right-bottom"><a class="sg-action btn -primary -circle48" href="'.url('project/'.$this->projectId.'/eval.indicator',array('debug'=>post('debug'))).'" data-rel="#main"><i class="icon -save -white"></i></a></div>';
		} else if ($isEditable) {
			$ret.='<div class="btn-floating -right-bottom"><a class="sg-action btn -floating -circle48" href="'.url('project/'.$this->projectId.'/eval.indicator/edit',array('debug'=>post('debug'))).'" data-rel="#main"><i class="icon -edit -white"></i></a></div>';
		}

		$ret.='</div><!-- project-result -->';

		return new Scaffold([
			'appBar' => new ProjectInfoAppBarWidget($this->projectInfo),
			'body' => new Widget([
				'children' => [
					$ret,
				], // children
			]), // Widget
		]);
	}
}
?>