/**
 * SoftGanz JavaScript Library
 *
 * library :: Javascript Library For SoftGanz
 * Created :: 2009-09-22
 * Modify  :: 2025-12-29
 * Version :: 4
 * Version :: 4.00.09
 *
 * Copyright :: Copyright (c) 2000-present , The SoftGanz Group By Panumas Nontapan
 * Author    :: Panumas Nontapan <webmaster@softganz.com>
 * Website   :: http://www.softganz.com
 * ============================================
 * This program is free software. You can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License.
 * ============================================
 *
 * Using Library
 * - jquery.3.3.js : https://jquery.com
 * - jquery.form.js : http://jquery.malsup.com/form/
 * - jquery.editable.js : https://github.com/*NicolasCARPi/jquery_jeditable
 * - gmaps.js : https://hpneo.github.io/gmaps/
 */

'use strict'

let sgLibraryVersion = '4.00.09'
let thaiMonthName = ["มกราคม", "กุมภาพันธ์", "มีนาคม", "เมษายน", "พฤษภาคม", "มิถุนายน", "กรกฎาคม", "สิงหาคม", "กันยายน", "ตุลาคม", "พฤศจิกายน", "ธันวาคม"];
let debugSignIn = false
let firebaseConfig
let gMapsLoaded = false
let sgTabIdActive = null
let sgPrintWindow = null

console.log('SG-LIBRARY Version ' + sgLibraryVersion + ' loaded');

function loadGoogleMaps(callback) {
	//console.log(googleMapKeyApi)
	if(!gMapsLoaded) {
		//console.log("Load google map api with key "+googleMapKeyApi)
		$.getScript("https://maps.googleapis.com/maps/api/js?language=th&key="+googleMapKeyApi+"&callback="+callback, function(data, textStatus, jqxhr){
			//console.log( data ); // Data returned
			//console.log( textStatus ); // Success
			//console.log( jqxhr.status ); // 200
			//console.log( "Load was performed." );
		})
	} else {
		window[callback]()
	}
	gMapsLoaded = true
}


function notify(text,delay) {
	var msg = $('#notify')
	var width = $(document).width()

	if (text == undefined || text == null || typeof text != 'string') {
		text = ''
	}

	if (text == '') {
		msg.hide().fadeOut()
		return
	} else if (text.substring(0,7) == 'LOADING') {
		text = '<div class="loader -rotate"></div><span>กำลังโหลด'+text.substring(7)+'</span>'
	} else if (text.substring(0,10) == 'PROCESSING') {
		text = '<div class="loader -rotate"></div><span>กำลังดำเนินการ'+text.substring(10)+'</span>'
	}

	msg.html(text)
	.hide()
	.on('click', function() {$(this).hide()})
	.css({
		'display': 'inline-block',
		'left' : (width - msg.width()) / 2, // half width - half element width
		'z-index' : 999999 // make sure element is on top
	})
	.fadeIn()

	if (delay) setTimeout(function() { msg.fadeOut(); }, delay)
}

var fetch_unix_timestamp = function() { return parseInt(new Date().getTime().toString().substring(0, 10))}


