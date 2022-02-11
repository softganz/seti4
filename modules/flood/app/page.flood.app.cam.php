<?php
/**
 * Camery monitor
 *
 * @param String $camid
 * @return String
 */
function flood_app_cam($self,$camid) {
	$showdate = SG\getFirst(post('d'),NULL);
	$items = SG\getFirst(post('items'),30);
	$show = SG\getFirst(post('show'),30);
	$self->theme->title = 'Camera Monitor';

	$cameraInfo = R::Model(
		'flood.camera.get',
		Array('camid' => $camid, 'date' => $showdate),
		Array('updateView' => true, 'photo'=>$items)
	);

	$self->theme->title = $cameraInfo->title;


	/*
	$stmt='SELECT l.*, w.bankheightleft, w.bankheightright, w.depth, w.gateheight FROM %flood_level% l LEFT JOIN %flood_water% w USING(`camid`) WHERE l.camid=:camid AND `priority`>=0 ORDER BY rectime DESC LIMIT 1';
	$water_level=mydb::select($stmt,':camid',$camid);
	$bankheightleft=$water_level->bankheightleft;
	$bankheightright=$water_level->bankheightright;
	$gateheight=$dbs->water_level->gateheight;
	$depth=$water_level->depth;
	*/

	$photoUrl = flood_model::photo_url($cameraInfo->photos[0]);

	$ret .= '<section class="flood-app-camera-view">';
	$ret .= '<header class="header -box"><h3>'.$cameraInfo->title.'</h3></header>';

	$ret .= '<div id="camera-'.$cameraInfo->name.'" class="flood-camera-realtime cctv-view">'._NL;
	$ret .= '<div class="cctv-last">'._NL;
	$ret .= '<img id="photo-last" class="photo-last -photo" src="'.$photoUrl.'" alt="Photo" title="คลิกบนภาพเพื่อดูภาพขยาย" />'._NL;
	if ($cameraInfo->overlay_url) {
		$ret .= '<img class="photo-overlay" src="'.$cameraInfo->overlay_url.'?v=2" alt=""/>';
	}
	$ret .= '<p class="-timestamp"><span class="-date">'.sg_date($cameraInfo->photos[0]->atdate,'ว ดด ปป').'</span><span class="-time">'.sg_date($cameraInfo->photos[0]->atdate,'H:i').'</span></p>'._NL;
	$ret .= '<div class="-ad" id="ad-'.$cameraInfo->name.'">'._NL;


	$sponsorName = SG\getFirst($cameraInfo->replaceSponsorName, $cameraInfo->sponsor_name);
	$sponsorLogo = SG\getFirst($cameraInfo->replaceSponsorLogo, $cameraInfo->sponsor_logo);
	$sponsorText = SG\getFirst($cameraInfo->replaceSponsorText, $cameraInfo->sponsor_text);

	if ($sponsorName == 'no') {
		// no sponsor
	} else if ($sponsorLogo) {
		$ret .= '<img src="'.$sponsorLogo.'" alt="" /><span>'.$sponsorText.'</span>';
	} else {
		$ret .= '<a href="/sponsor"><img src="/themes/default/youradhere.jpg" alt="" /></a><span><a href="/sponsor">พื้นที่ขอรับการสนับสนุนค่าใช้จ่ายของกล้อง CCTV สนใจติดต่อ <strong>โทร. 081 698 1975</strong></a></span>';
	}
	$ret .= '</div><!--cctv-ad-->'._NL;
	$ret .= '</div><!--cctv-last-->'._NL;
	$ret .= '</div><!-- flood-cctv-view -->'._NL;


	// Show thumbnail
	$ret .= '<div id="flood-camera-info" class="flood-camera-info">'._NL;
	$ret .= '<ul id="flood-camera-photos" class="photo-list">'._NL;
	foreach ($cameraInfo->photos as $i => $rs) {
		if ($i >= $show) break;
		$photoUrl = flood_model::photo_url($rs);
		$ret .= '<li>'
			.'<a class="sg-action" href="'.$photoUrl.'" data-image="'.$photoUrl.'" data-date="'.sg_date($rs->atdate,'ว ดด ปป').'" data-time="'.sg_date($rs->atdate,'H:i').'">'
			.'<img id="'.$cameraInfo->name.'-'.$rs->aid.'" src="'.flood_model::thumb_url($rs).'" />'
			.'</a>'
			.'<p>'.sg_date($rs->atdate,'H:i').' น.'
			.(0 && user_access('administrator floods,operator floods')?' <span><a href="'.url('flood/camera/deletephoto/'.$rs->aid).'" title="ลบภาพนี้ทิ้ง" class="icon flood-del-thumb">X</a></span>':'')
			.'</p>'
			.'</li>'._NL;
	}
	$ret .= '</ul>'._NL;

	$ret .= '</div><!-- flood-camera-info -->'._NL;

	$ret .= '<div id="flood-camera-desc">'.(user_access('administrator floods','edit own flood content',$cameraInfo->uid)?'<a href="'.url('flood/camera/edit/'.$cameraInfo->camid).'" fld="appdesc">แก้ไข</a><br clear="all" />':'').$cameraInfo->appdesc.'</div>';

	//  The Open Graph Protocol
	$opengraph->title = $cameraInfo->title.' - '.cfg('web.title');
	$opengraph->type = 'website';
	$opengraph->url = url('flood/cam/'.$camid);
	$opengraph->image = $photoUrl;
	$opengraph->description = 'ภาพบันทึกจากกล้อง IP Camera ที่ '.$cameraInfo->title.' เมื่อเวลา '.sg_date($cameraInfo->photos[0]->atdate,'ว ดด ปป H:i').' น.';
	sg::add_opengraph($opengraph);

	$ret .= '<script type="text/javascript">
	$(".photo-list a").click(function(){
		var $this = $(this)
		var $photoImage = $("#photo-last")
		console.log($this.attr("href"))
		$photoImage.attr("src", $this.attr("href"))
		$(".-date").text($this.data("date"))
		$(".-time").text($this.data("time"))
		return false
	});
	</script>';

	$ret .= '</section><!-- flood-app-camera-view -->';

	//unset($cameraInfo->desc,$cameraInfo->appdesc);
	//$ret.=print_o($cameraInfo,'$cameraInfo');
	return $ret;
}
?>