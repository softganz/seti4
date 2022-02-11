// Submit symbol search form
$(document).ready(function() {
//	$("#saveup-toolbar>ul>li:nth-child(3)>a").addClass("disabled");
//	$("#saveup-toolbar>ul>li:nth-child(3)>a").addClass("disabled");
})

var errorCount=0;

// Show date picker
$(document).on('focus', 'input.form-date', function(e) {
	$(this)
	.datepicker({
		clickInput:true,
		dateFormat: "dd/mm/yy",
		altFormat: "yy-mm-dd",
		altField: "#saveup-bank-trans-date",
		disabled: false,
		monthNames: thaiMonthName,
	})
})

// Add member
$(document).on('submit', 'form#saveup-addmember',function(e) {
	var fldCheck=[
		["edit-member-mid","หมายเลขบัญชี"],
		["edit-member-firstname","ชื่อ"],
		["edit-member-lastname","นามสกุล"],
	];
	var error;
	for (fld in fldCheck) {
		if ($("#"+fldCheck[fld][0]).val().trim()=="") {
			//		alert(fld[0]+$("#"+fldCheck[fld][0]).val().trim());
			error=fld;
			break;
		}
	}
	//		alert("Error "+error[0]);
	if (error) {
		// Notification and return to form
		var errorMsg="กรุณาป้อน \""+fldCheck[error][1]+"\"";
		if (errorCount>10) alert(errorMsg); else notify(errorMsg,10000);
		$("#"+fldCheck[error][0]).focus();
		++errorCount;
		return false;
	}
	$.post($(this).attr("action"),$(this).serialize(), function(html) {
		$("#saveup-main").html(html)
	});
	return false;
});

// Import daily trade from SET
$(document).on('submit', 'form#saveup-bank-trans-add', function(e) {

	var EnteredDate = $("#saveup-bank-trans-date").val()
	var date = EnteredDate.substring(8, 10);
	var month = EnteredDate.substring(5, 7);
	var year = EnteredDate.substring(0, 4);
	var myDate = new Date(year, month - 1, date);

	var today = new Date();

	if (myDate >= today) {
		alert("วันที่ลงรายการฝาก-ถอน มากกว่าวันปัจจุบัน. กรุณาเลือกวันที่ใหม่อีกครั้ง");
		return false
	}

	$.post($(this).attr("action"),$(this).serialize(), function(html) {
		$("#saveup-main").html(html);
	});
	return false;
});

// Set symbol wishlist
$(document).on('submit', '#set-setting-wishlist>form', function(e) {
	var $this=$(this);
	var dataType=$this.data('type');
	$.get($this.attr('action'),$this.serialize(), function(data) {
		html=dataType=="json" ? data.html : data;
		$("#set-info").html(html);
		if (data.msg) notify(data.msg,5000);
	},dataType);
	return false;
});

