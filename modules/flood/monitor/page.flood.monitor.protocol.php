<?php
/**
* flood_monitor_protocol
*
* @param Object $self
* @return String
*/
function flood_monitor_protocol($self) {
	$basin=post('basin');

	$target['MBT']=array(''=>'พื้นที่เป้าหมายบ้านฉลุงเหนือ', '2'=>'พื้นที่เป้าหมายเทศบาลเมืองสตูล');
	$target['NWT']=array(''=>'พื้นที่เป้าหมายตลาดนาทวี','2'=>'พื้นที่เป้าหมายเทศบาลตำบลจะนะ');

	R::View('flood.monitor.toolbar',$self);

	$ret.='<div class="flood--protocol">';
	$ret.='<h3 class="clear">Protocol</h3>';
	if (in_array($basin,array('MBT','NWT'))) $ret.='<h4>'.$target[$basin][''].'</h4>';
	$ret.='<ul class="flood--protocol--list">';
	$ret.='<li><a class="sg-action" data-group="protocol" href="'.url('file/flood/site/'.$basin.'-protocol.png').'" data-rel="img"><img src="'.url('file/flood/site/'.$basin.'-protocol.png').'" width="100%" />ขั้นตอนการประเมินสถานการณ์</a></li>';
	$ret.='<li><a class="sg-action" data-group="protocol" href="'.url('file/flood/site/'.$basin.'-yellow-flag.png').'" data-rel="img"><img src="'.url('file/flood/site/'.$basin.'-yellow-flag.png').'" width="100%" />ขั้นตอนการแจ้งเตือนให้เฝ้าระวัง (ธงเหลือง)</a></li>';
	$ret.='<li><a class="sg-action" data-group="protocol" href="'.url('file/flood/site/'.$basin.'-red-flag.png').'" data-rel="img"><img src="'.url('file/flood/site/'.$basin.'-red-flag.png').'" width="100%" />ขั้นตอนการเตือนภัยน้ำท่วม (ธงแดง)</a></li>';
	$ret.='<li><a class="sg-action" data-group="protocol" href="'.url('file/flood/site/'.$basin.'-red-cancel.png').'" data-rel="img"><img src="'.url('file/flood/site/'.$basin.'-red-cancel.png').'" width="100%" />ขั้นตอนการยกเลิกเตือนภัยน้ำท่วม (ลดธงแดง)</a></li>';
	$ret.='</ul>';

	if (in_array($basin,array('NWT','MBT'))) {
		$ret.='<h3 class="clear">Protocol</h3>';
		$ret.='<h4>'.$target[$basin][2].'</h4>';
		$ret.='<ul class="flood--protocol--list">';
		$ret.='<li><a class="sg-action" data-group="protocol1" href="'.url('file/flood/site/'.$basin.'-2'.'-protocol.png').'" data-rel="img"><img src="'.url('file/flood/site/'.$basin.'-2'.'-protocol.png').'" width="100%" />ขั้นตอนการประเมินสถานการณ์</a></li>';
		$ret.='<li><a class="sg-action" data-group="protocol1" href="'.url('file/flood/site/'.$basin.'-2'.'-yellow-flag.png').'" data-rel="img"><img src="'.url('file/flood/site/'.$basin.'-2'.'-yellow-flag.png').'" width="100%" />ขั้นตอนการแจ้งเตือนให้เฝ้าระวัง (ธงเหลือง)</a></li>';
		$ret.='<li><a class="sg-action" data-group="protocol1" href="'.url('file/flood/site/'.$basin.'-2'.'-red-flag.png').'" data-rel="img"><img src="'.url('file/flood/site/'.$basin.'-2'.'-red-flag.png').'" width="100%" />ขั้นตอนการเตือนภัยน้ำท่วม (ธงแดง)</a></li>';
		$ret.='<li><a class="sg-action" data-group="protocol1" href="'.url('file/flood/site/'.$basin.'-2'.'-red-cancel.png').'" data-rel="img"><img src="'.url('file/flood/site/'.$basin.'-2'.'-red-cancel.png').'" width="100%" />ขั้นตอนการยกเลิกเตือนภัยน้ำท่วม (ลดธงแดง)</a></li>';
		$ret.='</ul>';

	}
	$ret.='</div>';
	return $ret;
}
?>