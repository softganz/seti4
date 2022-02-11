<?php
/**
* Green :: App Home Pahe
* Created 2019-10-27
* Modify  2019-10-27
*
* @param Object $self
* @return String
*/

$debug = true;

function green_app($self) {
	$isDevVersion = true; //in_array(i()->username, explode(',',cfg('green.useDevVersion')));
	$isShowAll = true;

	$ret = '';

	if (post('u')) $isShowAll = false;

	//$ret .= print_o(R()->appAgent, '$appAgent');
	$lastVersion = '0.20.06';
	$updatePlayStoreUrl = "https://play.app.goo.gl/?target=browser&link=https://play.google.com/store/apps/details?id=com.softganz.green";
	if (R()->appAgent->OS == 'Android' && R()->appAgent->ver < $lastVersion) {
	//if (i()->username == 'softganz') {
		//$ret .= R()->appAgent->OS == 'Android' ? 'Yes Android': 'Not Android';
		//$ret .= R()->appAgent->ver == '0.1.12' ? 'Yes 0.1.12': 'Not 0.1.12';
		//$ret .= gettype(R()->appAgent->ver);
		$ret .= '<div class="notify" style="padding: 24px; text-align: center; xbackground-color: #fff;">'
			. '<p>เนื่องจากมีการอัพเดทแอพเป็นรุ่นใหม่ ขอให้ทุกท่านอัพเดทแอพเป็นรุ่นล่าสุดเพื่อให้สามารถใช้งานคุณสมบัติใหม่ๆ ได้</p>'
			. '<a class="sg-action btn -primary" href="'.$updatePlayStoreUrl.'" data-webview="browser">ดำเนินการอัพเดทแอพ</a>'
			. '<p>New ver is '.$lastVersion.' current ver '.R()->appAgent->ver.'</p>'
			. '</div>';
	}

	//for ($i=0;$i<=100;$i++) {
	//	$ret .= R::On('ibuy.activity.create', $i+470);
	//}

	//R::View('org.toolbar',$self,'Hatyai Go Green');
	//unset($self->theme->toolbar, $self->theme->title);

	/*
	$ret .= '<p style="margin: 32px;"><a class="btn" href="javascript:void(0)" onclick="showAndroidToast(\'Hello Android!\')">Click for android</a></p>
	<script type="text/javascript">
	function showAndroidToast(toast) {
		//notify(typeof Android + toast)
		//alert(Android)
		//Android.showToast(toast)
		if (typeof Android=="object")
			Android.showToast(toast);
		else
			console.log("App not run on Android")
	}
	</script>';
	*/

/*
	if (is_admin() || date('Y-m-d H') >= '2019-12-13 07') {
		$ret .= '<section class="hygreensmile"><a class="sg-action" href="{url:paper/395}" data-webview="ร่วมสนุกรับของที่ระลึก"><img src="https://communeinfo.com/upload/pics/hygreansmile-join-2.jpg" width="100%" style="display: block; margin: 0 auto; max-width: 600px;" /><br />ร่วมสนุกรับของที่ระลึกตลาดหาดใหญ่กรีนสมาย</a></section>';
	}

	$ret .= '<div class="-sg-text-center" style="padding: 24px;">';
	if (i()->username == 'softganz') $ret .= '<a class="btn" href="javascript:void(0)" onclick=\'eraseCookie("hygreensmile")\'>Remove cookie</a> ';
	if (is_admin()) {
		$ret .= '<a class="btn" href="'.url('green/walkin/list').'">รายการลงทะเบียน</a>';
	}
	$ret .= '</div>';
	*/

	//$ret .= '<div class="sg-load" data-url="'.url('green/news').'"></div>';

	if ($isShowAll) $ret .= R::Page('green.news', NULL);

	$toolbar = new Toolbar(NULL);

	// Show main navigator
	$ui = new Ui(NULL, 'ui-nav');
	$ui->add('<a class="sg-action" href="'.url('green/goods').'" data-webview="ร้านค้า"><i class="icon -material">account_balance</i><span>ร้านค้า</span></a>');
	$ui->add('<a class="sg-action" href="'.url('green/group').'" data-webview="กลุ่ม"><i class="icon -material">people</i><span>กลุ่ม</span></a>');
	//$ui->add('<a class="sg-action" href="'.url('green/goods').'" data-webview="สินค้า"><i class="icon -material">local_florist</i><span>สินค้า</span></a>');
	//$ui->add('<a class="sg-action" href="'.url('green/plant').'" data-webview="จองผลผลิต"><i class="icon -material">done_all</i><span>จอง</span></a>');
	$ui->add('<a class="sg-action" href="'.url('green/follow').'" data-webview="ติดตาม"><i class="icon -material">star</i><span>ติดตาม</span></a>');
	$ui->add('<a class="sg-action" href="'.url('green/app/article').'" data-webview="สาระเกษตรอินทรีย์"><i class="icon -material">movie</i><span>สาระ</span></a>');

	//$ui->add('<a class="sg-action" href="'.url('green/category').'" data-webview="หมวด"><i class="icon -material">view_module</i><span>หมวด</span></a>');

	if (_DOMAIN_SHORT == 'localhost') {
		$ui->add('<a href="'.url('green/my').'"><i class="icon -material">account_circle</i><span>My</span></a>');
	}

	$toolbar->addNav('main', $ui);

	if ($isShowAll) {
		//$ret .= '<nav class="nav -page -app-icon">'.$ui->build().'</nav>'._NL;
		$ret .= $toolbar->build();
	}



	// Show Activity Post Button
	$ret .= '<div id="green-chat-box" class="ui-card green-chat-box">'
		. '<div class="ui-item">'
		// . '<header class="header"><h3>เขียนบันทึก</h3></header>'
		. '<div><img src="'.model::user_photo(i()->username).'" width="40" height="40" />&nbsp;'
		. '<a class="sg-action form-text" href="'.url('green/activity/form').'" placeholder="เขียนบันทึกการทำกิจกรรม" data-rel="box" data-width="480" data-height="640" data-webview="บันทึกการทำกิจกรรม">เขียนบันทึกการทำกิจกรรม</a>&nbsp;'
		. '<a class="sg-action btn -link" href="'.url('green/activity/form').'" data-rel="box" data-width="480" data-height="640" data-webview="บันทึกการทำกิจกรรม"><i class="icon -camera"></i><span>Photo</span></a></div>'
		. '</div>'
		. '</div>';


	if (false) {
		$ui = new Ui(NULL, 'ui-menu');
		$ui->addConfig('nav', '{class: "nav -sg-text-center", style: "padding: 32px; background: #fff; margin: 16px; border-radius: 8px;"}');
		$ui->add('<a class="sg-action" href="'.url('green/my/tree').'" data-webview="Tree Bank" data-permission="CAMERA">TREE BANK in WEBVIEW</a>');
		$ui->add('<a class="sg-action" href="'.url('green/land/68/map',array('options:fullpage,notoolbar'=>'')).'" data-webview="Tree Bank" data-options=\'{refresh: false, permission: "ACCESS_FINE_LOCATION"}\'>MAP</a>');
		$ui->add('<a class="sg-action" href="'.url('green/my/tree').'" data-rel="box">TREE BANK in BOX</a>');
		$ui->add('<a href="'.url('green/my/tree').'">TREE BANK in this View</a>');
		$ret .= $ui->build();
	}

	//$ret .= R::Page('green.activity', NULL);

	// Show visit history
	$ret .= '<section id="green-activity-card" class="sg-load" data-url="'.url('green/activity', array('u' => post('u'))).'">'._NL;
	$ret .= '<div class="loader -rotate" style="width: 64px; height: 64px; margin: 32px auto; display: block;"></div>';
	$ret .= '</section><!-- imed-my-note -->';



	if ($isShowAll) {
		// Show Land With Proof

		/*
		$stmt = 'SELECT
			l.*
			, u.`username`
			, u.`name` `posterName`
			FROM %ibuy_farmland% l
				LEFT JOIN %users% u USING(`uid`)
			WHERE l.`approved` IN ("ApproveWithCondition", "Approve")
			ORDER BY FIELD(l.`approved`, "ApproveWithCondition", "Approve") DESC, `landid` DESC';

		$dbs = mydb::select($stmt);

		$ret .= '<section>'._NL;

		$ret .= '<header class="header"><h3 style="font-family: Prompt; font-size: 1.4em; text-align: center;">แปลงการผลิตที่ผ่านมาตรฐาน</h3></header>';
		
		$landCard = new Ui('div', 'ui-card');

		foreach ($dbs->items as $rs) {
			$headerNav = new Ui();
			$headerNav->addConfig('nav', '{class: "nav -header"}');

			$headerNav->add('<a class="sg-action btn'.($rs->approved == 'Approve' ? ' -success' : '').'" href="'.url('green/land/'.$rs->landid).'" data-webview="'.htmlspecialchars($rs->landname).'">'
				. (in_array($rs->approved, array('Approve', 'ApproveWithCondition')) ? '<i class="icon -material">'.($rs->approved == 'Approve' ? 'done_all' : 'done').'</i>' : '')
				. '<span>'.SG\getFirst($rs->standard,'NONE').'</span></a>'
			);

			$headerNav->add('<a class="sg-action btn -link" href="'.url('green/land/'.$rs->landid.'/map', array('options:fullpage,notoolbar'=>'')).'" data-rel="box" data-width="640" data-class-name="-map" data-webview="แผนที่แปลงผลิต" data-options=\'{refresh: false}\'><i class="icon -material -land-map'.($rs->location ? ' -active' : '').'">where_to_vote</i><span class="-hidden">แผนที่</span></a>');

			$landCard->add(
				'<div class="header">'
				. '<span class="profile"><img class="poster-photo -sg-32" src="'.model::user_photo($rs->username).'" width="24" height="24" alt="" />'
				. '<h3>'.$rs->landname.'</h3>'
				. '<span class="poster-name">By '.$rs->username.'</span>'
				. '</span><!-- profile -->'
				. $headerNav->build()
				. '</div><!-- header -->'._NL
				. '<div class="detail">'
				. '<p>พื้นที่ '
				. ($rs->arearai > 0 ? $rs->arearai.' ไร่ ' : '')
				. ($rs->areahan > 0 ? $rs->areahan.' งาน ' : '')
				. ($rs->areawa > 0 ? $rs->areawa.' ตารางวา' : '')
				. ($rs->approved ? 'มาตรฐาน '.$rs->standard.' ( '.$rs->approved.' )<br />'._NL : '')
				. 'ประเภทผลผลิต '.$rs->producttype.'</p>'
				//. print_o($rs, '$rs')
				. '</div><!-- detail -->'._NL,
				'{class: "sg-action", href: "'.url('green/land/'.$rs->landid).'", "data-webview": "'.htmlspecialchars($rs->landname).'", onclick: ""}'
			);
		}

		$ret .= $landCard->build();
		*/



		// Show Green Goods
		//$ret .= '<div class="sg-load" data-url="'.url('green/goods').'"></div>';




		/*
		$qrCode = SG\qrcode('/paper/395','{width: 512, height: 512, domain: "https://communeinfo.com", imgWidth: "200px", imgHeight: "200px"}');

		$ret .= '<div class="qrcode" style="margin: 128px auto; padding: 16px;color:red; font-weight: bold";">'
			. $qrCode.'<br />'
			. '<a class="btn -link" href="'.url('paper/395').'" target="_blank">ร่วมสนุกรับของที่ระลึกตลาดหาดใหญ่กรีนสมาย สำหรับ iOS, iPhone</a><br />'
			. '</div>';
		*/
	}

	head('<style type="text/css">
	.hygreensmile {padding: 0 0 16px 0; text-align: center; margin: 0 auto; background-color: #f60;}
	.hygreensmile>a {padding:0; font-size: 1.1em; margin: 0; width: 100%; border-radius: 0; color: #fff;}
	@media (min-width:30em){ /* 480/16 = 30 */
		.hygreensmile {background-color: #fff;}
		.hygreensmile>a {color: #333;}
	}
	</style>');

	$headerScript = '<script type="text/javascript">
	var canQuery = true

	$(document).on("click", ".btn.-green-plant-down", function() {
		var $qty = $(this).closest(".form-item").find(".form-text")
		if ($qty.val() > 1) {
			$qty.val($qty.val() - 1)
		}
	});

	$(document).on("click", ".btn.-green-plant-up", function() {
		var $qty = $(this).closest(".form-item").find(".form-text")
		if (parseFloat($qty.val()) < $qty.data("balance")) {
			$qty.val(parseInt($qty.val())	+ 1)
		}
	});

	$(document).on("keydown", ".-comment-form .form-textarea", function(event) {
		console.log("event.keyCode",event.keyCode)
		if (event.keyCode == 13 && event.ctrlKey) {
			console.log("CTRL+ENTER")
			$(this).height("+=1.6em")
			$(this).val($(this).val()+"\r\n")
		} else if(event.keyCode == 13) {
			console.log("SAVE")
			$(this).closest("form").submit()
			$(this).val("").height("1em")
			return false
		}
	});

	function greenMsgLikeDone($this, data) {
		console.log(data)
		if (data.liked) {
			$this.closest(".btn").removeClass("-inactive").addClass("-active")
		} else {
			$this.closest(".btn").removeClass("-active").addClass("-inactive")
		}

		if (data.liketimes) {
			$this.closest(".ui-item.-green-activity").find(".liketimes").text(data.liketimes)
			$this.closest(".ui-item.-green-activity").find(".-like-status").removeClass("-hidden")
		} else {
			$this.closest(".ui-item.-green-activity").find(".-like-status").addClass("-hidden")
		}
	}
	';

	if (cfg('firebase')) {
		$headerScript .= '
		$(document).ready(function() {
			if (!firebaseConfig) return

			var database = firebase.database()
			var ref = database.ref(firebaseConfig.msg)
			var drawUrl = "'.url('green/activity/render').'/"
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
				if (snap.val().changed == "new") {
					$.post(drawUrl + snap.val().seq, function(html) {
						console.log(html)
						if (html) {
							var visitBox = $("#green-activity")
							visitBox.prepend(html)
						}
					})
					console.log("ADD #" + (++i) + " : " + snap.key, snap.val())
				}
			})

			ref
			.on("child_changed",function(snap) {
				if (snap.val().changed == "delete") {
					$("#green-activity-"+snap.key).remove()
					console.log("REMOVE #",++i + " : " + snap.key, snap.val())
				} else {
					$.post(drawUrl + snap.key, function(html) {
						$("#green-activity-"+snap.key).replaceWith(html)
					})
					console.log("CHANGE #" + (++i) + " : " + snap.key, snap.val())
				}
			})
		})';
	}

	$headerScript .= '</script>'._NL;

	head($headerScript);

	return $ret;
}
?>