var thaiMonthName=["มกราคม", "กุมภาพันธ์", "มีนาคม", "เมษายน", "พฤษภาคม", "มิถุนายน", "กรกฎาคม", "สิงหาคม", "กันยายน", "ตุลาคม", "พฤศจิกายน", "ธันวาคม"];
var defaultRelTarget="#main";

function go(prompt,url) {
	if ( prompt=='' ) prompt="Please confirm (Yes/No) ?";
	if (confirm(prompt)) {
		window.location = url;
		return true;
	}
}

function popup(url,width,height) {
	cusuwinpopup=window.open(url,'','toolbar=no,location=no,directories=no,menubar=no,status=yes,scrollbars=yes,resizable=no,width='+width+',height='+height);
	return false;
}

function sb(txt) { window.status = txt; return true; }

function notify(text,delay) {
	var msg = $('#notify');
	var width = $(document).width();

	if (text==undefined || text==null) text='';
	if (text=='') {
		msg.hide();
		return;
	}
	msg.html(text)
	.fadeIn()
	.click(function() {$(this).hide()})
	.css({
		'display':'inline-block',
		'left' : width/2 - (msg.width() / 2), // half width - half element width
		'z-index' : 999999 // make sure element is on top
	})
	.show();

	if (delay) setTimeout(function() { msg.fadeOut().hide(); }, delay);
}

fetch_unix_timestamp = function() { return parseInt(new Date().getTime().toString().substring(0, 10))}

function limitText(limitField, limitCount, limitNum) {
	if (limitField.value.length > limitNum) {
		limitField.value = limitField.value.substring(0, limitNum);
	} else {
		document.getElementById(limitCount).innerHTML = limitNum - limitField.value.length;
	}
}

function sleep(ms) {
	var dt = new Date();
	dt.setTime(dt.getTime() + ms);
	while (new Date().getTime() < dt.getTime());
}


function debug (html,clear) {
	var elem=document.getElementById('debug');
	if (elem==undefined) return;
	if (clear) elem.innerHTML='';
	elem.innerHTML = elem.innerHTML+html;
}

function scrollObject(main, width, height, direct, pause, speed) {
	var self = this;
	this.main = main;
	this.width = width;
	this.height = height;
	this.direct = direct;
	this.pause = pause;
	this.speed = Math.max(1.001, Math.min((direct == "up" || direct == "down") ? height : width, speed));
	this.block = new Array();
	this.blockprev = this.offset = 0;
	this.blockcurr = 1;
	this.mouse = false;
	this.scroll = function() {
		if (!document.getElementById) return false;
		this.main = document.getElementById(this.main);
			while (this.main.firstChild) this.main.removeChild(this.main.firstChild);
			this.main.style.overflow = "hidden";
		this.main.style.position = "relative";
		this.main.style.width = this.width + "px";
		this.main.style.height = this.height + "px";
		for (var x = 0; x < this.block.length; x++) {
		//			var container = document.createElement('table');
		//			table.cellPadding = table.cellSpacing = table.border = "0";
			var container = document.createElement('div');
			container.style.position = "absolute";
			container.style.left = container.style.top = "0px";
			container.style.width = this.width + "px";
			container.style.height = this.height + "px";
			container.style.overflow = container.style.visibility = "hidden";
			//			var tbody = document.createElement('div');
			//			var tr = document.createElement('tr');
			//			var td = document.createElement('td');
			//			td.innerHTML = this.block[x];
			//			tr.appendChild(td);
			//			tbody.appendChild(tr);
			//			table.appendChild(tbody);
			container.innerHTML = this.block[x];
			this.main.appendChild(this.block[x] = container);
		}
			if (this.block.length > 1) {
			this.main.onmouseover = function() { self.mouse = true; }
			this.main.onmouseout = function() { self.mouse = false; }
			setInterval(function() {
						if (!self.offset && self.scrollLoop()) self.block[self.blockcurr].style.visibility = "visible";
					}, this.pause);
			}
		this.block[this.blockprev].style.visibility = "visible";
		}
		this.scrollLoop = function() {
		if (!this.offset) {
			if (this.mouse) return false;
			this.offset = (this.direct == "up" || this.direct == "down") ? this.height : this.width;
		} else this.offset = Math.floor(this.offset / this.speed);
		if (this.direct == "up" || this.direct == "down") {
			this.block[this.blockcurr].style.top = ((this.direct == "up") ? this.offset : -this.offset) + "px";
			this.block[this.blockprev].style.top = ((this.direct == "up") ? this.offset - this.height : this.height - this.offset) + "px";
		} else {
			this.block[this.blockcurr].style.left = ((this.direct == "left") ? this.offset : -this.offset) + "px";
			this.block[this.blockprev].style.left = ((this.direct == "left") ? this.offset - this.width : this.width - this.offset) + "px";
		}
		if (!this.offset) {
			this.block[this.blockprev].style.visibility = "hidden";
			this.blockprev = this.blockcurr;
			if (++this.blockcurr >= this.block.length) this.blockcurr = 0;
		} else setTimeout(function() { self.scrollLoop(); }, 50);
		return true;
	}
	this.loadDataMarqueeAndScroll = function () {
		var listP=document.getElementById(this.main).childNodes;
		var aTmp = new Array ();
		for (var x=0; x<listP.length; x++){
			el=listP[x];
			if (el.nodeName=='P') aTmp.push(""+(el.innerHTML));
		}
		this.block = aTmp;
		this.scroll();
	}
}

