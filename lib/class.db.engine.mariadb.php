<?php
/**
 * MariaDbEngine :: Internal MariaDB/MySQL implementation used by DB
 *
 * Use DB for application code, this class is for DB internal use only.
 *
 * Author   :: Little Bear<softganz@gmail.com>
 * Created  :: 2026-11-03
 * Modified :: 2026-11-04
 * Version  :: 3
 */

declare(strict_types=1);

namespace Softganz;

class MariaDbEngine implements DbEngineInterface {
	private ?object $connection = null;
	private bool $status = false;
	private ?string $srcStmt = null;
	private ?string $stmt = null;
	private array $args = [];
	private array $error = [];
	private ?string $errorMsg = null;
	private ?\PDO $PDO = null;
	private array $errors = [];
	private array $queryItems = [];
	private bool $debug = false;
	private array $debugMessage = [];
	private array $var = [];
	private array $where = [];
	private ?object $options = null; // key, value, group, sum, jsonDecode, multiple, history, log, debug, showResult
	private bool $multipleQuery = false; // Use for multiple query in one statement, default is single query
	private ?string $callerFrom = null;
	private DB $owner; // DB facade, passed to onComplete callback

	public ?float $connectionTime = null;
	public int $count = 0;
	public array $items = [];

	function __construct(array $args, DB $owner) {
		$this->owner = $owner;
		$args = is_string($args) ? [0 => $args] : (array) $args;
		$this->multipleQuery = (bool) ($args['multipleQuery'] ?? false);

		// Connect to database
		if (isset($args['connection']) && $args['connection']) {
			$this->createDbConnection($args['connection']);
		} elseif (function_exists('R')) {
			$this->PDO = R()->DB->PDO();
			$this->status = (bool) $this->PDO;
		}

		if (!$this->status) {
			return;
		}

		$this->srcStmt = isset($args[0]) ? trim((string) $args[0]) : null;
		unset($args[0]);
		$this->args = $args;

		$this->setOptions((array) ($args['options'] ?? []));
	}

	// Called by DB::select
	function select(array $args, DB $db, ?string $callerFrom = null) {
		if (!$this->status || !$this->PDO || empty($this->srcStmt)) {
			throw new DbException(
				'Database is not connected or select statement is empty.',
				503,
				'',
				(string) $this->srcStmt
			);
		}

		$this->callerFrom = $callerFrom;
		$this->PDO->setAttribute(\PDO::ATTR_EMULATE_PREPARES, $this->multipleQuery);
		$queryResult = $this->selectResult();

		// Query error, return exception
		if ($queryResult instanceof DbException) {
			$errorMessage =
				$this->stmt
				. '; <span style="color:red;">-- ERROR :: '
				. ($this->errorMsg ?? 'Select data from database was error.')
				. '</font>';
			$this->setDebugMessage('PREPARE', $errorMessage);
			throw new DbException(
				'Select data from database was error.',
				503,
				$errorMessage,
				(string) $this->stmt
			);
		}

		if (preg_match('/(LIMIT[\s].*1|LIMIT[\s].*1;)$/i', $this->stmt)) {
			$result = new DbSelect(reset($this->items));
		} else {
			$result = new DbSelect([
				'count' => $this->count,
				'foundRows' => null,
				'items' => $this->items
			]);

			if (preg_match('/SQL_CALC_FOUND_ROWS/', $this->stmt)) {
				$result->foundRows = DB::select([
					'SELECT FOUND_ROWS() `totals` LIMIT 1',
					'options' => ['history' => false]
				])->totals;
			} else {
				unset($result->foundRows);
			}
		}

		$result->setDB($db);
		$result->setResult($queryResult);

		if (isset($args['onComplete']) && is_callable($args['onComplete'])) {
			$args['onComplete']($result);
		}

		if (isset($this->options->sum) && $this->options->sum) {
			$result->sum = $this->options->sum;
		}

		if ($this->options->showResult) {
			if (function_exists('debugMsg')) {
				debugMsg($result, 'DB::select');
			}
		}

		return $result;
	}

