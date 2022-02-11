<?php
/**
* iMed :: Visit Survey Form
* Created 2020-12-11
* Modify  2021-05-28
*
* @param Object $psnInfo
* @param Object $visitInfo
* @param String $formId
* @return Widget
*
* @usage imed/visit/{id}/form/{seq}/{form}
*/

$debug = true;

class ImedVisitForm {
	var $patientInfo;
	var $visitInfo;
	var $formId;
	var $formDone;
	var $firebaseUpdate = true;

	function __construct($patientInfo, $visitInfo, $formId) {
		$this->patientInfo = $patientInfo;
		$this->visitInfo = $visitInfo;
		$this->formId = $formId;
		$this->formDone = post('formDone');
		$this->firebaseUpdate = post('fb') == 'no' ? false : true;
	}

	function build() {
		// Data Model
		$getSchema = post('schema');

		$psnId = $this->patientInfo->psnId;
		$seqId = $this->visitInfo->seqId;
		$formId = $this->formId;

		$isEdit = is_admin('imed') || $this->visitInfo->uid == i()->uid;
		$isApp = R()->appAgent;

		if (!($isEdit || $this->visitInfo->seqId == -1)) return message(['responseCode' => _HTTP_ERROR_NOT_ALLOWED, 'text' => 'Access denied']);

		$surveyInfo = R::Model('imed.visit.survey.get', '{formId: "'.$formId.'", psnId: '.$psnId.', seqId: '.$seqId.'}', '{debug: false}');

		if ($formId) {
			$schema = file_get_contents('modules/imed/assets/schema.visit.'.$formId.'.json', true);
		} else  if ($surveyInfo->info->qtform) {
			$formInfo = R::Model('qt.form.get', $surveyInfo->info->qtform);
			$schema = $formInfo->info->schema;
		}

		//debugMsg('<pre>'.$schema.'</pre>');

		// debugMsg($surveyInfo, '$surveyInfo');
		// debugMsg($this->visitInfo, '$visitInfo');

		// View Model
		return new Survey([
			'debug' => false,
			'schema' => $schema,
			'values' => (function($surveyInfo){
				$values = ['value' => $surveyInfo->info->value];
				foreach ($surveyInfo->data as $key => $value) {
					$values[$key] = $value;
				}
				return $values;
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
									// $isEdit && $surveyInfo->qtRef ? '<a class="sg-action" href="'.url('imed/api/visit/'.$psnId.'/qt.delete/'.$seqId,['qtref' => $surveyInfo->qtRef]).'" data-rel="notify" data-done="close | load:#imed-visit-'.$seqId.':'.url('imed/visit/'.$psnId.'/item/'.$seqId, ['ref' => post('ref')]).'" data-title="ลบแบบเก็บข้อมูล" data-confirm="ต้องการลบแบบเก็บข้อมูล กรุณายืนยัน?"><i class="icon -material">delete</i></a>' : NULL,
								], // children
							]), // Ui
						], // children
					]))->build() // Container
					. '</header>',

				'form' => new Form([
					'action' => url('imed/api/visit/'.$psnId.'/qt.save/'.$seqId),
					'variable' => 'data',
					'class' => 'sg-form imed-visit-survey-form',
					'rel' => 'none',
					'done' => 'back'.($this->formDone ? ' | callback:'.$this->formDone : ''),
					'children' => [
						'refid' => ['type' => 'hidden', 'name' => 'refid', 'value' => $surveyInfo->qtRef],
						'group' => ['type' => 'hidden', 'name' => 'group', 'value' => 3],
						'formid' => ['type' => 'hidden', 'name' => 'formid', 'value' => $formId],
						'firebaseUpdate' => !$this->firebaseUpdate ? ['type' => 'hidden', 'name' => 'firebaseUpdate', 'value' => 'no'] : NULL,
					],
				]),

				'<script type="text/javascript">
				$("form [data-sum]").change(function() {
					var $this = $(this)
					var $form = $this.closest("form")
					var sumValue = 0
					//var calculateText = $form.data("calculateTotal")
					var targetField = $this.data("sum")
					//var calculateField = calculateText.split("=")[1].split(",")
					//console.log("targetField = "+targetField)
					//console.log(calculateField)
					//console.log("Change ", $this)
					$form.find("[data-sum=\'"+targetField+"\']").each(function(i){
						if (this.checked) {
							sumValue += parseInt($(this).attr("value"))
							//console.log("CHECKED",this)
						} else {
							//console.log("NOT CHECK ",this)
						}
						if ($(this).data("sum")) {
						}
					})
					$(targetField).val(sumValue)
					// console.log("SUM VALUE = ",sumValue)
				})
				</script>',
			], // children
		]); // Survey
	}
}
?>