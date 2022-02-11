<?php
/**
 * Franchide register
 *
 * @return String
 */

import('model:user.php');

function ibuy_franchise_register($self) {
	$self->theme->title='Franchise Register';
	if ($_POST['cancel']) location();
	$post=(object)post('register',_TRIM+_STRIPTAG);
	$authcode=$_POST['authcode'];
	if (empty($authcode)) {
		if (user_access('administer ibuys')) {
			$authcode=substr(cfg('ibuy.authcode'),0,4);
		} else {
			$ret.='<p><strong>คำเตือน : ผู้ที่จะสมัครเป็นเฟรนไชส์ได้จะต้องได้รับรหัสยืนยันการสมัครจากเจ้าของเฟรนไชส์เท่านั้น</strong></p>
			<p>กรุณาระบุรหัสยืนยันสำหรับลงทะเบียนเฟรนไชส์ใหม่ในช่องด้านล่างให้ถูกต้อง</p>';
			$form->config->method='post';
			$form->config->action=url(q());

			$form->authcode->type='text';
			$form->authcode->name='authcode';
			$form->authcode->label='รหัสยืนยันสำหรับลงทะเบียนเฟรนไชส์ :';
			$form->authcode->size=10;
			$form->authcode->require=true;
			$form->authcode->autocomplete='OFF';

			$form->submit->type='submit';
			$form->submit->items->confirm='ตกลง';
			$form->submit->posttext=' หรือ <a href="'.url().'">ยกเลิก</a>';

			$ret .= theme('form','edit-generatecode',$form);

			$ret.='<p>หากท่านยังไม่พร้อมที่จะสมัครเป็นเฟรนไชส์ ท่านสามารถ <a class="button-register-resaler" href="'.url('ibuy/resaler/register').'">สมัครตัวแทนจำหน่ายสินค้า</a> เพื่อสั่งซื้อสินค้าในราคาสมาชิกไปจำหน่ายในร้านของท่านก็ได้</p>';

			return $ret;
		}
	} else if ($authcode && !in_array($authcode,explode(',',cfg('ibuy.authcode')))) {
		return message('error','รหัสยืนยันสำหรับลงทะเบียนเฟรนไชส์ผิดพลาด กรุณาติดต่อเจ้าของเฟรนไชส์');
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
		if (empty($post->shop_payment)) $error[]='กรุณาป้อน เงื่อนไขการชำระเงิน'; //-- fill payment_method
		if (empty($_POST['condition'])) $error[]='กรุณายืนยันการอ่านเงื่อนไขการสมัคร'; //-- fill condition

		// start saving new account
		if (!$error && $_POST['save']) {
			load_module('class.user.php');
			// Create user information
			$result = UserModel::Create($post);

			// Create franchise information
			$payment=cfg('ibuy.franchise.payment.method');
			list($payment_key,$payment_cond)=explode(':',$post->shop_payment);
			$payment_method=$payment->{$payment_key}->items[$payment_cond];
			$payment_items=explode('/',$payment_method);
			$post->custpaymentmethod=$post->shop_payment.':'.$payment_method;
			$post->custpaymentperiod=count($payment_items);
			$post->shop_payment_total=0;
			foreach ($payment_items as $item) $post->shop_payment_total+=$item;
			unset($post->shop_payment);
			$ret.=__ibuy_ranchise_register_create($post);

			$ret .= message('status','Member register complete');
			unset($GLOBALS['counter']->members);
			CounterModel::make(cfg('counter'));
			cfg_db('counter',$GLOBALS['counter']);

			if (!user_access('administer ibuys')) {
				R()->user = UserModel::signInProcess($post->username,$post->repassword);
			}
			location('ibuy/franchise/'.$post->username);
		}
	}
	if ($error) $ret .= message('error',$error);
	$ret.=__ibuy_ranchise_register_form($authcode,$post);
	return $ret;
}

/**
 * Generate franchise register form
 *
 * @param Object $post
 * @return String
 */
