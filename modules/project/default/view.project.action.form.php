<?php
/**
* Module Method
* Created 2019-09-01
* Modify  2019-09-20
*
* @param Object $projectInfo
* @param Int $activityId
* @param Object $data
* @param Object $options
* @return String
*/

$debug = true;

function view_project_action_form($projectInfo, $activityId = NULL, $data = NULL, $options = '{}') {
	$defaults = '{debug:false, moneyform: "row"}';
	$options = sg_json_decode($options, $defaults);

	$isAdmin = user_access('administer projects');
	$tpid = $projectInfo->tpid;

	$activityOption = $projectInfo->settings->activity;
	$showFields = new stdClass();
	if ($activityOption->field) {
		foreach (explode(',', $activityOption->field) as $item) $showFields->{trim($item)} = true;
	}

	//$mainActivity=project_model::get_main_activity($tpid);

	if (is_null($data)) $data = (Object) [];
	else if (is_array($data)) $data = (Object) $data;
	$data->part = 'owner'; // owner,trainer
	//$ret .= print_o($data,'$data');
	$form = new Form('action', url('project/info/api/'.$tpid.'/action.save/'.$data->actionId), 'action-post', 'sg-form -action');
	$form->addData('checkValid', true);
	if ($options->ret) {
		$form->addData('rel', '#main');
		$form->addData('ret', $options->ret);
		$form->addData('done', 'close | moveto:#project-action-'.$data->actionId);
	} else {
		if ($data->actionId) {
			$form->addData('rel', 'notify');
		} else {
			$form->addData('rel', 'after:#action-top');
		}
		$form->addData('done', 'close | '.($data->actionId ? 'load->replace:#project-action-'.$data->actionId.':'.url('project/'.$tpid.'/action.view/'.$data->actionId) : 'moveto:#action-top').' | moveto:#project-action-'.$data->actionId.' | load->replace:#project-action-plan:'.url('project/'.$tpid.'/action.plan'));
	}

	if ($options->rel) $form->addData('rel', $options->rel);
	if ($options->done) $form->addData('done', $options->done);

	//$form->config->attr = 'autosave="'.url('project/edit/actionautosave/'.$tpid).'"';
	$form->addText('<header class="header -box">'._HEADER_BACK.'<h3>บันทึกกิจกรรม</h3></header>');

	$form->addField('actionId', array('type' => 'hidden', 'value' => $data->actionId));
	$form->addField('calid', array('type' => 'hidden', 'value' => $data->calid));
	$form->addField('activityId', array('type' => 'hidden', 'value' => $data->activityId));
	//$form->addField('ret', array('type' => 'hidden', 'name' => 'ret', 'value' => $options->ret ? $options->ret : 'project/'.$tpid.'/info.action'));
	$form->addField('rate', array('type' => 'hidden', 'value' => -1));

	$form->addField('part', array('type' => 'hidden', 'value' => $data->part));

	$form->addField('date1', array('type' => 'hidden', 'value' => htmlspecialchars(SG\getFirst($data->date1,date('Y-m-d')))));


	$form->addText('<section class="">');
	$form->addText((new ListTile([
		'class' => 'form-item',
		'title' => '<h3>รายละเอียดกิจกรรมที่ทำ</h3>',
		'leading' => '<i class="icon -material">rule</i>',
		]))->build()
	);

	if ($data->serieNo) $form->addText('<div class="form-item">รุ่น : '.$data->serieNo.'</b></div>');

	$form->addField(
		'title',
		array(
			'type' => 'text',
			'label' => 'ชื่อกิจกรรม',
			'class' => '-fill',
			'require' => true,
			'placeholder' => 'ระบุชื่อกิจกรรม',
			'value' => htmlspecialchars($data->title),
		)
	);

	$form->addField(
		'actionDate',
		array(
			'type' => 'text',
			'label' => 'วันที่ปฎิบัติ',
			'require' => true,
			'class' => 'sg-datepicker -date',
			'readonly' => true,
			'value' => htmlspecialchars(sg_date(SG\getFirst($data->actionDate, date('Y-m-d')), 'd/m/Y')),
		)
	);

	if ($showFields->objectiveDetail) {
		$form->addField(
			'objectiveDetail',
			array(
				'type' => 'text',
				'label' => 'วัตถุประสงค์ของกิจกรรม',
				'class' => '-fill',
				'description' => 'ระบุรายละเอียดวัตถุประสงค์ของกิจกรรมนี้',
				'placeholder' => 'ระบุรายละเอียดวัตถุประสงค์ของกิจกรรมนี้',
				'value' => $data->objectiveDetail,
			)
		);
	}

	if ($showFields->targetJoin) {
		$form->addField(
			'targetJoinAmt',
			array(
				'type' => 'text',
				'label' => 'จำนวนคน/ผู้เข้าร่วมกิจกรรมจริง',
				'class' => '-numeric',
				'maxlength' => 5,
				'placeholder' => '0',
				'value' => htmlspecialchars(number_format($data->targetJoinAmt,0)),
			)
		);

		$form->addField(
			'targetJoinDetail',
			array(
				'type' => 'textarea',
				'label' => 'รายละเอียดกลุ่มเป้าหมายที่เข้าร่วม',
				'class' => '-fill',
				'rows' => 3,
				'description' => 'ระบุรายละเอียดของกลุ่มเป้าหมายที่เข้าร่วมจริง เช่น กลุ่ม ภาคี จำนวนคน',
				'placeholder' => 'ระบุรายละเอียดของกลุ่มเป้าหมายที่เข้าร่วมจริง เช่น กลุ่ม ภาคี จำนวนคน',
				'value' => $data->targetJoinDetail,
			)
		);
	}

	$form->addField(
		'actionReal',
		array(
			'type' => 'textarea',
			'label' => 'รายละเอียดขั้นตอน กระบวนการ',
			'class' => '-fill',
			'rows' => 4,
			'require' => true,
			'value' => $data->actionReal,
			'description' => 'ระบุรายละเอียดขั้นตอน กระบวนการ ที่ได้ดำเนินการ',
			'placeholder' => 'ระบุรายละเอียดขั้นตอน กระบวนการ ที่ได้ดำเนินการ',
		)
	);

	$form->addField(
		'outputOutcomeReal',
		array(
			'type' => 'textarea',
			'label' => 'ผลผลิต (Output) / ผลลัพธ์ (Outcome) ที่เกิดขึ้นจริง',
			'class' => '-fill',
			'rows' => 8,
			'require' => true,
			'value' => $data->outputOutcomeReal,
			'description' => 'กรุณาระบุเนื้อหา/ข้อสรุปสำคัญต่าง ๆ จากกิจกรรม ที่สามารถนำมาขยายผลต่อได้ เช่น ความรู้ กลุ่มแกนนำ แผนงานต่าง ๆ และผลที่ได้จากกิจกรรม อาทิ พฤติกรรม หรือสิ่งที่เกิดขึ้นภายหลังกิจกรรม เช่น การรวมกลุ่มทำกิจกรรมต่อเนื่อง (ซึ่งจะทราบได้จากการติดตามประเมินผลของโครงการ)',
			'placeholder' => 'กรุณาระบุเนื้อหา/ข้อสรุปสำคัญต่าง ๆ จากกิจกรรม ที่สามารถนำมาขยายผลต่อได้ เช่น ความรู้ กลุ่มแกนนำ แผนงานต่าง ๆ และผลที่ได้จากกิจกรรม อาทิ พฤติกรรม หรือสิ่งที่เกิดขึ้นภายหลังกิจกรรม เช่น การรวมกลุ่มทำกิจกรรมต่อเนื่อง (ซึ่งจะทราบได้จากการติดตามประเมินผลของโครงการ)',
		)
	);

	if ($showFields->rate1) {
		$form->addField(
			'rate1',
			array(
				'type' => 'radio',
				'label' => 'ประเมินผล คุณภาพกิจกรรม',
				'options' => array('4'=>'4=บรรลุผลมากกว่าเป้าหมาย', '3'=>'3=บรรลุผลตามเป้าหมาย', '2'=>'2=เกือบได้ตามเป้าหมาย', '1'=>'1=ได้น้อยกว่าเป้าหมายมาก','-1'=>'0=ไม่สามารถประเมินได้'),
				'value' => $data->rate1,
			)
		);
	}


	if ($showFields->problem) {
		$form->addField(
			'problem',
			array(
				'type' => 'textarea',
				'label' => 'ปัญหา/แนวทางแก้ไข',
				'class' => '-fill',
				'rows' => 5,
				'placeholder' => 'ระบุปัญหา และ แนวทางการพัฒนาครั้งต่อไป',
				'value' => $data->problem,
			)
		);
	}

	if ($data->part=='owner') {
		if ($showFields->recommendation) {
			$form->addField(
				'recommendation',
				array(
					'type' => 'textarea',
					'label' => 'ข้อเสนอแนะต่อ '.$projectInfo->settings->grant->by.' (ระบุเป็นข้อ)',
					'class' => '-fill',
					'rows' => 3,
					'placeholder' => 'ระบุข้อเสนอแนะต่อ '.$projectInfo->settings->grant->by.' (ระบุเป็นข้อ)',
					'value' => $data->recommendation,
				)
			);
		}

		if ($showFields->support) {
			$form->addField(
				'support',
				array(
					'type' => 'textarea',
					'label' => 'ความต้องการสนับสนุนจากพี่เลี้ยงและ '.$projectInfo->settings->grant->pass.' (ระบุเป็นข้อ)',
					'class' => '-fill',
					'rows' => 3,
					'placeholder' => 'ระบุความต้องการสนับสนุนจากพี่เลี้ยงและ '.$projectInfo->settings->grant->pass.' (ระบุเป็นข้อ)',
					'value' => $data->support,
				)
			);
		}

		if ($showFields->followerRecommendation) {
			$form->addField(
				'followerRecommendation',
				array(
					'type' => 'textarea',
					'label' => 'คำแนะนำจากเจ้าหน้าที่ติดตามในพื้นที่',
					'class' => '-fill',
					'rows' => 5,
					'value' => $data->followerRecommendation,
					'placeholder' => 'ระบุรายละเอียดคำแนะนำจากเจ้าหน้าที่ติดตามในพื้นที่'
				)
			);
		}

		if ($showFields->followerName) {
			$form->addField(
				'followerName',
				array(
					'type' => 'text',
					'label' => 'ชื่อผู้ติดตามในพื้นที่ของ '.$projectInfo->settings->grant->by,
					'class' => '-fill',
					'value' => htmlspecialchars($data->followerName),
					'placeholder' => 'ระบุชื่อผู้ติดตามในพื้นที่ของ '.$projectInfo->settings->grant->by,
				)
			);
		}
	} else {
		if ($showFields->recommendation) {
			$form->addField(
				'recommendation',
				array(
					'type' => 'textarea',
					'label' => 'ข้อเสนอแนะต่อพื้นที่',
					'class' => '-fill',
					'rows' => 3,
					'placeholder' => 'ระบุข้อเสนอแนะต่อพื้นที่ (ระบุเป็นข้อ)',
					'value' => $data->recommendation,
				)
			);
		}

		if ($showFields->followerRecommendation) {
			$form->addField(
				'followerRecommendation',
				array(
					'type' => 'textarea',
					'label' => 'ข้อเสนอแนะต่อ '.$projectInfo->settings->grant->by,
					'class' => '-fill',
					'rows' => 3,
					'placeholder' => 'ระบุข้อเสนอแนะต่อ '.$projectInfo->settings->grant->by.' (ระบุเป็นข้อ)',
					'value' => $data->followerRecommendation,
				)
			);
		}
	}


	$form->addText('</section>');


	$form->addText('<section class="">');



	if ($data->part == 'owner') {
		$form->addText((new ListTile([
			'class' => 'form-item',
			'title' => '<h3>รายงานสรุปการใช้เงิน</h3>',
			'leading' => '<i class="icon -material">paid</i>',
			]))->build()
		);

		$form->addText('<div class="form-item">งบประมาณที่ตั้งไว้ <b>' . number_format($data->budgetPreset,2).'</b> บาท</div>');

		$tableTypeRow = $options->moneyform == 'row';
		$tables = new Table();
		$tables->addClass('project-money-send');
		if ($tableTypeRow) {
			$tables->thead = array('ประเภทรายจ่าย', 'จำนวนเงิน');
		} else {
			$tables->thead = '<thead><tr><th colspan="6">ประเภทรายจ่าย</th><th rowspan="2">รวมรายจ่าย</th></tr><tr><th>ค่าตอบแทน</th><th>ค่าจ้าง</th><th>ค่าใช้สอย</th><th>ค่าวัสดุ</th><th>ค่าสาธารณูปโภค</th><th>อื่น ๆ</th></tr></thead>';
		}

		if ($data->flag == _PROJECT_LOCKREPORT) {
			$tables->rows[] = array(
				$data->exp_meed.'<input size="10"  name="action[exp_meed]" id="edit-action-exp_meed" class="form-text require" type="hidden" readonly="readonly" value="'.htmlspecialchars($data->exp_meed).'" />',
				$data->exp_wage.'<input size="10"  name="action[exp_wage]" id="edit-action-exp_wage" class="form-text require" type="hidden" value="'.htmlspecialchars($data->exp_wage).'" />',
				$data->exp_supply.'<input size="10"  name="action[exp_supply]" id="edit-action-exp_supply" class="form-text require" type="hidden" value="'.htmlspecialchars($data->exp_supply).'" />',
				$data->exp_material.'<input size="10"  name="action[exp_material]" id="edit-action-exp_material" class="form-text require" type="hidden" value="'.htmlspecialchars($data->exp_material).'" />',
				$data->exp_utilities.'<input size="10"  name="action[exp_utilities]" id="edit-action-exp_utilities" class="form-text require" type="hidden" value="'.htmlspecialchars($data->exp_utilities).'" />',
				$data->exp_other.'<input size="10"  name="action[exp_other]" id="edit-action-exp_other" class="form-text require" type="hidden" value="'.htmlspecialchars($data->exp_other).'" />',
				$data->exp_total.'<input size="10"  name="action[exp_total]" id="edit-action-exp_total" class="form-text require" type="hidden" value="'.htmlspecialchars($data->exp_total).'" />',
			);
		} else {
			if ($tableTypeRow) {
				$tables->rows[] = array(
					'ค่าตอบแทน',
					'<input size="10"  name="action[exp_meed]" id="edit-action-exp_meed" class="form-text require" type="text" value="'.htmlspecialchars(SG\getFirst($data->exp_meed,0)).'" placeholder="0.00" autocomplete="off" />'
				);
				$tables->rows[] = array(
					'ค่าจ้าง',
					'<input size="10"  name="action[exp_wage]" id="edit-action-exp_wage" class="form-text require" type="text" value="'.htmlspecialchars(SG\getFirst($data->exp_wage,0)).'" placeholder="0.00" autocomplete="off" />'
				);
				$tables->rows[] = array(
					'ค่าใช้สอย',
					'<input size="10"  name="action[exp_supply]" id="edit-action-exp_supply" class="form-text require" type="text" value="'.htmlspecialchars(SG\getFirst($data->exp_supply,0)).'" placeholder="0.00" autocomplete="off" />'
				);
				$tables->rows[] = array(
					'ค่าวัสดุ',
					'<input size="10"  name="action[exp_material]" id="edit-action-exp_material" class="form-text require" type="text" value="'.htmlspecialchars(SG\getFirst($data->exp_material,0)).'" placeholder="0.00" autocomplete="off" />'
				);
				$tables->rows[] = array(
					'ค่าสาธารณูปโภค',
					'<input size="10"  name="action[exp_utilities]" id="edit-action-exp_utilities" class="form-text require" type="text" value="'.htmlspecialchars(SG\getFirst($data->exp_utilities,0)).'" placeholder="0.00" autocomplete="off" />'
				);
				$tables->rows[] = array(
					'อื่น ๆ',
					'<input size="10"  name="action[exp_other]" id="edit-action-exp_other" class="form-text require" type="text" autocomplete="off" value="'.htmlspecialchars(SG\getFirst($data->exp_other,0)).'" placeholder="0.00" />'
				);
				$tables->rows[] = array(
					'รวมรายจ่าย',
					'<input size="10"  name="action[exp_total]" id="edit-action-exp_total" class="form-text require" type="text" value="'.htmlspecialchars(SG\getFirst($data->exp_total,0)).'" placeholder="0.00" />'
				);

			} else {
				$tables->rows[] = array(
					'<input size="10"  name="action[exp_meed]" id="edit-action-exp_meed" class="form-text require" type="text" value="'.htmlspecialchars(SG\getFirst($data->exp_meed,0)).'" placeholder="0.00" />',
					'<input size="10"  name="action[exp_wage]" id="edit-action-exp_wage" class="form-text require" type="text" value="'.htmlspecialchars(SG\getFirst($data->exp_wage,0)).'" placeholder="0.00" />',
					'<input size="10"  name="action[exp_supply]" id="edit-action-exp_supply" class="form-text require" type="text" value="'.htmlspecialchars(SG\getFirst($data->exp_supply,0)).'" placeholder="0.00" />',
					'<input size="10"  name="action[exp_material]" id="edit-action-exp_material" class="form-text require" type="text" value="'.htmlspecialchars(SG\getFirst($data->exp_material,0)).'" placeholder="0.00" />',
					'<input size="10"  name="action[exp_utilities]" id="edit-action-exp_utilities" class="form-text require" type="text" value="'.htmlspecialchars(SG\getFirst($data->exp_utilities,0)).'" placeholder="0.00" />',
					'<input size="10"  name="action[exp_other]" id="edit-action-exp_other" class="form-text require" type="text" value="'.htmlspecialchars(SG\getFirst($data->exp_other,0)).'" placeholder="0.00" />',
					'<input size="10"  name="action[exp_total]" id="edit-action-exp_total" class="form-text require" type="text" value="'.htmlspecialchars(SG\getFirst($data->exp_total,0)).'" placeholder="0.00" />',
				);
			}
		}

		$form->addText($tables->build());

	} else {
		$form->addText(
			'<input type="hidden" name="action[exp_meed]" id="edit-action-exp_meed" value="0" />'
			.'<input type="hidden" name="action[exp_wage]" id="edit-action-exp_wage" value="0" />'
			.'<input type="hidden" name="action[exp_supply]" id="edit-action-exp_supply" value="0" />'
			.'<input type="hidden" name="action[exp_material]" id="edit-action-exp_material" value="0" />'
			.'<input type="hidden" name="action[exp_utilities]" id="edit-action-exp_utilities" value="0" />'
			.'<input type="hidden" name="action[exp_other]" id="edit-action-exp_other" value="0" />'
			.'<input type="hidden" name="action[exp_total]" id="edit-action-exp_total" value="0" />'
		);
	}

	$form->addText('</section>');


	$form->addField(
		'save',
		array(
			'type' => 'button',
			'value' => '<i class="icon -save -white"></i><span>บันทึกกิจกรรม</span>',
			'container' => array('class'=>'-sg-text-right'),
		)
	);


	$form->photoremark = '<strong>หมายเหตุ : ภาพถ่ายประกอบกิจกรรมหรือไฟล์รายงานรายละเอียดประกอบกิจกรรม สามารถส่งเพิ่มเติมได้หลังจากบันทึกข้อมูลเสร็จเรียบร้อยแล้ว</strong>';

	$ret .= $form->build();

	//$ret .= print_o($data,'$data');
	//$ret .= print_o($projectInfo,'$projectInfo');

	$ret .= '<style type="text/css">
	.form.-action h4 {padding:8px;background-color:#ddd;}
	</style>';

	$ret .= '<script type="text/javascript">
	$(document).ready(function() {
		var errorCount=0;
		var isChange=false;
		var $form=$("form#action-post");
		var autoSave='.SG\getFirst(post('r'),cfg('project.action.autosave'),120).';
		var formSubmit = false;
		var debug = false

		$(".project-action-money .form-text").change(function() {
			var amt=$(this).val().replace(/[^0-9.]/g, "");
			var total=0;
			if (amt.indexOf(".") == -1) amt=amt+".00";
			$(this).val(amt);
			$(".project-action-money .form-text").each(function(i,data) {
				if ($(data).attr("name")!="action[exp_total]") {
					var m=$(data).val();
					if (m!="") total+=parseFloat(m);
				}
			});
			$("#edit-action-exp_total").val(total.toFixed(2));
			notify(total);
		});

		$form.find(".form-text, .form-textarea").keypress(function(e) {
			isChange=true;
		});

		$(".form-text, .form-checkbox").keypress(function(e) {
			if (e.which == 13) return false;
		});

		$("#edit-action-date2")
		.datepicker({
			clickInput:true,
			dateFormat: "dd/mm/yy",
			altFormat: "yy-mm-dd",
			altField: "#edit-action-date1",
			disabled: false,
			monthNames: thaiMonthName,
		});


		$("#edit-action-exp_total").attr("readonly", true).css({"font-weight": "bold"});
	/*	$("#edit-action-exp_total").attr("disabled", "disabled").css({"background-color": "#eeeeee", "font-weight": "bold"}); */
		$(".project-money-send .form-text").change(function() {
			var amt=$(this).val().replace(/[^0-9.]/g, "");
			var total=0;
			if (amt.indexOf(".") == -1) amt=amt+".00";
			$(this).val(amt);
			$(".project-money-send .form-text").each(function(i,data) {
				if ($(data).attr("name")!="action[exp_total]") {
					var m=$(data).val();
					if (m!="") total+=parseFloat(m);
				}
			});
			$("#edit-action-exp_total").val(total.toFixed(2));
		});

		// ทุก ๆ x นาที ให้บันทึกข้อมูลอัตโนมัติ
		if (autoSave) {
			$(function () {
				(function autoSaveData() {
					if (isChange) {
						$.post($form.attr("autosave"),$form.serialize(true), function(data) {
							formSubmit=data.error?false:true;
							if (debug) notify(data.msg,30000); else notify(data.msg);
							isChange=false;
							if (data.actionId) $("#edit-action-actionId").val(data.actionId);
						},"json");
					}
					//calling the anonymous function after 1000 milli seconds
					setTimeout(autoSaveData, autoSave*1000);  //second
				})(); //self Executing anonymous function
			});
		}


		$form.submit(function() {
			return;
			var error=false;
			var $obj;
			var fld;
			var fldCheck=[
				["edit-action-date1","วันที่"],
				["edit-action-actionname","กิจกรรม"],
				["edit-action-detail3","วัตถุประสงค์ย่อย"],
				["edit-action-text3","รายละเอียดกลุ่มเป้าหมายที่ตั้งไว้ตามแผนงาน"],
				["edit-action-text9","รายละเอียดกลุ่มเป้าหมายที่เข้าร่วม"],
				["edit-action-text2","รายละเอียดขั้นตอน กระบวนการ กิจกรรมปฎิบัติจริง"],
				["edit-action-text4","ผลสรุปที่สำคัญของกิจกรรม"],
				["edit-action-exp_meed","ค่าตอบแทน"],
				["edit-action-exp_wage","ค่าจ้าง"],
				["edit-action-exp_supply","ค่าใช้สอย"],
				["edit-action-exp_material","ค่าวัสดุ"],
				["edit-action-exp_utilities","ค่าสาธารณูปโภค"],
				["edit-action-exp_other","อื่น ๆ"],
				["edit-action-exp_total","รวมรายจ่าย"],
			];
			for (fld in fldCheck) {
				if (fldCheck[fld][0]=="action[rate1]") {
					if (!$("input[name=\'"+fldCheck[fld][0]+"\']:checked").val()) {
						error=fld;
						break;
					}
				} else if ($("#"+fldCheck[fld][0]).val().trim()=="") {
					error=fld;
					break;
				}
			}
			if (error) {
				// Auto save some data
				if (autoSave) {
					$.post($form.attr("autosave"),$form.serialize(true), function(data) {
						isChange=false;
						if (data.actionId) $("#edit-action-actionId").val(data.actionId);
	//					notify(data.debug);
					},"json");
				}
				// Notification and return to form
				var errorMsg="กรุณาป้อน \""+fldCheck[error][1]+"\"";
				if (errorCount>10) alert(errorMsg); else notify(errorMsg,10000);
				$("#"+fldCheck[error][0]).focus();
				++errorCount;
				formSubmit=false;
				return false;
			} else if (!formSubmit) {
				notify("กำลังตรวจสอบความถูกต้อง");
				$.post($form.attr("autosave"),$form.serialize(true), function(data) {
					if (data.actionId) $("#edit-action-actionId").val(data.actionId);
					if (data.error) notify("Error : "+data.error);
					formSubmit=data.error?false:true;
					if (formSubmit) $form.submit();
	//					notify(data.debug);
				},"json");
			}
			if (formSubmit) {
				return true;
			} else {
				// Check form submit
				return false;
			}
		});
	 });
	</script>';
	return $ret;
}
?>