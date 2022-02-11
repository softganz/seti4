<?php
/**
* iMed :: Psychiatry home page
* Created 2021-05-26
* Modify  2021-05-31
*
* @return Widget
*
* @usage imed/psyc
*/

$debug = true;

import('widget:imed.admit.status');

class ImedPsycHome {
	function build() {
		// $psnId = SG\getFirst($psnId, post('pid'));
		// $getSearch = post('pn');

		if (!i()->ok) {
			return new Container([
				'children' => [
					R::View('signform', '{time:-1, showTime: false}'),
					'<style type="text/css">
					.toolbar.-main.-imed h2 {text-align: center;}
					.form.signform .form-item {margin-bottom: 16px; position: relative;}
					.form.signform label {position: absolute; left: 8px; color: #666; font-style: italic; font-size: 0.9em; font-weight: normal;}
					.form.signform .form-text, .form.signform .form-password {padding-top: 16px;}
					.module-imed.-softganz-app .form-item.-edit-cookielength {display: none;}
					.login.-normal h3 {display: none;}
					</style>',
				],
			]);
		}

		$userId = i()->uid;
		$isAdmin = is_admin('imed');

		return new Scaffold([
			'child' => new Container([
				'children' => [
					// Show search box
					'<div id="green-chat-box" class="sg-action chat-box -imed-app-home" href="'.url('imed/psyc/search').'" data-webview="ค้นหา">'
						. '<img src="'.model::user_photo(i()->username).'" width="40" height="40" />&nbsp;'
						. '<a class="sg-action form-text" href="'.url('imed/psyc/search').'" data-webview=\'ค้นหา\' data-options=\'{history: true, actionBar: true, menu: [{id: "person_add", label: "เพิ่มชื่อผู้ป่วย", title: "เพิ่มชื่อผู้ป่วย", call="addPatient"}]}\'><span style="font-size: 0.825em;">ค้น ชื่อ นามสกุล หรือ เลข 13 หลัก ของผู้ป่วย</span><i class="icon -material">search</i></a>'
						. '</div>',

					// Show notify
					R::View('imed.my.notify'),

					// Show my patient card
					'<div class="sg-load" data-url="'.url('imed/my/patient/card', ['ref' => 'psyc']).'" data-replace="true"></div>',

					// Show green/yellow/red of patient
					new ImedAdmitStatusWidget([
						'more' => '<a class="btn -link" href="'.url('imed/psyc/report/psyc/status').'"><span>MORE</span><i class="icon -material">navigate_next</i></a>',
					]),

					// Show visit history
					'<div class="sg-load" data-url="'.url('imed/visits', ['ref' => 'psyc']).'" data-replace="true">'._NL
						. '<div class="loader -rotate" style="width: 64px; height: 64px; margin: 48px auto; display: block;"></div>'
						. '</div><!-- imed-my-note -->',

					$this->_script(),
				], // children
			]),
		]); // Scaffold
	}

	function _script() {
		head('<style type="text/css">
			.toolbar.-main.-imed {border-bottom : 1px #eee solid; overflow: hidden;}
			.toolbar.-main.-imed .form {margin: 0; padding: 0; height: 47px;}
			.module-imed.-app .imed-search-patient .form-item.-edit-pn {padding: 5px 8px;}
			.chat-box .ui-card -patient {margin: 0;}
		</style>');

		$headerScript = '<script type="text/javascript">
		function onWebViewComplete() {
			var options = {title: "iMed@Psyc", actionBar: true, clearCache: true, actionBarColor: "#DE901C", menu: []}
			options.menu.push({id: "cancel", label: "Home", title: "iMed@home", load: "imed/app/select?app=home", options: {actionBar: true}})
			return options
		}

		$(document).ready(function() {
			var lastPos = 0
			var $box = $("#green-chat-box")
			var boxPosition = $("#green-chat-box").position()
			var boxTop = boxPosition.top //$("#banner").height()
			var boxHeight = $box.height()
			var patientTop = $("#green-chat-box").position().top
			//console.log("Box Top = "+boxTop)
			//console.log("Height = "+$("#green-chat-box").height())
			window.onscroll = function() {
				var pos = $(this).scrollTop()
				var offset = $box.offset()
				//console.log(offset)
				//console.log("pos "+pos)
				//console.log("top = "+$("#green-chat-box").position().top)
				//console.log(pos <= lastPos ? "down" : "up")

				if (pos > lastPos) {
					// Scroll Up
					var currentTop = $box.offset().top
					//$("#patient-list").css({marginTop: boxHeight})
					//console.log("currentTop = "+currentTop)
					if (pos>=157) {
						$box.addClass("-fixed").removeClass("-scroll-up")
						$("#patient-list").css({marginTop: "72px"})
					}
					if (currentTop - pos > 0) {
						//$("#green-chat-box").css({top: boxTop - pos})
						//$("#patient-list").css({paddingTop: patientTop - pos})
					} else {
						//$("#green-chat-box").addClass("-fixed").removeClass("-scroll-up")
						//$("#patient-list").css({paddingTop: 0})
					}
				} else {
					// Scroll Down
					if (pos == 0) {
						$box.removeClass("-fixed").removeClass("-scroll-up")
						$("#patient-list").css({marginTop: 0})
					}
				}
				lastPos = pos
			}
		});
		'._NL;

		if (cfg('firebase')) {
			$headerScript .= '$(document).ready(function() {
			if (!firebaseConfig) return

			var database = firebase.database()
			var ref = database.ref(firebaseConfig.visit)
			var drawUrl = "'.url('imed/app/visit/render').'"
			var i = 0
			var getCurrentTimestamp = (function() {
					var OFFSET = 0
					database.ref("/.info/serverTimeOffset").on("value", function(ss) {
						OFFSET = ss.val()||0
					});
					return function() { return Date.now() + OFFSET }
			})();

			var now = getCurrentTimestamp()

			// console.log(now)
			ref
			.orderByChild("time")
			.startAt(now)
			.on("child_added",function(snap){
				$.post(drawUrl + "/" + snap.val().seq, function(html) {
					if (html) {
						let visitBox = $("#imed-my-note")
						visitBox.prepend(html)
					}
				})
				// console.log(++i + " : " + snap.key, snap.val())
			})
			';

			if (cfg('imed.visit.realtime.change.member') == 'all' || (cfg('imed.visit.realtime.change.member') == 'admin' && is_admin('imed'))) {
				$headerScript .= '
			ref
			.on("child_changed",function(snap) {
				$.post(drawUrl + "/" + snap.key, function(html) {
					$("#noteUnit-"+snap.key).replaceWith(html)
				})
				//console.log(++i + " : " + snap.key, snap.val())
			})
				';
			}

			$headerScript .= '
			})';
		}

		$headerScript .= '</script>'._NL;

		head($headerScript);
	}
}
?>