<?php
/**
 * flood_init class for Flood Management
 *
 * @package flood
 * @version 0.10
 * @copyright Copyright (c) 2000-present , The SoftGanz Group By Panumas Nontapan
 * @author Panumas Nontapan <webmaster@softganz.com> , http://www.softganz.com
 * @created 2011-07-26
 * @modify 2013-10-15
 * ============================================
 * This program is free software. You can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License.
 * ============================================
 */

cfg('flood.version','1.10.0');
cfg('flood.release','27.8.18');

cfg('web.secondary',cfg('flood.secondary'));

//menu('flood/event','Flood Status Monitor','flood.event','__controller',2,true,'static');
//menu('flood/monitor','Flood Monitor','flood','__controller',1,true,'static');
//menu('flood/data','Flood Data import','flood.data','__controller',2,true,'static');

menu('flood','Flood Home','flood','__controller',1,'access flood content','static');

define('_FLOOD_UPLOAD_URL_OLD',cfg('upload.url').'fl/');
define('_FLOOD_UPLOAD_FOLDER_OLD',cfg('upload.folder').'fl/');

define('_FLOOD_UPLOAD_URL',cfg('url').'floodphoto/');
define('_FLOOD_UPLOAD_FOLDER',cfg('folder.abs').'floodphoto/');

define('_FLOOD_PHOTO_URL',_FLOOD_UPLOAD_URL.'cam/');
define('_FLOOD_PHOTO_FOLDER',_FLOOD_UPLOAD_FOLDER.'cam/');

define('_FLOOD_LASTPHOTO_URL',_FLOOD_UPLOAD_URL.'last/');
define('_FLOOD_LASTPHOTO_FOLDER',_FLOOD_UPLOAD_FOLDER.'last/');

define('_FLOOD_THUMB_URL',_FLOOD_UPLOAD_URL.'thumb/');
define('_FLOOD_THUMB_FOLDER',_FLOOD_UPLOAD_FOLDER.'thumb/');

//define('_FLOOD_FTP_FOLDER_SRC',cfg('upload.folder').'fl/ftp/');
//define('_FLOOD_FTP_FOLDER_SRC',_FLOOD_UPLOAD_FOLDER.'ftpcam/');
define('_FLOOD_FTP_FOLDER_SRC','./floodphoto/ftpcam/');

head('flood.js', '<script type="text/javascript" src="flood/js.flood.js"></script>');

require_once('class.flood.model.php');

class Flood_base extends Module {
	var $module='flood';

	function __construct() {
		parent::__construct($this->module);
		$this->theme->title=tr('Hatyai Flood Monitor','เฝ้าระวังน้ำท่วมหาดใหญ่');
		$this->theme->option->title=true;
	}

