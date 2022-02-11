<?php
function flood_app_basin($self=NULL) {
	$basin = $_COOKIE['basin'];

	$floodCamera = cfg('flood.camera');

	$notUpdateTime = 60*60;
	$cameraList = $floodCamera->{$basin}->camera;
	$cams = R::Model('flood.camera.list',$cameraList);


	$ret .= '<div id="flood-thumb">';
	$ret .= '<ul class="flood-thumb">';
	foreach ($cams as $crs) {
		$ret .= '<li>'
			. '<h3><a class="sg-action" href="'.url('flood/app/cam/'.$crs->camid).'" data-rel="#app-info">'.$crs->title.'</a></h3>'
			. '<a class="sg-action" href="'.url('flood/app/cam/'.$crs->camid).'" data-rel="#app-info" data-webview="'.$crs->title.'"><img id="thumb-'.$crs->name.'" src="'.flood_model::thumb_url($crs).'" title="'.$crs->title.'" /></a>'
			. (date('U')-$crs->last_updated>30*60?'<p class="flood-cam-error not-update">ยังไม่ได้รับภาพใหม่</p>':'')
			. '</li>'._NL;
	}
	$ret .= '</ul></div>'._NL;

	$ret .= '<nav class="nav -page -selectbasin">'
		. '<a class="sg-action btn" style="display:block;width:auto;text-align:center;padding:10px 0; margin:8px; border:none; background-color:#009DFF; font-weight:normal;font-size:1.4em;color:#D6EFFF;box-shadow:none;" data-rel="#app-info" href="'.url('flood/app/basin/select').'">'
		. 'เลือกลุ่มน้ำ : '
		. ($basin ? $floodCamera->{$basin}->title : 'ทุกลุ่มน้ำ')
		. ' <i class="icon -sort -white"></i>'
		. '</a>'
		. '</nav>';

	$ret .= '<div id="app-info">'._NL;

	if (i()->username=='softganz') {
		//$ret .= print_o(getallheaders(),'getallheaders()');
		//phpinfo();
		//$ret .= print_o($_SERVER,'$_SERVER');
	}


	$cardUi = new Ui('div', 'ui-card flood-camera-realtime flood-camera -app');

	foreach ($cams as $rs) {
		$ad = '';
		$sponsorName = SG\getFirst($rs->replaceSponsorName, $rs->sponsor_name);
		$sponsorLogo = SG\getFirst($rs->replaceSponsorLogo, $rs->sponsor_logo);

		if ($sponsorName == 'no') ; // no sponsor
		else if ($sponsorLogo) {
			$ad = '<img src="'.$sponsorLogo.'" alt="" />';
		} else {
			$ad = '<img src="https://www.hatyaicityclimate.org/file/ad/youradhere.jpg" alt="" />';
		}

		// This routine also call from home page, not include flood_model
		// So can't use thumb_url

		//$thumbUrl = flood_model::thumb_url($rs->name,$rs->last_photo);
		// _CACHE_URL.$rs->name.'-'.$rs->last_photo;
		$photoUrl = flood_model::photo_url($rs);

		$cardStr = '<div class="header"><h3><a href="'.url('flood/cam/'.$rs->camid).'">'.$rs->title.'</a></h3></div>'
		. '<div class="detail">'
		. '<a href="'.url('flood/app/cam/'.$rs->camid).'" class="sg-action" data-rel="#app-info" data-webview="'.$rs->title.'">'
		. '<img id="camera-image-'.$rs->name.'" class="-photo" src="'.$photoUrl.'?v=1" />'
		. '</a>'
		. ($ad ? '<div class="-ad" id="ad-'.$rs->name.'">'.$ad.'</div>' : '')
		. '<p class="-timestamp">'
		. '<span class="-date">'.sg_date($rs->last_updated,'ว ดด ปป').'</span>'
		. '<span class="-time">'.sg_date($rs->last_updated,'H:i').'</span>'
		. '</p>'
		. (date('U')-$rs->last_updated>90*60 ? '<p class="-error -not-update">ยังไม่ได้รับภาพใหม่</p>' : '')
		. '</div>';

		$cardUi->add(
			$cardStr,
			array(
				'id' => 'camera-'.$rs->name,
				'class' => 'camera -'.$rs->name,
			)
		);
	}

	$ret .= $cardUi->build()._NL;

	/*
	//$ret.=print_o($cams,'$cams');
	$ret.='<ul class="flood-hot-monitor">'._NL;
	foreach ($cams as $rs) {
		list($x,$y)=explode(',',$rs->location);
		$ad='<div class="cctv-ad">'._NL;
		if ($rs->sponsor_name=='no') {
			// no sponsor
		} else if ($rs->sponsor_logo) {
			$ad.='<img src="'.$rs->sponsor_logo.'" alt="" />'._NL;
		} else {
			$ad.='<img src="https://hatyaicityclimate.org/themes/default/youradhere.jpg?v=1" alt="" />'._NL;
		}
		$ad.='</div><!-- cctv-ad -->';

		$ret.='<li id="camera-'.$rs->name.'" class="cctv -'.$rs->name.'">'._NL;
		$ret.='<h3>'.$rs->title.'</h3>'._NL;
		$ret.='<div class="cctv-photo">'._NL;
		$ret.='<a href="'.url('flood/app/cam/'.$rs->camid).'" class="sg-action" data-rel="#app-info" data-webview="'.$rs->title.'">';
		$ret.='<img id="'.$rs->name.'" class="cctv-current" src="'.flood_model::photo_url($rs).'" />'._NL;
		$ret.='</a>';
		if ($rs->overlay_url) $ret.='<img class="photo-overlay" src="'.$rs->overlay_url.'?v=2" alt=""/>'._NL;
		$ret.='</div><!-- cctv-photo -->'._NL;
		$ret.=$ad._NL;
		$ret.='<p id="'.$rs->name.'-time" class="cctv-time -'.$rs->name.'">'
					.'<span class="date -date">'.sg_date($rs->last_updated,'ว ดด ปป').'</span>'
					.'<span class="date -time">'.sg_date($rs->last_updated,'H:i').'</span>'
					.'</p>';
		if (date('U')-$rs->last_updated>$notUpdateTime) $ret.='<p class="cctv-error -notupdate">ยังไม่ได้รับภาพใหม่</p>'._NL;
		$ret.='</li>'._NL;
		$cams_list[]=$rs->name;
	}
	$ret.='</ul>'._NL;
	*/

	$ret.='</div><!-- app-info -->'._NL;

	return $ret;
}
?>