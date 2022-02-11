<?php
/**
 * flood class for Flood Management
 *
 * @package flood
 * @version 0.10
 * @copyright Copyright (c) 2000-present , The SoftGanz Group By Panumas Nontapan
 * @author Panumas Nontapan <webmaster@softganz.com> , http://www.softganz.com
 * @created 2011-07-26
 * @modify 2011-10-17
 * ============================================
 * This program is free software. You can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License.
 * ============================================
 */

 function flood($self) {
	R::View('flood.toolbar',$self);

	$homeCamera = R::Model('flood.camera.list');

	$gis['center']='6.931640, 100.43967';
	$gis['zoom']=12;

	$ret .= '<div class="flood-camera-realtime">'.R::Page('flood.camera.basin', NULL).'</div>';

	/*
	foreach ($homeCamera as $rs) {
		list($x,$y)=explode(',',$rs->location);
		$ad = '<div class="flood-camera-monitor-ad" id="ad-'.$rs->name.'">';
		if ($rs->sponsor_name=='no') {
			// no sponsor
		} else if ($rs->sponsor_logo) {
			$ad.='<img src="'.$rs->sponsor_logo.'" alt="" />';
		} else {
			$ad.='<img src="/themes/default/youradhere.jpg" alt="" />';
		}
		$ad .= '</div>';
		$ret .= '<li id="camera-'.$rs->name.'">'.
			'<h3><a href="'.url('flood/cam/'.$rs->camid).'">'.$rs->title.'</a></h3>'
			.'<a href="'.url('flood/cam/'.$rs->camid).'">'
			.'<img id="'.$rs->name.'" src="'.flood_model::thumb_url($rs).'" /></a>'
			.$ad
			.'<p id="'.$rs->name.'-time" class="flood-timestamp -'.$rs->name.'">'
			.'<span class="date -date">'.sg_date($rs->last_updated,'ว ดด ปป').'</span>'
			.'<span class="date -time">'.sg_date($rs->last_updated,'H:i').'</span>'
			.'</p>'
			//.'<p><a class="water-level" href="'.url('flood/status/level/'.$rs->camid).'">ระดับน้ำ</a></p>'
			.(date('U')-$rs->last_updated>30*60?'<p class="flood-cam-error not-update">ยังไม่ได้รับภาพใหม่</p>':'')
			.'</li>'._NL;

		$gis['markers'][] = array(
			'latitude'=>$x,
			'longitude'=>$y,
			'title'=>$rs->title,
			'icon'=>'/library/img/geo/webcam.jpg',
			'content'=>'<h4><a href="'.url('flood/cam/'.$rs->camid).'">'.$rs->title.'</a></h4><a href="'.url('flood/cam/'.$rs->camid).'"><img id="'.$rs->camid.'" src="'.flood_model::thumb_url($rs).'" /></a><p id="'.$rs->camid.'-time">'.sg_date($rs->last_updated,'ว ดด ปป H:i').' น. </p><p><a class="water-level" href="'.url('flood/status/level/'.$rs->camid).'">ระดับน้ำ</a></p>'
		);

		$cams_list[]=$rs->name;
	}
	$ret.='</ul></div>'._NL;

	$ret='<p>ระบบเฝ้าระวังภัยน้ำท่วมหาดใหญ่ ยังคงอยู่ในช่วงของการพัฒนา ต้องการความร่วมมือจากหลายภาคส่วน โดยเฉพาะหน่วยงาน/องค์กร/ประชาชนที่มี IP Camera สามารถเข้าร่วมโครงการเพื่อนำภาพจากกล้อง IP Camera ของท่านมาเผยแพร่ในเว็บไซท์แห่งนี้ เพื่อให้ทีมงานได้นำมาใช้เป็นข้อมูลในการประมวลสถานการณ์สำหรับการป้องกันภัยจากน้ำท่วมที่อาจจะเกิดขึ้นได้</p>
	<div id="flood-main-monitor">
	<h2>ภาพจากกล้อง IP Camera สำหรับสอดส่องระดับน้ำในพื้นที่ลุ่มน้ำอู่ตะเภา</h2>
	'.$ret.'
	</div>';
	*/

	return $ret;
}
?>