	// Called by DB::query
	function query(DB $db, ?string $callerFrom = null) {
		if (!$this->status || !$this->PDO) {
			throw new DbException(
				'Database is not connected.',
				503,
				'',
				(string) $this->srcStmt
			);
		}

		$this->callerFrom = $callerFrom;
		$result = $this->queryResult();

		if ($result instanceof DbException) {
			$errorMessage =
				$this->stmt
				. '; <span style="color:red;">-- ERROR :: '
				. ($this->errorMsg ?? 'Update data to database was error.')
				. '</font>';

			$this->setDebugMessage('PREPARE', $errorMessage);

			throw new DbException(
				'Update data to database was error.',
				503,
				$errorMessage,
				(string) $this->stmt
			);
		}

		unset($this->items, $this->count);
		$this->PDO->setAttribute(\PDO::ATTR_EMULATE_PREPARES, $this->multipleQuery);

		return new DbQuery([
			'query' => $this->stmt,
			'result' => $result,
			'DB' => $db,
			'insertId' => $this->insertId()
		]);
	}

	// Whether query history is enabled (caller lookup is only needed then)
	function historyEnabled() {
		return $this->options->history !== false;
	}

	// Public method
	function prepare() {
		$this->stmt = $this->srcStmt;

		if (isset($this->args['var'])) {
			$this->setVariable($this->args['var']);
		}
		$this->setWhere();

		$this->replaceWhere();
		$this->replaceVariable();
		$this->replaceTable();

		$this->migrateStmtVersion();
	}

	function selectResult() {
		if (!$this->status || empty($this->srcStmt)) {
			return;
		}

		// Prepare statement
		$this->prepare();

		$this->setDebugMessage('SRC', $this->srcStmt);
		$this->setDebugMessage('PREPARE', $this->stmt);
		if (isset($this->args['var'])) {
			$this->setDebugMessage('VAR', $this->args['var']);
		}
		if (isset($this->args['where'])) {
			$this->setDebugMessage('WHERE', $this->args['where']);
		}
		$this->setDebugMessage('OPTIONS', $this->options);

		try {
			$start = microtime(true);
			$query = $this->PDO->query($this->stmt, \PDO::FETCH_ASSOC);
			if ($query === false) {
				throw new \PDOException(
					$this->PDO->errorInfo()[2] ?? 'Select query failed.'
				);
			}

			// Multiple statements: use the result set of the last statement
			if ($this->multipleQuery) {
				while ($query->nextRowset()) {
				}
			}

			// Select complete
			$end = microtime(true);
			$this->updateLastQueryStmt(
				'Select',
				$this->formatStmt([
					'rowCount' => $query->rowCount(),
					'time' => $end - $start
				])
			);

			$this->items = $this->fetchRow($query);

			$this->count = count($this->items);
		} catch (\PDOException $exception) {
			$queryError = $this->PDO->errorInfo();
			$errorCode = $queryError[1] ? $queryError[1] : $exception->getCode();
			$errorMsg = $exception->getMessage();
			$queryStmt = $this->formatStmt([
				'errorCode' => $queryError[1] ? $queryError[1] : $exception->getCode(),
				'errorMessage' => $exception->getMessage(),
				'SqlState' => $exception->getCode()
			]);

			$this->updateLastQueryStmt('Select', $queryStmt, $queryError);

			// Show debug message if debug mode is on, otherwise return exception
			if (
				isset($this->options->debug) &&
				$this->options->debug &&
				function_exists('debugMsg')
			) {
				debugMsg($this->debugMsg());
				return;
			}

			return new DbException(
				$errorMsg,
				$errorCode,
				(object) [
					'state' => $queryError[0],
					'query' => $queryStmt
				],
				$queryStmt
			);
		}

		// Show debug message if debug mode is on
		if (
			isset($this->options->debug) &&
			$this->options->debug &&
			function_exists('debugMsg')
		) {
			debugMsg($this->debugMsg());
		}

		return $query;
	}

