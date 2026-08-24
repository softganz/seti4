<?php
/**
 * Stats    :: Hit Per Day
 * Author   :: Little Bear<softganz@gmail.com>
 * Created  :: 2022-12-20
 * Modified :: 2026-08-24
 * Version  :: 3
 *
 * @param String $year
 * @return Widget
 *
 * @uses stats/hits/per/day
 */

use Softganz\DB;

class StatsHitsPerDay extends Page {
	var $year;

	function __construct($year = NULL) {
		parent::__construct([
			'year' => $year
		]);
	}

	function build() {
		if ($this->year) {
			list($year,$month) = explode('-', $this->year);
		} else {
			$month = date('m');
			$year = date('Y');
		}

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'Hits Per Day',
			]), // AppBar
			'body' => new Widget([
				'children' => [
					new ListTile(['title' => 'Hits per day', 'leading' => new Icon('groups')]),
					self::showHitPerDay($year,$month),
				], // children
			]), // Widget
		]);
	}

	private static function showHitPerDay($year=null,$month=null) {
		$dbs = DB::select([
			'SELECT `hits`, `users`, `log_date`
			FROM %counter_day%
			%WHERE%
			ORDER BY `log_date` DESC',
			'%WHERE%' => [
				['DATE_FORMAT(log_date,"%Y") = :year', ':year' => $year],
				$month ? ['DATE_FORMAT(log_date,"%m") = :month', ':month' => $month] : null
			]
		]);

		$max_hits = 0;
		$max_users = 0;
		$hits_count = 0;
		$users_count = 0;

		foreach ( $dbs->items as $rs ) {
			$max_hits = $rs->hits > $max_hits ? $rs->hits : $max_hits;
			$max_users = $rs->users > $max_users ? $rs->users : $max_users;
			$hits_count = $hits_count+$rs->hits;
			$users_count = $users_count+$rs->users;
		}

		$is_view_log = user_access('administer contents,administer watchdogs');

		return new Table([
			'class' => 'hits -sg-text-center',
			'caption' => 'Hits per day',
			'thead' => [
				'date -date' => 'Date',
				'chart -fill' => '',
				'Hits',
				'Users',
				'Member',
			],
			'children' => array_map(
				function($rs) use($is_view_log, $max_hits) {
					if ( $max_hits > 0 ) $hit_width = round($rs->hits*200/$max_hits);
					if ( $max_hits > 0 ) $user_width = round($rs->users*200/$max_hits);

					return [
						($is_view_log ? '<a href="'.url('stats/list', ['date' => $rs->log_date]).'">' : '')
						. $rs->log_date
						. ($is_view_log ? '</a>' : ''),
						'<div class="hits-item -hit" style="width:'.$hit_width.'px;"></div><div class="hits-item -user" style="width:'.$user_width.'px;"></div>',
						number_format($rs->hits),
						number_format($rs->users),
						new Button([
							'type' => 'link',
							'class' => 'sg-action',
							'href' => Url::link('stats/user/date/' . $rs->log_date),
							'rel' => 'box',
							'boxWidth' => 640,
							'icon' => new Icon('groups')
						])
					];
				},
				$dbs->items
			), // children
			'tfoot' => [
				[
					'',
					'Total',
					number_format($hits_count),
					number_format($users_count),
					'',
				],
			],
		]);
	}
}
?>
