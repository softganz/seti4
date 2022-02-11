<?php
/**
* Module :: Description
* Created 2021-08-01
* Modify  2021-08-01
*
* @param String $arg1
* @return Widget
*
* @usage module/{id}/method
*/

$debug = true;

class ImedAppSocialInfo extends Page {
	var $orgId;
	var $orgInfo;

	function __construct($orgId = NULL) {
		$this->orgInfo = is_object($orgId) ? $orgId : R::Model('imed.social.get', $orgId, '{}');
		$this->orgId = $this->orgInfo->orgId;
	}

	function build() {
		if (!$this->orgId) return message('error','ไม่มีข้อมูลของกลุ่มที่ระบุ');

		$isAdmin = $this->orgInfo->RIGHT & _IS_ADMIN;
		$isMember = $this->orgInfo->is->socialtype;
		$isRemovePatient = $isAdmin || in_array($isMember,array('MODERATOR','CM'));

		if (!($isAdmin || $isMember)) return message('error','ขออภัย ท่านไม่ได้อยู่ในกลุ่มนี้');

		// R::View('imed.toolbar', $self, $orgInfo->name, 'app.social', $orgInfo);

		// $ret .= R::Page('imed.app.social.patient', NULL, $orgId)->build();

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => '@'.$this->orgInfo->name,
				'navigator' => [
					new Ui([
						'class' => 'ui-nav -info',
						'children' => [
							'<a class="sg-action" href="'.url('imed/rehab/'.$this->orgId.'/visit').'" data-rel="#main"><i class="icon -material">house</i><span class="-hidden">{tr:เยี่ยมบ้าน}</span></a>',
							'<a class="sg-action" href="'.url('imed/app/social/patient/'.$this->orgId).'" data-rel="#main" data-webview="ดูแล"><i class="icon -material">accessible</i><span class="-hidden">{tr:Patients}</span></a>',
							'<a class="sg-action" href="'.url('imed/social/'.$this->orgId.'/member').'" data-rel="#main" data-webview="Group Members"><i class="icon -material">people</i><span class="-hidden">{tr:Members}</span></a>',
							$isAdmin || $isPoCenter ? '<a class="sg-action" href="'.url('imed/app/pocenter/'.$this->orgId).'" data-rel="#main" data-webview="กายอุปกรณ์"><i class="icon -material">accessible_forward</i><span class="-hidden">กายอุปกรณ์</span></a>' : NULL,
							'<a class="" href="'.url('imed/app/social/menu/'.$this->orgId).'"><i class="icon -material">menu</i><span class="-hidden">Menu</span></a>',
						], // children
					]), // Ui
				]
			]),
			'body' => new Widget([
				'children' => [
					R::Page('imed.app.social.patient', $this->orgId)
				],
			]),
		]);
	}
}
?>

