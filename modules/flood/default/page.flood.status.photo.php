<?php
/**
* Flood Camera Photo
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function flood_status_photo($self, $camid) {
	$items = SG\getFirst(post('items'),30);
	$showdate = SG\getFirst(post('d'),NULL);
	$camname = R::Model('flood.camera.get',$camid)->name;

	$isAdmin = user_access('administrator floods,operator floods');

	mydb::where('p.`camid` = :camid',':camid',$camid);
	if ($showdate) mydb::where('p.`created` <= :showdate', ':showdate',sg_date($showdate.' 23:59:59','U'));

	mydb::value('$LIMIT$',$items);
	$stmt = 'SELECT
		p.`aid`, c.`name`, p.`photo`, p.`created` `atdate`
		, ROUND(p.`created`/86400)
		FROM %flood_photo% p
			LEFT JOIN %flood_cam% c USING(`camid`)
		%WHERE%
		ORDER BY aid DESC
		LIMIT $LIMIT$';

	$photos = mydb::select($stmt);

	$ret .= '<ul id="flood-camera-photos" class="photo-list">'._NL;
	foreach ($photos->items as $rs) {
		$photoUrl = flood_model::photo_url($rs);

		$ret .= '<li class="-hover-parent">'
			.'<a href="'.$photoUrl.'" target="_blank" data-image="'.$photoUrl.'">'
			.'<img id="'.$camname.'-'.$rs->aid.'" src="'.flood_model::thumb_url($rs).'" />'
			.'</a>'
			.'<p>'.sg_date($rs->atdate,'ว ดดปป H:i').' น.'
			.($isAdmin ? ' <nav class="nav iconset -hover"><a class="sg-action" href="'.url('flood/camera/deletephoto/'.$rs->aid).'" title="ลบภาพนี้ทิ้ง" data-rel="none" data-removeparent="li" data-confirm="ลบภาพนี้ทิ้ง"><i class="icon -cancel"></i></a></nav>':'')
			.'</p>'
			.'</li>'._NL;
	}
	$ret .= '</ul>'._NL;
	return $ret;
}
?>