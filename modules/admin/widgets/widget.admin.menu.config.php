<?php
/**
* Admin : Configuration Menu
* Created 2016-11-08
* Modify  2022-03-31
*
* @return Widget
*
* @usage new AdminMenuConfigWidget()
*/

class AdminMenuConfigWidget extends Widget {
	function build() {
		return new Column([
			'children' => [
				new Card([
					'children' => [
						new ListTile([
							'title' => 'App Agent',
							'leading' => new Icon(R()->appAgent ? 'android' : 'web'),
							'trailing' => R()->appAgent ? '<a class="btn" class="sg-action" href="'.url('',array('setting:app' => '{}')).'" data-rel="none" data-done="reload">Mobile App</a>' : '<a class="sg-action btn" href="'.url('',array('setting:app' => '{OS:%22Android%22,ver:%220.20.0%22,type:%22App%22,dev:%22Softganz%22}')).'" data-rel="none" data-done="reload">Web App</a>',
							'subtitle' => 'Agent is '.(R()->appAgent ? implode(',', (Array) R()->appAgent) : 'Web Agent'),
						]),
					], // children
				]), // Card

				new Card([
					'children' => [
						new ListTile([
							'title' => 'Clear empty session',
							'leading' => new Icon('groups'),
							'trailing' => '<a class="btn" href="'.url('admin/config/session/clear').'">Clear empty session</a>',
							'subtitle' => 'Remove all empty from database.',
						]),
					], // children
				]),

				new Card([
					'children' => [
						new ListTile([
							'title' => 'Clear day key',
							'leading' => new Icon('keys'),
							'trailing' => new Row([
								'child' => '<a class="sg-action btn" href="'.url('admin/config/daykey/clear').'" data-rel="none" data-title="Clear" data-confirm="Clear daykey?">Clear daykey</a>'
							]),
							'subtitle' => 'Remove all daykey from database.',
						]),
					], // children
				]), // Card

				new Card([
					'children' => [
						new ListTile([
							'title' => 'Clear user online',
							'leading' => new Icon('people'),
							'trailing' => '<a class="btn" href="'.url('admin/config/online').'">Clear user online</a>',
							'subtitle' => 'Remove all user online item from database.',
						]),
					], // children
				]),

				new Card([
					'children' => [
						new ListTile([
							'title' => 'Developer JS Mode',
							'leading' => new Icon('javascript'),
							'trailing' => new Row([
								'children' => [
									$_SESSION['devMode'] ? '<a class="sg-action btn -link" href="'.url('admin/config',['devMode' => 'clear']).'" data-rel="none" data-options=\'{"silent": true}\' data-done="load"><i class="icon -material -green">toggle_on</i><span>ON</span></a>' : '<a class="sg-action btn -link" href="'.url('admin/config',['devMode' => 'yes']).'" data-rel="none" data-options=\'{"silent": true}\' data-done="load"><i class="icon -material -gray">toggle_off</i><span>OFF</span></a>',
									// '<a class="btn" href="'.url('admin/config',['devMode' => 'clear']).'"><i class="icon -material">cancel</i><span>CLEAR</span></a>',
								]
							]), // Row
							'subtitle' => 'Developer JS Mode is '.($_SESSION['devMode'] ? 'ON' : 'OFF'),
						]),
					], // children
				]),

				new Card([
					'children' => [
						new ListTile([
							'title' => 'Re-build counter',
							'leading' => new Icon(''),
							'trailing' => new Row([
								'child' => '<a class="btn" href="'.url('admin/config/counter').'">Re-build</a>'
							]),
							'subtitle' => 'Re-build counter and write to database.',
						]),
					], // children
				]), // Card

				new Card([
					'children' => [
						new ListTile([
							'title' => 'DB variable list',
							'leading' => new Icon(''),
							'trailing' => new Row([
								'child' => '<a class="btn" href="'.url('admin/config/dbvar').'">Show</a>'
							]),
							'subtitle' => 'List of DB config',
						]),
					], // children
				]), // Card

				new Card([
					'children' => [
						new ListTile([
							'title' => 'Server information',
							'leading' => new Icon(''),
							'trailing' => new Row([
								'child' => '<a class="btn" href="'.url('admin/config/phpinfo').'">View</a>'
							]),
							'subtitle' => 'Information for web server and php environment.',
						]),
					], // children
				]), // Card

				new Card([
					'children' => [
						new ListTile([
							'title' => 'View configuration',
							'leading' => new Icon(''),
							'trailing' => new Row([
								'child' => '<a class="btn" href="'.url('admin/config/view').'">View</a>'
							]),
							'subtitle' => 'Display all software configuration.',
						]),
					], // children
				]), // Card

				new Card([
					'children' => [
						new ListTile([
							'title' => 'View cookies value',
							'leading' => new Icon(''),
							'trailing' => new Row([
								'child' => '<a class="btn" href="'.url('admin/config/cookie').'">View</a>'
							]),
							'subtitle' => 'Display all $_COOKIES variable.',
						]),
					], // children
				]), // Card

				new Card([
					'children' => [
						new ListTile([
							'title' => 'View db variable',
							'leading' => new Icon(''),
							'trailing' => new Row([
								'child' => '<a class="btn" href="'.url('admin/config/db').'">View</a>'
							]),
							'subtitle' => 'Display only configuration stroe in table.',
						]),
					], // children
				]), // Card

				new Card([
					'children' => [
						new ListTile([
							'title' => 'View session value',
							'leading' => new Icon(''),
							'trailing' => new Row([
								'child' => '<a class="btn" href="'.url('admin/config/session').'">View</a>'
							]),
							'subtitle' => 'Display all $_SESSION variable.',
						]),
					], // children
				]), // Card
			], // children
		]);

		//setting:app={OS:"Android",ver:"0.20",type:"icar",dev:"Softganz"}
	}
}
?>