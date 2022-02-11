/**
 * SoftGanz JavaScript Library
 *
 * @package library
 * @version 3.10
 * @copyright Copyright (c) 2000-present , The SoftGanz Group By Panumas Nontapan
 * @author Panumas Nontapan <webmaster@softganz.com>
 * http://www.softganz.com
 * @created 2009-09-22
 * @modify  2019-02-22
 * ============================================
 * This program is free software. You can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License.
 * ============================================
*/

/*
 * Using :
 * - jquery.3.3.js : https://jquery.com
 * - jquery.form.js : http://jquery.malsup.com/form/
 * - jquery.editable.js : https://github.com/*NicolasCARPi/jquery_jeditable
 * - gmaps.js : https://hpneo.github.io/gmaps/
 */

'use strict'

console.log('SG-library Version 3.00.1 loaded')

var thaiMonthName=["มกราคม", "กุมภาพันธ์", "มีนาคม", "เมษายน", "พฤษภาคม", "มิถุนายน", "กรกฎาคม", "สิงหาคม", "กันยายน", "ตุลาคม", "พฤศจิกายน", "ธันวาคม"];
var defaultRelTarget="#main";
var debugSignIn=false;
var firebaseConfig;

var gMapsLoaded = false;

var sgTabIdActive = null

function loadGoogleMaps(callback) {
	//console.log(googleMapKeyApi)
	if(!gMapsLoaded) {
		//console.log("Load google map api with key "+googleMapKeyApi)
		$.getScript("https://maps.googleapis.com/maps/api/js?language=th&key="+googleMapKeyApi+"&callback="+callback, function(){})
	} else {
		window[callback]()
	}
	gMapsLoaded = true;
}


function notify(text,delay) {
	var msg = $('#notify');
	var width = $(document).width();

	if (text == undefined || text == null)
		text = '';

	if (text == '') {
		msg.hide().fadeOut();
		return;
	} else if (text.substring(0,7) == 'LOADING') {
		text = '<div class="loader -rotate"></div><span>กำลังโหลด'+text.substring(7)+'</span>';
	} else if (text.substring(0,10) == 'PROCESSING') {
		text = '<div class="loader -rotate"></div><span>กำลังดำเนินการ'+text.substring(10)+'</span>';
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

	if (delay) setTimeout(function() { msg.fadeOut(); }, delay);
}

var fetch_unix_timestamp = function() { return parseInt(new Date().getTime().toString().substring(0, 10))}

/**
 * Editor functions
 */
var editor = {
	version : '0.0.3b',
	controls : new Array() ,
	start_tag : '',
	end_tag : '',

	click : function (e) {
		var evt
		e ? evt = e : evt = event;
		var cSrc=evt.target?evt.target:evt.srcElement;
		var elem=document.getElementsByTagName('textarea');
		var ctrl=cSrc.parentNode;
		var myField
		var dom
		//		alert(ctrl);
		//alert('click '+ctrl+' : '+ctrl.id+' : '+ctrl.className);
		if (ctrl && ctrl.className=='editor') {
			myField=document.getElementById(ctrl.title);
			//			alert('set myField ',editor.myField.id);
			//			debug('<p>control parent id '+ctrl.id+' : '+ctrl.title+' : '+ctrl.className+' : '+ctrl.parentNode.id+'</p>',false);
			//			debug('insert into id '+myField.id+' tag = '+editor.start_tag+' | '+editor.end_tag);
			if (editor.start_tag) editor.insertCode(myField,editor.start_tag,editor.end_tag);
		}
		editor.start_tag='';
		editor.end_tag='';
	} ,

	insert : function (i,o) {
		if(i == undefined) { i=''; }
		if(o == undefined) { o=''; }
		this.start_tag=i;
		this.end_tag=o;
	} ,

	insertCode : function(myField,i,o) {
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
			html=html+'<img src="/library/img/none.gif" width="'+cell_width+'" height="'+cell_width+'" onClick="editor.insert(\'[color=#' + this.COLORS[i][j] + ']\',\'[/color]\')" style="background-color:#'+this.COLORS[i][j]+';margin:0 1px 1px 0;">';
			}
		}
		var elem=document.getElementById(id);
		if (elem==undefined) return;
		elem.innerHTML = html;
		//dom.toggle(id);
	}
}


