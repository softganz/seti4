<?php
/**
* Admin : Log Menu
* Created 2016-11-08
* Modify  2022-03-31
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
							'leading' => new Icon(''),
							'trailing' => new Button([
								'href' => url('admin/log/ban'),
								'text' => 'Ban List',
							]),
							'subtitle' => 'View IP ban list.',
						]),
					], // children
				]), // Card


				new Card([
					'children' => [
						new ListTile([
							'crossAxisAlignment' => 'start',
							'title' => 'Cache viewer',
							'leading' => new Icon(''),
							'trailing' => new Button([
								'href' => url('admin/log/cache'),
								'text' => 'Cache viewer',
							]),
							'subtitle' => 'View cache log.',
						]),
					], // children
				]), // Card

				new Card([
					'children' => [
						new ListTile([
							'crossAxisAlignment' => 'start',
							'title' => 'Clear log',
							'leading' => new Icon(''),
							'trailing' => new Button([
								'href' => url('admin/log/clear'),
								'text' => 'Clear log',
							]),
							'subtitle' => 'Clear old log.',
						]),
					], // children
				]), // Card

				new Card([
					'children' => [
						new ListTile([
							'crossAxisAlignment' => 'start',
							'title' => 'Recent log entries',
							'leading' => new Icon(''),
							'trailing' => new Button([
								'href' => url('watchdog'),
								'text' => 'Recent log entries',
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
							'leading' => new Icon(''),
							'trailing' => new Button([
								'href' => url('watchdog/list/keyword/access denied'),
								'text' => 'View access denied',
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
							'leading' => new Icon(''),
							'trailing' => new Button([
								'href' => url('watchdog/list/keyword/page not found'),
								'text' => 'Last "page not found" errors',
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
							'leading' => new Icon(''),
							'trailing' => new Button([
								'href' => url('watchdog/list/keyword/access denied'),
								'text' => 'Top "access denied" errors',
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
							'leading' => new Icon(''),
							'trailing' => new Button([
								'href' => url('watchdog/list/keyword/page not found'),
								'text' => 'Top "page not found" errors',
							]),
							'subtitle' => 'Top "page not found" errors',
						]),
					], // children
				]), // Card

				new Card([
					'children' => [
						new ListTile([
							'crossAxisAlignment' => 'start',
							'title' => 'Log analysis',
							'leading' => new Icon(''),
							'trailing' => new Button([
								'href' => url('watchdog/analysis'),
								'text' => 'Log analysis',
							]),
							'subtitle' => '',
						]),
					], // children
				]), // Card

				new Card([
					'children' => [
						new ListTile([
							'crossAxisAlignment' => 'start',
							'title' => 'Web statistics',
							'leading' => new Icon(''),
							'trailing' => new Button([
								'href' => url('stats'),
								'text' => 'Web statistics',
							]),
							'subtitle' => 'Show web statistics',
						]),
					], // children
				]), // Card

			], // children
		]);
	}
}
?>