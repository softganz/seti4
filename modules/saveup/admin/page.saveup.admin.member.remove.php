<?php
/**
 * Home page
 *
 * @return String
 */
function saveup_admin_member_remove($self) {
	$self->theme->title='กลุ่มออมทรัพย์ - ลบสมาชิก';

	$ret.='<form method="post" action="'.url('saveup/admin/member/remove').'">';
	$ret.='<label>สมาชิกที่ต้องการลบทิ้ง <input type="text" name="mid" class="sg-autocomplete" data-query="'.url('saveup/api/member').'" /><br />';
	$ret.='<input type="checkbox" name="confirm" value="yes" /> ยืนยันการลบสมาชิก<br />';
	$ret.='<button type="submit">ลบสมาชิก</button>';
	$ret.='</form>';

	if (post('mid') && SG\confirm()) {
		$mid=post('mid');
		debugMsg('Remove saveup id '.$mid);
		$result=R::Model('saveup.member.remove',$mid);
		$ret.='<p class="notify">ลบสมาชิก <b>'.$mid.'</b> เรียบร้อย</p>';
	}


	//$ret.=print_o(post(),'post()');
	return $ret;
}
?>