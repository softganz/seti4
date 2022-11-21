<?php
/**
* Admin : User Menu
* Created 2016-11-08
* Modify  2022-03-31
*
* @return Widget
*
* @usage new AdminMenuUserWidget()
*/

class AdminMenuUserWidget extends Widget {
	function build() {
		return new Column([
			'children' => [
				new Card([
					'children' => [
						new ListTile([
							'crossAxisAlignment' => 'start',
							'title' => 'Access control',
							'leading' => new Icon(''),
							'trailing' => new Button([
								'type' => 'normal',
								'href' => url('admin/user/access'),
								'text' => 'Access control',
							]),
							'subtitle' => 'User can access each of module.',
						]),
					], // children
				]), // Card

				new Card([
					'children' => [
						new ListTile([
							'crossAxisAlignment' => 'start',
							'title' => 'Access rules',
							'leading' => new Icon(''),
							'trailing' => new Button([
								'type' => 'normal',
								'href' => url('admin/user/rules'),
								'text' => 'Access rules',
							]),
							'subtitle' => 'Rules.',
						]),
					], // children
				]), // Card

				new Card([
					'children' => [
						new ListTile([
							'crossAxisAlignment' => 'start',
							'title' => 'Create users',
							'leading' => new Icon(''),
							'trailing' => new Button([
								'type' => 'normal',
								'href' => url('admin/user/create'),
								'text' => 'Create users',
							]),
							'subtitle' => 'Create many users in one click.',
						]),
					], // children
				]), // Card

				new Card([
					'children' => [
						new ListTile([
							'crossAxisAlignment' => 'start',
							'title' => 'Roles',
							'leading' => new Icon(''),
							'trailing' => new Button([
								'type' => 'normal',
								'href' => url('admin/user/roles'),
								'text' => 'Roles',
							]),
							'subtitle' => 'Roles for each user group.',
						]),
					], // children
				]), // Card

				new Card([
					'children' => [
						new ListTile([
							'crossAxisAlignment' => 'start',
							'title' => 'Users',
							'leading' => new Icon(''),
							'trailing' => new Button([
								'type' => 'normal',
								'href' => url('admin/user/list'),
								'text' => 'Users',
							]),
							'subtitle' => 'All user listing.',
						]),
					], // children
				]), // Card

				new Card([
					'children' => [
						new ListTile([
							'crossAxisAlignment' => 'start',
							'title' => 'User settings',
							'leading' => new Icon(''),
							'trailing' => new Button([
								'type' => 'normal',
								'href' => url('admin/user/setting'),
								'text' => 'User settings',
							]),
							'subtitle' => 'Setting for user.',
						]),
					], // children
				]), // Card
			], // children
		]);
	}
}
?>