	function queryResult() {
		if (!$this->status || empty($this->srcStmt)) {
			return;
		}

		// Prepare statement
		$this->prepare();

		$this->setDebugMessage('SRC', $this->srcStmt);
		$this->setDebugMessage('QUERY', $this->stmt);
		if (isset($this->args['var'])) {
			$this->setDebugMessage('VAR', $this->args['var']);
		}
		if (isset($this->args['where'])) {
			$this->setDebugMessage('WHERE', $this->args['where']);
		}
		$this->setDebugMessage('OPTIONS', $this->options);

		$start = microtime(true);

		try {
			$query = $this->PDO->query($this->stmt, \PDO::FETCH_ASSOC);
			if ($query === false) {
				throw new \PDOException(
					$this->PDO->errorInfo()[2] ?? 'Query failed.'
				);
			}
		} catch (\PDOException $exception) {
			$queryError = $this->PDO->errorInfo();
			$errorMsg = $exception->getMessage();
			$errorCode = $queryError[1] ? $queryError[1] : $exception->getCode();
			$queryStmt = $this->formatStmt([
				'errorCode' => $queryError[1] ? $queryError[1] : $exception->getCode(),
				'errorMessage' => $exception->getMessage(),
				'SqlState' => $exception->getCode()
			]);

			$this->updateLastQueryStmt('Query', $queryStmt, $queryError);

			// Show debug message if debug mode is on, otherwise return exception
			if (
				isset($this->options->debug) &&
				$this->options->debug &&
				function_exists('debugMsg')
			) {
				debugMsg($this->debugMsg());
				return;
			}

			return new DbException($errorMsg, $errorCode, $queryError[0], $queryStmt);
		}

		// Query complete
		$end = microtime(true);
		$this->updateLastQueryStmt(
			'Query',
			$this->formatStmt([
				'rowCount' => $query->rowCount(),
				'time' => $end - $start
			])
		);

		if (
			isset($this->args['onComplete']) &&
			is_callable($this->args['onComplete'])
		) {
			$this->args['onComplete']($this->owner);
		}
		if (
			isset($this->options->debug) &&
			$this->options->debug &&
			function_exists('debugMsg')
		) {
			debugMsg($this->debugMsg());
		}

		return $query;
	}

	function fetchRow(\PDOStatement $items): array {
		$result = [];
		foreach ($items as $key => $value) {
			$value = (object) $value;
			if (isset($this->options->jsonDecode) && $this->options->jsonDecode) {
				foreach ($this->options->jsonDecode as $jsonDecode) {
					$jsonDecodeResult = json_decode(
						(string) ($value->{$jsonDecode['field']} ?? '{}')
					);
					if ($jsonDecode['type'] === 'merge') {
						$foundField = false;
						$before = [];
						$after = [];
						foreach ($value as $itemKey => $itemValue) {
							if ($itemKey === $jsonDecode['field']) {
								$foundField = true;
								continue;
							}
							if (!$foundField) {
								$before[$itemKey] = $itemValue;
							} else {
								$after[$itemKey] = $itemValue;
							}
						}
						$value = (object) array_replace_recursive(
							(array) $before,
							(array) $jsonDecodeResult,
							(array) $after
						);
					} else {
						$value->{$jsonDecode['field']} = $jsonDecodeResult;
					}
				}
			}

			if (isset($this->options->group) && $this->options->group) {
				// Collect result using group of items and/or using key and value
				if (
					isset($this->options->key) &&
					$this->options->key &&
					isset($this->options->value) &&
					$this->options->value
				) {
					$result[$value->{$this->options->group}][
						$value->{$this->options->key}
					] = $value->{$this->options->value};
				} elseif (isset($this->options->key) && $this->options->key) {
					$result[$value->{$this->options->group}][
						$value->{$this->options->key}
					] = $value;
				} else {
					$result[$value->{$this->options->group}][] = $value;
				}
			} elseif (
				isset($this->options->key) &&
				isset($this->options->value) &&
				$this->options->key &&
				$this->options->value
			) {
				// Collect result using key and value
				$result[$value->{$this->options->key}] =
					$value->{$this->options->value};
			} elseif (isset($this->options->key) && $this->options->key) {
				// Collect result using key
				$result[$value->{$this->options->key}] = $value;
			} else {
				// Collect result by auto key
				$result[] = $value;
			}

			// Generate sum value of fields
			if (isset($this->options->sum) && $this->options->sum) {
				foreach ($this->options->sum as $keySum => $valueSum) {
					$this->options->sum->{$keySum} += $value->{$keySum} ?? 0;
				}
			}
		}
		return $result;
	}

