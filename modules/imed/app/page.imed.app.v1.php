<?php
function imed_app_v1($self, $psnId = NULL) {
	$psnId = SG\getFirst($psnId, post('pid'));
	$getSearch = post('pn');

	$uid = i()->uid;

	$ret = '';

	R::View('imed.toolbar',$self,'@'.i()->name,'app');

	$form = new Form(NULL,url('imed/app/person/search','webview'),NULL,'sg-form imed-search-patient');
	$form->addConfig('method', 'GET');
	$form->addData('checkValid',true);
	$form->addData('rel', '#patient-list');
	$form->addField('pid',array('type' => 'hidden', 'id' => 'pid'));
	$form->addField(
		'pn',
		array(
			'label' => 'ชื่อผู้ป่วยที่ต้องการเยี่ยมบ้าน',
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
			'posttext' => '<div class="input-append"><span><button type="submit" class="btn"><i class="icon -material">search</i></button></span></div><span><a class="sg-action btn -primary -addnew" href="'.url('imed/app/patient/add').'" data-rel="#patient-list" title="เพิ่มชื่อผู้ป่วยรายใหม่"><i class="icon -material">person_add</i></a></span>',
			'container' => '{class: "-group"}',
		)
	);

	$ret .= $form->build();

	$ret .= '<div style="padding: 8px 24px; background-color: #fff;">ค้นหา ชื่อ นามสกุล โดยไม่ต้องป้อนคำนำหน้านาม</div>';

	$ret .= '<div id="patient-list">';
	$ret .= '<div class="ui-card -patient -sg-flex -co-2"></div>';


	// Show visit history
	$ret .= '<div id="imed-my-note-load" class="sg-load" data-url="'.url('imed/visits').'">'._NL;
	$ret .= '<div id="imed-my-note" class="loader -rotate" style="margin: 48px auto; display: block;"></div>';
	$ret .= '</div><!-- imed-my-note -->';

	//$ret .= R::Page('imed.app.visit.owner',$self);

	$ret .= '</div>';

	$ret .= '<style type="text/css">
	.module-imed.-app .form.imed-search-patient {padding-top: 0px; background-color: #fff;}
	.module-imed.-app .form.imed-search-patient .btn.-primary {border-radius: 50%; margin-left: 16px; background-color: green; box-shadow: 0 0 0 green inset;}
	</style>';

	$headerScript = '<script type="text/javascript">
	var canQuery = true
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

	return $ret;
}
?>