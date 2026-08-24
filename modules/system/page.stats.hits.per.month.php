<?php
/**
 * Stats    :: Hit Per Month
 * Author   :: Little Bear<softganz@gmail.com>
 * Created  :: 2022-12-20
 * Modified :: 2026-08-24
 * Version  :: 2
 *
 * @return Widget
 *
 * @uses ststa/hits/per/month
 */

use Softganz\DB;

class StatsHitsPerMonth extends Page {
	function build() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'Hits Per Month',
			]), // AppBar
			'body' => new Widget([
				'children' => [
					new ListTile(['title' => 'Hits per month', 'leading' => new Icon('groups')]),
					self::showHitPerMonth(),
				], // children
			]), // Widget
		]);
	}

	private static function showHitPerMonth() {
		$dbs = DB::select([
			'SELECT
			date_format(`log_date`,"%Y-%m")  AS `log_month` 
			, SUM(`hits`) as `hits`
			, SUM(`users`) as `users` 
			FROM %counter_day% 
			GROUP BY `log_month` 
			ORDER BY `log_month` DESC'
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

		return new Table([
			'class' => 'hits -sg-text-center',
			'caption' => 'Hits per month',
			'thead' => [
				'date -date' => 'Date',
				'chart -fill' => '',
				'Hits',
				'Users',
			],
			'children' => array_map(
				function($rs) use($max_hits) {
					if ( $max_hits > 0 ) $hit_width = round($rs->hits*200/$max_hits);
					if ( $max_hits > 0 ) $user_width = round($rs->users*200/$max_hits);

					return [
						'<a href="'.url('stats/hits/per/day/'.$rs->log_month).'">'.$rs->log_month.'</a>',
						'<div class="hits-item -hit" style="width:'.$hit_width.'px;"></div><div class="hits-item -user" style="width:'.$user_width.'px;"></div>',
						number_format($rs->hits),
						number_format($rs->users),
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
				]
			]
		]);
	}
}
?>