	function insertId() {
		return intval($this->PDO->lastInsertId());
	}

	function args() {
		return $this->args;
	}

	function options() {
		return $this->options;
	}

	function PDO() {
		return $this->PDO;
	}

	// Create statement with message and error code
	private function formatStmt($addMessage = []) {
		$stmt = $this->stmt;
		if (isset($addMessage['errorMessage']) && $addMessage['errorMessage']) {
			$stmt .=
				';<br><span style="color:red;">-- ERROR #'
				. $addMessage['errorCode']
				. ' '
				. $addMessage['errorMessage']
				. '</span>';
		}

		if (isset($addMessage['rowCount'])) {
			$stmt .=
				'<br><span style="color:green">-- '
				. (isset($addMessage['rowCount'])
					? '<b>'
						. number_format($addMessage['rowCount'])
						. '</b> affected rows'
					: '')
				. (isset($addMessage['time'])
					? ' in <b>' . number_format($addMessage['time'] * 1000, 2) . '</b> ms'
					: '')
				. '</span>';
		}
		$stmt .=
			'<br><font color="gray">-- Call <b>DB</b> from '
			. $this->callerFrom
			. '</font>';
		return $stmt;
	}

	function sum(string $key) {
		if ($this->options->sum) {
			return $this->options->sum->{$key};
		}
	}

	function queryItems($stmt = null) {
		if ($stmt) {
			$this->queryItems[] = $stmt;
		}
		return $this->queryItems;
	}

	function debugMsg() {
		$ret = '';
		foreach ($this->debugMessage as $debugKey => $debugText) {
			$ret .= '<div>';
			if (!is_numeric($debugKey)) {
				$ret .= '<b><i>' . $debugKey . ' ::</i></b><br />';
			}
			if (is_string($debugText)) {
				$ret .= preg_match('/^SELECT|UPDATE|INSERT|DELETE|ALTER/i', $debugText)
					? '<pre>' . $debugText . '</pre>'
					: $debugText;
			} else {
				$ret .= class_exists('\DebugMsg')
					? new \DebugMsg($debugText, $debugKey)->build()
					: '<pre>' . print_r($debugText, true) . '</pre>';
			}
			$ret .= '</div>';
		}
		return $ret;
	}

