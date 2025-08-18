<?php
/**
 * Admin   :: Install Basic Database Table
 * Created :: 2016-11-08
 * Modify  :: 2025-08-18
 * Version :: 9
 *
 * @return Widget
 *
 * @usage admin/install
 */

use Softganz\DB;

class AdminInstall extends Page {
	var $firstTime;
	var $dbPrefix;

	function __construct() {
		parent::__construct([
			'dbPrefix' => Request::all('dbPrefix'),
			'firstTime' => !DB::tableExists('%variable%'),
		]);
	}

	function rightToBuild() {
		if ($this->firstTime) return true;
		if (!user_access('access administrator pages')) return error(_HTTP_ERROR_FORBIDDEN,'Access denied');
		return true;
	}

	#[\Override]
	function build() {
		// if ($this->firstTime) return true;
		// if (!user_access('access administrator pages')) return message('error','Access denied','admin');

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => $this->firstTime ? 'First time installation database' : 'Site Installation',
			]), // AppBar
			'body' => new Widget([
				'children' => [
					$this->form(),
					new Container([
						'id' => 'result'
					]), // Container
				], // children
			]), // Widget
		]);
	}

	private function form() {
		return new Form([
			'class' => 'sg-form',
			'action' => url('admin/install..start'),
			'id' => 'install',
			'rel' => '#result',
			'children' => [
				'dbPrefix' => [
					'type' => 'text',
					'label' => 'Enter new table prefix',
					'name' => 'dbPrefix',
					'size' => 20,
					'maxlength' => 5,
					'require' => true,
					'value' => cfg('db.prefix'),
					'description' => 'Please enter table prefix for many set of table.',
				],
				'submit' => [
					'type' => 'button',
					'value' => '<i class="icon -material">done_all</i><span>START INSTALL</span>',
					'pretext' => '<a class="btn -link -cancel" href="'.url('admin').'"><i class="icon -material">cancel</i><span>CANCEL</span></a>',
					'container' => '{class: "-sg-text-right"}',
				],
			], // children
		]);
	}

	public function start() {
		$result = $this->createTable();

		if ($this->firstTime && !$result->error) {
			$this->setDefaultConfig();
			return '<script>window.location.href = "'.url('user/register').'";</script>';
		}

		return new Widget([
			'children' => [
				new ListTile(['title' => 'Installation Result']),
				$result->error ? '<p class="notify">Installation error!!!!</p>' : 'Install table with table prefix '.$db_prefix.' complete.',
				new Container([
					'children' => array_map(
						function($query) {
							return new Card([
								'children' => [
									new ListTile(['title' => 'Create table', 'leading' => new Icon('cloud_circle')]),
									new Container(['class' => '-sg-paddingnorm', 'child' => $query]),
								], // children
							]);
						},
						(Array) $result->query
					), // children
				]), // Container
			], // children
		]);
	}

	// private function firstTimeInstall() {
	// 	// $result = $this->__admin_install_create_table();
	// 	if (!$result->error) {
	// 		// $this->__admin_install_set_default_config();
	// 		// location('user/register');
	// 	}
	// 	return new Scaffold([
	// 		'appBar' => new AppBar([
	// 			'title' => 'First time installation database',
	// 			'leading' => new Icon('rocket_launch'),
	// 		]),
	// 		'body' => new Widget([
	// 			'children' => [
	// 				$this->form(),
	// 				$result->error ? new Widget([
	// 					'children'=> [
	// 						message('error','First time installation database error'),
	// 						'<h3>Install table error.</h3><ul><li>'.implode('</li><li>',$result->error).'</li></ul>',
	// 					]
	// 				]) : NULL,
	// 				$result->table ? '<h3>Install table completed.</h3><ul><li>'.implode('</li><li>',$result->table).'</li></ul>' : NULL,
	// 				$result->module ? '<h3>Install module.</h3>'.implode('',$result->module) : NULL,
	// 			], // children
	// 		]), // Widget
	// 	]);
	// }



	// function admin_install($self) {
	// 	$self->theme->title='Site Install';


	// 	if (!user_access('access administrator pages')) return message('error','Access denied','admin');

	// 	if ($db_prefix=post('db_prefix')) {
	// 		$oldDbPrefix=cfg('db.prefix');
	// 		cfg('db.prefix',$db_prefix);
	// 		$result=__admin_install_create_table($db_prefix);
	// 		cfg('db.prefix',$oldDbPrefix);
	// 		$ret.=notify($result->error?'Installation error!!!!':'Install table with table prefix '.$db_prefix.' complete.');
	// 	}

	// 	$ret .= '<h3>Install other table prefix</h3>';

	// 	$form = new Form([
	// 		'variable' => 'table',
	// 		'action' => url(q()),
	// 		'id' => 'edit-install',
	// 		'children' => [
	// 			'db_prefix' => [
	// 				'type'=>'text',
	// 				'label'=>'Enter new table prefix',
	// 				'name'=>'db_prefix',
	// 				'size'=>20,
	// 				'maxlength'=>5,
	// 				'require'=>true,
	// 				'value'=>cfg('db.prefix'),
	// 				'description'=>'Please enter table prefix for many set of table.',
	// 			],
	// 			'submit' => [
	// 				'type'=>'button',
	// 				'value'=>'<i class="icon -material">done_all</i><span>START INSTALL</span>',
	// 				'pretext' => '<a class="btn -link -cancel" href="'.url('admin').'"><i class="icon -material">cancel</i><span>CANCEL</span></a>',
	// 				'container' => '{class: "-sg-text-right"}',
	// 			],
	// 		], // children
	// 	]);

	// 	$ret .= $form->build();

	// 	if ($result->error) $ret.='<h3>Install table error.</h3><ul><li>'.implode('</li><li>',$result->error).'</li></ul>';
	// 	if ($result->complete) $ret.='<h3>Install table completed.</h3><ul><li>'.implode('</li><li>',$result->complete).'</li></ul>';

	// 	return $ret;
	// }

	/**
	 * Create table with prefix
	 *
	 * @return Array
	 */
	function createTable($prefix = NULL) {
		$query = (Object) [];
		$result = (Object) [
			'error' => [],
			'complete' => [],
			'query' => [],
		];

		$query->block_daykey='CREATE TABLE IF NOT EXISTS %block_daykey% (
			`id` int(10) unsigned NOT NULL auto_increment,
			`key1` char(10) default NULL,
			`key2` char(10) default NULL,
			`key3` char(10) default NULL,
			`key4` char(10) default NULL,
			`key5` char(10) default NULL,
			`generate_on` datetime default NULL,
			PRIMARY KEY  (`id`),
			KEY `generate_on` (`generate_on`),
			KEY `key1` (`key1`),
			KEY `key2` (`key2`),
			KEY `key3` (`key3`),
			KEY `key4` (`key4`),
			KEY `key5` (`key5`)
		);';

		$query->bigdata='CREATE TABLE IF NOT EXISTS %bigdata% (
			`bigid` int(10) unsigned NOT NULL AUTO_INCREMENT,
			`keyname` varchar(50) DEFAULT NULL,
			`keyid` int(10) unsigned DEFAULT NULL,
			`fldname` varchar(100) DEFAULT NULL,
			`fldtype` varchar(30) DEFAULT NULL,
			`fldref` varchar(30) DEFAULT NULL,
			`data` JSON NOT NULL DEFAULT "{}",
			`flddata` longtext DEFAULT NULL,
			`created` bigint(20) unsigned DEFAULT NULL,
			`ucreated` int(10) unsigned DEFAULT NULL,
			`modified` bigint(20) unsigned DEFAULT NULL,
			`umodified` int(10) unsigned DEFAULT NULL,
			PRIMARY KEY (`bigid`),
			KEY `keyname` (`keyname`),
			KEY `keyid` (`keyid`),
			KEY `fldname` (`fldname`),
			KEY `fldref` (`fldref`),
			KEY `created` (`created`),
			KEY `ucreated` (`ucreated`),
			KEY `modified` (`modified`),
			KEY `umodified` (`umodified`)
		);';


		$query->cache='CREATE TABLE IF NOT EXISTS %cache% (
			`cid` varchar(1000) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL default "",
			`data` text,
			`expire` int(11) NOT NULL default 0,
			`created` int(11) NOT NULL default 0,
			`headers` text,
			PRIMARY KEY  (`cid`),
			KEY `expire` (`expire`)
		);';

		$query->users='CREATE TABLE IF NOT EXISTS %users% (
			`uid` int(10) unsigned NOT NULL auto_increment,
			`username` varchar(30) NULL default NULL,
			`password` varchar(255)NULL default NULL,
			`name` varchar(255) default NULL,
			`status` enum("enable", "disable", "block", "waiting", "locked") default NULL,
			`roles` varchar(255) NOT NULL default "",
			`email` varchar(50) default NULL,
			`hits` int(10) unsigned NOT NULL default 0,
			`datein` datetime default NULL,
			`tries` tinyint(2) default 0,
			`remote_ip` varchar(15) default NULL,
			`date_tries` datetime default NULL,
			`last_login` datetime default NULL,
			`last_login_ip` bigint(20) default NULL,
			`login_time` datetime default NULL,
			`login_ip` bigint(20) default NULL,
			`lastHitTime` datetime default NULL,
			`code` varchar(255) default NULL,
			`pwresettime` BIGINT NULL DEFAULT NULL,
			`views` int(10) unsigned NOT NULL default 0,
			`show_name` tinyint(4) NOT NULL default 0,
			`name_prefix` varchar(10) default NULL,
			`real_name` varchar(50) default NULL,
			`mid_name` varchar(50) default NULL,
			`last_name` varchar(50) default NULL,
			`gender` enum("male","female") default NULL,
			`birthday` date default NULL,
			`picture` varchar(50) default NULL,
			`occupation` varchar(50) default NULL,
			`position` varchar(50) default NULL,
			`organization` varchar(100) default NULL,
			`address` varchar(255) default NULL,
			`areacode` varchar(8) DEFAULT NULL,
			`amphur` varchar(50) default NULL,
			`province` varchar(50) default NULL,
			`zipcode` varchar(10) default NULL,
			`country` varchar(50) default NULL,
			`latitude` varchar(10) default NULL,
			`longitude` varchar(10) default NULL,
			`phone` varchar(20) default NULL,
			`mobile` varchar(20) default NULL,
			`fax` varchar(20) default NULL,
			`website` varchar(200) default NULL,
			`about` text,
			`admin_remark` text NULL DEFAULT NULL,
			PRIMARY KEY  (`uid`),
			UNIQUE KEY `username` (`username`),
			KEY `email` (`email`),
			KEY `name` (`name`),
			KEY `areacode` (`areacode`),
			KEY `pwresettime` (`pwresettime`),
			KEY `lastHitTime` (`lastHitTime`)
		);';

		$query->users_online = 'CREATE TABLE IF NOT EXISTS %users_online% (
			`keyid` varchar(100) NOT NULL,
			`uid` int(11) DEFAULT NULL,
			`name` varchar(255) DEFAULT NULL,
			`coming` bigint(20) DEFAULT NULL,
			`access` bigint(20) DEFAULT NULL,
			`hits` int(11) DEFAULT 0,
			`ip` varchar(50) DEFAULT NULL,
			`host` varchar(255) DEFAULT NULL,
			`browser` varchar(255) DEFAULT NULL,
			PRIMARY KEY (`keyid`),
			KEY `uid` (`uid`),
			KEY `coming` (`coming`),
			KEY `access` (`access`)
		);';

		$query->users_role = 'CREATE TABLE IF NOT EXISTS %users_role% (
			`uid` int(10) UNSIGNED NOT NULL,
			`role` varchar(20) NOT NULL,
			`status` enum("WAITING","ENABLE","BLOCK") DEFAULT NULL,
			`reason` varchar(100) DEFAULT NULL,
			`approved` date DEFAULT NULL,
			`created` bigint(20) DEFAULT NULL,
			PRIMARY KEY (`uid`,`role`),
			KEY `uid` (`uid`),
			KEY `role` (`role`),
			KEY `status` (`status`),
			KEY `created` (`created`),
			KEY `dateapproved` (`approved`)
		);';

		$query->variable='CREATE TABLE IF NOT EXISTS %variable% (
			`name` varchar(50) NOT NULL default "",
			`value` longtext NOT NULL,
			PRIMARY KEY  (`name`)
		);';

		$query->counter_day='CREATE TABLE IF NOT EXISTS %counter_day% (
			`log_date` date NOT NULL default "0000-00-00",
			`hits` int(10) unsigned NOT NULL default 0,
			`users` int(10) unsigned NOT NULL default 0,
			`h_00` smallint(5) unsigned NOT NULL default 0,
			`h_01` smallint(5) unsigned NOT NULL default 0,
			`h_02` smallint(5) unsigned NOT NULL default 0,
			`h_03` smallint(5) unsigned NOT NULL default 0,
			`h_04` smallint(5) unsigned NOT NULL default 0,
			`h_05` smallint(5) unsigned NOT NULL default 0,
			`h_06` smallint(5) unsigned NOT NULL default 0,
			`h_07` smallint(5) unsigned NOT NULL default 0,
			`h_08` smallint(5) unsigned NOT NULL default 0,
			`h_09` smallint(5) unsigned NOT NULL default 0,
			`h_10` smallint(5) unsigned NOT NULL default 0,
			`h_11` smallint(5) unsigned NOT NULL default 0,
			`h_12` smallint(5) unsigned NOT NULL default 0,
			`h_13` smallint(5) unsigned NOT NULL default 0,
			`h_14` smallint(5) unsigned NOT NULL default 0,
			`h_15` smallint(5) unsigned NOT NULL default 0,
			`h_16` smallint(5) unsigned NOT NULL default 0,
			`h_17` smallint(5) unsigned NOT NULL default 0,
			`h_18` smallint(5) unsigned NOT NULL default 0,
			`h_19` smallint(5) unsigned NOT NULL default 0,
			`h_20` smallint(5) unsigned NOT NULL default 0,
			`h_21` smallint(5) unsigned NOT NULL default 0,
			`h_22` smallint(5) unsigned NOT NULL default 0,
			`h_23` smallint(5) unsigned NOT NULL default 0,
			`u_00` smallint(5) unsigned NOT NULL default 0,
			`u_01` smallint(5) unsigned NOT NULL default 0,
			`u_02` smallint(5) unsigned NOT NULL default 0,
			`u_03` smallint(5) unsigned NOT NULL default 0,
			`u_04` smallint(5) unsigned NOT NULL default 0,
			`u_05` smallint(5) unsigned NOT NULL default 0,
			`u_06` smallint(5) unsigned NOT NULL default 0,
			`u_07` smallint(5) unsigned NOT NULL default 0,
			`u_08` smallint(5) unsigned NOT NULL default 0,
			`u_09` smallint(5) unsigned NOT NULL default 0,
			`u_10` smallint(5) unsigned NOT NULL default 0,
			`u_11` smallint(5) unsigned NOT NULL default 0,
			`u_12` smallint(5) unsigned NOT NULL default 0,
			`u_13` smallint(5) unsigned NOT NULL default 0,
			`u_14` smallint(5) unsigned NOT NULL default 0,
			`u_15` smallint(5) unsigned NOT NULL default 0,
			`u_16` smallint(5) unsigned NOT NULL default 0,
			`u_17` smallint(5) unsigned NOT NULL default 0,
			`u_18` smallint(5) unsigned NOT NULL default 0,
			`u_19` smallint(5) unsigned NOT NULL default 0,
			`u_20` smallint(5) unsigned NOT NULL default 0,
			`u_21` smallint(5) unsigned NOT NULL default 0,
			`u_22` smallint(5) unsigned NOT NULL default 0,
			`u_23` smallint(5) unsigned NOT NULL default 0,
			PRIMARY KEY  (`log_date`)
		);';


		// Create partition from now to 20 year
		$partition = '';
		for ($year = date('Y'); $year <= date('Y')+20; $year++) {
			$partition .= 'PARTITION p_'.$year.' VALUES LESS THAN ('.($year+1).'),'._NL."\t\t";
		}
		$partition .= 'PARTITION p_future VALUES LESS THAN (MAXVALUE)';

		$query->counter_log='CREATE TABLE IF NOT EXISTS %counter_log%
		(
			`id` bigint(20) unsigned NOT NULL auto_increment,
			`new_user` tinyint(4) default NULL,
			`log_date` datetime default NULL,
			`user` int(10) unsigned default NULL,
			`ip` bigint(20) unsigned default NULL,
			`url` varchar(255) default NULL,
			`referer` varchar(255) default NULL,
			`browser` varchar(255) default NULL,
			`version` varchar(4) default NULL,
			`platform` varchar(10) default NULL,
			`browsername` varchar(30) default NULL,
			PRIMARY KEY  (`id`, `log_date`),
			KEY `user` (`user`,`id`),
			KEY `ip` (`ip`,`id`),
			KEY `log_date` (`log_date`),
			KEY `url` (`url`)
		)
		PARTITION BY RANGE (YEAR(`log_date`))
		(
			'.$partition.'
		);';

		$query->counter_bot = 'CREATE TABLE %counter_bot% LIKE %counter_log%';

		$query->vocabulary='CREATE TABLE IF NOT EXISTS %vocabulary% (
			`vid` int(10) unsigned NOT NULL auto_increment,
			`name` varchar(255) NULL,
			`description` text,
			`help` varchar(255) NULL,
			`relations` tinyint(3) unsigned NOT NULL default 0,
			`hierarchy` tinyint(3) unsigned NOT NULL default 0,
			`multiple` tinyint(3) unsigned NOT NULL default 0,
			`required` tinyint(3) unsigned NOT NULL default 0,
			`tags` tinyint(3) unsigned NOT NULL default 0,
			`module` varchar(255) NULL,
			`weight` tinyint(4) NOT NULL default 0,
			PRIMARY KEY  (`vid`)
		);';

		$query->vocabulary_types='CREATE TABLE IF NOT EXISTS %vocabulary_types% (
			`vid` int(10) unsigned NOT NULL default 0,
			`type` varchar(32) NOT NULL,
			PRIMARY KEY  (`vid`,`type`)
		);';

		$query->tag='CREATE TABLE IF NOT EXISTS %tag% (
			`tid` int(10) unsigned NOT NULL auto_increment,
			`vid` int(10) unsigned NULL default NULL,
			`ownid` int(10) unsigned NULL default NULL,
			`taggroup` varchar(30) NULL default NULL,
			`catid` int(10) unsigned NULL default NULL,
			`catparent` int(10) unsigned NULL default NULL,
			`process` tinyint(4) NULL default NULL,
			`isdefault` ENUM("Yes") DEFAULT NULL,
			`name` varchar(255) NOT NULL DEFAULT "",
			`description` text,
			`weight` tinyint(4) NOT NULL default 0,
			`liststyle` varchar(10) NULL default NULL,
			`listclass` VARCHAR(50) NULL DEFAULT NULL,
			PRIMARY KEY  (`tid`),
			KEY `vid` (`vid`),
			KEY `taggroup` (`taggroup`)
		);';

		$query->tag_hierarchy='CREATE TABLE IF NOT EXISTS %tag_hierarchy% (
			`tid` int(10) unsigned NOT NULL default 0,
			`parent` int(10) unsigned NOT NULL default 0,
			PRIMARY KEY  (`tid`,`parent`),
			KEY `tid` (`tid`),
			KEY `parent` (`parent`)
		);';

		$query->tag_relation='CREATE TABLE IF NOT EXISTS %tag_relation% (
			`trid` int(11) NOT NULL auto_increment,
			`tid1` int(10) unsigned NOT NULL default 0,
			`tid2` int(10) unsigned NOT NULL default 0,
			PRIMARY KEY  (`trid`),
			UNIQUE KEY `tid1_tid2` (`tid1`,`tid2`),
			KEY `tid2` (`tid2`)
		);';

		$query->tag_synonym='CREATE TABLE IF NOT EXISTS %tag_synonym% (
			`tsid` int(11) NOT NULL auto_increment,
			`tid` int(10) unsigned NOT NULL default 0,
			`name` varchar(255) NOT NULL default "",
			PRIMARY KEY  (`tsid`),
			KEY `tid` (`tid`),
			KEY `name` (`name`)
		);';

		$query->tag_topic='CREATE TABLE IF NOT EXISTS %tag_topic% (
			`tpid` int(10) unsigned NOT NULL default 0,
			`vid` int(10) unsigned NOT NULL default 0,
			`tid` int(10) unsigned NOT NULL default 0,
			PRIMARY KEY  (`tid`,`tpid`),
			KEY `tpid` (`tpid`),
			KEY `tid` (`tid`)
		);';

		$query->topic = 'CREATE TABLE IF NOT EXISTS %topic% (
			`tpid` int(10) unsigned NOT NULL auto_increment,
			`revid` int(10) unsigned default NULL,
			`type` varchar(32) DEFAULT NULL,
			`parent` int(10) unsigned DEFAULT NULL,
			`thread` int(10) unsigned DEFAULT NULL,
			`template` varchar(50) DEFAULT NULL,
			`language` varchar(12) DEFAULT NULL,
			`weight` tinyint(4) NOT NULL default 0,
			`bid` int(10) unsigned default NULL,
			`status` tinyint(4) NOT NULL default 1 COMMENT "1=draft 2=publish 3=waiting 4=block 5=lock",
			`approve` enum("LEARN","USE","MASTER") NOT NULL DEFAULT "LEARN",
			`access` tinyint(4) NOT NULL default 1,
			`orgid` int(10) unsigned DEFAULT NULL,
			`uid` int(10) unsigned DEFAULT NULL,
			`poster` varchar(20) DEFAULT NULL,
			`email` varchar(50) NULL DEFAULT NULL,
			`areacode` char(8) DEFAULT NULL,
			`title` varchar(255) DEFAULT NULL,
			`created` datetime DEFAULT NULL,
			`changed` datetime DEFAULT NULL,
			`promote` tinyint(4) NOT NULL default 0,
			`moderate` tinyint(4) NOT NULL default 0,
			`sticky` tinyint(4) unsigned NOT NULL default 0,
			`rating` DECIMAL(2,1) NULL DEFAULT NULL,
			`ratetimes` BIGINT NOT NULL DEFAULT 0,
			`liketimes` BIGINT NOT NULL DEFAULT 0,
			`comment` tinyint(4) NOT NULL default 0,
			`view` mediumint(8) unsigned NOT NULL default 0,
			`last_view` datetime DEFAULT NULL,
			`reply` mediumint(8) unsigned NOT NULL default 0,
			`last_reply` datetime DEFAULT NULL,
			`ip` bigint(20) DEFAULT NULL,
			PRIMARY KEY  (`tpid`),
			KEY `revid` (`revid`),
			KEY `created` (`created`),
			KEY `changed` (`changed`),
			KEY `last_reply` (`last_reply`),
			KEY `views` (`view`),
			KEY `last_view` (`last_view`),
			KEY `reply` (`reply`),
			KEY `status` (`status`),
			KEY `sticky` (`sticky`),
			KEY `uid` (`uid`),
			KEY `areacode` (`areacode`),
			KEY `type` (`type`,`tpid`),
			KEY `promote_status` (`promote`,`status`),
			KEY `bid` (`bid`),
			KEY `type_bid` (`type`,`bid`,`status`),
			KEY `title` ( `title` ),
			KEY `parent` ( `parent` ),
			KEY `orgid` ( `orgid` ),
			KEY `rating` (`rating`),
			KEY `ratetimes` (`ratetimes`),
			KEY `liketimes` (`liketimes`),
			KEY `approve` (`approve`)
		);';

		$query->topic_revisions='CREATE TABLE IF NOT EXISTS %topic_revisions% (
			`tpid` int(10) unsigned NOT NULL,
			`revid` int(10) unsigned NOT NULL auto_increment,
			`uid` int(10) unsigned default NULL,
			`format` tinyint(4) NOT NULL default 0,
			`title` varchar(255) NULL,
			`body` LONGTEXT,
			`property` text,
			`email` varchar(100) NULL,
			`homepage` varchar(200) NULL,
			`redirect` varchar(255) NULL,
			`css` TEXT NULL DEFAULT NULL,
			`phpBackend` LONGTEXT NULL DEFAULT NULL,
			`script` LONGTEXT NULL DEFAULT NULL,
			`data` JSON NOT NULL DEFAULT "{}",
			`timestamp` datetime default NULL,
			PRIMARY KEY  (`revid`),
			KEY `tpid` (`tpid`),
			KEY `muid` (`uid`)
		);';

		$query->topic_types='CREATE TABLE IF NOT EXISTS %topic_types% (
			`type` varchar(32) NOT NULL,
			`name` varchar(255) NULL,
			`module` varchar(255) NULL,
			`description` text NULL,
			`help` text NULL,
			`has_title` tinyint(3) unsigned NOT NULL default 0,
			`title_label` varchar(255) NULL,
			`has_body` tinyint(3) unsigned NOT NULL default 0,
			`body_label` varchar(255) NULL,
			`min_word_count` smallint(5) unsigned NOT NULL default 0,
			`custom` tinyint(4) NOT NULL default 0,
			`modified` tinyint(4) NOT NULL default 0,
			`locked` tinyint(4) NOT NULL default 0,
			`orig_type` varchar(255) NULL,
			PRIMARY KEY  (`type`)
		);';

		$query->topic_comments='CREATE TABLE IF NOT EXISTS %topic_comments% (
			`cid` int(11) unsigned NOT NULL auto_increment,
			`pid` int(11) unsigned NOT NULL default 0,
			`tpid` int(11) unsigned NOT NULL default 0,
			`uid` int(11) unsigned default NULL,
			`status` tinyint(3) unsigned NOT NULL default 0,
			`no` int(10) unsigned NOT NULL default 0,
			`giverating` TINYINT NULL DEFAULT NULL,
			`subject` varchar(64) NOT NULL default "",
			`comment` longtext NOT NULL,
			`hostname` varchar(128) NOT NULL default "",
			`timestamp` datetime default NULL,
			`format` tinyint(3) NOT NULL default 0,
			`thread` varchar(255) NOT NULL,
			`name` varchar(60) default NULL,
			`mail` varchar(64) default NULL,
			`homepage` varchar(255) default NULL,
			`ip` bigint(20) NOT NULL default 0,
			PRIMARY KEY  (`cid`),
			KEY `pid` (`pid`),
			KEY `tpid` (`tpid`),
			KEY `status` (`status`),
			KEY `timestamp` (`timestamp`)
		);';

		$query->topic_files='CREATE TABLE IF NOT EXISTS %topic_files% (
			`fid` int(10) unsigned NOT NULL auto_increment,
			`fkey` varchar(32) NULL DEFAULT NULL,
			`tpid` int(10) unsigned NULL DEFAULT NULL,
			`cid` int(10) unsigned NULL DEFAULT NULL,
			`uid` int(10) unsigned NULL DEFAULT NULL,
			`orgid` int(10) unsigned NULL DEFAULT NULL,
			`refid` int(10) unsigned NULL DEFAULT NULL,
			`type` enum("photo","doc","movie","audio") DEFAULT NULL,
			`tagname` VARCHAR(50) NULL DEFAULT NULL,
			`folder` VARCHAR(50) NULL DEFAULT NULL,
			`cover` ENUM("Yes") NULL DEFAULT NULL,
			`gallery` int(10) unsigned DEFAULT NULL,
			`file` varchar(200) DEFAULT NULL,
			`title` varchar(255) DEFAULT NULL,
			`description` text,
			`comment` tinyint(4) NOT NULL DEFAULT 0,
			`votes` int(10) unsigned NOT NULL DEFAULT 0,
			`view` smallint(5) unsigned NOT NULL DEFAULT 0,
			`last_view` datetime DEFAULT NULL,
			`reply` smallint(5) unsigned NOT NULL DEFAULT 0,
			`last_reply` datetime DEFAULT NULL,
			`download` int(10) unsigned NOT NULL DEFAULT 0,
			`last_download` datetime DEFAULT NULL,
			`timestamp` datetime DEFAULT NULL,
			`ip` bigint(20) NOT NULL DEFAULT 0,
			PRIMARY KEY  (`fid`),
			KEY `tpid` (`tpid`),
			KEY `cid` (`cid`),
			KEY `uid` (`uid`),
			KEY `gallery` (`gallery`),
			KEY `file` (`file`),
			KEY `type` (`type`),
			KEY `tagname` (`tagname`),
			KEY `orgid` (`orgid`),
			KEY `refid` (`refid`),
			UNIQUE (`fkey`)
		);';

		$query->topic_parent='CREATE TABLE IF NOT EXISTS %topic_parent% (
			`tpid` int(11) UNSIGNED NOT NULL DEFAULT 0,
			`parent` int(11) UNSIGNED NOT NULL DEFAULT 0,
			`tgtid` int(11) UNSIGNED NOT NULL DEFAULT 0,
			`bdgroup` int(11) UNSIGNED NOT NULL DEFAULT 0,
			`budget` decimal(12,2) NOT NULL,
			PRIMARY KEY (`tpid`,`parent`,`tgtid`,`bdgroup`),
			KEY `tpid` (`tpid`),
			KEY `parent` (`parent`),
			KEY `tgtid` (`tgtid`),
			KEY `bdgroup` (`bdgroup`)
		)';

		$query->topic_user='CREATE TABLE IF NOT EXISTS %topic_user% (
			`tpid` int(11) UNSIGNED NOT NULL DEFAULT 0,
			`uid` int(11) UNSIGNED NOT NULL DEFAULT 0,
			`membership` enum("MANAGER","FOLLOWER","TRAINER","OWNER","ACCOUNTANT","FINANCE","REGULAR MEMBER","COMMENTATOR","VIEWER","EDITOR") NOT NULL DEFAULT "REGULAR MEMBER",
			PRIMARY KEY (`tpid`,`uid`)
		)';

		$query->topic_data='CREATE TABLE IF NOT EXISTS %topic_data% (
			`tpid` int(11) UNSIGNED NOT NULL,
			`json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`json`)),
			PRIMARY KEY (`tpid`)
		)';

		$query->url_alias='CREATE TABLE IF NOT EXISTS %url_alias% (
			`pid` int(10) unsigned NOT NULL auto_increment,
			`alias` varchar(128) NOT NULL,
			`system` varchar(128) NOT NULL,
			`language` varchar(12) NOT NULL default "",
			PRIMARY KEY  (`pid`),
			UNIQUE KEY `dst_language` (`system`,`language`),
			KEY `alias` (`alias`)
		);';

		$query->session='CREATE TABLE IF NOT EXISTS %session% (
			`sess_id` varchar(200) NOT NULL,
			`user` varchar(30) DEFAULT NULL,
			`sess_start` datetime NOT NULL,
			`expire` INT NOT NULL DEFAULT 0,
			`sess_last_acc` datetime NOT NULL,
			`sess_data` text  NOT NULL,
			PRIMARY KEY (`sess_id`),
			KEY `user` (`user`),
			KEY `sess_start` (`sess_start`),
			KEY `expire` (`expire`),
			KEY `sess_last_acc` (`sess_last_acc`)
		);';

		$query->property='CREATE TABLE IF NOT EXISTS %property% (
			`propid` bigint(20) unsigned NOT NULL DEFAULT 0,
			`module` varchar(30) NOT NULL DEFAULT "",
			`name` varchar(30) NOT NULL DEFAULT "",
			`item` varchar(30) DEFAULT NULL,
			`value` text NULL DEFAULT NULL,
			PRIMARY KEY (`propid`,`module`,`name`), KEY `name` (`name`)
		);';

		$query->watchdog='CREATE TABLE IF NOT EXISTS %watchdog% (
			`wid` bigint(20) NOT NULL auto_increment,
			`date` datetime default NULL,
			-- `logDate` date default NULL,
			`uid` int(10) unsigned default NULL,
			`ip` int(11) NOT NULL default 0,
			`module` varchar(20) default NULL,
			`keyword` varchar(50) default NULL,
			`keyid` bigint(20) NULL DEFAULT NULL,
			`fldname` varchar(100) NULL DEFAULT NULL,
			`message` text,
			`url` varchar(255) default NULL,
			`referer` varchar(255) default NULL,
			`browser` varchar(255) default NULL,
			PRIMARY KEY  (`wid`),
			KEY `uid` (`uid`),
			KEY `ip` (`ip`),
			KEY `date` (`date`),
			-- KEY `logDate` (`logDate`),
			KEY `module` (`module`),
			KEY `keyword` (`keyword`),
			KEY `keyid` (`keyid`),
			KEY `fldname` (`fldname`),
			KEY `modulekeyword` (`module`, `keyword`)
		);';

		$query->reaction='CREATE TABLE IF NOT EXISTS %reaction% (
			`actid` int(11) NOT NULL AUTO_INCREMENT,
			`refid` int(11) NULL,
			`uid` int(11) NULL,
			`action` varchar(10) NULL,
			`dateact` bigint(20) NULL,
			PRIMARY KEY (`actid`),
			KEY `refid` (`refid`),
			KEY `uid` (`uid`),
			KEY `action` (`action`),
			KEY `dateact` (`dateact`)
		);';

		$query->lastno = 'CREATE TABLE IF NOT EXISTS %lastno% (
			`orgId` int(11) NOT NULL,
			`docName` varchar(20) NOT NULL,
			`docFormat` varchar(20) NOT NULL,
			`lastNo` varchar(20) NOT NULL,
			`resetOnPeriod` tinyint(4) NOT NULL DEFAULT 1,
			PRIMARY KEY (`orgId`,`docName`)
		);';

		// create table with prefix
		foreach ($query as $table => $stmt) {
			if (!DB::tableExists('%'.$table.'%')) {
				DB::query([$stmt]);
				// debugMsg('ERROR: '.R('error'));
				// debugMsg('ERROR -> '.R('error')->query);
				// debugMsg($a,'$a');
				$result->query[] = R('query');
				// $result->complete[] = R('query');
				if (R('error')->code) {
					$result->error[] = R('error')->query;
				} else {
					$result->complete[] = R('query'); //$prefix.$table;
				}
			}
		}

		return $result;
	}

	/**
	 * Set default configuration
	 *
	 * @return null
	 */
	private function setDefaultConfig() {
		// add default module
		$add_module_lists=array('system','user','paper','comment','watchdog');
		$perm=cfg('perm');
		// add new module
		foreach ($add_module_lists as $module) {
			process_install_module($module);
		}

		cfg_db('encrypt_key',md5(rand().time()).md5(uniqid()));
		cfg_db('version.install',cfg('core.version.install'));

		$cfgWebHomepage=cfg('web.homepage');
		$cfgNavigator=cfg('navigator');
		if (empty($cfgWebHomepage)) cfg_db('web.homepage','paper');
		if (empty($cfgNavigator)) cfg_db('navigator','<div class="page -nav">
	<nav class="nav -owner">
	<h2 class="-header">Owner Menu</h2>
	<ul class="menu -pulldown">
	<li class="-profile -left-side"><div class="widget signform" data-option-replace="yes" data-paper="story:ส่งข่าว-บทความ-วีดีโอ"></div></li>
	<?php echo i()->ok ? \'<li class="-signout"><a href="{url:signout}"><i class="icon -material">logout</i><span>Sign Out</span></a>\' : \'<li class="-signup"><a href="{url:user/register}"><span>Sign Up</span></a>\';?></li>
	</ul>
	</nav>
	<nav class="nav -main sg-responsivemenu">
	<h2 class="-header">Menu</h2>
	<ul class="menu -main">
	<li><a href="{url:/}">Home</a></li>
	<li><a href="{url:sitemap}">Site map</a></li>
	<li><a href="{url:contact}">Contact</a></li>
	<li><a href="{url:aboutus}">About us</a></li>
	</ul>
	</nav>
	</div><!-- page -nav -->');
	}

}
?>