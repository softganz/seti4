<?php
/**
 * Stats   :: Current User Online
 * Author  :: Little Bear<softganz@gmail.com>
 * Created :: 2018-09-01
 * Modify  :: 2026-05-21
 * Version :: 6
 *
 * @return Widget
 *
 * @example stats/online
 */

use Softganz\DB;

class StatsOnline extends Page {
	var $onlineType;
	var $right;

	function __construct() {
		parent::__construct([
			'onlineType' => Request::get('show'),
			$this->right = (Object) [
				'fullView' => user_access('administer contents,administer watchdogs'),
			]
		]);
	}

	/**
	 * Build page
	 *
	 * @return object
	 */
	function build(): object {
		$today = today();

		$yesterday = date('Y-m-d', mktime(0, 0, 0, $today->mon, $today->mday - 1, $today->year));

		$dayHits = DB::select([
			'SELECT `log_date`, `hits`, `users`
			FROM %counter_day%
			%WHERE%
			ORDER BY log_date DESC LIMIT 2',
			'where' => [
				'%WHERE%' => [
					['log_date >= :yesterday', ':yesterday' => $yesterday]
				],
			],
		]);

		foreach ($dayHits->items as $hit) {
			if ($hit->log_date == $today->date) $today_hits = $hit;
			else if ($hit->log_date == $yesterday) $yesterday_hits = $item;
		}

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'Current online <b>'.number_format(CounterModel::onlineCount()) . '</b> users @'.sg_date(cfg('dateformat')),
				'leading' => '<i class="icon -material">account_circle</i>',
				'navigator' => [
					new Button([
						'href' => Url::link('stats'),
						'icon' => new Icon('pie_chart'),
						'text' => 'STAT',
					]),
					new Button([
						'class' => 'sg-action',
						'href' => Url::link('stats/online'),
						'icon' => new Icon('people'),
						'text' => 'ALL',
						'rel' => '#main'
					]),
					new Button([
						'class' => 'sg-action',
						'href' => Url::link('stats/online', ['show' => 'user']),
						'icon' => new Icon('person'),
						'text' => 'USER',
						'rel' => '#main'
					]),
					new Button([
						'class' => 'sg-action',
						'href' => Url::link('stats/online', ['show' => 'member']),
						'icon' => new Icon('account_circle'),
						'text' => 'MEMBER',
						'rel' => '#main'
					]),
					new Button([
						'class' => 'sg-action',
						'href' => Url::link('stats/online', ['show' => 'bot']),
						'icon' => new Icon('block'),
						'text' => 'BOT',
						'rel' => '#main'
					]),
					new Button([
						'class' => 'sg-action',
						'href' => Url::link('stats/list'),
						'icon' => new Icon('view_list'),
						'text' => 'LOG',
						'rel' => '#main'
					]),
				], // Navigator
			]), // AppBar
			'body' => new Card([
				'children' => [
					new ListTile(['title' => 'Members Online', 'leading' => new Icon('groups')]),
					new Container([
						'class' => '-sg-paddingnorm',
						'children' => [
							'Today <strong>' . number_format($today_hits->hits) . '</strong> hits from <strong>' . number_format($today_hits->users) . '</strong> users. ',
							'Yesterday <strong>' . number_format($yesterday_hits->hits) . '</strong> hits from <strong>' . number_format($yesterday_hits->users) . '</strong> users.'
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
							'ban -center' => $this->right->fullView ? '<a class="sg-action" href="' . Url::link('admin/ban..list') . '" data-rel="box" data-width = "full">Ban</a>' : '',
						],
						'children' => array_map(
							function($rs) {
								static $no = 0;
								$full_ip = $rs->ip;
								$show_ip = $this->right->fullView ? $full_ip : sg_sub_ip($full_ip);
								$current_date = date('Y-m-d');

								return [
									++$no,
									$this->right->fullView ? new Button([
										'href' => Url::link('stats/list', ['ip' => $full_ip]),
										'text' => $show_ip,
									]) : $show_ip,

									$rs->username ? '<img class="profile-photo -sg-32" src="'.BasicModel::user_photo($rs->username) . '" />' : '',
									$this->right->fullView && $rs->name ? '<a href="' . Url::link('stats/list',array('user'=>$rs->uid)) . '">'.$rs->name.'</a>' : $rs->name,
									date(($current_date != date('Y-m-d',$rs->coming) ? 'Y-m-d ' : '') . 'H:i:s',$rs->coming),
									date(($current_date != date('Y-m-d',$rs->access) ? 'Y-m-d ' : '') . 'H:i:s',$rs->access),
									number_format($rs->hits),
									$rs->browser . ' ' . ($this->right->fullView ? $rs->host : ''),
									$this->right->fullView ? new Button([
										'type' => 'link',
										'class' => 'sg-action',
										'href' => Url::link('admin/ban', ['ip' => $full_ip, 'host' => $rs->host]),
										'icon' => new Icon('block'),
										'rel' => 'box',
										'boxWidth' => 900
									]) : '',
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
