<?php
/**
* iMed :: App Home Ver 2
* Created 2020-09-23
* Modify  2021-05-31
*
* @param Int $psnId
* @return Widget
*
* @usage imed/app/{$psnId}
*/

$debug = true;

import('model:imed.patient');

class ImedApp {
	var $psnId;
	var $_args = [];

	function __construct($psnId = NULL, $action = NULL, $tranId = NULL) {
		$this->psnId = $psnId;
		$this->action = $action;
		$this->tranId = $tranId;
		$this->_args = func_get_args();
	}

	function build() {
		if (!is_numeric($this->psnId)) {$this->action = $this->psnId; unset($this->psnId);} // Action as psnId and clear

		if (is_numeric($this->psnId)) {
			$patientInfo = PatientModel::get($this->psnId);
		}

		if (empty($this->psnId) && empty($this->action)) $this->action = 'home';
		else if ($this->psnId && empty($this->action)) $this->action = 'info.home';

		$argIndex = 2; // Start argument

		// debugMsg('PAGE CONTROLLER Id = '.$this->psnId.' , Action = imed.app.'.$this->action.' , ArgIndex = '.$argIndex.' , Arg 1 = '.$this->_args[$argIndex]);
		// debugMsg($this->_args, '$args');

		$ret = R::Page(
			'imed.app.'.$this->action,
			$patientInfo,
			$this->_args[$argIndex],
			$this->_args[$argIndex+1],
			$this->_args[$argIndex+2],
			$this->_args[$argIndex+3],
			$this->_args[$argIndex+4]
		);

		//debugMsg('TYPE = '.gettype($ret));
		if (is_null($ret)) $ret = message('error', 'ขออภัย!!! ไม่เจอหน้าที่ต้องการอยู่ระบบ');

		return $ret;
	}






