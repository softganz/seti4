// Refresh
var isRefresh=false
var refreshUrl=$('#app-output').data('load')
var refreshActive=0
var refreshTime=60000
var refreshCount=0

$(document).on('click', '#set-refresh', function(e) {
	console.log('Refresh')
	refreshUrl = $('#app-output').data('load')
	isRefresh = !isRefresh
	$(this).text('Refresh '+(isRefresh?'ON':'OFF'))
	if (isRefresh) refreshPort()
});

function refreshPort() {
	refreshCount++
	//notify('Refreshing ('+refreshCount+')')
	$('#set-refresh').text('Refreshing ('+refreshCount+')')
	$.get(refreshUrl, function(html) {
		notify()
		$('#app-output').html(html)
		$('#set-refresh').text('Refresh ('+refreshCount+')')
	})
}

(function request() {
	if (isRefresh && $('#set-tabs').length) {
		refreshPort()
	}
	// calling the anonymous function after refreshTime milli seconds
	// if (refreshActive) {notify("clear timeout");clearTimeout(refreshActive);}
	refreshActive=setTimeout(request, refreshTime);  //second
})(); //self Executing anonymous function

// Submit symbol search form
$(document).on('submit', '#set-search>form', function(e) {
	var $this=$(this);
	notify('LOADING')
	$.get($this.attr('action'),{symbol: $('.form-text',$this).val()}, function(html) {
		$('#app-output').html(html);
		notify()
	});
	return false;
});

var errorCount=0;

// Show date picker
$(document).on('focus', '#edit-set-datein', function(e) {
	$(this)
	.datepicker({
		clickInput:true,
		dateFormat: "dd/mm/yy",
		altFormat: "yy-mm-dd",
		altField: "#edit-set-date",
		disabled: false,
		monthNames: thaiMonthName,
	})
})

$(document).on('change','form#edit-set .form-text, form#edit-set .form-select', function(e) {
	var $this=$(this)
	var id=$this.attr('id')
	var comFeeRate=0.15
	var tradFeeRate=0.0068
	var chargeFeeRate=0.001
	var vatFeeRate=7
	if (id=='edit-set-volumes' || id=='edit-set-price' || id=='edit-set-bsd' ) {
		var subNetAmount=$('#edit-set-volumes').val()*$('#edit-set-price').val()
		var commission=subNetAmount*comFeeRate/100
		var tradingFee=subNetAmount*tradFeeRate/100
		var  chargingFee=subNetAmount*chargeFeeRate/100
		var allFee=commission+tradingFee+chargingFee
		var vatFee=allFee*vatFeeRate/100
		var netAmount=0
		var bsd=$('#edit-set-bsd').val()
		if (bsd=='B') {
			netAmount=subNetAmount+allFee+vatFee
		} else if (bsd=='S') {
			netAmount=subNetAmount-allFee-vatFee
		} else if (bsd=='D') {
			netAmount=subNetAmount
		}
		commission=commission.toFixed(2)
		tradingFee=tradingFee.toFixed(2)
		chargingFee=chargingFee.toFixed(2)
		vatFee=vatFee.toFixed(2)
		$('#edit-set-commission').val(commission)
		$('#edit-set-tradingfee').val(tradingFee)
		$('#edit-set-chargingfee').val(chargingFee)
		$('#edit-set-vat').val(vatFee)
		$('#edit-set-netamount').val(netAmount.toFixed(2))
	} else {
		var subNetAmount=parseFloat($('#edit-set-volumes').val()*$('#edit-set-price').val())
		var allFee=parseFloat($('#edit-set-commission').val())+parseFloat($('#edit-set-tradingfee').val())+parseFloat($('#edit-set-chargingfee').val())
		var vatFee=allFee*vatFeeRate/100
		var netAmount=subNetAmount+allFee+vatFee
		$('#edit-set-vat').val(vatFee.toFixed(2))
		$('#edit-set-netamount').val(netAmount.toFixed(2))
	}
})

// Add symbol trade
$(document).on('submit', 'form#edit-set',function(e) {
	var fldCheck=[
								["edit-set-datein","วันที่"],
								["edit-set-symbol","หลักทรัพย์"],
								["edit-set-bsd","B/S/B"],
								["edit-set-volumes","จำนวนหุ้น"],
								["edit-set-price","ราคา"],
								["edit-set-commission","Commission"],
								["edit-set-tradingfee","Trading Fee"],
								["edit-set-chargingfee","Charging Fee"],
								["edit-set-vat","VAT"],
								["edit-set-netamount","Net Amount"],
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
	return true;
});

// Import daily trade from SET
$(document).on('submit', 'form#edit-info', function(e) {
	$.post($(this).attr("action"),$(this).serialize(), function(data) {
		$("#edit-data").val("");
		notify("Saved",10000);
	});
	return false;
});

// Update inline form rel=id of target, data-type=type of return
$(document).on('submit', 'form[rel]', function(e) {
	var $this=$(this)
	var rel=$this.attr('rel')
	notify('กำลังบันทึก')
	var dataType=$this.data('type')==undefined?null:$this.data('type')
	$.post($this.attr('action'),$this.serialize(), function(data) {
		html=dataType=="json" ? data.html : data
		$("#"+rel).html(html)
		notify()
		if (data.msg) notify(data.msg,5000)
	},dataType)
	return false;
});

// Set symbol wishlist
$(document).on('submit', '#set-note-post', function(e) {
	var $this=$(this)
	notify('กำลังบันทึก')
	$.post($this.attr('action'),$this.serialize(), function(html) {
		$('#set-note-msg').val('')
		$('#set-note-items').html(html)
		notify()
	})
	return false;
});

$(document).on('click','#set-myport>h3>a', function() {
	var $parent=$(this).closest('div');
	$parent.find('ul').toggle()
})

$(document).on('click','#set-wishlist>h3>a', function() {
	var $parent=$(this).closest('div');
	//	alert($parent.attr('id'))
	$parent.find('div').toggle()
	$.get($(this).attr('href'),function(html) {
		$parent.find('div').html(html)
	})
	return false
})

$(document).on('change','#set-cal-cost .form-text', function() {
	var cost=0
	var eps=$('input[name="eps"]').val()
	var growth=$('input[name="g"]').val()
	cost=eps*(8.5+2*growth)
	$('input[name="cost"]').val(cost.toFixed(2))
	return false
})
$(document).on('submit','#set-cal-cost', function() {
	return false
})

$(document).ready(function() {
});
/*
	$("a[group]").each(function(i){
		var $this=$(this);
		var group=$this.attr("group");
		$this.colorbox({rel:group});
	});
*/
	$(document).on('click','a.sg-show', function() {
//		alert($(this).attr('href'))
		$.colorbox({photo:true, href:$(this).attr('href')})
		return false;
	})