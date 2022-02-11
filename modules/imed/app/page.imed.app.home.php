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

class ImedAppHome {
	var $isAccessDevVersion = false;

	function __construct() {
		$this->isAccessDevVersion = in_array(i()->username, ['softganz','bear1','momo','chakrite','punyha']);
	}

	function build() {
		$psnId = SG\getFirst($this->psnId, post('pid'));
		$getSearch = post('pn');


		$uid = i()->uid;

		// debugMsg($_SESSION,'$_SESSION');

		if ($_SESSION['imedapp'] === 'psyc') location('imed/psyc');
		else if ($_SESSION['imedapp'] === 'care') location('imed/care');

		$lastVersion = '0.3.10';
		$updatePlayStoreUrl = "https://play.app.goo.gl/?target=browser&link=https://play.google.com/store/apps/details?id=com.softganz.imedhome";
		// if ($uid && R()->appAgent->OS == 'Android' && R()->appAgent->ver < $lastVersion) {
		// //if (i()->username == 'softganz') {
		// 	//debugMsg(R()->appAgent->OS == 'Android' ? 'Yes Android': 'Not Android');
		// 	//debugMsg(R()->appAgent->ver == '0.1.12' ? 'Yes 0.1.12': 'Not 0.1.12');
		// 	//debugMsg(gettype(R()->appAgent->ver));
		// 	debugMsg('<div class="notify" style="padding: 24px; text-align: center;">'
		// 		. '<p>เนื่องจากมีการอัพเดทแอพเป็นรุ่นใหม่ ขอให้ทุกท่านอัพเดทแอพเป็นรุ่นล่าสุดเพื่อให้สามารถใช้งานคุณสมบัติใหม่ๆ ได้</p>'
		// 		. '<a class="sg-action btn -primary" href="'.$updatePlayStoreUrl.'" '.(R()->appAgent->ver >= '0.2' ? 'data-webview="browser"' : '').'>ดำเนินการอัพเดทแอพ</a>'
		// 		. '<p>New version is '.$lastVersion.' current verion '.R()->appAgent->ver.'</p>'
		// 		. '</div>');
		// }


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

		if (R()->appAgent && R()->appAgent->ver < '0.2') {
			return R::Page('imed.app.v1', $self);
		}

		$isAdmin = user_access('administer imeds');


		$this->_script();

		return new Scaffold([
			'child' => new Widget([
				'children' => [
					// Show banner
					'<div id="banner" class="imed-app-banner"><span class="-logo-back"></span><span class="-title">iMed@home</span><span class="-motto"><span class="-th">ร่วมสร้างสังคมไม่ทอดทิ้งกัน</span><span class="-en">Social Network Home Health Care</div>',

					R()->appAgent->OS == 'Android' && R()->appAgent->ver < $lastVersion ?
						'<div class="notify" style="padding: 24px; text-align: center;">'
							. '<p>เนื่องจากมีการอัพเดทแอพเป็นรุ่นใหม่ ขอให้ทุกท่านอัพเดทแอพเป็นรุ่นล่าสุดเพื่อให้สามารถใช้งานคุณสมบัติใหม่ๆ ได้</p>'
							. '<a class="sg-action btn -primary" href="'.$updatePlayStoreUrl.'" '.(R()->appAgent->ver >= '0.2' ? 'data-webview="browser"' : '').'>ดำเนินการอัพเดทแอพ</a>'
							. '<p>New version is '.$lastVersion.' current verion '.R()->appAgent->ver.'</p>'
							. '</div>'
					: NULL,

					// Show search box
					'<div id="green-chat-box" class="sg-action chat-box -imed-app-home" href="'.url('imed/app/search').'" data-webview="ค้นหา">'
						. '<img src="'.model::user_photo(i()->username).'" width="40" height="40" />&nbsp;'
						. '<a class="sg-action form-text" href="'.url('imed/app/search').'" data-webview=\'ค้นหา\' data-options=\'{history: true, actionBar: true, menu: [{id: "person_add", label: "เพิ่มชื่อผู้ป่วย", title: "เพิ่มชื่อผู้ป่วย", call="addPatient"}]}\'><span style="font-size: 0.825em;">ค้น ชื่อ นามสกุล หรือ เลข 13 หลัก ของผู้ป่วย</span><i class="icon -material">search</i></a>'
						. '</div>',

					// Check group invite
					R::View('imed.my.notify'),

					$this->_appList(),

					// Show my patient card
					'<div id="patient-my" class="sg-load" data-url="'.url('imed/my/patient/card', ['ref' => 'app']).'" data-replace="true" style="margin: 8px 0 0 0;"></div>',

					// Show visit history
					'<div class="sg-load" data-url="'.url('imed/visits', ['ref' => 'app']).'" data-replace="true">'._NL
						. '<div class="loader -rotate" style="width: 64px; height: 64px; margin: 48px auto; display: block;"></div>'
						. '</div><!-- imed-my-note -->',

				], // children
			]),
		]); // Scaffold
	}

