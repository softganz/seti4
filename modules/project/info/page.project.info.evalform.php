<?php
/**
* Project :: Follow Eval Form Information
* Created 2021-05-31
* Modify  2021-10-27
*
* @param Object $projectInfo
* @return Widget
*
* @usage project/{id}/info.evalform
*/

import('widget:project.info.appbar.php');

class ProjectInfoEvalform extends Page {
	var $projectId;
	var $projectInfo;

	function __construct($projectInfo) {
		$this->projectId = $projectInfo->projectId;
		$this->projectInfo = $projectInfo;
	}

	function build() {
		if (!$this->projectId) return new ErrorMessage(['code' => _HTTP_ERROR_BAD_REQUEST, 'text' => 'ไม่มีข้อมูลโครงการที่ระบุ']);

		$isHIA = mydb::select('SELECT * FROM %project_tr% WHERE `tpid` = :projectId AND `formid` = "eval-hia" LIMIT 1', ':projectId', $this->projectId)->count();

		$isInput = mydb::select('SELECT * FROM %project_tr% WHERE `tpid` = :projectId AND `formid` = "eval-input" LIMIT 1', ':projectId', $this->projectId)->count();

		$isSuccess = mydb::select('SELECT * FROM %project_tr% WHERE `tpid` = :projectId AND `formid` = "eval-success" LIMIT 1', ':projectId', $this->projectId)->count();

		$isValuation = mydb::select('SELECT * FROM %project_tr% WHERE `tpid` = :projectId AND `formid` = "valuation" LIMIT 1', ':projectId', $this->projectId)->count();

		$isIndicator = false;
		foreach ($this->projectInfo->objective as $rs) {
			if ($rs->outputSize || $rs->outcomeDetail || $rs->impactDetail || $rs->noticeDetail) {
				$isIndicator = true;
				break;
			}
		}

		return new Scaffold([
			'appBar' => new ProjectInfoAppBarWidget($this->projectInfo),
			'body' => new Widget([
				'children' => [
					'<header class="header"><h3>แบบประเมิน</h3></header>',
					new Ui([
						'type' => 'menu',
						'children' => [
							'<a class="" href="'.url('project/'.$this->projectId.'/eval.input').'"><i class="icon -material -'.($isInput ? 'green' : 'gray').'">check_circle</i><span>1. แบบติดตามประเมินปัจจัยนำเข้า (Input Evaluation)</span></a>',
							'<a class="" href="'.url('project/'.$this->projectId.'/eval.process').'"><i class="icon -material -'.($isProcess ? 'green' : 'gray').'">check_circle</i><span>2. แบบการติดตามประเมินผลการดำเนินกิจกรรมของโครงการ (Process Evaluation)</span></a>',
							'<a class="" href="'.url('project/'.$this->projectId.'/eval.indicator').'"><i class="icon -material -'.($isIndicator ? 'green' : 'gray').'">check_circle</i><span>3. แบบประเมินผลการดำเนินงาน (Performance/Product Evaluation)</span></a>',
							'<a class="" href="'.url('project/'.$this->projectId.'/eval.success').'"><i class="icon -material -'.($isSuccess ? 'green' : 'gray').'">check_circle</i><span>4. แบบการวิเคราะห์และการสังเคราะห์ปัจจัยกำหนดความสำเร็จของโครงการ</span></a>',
							'<a class="" href="'.url('project/'.$this->projectId.'/eval.valuation').'"><i class="icon -material -'.($isValuation ? 'green' : 'gray').'">check_circle</i><span>5. แบบประเมินคุณค่าของโครงการที่เกี่ยวข้องกับการสร้างเสริมสุขภาพ</span></a>',
							'<a class="" href="'.url('project/'.$this->projectId.'/eval.hia').'"><i class="icon -material -'.($isHIA ? 'green' : 'gray').'">check_circle</i><span>6. แบบประเมิน HIA</span></a>',
						], // children
					]), // Ui
				], // children
			]), // Widget
		]);
	}
}
?>