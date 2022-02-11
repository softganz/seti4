<?php
/**
* Garage :: User management
* Created 2019-12-02
* Modify  2019-12-02
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function garage_shop_member($self, $shopInfo = NULL) {
	if (!$shopInfo) $shopInfo = R::Model('garage.get.shop');
	if (!($shopId = $shopInfo->shopid)) return message('error', 'PROCESS ERROR');

	new Toolbar($self,'พนักงาน');

	$isEdit = in_array($shopInfo->iam, array('ADMIN','MANAGER'));

	if (!$isEdit) return message('error', 'Access denied');

	$ret .= R::View('button.floating', '<a class="sg-action btn -floating -circle48" href="'.url('garage/shop/0/member.form').'" data-rel="box" data-width="640"><i class="icon -material">add</i></a>');
	//'<div class="btn-floating -right-bottom"><a class="sg-action btn -floating -circle48" href="'.url('garage/shop/0/member.form').'" data-rel="box" data-width="640"><i class="icon -material">add</i></a></div>';

	$tables = new Table();
	$tables->thead = array(
		'photo -center'=>'',
		'ชื่อสมาชิก',
		'group -center'=>'กลุ่ม',
		'position -center' => 'ตำแหน่ง',
		'date -hover-parent'=>'สร้างเมื่อ'
	);

	$stmt = 'SELECT
			gu.*
		, u.`username`, u.`name`, UPPER(gu.`membership`) `membership`, u.`datein`
		, s.`shortname` `shopShortName`
		FROM %garage_user% gu
			LEFT JOIN %users% u USING(`uid`)
			LEFT JOIN %garage_shop% s USING(`shopid`)
		WHERE (gu.`shopid` = :shopid OR s.`shopparent` = :shopid) AND u.`status` = "enable"
		ORDER BY FIELD(`membership`,"ADMIN","MANAGER","OWNER","ACCOUNTING","OFFICER","INVENTORY","CARIN","FOREMAN","TECHNICIAN", "MEMBER", "DELETED") ASC, CONVERT(`name` USING tis620) ASC
		';

	$memberDbs = mydb::select($stmt,':shopid',$shopId);


	$isViewMemberProfile = $isEdit;

	foreach ($memberDbs->items as $rs) {
		if ($rs->uid == 1) continue;
		$submenuUi = new Ui();
		$submenuUi->addConfig('nav', '{class: "nav -icons -hover"}');
		if ($isEdit) {
			if ($rs->membership != 'DELETED') $submenuUi->add('<a class="sg-action" href="'.url('garage/shop/0/member.form/'.$rs->uid).'" data-rel="box" data-width="640"><i class="icon -material">edit</i></a>');
			if (!in_array($rs->membership, array('ADMIN', 'MANAGER', 'DELETED'))) {
				$submenuUi->add('<a class="sg-action" href="'.url('garage/shop/0/info/member.remove/'.$rs->uid).'" title="ลบ" data-rel="notify" data-done="load" data-title="ลบสมาชิก" data-confirm="ต้องการลบข้อมูล กรุณายืนยัน?"><i class="icon -material">cancel</i></a>');
			}
			if ($rs->membership == 'DELETED') {
				$submenuUi->add('<a class="sg-action" href="'.url('garage/shop/0/info/member.recall/'.$rs->uid).'" title="เรียกคืนสมาชิก" data-rel="notify" data-done="load" data-title="เรียกคืนสมาชิก" data-confirm="ต้องการเรียกคืนสมาชิก กรุณายืนยัน?"><i class="icon -material">add</i></a>');
			}
		}

		$tables->rows[] = array(
			'<img src="'.model::user_photo($rs->username).'" width="32" height="32" alt="'.htmlspecialchars($rs->name).'" title="'.htmlspecialchars($rs->name).'" />',
			($isViewMemberProfile?'<span><a class="sg-action" href="'.url('garage/profile/'.$rs->uid).'" data-rel="box" data-width="320">':'').$rs->name.($isViewMemberProfile?'</a> '.(in_array($rs->membership, array('MEMBER','DELETED')) ? '('.$rs->username.')' : '').'</span>':''),
			$rs->membership.'@'.$rs->shopShortName,
			$rs->position,
			sg_date($rs->datein,'d/m/ปปปป H:i')
			.$submenuUi->build(),
		);
	}

	$ret .= $tables->build();

	$ret .= '<p>จำนวนพนักงานทั้งหมด <b>'.$memberDbs->count().'</b> คน</p>';

	//$ret .= print_o($memberDbs,'$memberDbs');

	//$ret .= print_o($shopInfo,'$shopInfo');
	return $ret;
}
?>