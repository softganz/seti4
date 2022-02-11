<?php
/**
* Garage Car In
* Created 2019-12-20
* Modify  2019-12-20
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function garage_job_in($self, $jobInfo = NULL, $carInId = NULL) {
	//if (!(is_object($jobInfo) || $jobInfo == '*')) location('garage/job/'.$jobInfo.'/in');
	$jobId = $jobInfo->tpid;

	// New car in form
	if (!$jobId) return R::Page('garage.job.in.new', $self);


	$shopId = ($shopInfo = R::Model('garage.get.shop')) ? $shopInfo->shopid : NULL;

	page_class('-job-type-'.$jobInfo->shopShortName);

	$toolbar = new Toolbar($self,'รับรถ - '.$jobInfo->plate.' (เลขจ็อบ '.$jobInfo->jobno.')');

	$toolbarNav = new ui(NULL,'ui-nav');

	$toolbarNav->add('<a class="btn" href="'.url('garage/in').'" title="รับรถ"><i class="icon -material">add_business</i><span>รับรถ</span></a>');
	$toolbarNav->add('<a class="sg-action btn" href="'.url('garage/job/'.$jobId.'/in.detail').'" data-rel="#main"><i class="icon -material -green">directions_car</i><span>ข้อมูลรถ</span></a>');
	$toolbarNav->add('<a class="sg-action btn" href="'.url('garage/job/'.$jobId.'/in.before').'" data-rel="#main" title="ตรวจรถก่อนซ่อม"><i class="icon -material -blue" style="color: #1b7fff">build_circle</i><span>ตรวจรถก่อนซ่อม</span></a>');
	$toolbarNav->add('<a class="sg-action btn" href="'.url('garage/job/'.$jobId.'/in').'" data-rel="#main" title="รายการความเสียหาย"><i class="icon -material -red">verified</i><span>ความเสียหาย</span></a>');
	$toolbarNav->add('<a class="sg-action btn" href="'.url('garage/job/'.$jobId.'/do').'" data-rel="#main"><i class="icon -material">assignment</i><span>ใบสั่งงาน</span></a>');
	$toolbarNav->add('<a class="btn" href="javascript:window.print()" style="border-radius: 4px;"><i class="icon -material">print</i><span class="-hidden">พิมพ์</span></a>');

	$toolbar->addNav('main', $toolbarNav);

	if (!$jobId) return message('error', 'ERROR: ไม่มีจ็อบที่ระบุ');
	else if (!$jobInfo->templateid) return message('error', 'ERROR: ไม่ใช่รายการรับรถ');
	else if ($jobInfo->qt) return message('error', 'ERROR: จ็อบมีการเสนอราคาแล้ว ไม่สามารถแก้ไขได้');




	$isEditable = R::Model('garage.right', $shopInfo, 'carin') || $bigData->ucreated == i()->uid;

	$ret .= '<div class="garage-job-in -sg-flex">';

	$ret .= '<div class="-car">';
	$ret .= '<div class="-car-photo -type-'.$jobInfo->cartype.'">';
	$ui = new Ui();
	for ($i = 1; $i <= 13; $i++) $ui->add('<a class="sg-action -car-pos -pos-'.$i.'" href="'.url('garage/job/'.$jobId.'/in.pos/'.$jobInfo->cartype.'/'.$i).'" data-rel="#pos-select">'.$i.'</a>');
	$ret .= $ui->build();
	$ret .= '</div><!-- -car-photo -->';
	$ret .= '</div><!-- -car -->';



	$inlineAttr = array();
	$inlineAttr['class'] = 'garage-job-in-form';
	if ($isEditable) {
		$inlineAttr['class'] .= ' sg-inline-edit';
		$inlineAttr['data-update-url'] = url('garage/job/'.$jobId.'/info/in.tran.save');
		if (debug('inline')) $inlineAttr['data-debug'] = 'inline';
	}

	$ret .= '<div id="garage-job-in-form" '.sg_implode_attr($inlineAttr).'>'._NL;


	$damagecodeList = mydb::select('SELECT * FROM %garage_damage%')->items;
	$damagecodeOptions = '<option value="">???</option>';
	foreach ($damagecodeList as $v) {
		$damagecodeOptions .= '<option value="'.$v->damagecode.'" '.($jobTranInfo->damagecode == $v->damagecode?'selected="selected"':'').' data-pretext="'.$v->pretext.'">'.$v->damagecode.' : '.$v->damagename.'</option>';
	}

	$ret .= '<div class="-search">';

	$form = new Form(NULL, url('garage/job/'.$jobId.'/info/tran.save'),NULL, 'sg-form -in-search');
	$form->addData('checkValid', true);
	$form->addData('rel', 'console');
	$form->addData('done', 'javascript: clearSearchForm() | load->replace:.garage-in-form:'.url('garage/job/'.$jobId.'/in.tran'));

	$form->addField('repairid', array('type' => 'hidden', 'value' => "", 'require'=>true, 'label' => 'ค้นรายการ'));
	$form->addField('qty', array('type' => 'hidden', 'value' => "1"));
	//$form->addField('damagecode', array('type' => 'hidden', 'value' => "A"));

	$form->addField(
		'description',
		array(
			'type' => 'text',
			'class' => 'sg-autocomplete -fill',
			'attr' => array(
				'data-query' => url('garage/api/repaircode'),
				'data-altfld' => "edit-repairid",
				'data-select' => "name",
			),
			'value' => htmlspecialchars($insuName),
			'placeholder' => 'ค้นรายการสั่งซ่อม/อะไหล่',
			'pretext' => '<div class="input-prepend">'
				. '<span><a class="btn" href="javascript:void(0)" onclick=\'$("#edit-repairid").val("");$("#edit-description").val("");\'><i class="icon -material -gray -sg-16">clear</i></a></span>'
				. '</div>',
			'posttext' => '<div class="input-append">'
				. '<span><select id="edit-damagecode" class="form-select -damagecode" name="damagecode">'.$damagecodeOptions.'</select></span>'
				. '<span class="-primary"><button class="btn -primary"><i class="icon -material">add</i><span>เพิ่ม</span></button></span>'
				. '</div>',
			'container' => '{class: "-group"}',
		)
	);

	/*
	$form->addField(
		'description',
		array(
			'type' => 'text',
			'class' => 'sg-autocomplete -fill',
			'attr' => array(
				'data-query' => url('garage/api/repaircode'),
				'data-altfld' => "edit-repairid",
				'data-select' => "name",
			),
			'value' => htmlspecialchars($insuName),
			'placeholder' => 'ค้นรายการสั่งซ่อม/อะไหล่',
			'pretext' => '<div class="input-prepend">'
				. '<span><a class="btn"><i class="icon -material">search</i></a></span>'
				. '<span><a class="btn"><i class="icon -material">search</i><span>ค้น</span></a></span>'
				. '<span><a class="btn"><span>ค้น</span></a></span>'
				. '<span><select id="edit-damagecode" class="form-select -damagecode" name="damagecode">'.$damagecodeOptions.'</select></span>'
				. '<span><a class="btn" href="javascript:void(0)" onclick=\'$("#edit-repairid").val("");$("#edit-description").val("");\'><i class="icon -material -gray -sg-16">clear</i></a></span>'
				. '<span><select id="edit-damagecode" class="form-select -damagecode" name="damagecode">'.$damagecodeOptions.'</select></span>'
				. '</div>',
			'posttext' => '<div class="input-append">'
				. '<span><select id="edit-damagecode" class="form-select -damagecode" name="damagecode">'.$damagecodeOptions.'</select></span>'
				. '<span><select id="edit-damagecode" class="form-select -damagecode" name="damagecode">'.$damagecodeOptions.'</select></span>'
				. '<span class="-primary"><button class="btn -primary"><i class="icon -material">add</i><span>เพิ่ม</span></button></span>'
				. '</div>',
			'container' => '{class: "-group"}',
		)
	);

	$form->addField(
		'a1',
		array(
			'type' => 'text',
			'class' => 'sg-autocomplete -fill',
			'attr' => array(
				'data-query' => url('garage/api/repaircode'),
				'data-altfld' => "edit-repairid",
				'data-select' => "name",
			),
			'value' => htmlspecialchars($insuName),
			'placeholder' => 'ค้นรายการสั่งซ่อม/อะไหล่',
			'pretext' => '<div class="input-prepend">'
				. '<span><select id="edit-damagecode" class="form-select -damagecode" name="damagecode">'.$damagecodeOptions.'</select></span>'
				. '<span><a class="btn" href="javascript:void(0)" onclick=\'$("#edit-repairid").val("");$("#edit-description").val("");\'><i class="icon -material -gray -sg-16">clear</i></a></span>'
				. '<span><select id="edit-damagecode" class="form-select -damagecode" name="damagecode">'.$damagecodeOptions.'</select></span>'
				. '</div>',
			'container' => '{class: "-group"}',
		)
	);

	$form->addField(
		'a2',
		array(
			'type' => 'text',
			'class' => 'sg-autocomplete -fill',
			'attr' => array(
				'data-query' => url('garage/api/repaircode'),
				'data-altfld' => "edit-repairid",
				'data-select' => "name",
			),
			'value' => htmlspecialchars($insuName),
			'placeholder' => 'ค้นรายการสั่งซ่อม/อะไหล่',
			'posttext' => '<div class="input-append">'
				. '<span><select id="edit-damagecode" class="form-select -damagecode" name="damagecode">'.$damagecodeOptions.'</select></span>'
				. '<span><select id="edit-damagecode" class="form-select -damagecode" name="damagecode">'.$damagecodeOptions.'</select></span>'
				. '<span class="-primary"><button class="btn -primary"><i class="icon -material">add</i><span>เพิ่ม</span></button></span>'
				. '</div>',
			'container' => '{class: "-group"}',
		)
	);
	*/

	$form->addField('go',array('type'=>'button','value'=>'<i class="icon -material">search</i>','container'=>'{class: "-hidden"}'));

	$ret .= $form->build();

	/*
	$form = new Form();
	$form->addField('a',array('type'=>'text','class'=>'-fill'));
	$ret .= $form->build();
	*/
	$ret .= '</div><!-- -search -->';



	$ret .= '<div id="pos-select"><p class="-sg-text-center" style="padding: 32px 0;">เลือกรายการซ่อมจากตำแหน่งรถ หรือ ค้นหารายการจากช่องค้นหา</p></div><!-- pos-select -->';


	$ret .= '</div><!-- garage-job-in-form -->';

	$ret .= R::Page('garage.job.in.tran', NULL, $jobInfo, $carId);

	//$ret .= '<div id="result"></div>';

	$ret .= '</div><!-- garage-job-in -->';


	head('<style type="text/css">
		.box {box-shadow: none;}
	.garage-in-form .item td {padding: 0; vertical-align: middle;}
	.garage-in-form .code {display: none;}
	.garage-in-form .title {background-color: #ccc; text-align: center;}
	.garage-in-form .garage-in-form-item .icon {color: #ddd; margin: 0;}
	.garage-in-form td label {margin: 2px; padding: 8px; display: block; border-radius: 4px;}
	.garage-in-form td label:hover {background-color: #eee;}
	.garage-in-form .inline-edit-item.-active .icon {color: green;}

	.garage-in-form input[type="radio"] {display: none;}
	.garage-in-form-item>tbody>tr.-forinput:last-child .inline-edit-item.-radio {display: none;}
	.garage-in-form-item>thead>tr>th:first-child {border-left: 2px #f7f7f7 solid;}
	.garage-in-form-item>tbody>tr>th:first-child {border-left: 2px #f7f7f7 solid;}
	.garage-in-form-item>tbody>tr>td:first-child {border-left: 2px red solid; padding-left: 4px;}
	.garage-in-form-item>tbody>tr.-type-2>td:first-child {border-left: 2px green solid;}
	.garage-in-form-item>tbody>tr.-type-2.-show-code>td:nth-child(n+4)>* {display: none;}
	.garage-in-form-item>tbody>tr.-type-2.-show-code>td:last-child>* {display: block;}
	.garage-job-in-detail.sg-view.-co-2>.-sg-view:nth-of-type(2) {flex: 0 0 150px;}
	</style>');

	head('<script type="text/javascript">
			$(document).on("change",".form.-in-search .form-select.-damagecode",function() {
		var damagecode = $(this).val()
		var $detail = $(this).closest("form").find("#edit-description")
		var pretext = $(this).find(":selected").data("pretext")
		console.log(damagecode+" pretext = "+pretext)
		$detail.val(pretext+" "+$detail.val())
	});

	$(document).on("change",".form.-in-pos .form-select.-damagecode",function() {
		var damagecode = $(this).val()
		var detail = $(this).prev().data("src")
		var pretext = $(this).find(":selected").data("pretext")
		//console.log(damagecode+" pretext = "+pretext)
		$(this).prev().val(pretext+" "+detail)
	});

	$(document).on("change",".form.-in-detail .form-item.-edit-damagecode .form-select",function() {
		var damagecode = $(this).val()
		var $detail = $(this).closest("form").find("#edit-description")
		var pretext = $(this).find(":selected").data("pretext")
		//console.log(damagecode+" pretext = "+pretext)
		$detail.val(pretext+" "+$detail.val())
	});

	function updateCustomerId($this, data) {
		$("#edit-job-customerid").val(data.customerid)
		$("#edit-job-customername").val(data.customername)
	}

	function clearSearchForm() {
		$("#edit-description").val("");
		$("#edit-repairid").val("");
		$("#edit-damagecode option:selected").prop("selected",false);
	}
	</script>');


	return $ret;







	//TODO:: Check and remove this code
	$xxx = '<script type="text/javascript">
	var oldRadioActive
	var deleteUrl = "'.url('garage/job/'.$jobId.'/info/tran.delete/').'"
	var $lastTableRow = $("#garage-in-form-item").find("tbody:last").find("tr:last").html()




	function garageInFormBefore($this,$parent) {
		//console.log("ON BEFORE FUNCTION")
		//console.log($this)

		//oldRadioActive = $(this).closest(".inline-edit-item").hasClass("-active")
		//$(this).closest("tr").find(".inline-edit-item").removeClass("-active")
		//$(this).closest(".inline-edit-item").addClass("-active")
	}

	$(document).on("click", ".garage-in-form .inline-edit-field.-radio", function() {
		oldRadioActive = $(this).closest(".inline-edit-item").hasClass("-active")
		$(this).closest("tr").find(".inline-edit-item").removeClass("-active")
		$(this).closest(".inline-edit-item").addClass("-active")
	});


	function garageJobInRadioCallback($this,data,$parent) {
		if (oldRadioActive) {
			//console.log("REMOVE ITEM")
			var para = {}
			para.confirm = "yes"
			$.post(deleteUrl + $this.data("tr"), para, function() {
				$this.prop("checked", false)
				$this.removeData("tr")
				$this.closest("tr").find(".inline-edit-item").removeClass("-active")
			})
		}
	}

	function garageJobInDetailCallback($this,data,$parent) {
		var $currentRow = $this.closest("tr")
		$currentRow.find(".inline-edit-field.-radio").data("description", data.value)

		// Check if last row, add new row
		if ($currentRow.is(":last-child")) {
			//console.log("ADD NEW ROW")
			//console.log($currentRow)

			$currentRow.find(".inline-edit-item.-radio").show()
			var nextNo = $currentRow.data("rowid")+1
			//var $newRow = $currentRow.clone()
			//console.log("NEXT NO = "+nextNo)

			$currentRow.after("<tr>"+$lastTableRow+"</tr>")

			//var $newRow = $this.closest("tbody").find("tr:last")

			var $newRow = $("#garage-in-form-item").find("tbody:last").find("tr:last")

			$newRow.addClass("-row-"+nextNo+" -forinput").attr("id", "row-"+nextNo)
			var $newRow = $("#row-"+nextNo)

			$newRow.attr("data-rowid",nextNo)
				.find(".inline-edit-field")
				.attr("name", "r"+nextNo)
				.attr("data-tr", "")
				.attr("data-name", "r"+nextNo)
				.attr("data-group", "in:r"+nextNo)

			$newRow.find(".inline-edit-item.-text>span>span").html("<span class=\"placeholder -no-print\">ระบุ</span>")

			//console.log($newRow.find(".inline-edit-field").data("group"))
			//console.log($newRow.html())
			//console.log($newRow)
		}
	}

	/**********
	$(".form-item .code:checked").each(function() {
		var $icon = $(this).parent("label").find(".icon")
		$(this).parent("label").toggleClass("-active")
		//console.log($icon)
	})

	$(".garage-in-form .code").click(function() {
		$(this).closest("tr").find(".form-item").removeClass("-active")
		$(this).closest(".form-item").addClass("-active")
	});
	***********/

	</script>';

	//$ret .= print_o($templateList, '$templateList');
	//$ret .= print_o($jobInfo, '$jobInfo');
	//$ret .= print_o($shopInfo, '$shopInfo');

}
?>