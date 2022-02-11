<?php
/**
* Project :: Fund Officer
* Created 2020-06-07
* Modify  2020-06-07
*
* @param Object $self
* @param Object $fundInfo
* @param String $action
* @return String
*
* @usage project/fund/$orgId/info.officer[/$action]
*/

$debug = true;

function project_fund_info_officer($self, $fundInfo, $action = NULL) {
	if (!($orgId = $fundInfo->orgid)) return message('error', 'PROCESS ERROR:NO FUND');

	$isCreateMember = $fundInfo->right->createMember;
	$isViewMemberProfile = $fundInfo->right->viewMemberProfile;

	$ret = '';

	$tables = new Table();
	$tables->thead = array('','ชื่อ','pos -hover-parent' => 'ตำแหน่ง');
	$tables->addConfig('showHeader', false);

	if ($isCreateMember && $action == 'add') {

		$ret = '<form class="sg-form" action="'.url('project/fund/'.$orgId.'/info/officer.save').'" data-rel="notify" data-done="load->replace:this:'.url('project/fund/'.$orgId.'/info.officer').'">';

		$stmt = 'SELECT * FROM %users% WHERE `status` = "enable" AND `username` LIKE "T'.$fundInfo->info->changwat.'%" AND `name` != "ชื่อพี่เลี้ยง"';

		$dbs = mydb::select($stmt);

		foreach ($dbs->items as $rs) {
			$optionStr .= '<option value="'.$rs->uid.'">'.$rs->name.'</option>';
		}

		$tables->rows[] = array(
			'',
			'<select class="form-select" name="uid"><option value="">===เลือกชื่อ===</option>'.$optionStr.'</select>',
			'<select class="form-select -fill" name="type"><option value="">===เลือกกลุ่ม==</option><option value="TRAINER">พี่เลี้ยงกองทุน</option></select>',
			'<button class="btn -primary" type="submit">เพิ่ม</button>'
		);
	}

	$stmt = 'SELECT u.`uid`, u.`username`, u.`name`, UPPER(tu.`membership`) `membership`
		FROM %org_officer% tu
			LEFT JOIN %users% u USING(`uid`)
		WHERE tu.`orgid` = :orgid AND u.`status` = "enable" AND tu.`membership` != "MEMBER"
		ORDER BY FIELD(tu.`membership`,"ADMIN","OWNER","OFFICER","TRAINER","MANAGER") ASC';

	$member = mydb::select($stmt,':orgid',$orgId);
	//$ret .= print_o($member,'$member');

	foreach ($member->items as $mrs) {
		$tables->rows[]=array(
			'<img src="'.model::user_photo($mrs->username).'" width="32" height="32" alt="'.htmlspecialchars($mrs->name).'" title="'.htmlspecialchars($mrs->name).'" />',
			($isViewMemberProfile?'<span><a class="sg-action" href="'.url('project/fund/'.$orgId.'/info.member.view/'.$mrs->uid).'" data-rel="box" data-width="640">':'').$mrs->name.($isViewMemberProfile?'</a></span>':''),
			$mrs->membership
			. ($isCreateMember && in_array($mrs->membership,array('TRAINER','MEMBER')) ? '<nav class="nav -icons -hover"><a class="sg-action" href="'.url('project/fund/'.$orgId.'/info/member.remove/'.$mrs->uid).'" title="ลบ" data-rel="notify" data-done="remove:parent tr" data-title="ลบสมาชิกออกจากกองทุน" data-confirm="ต้องการลบสมาชิกออกจากกองทุน กรุณายืนยัน?"><i class="icon -cancel"></i></a></nav>':'')
		);
	}

	$ret .= $tables->build();

	if ($isCreateMember && $action == 'add') {
		$ret .= '</form>';
	}

	return $ret;
}
?>