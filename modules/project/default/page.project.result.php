<?php
/**
* Project :: Result
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

class ProjectResult extends Page {
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

		$formid='finalreport';

		$finalReportTitle = project_model::get_tr($this->projectId,$formid.':title');

		$isViewOnly = $this->action == 'view';
		$isEditable = $projectInfo->info->isRight;
		$isEdit = $projectInfo->info->isRight && $this->action == 'edit';

		$ret .= '<h2 class="title -main">ผลการดำเนินโครงการ</h2>';

		if ($isEdit) {
			$inlineAttr['class'] = 'sg-inline-edit ';
			$inlineAttr['data-tpid'] = $this->projectId;
			$inlineAttr['data-update-url'] = url('project/edit/tr');
			if (post('debug')) $inlineAttr['data-debug'] = 'yes';
		}
		$inlineAttr['class'] .= 'project-result';

		$ret.='<div id="project-result" '.sg_implode_attr($inlineAttr).'>'._NL;


		$ret .= '<section class="project-result-summary box">';
		$ret .= '<h3>สรุปผลการดำเนินโครงการ</h3>';

		$ret .= '<p><b>ผลการดำเนินโครงการ/กิจกรรม:</b><br />';
		$ret .= View::inlineedit(
			array(
				'group' => 'project',
				'fld' => 'performance',
				'name' => 'performance',
				'class' => '-block',
				'value' => $projectInfo->info->performance,
			),
			'1:บรรลุตามวัตถุประสงค์ของโครงการ',
			$isEdit,
			'radio'
		).'<br />';

		$ret .= View::inlineedit(
			array(
				'group' => 'project',
				'fld' => 'performance',
				'name' => 'performance',
				'class' => '-block',
				'value' => $projectInfo->info->performance,
			),
			'2:บรรลุตามวัตถุประสงค์บางส่วนของโครงการ',
			$isEdit,
			'radio'
		).'<br />';

		$ret .= View::inlineedit(
			array(
				'group' => 'project',
				'fld' => 'performance',
				'name' => 'performance',
				'value' => $projectInfo->info->performance,
			),
			'0:ไม่บรรลุตามวัตถุประสงค์ของโครงการ',
			$isEdit,
			'radio'
		);

		$ret .= '</p>';

		$ret .= View::inlineedit(
			array(
				'group'=>'project',
				'fld'=>'summary',
				'ret'=>'html',
				'button'=>'yes',
				'label' => '<b>สรุปผลการดำเนินโครงการ :</b>',
				'value'=>$projectInfo->info->summary,
			),
			$projectInfo->info->summary,
			$isEdit,
			'textarea'
		);


		$ret .= '</section><!-- project-result-summary -->';


		$ret .= '<section class="project-result-objective box">';
		$ret .= '<h3 class="title -sub1">ผลผลิตโครงการ</h3>';
		$tables = new Table();
		$tables->thead = array(
				'no'=>'',
				'วัตถุประสงค์',
				'sit -amt -nowrap' => 'สถานการณ์',
				'target -amt -nowrap' => 'เป้าหมาย',
				'output -amt -nowrap' => 'ผลผลิต',
				'อธิบาย',
			);
		$no=0;
		foreach ($projectInfo->objective as $rs) {
			$tables->rows[]=array(
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
					),
					$rs->outputSize ? number_format($rs->outputSize,2) : '',
					$isEdit
				),
				view::inlineedit(
					array(
						'group'=>'info:objective:'.$rs->trid,
						'fld'=>'text3',
						'class'=>'-fill',
						'tr'=>$rs->trid,
						'ret'=>'html',
					),
					$rs->noticeDetail,
					$isEdit,
					'textarea'
				),
			);
		}
		$ret.=$tables->build();
		$ret .= '</section><!-- project-result-objective -->';


		$ret .= '<section class="project-result-target box">';
		$ret .= '<h3 class="title -sub1">ผู้เข้าร่วมโครงการ</h3>';

		/*
		$ret .= '<p>';
		$ret .= View::inlineedit(
							array(
								'group'=>'project',
								'fld'=>'jointarget',
								'ret'=>'numeric',
								'label' => '<b>จำนวนผู้เข้าร่วม : </b>',
							),
							$projectInfo->info->jointarget,
							$isEdit
						)
					. ' (คน)';
		$ret .= '</p>';
		*/


		$targetTables = new Table();
		$targetTables->addClass('-target');
		$targetTables->thead = array('กลุ่มเป้าหมาย','amt -target'=>'จำนวนที่วางไว้(คน)','amt -output'=>'จำนวนที่เข้าร่วม(คน)');

		$targetTables->rows['totaltarget'] = array(
			'<b>จำนวนกลุ่มเป้าหมายทั้งหมด</b>',
			'',
			View::inlineedit(
				array(
					'group'=>'project',
					'fld'=>'jointarget',
					'ret'=>'numeric',
				),
				$projectInfo->info->jointarget,
				$isEdit
			)
		);

		$targetTables->rows[] = '<header>';
		foreach ($projectInfo->target as $targetGroup) {
			$h = reset($targetGroup);

			$targetTables->rows[] = array('<b>'.$h->parentName.'</b>','','');
			foreach ($targetGroup as $key => $targetItem) {
				$targetAmount += $targetItem->amount;
				$targetTables->rows[] = array(
					$targetItem->targetName,
					is_null($targetItem->amount) ? '-' : number_format($targetItem->amount),
					View::inlineedit(
						array(
							'group'=>'target:'.$targetItem->catid,
							'fld'=>'joinamt',
							'tagname'=>'info',
							'tr'=>$targetItem->catid,
							'class'=>'-fill',
							'ret'=>'numeric',
						),
						$targetItem->joinamt?number_format($targetItem->joinamt):'',
						$isEdit
					),
					$targetItem->output,
					'config'=>array('class'=>'project-target-item','data-tgtid'=>$targetItem->catid)
				);
			}
		}
		$targetTables->rows['totaltarget'][1] = $targetAmount;
		$ret .= $targetTables->build();
		$ret .= '</section><!-- project-result-target -->';




		$ret .= '<section class="project-result-abstract box">';

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

		$section = 'title';
		$irs = $finalReportTitle->items[$section] ? end($finalReportTitle->items[$section]) : (Object) [];

		$ret .= '<h3>บทคัดย่อ<sup>*</sup></h3>';
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
			$isEdit,
			'textarea'
		);

		$ret.='<p class="noprint"><em>หมายเหตุ *<ul><li><strong>บทคัดย่อ</strong> จะนำไปใส่ในส่วนบทคัดย่อของรายงานฉบับสมบูรณ์</li><li>หากต้องการใช้ค่าเริ่มต้นของบทคัดย่อ ให้ลบข้อความในช่องบทคัดย่อ ทั้งหมด แล้วกดปุ่ม Refresh</li></ul></em></p>';
		$ret .= '</section><!-- project-result-abstract -->';


		if ($isViewOnly) {
			// Do nothing
		} else if ($isEdit) {
			$ret .= '<div class="btn-floating -right-bottom"><a class="sg-action btn -primary -circle48" href="'.url('project/'.$this->projectId.'/result',array('debug'=>post('debug'))).'" data-rel="#main"><i class="icon -save -white"></i></a></div>';
		} else if ($isEditable) {
			$ret.='<div class="btn-floating -right-bottom"><a class="sg-action btn -floating -circle48" href="'.url('project/'.$this->projectId.'/result/edit',array('debug'=>post('debug'))).'" data-rel="#main"><i class="icon -edit -white"></i></a></div>';
		}

		$ret.='</div><!-- project-result -->';

		//$ret.=print_o($projectInfo,'$projectInfo');
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