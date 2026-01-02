<?php
/**
 * Admin   :: Ban Management
 * Author  :: Little Bear<softganz@gmail.com>
 * Created :: 2024-07-08
 * Modify  :: 2026-01-02
 * Version :: 4
 *
 * @return Widget
 *
 * @usage admin/ban
 */

class AdminBan extends Page {
	var $ip;
	var $host;

	function __construct() {
		parent::__construct([
			'ip' => Request::all('ip'),
			'host' => Request::all('host'),
		]);
	}

	function rightToBuild() {return true;}

	function build() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'Ban Management',
			]), // AppBar
			'body' => new Widget([
				'children' => [
					new Row([
						'children' => [
							$this->form(),
							'row-expand' => $this->list(),
						]
					]),
				], // children
			]), // Widget
		]);
	}

	function list() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'Ban List',
				'boxHeader' => true,
			]),
			'body' => new Table([
				'class' => '-center',
				'thead' => ['IP/Host', 'Start Time', 'End Time', 'icons -i1' => ''],
				'children' => array_map(
					function($ban) {
						return [
							trim($ban->ip.' '.$ban->host),
							$ban->start,
							$ban->end,
							new Nav([
								'children' => [
									// new Button([
									// 	'type' => 'secondary',
									// 	// 'href' => url(),
									// 	'class' => '-disabled',
									// 	'icon' => new Icon('edit'),
									// ]),
									new Button([
										'type' => 'danger',
										'class' => 'sg-action',
										'href' => url('api/admin/ban/remove', ['id' => $ban->id]),
										'icon' => new Icon('clear'),
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
					(Array) BanModel::getList()->items
				)
			])
		]);
	}

	private function form() {
		return new Form([
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
					'class' => '-fill',
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
					'class' => '-primary -fill',
					'value' => '<i class="icon -material">done_all</i><span>Save</span>',
				]
			]
		]);
	}
}
?>