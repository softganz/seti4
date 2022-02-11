<?php
/**
* Green : User Page
* Created 2020-11-17
* Modify  2020-11-17
*
* @param Object $self
* @param Int $userId
* @return String
*
* @usage green/u/{$Id}
*/

$debug = true;

function green_u($self, $userId) {
	// Data Model
	$isAdmin = is_admin('green');
	$isRight = $isAdmin;

	$stmt = 'SELECT `uid`, `username`, `name`, `organization`, `mobile`, `datein` FROM %users% WHERE `uid` = :uid LIMIT 1';

	$userInfo = mydb::select($stmt, ':uid', $userId);

	$orgList = R::Model('green.shop.get', '{user: '.$userInfo->uid.'}', '{debug: false, limit: 100}');


	// View Model
	$ret = '<section>';
	$ret .= '<header class="header -box -hidden">'._HEADER_BACK.'<h3>'.$userInfo->name.'</h3></header>';

	$ret .= '<div class="popup-profile -clearfix -sg-text-center"><img src="'.model::user_photo($userInfo->username).'" width="100" height="100" style="display: block; width: 200px; height: 200px; margin: 0 auto 16px; border-radius: 50%; float: none;" />'
		. '<span class="name">'.$userInfo->name.'</span>'
		//. '<span class="">ข้อมูลที่เปิดเผยได้</span>'
		//. '<span class="">เพื่อน xx คน</span>'
		. '<span class="">'.$userInfo->organization.'</span>'
		. ($isRight && $userInfo->mobile ? '<span>โทร '.$userInfo->mobile.'</span>' : '')
		. '</div>';

	if ($isAdmin) {
		$ret .= '<p>เริ่มเป็นสมาชิกเมื่อ '.sg_date($userInfo->datein, _DATE_FORMAT).'</p>';
	}



	if ($orgList) {
		$orgCard = new Ui('div', 'ui-card');
		foreach ($orgList as $rs) {
			$orgCard->add(
				'<div class="detail"><i class="icon -material">groups</i>'.$rs->name.'</div>',
				array(
					'class' => 'sg-action',
					'href' => url('green/shop/'.$rs->shopid),
					'data-webview' => $rs->name,
				)
			);
		}
		$ret .= $orgCard->build();
	}


	// Show visit history
	$ret .= '<section id="green-activity-card" class="sg-load" data-url="'.url('green/activity', array('u' => $userId)).'">'._NL
		. '<div class="loader -rotate" style="width: 64px; height: 64px; margin: 32px auto; display: block;"></div>'
		. '</section><!-- green-activity-card -->';

	
	//$ret .= print_o($orgList,'$orgList');


	//$ret .= print_o($userInfo,'$userInfo');

	return $ret;
}
?>