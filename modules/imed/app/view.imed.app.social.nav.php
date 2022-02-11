<?php
/**
* Project detail
*
* @param Object $self
* @param Object $psn
* @param Object $para
* @return String
*/
function view_imed_app_social_nav($orgInfo, $options) {
	$orgId = $orgInfo->orgid;

	//$ret = 'AAA<br />AAA<br />AAA<br />AAA<br />AAA<br />';

	$isMember = $orgInfo->is->socialtype;
	$isAdmin = $orgInfo->RIGHT & _IS_ADMIN;
	$isGroupAdmin = $isAdmin  || in_array($isMember,array('ADMIN','MODERATOR'));
	$isRemovePatient = $isAdmin || in_array($isMember,array('MODERATOR','CM'));

	$isPoCenter = mydb::select('SELECT `orgid` FROM %org_service% WHERE `orgid` = :orgid AND `servname` = "POCENTER" LIMIT 1', ':orgid', $orgId)->orgid;

	if ($orgId && i()->ok) {
		$ui = new Ui(NULL,'ui-nav -info');
		$ui->add('<a class="sg-action" href="'.url('imed/rehab/'.$orgId.'/visit').'" data-rel="#main"><i class="icon -material">fact_check</i><span class="-hidden">{tr:เยี่ยมบ้าน}</span></a>');
		$ui->add('<a class="sg-action" href="'.url('imed/app/social/patient/'.$orgId).'" data-rel="#main" data-webview="ดูแล"><i class="icon -material">accessible</i><span class="-hidden">{tr:Patients}</span></a>');
		$ui->add('<a class="sg-action" href="'.url('imed/social/'.$orgId.'/member').'" data-rel="#main" data-webview="Group Members"><i class="icon -material">people</i><span class="-hidden">{tr:Members}</span></a>');
		if ($isAdmin || $isPoCenter) {
			$ui->add('<a class="sg-action" href="'.url('imed/app/pocenter/'.$orgId).'" data-rel="#main" data-webview="กายอุปกรณ์"><i class="icon -material">accessible_forward</i><span class="-hidden">กายอุปกรณ์</span></a>');
		}
//	$ret .= R::Page('imed.pocenter.stock.list',NULL, $orgInfo);
		$ui->add('<a class="" href="'.url('imed/app/social/menu/'.$orgId).'"><i class="icon -material">menu</i><span class="-hidden">Menu</span></a>');

		/*
		$dropUi = new Ui();
		$dropUi->add('<a class="sg-action" href="'.url('imed/social/'.$orgId.'/careplan/list').'" data-rel="#main" data-webview="Care Plan List"><i class="icon -material">view_list</i><span>{tr:Care Plan List}</span></a>');
		if ($isGroupAdmin) {
			$dropUi->add('<sep>');
			$dropUi->add('<a class="sg-action" href="'.url('imed/social/'.$orgId.'/report').'" data-rel="#main" data-webview="รายงาน"><i class="icon -material">assessment</i><span>รายงาน</span></a>');
			$dropUi->add('<a class="sg-action" href="'.url('imed/social/'.$orgId.'/setting').'" data-rel="#main" data-webview="Settings"><i class="icon -material">settings</i><span>{tr:Settings}</span></a>');
		}


		$ui->add(sg_dropbox($dropUi->build()));
		*/

		$ret .= $ui->build();
	}


	/*
	$ui->add('<a class="sg-action" href="'.url('imed/rehab/'.$orgId.'/visit').'" data-rel="#imed-app"><i class="icon -doctor"></i><span>{tr:เยี่ยมบ้าน}</span></a>');
	$ui->add('<a class="sg-action" href="'.url('imed/social/'.$orgId.'/patient').'" data-rel="#imed-app"><i class="icon -material">accessible</i><span>{tr:Patients}</span></a>');
	$ui->add('<a class="sg-action" href="'.url('imed/social/'.$orgId.'/member').'" data-rel="#imed-app"><i class="icon -material">account_circle</i><span>{tr:Members}</span></a>');
	//$ui->add('<a class="sg-action" href="'.url('imed/social/'.$orgId.'/chat').'" data-rel="#imed-app"><i class="icon -material">chat</i><span>{tr:Chat}</span></a>');
	if ($isAdmin || $isPoCenter) {
		$ui->add('<a href="'.url('imed/pocenter/'.$orgId).'" ><i class="icon -material">accessible_forward</i><span>กายอุปกรณ์</span></a>');
	}
	if ($ui) {
		$ui->add('<a class="sg-action" href="'.url('imed/social/'.$orgId.'/setting').'" data-rel="#imed-app"><i class="icon -material">settings</i><span>{tr:Settings}</span></a>');
	}
	*/
	return $ret;
}
?>