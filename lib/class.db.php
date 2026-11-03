<?php
/**
 * DB       :: Database Management
 * Author   :: Little Bear<softganz@gmail.com>
 * Created  :: 2023-07-28
 * Modified :: 2026-11-03
 * Version  :: 59
 *
 * @param array $args
 * @return object
 *
 * @uses new Softganz\DB([])
 * @uses DB::select([stmt, where, var, options])
 * @uses DB::query([stmt, where, var, options])
 */

namespace Softganz;

use AllowDynamicProperties;

#[AllowDynamicProperties]
class DataModel {
	function __construct($args) {
		foreach ($args as $key => $value) {
			$this->{$key} = $value;
		}
	}
}

#[AllowDynamicProperties]
class SetDataModel extends DataModel {
	function __construct($args) {
		if (is_string($args)) {
			$args = explode(',', $args);
		}
		foreach ($args as $key => $value) {
			$this->{$key} = $value;
		}
	}
}

#[AllowDynamicProperties]
class JsonDataModel extends DataModel {
	function __construct($args) {
		$this->args = $args;
	}
}

#[AllowDynamicProperties]
class JsonArrayDataModel extends DataModel {
	function __construct($args) {
		$this->args = $args;
	}
}

/**
 * For limit = 1
 * @return array of fields
 *
 * For limit != 1
 * @return count
 * @return foundRows: select with SQL_CALC_FOUND_ROWS
 * @return items
 * @return DB
 */
#[AllowDynamicProperties]
class DbSelect {
	private $result;
	private $DB;

	function __construct($args = []) {
		if ($args) {
			foreach ($args as $key => $value) {
				$this->{$key} = $value;
			}
		}
	}

	public function count() {
		return $this->count;
	}
	public function valueOf($field) {
		return isset($this->{$field}) ? $this->{$field} : null;
	}

	public function values($fields = []) {
		$values = (object) [];
		foreach ((array) $fields as $field) {
			$values->{$field} = isset($this->{$field}) ? $this->{$field} : null;
		}

		return $values;
	}

	public function DB() {
		return $this->DB;
	}
	public function result() {
		return $this->result;
	}
	public function rowCount() {
		return $this->result ? $this->result->rowCount() : null;
	}

	public function setDB($db) {
		$this->DB = $db;
	}
	public function setResult($result) {
		$this->result = $result;
	}
}

class DbQuery {
	var $query;
	private $result;
	private $DB;
	private $insertId;

	function __construct($args = []) {
		$this->query = $args['query'] ?? null;
		$this->result = $args['result'] ?? null;
		$this->DB = $args['DB'] ?? null;
		$this->insertId = $args['insertId'] ?? null;
	}

	public function DB() {
		return $this->DB;
	}
	public function result() {
		return $this->result;
	}
	public function insertId() {
		return $this->insertId;
	}
	public function rowCount() {
		return $this->result ? $this->result->rowCount() : null;
	}
}

// Add custom exception class
class DbException extends \Exception {
	var $error = false;
	private $state;
	private $query;

	public function __construct(
		$message = null,
		$code = null,
		$error = null,
		$query = null
	) {
		parent::__construct($message, (int) $code);
		$this->error = $error;
		$this->query = $query;
	}

	public function getQuery() {
		return $this->query;
	}

	public function getState() {
		return $this->state;
	}
}

/**
 * Array Arguments:
 * statment   :: string without key
 * connection :: string | array
 * var        :: object | array
 * where      :: array of array
 * options    :: array of debug, key, value, group
 */

#[AllowDynamicProperties]
class DB {
	private MariaDbEngine $engine;

	function __construct($args = null) {
		$this->engine = new MariaDbEngine($args, $this);
	}

	// Call by static method
	public static function select(array $args) {
		$db = new DB($args);
		return $db->engine->select(
			$args,
			$db,
			$db->engine->historyEnabled() ? get_caller(__FUNCTION__)['from'] : null
		);
	}

	public static function query(array $args) {
		$db = new DB($args);
		return $db->engine->query(
			$db,
			$db->engine->historyEnabled() ? get_caller(__FUNCTION__)['from'] : null
		);
	}

	public static function tableExists(string $table) {
		return DB::select([
			'SHOW TABLES LIKE :table',
			'var' => [
				':table' => preg_replace('/`/', '', db($table)) // Remove `
			]
		])->count;
	}

	/**
	 * Get Column Name of Table
	 *
	 * @param string $table
	 * @param string $colName
	 * @return array
	 */
	public static function columns($table, $colName = '', $dbname = '') {
		static $tables = null;
		if (!isset($tables[$table])) {
			$tables = [];
			if (substr($table, 0, 1) === '`') {
				$tablename = $table;
			} elseif (substr($table, 0, 1) === '%') {
				$tablename = $table;
			} else {
				$tablename = '%' . $table . '%';
			}

			foreach (
				DB::select([
					'SHOW COLUMNS FROM '
					. ($dbname ? '`' . $dbname . '`.' : '')
					. $tablename
				])->items
				as $rs
			) {
				foreach ($rs as $key => $value) {
					if (is_long($key)) {
						unset($rs[$key]);
					}
				}
				$tables[$table][$rs->Field] = $rs;
			}
			// debugMsg($tables[$table], '$tables[' .$table .']');
		}
		return $colName
			? (array_key_exists($colName, $tables[$table])
				? $tables[$table][$colName]
				: false)
			: $tables[$table];
	}

	// Public instance methods, implementation is in MariaDbEngine
	function PDO() {
		return $this->engine->PDO();
	}
}
