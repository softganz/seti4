<?php
/**
* Project :: App Home Page
* Created 2021-01-20
* Modify  2021-01-29
*
* @param Object $self
* @return String
*
* @usage project/app
*/

$debug = true;

function project_app($self) {
	$isDevVersion = true; //in_array(i()->username, explode(',',cfg('project.useDevVersion')));
	$isShowAll = true;
	$isFirebaseRealTime = false; //is_admin() || in_array(i()->username, array('softganz', 'pongtheps'));

	$ret = '';

	if (post('u')) $isShowAll = false;

	//$ret .= print_o(R()->appAgent, '$appAgent');
	$lastVersion = '0.1.03';
	$updatePlayStoreUrl = "https://play.app.goo.gl/?target=browser&link=https://play.google.com/store/apps/details?id=com.softganz.otou";
	if (R()->appAgent->OS == 'Android' && R()->appAgent->ver < $lastVersion) {
		//if (i()->username == 'softganz') {
		//$ret .= R()->appAgent->OS == 'Android' ? 'Yes Android': 'Not Android';
		//$ret .= R()->appAgent->ver == '0.1.12' ? 'Yes 0.1.12': 'Not 0.1.12';
		//$ret .= gettype(R()->appAgent->ver);
		$ret .= '<div class="notify" style="padding: 24px; text-align: center;">'
			. '<p>เนื่องจากมีการอัพเดทแอพเป็นรุ่นใหม่ ขอให้ทุกท่านอัพเดทแอพเป็นรุ่นล่าสุดเพื่อให้สามารถใช้งานคุณสมบัติใหม่ๆ ได้อย่างสมบูรณ์</p>'
			. '<a class="sg-action btn -primary" href="'.$updatePlayStoreUrl.'" data-android="browser">ดำเนินการอัพเดทแอพ</a>'
			. '<p>New ver is '.$lastVersion.' current ver '.R()->appAgent->ver.'</p>'
			. '</div>';
	}

	$ret .= '<div class="project-app-banner"><img src="//1t1u.psu.ac.th/upload/pics/1t1u-banner.png" width="100%" alt="1T1U" /></div>';
	//if ($isShowAll) $ret .= R::Page('project.app.news', NULL);


	// Show main navigator
	/*
	$toolbar = new Toolbar(NULL);
	$ui = new Ui(NULL, 'ui-nav');
	$toolbar->addNav('main', $ui);
	//$ret .= $toolbar->build();
	*/



	// Show Activity Post Button
	$ret .= '<div id="project-chat-box" class="ui-card project-chat-box">'
		. '<div class="ui-item">'
		. '<div><img src="'.model::user_photo(i()->username).'" width="40" height="40" />&nbsp;'
		. '<a class="sg-action form-text" href="'.url('project/app/action/form').'" placeholder="เขียนบันทึกการทำกิจกรรม" data-rel="#project-activity-card" data-width="480" data-height="100%" x-data-webview="บันทึกการทำกิจกรรม">เขียนบันทึกการทำกิจกรรม</a>&nbsp;'
		. '<a class="sg-action btn -link" href="'.url('project/app/action/form').'" x-data-rel="box" data-width="480" data-height="100%" data-webview="บันทึกการทำกิจกรรม"><i class="icon -camera"></i><span>Photo</span></a></div>'
		. '</div>'
		. '</div>';


	//TODO: Add App Short Message. Eg. ลิงก์เพื่อเข้าอบรม
	$ret .= '<div id="project-app-message" class="sg-load" data-url="'.url('project/app/message').'"></div>';

	$ret .= '<div id="project-child-card" class="sg-load" data-url="'.url('project/child').'" style="margin: 8px 0 16px 0;"></div>';

	// Show project activity
	$ret .= '<section id="project-activity-card" class="sg-load" data-url="'.url('project/app/activity', array('u' => post('u'))).'" data-webview-resume="load:#project-activity-card:'.url('project/app/activity', array('u' => i()->uid)).'">'._NL;
	$ret .= '<div class="loader -rotate" style="width: 64px; height: 64px; margin: 32px auto; display: block;"></div>';
	$ret .= '</section><!-- project-app-activity -->';

	head('<style type="text/css"></style>');

	$headerScript = '<script type="text/javascript">';

	$headerScript .= '
	$(document).ready(function() {
		$("#project-chat-box a").click(function() {
			console.log("CLICK")
			$("#project-app-message").hide()
			$("#project-child-card").hide()
		})
	})
	';

	if ($isFirebaseRealTime && cfg('firebase')) {
		$headerScript .= '
		$(document).ready(function() {
			if (!firebaseConfig) return

			let database = firebase.database()
			let ref = database.ref(firebaseConfig.update)
			let i = 0
			let getCurrentTimestamp = (function() {
				let OFFSET = 0
				database.ref("/.info/serverTimeOffset").on("value", function(ss) {
					OFFSET = ss.val()||0
				});
				return function() { return Date.now() + OFFSET }
			})();

			let now = getCurrentTimestamp()

			ref
			.orderByChild("time")
			.startAt(now)
			.on("child_added",function(snap) {
				let $insertEle = $("#project-activity-card")
				console.log("$insertEle.length = ",$insertEle.length)
				if ($insertEle.length && snap.val().changed == "new") {
					let drawUrl = "'.url('project').'/" + snap.val().projectId + "/info.action.card/" + snap.key
					//console.log("drawUrl = ",drawUrl)
					$.post(drawUrl, function(html) {
						//console.log(html)
						if (html) {
							$insertEle.find("#project-activity").prepend(html)
						}
					});
					//$insertEle.find("#project-activity").prepend("NEW ACTION")
				}
				console.log("ADD #" + (++i) + " : " + snap.key, snap.val())
			})

			ref
			.on("child_changed",function(snap) {
				let $updateEle = $("#project-action-"+snap.key)
				console.log("CHANGE #" + (++i) + " : " + snap.key, snap.val())
				console.log("$updateEle.length = ",$updateEle.length)
				if ($updateEle.length) {
					if (snap.val().changed == "remove") {
						$("#project-action-"+snap.key).remove()
						console.log("REMOVE #",++i + " : " + snap.key, snap.val())
					} else {
						//$updateEle.replaceWith("UPDATE ACTION")
						let drawUrl = "'.url('project').'/" + snap.val().projectId + "/info.action.card/" + snap.key
						$.post(drawUrl, function(html) {
							$updateEle.replaceWith(html)
						});
					}
				}
			})
		})
		';
	}

	$headerScript .= '</script>'._NL;

	head($headerScript);

	return $ret;
}
?>