// Add String prototype
String.prototype.getFuncBody = function() {var str=this.toString(); str=str.replace(/[^{]+{/,""); str=str.substring(0,str.length-1); str = str.replace(/\n/gi,""); if(!str.match(/\(.*\)/gi))str += ")"; return str;}

String.prototype.left = function(n) { return this.substring(0, n); }

String.prototype.empty = function() { var str=this.toString().trim(); return str=="" || str=="0"; }

Date.prototype.toW3CString=function(){var f=this.getFullYear();var e=this.getMonth();e++;if(e<10){e="0"+e}var g=this.getDate();if(g<10){g="0"+g}var h=this.getHours();if(h<10){h="0"+h}var c=this.getMinutes();if(c<10){c="0"+c}var j=this.getSeconds();if(j<10){j="0"+j}var d=-this.getTimezoneOffset();var b=Math.abs(Math.floor(d/60));var i=Math.abs(d)-b*60;if(b<10){b="0"+b}if(i<10){i="0"+i}var a="+";if(d<0){a="-"}return f+"-"+e+"-"+g+"T"+h+":"+c+":"+j+a+b+":"+i};

/* init bb click */
if (typeof document.attachEvent!='undefined') {
	//	window.attachEvent("onload", ajax.linkRelationInit);
	document.attachEvent('onclick',editor.click);
} else {
	//	window.addEventListener('load',ajax.linkRelationInit,false);
	document.addEventListener('click',editor.click,false);
}


$(document).on('click',function(e) {
	window.onscroll = function(){};
});

$(document).on('submit','form.signform, .member-zone', function(e) {
	var $this=$(this);

	//if (typeof debugSignIn === 'undefined') var debugSignIn=false; 
	//var debugSignIn=false;

	//	alert($this.attr('id')+' u='+$this.find("#edit-username").val()+' p='+$this.find("#edit-password").val())
	if ($this.find('.form-text.-username').val() == '') {
		notify('กรุณาป้อน Username');
		$this.find(".form-text.-username").focus();
	} else if ($this.find('.form-password.-password').val() == '') {
		notify('กรุณาป้อน Password');
		$this.find('.form-password.-password').focus();
	} else {
		notify("กำลังเข้าสู่ระบบ");
		if (debugSignIn) notify("Signin request");
		$.post($this.attr("action"),$this.serialize(),function(html) {
			if (debugSignIn) notify("Start request complete");
			var error=html
			//alert("Sign in process");
			//window.location=document.URL;
			/*
			if(navigator.userAgent.match(/Android/i)) {
				alert("Sign 1");
				//document.location=document.URL;
				//window.open(document.URL);
			} else {
				alert("Sign 2");
				window.location.replace(document.URL);
			}
			*/

			if (html.search('signin-error')==-1) {
				// If Sign In Complete then redirect to current URL
				//alert("Sign in complete");
				if (debugSignIn) notify("Signin complete");
				//window.location=document.URL;
				if((navigator.userAgent.indexOf('Android') != -1)) {
					if (debugSignIn) notify("Sign in from Android "+document.URL);
					// Old way
					//$("#primary").html(html);

					// New way is reload page
					window.location=document.URL;
					//document.location=document.URL;
					//window.open(document.URL);
					//document.location.reload();
					//window.location.href=document.URL;
					//if (debugSignIn) notify("Sign in from Android Complete."+document.URL);
				} else {
					if (debugSignIn) notify("Sign in from Web Browser");
					window.location=document.URL;
				}

			} else {
				// If Sign In Error then show error message
				if (debugSignIn) notify("Signin error");
				var matches = [];
				html.replace(/<div class="signin-error hidden">(.*?)<\/div>/g, function () {
					matches.push(arguments[1]);
				});
				if (debugSignIn) notify(matches);
				notify(matches);
			}
		});
	}
	return false;
});


/*
$(document).tooltip({
	items: ".sg-tooltip",
	tooltipClass:'preview-tip',
	content: function(callback) {
		var $this = $(this)
		console.log("sg-tooltip")
		$.get($this.data('url'),function(html) {callback(html)})
		return 'กำลังโหลด'
	}
});
*/


$(document).ready(function() {
	$('body').prepend('<div id="notify" class="-no-print"></div><div id="tooltip" class="-noprint"></div>');
	$("#notify").hide();

	$('a[href$=".pdf"], a[href*="files/"]').addClass('pdflink');



	$('.sg-load,[data-load]').each(function(index) {
		var $this = $(this);
		var uri = $this.data('url');
		var para = {}

		para = $this.data();
		delete para.url

		console.log("Load data from url "+$this.attr("class")+" | "+$this.data("load")+" | "+uri)+para;

		if (uri == undefined) uri = $this.data('load');

		if (uri) {
			if (uri.left(1)!='/') uri = url + uri;
			$.post(uri, para, function(html) {
				//console.log(html)
				$this.html(html);
			});
		}
	});
});




$(document).on('mousemove','[data-tooltip]', function(e) {
	var moveLeft = 0;
	var moveDown = 0;
	var target = '#tooltip';
	var leftD = e.pageX+20// + parseInt(moveLeft);
	var maxRight = leftD + $(target).outerWidth();
	var windowLeft = $(window).width() - 40;
	var windowRight = 0;
	var maxLeft = e.pageX - (parseInt(moveLeft) + $(target).outerWidth() + 20);
	var text=$(this).attr("data-tooltip")
	$(target).html(text).show();

	if(maxRight > windowLeft && maxLeft > windowRight) {
		leftD = maxLeft;
	}
	var topD = e.pageY +10;//parseInt(moveDown);
	var maxBottom = parseInt(e.pageY + parseInt(moveDown) + 20);
	var windowBottom = parseInt(parseInt($(document).scrollTop()) + parseInt($(window).height()));
	var maxTop = topD;
	var windowTop = parseInt($(document).scrollTop());
	if(maxBottom > windowBottom) {
		topD = windowBottom - $(target).outerHeight() - 20;
	} else if(maxTop < windowTop){
		topD = windowTop + 20;
	}
	$(target).css('top', topD).css('left', leftD);
}).on('mouseleave','[data-tooltip]', function(e) {
	var target = '#tooltip';
	$(target).hide();
});


/*
 * sgUpdateData :: SoftGanz Update data to DOM
 * @param String html
 * @param String relTarget
 * @param jQuery Object $this
 * @param Object options
 */
function sgUpdateData(html, relTarget, $this, options = {}) {
	if (relTarget == undefined) return

	var relExplode = relTarget.split(':')
	var relType = relExplode[0]
	var $ele

	if (relExplode.length > 1 )
		relTarget = relExplode[1]

	if (relTarget.charAt(0).match(/[a-z]/i))
		relTarget = '#'+relTarget

	//console.log('Type = ' + relType + ' Target = ' + relTarget)
	//console.log('$this',$this)

	if (relType == "none") {
		; // Do Nothing
	} else if (relType == 'notify') {
		notify(relTarget != '#notify' ? relTarget : html, 20000)
	} else if (relType == 'box') {
		sgShowBox(html, $this)
	} else if (relType == 'reload') {
		window.location=document.URL;
	} else if (relType == 'this') {
		$this.html(html);
	} else if (relType == 'parent') {
		$ele = relTarget == '#parent' ? $this.parent() : $this.closest(relTarget);
		$ele.html(html);
	} else if (relType == 'replace') {
		$ele = relTarget == '#replace' ? $this : ($this.closest(relTarget).length ? $this.closest(relTarget): $(relTarget));
		$ele.replaceWith(html);
	} else if (relType == 'after') {
		$ele = relTarget == '#after' ? $this : $(relTarget);
		$ele.after(html);
	} else if (relType == 'append') {
		$ele = relTarget == '#append' ? $this : $(relTarget);
		$ele.append(html)
	} else if (relType == 'refresh') {
		$ele = relTarget=='#refresh' ? $('#main') : $(relTarget);
		var refreshUrl = $ele.data('url') ? $ele.data('url') : document.URL
		//console.log('Taget = '+relTarget+' Refresh Url : '+refreshUrl)
		if (refreshUrl) {
			$.post(refreshUrl,function(html){
				$ele.html(html);
			});
		}
	} else {
		$(relTarget).html(html);
	}
}


/*
 * sgShowBox :: SoftGanz Show Box
 * @param String html
 * @param jQuery Object $this
 * @param Object options
 */
function sgShowBox(html, $this, options) {
	var defaults = {
		fixed: true,
		opacity: 0.5,
		width: "95%",
		maxHeight: "95%",
		maxWidth: "95%",
	}

	var $boxElement = $('#cboxLoadedContent');
	if ($boxElement.length) {
		//console.log('Show in current box')
		$boxElement.html(html)
	}	else {
		// lock scroll position, but retain settings for later
		var x = window.scrollX;
		var y = window.scrollY;
		window.onscroll = function(){window.scrollTo(x, y);};

		//console.log('Open new box')
		//console.log($this instanceof jQuery)
		options = $.extend(defaults, options)
		if ($this instanceof jQuery) options = $.extend(options, $this.data());
		options.html = html

		$.colorbox(options);
	}
}


/*
* Open confirm box when link has data-confirm
* Event Listener : onClick
* DOWNLOAD : http://craftpip.github.io/jquery-confirm/
*/
$(document).on('click','a[data-confirm]',function(ele) {
	console.log("Data Confirm Click by CONFIRM")
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
				text: ' <i class="icon -save -white"></i> OK ',
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
})



/*
 * sg-box :: Load html from url and display in box
 * written by Panumas Nontapan
 * http://softganz.com
 * Using <a class="sg-box" href="">Text</a>
 */
$(document).on('click','.sg-box', function(e) {
	var defaults={
		fixed: true,
		opacity: 0.5,
		width: "95%",
		maxHeight: "95%",
		maxWidth: "95%",
		onComplete: function() {
			console.log('SG-BOX Complete')
		}
	}

	var $this = $(this)

	if ($this.data('webview')) return true;

	if ($this.attr('href') == undefined) return true;

	// lock scroll position, but retain settings for later
	var x=window.scrollX;
	var y=window.scrollY;
	window.onscroll=function(){window.scrollTo(x, y);};

	var group=$this.data("group");
	var options = $.extend(defaults, $this.data());
	if (options.group) options.rel=options.group

	/*
	if ($this.data('confirm')!=undefined && !confirm($this.data('confirm'))) {
		return false
	}
	*/


	console.log('***** SG-BOX Start '+$this.data('confirmed'))

	var confirm = $this.data('confirm') == undefined || $this.data('confirmed')

	if (!confirm) {
		e.stopPropagation()
		return false
	}


	if ($this.attr('href')=='#close') {
		$.colorbox.close()
		return false
	}

	$('.sg-box[data-group="'+group+'"]').each(function(i){
		var $elem=$(this);
		$elem.colorbox(options);
	});
	options.open=true
	$this.colorbox(options);

	// Process callback function
	var callbackFunction=$this.data("callback");
	if (callbackFunction && typeof window[callbackFunction] === 'function') {
		window[callbackFunction]($this,'');
	}

	console.log("BOX Complete")
	e.stopPropagation()
	return false
});


/*
 * sg-action :: Softganz link action
 * written by Panumas Nontapan
 * http://softganz.com
 * Using <a class="sg-action" data-rel="" data-confirm="" data-removeparent="tag" data-do="" data-callback="">...</a>
 */
$(document).on('click','.sg-action', function(e) {
	var $this = $(this)
	var url = $this.attr('href')
	var relTarget = $this.data('rel')
	var retUrl = $this.data('ret')
	var para = {}

	if (url == 'javascript:void(0)')
		url = $this.data('url');

	console.log('***** sg-action start ')

	var confirm = $this.data('confirm') == undefined || $this.data('confirmed')

	if (!confirm) {
		e.stopPropagation()
		return false
	} else if ($this.data('confirmed')) {
		para.confirm = 'yes'
	}


	//if ($this.data('confirm') != undefined) {
		/*
		if (confirm($this.data('confirm'))) {
			para.confirm = 'yes'
		} else {
			e.stopPropagation()
			return false
		}
		*/


		//return false;
	//}

	if (relTarget == 'close' || $this.data('do') == 'closebox') {
		if ($(e.target).closest('.sg-dropbox.box').length != 0) {
			//alert('Close box '+$(e.target).closest('.sg-dropbox.box').attr('class'))
			$('.sg-dropbox.box').children('div').hide()
			$('.sg-dropbox.box.active').removeClass('active')
			return false
		} else if ($('#cboxLoadedContent').length) {
			$.colorbox.close()
			return false
		} else {
			// If no active box do after
		}
		//e.stopPropagation()
	}

	//console.log('data = '+JSON.stringify($this.data()))
	if ($this.data('webview') && typeof Android=="object") {
		var webViewData = JSON.stringify($this.data())
		var pattern = /^((http|https|ftp):\/\/)/
		var location = ''
		if(pattern.test(url)) {
			location = url
		} else {
			location = document.location.origin + url
		}
		Android.showWebView(location, webViewData)
		return false
	}

	if (relTarget == undefined && retUrl == undefined) {
		if (JSON.stringify(para) == '{}') return true
		var hrefUrl = $this.attr('href')
		hrefUrl = hrefUrl + (hrefUrl.indexOf('?') == -1 ? '?' : '&') + $.param(para)
		window.location = hrefUrl
		return false
	}

	console.log("Load from url "+url)
	notify('LOADING');

	$.post(url, para, function(html) {
		notify()
		console.log("Load completed.")
		if (retUrl) {
			console.log("Return URL "+retUrl)
			$.post(retUrl, function(html) {
				sgUpdateData(html, relTarget, $this)
				notify()
			})
		} else {
			sgUpdateData(html, relTarget,$this)

			if ($this.data('moveto')) {
				var moveto = $this.data('moveto').split(',');
				window.scrollTo(parseInt(moveto[0]), parseInt(moveto[1]));
			}
		}

		// REMOVE element after done
		if ($this.data('removeparent')) {
			var removeTag = $this.data('removeparent')
			var $removeElement = removeTag.charAt(0).match(/\.|\#/i) ? $(removeTag) : $this.closest(removeTag)
			$removeElement.remove()
		}

		// CLOSE BOX after done
		if ($this.data('closebox') || $this.data('complete') == 'close') {
			if ($(e.rel).closest('.sg-dropbox.box').length != 0) {
				$('.sg-dropbox.box').children('div').hide()
				$('.sg-dropbox.box.active').removeClass('active')
			} else {
				$.colorbox.close()
			}
		}

		// Process CALLBACK function
		var callback = $this.data("callback");
		if (callback && typeof window[callback] === 'function') {
			window[callback]($this,html);
		} else if (callback) {
			window.location = callback;
		}
	}).fail(function() {
		notify('ERROR ON LOADING')
	})

	return false;
});


//$("#project-detail").animate({ scrollTop: 100 }, 'slow')

$.fn.isOnScreen = function(){
	var element = this.get(0);
	if (!element) return true
	var bounds = element.getBoundingClientRect();
	//console.log("bounds.top "+bounds.top, "innerHeight "+window.innerHeight)
	return bounds.top < window.innerHeight && bounds.bottom > 0;
}

/*
 * sg-form :: Softganz form
 * written by Panumas Nontapan
 * http://softganz.com
 * Using <form class="sg-form"></form>
 */
$(document).on('submit', 'form.sg-form', function(e) {
	var $this = $(this)
	var relTarget = $this.data('rel')
	var retUrl = $this.data('ret')
	var onComplete = $this.data('complete')
	var checkValid = $this.data('checkvalid')
	var errorField = ''
	var errorMsg = ''

	console.log('sg-form :: Submit of '+$this.attr('id'));

	// Check field valid
	if (checkValid) {
		console.log('Form Check input valid start.');
		$this.find('.require, .-require').each(function(i) {
			var $inputTag = $(this);
			//console.log('Form check valid input tag '+$inputTag.prop("tagName")+' type '+$inputTag.attr('type')+' id='+$inputTag.attr('id'))
			if (($inputTag.attr('type') == 'text' || $inputTag.attr('type') == 'password' || $inputTag.attr('type') == 'hidden' || $inputTag.prop("tagName") == 'TEXTAREA') && $inputTag.val().trim() == "") {
				errorField = $inputTag;
				errorMsg = 'กรุณาป้อนข้อมูลในช่อง " '+$('label[for='+errorField.attr('id')).text()+' "';
				$inputTag.focus();
			} else if ($inputTag.prop("tagName") == 'SELECT' && ($inputTag.val() == 0 || $inputTag.val() == -1 || $inputTag.val() == '')) {
				errorField = $inputTag;
				errorMsg='กรุณาเลือกข้อมูลในช่อง " '+$('label[for='+errorField.attr('id')).text()+' "';
			} else if (($inputTag.attr('type') == 'radio' || $inputTag.attr('type') == 'checkbox')
					&& !$("input[name=\'"+$inputTag.attr('name')+"\']:checked").val()) {
				errorField = $inputTag;
				errorMsg = errorField.closest('div').children('label').first().text();
			}
			console.log($inputTag.attr('name'))
			console.log($("input[name=\'"+$inputTag.attr('name')+"\']*:checked").val())
			if (errorField) {
				//console.log('Invalid input '+errorField.attr('id'))
				var invalidId = errorField.attr('id')
				//console.log('invalidId = ',invalidId)
				//$('#'+invalidId).focus();
				//console.log($('#'+invalidId).isOnScreen() ? 'VISIBLE' : 'INVISIBLE')
				if (! $('#'+invalidId).isOnScreen()) {
					$('html,body').animate({ scrollTop: errorField.offset().top-100 }, 'slow');
				}
				notify(errorMsg);
				return false;
			}
		});
		if (errorField) return false;
	}


	if (relTarget == undefined) return true;

	notify('PROCESSING');

	console.log('Send form to '+$this.attr('action'));
	console.log('Result to '+relTarget);

	if ($this.hasClass('-upload')) {
		console.log('UPLAOD FILE')
		$this.ajaxForm({
			success: function(html) {
				console.log('Inline upload file complete.');
				if (typeof Android=="object") Android.showToast('อัพโหลดไฟล์เรียบร้อบ')
				notify("ดำเนินการเสร็จแล้ว.",5000)
				$this.val("")
				$this.replaceWith($this.clone(true))
			}
		}).ajaxSubmit(function(html) {
			//console.log("SUBMIT "+html)
			if (retUrl) {
				//console.log("Return URL "+retUrl)
				$.post(retUrl, function(html) {
					sgUpdateData(html, relTarget,$this)
					notify()
				})
			} else {
				sgUpdateData(html, relTarget,$this)
			}


			if (relTarget != 'notify') notify()
			notify("ดำเนินการเสร็จแล้ว.",5000)
		})

	} else {
		// Start post form
		//console.log('FORM DATA ',$this.serialize())
		$.post(
			$this.attr('action'),
			$this.serialize(),
			function(html) {
				console.log('Form submit completed and send output to '+relTarget);
				if (onComplete == 'remove') {
					$this.remove()
				} else if (onComplete == 'close' || onComplete == 'closebox') {
					if ($(e.rel).closest('.sg-dropbox.box').length!=0) {
						$('.sg-dropbox.box').children('div').hide()
						$('.sg-dropbox.box.active').removeClass('active')
						//alert($(e.rel).closest('.sg-dropbox.box').attr('class'))
					} else {
						$.colorbox.close()
					}
				}

				if (retUrl) {
					console.log("Return URL "+retUrl)
					$.post(retUrl, function(html) {
						sgUpdateData(html, relTarget,$this)
						notify()
					})
				} else {
					sgUpdateData(html, relTarget,$this)

					if ($this.data('moveto')) {
						var moveto = $this.data('moveto').split(',');
						window.scrollTo(parseInt(moveto[0]), parseInt(moveto[1]));
					}
				}


				if (relTarget.substring(0,6) != 'notify') notify()

				// Process callback function
				var callback = $this.data('callback');
				if (callback && typeof window[callback] === 'function') {
					window[callback]($this,html);
				} else if (callback) {
					window.location=callback;
				}
			}, $this.data('dataType') == undefined ? null : $this.data('dataType')
		).fail(function() {
			notify('ERROR ON POSTING')
		})
	}
	return false
})
.on('keydown', 'form.sg-form input:text', function(event) {
	var n = $("input:text").length
	if(event.keyCode == 13) {
		event.preventDefault()
		var nextIndex = $('input:text').index(this) + 1
		if(nextIndex < n)
			$('input:text')[nextIndex].focus()
		return false
	}
});



/*
 * sg-tabs :: Softganz tabs
 * written by Panumas Nontapan
 * http://softganz.com
 * Using <div class="sg-tabs"><ul class="tabs">tab click</ul><div>tab container</div></div>
 */
$(document).on('click', '.sg-tabs>ul.tabs>li>a', function(e) {
	var $this = $(this)
	var $parent = $this.closest('.sg-tabs')
	var href = $this.attr('href')
	$this.closest('ul').children('li').removeClass('active')
	$this.closest('li').addClass('active')

	sgTabIdActive = $this.attr("id")
	//console.log("Tab Active = ",sgTabIdActive)

	if ($this.attr('target') != undefined) return true;
	if (href == undefined || href == 'javascript:void(0)') {
		// do nothing
	} else if (href.left(1) == '#') {
		$parent.children('div').hide()
		$parent.children($this.attr('href')).show()
	} else {
		notify('LOADING')
		//window.history.pushState({},$this.text(),href)
		$.get(href,function(html) {
			$parent.children('div').html(html)
			notify()
		})
	}

	// Process CALLBACK function
	var callback = $this.data("callback")
	if (callback && typeof window[callback] === 'function') {
		window[callback]($this,html)
	} else if (callback) {
		window.location = callback
	}

	return false
});



/*
 * sg-dropbox :: Softganz dropbox
 * written by Panumas Nontapan
 * http://softganz.com
 * Using sg_dropbox()
 */
$(document).on('click', '.sg-dropbox>a', function() {
	var $parent=$(this).parent()
	var $wrapper=$(this).next()
	var $target=$parent.find('.sg-dropbox--content')

	$('.sg-dropbox.click').not($(this).parent()).each(function() {
		$(this).children('div').hide()
	});
	if ($parent.data('type')=='box') {
		$parent.css('display',"block").addClass('active')
		if ($parent.data('url')!=undefined) {
			$target.html('LOADING')
			$wrapper.show()
			$.get($parent.data('url'),function(html) {
				$target.html(html)
			});
		} else $wrapper.show()

	} else if ($parent.data('type')=='click') {
		$wrapper.show()
	} else {
		$wrapper.toggle()
	}
	var offset=$(this).offset()
	var width=$wrapper.width()
	var docwidth=$(document).width()
	var right=0
	if (offset.left+width>docwidth) {
		var right=docwidth-offset.left-$(this).width()-8;//offset.left
		$wrapper.css({'rightside':right+"px"})
	}
	//notify("left: " + offset.left + ", top: " + offset.top+", width="+width+", document width="+docwidth+", right="+right)
 	return false
})
.on('click','body', function(e) {
	$('.sg-dropbox.click').children('div').hide()
	//notify(e.target.className)
	if ($(e.target).closest('.sg-dropbox.box').length===0) {
		$('.sg-dropbox.box').children('div').hide()
		$('.sg-dropbox.box.active').removeClass('active')
	}
});





/*
 * sg-datepicker :: Softganz datepicker
 * written by Panumas Nontapan
 * http://softganz.com
 * Using <input class="sg-datepicker" type="text" data-callback="" />
 * 	data-aldfld = "id"
 * DOWNLOAD : https://flatpickr.js.org/
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

$(document).on('focus', '.sg-datepicker', function(e) {
	console.log('SG-DATEPICKER Start')
	$(this).attr('autocomplete','off')
	var defaults={
		clickInput: true,
		dateFormat: "dd/mm/yy",
		altFormat: "yy-mm-dd",
		altField: "",
		disabled: false,
		monthNames: thaiMonthName,
		beforeShow: function( el ){
			console.log('SG-DATEPICKER Befor show')
			//$(el).css('top','200px')
			//console.log('ele.top',$(el).css('top'))
			// set the current value before showing the widget
			//$('.ui-datepicker').css({'position':'relative','z-index':999999,'top':'300px'})
			$(this).data('previous', $(el).val() );
			$(".ui-datepicker:visible").css({top:"+=5"});
		},
		open: function() {
			console.log('SG-DATEPICKER Open')
			//$(".ui-datepicker:visible").css({top:"+=5"});
		},
		onSelect: function(dateText,inst) {
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

	$(this).datepicker(options)
});





/*
 * sg-address :: Softganz address
 * written by Panumas Nontapan
 * http://softganz.com
 * Using <input class="sg-address" type="text" />
 */
$(document).on('focus', '.sg-address', function(e) {
	var $this=$(this)
	$this
	.autocomplete({
		source: function(request, response){
			console.log('Search address of ' + request.term)
			$.get(url+"api/address?q="+encodeURIComponent(request.term), function(data){
				response(data)
			}, "json");
		},
		minLength: 6,
		dataType: "json",
		cache: false,
		select: function(event, ui) {
			this.value = ui.item.label;
			// Do something with id
			console.log('Return Address : '+ui.item.value)
			if ($this.data('altfld')) $("#"+$this.data('altfld')).val(ui.item.value);

			// Process call back
			var callback = $this.data('callback');
			if (callback) {
				if (callback == 'submit') {
					//$this.closest('form').triger('submit');
					$(this).closest("form").trigger("submit");
				} else if (typeof window[callback] === 'function') {
					 window[callback]($this, ui);
				} else {
					var url = callback + '/' + ui.item.value
					window.location = url;
				}
			}

			return false;
		}
	})
	.autocomplete( "instance" )._renderItem = function( ul, item ) {
		if (item.value=='...') {
			return $('<li class="ui-state-disabled -more"></li>')
			.append(item.label)
			.appendTo( ul );
		} else {
			return $( "<li></li>" )
			.append( "<a><span>"+item.label+"</span>"+(item.desc!=undefined ? "<p>"+item.desc+"</p>" : "")+"</a>" )
			.appendTo( ul )
		}
	}
});





/*
 * sg-autocomplete :: Display autocomplete box
 * written by Panumas Nontapan
 * http://softganz.com
 * Using <form><input class="sg-autocomplete" type="text" /></form>
 * 	data-aldfld = "id"
 *	data-select = string
 *	data-select = {"id":"result field key"[, "id":"result field key"]}
 */
$(document).on('focus', '.sg-autocomplete', function(e) {
	var $this = $(this)
	var $form = $this.closest('form')
	var minLength=1
	if ($this.data('minlength')) minLength=$this.data('minlength')
	$this
	.autocomplete({
		source: function(request, response){
			var para={}
			para.n=$this.data('item');
			para.q=$this.val();
			//console.log("Query "+$this.data('query'))
			notify("กำลังค้นหา");
			$.get($this.data('query'),para, function(data){
				notify();
				response(data);
			}, "json");
		},
		minLength: minLength,
		dataType: "json",
		cache: false,
		open: function() {
			$(".ui-autocomplete:visible").css({top:"+=5"});
			if ($this.data('class')) {
				$this.autocomplete("widget").addClass($this.data('class'));
			}
			if ($this.data('width')) {
				$this.autocomplete("widget").css({"width":$this.data('width')});
			}
		},
		focus: function(event, ui) {
			//this.value = ui.item.label;
			//event.preventDefault();
			return false
		},
		select: function(event, ui) {
			// Return in ui.item.value , ui.item.label
			// Do something with id
			// console.log(ui.item.value);
			if ($this.data('altfld')) {
				var altElement = "#"+$this.data('altfld')
				if ($form.find(altElement).length) {
					$form.find(altElement).val(ui.item.value)
				} else {
					$(altElement).val(ui.item.value)
				}
			}

			// data-select = string
			// data-select = {"id-1":"result field key 1", "id-2":"result field key 2"}
			if ($this.data('select')!=undefined) {
				var selectValue=$this.data('select');
				if (typeof selectValue == 'object') {
					//console.log(selectValue)
					var x;
					for (x in selectValue) {
						$('#'+x).val(ui.item[selectValue[x]]);
						//console.log(x+" "+selectValue[x])
					}
				} else if (typeof selectValue == 'string') {
					$this.val(ui.item[selectValue]);
				}
			} else {
				$this.val(ui.item.label);
			}


			// Process call back
			var callback = $this.data('callback');
			if (callback) {
				if (callback == 'submit') {
					//$this.closest('form').triger('submit');
					$(this).closest("form").trigger("submit");
				} else if (typeof window[callback] === 'function') {
					 window[callback]($this, ui);
				} else {
					var url = callback + '/' + ui.item.value
					window.location = url;
				}
			}
			
			return false;
		}
	})
	.autocomplete( "instance" )._renderItem = function( ul, item ) {
		if (item.value=='...') {
			return $('<li class="ui-state-disabled -more"></li>')
			.append(item.label)
			.appendTo( ul );
		} else {
			return $( "<li></li>" )
			.append( "<a><span>"+(item.altLabel != undefined ? item.altLabel : item.label)+"</span>"+(item.desc != undefined ? "<p>"+item.desc+"</p>" : "")+"</a>" )
			.appendTo( ul )
		}
	}
});




/*
 * Scroll to DOM id or link name
 */
$(document).on('click', 'a[href^=\\#]', function(e) {
	e.preventDefault();
	var dest = $(this).attr('href')
	var isTabClick = $(this).parent().parent().hasClass('tabs')
	//console.log("Goto " + dest + " isTab " + isTabClick + ' '+$(this).parent().parent().attr('class'))

	if (dest=="#" || isTabClick) return;

	var id = dest.substring(1)
	if ($(dest).length) {
		// Scroll to element id
		$('html,body').animate({ scrollTop: $(dest).offset().top }, 'slow');
		return true;
	} else if ($('a[name="'+id+'"]').length) {
		// Scroll to link name
		$('html,body').animate({ scrollTop: $('a[name="'+id+'"]').offset().top - 64 }, 'slow');
		return true;
	}
});





/*
 * Softganz inline upload file
 * written by Panumas Nontapan
 * http://softganz.com
 * Using <form class="sg-upload"><input class="inline-uplaod" type="file" /></form>
 */
$(document).on('change', "form.sg-upload .inline-upload", function() {
	var $this=$(this)
	var $form=$this.closest("form")
	var target=$form.data('rel')
	var targetClass = ' class="ui-item -hover-parent"'

	if (typeof Android=="object") Android.showToast('กำลังอัพโหลดไฟล์')

	console.log('sg-upload :: Inline upload file start and show result in '+target)
	//notify("<p style=\"background-color:#fff;padding:16px;\"><img src=\"/library/img/loading.gif\" alt=\"Uploading\"/> กำลังอัพโหลดไฟล์ กรุณารอสักครู่<br />"+$this.val()+"<br /><img src=\""+$this.val()+"\" /></p>")
	notify('<div class="loader -rotate"></div> กำลังอัพโหลดไฟล์ กรุณารอสักครู่')
	$form.ajaxForm({
		success: function(data) {
			console.log('Inline upload file complete.');
			if (typeof Android=="object") Android.showToast('อัพโหลดไฟล์เรียบร้อบ')
			if (target) {
				if ($form.data('append')) {
					var insertElement='<'+$form.data('append')+targetClass+'>'+data+'</'+$form.data('append')+'>';
					$('#'+target).append(insertElement);
				} else if ($form.data('prepend')) {
					var insertElement='<'+$form.data('prepend')+targetClass+'>'+data+'</'+$form.data('prepend')+'>';
					$('#'+target).prepend(insertElement);
					console.log(insertElement)
				} else if ($form.data('before')) {
					var insertElement='<'+$form.data('before')+targetClass+'>'+data+'</'+$form.data('before')+'>';
					console.log('Before ',$form.data('before'));
					console.log('Value ',insertElement)
					$this.closest($form.data('before')).before(insertElement);
				} else if ($form.data('after')) {
					var insertElement='<'+$form.data('after')+targetClass+'>'+data+'</'+$form.data('after')+'>';
					console.log($form.data('after'));
					console.log(insertElement)
					$this.closest($form.data('after')).after(insertElement);
				} else {
					$('#'+target).html(data);
				}
			}
			notify("ดำเนินการเสร็จแล้ว.",5000)
			$this.val("")
			$this.replaceWith($this.clone(true))
		}
	}).submit()
});


/*
$(document).on('click', 'textarea', function(ele) {
	var $this = $(this)
	//$this.height("5px");
	$this.height($this.prop('scrollHeight')+"px");
	console.log("Text reaa click")
});

$(document).on('keyup', '.inline-edit-field textarea', function(ele) {
	var $this = $(this)
	$this.height("5px");
	$this.height($this.prop('scrollHeight')+"px");
});
*/



/*
 * Softganz inline edit field
 * Written by Panumas Nontapan
 * http://softganz.com
 * Using <div class="sg-inline-edit"><span class="inline-edit-field" data-type="text"></span></div>
 * DOWNLOAD : https://github.com/NicolasCARPi/jquery_jeditable
 */

(function($) { // sg-inline-edit
	var version = '1.01'
	var sgInlineEditAction = 'click'
	var updatePending = 0
	var updateQueue = 0
	var database;
	var ref
	var debug

	$.fn.sgInlineEdit = function(target, options = {}) {
		// default configuration properties
		if (typeof $.fn.editable === 'undefined') {
			console.log('ERROR :: $.editable is not load')
			return
		}

		if ('disable' === target) {
			//$(this).data('disabled.editable', true);
			return;
		}
		if ('enable' === target) {
			//$(this).data('disabled.editable', false);
			return;
		}
		if ('destroy' === target) {
			//$(this)
			//.unbind($(this).data('event.editable'))
			//.removeData('disabled.editable')
			//.removeData('event.editable');
			return;
		}


		var $this = $(this)
		var $parent = $this.closest('.sg-inline-edit')
		var postUrl = $parent.data('update-url');
		var inputType = $this.data('type');
		var callback = $this.data('callback');

		debug = $parent.data('debug') ? true : false

		if (inputType == 'money' || inputType == 'numeric' || inputType == 'text-block') {
			inputType = 'text'
		} else if (inputType == 'radio' || inputType == 'checkbox') {
			//console.log('RADIO or CHECKBOX Click:',$this)
			var value = $this.is(':checked') ? $this.val() : ''
			//self.save($this, value, callback)
			//self.save($this, value, callback);
			//return
		} else if (inputType == 'link') {
			return
		} else if (inputType == '' || inputType == undefined) {
			inputType = 'text'
			$this.data('type','text')
		}

		var defaults = {
			type: inputType,
			container : $(this),
			/*
			onblur : function(value) {
					$(this).closest('.inline-edit-field').removeClass('-active');
					notify(value)
					$(this).closest('form').submit();
				},
				*/
			// onblur: function() {'submit'},
			onblur: $this.data('onblur') ? $this.data('onblur') : 'submit',
			data: function(value, settings) {
					if ($this.data('data'))
						return $this.data('data');
					else if ($this.data('value') != undefined)
						return $this.data('value');
					else if (value == '...')
						return '';
					return value;
				},
			loadurl: $this.data('loadurl'),
			/*loaddata : function(value, settings) {
					console.log($this.data('loaddata'))
					if ($this.data('loaddata')) {
					}
					return {foo: 'bar'};
				},
				*/
				/*
			callback: function(result, settings, submitdata) {
					console.log('CALLBACK')
					console.log(result)
					console.log(settings)
					console.log(submitdata)
					//$this.html('<span>'+result+'</soan>')
				},
				*/
			before : function() {
					//var height = $this.height()
					//console.log('BEFORE EDIT '+$this.attr('class')+' height = '+$this.height())
					//$this.height('500px')
					//$this.find('.form-textarea').height($this.prop('scrollHeight')+'px');
					//$this.find('.form-textarea').height('100%')
				},
			cancel		: $(this).data('button')=='yes' ? '<button class="btn -link -cancel"><i class="icon -cancel -gray"></i><span>ยกเลิก</span></button>':null,
			submit		: $(this).data('button')=='yes' ? '<button class="btn -primary"><i class="icon -save -white"></i><span>บันทึก</span></button>':null,
			placeholder: $(this).data('placeholder') ? $(this).data('placeholder') : '...',
		}

		var dataOptions = $this.data('options')
		var settings = $.extend({}, $.fn.sgInlineEdit.defaults, defaults, options, dataOptions)

		//console.log('dataOptions',dataOptions)
		//console.log($this.data('options'))
		//console.log('SG-INLINE-EDIT SETTING:',settings)

		if ($this.data('type') == 'textarea') settings.inputcssclass = 'form-textarea'
		else if ($this.data('type') == 'text') settings.inputcssclass = 'form-text'
		else if ($this.data('type') == 'number') settings.inputcssclass = 'form-text -number'
		else if ($this.data('type') == 'email') settings.inputcssclass = 'form-text -email'
		else if ($this.data('type') == 'url') settings.inputcssclass = 'form-text -url'
		else if ($this.data('type') == 'autocomplete') settings.inputcssclass = 'form-text -autocomplete'
		else if ($this.data('type') == 'select') settings.inputcssclass = 'form-select'

		$this.editable(function(value, settings) {
			self.save($this, value, callback)
			return value
		} , 
		settings
		).trigger('edit')



		self.save = function($this, value, callback) {
			//console.log('Update Value = '+value)

			if (postUrl == undefined) {
				notify('ข้อมูลปลายทางสำหรับบันทึกข้อมูลผิดพลาด')
				return
			}

			var para = $.extend({},$parent.data(), $this.data())

			delete para['options']
			delete para['data']
			delete para['event.editable']
			delete para['uiAutocomplete']
			para.action = 'save';
			para.value = value.replace(/\"/g, "\"");
			$this.data('value', para.value);

			//if (settings.blank === null && para.value === "") para.value = null
			//console.log(settings.blank)

			//console.log('UPDATE PARA:', para)

			updatePending++
			updateQueue++

			notify('กำลังบันทึก กรุณารอสักครู่....' + (debug ? '<br />Updating : pending = '+updatePending+' To = '+postUrl+'<br />' : ''))

			// Lock all inline-edit-field until post complete
			$parent.find('.inline-edit-field').addClass('-disabled');

			//console.log('length='+$('[data-group="'+para.group+'"]').length)
			//console.log(para)
			
			$.post(postUrl,para, function(data) {
				updatePending--
				$parent.find('.inline-edit-field').removeClass('-disabled');

				if (data == '' || data == '<p>&nbsp;</p>')
					data = '...';

				//console.log('RETURN DATA:', data)

				if (para.ret == 'refresh') {
					window.location = window.location;
				} else if ($this.data('type') == 'autocomplete') {
					$this.data('value',para.value)
					$this.html('<span>'+data.value+'</span>');
				} else if ($this.data('type') == 'radio') {
				} else if ($this.data('type') == 'checkbox') {
				} else if ($this.data('type') == 'select') {
					var selectValue
					if ($this.data('data')) {
						selectValue = $this.data('data')[data.value]
					} else {
						selectValue = data.value
					}
					$this.html('<span>'+selectValue+'</span>');
				} else {
					$this.html('<span>'+data.value+'</span>');
				}


				var replaceTrMsg = '';
				//console.log('para.tr='+para.tr+' data.tr='+data.tr);
				if (para.tr != data.tr) {
					if (data.tr == 0)
						data.tr = '';
					//console.log(para.group+' : '+para.tr+' : '+data.tr)
					$('[data-group="'+para.group+'"]').data('tr', data.tr);
					replaceTrMsg = 'Replace tr of group '+para.group+' with '+data.tr;
					//console.log(replaceTrMsg);
				}

				notify(
					(data.error ? data.error : data.msg)
					+ (debug && data.debug ? '<div class="-sg-text-left" style="white-space: normal;">update queue='+updateQueue+', update pending='+updatePending+'<br />Parameter : group='+para.group+', fld='+para.fld+', tr='+para.tr+', Value='+data.value+'<br />Debug : '+data.debug+'<br />Return : tr='+data.tr+'<br />'+replaceTrMsg+'</div>' : ''),
					debug ? 300000 : 5000);

				// Process callback function

				//console.log(settings)

				if (settings.rel) {
					if (settings.ret) {
						//console.log("Return URL "+settings.ret)
						$.post(settings.ret, function(html) {
							sgUpdateData(html, settings.rel, $this)
						})
					} else {
						sgUpdateData(data.value, settings.rel, $this)
					}
				}

				var callbackFunction = $this.data('callback');
				//console.log("CALLBACK ",callbackFunction)
				if (callbackFunction) {
					if (typeof window[callbackFunction] === 'function') {
						window[callbackFunction]($this,data,$parent);
					} else if ($this.data('callbackType') == 'silent') {
						$.get(callback, function() {})
					} else {
						window.location = callback;
					}
				}

			},'json')
			.fail(function() {
		    notify('ERROR ON POSTING. Please Contact Admin.');
		  });


			/*
			if (firebaseConfig) {
				//console.log(para);
				var data = {}
				data.tags = 'Project Transaction Update';
				if (typeof para.id != 'undefined')
					data.tpid = para.id;
				if (typeof para.group != 'undefined')
					data.group = para.group;
				if (typeof para.fld != 'undefined')
					data.field = para.fld;
				if (typeof para.tr != 'undefined')
					data.tr = para.tr;
				data.value = para.value;
				data.url = window.location.href;
				data.time = firebase.database.ServerValue.TIMESTAMP;
				//console.log(data)
				//ref = database.ref('/update/aa/');
				ref.push(data, function(error){
					if (error) {
						console.log('Data could not be saved.' + error);
					} else {
						console.log('Data saved successfully.');
					}
				});
				//ref.off();
				//console.log(ref);
			}
			*/
		}

		// SAVE value immediately when radio or checkbox click
		if (inputType == 'radio' || inputType == 'checkbox') {
			self.save($this, value, callback)
		}

		// RETURN that can call from outside
		return {
			// GET VERSION
			getVersion: function() {
				return version
			},

			// SAVE DATA IN FORM TO TARGET
			update: function($this, value, callback) {
				self.save($this, value, callback)
			}
		}
	}

	/* Publicly accessible defaults. */
	$.fn.sgInlineEdit.defaults = {
		indicator			: '<div class="loader -rotate"></div>',
		tooltip 			: 'คลิกเพื่อแก้ไข',
		cssclass			: 'inlineedit',
		width					: 'none',
		height 				: 'none',
    cancelcssclass: 'btn -link -cancel',
    submitcssclass: 'btn -primary',
		showButtonPanel: true,
		indicator 		: 'SAVING',
		event 				: 'edit',
		inputcssclass : '',
		autocomplete 	: {},
		datepicker 		: {},
	}



	$(document).on(sgInlineEditAction, '.sg-inline-edit .inline-edit-field', function() {
		console.log('$.sgInlineEdit version ' + version + ' start')
		$(this).sgInlineEdit()
	})

	$(document).on('keydown', ".sg-inline-edit .inline-edit-field", function(evt) {
		if(evt.keyCode==9) {
			var $this=$(this);
			var $allBox=$this.closest(".sg-inline-edit");
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

})(jQuery);


// Add editable plugin
// Move into sgInlineEdit after fixed all other inline-edit to sg-inline-edit
$(document).ready(function() {

	if (typeof $.fn.editable === 'undefined') return

	$.editable.addInputType('checkbox', {
	});

	$.editable.addInputType('radio', {
	});

	//Add input type autocomplete to jEditable
	$.editable.addInputType('autocomplete', {
		element : $.editable.types.text.element,
		plugin : function(settings, original) {
			$(original).attr( 'autocomplete','off' );
			var defaults = {
				target: '',
				source: function(request, response) {
					var queryUrl = settings.autocomplete.query
					var para = {}
					para.q = request.term
					$.get(queryUrl,para, function(data){
						response($.map(data, function(item){
							// RETURN all of data field
							return item
						}))
					}, 'json');
				},
				minLength: 2,
				dataType: 'json',
				cache: false,
				// On move up/down by keyboard or mouse over
				focus: function(event,ui) {
					event.preventDefault();
					//console.log('FOCUS '+ui.item.label)
					//settings.container.find('input').val(ui.item.label);
				},
				select: function(event, ui) {
					//console.log('ui.item',ui.item)
					this.value = ui.item.label;
					//settings.container.data('value',ui.item.value)
					var targetValue = settings.autocomplete.target
					console.log('targetValue',targetValue)
					if (targetValue) {
						if (typeof targetValue == 'string') {
							targetValue = JSON.parse('{"'+targetValue+'": "value"}')
						}
						//console.log("HAVE TARGET", targetValue)
						for (var key in targetValue) {
							//$('#'+x).val(ui.item[selectValue[x]]);
							var dataValue = ui.item[targetValue[key]]
							console.log('key = ' + key + ' , value = item.ui.'+ dataValue)
							if (key.substring(0,1) == '#' || key.substring(0,1) == '.') {
								$(key).val(ui.item.value)
							} else {
								$(original).data(key, dataValue)
								console.log('data of key '+ key +' = '+$(original).data(key))
							}
						}
					}

					/*
					if (target && target.substring(0,1) == '#') {
						if ($form.find(target).length) {
							$form.find(target).val(ui.item.value)
						} else {
							$(target).val(ui.item.value)
						}

					} else if (target) {
					}
					*/

					/*
					if ($this.data('select')!=undefined) {
						var selectValue=$this.data('select');
						if (typeof selectValue == 'object') {
							console.log(selectValue)
							var x;
							for (x in selectValue) {
								$('#'+x).val(ui.item[selectValue[x]]);
								console.log(x+" "+selectValue[x])
							}
						} else if (typeof selectValue == 'string') {
							$this.val(ui.item[selectValue]);
						}
					} else {
						$this.val(ui.item.label);
					}
					*/
					//if (settings.container.data('ret') == 'address') {
					//	settings.container.data('areacode',ui.item.value)
					//}
					$(this).submit()
				}
			}
			
			settings.autocomplete = $.extend({}, defaults,settings.autocomplete)

			//console.log('SG-INLINE-EDIT:AUTOCOMPLETE settings:',settings)


			$('input', this).autocomplete(
				settings.autocomplete,
			)
			.autocomplete( 'instance' )._renderItem = function( ul, item ) {
				if (item.value=='...') {
					return $('<li class="ui-state-disabled -more"></li>')
					.append(item.label)
					.appendTo( ul );
				} else {
					return $( '<li></li>' )
					.append( '<a><span>'+item.label+'</span>'+(item.desc!=undefined ? '<p>'+item.desc+'</p>' : '')+'</a>' )
					.appendTo( ul )
				}
			}
		}
	})


	$.editable.addInputType('datepicker-old', {

		element : function(settings, original) {
			var input = $('<input class="form-text -datepicker" />');
			input.attr( 'autocomplete','off' );
			if (settings.datepicker) {
				input.datepicker(settings.datepicker);
			} else {
				input.datepicker();
			}

			// get the date in the correct format
			if (settings.datepicker.format) {
				input.datepicker('option', 'dateFormat', settings.datepicker.format);
			}

			$(this).append(input);
			return(input);
		},

		submit: function (settings, original) {
			var dateRaw = $('input', this).datepicker('getDate');
			var dateFormatted;

			if (settings.datepicker.format) {
				dateFormatted = $.datepicker.formatDate(settings.datepicker.format, new Date(dateRaw));
			} else {
				dateFormatted = dateRaw;
			}
			$('input', this).val(dateFormatted);
			},

			plugin : function(settings, original) {
			// prevent disappearing of calendar
			settings.onblur = 'submit';
		}
	})

	$.editable.addInputType('datepicker', {

		element : function(settings, original) {
			var input = $('<input class="form-text -datepicker" />')
			input.attr( 'autocomplete','off' )

			var defaults = {
					format: 'dd/mm/yy',
					monthNames: thaiMonthName,
					beforeShow: function( el ){
						// set the current value before showing the widget
						//$(this).data('previous', $(el).val() );
						console.log('INLINE DATE BEFORE SHOWs')
						$(".ui-datepicker:visible").css({top:"+=5"});
					},
					open: function() {
						console.log('INLINE DATEOPEN')
						$(".ui-datepicker:visible").css({top:"+=5"});
					},
					onSelect: function() {
							// clicking specific day in the calendar should
							// submit the form and close the input field
							$(this).submit();
						},
				}
			settings.datepicker = $.extend({},defaults)

			input.datepicker(settings.datepicker);

			// get the date in the correct format
			if (settings.datepicker.format) {
				input.datepicker('option', 'dateFormat', settings.datepicker.format);
			}

			$(this).append(input);
			return(input);
		},

		submit: function (settings, original) {
				var dateRaw = $('input', this).datepicker('getDate');
				var dateFormatted;

				if (settings.datepicker.format) {
					dateFormatted = $.datepicker.formatDate(settings.datepicker.format, new Date(dateRaw));
				} else {
					dateFormatted = dateRaw;
				}
				$('input', this).val(dateFormatted);
				$('input', this).datepicker('hide');
			},

			plugin : function(settings, original) {
			// prevent disappearing of calendar
			//settings.onblur = 'submit';
			settings.onblur = 'nothing'
		}
	})

	/*
	$.editable.addInputType( 'datepicker', {
		// create input element
		element: function( settings, original ) {
			var form = $( this ),
			input = $( '<input class="form-text" />' );
			input.attr( 'autocomplete','off' );
			form.append( input );
			return input;
		},

		attach jquery.ui.datepicker to the input element
		plugin: function( settings, original ) {
			var form = this,
			input = form.find( 'input' );

			// Don't cancel inline editing onblur to allow clicking datepicker
			settings.onblur = 'nothing';

			input.datepicker( {
				dateFormat: settings.dateFormat,
				monthNames: settings.monthNames,
				showButtonPanel: settings.showButtonPanel,
				changeMonth: settings.changeMonth,
				changeYear: settings.changeYear,
				altFormat: settings.altFormat,
				altField: settings.altField,

				onSelect: function() {
					// clicking specific day in the calendar should
					// submit the form and close the input field
					form.submit();
					console.log('SELECT')
				},

				onClose: function() {
						setTimeout( function() {
							if ( !input.is( ':focus' ) ) {
								// input has NO focus after 150ms which means
								// calendar was closed due to click outside of it
								// so let's close the input field without saving
								original.reset( form );
							} else {
								// input still HAS focus after 150ms which means
								// calendar was closed due to Enter in the input field
								// so lets submit the form and close the input field
								form.submit();
							}
							// the delay is necessary; calendar must be already
							// closed for the above :focus checking to work properly;
							// without a delay the form is submitted in all scenarios, which is wrong
						}, 150 );
					}
			} )
		}
	})
	*/

	/*
	if (firebaseConfig) {
		database = firebase.database();
		ref = database.ref('/update/');
	}
	*/
});






/*
 * sg-chart :: Display Google chart
 * Written by Panumas Nontapan
 * http://softganz.com
 * Using <div class="sg-chart" data-chart-type="bar" data-options='{}'><h3>Chart Title</h3><table><tbody><tr><td>..</td><td>..</td></tr>...</tbody></table></div>
 */
$(document).ready(function() {
	$('.sg-chart').each(function(index) {
		var $container=$(this);
		var chartId=$container.attr("id");
		var chartTitle=$container.find("h3").text();
		var chartType=$container.data("chartType");
		var $chartTable=$(this).find("table");
		var chartData=[];
		var chartColumn=[];
		var options={};

		if (chartType==undefined) chartType="col"

		console.log("=== sg-chart create "+chartId+" ===")
		console.log('Chart Title : '+chartTitle+' Chart Type : '+chartType)


		var defaults={
						pointSize: 4,
						vAxis: {
							viewWindowMode: "explicit",
						},
						hAxis: {
							textStyle: {
								fontSize:10,
							}
						},
						annotations: {
							textStyle: {
								fontSize:9,
							},
						},
				};
		if ($container.data("series")==2) {
			defaults.series={
											0:{targetAxisIndex:0},
											1:{targetAxisIndex:1},
										}
		}
		var options = $.extend(defaults, $(this).data('options'));
		//options=$(this).data('options');
		//console.log(defaults);

		$.each($chartTable.find('tbody>tr'),function(i,eachRow){
			var $row=$(this)
			//console.log($row.text())
			var rowData=[]
			$.each($row.find('td'),function(j,eachCol){
				var $col=$(this)
				var colKey=$col.attr('class').split(':')
				if (i==0) chartColumn.push([colKey[0],colKey[1],colKey[2]==undefined?'':colKey[2]])
				var colValue
				if (colKey[0]=="string") colValue=$col.text()
				else colValue=Number($col.text().replace(/[^\d\.]/g,''))
				//console.log($col.attr('class')+$col.text())
				rowData.push(colValue)
			})
			chartData.push(rowData)
			//console.log(rowData)
		})
		//console.log('Chart Data')
		//console.log(chartData)
		//console.log('Chart Column : '+chartColumn)

		google.charts.load("current", {"packages":["corechart"]});
		google.charts.setOnLoadCallback(drawChart);

		function drawChart() {
			/*
			options = {
											pointSize: 4,
											vAxis: {
												viewWindowMode: "explicit",
											},
										};
			if ($container.data("series")==2) {
				options.series={
												0:{targetAxisIndex:0},
												1:{targetAxisIndex:1},
											}
			}
			if (chartType=="pie") {
				options = {
												legend: {position: "none"},
												// chartArea: {width:"100%",height:"80%"},
											};
				console.log($container.data("options"))
				if ($container.data("options")) options=$container.data("options");
				//options.legend="label";
				//options.pieSliceText="percent";
				//options.legend.position="labeled";
				//options.legend.position=$container.data("legendSeries")?$container.data("legendSeries"):"none";
												// chartArea: {width:"100%",height:"80%"},
			}
											*/
			options.title=chartTitle;
			//console.log(options);
			var data = new google.visualization.DataTable();
			// Add chart column
			$.each(chartColumn,function(i){
				if (chartColumn[i][2]=='role') data.addColumn({type: chartColumn[i][0], role: 'annotation'});
				else data.addColumn(chartColumn[i][0],chartColumn[i][1]);
			})
			// Add chart rows
			data.addRows(chartData);

			var chartContainer=document.getElementById(chartId)
			var chart
			//var chart = new google.visualization.PieChart(chartContainer);
			if (chartType=="line") {
				chart = new google.visualization.LineChart(chartContainer);
			} else if (chartType=="bar") {
				chart = new google.visualization.BarChart(chartContainer);
			} else if (chartType=="col") {
				chart = new google.visualization.ColumnChart(chartContainer);
			} else if (chartType=="pie") {
				chart = new google.visualization.PieChart(chartContainer);
			} else if (chartType=="combo") {
				chart = new google.visualization.ComboChart(chartContainer);
			}
			if ($container.data("image")) {
				google.visualization.events.addListener(chart, 'ready', function () {
					var imgUri = chart.getImageURI();
					// do something with the image URI, like:
					document.getElementById($container.data("image")).src = imgUri;
				});
			}
			chart.draw(data, options);
		}
	});
});



// Province change
$(document).on('change','.sg-changwat',function() {
	var $this=$(this)
	var $form=$this.closest('form');
	var $changwat=$form.find('.sg-changwat');
	var $ampur=$form.find('.sg-ampur');
	var $tambon=$form.find('.sg-tambon');
	var $village=$form.find('.sg-village');

	console.log('Get Ampur of ' + $this.val())
	if ($this.val()=='') {
		$ampur.val("").hide();
	} else {
		$ampur.val("").show();
	}
	$tambon.val("").hide();
	if ($village.length) $village.val("").hide()
	if ($ampur.length) $ampur[0].options.length = 1;
	if ($tambon.length) $tambon[0].options.length = 1;
	if ($village.length) $village[0].options.length = 1;
	if ($this.data('altfld')) $($this.data('altfld')).val($this.val());
	$.get(url+'api/ampur',{q:$this.val()}, function(data) {
		for (var i = 0; i < data.length; i++) {
			$ampur.append(
				$("<option></option>")
				.text(data[i].label)
				.val(data[i].ampur)
			);
		};
	},'json')
	if ($this.data('change') == 'submit') $form.submit();
	//$this.closest('form').submit()
});

// Ampur change
$(document).on('change','.sg-ampur', function() {
	var $this=$(this);
	var $form=$this.closest('form');
	var $changwat=$form.find('.sg-changwat');
	var $ampur=$form.find('.sg-ampur');
	var $tambon=$form.find('.sg-tambon');
	var $village=$form.find('.sg-village');

	console.log('Get Tambon of ' + $this.val())

	if ($this.val()=='') {
		$tambon.val("").hide();
	} else {
		$tambon.val("").show();
	}
	$village.val("").hide()
	if ($tambon.length) $tambon[0].options.length = 1;
	if ($village.length) $village[0].options.length = 1;
	if ($changwat.data('altfld')) $($changwat.data('altfld')).val($changwat.val()+$this.val());
	$.get(url+'api/tambon',{q:$changwat.val()+$ampur.val()}, function(data) {
		for (var i = 0; i < data.length; i++) {
			$tambon.append(
				$("<option></option>")
				.text(data[i].label)
				.val(data[i].tambon)
			);
		};
	},'json')
	if ($this.data('change') == 'submit') $form.submit();
});

// Tambon change
$(document).on('change','.sg-tambon', function() {
	var $this=$(this)
	var $form=$this.closest('form');
	var $changwat=$form.find('.sg-changwat');
	var $ampur=$form.find('.sg-ampur');
	var $tambon=$form.find('.sg-tambon');
	var $village=$form.find('.sg-village');

	console.log('Get Village of ' + $this.val())

	if ($changwat.data('altfld')) $($changwat.data('altfld')).val($changwat.val()+$ampur.val()+$this.val());
	if (!$village.length) return;
	if ($this.val()=='') {
		$village.val("").hide();
	} else {
		$village.val("").show()
	}
	if ($village.length) $village[0].options.length = 1;
	$.get(url+'api/village',{q:$changwat.val()+$ampur.val()+$tambon.val()}, function(data) {
		for (var i = 0; i < data.length; i++) {
			$village.append(
				$("<option></option>")
				.text(data[i].label)
				.val(data[i].village)
			);
		};
	},'json')
	if ($this.data('change') == 'submit') $form.submit();
});

// Village cgange
$(document).on('change','.sg-village', function() {
	var $this=$(this)
	var $form=$this.closest('form');
	var $changwat=$form.find('.sg-changwat');
	var $ampur=$form.find('.sg-ampur');
	var $tambon=$form.find('.sg-tambon');
	var $village=$form.find('.sg-village');

	console.log($this.val())

	if ($changwat.data('altfld')) $($changwat.data('altfld')).val($changwat.val()+$ampur.val()+$tambon.val()+$this.val());

	if ($this.data('change') == 'submit') $form.submit();
});



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
 * 	<div id="slider">
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
(function($) {
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
(function($){
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
})(jQuery);







/*!
 * Responsive Menu v0.0.0 by @softganz
 * Copyright 2013 Softganz Group.
 * Licensed under http://www.apache.org/licenses/LICENSE-2.0
 *
 * Designed and built with all the love in the world by @softganz.
 */

if (typeof jQuery === "undefined") { throw new Error("Responsive Menu requires jQuery") }

+function ($) {

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
		this.$element.prepend('<button type="button" class="sg-navtoggle" aria-hidden="true"><i aria-hidden="true" class="icon-menu"><span>&nbsp;</span><span>&nbsp;</span><span>&nbsp;</span></i><span>Menu</span></button>')
		var $parent=this.$element
		$(this.$element).on('click','.sg-navtoggle', function() {
			$parent.toggleClass('active')
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
}(jQuery);