/**
 * dom tools functions
 */
dom = {
	element : function (id) {
		var e = document.getElementById(id);
		return e;
	} ,

	toggle : function (id) {
		o=document.getElementById(id)
		var display = dom.getStyle(o, "display");
			if (o.style) o.style.display = (display != "none") ? "none" : dom.getDisplayStyleByTagName(o);
	} ,

	solo : function (show,hide1,hide2,hide3,hide4,hide5) {
		o=document.getElementById(show)
		if (o!=undefined) {
			var display = dom.getStyle(o, "display");
				if (o.style) o.style.display = dom.getDisplayStyleByTagName(o);
		}
		if(hide1 != undefined) {
			o=document.getElementById(hide1)
			if (o!=undefined) {
				var display = dom.getStyle(o, "display");
				if (o.style) o.style.display = "none";
			}
		}
		if(hide2 != undefined) {
			o=document.getElementById(hide2)
			if (o!=undefined) {
				var display = dom.getStyle(o, "display");
				if (o.style) o.style.display = "none";
			}
		}
		if(hide3 != undefined) {
			o=document.getElementById(hide3)
			if (o!=undefined) {
				var display = dom.getStyle(o, "display");
				if (o.style) o.style.display = "none";
			}
		}
		if(hide4 != undefined) {
			o=document.getElementById(hide4)
			if (o!=undefined) {
				var display = dom.getStyle(o, "display");
				if (o.style) o.style.display = "none";
			}
		}
		if(hide5 != undefined) {
			o=document.getElementById(hide5)
			if (o!=undefined) {
				var display = dom.getStyle(o, "display");
				if (o.style) o.style.display = "none";
			}
		}

	} ,

	getDisplayStyleByTagName : function (o) {
		n = o.nodeName.toLowerCase();
		return (
			n == "span"
			|| n == "img"
			|| n == "a"
			) ? "inline" : "block";
	} ,

	getStyle : function (el, style) {
		if (!document.getElementById || !el) return;
		if (document.defaultView && document.defaultView.getComputedStyle) {
			return document.defaultView.getComputedStyle(el, "").getPropertyValue(style);
		} else if (el.currentStyle) {
			return el.currentStyle[style];
		} else {
			return el.style.display;
		}
	} ,

	getFormValue : function(id) {
		params='podcast[title]='+dom.element('edit-title').value;
		params+='&podcast[body]='+dom.element('edit-podcast-body').value;
		return params;
	}

}

/**
 * Ajax tools functions
 */
