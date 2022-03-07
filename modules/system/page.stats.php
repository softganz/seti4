<?php
/**
* Stats :: Main Page
* Created 2022-02-13
* Modify  2022-02-13
*
* @return Widget
*
* @usage stats
*/

import('model:counter.php');

class Stats extends Page {
	function build() {
		$today = today();

		$onlines = mydb::select('SELECT COUNT(*) `total` FROM %users_online% LIMIT 1')->total;

		$yesterday = date('Y-m-d',mktime(0,0,0,$today->mon,$today->mday-1,$today->year));

		mydb::where('log_date>= :yesterday', ':yesterday', $yesterday);
		$rs = mydb::select(
			'SELECT `log_date`, `hits`, `users`
			FROM %counter_day%
			%WHERE%
			ORDER BY log_date DESC LIMIT 2'
		);

		foreach ($rs->items as $item) {
			if ($item->log_date == $today->date) $today_hits = $item;
			else if ($item->log_date == $yesterday) $yesterday_hits = $item;
		}

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'Current online <b>'.number_format(CounterModel::onlineCount()).'</b> users @'.sg_date(cfg('dateformat')),
				'leading' => '<i class="icon -material">pie_chart</i>',
				'navigator' => [
					'<a href="'.url('stats').'"><i class="icon -material">pie_chart</i><span>STAT</span></a>',
					'<a href="'.url('stats/online').'"><i class="icon -material">people</i><span>ALL</span></a>',
					'<a href="'.url('stats/online', array('show' => 'user')).'"><i class="icon -material">person</i><span>USER</span></a>',
					'<a href="'.url('stats/online', array('show' => 'member')).'"><i class="icon -material">account_circle</i><span>MEMBER</a>',
					'<a href="'.url('stats/online', array('show' => 'bot')).'"><i class="icon -material">block</i><span>BOT</span></a>',
				], // Navigator
			]), // AppBar
			'body' => new Widget([
				'children' => [
					'<p>'
						. 'Today <strong>'.number_format($today_hits->hits).'</strong> hits from <strong>'.number_format($today_hits->users).'</strong> users. '
						. 'Yesterday <strong>'.number_format($yesterday_hits->hits).'</strong> hits from <strong>'.number_format($yesterday_hits->users).'</strong> users.'
						. '</p>',
					R::PageWidget('stats.online'),

					R::View('stats.hits.per.day',date('Y'),date('m')),

					R::View('stats.hits.per.month'),
				], // children
			]), // Widget
		]);
	}
}
?>
<?php
function stats($self) {
	return $ret;
}
?>