<?php
/**
* Project :: Send Activity of Kamsai Network School
*
* @param Object $self
* @param Int $orgId
* @param Object $data
* @return String
*/

function project_knet_action_add($self, $orgId = NULL, $data = NULL) {
	$orgInfo = is_object($orgId) ? $orgId : R::Model('school.get',$orgId);
	$orgId = $orgInfo->orgid;

	if (!$orgId) return message('error', 'ไม่มีข้อมูลองค์กรที่ระบุ');

	R::View('project.toolbar', $self, $orgInfo->name, 'knet', $orgInfo);

	$isAdmin = $orgInfo->RIGHT & _IS_ADMIN;
	$isEdit = ($isAdmin || in_array($orgInfo->officers[i()->uid],array('ADMIN','OFFICER')));

	$ret .= '<header class="header">'._HEADER_BACK.'<h3 class="title">บันทึกกิจกรรม</h3></header>';

	$isCreatable = $isEdit;

	$isSchoolFoodPilot = mydb::select(
		'SELECT * FROM %project_orgco% WHERE `orgId` = :orgId AND `tpid` = 450 LIMIT 1',
		[':orgId' => $orgId]
	)->tpid;

	if (!$isCreatable) return message('error', 'Access Denied');

	if (empty($data->actionDate)) $data->actionDate = date('d/m/Y');
	if (empty($data->actionDate)) $data->actionDate = date('d/m/Y');

	if ($data->_error) $ret .= message('error',$data->_error);

	// debugMsg($data, '$data');

	$form = new Form([
		'variable' => 'action',
		'action' => url('project/knet/'.$orgId.'/action.save'.($data->actionId ? '/'.$data->actionId : '')),
		'id' => 'project-knet-action-form-add',
		'class' => 'sg-form project-knet-action-form -add',
		'checkValid' => true,
		'rel' => 'notify',
		'done' => 'load | close',
		'children' => [
			'tpid' => ['type' => 'hidden', 'value' => $data->tpid,],
			'activityId' => ['type' => 'hidden', 'value' => $data->activityId],
			'title' => [
				'type' => 'text',
				'label' => 'ชื่อกิจกรรม',
				'class' => '-fill',
				'require' => true,
				'value' => $data->title,
				'placeholder' => 'ระบุชื่อกิจกรรม',
			],
			'date' => [
				'type' => 'textfield',
				'label' => 'เมื่อไหร่',
				'value' => (function($data) {
					$times = [];
					for ($hr = 7; $hr < 24; $hr++) {
						for ($min = 0; $min < 60; $min += 30) {
							$times[] = sprintf('%02d',$hr).':'.sprintf('%02d',$min);
						}
					}
					$value = '<input type="text" name="action[actionDate]" id="edit-action-actionDate" maxlength="10" class="sg-datepicker form-text require" style="width:80px;" value="'.sg_date($data->actionDate,'d/m/Y').'"> <select class="form-select" name="action[actionTime]" id="edit-action-actionTime">';
					foreach ($times as $time) {
						$value .= '<option value="'.$time.'"'.($time == $data->actionTime ? ' selected="selected"' : '').'>'.$time.'</option>';
					}
					$value .= '</select>';
					return $value;
				})($data),
			],

			// Standard
			'standardId' => $isSchoolFoodPilot ? [
				'type' => 'checkbox',
				'label' => 'มาตรฐาน:',
				'multiple' => true,
				'options' => [
					1 => 'มาตรฐานที่ 1 นโยบายและการบริหารจัดการของสถานศึกษา',
					2 => 'มาตรฐานที่ 2 การจัดการด้านความปลอดภัยอาหาร สุขาภิบาลอาหารและสิ่งแวดล้อม',
					3 => 'มาตรฐานที่ 3 คุณค่าทางโภชนาการสารอาหารที่เด็กควรได้รับตามวัน',
					4 => 'มาตรฐานที่ 4 การบูรณาการจัดการเรียนรู้และปัจจัยแวดล้อมเชิงสร้างสรรค์',
					5 => 'มาตรฐานที่ 5 การเฝ้าระวังภาวะโภชนาการ',
				],
				'value' => mydb::select(
					'SELECT `standardId` FROM %project_standard%
					WHERE `actionId` = :actionId;
					-- {key: "standardId", value: "standardId"}',
					[':actionId' => $data->actionId]
				)->items,
			] : NULL,

			'guideId' => !$isSchoolFoodPilot ? [
				'type' => 'checkbox',
				'label' => 'แนวทางดำเนินงาน:',
				'multiple' => true,
				'options' => mydb::select(
					'SELECT `trid`,`detail1` `objTitle`
					FROM %project_tr%
					WHERE `tpid` IS NULL AND `formid` = "info" AND `part` = "objective";
					-- {key: "trid", value: "objTitle"}'
				)->items,
				'value' => mydb::select(
					'SELECT * FROM %project_actguide%
					WHERE `actionid` = :actionid;
					-- {key: "guideid", value: "guideid"}',
					[':actionid' => $data->actionId]
				)->items,
			] : NULL,

			'actionReal' => [
				'type' => 'textarea',
				'label' => 'รายละเอียดกิจกรรม',
				'class' => '-fill',
				'rows' => 5,
				'value' => $data->actionReal,
				'placeholder' => 'ระบุรายละเอียดของกิจกรรมที่ได้ดำเนินการ',
			],
			'outputOutcomeReal' => [
				'type' => 'textarea',
				'label' => 'ผลผลิต / ผลลัพธ์',
				'class' => '-fill',
				'rows' => 5,
				'value' => $data->outputOutcomeReal,
				'placeholder' => 'ระบุรายละเอียดผลผลิต / ผลลัพธ์ของกิจกรรมที่ได้จากการดำเนินการ',
			],
			'save' => [
				'type' => 'button',
				'value' => '<i class="icon -save -white"></i><span>บันทึกกิจกรรม</span>',
				'pretext' => '<a class="sg-action btn -link -cancel" href="javascript:void(0)" data-rel="close"><i class="icon -material -gray">cancel</i><span>{tr:CANCEL}</span></a>',
				'container' => '{class: "-sg-text-right"}',
			],
		], // children
	]);

	$ret .= $form->build();

	return $ret;
}
?>
