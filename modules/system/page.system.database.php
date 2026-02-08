<?php
/**
 * System  :: Database Field In All Table Page
 * Author  :: Little Bear<softganz@gmail.com>
 * Created :: 2026-02-08
 * Modify  :: 2026-02-08
 * Version :: 1
 *
 * @return Widget
 *
 * @usage system/database
 */

use Softganz\DB;

class SystemDatabase extends Page {
	var $nodeId;
	var $fieldName = 'tpid';
	var $fieldValue;

	function __construct() {
		parent::__construct([
			'fieldName' => SG\getFirst(strtolower(Request::all('field'))),
			'fieldValue' => SG\getFirst(Request::all('value'))
		]);
	}

	function rightToBuild() {
		if (empty($this->fieldName)) return error(_HTTP_ERROR_BAD_REQUEST, 'No Field Name');
		if ($this->fieldValue === '') return error(_HTTP_ERROR_BAD_REQUEST, 'No Field Value');
		if (!is_admin()) return error(_HTTP_ERROR_FORBIDDEN, 'Access Denied');

		return true;
	}

	#[\Override]
	function build() {
		$allTables = $this->getTables();

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'System Data In All Table',
				'leading' => new Icon('database'),
			]), // AppBar
			'body' => new Widget([
				'children' => [
					// new DebugMsg(R(), 'R()'),
					new Widget([
						'children' => array_map(
							function($table) {
								$columns = (Array) $this->getColouns($table->name)->items;
								$hasRecord = isset($columns[$this->fieldName]);
								return new Card([
									'children' => [
										new Header([
											'crossAxisAlignment' => 'center',
											'title' => $table->name,
											'leading' => new Icon('database'),
											'trailing' => new Row([
												'style' => 'gap: 16px;',
												'children' => [
													$hasRecord ? new ExpandButton() : NULL,
													// new ExpandButton()
												], // children
											]), // Row
										]), // Header
										$hasRecord ? $this->showDatas($table->name) : NULL,
										// $hasRecord ? new DebugMsg($columns, '$columns') : NULL,
									], // children
								]);
							},
							$allTables->items
						)
					]),
					// new DebugMsg($allTables, '$allTables'),
					$this->topic(),
				], // children
			]), // Widget
		]);
	}

	private function getTables() {
		return DB::select([
			'SELECT `table_name` AS `name`
			FROM information_schema.tables
			WHERE table_schema = :dbname
			AND table_type = "BASE TABLE"
			ORDER BY `name` ASC',
			'var' => [':dbname' => R()->myDb->db]
		]);
		return DB::select([
			'SHOW FULL TABLES',
			'var' => [
			]
		]);
	}

	private function getColouns($tableName) {
		try {
			$columns = DB::select([
				'SHOW COLUMNS FROM $tableName$;',
				'var' => ['$tableName$' => $tableName],
				'options' => ['key' => 'Field'],
				// 'onComplete' => function($data) {
				// 	debugMsg($data, '$data');
				// }
			]);
			$columns->items = array_change_key_case($columns->items, CASE_LOWER);
			return $columns;
		} catch (Exception $exception) {
			return [];
		}
	}

	private function showDatas($tableName) {
		try {
			$datas = DB::select([
				'SELECT *
				-- , \'<a href="'.Url::link('system/database/', ['field' => $this->fieldName, 'table' => $tableName]).'">x</a>\' AS `showDeleteButton`
				FROM $TABLE$ WHERE `$FIELDNAME$` = :fieldValue ORDER BY `$FIELDNAME$` ASC',
				'var' => [
					'$TABLE$' => $tableName,
					'$FIELDNAME$' => $this->fieldName,
					':fieldValue' => $this->fieldValue,
				]
			]);
		} catch (Exception $exception) {
			return new DebugMsg($exception->getMessage().' : '.R('query'));
		}

		return new ScrollView([
			'children' => [
				new Container([
					'class' => '-sg-paddingnorm',
					'child' => 'Found <b>'.$datas->count.' records.'
				]),
				new Table([
					'class' => '-data-list',
					'thead' => array_keys((Array) $datas->items[0]),
					'children' => $datas->items
				]),
				// new DebugMsg(R('query')),
				// new DebugMsg($data, '$data')
			], // children
		]);
	}

	private function topic() {
		
	}
}
?>