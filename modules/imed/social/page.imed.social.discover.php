<?php
/**
* Module Method
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function imed_social_discover($self) {
	$getRef = post('ref');
	//$ret = '<h3>@Social Groups Discover</h3>';

	$ui = new Ui();
	$ui->add('<a class="sg-action btn" href="'.url('imed/social/discover').'" data-rel="#imed-app"><i class="icon -material">search</i><span>{tr:Discover}</span></a>');
	$ui->add('<a class="sg-action btn" href="'.url('imed/social/group').'" data-rel="#imed-app"><i class="icon -material">group</i><span>{tr:Groups}</span></a>');
	$ui->add('<a class="sg-action btn -primary" href="'.url('imed/social/group/create').'" data-rel="box" data-width="640"><i class="icon -material -white">add_circle_outline</i><span>{tr:Create Group}</span></a>',array('class'=>'-create'));
	$ret .= '<nav class="nav -page -social">'.$ui->build().'</nav>';


	$stmt = 'SELECT g.`orgid`, g.`uid`, o.`name` `groupname`
		, u.`name` `addByName`
		, (SELECT COUNT(*) FROM %imed_socialmember% WHERE `orgid` = g.`orgid`) `memberCount`
		, (SELECT COUNT(*) FROM %imed_socialpatient% WHERE `orgid` = g.`orgid`) `patientCount`
		, g.`created`
		FROM %imed_socialgroup% g
			LEFT JOIN %users% u USING(`uid`)
			LEFT JOIN %db_org% o USING(`orgid`)
		ORDER BY g.`created` DESC, CONVERT(o.`name` USING tis620) ASC';
	$dbs = mydb::select($stmt, ':uid',i()->uid);

	$ui = new Ui(NULL,'ui-card -group -flex');
	foreach ($dbs->items as $rs) {
		// Dropdown menu : Edit group settings,Leave Group
		if ($getRef == 'app') {
			$ui->add('<div class="header -sg-flex">'
				. '<i class="icon -material -sg-48">group</i>'
				. '<span class="profile"><b>'.$rs->groupname.'</b>'
				. '<span class="timestamp -full">Created by '.$rs->addByName.' on '.sg_date($rs->created, 'ว ดด ปปปป').'</span>'
				. '</span>'
				. '</div>'
				. '<p>'
				. $rs->memberCount.' members '
				. $rs->patientCount.' patients'
				. '</p>',
				array(
					'class' => 'sg-action',
					'href' => url('imed/app/social/'.$rs->orgid),
					'data-webview' => $rs->groupname,
				)
			);
		} else {
			$ui->add('<div class="header">'
				. '<a class="" href="'.url('imed/social/'.$rs->orgid).'"><i class="icon -material -sg-64">group</i>'.$rs->groupname.'</a>'
				. '<span class="timestamp">Created by '.$rs->addByName.' on '.sg_date($rs->created, 'ว ดด ปปปป').'</span>'
				. '</div>'
				. $rs->memberCount.' members '
				. $rs->patientCount.' patients'
			);
		}
	}
	$ret .= '<nav class="nav">'.$ui->build().'</nav>';
	return $ret;
}
?>