ajax = {
	Version: '0.0.2',
	loadStatusText:'<div class="loading">Loading...</div>',
	boxHeader:'<div style="text-align:right;"><input type="submit" value="close" onClick="ajax.HideBox()"></div>',
	activeRequestCount: 0,
	getTransport : function() {
		var xmlHttp=null;
		// Firefox, Opera 8.0+, Safari
		try { xmlHttp=new XMLHttpRequest(); }
		catch (e) {
			// Internet Explorer
			try { xmlHttp=new ActiveXObject("Msxml2.XMLHTTP"); }
			catch (e) { xmlHttp=new ActiveXObject("Microsoft.XMLHTTP");}
		}
		return xmlHttp;
	} ,

	Updater : function(container, url, headerText) {
		this.container=container;
		this.stateChange = function stateChange() {
			if (transport.readyState==4) {
				//			alert(transport.responseText);
				//				document.getElementById(container).innerHTML=headerText;
				//				document.getElementById(container).innerHTML=transport.responseText;
				document.getElementById(container).innerHTML=(typeof(headerText)!='undefined'?headerText:'')+transport.responseText;
			}
		}

		document.getElementById(this.container).innerHTML=ajax.loadStatusText+document.getElementById(this.container).innerHTML;
		var transport=ajax.getTransport();
		if (transport==null) {
			alert('Your browser does not support AJAX!');
			return;
		}
		transport.onreadystatechange=this.stateChange;
		transport.open("GET",url,true);
		transport.send(null);
	} ,

	create_http_handle : function(TYPE){
		var http_handle = false;
		if (window.XMLHttpRequest){
			http_handle = new XMLHttpRequest();
			if (http_handle.overrideMimeType){
				if (TYPE == "XML"){
					http_handle.overrideMimeType('text/xml');
				} else {
					http_handle.overrideMimeType('text/html');
				}
			}
		} else if (window.ActiveXObject){
			try {
				http_handle = new ActiveXObject("Msxml2.XMLHTTP");
			} catch (e) {
				try {
					http_handle = new ActiveXObject("Microsoft.XMLHTTP");
				} catch (e) {}
			}
		}
		if (!http_handle){
			alert("We are sorry but you are using an outdated browser.  To view this site you must update your browser.");
			return false;
		} else {
			return http_handle;
		}
	} ,

	sendHTTPrequest : function(id, url, method, params, type){
		if (type== "")type= "HTML";
		http = this.create_http_handle(type);
		http.onreadystatechange = function() {
			if(http.readyState == 4 && http.status == 200) {
				// id in format function() them evaluate function
				if (id.indexOf('(')>0) {
					eval(id);
				} else {
					document.getElementById(id).innerHTML=http.responseText;
				}
			}
		}
		//Kill the Cache problem in IE.
		var now = "upid=" + new Date().getTime();
		params += (params.indexOf("?")+1) ? "&" : "?";
		params += now;
		if (method == "POST"){
			http.open('POST', url, true);
			http.setRequestHeader("Content-type", "application/x-www-form-URLencoded");
			http.setRequestHeader("Content-length", params.length);
			http.setRequestHeader("Connection", "close");
			http.send(params);
		} else {
			http.open('GET', url+ params, true);
			http.send(null);
		}
	} ,

	link : function (id,url,header) {
		if (jQuery) {
			notify("Please wait...");
			$id=$("#"+id);
			$id.fadeOut('fast').hide();
			$.get(url,function(data) {
				$id.html(data)
				.slideDown("slow");
				notify();
			});
			return false;
		} else {
			new ajax.Updater(id,url,header);
		}
		return false;
	} ,

	go : function (id,prompt,url) {
		if ( prompt== '' ) prompt='Please confirm to continue?';
		if (confirm(prompt)) {
			new ajax.Updater(id,url);
		}
	} ,

	getAbsolutePos : function(el) {
		var r = { x: el.offsetLeft, y: el.offsetTop };
		if (el.offsetParent) {
			var tmp = ajax.getAbsolutePos(el.offsetParent);
			r.x += tmp.x;
			r.y += tmp.y;
		}
		return r;
	} ,

	box : function (e,id,width,height,text){
	//	ajax.HideBox();
		if(document.all)e = event;

		var obj = document.getElementById(id);

		obj.style.display = 'block';
		obj.style.position= 'absolute';
		obj.style.width = width+'px';
		if (height>0) obj.style.height= height+'px';
		var st = Math.max(document.body.scrollTop,document.documentElement.scrollTop);
		if(navigator.userAgent.toLowerCase().indexOf('safari')>=0)st=0;
		if (obj.offsetParent) parent_r=ajax.getAbsolutePos(obj.offsetParent);

		var leftPos = e.clientX - parent_r.x;
		if(leftPos<0)leftPos = 0;
		obj.style.left = leftPos + 'px';
	//	obj.style.top = e.clientY - obj.offsetHeight -1 + st + 'px';
		obj.style.top = e.clientY+st+10+ 'px';
	//	text = text+'left='+obj.style.left+', top='+obj.style.top+',clientX='+e.clientX+',clientY='+e.clientY+',offsetHeight='+obj.offsetHeight;
		if (text.substr(0,4)=='url:') {
			url=text.substr(4,text.length-4);
			new ajax.Updater('box',url,ajax.boxHeader);
		} else {
			obj.innerHTML = text;
		}
	} ,

	HideBox : function () {
		document.getElementById('box').style.display = 'none';
	} ,

	viewimage : function view_image(img,width,height,prop) {
		var scroll = "no"
		var w = width;
		var h = height;
		if (width>=800) { w=800; scroll = "yes"; }
		if (height>=800) { h=800; scroll = "yes"; }
		view_image_win=window.open("",'','toolbar=no,location=no,directories=no,menubar=no,status=no,scrollbars='+scroll+',resizable=yes,width='+w+',height='+h);
		view_image_win.focus();
		view_image_win.document.write("<html>\n");
		view_image_win.document.write("<head><title>"+img+" : "+width+"x"+height+" pixel</title><style><!-- * {margin:0;padding:0}--></style></head>\n");
		view_image_win.document.write("<body>\n");
		view_image_win.document.write("<center><img src='"+img+"' ><center>");
		view_image_win.document.write("</body>\n");
		view_image_win.document.write("</html>\n");
		view_image_win.document.close();
	} ,
	linkRelationInit : function () {
	}
		/*
			linkRelationInit : function () {
				var a_list=document.getElementsByTagName("A") //array containing the A elements
		//		alert('<p><em>relation init</em></p>');
				for (var x=0; x<a_list.length; x++){ //loop through each A element
					alink=a_list[x];
					if (alink.getAttribute("rel")){
						var modifiedurl=alink.getAttribute("href").replace(/^http:\/\/[^\/]+\//i, "http://"+window.location.hostname+"/")
						alink.setAttribute("href", modifiedurl) //replace URL's root domain with dynamic root domain, for ajax security sake
						alink.onclick=function(){
		//					alert('link relation')
							ajax.Updater(this.getAttribute("rel"),this.getAttribute("href"))
							return false
						}
					}
				}
			} ,

		*/
}

