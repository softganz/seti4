<?php
/**
* Module Method
* Created 2020-01-01
* Modify  2020-01-01
*
* @param Object $self
* @param Int $var
* @return String
*
* @usage project/fund/$orgId/info.member
*/

$debug = true;

function project_fund_info_member($self, $fundInfo) {
	if (!($orgId = $fundInfo->orgid)) return message('error', 'PROCESS ERROR:NO FUND');

	$isCreateMember = $fundInfo->right->createMember;
	$isAccessMember = $fundInfo->right->accessMember;
	$isViewMemberProfile = $fundInfo->right->viewMemberProfile;

	if (!$isAccessMember) return '<p class="notify">ขออภัย : สำหรับเจ้าหน้าที่'.$fundInfo->name.'เท่านั้น</p>';

	$ret = '';

	$ret .= '<header class="header"><h3>สมาชิกร่วมงานกองทุน</h3></header>';

	if ($isCreateMember) {
		$ret.='<div class="btn-floating -right-bottom"><a class="sg-action btn -floating -circle48" href="'.url('project/fund/'.$orgId.'/member.create').'" data-rel="#main"><i class="icon -material -white">person_add</i></a></div>';
	}

	$tables = new Table();
	$tables->thead = array('center -1'=>'','ชื่อสมาชิก','center -2'=>'กลุ่ม','date -hover-parent'=>'สร้างเมื่อ');

	$stmt = 'SELECT
			u.`uid`, u.`username`, u.`name`, UPPER(tu.`membership`) `membership`, u.`datein`
		FROM %org_officer% tu
			LEFT JOIN %users% u USING(`uid`)
		WHERE tu.`orgid` = :orgid AND u.`status` = "enable"
		UNION
		SELECT
			u.`uid`, u.`username`, u.`name`, "DELETED", u.`datein`
			FROM %users% u
			LEFT JOIN %org_officer% oo ON oo.`orgid` = :orgid AND oo.`uid` = u.`uid`
		WHERE u.`username` LIKE :fundid AND oo.`orgid` IS NULL
		ORDER BY FIELD(`membership`,"ADMIN","OWNER","OFFICER","TRAINER","MANAGER","MEMBER", "DELETED") ASC, CONVERT(`name` USING tis620) ASC
		';

	$member = mydb::select($stmt,':orgid',$fundInfo->orgid, ':fundid', $fundInfo->fundid.'-%');
	//$ret.=print_o($member,'$member');

	//$ret.='<ul class="project__member">'._NL;
	foreach ($member->items as $mrs) {
		$submenuUi = new Ui();
		if ($isCreateMember) {
			if ($mrs->membership == 'MEMBER') {
				$submenuUi->add('<a class="sg-action" href="'.url('project/fund/'.$orgId.'/info/member.cancel/'.$mrs->uid).'" title="ลบ" data-rel="#main" data-ret="'.url('project/fund/'.$orgId.'/info.member').'" data-title="ยกเลิกการใช้งานของสมาชิก" data-confirm="ต้องการยกเลิกการใช้งานของสมาชิก กรุณายืนยัน?"><i class="icon -cancel"></i></a>');
			}
			if ($mrs->membership == 'DELETED') {
				$submenuUi->add('<a class="sg-action" href="'.url('project/fund/'.$orgId.'/info/member.recall/'.$mrs->uid).'" title="เรียกคืนสมาชิก" data-rel="#main" data-ret="'.url('project/fund/'.$orgId.'/info.member').'" data-title="เรียกคืนสมาชิก" data-confirm="ต้องการเรียกคืนสมาชิก กรุณายืนยัน?"><i class="icon -addbig"></i></a>');
			}
		}
		$menu = $submenuUi->count() ? '<nav class="nav iconset -hover">'.$submenuUi->build().'</nav>' : '';

		$tables->rows[] = array(
			'<img src="'.model::user_photo($mrs->username).'" width="32" height="32" alt="'.htmlspecialchars($mrs->name).'" title="'.htmlspecialchars($mrs->name).'" />',
			($isViewMemberProfile ? '<span><a class="sg-action" href="'.url('project/fund/'.$orgId.'/info.member.view/'.$mrs->uid).'" data-rel="box" data-width="640">':'').$mrs->name.($isViewMemberProfile?'</a> '.(in_array($mrs->membership, array('MEMBER','DELETED')) ? '('.$mrs->username.')' : '').'</span>':''),
			$mrs->membership,
			sg_date($mrs->datein,'d/m/ปปปป H:i')
			.$menu,
		);
	}

	$ret.=$tables->build();

	return $ret;
}
?>