<?php
/**
* Module Method
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function imed_social_group($self, $orgId = NULL) {
	//$ret = '<h3>@Social Groups</h3>';
	$isCreateGroup = is_admin('imed') || imed_model::get_user_zone(i()->uid);

	$ui = new Ui();
	$ui->add('<a class="sg-action btn" href="'.url('imed/social/discover').'" data-rel="#imed-app"><i class="icon -material">search</i><span>{tr:Discover}</span></a>');
	$ui->add('<a class="sg-action btn" href="'.url('imed/social/group').'" data-rel="#imed-app"><i class="icon -material">group</i><span>{tr:Groups}</span></a>');

	if ($isCreateGroup) {
		$ui->add('<a class="sg-action btn -primary" href="'.url('imed/social/group/create').'" data-rel="box" data-width="640"><i class="icon -material -white">add_circle_outline</i><span>{tr:Create Group}</span></a>',array('class'=>'-create'));
	}
	$ret .= '<nav class="nav -page -social">'.$ui->build().'</nav>';


	//$ret .= '<h3>Pending invitations</h3>';

	$stmt = 'SELECT m.`orgid`, m.`uid`, o.`name` `groupname`
					, u.`name` `addByName`
					, (SELECT COUNT(*) FROM %imed_socialmember% WHERE `orgid` = m.`orgid`) `memberCount`
					, (SELECT COUNT(*) FROM %imed_socialpatient% WHERE `orgid` = m.`orgid`) `patientCount`
					, m.`created`
					FROM %imed_socialmember% m
						LEFT JOIN %users% u USING(`uid`)
						LEFT JOIN %db_org% o USING(`orgid`)
					WHERE m.`uid` = :uid AND m.`membership` LIKE "ADMIN"';
	$dbs = mydb::select($stmt, ':uid',i()->uid);

	if ($dbs->_num_rows) {
		$ret .= '<h3>Groups You Manage</h3>';
		$ui = new Ui(NULL,'ui-card -group -sg-flex -co-2');
		foreach ($dbs->items as $rs) {
			// Dropdown menu : Edit group settings,Leave Group
			$ui->add('<div class="header">'
					. '<a class="" href="'.url('imed/social/'.$rs->orgid).'"><i class="icon -material -sg-64">group</i>'.$rs->groupname.'</a>'
					. '<span class="timestamp">Created by '.$rs->addByName.' on '.sg_date($rs->created, 'ว ดด ปปปป').'</span>'
					. '</div>'
					. '<p>'
					. $rs->memberCount.' members '
					. $rs->patientCount.' patients'
					. '</p>'
				);
		}
		if ($dbs->count() % 2) $ui->add('&nbsp;', '{class: "-empty"}');
		$ret .= $ui->build();
	}



	$stmt = 'SELECT m.`orgid`, m.`uid`, o.`name` `groupname`
					, u.`name` `addByName`
					, (SELECT COUNT(*) FROM %imed_socialmember% WHERE `orgid` = m.`orgid`) `memberCount`
					, (SELECT COUNT(*) FROM %imed_socialpatient% WHERE `orgid` = m.`orgid`) `patientCount`
					, m.`created`
					FROM %imed_socialmember% m
						LEFT JOIN %users% u USING(`uid`)
						LEFT JOIN %db_org% o USING(`orgid`)
					WHERE m.`uid` = :uid AND m.`membership` NOT LIKE "ADMIN"';
	$dbs = mydb::select($stmt, ':uid',i()->uid);

	if ($dbs->_num_rows) {
		$ret .= '<h3>Your Groups</h3>';
		$ui = new Ui(NULL,'ui-card -group -sg-flex -co-2');
		foreach ($dbs->items as $rs) {
			// Dropdown menu : Edit group settings,Leave Group
			$ui->add('<div class="header">'
					. '<a class="" href="'.url('imed/social/'.$rs->orgid).'"><i class="icon -material -sg-64">group</i>'.$rs->groupname.'</a>'
					. '<span class="timestamp">Created by '.$rs->addByName.' on '.sg_date($rs->created, 'ว ดด ปปปป').'</span>'
					. '</div>'
					. '<p>'
					. $rs->memberCount.' members '
					. $rs->patientCount.' patients'
					. '</p>'
				);
		}
		if ($dbs->count() % 2) $ui->add('&nbsp;', '{class: "-empty"}');
		$ret .= $ui->build();
	}

	if ($orgId) {
		$ret .= '<h3>สมาชิกกลุ่ม</h3>';
		$stmt = 'SELECT
							sm.*, u.`name`, u.`username`, ua.`name` `addByName`
						FROM %imed_socialmember% sm
							LEFT JOIN %users% u USING(`uid`)
							LEFT JOIN %users% ua ON ua.`uid` = sm.`addby`
						WHERE `orgid` = :orgid';
		$dbs = mydb::select($stmt, ':orgid', $orgId);

		$ui = new Ui(NULL,'ui-card -member -flex');
		foreach ($dbs->items as $rs) {
			$dropUi = new Ui();
			if ($rs->membership == 'ADMIN') {
				$dropUi->add('<a href="">Change to Moderator</a>');
				$dropUi->add('<a href="">Remove as Admin</a>');
				$dropUi->add('<a href="">Leave Group</a>');
			} else {
				$dropUi->add('<a href="">Make Admin</a>');
				$dropUi->add('<a href="">Make Moderator</a>');
				$dropUi->add('<a href="">Make CM</a>');
				$dropUi->add('<a href="">Make CG</a>');
				$dropUi->add('<a href="">Make Normal Member</a>');
				$dropUi->add('<a href="">Remove from Group</a>');
				$dropUi->add('<a href="">Mute Member</a>');
			}

			$ui->add('<a class="sg-action" href="'.url('imed/u/'.$rs->uid).'" data-rel="box">'
				. '<img class="-sg-circle" src="'.model::user_photo($rs->username).'" width="48" height="48" />'.$rs->name
				. '</a>'
				. '<br />'
				. '<span class="-sg-inline-block"><i class="icon -material">'.($rs->membership == 'ADMIN' ? 'star' : 'person_outline').'</i>'.$rs->membership.'</span>'
				. '<br /><span>Added by '.$rs->addByName.' on '.sg_date($rs->created, 'ว ดด ปปปป').'</span>'
				. '<nav class="nav -card -sg-text-right"><a class="btn" href=""><i class="icon -material">message</i><span>Message</span></a>'
				. sg_dropbox($dropUi->build(),'{class:"leftside -x-atright"}')
				. '</nav>'
			);
		}
		$ret .= $ui->build();

	}

	/*

	$ret .= '<h3>ผู้ป่วยใน'.($orgId ? 'กลุ่ม' : 'ความดูแล').'</h3>';

	mydb::where('sp.`orgid` IN (SELECT `orgid` FROM %imed_socialmember% WHERE `uid` = :uid AND `status` > 0)');
	if ($orgId) mydb::where('sp.`orgid` = :orgid', ':orgid', $orgId);
	$stmt = 'SELECT
					  sp.`psnid`
					, CONCAT(p.`prename`," ",p.`name`," ",p.`lname`) `fullname`
					, CONCAT(p.`name`," ",p.`lname`) `name`
					, p.`sex`
					FROM %imed_socialpatient% sp
						LEFT JOIN %db_person% p USING(`psnid`)
					%WHERE%
					ORDER BY CONVERT(p.`name` USING tis620) ASC,  CONVERT(p.`lname` USING tis620) ASC';
	$dbs = mydb::select($stmt, ':uid', i()->uid);

	$ui = new Ui(NULL,'ui-card -patient -flex');
	foreach ($dbs->items as $rs) {
		$dropUi = new Ui();
		$dropUi->add('<a href="">Remove from Group</a>');

		$ui->add('<a class="sg-action" href="'.url('imed/patient/individual/'.$rs->psnid).'" data-rel="#imed-app" onclick="initPatient('.$rs->psnid.',\''.$rs->name.'\')" class="sg-tooltip" data-url="'.url('imed/patient/shortinfo/'.$rs->psnid).'">'
							. '<img class="-sg-circle" src="'.imed_model::patient_photo($rs->psnid).'" width="48" height="48" />'.$rs->fullname
							. '</a>'
							. '<br />'
							. '<span class="-sg-inline-block">Added by ??? on ??/??/????</span>'
							. '<nav class="nav -card -sg-text-right"><a class="sg-action btn" href="'.url('imed/patient/individual/'.$rs->psnid).'" data-rel="#imed-app" onclick="initPatient('.$rs->psnid.',\''.$rs->name.'\')" class="sg-tooltip" data-url="'.url('imed/patient/shortinfo/'.$rs->psnid).'"><i class="icon -doctor"></i><span>เยี่ยมบ้าน</span></a>'
							. sg_dropbox($dropUi->build())
							. '</nav>'
						);
	}
	$ret .= $ui->build();

	//$ret .= print_o($dbs,'$dbs');
	$ret .= '<style type="text/css">
	@media (min-width:40em) {
	.ui-card.-flex {display:flex; justify-content: space-between; flex-wrap: wrap;}
	.ui-card.-flex .ui-item {width: 45%;}
	}
	</style>';

	*/
	return $ret;
}
?>