<?php
/**
* Module : Module Method
* Created 2020-08-01
* Modify  2020-08-01
*
* @param Object $self
* @param Int $var
* @return String
*
* @usage module/{$Id}/method
*/

$debug = true;

function garage_job_in_new($self) {
	$shopId = ($shopInfo = R::Model('garage.get.shop')) ? $shopInfo->shopid : NULL;

	new Toolbar($self, 'รับรถใหม่');

	$ret = '';


	$stmt = 'SELECT * FROM %garage_shop% WHERE `shopid` IN ( :shopId )';
	$shopDbs = mydb::select($stmt, ':shopId', 'SET:'.$shopInfo->branchId);

	$stmt = 'SELECT * FROM %garage_cartype% ORDER BY CONVERT(`cartypename` USING tis620) ASC';
	$templateDbs = mydb::select($stmt, ':shopId', 'SET:'.$shopInfo->branchId);

	$form = new Form(NULL, url('garage/job/*/in.create'), NULL, 'garage-job-in-form');

	$jobUi = new Ui('div', 'ui-card');
	$jobUi->addConfig('nav', '{class: "nav -master -job-new"}');
	$jobUi->header('<h5>เลือกสาขา: <span id="nextdocno" style="display: block;">&nbsp;</span></h5>', '{style: "flex: 1 0 100%"}');


	foreach ($shopDbs->items as $rs) {
		$jobUi->add('<label><input class="radio-shopid" type="radio" name="shop" value="'.$rs->shopid.'" /><i class="icon -material -i48">account_balance</i><span>'.$rs->shortname.'</span></label><i class="icon -material -select-active">done</i>');
	}

	$form->addText($jobUi->build());

	//เลือกประเภทจ็อบ จอดซ่อม/รอซ่อม(เปิดจ็อบโดยที่รถยังไม่เข้า สร้างจ็อบแต่ไม่ run เลขที่, ระบุเลขรอ-carwaitno)
	//TODO: แสดงรายชื่อรถที่รอซ่อม -> เลือก -> สร้างเป็นจ็อบจอดซ่อม โดย run เลขที่ต่อจากปัจจุบัน และบันทึกวันที่รถเข้า (carindate/iscar)

	// Select car type
	$jobUi = new Ui('div', 'ui-card');
	$jobUi->addConfig('nav', '{class: "nav -master -type"}');
	$jobUi->header('<h5>เลือกประเภทรถ:</h5>', '{style: "flex: 1 0 100%"}');

	foreach ($templateDbs->items as $rs) {
		$jobUi->add(
			'<label><input class="radio-cartype" type="radio" name="type" value="'.$rs->cartypeid.'" /><i class="icon -material -i48"></i><span>'.$rs->cartypename.'</span></label><i class="icon -material -select-active">done</i>',
			'{class: "-car-type-'.$rs->cartypeid.'"}'
		);
	}

	$form->addText($jobUi->build());

	$form->addField(
		'plate',
		array(
			'type' => 'text',
			'label' => 'ทะเบียนรถ',
			'require' => true,
			'class' => '-fill',
		)
	);

	$form->addField(
		'save',
		array(
			'type' => 'button',
			'class' => '-fill',
			'value' => '<i class="icon -material -sg-48">add</i><span>บันทึกรับรถ</span>',
			'container' => '{class: "-sg-text-right"}',
		)
	);

	$ret .= $form->build();

	$ret .= '<style type="text/css">
	.garage-job-in-form input[type="radio"] {display: none;}
	.nav.-type {display: none;}
	.form-item.-edit-plate {display: none;}
	.form-item.-edit-save {display: none;}
	.nav.-job-new label, .nav.-type label {display: block; position: absolute; top: 0; left: 0; right: 0; bottom: 0;}
	.nav.-master>.ui-card>.ui-item.-active {background-color: #f60;}
	</style>'._NL._NL;

	$ret .= '<script type="text/javascript">
	console.log("LOAD")
	$(document).on("change",".nav.-master.-job-new>.ui-card>.ui-item",function() {
		console.log("Shop Click")
		$(this).closest(".ui-card").find(".ui-item").removeClass("-active")
		$(this).addClass("-active")
		$(".nav.-type").show()

		var url = "'.url('garage/api/docno').'"
		var para = {name: "job", type: "next"}
		para.shop = $(".radio-shopid:checked").val()
		console.log("para = ",para)
		$.get(url, para, function(doc) {
			console.log(doc)
			$("#nextdocno").text(" (เลขใบสั่งซ่อมถัดไปคือ "+doc.next+")")
		}, "json")
	});

	$(document).on("change",".nav.-master.-type>.ui-card>.ui-item",function() {
		console.log("Type Click")
		$(this).closest(".ui-card").find(".ui-item").removeClass("-active")
		$(this).addClass("-active")
		$("#form-item-edit-plate").show()
		$("#form-item-edit-save").show().find("button").attr("disabled", "disabled").addClass("-disabled")
	});

	$("#form-item-edit-plate").keyup(function() {
		$("#form-item-edit-save").find("button").removeAttr("disabled").removeClass("-disabled")
	});

	</script>';

	return $ret;
}
?>