// Add String prototype
String.prototype.getFuncBody = function() {var str = this.toString(); str = str.replace(/[^{]+{/,""); str = str.substring(0,str.length-1); str = str.replace(/\n/gi,""); if(!str.match(/\(.*\)/gi))str += ")"; return str;}

String.prototype.left = function(n) { return this.substring(0, n); }

String.prototype.empty = function() { var str = this.toString().trim(); return str == "" || str == "0"; }

String.prototype.sgMoney = function(digit) {
	var money = parseFloat(this.replace(/[^0-9.]+|\.(?!\d)/g, ""))
	if (digit) money = parseFloat(parseFloat(money).toFixed(digit))
	return money
}

function thousandsSeparators(num, sep = ',', digit = 2) {
	if (typeof num != "number") return num
	var num_parts = num.toFixed(digit).split(".")
	num_parts[0] = num_parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, sep)
	return num_parts.join(".")
}

Date.prototype.toW3CString=function(){var f=this.getFullYear();var e=this.getMonth();e++;if(e<10){e="0"+e}var g=this.getDate();if(g<10){g="0"+g}var h=this.getHours();if(h<10){h="0"+h}var c=this.getMinutes();if(c<10){c="0"+c}var j=this.getSeconds();if(j<10){j="0"+j}var d=-this.getTimezoneOffset();var b=Math.abs(Math.floor(d/60));var i=Math.abs(d)-b*60;if(b<10){b="0"+b}if(i<10){i="0"+i}var a="+";if(d<0){a="-"}return f+"-"+e+"-"+g+"T"+h+":"+c+":"+j+a+b+":"+i};


function sgPrintPage(str) {
	sgPrintWindow = window.open(str,"mywindow")
	$(sgPrintWindow.document).ready(function() {
		setTimeout("sgPrintWindow.print()", 500)
		setTimeout("sgPrintWindow.close()", 2000)
	})
}

function showPassword(element) {
	let $passwordElement = $(element).closest("span").find("input")
	let inputType = $passwordElement.attr("type")

	if (inputType == "password") {
		$passwordElement.attr("type", "text")
		$(element).text("visibility").addClass("-active")
	} else {
		$passwordElement.attr("type", "password")
		$(element).text("visibility_off").removeClass("-active")
	}
}

// if (window.performance && window.performance.navigation.type == window.performance.navigation.TYPE_BACK_FORWARD) {
// 	alert('hello world');
// }

// if (window.performance && window.performance.navigation.type == window.performance.navigation.TYPE_BACK_FORWARD) {
//   // alert('hello world');
//   console.log(window.performance)
//   // return false
// }

// Case 1
// document.onmouseover = function() {
// 	//User's mouse is inside the page.
// 	window.innerDocClick = true;
// 	console.log('onMouseOver')
// }

// document.onmouseleave = function() {
// 	//User's mouse has left the page.
// 	window.innerDocClick = false;
// 	console.log('onMouseLeave')
// }

// Case 2
// window.onhashchange = function() {
// 	console.log('onHashChange')
// 	if (window.innerDocClick) {
// 		window.innerDocClick = false;
// 	} else {
// 		console.log("HASH = ",window.location.hash)
// 		if (window.location.hash != '#undefined') {
// 			console.log("HASH != undefined => Go Back")
// 			// goBack();
// 		} else {
// 			console.log("HASH = undefined => Reload")
// 			history.pushState("", document.title, window.location.pathname);
// 			// location.reload();
// 		}
// 	}
// }

// Case 3
// window.addEventListener('popstate', function(event) {
// 	var state = event.state
// 	console.log('State = ', state)
// 	// The popstate event is fired each time when the current history entry changes.

// 	if (state !== null) {
// 		console.log('Call AJAX')
// 	}
// 	// var r = confirm("You pressed a Back button! Are you sure?!");

// 	// if (r == true) {
// 	// 	// Call Back button programmatically as per user confirmation.
// 	// 	history.back();
// 	// 	// Uncomment below line to redirect to the previous page instead.
// 	// 	// window.location = document.referrer // Note: IE11 is not supporting this.
// 	// } else {
// 	// 	// Stay on the current page.
// 	// 	history.pushState(null, null, window.location.pathname);
// 	// }

// 	// history.pushState(null, null, window.location.pathname);

// }, false);

// window.onbeforeunload = function(e) {
// 	alert('onbeforeunload')
//   e.preventDefault();
//   // e.returnValue = 'There are unsaved changes. Sure you want to leave?';
// };
// $(window).unload(function() {
// 	alert('unload')
// });

$(document).on('click',function(e) {
	window.onscroll = function(){};
});



// Proceed user signin
$(document).on('submit','form.signform', function(e) {
	let $this = $(this);
	let signInOk = function(result) {
		// If Sign In Complete then redirect to current URL
		notify(result.signInResult)
		if ($this.data('complete')) {
			window.location = $this.data('complete')
		} else if ($this.data('rel')) {
			sgUpdateData(result.signInResult, $this.data('rel'), $this)
			if ($this.data('done')) sgActionDone($this.data('done'), $this)
		} else if((navigator.userAgent.indexOf('Android') != -1)) {
			if (debugSignIn) notify("Sign in from Android "+document.URL)
			window.location = document.URL
		} else {
			if (debugSignIn) notify("Sign in from Web Browser")
			window.location = document.URL
		}
	}

	if ($this.find('.form-text.-username').val() == '') {
		notify('กรุณาป้อน Username');
		$this.find(".form-text.-username").focus();
	} else if ($this.find('.form-password.-password').val() == '') {
		notify('กรุณาป้อน Password');
		$this.find('.form-password.-password').focus();
	} else {
		notify("กำลังเข้าสู่ระบบ");
		if (debugSignIn) notify("Signin request");
		$.post($this.attr("action"), $this.serialize(), function() {}, 'json')
		.fail(function(response) {
			// Sometime sign in ok but error 500:Internal error or empty response
			// On error, responseJSON have value ok
			// console.log(response)
			if ("responseJSON" in response && "ok" in response.responseJSON && response.responseJSON.ok) {
				console.log('SIGN IN RESPONSE FAIL BUT OK!!!!')
				signInOk(response.responseJSON)
			} else {
				// console.log('SIGN IN FAIL!!!')
				let message = 'เกิดข้อผิดพลาดในการเข้าสู่ระบบ';
				if ("responseJSON" in response && "signInResult" in response.responseJSON) {
					message = response.responseJSON.signInResult;
				}
				notify(message)
			}
		})
		.done(function(result) {
			console.log('SIGN IN DONE!!!!')
			// console.log(result)
			if (result.ok) signInOk(result)
		});
	}
	return false;
});

document.addEventListener('click', function(event) {
	if (!event.target.classList.contains('-change-color-scheme')) return;

	if (document.body.classList.contains('theme-dark')) {
		event.target.textContent = 'light_mode';
		document.body.classList.remove("theme-dark")
		setCookie("color-scheme", 'light', 365);
	} else {
		event.target.textContent = 'dark_mode';
		document.body.classList.add("theme-dark")
		setCookie("color-scheme", 'dark', 365);
	}
});

$(document).ready(function() {
	$('body').append('<div id="notify" class="notify-main -no-print"></div><div id="tooltip" class="-noprint"></div>');
	$("#notify").hide();

	$('a[href$=".pdf"], a[href*="files/"]').addClass('pdflink');



	// .sg-load,[data-load
	$('.sg-load,[data-load]').each(function(index) {
		let $this = $(this);
		let loadUrl = $this.data('url');
		let replace = $this.data('replace')
		let para = {}

		para = $this.data();
		delete para.url

		// console.log('Load from url '+$this.data('loadUrl'));

		if (loadUrl == undefined) loadUrl = $this.data('load');

		if (loadUrl) {
			if (loadUrl.left(1)!='/') loadUrl = SG.url(loadUrl);
			$.post(loadUrl, para, function(html) {
				if (replace) {
					$this.replaceWith(html)
				} else {
					$this.html(html);
				}
			});
		}
	});

	// Tab press, add tab to textarea
	// const divs = document.querySelectorAll(".-monospace");

	// divs.forEach(el => el.addEventListener("keydown", event => {
	// 	console.log(event.target.getAttribute("data-el"));
	// 	if (event.key == "Tab") {
	// 		event.preventDefault();
	// 		let target = event.target;
	// 		var start = target.selectionStart;
	// 		var end = target.selectionEnd;

	// 		// set textarea value to: text before caret + tab + text after caret
	// 		target.value = target.value.substring(0, start) + "\t" + target.value.substring(end);

	// 		// put caret at right position again
	// 		target.selectionStart = target.selectionEnd = start + 1;
	// 	}
	// }));
});


// Tab press, add tab to textarea
$(document).on('keydown', '.-monospace', function(event) {
	// console.log($(this));
	if (event.key == "Tab") {
		event.preventDefault();
		let target = event.target;
		var start = target.selectionStart;
		var end = target.selectionEnd;

		// set textarea value to: text before caret + tab + text after caret
		target.value = target.value.substring(0, start) + "\t" + target.value.substring(end);

		// put caret at right position again
		target.selectionStart = target.selectionEnd = start + 1;
	}
})
// const divs = document.querySelectorAll(".-monospace");

// divs.forEach(el => el.addEventListener("keydown", event => {
// 	console.log(event.target.getAttribute("data-el"));
// 	if (event.key == "Tab") {
// 		event.preventDefault();
// 		let target = event.target;
// 		var start = target.selectionStart;
// 		var end = target.selectionEnd;

// 		// set textarea value to: text before caret + tab + text after caret
// 		target.value = target.value.substring(0, start) + "\t" + target.value.substring(end);

// 		// put caret at right position again
// 		target.selectionStart = target.selectionEnd = start + 1;
// 	}

// }));


// Show tooltip on mouse move
$(document).on('mousemove','[data-tooltip]', function(e) {
	var moveLeft = 0
	var moveDown = 0
	var target = '#tooltip'
	var leftD = e.pageX + 20
	var maxRight = leftD + $(target).outerWidth()
	var windowLeft = $(window).width() - 40
	var windowRight = 0
	var maxLeft = e.pageX - (parseInt(moveLeft) + $(target).outerWidth() + 20)
	var text=$(this).attr("data-tooltip")
	$(target).html(text).show()

	if(maxRight > windowLeft && maxLeft > windowRight) {
		leftD = maxLeft
	}
	var topD = e.pageY +10
	var maxBottom = parseInt(e.pageY + parseInt(moveDown) + 20)
	var windowBottom = parseInt(parseInt($(document).scrollTop()) + parseInt($(window).height()))
	var maxTop = topD
	var windowTop = parseInt($(document).scrollTop())
	if(maxBottom > windowBottom) {
		topD = windowBottom - $(target).outerHeight() - 20
	} else if(maxTop < windowTop){
		topD = windowTop + 20
	}
	$(target).css('top', topD).css('left', leftD)
}).on('mouseleave','[data-tooltip]', function(e) {
	var target = '#tooltip'
	$(target).hide()
})



/*
* Open confirm box when link has data-confirm using $.comfirm library
* Event Listener : onClick
* DOWNLOAD : http://craftpip.github.io/jquery-confirm/
*/
$(document).on('click','a[data-confirm]',function(ele) {
	//console.log("Data Confirm Click by CONFIRM")
	var $this = $(this)
	//console.log($this.attr('href'))
	if ($this.data('confirmed')) return true
	$this.data('confirmed',false)
	ele.stopPropagation()

	$.confirm({
		title: $this.data('title') ? $this.data('title') : $this.data('confirm'),
		content: $this.data('confirm'),
		draggable: true,
		escapeKey: true,
		backgroundDismiss: false,
		escapeKey: 'Cancel',
		scrollToPreviousElement: false,
		boxWidth: '300px',
		useBootstrap: false,
		theme: 'material',
		buttons: {
			Cancel: {
				btnClass: 'btn -link -cancel',
				action: function() {
					//$( this ).dialog( "close" );
				}
			},
			Ok : {
				text: ' <i class="icon -material">done_all</i> OK ',
				btnClass: 'btn -primary',
				keys: ['enter', 'o'],
				action: function() {
					//$( this ).dialog( "close" );
					//console.log("OK")
					$this.data('confirmed',true)
					//console.log($this.data('confirmed'))
					//console.log('href='+$this.attr('href'))
					$this.trigger('click')
					$this.removeData('confirmed')
				}
			},
		},
	});
	ele.stopPropagation()
	return false;
});







/*
* sg-datepicker :: Softganz datepicker
* written by Panumas Nontapan
* https://softganz.com
* Using <input class="sg-datepicker" type="text" data-callback="" />
* 	data-aldfld = "id"
* DOWNLOAD : https://flatpickr.js.org/
*
* ********* NOT USED ********
*/
$(document).on('focus', '.flatpickr-sg-datepicker', function(e) {
	var defaults={
		clickInput: true,
		dateFormat: "d/m/Y",
		altFormat: "yy-mm-dd",
		altField: "",
		disabled: false,
		locale: 'th',
		monthNames: thaiMonthName,
		beforeShow: function( el ){
			// set the current value before showing the widget
			//$(this).data('previous', $(el).val() );
			$(".ui-datepicker:visible").css({top:"+=5"});
		},
		onSelect: function(dateText,inst) {
			console.log('DATEPICKET SELECT')
			if( $(this).data('previous') != dateText ) {
				if ($(this).data('diff')) {
					// Calculate for date diff into other field
					var $toDate = $('#'+$(this).data('diff'));
					//console.log('Calculate date diff to '+$toDate.attr('id'));

					var $fromDate = $(this);
					//var $toDate=$(this).closest('form').find('.sg-checkdateto');
					if ($toDate.val() == '') {
						$toDate.val($fromDate.val());
					} else {
						var diff_date = 0;
						var days = 24*60*60*1000;
						var prevDateText = $(this).data('previous') ? $(this).data('previous') : dateText;
						var prevDateArray = prevDateText.split("/");
						var fromDateArray = $(this).val().split("/");
						var toDateArray = $toDate.val().split("/");

						var prevDate = new Date(prevDateArray[2],prevDateArray[1] - 1,prevDateArray[0]);
						var toDate = new Date(toDateArray[2],toDateArray[1] - 1,toDateArray[0]);

						var fromDate = new Date(fromDateArray[2],fromDateArray[1] - 1,fromDateArray[0]);

						diff_date = Math.round((toDate - prevDate) / days);

						var newToDate = new Date(fromDate);

						newToDate.setDate(fromDate.getDate() + diff_date);

						var dd = newToDate.getDate();
						var mm = newToDate.getMonth() + 1;
						var yy = newToDate.getFullYear();

						var pad = '00'
						// Fill 0 before date with length 2 => (pad + dd).slice(-pad.length)
						var newToDateFormatted = (pad + dd).slice(-pad.length) + '/' + (pad + mm).slice(-pad.length) + '/' + yy;
						//console.log(newToDateFormatted)
						$toDate.val(newToDateFormatted);
					}
				}
				$(this).trigger('dateupdated');
			}
			// Process call back
			var callback=$(this).data('callback');
			if (callback) {
				if (callback=='submit') {
					$(this).closest('form').submit()
				} else if (typeof window[callback]==='function') {
					 window[callback](dateText,$(this));
				} else {
					var url=callback+'/'
					window.location=url;
				}
			}
		},
	}
	var options = $.extend(defaults, $(this).data());
	//if (options.onSelect)
	//console.log('OPTIONS ',options)

	$(this).flatpickr(options)
});









//**** Scroll to DOM id or link name ****
$(document).on('click', 'a[href^=\\#]:not(.sg-action)', function(e) {
	e.preventDefault();
	var dest = $(this).attr('href')
	var isTabClick = $(this).parent().parent().hasClass('tabs')
	//console.log("Goto " + dest + " isTab " + isTabClick + ' '+$(this).parent().parent().attr('class'))

	if (dest=="#" || isTabClick) return;

	var id = dest.substring(1)
	if ($(dest).length) {
		// Scroll to element id
		$('html,body').animate({ scrollTop: $(dest).offset().top - 128 }, 'slow');
		return true;
	} else if ($('a[name="'+id+'"]').length) {
		// Scroll to link name
		$('html,body').animate({ scrollTop: $('a[name="'+id+'"]').offset().top - 128 }, 'slow');
		return true;
	}
});








//**** Editor functions ****/
var editor = {
	version : '0.0.3b',
	controls : new Array() ,
	start_tag : '',
	end_tag : '',

	click : function (domEvent) {
		let evt
		domEvent ? evt = domEvent : evt = event;
		let cSrc = evt.target ? evt.target : evt.srcElement;
		let elem = document.getElementsByTagName('textarea');
		let ctrl = cSrc.parentNode;
		let myField
		let dom
		//alert(ctrl);
		//alert('click '+ctrl+' : '+ctrl.id+' : '+ctrl.className);
		//myField=document.getElementById(ctrl.title);
		//alert('set myField ',editor.myField.id);
		// console.log('CLICK')
		// console.log(ctrl.id)
		// if (ctrl && ctrl.id == 'edit-detail-body-control') {
		if (ctrl && ctrl.className == 'editor') {
			myField = document.getElementById(ctrl.title);
			//alert('set myField ',editor.myField.id);
			//			debug('<p>control parent id '+ctrl.id+' : '+ctrl.title+' : '+ctrl.className+' : '+ctrl.parentNode.id+'</p>',false);
			//			debug('insert into id '+myField.id+' tag = '+editor.start_tag+' | '+editor.end_tag);
			// console.log(domEvent)
			// console.log(event)
			if (editor.start_tag) editor.insertCode(myField,editor.start_tag,editor.end_tag);
		}
		editor.start_tag='';
		editor.end_tag='';
	} ,

	insert : function (i,o) {
		// console.log("INSERT")
		if(i == undefined) { i=''; }
		if(o == undefined) { o=''; }
		this.start_tag=i;
		this.end_tag=o;
	} ,

	insertCode : function(myField,i,o) {
		// console.log('INSERT CODE')
		// console.log(myField)
		// IE selection support
		if (document.selection) {
			myField.focus();
			sel = document.selection.createRange();
			if (sel.text.length > 0) {
				sel.text = i + sel.text + o;
			} else {
				sel.text = i + o;
			}
			myField.focus();
		// MOZILLA selection support
		} else if (myField.selectionStart || myField.selectionStart == '0') {
			var startPos = myField.selectionStart;
			var endPos = myField.selectionEnd;
			var cursorPos = endPos;
			var scrollTop = myField.scrollTop;
			if (startPos != endPos) {
				myField.value = myField.value.substring(0, startPos)
				+ i
				+ myField.value.substring(startPos, endPos)
				+ o
				+ myField.value.substring(endPos, myField.value.length);
				cursorPos = cursorPos + i.length + o.length;
			} else {
				myField.value = myField.value.substring(0, startPos)
				+ i
				+ o
				+ myField.value.substring(endPos, myField.value.length);
				cursorPos = startPos + i.length;
			}
			myField.focus();
			myField.selectionStart = cursorPos;
			myField.selectionEnd = cursorPos;
			myField.scrollTop = scrollTop;
		// SAFARI and others
		} else {
			myField.value += i+o;
			myField.focus();
		}
	} ,

	url : function (i) {
			var defaultValue = 'http://';
		var url = prompt('enter your url' ,defaultValue);
		if (url == undefined) return;

		// insert BBcode Link
		//		this.insert('[url='+ url + ']','[/url]');

		// insert Markdown Link
		this.insert('[',']('+url+')');
	} ,

	image : function (src) {
			var defaultValue = 'http://';
		if (src == undefined) {
			src = prompt('enter your image location', defaultValue);
			if (src == undefined || src==defaultValue) return;
		}
		// insert BBcode Image
		//		this.insert('[img]'+src,'[/img]');

		// insert Markdown Image
		this.insert('![คำอธิบายภาพ',']('+src+' "ชื่อภาพ")');
	} ,

	emotion : function (id) {
		//dom.toggle(id);
	} ,

	color : function (id) {
		this.COLORS = [
			["ffffff", "cccccc", "c0c0c0", "999999", "666666", "333333", "000000"], // blacks
			["ffcccc", "ff6666", "ff0000", "cc0000", "990000", "660000", "330000"], // reds
			["ffcc99", "ff9966", "ff9900", "ff6600", "cc6600", "993300", "663300"], // oranges
			["ffff99", "ffff66", "ffcc66", "ffcc33", "cc9933", "996633", "663333"], // yellows
			["ffffcc", "ffff33", "ffff00", "ffcc00", "999900", "666600", "333300"], // olives
			["99ff99", "66ff99", "33ff33", "33cc00", "009900", "006600", "003300"], // greens
			["99ffff", "33ffff", "66cccc", "00cccc", "339999", "336666", "003333"], // turquoises
			["ccffff", "66ffff", "33ccff", "3366ff", "3333ff", "000099", "000066"], // blues
			["ccccff", "9999ff", "6666cc", "6633ff", "6600cc", "333399", "330099"], // purples
			["ffccff", "ff99ff", "cc66cc", "cc33cc", "993399", "663366", "330033"] // violets
			];

		var cell_width = 14;
		var html = "";
		for (var i = 0; i < this.COLORS.length; i++) {
			for (var j = 0; j < this.COLORS[i].length; j++) {
			html=html+'<img src="/css/img/none.gif" width="'+cell_width+'" height="'+cell_width+'" onClick="editor.insert(\'[color=#' + this.COLORS[i][j] + ']\',\'[/color]\')" style="background-color:#'+this.COLORS[i][j]+';margin:0 1px 1px 0;">';
			}
		}
		var elem=document.getElementById(id);
		if (elem==undefined) return;
		elem.innerHTML = html;
		//dom.toggle(id);
	}
}

/* init bb click */
if (typeof document.attachEvent != 'undefined') {
	document.attachEvent('onclick', editor.click);
} else {
	document.addEventListener('click', editor.click, false);
}





/*
* 	Easy Slider 1.5 - jQuery plugin
*	written by Alen Grakalic
*	http://cssglobe.com/post/4004/easy-slider-15-the-easiest-jquery-plugin-for-sliding
*
*	Copyright (c) 2009 Alen Grakalic (http://cssglobe.com)
*	Dual licensed under the MIT (MIT-LICENSE.txt)
*	and GPL (GPL-LICENSE.txt) licenses.
*
*	Built for jQuery library
*	http://jquery.com
*
*	markup example for $("#slider").easySlider();
*
* 	<div id="slider" class="sg-slider">
*		<ul>
*			<li><img src="images/01.jpg" alt="" /></li>
*			<li><img src="images/02.jpg" alt="" /></li>
*			<li><img src="images/03.jpg" alt="" /></li>
*			<li><img src="images/04.jpg" alt="" /></li>
*			<li><img src="images/05.jpg" alt="" /></li>
*		</ul>
*	</div>
*
*/
(function($) {	// Easy Slider
	$.fn.easySlider = function(options){
		// default configuration properties
		var defaults = {
			prevId: 		'prevBtn',
			prevText: 		'Previous',
			nextId: 		'nextBtn',
			nextText: 		'Next',
			controlsShow:	true,
			controlsBefore:	'',
			controlsAfter:	'',
			controlsFade:	true,
			firstId: 		'firstBtn',
			firstText: 		'First',
			firstShow:		false,
			lastId: 		'lastBtn',
			lastText: 		'Last',
			lastShow:		false,
			vertical:		false,
			speed: 			800,
			auto:			false,
			pause:			2000,
			continuous:		false,
			numeric: 		false,
			numericId: 		'controls',
			hoverpause: false,
			beginSlide: {},
			debug: false,
		};
		var options = $.extend(defaults, options);
		var animateCount=0;

		this.each(function() {
			var obj = $(this);
			var $slideMain;
			if (obj.data('slider')==undefined) {
				$slideMain = obj.children().first();
			} else {
				$slideMain = obj.find('.'+obj.data('slider')).children().first();
			}
			var $slideTag = $slideMain.children().first();
			options = $.extend(options, obj.data());
			options.slideTag=$slideTag.prop('tagName');
			var s = $(options.slideTag, $slideMain).length;
			var w = $(obj).width();
			var h = $(obj).height();

			if (options.debug) notify('Slide Main='+$slideMain.prop('tagName')+' Slide Tag='+options.slideTag+' on '+s+' slide Option='+options);

			$(options.slideTag, $slideMain).width(w).css({'overflow':'hidden', 'position':'relative'});
			$(options.slideTag, $slideMain).height(h);
			obj.css("overflow","hidden");
			var ts = s-1;
			var t = 0;
			$slideMain.css({'width': s*w, 'margin':0, 'padding':0, 'list-style':'none'});
			if(!options.vertical) $(options.slideTag, $slideMain).css('float','left');

			if(options.controlsShow){
				var html = options.controlsBefore;
				if(options.firstShow) html += '<span id="'+ options.firstId +'"><a href=\"javascript:void(0);\">'+ options.firstText +'</a></span>';
				html += ' <span id="'+ options.prevId +'"><a href=\"javascript:void(0);\">'+ options.prevText +'</a></span>';
				html += ' <span id="'+ options.nextId +'"><a href=\"javascript:void(0);\">'+ options.nextText +'</a></span>';
				if(options.lastShow) html += ' <span id="'+ options.lastId +'"><a href=\"javascript:void(0);\">'+ options.lastText +'</a></span>';
				html += options.controlsAfter;
				$(obj).after(html);
			};
			$("a","#"+options.nextId).click(function(){ animate("next",true); });
			$("a","#"+options.prevId).click(function(){ animate("prev",true); });
			$("a","#"+options.firstId).click(function(){ animate("first",true); });
			$("a","#"+options.lastId).click(function(){ animate("last",true); });

			function animate(dir,clicked){
				if (options.debug) notify('Slide Main='+$slideMain.prop('tagName')+' Slide Tag='+options.slideTag+' on '+s+' slide '+(++animateCount)+' Option='+options.controlsShow);
				//options.onBeginSlide.call($slideMain);
				var ot = t

				switch(dir){
					case "next":	t = (ot>=ts) ? (options.continuous ? 0 : ts) : t+1; break;
					case "prev":	t = (t<=0) ? (options.continuous ? ts : 0) : t-1; break;
					case "first":	t = 0; break;
					case "last":	t = ts; break;
					default:			break;
				};

				if($.isFunction(options.onBeginSlide)) {
					// call user provided method
					options.onBeginSlide.call(this,t);
				}

				var diff = Math.abs(ot-t)
				var speed = diff*options.speed
				var p

				if(!options.vertical) {
					p = (t*w*-1);
					$slideMain.animate( { marginLeft: p }, speed );
				} else {
					p = (t*h*-1);
					$slideMain.animate( { marginTop: p }, speed );
				};
				if(!options.continuous && options.controlsFade){
					if(t==ts){
						$("a","#"+options.nextId).hide();
						$("a","#"+options.lastId).hide();
					} else {
						$("a","#"+options.nextId).show();
						$("a","#"+options.lastId).show();
					};
					if(t==0){
						$("a","#"+options.prevId).hide();
						$("a","#"+options.firstId).hide();
					} else {
						$("a","#"+options.prevId).show();
						$("a","#"+options.firstId).show();
					};
				};
				if(clicked) clearTimeout(timeout);
				if(options.auto && dir=="next" && !clicked){;
					timeout = setTimeout(function(){
						animate("next",false);
					},diff*options.speed+options.pause);
				};

			};
			// init
			var timeout;
			if(options.auto){;
				timeout = setTimeout(function(){
					animate("next",false);
				},options.pause);
			};
			if(options.hoverpause && options.auto){
            $(this).mouseover(function(){
                clearTimeout(timeout);
            }).mouseout(function(){
                animate("next",false);
            })
			}
			if(!options.continuous && options.controlsFade){
				$("a","#"+options.prevId).hide();
				$("a","#"+options.firstId).hide();
			};
		});
	};

	$(document).ready(function() {
		$(".sg-slider").easySlider({
			auto: true,
			continuous: true,
			pause: 5000,
			speed: 500,
			controlsShow: false,
			debug: false,
		});
	});

})(jQuery);







/*
* A quick plugin which implements phpjs.org's number_format as a jQuery
* plugin which formats the number, and inserts to the DOM.
*
* By Sam Sehnert, teamdf.com — http://www.teamdf.com/web/jquery-number-format/178/
*/
(function($) {	// softganz extend function
	$.fn.number = function( number, decimals, dec_point, thousands_sep ){
		number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
		var n = !isFinite(+number) ? 0 : +number,
			prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
			sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
			dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
			s = '',
			toFixedFix = function (n, prec) {
					var k = Math.pow(10, prec);
					return '' + Math.round(n * k) / k;
			};
		// Fix for IE parseFloat(0.55).toFixed(0) = 0;
		s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
		if (s[0].length > 3) {
			s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
		}
		if ((s[1] || '').length > prec) {
			s[1] = s[1] || '';
			s[1] += new Array(prec - s[1].length + 1).join('0');
		}
		// Add this number to the element as text.
		this.text( s.join(dec) );
	};

	$.fn.isOnScreen = function(){
		var element = this.get(0)
		if (!element) return true
		var bounds = element.getBoundingClientRect()
		//console.log("bounds.top "+bounds.top, "innerHeight "+window.innerHeight)
		return bounds.top < window.innerHeight && bounds.bottom > 0
	}
})(jQuery);







/*
* Responsive Menu v0.0.0 by @softganz
* Copyright 2013 Softganz Group.
* Licensed under http://www.apache.org/licenses/LICENSE-2.0
*
* Designed and built with all the love in the world by @softganz.
*/
(function($) {	// ResponsiveMenu

	// ResponsiveMenu PUBLIC CLASS DEFINITION
	// ==============================

	var ResponsiveMenu = function (element, options) {
		this.options		=
		this.enabled		=
		this.$element   = null

		this.init(element, options)
	}

	ResponsiveMenu.DEFAULTS = {
		test: 'loading...',
	}

	ResponsiveMenu.prototype.init = function (element, options) {
		this.enabled  = true
		this.$element = $(element)
		this.options  = this.getOptions(options)
		this.$element.prepend('<button type="button" class="sg-navtoggle btn" aria-hidden="true"><i aria-hidden="true" class="icon -material">menu</button>')
		var $parent=this.$element
		$(this.$element).on('click','.sg-navtoggle', function() {
			$parent.toggleClass('-active')
		})
	}

	ResponsiveMenu.prototype.getDefaults = function () {
		return ResponsiveMenu.DEFAULTS
	}

	ResponsiveMenu.prototype.getOptions = function (options) {
		options = $.extend({}, this.getDefaults(), this.$element.data(), options)
		return options
	}

	// ResponsiveMenu PLUGIN DEFINITION
	// ========================

	var old = $.fn.button

	$.fn.responsivemenu = function (option) {
		return this.each(function () {
			var $this			= $(this)
			var data			= $this.data('sg.responsivemenu')
			var options	= typeof option == 'object' && option

			if (!data) $this.data('sg.responsivemenu', (data = new ResponsiveMenu(this, options)))
			if (typeof option == 'string') data[option]()
		})
	}

	$.fn.responsivemenu.Constructor = ResponsiveMenu

	// ResponsiveMenu NO CONFLICT
	// ==================

	$.fn.responsivemenu.noConflict = function () {
		$.fn.responsivemenu = old
		return this
	}

	$(document).ready(function() {
		$('.sg-responsivemenu').responsivemenu()
	})
})(jQuery);


function setCookie(name,value,days) {
    var expires = "";
    if (days) {
        var date = new Date();
        date.setTime(date.getTime() + (days*24*60*60*1000));
        expires = "; expires=" + date.toUTCString();
    }
    document.cookie = name + "=" + (value || "")  + expires + "; path=/";
}
function getCookie(name) {
    var nameEQ = name + "=";
    var ca = document.cookie.split(';');
    for(var i=0;i < ca.length;i++) {
        var c = ca[i];
        while (c.charAt(0)==' ') c = c.substring(1,c.length);
        if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
    }
    return null;
}
function eraseCookie(name) {
	//document.cookie = name + '=; Max-Age=-99999999;'
	document.cookie = name + "=; expires = Thu, 01 Jan 1970 00:00:00 GMT; path=/"
}