<?php
/**
 * DB       :: Database Management
 * Author   :: Little Bear<softganz@gmail.com>
 * Created  :: 2023-07-28
 * Modified :: 2026-11-04
 * Version  :: 61
 *
 * @param array $args
 * @return object
 *
 * @uses new Softganz\DB([])
 * @uses DB::select([query, where, var, options])
 * @uses DB::query([query, where, var, options])
 */

namespace Softganz;

use AllowDynamicProperties;

#[AllowDynamicProperties]
class DataModel {
	function __construct(array $args) {
		foreach ($args as $key => $value) {
			$this->{$key} = $value;
		}
	}
}

#[AllowDynamicProperties]
class SetDataModel extends DataModel {
	function __construct(array|string $args) {
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
	function __construct(object $args) {
		$this->args = $args;
	}
}

#[AllowDynamicProperties]
class JsonArrayDataModel extends DataModel {
	function __construct(array $args) {
		$this->args = $args;
	}
}

/**
 * For limit = 1
 * @return array of fields
 *
 * For limit != 1
 * @return int count
 * @return int foundRows: select with SQL_CALC_FOUND_ROWS
 * @return array items
 * @return object DB
 */
#[AllowDynamicProperties]
class DbSelect {
	private object $result;
	private object $DB;

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
	public function valueOf(string $field) {
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

	public function setDB(object $db) {
		$this->DB = $db;
	}
	public function setResult(object $result) {
		$this->result = $result;
	}
}

class DbQuery {
	var ?string $query;
	private object $result;
	private object $DB;
	private ?int $insertId;

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
	private string $state;
	private string $query;

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
	private DbEngineInterface $engine;

	function __construct($args = null) {
		$this->engine = self::createEngine((array) $args, $this);
	}

	// Select engine by connection type, default is MariaDB/MySQL
	private static function createEngine(
		array $args,
		DB $owner
	): DbEngineInterface {
		// Engine from connection type, or from uri scheme (mysql://, progress://)
		$connection = is_array($args) ? $args['connection'] ?? [] : [];
		$type = isset($connection['type'])
			? strtolower((string) $connection['type'])
			: (is_string($connection['uri'] ?? null)
				? strtolower((string) parse_url($connection['uri'], PHP_URL_SCHEME))
				: 'mysql');

		// Engine class is loaded by cfg('db'), only the matching engine is available
		$engineClass = match ($type) {
			'mysql', 'mariadb' => MariaDbEngine::class,
			'progress', 'odbc' => ProgressDbEngine::class,
			default => throw new \InvalidArgumentException(
				'Unsupported DB engine: ' . $type
			)
		};
		if (!class_exists($engineClass, false)) {
			throw new \InvalidArgumentException(
				'DB engine ' . $type . ' is not loaded, check cfg(\'db\')'
			);
		}
		return new $engineClass($args, $owner);
	}

	// Call by static method
	// Accepts array form: DB::select([query, '%WHERE%' => ..., 'var' => ..., 'options' => ...])
	// or named form: DB::select(query: '...', where: [...], var: [...], options: [...], debug: true)
	public static function select(
		array|string $args = [],
		?string $query = null,
		?array $where = null,
		array|object|null $var = null,
		?array $options = null,
		?bool $debug = null,
		?bool $multipleQuery = null,
		?string $key = null,
		?string $value = null,
		?string $group = null,
		?string $sum = null,
		?array $jsonDecode = null,
		?bool $history = null,
		?bool $log = null,
		?bool $showResult = null
	) {
		$args = self::buildArgs(
			$args,
			$query,
			$where,
			$var,
			$options,
			$debug,
			$multipleQuery,
			$key,
			$value,
			$group,
			$sum,
			$jsonDecode,
			$history,
			$log,
			$showResult
		);

		$db = new DB($args);
		return $db->engine->select(
			$args,
			$db,
			$db->engine->historyEnabled() ? get_caller(__FUNCTION__)['from'] : null
		);
	}

	// Accepts the same arguments as select()
	public static function query(
		array|string $args = [],
		?string $query = null,
		?array $where = null,
		array|object|null $var = null,
		?array $options = null,
		?bool $debug = null,
		?bool $multipleQuery = null,
		?bool $history = null,
		?bool $log = null
	) {
		$args = self::buildArgs(
			args: $args,
			query: $query,
			where: $where,
			var: $var,
			options: $options,
			debug: $debug,
			multipleQuery: $multipleQuery,
			history: $history,
			log: $log
		);

		$db = new DB($args);
		return $db->engine->query(
			$db,
			$db->engine->historyEnabled() ? get_caller(__FUNCTION__)['from'] : null
		);
	}

	// Merge positional/named arguments into the statement args array
	private static function buildArgs(
		array|string $args,
		?string $query,
		?array $where,
		array|object|null $var,
		?array $options,
		?bool $debug,
		?bool $multipleQuery,
		?string $key = null,
		?string $value = null,
		?string $group = null,
		?string $sum = null,
		?array $jsonDecode = null,
		?bool $history = null,
		?bool $log = null,
		?bool $showResult = null
	): array {
		if (is_string($args)) {
			$args = [$args];
		}
		if ($query !== null) {
			$args[0] = $query;
		}
		if ($where !== null) {
			$args['%WHERE%'] = $where;
		}
		if ($var !== null) {
			$args['var'] = $var;
		}
		if ($multipleQuery !== null || isset($args['multipleQuery'])) {
			$args['multipleQuery'] =
				(bool) $multipleQuery || (bool) ($args['multipleQuery'] ?? false);
		}

		// Named options override the options array
		$namedOptions = array_filter(
			[
				'key' => $key,
				'value' => $value,
				'group' => $group,
				'sum' => $sum,
				'jsonDecode' => $jsonDecode,
				'history' => $history,
				'log' => $log,
				'showResult' => $showResult,
				'debug' => $debug
			],
			fn($item) => $item !== null
		);
		if ($namedOptions) {
			$options = array_replace($options ?? [], $namedOptions);
		}
		if ($options !== null) {
			$args['options'] = $options;
		}

		return $args;
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
