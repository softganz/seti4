<?php
/**
 * Edit shop information
 *
 * @return String
 */
function ibuy_franchise_modify($self,$fid) {
	$self->theme->title='แก้ไขรายละเอียด';
	$isAdmin=user_access('administer ibuys');
	if (empty($fid)) $fid=i()->uid;
	$shopInfo=ibuy_model::get_franchise($fid);
	//$ret.=print_o($shopInfo,'$shopInfo');
	if ($shopInfo->_empty) return message('error','ไม่มีข้อมูลตามเงื่อนไขที่ระบุ');
	if (!(user_access('administer ibuys') || i()->uid==$shopInfo->uid)) return message('error','Access denied');
	if ($_POST['cancel']) location('ibuy/franchise/'.$shopInfo->uid);

	$ret.=R::View('ibuy.franchise.menu','edit',$shopInfo->uid,$shopInfo->username);
	$error=array();
	if ($_POST['save']) {
		$post=(object)post('franchise');
		if ($post->name=='') $error[]='กรุณาป้อน ชื่อสำหรับแสดง (Name)'; //-- fill name
		if (mydb::select('SELECT COUNT(*) `totals` FROM %users% WHERE `uid` != :fid AND `name` = :name LIMIT 1', ':fid', $fid, ':name',$post->name)->totals) $error[]='ชื่อ <strong><em>'.$post->name.'</em></strong> มีผู้อื่นใช้ไปแล้ว'; //-- duplicate name
		if ($post->email=='') $error[]='กรุณาป้อน อีเมล์ (E-mail)'; //-- fill email
		if ($post->email && !sg_is_email($post->email)) $error[]='อีเมล์ (E-mail) ไม่ถูกต้อง'; //-- invalid email
		if ($post->custname=='') $error[]='กรุณาป้อน ชื่อร้าน (Shop name)'; //-- fill custname
		if ($post->custaddress=='') $error[]='กรุณาป้อน สถานที่ตั้งร้าน (Location)'; //-- fill custaddress
		if ($post->custzip=='') $error[]='กรุณาป้อน รหัสไปรษณีย์ (ZIP code)'; //-- fill custzip
		if ($post->custphone=='') $error[]='กรุณาป้อน โทรศัพท์ (Phone)'; //-- fill custphone
		if (empty($error)) {
			$stmt='UPDATE %users% SET name=:name , email=:email WHERE uid=:fid LIMIT 1';
			mydb::query($stmt,':name',$post->name,':email',$post->email,':fid',$fid);
			$stmt='UPDATE %ibuy_customer% SET custname=:custname , custattn=:custattn , custaddress=:custaddress , custzip=:custzip , custphone=:custphone , custlicense=:custlicense , shippingby=:shippingby, latlng=:latlng WHERE uid=:fid LIMIT 1';
			mydb::query($stmt,$post,':fid',$fid);

			// set discount_hold
			if ($isAdmin) {
				unset($stmt);
				if (isset($post->discount_hold_status)) {
					if ($post->discount_hold_status==0) {
						// set to hold
						if ($shopInfo->discount_hold==-1) $stmt='UPDATE %ibuy_customer% SET discount_hold=0 WHERE uid=:uid LIMIT 1';
					} else if ($post->discount_hold_status==-1) {
						// move discount_hole to discount
						if ($shopInfo->discount_hold>=0) $stmt='UPDATE %ibuy_customer% SET discount=discount+discount_hold,discount_hold=-1 WHERE uid=:uid LIMIT 1';
					}
					if (isset($stmt)) mydb::query($stmt,':uid',$fid);
				}
				if ($post->custtype) {
					if ($post->custtype==-1) $post->custtype = NULL;
					mydb::query('UPDATE %ibuy_customer% SET `custtype`=:custtype WHERE `uid`=:uid LIMIT 1',':uid',$fid,':custtype',$post->custtype);

					$userRole=mydb::select('SELECT `roles` FROM %users% WHERE `uid`=:uid LIMIT 1',':uid',$fid)->roles;
					$rs=mydb::select('SELECT `roles` FROM %users% WHERE `uid`=:uid LIMIT 1',':uid',$fid);
					//$ret.='User role='.$userRole;
					//$ret.=print_o($rs);
					if ($post->roles && !($fid==1 || $userRole=='admin')) {
						//$roles=in_array($post->custtype, array('resaler','franchise'))?$post->custtype:'';
						mydb::query('UPDATE %users% SET `roles`=:roles WHERE `uid`=:uid LIMIT 1',':uid',$fid,':roles',$post->roles);
					}
				}
			}
			location('ibuy/franchise/'.$shopInfo->uid);
		}
	} else {
		$post=$shopInfo;
	}

	if ($error) $ret.=message('error',$error);


	$form = new Form('franchise', url(q()), 'edit-register');

	$form->submit->type='submit';
	$form->submit->items->save=tr('Save');
	$form->submit->items->cancel=tr('Cancel');

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
	$form->latlng->description='ตำแหน่งละติจูด-ลองกิจูดบนแผนที่ ตัวอย่าง 7.007977,100.467167 สามารถหาข้อมูลเพิ่มเติมได้จาก <a href="http://maps.google.co.th/maps?hl=th&tab=wl&q='.($post->latlng?$post->latlng:$post->province).'" target="_blank">Google Map</a> หรือจาก<a href="#map">แผนที่ด้านล่าง</a>';

	$form->shop_info_e = '</fieldset>';

	$form->shop_other_s = '<fieldset><legend>ข้อมูลอื่น ๆ</legend>';

	if (user_access('administer ibuys')) {
			$memberLevelList = array('-1'=>'ไม่สามารถซื้อสินค้า');
		foreach (cfg('ibuy.price.use') as $key => $value) {
			if ($key == 'cost') continue;
			$memberLevelList[$key] = $value->label;
		}

		$form->custtype->type='radio';
		$form->custtype->label='ระดับราคาสินค้า :';
		$form->custtype->options = $memberLevelList;
		$form->custtype->value=SG\getFirst($post->custtype,-1);

		if (in_array($post->roles,array('resaler','franchise'))) {
			$form->roles->type='select';
			$form->roles->label='กลุ่มสมาชิก :';
			$form->roles->options=array(
							''=>'===เลือก===',
							'resaler'=>'ตัวแทนจำหน่าย',
							'franchise'=>'เฟรนไชส์'
							);
			$form->roles->value=$post->roles;
		}

		$form->discount_hold_status->type='radio';
		$form->discount_hold_status->label='สิทธิ์ในการได้รับค่าส่วนแบ่งการตลาด :';
		$form->discount_hold_status->options[0]='ไม่ ยังไม่สามารถใช้ค่าส่วนแบ่งการตลาดมาเป็นส่วนลดสินค้า';
		$form->discount_hold_status->options[-1]='ใช่ สามารถนำค่าส่วนแบ่งการตลาดมาใช้เป็นส่วนลดสินค้าได้';
		$form->discount_hold_status->value=$shopInfo->discount_hold<0?-1:0; // -1 is not hold , 0 or more is hold
		if ($shopInfo->discount_hold>=0) $form->discount_hold_status->posttext='ส่วนลดที่ยังใช้ไม่ได้มีจำนวน <strong>'.number_format($shopInfo->discount_hold,2).'</strong> บาท หากกำหนดว่า <strong>ใช่</strong> จะนำส่วนลดนี้ไปรวมกับส่วนลดปกติ';

	}

	$form->shippingby->type='text';
	$form->shippingby->label='ขนส่งสินค้าโดย';
	$form->shippingby->maxlength=200;
	$form->shippingby->size=50;
	$form->shippingby->value=htmlspecialchars($post->shippingby);

	$form->shop_other_e = '</fieldset>';

	$ret .= $form->build();
	//$ret.=print_o($post,'$post');




	if ($post->latlng) {
		$gis['center']=$post->latlng;
		$gis['zoom']=13;
		list($lat,$lnt)=explode(',',$post->latlng);
		$gis['markers'][]=array('latitude'=>$lat,
													'longitude'=>$lnt,
													);
	} else {
		$gis['center']='13.74676363889678,100.50716685742191';
		$gis['zoom']=7;
	}
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
					$("#edit-franchise-latlng").val(latLng);
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
						$("#edit-franchise-latlng").val(latLng);
					}).dragend(function(event) {
						var latLng=event.latLng.lat()+","+event.latLng.lng();
						$("#edit-franchise-latlng").val(latLng);
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