<?php
/**
 * Resaler register
 *
 * @return String
 */

import('model:user.php');

function ibuy_resaler_register($self) {
	$self->theme->title='สมัครตัวแทนจำหน่าย';
	if ($_POST['cancel']) location();
	$post=(object)post('register',_TRIM+_STRIPTAG);
	$authcode=$_GET['authcode'];
	if ($_POST['request']) {
		$post=(object)$_POST;
	} else if (cfg('ibuy.resaler.register.check') && empty($post->wuid)) {
		$ret.=cfg('ibuy.resaler.register.check');
		return $ret;
	} else if ($_POST['confirm']) {
		if (!user_access('administer ibuys')) return message('error','Access denied');
		$self->theme->title='ยืนยันการสมัครเป็นตัวแทนจำหน่าย';
		mydb::query('UPDATE %users% SET `status`="enable" , `roles`="resaler" , `code`=NULL WHERE uid=:uid LIMIT 1',':uid',$_POST['uid']);
		$ret.=message('status','ยืนยันการสมัครสมาชิกเป็นตัวแทนจำหน่ายเรียบร้อย');
		return $ret;
	} else if ($_POST['remove']) {
		if (!user_access('administer ibuys')) return message('error','Access denied');
		$self->theme->title='ลบข้อมูลการสมัครเป็นตัวแทนจำหน่าย';
		mydb::query('DELETE FROM %users% WHERE `uid`=:uid LIMIT 1',':uid',$_POST['uid']);
		mydb::query('DELETE FROM %ibuy_customer% WHERE `uid`=:uid LIMIT 1',':uid',$_POST['uid']);
		$ret.=message('status','ลบข้อมูลการสมัครสมาชิกเป็นตัวแทนจำหน่ายเรียบร้อย');
		return $ret;
	} else if ($_POST['save']) {
		$error=false;
		if (empty($post->username)) $error[]='กรุณาป้อน ชื่อสมาชิก (Username)';
		if (strlen($post->username)<4) $error[]='ชื่อสมาชิก (Username) อย่างน้อย 4 อักษร'; //-- username length
		if (!preg_match(cfg('member.username.format'),$post->username)) $error[]='ชื่อสมาชิก (Username) <strong><em>'.$post->username.'</em></strong> มีอักษรที่ไม่ถูกต้อง'; //-- check valid char
		if (db_count('%users%','username="'.$post->username.'"') ) $error[]='ชื่อสมาชิก (Username) <strong><em>'.$post->username.'</em></strong> มีผู้อื่นใช้ไปแล้ว'; //-- duplicate username
		if ($post->password=='') $error[]='กรุณาระบุ รหัสผ่าน (Password)'; //-- fill password
		if ($post->password && strlen($post->password)<6) $error[]='รหัสผ่าน (Password) ต้องยาวอย่างน้อย 6 อักษร'; //-- password length
		if ($post->password && $post->password != $post->repassword) $error[]='กรุณายืนยันรหัสผ่าน (Re-enter password) ให้เหมือนกันรหัสที่ป้อน'; //-- password <> retype
		if ($post->name=='') $error[]='กรุณาป้อน ชื่อสำหรับแสดง (Name)'; //-- fill name
		if ( db_count('%users%','name="'.addslashes($post->name).'"') ) $error[]='ชื่อ <strong><em>'.$post->name.'</em></strong> มีผู้อื่นใช้ไปแล้ว'; //-- duplicate name
		if ( $post->email=='') $error[]='กรุณาป้อน อีเมล์ (E-mail)'; //-- fill email
		if ($post->email && !sg_is_email($post->email)) $error[]='อีเมล์ (E-mail) ไม่ถูกต้อง'; //-- invalid email
		if ($post->custname=='') $error[]='กรุณาป้อน ชื่อร้าน (Shop name)'; //-- fill custname
		if ($post->pid=='-1') $error[]='กรุณาป้อน จังหวัด (Province)'; //-- fill province
		if ($post->custaddress=='') $error[]='กรุณาป้อน สถานที่ตั้งร้าน (Location)'; //-- fill custaddress
		if ($post->custzip=='') $error[]='กรุณาป้อน รหัสไปรษณีย์ (ZIP code)'; //-- fill custzip
		if ($post->custphone=='') $error[]='กรุณาป้อน โทรศัพท์ (Phone)'; //-- fill custphone
		if (empty($_POST['condition'])) $error[]='กรุณายืนยันการอ่านเงื่อนไขการสมัคร'; //-- fill condition

		// start saving new account
		if (!$error && $_POST['save']) {
			load_module('class.user.php');
			// Create user information
			$result = UserModel::create($post);

			// Create resaler information
			$ret.=__ibuy_resaler_create($post);

			unset($GLOBALS['counter']->members);
			CounterModel::make(cfg('counter'));
			cfg_db('counter',$GLOBALS['counter']);

			if (user_access('administer ibuys')) {
				location('ibuy/franchise/'.$post->username);
				return $ret;
			} else {
				R()->user = UserModel::signInProcess($post->username,$post->repassword);
				$ret.=message('status','บันทึกข้อมูลการสมัครเป็นตัวแทนจำหน่ายเรียบร้อย');
				location('ibuy/franchise/'.$post->username);
				return $ret;
			}
		}
	}
	if ($error) $ret .= message('error',$error);
	$ret.=__ibuy_resaler_register_form($post);
	return $ret;
}

