// Global variable
var patientID=0;
var seq=0;
var vitalSign={};
var fldClick;
var postUrl;
var tab=1;
var updatePending=0;
var updateQueue=0;
var updateCount=0;
var loadCount=0;

function imedCall() {
	alert("CALL")
}
function initPatient(pid, name = '', initTab = '') {
	console.log("Init patient "+pid)
	patientID=pid
	if (initTab) tab=initTab
	notify("Loading patient");

	/*
	// Click Visit button
	$("#imed-box-patient>ul>li:nth-child(3)>a").removeClass("disabled").show().css("display","inline-block");
	$("#imed-box-patient>ul>li:nth-child(4)>a").removeClass("disabled").show().css("display","inline-block");
	$("#imed-box-patient>ul>li:nth-child(4)>a").trigger("click");
	*/

	var loadUrl = url+'imed/patient/load'
	var para={id: patientID};
	$.get(loadUrl, para, function(data) {
		//console.log(data)
		notify();
		console.log("Load Patient Box completed.");

		if (data.realname) name = data.realname
		if (name) {
			$("#patientSearch").val(name)
			$("#imed-box h2").text(name)
		}

		$("#patientPhoto>img").attr("src",data.photo);

		$('#myWrite').empty().append(data.toolbox).fadeIn(200)

		if (data.psnInfo) {
			$("#patient-info>li").show()
		} else {
			$("#patient-info>li").hide()
			$(".patient--type--visit").show()
			$(".patient--type--poorman").show()
		}

		//$(".writeBox").focus()
		window.scrollTo(0, 0)
		$('#imed-app').html(data.info)
		//$("#patient-info>li:nth-child("+tab+")>a").trigger("click");
		tab=1
		//				$("#patient-info>li:nth-child(3)>a").trigger("click");
		console.log("Init patient completed.")
	},'json');
	return false;
}

function getPatientID() {
	return patientID;
}

function patientSearchCallback($this,ui) {
	initPatient(ui.item.value)
	return false;
}

function imedAddPersonCallback($this,data) {
	$('#imed-app').html(data.html)
	if (data.error) {
		notify(data.error)
	} else if (data.pid) {
		initPatient(data.pid,data.name)
		$("#imed-nav-homevisit").trigger("click")
		$.get(url+"imed/patient/individual/"+data.pid,function(data) {
			$("#imed-app").html(data);
		});
	}
}

function loadDisabled() {
	$("#patient-info>li:nth-child(3)>a").trigger("click");
}



// Init patient when like has role is patient was click
$(document).on('click','a[role="patient"]', function() {
	if ($(this).data('pid')) initPatient($(this).data('pid'))
	return false
});

// Load Patient Information
$(document).on("click", "#patient-info a", function(e) {
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

// Save post message
$(document).on("click",".toolbar>ul.post>li>button", function() {
	notify("กำลังบันทึก กรุณารอสักครู่");
	var $writeBox = $(".writeBox");
	var msg = $writeBox.val();
	var service = $writeBox.data("service");
	var para = {psnId: patientID, seq: seq, msg: msg, service: service};
	// console.log("iMed WriteBox On Click to Save",para)
	// console.log(url+"imed/api/visit/create")
	$.post(url+"imed/api/visit/create/",para,function(html) {
		notify("บันทึกเรียบร้อย",20000);
		if ($("#imed-my-note").length == 0) {
		} else {
			//$("#imed-my-note").prepend(html);
			//$("#imed-my-note").prepend(html).masonry("reload");
		}
		$(".writeBox").val("");
		$(".patient--type--visit>a").trigger("click");
		seq=0;
	});
});






(function($) {	// iMed PoorMan Extend Function
	var lastid=0;
	var isFormChangeWaitng=true;

	$(document).on("change","#imed-poorman-form #edit-data-qt-PSNL-PRENAME",function(){
		console.log($(this).val())
		if ($(this).val()=="อื่นๆ") {
			$(this).next().show().focus()
		} else {
			$(this).next().hide()
		}
	});


	$(document).on("click","#imed-poorman-form input[name=\'data[qt:PSNL.HOME.NOTSAMEADDRESS]\']",function() {
		if($(this).is(":checked")) {
			$("#imed-poorman-form-regishome").show();
		} else {
			$("#imed-poorman-form-regishome").hide();
		}
	});

	$(document).on("submit", "#imed-poorman-form", function() {
		console.log("Form "+$(this).attr("id")+" submit");
		return true;
	})
	.on("keydown", "#imed-poorman-form input:text", function(event) {
		var n = $("input:text").length
		if(event.keyCode == 13) {
			event.preventDefault()
			var nextIndex = $("input:text,textarea").index(this) + 1
			if(nextIndex < n)
				$("input:text,textarea")[nextIndex].focus()
			return false
		}
	});

	$(document)
	.on("change","#imed-poorman-form input:not(.inline-upload), #imed-poorman-form textarea",function(){
		//if (!isFormChangeWaitng) return;
		var $this=$(this);
		var addPara="";
		isFormChangeWaitng=false;

		if ($this.attr("type")=="file") return false;
		console.log("Update change of "+$this.attr("type")+" "+$this.attr("name"));
		if ($this.attr("type")=="checkbox") {
			console.log("Check value = "+$this.val())
			$this.data("old",$this.val());
			if ($this.is(":checked")) {
				;
			} else {
				addPara="&"+$this.attr("name")+"=";
				//$this.val("");
			}
		}
		//console.log("ID "+$this.attr("id")+" change.");
		var $form=$this.closest("form");
		var para=$form.serialize()+addPara;
		//console.log(para)
		$.post($form.attr("action"),para, function(data) {
			console.log("qtref="+data.qtref+" psnid="+data.psnid);
			console.log("Save result "+data.msg);

			$("#qtref").val(data.qtref);
			$("#psnid").val(data.psnid);
			$("#qtrefno").val(data.qtrefno);
			//if ($this.attr("type")=="checkbox") $this.val($this.data("old"));
			isFormChangeWaitng=true;
		},
		"json");
		return false;
	})
	.on("change","#imed-poorman-form .inline-upload",function(e){
		var $this=$(this);
		var $form=$this.closest("form");
		var id="photo-"+(++lastid);
		console.log("Poorman inline upload file start and show result "+id)
		console.log("Inline action "+$form.attr("action"));
		var insertElement="<li id=\""+id+"\" class=\"-hover-parent\"><img class=\"photoitem\" src=\"/library/img/loading.gif\" /></li>";
		$this.closest("li").before(insertElement);
		$form.ajaxSubmit({
			success: function(data) {
				console.log("Inline upload Save result :: "+data);
				$("#"+id).html(data);
				$this.val("");
				$this.replaceWith($this.clone(true));
			}
		})
		//e.stopPropagation()
		return false;
	});

	$(document).on("change","#imed-poorman-form #edit-data-birth-year",function(){
		console.log("Age change")
		var age=new Date().getFullYear()-$(this).val();
		$("#age").text(age);
	});
})(jQuery);
