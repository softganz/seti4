<?php
/**
 * Stats   :: Main Page
 * Author  :: Little Bear<softganz@gmail.com>
 * Created :: 2022-02-13
 * Modify  :: 2025-12-23
 * Version :: 4
 *
 * @return Widget
 *
 * @usage stats
 */

class Stats extends Page {
	var $right;

	function __construct() {
		parent::__construct([
			'right' => (Object) [
				'accessReport' => is_admin(),
			]
		]);
	}

	function build() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'Current online <b>'.number_format(CounterModel::onlineCount()).'</b> users @'.sg_date(cfg('dateformat')),
				'leading' => '<i class="icon -material">pie_chart</i>',
				'trailing' => new Row([
					'children' => [
						new Dropbox([
							'children' => [
								$this->right->accessReport ? new Button([
									'class' => 'sg-action',
									'href' => url('stats/report/min10'),
									'text' => 'Hits in 10 min.',
									'icon' => new Icon('insights'),
									'rel' => '#main'
								]) : NULL,
							], // children
						]), // Dropbox
					], // children
				]), // Row
				'navigator' => [
					'<a href="'.url('stats').'"><i class="icon -material">pie_chart</i><span>STAT</span></a>',
					'<a class="sg-action" href="'.url('stats/online').'" data-rel="#main"><i class="icon -material">people</i><span>ALL</span></a>',
					'<a class="sg-action" href="'.url('stats/online', ['show' => 'user']).'" data-rel="#main"><i class="icon -material">person</i><span>USER</span></a>',
					'<a class="sg-action" href="'.url('stats/online', ['show' => 'member']).'" data-rel="#main"><i class="icon -material">account_circle</i><span>MEMBER</a>',
					'<a class="sg-action" href="'.url('stats/online', ['show' => 'bot']).'" data-rel="#main"><i class="icon -material">block</i><span>BOT</span></a>',
					'<a class="sg-action" href="'.url('stats/list').'" data-rel="#main"><i class="icon -material">view_list</i><span>List</span></a>',
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