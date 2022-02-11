<?php
/**
* Flood News Feed
*
* @param Object $self
* @return String
*/

$debug = true;
function flood_climate_feed($self) {
	$getRef = post('ref');

	$self->theme->title = 'แจ้งสถานการณ์';

	R::View('toolbar', $self, 'City Climate', 'flood.climate');


	/*
	if (i()->username == 'softganz') {
		$ret .= '<script>$(document).ready(function() {
			$(".page.-main").prepend($("body").attr("class"))
		})</script>';
	}
	*/

	// Content of news feed
	// ภาพล่าสุดจาก CCTV, รายการจากการแจ้งสถานการณ์, จุดถ่ายภาพ, น้ำท่วมทาง
	// ข่าวประชาสัมพันธ์, รายการแจ้งจากเครือข่าย
	// บริจาค ต้องการ/มีให้

	// Show chat button
	$ret .= '<div id="flood-chat-send" class="ui-card flood-chat-send">'
			. '<div class="ui-item">'
			. '<img src="'.model::user_photo(i()->username).'" width="40" height="40" />&nbsp;'
			. '<a class="sg-action form-text" id="sendtext" href="'.url('flood/climate/chat/form').'" placeholder="แจ้งสถานการณ์?" data-rel="box" data-width="480" data-height="80%" data-webview="แจ้งสถานการณ์">แจ้งสถานการณ์?</a>&nbsp;'
			. '<a class="sg-action btn -link" href="'.url('flood/climate/chat/form').'" data-rel="box" data-width="480" data-height="80%" data-webview="แจ้งสถานการณ์"><i class="icon -camera"></i><span>Photo</span></a>'
			. '</div>'
			. '</div>';


	$lastVersion = '0.3.01';
	$updatePlayStoreUrl = "https://play.app.goo.gl/?webview&target=browser&link=https://play.google.com/store/apps/details?id=com.softganz.hatyaicityclimate";
	if (R()->appAgent->OS == 'Android' && R()->appAgent->ver >= '0.3' && R()->appAgent->ver < $lastVersion) {
		$ret .= '<div class="notify" style="padding: 24px; text-align: center;">'
			. '<p>เนื่องจากมีการอัพเดทแอพเป็นรุ่นใหม่ ขอให้ทุกท่านอัพเดทแอพเป็นรุ่นล่าสุดเพื่อให้สามารถใช้งานคุณสมบัติใหม่ๆ ได้</p>'
			. '<a class="sg-action btn -primary" href="'.$updatePlayStoreUrl.'" data-webview="browser">ดำเนินการอัพเดทแอพ</a>'
			. '<p>( New version is '.$lastVersion.' current version '.R()->appAgent->ver.' )</p>'
			. '</div>';
	}

	if (R()->appAgent) {
		$ret .= '<p class="-x-sg-flex -flex-nowrap" style="padding: 0 32px 16px;"><span style="display: block; float: left;"><img src="//www.hatyaicityclimate.org/upload/pics/logo-scccrn.png" alt="SCCCRN HATYAI" width="80" style="border-radius: 16px; margin-right: 16px;" /></span><span><b>Application เฝ้าระวังน้ำท่วม - City Climate ดำเนินการโดยภาคประชาชนเมืองหาดใหญ่</b> มีวัตถุประสงค์เพื่อรวบรวมข้อมูลเกี่ยวกับสถานการณ์น้ำใน จังหวัดสงขลา สำหรับการเรียนรู้เพื่อเตือนภัยน้ำท่วมด้วยตนเอง ภายใต้การบูรณาการความร่วมมือขององค์กรภาครัฐ ภาคเอกชน ภาควิชาการ และภาคประชาชน <strong>ภาพและเนื้อหาในเว็บไซท์สามารถนำไปเผยแพร่ต่อได้โดยใช้<a href="//creativecommons.org/licenses/by-sa/3.0/" target="_blank">สัญญาอนุญาตของครีเอทีฟคอมมอนส์แบบ แสดงที่มา-อนุญาตแบบเดียวกัน 3.0 ที่ยังไม่ได้ปรับแก้</a> และให้มีการอ้างอิงแหล่งที่มาทุกครั้ง</strong></span></p>';
	}

	$photoCardUi = new Ui('div', 'ui-card flood-camera-realtime flood-camera -feed');


	/*
	$photoCardUi->add('<div class="cctv-photo"><a class="sg-action" href="https://airsouth.things.in.th/d/-B73iuGWz/aerosure?orgId=1" data-webview="สถานีวัด PM 2.5" target="_blank"><img class="cctv-current" src="//hatyaicityclimate.org/upload/aerosure.png?v=3" style="" /></a></div><h3>สถานีวัดPM 2.5</h3>', '{id: "camera-airsouth", class: "-cctv -airsouth"}');

	$photoCardUi->add('<div class="cctv-photo" style="overflow: hidden;"><a class="sg-action" href="https://airsouth.things.in.th/d/y-_6-qGWk/aerosure-05-m-haadaihy?refresh=15m&orgId=1" data-webview="สถานี PM 2.5 ม.อ." target="_blank"><span style="position: absolute; top:0; bottom:0; left:0; right:0;"></span><iframe src="https://airsouth.things.in.th/d-solo/-B73iuGWz/aerosure?orgId=1&panelId=15" style="width: 120%; height: 150%; margin-top: -40px; margin-left: -5px; pointer-events: none; background: #212125; background-size: 100%;"></iframe></a></div><h3>PM 2.5 ม.อ.</h3>', '{id: "camera-airsouth", class: "-cctv -airsouth"}');

	$photoCardUi->add('<div class="cctv-photo" style="overflow: hidden;"><a class="sg-action" href="https://airsouth.things.in.th/d/rJLpYqMZz/aerosure-01-haadethphaa?refresh=15m&orgId=1" data-webview="สถานี PM 2.5 เทพา" target="_blank"><span style="position: absolute; top:0; bottom:0; left:0; right:0;"></span><iframe src="https://airsouth.things.in.th/d-solo/-B73iuGWz/aerosure?orgId=1&panelId=4" style="width: 120%; height: 150%; margin-top: -40px; margin-left: -5px; pointer-events: none; background: #212125; background-size: 100%;"></iframe></a></div><h3>PM 2.5 เทพา</h3>', '{id: "camera-airsouth", class: "-cctv -airsouth"}');
	*/

	$ret .= '<style>
	#app-info {display: none;} .nav.-page.-selectbasin {display: none;}
	.cctv-photo {width: 100%; height: 100%;}
	.ui-item.-cctv h3 {position: absolute; bottom: 0; left: 0; width: 100%; padding: 0; margin: 0; text-align: center; z-index: 1; font-size: 1em; height: 1.4em; overflow: hidden; pointer-events: none; background-color: #333; color: #fff; opacity: 0.5;}
	.ui-item.-cctv .cctv-error {top: 0; left: 0; bottom: 0; right: 0; padding: 0; margin: 0; font-size: 0.8em; line-height: 96px; width: 100%;}
	.ui-item.-cctv .cctv-time {top: 0; left: 0; right: 0; padding: 0 8px 0 0 ; margin: 0; font-size: 0.7em; border-radius: 0; text-align: right; pointer-events: none;}
	.ui-item.-cctv .cctv-time>span {display: inline-block; margin-left: 8px;}
	</style>';


	$notUpdateTime = 60*60;

	$cams = R::Model('flood.camera.list');


	foreach ($cams as $rs) {
		$isUpdated = date('U') - $rs->last_updated <= $notUpdateTime;
		if (!$isUpdated) {
			continue;
		}

		list($x,$y) = explode(',',$rs->location);
		$cardStr = '<div class="header"><h3>'.$rs->title.'</h3></div>'
			. '<div class="detail">'
			. ($getRef == 'desktop' ? '<a href="'.url('flood/cam/'.$rs->camid).'">' : '<a href="'.url('flood/app/cam/'.$rs->camid).'" class="sg-action" data-rel="box" data-width="100%" data-height="90%" data-webview="'.$rs->title.'">')
			. '<img id="'.$rs->name.'" class="cctv-current -photo" src="'.flood_model::photo_url($rs).'" />'
			. '</a>'
			. ($rs->overlay_url ? '<img class="photo-overlay" src="'.$rs->overlay_url.'?v=2" alt=""/>' : '')
			. '<p id="'.$rs->name.'-time" class="-timestamp">'
			.'<span class="-date">'.sg_date($rs->last_updated,'ว ดด ปป').'</span>'
			.'<span class="-time">'.sg_date($rs->last_updated,'H:i').'</span>'
			.'</p>'
			. ($isUpdated ? '' : '<p class="cctv-error -notupdate">ยังไม่ได้รับภาพใหม่</p>')
			. '<span class="-status-dot">'.($isUpdated ? '': ' -not-update').'</span>'
			. '</div><!-- detail -->';

		$photoCardUi->add(
			$cardStr,
			'{id: "camera-'.$rs->name.'", class: "camera -'.$rs->name.'"}'
		);
	}




	$ret .= '<div id="flood-chat-card">'._NL;

	$ret .= $photoCardUi->build();

	$ret .= R::Page('flood.chat.drawmsg', NULL);
	$ret .= '</div>'._NL;

	head(
	'<script type="text/javascript"><!--
		function onWebViewComplete() {
			console.log("CALL onWebViewComplete FROM WEBVIEW")
			var options = {actionBar: true, navBar: true}
			return options
		}
	</script>'
	);

	head('flood.event.js','<script type="text/javascript" src="/flood/js.flood.event.js?v=3"></script>');

	if (cfg('flood.realtime.firebase')) {
		$ret.='<script type="text/javascript">
			$(document).ready(function() {

				var chatBox = $("#flood-chat-card");
				if (firebaseConfig) {
					var database = firebase.database();
					var ref = database.ref(firebaseConfig.flood + "chat")


					// Sort on time

					var getCurrentTimestamp = (function() {
							var OFFSET = 0;

							database.ref("/.info/serverTimeOffset").on("value", function(ss) {
								OFFSET = ss.val()||0;
							});

							return function() {
							return Date.now() + OFFSET;
						}
					})();

					var now = getCurrentTimestamp();
					//var delta = now - timestamp;

					//console.log(now);

					var i=0;

					ref
					.orderByChild("timestamp")
					.startAt(now)
					.on("child_added",function(snap){
						//console.log("child added");
						//console.log(snap.val());
						addToList(snap.val(),snap.key);
						console.log(++i + " : " + snap.key)
						console.log(snap.val())
					});

				}

			function addToList(chatInfo,key) {
				var drawUrl = "'.url('flood/chat/drawmsg').'"
				var para = {}
				para.eid = chatInfo.eventId
				console.log("PARAMETER ",para)
				$.post(drawUrl, para, function(html) {
					console.log(html)
					chatBox.prepend(html);
				})
			}

			function floodChatUpdate(chatKey, chatInfo) {
				/*
				var $camera=$("#camera-"+cameraName);
				var $img=$("#"+cameraName);
				var $time=$("#"+cameraName+"-time");
				var $thumb=$("#thumb-"+cameraName);
				$img.attr("src",cameraInfo.url);

				console.log("Update Photo Src " + cameraInfo.url)

				$thumb.attr("src",cameraInfo.thumb);
				$camera.find(".date.-date").html(cameraInfo.date);
				$camera.find(".date.-time").html(cameraInfo.time);
				$camera.find(".flood-cam-error.not-update").hide();
				*/

				var $chatCard = $("#flood-chat-card")
				$chatCard.prepend(chatKey)
				return;
			}

			});
			</script>';
	}

	return $ret;
}
?>