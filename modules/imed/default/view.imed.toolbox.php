<?php
/**
* Show module menu
*
* @param Record Set $rs
* @return String
*/

$debug = true;

function view_imed_toolbox($self, $title = 'iMed@home', $nav = 'default', $psn = NULL, $options = '{}') {
	//$self->theme->title = $title;
	$ret = '<div id="imed-box" class="imed-box -'.$nav.'">'._NL;
	$ret .= '<nav class="nav -imed">';
	$ret .= '<h2>'.$title.'</h2>'._NL;
	// Friend Search box : List friend (at top), group, other name, patient name in dropdown box. Show wall on select. Show all name on submit button click

	$ui = new Ui(NULL,'ui-main');

	$ui->add('<span id="patientPhoto" class="patient-photo"><img src="'.imed_model::patient_photo($rs->psnid).'" width="32" height="32" /><span class="arrow-right "></span></span>'
			. '<input id="patientSearch" class="sg-autocomplete form-text patient-search" type="text" size="15" maxlength="100" accesskey="/" name="pn" autocomplete="off" tabindex="" value="" data-query="'.url('imed/api/person').'" data-callback="patientSearchCallback" title="ป้อนชื่อผู้ป่วยหรือเลข 13 หลัก เพื่อค้นหา" placeholder="ป้อนชื่อผู้ป่วยหรือเลข 13 หลัก">'
			. '<i class="icon -search"></i>'
			. '<a id="imed-patient-add" class="sg-action btn -primary -add -circle32" href="'.url('imed/patient/add').'" data-rel="#imed-app" data-tooltip="ป้อนชื่อในช่องด้านซ้ายและคลิก <strong>+เพิ่มรายชื่อ</strong><br />เพื่อเพิ่มรายชื่อในช่องค้นหาด้านซ้ายมาเป็นผู้ที่ฉันจะดูแลสุขภาพรายใหม่"><i class="icon -material -white">person_add</i><span class="-hidden">เพิ่มผู้ป่วย</span><!-- <sup class="tooltip" tooltip-uri="'.url('imed/help/patient_add').'">?</sup> --></a>'
			, array('class'=>'nav-patient -search')
		);


	$ui->add('<sep>');

	$ui->add('<a href="'.url('imed').'"><i class="icon -material">home</i><span>Home</span></a>', array('class'=>'nav-patient -home'));
	$ui->add('<a class="" href="'.url('imed/social').'" title="กลุ่มทำงาน"><i class="icon -material">group</i><span>Groups</span></a>', array('class'=>'nav-patient -group'));
	$ui->add('<a class="" href="'.url('imed').'"><img src="'.model::user_photo(i()->username).'" width="32" height="32" />'.i()->name.'</a>', array('class'=>'nav-patient -member'));
	$ui->add('<a class="sg-action" href="'.url('imed/help').'" title="Help" data-rel="#imed-app"><i class="icon -material">help</i><span>&nbsp;</span></a>', array('class'=>'nav-patient -help'));

	$ret .= $ui->build();


	/*
	$ret .= '<nav id="imed-box-patient" class="nav -patient">';
	$ret .= '<ul>'._NL;


	$ret.='<li class="nav-patient -search">';
	$ret.='<span id="patientPhoto" class="patient-photo"><img src="'.imed_model::patient_photo($rs->psnid).'" width="32" height="32" /><span class="arrow-right "></span></span>';
	$ret.='<input id="patientSearch" class="sg-autocomplete form-text patient-search" type="text" size="15" maxlength="100" accesskey="/" name="pn" autocomplete="off" tabindex="" value="" data-query="'.url('imed/api/person').'" data-callback="patientSearchCallback" title="ป้อนชื่อผู้ป่วยหรือเลข 13 หลัก เพื่อค้นหา" placeholder="ป้อนชื่อผู้ป่วยหรือเลข 13 หลัก">';
	$ret.='<i class="icon -search"></i>';
	$ret.='</li>';
	$ret.='<li class="nav-patient -add"><a id="imed-patient-add" class="btn -primary -add -circle32" href="'.url('imed/patient/add').'" data-rel="#imed-app" data-tooltip="ป้อนชื่อในช่องด้านซ้ายและคลิก <strong>+เพิ่มรายชื่อ</strong><br />เพื่อเพิ่มรายชื่อในช่องค้นหาด้านซ้ายมาเป็นผู้ที่ฉันจะดูแลสุขภาพรายใหม่"><i class="icon -material -white">person_add</i><span class="-hidden">เพิ่มผู้ป่วย</span><!-- <sup class="tooltip" tooltip-uri="'.url('imed/help/patient_add').'">?</sup> --></a></li>';


	$ret .= '</ul>';
	*/
	$ret .= '</nav>'._NL;

	//		$ret.='<img class="profile" id="imed-patient-photo" src="'.imed_model::patient_photo().'" />';
	$ret.='<div id="myWrite" class="myWrite"><textarea id="myWriteBox" class="writeBox" data-service="Take notes" placeholder="เขียนบันทึกของคุณ"></textarea><div class="toolbar"></div></div>'._NL;
	//$ret.='<div id="help"><h2>ระบบช่วยเหลือ</h2></div>'._NL;
	$ret.='</div><!-- imed-box -->'._NL;

	//$self->theme->toolbar = $ret;

	//head('jsapi','<script type="text/javascript" src="https://www.google.com/jsapi"></script>');
	head('js.imed.js','<script type="text/javascript" src="imed/js.imed.js?v=1"></script>');
	head('js.imed.public.js','<script type="text/javascript" src="imed/js.imed.public.js"></script>');

	$retNot.='
<script type="text/javascript">


// Set for myWriteBox
// Save post message

// @deprecate :: Plese user imed.js => Save post message
/*
$("#imed-box").on("click",".toolbar>ul.post>li>button", function() {
	notify("กำลังบันทึก กรุณารอสักครู่");
	var $writeBox = $(".writeBox");
	var msg=$writeBox.val();
	var service=$writeBox.data("service");
	para={pid: patientID, seq: seq, msg: msg, service: service};
	console.log("iMed Toolbar Save",para)
	$.post(url+"imed/patient/"+patientID+"/visit.save",para,function(html) {
		notify("บันทึกเรียบร้อย",20000);
		if ($("#imed-my-note").length==0) {
			$("#myWrite>.toolbar>ul>li:nth-child(5)>a").trigger("click");
		} else {
			$("#imed-app").prepend(html);
	//				$("#imed-my-note").prepend(html).masonry("reload");
		}
		$(".writeBox").val("");
		seq=0;
	});
});
*/

// Load Patient Information
$("#imed-box").on("click", "#patient-info a", function(e) {
var url=$(this).attr("href");
notify("Loading Patient Information...",5000);
//alert("Load toolbar url "+url);
if ($(this).attr("rel")==undefined) {
	$.get(this.href,{id: patientID,write: $(".writeBox").val()}, function(html) {
		$("#imed-app").empty().append(html);
		notify();
	});
//		e.preventDefault();
	return false;
}
});


$("#imed-box").on("change", "#photoimg", function() {
	notify("<img src=\"/library/img/loading.gif\" alt=\"Uploading....\"/> Please wait....");
//		$("#imed-app").html("<img src=\"/library/img/loading.gif\" alt=\"Uploading....\"/>");
$("#imageform input[name=\'pid\']").val(patientID);
$("#imageform input[name=\'seq\']").val(seq);
$("#imageform").ajaxForm({
/*			dataType: "json", */
/*			target: "#imed-app", */
	success: function(data) {
		$("#imed-app").prepend(data);
		notify("Upload photo complete seq="+seq,5000);
	}
}).submit();
});

$("body").on("click","#sidePanel", function() {
var $this=$(this);
//notify("Click left="+$this.height());
if ($this.height()<50) {
	$this.height("auto").width("auto");
	var height=$this.height()+40;
	$this.height(height);
	$("#panelContent").stop(true, false).animate({"left": "0px"}, 100);
} else {
	$this.height("26px").width("26px");
	$("#panelContent").stop(true, false).animate({"left": "-222px"}, 100);
}
});

$(document).ready(function() {
postUrl=$("#imed-app").attr("url");

$("label[for=\'fq\']").hide();

$("#imed-box-patient>ul>li:nth-child(3)>a").addClass("disabled");
$("#imed-box-patient>ul>li:nth-child(4)>a").addClass("disabled");

// Set up Main Tab Bar
$("#help").hide();
$("#imed-box-patient>ul>li:first").addClass("active");

});
</script>';
	return $ret;
}
?>