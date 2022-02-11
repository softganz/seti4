<?php
/**
* Flood : View/List Camera Information
* Created 2019-02-28
* Modify  2020-08-25
*
* @param Int $camId
* @return Widget
*
* @usage flood/cam/{id}
*/

import('widget:flood.camera.nav.php');

class FloodCam extends Page {
	var $camId;

	function __construct($camId = NULL) {
		$this->camId = $camId;
	}

	function build() {
		$camId = $this->camId;
		$showdate = SG\getFirst(post('d'),NULL);
		$showPhotoItems = SG\getFirst(post('items'),300);
		$showThumbnailItems = SG\getFirst(post('show'),30);
		$self->theme->title = 'Camera Monitor';
		$isAdmin = user_access('administrator floods,operator floods');

		if ($camId) {
			// Show photo from camera
			$cameraInfo = R::Model(
				'flood.camera.get',
				Array('camid' => $camId, 'date' => $showdate),
				Array('updateView' => true, 'photo' => $showPhotoItems)
			);
		} else {
			// Show all camera
			return $this->cameraList();
		}

		//$ret .= print_o($cameraInfo,'$cameraInfo');

		if (!$cameraInfo) {
			$ret .= message('error','ไม่มีกล้องตามที่ระบุ');
			return $ret;
		}




		$camId = $cameraInfo->camid;

		$stmt = 'SELECT
			l.*, w.`bankheightleft`
			, w.`bankheightright`, w.`depth`, w.`gateheight`
			FROM %flood_level% l
				LEFT JOIN %flood_water% w USING(`camid`)
			WHERE l.`camid` = :camid AND `priority` >= 0
			ORDER BY rectime DESC
			LIMIT 1';

		$water_level = mydb::select($stmt,':camid',$camId);

		$bankheightleft = $water_level->bankheightleft;
		$bankheightright = $water_level->bankheightright;
		$gateheight = $water_level->gateheight;
		$depth = $water_level->depth;


		$ret .= '<section id="flood-camera-view" class="flood-camera-view" data-auto-play="'.$_REQUEST['play'].'">'._NL;

		// Show camera thumbnail
		if (post('camlist') != 'no') {
			$hotCamera = R::Model('flood.camera.list');

			$ret .= '<div class="flood-camera-thumb">'._NL;
			$ret .= '<ul>'._NL;
			foreach ($hotCamera as $crs) {
				$ret .= '<li><h3><a href="'.url('flood/cam/'.$crs->camid).'">'.$crs->title.'</a></h3>'
					. '<a href="'.url('flood/cam/'.$crs->camid).'">'
					. '<img src="'.flood_model::thumb_url($crs).'" title="'.$crs->title.'" />'
					. '</a>'
					. (date('U')-$crs->last_updated>30*60?'<p class="flood-cam-error not-update">ยังไม่ได้รับภาพใหม่</p>':'')
					. '</li>'._NL;
			}
			$ret .= '</ul>'._NL;
			$ret .= '</div><!--flood-camera-thumb-->'._NL;
		}



		// Show Camera Big Photo
		$ret .= '<div class="flood-camera-realtime photo-last">'._NL;

		if (post('realtime') && $isAdmin) {
			$ret .= '<iframe id="photo-realtime" src="'.$cameraInfo->camip.':'.$cameraInfo->port.'" width="100%" height="100%"></iframe>';
		} else {
			$lastPhoto = reset($cameraInfo->photos);
			//$photoLocation = flood_model::photo_loc($lastPhoto);

			$ret .= '<div id="slider"><ul><li id="slider-prev"><a href="#" title="ภาพก่อนหน้า"><i class="icon -back"></i></a></li><li id="slider-range"><div id="slider-range-min" title="ลากปุ่มเพื่อดูภาพอื่น ๆ"></div></li><li id="slider-next"><a href="#" title="ภาพถัดไป"><i class="icon -forward"></i></a></li><!-- <li id="slider-zoom"><a href="#">ขยาย</a></li>--></ul></div><!--slider-->'._NL;

			$ret .= '<div id="camera-'.$cameraInfo->name.'" class="cctv-photo">'
				. '<img id="camera-image-'.$cameraInfo->name.'" class="-photo" src="'.flood_model::photo_url($lastPhoto).'" alt="Photo" title="คลิกบนภาพเพื่อดูภาพขยาย" />'._NL
				. ($cameraInfo->overlay_url ? '<img class="flood-camera-overlay" src="'.$cameraInfo->overlay_url.'" alt=""/>' : '')
				. '<p class="-timestamp -'.$rs->name.'">'
				. '<span class="-date">'.sg_date($cameraInfo->last_updated,'ว ดด ปป').'</span>'
				. '<span class="-time">'.sg_date($cameraInfo->last_updated,'H:i').'</span>'
				. '</p>'
				. '</div>'._NL;
			//$ret .= '<p id="photo-time">เมื่อ '.sg_date($lastPhoto->atdate,'ว ดด ปป H:i').' น. </p>'._NL;


			if ($cameraInfo->replaceid) {
				$replaceCam = mydb::select('SELECT * FROM %flood_cam% WHERE `camid` = :camid LIMIT 1', ':camid', $cameraInfo->replaceid);
			}


			// Show Sponser
			$sponsorName = SG\getFirst($replaceCam->sponsor_name, $cameraInfo->sponsor_name);
			$sponsorLogo = SG\getFirst($replaceCam->sponsor_logo, $cameraInfo->sponsor_logo);
			$sponsorUrl = SG\getFirst($replaceCam->sponsor_url, $cameraInfo->sponsor_url);
			$sponsorText = SG\getFirst($replaceCam->sponsor_text, $cameraInfo->sponsor_text);

			$ret .= '<div class="-ad" id="ad-'.$cameraInfo->name.'">'._NL;
			if ($sponsorName=='no') {
				// no sponsor
			} else if ($sponsorLogo) {
				$ret .= '<a href="'.$sponsorUrl.'"><img src="'.$sponsorLogo.'" alt="" /></a><span><a href="'.$sponsorUrl.'">'.$sponsorText.'</a></span>';
			} else {
				$ret .= '<a href="/sponsor"><img src="/themes/default/youradhere.jpg" alt="" /></a><span><a href="/sponsor">พื้นที่ขอรับการสนับสนุนค่าใช้จ่ายของกล้อง CCTV สนใจติดต่อ <strong>โทร. 081 698 1975</strong></a></span>';
			}
			$ret .= '</div><!-- -ad-->'._NL;

		}

		$ret .= '</div><!--photo-last-->'._NL;






		// Show Previous Photo Items

		$ret .= '<div class="photo-thumbnail">'._NL;
		//		if ($water_level->_num_rows) $ret.='<p class="widget" id="flood-water-current">ระดับน้ำ <strong id="crowd-flood-level">'.sg::sealevel($water_level->waterlevel,2).'</strong> ม.รทก.<br />เมื่อ <span id="crowd-flood-rectime">'.(sg_date($water_level->rectime,'ว ดด ปป H:i')).'</span> <a class="sg-action" href="'.url('paper/195').'" data-rel="box" rel-width="80%">ช่วยกันป้อนระดับน้ำ</a></p>'._NL;

		$ret .= '<ul id="flood-camera-photos" class="photo-list">'._NL;
		foreach ($cameraInfo->photos as $i=>$prs) {
			$photoUrl = flood_model::photo_url($prs);
			$imgs[] = (Object) Array(
				'photo' => $photoUrl,
				'date' => sg_date($prs->atdate, 'ว ดด ปป'),
				'time' => sg_date($prs->atdate, 'H:i'),
			);

			if ($i < $showThumbnailItems) {
				$ret .= '<li>';
				$ret .= '<a href="'.$photoUrl.'" target="_blank" data-image="'.$photoUrl.'" data-date="'.sg_date($prs->atdate, 'ว ดด ปป').'" data-time="'.sg_date($prs->atdate, 'H:i').'">';
				$ret .= '<img id="'.$cameraInfo->name.'-'.$prs->aid.'" src="'.flood_model::thumb_url($prs).'" /></a>';
				$timestamp = '<p>';
				if (sg_date($prs->atdate,'Y-m-d') != date('Y-m-d')) {
					$timestamp .= sg_date($prs->atdate,'ว ดดปป').' ';
				}
				$timestamp .= sg_date($prs->atdate,'H:i').' น.';
				$timestamp .= '</p>';

				$ret .= $timestamp;

				$ret .= $isAdmin ? ' <span class="iconset -hidden"><a class="sg-action" href="'.url('flood/camera/deletephoto/'.$prs->aid).'" data-confirm="ลบภาพนี้ทิ้ง" data-rel="none" data-removeparent="li"><i class="icon -cancel"></i><span class="-hidden">Delete</span></a></span>' : '';
				$ret .= '</li>'._NL;
			}
		}
		$ret .= '</ul>'._NL;

		$ret .= '</div><!-- flood-camera-info -->'._NL;



		// Show Slider
		$slideTime = SG\getFirst(post('t'),5);
		for ($i = 1; $i <= 5; $i++) {
			$options .= '<option value="'.$i.'" '.($i==$slideTime?'selected="selected"':'').'>'.$i.' วินาที</option>';
		}
		$ret .= '<div id="flood-for-delveloper"><form method="get" action="'.url('flood/cam/'.$camId).'">แสดงภาพวันที่ <input type="text" name="d" size="10" class="form-text sg-datepicker" value="'.SG\getFirst($_REQUEST['d'],date('Y-m-d')).'" data-date-format="yy-mm-dd" /> จำนวนภาพใหญ่ <input type="text" name="items" size="5" class="form-text" value="'.$showPhotoItems.'" /> ';
		$ret .= 'จำนวนภาพเล็ก <input type="text" name="show" size="5" class="form-text" value="'.$showThumbnailItems.'" />';
		$ret .= '<br /><p><button class="btn" type="submit" value="แสดงภาพนิ่ง">แสดงภาพนิ่ง</button> <button class="btn" type="submit" name="play"  value="ถอยหลัง">&lt; ถอยหลัง</button> <select name="t" class="form-select">'.$options.'</select> <button class="btn" type="submit" name="play" value="ไปหน้า">ไปหน้า &gt;</button></p></form>'._NL;
		$ret .= '<div class="form-item">'
			. '<label>ตำแหน่งภาพล่าสุดของกล้องนี้</label>'
			. '<input type="text" class="form-text" value="'._DOMAIN._FLOOD_LASTPHOTO_URL.$cameraInfo->name.'.jpg" style="width:90%;" /></div>'
			//. '<div class="form-item"><label>ตำแหน่งภาพที่กำลังแสดง</label><input id="flood-current-photo-url" type="text" class="form-text" value="'._DOMAIN.flood_model::photo_url($cameraInfo->photos[0]).'" style="width:90%;" /></div>'
			. '</div>'._NL;

		// Show Camera Description
		$ret .= '<div id="flood-camera-desc">'.(user_access('administrator floods','edit own flood content',$cameraInfo->uid)?'<a href="'.url('flood/camera/edit/'.$camId).'" fld="desc">แก้ไข</a><br clear="all" />':'').$cameraInfo->desc.'</div>';

		//  The Open Graph Protocol
		$opengraph->title = $cameraInfo->title.' - '.cfg('web.title');
		$opengraph->type = 'website';
		$opengraph->url = url('flood/cam/'.$camId);
		$opengraph->image = flood_model::photo_url($cameraInfo->photos[0]);
		$opengraph->description = 'ภาพบันทึกจากกล้อง IP Camera ที่ '.$cameraInfo->title.' เมื่อเวลา '.sg_date($cameraInfo->photos->atdate,'ว ดด ปป H:i').' น.';
		sg::add_opengraph($opengraph);

		$imgs = array_reverse($imgs);

		// ทุก ๆ 1 นาที ให้ไปดึงภาพแต่ละจุดสำคัญมาแสดง
		$ret .= '<script type="text/javascript">
	var camId='.$camId.';
	var cameraName="'.$cameraInfo->name.'";
	var imgs = '.SG\json_encode($imgs).';
	var refreshUrl="'.url('flood/api/lastphoto/'.$cameraInfo->name).'";
	var refreshTime='.cfg('flood')->refreshtime.';
	var isRefresh='.($showdate?'false':'true').';
	var zoomStatus=true;
	var oldPhotoDesc=$("#photo-desc").text();
	var debug='.(isset($_REQUEST['debug'])?'true':'false').';
	var autoPlay="'.$_REQUEST['play'].'";
	var playTime='.SG\getFirst($_REQUEST['t'],2).'*1000;
	var urlCrowdWaterUpdate="'.url('flood/crowd_water_update').'";

	</script>';
		$ret .= '</section>';

		// R::View('flood.toolbar',$self,$cameraInfo->title,NULL,$cameraInfo);

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => $cameraInfo->title,
				'navigator' => new FloodCameraNavWidget($cameraInfo),
			]), // AppBar
			'body' => new Widget([
				'children' => [
					$ret
				], // children
			]), // Widget
		]);
	}

	function cameraList() {
		$ret = '';
		$cams = mydb::select('SELECT * FROM %flood_cam% ORDER BY `view` DESC');

		if ($cams->_num_rows>1) {
			$ret .= '<div class="flood__camera">'._NL;
			$ret .= '<ul class="flood__camera__list">'._NL;
			foreach ($cams->items as $rs) {
				$ret .= '<li>'
					. '<h4><a href="'.url('flood/cam/'.$rs->camid).'">'.SG\getFirst($rs->title).'</a></h4>'
					. '<a href="'.url('flood/cam/'.$rs->camid).'">'
					. '<img id="'.$rs->name.'" src="'
					. flood_model::thumb_url((object) array('name'=>$rs->name, 'photo'=>$rs->last_photo, 'atdate'=>$rs->last_updated))
					.'" />'
					. '</a>'
					. '<p id="'.$rs->name.'-time">ดูล่าสุด '.sg_date($rs->last_view,'ว ดด ปป H:i').' น.<br />ดู '.number_format($rs->view).' ครั้ง</p>'
				//	. print_o($rs,'$rs')
					. '</li>'._NL;
				$cams_list[]=$rs->name;
			}
			$ret .= '</ul>'._NL;
			$ret .= '</div>'._NL;
			return $ret;
		}
		return $ret;
	}

}
?>