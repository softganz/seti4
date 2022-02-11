<?php
/**
* Module Method
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function imed_app_social_group($self, $orgId = NULL) {
	if (!i()->ok) return R::View('signform', '{time:-1, showTime: false}');

	$isCreateGroup = is_admin('imed') || imed_model::get_user_zone(i()->uid);

	
	//$ui->add('<a class="sg-action btn" href="'.url('imed/app/social/discover').'" data-rel="#imed-app"><i class="icon -material">search</i><span>Discover</span></a>');
	//$ui->add('<a class="sg-action btn" href="'.url('imed/app/social/group').'" data-rel="#imed-app"><i class="icon -material">group</i><span>Groups</span></a>');
	//$ui->add('<a class="sg-action btn -info" href="'.url('imed/social/group/create').'" data-rel="box" data-width="640"><i class="icon -material -white">add_circle_outline</i><span>Create Group</span></a>',array('class'=>'-create'));
	//$ret .= '<nav class="nav -page -social">'.$ui->build().'</nav>';


	//$ret .= '<h4>Pending invitations</h3>';

	$ret .= '<section id="imed-app-social-group">';

	$ret .= R::Page('imed.social.my.invite', NULL);

	$ret .= '<header class="header"><h3>Groups You Manage</h3></header>';

	$stmt = 'SELECT
			m.`orgid`, m.`uid`, o.`name` `groupname`
		, u.`name` `addByName`
		, (SELECT COUNT(*) FROM %imed_socialmember% WHERE `orgid` = m.`orgid`) `memberCount`
		, (SELECT COUNT(*) FROM %imed_socialpatient% WHERE `orgid` = m.`orgid`) `patientCount`
		, m.`created`
		FROM %imed_socialmember% m
			LEFT JOIN %db_org% o USING(`orgid`)
			LEFT JOIN %users% u ON u.`uid` = o.`uid`
		WHERE m.`uid` = :uid AND m.`membership` LIKE "ADMIN"';

	$dbs = mydb::select($stmt, ':uid',i()->uid);

	$ui = new Ui('div','ui-card -group -sg-flex -co-2');
	foreach ($dbs->items as $rs) {
		// Dropdown menu : Edit group settings,Leave Group
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
	}
	if ($dbs->count() % 2) $ui->add('&nbsp;', '{class: "-empty"}');
	$ret .= $ui->build();





	$ret .= '<h3>Your Groups</h3>';

	$stmt = 'SELECT
		  m.`orgid`, m.`uid`, o.`name` `groupname`
		, u.`name` `addByName`
		, (SELECT COUNT(*) FROM %imed_socialmember% WHERE `orgid` = m.`orgid`) `memberCount`
		, (SELECT COUNT(*) FROM %imed_socialpatient% WHERE `orgid` = m.`orgid`) `patientCount`
		, m.`created`
		FROM %imed_socialmember% m
			LEFT JOIN %db_org% o USING(`orgid`)
			LEFT JOIN %users% u ON u.`uid` = o.`uid`
		WHERE m.`uid` = :uid AND m.`membership` NOT LIKE "ADMIN"';

	$dbs = mydb::select($stmt, ':uid',i()->uid);

	$ui = new Ui(NULL,'ui-card -group -sg-flex -co-2');
	foreach ($dbs->items as $rs) {
		// Dropdown menu : Edit group settings,Leave Group
		$ui->add('<div class="header">'
				. '<a class="sg-action" href="'.url('imed/app/social/'.$rs->orgid).'" data-webview="'.$rs->groupname.'"><i class="icon -material -sg-64">group</i>'.$rs->groupname.'</a>'
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

	$ret .= '</section><!-- imed-app-social-group -->';

	head('<style type="text/css">
		.nav.-page.-social {display: none;}
	</style>'
	);

	return new Scaffold([
		'appBar' => new AppBar([
			'title' => '@Social Groups',
			'navigator' => [
				new Ui([
					'children' => [
						'<a class="sg-action" href="'.url('imed/social/discover', array('ref'=>'app')).'" data-rel="#main"><i class="icon -material">search</i></a>',
						['<a class="sg-action" href="'.url('imed/social/discover', array('ref'=>'app')).'" data-rel="#main"><i class="icon -material">search</i></a>',array('class'=>'-create')],
						$isCreateGroup ? '<a class="sg-action btn -link" href="'.url('imed/social/group/create',array('ref'=>'app')).'" data-rel="box" data-width="640" title="{tr:Create Group}"><i class="icon -material -white">group_add</i><span class="-hidden">{tr:Create Group}</span></a>' : NULL,
					],
				]), // Ui
			], // children
		]), // AppBar
		'children' => [
			$ret,
		],
	]);
}
?>