	// Private method
	private function createDbConnection(array $connection) {
		if (is_string($connection['uri'] ?? null)) {
			$matched = preg_match(
				'/(mysql)\:\/\/([^:]*)\:([^@]*)\@([^\/]*)\/(.*)/i',
				$connection['uri'],
				$out
			);
			if ($matched !== 1) {
				throw new \Exception('+ขออภัย รูปแบบการเชื่อมต่อฐานข้อมูลไม่ถูกต้อง', 500);
			}
			$connection['type'] = $out[1];
			$connection['user'] = $out[2];
			$connection['password'] = $out[3];
			$connection['host'] = $out[4];
			$connection['database'] = $out[5];
		} elseif (is_array($connection['uri'])) {
			$connection['type'] = $connection['uri']['type'];
			$connection['user'] = $connection['uri']['user'];
			$connection['password'] = $connection['uri']['password'];
			$connection['host'] = $connection['uri']['host'];
			$connection['database'] = $connection['uri']['database'];
		}

		$this->connection = (object) $connection;
		$dsn =
			$connection['type']
			. ':host='
			. $connection['host']
			. ';dbname='
			. $connection['database']
			. ';charset='
			. $connection['characterSetName'];

		$start_time = microtime(true);

		try {
			$pdoOptions = [
				\PDO::ATTR_EMULATE_PREPARES => $this->multipleQuery, // turn off emulation mode for "real" prepared statements
				\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION, //turn on errors in the form of exceptions
				\PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC, //make the default fetch be an associative array
				\Pdo\Mysql::ATTR_FOUND_ROWS => true
			];
			$this->PDO = new \PDO(
				$dsn,
				$connection['user'],
				$connection['password'],
				$pdoOptions
			);
		} catch (\PDOException $e) {
			$this->errors[] = $this->errorMsg = $e->getMessage();
			throw new \Exception(
				'+ขออภัย มีปัญหาในการติดต่อกับฐานข้อมูล',
				$e->getCode()
			);
		}

		$end_time = microtime(true);
		$connection_time = $end_time - $start_time;

		$this->connectionTime = round($connection_time, 4);

		$this->status = true;

		// Disabled Strict mode
		if ($this->PDO->query('SET @@SESSION.sql_mode = ""') === false) {
			throw new \Exception(
				'+ขออภัย ไม่สามารถตั้งค่า SQL mode ได้',
				500
			);
		}
		// $this->PDO->query('SET GLOBAL slow_query_log=1;');
	}

	private function setOptions(array $options) {
		$this->options = (object) array_replace_recursive(
			[
				'log' => true,
				'history' => true,
				'key' => null,
				'value' => null,
				'group' => null,
				'multiple' => false,
				'showResult' => false,
				'debug' => false,
				'sum' => [],
				'jsonDecode' => []
			],
			(array) $options
		);

		if ($this->options->sum) {
			$sumFields = $this->options->sum;
			$this->options->sum = (object) [];
			foreach (explode(',', (string) $sumFields) as $value) {
				$this->options->sum->{$value} = 0;
			}
		}

		if ($this->options->multiple) {
			$this->PDO->setAttribute(\PDO::ATTR_EMULATE_PREPARES, true);
		}
	}

	private function setDebugMessage(?string $key, mixed $message): array {
		if ($key) {
			$this->debugMessage[$key] = $message;
		} else {
			$this->debugMessage[] = $message;
		}
		if ($this->debug) {
			if (function_exists('debugMsg')) {
				debugMsg($message);
			} else {
				echo '<div class="debug-msg">' . $message . '</div>';
			}
		}
		return $this->debugMessage;
	}

	private function setWhere() {
		// If %WHERE% exist in args, convert to where with key %WHERE% and value is array of where item
		foreach ($this->args as $argKey => $argValue) {
			if (preg_match('/^%WHERE.*%$/', (string) $argKey)) {
				$this->args['where'][$argKey] = $argValue;
				unset($this->args[$argKey]);
			}
		}

		if (empty($this->args['where'])) {
			return;
		}

		foreach ($this->args['where'] as $whereKey => $whereGroup) {
			$where = [];
			foreach ($whereGroup as $whereItem) {
				if (isset($whereItem[0]) && $whereItem[0]) {
					$where[] = $whereItem[0];
				}
				unset($whereItem[0]);
				if ($whereItem) {
					$this->setVariable($whereItem);
				}
			}
			$this->where[$whereKey] = empty($where)
				? ''
				: 'WHERE ' . implode(' AND ', $where);
		}
	}

	private function setVariable($variable = []) {
		if (empty($variable)) {
			return;
		}

		// Value of var is object, convert to array with : in front of key
		if (is_object($variable)) {
			$variable = $this->valueObjectConvert($variable);
		}

		foreach ($variable as $key => $value) {
			$this->setEachVariable($key, $value);
		}
	}

