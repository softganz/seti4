<?php
/**
 * Tabs menu
 *
 * @param String $active
 * @param Integer $fid Franchise ID
 * @param String $shopname Owner username
 * @return String
 */
function view_ibuy_franchise_menu($active = NULL,$fid = NULL,$shopname = NULL) {
	$ret='<ul class="tabs tabs-primary">'._NL;
	$ret.='<li class="tabs-franchise'.($active=='franchise'?' -active':'').'"><a href="'.url('ibuy/franchise').'">เฟรนไชส์</a></li>'._NL;
	$ret.='<li class="tabs-resaler'.($active=='resaler'?' -active':'').'"><a href="'.url('ibuy/resaler').'">ตัวแทนจำหน่าย</a></li>'._NL;
	if ($shopname) $ret.='<li'.($active=='detail'?' class="-active"':'').'><a href="'.url('ibuy/franchise/'.$shopname).'">รายละเอียด</a></li>'._NL;
	if ($fid && (user_access('administer ibuys') || $fid==i()->uid)) $ret.='<li'.($active=='edit'?' class="-active"':'').'><a href="'.url('ibuy/franchise/modify/'.$fid).'">แก้ไขรายละเอียด</a></li>'._NL;
	if ($fid && user_access('administer ibuys')) $ret.='<li'.($active=='logas'?' class="-active"':'').'><a href="'.url('admin/user/logas/name/'.$shopname).'">เข้าสู่ระบบด้วยชื่อนี้</a></li>'._NL;
	$ret.='</ul>'._NL;

	return $ret;
}
?>