	function _home() {
		$psnId = SG\getFirst($this->psnId, post('pid'));
		$getSearch = post('pn');

		$uid = i()->uid;

		$ret = '';

		$lastVersion = '0.20';
		$updatePlayStoreUrl = "https://play.app.goo.gl/?target=browser&link=https://play.google.com/store/apps/details?id=com.softganz.imedhome";
		// if ($uid && R()->appAgent->OS == 'Android' && R()->appAgent->ver < $lastVersion) {
		// //if (i()->username == 'softganz') {
		// 	//$ret .= R()->appAgent->OS == 'Android' ? 'Yes Android': 'Not Android';
		// 	//$ret .= R()->appAgent->ver == '0.1.12' ? 'Yes 0.1.12': 'Not 0.1.12';
		// 	//$ret .= gettype(R()->appAgent->ver);
		// 	$ret .= '<div class="notify" style="padding: 24px; text-align: center;">'
		// 		. '<p>เนื่องจากมีการอัพเดทแอพเป็นรุ่นใหม่ ขอให้ทุกท่านอัพเดทแอพเป็นรุ่นล่าสุดเพื่อให้สามารถใช้งานคุณสมบัติใหม่ๆ ได้</p>'
		// 		. '<a class="sg-action btn -primary" href="'.$updatePlayStoreUrl.'" '.(R()->appAgent->ver >= '0.2' ? 'data-webview="browser"' : '').'>ดำเนินการอัพเดทแอพ</a>'
		// 		. '<p>New version is '.$lastVersion.' current verion '.R()->appAgent->ver.'</p>'
		// 		. '</div>';
		// }


		if (!i()->ok) {
			$ret = R::View('signform', '{time:-1, showTime: false}');
			$ret .= '<style type="text/css">
			.toolbar.-main.-imed h2 {text-align: center;}
			.form.signform .form-item {margin-bottom: 16px; position: relative;}
			.form.signform label {position: absolute; left: 8px; color: #666; font-style: italic; font-size: 0.9em; font-weight: normal;}
			.form.signform .form-text, .form.signform .form-password {padding-top: 16px;}
			.module-imed.-softganz-app .form-item.-edit-cookielength {display: none;}
			.login.-normal h3 {display: none;}
			</style>';
			return $ret;
		}

		// if numeric parameter, load patient main page
		if ($psnId && is_numeric($psnId)) return R::Page('imed.app.patient', $self, $psnId);


		if (R()->appAgent && R()->appAgent->ver < '0.2') {
			$ret .= R::Page('imed.app.v1', $self);
			return $ret;
		}



		$isAdmin = user_access('administer imeds');

		/*
		$ret.='<p style="margin: 32px;"><a class="btn" href="javascript:void(0)" onclick="showAndroidToast(\'Hello Android!\')">Click for android</a></p>
		<script type="text/javascript">
		function showAndroidToast(toast) {
			if (typeof Android=="object") Android.showToast(toast);
			else console.log("App not run on Android")
		}
		</script>';
		*/

		//$ret .= '@'.date('H:i:s');

		//R::View('imed.toolbar',$self,'@'.i()->name,'app');

		//$ret .= print_o(getallheaders(),'getallheaders()');
		//$ret .= print_o($_SERVER,'$_SERVER');
		//$ret .= $_SERVER['HTTP_USER_AGENT'];

		// Show Activity Post Button

		/*
		$form = new Form(NULL,url('imed/app/person/search','webview'),NULL,'sg-form imed-search-patient');
		$form->addConfig('method', 'GET');
		$form->addData('checkValid',true);
		$form->addData('rel', '#patient-list');
		$form->addField('pid',array('type' => 'hidden', 'id' => 'pid'));
		$form->addField(
			'pn',
			array(
				//'label' => 'ชื่อผู้ป่วยที่ต้องการเยี่ยมบ้าน',
				'type' => 'text',
				//'class' => 'sg-autocomplete -fill',
				'class' => '-fill',
				'require' => true,
				'value' => htmlspecialchars($getSearch),
				'autocomplete' => 'OFF',
				'attr' => array(
						'data-query' => url('imed/api/person'),
						'data-altfld' => 'pid',
					),
				'placeholder' => 'ระบุ ชื่อ นามสกุล หรือ เลข 13 หลัก ของผู้ป่วย',
				'pretext' => '<div class="input-prepend"><span><a class="-sg-16" href="javascript:void(0)" onclick=\'$("#edit-pn").val("");\'><i class="icon -material -gray -sg-16">clear</i></a></span></div>',
				'posttext' => '<div class="input-append"><span><button type="submit" class="btn"><i class="icon -material">search</i></button></span></div><span><a class="sg-action btn -primary -addnew" href="'.url('imed/app/patient/add').'" data-rel="#patient-list" data-webview="เพิ่มชื่อผู้ป่วยรายใหม่" title="เพิ่มชื่อผู้ป่วยรายใหม่"><i class="icon -material">person_add</i></a></span>',
				'container' => '{class: "-group"}',
			)
		);
		*/


		// $ret .= '<div id="banner" style="margin: 0; padding: 0;"><img src="//communeinfo.com/upload/banner-imed-02.jpg" width="100%" /></div>';

		// $ret .= '<div id="green-chat-box" class="sg-action chat-box -imed-app-home" href="'.url('imed/app/search').'" data-webview="ค้นหา">'
		// 	. '<img src="'.model::user_photo(i()->username).'" width="40" height="40" />&nbsp;'
		// 	. '<a class="sg-action form-text" href="'.url('imed/app/search').'" data-webview=\'ค้นหา\' data-options=\'{history: true, actionBar: true, menu: [{id: "person_add", label: "เพิ่มชื่อผู้ป่วย", title: "เพิ่มชื่อผู้ป่วย", call="addPatient"}]}\'><span style="font-size: 0.825em;">ค้น ชื่อ นามสกุล หรือ เลข 13 หลัก ของผู้ป่วย</span><i class="icon -material">search</i></a>'
		// 	. '</div>';
		// 	// Test menu {id: "addperson", label: "เพิ่มชื่อผู้ป่วย", title: "เพิ่มชื่อผู้ป่วย", call="addPatient"},{id: "account", label: "Account", load: "imed/app/my"},{id: "group", label: "Call Script", call="addPatient"}

		// /*
		// } else {
		// 	$ret .= '<div id="imed-toolbar" class="toolbar -main -imed">'
		// 		. $form->build()
		// 		. '</div>';
		// }
		// */

		// // Check group invite
		// $stmt = 'SELECT
		// 	b.`fldref` `orgid`, b.`keyid` `uid`
		// 	, u.`username`, u.`name`
		// 	, o.`name` `orgName`
		// 	, b.`flddata` `data`
		// 	FROM %bigdata% b
		// 		LEFT JOIN %users% u ON u.`uid` = b.`keyid`
		// 		LEFT JOIN %db_org% o ON o.`orgid` = b.`fldref`
		// 	WHERE b.`keyname` = "imed" AND b.`fldname` = "group.invite" AND b.`keyid` = :uid';
		// $watingInvite = mydb::select($stmt, ':uid', i()->uid);
		// $watingInviteCard = new Ui(NULL, 'ui-card -patient');
		// if ($watingInvite->count()) {
		// 	$watingInviteCard->add(
		// 		'<div class="detail">'
		// 		. 'มีคำเชิญเข้ากลุ่ม '
		// 		. '<nav class="nav -card -sg-text-right"><a class="btn -link -cancel" href=""><a class="sg-action btn -primary" href="'.url('imed/social/my/invite').'" data-rel="box"><i class="icon -material">description</i><span>รายละเอียด</span></a></nav>'
		// 		. '</div>',
		// 		array(
		// 			'class' => 'sg-action',
		// 			'href' => url('imed/social/my/invite'),
		// 			'data-rel' => 'box',
		// 		)
		// 	);
		// 	$ret .= $watingInviteCard->build();
		// }
		// //$ret .= print_o($watingInvite);

		// $ret .= '<div id="patient-my" class="sg-load" data-url="'.url('imed/my/patient/card').'" data-replace="true" style="margin: 8px 0 0 0;"></div>';

		// //$ret .= '<div id="patient-list">';

		// //$ret .= '<div class="ui-card -patient -sg-flex -co-2" style="margin: 0;"></div>';

		// // Show visit history
		// $ret .= '<div id="imed-my-note-load" class="sg-load" data-url="'.url('imed/visits', ['ref' => 'app']).'" data-replace="true">'._NL;
		// $ret .= '<div id="imed-my-note" class="loader -rotate" style="margin: 48px auto; display: block;"></div>';
		// $ret .= '</div><!-- imed-my-note -->';

		// //$ret .= '</div><!-- patient-list -->';

		head('<style type="text/css">
			.toolbar.-main.-imed {border-bottom : 1px #eee solid; overflow: hidden;}
			.toolbar.-main.-imed .form {margin: 0; padding: 0; height: 47px;}
			.module-imed.-app .imed-search-patient .form-item.-edit-pn {padding: 5px 8px;}
			.chat-box .ui-card -patient {margin: 0;}
		</style>');

		$headerScript = '<script type="text/javascript">
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

		function onWebViewComplete() {
			console.log("CALL onWebViewComplete FROM MAINACTIVITY")
			var options = {title: "iMed@home", actionBar: true, clearCache: true}
			menu = []
			menu.push({id: "person_add", label: "เพิ่มชื่อผู้ป่วย", title: "เพิ่มชื่อผู้ป่วย", call: "addPatient", options: {actionBar: false}})
			menu.push({id: "person", label: "Account", link: "imed/app/my/profile/info", title: "ACCOUNT", options: {actionBar: false}})
			//options.menu = menu
			return options
		}

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

			ref
			.orderByChild("time")
			.startAt(now)
			.on("child_added",function(snap){
				$.post(drawUrl + "/" + snap.val().seq, function(html) {
					if (html) {
						var visitBox = $("#imed-my-note")
						visitBox.prepend(html)
					}
				})
				//console.log(++i + " : " + snap.key, snap.val())
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

		head($headerScript);

		return new Scaffold([
			'child' => new Container([
				'children' => [
					// Show banner
					'<div id="banner" style="margin: 0; padding: 0;"><img src="//communeinfo.com/upload/banner-imed-02.jpg" width="100%" /></div>',

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
}
?>