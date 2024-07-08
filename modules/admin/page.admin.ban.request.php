<?php
/**
* Module  :: Description
* Created :: 2024-07-08
* Modify  :: 2024-07-08
* Version :: 1
*
* @param String $arg1
* @return Widget
*
* @usage module/{id}/method
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
			'body' => new Widget([
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
				], // children
			]), // Widget
		]);
	}
}
?>