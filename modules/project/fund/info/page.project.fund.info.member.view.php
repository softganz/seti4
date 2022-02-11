<?php
/**
* Project :: View Member Information
* Created 2020-06-07
* Modify  2020-06-08
*
* @param Object $self
* @param Object $fundInfo
* @param Int $uid
* @return String
*
* @usage project/fund/$orgId/info.view/$uid
*/

$debug = true;

function project_fund_info_member_view($self, $fundInfo, $uid = NULL) {
	if (!($orgId = $fundInfo->orgid)) return message('error', 'PROCESS ERROR:NO FUND');

	$user = R::Model('user.get',$uid);
	if ($user->_empty) return message('error','User <em>'.$uid.'</em> not exists.');

	$isViewMemberProfile = $fundInfo->right->viewMemberProfile;
	$isLogAs = $user->uid != 1 && user_access('access administrator pages');


	// Set admin menu
	$headerUi = new Ui();
	if ($isLogAs) {
		$headerUi->add('<a class="btn" href="'.url('admin/user/logas/name/'.$user->username).'"><i class="icon -material">how_to_reg</i><span>LOG AS '.$user->username.'</span></a>');
	}


	$ret = '<header class="header -box">'._HEADER_BACK.'<h3>'.$user->name.'</h3><nav class="nav">'.$headerUi->build().'</nav></header>';

	$ret .= '<div class="sg-view -co-2">';

	$ret .= '<div class="-sg-view">'
		. '<img class="member-photo" src="'.model::user_photo($user->username).'" alt="'.htmlspecialchars($user->name).'" width="200" height="200" style="margin: 32px auto; display: block;" /><br />'._NL;


	if (!$isViewMemberProfile) return $ret.'</div></div>';


	$ret .= ($user->real_name || $user->mid_name || $user->last_name ? 'ชื่อจริง'.' : '.($user->name_prefix?$user->name_prefix.' ':'').$user->real_name.($user->mid_name?' ('.$user->mid_name.')':'').' '.$user->last_name.'<br /><br />' : '')
		. '<br />อีเมล์ : '.$user->email.'<br />
		โทรศัพท์ '.$user->mobile.' '.$user->phone.'<br />'._NL
		. ($user->occupation ? 'อาชีพ'.' : '.$user->occupation.'<br /><br />':'')._NL
		. ($user->position? 'ตำแหน่ง : '.$user->position.'<br /><br />':'')._NL
		. ($user->organization ? 'องค์กร / บริษัท : '.$user->organization.'<br /><br />':'')._NL
		. ($user->address || $user->amphur || $user->province ? 'ที่อยู่ : '.$user->address.' '.$user->amphur.' '.$user->province.' '.$user->zipcode.' '.$user->country.'<br /><br />':'')._NL
		. ($user->phone && (user_access('administer users') || i()->uid==$user->uid) ? 'โทรศัพท์ : '.$user->phone.'<br /><br />':'')._NL
		. ($user->mobile && (user_access('administer users') || i()->uid==$user->uid) ? 'โทรศัพท์เคลื่อนที่ : '.$user->mobile.'<br /><br />':'')._NL
		. ($user->fax ? 'แฟกซ์ : '.$user->fax.'<br /><br />':'')._NL
		. ($user->website ? 'เว็บไซท์ : '.'<a href="'.$user->website.'" target="_blank">'.$user->website.'</a><br /><br />':'')._NL
		. ($user->about ? 'ประวัติย่อ'.' : <br /><br />'.sg_text2html($user->about):'').'<br />'._NL;

	$ret .= '</div>';

	$ret .= '<div class="-sg-view">'
		. '<p>'.'เริ่มเป็นสมาชิกตั้งแต่ วัน'.sg_date($user->datein,'ววว ว ดด ปป H:i').' น.<br />'
		. ($user->login_time ? 'เข้าระบบล่าสุดเมื่อ วัน'.sg_date($user->login_time,'ววว ว ดด ปปปป H:i').' น.'.'<br />' : '')
		. 'เข้าชมเว็บไซท์ : '.number_format($user->hits).' ครั้ง<br />อ่าน : '.number_format($user->views).' ครั้ง'
		. '</p>'
		. '</div>';

	$ret .= '</div>';

	return $ret;
}
?>