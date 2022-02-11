<?php
function flood_app_basin_select($self) {
	$basinList = cfg('flood')->basin;

	if ($basin = post('b')) {
		//$ret.='เลือกลุ่มน้ำ '.post('b');
		if ($basin == 'HOME') {
			setcookie('basin',"",time() - 3600,cfg('cookie.path'),cfg('cookie.domain'));
		} else {
			setcookie('basin',$basin,time()+365*24*60*60,cfg('cookie.path'),cfg('cookie.domain'));
		}
		//$ret .= R::Page('flood.app.basin',NULL);
		location('flood/app/basin');
		//$ret.=$this->_flood();
		return $ret;
	}

	$ret = '<h3>เลือกลุ่มน้ำ</h3>';
	$ret .= '<ul class="fullbar">';
	foreach (explode(',', 'HOME,'.$basinList) as $basinKey) {
		$ret .= '<li>'
			. '<a class="sg-action" data-rel="#main" href="'.url('flood/app/basin/select',array('b'=>$basinKey)).'">'.cfg('flood.camera')->{$basinKey}->title.'</a>'
			. '</li>';
	}
	$ret .= '</ul>';

	$ret .= '<style type="text/css">
	h3 {text-align: center;}
	.fullbar {margin:0 8px; padding:0; list-style-type:none; text-align: center;}
	.fullbar a {padding: 10px 0; display: block; margin: 20px 0; background:#ddd;}
	.fullbar a:hover {background: #d5d5d5;}
	.fullbar a:active {background:#eee;}
	</style>';
	return $ret;
}
?>