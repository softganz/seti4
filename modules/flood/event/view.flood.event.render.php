<?php
/**
* Module Method
*
* @param 
* @return String
*/

$debug = true;

function view_flood_event_render($rs, $comments=NULL) {
	$isAdmin=i()->admin;
	$isEdit=user_access('administrator floods','edit own flood content',$rs->uid);
	$flagList=array('Red'=>'แดง','Yellow'=>'เหลือง','Green'=>'เขียว');

	$ret='<div class="flood-event-show-item" data-id="'.$rs->eid.'">'._NL;
	$ret.='<div class="poster"><img class="profile" src="'.model::user_photo($rs->username).'" width="24" height="24" /><strong>'.$rs->name.'</strong>'.($rs->where?' ที่ <strong>'.$rs->where.'</strong>':'').' เมื่อ <strong>'.sg_date($rs->when,'ว ดด ปปปป H:i').'</strong>';
	$ret.='</div>'._NL;

	$ui=new Ui(NULL,'iconset');
	$dropmenu=new Ui();
	if ($isAdmin) {
		$dropmenu->add('<a class="sg-action" href="'.url('flood/event/makeheadline/'.$rs->eid).'" data-rel="none">Make As Headline</a>');
	}
	if ($isEdit) $ui->add('<a class="sg-action" href="'.url('flood/event/delete/'.$rs->eid).'" title="ลบรายการนี้" data-rel="none" data-confirm="ลบรายการนี้?" data-removeparent="div"><i class="icon -delete"></i><span class="-hidden">ลบ</span></a>');
	$ui->add(sg_dropbox($dropmenu->build()));
	$ret.=$ui->build();

	if ($rs->photo) {
		$photoUrl = flood_model::chatphoto_url($rs);
		$ret.='<div class="photo-th"><a class="sg-action" href="'.$photoUrl.'" data-rel="img"><img class="flood-event-photo" src="'.$photoUrl.'" /></a></div>';
	}
	if ($rs->station) $ret.='<div class="sensor"><strong>สถานี '.$rs->station.' : '.$rs->stationTitle.'</strong><br />'.($rs->staffflag?'<span class="flag--'.$rs->staffflag.'">สถานการณ์ : ธง'.$flagList[$rs->staffflag].'</span><br />':'').($rs->sensorvalue===NULL?'':' ระดับน้ำ : '.number_format($rs->sensorvalue,2).' เมตร').'</div>';
	$ret.='<div class="msg">'.$rs->msg.'</div>'._NL;
	if (!$rs->station) $ret.='<div class="status">'.sg_date($rs->created,'ว ดด ปป H:i').'</div>'._NL;
	$ret.='<div class="flood-event-show-comment">'._NL;
	if ($comments) {
		foreach ($comments as $comment) {
			$ret .= R::View('flood.event.render.comment',$comment);
		}
	}
	if (i()->ok && !$rs->station) {
		$ret.='<form method="post" action="'.url('flood/event/post').'"><input type="hidden" name="parent" value="'.$rs->eid.'" /><img class="profile" src="'.model::user_photo(i()->username).'" width="24" height="24" /><textarea name="msg" class="form-textarea flood-event-comment-box" rows="1" cols="20" placeholder="เขียนความคิดเห็น..."></textarea></form>'._NL;
	}
	$ret.='</div>'._NL;
	$ret.='</div><!--flood-event-show-item-->'._NL;
	return $ret;
}
?>