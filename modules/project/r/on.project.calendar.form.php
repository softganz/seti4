<?php
/**
* Model Name
*
* @param Object $conditions
* @return Object $options
*/

$debug = true;

function on_project_calendar_form($form, $data, $para) {
	if ($data->id) {
		$rs=mydb::select('SELECT * FROM %project_activity% WHERE `calid`=:calid LIMIT 1',':calid',$data->id);
	}

	$stmt = 'SELECT
			o.`trid` objectiveId, o.`text1` objectiveText,
			a.`trid` mainactId, a.`detail1` mainactText
		FROM %project_tr% a
			LEFT JOIN %project_tr% o ON a.`parent`=o.`trid`
		WHERE a.`tpid`=:tpid AND a.`formid`="info" AND a.`part`="mainact"
		ORDER BY o.`trid` ASC, CONVERT (a.`detail1` USING tis620) ASC';

	$mainactDbs = mydb::select($stmt, ':tpid',$para->tpid);
	//$form->sql=print_o($mainactDbs,'$mainactDbs');

	if ($mainactDbs->count()) {
		$form->addField(
			'mainact',
			[
				'type' => 'select',
				'label' => 'กิจกรรมหลัก :',
				'options' => (function($mainactDbs) {
					$options = [-1 => 'ไม่มีกิจกรรมหลัก'];
					foreach ($mainactDbs->items as $item) {
						$options[$item->objectiveText][$item->mainactId] = $item->mainactText;
					}
					return $options;
				})($mainactDbs),
				'value' => $rs->mainact,
			],
		);
	}

	$form->addField(
		'calowner',
		[
			'type' => 'radio',
			'options' => array(1 => 'กิจกรรมของโครงการ', 2 => 'กิจกรรมของพี่เลี้ยง'),
			'display' => 'inline',
			'value' => SG\getFirst($rs->calowner,'1'),
		]
	);


	$form->addField(
		'budget',
		[
			'type' => 'text',
			'label' => 'งบประมาณที่ตั้งไว้ (บาท)',
			'size' => 10,
			'maxlength' => 12,
			'value' => $rs->budget,
			'placeholder' => '0.00',
		]
	);

	$form->addField(
		'targetpreset',
		[
			'type' => 'text',
			'label' => 'จำนวนกลุ่มเป้าหมาย (คน)',
			'size' => 10,
			'maxlength' => 7,
			'value' => $rs->targetpreset,
			'placeholder' => '0',
		]
	);

	$form->addField(
		'targetdetail',
		[
			'type' => 'textarea',
			'label' => 'รายละเอียดกลุ่มเป้าหมาย',
			'class' => '-fill',
			'rows' => 5,
			'value' => $rs->target,
		]
	);

	//$form->category->label='ชุดโครงการ';
	if (isset($form->children['detail'])) $form->children['detail']['label'] = 'รายละเอียดกิจกรรมตามแผน';

	/*
	$form->addText('<script type="text/javascript">
	$("form#edit-calendar").submit(function() {
		var ownerChecked=$("input[name=\'calendar[calowner]\'][value=\'1\']:checked").val()
		if (ownerChecked && $("#edit-calendar-mainact").val()==-1) {
			alert("กรุณาเลือกกิจกรรมหลัก")
			return false;
		}
	});
	</script>');
	*/

	$children = (Object) $form->children;

	property_reorder($children,'mainact','before title');
	property_reorder($children,'calowner','before title');
	property_reorder($children,'budget','before detail');
	property_reorder($children,'targetpreset','before detail');
	property_reorder($children,'targetdetail','before detail');
	property_reorder($children,'target','before detail');

	$form->children = (Array) $children;

	return $form;
}
?>