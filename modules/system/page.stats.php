<?php
/**
* Stats   :: Main Page
* Created :: 2022-02-13
* Modify  :: 2022-12-20
* Version :: 2
*
* @return Widget
*
* @usage stats
*/

class Stats extends Page {
	function build() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'Current online <b>'.number_format(CounterModel::onlineCount()).'</b> users @'.sg_date(cfg('dateformat')),
				'leading' => '<i class="icon -material">pie_chart</i>',
				'navigator' => [
					'<a href="'.url('stats').'"><i class="icon -material">pie_chart</i><span>STAT</span></a>',
					'<a class="sg-action" href="'.url('stats/online').'" data-rel="#main"><i class="icon -material">people</i><span>ALL</span></a>',
					'<a class="sg-action" href="'.url('stats/online', ['show' => 'user']).'" data-rel="#main"><i class="icon -material">person</i><span>USER</span></a>',
					'<a class="sg-action" href="'.url('stats/online', ['show' => 'member']).'" data-rel="#main"><i class="icon -material">account_circle</i><span>MEMBER</a>',
					'<a class="sg-action" href="'.url('stats/online', ['show' => 'bot']).'" data-rel="#main"><i class="icon -material">block</i><span>BOT</span></a>',
				], // Navigator
			]), // AppBar
			'body' => new Widget([
				'children' => [
					'<div class="sg-load" data-url="'.url('stats/online', ['show' => 'member']).'"></div>',
					'<div class="sg-load" data-url="'.url('stats/hits/per/day').'"></div>',
					'<div class="sg-load" data-url="'.url('stats/hits/per/month').'"></div>',
					// R::View('stats.hits.per.month'),
				], // children
			]), // Widget
		]);
	}
}
?>