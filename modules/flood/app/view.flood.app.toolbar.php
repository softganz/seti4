<?php
function view_flood_app_toolbar($rs=NULL) {
	$ret.='<div class="toolbar main">'._NL;
	$ret.='<h2>'.SG\getFirst(tr('Hatyai Flood Monitor','เฝ้าระวังน้ำท่วมหาดใหญ่')).'</h2>';

	$ret.='<ul>'._NL;
	$ret.='<li><a class="sg-action" href="'.url('flood/app/basin/v1').'" data-rel="#flood-event"><i class="icon2 -camera"></i>'.tr('CCTV Camera','ภาพ CCTV').'</a></li>';
	$ret.='<li><a class="sg-action" href="'.url('flood/event/init').'" data-rel="#flood-event"><i class="icon2 -rain"></i>'.tr('Send Event','แจ้งสถานการณ์').'</a></li>'._NL;
	$ret.='<li><a class="sg-action" href="'.url('flood/event/map').'" data-rel="#flood-event"><i class="icon2 -rain"></i>'.tr('Warning Map','แผนที่เตือนภัย').'</a></li>'._NL;
	$ret.='<li><a class="sg-action" href="'.url('flood/event/send').'" data-rel="#flood-event"><i class="icon2 -photos"></i>'.tr('Warning Team','ทีมเตือนภัย').'</a></li>'._NL;
	$ret.='</ul>'._NL;
	$ret.='</div>';
	return $ret;
}
?>