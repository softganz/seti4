<?php
/**
* iMed :: Care home page
* Created 2021-05-26
* Modify  2021-05-31
*
* @return Widget
*
* @usage imed/care
*/

$debug = true;

class ImedCareVisitHome {
	function __construct() {}

	function build() {

		$psnId = SG\getFirst($psnId, post('pid'));
		$getSearch = post('pn');

		$uid = i()->uid;

		$ret = '';

		$lastVersion = '0.20';
		$updatePlayStoreUrl = "https://play.app.goo.gl/?target=browser&link=https://play.google.com/store/apps/details?id=com.softganz.imedhome";
		if ($uid && R()->appAgent->OS == 'Android' && R()->appAgent->ver < $lastVersion) {
		//if (i()->username == 'softganz') {
			//$ret .= R()->appAgent->OS == 'Android' ? 'Yes Android': 'Not Android';
			//$ret .= R()->appAgent->ver == '0.1.12' ? 'Yes 0.1.12': 'Not 0.1.12';
			//$ret .= gettype(R()->appAgent->ver);
			$ret .= '<div class="notify" style="padding: 24px; text-align: center; xbackground-color: #fff;">'
				. '<p>เนื่องจากมีการอัพเดทแอพเป็นรุ่นใหม่ ขอให้ทุกท่านอัพเดทแอพเป็นรุ่นล่าสุดเพื่อให้สามารถใช้งานคุณสมบัติใหม่ๆ ได้</p>'
				. '<a class="sg-action btn -primary" href="'.$updatePlayStoreUrl.'" '.(R()->appAgent->ver >= '0.2' ? 'data-webview="browser"' : '').'>ดำเนินการอัพเดทแอพ</a>'
				. '<p>New version is '.$lastVersion.' current verion '.R()->appAgent->ver.'</p>'
				. '</div>';
		}


		if (!i()->ok) {
			$ret = R::View('signform', '{time:-1, showTime: false}');
			$ret .= '<style type="text/css">
			.toolbar.-main.-imed h2 {text-align: center;}
			.form.signform .form-item {margin-bottom: 16px; position: relative;}
			.form.signform label {position: absolute; left: 8px; color: #666; font-style: italic; font-size: 0.9em; font-weight: normal;}
			.form.signform .form-text, .form.signform .form-password {padding-top: 24px;}
			.module-imed.-softganz-app .form-item.-edit-cookielength {display: none;}
			.login.-normal h3 {display: none;}
			</style>';
			return $ret;
		}

		$isAdmin = user_access('administer imeds');

		// $ret .= '<div id="banner" style="margin: 0; padding: 0;"><img src="//communeinfo.com/upload/banner-imed-02.jpg" width="100%" /></div>';

		// // $ret .= '<div id="patient-list">';

		// $ret .= '<div id="patient-my" class="sg-load" data-url="'.url('imed/psyc/my/patient/card').'" data-replace="true" style="margin: 8px 0 0 0;"></div>';

		// // $ret .= '<div class="ui-card -patient -sg-flex -co-2" style="margin: 0;"></div>';

		// // Show visit history
		// $ret .= '<div id="ximed-my-note" class="sg-load" data-url="'.url('imed/visits', ['ref' => 'psyc']).'" data-replace="true">'._NL
		// 	. '<div class="loader -rotate" style="width: 64px; height: 64px; margin: 48px auto; display: block;"></div>'
		// 	. '</div><!-- imed-my-note -->';

		// // $ret .= '</div><!-- patient-list -->';

		head('<style type="text/css">
			.toolbar.-main.-imed {border-bottom : 1px #eee solid; overflow: hidden;}
			.toolbar.-main.-imed .form {margin: 0; padding: 0; height: 47px;}
			.module-imed.-app .imed-search-patient .form-item.-edit-pn {padding: 5px 8px;}
			.chat-box .ui-card -patient {margin: 0;}
		</style>');

		$headerScript = '<script type="text/javascript">
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

		function onWebViewComplete() {
			console.log("CALL onWebViewComplete FROM MAINACTIVITY")
			var options = {title: "iMedCare", actionBar: true, clearCache: true}
			menu = []
			menu.push({id: "person_add", label: "เพิ่มชื่อผู้ป่วย", title: "เพิ่มชื่อผู้ป่วย", call: "addPatient", options: {actionBar: false}})
			menu.push({id: "person", label: "Account", link: "imed/app/my/profile/info", title: "ACCOUNT", options: {actionBar: false}})
			//options.menu = menu
			return options
		}
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

			console.log(now)
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
					'<div class="sg-load" data-url="'.url('imed/my/patient/card', ['ref' => 'care']).'" data-replace="true"></div>',

					// Show visit history
					'<div class="sg-load" data-url="'.url('imed/visits', ['ref' => 'care']).'" data-replace="true">'._NL
						. '<div class="loader -rotate" style="width: 64px; height: 64px; margin: 48px auto; display: block;"></div>'
						. '</div><!-- imed-my-note -->',

				], // children
			]),
		]); // Scaffold
	}
}
?>