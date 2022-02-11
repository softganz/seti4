<?php
/**
* Green :: My Animal Form
*
* @param Object $self
* @param Int $plantId
* @return String
*/

$debug = true;

function green_my_animal_form($self, $plantId = NULL) {
	$getLandId = post('land');
	$landList = Array();
	$data = new stdClass();
	$orgInfo = NULL;
	$ret = '';

	$isAdmin = is_admin('green');
	$isAddPlant = false;

	if ($plantId) {
		$data = R::Model('green.plant.get', $plantId)->info;
		$orgInfo = R::Model('green.shop.get', $data->orgid);
	} else {
		$data->orgid = ($orgInfo = R::Model('green.shop.get', 'my')) ? $orgInfo->orgId : NULL;
		if ($getLandId) $data->landid = $getLandId;
	}

	$isOrgAdmin = $isAdmin || $orgInfo->RIGHT & _IS_EDITABLE;
	$isAddPlant = $isOrgAdmin || $orgInfo->is->membership;

	if (!$isAddPlant) return message('error', 'Access Denied');

	if ($data->orgid) {
		$landList = R::Model(
			'green.land.get',
			'{orgId: '.$data->orgid.', me: '.($isOrgAdmin ? 'false' : 'true').'}',
			'{debug: false, limit: "*"}'
		);
	}

	$landOptions = array('' => '== เลือกคอก ==');
	foreach ($landList->items as $rs) {
		$landOptions[$rs->landid] = $rs->landname;
	}






	// Start View

	if (!R()->appAgent) $ret = '<header class="header -box">'._HEADER_BACK.'<h3>ปศุสัตว์ @'.$orgInfo->name.'</h3></header>';

	//$ret .= print_o($landInfo);

	$form = new Form(NULL, url('green/my/info/activity.save'), NULL, 'sg-form');
	$form->addData('checkValid', true);
	$form->addData('rel', 'notify');
	$form->addData('done', R()->appAgent ? 'close' : 'back | load: .box-page | load');

	if ($data->msgid) {
		$form->addField('msgid', array('type' => 'hidden', 'value' => $data->msgid));
	}

	if($data->plantid) {
		$form->addField('plantid', array('type' => 'hidden', 'value' => $data->plantid));
	}

	$form->addField('orgid',array('type' => 'hidden', 'value' => $orgId));

	$form->addField('tagname',array('type' => 'hidden', 'value' => 'GREEN,ANIMAL'));

	$form->addField('message',array('type' => 'hidden', 'value' => 'เพิ่มสัตว์'));

	$productOptions = array(1 => 'โค');

	$form->addField('catid', array('type'=>'hidden','value'=>1));

	$form->addField(
		'landid',
		array(
			'type' => 'select',
			'label' => 'คอก (หรือแปลงที่ดินสำหรับเลี้ยง):',
			'class' => '-fill',
			'require' => true,
			'options' => $landOptions,
			'value' => $data->landid,
		)
	);

	$form->addField(
		'productname',
		array(
			'type' => 'text',
			'label' => 'ชื่อพันธุ์โค (จำนวน 1 ตัว)',
			'class' => '-fill',
			'require' => true,
			'value' => SG\getFirst($data->productname,'โค'),
			'placeholder' => 'ระบุชื่อพันธุ์สัตว์',
		)
	);

	$form->addField(
		'productcode',
		array(
			'type' => 'text',
			'label' => 'หมายเลขโค',
			'class' => '-fill',
			'maxlength' => 20,
			'value' => $data->productcode,
			'placeholder' => 'ระบุหมายเลขโค',
		)
	);

	$form->addField(
		'qty',
		array(
			'type' => 'hidden',
			'label' => 'จำนวน (ตัว)',
			'class' => '-fill',
			'maxlength' => 4,
			'value' => 1,
			'placeholder' => '0',
		)
	);

	$form->addField(
		'unit',
		array(
			'type' => 'hidden',
			'label' => 'หน่วยการผลิต:',
			'value' => 'ตัว',
		)
	);

	$form->addField(
		'startdate',
		array(
			'label' => 'วันที่เริ่มเลี้ยง',
			'type' => 'text',
			'class' => 'sg-datepicker',
			'require' => true,
			'value' => sg_date(SG\getFirst($data->startdate,date('Y-m-d')), 'd/m/Y'),
			'placeholder' => '31/12/2562',
		)
	);

	$form->addField(
		'startage',
		array(
			'label' => 'อายุ ณ วันที่เริ่มเลี้ยง (เดือน)',
			'type' => 'select',
			'class' => '-fill',
			'value' => $data->startage,
			'options' => '0..24',
		)
	);

	$form->addField(
		'cropdate',
		array(
			'label' => 'วันที่คาดว่าจะขาย',
			'type' => 'text',
			'class' => 'sg-datepicker',
			'value' => $data->cropdate ? sg_date($data->cropdate, 'd/m/Y') : '',
			'placeholder' => '31/12/2562',
		)
	);

	$form->addField(
		'detail',
		array(
			'label' => 'รายละเอียด',
			'type' => 'textarea',
			'class' => '-fill',
			'rows' => 3,
			'value' => $data->detail,
			'placeholder' => 'ระบุรายละเอียดอื่นๆ',
		)
	);

	$form->addField(
		'save',
		array(
			'type' => 'button',
			'value' => '<i class="icon -material">done_all</i><span>{tr:SAVE}</span>',
			'pretext' => '<a class="sg-action btn -link -cancel" data-rel="back"><i class="icon -material">cancel</i><span>{tr:CANCEL}</span></a>',
			'container' => '{class: "-sg-text-right"}',
		)
	);

	$form->addText('<p>เก็บข้อมูลโครายตัว</p>');
	$ret .= $form->build();

	//$ret .= print_o($data, '$data');
	//$ret .= print_o($shopInfo, '$shopInfo');

	head('<script type="text/javascript">
	function onWebViewComplete() {
		console.log("CALL onWebViewComplete FROM WEBVIEW")
		var options = {title: "แก้ไข", refreshOnBack: true}
		return options
	}
	</script>');

	$ret .= '<script type="text/javascript">
	$("#edit-catid").change(function() {
		var kindId = $(this).val()
		var selectedText = $(this).find("option:selected").text()
		console.log($(this).find("option:selected").text())
		if (kindId == 999) {
			$("#edit-productname").val("").attr("type", "text")
			$("#edit-message").val("ปลูกต้นไม้")
		} else {
			$("#edit-productname").val(selectedText)
			$("#edit-message").val("ปลูกต้นไม้ "+selectedText)
		}
	})
	</script>';
	return $ret;
}
?>