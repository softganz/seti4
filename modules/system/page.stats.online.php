<?php
/**
 * Stats   :: Current User Online
 * Author  :: Little Bear<softganz@gmail.com>
 * Created :: 2018-09-01
 * Modify  :: 2025-12-23
 * Version :: 4
 *
 * @param String $arg1
 * @return Widget
 *
 * @usage stats/online
 */

use Softganz\DB;

class StatsOnline extends Page {
	var $onlineType;
	var $right;

	function __construct() {
		$this->onlineType = post('show');
		$this->right = (Object) [
			'fullView' => user_access('administer contents,administer watchdogs'),
		];
	}

	function build() {
		$today = today();

		$onlines = DB::select(['SELECT COUNT(*) `total` FROM %users_online% LIMIT 1'])->total;

		$yesterday = date('Y-m-d',mktime(0,0,0,$today->mon,$today->mday-1,$today->year));

		$rs = DB::select([
			'SELECT `log_date`, `hits`, `users`
			FROM %counter_day%
			%WHERE%
			ORDER BY log_date DESC LIMIT 2',
			'where' => [
				'%WHERE%' => [
					['log_date>= :yesterday', ':yesterday' => $yesterday]
				],
			],
		]);

		foreach ($rs->items as $item) {
			if ($item->log_date == $today->date) $today_hits = $item;
			else if ($item->log_date == $yesterday) $yesterday_hits = $item;
		}

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'Current online <b>'.number_format(CounterModel::onlineCount()).'</b> users @'.sg_date(cfg('dateformat')),
				'leading' => '<i class="icon -material">account_circle</i>',
				'navigator' => [
					'<a href="'.url('stats').'"><i class="icon -material">pie_chart</i><span>STAT</span></a>',
					'<a href="'.url('stats/online').'"><i class="icon -material">people</i><span>ALL</span></a>',
					'<a href="'.url('stats/online', ['show' => 'user']).'"><i class="icon -material">person</i><span>USER</span></a>',
					'<a href="'.url('stats/online', ['show' => 'member']).'"><i class="icon -material">account_circle</i><span>MEMBER</a>',
					'<a href="'.url('stats/online', ['show' => 'bot']).'"><i class="icon -material">block</i><span>BOT</span></a>',
				], // Navigator
			]), // AppBar
			'body' => new Card([
				'children' => [
					new ListTile(['title' => 'Members Online', 'leading' => new Icon('groups')]),
					new Container([
						'class' => '-sg-paddingnorm',
						'children' => [
							'Today <strong>'.number_format($today_hits->hits).'</strong> hits from <strong>'.number_format($today_hits->users).'</strong> users. ',
							'Yesterday <strong>'.number_format($yesterday_hits->hits).'</strong> hits from <strong>'.number_format($yesterday_hits->users).'</strong> users.'
						],
					]),
					new Table([
						'caption' => 'Current online user',
						'thead' => [
							'no'=>'',
							'IP',
							'profile -center' => '',
							'Name',
							'comming -date'=>'Coming',
							'access -date'=>'Access',
							'amt hits'=>'Hits',
							'Browser/Host',
							'ban -center' => $this->right->fullView ? '<a class="sg-action" href="'.url('admin/ban..list').'" data-rel="box" data-width="full">Ban</a>' : '',
						],
						'children' => array_map(
							function($rs) {
								static $no = 0;
								$full_ip = $rs->ip;
								$show_ip = $this->right->fullView ? $full_ip : sg_sub_ip($full_ip);
								$current_date = date('Y-m-d');

								return [
									++$no,
									($this->right->fullView ? '<a href="'.url('stats/list',array('ip'=>$full_ip)).'">' : '')
									. $show_ip
									. ($this->right->fullView ? '</a>' : ''),
									$rs->username ? '<img class="profile-photo -sg-24" src="'.BasicModel::user_photo($rs->username).'" />' : '',
									$this->right->fullView && $rs->name ? '<a href="'.url('stats/list',array('user'=>$rs->uid)).'">'.$rs->name.'</a>' : $rs->name,
									date(($current_date != date('Y-m-d',$rs->coming) ? 'Y-m-d ' : '').'H:i:s',$rs->coming),
									date(($current_date != date('Y-m-d',$rs->access) ? 'Y-m-d ' : '').'H:i:s',$rs->access),
									number_format($rs->hits),
									$rs->browser.' '.($this->right->fullView ? $rs->host : ''),
									$this->right->fullView ? '<a class="sg-action btn -link" href="'.url('admin/ban/request', ['ip' => $full_ip, 'host' => $rs->host]).'" data-rel="box" data-width="800"><i class="icon -material">block</i></a>' : '',
								];
							},
							CounterModel::onlineUsers(['type' => $this->onlineType])
						), // children
					]), // Table
				], // children
			]), // Widget
		]);
	}
}
?>