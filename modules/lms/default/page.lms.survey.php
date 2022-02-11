<?php
/**
* LMS :: Course Evaluation Survey
* Created 2020-07-01
* Modify  2021-05-25
*
* @param Int $surveyId
* @return Widget
*/

$debug = true;

class LmsSurvey {
	var $surveyId;

	function __construct($surveyId = NULL) {
		$this->surveyId = $surveyId;
	}

	function build() {
		$surveyId = $this->surveyId;

		$getSchema = post('schema');

		$surveyInfo = R::Model('lms.survey.get', $surveyId);

		R::View('toolbar', $self, 'ประเมินผลวิชา'.($surveyInfo->moduleName ? '/' : '').$surveyInfo->moduleName, 'lms', $surveyInfo, '{searchform: false}');

		if (!($surveyId = $surveyInfo->surveyId)) return message('error', 'PROCESS ERROR');

		$isAdmin = user_access('administer lms');
		$isEdit = i()->uid == $surveyInfo->uid || $isAdmin;


		if (!$isEdit) return message('error', 'Access Denied');

		$currentDate = date('Y-m-d H:i:s');

		$isEvalDate = $courseInfo->info->datebegin && $courseInfo->info->dateend && $currentDate >= $courseInfo->info->datebegin && $currentDate <= $courseInfo->info->dateend;

		$ret = '<header class="header"><h5>ประเมินผลวิชา '.$surveyInfo->info->moduleName.' หลักสูตร '.$surveyInfo->info->courseName.'</h5></header>';

		if ($getSchema) {
			$schema = file_get_contents(dirname(__FILE__).'/schema.survey.'.$getSchema.'.json');
		} else  if ($surveyInfo->info->qtform) {
			$formInfo = R::Model('qt.form.get', $surveyInfo->info->qtform);
			$schema = $formInfo->info->schema;
		}

		if (empty($schema)) {
			$schema = file_get_contents(dirname(__FILE__).'/schema.survey.default.json');
		}

		// debugMsg($surveyInfo, '$surveyInfo');

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'ประเมินผลวิชา'.($surveyInfo->moduleName ? '/' : '').$surveyInfo->moduleName,
				'navigator' => [
					'info' => R::View('lms.nav', $surveyInfo, '{searchform: false}'),
				]
			]), // AppBar
			'children' => [
				new Survey([
					'debug' => false,
					'schema' => $schema,
					'values' => (function($surveyInfo) {
						$tranValue = [];
						foreach ($surveyInfo->trans as $key => $value) {
							$tranValue[$value->part.':rate'] = $value->rate;
							$tranValue[$value->part.':value'] = $value->value;
						}
						return $tranValue;
					})($surveyInfo),
					'children' => [
						'form' => new Form([
							'action' => url('lms/'.$surveyInfo->info->courseid.'/info/mod.survey.save/'.$surveyId),
							'variable' => 'data',
							'class' => 'sg-form lms-survey',
							'rel' => 'notify',
							'done' => 'reload:'.url('lms/student/survey'),
						]), // Form

						'<script type="text/javascript">
							$(".lms-survey .form-radio").change(function() {
								$form = $(this).closest("form")
								$.post($form.attr("action"), $form.serialize(), function(html) {
									notify("Updated.",500)
								})
							});
							$(".lms-survey .form-textarea, .lms-survey .form-text").blur(function() {
								$form = $(this).closest("form")
								$.post($form.attr("action"), $form.serialize(), function(html) {
									notify("Updated.",500)
								})
							});
						</script>',

						'<style tyle="text/css">
							.form-item {padding: 0;}
							.lms-survey .body-group {border: 1px #eee solid; margin: 16px; padding: 0; background-color: #f9f9f9;}
							.lms-survey .body-group>.form-item>label {padding: 0 8px;}
							.lms-survey .body-group>*:first-child>label {padding: 0; margin: 0;}
							.lms-survey .body-group h3 {background-color: #ddd; padding: 8px; display: block; font-weight: bold; margin: 1px;}
							.lms-survey .form-item abbr.checkbox label>span:last-child {margin-right: 32px;}
							.lms-survey .form-item .form-text {margin: 0 8px;}
							.lms-survey .form-item .form-text.-fill {margin: 8px; width: calc(100% - 32px);}
							.lms-survey .form-item .form-textarea.-fill {margin: 8px; width: calc(100% - 32px);}
							.lms-survey .form-item.-edit-data-save {padding: 32px;}
						</style>',
					], // children
				]), // Survey
			], // children
		]);
	}
}
?>