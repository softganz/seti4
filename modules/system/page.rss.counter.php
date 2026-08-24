<?php
/**
 * RSS      :: Get Counter
 * Author   :: Little Bear<softganz@gmail.com>
 * Created  :: 2021-09-20
 * Modified :: 2026-08-24
 * Version  :: 2
 *
 * @return Widget
 *
 * @uses rss/counter
 */

use Softganz\DB;

class RssCounter extends Page {
	function build() {
		$timer = new Timer();
		$timer->start(0);

		$day = (int) \SG\getFirst(Request::all('day', 'int'), 7);

		$counter = cfg('counter');

		$result = DB::select([
			'SELECT
				`log_date`, SUM(`hits`) `hits`, SUM(`users`) `users`
			FROM %counter_day%
			GROUP BY `log_date`
			ORDER BY `log_date` DESC
			LIMIT $LIMIT$',
			'var' => ['$LIMIT$' => $day]
		]);

		$timer->stop(0);

		$channel = [
			'title' => 'Counter Statistics',
			'link' => cfg('domain').'/rss/counter/day/'.$day,
			'description' => strip_tags(cfg('web.slogan')),
			'language' => 'en-us',
			'pubDate' => date('Y-m-d H:i:s'),
			'lastBuildDate' => date('Y-m-d H:i:s'),
			'generator' => 'SoftGanz RSS',
			'managingEditor' => 'support@softganz.com',
			'webMaster' => 'webmaster@softganz.com',
			'responseTime' => $timer->get(0),
			'online' => [
				'date' => date('Y-m-d H:i:s'),
				'members' => intval($counter->online_members),
				'users' => intval($counter->online_count),
				'memberName' => htmlspecialchars($counter->online_name),
			],
			'items' => [],
		];

		foreach ( $result->items as $rs) {
			$channel['items'][] = [
				'title' => 'stat',
				'date' => $rs->log_date,
				'hits' => intval($rs->hits),
				'users' => intval($rs->users),
				'description' => $rs->log_date.'/'.$rs->hits.'/'.$rs->users
			];
		}

		return $channel;
	}
}
?>