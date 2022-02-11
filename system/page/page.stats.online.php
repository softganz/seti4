<?php
/**
* Stats : Current User Online
* Created 2018-09-01
* Modify  2020-10-29
*
* @param Object $self
* @return String
*
* @usage stats/online
*/

$debug = true;

function stats_online($self) {
	$getShow = post('show');

	if ($getShow == 'user') mydb::where('o.`host` NOT LIKE "%bot%" AND o.`host` NOT LIKE "%craw%"');
	else if ($getShow == 'member') mydb::where('o.`uid` IS NOT NULL');
	else if ($getShow == 'bot') mydb::where('(o.`host` LIKE "%bot%" OR o.`host` LIKE "%craw%")');

	$stmt = 'SELECT o.* FROM %users_online% o %WHERE% ORDER BY o.`access` DESC';
	$dbs = mydb::select($stmt);


	$toolbar = new Toolbar($self, 'Current online <b>'.number_format($dbs->count()).'</b> users @'.sg_date(cfg('dateformat')));

	$toolbarNav = new Ui(NULL, 'ui-nav');
	$toolbarNav->add('<a href="'.url('stats').'"><i class="icon -material">pie_chart</i><span>STAT</span></a>');
	$toolbarNav->add('<a href="'.url('stats/online').'"><i class="icon -material">people</i><span>ALL</span></a>');
	$toolbarNav->add('<a href="'.url('stats/online', array('show' => 'user')).'"><i class="icon -material">person</i><span>USER</span></a>');
	$toolbarNav->add('<a href="'.url('stats/online', array('show' => 'member')).'"><i class="icon -material">account_circle</i><span>MEMBER</a>');
	$toolbarNav->add('<a href="'.url('stats/online', array('show' => 'bot')).'"><i class="icon -material">block</i><span>BOT</span></a>');
	$toolbar->addNav('main',$toolbarNav);

	$no = 0;
	$current_date = date('Y-m-d');

	$is_administer_watchdogs = user_access('administer contents,administer watchdogs');


	$tables = new Table();
	$tables->caption = 'Current online user';
	$tables->thead = array('no'=>'','IP','Name', 'comming -date'=>'Coming', 'access -date'=>'Access','amt hits'=>'Hits','Browser/Host',$is_administer_watchdogs?'Ban':'');

	foreach ($dbs->items as $rs) {
		$no++;
		$full_ip = $rs->ip;
		$show_ip = $is_administer_watchdogs ? $full_ip : sg_sub_ip($full_ip);

		$tables->rows[] = array($no,
			($is_administer_watchdogs ? '<a href="'.url('stats/list',array('ip'=>$full_ip)).'">' : '')
			. $show_ip
			. ($is_administer_watchdogs ? '</a>' : '')
			. ($full_ip === GetEnv('REMOTE_ADDR') ? '<i class="icon -person"></i>' : ''),
			$is_administer_watchdogs && $rs->name ? '<a href="'.url('stats/list',array('user'=>$rs->uid)).'">'.$rs->name.'</a>' : $rs->name,
			date(($current_date != date('Y-m-d',$rs->coming) ? 'Y-m-d ' : '').'H:i:s',$rs->coming),
			date(($current_date != date('Y-m-d',$rs->access) ? 'Y-m-d ' : '').'H:i:s',$rs->access),
			$rs->hits,
			$rs->browser.' '.$rs->host,
			$is_administer_watchdogs ? '<a class="sg-action btn -link" href="'.url('admin/api/ip.ban', ['ip' => $full_ip]).'" data-rel="notify" data-title="BAN IP" data-confirm="ต้องการ BAN IP นี้ไม่ให้เข้าใช้งานระบบ กรุณายืนยัน?"><i class="icon -material">block</i></a>' : '',
			);
		}
	$ret .= $tables->build();
	return $ret;
}
?>