<?php
/**
* Flood Chat
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;
function flood_climate_chat($self) {
	$self->theme->title = 'แจ้งสถานการณ์';

	// Show chat botton
	$ret .= '<div id="flood-chat-send" class="ui-card flood-chat-send">'
			. '<div class="ui-item">'
			. '<img src="'.model::user_photo(i()->username).'" width="40" height="40" />&nbsp;'
			. '<a class="sg-action form-text" id="sendtext" href="'.url('flood/climate/chat/form').'" placeholder="แจ้งสถานการณ์?" data-rel="box" data-width="480" data-webview="แจ้งสถานการณ์">แจ้งสถานการณ์?</a>&nbsp;'
			. '<a class="sg-action btn -link" href="'.url('flood/climate/chat/form').'" data-rel="box" data-width="480" data-webview="แจ้งสถานการณ์"><i class="icon -camera"></i><span>Photo</span></a>'
			. '</div>'
			. '</div>';



	$ret .= '<div id="flood-chat-card">'._NL;
	$ret .= R::Page('flood.chat.drawmsg', NULL);
	$ret .= '</div>'._NL;

	head('flood.event.js','<script type="text/javascript" src="/flood/js.flood.event.js?v=3"></script>');

	if (cfg('flood.realtime.firebase')) {
		$ret.='<script type="text/javascript">
			$(document).ready(function(){

				var chatBox = $("#flood-chat-card");
				if (firebaseConfig) {
					var database = firebase.database();
					//var ref = database.ref("/chat/");
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

					console.log(now);

					var i=0;

					//ref.on("child_added",function(snap){

					//console.log(firebase.database.ServerValue.TIMESTAMP);
					
					ref
					.orderByChild("timestamp")
					.startAt(now)
					.on("child_added",function(snap){
						//console.log("child added");
						//console.log(snap.val());
						addToList(snap.val(),snap.key);
						console.log(++i + " : " + snap.key)
						//console.log(snap.val())
					});

				}

			function addToList(chatInfo,key) {
				var drawUrl = "'.url('flood/chat/drawmsg').'"
				var para = {}
				para.eid = chatInfo.eventId
				$.post(drawUrl, para, function(html) {
					chatBox.prepend(html);
				})

				/*
				var date;
				if (typeof chatInfo.time == "number") {
					date=new Date(chatInfo.time+7*60*60*1000).toISOString();
				}  else {
					date=chatInfo.time;
				}
				var li = "<div class=\"additem\">";
				li+="<span class=\"time\">Tags : <b>"+chatInfo.tags+"</b> Topic "+chatInfo.tpid+" Group="+chatInfo.group+" Field="+chatInfo.field+" Time="+date+" ("+chatInfo.time+")"+" key="+key+"</span>";
				li+="<div class=\"post\">"+chatInfo.value+"</div>"
				if (chatInfo.url) {
					var l = document.createElement("a");
					l.href = chatInfo.url;
					li+="<div class=\"url\"><a href=\""+chatInfo.url+"\" target=\"_blank\">"+l.hostname+"</a></div>";
				}
				li+="</div>";
				chatBox.prepend(li);
				*/
			}




			/*
				if (firebaseConfig) {
					var database = firebase.database();
					var ref = database.ref("/chat/");

					var i=0;
			
					ref
					.on("child_added",function(snap){
						floodChatUpdate(snap.key,snap.val());
						console.log("New Photo Update #" + (++i) + " of " + snap.key)
						//console.log(snap.val())
					});
					
				}
			*/

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