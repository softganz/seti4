<?php
/**
* iMed :: Patient Disabled Information
* Created 2021-06-11
* Modify  2021-06-11
*
* @param Object $patientInfo
* @request formTitle
* @return Widget
*
* @usage imed/psyc/{id}/info.psyc
*/

$debug = true;

class ImedPatientFormDiagnosis {
	var $psnId;
	var $patientInfo;

	function __construct($patientInfo) {
		$this->psnId = $patientInfo->psnId;
		$this->patientInfo = $patientInfo;
	}

	function build() {
		$psnInfo = $this->patientInfo;
		$psnId = $psnInfo->psnId;

		$formTitle = post('title');
		$getCode = post('code');

		if (empty($psnInfo)) return message('error','ไม่มีข้อมูล');

		$isDisabled = $psnInfo->disabled->pid;

		$isAccess=$psnInfo->RIGHT & _IS_ACCESS;
		$this->isEdit=$psnInfo->RIGHT & _IS_EDITABLE;

		if (!$isAccess) return message('error',$psnInfo->error);

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => $this->formTitle.$this->patientInfo->realname,
				'removeOnApp' => true,
			]), // AppBar
			'body' => new Container([
				'children' => [
					'<header class="header -box -hidden">'._HEADER_BACK.'<h3>'.$formTitle.'</h3></header>',
					new Form([
						'action' => url('imed/patient/'.$this->psnId.'/info/tran.save'),
						'class' => 'sg-form',
						'rel' => 'none',
						'done' => 'callback:addDone | close',
						'children' => [
							'code' => ['type' => 'hidden', 'value' => $getCode],
							'detail1' => [
								'type' => 'textarea',
								'class' => '-fill',
								'placeholder' => 'เขียนบันทึก '.$formTitle,
							],
							'save' => [
								'type' => 'button',
								'value' => '<i class="icon -material">done</i><span>{tr:SAVE}</span>',
								'container' => '{class: "-sg-text-right"}',
							]
						], // children
					]), // Form
				], // children
			]), // Container
		]);
	}
}
?>