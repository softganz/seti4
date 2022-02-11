<?php
/**
* iBuy :: Green Smile Activity Form
* Created 2020-06-23
* Modify  2020-06-23
*
* @param Object $self
* @return String
*/

$debug = true;
function ibuy_green_activity_form($self) {
	$ret = '<header class="header -box -hidden">'._HEADER_BACK.'<h3>บันทึกการทำกิจกรรม</h3></header>';

	if (!i()->ok) {
		$ret .= R::View('signform', '{time:-1, rel: "none", done: "load | load->clear:box:'.url(q()).'"}');
		$ret .= '<style type="text/css">
		.toolbar.-main.-imed h2 {text-align: center;}
		.form.signform .form-item {margin-bottom: 16px; position: relative;}
		.form.signform label {position: absolute; left: 8px; color: #666; font-style: italic; font-size: 0.9em; font-weight: normal;}
		.form.signform .form-text, .form.signform .form-password {padding-top: 24px;}
		.module-imed.-softganz-app .form-item.-edit-cookielength {display: none;}
		.login {border: none; background-color: transparent;}
		.login.-normal h3 {display: none;}
		.form-item.-edit-cookielength {display: none;}
		.form.signform .ui-action>a {display: block;}
		</style>';
		return $ret;
	}

	$myShopList = R::Model('ibuy.shop.get', array('my' => '*'), '{debug: false, limit: "*"}');

	$ui = new Ui();
	$ui->addConfig('nav', '{class: "nav -selecttype"}');
	$ui->add('<a class="sg-action btn -active -dotype" data-type="GREEN,ACTIVITY"><i class="icon -material">directions_run</i><span>กิจกรรม</span></a>');
	if ($myShopList) {
		$ui->add('<a class="sg-action btn -dotype" href="'.url().'" data-type="GREEN,ONFIELD"><i class="icon -material">local_florist</i><span>ลงแปลง</span></a>');
		$ui->add('<a class="sg-action btn -dotype" href="'.url().'" data-type="GREEN,MOBILE"><i class="icon -material">directions_car</i><span>Green Mobile</span></a>');
		$ui->add('<a class="sg-action btn -dotype" href="'.url().'" data-type="GREEN,PLANT"><i class="icon -material">nature_people</i><span>ผลผลิตรอบใหม่</span></a>');
	}
	//$ret .= $ui->build();


	$optionLand = array('' => '== เลือกแปลงการผลิต ==');
	$stmt = 'SELECT l.`landid`, l.`landname`, o.`name` `orgName`
		FROM %ibuy_farmland% l
			LEFT JOIN %db_org% o USING(`orgid`)
			LEFT JOIN %org_officer% of USING(`orgid`)
		WHERE l.`uid` = :uid OR of.`uid` = :uid
		ORDER BY CONVERT(`landname` USING tis620) ASC';
	foreach (mydb::select($stmt, ':uid', i()->uid)->items as $rs) {
		$optionLand[$rs->orgName][$rs->landid] = $rs->landname;
	}


	$optionLocation = array('' => '== เลือกสถานที่ ==');
	$stmt = 'SELECT `bigid`, l.`flddata`
		FROM %bigdata% l
		WHERE l.`keyname` = "ibuy.location" AND l.`keyid` = :keyid';
	foreach (mydb::select($stmt, ':keyid', i()->uid)->items as $rs) {
		$value = json_decode($rs->flddata);
		//debugMsg($rs,'$rs');
		//debugMsg($value, '$value');
		$optionLocation[$rs->bigid] = $value->locname;
		$optionLocationStr .= '<abbr class="checkbox -block"><label><input type="radio" name="locid" value="'.$rs->bigid.'" class="-hidden"><i class="icon -material">check_circle</i><span>'.$value->locname.'</span></label></abbr>';
	}
	if (!$optionLocationStr) $optionLocationStr .= '<abbr class="-block -sg-text-center">ยังไม่มีสถานที่</abbr>';
	$optionLocationStr .= '<div class="-sg-flex -flex-nowrap" style="padding: 8px 0;"><input id="new-locname" type="text" class="form-text -fill" placeholder="เพิ่มสถานที่" /><input id="new-location" type="text" class="form-text -fill" placeholder="พิกัด เช่น 7.000,100.000" /><a id="new-location-save" class="btn -link" onClick="saveNewLocation($(this)); return false;"><i class="icon -material">add_circle_outline</i></a></div>';
	//debugMsg(mydb()->_query);

	$productOptions = R::Model('category.get', array('vid' => cfg('ibuy.vocab.category')), 'tid', '{debug: false, result: "group"}');

	$optionUnit = array('กก.' => 'กก.', 'ลิตร' => 'ลิตร', 'ก้อน' => 'ก้อน', 'ชิ้น' => 'ชิ้น', 'ตัว' => 'ตัว', 'แพ็ค' => 'แพ็ค', 'ขวด' => 'ขวด', 'ลัง' => 'ลัง');

	$optionStayTime = array('' => 'กี่นาที ???', 15 => '15 นาที', 30 => '30 นาที', 45 => '45 นาที', 60 => '1 ชั่วโมง', 120 => '2 ชั่วโมง', 180 => '3 ชั่วโมง', 240 => '4 ชั่วโมง');

	$form = new Form(NULL, url('ibuy/my/info/activity.save'), NULL, 'sg-form -upload ibuy-green-activity-form');

	$form->addData('rel',"notify");
	$form->addData('checkValid', true);
	$form->addConfig('enctype','multipart/form-data');
	$form->addData('done', 'close');

	$form->addField('tagname', array('type' => 'hidden', 'value' => 'GREEN,ACTIVITY'));

	$form->addText($ui->build());

	$form->addField(
		'message',
		array(
			'type' => 'textarea',
			'label' => 'กำลังทำกิจกรรม',
			'class' => '-fill',
			'rows' => 3,
			'placeholder' => (i()->ok ? '@'.i()->name.' ' : '').'บอกหน่อยว่ากำลังทำกิจกรรมอะไร',
			'config' => '{label: "hide"}',
		)
	);

	$form->addField(
		'locname',
		array(
			'type' => 'text',
			'label' => 'สถานที่',
			'class' => '-fill',
			'placeholder' => 'ระบุสถานที่',
			'posttext' => '<div class="input-append">'
				. SG\dropbox($optionLocationStr,'{class: "leftside -not-hide", icon: "material", iconText: "expand_more"}')
				. '</div>',
			'config' => '{label: "hide"}',
			'container' => '{class: "-group -hidden -for -green-mobile"}',
		)
	);

	$form->addField(
		'staytime',
		array(
			'type' => 'select',
			'label' => 'ระยะเวลา (นาที)',
			'class' => '-fill',
			'options' => $optionStayTime,
			'config' => '{label: "hide"}',
			'container' => '{class: "-hidden -for -green-mobile"}'
		)
	);

	$form->addField(
		'landid',
		array(
			'type' => 'select',
			'label' => 'แปลงการผลิต:',
			'class' => '-fill',
			'options' => $optionLand,
			'config' => '{label: "hide"}',
			'container' => '{class: "-hidden -for -green-onfield -green-plant"}'
		)
	);

	$form->addField(
		'productname',
		array(
			'label' => 'ชื่อผลผลิต',
			'type' => 'text',
			'class' => '-fill',
			'value' => htmlspecialchars($data->productname),
			'placeholder' => 'ระบุชื่อผลผลิต',
			'container' => '{class: "-require -hidden -for -green-plant"}'
		)
	);

	$form->addField(
		'catid',
		array(
			'type' => 'select',
			'label' => 'หมวด:',
			'class' => '-fill',
			'value' => $data->catid,
			'options' => array_merge_recursive(array('' => '== เลือกหมวด =='), $productOptions),
			'container' => '{class: "-require -hidden -for -green-plant"}'
		)
	);

	$form->addField(
		'startdate',
		array(
			'label' => 'วันที่เริ่มลงแปลง',
			'type' => 'text',
			'class' => 'sg-datepicker',
			'readonly' => true,
			'value' => $data->startdate ? sg_date($data->startdate, 'd/m/Y') : '',
			'placeholder' => '31/12/2562',
			'container' => '{class: "-hidden -for -green-plant"}'
		)
	);

	$form->addField(
		'cropdate',
		array(
			'label' => 'วันที่คาดว่าจะเก็บเกี่ยว',
			'type' => 'text',
			'class' => 'sg-datepicker',
			'readonly' => true,
			'value' => $data->cropdate ? sg_date($data->cropdate, 'd/m/Y') : '',
			'placeholder' => '31/12/2562',
			'container' => '{class: "-hidden -for -green-plant"}'
		)
	);

	$form->addField(
		'qty',
		array(
			'type' => 'text',
			'label' => 'ปริมาณการผลิต',
			'maxlength' => 10,
			'value' => htmlspecialchars($data->qty),
			'container' => '{class: "-hidden -for -green-plant"}'
		)
	);

	$form->addField(
		'unit',
		array(
			'type' => 'select',
			'label' => 'หน่วยการผลิต:',
			'maxlength' => 20,
			'options' => $optionUnit,
			'value' => htmlspecialchars($data->unit),
			'container' => '{class: "-hidden -for -green-plant"}'
		)
	);

	$form->addField(
		'saleprice',
		array(
			'type' => 'text',
			'label' => 'ราคาขาย (บาท)',
			'maxlength' => 10,
			'value' => htmlspecialchars($data->saleprice),
			'container' => '{class: "-hidden -for -green-plant"}'
		)
	);

	$form->addField(
		'bookprice',
		array(
			'type' => 'text',
			'label' => 'ราคาจอง (บาท)',
			'maxlength' => 10,
			'value' => htmlspecialchars($data->bookprice),
			'container' => '{class: "-hidden -for -green-plant"}'
		)
	);

	$form->addField('where',
		array(
			'type' => 'text',
			'label' => 'ที่ไหน?',
			'class' => 'sg-address -fill',
			'placeholder' => 'ระบุสถานที่ทำกิจกรรม',
			'container' => '{class: "-hidden"}',
		)
	);

	$form->addField('when',
		array(
			'type' => 'text',
			'label' => 'เมื่อไหร่?',
			'class' => '-fill',
			'value' => date('Y-m-d H:i'),
			'container' => '{class: "-hidden"}',
		)
	);

	$navUi = new Ui();
	$navUi->addConfig('nav', '{class: "nav -icons"}');

	$navUi->add('<div class="" style="margin-right: 4px;"><span class="btn btn-success fileinput-button"><i class="icon -material">photo_camera</i><span>ภาพถ่าย</span><input type="file" name="photo" id="ibuy-green-activity-photo"  accept="image/*;capture=camera" capture="camera"  /></span></div>');
	$navUi->add('<a class="btn"><i class="icon -material">place</i><span>สถานที่</span></a>');
	$navUi->add('<a class="btn"><i class="icon -material">more_time</i><span>เวลา</span></a>');

	$form->addText($navUi->build());

	$form->addField('save',
		array(
			'type' => 'button',
			'class' => '-fill',
			'value' => '<i class="icon -material">done_all</i><span>{tr:Post}</span>',
			//'pretext' => '<a class="sg-action btn -link -cancel" href="javascript:void(0)" data-rel="none" data-done="close"><i class="icon -material -gray">cancel</i><span>{tr:CANCEL}</span></a> ',
			'container' => array('class' => '-sg-text-center'),
		)
	);

	$ret .= $form->build();

	//head('flood.event.js','<script type="text/javascript" src="/flood/js.flood.event.js?v=3"></script>');

	$ret .= '<style type="text/css">
		.ibuy-green-activity-form .nav.-icons {padding: 8px;}
		.ibuy-green-activity-form .btn.-primary {width: 100%; margin: 0; padding: 12px 0; border-radius: 0;}

		.ibuy-green-activity-form .nav.-selecttype {padding: 0;}
		.ibuy-green-activity-form .nav.-selecttype .ui-item {margin: 0; border-bottom: 1px #eee solid;}
		.ibuy-green-activity-form .nav.-selecttype .btn {border: none; box-shadow: none; border-radius: 0; padding: 8px 0; color: #666; background-color: #fff;}
		.ibuy-green-activity-form .nav.-selecttype .btn:hover {background-color: #eee;}
		.ibuy-green-activity-form .nav.-selecttype .btn.-active {color: #c0ffc8; box-shadow: none; background-color: #20a200;}
		.ibuy-green-activity-form .nav.-selecttype .btn.-active>.icon {color: green; border-radius: 50%; box-shadow: 0 0 0 1px green; background-color: #c1d6c1;}
		.ibuy-green-activity-form .nav.-selecttype .ui-item {display: block; margin: 0;}
		.ibuy-green-activity-form .nav.-selecttype .ui-item .btn {display: block; margin: 0;}
		.ibuy-green-activity-form .form-item.-edit-locname .sg-dropbox>.-wrapper {width: 300px;}
		.ibuy-green-activity-form .form-item.-edit-locname .sg-dropbox>.-wrapper>.-content {background-color: #fff;}
		.ibuy-green-activity-form abbr {padding: 4px 8px; border-bottom: 1px #eee solid;}
		.ibuy-green-activity-form abbr:hover {background-color: #f7f7f7;}
		.ibuy-green-activity-form .form-item abbr label {margin: 0; font-weight: normal;}
		.ibuy-green-activity-form .form-item abbr>label>.icon {position: absolute; right: 8px;}

		.box-page>.form.ibuy-green-activity-form {padding-bottom: 88px;}
		.box-page .ibuy-green-activity-form .form-item.-edit-save {margin: 0; position: absolute; bottom: 0; padding: 0; left: 0; right: 0;}
		.module.-softganz-app .form-item {padding: 8px 0;}
		.module.-softganz-app .form-textarea {border-radius: 0; box-shadow: none;}
		.module.-softganz-app .form-select {border-radius: 0; box-shadow: none;}
		.module.-softganz-app .form-select:focus {box-shadow: none;}
		.module.-softganz-app .ibuy-green-activity-form .nav.-selecttype .btn {padding: 12px 0;}

	</style>';

	$ret .= '<script type="text/javascript">
	$(document).ready(function() {
		$("#edit-message").focus()
	})
	$(".btn.-dotype").click(function() {
		var $this = $(this)
		var dataType = $this.data("type").toLowerCase().replace(",","-")
		console.log(dataType)

		$(this).closest("ul").find("a").removeClass("-active")
		$(this).addClass("-active")
		$("#edit-tagname").val($(this).data("type"))
		$this.closest("form").find(".form-item.-for").addClass("-hidden").find(".-require").removeClass("-require")
		$this.closest("form").find(".form-item.-for.-"+dataType).removeClass("-hidden")
		$this.closest("form").find(".form-item.-for.-"+dataType+".-require").find(".form-text,.form-select").addClass("-require")
		return false
	});

	$(".form-item.-edit-locname input[name=locid]").click(function() {
		$("#edit-locname").val($(this).parent().find("span").text())
	});

	function saveNewLocation($this) {
		var saveUrl = "'.url('ibuy/my/info/location.save').'"
		var para = {}
		para.locname = $("#new-locname").val()
		para.location = $("#new-location").val()
		para.result = "json"
		if (para.locname) {
			console.log("SAVE",saveUrl,para)
			$.post(saveUrl, para, function(data) {
				//notify(data.html)
				console.log(data)
				html = "<abbr class=\"checkbox -block\"><label><input type=\"radio\" name=\"locid\" value=\""+data.locid+"\" class=\"-hidden\" checked=\"checked\"><i class=\"icon -material -gray\">check_circle</i><span>"+data.locname+"</span></label></abbr>"
				console.log($this)
				$(html).insertBefore($this.closest("div"))
				$("#edit-locname").val(data.locname)
			},"json").fail(function() {
				notify("ERROR ON LOADING")
			})
			.done(function() {
				console.log("ACTION DONE")
			});
		}
	}
	</script>';
	return $ret;
}
?>