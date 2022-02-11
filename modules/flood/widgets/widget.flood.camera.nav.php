<?php
/**
* Flood Widget :: Navigator
* Created 2021-08-01
* Modify  2021-08-01
*
* @param Array $args
* @return Widget
*
* @usage new NameWidget([])
*/

class FloodCameraNavWidget extends Widget {
	var $camId;
	var $cameraInfo;

	function __construct($cameraInfo = []) {
		$this->camId = $cameraInfo->camid;
		$this->cameraInfo = $cameraInfo;
	}

	function build() {
		$floodConfig = cfg('flood');

		$children = [];
		$children['main'] = new Row([
			'class' => 'main',
			'children' => [
				'<a href="'.url('flood').'"><i class="icon -material">videocam</i>'.tr('Camera','กล้อง').'</a>',
			],
		]);

		if ($this->camId) {
			$children['info'] = new Row([
				'tagName' => 'ul',
				'childTagName' => 'li',
				'class' => '-info',
				'children' => array_merge(
					[
						'<a href="'.url('flood/cam/'.$this->camId).'"><i class="icon -material">refresh</i>'.tr('Refresh','รีเฟรช').'</a>',
						'<sep>',
						'<a class="sg-action" href="'.url('flood/status/photo/'.$this->camId).'" data-rel="#flood-camera-info"><i class="icon -material">photo</i>ภาพ</a>',
						'<a class="sg-action" href="'.url('flood/status/level/'.$this->camId).'" data-rel="box" data-width="480" data-height="90%"><i class="icon -material">water</i>'.tr('Water Level','ระดับน้ำ').'</a>',
						'<a class="sg-action" href="'.url('flood/status/map/'.$this->camId).'" data-rel="box" data-width="480" data-height="100%"><i class="icon -material">place</i>แผนที่</a>',
					],
					is_admin('flood') ? [
						'<a'.($this->camId?' id="flood-camera-update"':'').' href="'.url('flood/camera/update'.($this->camId?'/'.$this->camId:'')).'"><i class="icon -material">cloud_upload</i>'.tr('Update','อัพเดท').'</a>',
						'<a href="'.url('flood/camera/upload'.($this->camId?'/'.$this->camId:'')).'"><i class="icon -material">upload</i>'.tr('Upload','อัพโหลด').'</a>',
						'<a href="'.url('flood/camera/edit/'.$this->camId).'"><i class="icon -material">edit</i>'.tr('Edit','แก้ไข').'</a>',
						'<a href="'.url('flood/camera/delete/'.$this->camId).'"><i class="icon -material">delete</i>'.tr('Delete','ลบทิ้ง').'</a>',
						'<a href="'.url('flood/cam/'.$this->camId,array('realtime'=>'yes')).'"><i class="icon -material">schedule</i>Realtime</a>',
						'<a href="'.url('flood/admin').'"><i class="icon -material">settings</i>'.tr('Manage','จัดการ').'</a>',
					] : []
				),
				// 'children' => (function($orgConfig) {
				// 	$childrens = [];

				// 	// Show button in follow navigator config
				// 	foreach (explode(',', $orgConfig->navigatorUse) as $navKey) {
				// 		$menuItem = $orgConfig->navigator->{$navKey};
				// 		if ($menuItem->access) {
				// 			if (!defined($menuItem->access)) continue;
				// 			else if (!($this->orgInfo->RIGHT & constant($menuItem->access))) continue;
				// 		}
				// 		$childrens[$navKey] = '<a href="'.url('org/'.$this->orgId.($menuItem->url ? '/'.$menuItem->url : '')).'" title="'.$menuItem->title.'" '.sg_implode_attr($menuItem->attribute).'><i class="icon -material">'.$menuItem->icon.'</i><span>'.$menuItem->label.'</span></a>';
				// 	}

				// 	// Show dashboard button
				// 	// if ($this->right->access) {
				// 	// 	$childrens['dashboard'] = '<a href="'.url('project/'.$orgId.'/info.dashboard').'" rel="nofollow" title="แผงควบคุมโครงการ"><i class="icon -material">dashboard</i><span>แผงควบคุม</span></a>';
				// 	// }

				// 	// Show print button
				// 	if ($this->options->showPrint) {
				// 		$childrens[] = '<sep>';
				// 		$childrens['print'] = '<a href="javascript:window.print()"><i class="icon -material">print</i><span>พิมพ์</span></a>';
				// 	}

				// 	return $childrens;
				// })($orgConfig),
			]);
		}

		head('zoom', '<script type="text/javascript" src="/js/jquery.elevateZoom-3.0.3.min.js"></script>');

		return new Widget([
			'children' => [
				'main' => new Row([
					'class' => '-main',
					'children' => $children,
				]),
			],
		]);
	}
}
?>

<?php
/**
* Show module toolbar menu
*
* @param Record Set $rs
* @return String
*/
function view_flood_toolbar($rs = NULL, $options = '{}') {
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
	return $ret;
}
?>