	/**
	* Show module toolbar menu
	*
	* @param Record Set $rs
	* @return String
	*/
	function __toolbar($rs=NULL) {
		$ret.='<ul>'._NL;
		$ret.='<li><a href="'.url('flood').'"><i class="icon2 -camera"></i>'.tr('Camera','กล้อง').'</a></li>';
		if ($rs->camid) {
			$ret.='<li><a href="'.url('flood/cam/'.$rs->camid).'"><i class="icon2 -refresh"></i>'.tr('Refresh','รีเฟรช').'</a></li>';
			$ret.='</ul>'._NL;
			$ret.='<ul>'._NL;
			$ret.='<li><a class="sg-action" href="'.url('flood/status/photo/'.$rs->camid).'" data-rel="#flood-camera-info"><i class="icon2 -photo"></i>ภาพ</a></li>';
			$ret.='<li><a class="sg-action" href="'.url('flood/status/level/'.$rs->camid).'" data-rel="#flood-camera-info"><i class="icon2 -level"></i>'.tr('Water Level','ระดับน้ำ').'</a></li>';
			$ret.='<li><a class="sg-action" href="'.url('flood/status/map/'.$rs->camid).'" data-rel="#flood-camera-info"><i class="icon2 -map"></i>แผนที่</a></li>'._NL;
		}
		$ret.='</ul>'._NL;

		if (user_access('create flood content')) {
			$ret.='<ul class="nav-admin">';
			$ret.='<li><a'.($rs->camid?' id="flood-camera-update"':'').' href="'.url('flood/camera/update'.($rs->camid?'/'.$rs->camid:'')).'"><i class="icon2 -update"></i>'.tr('Update','อัพเดท').'</a></li>';
			$ret.='<li><a href="'.url('flood/camera/upload'.($rs->camid?'/'.$rs->camid:'')).'"><i class="icon2 -upload"></i>'.tr('Upload','อัพโหลด').'</a></li>';
			if ($rs->camid) {
				if (user_access('administrator floods','edit own flood content',$rs->uid)) $ret.='<li><a href="'.url('flood/camera/edit/'.$rs->camid).'"><i class="icon2 -edit"></i>'.tr('Edit','แก้ไข').'</a></li><li><a href="'.url('flood/camera/delete/'.$rs->camid).'"><i class="icon2 -delete"></i>'.tr('Delete','ลบทิ้ง').'</a></li>';
				if (user_access('administrator floods')) {
					$ret.='<li><a href="'.url('flood/cam/'.$rs->camid,array('realtime'=>'yes')).'"><i class="icon2 -realtime"></i>Realtime</a></li>';
				}
			}
			if (user_access('administrator floods')) $ret.='<li><a href="'.url('flood/admin').'"><i class="icon2 -admin"></i>'.tr('Manage','จัดการ').'</a></li>';
			$ret.='</ul>'._NL;
		}

		head('<script type="text/javascript" src="/js/jquery.ui.map.js"></script>');
		head('googlemap','<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?language=th&key='.cfg('gmapkey').'"></script>');
		head('<script type="text/javascript" src="/js/js/jquery.elevateZoom-3.0.3.min.js"></script>');
		return $ret;

		$active=q(1);
		$ret.='<div class="toolbar">';
		$ret.='<h2>'.SG\getFirst($this->theme->title,tr('Hatyai Flood Monitor','เฝ้าระวังน้ำท่วมหาดใหญ่')).'</h2>';

		$ret.='<form method="get" action="'.url('flood/cam').'" name="memberlist" id="search" class="search-box" role="search"><input type="hidden" name="sid" id="sid" /><input type="text" name="q" id="search-box" size="40" value="'.$_GET['q'].'"><input type="submit" class="button" name="search" value="ค้นหากล้อง"></form>';

		$ret.='<ul>'._NL;
		$ret.='<li'.(empty($active)?' class="-active"':'').'><a href="'.url('flood').'">'.tr('Home','หน้าหลัก').'</a></li>';
		$ret.='<li'.($active=='member'?' class="-active"':'').'><a href="'.url('flood').'">'.tr('Monitor','เฝ้าดูภาพถ่ายน้ำ').'</a></li>';
		if (user_access('create flood content')) {
			$ret.='<li><a href="'.url('flood/camera/upload'.($rs->camid?'/'.$rs->camid:'')).'">'.tr('Upload','อัพโหลด').'</a></li>';
			$ret.='<li><a'.($rs->camid?' id="flood-camera-update"':'').' href="'.url('flood/camera/update'.($rs->camid?'/'.$rs->camid:'')).'">'.tr('Update','อัพเดท').'</a></li>';
		}
		if (user_access('administrator floods')) $ret.='<li><a href="'.url('flood/admin').'">'.tr('Manage','จัดการ').'</a></li>';
		$ret.='</ul>'._NL;
		if ($rs->camid) {
			$ret.='<ul>';
			$ret.='<li><a href="'.url('flood/cam/'.$rs->camid).'">'.tr('Refresh','รีเฟรช').'</a></li><li><a href="'.url('flood/camera/level/'.$rs->camid).'">'.tr('Water Level','ระดับน้ำ').'</a></li>';
			if (user_access('administrator floods','edit own flood content',$rs->uid)) $ret.='<li><a href="'.url('flood/camera/edit/'.$rs->camid).'">'.tr('Edit','แก้ไขรายละเอียด').'</a></li><li><a href="'.url('flood/camera/delete/'.$rs->camid).'">'.tr('Delete','ลบทิ้ง').'</a></li>';
			$ret.='</ul>';
		}

		$ret.='</div>'._NL;
		$ret.='
<script type="text/javascript">
$(document).ready(function() {
	$("#flood-camera-update").click(function() {
		notify("Updating");
		$.get(this.href,function(data) {notify("Completed",3000);});
		return false;
	});
});

</script>';

		return $ret;
	}
}
?>