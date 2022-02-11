<?php
/**
* iMed :: User List
* Created 2019-05-06
* Modify  2020-12-16
*
* @param Object $self
* @param Int $uid
* @return String
*
* @usage imed/admin/user/{id}
*/

$debug = true;

function imed_admin_user($self, $uid = NULL) {
	// Data Model
	$userInfo = R::Model('user.get',$uid);
	$zoneList = imed_model::get_user_zone($userInfo->uid);

	$stmt = 'SELECT s.*, o.`name` FROM %imed_socialmember% s LEFT JOIN %db_org% o USING(`orgid`) WHERE s.`uid` = :uid';
	$groupDbs = mydb::select($stmt, ':uid', $uid);

	$stmt = 'SELECT s.`pid` `psnid`, CONCAT(p.`prename`, " ", p.`name`, " ", p.`lname`) `fullname`, COUNT(*) `serviceTotals` FROM %imed_service% s LEFT JOIN %db_person% p ON p.`psnid` = s.`pid` WHERE s.`uid` = :uid GROUP BY `psnid`';
	$visitDbs = mydb::select($stmt, ':uid', $uid);


	// View Model
	$ret = '<section id="imed-admin-user" data-url="'.url('imed/admin/user/'.$uid).'">';

	$headerNav = new Ui();
	$headerNav->config('container', '{tag: "nav", class: "nav -header"}');
	$headerNav->add('<a class="sg-action btn -link" href="'.url('imed/admin/user/'.$uid).'" data-rel="replace:#imed-admin-user"><i class="icon -refresh"></i></a>');
	if ($user->uid!=1 && user_access('access administrator pages,administer users')) {
		$headerNav->add(' <a class="btn -link" href="'.url('admin/user/logas/name/'.$userInfo->username).'"><i class="icon -material">how_to_reg</i><span>LOG AS</span></a>');
	}
	$headerNav->add('<a class="sg-action btn -link" href="'.url('admin/user/edit/'.$userInfo->uid).'" data-rel="box" title="แก้ไขรายละเอียดสมาชิก" data-width="640"><i class="icon -edit"></i></a>');

	$ret .= '<header class="header -box">'.(R()->appAgent ?  '' : _HEADER_BACK).'<h3>ข้อมูลสมาชิก : '.$userInfo->name.'</h3>'.$headerNav->build().'</header>';


	$ret .= '<div class="-sg-text-center"><img class="member-photo" src="'.model::user_photo($userInfo->username).'" alt="'.htmlspecialchars($user->name).'" width="64" height="64" style="border-radius: 50%;" />';
	$ret.='<p><strong>'.$userInfo->name.'<br />('.$userInfo->username.')</strong>';
	$ret.='</p></div>';

	$form = new Form(NULL, url('imed/admin/info/zone.add/'.$uid), 'user-add-zone', 'sg-form report-form');
	$form->addData('checkValid', true);
	$form->addData('rel', 'notify');
	$form->addData('done', 'load:#imed-admin-user');
	$form->addConfig('title', 'พื้นที่จัดการข้อมูล');
	$form->addField('areacode', array('type'=>'hidden'));
	$form->addField(
		'areaname',
		array(
			'type' => 'text',
			'label' => 'เลือกพื้นที่:',
			'class' => 'sg-autocomplete -fill',
			'placeholder' => 'ระบุตำบล หรือ อำเภอ หรือ จังหวัด',
			'attr' => array(
				'data-query' => url('api/address'),
				'data-altfld' => 'edit-areacode',
			),
			'posttext' => '<div class="input-append"><span><button class="btn -primary"><i class="icon -material">add</i><span>เพิ่มพื้นที่</span></button></span></div>',
			'container' => '{class: "-group -label-in"}',
		)
	);

	$form->addField(
		'module',
		array(
			'type' => 'select',
			'label' => 'โมดูล:',
			'class' => '-fill',
			'options' => array('imed' => 'คนพิการ', 'imed.poorman' => 'คนยากลำบาก'),
			'container' => '{class: "-label-in"}',
		)
	);

	$form->addField(
		'refid',
		array(
			'type' => 'select',
			'label' => 'แบบสอบถาม:',
			'class' => '-fill',
			'options' => array(0 => 'ทุกแบบสอบถาม', 1 => 'แบบสอบถามคนพิการ', 4 => 'แบบสอบถาม สปจ.'),
			'container' => '{class: "-label-in"}',
		)
	);

	$form->addField(
		'right',
		array(
			'type' => 'radio',
			'label' => 'สิทธิ์ในการเข้าถึงข้อมูล:',
			'class' => '-fill',
			'require' => true,
			'options' => array('view' => 'ดูอย่างเดียว', 'edit' => 'ดู/แก้ไข', 'admin' => 'แอดมิน'),
			'container' => '{class: ""}',
		)
	);

	$ret .= $form->build();

	$zoneCard = new Ui('div', 'ui-card');

	foreach ($zoneList as $item) {
		$zone = trim(($item->subdistname?'ต.'.$item->subdistname:'').($item->distname?' อ.'.$item->distname:'').($item->provname?' จ.'.$item->provname:''));

		$zoneCard->add(
			'<div class="header"><h5>'.$zone.' ('.$item->zone.')'.'</h5>'
			. '<nav class="nav -header"><a class="sg-action" href="'.url('imed/admin/info/zone.delete/'.$uid,array('zone'=>$item->zone,'module'=>$item->module,'refid'=>$item->refid)).'" data-rel="none" data-done="remove:parent .ui-card>.ui-item" data-title="ลบพื้นที่" data-confirm="ต้องการลบสิทธื์ในการเข้าถึงข้อมูลของพื้นที่นี้ กรุณายืนยัน?"><i class="icon -material -gray">cancel</i></a></nav>'
			. '</div>'
			. '<div class="detail">'
			. 'โมดูล <b>'.$item->module.'</b>'
			. ' แบบสอบถาม <b>'.($item->refid != 0 ? $item->refid : '*').'</b>'
			. ' สิทธิ์ <b>'.$item->right.'</b>'
			. '</div>'
		);
	}

	$ret .= $zoneCard->build();
	
	$ret .= '<p>หมายเหตุ : '.$userInfo->address.' '.$userInfo->admin_remark.'</p>';
	//$ret.=print_o($zoneList,'$zoneList');

	$ret .= '<h5>กลุ่ม</h5>';
	$groupCard = new Ui(NULL, 'ui-menu');
	foreach ($groupDbs->items as $rs) {
		$groupCard->add('<a class="sg-action" href="'.url('imed/social/'.$rs->orgid).'" data-rel="box">'.$rs->name.'</a>');
	}
	if ($groupCard->count() == 0) $groupCard->add('ไม่มีกลุ่ม');

	$ret .= $groupCard->build();

	$ret .= '<h5>เยี่ยมบ้าน</h5>';

	$visitCard = new Ui(NULL, 'ui-menu');
	foreach ($visitDbs->items as $rs) {
		$patientUrl = $options->page == 'app' ? '<a class="sg-action" href="'.url('imed/app/'.$rs->psnid).'" data-webview="'.$rs->fullname.'">' : '<a class="sg-action" href="'.url('imed/patient/'.$rs->psnid).'" data-rel="box" data-width="640">';
		$visitCard->add($patientUrl.SG\getFirst($rs->fullname,'ไม่ระบุ').' ('.$rs->serviceTotals.' ครั้ง)</a>');
	}
	$ret .= $visitCard->build();

	$ret .= '</section>';

	return $ret;
}
?>