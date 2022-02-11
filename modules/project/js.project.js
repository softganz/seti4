/*!
 * Project v4.0.0 by @softganz
 * Copyright 2013 Softganz Group.
 * Licensed under http://www.apache.org/licenses/LICENSE-2.0
 *
 * Designed and built with all the love in the world by @softganz.
 */

var inlineeditAction='click';
var updatePending=0
var updateQueue=0
var database;
var ref;

$(document).ready(function(){
	if (firebaseConfig) {
		database = firebase.database();
		ref = database.ref('/update/');
	}
});

$(window).bind('beforeunload', function(){
	if (updatePending>0) return 'มีข้อมูลยังไม่ได้บันทึก';
});

function projectUpdate($this, value, callback) {
	var $parent=$this.closest(".inline-edit");
	var postUrl=$parent.data('update-url');
	var debug=$this.closest(".inline-edit").data('debug')?true:false;
	if (postUrl==undefined) {
		notify('ปลายทางสำหรับบันทึกข้อมูลทำงานผิดพลาด')
		return
	}
	var para={}
	para=$this.data()
	delete para["options"]
	delete para["data"]
	delete para["event_editable"]
	//console.log($this.data('tpid'));
	para.id=$this.data('tpid') ? $this.data('tpid') : ($parent.data('tpid')?$parent.data('tpid'):(typeof tpid != 'undefined'?tpid:null));
	para.action="save";
	console.log(para)
	//	para.type=$this.data("type");
	//	para.group=$this.data("group");
	//	para.fld=$this.data("fld") ? $this.data("fld") : $this.data("field");
	//	para.tr=$this.data("tr");
	console.log('value = '+value)
	para.value=value.replace(/\"/g, "\"");
	para.period=$this.closest(".inline-edit").data("period");
	//	if ($this.data("parent")) para.parent=$this.data("parent");
	//	if ($this.data("ret")) para.ret=$this.data("ret");
	$this.data('value',para.value);

	updatePending++
	updateQueue++

	notify("กำลังบันทึก กรุณารอสักครู่...."+(debug?' (Project updating : update pending='+updatePending+')'+'<br />Post url : '+postUrl+'<br />'+para:''));

	// Lock all inline-edit-field until post complete
	$parent.find('.inline-edit-field').addClass('-disabled');

	//console.log("length="+$("[data-group=\""+para.group+"\"]").length)
	//alert("Wait")
	//console.log(para)
	$.post(postUrl,para, function(data) {
		updatePending--
		$parent.find('.inline-edit-field').removeClass('-disabled');

		if (data=="" || data=="<p>&nbsp;</p>") data="...";
		if (para.ret=="refresh") window.location=window.location;

		if ($this.data("type")=='autocomplete') {
			$this.data('value',para.value)
			$this.html(data.value);
		} else if ($this.data("type")=="radio") {
		} else if ($this.data("type")=="checkbox") {
		} else if ($this.data("type")=="select") {
			var selectValue
			if ($this.data('data')) {
				selectValue = $this.data('data')[data.value]
			} else {
				selectValue = data.value
			}
			$this.html('<span>'+selectValue+'</span>');
		} else if (data.value=="" || $this.data('value')=='') {
			$this.html("...");
		} else {
			$this.html('<span>'+data.value+'</span>');
		}

		var replaceTrMsg='';
		//console.log("para.tr="+para.tr+" data.tr="+data.tr);
		if (para.tr!=data.tr) {
			if (data.tr==0) data.tr="";
			//console.log(para.group+' : '+para.tr+' : '+data.tr)
			$('[data-group="'+para.group+'"]').data("tr",data.tr);
			replaceTrMsg='Replace tr of group '+para.group+' with '+data.tr;
			console.log(replaceTrMsg);
		}
		notify((data.error?data.error:data.msg)+(debug?"<br />update queue="+updateQueue+", update pending="+updatePending+"<br />Parameter : group="+para.group+", fld="+para.fld+", tr="+para.tr+", Value="+data.value+"<br />Debug : "+data.debug+"<br />Return : tr="+data.tr+"<br />"+replaceTrMsg:""),debug?300000:5000);

		// Process callback function
		var callbackFunction = $this.data("callback");
		if (callbackFunction) {
			if (typeof window[callbackFunction] === 'function') {
				window[callbackFunction]($this,data,$parent);
			} else if ($this.data("callbackType") == 'silent') {
				$.get(callback, function() {})
			} else {
				window.location=callback;
			}
		}

	},"json")
	.fail(function() {
    notify("ERROR ON POSTING. Please contact admin.");
  });

	if (firebaseConfig) {
		//console.log(para);
		var data={}
		data.tags="Project Transaction Update";
		if (typeof para.id != "undefined") data.tpid=para.id;
		if (typeof para.group != "undefined") data.group=para.group;
		if (typeof para.fld != "undefined") data.field=para.fld;
		if (typeof para.tr != "undefined") data.tr=para.tr;
		data.value=para.value;
		data.url=window.location.href;
		//data.time=new Date().toW3CString();
		data.time=firebase.database.ServerValue.TIMESTAMP;
		//alert(data.time);
		//console.log(data)
		//ref = database.ref('/update/aa/');
		ref.push(data, function(error){
			if (error) {
				console.log("Data could not be saved." + error);
			} else {
				console.log("Data saved successfully.");
			}
		});
		//ref.off();
		//console.log(ref);
	}
};


