<?php
/**
* Project :: Month KPI Form
* Created 2020-01-25
* Modify  2020-01-25
*
* @param Object $projectInfo
* @param Int $qtRef
* @return Widget build()
*
* @usage project/{id}/info.kpi/{qtRef}?form={formId}
*/

$debug = true;

class ProjectInfoKpi extends Page {
	var $projectInfo;
	var $qtRef;

	function __construct($projectInfo, $qtRef = NULL) {
		$this->projectInfo = $projectInfo;
		$this->qtRef = $qtRef;
	}

	function build() {
		$projectInfo = $this->projectInfo;
		$qtRef = $this->qtRef;

		// Data Model
		if (!($projectId = $projectInfo->projectId)) return message('error', 'PROCESS ERROR');

		$isEdit = $projectInfo->right->isAdmin || $projectInfo->right->isOwner;

		if (!$isEdit) return message('error','Access denied');

		$formId = SG\getFirst(post('form'), 'psi');

		$getSchema = post('schema');

		$isApp = R()->appAgent;


		if ($qtRef) {
			$surveyInfo = R::Model('qt.get', $qtRef, '{debug: false}');
		} else {
			$surveyInfo->refid = NULL;
			$surveyInfo->info->tpid = $projectId;
			$surveyInfo->info->qtform = $formId;
			$surveyInfo->info->qtgroup = 10;
			$stmt = 'SELECT * FROM %qtmast% WHERE `tpid` = :projectId AND `qtform` = :formId ORDER BY `qtdate` DESC LIMIT 1';
			$lastReport = mydb::select($stmt, ':projectId', $projectId, ':formId', $formId);
			if ($lastReport->count()) {
				$surveyInfo->info->qtdate = date('Y-m-t', strtotime($lastReport->qtdate.' +1 day'));
			} else {
				$surveyInfo->info->qtdate = sg_date($projectInfo->info->date_from, 'Y-m-t');
			}
		}

		if ($formId) {
			$schema = file_get_contents(dirname(__FILE__).'/schema.project.'.$formId.'.json');
		} else  if ($surveyInfo->info->qtform) {
			$formInfo = R::Model('qt.form.get', $surveyInfo->info->qtform);
			$schema = $formInfo->info->schema;
		}

		//debugMsg($surveyInfo, '$surveyInfo');

		// View model
		return new Survey([
			'debug' => false,
			'schema' => $schema,
			'values' => (function($surveyInfo) {
				$tranValue = [];
				foreach ($surveyInfo->tran as $key => $value) {
					$tranValue['data'][$value->part] = $value->value;
				}
				return $tranValue;
			})($surveyInfo),
			'children' => [
				'<header class="header">'._HEADER_BACK
					. '<h3>'.json_decode($schema)->title->label.'</h3>'
					. (new Container([
						'tagName' => 'nav',
						'class' => 'nav',
						'children' => [
							new Ui([
								'children' => [
									$qtRef && $isEdit ? '<a class="sg-action" href="'.url('project/'.$projectId.'/info/qt.remove/'.$qtRef).'" data-rel="notify" data-done="close | load" data-title="ลบตัวชี้วัด" data-confirm="ต้องการลบตัวชี้วัด กรุณายืนยัน?"><i class="icon -material">delete</i></a>' : NULL,
								], // children
							]), // Ui
						], // children
					]))->build() // Container
					. '</header>',

				'form' => new Form([
					'action' => url('project/'.$projectId.'/info/qt.save/'.$seqId),
					'variable' => 'data',
					'class' => 'sg-form project-month-kpi-form',
					'rel' => 'notify',
					'done' => $isApp ? 'close | load' : 'close | load',
					'title' => 'ประจำเดือน '.sg_date($surveyInfo->info->qtdate, 'ดดด ปปปป'),
					'children' => [
						'refid' => ['type' => 'hidden', 'name' => 'refid', 'value' => $surveyInfo->qtRef],
						'org' => ['type' => 'hidden', 'name' => 'org', 'value' => $projectInfo->info->orgid],
						'group' => ['type' => 'hidden', 'name' => 'group', 'value' => $surveyInfo->info->qtgroup],
						'formid' => ['type' => 'hidden', 'name' => 'formid', 'value' => $surveyInfo->info->qtform],
						'qtdate' => ['type' => 'hidden', 'name' => 'qtdate', 'value' => $surveyInfo->info->qtdate],
					], // children
				]), // Form

				'<style type="text/css">
					.form-item .form-checkbox {display: none;}
					.form-item abbr.checkbox {padding: 0; margin-bottom: 8px;}
					.form-item .checkbox.-block>label {display: flex; background-color: #fff; padding: 8px; border-radius: 8px;}
					.form-item .option.-block .icon {flex: 0 0 24px; margin-right: 8px;}
					.form-item .option.-block b {display: block; background-color: #efb0ff; color: #6f008a; padding: 4px; border-radius: 4px; margin-right: 8px; height: 1.4em;}
					.form-item .option.-block span {flex: 1; display: block;}
					</style>',
			], // children
		]); // Survey
	}
}
?>