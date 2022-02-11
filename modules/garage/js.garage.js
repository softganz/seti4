console.log('SG-garage Version 4.00 loaded')

var currentRepairInfo={"priceA":0,"priceB":0,"priceC":0,"priceD":0};
var repairChange=false;

function garageRepairCodeSelect($this,ui) {
	currentRepairInfo=ui.item;
	repairChange=true;
}

function garageJobTranEditClick() {
	$('html,body').animate({ scrollTop: $('#garage-job-tran').offset().top-100 }, 'fast');
	$('#repaircode').focus();
}

function garageCodeEditClick() {
	$('html,body').animate({ scrollTop: $('#garage-code-trans').offset().top-100 }, 'fast');
	$('#codeid').focus();
}

$(document).on("change","#garage-job-tr-new #repairname",function() {
	repairChange=true;
});

$(document).on("change","#garage-job-tr-new #damagecode",function() {
	var damagecode=$(this).val();
	var price=0;
	if (damagecode=="A") price=currentRepairInfo.priceA
	else if (damagecode=="B") price=currentRepairInfo.priceB
	else if (damagecode=="C") price=currentRepairInfo.priceC
	else if (damagecode=="D") price=currentRepairInfo.priceD
	console.log("Damage Code is "+damagecode+" price = "+price);
	$("#price").val(price);

	var pretext=$(this).find(':selected').data('pretext');
	console.log("pretext="+pretext);
	//$("#garage-job-tr-new #repairname").val(pretext+" "+currentRepairInfo.name);
	$("#garage-job-tr-new #repairname").val(pretext+" "+$("#garage-job-tr-new #repairname").val());
	repairChange=false;
});

$(document).on('change','#garage-job-tr-new #qty,#garage-job-tr-new #price,#garage-job-tr-new #damagecode,#garage-job-tr-new #repaircode,#garage-job-tr-new #discountamt,#garage-job-tr-new #vatamt',function() {
	garageCalculateJobTran();
}).on('keyup','#garage-job-tr-new #qty,#garage-job-tr-new #price,#garage-job-tr-new #discountamt,#garage-job-tr-new #vatamt',function() {
	garageCalculateJobTran();
}).on('keyup','#garage-job-tr-new #discountrate',function() {
	console.log($(this).val());
	//discountamt=+$("#garage-job-tr-new #discountamt").val();
	var qty=0;
	var price=0;
	var discountamt=0;
	var discountrate=0;
	qty=+$("#garage-job-tr-new #qty").val();
	price=+$("#garage-job-tr-new #price").val();
	discountrate=$(this).val();
	discountamt=qty*price*discountrate/100;
	discountamt=discountamt.toFixed(2);
	$("#garage-job-tr-new #discountamt").val(discountamt);
	garageCalculateJobTran();
});

function garageCalculateJobTran() {
	var qty;
	var price;
	var totalSale=0;
	var discountamt=0;
	var vatamt=0;
	qty=+$("#garage-job-tr-new #qty").val();
	price=+$("#garage-job-tr-new #price").val();
	if ($("#garage-job-tr-new #discountamt").length) {

		discountamt=+$("#garage-job-tr-new #discountamt").val();
	}
	if ($("#garage-job-tr-new #vatamt").length) {
		vatamt=+$("#garage-job-tr-new #vatamt").val();
	}
	totalSale=qty*price-discountamt+vatamt;
	$("#garage-job-tr-new #totalsale").val(totalSale.toFixed(2));
	console.log("Calculate total sale="+totalSale);
}

	function garagePartWaitUpdate($this, data) {
		console.log($this)
		console.log(data)
		$this.closest('tr').toggleClass('-wait')
	}

$(document).on('click','.garage-code-trans .search',function(){
	var $this=$(this);
	var q=$this.prev().val();
	var $form=$this.closest('form');
	var currentUrl=window.location.origin+window.location.pathname;
	console.log($form.attr('action'));
	console.log($this.prev().val());
	console.log(currentUrl);
	window.location=currentUrl+'?q='+q;

	return false;
});

$(document).on('change', '.garage-job-in-before-form', function() {
	var $target = $(event.target)
	console.log($target)
	var $this = $(this)
	var $form = $this.closest('form');
	var para = {}
	para.group = 'before'
	para.key = $target.attr('name')
	var inputType = $target.attr('type')
	if (inputType == undefined) inputType = $target.prop('tagName')
	switch (inputType.toUpperCase()) {
		case 'CHECKBOX':
			para.value = $target.is(':checked') ? $target.val() : ''
			break;
		case 'SELECT':
			para.value = $target.val()
			break;
		case 'TEXTAREA':
			para.value = $target.val();
			break;
		case 'TEXT':
			para.value = $target.val();
			break;
	}
	//console.log(para)
	$.post($form.attr('action'), para, function(data){
		//notify(data)
	});
});