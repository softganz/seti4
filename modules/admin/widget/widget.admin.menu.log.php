<?php
/**
* Admin   :: Log Menu
* Created :: 2016-11-08
* Modify  :: 2024-10-03
* Version :: 3
*
* @return Widget
*
* @usage new AdminMenuLogWidget()
*/

class AdminMenuLogWidget extends Widget {
	function build() {
		return new Column([
			'children' => [
				new Card([
					'children' => [
						new ListTile([
							'crossAxisAlignment' => 'start',
							'title' => 'Ban List',
							'leading' => new Icon('block'),
							'trailing' => new Button([
								'type' => 'secondary',
								'href' => Url::link('admin/ban..list'),
								'text' => 'Ban List',
								'icon' => new Icon('arrow_circle_right'),
								'iconPosition' => 'right'
							]),
							'subtitle' => 'View IP ban list.',
						]),
					], // children
				]), // Card

				new Card([
					'children' => [
						new ListTile([
							'crossAxisAlignment' => 'start',
							'title' => 'Cache Viewer',
							'leading' => new Icon('cached'),
							'trailing' => new Button([
								'type' => 'secondary',
								'href' => Url::link('admin/log/cache'),
								'text' => 'Cache Viewer',
								'icon' => new Icon('arrow_circle_right'),
								'iconPosition' => 'right'
							]),
							'subtitle' => 'View cache log.',
						]),
					], // children
				]), // Card

				new Card([
					'children' => [
						new ListTile([
							'crossAxisAlignment' => 'start',
							'title' => 'Clear Log',
							'leading' => new Icon('delete_history'),
							'trailing' => new Button([
								'type' => 'secondary',
								'href' => Url::link('admin/log/clear'),
								'text' => 'Clear Log',
								'icon' => new Icon('arrow_circle_right'),
								'iconPosition' => 'right'
							]),
							'subtitle' => 'Clear old log.',
						]),
					], // children
				]), // Card

				new Card([
					'children' => [
						new ListTile([
							'crossAxisAlignment' => 'start',
							'title' => 'Log Partition',
							'leading' => new Icon('splitscreen_bottom'),
							'trailing' => new Button([
								'type' => 'secondary',
								'href' => Url::link('admin/log/partition'),
								'text' => 'Log Partition',
								'icon' => new Icon('arrow_circle_right'),
								'iconPosition' => 'right'
							]),
							'subtitle' => 'Manage log partition.',
						]),
					], // children
				]), // Card

				new Card([
					'children' => [
						new ListTile([
							'crossAxisAlignment' => 'start',
							'title' => 'Log Analysis',
							'leading' => new Icon('analytics'),
							'trailing' => new Button([
								'type' => 'secondary',
								'href' => Url::link('watchdog/analysis'),
								'text' => 'Log Analysis',
								'icon' => new Icon('arrow_circle_right'),
								'iconPosition' => 'right'
							]),
							'subtitle' => 'Log analysis.',
						]),
					], // children
				]), // Card

				new Card([
					'children' => [
						new ListTile([
							'crossAxisAlignment' => 'start',
							'title' => 'Log Counter Count',
							'leading' => new Icon('counter_1'),
							'trailing' => new Button([
								'type' => 'secondary',
								'href' => Url::link('admin/log/counter/count'),
								'text' => 'Log Counter Count',
								'icon' => new Icon('arrow_circle_right'),
								'iconPosition' => 'right'
							]),
							'subtitle' => 'Log counter couter by minute',
						]),
					], // children
				]), // Card

				new Card([
					'children' => [
						new ListTile([
							'crossAxisAlignment' => 'start',
							'title' => 'Web Statistics',
							'leading' => new Icon('bar_chart'),
							'trailing' => new Button([
								'type' => 'secondary',
								'href' => Url::link('stats'),
								'text' => 'Web Statistics',
								'icon' => new Icon('arrow_circle_right'),
								'iconPosition' => 'right'
							]),
							'subtitle' => 'Show web statistics',
						]),
					], // children
				]), // Card

				new Card([
					'children' => [
						new ListTile([
							'crossAxisAlignment' => 'start',
							'title' => 'Recent log entries',
							'leading' => new Icon('format_list_bulleted'),
							'trailing' => new Button([
								'type' => 'secondary',
								'href' => Url::link('watchdog'),
								'text' => 'Recent log entries',
								'icon' => new Icon('arrow_circle_right'),
								'iconPosition' => 'right'
							]),
							'subtitle' => 'View recent log entries.',
						]),
					], // children
				]), // Card

				new Card([
					'children' => [
						new ListTile([
							'crossAxisAlignment' => 'start',
							'title' => 'Last "access denied" errors',
							'leading' => new Icon('format_list_bulleted'),
							'trailing' => new Button([
								'type' => 'secondary',
								'href' => Url::link('watchdog/list/keyword/access denied'),
								'text' => 'View access denied',
								'icon' => new Icon('arrow_circle_right'),
								'iconPosition' => 'right'
							]),
							'subtitle' => 'Last of "Access denied" url address',
						]),
					], // children
				]), // Card

				new Card([
					'children' => [
						new ListTile([
							'crossAxisAlignment' => 'start',
							'title' => 'Last "page not found" errors',
							'leading' => new Icon('format_list_bulleted'),
							'trailing' => new Button([
								'type' => 'secondary',
								'href' => Url::link('watchdog/list/keyword/page not found'),
								'text' => 'Last "page not found" errors',
								'icon' => new Icon('arrow_circle_right'),
								'iconPosition' => 'right'
							]),
							'subtitle' => 'Last of error "Page not found" url address',
						]),
					], // children
				]), // Card

				new Card([
					'children' => [
						new ListTile([
							'crossAxisAlignment' => 'start',
							'title' => 'Top "access denied" errors',
							'leading' => new Icon('format_list_bulleted'),
							'trailing' => new Button([
								'type' => 'secondary',
								'href' => Url::link('watchdog/list/keyword/access denied'),
								'text' => 'Top "access denied" errors',
								'icon' => new Icon('arrow_circle_right'),
								'iconPosition' => 'right'
							]),
							'subtitle' => 'Top of Access denied url address',
						]),
					], // children
				]), // Card

				new Card([
					'children' => [
						new ListTile([
							'crossAxisAlignment' => 'start',
							'title' => 'Top "page not found" errors',
							'leading' => new Icon('format_list_bulleted'),
							'trailing' => new Button([
								'type' => 'secondary',
								'href' => Url::link('watchdog/list/keyword/page not found'),
								'text' => 'Top "page not found" errors',
								'icon' => new Icon('arrow_circle_right'),
								'iconPosition' => 'right'
							]),
							'subtitle' => 'Top "page not found" errors',
						]),
					], // children
				]), // Card

			], // children
		]);
	}
}
?>