<?php
/**
* Home page of package imed
*
* @return String
*/
function imed($self, $uid = NULL) {
	$psnId = post('pid');

	$isAdmin = user_access('administer imeds');
	$userZone = imed_model::get_user_zone(i()->uid,'imed');

	/*
	// Just sign in
	if (i()->ok && $_REQUEST['username'] && $_REQUEST['password']) {
		$ret.='เข้าสู่ระบบสมาชิกเรียบร้อย <a href="'.url('imed').'">กรุณาคลิกที่นี่ เพื่อเข้าสู่หน้าหลักของ iMed@Home</a>';
		if (_AJAX) return $ret; else location('imed');
	} else if (user_access('administrator imeds')) {
			// Admin : Do nothing
	} else if (i()->ok) {
		$userZone=imed_model::get_user_zone(i()->uid);
		if (!$userZone) {
			//return message('error','ขออภัย ท่านยังไม่ได้รับการกำหนดพื้นที่ในการดูแล กรุณาติดต่อผู้ดูแลระบบเพื่อขอสิทธิ์ในการเข้าใช้งาน');
		}
	} else {
		// Show signin form
		return R::View('signform');
	}
	*/

	$ret .= R::View('imed.toolbox',$self,'iMed@Home');

	if (empty($uid)) $uid = i()->uid;
	$owner = mydb::select('SELECT * FROM %users% WHERE uid = :uid LIMIT 1',':uid',$uid);





	// Total home visit
	$times->total = mydb::select('SELECT COUNT(*) total FROM %imed_service% LIMIT 1')->total;
	$monthStart = date('U', mktime(0,0,0,date('m'),1,date('Y')));
	$monthEnd = date('U', mktime(0,0,0,date('m')+1,0,date('Y')));
	$times->month = mydb::select('SELECT COUNT(*) total FROM %imed_service% WHERE `created` BETWEEN :start AND :end LIMIT 1',':start',$monthStart,':end',$monthEnd)->total;

	$zoneStr = '';
	foreach ($userZone as $zone) {
		$zoneStr .= '<li>'.SG\implode_address($zone,'short').'('.$zone->right.')</li>';
	}

	$ret .= '<div id="imed-side-status" class="imed-sidebar -status -no-print">';
	$ret .= '<div class="menu-mobile">'.R::View('imed.menu.main')->build().'</div>';

	$ret.='<div id="imed-my-status" class="imed-my-status">
	<p style="margin:0; padding:4px;background:#ffdbcf;text-align:center;">มีปัญหาการใช้งาน กรุณาแจ้งได้ที่อีเมล์ <strong>softganz@yahoo.com</strong></p>
	<p style="margin:0;padding:16px;background:#e1ffad;">ข่าวดีสำหรับเครือข่ายเยี่ยมบ้านคนพิการ/ผู้สูงอายุ/ผู้ป่วยติดเตียง/คนยากลำบาก มูลนิธิชุมชนสงขลาได้พัฒนาแอพพลิเคชั่น iMed@home <b>สำหรับทีมงานที่ใช้สมาร์ทโฟนระบบ Android</b> เพื่อความสะดวกใน<b>การบันทึกข้อมูลการเยี่ยมบ้านผ่านสมาร์ทโฟน</b> ท่านสามารถโหลดไปลองใช้งาน แอพอยู่ที่ <a href="https://play.google.com/store/apps/details?id=com.softganz.imedhome" target="_blank">Google Play</a> หรือเปิด Play Store จากโทรศัพท์แล้วใช้คำค้น <b>iMed@Home</b> หลังจากติดตั้งเรียบร้อยให้เข้าสู่ระบบสมาชิกด้วยชื่อสมาชิกที่ใช้งานอยู่บนเว็บไซท์ตามปกติ ส่วนระบบ iOS ทีมงานกำลังอยู่ระหว่างการพัฒนาระบบ รอพบกันในอีกไม่นาน</p>
	<a href="https://play.google.com/store/apps/details?id=com.softganz.imedhome" target="_blank"><img src="//communeinfo.com/upload/pics/appimedathome-poster1.jpg" width="100%" alt="iMed@Home App" /></a>
	<p align="center">เดือนนี้มีการเยี่ยมบ้านไปแล้ว <strong style="font-size:20pt;">'.$times->month.'</strong> ครั้ง<br />
	การเยี่ยมบ้านทั้งหมด <strong style="font-size:20pt;">'.$times->total.'</strong> ครั้ง</p>'._NL
	. '<h4>พื้นที่รับผิดชอบ</h4><ul>'.($zoneStr?$zoneStr:'ไม่กำหนดพื้นที่').'</ul>'._NL;

	$qrCodeWebApp = SG\qrcode('imed/m','{width: 512, height: 512, domain: "https://communeinfo.com/", imgWidth: "200px", imgHeight: "200px"}');

	$qrCodePlayStore = SG\qrcode('store/apps/details?id=com.softganz.imedhome','{width: 512, height: 512, domain: "https://play.google.com/", imgWidth: "200px", imgHeight: "200px"}');

	$qrCard = new Ui(NULL, 'ui-card');
	$qrCard->add(
		'<p class="-sg-text-center">เว็บแอพพลิเคชั่น</p>'
		. '<div class="qrcode">'
		. $qrCodeWebApp.'<br />'
		. '</div>',
		'{style: "margin: 64px 0 128px 0;"}'
	);

	$qrCard->add('<p class="-sg-text-center">ดาวน์โหลดแอพพลิเคชั่นจาก Google Play Store</p>'
		. '<div class="qrcode">'
		. $qrCodePlayStore.'<br />'
		. '</div>',
		'{style: "margin: 64px 0 128px 0;"}'
	);

	$ret .= $qrCard->build();

	$ret .= '</div><!-- imed-my-status -->'._NL;

	$ret.='</div><!-- imed-side-status -->'._NL;

	$ret.='<!-- Change id from imed-main-status to imed-app -->'._NL;
	$ret.='<div id="imed-app" class="imed-app">'._NL;

	/*
	if (i()->username == 'softganz') {
		$ret .= print_o(post(),'post()');
		$ret .= '<script>
		$(document).ready(function() {notify($("body").attr("class"))})</script>';
	}
	*/

	if (post('fq')) {
		// $ret.='<h3>Find friend</h3>';
		// $stmt='SELECT * FROM %users% WHERE `username` LIKE :q OR `name` LIKE :q OR `email` LIKE :q';
		// $frdb=mydb::select($stmt,':q','%'.$_REQUEST['fq'].'%');
		// $ret.='<ul>';
		// foreach ($frdb->items as $frs) {
		// 	$ret.='<li class="clear-fix"><a class="sg-action" href="'.url('imed/u/'.$frs->uid).'" tooltip-uri="'.url('imed/u/'.$frs->uid).'" data-rel="box"><img class="searchNamePhoto" src="'.model::user_photo($frs->username).'" width="75" height="75" alt="" /><span class="noteUnitName">'.$frs->name.'</a></span></li>';
		// }
		// $ret.='</ul>';
	} else if ($psnId) {
		$ret.='<script type="text/javascript">
		$(document).ready(function() {
			initPatient('.$psnId.');
		});
		</script>';
	} else {
		mydb::query('SET @@group_concat_max_len = 4096;');

		$ret.='<h3>Welcome home '.$owner->name.'</h3>'._NL;

		// Show visit history
		$ret .= '<div class="sg-load" data-url="'.url('imed/visits', ['ref' => 'web']).'" data-replace="true">'._NL
			. '<div class="loader -rotate" style="width: 64px; height: 64px; margin: 48px auto; display: block;"></div>'
			. '</div><!-- imed-my-note -->';

		// if ($isAdmin) {
		// 	// Get all record
		// } else  if ($userZone) {
		// 	mydb::where('(s.`uid` = :uid OR p.`uid` = :uid OR '.R::Model('imed.person.zone.condition',$userZone).')', ':uid', $uid);
		// } else if (i()->ok) {
		// 	mydb::where('s.`uid` = :uid OR p.`uid` = :uid', ':uid', $uid);
		// } else {
		// 	mydb::where('false');
		// }



		// $stmt='SELECT
		// 	  s.`pid` `psnid`, s.*
		// 	, u.`username`, u.`name`
		// 	, p.`prename`
		// 	, CONCAT(p.`name`," ",p.`lname`) `patient_name`
		// 	, b.`score`
		// 	, q2.`q2_score`, q2.`q9_score`
		// 	, GROUP_CONCAT(CONCAT(`fid`,"|"),`file`) `photos`
		// 	, (SELECT GROUP_CONCAT(`needid`) FROM %imed_need% WHERE `seq` = s.`seq`) `needItems`
		// 	FROM %imed_service% s
		// 		LEFT JOIN %users% u USING (`uid`)
		// 		LEFT JOIN %db_person% p ON p.`psnid`=s.`pid`
		// 		LEFT JOIN %imed_barthel% b USING(`seq`)
		// 		LEFT JOIN %imed_2q9q% q2 USING(`seq`)
		// 		LEFT JOIN %imed_files% f ON f.`seq`=s.`seq` AND f.`type`="photo"
		// 	%WHERE%
		// 	GROUP BY `seq`
		// 	ORDER BY `seq` DESC
		// 	LIMIT 100';

		// $dbs=mydb::select($stmt,':uid',$uid);
		// //$ret.=print_o($dbs,'$dbs');

		// $ui = new Ui('div','ui-card imed-my-note');
		// $ui->addId('imed-my-note');

		// foreach ($dbs->items as $rs) {
		// 	$ui->add(R::View('imed.visit.render',$rs), '{class: "", id: "noteUnit-'.$rs->seq.'"}');
		// }
		// $ret .= $ui->build().'<!-- imed-my-note -->';
	}

	$ret.='</div><!-- imed-app -->'._NL;
	return $ret;
}
?>