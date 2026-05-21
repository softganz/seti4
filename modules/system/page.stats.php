<?php
/**
 * Stats   :: Main Page
 * Author  :: Little Bear<softganz@gmail.com>
 * Created :: 2022-02-13
 * Modify  :: 2026-05-21
 * Version :: 5
 *
 * @return Widget
 *
 * @example stats
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

	/**
	 * Build page
	 *
	 * @return object
	 */
	#[\Override]
	function build(): object {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'Current online '
					. '<b>' . number_format(count((Array) CounterModel::onlineUsers(['type' => 'member']))) . '/' . number_format(CounterModel::onlineCount()) . '</b> '
					. 'users @' . sg_date(cfg('dateformat')),
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
								]) : null,
							], // children
						]), // Dropbox
					], // children
				]), // Row
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
			'body' => new Widget([
				'children' => [
					'<div class="sg-load" data-url="' . Url::link('stats/online', ['show' => 'member']) . '"></div>',
					'<div class="sg-load" data-url="' . Url::link('stats/hits/per/day') . '"></div>',
					'<div class="sg-load" data-url="' . Url::link('stats/hits/per/month') . '"></div>',
				], // children
			]), // Widget
		]);
	}
}
?>