function __ibuy_ranchise_register_form($authcode,$post=null) {
	$form->config->variable='register';
	$form->config->method='post';
	$form->config->action=url(q());

	$form->autocode->type='hidden';
	$form->autocode->name='authcode';
	$form->autocode->value=$authcode;

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


	$form->member_info = '<fieldset><legend>ข้อมูลส่วนบุคคล (Personal information)</legend>';

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

	$form->latlng->type='text';
	$form->latlng->label='ละติจูด , ลองกิจูด';
	$form->latlng->maxlength=30;
	$form->latlng->size=50;
	$form->latlng->value=htmlspecialchars($post->latlng);
	$form->latlng->description='ตำแหน่งละติจูด-ลองกิจูดบนแผนที่ ตัวอย่าง 7.007977,100.467167 สามารถหาข้อมูลเพิ่มเติมได้จาก <a href="http://maps.google.co.th/maps?hl=th&tab=wl" target="_blank">Google Map</a> หรือจาก<a href="#map">แผนที่ด้านล่าง</a>';

	$form->shop_info_e = '</fieldset>';


	$payment=cfg('ibuy.franchise.payment.method');

	foreach ($payment as $k=>$p) {
		$fk='payment_info_'.$k;
		$form->{$fk.'_s'} = '<fieldset><legend>'.$p->label.'</legend>';
		$form->$fk->shop_payment->type='radio';
		$form->$fk->shop_payment->require=true;
		$form->$fk->shop_payment->value=$post->shop_payment;
		foreach ($p->items as $kp=>$vp) {
			list($first,$next)=explode('/',$vp);
			$form->$fk->shop_payment->options[$k.':'.$kp]=$kp.' : ชำระงวดแรก <strong>'.number_format($first,2).'</strong> บาท'.($next?' งวดต่อไปงวดละ <strong>'.number_format($next,2).'</strong> บาท':'');
		}
		$form->{$fk.'_e'} = '</fieldset>';
	}

	$condition=paper_model::get_topic_by_id(cfg('ibuy.condition_of_use'));

	$form->condition->label='เงื่อนไขการให้บริการ';
	$form->condition->pretext='<div id="franchise-condition">'.$condition->body.'</div>';
	$form->condition->type='checkbox';
	$form->condition->name='condition';
	$form->condition->options->read='ข้าพเจ้าได้อ่านเงื่อนไขของการเป็นสมาชิกเฟรนไชส์ ตามรายละเอียดด้านบนอย่างถี่ถ้วนแล้ว และยอมรับในเงื่อนไขตามที่ระบุไว้ทุกประการ';
	$form->condition->require=true;
	$form->condition->value=$_POST['condition'];

	$form->submit->type='submit';
	$form->submit->items->save=tr('Register');
	$form->submit->items->cancel=tr('Cancel');

	$form->help->type='textfield';
	$form->help->value=sg_client_convert('<strong>หมายเหตุ</strong> กรุณากรอกข้อมูลในช่องที่มีเครื่องหมาย * กํากับอยู่ให้ครบถ้วนสมบูรณ์');

	$ret .= theme('form','edit-register',$form);

	$ret.='<a name="map"></a><iframe src="http://maps.google.co.th/maps?hl=th&tab=wl" width="100%" height="600"></iframe>';

	return $ret;
}

/**
 * Create franchise information
 *
 * @param Object $post
 * @return String
 */
function __ibuy_ranchise_register_create($post=array()) {
	// Create franchise information
	$stmt='INSERT INTO %ibuy_customer%
					( `uid` , `custtype` , `custname` , `custattn` , `pid` , `custaddress` , `custzip` , `custphone` , `custlicense` ,
						`custpaymentperiod` , `custpaymentmethod` , `latlng` )
				VALUES
					( :uid , "franchise" , :custname , :custattn , :pid , :custaddress , :custzip , :custphone , :custlicense ,
						:custpaymentperiod , :custpaymentmethod , :latlng )';
	$result=mydb::query($stmt,$post);

	// Change user roles to franchise
	mydb::query('UPDATE %users% SET `roles`="franchise" WHERE `uid`=:uid LIMIT 1',':uid',$post->uid);
	$stmt='INSERT INTO %ibuy_order% (`uid`,`ordertype`,`orderdate`,`subtotal`,`total`,`balance`) VALUE (:uid,:ordertype,:orderdate,:subtotal,:total,:balance)';

	// Create franchise total payment
	if (cfg('ibuy.franchise.register.payment')) {
		mydb::query($stmt,':uid',$post->uid,':ordertype',__IBUY_TYPE_FRANCHISE,':orderdate',date('U'),':subtotal',$post->shop_payment_total,':total',$post->shop_payment_total,':balance',$post->shop_payment_total);
	}
	return $ret;
}
?>