	private function setEachVariable(int|string $key, mixed $value): mixed {
		$key = (string) $key;

		if (is_null($value)) {
			$value = 'NULL';
		} elseif (
			is_object($value) &&
			get_class($value) === 'Softganz\JsonDataModel'
		) {
			$value = $this->jsonObjectString($value->args);
		} elseif (
			is_object($value) &&
			get_class($value) === 'Softganz\JsonArrayDataModel'
		) {
			$value = $this->jsonArrayString($value->args);
		} elseif (
			is_object($value) &&
			get_class($value) === 'Softganz\SetDataModel'
		) {
			$value = $this->valueOfSet($value);
		} elseif (preg_match('/^(\:JSON_OBJECT)(\:.*)/i', $key)) {
			$value = $this->jsonObjectString($value);
		} elseif (is_object($value)) {
			// If value is object, recursive all element with add : in front of key
			$this->setVariable($value);
			return null;
		} elseif (is_array($value)) {
			// If value is object, recursive all element without add : in front of key
			$this->setVariable($value);
			return null;
		} elseif (preg_match('/^(\:\`).*(\`)$/', $key)) {
			// If key leading with :` and end with ` then value is quote and remove ' and add ` at front and end
			$value = '`' . trim($this->quote($value), '\'') . '`';
		} elseif (preg_match('/^(\:\:).*(\:\:)$/', $key)) {
			// If key leading with :" and end with " then value is quote and remove ' and add ` at front and end
			$value = trim($this->quote($value), '\'');
		} elseif (preg_match('/^\$([a-zA-Z0-9_]*)\$$/', $key)) {
			// If key leading and ending with $ and function name format, Don't quote
			$value = $value;
		} elseif (preg_match('/^\$([a-zA-Z0-9_]*)/', $key)) {
			// If key leading with $ and follow by function name format, Quote value
			$value = $this->quote($value);
		} elseif (is_string($value) && preg_match('/^func\./i', $value)) {
			$value = substr($value, 5);
		} elseif (is_string($value)) {
			$value = $this->quote($value);
		} elseif (is_numeric($value)) {
			$value = $value;
		} elseif (is_bool($value)) {
			$value = $value ? 1 : 0;
		} else {
			$value = '""';
		}
		$this->var[$key] = $value;
		return $value;
	}

	private function replaceWhere() {
		if (empty($this->where)) {
			return;
		}

		$whereKey = array_keys($this->where);

		// Quote value keys before use preg_replace
		$whereKey = array_map(function ($v) {
			return '#' . preg_quote($v) . '#';
		}, $whereKey);

		// Replace value keys with $this->var
		$this->stmt = $this->checkPregResult(
			preg_replace($whereKey, $this->where, $this->stmt)
		);
	}

	private function replaceVariable() {
		if (empty($this->var)) {
			return;
		}

		$varKey = array_keys($this->var);

		// Quote value keys before use preg_replace
		$varKey = array_map(function ($v) {
			return '#' . preg_quote((string) $v) . '#';
		}, $varKey);

		// Replace value keys with $this->var
		$this->stmt = $this->checkPregResult(
			preg_replace($varKey, $this->var, $this->stmt)
		);
	}

	// Throw when a regex replace fails (preg functions return null on error)
	private function checkPregResult(?string $result): string {
		if ($result === null) {
			throw new \RuntimeException(
				'+ขออภัย ไม่สามารถประมวลผลคำสั่ง SQL ได้: ' . preg_last_error_msg()
			);
		}
		return $result;
	}

	private function migrateStmtVersion() {
		$this->jsonVersionConvert();
	}