$(document).on(inlineeditAction,".inline-edit .inline-edit-field", function() {
	var version = '0.10project'
	var $this=$(this);
	var postUrl=$this.closest(".inline-edit").data('update-url');
	var inputType=$this.data("type");

	console.log('$.sgInlineEdit version ' + version + ' start')

	if (inputType=="money" || inputType=="numeric" || inputType=="text-block") {
		inputType="text"
	} else if (inputType=='radio' || inputType=='checkbox') {
		var callback=$this.data('callback');
		value=$this.is(":checked")?$this.val():""
		projectUpdate($this,value,callback);
		return
	} else if (inputType=='link') {
		return
	} else if (inputType=="") {
		inputType="text"
		$this.data('type','text')
	}
	$this.addClass('-'+$this.data('type'))

	var defaults = {
		type: inputType,
		indicator : '<img src="/library/img/loading.gif" />',
		tooltip: "คลิกเพื่อแก้ไข",
		/*
		onblur : function(value) {
				$(this).closest('.inline-edit-field').removeClass('-active');
				notify(value)
				$(this).closest('form').submit();
			},
			*/
		// onblur: function() {"submit"},
		onblur: $this.data("onblur") ? $this.data("onblur") : "submit",
		data: function(value, settings) {
				if ($this.data("data")) return $this.data("data");
				else if ($this.data('value')!=undefined) return $this.data('value');
				else if (value=='...') return '';
				return value;
			},
		loadurl  : $this.data("load")?postUrl+"?action=get&tpid="+tpid+"&group="+$this.data("group")+"&tr="+$this.data("tr")+"&fld="+$this.data("fld"):"",
		loaddata : function(value, settings) {
				if ($this.data("load")) {
					return {tr:$this.data("tr")};
				}
			},
		autocomplete : {
			source: function(request, response){
				notify(request.term);
				var queryUrl=$this.data('query')
				//notify('fld='+$this.data('fld'))
				//$.get(url+"api/address?q="+encodeURIComponent(request.term), function(data){
				$.get(queryUrl+'?q='+encodeURIComponent(request.term), function(data){
					response($.map(data, function(item){
					return {
						label: item.label,
						value: item.label+($this.data('ret')=='address' || $this.data('fld')=='area' ? '|'+item.value : '')
					}
					}))
				}, "json");
			},
			minLength: 2 /*$this.data('minlength') ? $this.data('minlength') : 5 */,
			dataType: "json",
			cache: false,
			select: function(event, ui) {
				this.value = ui.item.label;
				//$this.data('value',ui.item.label)
				//alert('data-fld='+$this.data('fld')+"\nlabel="+ui.item.label+'\nvalue='+ui.item.value);
			}
		},
		cssclass	: "inlineedit",
		width			: "none",
		height 		: 'none',
		cancel		: $this.data("button")=='yes'?"<button class=\"btn -link -cancel\"><i class=\"icon -cancel -gray\"></i><span>ยกเลิก</span></button>":null,
		submit		: $this.data("button")=='yes'?"<button class=\"btn -primary\"><i class=\"icon -save -white\"></i><span>บันทึก</span></button>":null,
		monthNames: thaiMonthName,
		dateFormat: "dd/mm/yy",
		showButtonPanel: true,
		placeholder: '...',
		datepicker : {format: "dd/mm/yy"},
		event 		: 'edit',
		container : $this,
	}

	var options = {}
	var dataOptions = $this.data('options')
	var settings = $.extend({}, defaults, options, dataOptions)

	if ($this.data('type') == 'textarea') settings.inputcssclass = 'form-textarea';
	else if ($this.data('type') == 'text') settings.inputcssclass = 'form-text';
	else if ($this.data('type') == 'number') settings.inputcssclass = 'form-text -number';
	else if ($this.data('type') == 'email') settings.inputcssclass = 'form-text -email';
	else if ($this.data('type') == 'url') settings.inputcssclass = 'form-text -url';
	else if ($this.data('type') == 'autocomplete') settings.inputcssclass = 'form-text -autocomplete';
	else if ($this.data('type') == 'select') settings.inputcssclass = 'form-select';

	$this
	.editable(function(value, settings) {
		var callback=$this.data('callback');
		projectUpdate($this,value,callback);
		return value;
	} , settings).trigger('edit');
});

