<?php
/**
* Project :: Add Activity Plan
* Created 2021-11-15
* Modify  2021-11-15
*
* @param Object $projectInfo
* @param Int $tranId
* @param JSON String/Object $options
* @return String
*
* @usage project/{id}/info.plan.form
*/

$debug = true;

// import('widget:project.follow.nav.php');

class ProjectInfoPlanForm extends Page {
	var $projectId;
	var $right;
	var $projectInfo;

	function __construct($projectInfo, $tranId = NULL/*, $options = '{}'*/) {
		$this->projectId = $projectInfo->projectId;
		$this->projectInfo = $projectInfo;
		$this->right = (Object) [
			'edit' => $projectInfo->info->RIGHT & _IS_EDITABLE,
		];
		$this->tranId = $tranId;
		// $this->options = $options;
	}

	function build() {
		if (!$this->projectId) return message(['code' => _HTTP_OK_NO_CONTENT, 'text' => 'ไม่มีข้อมูลโครงการ']);
		else if (!$this->right->edit) return message(['code' => _HTTP_ERROR_UNAUTHORIZED, 'text' => 'Access Denied']);

		$projectId = $this->projectInfo->projectId;
		$projectInfo = $this->projectInfo;
		$formType = SG\getFirst($data->formType,'detail');
		$options = options('project');

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
		if (empty($data->from_time)) $data->from_time = '09:00';
		if (empty($data->to_time)) {
			list($hr,$min) = explode(':',$data->from_time);
			$data->to_time = sprintf('%02d',$hr+1).':'.$min;
		}
		if (empty($data->privacy)) $data->privacy = 'public';

		list(,$month,$year) = explode('/',$data->from_date);

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'รายละเอียดกิจกรรม',
				'boxHeader' => true,
				'leading' => _HEADER_BACK,
				// 'navigator' => new ProjectFollowNavWidget($this->projectInfo, ['showPrint' => true]),
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

							'parent' => [
								'type' => 'select',
								'label' => 'กลุ่มกิจกรรม:',
								'class' => '-fill',
								'value' => $data->parent,
								'options' => (function($projectId, $calId = NULL) {
									$options = [''=>'== เลือกกลุ่มกิจกรรม =='];
									mydb::where('c.`tpid` = :tpid AND a.`parent` IS NULL', ':tpid', $projectId);
									if ($calId) mydb::where('c.`id` != :calid', ':calid', $calId);
									$stmt = 'SELECT a.`trid` `activityId`, `title`
										FROM %calendar% c
											LEFT JOIN %project_tr% a ON a.`tpid` = c.`tpid` AND a.`calid` = c.`id` AND a.`formid` = "info" AND a.`part` = "activity"
										%WHERE%
										ORDER BY CASE
											WHEN a.`tagname` IS NOT NULL THEN 1
											ELSE 2
										END ASC,
										CONVERT (`title` USING tis620) ASC
										';
									foreach (mydb::select($stmt)->items as $rs) {
										if ($rs->activityId) $options[$rs->activityId] = $rs->title;
									}
									return $options;
								})($projectId, $data->calid),
							],

							'title' => [
								'type'=>'text',
								'label'=>'ชื่อกิจกรรม',
								'class'=>'-fill',
								'maxlength'=>255,
								'require'=>true,
								'placeholder'=>'ระบุชื่อกิจกรรม',
								'value'=> $data->title
							],

							'date' => [
								'type' => 'textfield',
								'label' => 'เมื่อไหร่',
								'require' => true,
								'value' => (function($projectInfo, $data) {
									for ($hr = 7; $hr < 24; $hr++) {
										for ($min = 0; $min < 60; $min += 30) {
											$times[] = sprintf('%02d',$hr).':'.sprintf('%02d',$min);
										}
									}

									$minDate = sg_date(SG\getFirst($projectInfo->info->date_from,date('Y-m-d')),'j/n/Y');
									$maxDate = sg_date(SG\getFirst($projectInfo->info->date_end,date('Y-m-d')),'j/n/Y');
									$value = '<input type="text" name="activity[from_date]" id="edit-activity-from_date" maxlength="10" class="sg-datepicker form-text require -date" style="width:6em;" value="'.htmlspecialchars(sg_date($data->from_date,'d/m/Y')).'" data-min-date="'.$minDate.'" data-max-date="'.$maxDate.'" data-diff="edit-activity-to_date"> <select class="form-select" name="activity[from_time]" id="edit-activity-from_time">';
									foreach ($times as $time) {
										$value .= '<option value="'.$time.'"'.($time == $data->from_time?' selected="selected"':'').'>'.$time.'</option>';
									}
									$value .= '</select>
									ถึง <select class="form-select" name="activity[to_time]" id="edit-activity-to_time">';
									foreach ($times as $time) {
										$value .= '<option value="'.$time.'"'.($time == $data->to_time?' selected="selected"':'').'>'.$time.'</option>';
									}
									$value .= '</select>
									<input type="text" name="activity[to_date]" id="edit-activity-to_date" maxlength="10" class="sg-datepicker form-text require -date" style="width:6em;" value="'.htmlspecialchars(sg_date($data->to_date,'d/m/Y')).'" data-min-date="'.sg_date($projectInfo->info->date_from,'j/n/Y').'" data-max-date="'.sg_date($projectInfo->info->date_end,'j/n/Y').'">';
									$value .= ' ('.$minDate.' - '.$maxDate.')';
									return $value;
								})($projectInfo, $data),
							],

							'areacode' => ['type' => 'hidden', 'value' => $data->areacode],
							'latlng' => ['type' => 'hidden', 'value' => $data->latlng],
							'location' => [
								'type' => $formType == 'short'?'hidden':'text',
								'label' => 'ที่ไหน',
								'maxlength' => 255,
								'placeholder' => 'ระบุสถานที่ หมู่ที่ ตำบล',
								'value' => $data->location,
								'class' => 'sg-address -fill',
								'attr' => 'data-altfld="edit-activity-areacode"',
								//'posttext' => ' <a href="javascript:void(0)" id="activity-addmap">แผนที่</a><div id="activity-mapcanvas" class="-hidden"></div>',
							],

							// Budget
							'budget' => $data->childs ?
							[
								'type' => 'hidden',
								'label' => 'งบประมาณที่ตั้งไว้ (บาท)',
								'maxlength' => 11,
								'class' => '-money',
								'placeholder' => '0.00',
								'value' => 0,
							]
							: [
								'type' => 'text',
								'label' => 'งบประมาณที่ตั้งไว้ (บาท)',
								'maxlength' => 11,
								'class' => '-money',
								'placeholder' => '0.00',
								'value' => number_format($data->budget,2,'.',''),
							],

							// Multiple Target
							$options->multipleTarget ? [
								'label' => 'กลุ่มเป้าหมาย/ผู้มีส่วนร่วม/ผู้สนับสนุนที่เข้าร่วมกิจกรรม',
								'type' => 'textfield',
								'value' => (function($data) {
									debugMsg($data, '$data');
									$joinListTable = new Table();
									$joinListTable->thead = ['กลุ่มเป้าหมาย','amt'=>'จำนวนคน'];
									$joinListTable->rows[] = ['<td class="subheader" colspan="2">กลุ่มเป้าหมายที่เข้าร่วม'];
									foreach (cfg('project.target') as $key => $value) {
										$joinListTable->rows[] = [
											$value,
											'<input class="form-text -numeric" type="text" name="activity['.$key.']" size="5" value="'.$data->{'targt_'.$key}.'" /> คน'
										];
									}
									$joinListTable->rows[] = ['<td class="subheader" colspan="2">ผู้มีส่วนร่วม/ผู้สนับสนุน'];
									foreach (cfg('project.support') as $key => $value) {
										$joinListTable->rows[] = [
											$value,
											'<input class="form-text -numeric" type="text" name="activity['.$key.']" size="5" value="'.$data->{'targt_'.$key}.'" /> คน'
										];
									}
									return $joinListTable->build();
								})($data),
							] : NULL,

							// Single Target
							'targetpreset' => !$options->multipleTarget ? [
								'type' => 'text',
								'label' => 'กลุ่มเป้าหมาย (คน)',
								'maxlength' => 5,
								'class' => '-numeric',
								'placeholder' => '0',
								'value' => number_format($data->targetpreset,0,'',''),
							] : NULL,
							'targetdetail' => !$options->multipleTarget ? [
								'type' => 'textarea',
								'label' => 'รายละเอียดกลุ่มเป้าหมาย',
								'class' => '-fill',
								'rows' => 3,
								'value' => $data->target,
							] : NULL,

							'detail' => [
								'type' => $formType == 'short'?'hidden':'textarea',
								'label' => 'รายละเอียดกิจกรรมตามแผน',
								'rows' => 5,
								'class' => '-fill',
								'placeholder' => 'ระบุรายละเอียดของกิจกรรมที่วางแผนว่าจะทำ',
								'value' => $data->detail,
							],
							'color' => [
								'type' => 'colorpicker',
								'label' => 'สีของกิจกรรม',
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

	function _script() {
		return '<script type="text/javascript">
		var from=$("#edit-activity-from_date").val().split("/");
		var to=$("#edit-activity-to_date").val().split("/");
		var fromDate=new Date(from[2],from[1]-1,from[0]);
		var toDate=new Date(to[2],to[1]-1,to[0]);

		var minutes = 1000*60;
		var hours = minutes*60;
		var days = hours*24;

		var diff_date = Math.round((toDate - fromDate)/days);
		//console.log("diff_date="+diff_date)


		$("#edit-activity-from_date").change(function() {
			var from=$(this).val().split("/");
			toDate=new Date(from[2],from[1]-1,from[0]);
			toDate.setDate(toDate.getDate()+diff_date);
			$("#edit-activity-to_date").val($.datepicker.formatDate("dd/mm/yy",toDate));
			//console.log("from date change")
		});
		$("#edit-activity-to_date").change(function() {
			from=$("#edit-activity-from_date").val().split("/");
			to=$("#edit-activity-to_date").val().split("/");
			fromDate=new Date(from[2],from[1]-1,from[0]);
			toDate=new Date(to[2],to[1]-1,to[0]);
			diff_date = Math.round((toDate - fromDate)/days);
		});

	  setTimeout(function() { $("#edit-activity-title").focus() }, 500);
		</script>';
	}
}
?>
<?php
/**
* Add Activity Plan
*
* @param Object $projectInfo
* @param Object $data
* @param JSON String/Object $options
* @return String
*/
function project_info_plan_form($self, $projectInfo, $tranId = NULL, $options = '{}') {
	$projectId = $projectInfo->tpid;
	$formType = SG\getFirst($data->formType,'detail');
	$options = options('project');

	if ($tranId) {
		$data = R::Model('project.calendar.get', array('activityId'=>$tranId));
	} else {
		$data = new stdClass();
		if (post('parent')) $data->parent = post('parent');
	}

	$ret .= '<header class="header -box"><nav class="nav -back"><a class="sg-action" href="javascript:void(0)" data-rel="back"><i class="icon -material">arrow_back</i></a></nav><h3>รายละเอียดกิจกรรม</h3></header>';

	//$ret .= print_o($data,'$data');
	//$ret.=print_o($projectInfo,'$projectInfo');

	if ($formType != 'short') {
		$ret .= '<h3 class="title">'.$mainact->title.'</h3>';
		$ret .= '<h5 class="title -box">'.($data?'แก้ไข':'เพิ่ม').'กิจกรรมย่อย</h5>';
		$ret .= '<div class="box--mainbar--no">'._NL;
	}

	if (is_array($data)) $data = (object) $data;
	if (empty($data->tpid)) $data->tpid = $projectId;

	// Set default value from main activity
	//if (empty($data->mainact)) $data->mainact=$actid;
	if (empty($data->title)) $data->title = $mainact->title;
	if (empty($data->objective)) $data->objective = $mainact->objectiveTitle;
	if (empty($data->detail)) $data->detail = $mainact->desc;
	if (empty($data->budget)) {
		$data->budget = $mainact->budget - $mainact->totalBudget;
		if ($data->budget < 0) $data->budget = 0;
	}
	if (empty($data->targetpreset)) $data->targetpreset = number_format($mainact->target);

	// Set default value from current date
	if (empty($data->from_date)) $data->from_date = date('j/n/Y');
	if (empty($data->to_date)) $data->to_date = $data->from_date;
	if (empty($data->from_time)) $data->from_time = '09:00';
	if (empty($data->to_time)) {
		list($hr,$min) = explode(':',$data->from_time);
		$data->to_time = sprintf('%02d',$hr+1).':'.$min;
	}
	if (empty($data->privacy)) $data->privacy = 'public';

	list(,$month,$year) = explode('/',$data->from_date);





	$form = new Form([
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

			'title' => [
				'type'=>'text',
				'label'=>'ทำอะไร',
				'class'=>'-fill',
				'maxlength'=>255,
				'require'=>true,
				'placeholder'=>'ระบุชื่อกิจกรรม',
				'value'=>htmlspecialchars($data->title)
			],

			'parent' => [
				'type' => 'select',
				'label' => 'ภายใต้กิจกรรม:',
				'class' => '-fill',
				'value' => $data->parent,
				'options' => (function($projectId, $calId = NULL) {
					$options = [''=>'== กิจกรรมระดับบนสุด =='];
					mydb::where('c.`tpid` = :tpid AND a.`parent` IS NULL', ':tpid', $projectId);
					if ($calId) mydb::where('c.`id` != :calid', ':calid', $calId);
					$stmt = 'SELECT a.`trid` `activityId`, `title` FROM %calendar% c LEFT JOIN %project_tr% a ON a.`tpid` = c.`tpid` AND a.`calid` = c.`id` AND a.`formid` = "info" AND a.`part` = "activity" %WHERE%';
					foreach (mydb::select($stmt)->items as $rs) {
						if ($rs->activityId) $options[$rs->activityId] = $rs->title;
					}
					return $options;
				})($projectId, $data->calid),
			],
			'date' => [
				'type' => 'textfield',
				'label' => 'เมื่อไหร่',
				'require' => true,
				'value' => (function($projectInfo, $data) {
					for ($hr = 7; $hr < 24; $hr++) {
						for ($min = 0; $min < 60; $min += 30) {
							$times[] = sprintf('%02d',$hr).':'.sprintf('%02d',$min);
						}
					}

					$minDate = sg_date(SG\getFirst($projectInfo->info->date_from,date('Y-m-d')),'j/n/Y');
					$maxDate = sg_date(SG\getFirst($projectInfo->info->date_end,date('Y-m-d')),'j/n/Y');
					$value = '<input type="text" name="activity[from_date]" id="edit-activity-from_date" maxlength="10" class="sg-datepicker form-text require -date" style="width:6em;" value="'.htmlspecialchars(sg_date($data->from_date,'d/m/Y')).'" data-min-date="'.$minDate.'" data-max-date="'.$maxDate.'" data-diff="edit-activity-to_date"> <select class="form-select" name="activity[from_time]" id="edit-activity-from_time">';
					foreach ($times as $time) {
						$value .= '<option value="'.$time.'"'.($time == $data->from_time?' selected="selected"':'').'>'.$time.'</option>';
					}
					$value .= '</select>
					ถึง <select class="form-select" name="activity[to_time]" id="edit-activity-to_time">';
					foreach ($times as $time) {
						$value .= '<option value="'.$time.'"'.($time == $data->to_time?' selected="selected"':'').'>'.$time.'</option>';
					}
					$value .= '</select>
					<input type="text" name="activity[to_date]" id="edit-activity-to_date" maxlength="10" class="sg-datepicker form-text require -date" style="width:6em;" value="'.htmlspecialchars(sg_date($data->to_date,'d/m/Y')).'" data-min-date="'.sg_date($projectInfo->info->date_from,'j/n/Y').'" data-max-date="'.sg_date($projectInfo->info->date_end,'j/n/Y').'">';
					$value .= ' ('.$minDate.' - '.$maxDate.')';
					return $value;
				})($projectInfo, $data),
			],

			'areacode' => ['type' => 'hidden', 'value' => $data->areacode],
			'latlng' => ['type' => 'hidden', 'value' => $data->latlng],
			'location' => [
				'type' => $formType == 'short'?'hidden':'text',
				'label' => 'ที่ไหน',
				'maxlength' => 255,
				'placeholder' => 'ระบุสถานที่ หมู่ที่ ตำบล',
				'value' => $data->location,
				'class' => 'sg-address -fill',
				'attr' => 'data-altfld="edit-activity-areacode"',
				//'posttext' => ' <a href="javascript:void(0)" id="activity-addmap">แผนที่</a><div id="activity-mapcanvas" class="-hidden"></div>',
			],

			// Budget
			'budget' => $data->childs ?
			[
				'type' => 'hidden',
				'label' => 'งบประมาณที่ตั้งไว้ (บาท)',
				'maxlength' => 11,
				'class' => '-money',
				'placeholder' => '0.00',
				'value' => 0,
			]
			: [
				'type' => 'text',
				'label' => 'งบประมาณที่ตั้งไว้ (บาท)',
				'maxlength' => 11,
				'class' => '-money',
				'placeholder' => '0.00',
				'value' => number_format($data->budget,2,'.',''),
			],
			// TODO : เขียนใหม่
			// (function($projectId, $data, $multipleBudget) {
			// 	$children = [];
			// 	if ($data->childs) {
			// 		$children['budget'] = [
			// 			'type' => 'hidden',
			// 			'label' => 'งบประมาณที่ตั้งไว้ (บาท)',
			// 			'maxlength' => 11,
			// 			'class' => '-money',
			// 			'placeholder' => '0.00',
			// 			'value' => 0,
			// 		];
			// 	} else if ($multipleBudget) {
			// 		$stmt = 'SELECT
			// 			`tpid`,`trid`,`parent`,`gallery` `expcode`
			// 			,`num1` `amt`, `num2` `unitprice`, `num3` `times`, `num4` `total`
			// 			,`detail1` `unitname`, `text1` `detail`
			// 			FROM %project_tr%
			// 			WHERE `tpid` = :tpid AND `formid` = "develop" AND `part` = "exptr" AND `calid` = :calid ';
			// 		foreach (mydb::select($stmt,':tpid',$projectId,':calid',$data->calid)->items as $item) {
			// 			$expTr[$item->expcode] = $item;
			// 		}
			// 		$expCategoty = model::get_category('project:expcode');
			// 		$tables = new Table();
			// 		$tables->thead = array('ประเภทรายจ่าย/รายละเอียด','รวมเงิน (บาท)');
			// 		foreach ($expCategoty as $expCode => $expName) {
			// 			$exp = $expTr[$expCode];
			// 			//$ret.=print_o($exp,'$exp');
			// 			$tables->rows[] = [
			// 				'<input type="hidden" name="exp['.$expCode.'][expid]" value="'.$exp->trid.'" type="hidden" />'
			// 				.'<b>'.$expName.' :</b><br />'
			// 				.'<textarea name="exp['.$expCode.'][detail]" rows="2" cols="50" placeholder="ระบุรายละเอียดค่าใช้จ่าย" style="width:95%;">'.htmlspecialchars($exp->detail).'</textarea>',
			// 				//.'<input type="text" name="exp['.$expCode.'][detail]" value="'.htmlspecialchars($exp->detail).'" size="50" placeholder="ระบุรายละเอียดค่าใช้จ่าย" style="width:95%;" />',
			// 				'<br /><input type="text" name="exp['.$expCode.'][total]" size="10" value="'.$exp->total.'" style="text-align:center; margin:0 auto; display:block;" />',
			// 			];
			// 		}
			// 		$tables->tfoot[] = ['รวมงบประมาณที่ตั้งไว้',$data->budget];
			// 		$children[] = $tables->build();
			// 	} else {
			// 		debugMsg('HERE');
			// 		$children['budget'] = [
			// 			'type' => 'text',
			// 			'label' => 'งบประมาณที่ตั้งไว้ (บาท)',
			// 			'maxlength' => 11,
			// 			'class' => '-money',
			// 			'placeholder' => '0.00',
			// 			'value' => number_format($data->budget,2,'.',''),
			// 		];
			// 	}
			// 	return ['children' => $children];
			// })($projectId, $data, $options->multipleBudget),

			// Multiple Target
			$options->multipleTarget ? [
				'label' => 'กลุ่มเป้าหมาย/ผู้มีส่วนร่วม/ผู้สนับสนุนที่เข้าร่วมกิจกรรม',
				'type' => 'textfield',
				'value' => (function($data) {
					debugMsg($data, '$data');
					$joinListTable = new Table();
					$joinListTable->thead = ['กลุ่มเป้าหมาย','amt'=>'จำนวนคน'];
					$joinListTable->rows[] = ['<td class="subheader" colspan="2">กลุ่มเป้าหมายที่เข้าร่วม'];
					foreach (cfg('project.target') as $key => $value) {
						$joinListTable->rows[] = [
							$value,
							'<input class="form-text -numeric" type="text" name="activity['.$key.']" size="5" value="'.$data->{'targt_'.$key}.'" /> คน'
						];
					}
					$joinListTable->rows[] = ['<td class="subheader" colspan="2">ผู้มีส่วนร่วม/ผู้สนับสนุน'];
					foreach (cfg('project.support') as $key => $value) {
						$joinListTable->rows[] = [
							$value,
							'<input class="form-text -numeric" type="text" name="activity['.$key.']" size="5" value="'.$data->{'targt_'.$key}.'" /> คน'
						];
					}
					return $joinListTable->build();
				})($data),
			] : NULL,

			// Single Target
			'targetpreset' => !$options->multipleTarget ? [
				'type' => 'text',
				'label' => 'กลุ่มเป้าหมาย (คน)',
				'maxlength' => 5,
				'class' => '-numeric',
				'placeholder' => '0',
				'value' => number_format($data->targetpreset,0,'',''),
			] : NULL,
			'targetdetail' => !$options->multipleTarget ? [
				'type' => 'textarea',
				'label' => 'รายละเอียดกลุ่มเป้าหมาย',
				'class' => '-fill',
				'rows' => 3,
				'value' => $data->target,
			] : NULL,

			'detail' => [
				'type' => $formType == 'short'?'hidden':'textarea',
				'label' => 'รายละเอียดกิจกรรมตามแผน',
				'rows' => 5,
				'class' => '-fill',
				'placeholder' => 'ระบุรายละเอียดของกิจกรรมที่วางแผนว่าจะทำ',
				'value' => $data->detail,
			],
			'color' => [
				'type' => 'colorpicker',
				'label' => 'สีของกิจกรรม',
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
	]);

	// if ($para->module) $form=do_class_method($para->module.'.extension','calendar_form', $form, $data, $para);

	$ret .= $form->build();


	//$ret.=print_o($data,'$data').print_o($mainact,'mainact');
	//$ret.=print_o($project,'$project');

	if ($formType != 'short') {
		$ret .= '</div>';
	}

	$ret .= '<script type="text/javascript">
	var from=$("#edit-activity-from_date").val().split("/");
	var to=$("#edit-activity-to_date").val().split("/");
	var fromDate=new Date(from[2],from[1]-1,from[0]);
	var toDate=new Date(to[2],to[1]-1,to[0]);

	var minutes = 1000*60;
	var hours = minutes*60;
	var days = hours*24;

	var diff_date = Math.round((toDate - fromDate)/days);
	//console.log("diff_date="+diff_date)


	$("#edit-activity-from_date").change(function() {
		var from=$(this).val().split("/");
		toDate=new Date(from[2],from[1]-1,from[0]);
		toDate.setDate(toDate.getDate()+diff_date);
		$("#edit-activity-to_date").val($.datepicker.formatDate("dd/mm/yy",toDate));
		//console.log("from date change")
	});
	$("#edit-activity-to_date").change(function() {
		from=$("#edit-activity-from_date").val().split("/");
		to=$("#edit-activity-to_date").val().split("/");
		fromDate=new Date(from[2],from[1]-1,from[0]);
		toDate=new Date(to[2],to[1]-1,to[0]);
		diff_date = Math.round((toDate - fromDate)/days);
	});

  setTimeout(function() { $("#edit-activity-title").focus() }, 500);
	</script>';
	return $ret;
}
?>