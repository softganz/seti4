<?php
/**
* Project :: Follow Objective
* Created 2022-02-01
* Modify  2022-02-01
*
* @param Object $projectInfo
* @return Widget
*
* @usage project/{id}/info.objective
*/

class ProjectInfoObjective extends Page {
	var $projectId;
	var $projectInfo;

	function __construct($projectInfo) {
		$this->projectId = $projectInfo->projectId;
		$this->projectInfo = $projectInfo;
	}

	function build() {
		if (!$this->projectId) return new ErrorMessage(['code' => _HTTP_ERROR_BAD_REQUEST, 'text' => 'ไม่มีข้อมูลโครงการที่ระบุ']);

		$projectInfo = $this->projectInfo;

		$isEdit = $projectInfo->info->isEdit;

		$ret = '';
		// วัตถุประสงค์ทั่วไป และ วัตถุประสงค์เฉพาะ
		foreach(mydb::select('SELECT `catid`,`name` FROM %tag% WHERE `taggroup`="project:objtype" ORDER BY `catid` ASC')->items as $item)
			$objTypeList[$item->catid]=$item->name;

		$ret .= '<div id="project-info-objective-'.$this->projectId.'" class="project-info-objective">'._NL;

		//$ret .= print_o($objTypeList,'$objTypeList');


		$objectiveNo = 0;
		$tables = new Table();
		$tables->addClass('project-info-objective-list');
		$tables->thead = array(
			'no'=>'',
			'วัตถุประสงค์/ตัวชี้วัดความสำเร็จ',
			'problemsize -amt' => 'ขนาดปัญหา',
			'targetsize -amt -hover-parent' => 'เป้าหมาย 1 ปี',
		);

		foreach ($objTypeList as $objTypeId => $objTypeName) {
			foreach ($projectInfo->objective as $objective) {
				if ($objective->objectiveType!=$objTypeId) continue;

				$objectiveIsUse=false;
				foreach ($info->mainact as $mainActItem) {
					if (empty($mainActItem->parentObjectiveId)) continue;
					if (in_array($objective->trid, explode(',', $mainActItem->parentObjectiveId))) {
						$objectiveIsUse=true;
						break;
					}
				}

				$submenu='';
				if ($isEdit) {
					if ($objectiveIsUse) {
						//$ui->add('<a href="javascript:void(0)" title="วัตถุประสงค์ข้อนี้มีการใช้งานในกิจกรรมหลักแล้ว">ลบวัตถุประสงค์ไม่ได้</a>');
					} else {
						$submenu = '<nav class="nav iconset -hover"><a class="sg-action" href="'.url('project/'.$this->projectId.'/info/objective.remove/'.$objective->trid).'" data-confirm="คุณต้องการลบวัตถุประสงค์นี้ แน่ใจหรือไม่ กรุณายืนยัน?" data-rel="this" data-removeparent="tr"><i class="icon -cancel -gray"></i></a></nav>';
					}
				}

				$tables->rows[]=array(
					++$objectiveNo,
					(
						$objective->refid
						?
						$objective->title
						:
						view::inlineedit(array('group'=>'tr:info:objective', 'fld'=>'text1', 'tr'=>$objective->trid,'class'=>'-title -fill'), $objective->title, $isEdit, 'textarea')
					)
					.
					'<br /><label><i>ตัวชี้วัดความสำเร็จ :</i></label>'.view::inlineedit(array('group'=>'tr:info:objective','fld'=>'text2','tr'=>$objective->trid, 'ret'=>'html','class'=>'-fill'),$objective->indicatorDetail,$isEdit,'textarea'),
					$objective->problemsize,
					view::inlineedit(array('group'=>'tr:info:objective','fld'=>'num2','tr'=>$objective->trid, 'ret'=>'html','class'=>'-fill','ret'=>'numeric','placeholder'=>'?'),$objective->targetsize,$isEdit)
					.$submenu,
				);
			}
		}


		$ret .= $tables->build();




		// Add Custom Objective
		if ($isEdit) {
			$ret.='<nav class="nav -page actionbar -project -objective -sg-text-right"><a class="btn -primary" href="javascript:void(0)" onclick=\'$("#project-info-objective-form").show();return false;\'><i class="icon -addbig -white"></i><span>เพิ่มวัตถุประสงค์อื่น ๆ</span></a></nav>'._NL;
			$ret .= $this->__project_info_objective_form($projectInfo);
			$ret.='<p><em>เลือกตัวอย่างวัตถุประสงค์จากความสอดคล้องกับแผนงานที่ระบุไว้แล้ว หรือ คลิกเพิ่มวัตถุประสงค์อื่นๆ แล้วบันทึก</em></p>';
		}

		//$ret.=print_o($projectInfo,'$projectInfo');
		//$ret.=print_o($options,'$options');

		$ret.='</div><!-- project-info-objective-'.$this->projectId.' -->'._NL;

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => $this->projectInfo->title,
			]), // AppBar
			'body' => new Widget([
				'children' => [
					$ret,
				], // children
			]), // Widget
		]);
	}

	function __project_info_objective_form($projectInfo) {
		/*
		$stmt = 'SELECT p.*,pn.`name` `planName`
						FROM %tag% p
							LEFT JOIN %tag% pn ON pn.`taggroup`="project:planning" AND CONCAT("project:problem:",pn.`catid`)=p.`taggroup`
						WHERE p.`taggroup` IN
							(SELECT CONCAT("project:problem:",`refid`) FROM %project_tr% WHERE `tpid`=:projectId AND `formid`="info" AND `part`="supportplan")';
		$problemDbs = mydb::select($stmt,':projectId',$this->projectId);
		*/

		//$ret.=print_o($projectInfo->problem,'$projectInfo->problem');
		//$ret.=print_o($projectInfo->objective,'$projectInfo->objective');

		$problemList = array('' => '=== เลือกสถานการณ์ปัญหา ===');
		foreach ($projectInfo->problem as $rs) {
			if ($this->__is_problem_used($rs->trid,$projectInfo)) continue;
			$problemList[$rs->trid] = $rs->problem.($rs->problemsize ? ' (ขนาดปัญหา '.$rs->problemsize.')' : '');
		}

		$form = new Form(NULL,url('project/'.$this->projectId.'/info/objective.save'), 'project-info-objective-form', 'sg-form project-info-objective-form -hidden');
		$form->addData('rel', 'notify');
		$form->addData('done', 'load->replace: #project-info-objective-'.$this->projectId.':'.url('project/'.$this->projectId.'/info.objective'));

		$form->addConfig('title','เพิ่มวัตถุประสงค์');

		$form->addField(
			'objective',
			array(
				'type'=>'text',
				'label'=>'ระบุวัตถุประสงค์อื่น ๆ',
				'class'=>'-fill',
				'placeholder' => 'เช่น เพื่อเพิ่มจำนวนผู้มีกิจกรรมทางกายในชุมชน',
			)
		);

		$form->addField(
			'problemId',
			array(
				'type'=>'select',
				'label'=>'เชื่อมโยงกับสถานการณ์ปัญหา',
				'class'=>'-fill',
				'options'=>$problemList,
			)
		);

		$form->addField(
			'indicator',
			array(
				'type'=>'textarea',
				'label'=>'ตัวชี้วัดความสำเร็จ',
				'class'=>'-fill',
				'rows'=>3,
				'placeholder' => 'เช่น ร้อยละของผู้มีกิจกรรมทางกายในชุมชนเพิ่มขึ้น'
			)
		);

		$form->addField(
			'targetsize',
			array(
				'type'=>'text',
				'label'=>'เป้าหมาย 1 ปี (หน่วยตามตัวชี้วัดความสำเร็จ)',
				'class'=>'-fill',
				'placeholder'=>'0.00',
			)
		);

		$form->addField(
		'save',
		array(
			'type'=>'button',
			'value'=>'<i class="icon -save -white"></i><span>{tr:SAVE}</span>',
			'pretext'=>'<a class="btn -link -cancel" href="javascript:void(0)" onclick=\'$("#project-info-objective-form").hide();return false;\'><i class="icon -cancel -gray"></i>{tr:CANCEL}</a>',
			'container' => '{class: "-sg-text-right"}'
			)
		);

		$ret .= $form->build();

		$ret .= '<style type="text/css">
		.project-info-objective-form {margin:32px 16px; text-align:left; box-shadow:0 0 0 1px #eee inset;border-radius:4px;}
		</style>';
		return $ret;
	}

	function __is_problem_used($problemId, $projectInfo) {
		$found = false;
		//debugMsg('Check '.$taggroup.' Catid '.$catid);
		foreach ($projectInfo->objective as $rs) {
			if ($rs->problemId == $problemId) {
				$found = true;
				break;
			}
		}
		return $found;
	}
}
?>