$(document).on('focus', '.inline-edit-field input', function () {
	$(this).closest('.inline-edit-field').addClass('-active');
});
$(document).on('blur', '.inline-edit-field input', function () {
	$(this).closest('.inline-edit-field').removeClass('-active');
});

$(document).on('keydown', ".inline-edit .inline-edit-field", function(evt) {
	if(evt.keyCode==9) {
		var $this=$(this);
		var $allBox=$this.closest(".inline-edit");
		var nextBox='';
		var currentBoxIndex=$(".inline-edit-field").index(this);
		if (currentBoxIndex == ($(".inline-edit-field").length-1)) {
			nextBox=$(".inline-edit-field:first");
		} else {
			nextBox=$(".inline-edit-field").eq(currentBoxIndex+1);
		}
		$(this).find("input").blur();
		$(nextBox).trigger('click')
		//		notify('Index='+currentBoxIndex+$this' Length='+$allBox.children(".inline-edit-field").length+' Next='+nextBox.data('fld'))
		return false;
	};
});

$(document).ready(function() {

	//	$("#project-map").css("height",$("#project-info").css("height"));
	//alert($(window).height())
	var otherHeight=0;
	//otherHeight=$('#header-wrapper').css('height')+$('#paper-toolbar').css('height');
	$("#project-map").css("height",$(window).height()*2/3);

	$("#project-develop-search [name=q]")
		.autocomplete({
			source: function(request, response){
				$.get(url+"project/get/develop?n=50&q="+encodeURIComponent(request.term), function(data){
					response($.map(data, function(item){
					return {
						label: item.label,
						value: item.value
					}
					}))
				}, "json");
			},
			minLength: 2,
			dataType: "json",
			cache: false,
			select: function(event, ui) {
				this.value = ui.item.label;
				// Do something with id
				notify("Please wait...");
				window.location=url+"project/develop/view/"+ui.item.value;
				return false;
			}
		});

});


$(document).on('click','#project-report-follow [data-action]', function() {
	$this=$(this);
	var $parent=$this.closest(".inline-edit")
	var postUrl=$parent.data('update-url')
	if ($this.attr("title")==undefined || ($this.attr("title") && confirm($this.attr("title")+" กรุณายืนยัน?"))) {
		var action=$this.data("action")
		var group=$this.data("group")
		var tr=$this.data("tr");
		//		alert("peroid="+period+" postUrl="+postUrl+" url="+window.location.href+"/part/"+currentPart.substr(1));
		var para={id: tpid, period:period, action: action, group: group, tr: tr}
		$.post(postUrl,para, function(data) {
			notify(data.msg)
			$.get(url+$parent.data("load"),function(data) {
				$parent.html(data)
				displayPart()
			});
		}, "json")
	}
	return false
})

$(document).on('submit',"#addfo", function() {
	$this=$(this)
	var $parent=$this.closest(".inline-edit")
	$.post($this.attr("action"),$(this).serialize(), function(data) {
			$.get(url+$parent.attr("rel-uri"),function(data) {
				$parent.html(data)
				displayPart()
			})
		})
	return false
})

$(document).on("click", ".project-lockmoney", function() {
	var $this=$(this)
	var $child=$this.children('i');
	$.post($this.attr('href'),function(data) {
		notify(data.msg,5000)
		if ($child.text() == 'lock') {
			$child.text('lock_open').addClass('-gray')
		} else {
			$child.text('lock').removeClass('-gray')
		}
		//$this.html(data.value)
	},"json")
	return false
})

$(document).on('click', '[data-show]', function(e) {
	$('.'+$(this).data('show')).toggle()
	return false;
})

