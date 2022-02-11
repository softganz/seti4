<?php
function view_project_action_form($projectInfo, $activityId=NULL, $data=NULL, $options='{}') {
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
	$form->addField('ret', array('type' => 'hidden', 'name' => 'ret', 'value' => $options->ret ? $options->ret : 'paper/'.$tpid.'/owner'));
	$form->addField('rate', array('type' => 'hidden', 'value' => -1));
	if (empty($data->calid)) $form->addField('newcalendar',array('type'=>'hidden','value'=>'yes'));

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
					'actionReal',
					array(
						'type' => 'textarea',
						'label' => 'รายละเอียดขั้นตอน กระบวนการ',
						'class' => '-fill',
						'rows' => 6,
						'require' => true,
						'value' => $data->actionReal,
						'description' => 'ระบุรายละเอียดขั้นตอน กระบวนการ ที่ได้ดำเนินการ',
						'placeholder' => 'ระบุรายละเอียดขั้นตอน กระบวนการ ที่ได้ดำเนินการ',
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
			$tables->thead = '<thead><tr><th colspan="6">ประเภทรายจ่าย</th><th rowspan="2">รวมรายจ่าย</th></tr><tr><th>ค่าตอบแทน</th><th>ค่าวัสดุ</th><th>ค่าเดินทาง</th><th>ค่าใช้สอย</th><th>ค่าสาธารณูปโภค</th><th>อื่น ๆ</th></tr></thead>';
		}

		if ($data->flag == _PROJECT_LOCKREPORT) {
			$tables->rows[] = array(
													$data->exp_meed.'<input size="10"  name="action[exp_meed]" id="edit-action-exp_meed" class="form-text require" type="hidden" readonly="readonly" value="'.htmlspecialchars($data->exp_meed).'" />',
													$data->exp_travel.'<input size="10"  name="action[exp_travel]" id="edit-action-exp_travel" class="form-text require" type="hidden" value="'.htmlspecialchars($data->exp_travel).'" />',
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
													'ค่าวัสดุ',
													'<input size="10"  name="action[exp_material]" id="edit-action-exp_material" class="form-text require" type="text" value="'.htmlspecialchars($data->exp_material).'" placeholder="0.00" autocomplete="off" />'
												);
				$tables->rows[] = array(
													'ค่าเดินทาง',
													'<input size="10"  name="action[exp_travel]" id="edit-action-exp_travel" class="form-text require" type="text" value="'.htmlspecialchars($data->exp_travel).'" placeholder="0.00" autocomplete="off" />'
												);
				$tables->rows[] = array(
													'ค่าใช้สอย',
													'<input size="10"  name="action[exp_supply]" id="edit-action-exp_supply" class="form-text require" type="text" value="'.htmlspecialchars($data->exp_supply).'" placeholder="0.00" autocomplete="off" />'
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
													'<input size="10"  name="action[exp_material]" id="edit-action-exp_material" class="form-text require" type="text" value="'.htmlspecialchars($data->exp_material).'" placeholder="0.00" />',
													'<input size="10"  name="action[exp_travel]" id="edit-action-exp_travel" class="form-text require" type="text" value="'.htmlspecialchars($data->exp_travel).'" placeholder="0.00" />',
													'<input size="10"  name="action[exp_supply]" id="edit-action-exp_supply" class="form-text require" type="text" value="'.htmlspecialchars($data->exp_supply).'" placeholder="0.00" />',
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
						.'<input type="hidden" name="action[exp_travel]" id="edit-action-exp_travel" value="0" />'
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

	//$ret.=print_o($data,'$data');
	//$ret.=print_o($projectInfo,'$projectInfo');

	$ret .= '<style type="text/css">
	.form.-action h4 {padding:8px;background-color:#ddd;}
	</style>';

	$ret .= '<script type="text/javascript">
	$(document).ready(function() {
		var errorCount=0;
		var $form=$("form#action-post");

		$("#edit-action-exp_total").attr("readonly", true).css({"font-weight": "bold"});

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
	});
	</script>';
	return $ret;
}
?>
