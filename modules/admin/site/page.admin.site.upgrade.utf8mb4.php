<?php
/**
 * Admin    :: Page
 * Author   :: Little Bear<softganz@gmail.com>
 * Created  :: 2026-08-21
 * Modified :: 2026-08-21
 * Version  :: 1
 *
 * @param String $args
 * @return Widget
 *
 * @uses admin/site/upgrade/utf8mb4
 */

use Softganz\DB;

class AdminSiteUpgradeUtf8mb4 extends Page {
	var $args;

	function __construct($args = NULL) {
		parent::__construct([
			'args' => $args
		]);
	}

	/**
	 * Right to build
	 *
	 * @return object|boolean
	 */
	function rightToBuild(): object|bool {
		return true;
	}

	/**
	 * Build page
	 *
	 * return object
	 */
	#[\Override]
	function build(): object {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'Convert Table Charactor Set To UTF8MB4',
				'leading' => new Icon('swap_horizontal_circle')
			]), // AppBar
			'body' => new Widget([
				'children' => array_map(
					function($table) {
						$tableName = $table[0]->TABLE_NAME;

						foreach ($table as $key => $value) {
							$table[$key]->bin = new Button([
								'type' => 'link',
								'class' => 'sg-action',
								'href' => Url::link('admin/site/upgrade/utf8mb4..convert.field', ['table' => $tableName, 'field' => $value->COLUMN_NAME, 'type' => 'utf8mb4_bin']),
								'text' => 'BIN',
								'icon' => new Icon('turn_slight_left'),
								'rel' => 'none',
								'done' => 'load',
								'data-title' => 'Convert field',
								'data-confirm' => 'Convert this field from <b>' . $value->COLLATION_NAME . '</b> to <b>utf8mb4_bin</b>'
							]);
							$table[$key]->ci = new Button([
								'type' => 'link',
								'class' => 'sg-action',
								'href' => Url::link('admin/site/upgrade/utf8mb4..convert.field', ['table' => $tableName, 'field' => $value->COLUMN_NAME, 'type' => 'utf8mb4_unicode_ci']),
								'text' => 'CI',
								'icon' => new Icon('turn_slight_right'),
								'rel' => 'none',
								'done' => 'load',
								'data-title' => 'Convert field',
								'data-confirm' => 'Convert this field from <b>' . $value->COLLATION_NAME . '</b> to <b>utf8mb4_unicode_ci</b>'
							]);
						}
						// debugMsg($table, '$table');
						return new Card([
							'children' => [
								new Header([
									'crossAxisAlignment' => 'center',
									'title' => $tableName,
									'leading' => new Icon('table'),
									'trailing' => new Button([
										'type' => 'primary',
										'class' => 'sg-action',
										'href' => Url::link('admin/site/upgrade/utf8mb4..convert.table', ['table' => $tableName]),
										'icon' => new Icon('table_convert'),
										'text' => 'Convert All Field To utf8mb4',
										'rel' => 'notify',
										'done' => 'load',
										'data-title' => 'Convert to utf8mb4_unicode_ci',
										'data-confirm' => 'Please confirm?'
									])
								]),
								new Table([
								'thead' => ['Table', 'Column', 'Type', 'Length','Charector Set', 'Collection Name', 'Type', 'Check', '', ''],
								'children' => $table,
							])
							]
						]);
					},
					$this->getAllTable()->items
				),
			]), // Widget
		]);
	}

	/**
	 * เปลี่ยนทั้งตาราง และเปลี่ยนคอลัมน์ที่เป็นข้อความทั้งหมดในตารางนั้นอัตโนมัติ
	 *
	 * @return object
	 */
	function convertTable(): object {
		$tableName = Request::all('table');

		if (empty($tableName)) return apiError(_HTTP_ERROR_BAD_REQUEST, 'ไม่ระบุชื่อตาราง');
		if (!\SG\confirm()) return apiError(_HTTP_ERROR_BAD_REQUEST, 'ไม่ยืนยัน');

		DB::query([
			'ALTER TABLE :`tableName` 
			CONVERT TO CHARACTER SET utf8mb4 
			COLLATE utf8mb4_unicode_ci',
			'var' => [
				':`tableName`' => $tableName
			]
		]);

		return apiSuccess('Convert to utf8mb4_unicode_ci completed.');
	}

	/**
	 * เปลี่ยนทั้งตาราง และเปลี่ยนคอลัมน์ที่เป็นข้อความทั้งหมดในตารางนั้นอัตโนมัติ
	 *
	 * @return object
	 */
	function convertField(): object {
		$tableName = Request::all('table');
		$fieldName = Request::all('field');
		$typeName = Request::all('type');

		if (empty($tableName)) return apiError(_HTTP_ERROR_BAD_REQUEST, 'ไม่ระบุชื่อตาราง');
		if (empty($fieldName)) return apiError(_HTTP_ERROR_BAD_REQUEST, 'ไม่ระบุชื่อฟิลด์');
		if (empty($typeName)) return apiError(_HTTP_ERROR_BAD_REQUEST, 'ไม่ระบุชื่อชนิดข้อมูล');
		if (!\SG\confirm()) return apiError(_HTTP_ERROR_BAD_REQUEST, 'ไม่ยืนยัน');

		// ดึง COLUMN_TYPE เดิม เช่น varchar(255), text, mediumtext ฯลฯ เพื่อคงชนิดข้อมูลเดิมไว้
		$column = DB::select([
			'SELECT COLUMN_TYPE
			 FROM INFORMATION_SCHEMA.COLUMNS
			 WHERE TABLE_SCHEMA = :database_name
			   AND TABLE_NAME = :tableName
			   AND COLUMN_NAME = :fieldName
			 LIMIT 1',
			'var' => [
				':database_name' => R('DB')->connection()->database,
				':tableName' => $tableName,
				':fieldName' => $fieldName,
			]
		]);

		$columnType = $column->COLUMN_TYPE ?? 'TEXT';
		// debugMsg($columnType, '$columnType');

		DB::query([
			'ALTER TABLE :`tableName`
			MODIFY :`fieldName` $columnType$ CHARACTER SET utf8mb4 COLLATE $type$',
			'var' => [
				':`tableName`' => $tableName,
				':`fieldName`' => $fieldName,
				'$columnType$' => $columnType,
				'$type$' => $typeName
			]
		]);
		// debugMsg(R('query'));

		return apiSuccess('Convert to ' . $typeName.' completed.');
	}

	/**
	 * Get all table column
	 *
	 * @return object
	 */
	private function getAllTable(): object {
		return DB::select([
			'SELECT 
			COLUMNS.TABLE_NAME
			, COLUMNS.COLUMN_NAME
			, COLUMNS.DATA_TYPE
			, COLUMNS.CHARACTER_MAXIMUM_LENGTH AS `Length`
			, COLUMNS.CHARACTER_SET_NAME
			, COLUMNS.COLLATION_NAME
			, COLUMNS.COLUMN_TYPE
			, IF(CHECK_CONSTRAINTS.CHECK_CLAUSE REGEXP CONCAT("\\\\b", COLUMNS.COLUMN_NAME, "\\\\b"), CHECK_CONSTRAINTS.CHECK_CLAUSE, NULL) AS CHECK_CLAUSE
		FROM INFORMATION_SCHEMA.COLUMNS 
			LEFT JOIN INFORMATION_SCHEMA.CHECK_CONSTRAINTS 
				ON CHECK_CONSTRAINTS.CONSTRAINT_SCHEMA = COLUMNS.TABLE_SCHEMA
				AND CHECK_CONSTRAINTS.TABLE_NAME = COLUMNS.TABLE_NAME
		WHERE
			COLUMNS.TABLE_SCHEMA = :database_name
			AND COLUMNS.DATA_TYPE IN ("char", "varchar", "enum", "tinytext", "text", "mediumtext", "longtext")
		ORDER BY  COLUMNS.TABLE_NAME ASC',
		'var' => [
				':database_name' => R('DB')->connection()->database
			],
			'options' => ['group' => 'TABLE_NAME']
		]);
	}
}
?>