function m1_checksum($this, data) {
	var $income=$('#project-report-m1-summary>tbody>.row-1>.col-1')
	var sumIncome=0.00
	var sumRealBalance=0.00
	$('#project-report-m1-summary>tbody>.row-1>.col-1>span').each (function () {
		var money=$(this).data('value')
		if (typeof money=='string') {
			money=parseFloat(money.replace(/,/g, ''));
		}
		if (isNaN(money)) money=0;
		//alert(isNaN(money) + typeof money)
		//alert(money)
		sumIncome+=money
	})
	sumIncome.toFixed(2)
	var sumExpense=parseFloat($('#project-m1-sum-expense').text().replace(/,/g, ''))
	$('#project-report-m1-summary>tbody>.row-1>.col-3>span').each (function () {
		var money=$(this).data('value')
		if (typeof money=='string') money=parseFloat(money.replace(/,/g, ''))
		if (isNaN(money)) money=0;
		sumRealBalance+=money
	})
	sumRealBalance.toFixed(2)
	var balance=(sumIncome - sumExpense).toFixed(2)
	$('#project-m1-sum-income').number(sumIncome,2,'.',',')
	$('#project-m1-sum-balance').number(balance,2,'.',',')
	if (sumRealBalance==balance) {
		$('#project-m1-sum-balance').removeClass('notbalance')
		$('#project-m1-sum-balance-msg').hide()
	} else {
		$('#project-m1-sum-balance').addClass('notbalance')
		$('#project-m1-sum-balance-msg').addClass('notbalance').show()
	}
	//notify('sumIncome='+sumIncome+' sumExpense='+sumExpense+' sumRealBalance='+sumRealBalance+' balance='+balance,300000)
}

function projectPlanAddObjective($this,data,$parent) {
	//console.log(data);
	var url=$this.data('url');
	//console.log(url);
	//console.log($this.data('objid'));
	if (data.value) {
		var para={id:data.tr, to:data.value, confirm:'yes'}
		$.get(url+"/addobj/"+data.tr,para,function(html){
			//console.log(html)
		});
	} else {
		var para={id:$this.data('objid'), actid:data.tr, confirm:'yes'}
		$.get(url+"/removeobj/"+data.tr,para,function(html){
			//console.log(html)
		});
	}
}

function projectPlanTitleUpdate($this,data) {
	var $title=$("#plan-header-"+data.tr+">.title>.-title");
	$title.text(data.value);
}

function projectObjectiveTitleUpdate($this,data) {
	console.log("projectObjectiveTitleUpdate");
	var $title=$("#objective-header-"+data.tr+">.title>.-title");
	$title.text(data.value);
}

function treeRemove($this) {
	var $container=$this.closest(".-header");
	console.log($container.attr("class"))
	$container.next().remove();
	$container.remove();
}

function projectPlanExpenseAdd($this,data) {
	//console.log("callback "+$this.data("planid"))
	var $container=$("#plan-header-"+$this.data('planid'));
	$container.addClass('-activity');
	//console.log($container.children('.ui-menu.-main').find('.-add-plan').attr('class'));
	$container.children('.ui-menu.-main').find('.-add-plan').hide();
	console.log('ret='+$container.data('ret'))
	if ($container.data('ret')) window.location=$container.data('ret');
}

function projectDevelopMainactAddObjective($this,data,$parent) {
	//console.log(data);
	var url=$this.data('url');
	//console.log(url);
	//console.log($this.data('objid'));
	if (data.value) {
		var para={'action':'addobj','id':data.tr, 'to':data.value, 'confirm':'yes'}
		$.get(url,para,function(html){
			//console.log(html)
		});
	} else {
		var para={'action':'removeobj','id':$this.data('objid'),'actid':data.tr}
		$.get(url,para,function(html){
			//console.log(html)
		});
	}
}

function projectDevelopIssueChange($this, ui) {
	//project-develop-problem
	var loadUrl = $this.data("callbackUrl")
	$.get(loadUrl, function(html) {
		//alert(html)
		$("#main").html(html)
	})
	console.log($this.data("callbackUrl"))
}





//Add input type autocomplete to jEditable
/*
$.editable.addInputType('autocomplete', {
	element : $.editable.types.text.element,
	plugin : function(settings, original) {
		$('input', this).autocomplete(settings.autocomplete);
	}
});
*/

function refreshContent($this,data,$parent) {
	if (typeof $parent === 'undefined') $parent = $this;
	var refreshUrl = $parent.data('refresh-url');
	notify('กำลังโหลดใหม่');
	// console.log('Refresh url '+refreshUrl);
	$.get(refreshUrl,function(html) {
		//console.log('Refresh url completed.');
		$("#main").html(html);
		notify('');
	});
}

function projectDevelopActivityDateChange(dateText,inst) {
	console.log('check date from')
	console.log(inst.attr("class"))
}




$(document).on("click",".-showbtn",function() {
	$(this).closest("tr").addClass("-input-active").find(".form-item").show();
});



$(document).on("click",'.module-project .ui-tree .-showdetail',function() {
	var $this=$(this);
	var $icon=$this.children('.icon');
	console.log("Show detail")
	if ($icon.hasClass('-down')) {
		$icon.removeClass('-down').addClass('-up');
		$this.parent().next().show();
	} else {
		$icon.removeClass('-up').addClass('-down');
		$this.parent().next().hide();
	}
	return false;
});



