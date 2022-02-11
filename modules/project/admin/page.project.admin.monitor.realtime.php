<?php
function project_admin_monitor_realtime($self) {
	R::View('project.toolbar',$self,'Project Realtime Monitor','admin');

	$ret .= '<div class="chats"><div id="chats"></div></div>';

	$ret .= '<script type="text/javascript">
	$(document).ready(function(){
		var chatBox = $("#chats")
		if (firebaseConfig) {
			var database = firebase.database()
			var ref = database.ref("/update/")

			// Sort on time
			/*
			ref
			.orderByChild("time")
			.limitToLast(10)
			.once("value", (snapshot,error) => {
				var feed=[];
				//chatBox.empty();
				snapshot.forEach((snap)=>{
					//const comment = snap.val();
					//feed.push(comment);
					addToList(snap.val());
				});
				//console.log(feed);
			});
			*/

			var getCurrentTimestamp = (function() {
					var OFFSET = 0

					database.ref("/.info/serverTimeOffset").on("value", function(ss) {
						OFFSET = ss.val()||0
					})

					return function() {
						return Date.now() + OFFSET
					}
			})();

			var now = getCurrentTimestamp()
			//var delta = now - timestamp

			console.log(now)

			var i = 0

			//ref.on("child_added",function(snap){

			//console.log(firebase.database.ServerValue.TIMESTAMP);
			
			ref
			.orderByChild("time")
			.startAt(now - 1*24*60*60*1000)
			.on("child_added",function(snap){
				//console.log("child added");
				//console.log(snap.val());
				addToList(snap.val(),snap.key);
				console.log(++i + " : " + snap.key)
				console.log(snap.val())
			});
			


			/*
			ref
			.orderByChild("time")
			.on("value", (snapshot,error) => {
				var feed=[];
				//chatBox.empty();
				snapshot.forEach((snap)=>{
					const comment = snap.val();
					feed.push(comment);
					addToList(comment,comment);
				});
				//console.log(feed);
			});
			*/

		}

	function addToList(comment,key) {
		var date;
		console.log(typeof comment.time);
		if (typeof comment.time == "number") {
			date=new Date(comment.time+7*60*60*1000).toISOString();
		}  else {
			date=comment.time;
		}
		var li = "<div class=\"additem\">";
		li+="<span class=\"time\">Tags : <b>"+comment.tags+"</b> Topic "+comment.tpid+" Group="+comment.group+" Field="+comment.field+" Time="+date+" ("+comment.time+")"+" key="+key+"</span>";
		li+="<div class=\"post\">"+comment.value+"</div>"
		if (comment.url) {
			var l = document.createElement("a");
			l.href = comment.url;
			li+="<div class=\"url\"><a href=\""+comment.url+"\" target=\"_blank\">"+l.hostname+"</a></div>";
		}
		li+="</div>";
		chatBox.prepend(li);
	}

	});
	</script>';
	$ret.='<style type="text/css">
	.time {color:gray;font-size:0.8em;}
	.additem {margin:16px 0; padding:16px; border:none; box-shadow: 0px 0px 0px 1px #ddd inset; border-radius:2px;}
	.post {margin: 16px 0;}
	.url {}
	.url a {color:gray;}
	</style>';
	return $ret;
}
?>