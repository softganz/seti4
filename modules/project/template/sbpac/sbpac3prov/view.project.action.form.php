<?php
/**
* Show Project Action Form
*
* @param Object $projectInfo
* @param Int $activityId
* @param Object $data
* @param Object $options
* @return String
*/

$debug = true;

function view_project_action_form($projectInfo, $activityId = NULL, $data = NULL, $options = '{}') {
	$defaults = '{debug:false}';
	$options = sg_json_decode($options, $defaults);

	$isAdmin = user_access('administer projects');
	$tpid = $projectInfo->tpid;

	//$mainActivity=project_model::get_main_activity($tpid);

	$data->part = 'owner';
	$ret .= '<h2 class="title -box">บันทึกกิจกรรม</h2>';

	$form = new Form('action', url('project/info/api/'.$tpid.'/action.save/'.$activityId), 'action-post', 'sg-form -action');
	$form->addData('checkValid', true);
	$form->addData('complete', 'closebox');
	//$form->addData('rel', '#main');
	//$form->addData('ret', $options->ret ? $options->ret : url('paper/'.$tpid.'/owner'));
	//$form->config->attr = 'autosave="'.url('project/edit/actionautosave/'.$tpid).'"';

	$form->addField('actionId', array('type' => 'hidden', 'value' => $data->actionId));
	$form->addField('calid', array('type' => 'hidden', 'value' => $data->calid));
	$form->addField('activityId', array('type' => 'hidden', 'value' => $data->activityId));
	//$form->addField('ret', array('type' => 'hidden', 'name' => 'ret', 'value' => $options->ret ? $options->ret : 'paper/'.$tpid.'/owner'));

	$form->addField('part', array('type' => 'hidden', 'value' => $data->part));

	$form->addField('date1', array('type' => 'hidden', 'value' => htmlspecialchars(SG\getFirst($data->date1,date('Y-m-d')))));


	$form->addText('<section class="box"><h3>รายละเอียดกิจกรรมที่ทำ</h3>');

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
						'class' => 'sg-datepicker',
						'readonly' => true,
						'value' => htmlspecialchars(sg_date(SG\getFirst($data->actionDate, date('Y-m-d')), 'd/m/Y')),
						)
					);

	$form->addField(
					'targetJoinAmt',
					array(
						'type' => 'text',
						'label' => 'จำนวนผู้เข้าร่วมกิจกรรม (คน)',
						'class' => '-numeric',
						'maxlength' => 5,
						'placeholder' => '0',
						'value' => $data->targetJoinAmt ? htmlspecialchars(number_format($data->targetJoinAmt,0)) : ''
					)
				);

	$form->addField(
					'targetJoinDetail',
					array(
						'type' => 'textarea',
						'label' => 'รายละเอียดกลุ่มเป้าหมายที่เข้าร่วม',
						'class' => '-fill',
						'rows' => 3,
						'value' => $data->targetJoinDetail,
						'description' => 'ระบุรายละเอียดของกลุ่มเป้าหมายที่เข้าร่วมจริง เช่น กลุ่ม ภาคี จำนวนคน',
						'placeholder' => 'ระบุรายละเอียดของกลุ่มเป้าหมายที่เข้าร่วมจริง เช่น กลุ่ม ภาคี จำนวนคน',
					)
				);

	$form->addField(
					'actionReal',
					array(
						'type' => 'textarea',
						'label' => 'กิจกรรมที่ปฎิบัติ รายละเอียดขั้นตอน กระบวนการ',
						'class' => '-fill',
						'rows' => 6,
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
						'rows' => 15,
						'require' => true,
						'value' => $data->outputOutcomeReal,
						'description' => 'กรุณาระบุเนื้อหา/ข้อสรุปสำคัญต่าง ๆ จากกิจกรรม ที่สามารถนำมาขยายผลต่อได้ เช่น ความรู้ กลุ่มแกนนำ แผนงานต่าง ๆ และผลที่ได้จากกิจกรรม อาทิ พฤติกรรม หรือสิ่งที่เกิดขึ้นภายหลังกิจกรรม เช่น การรวมกลุ่มทำกิจกรรมต่อเนื่อง (ซึ่งจะทราบได้จากการติดตามประเมินผลของโครงการ)',
						'placeholder' => 'กรุณาระบุเนื้อหา/ข้อสรุปสำคัญต่าง ๆ จากกิจกรรม ที่สามารถนำมาขยายผลต่อได้ เช่น ความรู้ กลุ่มแกนนำ แผนงานต่าง ๆ และผลที่ได้จากกิจกรรม อาทิ พฤติกรรม หรือสิ่งที่เกิดขึ้นภายหลังกิจกรรม เช่น การรวมกลุ่มทำกิจกรรมต่อเนื่อง (ซึ่งจะทราบได้จากการติดตามประเมินผลของโครงการ)',
					)
				);

	for ($i = 0; $i <= 100; $i = $i + 10) $rateOtions[$i] = $i.'%';
	$form->addField(
						'rate1',
						array(
							'type' => 'radio',
							'label' => 'ประเมินความสำเร็จของการดำเนินกิจกรรม',
							'options' => $rateOtions,
							'value' => $data->rate1,
							'require' => true,
							'display' => 'inline-block',
						)
					);

	$form->addField(
						'rate2',
						array(
							'type' => 'radio',
							'label' => 'ประเมินความสำเร็จของการดำเนินกิจกรรม โดยผู้ติดตามโครงการ',
							'options' => $rateOtions,
							'value' => $data->rate2,
							'require' => true,
							'display' => 'inline-block',
						)
					);

	$form->addText('</section>');


	$form->addText('<section class="box">');



	if ($data->part == 'owner') {
		$form->addText('<h3>รายงานสรุปการใช้เงิน</h3>');

		$form->addText('งบประมาณที่ตั้งไว้ <b>' . number_format(htmlspecialchars($data->budgetPreset),2).'</b> บาท');

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
													'<input size="10"  name="action[exp_meed]" id="edit-action-exp_meed" class="form-text require" type="text" value="'.htmlspecialchars($data->exp_meed).'" placeholder="0.00" autocomplete="off" />'
												);
				$tables->rows[] = array(
													'ค่าจ้าง',
													'<input size="10"  name="action[exp_wage]" id="edit-action-exp_wage" class="form-text require" type="text" value="'.htmlspecialchars($data->exp_wage).'" placeholder="0.00" autocomplete="off" />'
												);
				$tables->rows[] = array(
													'ค่าใช้สอย',
													'<input size="10"  name="action[exp_supply]" id="edit-action-exp_supply" class="form-text require" type="text" value="'.htmlspecialchars($data->exp_supply).'" placeholder="0.00" autocomplete="off" />'
												);
				$tables->rows[] = array(
													'ค่าวัสดุ',
													'<input size="10"  name="action[exp_material]" id="edit-action-exp_material" class="form-text require" type="text" value="'.htmlspecialchars($data->exp_material).'" placeholder="0.00" autocomplete="off" />'
												);
				$tables->rows[] = array(
													'ค่าสาธารณูปโภค',
													'<input size="10"  name="action[exp_utilities]" id="edit-action-exp_utilities" class="form-text require" type="text" value="'.htmlspecialchars($data->exp_utilities).'" placeholder="0.00" autocomplete="off" />'
												);
				$tables->rows[] = array(
													'อื่น ๆ',
													'<input size="10"  name="action[exp_other]" id="edit-action-exp_other" class="form-text require" type="text" autocomplete="off" value="'.htmlspecialchars($data->exp_other).'" placeholder="0.00" />'
												);
				$tables->rows[] = array(
													'รวมรายจ่าย',
													'<input size="10"  name="action[exp_total]" id="edit-action-exp_total" class="form-text require" type="text" value="'.htmlspecialchars($data->exp_total).'" placeholder="0.00" />'
												);

			} else {
				$tables->rows[] = array(
													'<input size="10"  name="action[exp_meed]" id="edit-action-exp_meed" class="form-text require" type="text" value="'.htmlspecialchars($data->exp_meed).'" placeholder="0.00" />',
													'<input size="10"  name="action[exp_wage]" id="edit-action-exp_wage" class="form-text require" type="text" value="'.htmlspecialchars($data->exp_wage).'" placeholder="0.00" />',
													'<input size="10"  name="action[exp_supply]" id="edit-action-exp_supply" class="form-text require" type="text" value="'.htmlspecialchars($data->exp_supply).'" placeholder="0.00" />',
													'<input size="10"  name="action[exp_material]" id="edit-action-exp_material" class="form-text require" type="text" value="'.htmlspecialchars($data->exp_material).'" placeholder="0.00" />',
													'<input size="10"  name="action[exp_utilities]" id="edit-action-exp_utilities" class="form-text require" type="text" value="'.htmlspecialchars($data->exp_utilities).'" placeholder="0.00" />',
													'<input size="10"  name="action[exp_other]" id="edit-action-exp_other" class="form-text require" type="text" value="'.htmlspecialchars($data->exp_other).'" placeholder="0.00" />',
													'<input size="10"  name="action[exp_total]" id="edit-action-exp_total" class="form-text require" type="text" value="'.htmlspecialchars($data->exp_total).'" placeholder="0.00" />',
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
					array('type' => 'button', 'value' => '<i class="icon -save -white"></i><span>บันทึกกิจกรรม</span>')
					);


	$form->addText('<strong>หมายเหตุ : ภาพถ่ายประกอบกิจกรรมหรือไฟล์รายงานรายละเอียดประกอบกิจกรรม สามารถส่งเพิ่มเติมได้หลังจากบันทึกข้อมูลเสร็จเรียบร้อยแล้ว</strong>');

	$ret .= $form->build();

	//$ret .= print_o($data,'$data');
	//$ret .= print_o($projectInfo,'$projectInfo');

	$ret .= '<style type="text/css">
	.form.-action h4 {padding:8px;background-color:#ddd;}
	.form-item.-edit-action-rate1 .option,
	.form-item.-edit-action-rate2 .option {width:2.5em; padding:8px; border-radius:4px;border:1px #ccc solid; text-align: center; display: inline-block;}
	.form-item.-edit-action-rate1 .option input,
	.form-item.-edit-action-rate2 .option input {display: block; margin:0 auto;}
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
