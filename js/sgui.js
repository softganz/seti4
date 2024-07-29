/**
* sgui    :: Javascript Library For SoftGanz
* Created :: 2021-12-24
* Modify  :: 2024-07-25
* Version :: 21
*/

'use strict'

let sgUiVersion = '4.00.12'
let debugSG = false
let defaultRelTarget = "#main"
let sgBoxPageCount = 0
let popStateCallback = true
let cameraPermission = false

// For Mobile Web App Communication
let isAndroidWebViewReady = typeof Android == 'object'
let isFlutterInAppWebViewReady = false

// Import old library that will deprecated
import('./deprecated/sgui.inlineedit.v1.js')

console.log('SG-UI Version ' + sgUiVersion + ' loaded')

window.addEventListener(
	"flutterInAppWebViewPlatformReady",
	function(event) {
		isFlutterInAppWebViewReady = true
		let status = window
			.flutter_inappwebview
			.callHandler("getCameraPermission")
			.then(function(result) {
				cameraPermission = result
				// console.log('<==== JavaScript: Result from getCameraPermission', cameraPermission)
			});
	}
);

window.addEventListener('popstate', function (event) {
	sgPopState(event)
});

// Add click event to input type="file"
// for Flutter inapp_webview to check camera permission
$(document).on('click', 'input[type="file"]', function() {
	console.log('<==== INPUT TYPE FILE CLICK')
	console.log('JavaScript: Camera permission is ', cameraPermission)
	if (isFlutterInAppWebViewReady) {
		if (cameraPermission == 'PermissionStatus.denied') {
			// console.log("JavaScript: Request permission")
			// let status = window.flutter_inappwebview.callHandler("getCameraPermission", {key:"key1", value: "Tet"})
			// console.log('Return Status is ', JSON.stringify(status))

			// return requestCameraPermission()
			let result = window.flutter_inappwebview.callHandler("requestCameraPermission").then(function(permissionResult) {
				cameraPermission = permissionResult
				// console.log('JavaScript: RESULT', cameraPermission)
			});
			// console.log('Result is ', result)
			// console.log(result.toString())
			// console.log('JavaScript: After call Camera permission result is ',JSON.stringify(result))
			return false
		}
	}
});

async function requestCameraPermission() {
	let result = await window.flutter_inappwebview.callHandler("requestCameraPermission")
	console.log('CAMERA PERMISSION (after) is ', cameraPermission)
	// console.log('Result is ', result)
	// console.log(result.toString())
	// console.log('JavaScript: call CAMERA permission result is ',JSON.stringify(result))
	if (cameraPermission) {
		return true
	} else {
		return false
	}
}


/*
* sgFindTargetElement :: Find target element
* @param String target
* @param jQuery Object $this
* @return jQuery element
*/
function sgFindTargetElement(target, $this) {
	let $targetElement
	if (target == 'this') $targetElement = $this
	else if (target == 'parent') $targetElement = $this.parent()
	else if (target.match(/^parent /i)) $targetElement = $this.closest(target.substring(7))
	else if (target == 'before') $targetElement = $this.before()
	else if (target == 'after') $targetElement = $this.after()
	else if (target == 'prev') $targetElement = $this.prev()
	else if (target == 'next') $targetElement = $this.next()
	else if (target == 'box') $targetElement = $('#cboxLoadedContent>.box-page').last()
	else $targetElement = $(target)
	return $targetElement
}


/*
* sgShowBox :: SoftGanz Show Box
* @param String html
* @param jQuery Object $this
* @param Object options
*/
function sgShowBox(html, $this, options, e) {
	let defaults = {
		fixed: true,
		opacity: 0.5,
		width: "95%",
		maxHeight: "95%",
		maxWidth: "95%",
		className: 'colorbox' + ($this && $this.data('width') == 'full' ? ' -full' : ''),
		//iframe: false,
		onComplete: function() {}
	}

	let $boxElement = $('#cboxLoadedContent')
	let linkUrl
	let thisIsJ = false
	let currentX = window.scrollX
	let currentY = window.scrollY

	options = $.extend(defaults, options)
	if ($this instanceof jQuery) {
		thisIsJ = true
		linkUrl = $this.attr('href') ? $this.attr('href') : $this.attr('action')
		if ($this.data('className')) $this.data('className', options.className+' '+$this.data('className'))
		options = $.extend(options, $this.data(), $this.data('box'));
	}
	if ("boxwidth" in options) options.width = options.boxwidth
	if ("boxheight" in options) options.height = options.boxheight

	// Clear all box content
	if (options.clearBoxContent) {
		sgBoxPageCount = 0
		$boxElement.empty()
	}

	options.onClosed = function() {
		window.onscroll=function(){}
		// console.log('ON BOX CLOSE')
		sgBoxBack({close: true})
	}

	// lock scroll position, but retain settings for later
	window.onscroll = function(){window.scrollTo(currentX, currentY);};

	if (thisIsJ && $this.data('rel') === 'img') {
		sgBoxPageCount = 0
		let group = $this.data("group")
		options.open = true
		options.className = options.className+' -photo -full'

		$('.sg-action[data-group="'+group+'"]').each(function(i){
			let $elem = $(this)
			$elem.colorbox(options)
		})
		$this.colorbox(options)
		e.stopPropagation()
	} else if ($boxElement.length) {
		if (debugSG) console.log('Show Link In Current Box')
		if (debugSG) console.log('Link Url =',linkUrl)
		$boxElement.find('.box-page').hide()
		sgBoxPageCount++
		let pageHtml = '<div class="box-page" data-page="'+sgBoxPageCount+'" data-url="'+linkUrl+'">'+html+'</div>'
		$boxElement.append(pageHtml)
	}	else {
		sgBoxPageCount++
		options.html = '<div class="box-page" data-page="'+sgBoxPageCount+'" data-url="'+linkUrl+'">'+html+'</div>'

		$.colorbox(options)
	}

	history.pushState(null, document.title, '#box-'+sgBoxPageCount);
	// console.log(history.state, sgBoxPageCount)
	// console.log("pushState from sgShowBox()")
	// history.pushState(null, document.title, location.href);
}

async function sgBoxBack(options = {}) {
	// console.log(options)
	options = $.extend({close: null, historyBack: true}, options)
	let $boxElement = $('#cboxLoadedContent')
	let $boxPage = $('.box-page')

	// console.log('sgBoxBack sgBoxPageCount = ', sgBoxPageCount, ' $boxPage.length = ', $boxPage.length, '$boxElement.length = ', $boxElement.length, 'options = ', options)

	// if ($this.closest('.sg-dropbox.box').length != 0) {
	// 	$('.sg-dropbox.box').children('div').hide()
	// 	$('.sg-dropbox.box.active').removeClass('active')
	// 	return
	// } else
	// if ($boxElement.length == 0) return

	if (options.close) {
		// console.log('sgBoxBack => CLOSE BUTTON CLICK', $boxPage.length)
		if (isFlutterInAppWebViewReady) {
			window.flutter_inappwebview.callHandler("closeWebView");
		} else if (isAndroidWebViewReady) {
			Android.reloadWebView('Yes')
		} else if ($boxElement.length) {
			if (options.historyBack) {
				for (let historyCount = 0; historyCount < sgBoxPageCount; historyCount++) {
					// console.log('historyCount = ', historyCount)
					history.back()
				}
			}
			$.colorbox.close()
		}
		sgBoxPageCount = 0
	} else if (sgBoxPageCount === 1) {
		// console.log('sgBoxBack => CLOSE FOR LAST BOX')
		// history.back()
		$.colorbox.close()
		if (isAndroidWebViewReady) Android.reloadWebView('Yes')
		sgBoxPageCount = 0
		history.back()
	} else if (sgBoxPageCount > 1) {
		// console.log('sgBoxBack => BACK')
		// Remove last box page
		$boxElement.children('.box-page').last().remove()
		// Show last box after remove
		$boxElement.children('.box-page').last().show()
		if (options.historyBack) {
			popStateCallback = false
			await history.back()
			popStateCallback = true
		}
		sgBoxPageCount--
	}

	// Close box
	// let $boxElement = $('#cboxLoadedContent')
	// if ($boxElement.length) {
	// 	$.colorbox.close()
	// } else if (isFlutterInAppWebViewReady) {
	// 	window.flutter_inappwebview.callHandler("closeWebView");
	// 	return
	// } else if (isAndroidWebViewReady) {
	// 	Android.closeWebView()
	// 	return false
	// }
}

function sgPopState(event) {
	// console.log('POP STATE CALLBACK = ',popStateCallback)
	// if (!popStateCallback) return
	// console.log("popState", $(".box-page").length, event)
	// console.log(window.location.href, window.location.hash)
	if (sgBoxPageCount === 1) {
		// console.log("POP STATE => CLOSE")
		// history.back()
		// $.colorbox.close()
		sgBoxBack({close: true, historyBack: false})
	} else if (sgBoxPageCount > 1) {
		// console.log("POP STATE => BACK")
		// console.log("pushState from EventListener()")
		// history.pushState(null, document.title, location.href);
		// history.back()
		sgBoxBack({historyBack: false})
	}
	// history.pushState(null, document.title, location.href);
}

//action->replace:dom:url
//->replace:dom [tag|id|class]
// Using data-done="[action[->doneAction]:target"

