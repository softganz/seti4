<?php
/**
* Admin   :: Check Invalid Charactor In Username
* Created :: 2022-10-01
* Modify  :: 2024-08-19
* Version :: 2
*
* @return Widget
*
* @usage admin/user/check/username
*/

class AdminUserCheckUsername extends Page {

	function build() {
		$invalidUsername = mydb::select(
			'SELECT *
			FROM `sgz_users`
			WHERE `username` REGEXP "[\'\/\`\!\@\#\$\%\^\&\*\(\)\=\{\}\[\]\|\;\:\"\<\>\,\.\?]"
			ORDER BY `username` ASC'
		);

		return new Scaffold([
			'appBar' => new AdminAppBarWidget([
				'title' => 'Check Invalid Username'
			]), // AdminAppBarWidget
			'body' => new Table([
				'thead' => [
					'id -center' => 'ID',
					'Username',
					'Name',
					'E-Mail',
					'datein -date' => 'Date Register',
				],
				'children' => array_map(
					function($item) {
						return [
							$item->uid,
							$item->username,
							$item->name,
							$item->email,
							$item->datein,
						];
					},
					$invalidUsername->items
				), // children
			]), // Widget
		]);
	}
}
?>