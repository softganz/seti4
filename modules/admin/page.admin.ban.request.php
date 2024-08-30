<?php
/**
* Admin   :: Ban Request Form
* Created :: 2024-07-08
* Modify  :: 2024-08-30
* Version :: 2
*
* @return Widget
*
* @usage admin/ban/request
*/

class AdminBanRequest extends Page {
	var $ip;
	var $host;

	function __construct() {
		parent::__construct([
			'ip' => post('ip'),
			'host' => post('host'),
		]);
	}

	function build() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'Ban Request',
				'boxHeader' => true,
				'trailing' => new Button([
					'type' => 'link',
					'href' => url('admin/ban..list'),
					'text' => 'List',
				]), // Button
			]), // AppBar
			'body' => new Row([
				'children' => [
					new Form([
						'class' => 'sg-form',
						'action' => url('api/admin/ban/save'),
						'rel' => 'none',
						'done' => 'close',
						'children' => [
							'ip' => [
								'type' => 'text',
								'label' => 'IP (php regex match)',
								'class' => '-fill',
								'value' => $this->ip,
							],
							'host' => [
								'type' => 'text',
								'label' => 'Host (php regex match)',
								'class' => '-fill',
								'value' => $this->host,
							],
							'time' => [
								'type' => 'select',
								'label' => 'Time',
								'options' => [
									30 => '30 minute',
									60 => '1 Hour',
									2*60 => '2 Hours',
									1*24*60 => '1 Day',
									2*24*60 => '2 Days',
									1*7*24*60 => '1 Week',
									1*31*24*60 => '1 Month',
									100*365*24*60 => 'Forver'
								],
							],
							'save' => [
								'type' => 'button',
								'value' => '<i class="icon -material">done_all</i><span>Save</span>',
								'container' => ['class' => '-sg-text-right']
							]
						]
					]),
					new Container([
						'children' => [
							new ListTile(['title' => 'Ban List']),
							new Table([
								'thead' => ['IP/Host', 'Start Time', 'End Time', ''],
								'children' => array_map(
									function($ban, $key) {
										return [
											$ban->ip.$ban->host,
											$ban->start,
											$ban->end,
											new Nav([
												'children' => [
													new Button([
														// 'href' => url(),
														'class' => '-disabled',
														'icon' => new Icon('edit'),
													]),
													new Button([
														'class' => 'sg-action',
														'href' => url('api/admin/ban/remove', ['id' => $key]),
														'icon' => new Icon('cancel'),
														'rel' => 'none',
														'done' => 'remove: parent tr',
														'attribute' => [
															'data-title' => 'ลบรายการ',
															'data-confirm' => 'ลบรายการ กรุณายืนยัน?'
														]
													])
												], // children
											]), // Nav
										];
									},
									(Array) cfg('ban.ip'),
									array_keys((Array) cfg('ban.ip'))
								)
							]), // Table
						], // children
					]), // Container
				], // children
			]), // Row
		]);
	}
}
?>