	// Convert ->> to JSON_UNQUOTE(JSON_EXTRACT()) in mariadb to mySQL 5.7
	private function jsonVersionConvert() {
		if (preg_match('/\-\>\>/', $this->stmt, $out)) {
			// debugMsg('SRC STMT');
			// debugMsg($this->stmt);
			// debugMsg($out, '$out');
			// preg_match('/(`\w.*`) \-\>\> ("\$\.\w*\")/', $this->stmt, $out);
			// debugMsg($out, '$out');

			// Convert format from `table`.`field` ->> "$.jsonKey" to JSON_UNQUOTE(JSON_EXTRACT())
			$this->stmt = $this->checkPregResult(
				preg_replace(
					'/(`\w*`\.`\w*`) \-\>\> ("\$\.[\w\-\_\*\.0-9]*")/',
					'JSON_UNQUOTE(JSON_EXTRACT($1, $2))',
					$this->stmt
				)
			);
			# $this->stmt = preg_replace('/(`\w*`\.`\w*`) \-\>\> ("\$\.[\u0E01-\u0E5B]*")/', 'JSON_UNQUOTE(JSON_EXTRACT($1, $2))', $this->stmt);

			// $this->stmt = preg_replace('/(`dos`\.`information`) \-\>\> ("\$\.\w*\")/', 'JSON_UNQUOTE(JSON_EXTRACT($1, $2))', $this->stmt);
			// debugMsg('CONVERT STMT');
			// debugMsg($this->stmt);
		}
	}

	private function replaceTable() {
		// Replace %tablename% with db(%tablename%)
		$this->stmt = $this->checkPregResult(
			preg_replace_callback(
				'/\s\%([a-zA-Z_][a-zA-Z0-9_.]*)\%/i',
				function ($m) {
					return ' ' . $this->db($m[1]);
				},
				$this->stmt
			)
		);
	}

	private function valueOfSet(object $object): string {
		foreach ($object as $key => $value) {
			if (!is_numeric($value)) {
				$object->{$key} = $this->quote($value);
			}
		}
		return implode(' , ', (array) $object);
	}

	private function valueObjectConvert(object $object) {
		$result = [];
		foreach ($object as $key => $value) {
			if (preg_match('/^\$/', $key, $out)) {
				// Key leading with $ will not add : to front of it
				$result[$key] = $value;
			} else {
				$result[':' . $key] = $value;
			}
		}
		return $result;
	}

	private function quote(mixed $value): ?string {
		if (is_null($value)) {
			return null;
		}
		if (!is_scalar($value)) {
			throw new \InvalidArgumentException('Cannot quote non-scalar value in SQL.');
		}
		$quoted = $this->PDO->quote((string) $value);
		if ($quoted === false) {
			throw new \RuntimeException('+ขออภัย ไม่สามารถ quote ค่าในคำสั่ง SQL ได้');
		}
		return $quoted;
	}

	/**
	 * Set & get table with keyword
	 * @param string $key
	 * @param string $new_value
	 * @param string $prefix
	 * @param string $db
	 * @return string
	 */
	private function db(
		$key = null,
		$new_value = null,
		$prefix = null,
		$db = null
	) {
		static $items = [];
		static $src = [];

		if (empty($items) && function_exists('db')) {
			$items = db();
		}

		$tablePrefix = function_exists('cfg') ? cfg('db.prefix') : '';
		$ret = null;

		if (isset($key) && isset($new_value)) {
			$src[$key] = $new_value;
			$tablename = isset($db) ? '`' . $db . '`.' : ''; // Set database name
			if (preg_match('/\`([a-zA-Z0-9_].*)\`/', $new_value, $out)) {
				$tablename .= $new_value; // Use new_value on `table` or `db`.`table` format
			} else {
				$tablename .= '`' . $tablePrefix . $new_value . '`'; // Add prefix on table format
			}
			$items[$key] = $tablename;
			$ret = $items;
		} elseif (!isset($key) && isset($prefix)) {
			// Change all table items to new prefix value
			foreach ($src as $srcKey => $value) {
				$items[$srcKey] = $prefix . $value;
			}
			$ret = $items;
		} elseif (isset($key)) {
			// if key return value in condition
			// key format `table_name` return `table_name`
			// key format %table_name% return value in `$items[table_name]`
			// key format table_name and key in array items return items[table_name]
			// key format table_name and key not in items return db.prefix+table_name
			if (preg_match('/\`([a-zA-Z0-9_].*)\`/', $key, $out)) {
				$ret = $key;
			} elseif (preg_match('/\%([a-zA-Z0-9_].*)\%/', $key, $out)) {
				$ret = array_key_exists($out[1], $items)
					? $items[$out[1]]
					: '`' . $tablePrefix . $out[1] . '`';
			} else {
				$ret = array_key_exists($key, $items)
					? $items[$key]
					: '`' . $tablePrefix . $key . '`';
			}
		} else {
			$ret = $items;
		}
		return $ret;
	}

