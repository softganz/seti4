<?php
/**
* Project :: Follow Summary Information
* Created 2021-05-31
* Modify  2021-10-27
*
* @param Object $projectInfo
* @return Widget
*
* @usage project/{id}/info.summary
*/

import('widget:project.info.appbar.php');

class ProjectInfoSummary extends Page {
	var $projectId;
	var $projectInfo;

	function __construct($projectInfo) {
		$this->projectId = $projectInfo->projectId;
		$this->projectInfo = $projectInfo;
	}

	function build() {
		if (!$this->projectId) return message('error', 'ไม่มีข้อมูลโครงการที่ระบุ');

		return new Scaffold([
			'appBar' => new ProjectInfoAppBarWidget($this->projectInfo),
			'body' => new Widget([
				'children' => [
					new Card([
						'children' => [
							new ListTile([
								'class' => '-sg-paddingnorm',
								'title' => 'แบบประเมิน',
								'leading' => '<i class="icon -material">stars</i>',
							]),
							new Ui([
								'type' => 'menu',
								'children' => [
									'<a class="" href="'.url('project/'.$this->projectId.'/eval.input').'"><i class="icon -material -'.($isInput ? 'green' : 'gray').'">check_circle</i><span>1. แบบติดตามประเมินปัจจัยนำเข้า (Input Evaluation)</span></a>',
									'<a class="" href="'.url('project/'.$this->projectId.'/eval.process').'"><i class="icon -material -'.($isProcess ? 'green' : 'gray').'">check_circle</i><span>2. แบบการติดตามประเมินผลการดำเนินกิจกรรมของโครงการ (Process Evaluation)</span></a>',
									'<a class="" href="'.url('project/'.$this->projectId.'/eval.indicator').'"><i class="icon -material -'.($isIndicator ? 'green' : 'gray').'">check_circle</i><span>3. แบบประเมินผลการดำเนินงาน (Performance/Product Evaluation)</span></a>',
									'<a class="" href="'.url('project/'.$this->projectId.'/eval.success').'"><i class="icon -material -'.($isSuccess ? 'green' : 'gray').'">check_circle</i><span>4. แบบการวิเคราะห์และการสังเคราะห์ปัจจัยกำหนดความสำเร็จของโครงการ</span></a>',
									// '<a class="" href="'.url('project/'.$this->projectId.'/eval.valuation').'"><i class="icon -material -'.($isValuation ? 'green' : 'gray').'">check_circle</i><span>5. แบบประเมินคุณค่าของโครงการที่เกี่ยวข้องกับการสร้างเสริมสุขภาพ</span></a>',
									// ['<a class="" href="'.url('project/'.$this->projectId.'/eval.hia').'"><i class="icon -material -'.($isHIA ? 'green' : 'gray').'">check_circle</i><span>6. แบบประเมิน HIA</span></a>', '{class: "'.($isHIA ? '-active' : '').'"}'],
								], // children
							]), // Ui
						], // children
					]), // Card

					new Card([
						'children' => [
							new ListTile([
								'class' => '-sg-paddingnorm',
								'title' => 'รายงานประจำงวด',
								'leading' => '<i class="icon -material">stars</i>',
							]),
							new Ui([
								'type' => 'menu',
								'children' => [
									'<a class="" href="'.url('project/'.$this->projectId.'/operate').'"><i class="icon -material -gray">check_circle</i><span>1. รายงานการเงินประจำงวด</span></a>',
									'<a class="" href="'.url('project/'.$this->projectId.'/operate').'"><i class="icon -material -gray">check_circle</i><span>2. รายงานผลงานประจำงวด</span></a>',
								], // children
							]), // Ui
						], // children
					]), // Card

					new Card([
						'children' => [
							new ListTile([
								'class' => '-sg-paddingnorm',
								'title' => 'รายงานปิดโครงการ',
								'leading' => '<i class="icon -material">stars</i>',
							]),
							new Ui([
								'type' => 'menu',
								'children' => [
									'<a class="" href="'.url('project/'.$this->projectId.'/info.result').'"><i class="icon -material -gray">check_circle</i><span>1. รายงานสรุปผลการดำเนินงาน</span></a>',
									'<a class="" href="'.url('project/'.$this->projectId.'/operate.m2').'"><i class="icon -material -gray">check_circle</i><span>2. รายงานสรุปการเงินโครงการ</span></a>',
									'<a class="" href="'.url('project/'.$this->projectId.'/finalreport').'"><i class="icon -material -gray">check_circle</i><span>3. รายงานฉบับสมบูรณ์ (ปิดโครงการ)</span></a>',
								], // children
							]), // Ui
						], // children
					]), // Card

				], // children
			]), // Widget
		]);
	}
}
?>