/**
 * Editor functions
 */
editor = {
	version : '0.0.3b',
	controls : new Array() ,
	start_tag : '',
	end_tag : '',

	click : function (e) {
		e?evt=e:evt=event;
		var cSrc=evt.target?evt.target:evt.srcElement;
		var elem=document.getElementsByTagName('textarea');
		var ctrl=cSrc.parentNode;
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
		dom.toggle(id);
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
		dom.toggle(id);
	}
}


/**
 * Tab content
 */
function tabs(id) {
	var self = this;
	this.id = id;
	this.waitTime=0;
	this.rotateTime = 1000;
	this.tabCount=0;
	this.tabId=new Array();

	this.display = function (sID) {
		oObj = document.getElementById(sID);
		if (oObj) {
			oObj.style.display='block';
		}
	}
	this.hide = function (sID) {
		oObj = document.getElementById(sID);
		if (oObj) {
			oObj.style.display='none';
		}
	}
	this.getTabCount = function () {
		var listLI=document.getElementById(this.id).childNodes;
		for (var x=0; x<listLI.length; x++){
			el=listLI[x];
			if (el.nodeType==1 && el.nodeName=='LI') {
				this.tabCount++;
				this.tabId[this.tabCount]=el.id;
			}
		}
	}
	this.getTab = function (id){
		if (this.tabCount==0) this.getTabCount();
		for (i=1;i<=this.tabCount;i++) {
			if (id == i) {
				this.display(this.tabId[i]+'-content');
				document.getElementById(this.tabId[i]).className ='active';
			} else {
				this.hide(this.tabId[i]+'-content');
				document.getElementById(this.tabId[i]).className ='inactive';
			}
		}
	}
	this.click = function (id) {
		this.getTab(id);
		if (this.waitTime>0) {
			this.stoper();
			this.timer = setTimeout(function() { self.rotate(id,this.rotateTime); }, this.waitTime);
		}
	}
	this.rotate = function (id,rotateTime,waitTime){
		if (rotateTime>0) this.waitTime=rotateTime;
		if (waitTime>0) this.waitTime=waitTime;
		if (id >this.tabCount) id=1;
		this.getTab(id);
		id++;
		this.timer = setTimeout(function() { self.rotate(id,this.rotateTime); }, this.waitTime);
	}
	this.stoper = function () {
		clearTimeout(this.timer);
	}
}

