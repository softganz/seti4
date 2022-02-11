<?php
/**
* Module Method
*
* @param 
* @return String
*/

$debug = true;

function view_flood_event_render_comment($rs) {
	$ret='<div class="flood-event-show-comment-item"><img class="profile" src="'.model::user_photo($rs->username).'" width="24" height="24" /><strong>'.$rs->name.'</strong> '.$rs->msg.'<div>'.sg_date($rs->created,'ว ดด ปปปป H:i').' น.</div></div>'._NL;
	return $ret;
}
?>