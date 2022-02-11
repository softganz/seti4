<?php
function ibuy_manage_generate_code($self) {
	$self->theme->title='Generate franchise register authorize code';
	
	$ret.='<p><strong>คำเตือน : การสร้างชุดรหัสสำหรับลงทะเบียนเฟรนไชส์ใหม่จะทำการยกเลิกชุดรหัสสำหรับลงทะเบียนเดิม และใช้รหัสชุดใหม่แทนทั้งหมด</strong></p>';
	$ret.='<p>กรุณายืนยันการสร้างชุดรหัสสำหรับลงทะเบียนเฟรนไชส์ใหม่</p>';

	$ret .= '<div class="-sg-text-right"><a class="sg-action btn -primary" href="'.url(q(), ['confirm' => 'yes']).'" data-rel="#main">ยืนยันการสร้างชุดรหัสใหม่</a></div>';

	if (SG\confirm()) {
		$length=4;
		$authcode='';
		for ($i=0;$i<100;$i++) {
			$code='';
			for($j=0;$j<$length;$j++) {
				mt_srand((double)microtime()*1000000);
				$code .= chr(mt_rand(48,57));
			}
			$authcode.=$code.',';
		}
		$authcode=trim($authcode,',');
		cfg_db('ibuy.authcode',$authcode);
		$ret .= '<p>ชุดรหัสสำหรับลงทะเบียนเฟรนไชส์ชุดใหม่คือ</p>';
		//$ret.=str_replace(',',' , ',$authcode);
	} else {
		$ret .= '<p>ชุดรหัสปัจจุบันคือ : </p>';
	}
	
	$ret.='<p>'.str_replace(',',' , ',cfg('ibuy.authcode')).'</p>';

	return $ret;
}
?>