// drop-down menu
// this function is from gotoknow.org http://gotoknow.org//javascripts/application.js
// bug for IE
sfHover = function (id) {
	if (typeof document.attachEvent=='undefined') return;
	// set default id for user-menu
	if (id==null || id==undefined) id='user-menu';

	// add class property sfhover to LI tags of id
	if (document.getElementById(id)) {
		var sfEls = document.getElementById(id).getElementsByTagName("LI");
		for (var i=0; i<sfEls.length; i++) {
			sfEls[i].onmouseover=function() { this.className+=" sfhover"; }
			sfEls[i].onmouseout=function() { this.className=this.className.replace(new RegExp(" sfhover\\b"), "");}
		}
	}
}

String.prototype.getFuncBody = function(){
	var str=this.toString();
	str=str.replace(/[^{]+{/,"");
	str=str.substring(0,str.length-1);
	str = str.replace(/\n/gi,"");
	if(!str.match(/\(.*\)/gi))str += ")";
	return str;
}
String.prototype.left = function(n) { return this.substring(0, n); }
// String.prototype.trim = function() { return this.replace(/^s+|s+$/, ""); } // Remove because jquery  1.8.1 have it
String.prototype.empty = function() { var str=this.toString().trim(); return str=="" || str=="0"; }

/* init bb click */
if (typeof document.attachEvent!='undefined') {
	//	window.attachEvent("onload", ajax.linkRelationInit);
	document.attachEvent('onclick',editor.click);
} else {
	//	window.addEventListener('load',ajax.linkRelationInit,false);
	document.addEventListener('click',editor.click,false);
}

document.write( "<script type='text/javascript' src='/library/boxover.js'><\/script>" );

$(document).on('submit','form.signform, .member-zone', function(e) {
	var $this=$(this);
	//	alert($this.attr('id')+' u='+$this.find("#edit-username").val()+' p='+$this.find("#edit-password").val())
	if ($this.find("#edit-username").val()=="") {
		notify("กรุณาป้อน Username");
		$this.find("#edit-username").focus();
	} else if ($this.find("#edit-password").val()=="") {
		notify("กรุณาป้อน Password");
		$this.find("#edit-password").focus();
	} else {
		notify("กำลังเข้าสู่ระบบ");
		$.post($this.attr("action"),$this.serialize(),function(html) {
			var error=html
			if (html.search('signin-error')==-1) {
				window.location=document.URL;
			} else {
				var matches = [];
				html.replace(/<div class="signin-error hidden">(.*?)<\/div>/g, function () {
					matches.push(arguments[1]);
				});
				notify(matches)
			}
		});
	}
	return false;
});

var overCount=0
$(document).ready(function() {
	$('body').prepend('<div id="notify"></div><div id="tooltip"></div><div id="popup"></div><div id="dialog"></div><div id="debug"></div>');
	$("#notify").hide();
	sfHover();
	//	ajax.linkRelationInit();
	$('a[href$=".pdf"], a[href*="files/"]').addClass('pdflink');



	$(document).on({
		mouseenter: function(e) {
			var url=$(this).attr("tooltip-uri");
			timer = setTimeout(function() {
				overCount++
				notify('กำลังโหลด ('+overCount+')',5000)
				$.get(url, function(html) {
					var x = e.pageX - $(window).scrollLeft()+0;
					var y = e.pageY - $(window).scrollTop()+10;
					// html="X="+x+" Y="+y+"<br />"+"scrollTop="+$(window).scrollTop()+"<br />"+html;
					$("#popup").html(html).dialog({
						position: [x, y],
						modal: false,
					});
					$(".ui-dialog-titlebar").hide();
					/*$("body").append("<p id='tooltips'>"+html+"</p>");
					$("#tooltips")
					.css({"position":"absolute", border:"1px solid #333", background: "#f7f5d1", padding: "2px 5px", color: "#333", display: "none"})
					.css("top",(e.pageY - 10) + "px")
					.css("left",(e.pageX + 20) + "px")
					.fadeIn("fast"); */
				});
			}, 500); //set to one second now
		},
		mouseleave: function() {
			clearTimeout(timer);
			$("#popup").dialog("close");
		},
		click: function() {
			clearTimeout(timer);
			$("#popup").dialog("close");
		},
	},"[tooltip-uri]");

	$('[rel="load"]').each(function(index) {
		var $this=$(this);
		var uri=$this.attr('rel-uri')?$this.attr('rel-uri'):$this.attr('load-uri');
		if (uri) {
			if (uri.left(1)!='/') uri=url+uri;
			$.get(uri,function(data) {$this.html(data);});
		}
	})

	$('[data-load]').each(function(index) {
		var $this=$(this);
		var uri=$this.attr('data-load');
		if (uri) {
			if (uri.left(1)!='/') uri=url+uri;
			$.get(uri,function(html) {$this.html(html);});
		}
	})

	$(document).on('mousemove','[data-tooltip]', function(e) {
		var moveLeft = 0;
		var moveDown = 0;
		var target = '#tooltip';
		leftD = e.pageX+20// + parseInt(moveLeft);
		maxRight = leftD + $(target).outerWidth();
		windowLeft = $(window).width() - 40;
		windowRight = 0;
		maxLeft = e.pageX - (parseInt(moveLeft) + $(target).outerWidth() + 20);
		var text=$(this).attr("data-tooltip")
		$(target).html(text).show();

		if(maxRight > windowLeft && maxLeft > windowRight) {
			leftD = maxLeft;
		}
		topD = e.pageY +10;//parseInt(moveDown);
		maxBottom = parseInt(e.pageY + parseInt(moveDown) + 20);
		windowBottom = parseInt(parseInt($(document).scrollTop()) + parseInt($(window).height()));
		maxTop = topD;
		windowTop = parseInt($(document).scrollTop());
		if(maxBottom > windowBottom) {
			topD = windowBottom - $(target).outerHeight() - 20;
		} else if(maxTop < windowTop){
			topD = windowTop + 20;
		}
		$(target).css('top', topD).css('left', leftD);
	})

	$(document).on('mouseleave','[data-tooltip]', function(e) {
		var target = '#tooltip';
		$(target).hide();
	})

});

// Set body event
$(document).on('click','[rel]', function(event) {
	// Close dialog box on click
	if ($("#popup").dialog("option","modal")==false) $("#popup").dialog("close");

	// check click rel=toggle , theater , async , dialog , async-post
	var $target=$(event.target);
	var $linkTarget=$(event.target).closest("a, area");
	var para={};

	var confirmMsg=$linkTarget.data('confirm')?$linkTarget.data('confirm'):($linkTarget.attr("confirm")?$linkTarget.attr("confirm"):null)
	if (confirmMsg) {
		if (confirm(confirmMsg)) {
			para.confirm="yes";
		} else {
			event.stopPropagation();
			return false;
		}
	}
	// notify("Click id="+event.target.id+" tagname="+$target[0].tagName+" rel="+$target.attr("rel")+" rel-target="+$target.attr("rel-target")+"<br />linkTarget rel="+$linkTarget.attr("rel")+" ,linkTarget href="+$linkTarget.attr("href"));

	if ($linkTarget.attr("rel")) {
		var relTarget=$linkTarget.attr("rel-target")?$linkTarget.attr("rel-target"):defaultRelTarget;
		var dataType=$linkTarget.attr("data-type");

		// Load data from link url and show
		notify("กำลังโหลด...");
		$("#popup").html("");
		$.get($linkTarget.attr("href"), para, function(data) {
			notify();
			if (isRunOnHost && typeof(_gaq)!='undefined') {_gaq.push(['_trackPageview', $linkTarget.attr("href")]);}
			html=dataType=="json" ? data.html : data;
			if ($linkTarget.attr("rel")=="popup") {
				$("#popup").html(html).dialog({modal: true,position:"center",title:$linkTarget.attr("rel-title")}).mouseleave(function() {});
				$(".ui-dialog-titlebar").show();

				var width='30%';
				if ($linkTarget.data("width")!=undefined) width=$linkTarget.data("width");
				else if ($linkTarget.attr("rel-width")=="full") width=$("#content-wrapper").width();
				else width=$linkTarget.attr("rel-width");
				$("#popup").dialog("option","width",width);

				if ($linkTarget.attr("rel-height")) {
					var height=0;
					if ($linkTarget.attr("rel-height")=="full") height=$(window).height();
					else height=$linkTarget.attr("rel-height");
					$("#popup").dialog("option","height",height);
				}
				$("#popup").dialog("option","position","center");

			} else if ($linkTarget.attr("rel")=="click") {
				//						alert(html);
				$(relTarget).show().html(html);
			} else {
				//						location.hash = $linkTarget.attr("href");
				if ($linkTarget.attr("rel").substring(0,1)=="#") {
					$($linkTarget.attr("rel")).html("");
					//						alert(html);
					$($linkTarget.attr("rel")).show().html(html);
				} else {
					$("#"+$linkTarget.attr("rel")).show().html(html);
				}
			}
			var callbackFunction=$linkTarget.data("callback");
			if (callbackFunction && typeof window[callbackFunction] === 'function') {
				 window[callbackFunction]($linkTarget,html);
			}
			//					if ($linkTarget.attr("callback")) {
			//						var callbackFunction=$linkTarget.attr("callback");
			//						var func = (pastComplete)
			//						func(html);
			//						notify("func="+func+" callback="+callbackFunction);
			//					}
		},dataType);
		event.stopPropagation();
		return false;
	}
});

$(document).on('click','.sg-action', function(e) {
	var $this=$(this)
	var url=$this.attr('href')
	var rel=$this.data('rel')
	var ret=$this.data('ret')
	var para={}

	if (url=='javascript:void(0)') url=$this.data('url');
	if ($this.data('confirm')!=undefined) {
		if (confirm($this.data('confirm'))) {
			para.confirm='yes'
		} else {
			e.stopPropagation()
			return false
		}
	}
	if ($this.data('do')=='closebox') {
		if ($(e.target).closest('.sg-dropbox.box').length!=0) {
			//alert('Close box '+$(e.target).closest('.sg-dropbox.box').attr('class'))
			$('.sg-dropbox.box').children('div').hide()
			$('.sg-dropbox.box.active').removeClass('active')
		} else $.colorbox.close()
		//e.stopPropagation()
		return false
	}
	notify('กำลังโหลด')
	if (rel==undefined && ret==undefined) return true;
	$.get(url,para,function(html) {
		//alert(url)
		notify()
		if (ret) {
			$.get(ret,function(html) {
				if (rel=='box') $.colorbox({html:html,width:$('#colorbox').width()});
				else if (rel=='this') $this.html(html);
				else if (rel=='replace') $this.replaceWith(html);
				else if (rel=='notify') notify(html,20000);
				else $('#'+rel).html(html);
			})
		} else {
			if (rel=='box') $.colorbox({html:html,width:$('#colorbox').width()});
			else if (rel=='this') $this.html(html);
			else if (rel=='replace') $this.replaceWith(html);
			else if (rel=='notify') notify(html,20000);
			else $('#'+rel).html(html);
		}
		if ($this.data('removeparent')) $this.closest($this.data('removeparent')).remove()
		if ($this.data('closebox')) {
			if ($(e.target).closest('.sg-dropbox.box').length===0) {
				$('.sg-dropbox.box').children('div').hide()
				$('.sg-dropbox.box.active').removeClass('active')
			} else $.colorbox.close()
		}

		// Process callback function
		var callbackFunction=$this.data("callback");
		if (callbackFunction && typeof window[callbackFunction] === 'function') {
			window[callbackFunction]($this,html);
		}
	})
	return false;
});

$(document).on('click', '.sg-tabs ul.tabs>li>a', function(e) {
	var $this=$(this)
	var $parent=$this.closest('.sg-tabs')
	$this.closest('ul').children('li').removeClass('active')
	$this.closest('li').addClass('active')
	$parent.children('div').hide()
	$parent.children($this.attr('href')).show()
	return false
});

$(document).on('submit', 'form.sg-form', function(e) {
	var $this=$(this)
	if ($this.data('rel')) {
		notify('กำลังโหลด')
		$.get($this.attr('action'),$this.serialize(), function(data) {
			notify()
			$('#'+$this.data('rel')).html(data)
		})

		return false
	}
});

/*
$(document).on('click','.sg-box', function() {
	var $this=$(this)
	var group=$this.data("group");
	$('.sg-box[data-group="'+group+'"]').each(function(i){
		var $elem=$(this);
		$elem.colorbox({rel:group});
	});
	$this.colorbox({open:true, rel:group});
	return false
});
*/

$(document).on('click','.sg-box', function() {
	var defaults={
		fixed: true,
		opacity: 0.5,
		width: "90%",
		maxHeight: "90%",
		maxWidth: "90%",
	}
	var $this=$(this)
	var group=$this.data("group");
	var options = $.extend(defaults, $this.data());
	if (options.group) options.rel=options.group

	if ($this.data('confirm')!=undefined && !confirm($this.data('confirm'))) {
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

	return false
});

$(document).on('focus', '.sg-datepicker', function(e) {
	$(this)
	.datepicker({
		clickInput:true,
		dateFormat: "dd/mm/yy",
		altFormat: "yy-mm-dd",
		altField: $(this).data("altfield"),
		disabled: false,
		monthNames: thaiMonthName,
	})
});

$(document).on('focus', '.sg-autocomplete', function(e) {
	var $this=$(this)
	var minLength=1
	if ($this.data('minlength')) minLength=$this.data('minlength')
	$this
	.autocomplete({
		source: function(request, response){
			var para={}
			para.n=10
			para.q=request.term
			$.get($this.data('query'),para, function(data){
				response(data)
			}, "json");
		},
		minLength: minLength,
		dataType: "json",
		cache: false,
		focus: function(event, ui) {
			return false
			this.value = ui.item.label
			event.preventDefault()
		},
		select: function(event, ui) {
			this.value = ui.item.label
			// Do something with id
			if ($this.data('altfld')) $("#"+$this.data('altfld')).val(ui.item.value)
			var callback=$this.data('callback')
			if (callback && callback=='submit') $this.closest('form').submit()
			else if (callback && typeof window[callback] === 'function') {
				 window[callback]($this,ui)
			}
			return false
		}
	})
	.autocomplete( "instance" )._renderItem = function( ul, item ) {
		if (item.value=='...') {
			return $('<li class="ui-state-disabled more"></li>')
			.append(item.label)
			.appendTo( ul )
		} else {
			return $( "<li></li>" )
			.append( "<a>" + item.label + "<p>" + item.desc + "</p></a>" )
			.appendTo( ul )
		}
	}
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
			debug: false,
		};
		var options = $.extend(defaults, options);
		this.each(function() {
			var obj = $(this);
			var $slideMain = obj.children().first();
			var $slideTag = $slideMain.children().first();
			options.slideTag=$slideTag.prop('tagName');
			var s = $(options.slideTag, $slideMain).length;
			var w = $(obj).width();
			var h = $(obj).height();

			if (options.debug) notify('Slide Main='+$slideMain.prop('tagName')+' Slide Tag='+options.slideTag+' on '+s+' slide');

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
				var ot = t;
				switch(dir){
					case "next":	t = (ot>=ts) ? (options.continuous ? 0 : ts) : t+1; break;
					case "prev":	t = (t<=0) ? (options.continuous ? ts : 0) : t-1; break;
					case "first":	t = 0; break;
					case "last":	t = ts; break;
					default:			break;
				};
				var diff = Math.abs(ot-t);
				var speed = diff*options.speed;
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
			if(!options.continuous && options.controlsFade){
				$("a","#"+options.prevId).hide();
				$("a","#"+options.firstId).hide();
			};
		});
	};
})(jQuery);

(function($){

	/**
	 * A quick plugin which implements phpjs.org's number_format as a jQuery
	 * plugin which formats the number, and inserts to the DOM.
	 *
	 * By Sam Sehnert, teamdf.com — http://www.teamdf.com/web/jquery-number-format/178/
	 */
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

+function ($) { "use strict";

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

