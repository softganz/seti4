<?php
/**
* Module Method
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function publicmon_home($self) {
	R::View('publicmon.toolbar',$self,'Public Monitor');

	/*
	$callFromApp = preg_match('/softganz/', $_SERVER['HTTP_X_REQUESTED_WITH']);
	$ret .= 'Call from '.($callFromApp ? 'APP' : 'WEB').'<br />';
	$ret .= 'isMobileDevice = '.isMobileDevice().'<br />';

	$ret .= 'HTTP_X_REQUESTED_WITH = '.$_SERVER['HTTP_X_REQUESTED_WITH'].'<br />';
	//$ret .= print_o($_SERVER,'$_SERVER');
	*/

	//$ret .= phpinfo();
	$ret .= $_SERVER['HTTP_SOFTGANZ'];

	$ret .= '<div id="publicmon-home-send" class="ui-card publicmon-home-send"><div class="ui-item"><img src="'.model::user_photo(i()->username).'" width="40" height="40" />&nbsp;<a class="sg-action form-text" id="sendtext" href="'.url('publicmon/post').'" placeholder="แจ้งเหตุ?" data-rel="replace:#publicmon-home-send" data-webview="แจ้งเหตุ">แจ้งเหตุ?</a>&nbsp;<a class="sg-action btn -link" href="'.url('publicmon/post').'" data-rel="replace:#publicmon-home-send" data-webview="แจ้งเหตุ"><i class="icon -camera"></i><span>Photo</span></a></div></div>';

	//$ret .= '<a class="sg-action" href="'.url('publicmon/post').'" data-rel="#main">AJAX Call</a> <a class="sg-action" href="'.url('publicmon/post').'" data-rel="box">POPUP Call</a>';

	$cardUi = new Ui('div', 'ui-card');
	$dbs->items = array(array(''));

	$rs = (object) array(
					'username'=>'softganz',
					'name'=>'Little Bear',
					'created' => '2018-05-06 15:35:12',
					'timedata' => '2018-05-04 12:15:10',
					'pubtype' => 1,
					'pubtypename' => 'ไฟฟ้า',
					'detail' => 'แจ้งหลอดไฟฟ้าดับ ที่สี่แยกโคกเมา ขอให้เทศบาลมาช่วยเปลี่ยนหลอดให้ด่วนเลยครับ ตอนนี้มืดมาก ฝากใครช่วยแจ้งนายกให้ที',
					'status' => 'รอรับเรื่อง',
				);

	$cardUi->add(R::View('publicmon.card.render', $rs));
	$cardUi->add(R::View('publicmon.card.render', $rs));
	$cardUi->add(R::View('publicmon.card.render', $rs));
	$cardUi->add(R::View('publicmon.card.render', $rs));
	$cardUi->add(R::View('publicmon.card.render', $rs));
	$cardUi->add(R::View('publicmon.card.render', $rs));

	$ret .= $cardUi->build();

	return $ret;
}
?>