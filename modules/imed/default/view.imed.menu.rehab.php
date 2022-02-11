<?php
/**
* Module Method
*
* @param 
* @return String
*/

$debug = true;

function view_imed_menu_rehab($orgInfo) {
	$orgId = $orgInfo->orgid;

	$isAdmin = $orgInfo->RIGHT & _IS_ADMIN;

	$isPoCenter = mydb::select('SELECT `orgid` FROM %org_service% WHERE `orgid` = :orgid AND `servname` = "POCENTER" LIMIT 1', ':orgid', $orgId)->orgid;


	$menuUi = new Ui(NULL,'ui-menu');

	if ($orgInfo) {
		$menuUi->add('<a class="sg-action" href="'.url('imed/social/'.$orgId.'/about').'" data-rel="#imed-app"><i class="icon -material">help</i><span>{tr:About}</span></a>');
		$menuUi->add('<a class="sg-action" href="'.url('imed/rehab/'.$orgId.'/patient').'" data-rel="#imed-app"><i class="icon -material">accessible</i><span>{tr:Patients}</span></a>');
		$menuUi->add('<a class="sg-action" href="'.url('imed/rehab/'.$orgId.'/visit').'" data-rel="#imed-app"><i class="icon -doctor"></i><span>{tr:เยี่ยมบ้าน}</span></a>');
		$menuUi->add('<a class="sg-action" href="'.url('imed/rehab/'.$orgId.'/careplan').'" data-rel="#imed-app"><i class="icon -material">assignment</i><span>{tr:Care Plan}</span></a>');
		if ($isAdmin || $isPoCenter) {
			$menuUi->add('<a class="sg-action" href="'.url('imed/rehab/'.$orgId.'/pocenter').'"  data-rel="#imed-app"><i class="icon -material">accessible_forward</i><span>กายอุปกรณ์</span></a>');
		}
		$menuUi->add('<a class="sg-action" href="'.url('imed/social/'.$orgId.'/chat').'" data-rel="#imed-app"><i class="icon -material">chat</i><span>{tr:Chat}</span></a>');

		$menuUi->add('<sep>');

		$menuUi->add('<a class="sg-action" href="'.url('imed/social/'.$orgId.'/member').'" data-rel="#imed-app"><i class="icon -material">account_circle</i><span>{tr:Members}</span></a>');
		if ($isAdmin) {
			$menuUi->add('<a class="sg-action" href="'.url('imed/social/'.$orgId.'/setting').'" data-rel="#imed-app"><i class="icon -material">settings</i><span>{tr:Settings}</span></a>');
		}

	}

	$menuUi->add('<sep>');
	$menuUi->add('<b>{tr:Groups}</b>');

	$stmt = 'SELECT m.`orgid`, m.`uid`, o.`name` `groupname` FROM %imed_socialmember% m LEFT JOIN %db_org% o USING(`orgid`) WHERE m.`uid` = :uid';
	$dbs = mydb::select($stmt, ':uid',i()->uid);

	foreach ($dbs->items as $rs) {
		// Dropdown menu : Edit group settings,Leave Group
		$menuUi->add('<a class="" href="'.url('imed/rehab/'.$rs->orgid).'" data-rel="#imed-app" title="'.$rs->groupname.'"><img src="//img.softganz.com/img/disabledonfloor.jpg" width="32" height="32" style="border-radius: 50%; vertical-align: middle; margin-right: 4px;" /><span>'.$rs->groupname.'</span></a>');
	}


	return $menuUi;
}
?>