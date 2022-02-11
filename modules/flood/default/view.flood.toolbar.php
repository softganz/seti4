<?php
/**
* Show module toolbar menu
*
* @param Record Set $rs
* @return String
*/
function view_flood_toolbar($self,$title=NULL,$nav='default',$rs=NULL,$options='{}') {
	if ($title) $self->theme->title=$title;

	$ui = new Ui();
	$ui->addConfig('container','{tag: "nav", class: "nav"}');
	$ui->add('<a href="'.url('flood').'"><i class="icon2 -camera"></i>'.tr('Camera','กล้อง').'</a>');
	if ($rs->camid) {
		$ui->add('<a href="'.url('flood/cam/'.$rs->camid).'"><i class="icon2 -refresh"></i>'.tr('Refresh','รีเฟรช').'</a>');
		$ui->add('-');
		$ui->add('<a class="sg-action" href="'.url('flood/status/photo/'.$rs->camid).'" data-rel="#flood-camera-info"><i class="icon2 -photo"></i>ภาพ</a>');
		$ui->add('<a class="sg-action" href="'.url('flood/status/level/'.$rs->camid).'" data-rel="box" data-width="480" data-height="90%"><i class="icon2 -level"></i>'.tr('Water Level','ระดับน้ำ').'</a>');
		$ui->add('<a class="sg-action" href="'.url('flood/status/map/'.$rs->camid).'" data-rel="box" data-width="480" data-height="100%"><i class="icon2 -map"></i>แผนที่</a>');
	}
	$ui->add('-');

	if (user_access('create flood content')) {
		$ui->add('<a'.($rs->camid?' id="flood-camera-update"':'').' href="'.url('flood/camera/update'.($rs->camid?'/'.$rs->camid:'')).'"><i class="icon2 -update"></i>'.tr('Update','อัพเดท').'</a>');
		$ui->add('<a href="'.url('flood/camera/upload'.($rs->camid?'/'.$rs->camid:'')).'"><i class="icon2 -upload"></i>'.tr('Upload','อัพโหลด').'</a>');
		if ($rs->camid) {
			if (user_access('administrator floods','edit own flood content',$rs->uid)) {
				$ui->add('<a href="'.url('flood/camera/edit/'.$rs->camid).'"><i class="icon2 -edit"></i>'.tr('Edit','แก้ไข').'</a>');
				$ui->add('<a href="'.url('flood/camera/delete/'.$rs->camid).'"><i class="icon2 -delete"></i>'.tr('Delete','ลบทิ้ง').'</a>');
			}
			if (user_access('administrator floods')) {
				$ui->add('<a href="'.url('flood/cam/'.$rs->camid,array('realtime'=>'yes')).'"><i class="icon2 -realtime"></i>Realtime</a>');
			}
		}
		if (user_access('administrator floods')) {
			$ui->add('<a href="'.url('flood/admin').'"><i class="icon2 -admin"></i>'.tr('Manage','จัดการ').'</a>');
		}
	}

	head('<script type="text/javascript" src="/js/jquery.elevateZoom-3.0.3.min.js"></script>');
	$self->theme->toolbar = $ui->build();
	return $ret;
}
?>