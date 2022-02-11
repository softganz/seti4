<?php
/**
* iMed :: Patient Survey
* Created 2021-06-04
* Modify  2021-06-04
*
* @param Object $patientInfo
* @return Widget
*
* @usage imed/psyc/{id}/info.survey
*/

$debug = true;

class ImedPsycInfoSurvey extends Page {
	var $psnId;
	var $patientInfo;
	var $isAccess;
	var $isEdit;

	function __construct($patientInfo) {
		$this->psnId = $patientInfo->psnId;
		$this->patientInfo = $patientInfo;
		$this->isAccess = $this->patientInfo->RIGHT & _IS_ACCESS;
		$this->isEdit = $this->patientInfo->RIGHT & _IS_EDITABLE;
	}

	function build() {
		$psnId = $this->patientInfo->psnId;

		if (!$psnId) return message('error','Invalid Patient Information');

		$this->surveyList = [
			'ADR' => 'แบบบันทึกอาการไม่พึงประสงค์จากการใช้ยา (ADR)',
			'MARS' => 'แบบปะเมินความร่วมมือในการใช้ยา (MARS)',
			'ISPSYC' => 'แบบคัดกรองโรคจิต (ISPSYC)',
			'SMIV' => 'แบบติดตามผู้ป่วยจิตเวชที่มีความเสี่ยงสูง (SMI-V)',
			'PVSS' => 'แบบประเมินระดับความรุนแรงของความเสี่ยงต่อการก่อความรุนแรง (PVSS)',
		];
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'แบบประเมินอาการทางจิต - '.$this->patientInfo->info->fullname,
				'removeOnApp' => true,
			]), // AppBar

			'body' => new Container([
				'children' => [
					// new Container([
					// 	'tagName' => 'nav',
					// 	'class' => 'nav -banner-menu',
					// 	'children' => [
					// 		new Ui([
					// 			'debug' => false,
					// 			'columnPerRow' => 1,
					// 			'children' => [
					// 				'<a class="sg-action btn -primary -fill" href="'.url('imed/psyc/'.$psnId.'/info.survey/ADR').'" data-webview="เยี่ยมบ้าน"><span class="lang-text -th">แบบบันทึกอาการไม่พึงประสงค์จากการใช้ยา (ADR)</span></a>',
					// 				'<a class="sg-action btn -primary -fill" href="'.url('imed/psyc/'.$psnId.'/info.survey/MARS').'" data-webview="ข้อมูลทั่วไป"><span class="lang-text -th">แบบปะเมินความร่วมมือในการใช้ยา (MARS)</span></a>',
					// 				'<a class="sg-action btn -primary -fill" href="'.url('imed/psyc/'.$psnId.'/info.survey/ISPSYC').'" data-webview="ความต้องการ"><span class="lang-text -th">แบบคัดกรองโรคจิต (ISPSYC)</span></a>',
					// 				'<a class="sg-action btn -primary -fill" href="'.url('imed/psyc/'.$psnId.'/info.survey/SMIV').'" data-webview="แบบประเมินอาการ"><span class="lang-text -th">แบบติดตามผู้ป่วยจิตเวชที่มีความเสี่ยงสูง (SMI-V)</span></a>',
					// 				'<a class="sg-action btn -primary -fill" href="'.url('imed/psyc/'.$psnId.'/info.survey/PVSS').'" data-webview="ข้อมูลสุขภาพ"><span class="lang-text -th">แบบประเมินระดับความรุนแรงของความเสี่ยงต่อการก่อความรุนแรง (PVSS)</span></a>',
					// 			], // children
					// 		]), // Ui
					// 		'<style type="text/css">
					// 		.nav.-banner-menu>.ui-action>.ui-item>a {padding: 16px 0;}
					// 		</style>',
					// 	], // children
					// ]), // Container
					new Table([
						'thead' => ['date -date' => 'วันที่ประเมิน', 'type' => 'แบบประเมิน', 'point -amt -hover-parent' => 'คะแนนการประเมิน'],
						'children' => (function() {
							$rows = [];
							foreach (mydb::select('SELECT `qtref`, `qtdate`, `qtform`, `seq`, `value` FROM %qtmast% WHERE `psnid` = :psnId AND `qtform` IN ("ADR","MARS","ISPSYC","SMIV","PVSS") ORDER BY `qtdate` DESC, `seq` DESC', ':psnId', $this->psnId)->items as $item) {
								$rows[] = [
									$item->qtdate,
									SG\getFirst($this->surveyList[$item->qtform],$item->qtform),
									$item->value
									.'<nav class="nav -icon -hover"><a class="sg-action" href="'.url('imed/visit/'.$this->psnId.'/form/'.$item->seq.'/'.$item->qtform).'" data-rel="box" data-width="480" data-webview="แบบประเมิน"><i class="icon -material">find_in_page</i></a></nav>',
								];
							}
							return $rows;
						})(),
					])
				],
			]),
		]); //Scaffold
	}
}
?>