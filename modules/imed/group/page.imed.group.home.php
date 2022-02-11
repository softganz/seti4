<?php
/**
* iMed :: Group Home Page
* Created 2021-08-17
* Modify  2021-08-29
*
* @return Widget
*
* @usage imed/group/{id}
*/

$debug = true;

class ImedGroupHome extends Page {
	var $refApp;
	var $id = 'imed-group';
	var $urlView = 'imed/group/';

	function __construct() {parent::__construct();}

	function build() {
		if (!i()->ok) return R::View('signform', '{time:-1, showTime: false}');

		$isCreateGroup = is_admin('imed') || imed_model::get_user_zone(i()->uid);

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
							// '<a class="sg-action" href="'.url('imed/social/discover', array('ref'=>'app')).'" data-rel="#main"><i class="icon -material">search</i></a>',
							// ['<a class="sg-action" href="'.url('imed/social/discover', array('ref'=>'app')).'" data-rel="#main"><i class="icon -material">search</i></a>',array('class'=>'-create')],
							$isCreateGroup ? '<a class="sg-action btn -link" href="'.url('imed/social/group/create',array('ref'=>'app')).'" data-rel="box" data-width="640" title="{tr:Create Group}"><i class="icon -material -white">group_add</i><span class="-hidden">{tr:Create Group}</span></a>' : NULL,
						],
					]), // Ui
				], // children
			]), // AppBar
			'body' => new Container([
				'tagName' => 'section',
				'id' => $this->id,
				'children' => [
					R::Page('imed.social.my.invite', NULL),
					new Card([
						'children' => [
							new ListTile([
								'title' => 'Groups You Manage',
								'leading' => '<i class="icon -material">groups</i>',
							]),
						],
					]), // Card
					$this->myGroup(),
					new Card([
						'children' => [
							new ListTile([
								'title' => 'Groups You Join',
								'leading' => '<i class="icon -material">groups</i>',
							]),
						],
					]),
					$this->joinGroup(),
				], // children
			]), // Container
		]);
	}

	function myGroup() {
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
					'href' => url($this->urlView.$rs->orgid),
					'data-webview' => $rs->groupname,
				)
			);
		}
		if ($dbs->count() % 2) $ui->add('&nbsp;', '{class: "-empty"}');
		return $ui;
	}

	function joinGroup() {
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
					. '<a class="sg-action" href="'.url($this->urlView.$rs->orgid).'" data-webview="'.$rs->groupname.'"><i class="icon -material -sg-64">group</i>'.$rs->groupname.'</a>'
					. '<span class="timestamp">Created by '.$rs->addByName.' on '.sg_date($rs->created, 'ว ดด ปปปป').'</span>'
					. '</div>'
					. '<p>'
					. $rs->memberCount.' members '
					. $rs->patientCount.' patients'
					. '</p>',
					array(
						'class' => 'sg-action',
						'href' => url($this->urlView.$rs->orgid),
						'data-webview' => $rs->groupname,
					)
				);
		}
		if ($dbs->count() % 2) $ui->add('&nbsp;', '{class: "-empty"}');
		return $ui;
	}
}
?>