/**
 * Create resaler information
 *
 * @param Object $post
 * @return String
 */
function __ibuy_resaler_create($post=array()) {
	// Create franchise information
	$post->wuid=$post->wuid?$post->wuid:'func.NULL';
	$defaultClass=cfg('ibuy.resaler.register.class');
	$post->custtype=$defaultClass?$defaultClass : ($defaultClass===''?'':'resaler');
	$stmt='INSERT INTO %ibuy_customer%
					(`uid` , `wuid` , `custtype` , `custname` , `custattn` , `pid` , `custaddress` , `custzip` , `custphone` , `custlicense` ,`latlng`)
				VALUES
					(:uid , :wuid , :custtype , :custname , :custattn , :pid , :custaddress , :custzip , :custphone , :custlicense , :latlng )';
	mydb::query($stmt,$post);

	// Change user roles to resaler
	if ($post->custtype=='resaler') {
		mydb::query('UPDATE %users% SET `roles`="resaler" WHERE `uid`=:uid LIMIT 1',':uid',$post->uid);
	}
	return $ret;
}

/**
 * Generate resaler register form
 *
 * @param Object $post
 * @return String
 */
function __ibuy_resaler_register_form($post=null) {
	$form->config->variable='register';
	$form->config->method='post';
	$form->config->action=url(q());


	$form->wuid->type='hidden';
	$form->wuid->value=$post->wuid;

	$form->user_info_s = '<fieldset><legend>ข้อมูลสมาชิก (Account information)</legend>';

	$form->username->type='text';
	$form->username->label=sg_client_convert('ชื่อสมาชิก ( Username )');
	$form->username->maxlength=15;
	$form->username->size=20;
	$form->username->require=true;
	$form->username->value=htmlspecialchars($post->username);
	$form->username->description=sg_client_convert('<ul><li><strong>ชื่อสมาชิก (username)</strong> เป็นชื่อสำหรับใช้ในการ <strong>sign in</strong> เข้าสู่ระบบสมาชิก</li><li>ขนาดความยาว <strong>4-15</strong> ตัวอักษร</li><li>ชื่อสมาชิกต้องเป็นตัวอักษร '.cfg('member.username.format_text').' เท่านั้น</li><li>ห้ามมีการเว้นวรรคอย่างเด็ดขาด</li></ul>');

	$form->password->type='password';
	$form->password->label=sg_client_convert('รหัสผ่าน ( Password )');
	$form->password->maxlength=30;
	$form->password->size=20;
	$form->password->require=true;
	$form->password->value=htmlspecialchars($post->password);
	$form->password->description=sg_client_convert('รหัสผ่านต้องมีความยาวอย่างน้อย <strong>6 ตัวอักษร</strong>');

	$form->repassword->type='password';
	$form->repassword->label=sg_client_convert('ยืนยันรหัสผ่าน ( Re-enter Password )');
	$form->repassword->maxlength=30;
	$form->repassword->size=20;
	$form->repassword->require=true;
	$form->repassword->value=htmlspecialchars($post->repassword);
	$form->repassword->description=sg_client_convert('ยืนยันรหัสผ่านอีกครั้งเพื่อความถูกต้องของการป้อนรหัสผ่าน');

	$form->user_info_e = '</fieldset>';

	$form->member_info_s = '<fieldset><legend>ข้อมูลส่วนบุคคล (Personal information)</legend>';

	$form->name->type='text';
	$form->name->label=sg_client_convert('ชื่อสำหรับแสดง ( Name )');
	$form->name->maxlength=50;
	$form->name->size=50;
	$form->name->require=true;
	$form->name->value=htmlspecialchars($post->name);
	$form->name->description=sg_client_convert('<strong>ชื่อสำหรับแสดง ( Name )</strong> เป็นชื่อที่จะนำไปแสดงในหน้าเว็บไซท์ เมื่อท่านส่งหัวข้อหรือแสดงความคิดเห็น (ท่านสามารถใช้ชื่อย่อ หรือชื่อเล่น หรือสมญานามอื่นๆ ได้)');

	$form->email->type='text';
	$form->email->label=sg_client_convert('อีเมล์ ( E-Mail )');
	$form->email->maxlength=50;
	$form->email->size=50;
	$form->email->require=true;
	$form->email->value=htmlspecialchars($post->email);
	$form->email->description=sg_client_convert('<ul><li>กรุณาป้อนอี-เมล์ของท่านให้ถูกต้อง ทางเว็บไซท์จะไม่มีการแสดงอีเมล์นี้ของท่านในหน้าเว็บไซท์ แต่จะใช้ในกรณีดังต่อไปนี้<ol><li>ท่านลืมรหัสผ่าน ระบบจะส่งรหัสผ่านไปให้ท่านตามอีเมล์ที่ระบุนี้</li><li>มีการติดต่อจากแบบฟอร์มที่ให้กรอกในหน้าเว็บไซท์เพื่อส่งถึงท่าน</li></ol></li>').'
	'.(cfg('member.registration.method')==='email' ? sg_client_convert('<li><strong>เมื่อท่านลงทะเบียนเรียบร้อย เราจะส่งอี-เมล์ถึงท่าน ตามอี-เมล์ที่ท่านระบุ และท่านจะต้องทำการยืนยันการเป็นสมาชิก การสมัครสมาชิกจึงจะสมบูรณ์</strong></li>'):'').'</ul>';

	$form->member_info_e = '</fieldset>';

	$form->shop_info_s = '<fieldset><legend>ข้อมูลร้านค้า (Shop information)</legend>';

	$form->custname->type='text';
	$form->custname->label='ชื่อร้าน (Shop name)';
	$form->custname->maxlength=100;
	$form->custname->size=50;
	$form->custname->require=true;
	$form->custname->value=htmlspecialchars($post->custname);

	$form->custattn->type='text';
	$form->custattn->label='ชื่อผู้ติดต่อ (Attention)';
	$form->custattn->maxlength=100;
	$form->custattn->size=50;
	$form->custattn->require=true;
	$form->custattn->value=htmlspecialchars($post->custattn);

	$form->pid->type='textfield';
	$form->pid->label='จังหวัด (Province)';
	$form->pid->require=true;
	$form->pid->value='<select name="register[pid]" id="province">
<!-- มอก. 1099-2535 รหัสจังหวัดเพื่อการแลกเปลี่ยนข้อมูล
TIS 1099-2535 Standard for Province Identification Codes for Data Interchange
http://www.nectec.or.th/it-standards/std1099/std1099.htm
-->
<option value="-1" selected="selected">(เลือกจังหวัด)</option>
<option value="10">กรุงเทพมหานคร</option>
<optgroup label="ภาคเหนือ">
<option value="57">เชียงราย</option>
<option value="50">เชียงใหม่</option>
<option value="55">น่าน</option>
<option value="56">พะเยา</option>
<option value="54">แพร่</option>
<option value="58">แม่ฮ่องสอน</option>
<option value="52">ลำปาง</option>
<option value="51">ลำพูน</option>
<option value="64">สุโขทัย</option>
<option value="53">อุตรดิตถ์</option>
</optgroup>
<optgroup label="ภาคตะวันออกเฉียงเหนือ">
<option value="46">กาฬสินธุ์</option>
<option value="40">ขอนแก่น</option>
<option value="36">ชัยภูมิ</option>
<option value="48">นครพนม</option>
<option value="30">นครราชสีมา</option>
<option value="31">บุรีรัมย์</option>
<option value="44">มหาสารคาม</option>
<option value="49">มุกดาหาร</option>
<option value="35">ยโสธร</option>
<option value="45">ร้อยเอ็ด</option>
<option value="42">เลย</option>
<option value="47">สกลนคร</option>
<option value="32">สุรินทร์</option>
<option value="33">ศรีสะเกษ</option>
<option value="43">หนองคาย</option>
<option value="39">หนองบัวลำภู</option>
<option value="41">อุดรธานี</option>
<option value="34">อุบลราชธานี</option>
<option value="37">อำนาจเจริญ</option>
</optgroup>
<optgroup label="ภาคกลาง">
<option value="62">กำแพงเพชร</option>
<option value="18">ชัยนาท</option>
<option value="26">นครนายก</option>
<option value="73">นครปฐม</option>
<option value="60">นครสวรรค์</option>
<option value="12">นนทบุรี</option>
<option value="13">ปทุมธานี</option>
<option value="14">พระนครศรีอยุธยา</option>
<option value="66">พิจิตร</option>
<option value="65">พิษณุโลก</option>
<option value="67">เพชรบูรณ์</option>
<option value="16">ลพบุรี</option>
<option value="11">สมุทรปราการ</option>
<option value="75">สมุทรสงคราม</option>
<option value="74">สมุทรสาคร</option>
<option value="19">สระบุรี</option>
<option value="17">สิงห์บุรี</option>
<option value="72">สุพรรณบุรี</option>
<option value="15">อ่างทอง</option>
<option value="61">อุทัยธานี</option>
</optgroup>
<optgroup label="ภาคตะวันออก">
<option value="22">จันทบุรี</option>
<option value="24">ฉะเชิงเทรา</option>
<option value="20">ชลบุรี</option>
<option value="23">ตราด</option>
<option value="25">ปราจีนบุรี</option>
<option value="21">ระยอง</option>
<option value="27">สระแก้ว</option>
</optgroup>
<optgroup label="ภาคตะวันตก">
<option value="71">กาญจนบุรี</option>
<option value="63">ตาก</option>
<option value="77">ประจวบคีรีขันธ์</option>
<option value="76">เพชรบุรี</option>
<option value="70">ราชบุรี</option>
</optgroup>
<optgroup label="ภาคใต้">
<option value="81">กระบี่</option>
<option value="86">ชุมพร</option>
<option value="92">ตรัง</option>
<option value="80">นครศรีธรรมราช</option>
<option value="96">นราธิวาส</option>
<option value="94">ปัตตานี</option>
<option value="82">พังงา</option>
<option value="93">พัทลุง</option>
<option value="83">ภูเก็ต</option>
<option value="95">ยะลา</option>
<option value="85">ระนอง</option>
<option value="90">สงขลา</option>
<option value="91">สตูล</option>
<option value="84">สุราษฎร์ธานี</option>
</optgroup>
</select>';

	$form->custaddress->type='text';
	$form->custaddress->label='สถานที่ตั้งร้าน (Location)';
	$form->custaddress->maxlength=200;
	$form->custaddress->size=50;
	$form->custaddress->require=true;
	$form->custaddress->value=htmlspecialchars($post->custaddress);
	$form->custaddress->description='สถานที่ตั้งร้านจะเป็นสถานที่สำหรับส่งสินค้า ท่านต้องระบุให้ตรงกับความเป็นจริงเพื่อทางเราจะได้จัดส่งสินค้าไปให้ท่านได้ถูกต้องและรวดเร็ว';

	$form->custzip->type='text';
	$form->custzip->label='รหัสไปรษณีย์ (ZIP Code)';
	$form->custzip->maxlength=5;
	$form->custzip->size=20;
	$form->custzip->require=true;
	$form->custzip->value=htmlspecialchars($post->custzip);

	$form->custphone->type='text';
	$form->custphone->label='โทรศัพท์ (Phone)';
	$form->custphone->maxlength=50;
	$form->custphone->size=20;
	$form->custphone->require=true;
	$form->custphone->value=htmlspecialchars($post->custphone);

	$form->custlicense->type='text';
	$form->custlicense->label='ทะเบียนการค้า';
	$form->custlicense->maxlength=50;
	$form->custlicense->size=20;
	$form->custlicense->value=htmlspecialchars($post->custlicense);

	$form->shop_info_e = '</fieldset>';


	$form->latlng->type='text';
	$form->latlng->label='ละติจูด , ลองกิจูด';
	$form->latlng->maxlength=30;
	$form->latlng->size=50;
	$form->latlng->value=htmlspecialchars($post->latlng);
	$form->latlng->description='ตำแหน่งละติจูด-ลองกิจูดบนแผนที่ ตัวอย่าง 7.007977,100.467167 สามารถหาข้อมูลเพิ่มเติมได้จาก <a href="http://maps.google.co.th/maps?hl=th&tab=wl" target="_blank">Google Map</a> หรือจาก<a href="#map">แผนที่ด้านล่าง</a>';


	load_module('class.paper.model.php');
	$condition=paper_model::get_topic_by_id(cfg('ibuy.condition_of_resaler'));

	$form->condition_info_s = '<fieldset><legend>อื่น ๆ (Other information)</legend>';

	$form->condition->label='เงื่อนไขการให้บริการ';
	$form->condition->pretext='<div id="resaler-condition">'.$condition->body.'</div>';
	$form->condition->type='checkbox';
	$form->condition->name='condition';
	$form->condition->options->read='ข้าพเจ้าได้อ่านเงื่อนไขของการเป็นสมาชิกตัวแทนจำหน่าย ตามรายละเอียดด้านบนอย่างถี่ถ้วนแล้ว และยอมรับในเงื่อนไขตามที่ระบุไว้ทุกประการ';
	$form->condition->require=true;
	$form->condition->value=$_POST['condition'];

	$form->submit->type='submit';
	$form->submit->items->save=tr('Register');
	$form->submit->items->cancel=tr('Cancel');

	$form->condition_info_e = '</fieldset>';

	$form->help->type='textfield';
	$form->help->value=sg_client_convert('<strong>หมายเหตุ</strong> กรุณากรอกข้อมูลในช่องที่มีเครื่องหมาย * กํากับอยู่ให้ครบถ้วนสมบูรณ์');

	$ret .= theme('form','edit-register',$form);

	if ($post->latlng) {
		$gis['center']=$post->latlng;
		list($lat,$lnt)=explode(',',$post->latlng);
		$gis['markers'][]=array('latitude'=>$lat,
													'longitude'=>$lnt,
													);
	} else {
		$gis['center']='13.74676363889678,100.50716685742191';
	}
	$gis['zoom']=7;
	$gis['dragable']=true;

	$ret.='<br clear="all" /><p>คลิกบนแผนที่แล้วลากตัวระบุตำแหน่งเพื่อระบุพิกัดของร้าน</p>'._NL.'<div id="map_canvas" width="600" height="600" style="width:100%;height:600px;"></div>'._NL;
	head('<script type="text/javascript" src="/js/jquery.ui.map.js"></script>');
	head('googlemap','<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?language=th&key='.cfg('gmapkey').'"></script>');

	$ret.='<script type="text/javascript"><!--
$(document).ready(function() {
var gis='.json_encode($gis).';
var is_point=false;
$("#map_canvas")
	.gmap({
		center: gis.center,
		zoom: gis.zoom,
		scrollwheel: false
	})
	.bind("init", function(event, map) {
		if (gis.markers) {
			$.each( gis.markers, function(i, marker) {
				$("#map_canvas").gmap("addMarker", {
					position: new google.maps.LatLng(marker.latitude, marker.longitude),
					draggable: gis.dragable,
				}).click(function() {
				$("#map_canvas").gmap("openInfoWindow", { "content": marker.content }, this);
				}).dragend(function(event) {
					var latLng=event.latLng.lat()+","+event.latLng.lng();
					$("#edit-register-latlng").val(latLng);
				});
			});
		} else {
			$(map).click( function(event) {
				if (!is_point) {
					$("#map_canvas").gmap("addMarker", {
						position: event.latLng,
						draggable: true,
						bounds: false
					}, function(map, marker) {
						// After add point
						var latLng=event.latLng.lat()+","+event.latLng.lng();
						$("#edit-register-latlng").val(latLng);
					}).dragend(function(event) {
						var latLng=event.latLng.lat()+","+event.latLng.lng();
						$("#edit-register-latlng").val(latLng);
					});
				}
				is_point=true;
			});
		}
	});
});
--></script>';

	return $ret;
}
?>