	function _appList() {
		// if (!R()->appAgent || !$this->isAccessDevVersion) return NULL;
		return new Card([
			'style' => 'padding-bottom: 8px;',
			'children' => [
				new ListTile([
					'title' => 'iMed App Family',
				]),
				new Row([
					'class' => 'new-app',
					'children' => [
						$this->isAccessDevVersion ? '<a class="btn" href="'.(R()->appAgent ? url('imed/app/select?app=care') : url('imed/care')).'"><i class="icon"><img src="//communeinfo.com/themes/default/care/logo_care.png" width="24" height="24" /></i><span>iMedCare</span></a>' : NULL,
						'<a class="btn" href="'.(R()->appAgent ? url('imed/app/select?app=psyc') : url('imed/psyc')).'"><i class="icon -material">psychology</i><span>iMed@จิตเวช</span></a>',
						'<style>
						.new-app>*:last-child {display: none;}
						.new-app>.-item {margin: 0 16px 0 8px;}
						.new-app .btn {border-radius: 4px;}
						.new-app .icon {width: 48px; height: 48px; display: block; margin: 0 auto; border-radius: 50%; font-size: 40px; background-color: #fff;}
						</style>'
					], // children
				]), // Row
			], // children
		]);
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
			let back = "'.post('back').'"
			console.log("CALL onWebViewComplete FROM MAINACTIVITY")
			var options = {title: "iMed@home", actionBar: true, clearCache: true, history: false, actionBarColor: "#FF0000"}
			if (back) {
				options.menu = []
				options.menu.push({id: "search", label: "ค้นหาผู้ป่วย", title: "ค้นหาผู้ป่วย", link: "imed/app/search"})
			}
			return options
		}



		var canQuery = true

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

		$(document).on("keyup", "#edit-pn", function() {
			if (!canQuery) {
				console.log("NOT QUERY "+$(this).val())
				return false
			}

			var patientUrl = "'.url('imed/app/patient/').'"
			var $this = $(this)
			var url = $this.data("query")
			para = {}
			para.q = $this.val()

			//console.log("GET "+para.q)

			canQuery = false

			if ($this.val() != "") {
				$("#imed-my-note").hide()
				$("#patient-list>p").hide()
				$(".imed-my-note-more").hide()
			} else {
				$("#imed-my-note").show()
				$("#patient-list>p").show()
				$(".imed-my-note-more").show()
			}

			$.post(url,para, function(data) {
				$("#patient-list>.ui-card.-patient").empty()
				$.each(data, function(index,value){
					//console.log(value)
					var cardStr = ""
					if (value.value == "...") {
						cardStr = "<div class=\"ui-item\" style=\"text-align: center; padding:16px 0; flex:1 0 100%\"><a>... ยังมีอีก ...</a></div>"
					} else {
						var patientName = value.prename + " " + value.label
						cardStr = "<a href=\"" + patientUrl + "/" + value.value + "\" class=\"ui-item sg-action\" data-webview=\"true\" data-webview-title=\"" + patientName + "\"><div class=\"header\"><b>" + patientName + "</b></div><div class=\"detail\"><p>" + value.desc + "</p></div></a>"
					}
					$("#patient-list>.ui-card.-patient").append(cardStr)
				})
			}, "json")
			.done(function() {
				canQuery = true
			})
		})

		$("#edit-pn").click(function(){
			//$(this).closest("div").css("margin-right","0px");
		});'._NL;

		if (cfg('firebase')) {
			$headerScript .= '$(document).ready(function() {
			if (!firebaseConfig) return

			var database = firebase.database()
			var ref = database.ref(firebaseConfig.visit)
			var drawUrl = "'.url('imed/visit').'"
			var i = 0
			var getCurrentTimestamp = (function() {
				var OFFSET = 0
				database.ref("/.info/serverTimeOffset").on("value", function(ss) {
					OFFSET = ss.val()||0
				});
				return function() { return Date.now() + OFFSET }
			})();

			var now = getCurrentTimestamp()

			ref
			.orderByChild("time")
			.startAt(now)
			.on("child_added",function(snap){
				let url = drawUrl+"/"+snap.val().psnid+"/item/"+snap.key
				let para = {ref: "app"}
				$.post(url, para, function(html) {
					if (html) {
						var visitBox = $("#imed-visits")
						visitBox.prepend(html)
					}
				})
				console.log("NEW: " + (++i) + " : " + snap.key, snap.val())
			})
			';

			if (cfg('imed.visit.realtime.change.member') == 'all' || (cfg('imed.visit.realtime.change.member') == 'admin' && is_admin('imed'))) {
				$headerScript .= '
			ref
			.on("child_changed",function(snap) {
				let url = drawUrl+"/"+snap.val().psnid+"/item/"+snap.key
				let para = {ref: "app"}
				$.post(url, para, function(html) {
					$("#imed-visit-"+snap.key).replaceWith(html)
				})
				console.log("CHANGE: " + (++i) + " : " + snap.key, snap.val())
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