	private function jsonObjectString(
		mixed $value,
		int|string|null $key = null
	): string {
		// Encode to json string and decode to value becuase array has index as string will convert to object
		$encoded = json_encode($value);
		if ($encoded === false) {
			throw new \InvalidArgumentException('Cannot encode JSON value: ' . json_last_error_msg());
		}
		$value = json_decode($encoded);

		$jsonString = '';

		if ($key) {
			$jsonString .= '	"' . $key . '", ';
		}
		$jsonString .= is_object($value)
			? 'JSON_OBJECT(' . _NL
			: 'JSON_ARRAY(' . _NL;

		foreach ((array) $value as $jsonKey => $jsonValue) {
			if (is_array($jsonValue)) {
				$jsonString .=
					$this->jsonObjectString($jsonValue, $jsonKey) . ', ' . _NL;
			} elseif (is_object($jsonValue)) {
				$jsonString .= $this->jsonObjectString($jsonValue, $jsonKey) . ', ';
			} else {
				// Convert value to type
				if (
					is_null($jsonValue) ||
					(is_string($jsonValue) && trim($jsonValue) === '')
				) {
					$jsonValue = 'null';
				} elseif (is_numeric($jsonValue)) {
					$jsonValue = floatval($jsonValue);
				} else {
					$jsonValue = '"' . preg_replace('/[\"]/', '', (string) $jsonValue) . '"';
				}
				$jsonString .=
					(is_array($value) ? '	' : '	"' . $jsonKey . '", ')
					. $jsonValue
					. ', '
					. _NL;
			}
		}
		$jsonString = rtrim($jsonString, ' , ' . _NL);
		$jsonString .= _NL . ')';
		return $jsonString;
	}

	private function jsonArrayString(
		mixed $value,
		int|string|null $key = null
	): string {
		// debugMsg($value, 'JSON Value');
		$jsonString = '';
		if ($key) {
			$jsonString .= '"' . $key . '" , ';
		}
		$jsonString .= 'JSON_ARRAY(' . '"' . implode('","', (array) $value) . '"';
		// foreach ((array) $value as $jsonKey => $jsonValue) {
		// 	// debugMsg('KEY '.$jsonKey.' = '.$jsonValue);
		// 	$jsonString .= '"'.$jsonKey.'" , "'.preg_replace('/[\"]/', '', $jsonValue).'" ,';
		// 	// debugMsg($jsonString);
		// }
		// $jsonString = rtrim($jsonString, ' , ');
		$jsonString .= ')';
		return $jsonString;
	}

	private function updateLastQueryStmt(
		string $method,
		string $stmt,
		array $error = []
	): void {
		if (isset($this->options->history) && $this->options->history === false) {
			return;
		} // Do not save query history

		$this->setDebugMessage(null, $stmt);

		// Add message to error
		if ($error) {
			$this->errors[] = (object) [
				'code' => $error[1],
				'message' => $error[2],
				'query' => $stmt
			];
			$this->errorMsg = $error[2];

			// Save error to log
			if ($this->options->log && class_exists('\LogModel')) {
				\LogModel::save([
					'module' => 'DB',
					'keyword' => $method,
					'message' =>
						$stmt
						. '<pre>'
						. '<hr>GET: '
						. \json_encode($_GET, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
						. '<hr>POST: '
						. \json_encode($_POST, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
						. '<hr>FILES: '
						. \json_encode($_FILES, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
						. '</pre>'
				]);
			}
		}

		$this->queryItems($stmt);

		// Save query history
		if (function_exists('R')) {
			R('query', $stmt);
			R()->query_items[] = $stmt;
			R(
				'error',
				$error
					? (object) [
						'code' => $error[1],
						'message' => $error[2],
						'query' => $stmt
					]
					: ''
			);
		}
	}
}
