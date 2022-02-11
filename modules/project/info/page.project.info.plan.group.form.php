<?php
/**
* Project :: Activity Plan Group Form for Add/Edit
* Created 2022-02-04
* Modify  2022-02-04
*
* @param Object $projectInfo
* @param Int $tranId
* @return String
*
* @usage project/{id}/info.plan.group.form
*/

class ProjectInfoPlanGroupForm extends Page {
	var $projectId;
	var $right;
	var $projectInfo;

	function __construct($projectInfo, $tranId = NULL) {
		$this->projectId = $projectInfo->projectId;
		$this->projectInfo = $projectInfo;
		$this->right = (Object) [
			'edit' => $projectInfo->info->RIGHT & _IS_EDITABLE,
		];
		$this->tranId = $tranId;
	}

	function build() {
		if (!$this->projectId) return message(['code' => _HTTP_OK_NO_CONTENT, 'text' => 'ไม่มีข้อมูลโครงการ']);
		else if (!$this->right->edit) return message(['code' => _HTTP_ERROR_UNAUTHORIZED, 'text' => 'Access Denied']);

		$projectId = $this->projectInfo->projectId;
		$projectInfo = $this->projectInfo;
		$formType = SG\getFirst($data->formType,'detail');

		if ($this->tranId) {
			$data = R::Model('project.calendar.get', array('activityId'=>$this->tranId));
		} else {
			$data = (Object) [
				'projectId' => $this->projectId,
				'parent' => SG\getFirst(post('parent')),
			];
		}

		// Set default value from current date
		if (empty($data->from_date)) $data->from_date = date('j/n/Y');
		if (empty($data->to_date)) $data->to_date = $data->from_date;
		if (empty($data->privacy)) $data->privacy = 'public';

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'กลุ่มกิจกรรม',
				'boxHeader' => true,
				'leading' => _HEADER_BACK,
			]),
			'body' => new Widget([
				'children' => [
					new Form([
						'variable' => 'activity',
						'action' => url('project/info/api/'.$projectId.'/activity.save'),
						'id' => 'edit-activity',
						'class' => 'sg-form',
						'checkValid' => true,
						'rel' => _AJAX ? 'notify' : NULL,
						'done' => _AJAX ? 'close | load->replace:#project-plan-list' : NULL,
						'children' => [
							'calid' => $data->id ? ['type' => 'hidden', 'value' => $data->calid] : NULL,
							'tpid' => $data->tpid ? ['type' => 'hidden', 'value' => $data->tpid] : NULL,
							'activityid' => $data->activityId ? ['type' => 'hidden', 'value' => $data->activityId] : NULL,
							'type' => ['type' => 'hidden', 'name' => 'type', 'value' => $formType],
							'privacy' => ['type' => 'hidden', 'value' => 'public'],
							'calowner' => ['type' => 'hidden', 'value' => 1],
							'parent' => ['type' => 'hidden', 'value' => 'group'],

							'title' => [
								'type'=>'text',
								'label'=>'ชื่อกลุ่มกิจกรรม',
								'class'=>'-fill',
								'maxlength'=>255,
								'require'=>true,
								'placeholder'=>'ระบุชื่อกลุ่มกิจกรรม',
								'value'=> $data->title
							],

							'date' => [
								'type' => 'textfield',
								'label' => 'ช่วงเวลาดำเนินการ',
								'require' => true,
								'value' => (function($projectInfo, $data) {
									$minDate = sg_date(SG\getFirst($projectInfo->info->date_from,date('Y-m-d')),'j/n/Y');
									$maxDate = sg_date(SG\getFirst($projectInfo->info->date_end,date('Y-m-d')),'j/n/Y');
									$value = '<input type="text" name="activity[from_date]" id="edit-activity-from_date" maxlength="10" class="sg-datepicker form-text require -date" style="width:6em;" value="'.htmlspecialchars(sg_date($data->from_date,'d/m/Y')).'" data-min-date="'.$minDate.'" data-max-date="'.$maxDate.'" data-diff="edit-activity-to_date">';
									$value .= ' ถึง ';
									$value .= '<input type="text" name="activity[to_date]" id="edit-activity-to_date" maxlength="10" class="sg-datepicker form-text require -date" style="width:6em;" value="'.htmlspecialchars(sg_date($data->to_date,'d/m/Y')).'" data-min-date="'.sg_date($projectInfo->info->date_from,'j/n/Y').'" data-max-date="'.sg_date($projectInfo->info->date_end,'j/n/Y').'">';
									$value .= ' ('.$minDate.' - '.$maxDate.')';
									return $value;
								})($projectInfo, $data),
							],

							'color' => [
								'type' => 'colorpicker',
								'label' => 'สีของกลุ่มกิจกรรม',
								'color' => 'Red, Green, Blue, Black, Purple, Aquamarine, Aqua, Chartreuse,Coral, DarkGoldenRod, Olive, Teal, HotPink, Brown',
								'value' => $data->color,
							],
							'save' => [
								'type' => 'button',
								'value' => '<i class="icon -save -white"></i>{tr:SAVE}',
								'pretext' => ($data->calid && user_access(false) ? '<a class="sg-action btn -link" href="'.url('project/'.$projectId.'/info.plan.form/'.$data->activityId).'" data-rel="box"><i class="icon -refresh -gray"></i><span>Refresh</span></a>' : '').'<a class="sg-action btn -link -cancel" data-rel="close" href="javascript:voud(0)""><i class="icon -cancel -gray"></i><span>{tr:CANCEL}</span></a>',
								'container' => '{class: "-sg-text-right"}',
							],
						], // children
					]),

					$formType != 'short' ? '</div>' : '',
					// new DebugMsg($this->projectInfo, '$projectInfo'),
				],
			]),
		]);
	}
}
?>