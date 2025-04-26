<?php
/**
* Admin  :: Database Table
* Created :: 2025-04-36
* Modify  :: 2025-04-26
* Version :: 1
* 
* @return Widget
* 
* @usage admin/database/table
*/

use Softganz\DB;

class AdminDatabaseTable extends Page {
	function __construct() {
		parent::__construct();
	}

	function build() {
		$tables = DB::select([
			'SELECT * from `INFORMATION_SCHEMA`.`TABLES`  
			WHERE `TABLE_TYPE` = "base table"
			ORDER BY `TABLE_SCHEMA`, `TABLE_NAME`'
		]);
		// debugMsg($tables, '$tables');

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'Database Tables',
			]),
			'body' => new ScrollView([
				'child' => new Table([
					'class' => '-nowrap',
					'thead' => array_keys((Array) $tables->items[0]),
					'children' => $tables->items,
				]), // Table
			]), // ScrollView
		]);
	}
}
?>