<?php
/**
* Module Method
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function publicmon_assets($self) {
	R::View('publicmon.toolbar',$self,'Assets Management');

	$isEdit = true;

	//$ret .= '<div id="publicmon-home-send" class="ui-card publicmon-home-send"><div class="ui-item"><img src="'.model::user_photo(i()->username).'" width="40" height="40" />&nbsp;<a class="sg-action form-text" id="sendtext" href="'.url('publicmon/post').'" placeholder="แจ้งเหตุ?" data-rel="replace:#publicmon-home-send" data-webview="แจ้งเหตุ">แจ้งเหตุ?</a>&nbsp;<a class="sg-action btn -link" href="'.url('publicmon/post').'" data-rel="replace:#publicmon-home-send" data-webview="แจ้งเหตุ"><i class="icon -camera"></i><span>Photo</span></a></div></div>';


	$cardUi = new Ui('div', 'ui-card');
	$dbs->items = array(array(''));

	$rs = (object) array(
					'username' => 'softganz',
					'name' => 'มิเตอร์น้ำ 01-00',
					'created' => '2018-05-06 15:35:12',
					'timedata' => '2018-05-04 12:15:10',
					'pubtype' => 1,
					'pubtypename' => 'มิเตอร์น้ำ',
					'address' => '123 ม. 1 ต.ปริก อ.สะเดา จ.สงขลา',
					'detail' => '',
					'status' => 'รอรับเรื่อง',
				);
	$name = $rs->name;
	for ($i=1; $i<10; $i++) {
		$rs->name = $name . $i;
		$cardUi->add(R::View('publicmon.assets.render', $rs));
	}

	$ret .= $cardUi->build();

	if ($isEdit) {
		$ret .= '<div class="btn-floating -right-bottom">'
			.'<a class="sg-action btn -floating -circle32" href="'.url('publicmon/assets/add').'" data-webview="Create New Assets"><i class="icon -addbig -white"></i></a>'
			.'</div>';
	}

	return $ret;
}
?>