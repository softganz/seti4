<?php
/**
* Module : Module Method
* Created 2020-08-01
* Modify  2020-08-01
*
* @param Object $self
* @param Int $var
* @return String
*
* @usage module/{$Id}/method
*/

$debug = true;


// Load flood manifest for calling from homepage
//R::Manifest('flood');

function flood_camera_basin($self,$setid = 'HOME') {
	$cameraIdList = cfg('flood.camera')->{$setid}->camera;

	$cams = R::Model('flood.camera.list',$cameraIdList);
	//$ret.=print_o($cams,'$cams');


	foreach ($dbs->items as $rs) {
		$cams[$rs->camid] = $rs;
	}


	$cardUi = new Ui('div', 'ui-card flood-camera -hots');

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
		//$photoUrl = flood_model::thumb_url($rs);

		$cardStr = '<div class="header"><h3><a href="'.url('flood/cam/'.$rs->camid).'">'.$rs->title.'</a></h3></div>'
		. '<div class="detail">'
		. '<a href="'.url('flood/cam/'.$rs->camid).'" title="'.($sponsorName?'สนับสนุนภาพจาก '.$sponsorName:'').'">'
		. '<img id="camera-image-'.$rs->name.'" class="-photo" src="'.$photoUrl.'?v=1" />'
		. '</a>'
		. ($ad ? '<div class="-ad" id="ad-'.$rs->name.'">'.$ad.'</div>' : '')
		. '<p class="-timestamp">'
		. '<span class="-date">'.sg_date($rs->last_updated,'ว ดด ปป').'</span>'
		. '<span class="-time">'.sg_date($rs->last_updated,'H:i').'</span>'
		. '</p>'
		. (date('U')-$rs->last_updated>90*60 ? '<p class="-error -not-update">ยังไม่ได้รับภาพใหม่</p><span class="-status-dot -not-update"></span>' : '<span class="-status-dot"></span>')
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

	if (is_admin()) $ret .= '<div class="flood-hot-monitor-info">Camera Set CFG = '.$setid.'<a class="sg-action btn -link" href="'.url('admin/config/edit',['name' => 'flood.camera']).'" data-rel="box" data-width="800"><i class="icon -material">edit</i></a><br />CameraList = '.$cameraIdList.'</div>';

	return $ret;
}
?>