<?php
/**
* Module Method
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function imed_app_social_info($self, $orgId = NULL) {
	$orgInfo = is_object($orgId) ? $orgId : R::Model('imed.social.get', $orgId, '{}');
	$orgId = $orgInfo->orgid;

	if (!$orgId) return message('error','ไม่มีข้อมูลของกลุ่มที่ระบุ');

	$isAdmin = $orgInfo->RIGHT & _IS_ADMIN;
	$isMember = $orgInfo->is->socialtype;
	$isRemovePatient = $isAdmin || in_array($isMember,array('MODERATOR','CM'));

	if (!($isAdmin || $isMember)) return message('error','ขออภัย ท่านไม่ได้อยู่ในกลุ่มนี้');

	R::View('imed.toolbar', $self, $orgInfo->name, 'app.social', $orgInfo);

	$ret .= R::Page('imed.app.social.patient', NULL, $orgId)->build();


	return $ret;

	/*
	$ret .= '<div id="imed-toolbar" class="header -box xtoolbar -main -imed">
<h3>มูลนิธิชุมชนสงขลา</h3>
<nav class="nav -submodule -imed"><!-- nav of imed.app.social.nav --><ul class="ui-nav -info"><li class="ui-item"><a class="sg-action" href="/imed/rehab/781/visit" data-rel="#main"><i class="icon -doctor"></i><span class="-hidden">เยี่ยมบ้าน</span></a></li><li class="ui-item"><a class="sg-action" href="/imed/social/781/patient" data-rel="#main" data-webview="ดูแล"><i class="icon -material">accessible</i><span class="-hidden">ผู้ป่วย</span></a></li><li class="ui-item"><a class="sg-action" href="/imed/social/781/member" data-rel="#main" data-webview="Group Members"><i class="icon -material">account_circle</i><span class="-hidden">สมาชิก</span></a></li><li class="ui-item"><a class="sg-action" href="/imed/pocenter/781/stock.list" data-rel="#main" data-webview="กายอุปกรณ์"><i class="icon -material">accessible_forward</i><span class="-hidden">กายอุปกรณ์</span></a></li><li class="ui-item"><span class="sg-dropbox click leftside -no-print" data-type="click"><a href="javascript:void(0)" title="มีเมนูย่อย"><i class="icon -dropbox"></i></a><div class="sg-dropbox--wrapper -hidden"><div class="sg-dropbox--arrow"></div><div class="sg-dropbox--content"><ul class="ui-action"><li class="ui-item"><a class="sg-action" href="/imed/social/781/careplan/list" data-rel="#main" data-webview="Care Plan List"><i class="icon -material">view_list</i><span>Care Plan List</span></a></li><li class="ui-item"><a class="sg-action" href="/imed/social/781/setting" data-rel="#main" data-webview="Settings"><i class="icon -material">settings</i><span>การตั้งค่า</span></a></li></ul>
</div></div></span></li></ul>
</nav><!-- submodule -->
</div>';
	define(_AJAX,false);
	*/
	R::View('imed.toolbar', $self, $orgInfo->name, 'app.social', $orgInfo);

	//$ret .= $_SERVER['HTTP_USER_AGENT'];


	//$ret .= print_o(getallheaders(),'getallheaders()');

	//$ret .= print_o($_SERVER,'$_SERVER');



	mydb::where('sp.`orgid` = :orgid', ':orgid', $orgId);

	if (post('s') == 'new') {
		mydb::value('$ORDER$','sp.`created` DESC');
	} else {
		mydb::value('$ORDER$','CONVERT(p.`name` USING tis620) ASC,  CONVERT(p.`lname` USING tis620) ASC');
	}
	$stmt = 'SELECT
		  sp.`psnid`
		, p.`prename`
		, CONCAT(p.`name`," ",p.`lname`) `name`
		, p.`sex`
		, sp.`addby`
		, u.`name` `addByName`
		, (SELECT COUNT(*) FROM %imed_service% WHERE `pid` = p.`psnid`) `serviceAmt`
		, sp.`created`
		FROM %imed_socialpatient% sp
			LEFT JOIN %db_person% p USING(`psnid`)
			LEFT JOIN %users% u ON u.`uid` = sp.`addby`
		%WHERE%
		ORDER BY $ORDER$';

	$dbs = mydb::select($stmt, ':uid', i()->uid);



	$ui = new Ui(NULL,'ui-card -patient -sg-flex -co-2');

	if ($dbs->_empty) $ret .= '<p class="notify">ยังไม่มีบันทึกเยี่ยมบ้าน</p>';

	$myUid = i()->uid;
	foreach ($dbs->items as $rs) {
		$isRemoveable = $isRemovePatient || $rs->addby == $myUid;
		$cardUi = new Ui();
		$cardUi->add('<a class="sg-action btn" href="'.url('imed/app/'.$rs->psnid).'" data-webview="'.$rs->name.'"><i class="icon -doctor"></i><span>เยี่ยมบ้าน</span></a>');
		$dropUi = new Ui();
		if ($isRemoveable) {
			$dropUi->add('<a class="sg-action" href="'.url('imed/social/'.$orgId.'/patient.remove/'.$rs->psnid).'" data-rel="none" data-removeparent="ul.ui-card.-patient>.ui-item" data-title="ลบผู้ป่วยออกจากกลุ่ม" data-confirm="ต้องการลบผู้ป่วยออกจากกลุ่ม กรุณายืนยัน?">Remove from Group</a>');
		}
		if ($isCareManager) {
			$cardUi->add('<a class="sg-action btn" href="'.url('imed/care/'.$rs->psnid,array('org'=>$orgId)).'" data-rel="#imed-app" data-pid="'.$rs->psnid.'" data-done="moveto:0,1"><i class="icon -material">assignment</i><span>Care Plan</span></a>');
		}

		if ($dropUi->count()) $cardUi->add(sg_dropbox($dropUi->build(),'{class:"leftside"}'));

		$menu = '<nav class="nav -card -sg-text-right">'
				. $cardUi->build()
				. '</nav>';

/*
	.ui-card {margin: 0; padding: 0; list-style-type: none;}
		.ui-card>.ui-item {position: relative; margin-bottom:16px; box-shadow: 4px 4px 8px 2px hsla(0, 0%, 0%, 0.1);}
		.ui-card>.ui-item>h3 {padding: 8px;}
		.ui-card>.ui-item>p {padding: 8px;}
		.ui-card>.ui-item>.header {padding: 4px 16px;}
		.ui-card>.ui-item>.detail {padding: 16px;}
		.ui-card>.ui-item>.footer {margin: 4px 0; padding: 8px 0; font-size: 1em; font-style: normal; border-top: 1px #eee solid; text-align: left;}
		.ui-card .poster-photo {border-radius: 50%; width: 40px; height: 40px;}
		*/
		$ui->add('<div class="header">'
			. '<a class="sg-action" href="'.url('imed/app/'.$rs->psnid).'" data-webview="'.$rs->name.'">'
			. '<img class="poster-photo -sg-48" src="'.imed_model::patient_photo($rs->psnid).'" width="48" height="48" />'
			. '<span class="poster-name">'.$rs->prename.' '.$rs->name.'</span>'
			. '</a>'
			. '<span class="timestamp">Added by '.$rs->addByName.' on '.sg_date($rs->created, 'ว ดด ปปปป').'</span>'
			. '</div>'
			. '<div class="detail">เยี่ยมบ้าน '.$rs->serviceAmt.' ครั้ง</div>'
			. $menu
			//. print_o($rs,'$rs')
		);
	}
	$ret .= $ui->build();

	//$ret .= print_o($orgInfo,'$orgInfo');

	/*
	$ret .= '<style type="text/css">
	.header.-box .nav span {display: none;} {}
	.toolbar.-main.-imed {margin-top: 128px;}
	</style>';
	*/
	return $ret;
}
?>