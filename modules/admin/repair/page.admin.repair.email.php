<?php
/**
* Admin   :: Repair Email Page
* Created :: 2025-05-22
* Modify  :: 2025-05-22
* Version :: 2
*
* @return Widget
*
* @usage admin/repair/email
*/

use Softganz\DB;

class AdminRepairEmail extends Page {

	function __construct($args = NULL) {
		parent::__construct([]);
	}

	function rightToBuild() {
		return true;
	}

	#[\Override]
	function build() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'Repair Invalid Email',
			]), // AppBar
			'sideBar' => new SideBar([
				'style' => 'width: 200px',
				'children' => [
					new Button([
						'class' => 'sg-action',
						'href' => url('admin/repair/email..char'),
						'text' => 'Invalid Character',
						'rel' => '#main',
					]), // Button
					new Button([
						'class' => 'sg-action',
						'href' => url('admin/repair/email..no.at.sign'),
						'text' => 'No @ sign',
						'rel' => '#main',
					]), // Button
					new Button([
						'class' => 'sg-action',
						'href' => url('admin/repair/email..fix.empty'),
						'text' => 'Fix empty email',
						'rel' => '#main',
					]), // Button
				],
			]), // SideBar
			'body' => new Widget([
				'children' => [], // children
			]), // Widget
		]);
	}

	function char() {
		$emails = DB::select([
			'SELECT
			`uid`, `username`, `name`, `email`, LEFT(`email`,1) `first`
			FROM `sgz_users`
			WHERE `email` IS NOT NULL
			HAVING `first` NOT BETWEEN "0" AND "z"
			ORDER BY `first` ASC',
		]);
		
		// debugMsg($emails, '$emails');

		return new Table([
			'thead' => (Array) array_keys((Array) $emails->items[0]),
			'children' => $emails->items
		]);
	}

	function noAtSign() {
		$emails = DB::select([
			'SELECT
			`uid`, `username`, `name`, `email`
			FROM `sgz_users`
			WHERE `email` IS NOT NULL AND `email` NOT LIKE "%@%"
			ORDER BY `uid` ASC',
		]);
		
		// debugMsg($emails, '$emails');

		return new Table([
			'thead' => (Array) array_keys((Array) $emails->items[0]),
			'children' => $emails->items
		]);
	}

	function fixEmpty() {
		if (!SG\confirm()) return new Button([
			'type' => 'primary',
			'class' => 'sg-action',
			'href' => url('admin/repair/email..fix.empty'),
			'text' => 'Confirm to fix empty email',
			'rel' => '#main',
			'attribute' => [
				'data-title' => 'Confirm to fix empty email',
				'data-confirm' => 'Are you sure to fix empty email?',
			]
		]);

		DB::query([
			'UPDATE `sgz_users` SET `email` = NULL WHERE `email` = ""',
		]);

		return mydb()->_query;
	}

	// SELECT *,LEFT(`email`,1) `first` FROM `sgz_users` WHERE `email` IS NOT NULL HAVING `first` NOT BETWEEN "0" AND "z" ORDER BY `first` ASC;
// SELECT *,LEFT(`email`,1) `first` FROM `sgz_users` WHERE `email` IS NOT NULL AND `email` NOT LIKE "%@%" ORDER BY `first` ASC LIMIT 10000;


// UPDATE `sgz_users` SET `email` = NULL WHERE `email` IS NOT NULL AND `email` NOT LIKE "%@%";

}
?>