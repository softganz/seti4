<?php
/**
 * Home page
 *
 * @return String
 */
function saveup_admin_member_changeid($self) {
	$self->theme->title = 'กลุ่มออมทรัพย์ - เปลี่ยนไอดีสมาชิก';

	$ret .= '<form method="post" action="'.url('saveup/admin/member/changeid').'">';
	$ret .= '<label>ไอดีสมาชิกต้นทาง </label><input id="srcid" type="hidden" name="srcid" /><input type="text" name="srcname" class="sg-autocomplete" data-query="'.url('saveup/api/member').'" data-altfld="srcid" /> ';
	$ret .= '<label>ไอดีสมาชิกปลายทาง <input type="text" name="destid" class="sg-autocomplete" data-query="'.url('saveup/api/member').'" /> ';
	$ret .= '<button class="btn -primary" type="submit"><i class="icon -save -white"></i><span>เปลี่ยนไอดีสมาชิก</span></button>';
	$ret .= '</form>';

	$srcId = post('srcid');
	if (empty($srcId))
		list($srcId) = explode(' ',post('srcname'));

	list($destId) = explode(' ',post('destid'));

	if ($srcId && $destId) {

		$result = R::Model('saveup.member.changeid', $srcId, $destId);

		if ($result)
			$ret .= message('notify','เปลี่ยนรหัสสมาชิกจาก <b>'.$srcId.'</b> เป็น <b>'.$destId.'</b> เรียบร้อย');
		//$ret .= print_o($result,'$result');

		$ret .= '<div class="box">'.R::Page('saveup.member.view',NULL,$destId).'<br clear="all" /></div>';
	}


	//$ret.=print_o(post(),'post()');
	return $ret;
}
?>