/*
* sgUpdateData :: SoftGanz Update data to DOM
* @param String html
* @param String relTarget
* @param jQuery Object $this
* @param Object options
*
* Using data-rel="target[:id|class|<tag>]"
* Using data-rel="[action[->doneAction]:target"
*/
function sgUpdateData(html, relTarget, $this, options = {}) {
	if (relTarget == undefined) return

	let relExplode = relTarget.split(':')
	let relType = relExplode[0]
	let $ele

	if (relExplode.length > 1 )
		relTarget = relExplode[1]

	if (debugSG)console.log('Type = ' + relType + ' Target = ' + relTarget)
	//console.log('$this',$this)

	if (relType == 'none') {
		// Do Nothing
	} else if (relType == 'console') {
		console.log(html)
	} else if (relType == 'notify') {
		notify(relTarget != 'notify' ? relTarget : html, 20000)
	} else if (relType == 'box') {
		sgShowBox(html, $this, {clearBoxContent: relTarget == 'clear'})
		if (isAndroidWebViewReady) Android.reloadWebView('No')
	} else if (relType == 'close') {
		sgBoxBack({close: true})
	} else if (relType == 'reload') {
		window.location=document.URL;
	} else if (relType == 'this') {
		$this.html(html);
	} else if (relType == 'parent') {
		$ele = relTarget == 'parent' ? $this.parent() : $this.closest(relTarget);
		$ele.html(html);
	} else if (relType == 'replace') {
		$ele = relTarget == 'replace' ? $this : ($this.closest(relTarget).length ? $this.closest(relTarget): $(relTarget));
		$ele.replaceWith(html);
	} else if (relType == 'after') {
		$ele = relTarget == 'after' ? $this : $(relTarget);
		$ele.after(html);
	} else if (relType == 'append') {
		$ele = relTarget == 'append' ? $this : $(relTarget);
		$ele.append(html)
	} else if (relType == 'refresh') {
		$ele = relTarget=='refresh' ? $('#main') : $(relTarget);
		let refreshUrl = $ele.data('url') ? $ele.data('url') : document.URL
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
* sgActionDone :: SoftGanz Update data to DOM
* @param String doneData
* @param jQuery Object $this
* @param Object options
*
* Using data-done="action[->doneAction][:target[ targetDom]:url] [| ...]"
* action => notify, javascript, back, close, moveto, remove, reload, load
* doneAction (action = load) => replace, before, after, append, prepend, prev, next
* target => this, #id, .class, tag, parent, before, after, prev, next
* targetDom => #id, .class, tag
* Eg
* data-done="load->replace:#id:/project/view"
* data-done="remove:parent li.ui-action"
*/
async function sgActionDone(doneData, $this, data, options = {}) {
	if (doneData === undefined) return

	doneData = doneData.replace(/\{\{(\w+)\}\}/g, function($1,$2) {return data[$2];})

	doneData.split('|').map(function(doneItem) {
		let doneExplode = doneItem.trim().split(':')
		let doneType = doneExplode[0].split('->')[0]
		let doneAction = doneExplode[0].split('->')[1]
		let doneTarget = doneExplode.length > 1 ? doneExplode[1].trim() : ''

		if (doneTarget == '') doneTarget = '#main';

		// console.log(doneItem, doneExplode)
		// console.log('doneType = ',doneType, 'doneAction = ',doneAction)
		// console.log('doneTarget = ',doneTarget)
		// console.log(data)

		switch (doneType) {

			case 'notify':
				notify(doneExplode[1], 20000)
				break;

			case 'javascript':
				eval(doneTarget)
				break;

			case 'function':
				let fn = '(function '+doneTarget+')($this,data)'
				eval(fn)
				break;

			case 'callback':
				let callback = doneTarget.trim()
				if (doneTarget && typeof window[doneTarget] === 'function') {
					window[doneTarget]($this,data)
				}
				break;

			case 'back':
				// sgPopState()
				sgBoxBack()
				// let $boxElement = $('#cboxLoadedContent')
				// if ($boxElement.length) {
				// 	let $boxPage = $('.box-page')
				// 	if ($boxPage.length <= 1) {
				// 		$.colorbox.close()
				// 		if (isAndroidWebViewReady) Android.reloadWebView('Yes')
				// 	} else {
				// 		// Remove last box page
				// 		$boxElement.children('.box-page').last().remove()
				// 		// Show last box after remove
				// 		$boxElement.children('.box-page').last().show()
				// 	}
				// }
				break;

			case 'close':
				sgBoxBack({close: true})
				// let $boxElement = $('#cboxLoadedContent')
				// if ($boxElement.length) {
				// 	$.colorbox.close()
				// } else if (isFlutterInAppWebViewReady) {
				// 	window.flutter_inappwebview.callHandler("closeWebView");
				// 	return
				// } else if (isAndroidWebViewReady) {
				// 	Android.closeWebView()
				// 	return false
				// }
				break

			case 'moveto':
				if (doneExplode[1].substr(0,1) === '#') {
					if ($(doneExplode[1]).length) {
						// Scroll to element id
						$('html,body').animate({ scrollTop: $(doneExplode[1]).offset().top - $('#header-wrapper').height() - 16 }, 'slow');
					}
				} else {
					let moveto = doneExplode[1].split(',');
					window.scrollTo(parseInt(moveto[0]), parseInt(moveto[1]))
				}
				break

			case 'remove':
				let $ele = sgFindTargetElement(doneTarget, $this)
				$ele.remove()
				break

			case 'reload':
				// console.log('done reload')
				setTimeout(function(){
					let reloadUrl = doneExplode.length > 1 ? doneExplode[1] : document.URL
					reloadUrl = reloadUrl.split('#')[0]
					// console.log('done reload url '+reloadUrl)
					window.location = reloadUrl
				}, 200);
				break

			case 'load':
				setTimeout(function(){
					// console.log('DONE TARGET = ' + doneTarget)
					let $loadTargetElement = sgFindTargetElement(doneTarget, $this)
					let loadUrl = doneExplode.length > 2 ? doneExplode[2] : ($loadTargetElement.data('url') ? $loadTargetElement.data('url') : document.URL)
					if (loadUrl && ($loadTargetElement.length || doneTarget == 'none')) {
						// console.log('DONE TYPE = '+doneType + (doneAction ? '->'+doneAction : '') + ' : URL = ' + loadUrl)
						// loadUrl = loadUrl.replace(/\{\{(\w+)\}\}/g, function($1,$2) {return data[$2];})
						loadUrl = loadUrl.split('#')[0]
						// console.log(loadUrl)

						$.post(loadUrl,function(html){
							switch (doneAction) {
								case 'replace' : $loadTargetElement.replaceWith(html); break;
								case 'before' : $loadTargetElement.before(html); break;
								case 'after' : $loadTargetElement.after(html); break;
								case 'append' : $loadTargetElement.append(html); break;
								case 'prepend': $loadTargetElement.prepend(html); break;
								case 'prev' : $loadTargetElement.prev().html(html); break;
								case 'next' : $loadTargetElement.next().html(html); break;
								case 'clear' :
									if (doneTarget == 'box') {
										//console.log("CLEAR BOX WITH CLEAR");
										sgShowBox(html, $this, {clearBoxContent: true});
									}
									break;
								default: $loadTargetElement.html(html); break;
							}
							if (doneTarget == 'box' && $this.data('boxResize')) {
								$.fn.colorbox.resize({})
							}
						})
						.fail(function() {console.log('Refresh url fail')})
					}
				}, 200);
				break

		}

	})
}


/*
* sgWebViewDomProcess :: SoftGanz Update data to DOM in Mobile Application WebView
* @param String id
*
* Using {processDomOnResume: "#id" | ".class"} in onWebViewComplete
*/
function sgWebViewDomProcess(id) {
	let $this = $(id)
	//console.log("PROCESS DOM ",id,$this.data("webviewResume"))
	sgActionDone($this.data("webviewResume"), $this)
}





/*
* jQuery Extension :: Open Moblie Application Webview
* Created : 2021-08-08
*	written by Panumas Nontapan
*
*	Copyright (c) 2009 Softganz Group (https://softganz.com)
*	Dual licensed under the MIT (MIT-LICENSE.txt)
*	and GPL (GPL-LICENSE.txt) licenses.
*
*	Built for jQuery library (http://jquery.com)
*
*	markup example for $("#action").openWebview(event,{options}).chain();
*
* <a href="link" data-webview="title">text</a>
*/
(function($) { // data-webview
	let version = '0.03'
	let actionComplete = false

	$.fn.openWebview = function(event, options = {}) {
		let $this = $(this)
		let linkData = $this.data()
		let location = $this.attr('href')
		let openType = $this.data('webview')

		self.doAction = function() {
			if (!(isAndroidWebViewReady || isFlutterInAppWebViewReady)) return false

			let webviewData = JSON.stringify(linkData)

			if (openType == 'intent') {
				if (isFlutterInAppWebViewReady) {
					let options = $.extend({"actionBar": false}, $this.data('options'))
					const args = [location, linkData.webviewTitle, options]
					let r = window.flutter_inappwebview.callHandler("openIntent", ...args)
				} else if (isAndroidWebViewReady) {
					Android.openBrowser(location, webviewData)
				}
			} else if (openType == 'browser') {
				if (isFlutterInAppWebViewReady) {
					// const args = [location, linkData.webviewTitle, linkData];
					// let r = window.flutter_inappwebview.callHandler("openBrowser", ...args);
					let options = $.extend({"actionBar": false}, $this.data('options'))
					const args = [location, linkData.webviewTitle, options]
					let r = window.flutter_inappwebview.callHandler("openBrowser", ...args)
				} else if (isAndroidWebViewReady) {
					Android.openBrowser(location, webviewData)
				}
			} else if (openType == 'googlemap') {
				if (isFlutterInAppWebViewReady) {
					const args = [location, linkData];
					let r = window.flutter_inappwebview.callHandler("openGoogleMap", ...args);
				} else if (isAndroidWebViewReady) {
					Android.openGoogleMap(location, webviewData)
				}
			} else if (openType == 'server') {
				if (debugSG) console.log('Change to Server to '+linkData.server)
				if (isFlutterInAppWebViewReady) {
					const args = [linkData.server];
					console.log(linkData.server)
					let r = window.flutter_inappwebview.callHandler("changeServer", linkData.server);
				} else if (isAndroidWebViewReady) {
					Android.changeServer(linkData.server)
				}
			} else if (openType) {
				let pattern = /^((http|https|ftp):\/\/)/
				linkData.webviewTitle = linkData.webview
				webviewData = JSON.stringify(linkData)
				location = pattern.test(location) ? location : document.location.origin + location
				if (isFlutterInAppWebViewReady) {
					let options = $.extend({"actionBar": true}, $this.data('options'))
					const args = [location, linkData.webviewTitle, options]
					let r = window.flutter_inappwebview.callHandler("showWebView", ...args)
				} else if (isAndroidWebViewReady) {
					Android.showWebView(location, webviewData)
				}
			} else {
				return false
			}
		}

		// RETURN function that can call from outside
		$this.actionComplete = self.doAction() === false ? false : true

		$this.version = function() {
			console.log('$.sgAction version is '+version)
			return $this
		}
		return $this
	}

	$(document).on('click', '[data-webview]', function(event) {
		let actionComplete = $(this).openWebview(event,{}).actionComplete
		if (debugSG) console.log('Mobile App WebView '+version+' result is ', actionComplete)
		if (actionComplete) {
			event.stopImmediatePropagation()
			return false
		} else {
			return true
		}
	});
})(jQuery);






/*
* jQuery Extension :: sg-action
* Created : 2019-09-17
*	written by Panumas Nontapan
*
*	Copyright (c) 2009 Softganz Group (https://softganz.com)
*	Dual licensed under the MIT (MIT-LICENSE.txt)
*	and GPL (GPL-LICENSE.txt) licenses.
*
*	Built for jQuery library (http://jquery.com)
*
*	markup example for $("#action").sgAction(event,{options}).chain();
*
* <a class="sg-action" data-rel="target" data-done="action[->targetAction]:target:url | ..."></a>
*/
(function($) {
	let version = '1.01'
	let sgActionType = 'click'
	let actionResult
	let debug

	$.fn.sgAction = function(event, options = {}) {
		let $this = $(this)
		let linkData = $this.data()
		let dataOptions = linkData.options
		let url = $this.attr('href')
		let relTarget = linkData.rel
		let retUrl = linkData.ret
		let para = {}
		let $boxElement = $('#cboxLoadedContent')
		let confirm = linkData.confirm == undefined || linkData.confirmed
		let callback = linkData.callback
		let relAction
		let doneResult

		if (url == 'javascript:void(0)') url = linkData.url

		console.log('$.sgAction version ' + version + ' start')

		if (relTarget) {
			relAction = relTarget.split('->')[1]
			relTarget = relTarget.split('->')[0]
		}

		let defaults = {
			result: 'html',
			container : $(this),
			loadurl: linkData.loadurl,
			silent: false,
			callback : false,
		}

		let settings = $.extend({}, $.fn.sgAction.defaults, defaults, dataOptions, options)
		// console.log(dataOptions)
		// console.log(settings)

		self.doAction = async function() {
			//console.log("Do Action Start Something")
			//console.log('$THIS is ',$this)
			console.log('relTarget = '+relTarget+' Action = '+relAction)

			if (!confirm) {
				return
			} else if (linkData.confirmed) {
				$this.removeData('confirmed')
				para.confirm = 'yes'
			}

			if (relTarget == 'box' && relAction == 'clear') {
				sgBoxPageCount = 0
				$boxElement.empty()
			}

			// Process before action
			if (linkData.before) {
				sgActionDone(linkData.before, $this)
			}

			// Replace data-rel="close" with data-rel="none" data-done="close"
			// Replace data-rel="back" with data-rel="none" data-done="back"
			if (relTarget == 'close') {
				if ($this.closest('.sg-dropbox.box').length != 0) {
					$('.sg-dropbox.box').children('div').hide()
					$('.sg-dropbox.box.active').removeClass('active')
					return
				} else if ($('#cboxLoadedContent').length) {
					sgBoxBack({close: true})
					return
				} else if (isFlutterInAppWebViewReady) {
					window.flutter_inappwebview.callHandler("closeWebView");
					return
				} else if (isAndroidWebViewReady) {
					Android.closeWebView()
					return
				} else {
					// If no active box do after
					relTarget = undefined
				}
			} else if (relTarget == 'back' && $boxElement.length) {
				// console.log('BACK BUTTON CLICK')
				// sgBoxBack()
				history.back()
				// let $boxPage = $('.box-page')
				// if ($boxPage.length <= 1) {
				// 	$.colorbox.close()
				// 	//if (isAndroidWebViewReady) Android.reloadWebView('Yes')
				// 	if (isAndroidWebViewReady) {
				// 		console.log("ANDROID Back");
				// 	}
				// } else {
				// 	// Remove last box page
				// 	$boxElement.children('.box-page').last().remove()
				// 	// Show last box after remove
				// 	$boxElement.children('.box-page').last().show()
				// }
				return
			} else if (relTarget == 'img') {
				sgShowBox(null, $this, null, event)
				return
			}

			if (relTarget == undefined && retUrl == undefined) {
				// No attribute data-rel and data-ret
				// Redirect to href
				let hasPara = JSON.stringify(para) != '{}'
				let hrefUrl = $this.attr('href')
				hrefUrl = hrefUrl + (hasPara ? (hrefUrl.indexOf('?') == -1 ? '?' : '&') + $.param(para) : '')
				window.location = hrefUrl
				return true
			} else if (url && url.substr(0,1) == '#') {
				// href is begin with #
				// Get HTML from #id and send to data-rel
				// console.log('LOAD FROM DOM ' + url)
				let html = null
				if (url != '#' && $(url).length) html = $(url).get(0).innerHTML
				sgUpdateData(html, relTarget, $this)
				sgActionDone(linkData.done, $this, doneResult)
				return
			}


			if (debugSG) console.log("Load from url "+url)
			if (!settings.silent) notify(settings.indicator);

			// Show iframe in box
			if ($this.data('type') == 'iframe') {
				sgShowBox('<iframe src="'+url+'"></iframe>', $this, {clearBoxContent: relTarget == 'clear'})
				notify('')
				return
			}


			// console.log('URL = '+url)
			let urlMatch = url.match(/^(function|javascript)\:(.*)/)
			if (urlMatch) {
				let urlFunction = urlMatch[2]
				// console.log(urlFunction,urlMatch)
				// console.log("1.START EXECUTE FUNCTION")

				if (urlMatch[1] === 'javascript') {
					eval(urlFunction)
				} else {
					let exeFunction = window[urlFunction]
					await exeFunction($this).then(function(){
						// console.log("4.EXECUTE DONE")
						notify()
						sgActionDone(linkData.done, $this, doneResult)
					})
				}
				// console.log("9.END EXECUTE FUNCTION")
				return
			}

			$.post(url, para, function(html) {
				doneResult = html
				notify()
				if (!settings.silent) console.log("Load completed.")

				if (retUrl) {
					if (debugSG) console.log("Return URL "+retUrl)
					$.post(retUrl, function(html) {
						sgUpdateData(html, relTarget, $this)
						notify()
					})
				} else {
					sgUpdateData(html, relTarget, $this)
				}

				// @deprecated => use data-done="remove:parent element"
				// REMOVE element after done
				if (linkData.removeparent) {
					let removeTag = linkData.removeparent
					let $removeElement = removeTag.charAt(0).match(/\.|\#/i) ? $(removeTag) : $this.closest(removeTag)
					$removeElement.remove()
				}

				// Process CALLBACK function
				if (settings.callback) settings.callback($this,html)

				if (callback && typeof window[callback] === 'function') {
					window[callback]($this,html)
				} else if (callback) {
					window.location = callback
				}
			})
			.done(function(response) {
				// console.log('sg-action DONE');
				// console.log(response)
				if (response.responseCode && response.text) notify(response.text, 3000)
				sgActionDone(linkData.done, $this, doneResult)
			})
			.fail(function(response) {
				// // console.log('sg-action FAIL');
				console.log(response)

				// Old way
				// let errorMsg = 'ERROR : '
				// if (response.responseJSON.text) {
				// 	errorMsg += response.responseJSON.text
				// } else {
				// 	errorMsg += response.statusText
				// }
				// errorMsg += ' ('+response.status+')'
				// notify(errorMsg, 3000)

				// New way like form
				let errorMsg = 'ERROR : '
				// console.log(response.responseJSON.text)
				if (response.responseJSON != undefined && response.responseJSON.text) {
					errorMsg += response.responseJSON.text+' ('+response.status+')'
				} else {
					errorMsg += response.statusText+' ('+response.status+')'
				}
				notify(errorMsg)
				if (debugSG) console.log(response)
				return false

			});

			// console.log('sg-action done')
			return
		}


		self.test = function () {console.log('THIS IS A TEST')}

		$this.actionResult = self.doAction() === false ? false : true

		// RETURN function that can call from outside
		$this.version = function() {
			console.log('$.sgAction version is '+version)
			return $this
		}

		return $this

		return {
			ok : $this.actionResult,

			// GET VERSION
			version: function() {
				console.log('$.sgAction version is '+version)
				return $this
			},

			// SAVE DATA IN FORM TO TARGET
			update: function($this, value, callback) {
				//self.something($this, value, callback)
			},

			test: function() {return 'Test'},
		}
	}

	$.fn.sgForm = function() {

		return this
	}

	/* Publicly accessible defaults. */
	$.fn.sgAction.defaults = {
		indicator: 'LOADING',
		tooltip: 'คลิกเพื่อแก้ไข',
		cssclass: 'inlineedit',
		width: 'none',
		height: 'none',
		cancelcssclass: 'btn -link -cancel',
		submitcssclass: 'btn -primary',
		showButtonPanel: true,
		event: 'edit',
		inputcssclass: '',
	}

	$(document).on(sgActionType, '.sg-action', function(event) {
		let result = $(this).sgAction(event,{aTestOpt: "This is test option"})
		//console.log('RESULT ', result)
		return !result.actionResult
	});
})(jQuery);


/*
$(document).ready(function(){
	let $sgAction = $("#ticket-2")
	.sgAction(null, {
		aTestOpt: 'Test Option in query',
		callback: function($this, data) {console.log('project-pin CALLBACK PROCESS'+data)}
	})
	//.getVersion()
	//.attr("class")
	console.log('ACTION ', $sgAction);
	console.log('HIDE ',$sgAction.hide())
	console.log('ATTRIBUTE ID ',$sgAction.attr('id','aaa').show())
	console.log('GET VERSION = ',$sgAction.version().show())
});
*/





/*
* jQuery Extension :: sg-form :: Softganz form
* written by Panumas Nontapan
* https://softganz.com
* Using <form class="sg-form"></form>
*/
$(document).on('submit', 'form.sg-form', function(event) {
	let $this = $(this)
	let relTarget = $this.data('rel')
	let retUrl = $this.data('ret')
	let onComplete = $this.data('complete')
	let checkValid = $this.data('checkvalid')
	let onFormSubmit = $this.data('onformsubmit')
	let silent = $this.data('silent')
	let errorField = ''
	let errorMsg = ''
	let doneResult

	console.log('sg-form :: Submit');
	// console.log('rel', relTarget)
	// Check field valid
	if (checkValid) {
		console.log('Form Check input valid start.');
		$this.find('.require, .-require').each(function(i) {
			let $inputTag = $(this);
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
			//console.log($inputTag.attr('name'))
			//console.log($("input[name=\'"+$inputTag.attr('name')+"\']*:checked").val())
			if (errorField) {
				//console.log('Invalid input '+errorField.attr('id'))
				let invalidId = errorField.attr('id')
				//console.log('invalidId = ',invalidId)
				//$('#'+invalidId).focus();
				//console.log($('#'+invalidId).isOnScreen() ? 'VISIBLE' : 'INVISIBLE')
				if (! $('#'+invalidId).isOnScreen()) {
					$('html,body').animate({scrollTop: errorField.offset().top - 100}, 'slow');
				}
				notify(errorMsg);
				return false;
			}
		});
		if (errorField) return false;
	}

	// Process callback function
	// console.log('onFormSubmit',onFormSubmit,window[onFormSubmit])
	if (onFormSubmit && typeof window[onFormSubmit] === 'function') {
		// console.log('onFormSubmit START')
		return new Promise((resolve, reject) => {
			// event.preventDefault()
			// return false
			return window[onFormSubmit](event, this);
		});
	}
	// console.log('FORM CONTINUE')
	// event.preventDefault()
	// return false;


	if (relTarget == undefined) return true;

	if (!silent) notify('PROCESSING');

	if (debugSG) console.log('Send form to ' + $this.attr('action'));
	if (debugSG) console.log('Result to ' + relTarget);

	if ($this.hasClass('-upload')) {
		if (debugSG) console.log('SOFTGANZ UPLAOD FILE')
		$this.ajaxSubmit({
			success: function(html) {
				if (debugSG) console.log('SG-FORM.-UPLOAD ajaxSubmit upload file complete.');
				if (onComplete == 'remove') {
					$this.remove()
				} else if (onComplete == 'close' || onComplete == 'closebox') {
					if ($(event.rel).closest('.sg-dropbox.box').length!=0) {
						$('.sg-dropbox.box').children('div').hide()
						$('.sg-dropbox.box.active').removeClass('active')
						//alert($(event.rel).closest('.sg-dropbox.box').attr('class'))
					} else {
						sgBoxBack()
					}
				}

				if (retUrl) {
					//console.log("Return URL "+retUrl)
					$.post(retUrl, function(html) {
						sgUpdateData(html, relTarget,$this)
						notify()
						sgActionDone($this.data('done'), $this, html)
					})
				} else {
					sgUpdateData(html, relTarget,$this)
					// console.log(html)
					// console.log($this)
					// sgActionDone($this.data('done'), $this, html)
					// console.log('UPLOAD DONE')
					sgActionDone($this.data('done'), $this, doneResult);
				}
				if (relTarget != 'notify') notify()
				$this.replaceWith($this.clone(true))
			},
			error: function(data) {
				//console.log('ERROR AJAX SUBMIT')
				//console.log(data)
				notify(data.statusText)
				if (debugSG) console.log(data)
				sgUpdateData(data.responseText, relTarget,$this)
			}
		})

	} else {
		// Start post form
		// console.log('FORM DATA ',$this.serialize())
		$.post(
			$this.attr('action'),
			$this.serialize(),
			function(html) {
				// console.log(html)
				doneResult = html
				if (debugSG) console.log('Form submit completed and send output to '+relTarget);
				if (onComplete == 'remove') {
					$this.remove()
				} else if (onComplete == 'close' || onComplete == 'closebox') {
					if ($(event.rel).closest('.sg-dropbox.box').length!=0) {
						$('.sg-dropbox.box').children('div').hide()
						$('.sg-dropbox.box.active').removeClass('active')
						//alert($(event.rel).closest('.sg-dropbox.box').attr('class'))
					} else {
						sgBoxBack({close: true})
					}
				}

				if (retUrl) {
					if (debugSG) console.log("Return URL "+retUrl)
					$.post(retUrl, function(html) {
						sgUpdateData(html, relTarget, $this)
						notify()
					})
				} else {
					sgUpdateData(html, relTarget, $this)

					if ($this.data('moveto')) {
						let moveto = $this.data('moveto').split(',');
						window.scrollTo(parseInt(moveto[0]), parseInt(moveto[1]));
					}
				}

				if (relTarget.substring(0,6) != 'notify') notify()

				// Process callback function
				let callback = $this.data('callback');
				if (callback && typeof window[callback] === 'function') {
					window[callback]($this,html);
				} else if (callback) {
					window.location=callback;
				}
			}, $this.data('dataType') == undefined ? null : $this.data('dataType')
		).fail(function(response) {
			let errorMsg = 'ERROR : '
			if (response.responseJSON.text) {
				errorMsg += response.responseJSON.text+' ('+response.status+')'
			} else {
				errorMsg += response.statusText+' ('+response.status+')'
			}
			notify(errorMsg)
			if (debugSG) console.log(response)
			return false
		}).done(function(response) {
			if (response.responseCode && response.text) notify(response.text, 3000)
			sgActionDone($this.data('done'), $this, doneResult);
		})
	}
	return false
})
.on('keydown', 'form.sg-form input:text', function(event) {
	let $input = $(this).closest('form').find("input:text")
	let inputCount = $input.length
	if(event.keyCode == 13) {
		event.preventDefault()
		// console.log($input.attr('onEnter'))
		if ($input.attr('onEnter') == 'submit') {
			$input.closest('form').submit()
			return false
		}
		let nextIndex = $input.index(this) + 1
		if(nextIndex < inputCount)
			$input[nextIndex].focus()
		return false
	}
});





/*
* jQuery Extension :: sg-inlineedit
* Softganz inline edit field
* Written by Panumas Nontapan
* https://softganz.com
* Using <div class="sg-inline-edit"><span class="inlineedit-field" data-type="text"></span></div>
* DOWNLOAD : https://github.com/NicolasCARPi/jquery_jeditable
*/
(function($) {
	let inlineEditDom;
	let version = '2.01'
	let sgInlineEditAction = 'click'
	let updatePending = 0
	let updateQueue = 0
	let database;
	let ref
	let radioClickCount = 0
	let settings = {}


	let count = 0 // @deprecated
	let editActive = false // @deprecated

	let inputType = ''

	$.fn.sgInlineEdit2 = function(target, options = {}) {
		console.log('$.sgInlineEdit version ' + version + ' start')

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

		editActive = true
		count++
		// console.log("COUNT = ",count)
		// if (updatePending > 0) return

		let $this = $(this)
		let $inlineField = $this.closest('.inlineedit-field')
		let $inlineWidget = $this.closest('.sg-inlineedit')

		inputType = $inlineField.data('type')
		let onSaveFunction = $inlineWidget.attr('onSave')
		let onSaveFieldCallback = $inlineField.data('callback')
		let fieldOptions = $inlineField.data('options') ? $inlineField.data('options') : {}
		let showSubmitButton = (fieldOptions && 'button' in fieldOptions) || $inlineField.data('button') == 'yes'
		let postUrl = $inlineField.data('action') ? $inlineField.data('action') : $inlineField.data('updateUrl')
		let disableInputOnSave = false
		let debug = false

		if (postUrl === undefined) {
			postUrl = $inlineWidget.data('action') ? $inlineWidget.data('action') : $inlineWidget.data('updateUrl')
		}

		if (debugSG) console.log('=== SG-INLINE-EDIT DEBUG for "(' + inputType + ') ' + $inlineField.data("inputName") + '" ===>')

		// console.log('POST URL = ',postUrl)
		// console.log($inlineWidget.data('updateUrl'))
		// console.log('$inlineField', $inlineField)
		// console.log('$this', $this)
		// console.log('$inlineWidget', $inlineWidget)
		// console.log($inlineWidget.data());
		// console.log($this.data())
		// console.log('INPUT TYPE = ', inputType)
		// console.log('FIELD OPTIONS', fieldOptions)


		if (inputType == 'money' || inputType == 'numeric' || inputType == 'text-block') {
			inputType = 'text'
		} else if (inputType == 'radio' || inputType == 'checkbox') {
		} else if (inputType == 'link') {
			return
		} else if (inputType == '' || inputType == undefined) {
			inputType = 'text'
			$inlineField.data('type','text')
		}

		let defaults = {
			type: inputType,
			result: 'json',
			container : $inlineField,
			// Use 'cancel', 'submit', 'ignore' or function. If function returns false, the form is cancelled
			onblur: $inlineField.data('onblur') ? $inlineField.data('onblur') : 'submit', // submit,nothing
			// onblur: function(inputValue, settings, form) {
			// 	console.log("ONBLUR ", inputValue, settings, form)
			// 	let inputBox = form.find(".form-text")[0]
			// 	console.log(inputBox)
			// 	if (!inputBox.checkValidity()) {
			// 		notify('ป้อนข้อมูลไม่ถูกต้องตามรูปแบบที่กำหนด', 2000)
			// 		// _event.returnValue = false;
			// 		// if (_event.preventDefault) _event.preventDefault();
			// 		inputBox.focus()
			// 		return 'ignore'
			// 	}
			// 	console.log($inlineField.data('onblur') ? $inlineField.data('onblur') : 'submit')
			// 	return $inlineField.data('onblur') ? $inlineField.data('onblur') : 'submit'; // submit,nothing
			// },
			onedit: function(settings, self, event) {},
			// called on error
			onerror: function(settings, original, xhr) {},
			// called before reset
			onreset: function(settings, original) {},
			// called before submit
			onsubmit: function(settings, original) {return true},
			data: function(value, settings) {
				if ($inlineField.data('data')) return $inlineField.data('data');
				else if ($inlineField.data('value') != undefined) return $inlineField.data('value');
				else if (value == '...') return '';
				return value;
			},
			loadurl: $inlineField.data('loadurl'),
			before : function() {
				let options = $inlineField.data('options')
				let callbackFunction = options != undefined && options.hasOwnProperty('onBefore') ? options.onBefore : null
				if (callbackFunction && typeof window[callbackFunction] === 'function') {
					window[callbackFunction]($inlineField, $inlineWidget);
				}
			},
			cancel: showSubmitButton ? '<button class="widget-button btn -link -cancel"><i class="icon -material -gray">cancel</i><span>ยกเลิก</span></button>':null,
			submit: showSubmitButton ? '<button class="widget-button btn -primary"><i class="icon -material -white">done_all</i><span>บันทึก</span></button>':null,
			placeholder: $inlineField.data('placeholder') ? $inlineField.data('placeholder') : '...',
			inputcssclass: () => {
				let inputcssclass = ''
				if (inputType == 'numeric') inputcssclass = 'form-text -numeric'
				else if (inputType == 'money') inputcssclass = 'form-text -money'
				else if (inputType == 'email') inputcssclass = 'form-text -email'
				else if (inputType == 'url') inputcssclass = 'form-text -url'
				else if (inputType == 'autocomplete') inputcssclass = 'form-text -autocomplete'
				else inputcssclass = 'form-' + inputType
				return inputcssclass
			}
		}

		// defaults,inputcssclass
		settings = $.extend(
			{},
			$.fn.sgInlineEdit2.defaults,
			defaults,
			options,
			$inlineWidget.data('options'),
			$inlineField.data('options'),
			$this.data('options')
		)

		if (debugSG) console.log('SG-INLINE-EDIT SETTING:',settings)

		// console.log('value of container = ', settings.container.data('value'))
		// console.log('typeof container', typeof settings.container)
		// if (typeof settings.container === 'object') delete settings.container
		// console.log('fieldOptions',fieldOptions)
		//console.log($this.data('options'))

		// TODO: 2 lines below not used
		debug = $inlineWidget.data('debug') ? true : false
		if (fieldOptions && 'debug' in fieldOptions && fieldOptions.debug) debug = true

		debug = settings.debug;

		self.validValue = (value, settings) => {
			let errorMsg = ''

			if (debugSG) console.log('VALID VALUE:', value, settings)

			if (settings.minValue != undefined) {
				// if (settings.container.data('ret') != 'numeric') return true
				value = value.replace(/[^0-9.\-]+|\.(?!\d)/g, '')// = parseFloat(value)
				// console.log('minValue = ',settings.minValue,' value = ',value,' IS ',value < settings.minValue*1)
				if (value*1 < settings.minValue) {
					errorMsg = 'ข้อมูลมีค่าน้อยกว่าช่วงที่กำหนด', 2000
				}
			}

			if (settings.maxValue != undefined) {
				// if (settings.container.data('ret') != 'numeric') return true
				value = value.replace(/[^0-9.\-]+|\.(?!\d)/g, '')// = parseFloat(value)
				// console.log('maxValue = ',settings.maxValue,' value = ',value,' IS ',value > settings.maxValue*1)
				if (value*1 > settings.maxValue) {
					errorMsg = 'ข้อมูลมีค่ามากกว่าช่วงที่กำหนด', 2000
				}
			}

			if (settings.pattern) {
				let inputBox = settings.container.find(".form-text")[0]
				if (!inputBox.checkValidity()) {
					errorMsg = 'ข้อมูลไม่ถูกต้องตามรูปแบบที่กำหนด', 2000
				}
			}

			if (errorMsg) {
				settings.container.addClass('-invalid')
				// settings.container.trigger('click')
				return errorMsg
			}

			settings.container.removeClass('-invalid')
			return true
		}

		self.save = ($inlineField, value, callback) => {
			// console.log('Update Value = '+value)
			// console.log($inlineWidget.data('updateUrl'))
			// console.log('postUrl = ', postUrl)
			// console.log('parent.data', $inlineWidget.data());
			// console.log('this.data', $inlineField.data());

			if (postUrl === undefined) {
				// console.log('ERROR :: POSTURL UNDEFINED')
				notify('ข้อมูลปลายทางสำหรับบันทึกข้อมูลผิดพลาด (ไม่ได้ระบุ)')
				return
			}

			let para = $.extend({},$inlineWidget.data(), $inlineField.data())
			let returnType = para.retType || para.ret // if has retType then use retType, if ret use ret, if both use retType

			delete para['updateUrl']
			delete para['options']
			delete para['choices']
			delete para['data']
			delete para['event.editable']
			delete para['uiAutocomplete']
			delete para['rel']
			delete para['done']

			para.action = 'save';
			para.value = typeof value === 'string' ? value.replace(/\"/g, "\"") : value
			if ($inlineField.data('inputName')) {
				para[$inlineField.data('inputName')] = value
			}
			if (settings.var) para[settings.var] = para.value
			$inlineField.data('value', para.value)

			//if (settings.blank === null && para.value === "") para.value = null
			//console.log(settings.blank)

			// console.log(postUrl)
			if (debugSG) console.log('SENDING PARA:', para)

			updatePending++
			updateQueue++

			notify('กำลังบันทึก กรุณารอสักครู่....' + (debug ? '<br />Updating : pending = '+updatePending+' To = '+postUrl+'<br />' : ''))

			// Lock all inlineedit-field until post complete
			if (disableInputOnSave) $inlineWidget.find('.inlineedit-field').addClass('-disabled')

			//console.log('length='+$('[data-group="'+para.group+'"]').length)
			//console.log(para)

			$.post(postUrl, para, function(data) {
				updatePending--
				$inlineWidget.find('.inlineedit-field').removeClass('-disabled')

				if (typeof data == 'string') {
					let tempData = data
					data = {}
					data.value = para.value
					if (debug) data.msg = tempData
				}

				//if (data == '' || data == '<p>&nbsp;</p>')
				//	data = '...';

				if (debugSG) console.log('RETURN DATA:', data)

				if (returnType == 'refresh') {
					window.location = window.location
				} else if (inputType == 'autocomplete') {
					$inlineField.data('value',para.value)
					$inlineField.find('.-for-input').html(data.value);
				} else if (inputType == 'radio') {
				} else if (inputType == 'checkbox') {
				} else if (inputType == 'select') {
					let selectValue
					if ($inlineField.data('data')) {
						selectValue = $inlineField.data('data')[data.value]
					} else {
						selectValue = data.value
					}
					$inlineField.find('.-for-input').html(selectValue)
				} else {
					// console.log('REPLACE VALUE = ',data.value)
					// console.log($this)
					// $this.html('<span>'+(data.value == null ? '<span class="placeholder -no-print">'+settings.placeholder+'</span>' : data.value)+'</span>')

					$inlineField.find('.-for-input').html(data.value == null ? '<span class="placeholder -no-print">'+settings.placeholder+'</span>' : data.value)
					// $this.html('<span class="-for-input">'+(data.value == null ? '<span class="placeholder -no-print">'+settings.placeholder+'</span>' : data.value)+'</span>')
					// $this.html(data.value == null ? '<span class="placeholder -no-print">'+settings.placeholder+'</span>' : data.value)
				}


				let replaceTrMsg = '';
				//console.log('para.tr='+para.tr+' data.tr='+data.tr)
				if (para.tr != data.tr) {
					if (data.tr == 0)
						data.tr = '';
					//console.log(para.group+' : '+para.tr+' : '+data.tr)
					$('[data-group="'+para.group+'"]').data('tr', data.tr)
					replaceTrMsg = 'Replace tr of group '+para.group+' with '+data.tr
					//console.log(replaceTrMsg);
				}

				notify(
					('error' in data ? data.error : ('msg' in data ? data.msg : 'บันทึกเรียบร้อย'))
					+ (debug ? '<div class="-sg-text-left" style="white-space: normal;">Update queue = '+updateQueue+', Update pending = '+updatePending+'<br /><b>POST PARAMETER:</b><pre>'+JSON.stringify(para, null, "\t")+'</pre><b>RETURN VALUE:</b><pre>'+JSON.stringify(data, null, 2).replace(/\\n/g, "<br>").replace(/\\t/g, "  ").replace(/\\/g, "")+'</pre><br />'+replaceTrMsg+'</div>' : ''),
					debug ? 300000 : 5000
				)
			}, settings.result)
			.fail(function(response) {
				notify('ERROR ON POSTING. Please Contact Admin.');
				// console.log(response)
			})
			.done(function(response) {
				// console.log('response', response)

				// Process widget callback function
				// let widgetCallbackFunction = settings.callback ? settings.callback : $inlineField.data('callback')

				if (debugSG) console.log("CALLBACK ON SAVE COMPLETE -> " + onSaveFunction + (onSaveFunction ? '()' : ''), "response:", response)
				if (onSaveFunction && typeof window[onSaveFunction] === 'function') {
					window[onSaveFunction](settings, $inlineField, response);
					// window[onSaveFunction]($inlineField, response, $inlineWidget);
				}

				// Process callback function on each save field
				let callbackFunction = callback

				if (debugSG) console.log("CALLBACK ON SAVE FIELD COMPLETE -> " + callbackFunction + (callbackFunction ? '()' : ''))
				if (callbackFunction) {
					if (typeof window[callbackFunction] === 'function') {
						window[callbackFunction]($inlineField, response, $inlineWidget);
					} else if (settings.callbackType == 'silent') {
						$.get(callbackFunction, function() {})
					} else {
						window.location = callbackFunction;
					}
				}

				// console.log('settings.done ', settings.done)

				// Process action done
				if (settings.done) {
					if (debugSG) console.log('PROCESSING DONE:', settings.done)
					sgActionDone(settings.done, $inlineField, response);
				}
				editActive = false
				console.log('$.sgInlineEdit DONE!!!')
			});
		}

		self.saveRadio = () => {
			// console.log('$inlineField', $inlineField)

			// let $inputElement = $this.val()
			let value = $this.attr('value')
			// console.log('RADIO VALUE ',value)
			self.save($inlineField, value, onSaveFieldCallback)

			// console.log("SHOW:", fieldOptions)
			if ('showOn' in fieldOptions) {
				let showOnValue = fieldOptions.showOn.value
				let showOnElement = fieldOptions.showOn.element
				let $targetElement
				if (showOnElement === 'nextInput') $targetElement = $inlineField.next('.inlineedit-field')
				else $targetElement = $(showOnElement)

				if ($targetElement) {
					if (value === showOnValue) {
						$targetElement.removeClass('-hidden')
					} else {
						$targetElement.addClass('-hidden')
					}
				}
			}

			// setTimeout(function(){
			// 	let $inputElement = $this.find('input:checked')
			// 	// value = $inputElement.is(':checked') ? $inputElement.attr('value') : ''
			// 	let value = $inputElement.attr('value')
			// 	console.log('RADIO VALUE ',value)
			// 	// console.log('RADIO VALUE 1 ',$inputElement.attr('value'))
			// 	self.save($inlineField, value, onSaveFieldCallback)
			// }, 200)
		}

		self.saveCheckbox = () => {
			if ($inlineField.data('jsonType') === 'array') {
				let $allCheckbox = $this.closest('.inlineedit-field').find('input:checked')
				// console.log('INPUT ',$allCheckbox)
				let checkboxValue = []
				$allCheckbox.each(function(key, value){
					// console.log(key,$(this).attr('value'))
					checkboxValue.push($(this).attr('value'))
				})
				self.save($inlineField, checkboxValue, onSaveFieldCallback)
			} else {
				let value = $this.is(':checked') ? $this.attr('value') : ''
				self.save($inlineField, value, onSaveFieldCallback)
			}
			// console.log('CHECKBOX VALUE ',checkboxValue)
		}

		// checkValidity for 3 cse
		// case ENTER => HTML.checkValidity
		// case TAB => tab valid
		// case Blur => click outside
		self.saveEditable = () => {
			if (debugSG) console.log('ENTER EDITABLE MODE')
			$this.editable(
				function(value, settings) {
					if (debugSG) console.log('SAVE EDITABLE FUNCTION for "' + settings.container.data('inputName') + '"')
					let errorMsg = validValue(value, settings)
					if (true != errorMsg) {
						notify(errorMsg, 2000)
						// $this.trigger('click')
						setTimeout(function() {
							settings.container.find('-for-input').trigger('click')
							// $nextBoxInput.trigger('click')
						}, 100);
						return value
					}

					self.save($inlineField, value, onSaveFieldCallback)
					return value
				} ,
				settings
			).trigger('edit')
		}


		// Save value immediately when radio or checkbox click
		if (inputType == 'radio') saveRadio()
		else if (inputType == 'checkbox') saveCheckbox()
		else saveEditable()

		// RETURN that can call from outside
		return {
			settings: settings,

			// GET VERSION
			getVersion: function() {
				return version
			},

			validValue: function validValue(value, settings) {
				return self.validValue(value, settings)
			},

			onKeyDown: function(settings, dom, self, event) {
				// console.log("_event.keyCode=",_event.keyCode)
				let _event = event || window.event;

				// console.log('ONKEYDOWN: settings:', settings, 'dom: ', dom, 'self: ', self, 'event: ', event)
				// TAB Key
				if(_event.keyCode == 9) {
					let $this = $(self);

					// Check valid
					let inputBox = $this.find('.form-text')
					// console.log('INPUTBOX: ', inputBox.val(), inputBox)
					if (inputBox.length) {
						let errorMsg = dom.validValue(inputBox.val(), settings)
						if (true != errorMsg) {
							notify(errorMsg, 2000)
							_event.returnValue = false;
							if (_event.preventDefault) _event.preventDefault();
							return // value //$inlineField.data('value')
						}
					}

					// Goto next field
					let $allBox = $this.closest('.sg-inlineedit').find('.inlineedit-field:not(.-hidden');
					let $nextBox;
					let currentBoxIndex = $allBox.index(self)
					let $nextBoxInput

					if (currentBoxIndex === $allBox.length - 1) {
						$nextBox = $allBox.eq(0);
					} else {
						$nextBox = $allBox.eq(currentBoxIndex+1);
					}
					$nextBoxInput = $nextBox.find('.-for-input')

					// Blur to save value
					$this.find('input').blur();

					// Delay to focus next box
					setTimeout(function() {
						switch ($nextBox.data('type')) {
							case 'text':
							case 'textarea':
								$nextBoxInput.trigger('click')
								break;
							case 'radio':
							case 'checkbox':
								$nextBoxInput = $nextBox.find('.-for-input:first-child')
								$nextBoxInput.focus()
								break;
						}
					}, 100);
					// console.log('Index = ', currentBoxIndex)
					// console.log('Next Input = ', $nextBox.data('inputName'))
					// console.log('$this', $this)
					// console.log('Length = ',$allBox.length)
					return false;
				}
			},

			// SAVE DATA IN FORM TO TARGET
			update: function($inlineField, value, callback) {
				self.save($inlineField, value, callback)
			}
		}
	}

	/* Publicly accessible defaults. */
	$.fn.sgInlineEdit2.defaults = {
		indicator				: '<div class="loader -rotate"></div>',
		tooltip 				: 'คลิกเพื่อแก้ไข',
		cssclass				: 'inlineedit',
		width						: 'none',
		height 					: 'none',
		var							: null,
		cancelcssclass	: 'btn -link -cancel',
		submitcssclass	: 'btn -primary',
		showButtonPanel	: true,
		indicator 			: 'SAVING',
		event 					: 'edit',
		inputcssclass		: '',
		autocomplete 		: {},
		datepicker 			: {},
	}

	$(document).on(
		sgInlineEditAction,
		'.sg-inlineedit .inlineedit-field:not(.-readonly) .-for-input',
		function() {
			// console.log('updatePending = '+updatePending+' updateQueue = '+updateQueue)
			inlineEditDom = $(this).sgInlineEdit2()
		}
	);

	$(document).on('keydown', ".sg-inlineedit .inlineedit-field", function(event) {
		inlineEditDom.onKeyDown(inlineEditDom.settings, inlineEditDom, this, event)
	});


	// Add editable plugin
	// Move into sgInlineEdit after fixed all other inline-edit to sg-inline-edit
	$(document).ready(function() {

		if (typeof $.fn.editable === 'undefined') return

		// Show/ Hide element
		$('.inlineedit-field[data-options]').each(function(index) {
			// this.processDomOnValue($(this))
			let $this = $(this)
			let options = $this.data('options')

			if ('showOn' in options) {
				let $targetElement;
				let inputName = $this.data('inputName')
				let showOnValue = options.showOn.value
				let showOnElement = options.showOn.element
				let inputValue = $this.find('input[name=' + inputName + ']:checked').val()

				// console.log($(this).data('inputName'),$(this).data('options'),$(this))
				// console.log('showOnElement',$this.data('showOnElement'), inputName, inputValue)

				if (showOnElement === 'nextInput') {
					$targetElement = $this.next('.inlineedit-field')
				} else {
					$targetElement = $(showOnElement)
				}

				// console.log('$targetElement', $targetElement)

				if ($targetElement) {
					let inputName = $this.data('inputName')
					if (inputValue === showOnValue) {
						$targetElement.removeClass('-hidden')
					} else {
						$targetElement.addClass('-hidden')
					}
					// console.log($this.find('input[name='+ inputName +']'))
					// if ($this.find('input[name='+ inputName +']'))
				}
				// console.log('-----')
			}
		});

		$.editable.addInputType('checkbox', {});
		$.editable.addInputType('radio', {});

		//Add input type autocomplete to jEditable
		$.editable.addInputType('autocomplete', {
			element : $.editable.types.text.element,
			plugin : function(settings, original) {
				$(original).attr( 'autocomplete','off' );
				let defaults = {
					target: '',
					source: function(request, response) {
						let queryUrl = settings.autocomplete.query
						let para = {}
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
						let targetValue = settings.autocomplete.target
						// console.log('targetValue',targetValue)
						if (targetValue) {
							if (typeof targetValue == 'string') {
								targetValue = JSON.parse('{"'+targetValue+'": "value"}')
							}
							//console.log("HAVE TARGET", targetValue)
							for (let key in targetValue) {
								//$('#'+x).val(ui.item[selectValue[x]]);
								let dataValue = ui.item[targetValue[key]]
								// console.log('key = ' + key + ' , value = item.ui.'+ dataValue)
								if (key.substring(0,1) == '#' || key.substring(0,1) == '.') {
									$(key).val(ui.item.value)
								} else {
									$(original).data(key, dataValue)
									// console.log('data of key '+ key +' = '+$(original).data(key))
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
							let selectValue=$this.data('select');
							if (typeof selectValue == 'object') {
								console.log(selectValue)
								let x;
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

		$.editable.addInputType('datepicker', {
			element : function(settings, original) {
				let input = $('<input class="form-text -datepicker" />')
				input.attr( 'autocomplete','off' )

				let defaults = {
						format: 'dd/mm/yy',
						monthNames: thaiMonthName,
						beforeShow: function( el ){
							// set the current value before showing the widget
							//$(this).data('previous', $(el).val() );
							console.log('INLINE DATE BEFORE SHOW')
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
				let dateRaw = $('input', this).datepicker('getDate');
				let dateFormatted;
				if (dateRaw === null) {
					dateFormatted = null
				} else if (settings.datepicker.format) {
					dateFormatted = $.datepicker.formatDate(settings.datepicker.format, new Date(dateRaw));
				} else {
					dateFormatted = dateRaw;
				}
				if (dateFormatted) {
					$('input', this).val(dateFormatted);
				} else {
					$('input', this).val('');
				}
				$('input', this).datepicker('hide');
			},

			plugin : function(settings, original) {
				// prevent disappearing of calendar
				//settings.onblur = 'submit';
				settings.onblur = 'nothing'
			}
		})
	});
})(jQuery);







/*
* jQuery Extension :: sg-expand
* Softganz expand DOM below parent
* Written by Panumas Nontapan
* https://softganz.com
* Using <header><h3>Text</h3><a class="sg-expand"><i class="icon -material">expand_less</i></a></header>
*/
(function($) {	// sg-expand
	$(document).on("click",".sg-expand",function() {
		let $this = $(this)
		let $icon = $(this).children()
		if ($this.data('rel')) {
			$($this.data('rel')).toggle()
		} else {
			let $parent = $(this).closest('.widget-listtile')
			$parent.next().toggle()
			// $parent.nextAll().toggle()
		}

		if ($icon.text() == 'expand_less') {
			$icon.text('expand_more')
		} else if ($icon.text() == 'expand_more') {
			$icon.text('chevron_right')
		} else if ($icon.text() == 'chevron_right') {
			$icon.text('expand_more')
		}
	})
})(jQuery);





/*
* jQuery Extension :: sg-drawreport
* Created :: 2020-05-25
* Modify  :: 2024-03-14
* Version :: 2
*	Written :: Panumas Nontapan
*
*	Copyright (c) 2009 Softganz Group (https://softganz.com)
*	Dual licensed under the MIT (MIT-LICENSE.txt)
*	and GPL (GPL-LICENSE.txt) licenses.
*
*	Built for jQuery library (http://jquery.com)
*
*	markup example for $("#.btn.-primary.-submit").sgDrawReport(event,{options}).chain();
*
* <div class="sg-drawreport"></div>
*/
(function($) {	// sg-drawreport
	'use strict';

	let version = '0.11'
	let sgActionType = 'click'
	let toolbarIndex = 0

	$.fn.sgDrawReport = function(event, options = {}) {
		let $this = $(this)
		let $form = $this.closest('form')
		let $container = $this.closest('.sg-drawreport')
		let queryUrl = $container.data('query')
		let debug = false
		let $debugOutput = $("#report-output-debug")

		let callback = $container.data("callback");

		let dataOptions = $container.data('options')


		console.log('$.sgDrawReport version ' + version + ' init')

		let defaults = {
			dataType: 'json',
			container : $(this),
			callback : false,
		}

		let settings = $.extend({}, $.fn.sgDrawReport.defaults, defaults, dataOptions, options)
		//console.log(dataOptions)
		//console.log(settings)

		debug = settings.debug

		self.startDrawReport = function(data) {
			console.log('$.sgDrawReport ' + version + ' start draw report')
			// console.log('data', data)

			// let isDebug = $container.find('input[name="debug"]:checked').length > 0 && data.process != undefined

			if (settings.dataType == 'html') {
				let $detailElement = $($container.data('showHtml'))
				$detailElement.html(data)
				return
			}

			let $debugOutput = $container.find('#report-output-debug').empty()

			if (data.debug && data.process) {
				data.process.forEach(function(item) {
					$debugOutput.show()
					$debugOutput.append($('<div></div>').html(item))
				})
			}

			/*
			if (data.summary.length == 0) {
				notify('ไม่มีข้อมูล',5000)
				return
			}
			*/

			if ($container.data('showChart')) showChart(data)

			if ($container.data('showSummary')) showSummary(data)

			if ($container.data('showItems')) showItems(data)

			if ($container.data('showHtml')) showHtml(data)
		}

		self.showChart = function(data) {
			let $chartElement = $($container.data('showChart'))
			let chartType = $container.find('#graphtype').val()
			let graphData = [['รายการ','จำนวน']]

			// console.log("graphType = ", chartType)
			if (data.summary == undefined || $chartElement.length == 0) return

			data.summary.forEach(function(item, index) {
				graphData.push([item.label, item.project])
			})

			let dataForGraph = google.visualization.arrayToDataTable(graphData)

			let options = {
				title: data.title,
				hAxis: {title: "H Axis", titleTextStyle: {color: "black"}},
				vAxis: {title: "Y Axis", minValue: 0},
				isStacked: false
			}

			let chart

			if (chartType == 'Bar') {
				chart = new google.visualization.BarChart(document.getElementById("report-output-chart"))
			} else if (chartType == 'Col') {
				chart = new google.visualization.ColumnChart(document.getElementById("report-output-chart"))
			} else if (chartType == 'Line') {
				chart = new google.visualization.LineChart(document.getElementById("report-output-chart"))
			} else {
				chart = new google.visualization.PieChart(document.getElementById("report-output-chart"))
			}

			chart.draw(dataForGraph, options);
		}

		self.showSummary = function(data) {
			let $tableElement = $($container.data('showSummary'))
			$tableElement.empty()

			if (data.summary == undefined || $tableElement.length == 0) return

			let table = $('<table></table>').addClass('widget-table')
			let thead = $('<thead></thead>')

			Object.keys(data.summaryFields).forEach( function(key) {
				//console.log(key,data.summaryFields[key])
				thead.append($('<th></th>').text(data.summaryFields[key]))
			});

			table.append(thead)

			data.summary.forEach(function(item, index) {
				let row = $('<tr></tr>').addClass('row')
				Object.keys(data.summaryFields).forEach( function(key) {
					let itemValue = item[key]
					if (typeof itemValue === 'number' && parseInt(itemValue) != itemValue) {
						//console.log('CONVERT ', itemValue)
						itemValue = thousandsSeparators(itemValue)
					}
					row.append($('<td></td>').addClass('col '+(key == 'label' ? '' : '-center')).text(itemValue))
				});

				table.append(row)

			})

			if (data.total) {
				let tfoot = $('<tfoot></tfoot').append($('<tr></tr>'))

				Object.keys(data.summaryFields).forEach( function(key) {
					if (key == 'label') {
						tfoot.append('<td>รวมทั้งสิ้น</td>')
					} else if (data.summaryFields[key] == '%') {
						tfoot.append($('<td></td>').addClass('col -center').text('100%'))
					} else {
						tfoot.append($('<td></td>').addClass('col -center').text(data.total[key]))
					}
				});

				table.append(tfoot)
			}

			$tableElement.append(table)
		}

		self.showItems = function(data) {
			let $detailElement = $($container.data('showItems'))
			let isShowDetail = false

			$detailElement.empty()

			if (data.items == undefined || $detailElement.length == 0) return

			isShowDetail = data.items.length > 0

			if (!isShowDetail) return

			let exportBtn = $('<a/>')
			exportBtn
				.addClass('btn')
				.html('<i class="icon -material">cloud_download</i><span>EXPORT</span>')
				.attr('href', 'javascript:void(0)')
				.attr('onClick', 'export2excel("trans")')
			$detailElement.append($('<nav></nav>').addClass('nav -page -table-export -sg-text-right').append(exportBtn))

			let table = $('<table></table>').addClass('widget-table').attr('id','detail-list')
			let thead = $('<thead></thead>')
				.append($('<tr></tr>'));
			Object.keys(data.itemsFields).forEach( function(key) {
				thead.append($('<th></th>').text(data.itemsFields[key]))
			});
			table.append(thead)
			let tbody = $('<tbody></tbody>')


			data.items.forEach(function(item, index) {
				let row = $('<tr></tr>').addClass('row')
				let detailLink = $('<a/>')
				let linkField

				if (typeof item.config.link === 'object') {
					linkField = item.config.link.field
					detailLink
						.attr('href', item.config.link.href)
						.text(item[item.config.link.field])
						.addClass('sg-action')
						.attr('target', '_blank')
						.data('rel', 'box')
						.data('width', 640)
						.data('webview', item.fullname)
				}

				Object.keys(data.itemsFields).forEach( function(key) {
					row.append($('<td></td>').html(key == linkField ? detailLink : item[key]))
				});

				tbody.append(row)
			})

			table.append(tbody)

			$detailElement.append(table)
		}

		self.showHtml = function(data) {
			let $htmlElement = $($container.data('showHtml'))

			$htmlElement.empty()

			if (data.html == undefined) return
			$htmlElement.html(data.html)
		}

		self.apiParameter = function() {
			let para = {}
			$form.serializeArray().map(function(inputItem) {
				// console.log(inputItem)
				// console.log('inputItem = ', inputItem, 'match = ', inputItem.name.match(/\[\]$/))
				if (inputItem.name.match(/\[\]$/)) {
					if (para[inputItem.name] == undefined) para[inputItem.name] = []
					para[inputItem.name].push(inputItem.value)
				} else {
					para[inputItem.name] = inputItem.value
				}
			})

			return para
		}

		self.makeFilterBtn = function() {
			//console.log('MAKE FILTER BTN')
			let $filterBar = $container.find('#toolbar-report-filter-items')

			$filterBar.empty()
			$('.sg-drawreport .-filter-checkbox:checked').each(function(i) {
				$filterBar
					.append('<span class="" data-src="'+$(this).attr('id')+'">'+$(this).closest('label').text()+'<a class="-filter-remove"><i class="icon -material -sg-16">close</i></a></span>')
			})
		}

		self.startDebug = function(para) {
			$debugOutput.empty().show()
			$debugOutput.append(
				$('<a>')
				.text('Query Link')
				.attr({
					'href': queryUrl + '?' + new URLSearchParams(para),
					'target': '_blank'
				})
			)
			.append('<br>')
			.append(JSON.stringify(para))
			.append('<hr>')

		}

		// RETURN function that can call from outside
		self.doAction = function() {
			let outputOpacity = $(".report-output").css("opacity")

			if ($this.hasClass('-submit-group')) {
				$this.closest('ul').children().removeClass("-active")
				$this.closest('li').addClass("-active")
				$("#reporttype").val($this.attr("href").slice(1))
			} else if ($this.hasClass('-graph')) {
				$("#graphtype").val($this.val())
			}

			$(".report-output").css("opacity", 0.5)

			// Create post parameter
			let para = apiParameter()

			// console.log("API Parameter :: " + $form.serialize());
			// console.log('API Parameter :: ', para)
			// console.log('url = ', queryUrl + '?' + new URLSearchParams(para))
			// console.log($this.data())

			if (debug) startDebug(para)

			$.post(
				queryUrl,
				para,
				function(data) {
					notify()
					//console.log(data)
					//console.log('GET DATA')
				},
				settings.dataType
			).fail(function(data) {
				notify('ERROR ON POSTING')
				$(".report-output").css("opacity", outputOpacity)
				// console.log('DONE WITH data = ',data)
			}).done(function(data) {
				$(".report-output").css("opacity", outputOpacity)
				if (debugSG && data.debug) console.log('DONE WITH data = ',data)
				// if (debugSG) console.log('DONE WITH data = ',data)
				// Process callback function
				//console.log("CALLBACK = ", callback)
				if (callback && typeof window[callback] === 'function') {
					window[callback]($this,data);
				} else {
					startDrawReport(data)
				}
			})

			if (event != undefined) {
				let $eventTarget = $(event.target)
				//console.log($eventTarget)
				//console.log($eventTarget.attr('type'))

				if ($eventTarget.attr('type') != 'checkbox') {
					event.preventDefault()
				}
			}
		}

		$this.doAction = function() {
			doAction()
			return $this
		}

		$this.showChart = function(data) {
			showChart(data)
			return $this
		}

		$this.showSummary = function(data) {
			showSummary(data)
			return $this
		}

		$this.showItems = function(data) {
			showItems(data)
			return $this
		}

		$this.showHtml = function(data) {
			showHtml(data)
			return $this
		}

		$this.version = function() {
			console.log('$.sgDrawReport version is '+version)
			return $this
		}

		$this.makeFilterBtn = function() {
			makeFilterBtn()
			return $this
		}

		$this.apiParameter = function() {return apiParameter()}

		$this.queryUrl = queryUrl

		return $this
	}

	/* Publicly accessible defaults. */
	$.fn.sgDrawReport.defaults = {
		indicator			: 'LOADING',
		debug 				: false
	}

	$(document).on('click', '#toolbar-report-filter-items a.-filter-remove', function() {
		let srcId = $(this).closest('span').data('src')
		$('#'+srcId).prop("checked", false)
		let $srcAmt = $('#'+srcId).closest('li').find('.-amt')
		$srcAmt.html($srcAmt.text() - 1)
		let result = $(this).sgDrawReport(event, {aTestOption: "This is test option"}).doAction()
		$(this).parent().remove()
	});

	$(document).on(sgActionType, '.sg-drawreport .-submit', function(event) {
		let result = $(this).sgDrawReport(event, {aTestOption: "This is test option"}).doAction()
		return result
	});

	$(document).on('change', '.sg-drawreport .-filter-checkbox', function() {
		let checkCount = 0
		$(this).closest('.-checkbox').find('.-filter-checkbox:checked').each(function(i) {
			checkCount++
		})
		$(this).closest('li').find('.-check-count>.-amt').html(checkCount).parent().removeClass('-hidden').addClass(checkCount > 0 ? '' : '-hidden')
		$(this).sgDrawReport().makeFilterBtn()
	});

	$(document).on('click','.sg-drawreport .group-nav', function() {
		let $this = $(this)
		let isLeftNav = $this.hasClass('-left')
		let $groupBar = $this.closest('.-group')
		let containerWidth = $groupBar.width() - 30*2
		let itemWidth = $groupBar.find('ul>li').width()
		let itemInToolbar = Math.floor(containerWidth/itemWidth)
		let maxToolbarIndex = Math.ceil($groupBar.find('ul').width()/containerWidth)
		isLeftNav ? (toolbarIndex > 0 ? toolbarIndex-- : 0) : (toolbarIndex < maxToolbarIndex-1 ? toolbarIndex++ : maxToolbarIndex-1)
		let scrollWidth = toolbarIndex*itemWidth*itemInToolbar + (itemInToolbar*toolbarIndex+1) - 1
		$groupBar.find('ul').animate({"left": -scrollWidth+"px"}, "fast")
		//console.log('toolbarIndex = ',toolbarIndex)
		//console.log('CONTAINER WIDTH ',containerWidth)
		//console.log('itemWidth ',itemWidth)
		//console.log('itemInToolbar ',itemInToolbar)
	});
})(jQuery);





/*
* sg-tabs :: Softganz tabs
* written by Panumas Nontapan
* https://softganz.com
* Using <div class="widget-tabbar sg-tabs"><ul class="tabs">tab click</ul><div>tab container</div></div>
*/
$(document).on('click', '.widget-tabbar>.tabs>li>a, .sg-tabs>.ui-tab>.ui-item>a, .sg-tabs>ul.tabs>li>a, .sg-tabs>ul>li>a', function(e) {
	let $this = $(this)
	let $parent = $this.closest('.sg-tabs')
	let href = $this.attr('href')
	$this.closest('ul').children('li').removeClass('-active')
	$this.closest('li').addClass('-active')

	if ($this.attr('target') != undefined) return true;
	if (href == undefined || href == 'javascript:void(0)') {
		// do nothing
	} else if (href.left(1) == '#') {
		$parent.children('div').hide()
		$parent.children($this.attr('href')).show()
	} else if (!$this.hasClass('sg-action')) {
		notify('LOADING')
		// console.log('LOAD TAB')
		//window.history.pushState({},$this.text(),href)
		//TODO: FIXED bug on class sg-action will double request
		$.post(href,function(html) {
			$parent.children('div').html(html)
			notify()
		})
	}

	// Process CALLBACK function
	let callback = $this.data("callback")
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
* https://softganz.com
* Using sg_dropbox()
*/
$(document).on('click', '.sg-dropbox>a', function() {
	let $parent=$(this).parent()
	let $wrapper=$(this).next()
	let $target=$parent.find('.sg-dropbox--content')

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
	let offset=$(this).offset()
	let width=$wrapper.width()
	let docwidth=$(document).width()
	let right=0
	if (offset.left+width>docwidth) {
		let right=docwidth-offset.left-$(this).width()-8;//offset.left
		$wrapper.css({'rightside':right+"px"})
	}
	//notify("left: " + offset.left + ", top: " + offset.top+", width="+width+", document width="+docwidth+", right="+right)
 	return false
})
.on('click','body', function(e) {
	let $this = $(e.target)
	//console.log($this.closest('.sg-dropbox'))
	if ($this.closest('.sg-dropbox').length == 0) {
		$('.sg-dropbox.click').children('div').hide()
	}
	//notify(e.target.className)
	if ($(e.target).closest('.sg-dropbox.box').length===0) {
		$('.sg-dropbox.box').children('div').hide()
		$('.sg-dropbox.box.active').removeClass('active')
	}
});




$(document).on('focus', '.sg-datepicker', function(e) {
	console.log('SG-DATEPICKER Start')
	$(this).attr('autocomplete','off')
	let defaults = {
		clickInput: true,
		dateFormat: "dd/mm/yy",
		altFormat: "yy-mm-dd",
		altField: "AAAA",
		disabled: false,
		monthNames: thaiMonthName,
		beforeShow: function( e ){
			// console.log('SG-DATEPICKER Befor show')
			//$(e).css('top','200px')
			//console.log('ele.top',$(e).css('top'))
			// set the current value before showing the widget
			//$('.ui-datepicker').css({'position':'relative','z-index':999999,'top':'300px'})
			$(this).data('previous', $(e).val() );
			$(".ui-datepicker:visible").css({top:"+=5"});
		},
		open: function() {
			// console.log('SG-DATEPICKER Open')
			//$(".ui-datepicker:visible").css({top:"+=5"});
		},
		onSelect: function(dateText,inst) {
			if( $(this).data('previous') != dateText ) {
				if ($(this).data('diff')) {
					// Calculate for date diff into other field
					let $toDate = $('#'+$(this).data('diff'));
					// console.log('Calculate date diff to '+$toDate.attr('id'));

					let $fromDate = $(this);
					//let $toDate=$(this).closest('form').find('.sg-checkdateto');
					if ($toDate.val() == '') {
						$toDate.val($fromDate.val());
					} else {
						let diff_date = 0;
						let days = 24*60*60*1000;
						let prevDateText = $(this).data('previous') ? $(this).data('previous') : dateText;
						let prevDateArray = prevDateText.split("/");
						let fromDateArray = $(this).val().split("/");
						let toDateArray = $toDate.val().split("/");

						let prevDate = new Date(prevDateArray[2],prevDateArray[1] - 1,prevDateArray[0]);
						let toDate = new Date(toDateArray[2],toDateArray[1] - 1,toDateArray[0]);

						let fromDate = new Date(fromDateArray[2],fromDateArray[1] - 1,fromDateArray[0]);

						diff_date = Math.round((toDate - prevDate) / days);

						let newToDate = new Date(fromDate);

						newToDate.setDate(fromDate.getDate() + diff_date);

						let dd = newToDate.getDate();
						let mm = newToDate.getMonth() + 1;
						let yy = newToDate.getFullYear();

						let pad = '00'
						// Fill 0 before date with length 2 => (pad + dd).slice(-pad.length)
						let newToDateFormatted = (pad + dd).slice(-pad.length) + '/' + (pad + mm).slice(-pad.length) + '/' + yy;
						// console.log(newToDateFormatted)
						// console.log($toDate.data('maxDate'))
						$toDate.val(newToDateFormatted);
					}
				}
				$(this).trigger('dateupdated');
			}
			// Process call back
			let callback = $(this).data('callback');
			if (callback) {
				if (callback == 'submit') {
					$(this).closest('form').submit()
				} else if (typeof window[callback] === 'function') {
					 window[callback](dateText,$(this));
				} else {
					let url = callback+'/'
					window.location = url;
				}
			}
		},
	}
	let options = $.extend(defaults, $(this).data());

	$(this).datepicker(options)
});





/*
* sg-address :: Softganz address
* written by Panumas Nontapan
* https://softganz.com
* Using <input class="sg-address" type="text" />
*/
$(document).on('focus', '.sg-address', function(e) {
	let $this=$(this)
	$this
	.autocomplete({
		source: function(request, response){
			// console.log('Search address of ' + request.term)
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
			// console.log('Return Address : '+ui.item.value)
			if ($this.data('altfld')) $("#"+$this.data('altfld')).val(ui.item.value);

			// Process call back
			let callback = $this.data('callback');
			if (callback) {
				if (callback == 'submit') {
					//$this.closest('form').triger('submit');
					$(this).closest("form").trigger("submit");
				} else if (typeof window[callback] === 'function') {
					 window[callback]($this, ui);
				} else {
					let url = callback + '/' + ui.item.value
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
* https://softganz.com
* Using <form><input class="sg-autocomplete" type="text" /></form>
* data-aldfld = "id"
*	data-select = string
*	data-select = {"id":"result field key"[, "id":"result field key"]}
*/
$(document).on('focus', '.sg-autocomplete', function(e) {
	let $this = $(this)
	let $form = $this.closest('form')
	let minLength=1
	if ($this.data('minlength')) minLength=$this.data('minlength')
	$this
	.autocomplete({
		minLength: minLength,
		dataType: "json",
		cache: false,
		source: function(request, response){
			let para={}
			para.n=$this.data('item');
			para.q=$this.val();
			//console.log("Query "+$this.data('query'))
			notify("กำลังค้นหา");
			$.get($this.data('query'),para, function(data){
				notify();
				response(data);
				let renderComplete = $this.data('renderComplete')
				if (renderComplete && typeof window[renderComplete] === 'function') {
					return window[renderComplete]($this);
				}
			}, "json");
		},
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
			return false
		},
		select: function(event, ui) {
			// Return in ui.item.value , ui.item.label
			// Do something with id
			// console.log('Select ' + ui.item.value);
			if ($this.data('altfld')) {
				let altElement = "#"+$this.data('altfld')
				if ($form.find(altElement).length) {
					$form.find(altElement).val(ui.item.value)
				} else {
					$(altElement).val(ui.item.value)
				}
			}

			// data-select = string
			// data-select = {"id-1":"result field key 1", "id-2":"result field key 2"}
			if ($this.data('select')!=undefined) {
				let selectValue=$this.data('select');
				// console.log(selectValue)
				if (typeof selectValue == 'object') {
					// console.log("Select Value is Object", selectValue)
					let x;
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
			let callback = $this.data('callback');
			if (callback) {
				if (callback == 'submit') {
					$(this).closest("form").trigger("submit");
				} else if (typeof window[callback] === 'function') {
					 window[callback]($this, ui);
				} else {
					let urlSep = callback.match(/\?/i) ? '' : '/'
					let url = callback + urlSep + ui.item.value
					window.location = url
				}
			}

			return false;
		},
		response: function(event, ui) {
			//console.log('RESPONSE')
			//console.log('EVENT', event)
			let renderStart = $this.data('renderStart')
			if (renderStart && typeof window[renderStart] === 'function') {
				window[renderStart]($this, ui);
			}
		},
	})
	.autocomplete('instance')._renderItem = function( ul, item ) {
		let renderItem = $this.data('renderItem')
		if (renderItem && typeof window[renderItem] === 'function') {
			return window[renderItem]($this, ul, item);
		} else {
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
	}
});




/*
* Softganz inline upload file
* written by Panumas Nontapan
* https://softganz.com
* Using <form class="sg-upload"><input class="inline-uplaod" type="file" /></form>
*/
$(document).on('change', "form.sg-upload .inline-upload", function() {
	let $this = $(this)
	let $form = $this.closest("form")
	let target = $form.data('rel')
	let targetClass = ' class="' + ($form.data('class') != undefined ? $form.data('class') : 'ui-item -item -hover-parent') + '"'

	if (isAndroidWebViewReady) Android.showToast('กำลังอัพโหลดไฟล์')

	console.log('sg-upload :: Inline upload file start and show result in '+target)
	if ($form.data('before')) {
		let tagName = $form.data('before')
		let insertElement = '<'+tagName+targetClass+'><div class="loader -rotate -center"></div></'+tagName+'>'
		let $targetElement = $this.closest(tagName).before(insertElement)
		// console.log($targetElement)
	} else {
		notify('<div class="loader -rotate"></div> กำลังอัพโหลดไฟล์ กรุณารอสักครู่')
	}
	$form.ajaxForm({
		success: function(data) {
			// console.log('Inline upload file complete.', data);
			if (isAndroidWebViewReady) Android.showToast('อัพโหลดไฟล์เรียบร้อบ')
			if (target) {
				if ($form.data('append')) {
					let insertElement = '<'+$form.data('append')+targetClass+'>'+data+'</'+$form.data('append')+'>';
					$(target).append(insertElement);
				} else if ($form.data('prepend')) {
					let insertElement = '<'+$form.data('prepend')+targetClass+'>'+data+'</'+$form.data('prepend')+'>';
					$(target).prepend(insertElement);
					//console.log(insertElement)
				} else if ($form.data('before')) {
					//let tagName = $form.data('before')
					//let insertElement = '<'+tagName+targetClass+'>'+data+'</'+tagName+'>';
					//console.log('Before ',$form.data('before'));
					//console.log('Value ',insertElement)
					//$this.closest($form.data('before')).before(insertElement);
					$targetElement.prev().html(data)
				} else if ($form.data('after')) {
					let insertElement = '<'+$form.data('after')+targetClass+'>'+data+'</'+$form.data('after')+'>';
					//console.log($form.data('after'));
					//console.log(insertElement)
					$this.closest($form.data('after')).after(insertElement);
				} else {
					sgUpdateData(data, target, $this)
					//$(target).html(data);
				}
			}

			notify("ดำเนินการเสร็จแล้ว.",5000)
			$this.val("")
			$this.replaceWith($this.clone(true))
			sgActionDone($form.data('done'), $form, data);

			// if ($form.data('done') == 'close') {
			// 	sgBoxBack({close: true})
			// }
		},
		error: function(xhr, textStatus, errorThrown){
			notify(errorThrown, 5000)
		}
	}).submit()
});





/*
* sg-chart :: Display Google chart
* Written by Panumas Nontapan
* https://softganz.com
* Using <div class="sg-chart" data-chart-type="bar" data-options='{}'><h3>Chart Title</h3><table><tbody><tr><td>..</td><td>..</td></tr>...</tbody></table></div>
*/
function drawChart(chartDom) {
	let $container = $(chartDom)
	let chartId = $container.attr("id")
	let chartTitle = $container.find("h3").text()
	let chartType = $container.data("chartType")
	let $chartTable = $(chartDom).find("table")
	let $chartNav = $(chartDom).find(".widget-nav")
	let chartData = []
	let chartColumn = []
	let options = {}
	let chartDataObj = {}
	let chartContainer = document.getElementById(chartId)
	let chartWidget = null

	if (chartType == undefined) chartType = "col"

	console.log('::SG-CHART ' + chartType + ' of ' + chartId)
	// console.log('Chart Title : '+chartTitle+' Chart Type : '+chartType)
	// if ($chartNav.length) console.log($chartNav)

	let defaults = {
		pointSize: 4,
		allowHtml: true,
		pieHole: 0.4,
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
	options = $.extend(defaults, $container.data('options'));
	//console.log(defaults);
	// console.log('data-options', $container.data('options'))

	if ($container.data('value')) {

	} else {
		$.each($chartTable.find('tbody>tr'),function(i,eachRow){
			let $row = $(this)
			// console.log($row.text())
			let rowData = []
			$.each($row.find('td'),function(j,eachCol){
				let $col = $(this)
				let colKey = $col.attr('class').split(':')
				let colValue
				if (i == 0) {
					chartColumn.push([colKey[0],colKey[1],colKey[2]==undefined?'':colKey[2]])
				}
				if (colKey[0]=="string") {
					colValue = $col.text()
				} else {
					colValue = Number($col.text().replace(/[^\d\.]/g,''))
				}
				//console.log($col.attr('class')+$col.text())
				rowData.push(colValue)
				if (chartType == 'guage') {
					chartDataObj[$col.attr('class')] = colValue
				}
			})
			chartData.push(rowData)
			// console.log(rowData)
		})
		// console.log(chartDataObj)
		// console.log('Chart Column : ', chartColumn)
		// console.log('Chart Data', chartData)
	}


	google.charts.load("current", {"packages":["corechart","line", "gauge"]});
	// google.charts.load('current', {'packages':['line', 'corechart']});
	// google.charts.load("current", {"packages":["corechart"]});
	google.charts.setOnLoadCallback(drawChart);

	function drawChart() {
		if (chartTitle) options.title = chartTitle;
		// console.log('Options',options);
		// Add chart column
		let data = null;
		if ($container.data('value')) {
			data = google.visualization.arrayToDataTable($container.data('value'))
		} else {
			if (chartType == "guage") {
				let guageData = []
				guageData.push(['Label', 'Value'])
				// guageData.push(['งบประมาณ', 15080])
				$.each(chartData, function(i) {
					// console.log(chartData[i])
					guageData.push([chartData[i][0],chartData[i][1]])
					if (chartData[i][2]) options.max = chartData[i][2]
						// options.redFrom = 0
						// options.redTo = 10000000
						// options.yellowFrom = 10000001
						// options.yellowTo = 15000000
						// options.greenFrom = 15000001
						// options.greenTo = 20000000
						if ("redFrom" in chartDataObj) options.redFrom = chartDataObj.redFrom
						if ("redTo" in chartDataObj) options.redTo = chartDataObj.redTo
						if ("yellowFrom" in chartDataObj) options.yellowFrom = chartDataObj.yellowFrom
						if ("yellowTo" in chartDataObj) options.yellowTo = chartDataObj.yellowTo
						if ("greenFrom" in chartDataObj) options.greenFrom = chartDataObj.greenFrom
						if ("greenTo" in chartDataObj) options.greenTo = chartDataObj.greenTo
						if ("string:redColor" in chartDataObj) options.redColor = chartDataObj["string:redColor"]
				})
				data = google.visualization.arrayToDataTable(guageData)
				// data.addColumn('งบประมาณ', 15080)
				// console.log(chartColumn[i])
				// 	data.addColumn(chartColumn[i][1],chartColumn[i][2])
			} else {
				data = new google.visualization.DataTable();
				$.each(chartColumn, function(i) {
					if (chartColumn[i][2] == 'role') {
						data.addColumn({type: chartColumn[i][0], role: 'annotation'})
					} else {
						data.addColumn(chartColumn[i][0],chartColumn[i][1])
					}
				})
				data.addRows(chartData);
			}
			// console.log(data)
			// console.log('Options : ',options)
			// Add chart rows

			// let data = google.visualization.arrayToDataTable([
			// 	['Label', 'Value'],
			// 	['งบประมาณ', 15080],
			// 	['รายจ่าย', 12000],
			// ]);
		}

		if (chartType === 'line') {
			chartWidget = new google.charts.Line(chartContainer)
		} else if (chartType === 'bar') {
			chartWidget = new google.visualization.BarChart(chartContainer);
		} else if (chartType === 'col') {
			chartWidget = new google.visualization.ColumnChart(chartContainer);
		} else if (chartType === 'pie') {
			chartWidget = new google.visualization.PieChart(chartContainer);
		} else if (chartType === 'combo') {
			chartWidget = new google.visualization.ComboChart(chartContainer);
		} else if (chartType === 'guage') {
			chartWidget = new google.visualization.Gauge(chartContainer);
		}

		if ($container.data('callback')) {
			let callback = $container.data('callback')
			// google.visualization.events.addListener(chartWidget, "ready", window[callbackFunction](chartWidget));
			if (callback && typeof window[callback] === 'function') {
				let callbackFunction = window[callback]
				google.visualization.events.addListener(chartWidget, "ready", callbackFunction);
			}
			// window[doneTarget]()
		}

		if ($container.data("image")) {
			google.visualization.events.addListener(chartWidget, 'ready', function () {
				let imgUri = chartWidget.getImageURI();
				// do something with the image URI, like:
				document.getElementById($container.data("image")).src = imgUri;
			});
		}
		chartWidget.draw(data, options);

		if ($chartNav.length) {
			let a = document.createElement('span')
			a.innerHTML = $chartNav.prop('outerHTML')
			chartContainer.prepend(a)
			// chartContainer.before($('<div>').html($chartNav.html()))
			// console.log($chartNav.html())
		}
	}
}

$(document).ready(function() {
	$('.sg-chart').each(function(index) {
		drawChart(this)
	});
});








/*
* sgDrawMap :: Display Google Map
* Written by Panumas Nontapan
* https://softganz.com
*/
let sgDrawMap = function(thisMap, options = {}) {
	let defaults = {
		gisDigit: 14,
		zoom: 9,
		center: [],
		drag: "pin",
		dropPin: false,
		dropPinText: null,
		updateUrl: null,
		updatePara: null,
		updateIcon: null,
		mapCanvas: "#map-canvas",
		height: '100%',
		pin: [],
		markers: [],
		address: [],
		done: null,
		debug: false,
		callback : false,
	}

	let settings = $.extend({}, defaults, options)
	if (settings.debug) console.log(settings)

	let currentMarker
	let currentMarkerText
	let $currentMarkerDom = $("#current-location .value")
	let updateUrl = settings.updateUrl
	let is_point = settings.pin.lat ? true : false
	let currentInfoText = ""
	let dragText = updateUrl ? ((settings.drag == "map" ? "เลื่อนแผนที่" : "ลากหมุด") + "เพื่อเปลี่ยนตำแหน่ง") : ''
	let dragNotifyTime = 20000
	let zoomChange = false

	// console.log('settings', settings)

	$(".box-page").css({width: "100%", height: "100%", minWidth: "100%", minHeight: "100%"})
	$(".page.-map").css({height: settings.height, minWidth: "100%", minHeight: settings.height})
	$(settings.mapCanvas).css({width: "100%", height: "100%", minWidth: "100%", minHeight: "100%"})

	if (settings.dropPin) {
		if (is_point) notify(dragText, dragNotifyTime)
		else if (!is_point && settings.updateUrl) notify("คลิกบนแผนที่ตรงตำแหน่งที่ต้องการวางหมุด",20000)
	} else {
		is_point = true
	}

	let $map = new GMaps({
		div: settings.mapCanvas,
		zoom: settings.zoom,
		scrollwheel: true,
		lat: settings.center.lat,
		lng: settings.center.lng,
		//disableDefaultUI: true,
		//scrollwheel: false,
		//disableDoubleClickZoom: false,

		click: function(event) {
			if (!settings.updateUrl) return
			if (is_point) return

			notify(dragText, dragNotifyTime)
			currentMarkerText = event.latLng.lat().toFixed(settings.gisDigit)+','+event.latLng.lng().toFixed(settings.gisDigit)
			// console.log(currentMarkerText)
			currentMarker = createMarker({lat: event.latLng.lat(), lng: event.latLng.lng()})
			is_point = true
			let infoWindow = new google.maps.InfoWindow({content: currentInfoText});
			infoWindow.open($map, currentMarker)
			$currentMarkerDom.text(currentMarkerText)
		}
	});

	/*
		let myMap = document.getElementById(settings.mapCanvas);

		function zoomIn() {
			let lat
			let lng
			console.log('ZOOM Change '+$map.getZoom())
			zoomChange = true
			//$map.zoom = 10
			if (currentMarker) {
				lat = currentMarker.getPosition().lat()
				lng = currentMarker.getPosition().lng()
				console.log(lat+','+lng)
				//$map.setCenter(lat, lng)
				//currentMarker.setPosition($map.getCenter())
				//updateLocationValue($map.getCenter().lat(), $map.getCenter().lng())
			}
			$map.setZoom($map.getZoom() + 1);
			if (currentMarker) {
				$map.setCenter(lat, lng)
				//currentMarker.setPosition($map.getCenter())
				//updateLocationValue($map.getCenter().lat(), $map.getCenter().lng())
			}
		}
		myMap.addEventListener('dblclick', zoomIn, true);
		$map.addListener("x-zoom_changed", () => {
			console.log($map.getCenter().toUrlValue())
			if (currentMarker) {
				let lat = currentMarker.getPosition().lat();
				let lng = currentMarker.getPosition().lng()
				$map.setCenter(lat, lng)
				//currentMarker.setPosition($map.getCenter())
				//updateLocationValue($map.getCenter().lat(), $map.getCenter().lng())
			}
		})
	*/

	if (settings.drag == "map") {
		$map.addListener("center_changed", () => {
			// console.log('CENTER CHANGE '+$map.getCenter().toUrlValue())
			/*
			if (zoomChange) {
				zoomChange = false
				if (currentMarker) {
					$map.setCenter(currentMarker.getPosition().lat(), currentMarker.getPosition().lng())
				}
				return
			}
			*/
			if (currentMarker) {
				currentMarker.setPosition($map.getCenter())
				updateLocationValue($map.getCenter().lat(), $map.getCenter().lng())
			}
		})
	}



	function clearMap() {
		is_point = false
		locationUpdate("")
		currentMarker.setMap(null);
	}

	let locationUpdate = function(latLng) {
		if (updateUrl == undefined) return false

		let para = settings.updatePara == undefined ? {} : settings.updatePara
		para.location = latLng
		if (settings.debug) console.log("SAVE Location "+updateUrl,para)
		$.post(updateUrl, para, function(data) {
			let googleMapUrl = "https://www.google.com/maps/place/"+latLng
			let googleNavUrl = "geo:?q="+latLng

			$("#googlemap").attr("href", googleMapUrl).removeClass("-hidden")
			$("#googlenav").attr("href", googleNavUrl).removeClass("-hidden")

			let mapIcon = latLng != "" ? "where_to_vote" : "room"
			let mapActive = latLng != "" ? "-active" : ""
			if (settings.updateIcon) {
				$(settings.updateIcon)
					//.text(mapIcon)
					.removeClass("-active")
					.addClass(mapActive)
			}
			}).fail(function(response) {
				notify(response.responseJSON.text, 3000)
			})
			.done(function(data) {
				notify(data.text, 3000)
				sgActionDone(settings.done, null, latLng)
			})
	}

	let editLocation = function(latLng) {
		if (updateUrl == undefined) return false
		// console.log("EDIT", latLng)
	}

	function updateLocationValue(lat,lng) {
		let $currentLocation = $("#current-location")
		//console.log(currentMarker.position.toUrlValue())
		$currentLocation.find(".value").text(lat.toFixed(settings.gisDigit)+","+lng.toFixed(settings.gisDigit))
		$currentLocation.find(".show").text(lat.toFixed(4)+","+lng.toFixed(4))
	}

	function createMarker(marker) {
		currentMarkerText = marker.lat.toFixed(settings.gisDigit)+','+marker.lng.toFixed(settings.gisDigit)
		let saveNavText = '<nav class="nav -sg-flex">'
			+ '<a class="sg-action btn -link -cancel" href="#current-location" data-rel="none" data-title="ลบหมุด" data-confirm="ลบหมุด กรุณายืนยัน?" data-done="javascript:'+thisMap+'.clearMap()"><i class="icon -material">cancel</i><span>ลบหมุด</span></a>'
			+ '<a class="btn -primary" onClick=\''+thisMap+'.locationUpdate($("#current-location>.value").text());return false;\'><i class="icon -material">done</i><span>บันทึกตำแหน่ง</span></a>'
			+ '</nav>';
		let pinText = ''
		//console.log(marker.currentLocation)
		if (marker.currentLocation && settings.locationText != undefined) {
			//console.log("SET TO locationText")
			pinText = settings.locationText
		} else if (settings.pin && settings.pin.content != undefined) {
			pinText = settings.pin.content
		}
		currentInfoText = '<div class="map-info">'
			+ pinText
			+ (settings.updateUrl ? saveNavText : '')
			+ '<div id="current-location" class="current-location">พิกัด <span class="value -hidden">'+currentMarkerText+'</span><span class="show">'+marker.lat.toFixed(4)+','+marker.lng.toFixed(4)+'</span><!-- TODO:: <a class="btn -link" style="padding: 2px 4px 2px 8px; margin-left: 8px;" onClick=\''+thisMap+'.editLocation($("#current-location>.value").text());return false;\'><i class="icon -material -sg-16">edit</i><span>แก้ไข</span></a> --></div>'
			+ '</div>'

		/*
			if (settings.dropPin) {
				if (settings.dropPinText) {
					currentInfoText = '<div class="map-info">'
					+ settings.dropPinText
					+ '<div id="current-location" class="current-location">พิกัด <span class="value -hidden">'+currentMarkerText+'</span><span class="show">'+marker.lat.toFixed(4)+','+marker.lng.toFixed(4)+'</span></div>'
					+ '</div>'

				} else {
					currentInfoText = '<div class="map-info"><h2>'+settings.pin.title+'</h2>'
						+ '<nav class="nav -sg-flex">'
						+ '<a class="btn -primary" onClick=\''+thisMap+'.locationUpdate($("#current-location>.value").text());return false;\'><i class="icon -material">done</i><span>บันทึกตำแหน่ง</span></a>'
						+ '<a class="sg-action btn -link -cancel" href="#current-location" data-rel="none" data-title="ลบหมุด" data-confirm="ลบหมุด กรุณายืนยัน?" data-done="javascript:'+thisMap+'.clearMap()"><i class="icon -material">cancel</i><span>ลบหมุด</span></a>'
						+ '</nav>'
						+ '<div id="current-location" class="current-location">พิกัด <span class="value -hidden">'+currentMarkerText+'</span><span class="show">'+marker.lat.toFixed(4)+','+marker.lng.toFixed(4)+'</span></div>'
						+ '<p class="-hidden">ลากหมุดเพื่อเปลี่ยนตำแหน่ง</p>'
						+ '</div>'
				}
			} else {
				currentInfoText = '<div class="map-info"><h2>ตำแหน่งปัจจุบัน</h2>'
					+ '<div id="current-location" class="current-location">พิกัด <span class="value -hidden">'+currentMarkerText+'</span><span class="show">'+marker.lat.toFixed(4)+','+marker.lng.toFixed(4)+'</span></div>'
					+ '</div>'
			}
		*/

		return $map.addMarker({
			lat: marker.lat,
			lng: marker.lng,
			draggable: settings.updateUrl ? (settings.drag == 'map' ? false : true) : false,
			infoWindow: {title: settings.pin.title, content: currentInfoText},
			dragend: function(event) {
				updateLocationValue(event.latLng.lat(), event.latLng.lng())
			},
			// mouseover: function(){
			// 	(this.infoWindow).open(this.map, this);
			// },
			// mouseout: function(){
			// 	this.infoWindow.close();
			// }
		})
	}

	if (settings.pin.lat && settings.pin.lng) {
		currentMarker = createMarker(settings.pin)
		let infoWindow = new google.maps.InfoWindow({content: currentInfoText});
		infoWindow.open($map, currentMarker)
	}

	if (settings.markers) {
		$.each( settings.markers, function(i, item) {
			if (item.lat && item.lng) {
				$map.addMarker({
					lat: item.lat,
					lng: item.lng,
					draggable: item.draggable == undefined ? false : item.draggable,
					icon: item.icon == undefined ? 'https://chart.apis.google.com/chart?chst=d_map_pin_letter&chld=|CCCCCC|FFFFFF' : item.icon,
					infoWindow: {content: (item.content == undefined ? '' : item.content)},
				})
			}
		});
	}

	if (settings.address) {
		$.each( settings.address, function(i, address) {
			// console.log(address)
			GMaps.geocode({
				address: address,
				callback: function(results, status) {
					if (status == "OK") {
						let latlng = results[0].geometry.location;
						if (!is_point && i == 0) $map.setCenter(latlng.lat(), latlng.lng());
						$map.addMarker({
							lat: latlng.lat(),
							lng: latlng.lng(),
							icon: "https://softganz.com/library/img/geo/circle-green.png",
							infoWindow: {content: address}
						});
					}
				}
			})
		})
	}

	$("#getgis").click(function() {
		notify("กำลังหาตำแหน่งปัจจุบัน");
		// Try HTML5 geolocation.
		if (navigator.geolocation) {
			navigator.geolocation.getCurrentPosition(
				// Complete
				function(position) {
					notify()
					$map.setCenter({lat: position.coords.latitude, lng: position.coords.longitude})
					if (currentMarker == undefined) {
						currentMarker = createMarker({lat: position.coords.latitude, lng: position.coords.longitude, currentLocation: true})
						let infoWindow = new google.maps.InfoWindow({content: currentInfoText});
						infoWindow.open($map, currentMarker)
						is_point = true
					}
					currentMarker.setPosition($map.getCenter())
					updateLocationValue(position.coords.latitude, position.coords.longitude)
				},
				// Error
				function(e) {
					notify("Error: The Geolocation service failed.", 5000);
				},
				{ timeout: 7000, enableHighAccuracy: true, maximumAge: 0 }
			);
		} else {
			// Browser doesnt support Geolocation
			notify("Error: Browser doesnt support Geolocation.", 5000);
		}

		return false;
	});

	function getCurrentMarker() {return currentMarkerText}

	return {
		clearMap: clearMap,
		locationUpdate: locationUpdate,
		editLocation: editLocation,
		currentMarker: getCurrentMarker,
		settings: settings,
	}
}



// Fund area change
$(document).on('change','.sg-area-fund',function() {
	let $this = $(this)
	let $form = $this.closest('form');
	let $area = $form.find('.sg-area-fund');
	let $changwat = $form.find('.sg-changwat');
	let $ampur = $form.find('.sg-ampur');
	let $tambon = $form.find('.sg-tambon');
	let $village = $form.find('.sg-village');
	let altField = $this.data('altfld')

	console.log('Get Area of ' + $this.val())
	if ($this.val()=='') {
		$changwat.val("").hide();
	} else {
		$changwat.val("");
	}
	if ($ampur.length) $ampur.val("").hide();
	if ($tambon.length) $tambon.val("").hide();
	if ($village.length) $village.val("").hide()
	if ($changwat.length) $changwat[0].options.length = 1;
	if ($ampur.length) $ampur[0].options.length = 1;
	if ($tambon.length) $tambon[0].options.length = 1;
	if ($village.length) $village[0].options.length = 1;

	if (altField) {
		$form.find(altField).val($this.val())
	}

	$.get(url+'api/changwat',{"areaFund" : $this.val()}, function(data) {
		if (data.length) $changwat.show(); else $changwat.hide()
		for (let i = 0; i < data.length; i++) {
			$changwat.append(
				$("<option></option>")
				.text(data[i].label)
				.val(data[i].changwat)
			);
		};
	},'json')
	if ($this.data('change') == 'submit') $form.submit();
	//$this.closest('form').submit()
});

// Province change
$(document).on('change','.sg-changwat',function() {
	let $this=$(this)
	let $form=$this.closest('form');
	let $changwat=$form.find('.sg-changwat');
	let $ampur=$form.find('.sg-ampur');
	let $tambon=$form.find('.sg-tambon');
	let $village=$form.find('.sg-village');
	let altField = $this.data('altfld')

	console.log('Get Ampur of ' + $this.val())
	if ($this.val()=='') {
		$ampur.val("").hide();
	} else {
		$ampur.val("");
	}
	$tambon.val("").hide();
	if ($village.length) $village.val("").hide()
	if ($ampur.length) $ampur[0].options.length = 1;
	if ($tambon.length) $tambon[0].options.length = 1;
	if ($village.length) $village[0].options.length = 1;

	if (altField) {
		$form.find(altField).val($this.val())
	}

	$.get(url+'api/ampur',{q:$this.val()}, function(data) {
		if (data.length) $ampur.show(); else $ampur.hide()
		for (let i = 0; i < data.length; i++) {
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
	let $this = $(this);
	let $form = $this.closest('form');
	let $changwat = $form.find('.sg-changwat');
	let $ampur = $form.find('.sg-ampur');
	let $tambon = $form.find('.sg-tambon');
	let $village = $form.find('.sg-village');
	let altField = $this.data('altfld')

	// console.log('Get Tambon of ' + $this.val())

	if ($this.val()=='') {
		$tambon.val("").hide();
	} else {
		$tambon.val("");
	}
	$village.val("").hide()
	if ($tambon.length) $tambon[0].options.length = 1;
	if ($village.length) $village[0].options.length = 1;

	if (altField) {
		$form.find(altField).val($changwat.val()+$this.val())
	}

	$.get(url+'api/tambon',{q:$changwat.val()+$ampur.val()}, function(data) {
		if (data.length) $tambon.show(); else $tambon.hide()
		for (let i = 0; i < data.length; i++) {
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
	let $this = $(this)
	let $form = $this.closest('form');
	let $changwat = $form.find('.sg-changwat');
	let $ampur = $form.find('.sg-ampur');
	let $tambon = $form.find('.sg-tambon');
	let $village = $form.find('.sg-village');
	let altField = $this.data('altfld')

	if (altField) {
		// console.log('tambon altfld = ' + altField)
		$form.find(altField).val($changwat.val()+$ampur.val()+$this.val())
	}
	if (!$village.length) return;

	// console.log('Get Village of ' + $this.val())
	if ($this.val()=='') {
		$village.val("").hide();
	} else {
		$village.val("").show()
	}
	if ($village.length) $village[0].options.length = 1;
	$.get(url+'api/village',{q:$changwat.val()+$ampur.val()+$tambon.val()}, function(data) {
		for (let i = 0; i < data.length; i++) {
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
	let $this=$(this)
	let $form=$this.closest('form');
	let $changwat=$form.find('.sg-changwat');
	let $ampur=$form.find('.sg-ampur');
	let $tambon=$form.find('.sg-tambon');
	let $village=$form.find('.sg-village');

	if ($changwat.data('altfld')) $($changwat.data('altfld')).val($changwat.val()+$ampur.val()+$tambon.val()+$this.val());

	if ($this.data('change') == 'submit') $form.submit();
});

// Sort table column
$(document).on('click', '.widget-table>thead>tr>th[class*="-sort-"]', function(event) {
	notify('Sorting!!! Please wait...', 100)
	console.log('TABLE SORT START')

	$(this).closest('thead').find('th').removeClass('-order')
	$(this).addClass('-order')
	let tableId = $(this).closest('table').attr('id')
	// console.log(tableId)
	if (!tableId) return
	let thIndex = $(this).prevAll().length

	// $(this).closest('table').find('th').removeClass('order').removeClass('order-active');
	// $(this).addClass('order');

	// console.log(thIndex)
	// console.log('START')
	// sortTable(tableId, thIndex)
	// .then(function(){console.log("COMPLETE")})
	// console.log('END')
	// notify('')


	// column_sort(tableId, thIndex)
	// function column_sort() {
	//     getCellValue = (tr, idx) => $(tr).find('td').eq( idx ).text();

	//     comparer = (idx, asc) => (a, b) => ((v1, v2) =>
	//         v1 !== '' && v2 !== '' && !isNaN(v1) && !isNaN(v2) ? v1 - v2 : v1.toString().localeCompare(v2)
	//         )(getCellValue(asc ? a : b, idx), getCellValue(asc ? b : a, idx));

	//     table = $(this).closest('table')[0];
	//     tbody = $(table).find('tbody')[0];

	//     elm = $(this)[0];
	//     children = elm.parentNode.children;
	//     Array.from(tbody.querySelectorAll('tr')).sort( comparer(
	//         Array.from(children).indexOf(elm), table.asc = !table.asc))
	//         .forEach(tr => tbody.appendChild(tr) );
	// }

	function sortTable1(table_id, sortColumn){
		let tableData = document.getElementById(table_id).getElementsByTagName('tbody').item(0);
		let rowData = tableData.getElementsByTagName('tr');
		for(let i = 0; i < rowData.length - 1; i++){
			for(let j = 0; j < rowData.length - (i + 1); j++){
				if(Number(rowData.item(j).getElementsByTagName('td').item(sortColumn).innerHTML.replace(/[^0-9\.]+/g, "")) < Number(rowData.item(j+1).getElementsByTagName('td').item(sortColumn).innerHTML.replace(/[^0-9\.]+/g, ""))){
					tableData.insertBefore(rowData.item(j+1),rowData.item(j));
				}
			}
		}
	}

	function sortTable(id, n) {
		console.log('FUNCTION SORT START')
		let table, rows, switching, i, x, y, shouldSwitch, dir, switchcount = 0;
		table = document.getElementById(id);
		switching = true;
		//Set the sorting direction to ascending:
		dir = "asc";
		/*Make a loop that will continue until
		no switching has been done:*/
		while (switching) {
			//start by saying: no switching is done:
			switching = false;
			rows = table.rows;
			/*Loop through all table rows (except the
			first, which contains table headers):*/
			for (i = 1; i < (rows.length - 1); i++) {
				//start by saying there should be no switching:
				shouldSwitch = false;
				/*Get the two elements you want to compare,
				one from current row and one from the next:*/
				x = rows[i].getElementsByTagName("TD")[n];
				y = rows[i + 1].getElementsByTagName("TD")[n];
				/*check if the two rows should switch place,
				based on the direction, asc or desc:*/
				if (dir == "asc") {
					if (Number(x.innerHTML) > Number(y.innerHTML)) {
					// if (x.innerHTML.toLowerCase() > y.innerHTML.toLowerCase()) {
						//if so, mark as a switch and break the loop:
						shouldSwitch= true;
						break;
					}
				} else if (dir == "desc") {
					if (Number(x.innerHTML) < Number(y.innerHTML)) {
					// if (x.innerHTML.toLowerCase() < y.innerHTML.toLowerCase()) {
						//if so, mark as a switch and break the loop:
						shouldSwitch = true;
						break;
					}
				}
				console.log('FORLOOP END', rows.length)
			}
			if (shouldSwitch) {
				/*If a switch has been marked, make the switch
				and mark that a switch has been done:*/
				rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
				switching = true;
				//Each time a switch is done, increase this count by 1:
				switchcount ++;
			} else {
				/*If no switching has been done AND the direction is "asc",
				set the direction to "desc" and run the while loop again.*/
				if (switchcount == 0 && dir == "asc") {
					dir = "desc";
					switching = true;
				}
			}
			console.log('FUNCTION sortTable END')
			// notify('')
		}
		console.log('FUNCTION SORT END')
	}
	console.log('TABLE SORT END')
});