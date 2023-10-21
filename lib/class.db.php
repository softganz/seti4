<?php
/**
* DB      :: Database Management
* Created :: 2023-07-28
* Modify  :: 2023-10-21
* Version :: 4
*
* @param Array $args
* @return Object
*
* @usage new Softganz\DB([])
*/

namespace Softganz;

class DataModel {
	function __construct(Array $args) {
		foreach ($args as $key => $value) {
			$this->{$key} = $value;
		}
	}
}

class SetDataModel extends DataModel {
	function __construct(Array $args) {
		foreach ($args as $key => $value) $this->{$key} = $value;
	}
}

class JsonDataModel extends DataModel {
	function __construct(Array $args) {
		$this->args = $args;
	}
}

class JsonArrayDataModel extends DataModel {
	function __construct(Array $args) {
		$this->args = $args;
	}
}

class DbSelect {
	private $DB;

	function __construct($args = []) {
		foreach ($args as $key => $value) $this->{$key} = $value;
	}

	public function DB($db = NULL) {
		if (isset($db)) $this->DB = $db;
		return $this->DB;
	}
}

class DbQuery {
	private $DB;

	function __construct($args = []) {
		foreach ($args as $key => $value) $this->{$key} = $value;
	}

	public function DB($db = NULL) {
		if (isset($db)) $this->DB = $db;
		return $this->DB;
	}
}

/**
* Array Arguments:
* statment   :: String without key
* connection :: String | Array
* var        :: Object | Array
* where      :: Array of Array
* options    :: Array of debug, key, value, group
*/

class DB {
	private $status = false;
	private $srcStmt;
	private $stmt;
	private $args = [];
	private $errors = [];
	private $errorMsg;
	private $PDO;
	private $queryItems = [];
	private $debug = false;
	private $debugMessage = [];
	private $var = [];
	private $where = [];
	private $options;

	public $count = 0;
	public $items = [];

	function __construct($args = NULL) {
		$args = is_string($args) ? [0 => $args] : (Array) $args;

		if ($args['connection']) {
			$this->createDbConnection($args['connection']);
		} else if (function_exists('R')) {
			$this->PDO = R()->DB->PDO();
			$this->status = R()->DB->status;
		}

		if (!$this->status) return;
		$this->srcStmt = trim($args[0]);
		unset($args[0]);
		$this->args = $args;

		$this->setOptions();

		// if ($this->srcStmt) {
		// 	$this->setDebugMessage('SRC', $this->srcStmt);
		// 	// Start query
		// 	if (preg_match('/^SELECT/i', $this->srcStmt)) {
		// 		$this->selectResult();
		// 	} else {
		// 		$this->queryResult();
		// 	}
		// }
	}

	// Call by static method
	public static function select($args) {
		$select = new DB($args);
		$select->selectResult();

		if ($select->errorMsg) {
			$select->setDebugMessage('PREPARE', $select->stmt.'; <span style="color:red;">-- ERROR :: '.$select->errorMsg.'</font>');
			return new DbSelect(['errorMsg' => $select->errorMsg, 'DB' => $select]);
		}

		// debugMsg($select, '$select');
		if (preg_match('/(LIMIT[\s].*1|LIMIT[\s].*1;)$/i', $select->stmt)) {
			$result = new DbSelect(reset($select->items)); // + ['DB' => $select]]);
			$result->DB($select);
		} else {
			$result = new DbSelect(['count' => $select->count, 'items' => $select->items, 'DB' => $select]);
		}

		if ($select->options->sum) $result->sum = $select->options->sum;

		// debugMsg($select->errorMsg);
		// debugMsg($select->errors, 'error');
		return $result;
	}

	public static function query($args) {
		$queryResult = new DB($args);
		$queryResult->queryResult();
		unset($queryResult->items, $queryResult->count);
		$result = new DbQuery([
			'query' => mydb()->_query,
			'DB' => $queryResult
		]);

		return $result;
		return $queryResult;
	}



	// Public method
	function prepare() {
		$this->stmt = $this->srcStmt;

		$this->setVariable($this->args['var']);
		$this->setWhere();

		$this->replaceWhere();
		$this->replaceVariable();
		$this->replaceTable();

		$this->migrateStmtVersion();
	}

	function selectResult() {
		if (!$this->status || empty($this->srcStmt)) return;

		// Prepare statement
		$this->prepare();

		$this->setDebugMessage('SRC', $this->srcStmt);
		$this->setDebugMessage('PREPARE', $this->stmt);
		$this->setDebugMessage('VAR', $this->args['var']);

		$start = microtime(true);
		try {
			$query = $this->PDO->query($this->stmt, \PDO::FETCH_ASSOC);
		} catch (\PDOException $e) {
			$queryError = $this->PDO->errorInfo();
			$this->logError('mydb', 'select', $this->stmt, $queryError[1], $queryError[2]);
			$this->updateLastQueryStmt($this->stmt());

			return false;
		}

		// Select complete
		$end = microtime(true);
		$this->updateLastQueryStmt($this->stmt(['rowCount' => $query->rowCount(), 'time' => $end - $start]));
		$this->queryItems($this->stmt);
		if (function_exists('R')) R()->DB->queryItems($this->stmt);

		$this->items = $this->fetchRow($query);

		$this->count = count($this->items);

		if ($this->args['onComplete'] && is_callable($this->args['onComplete'])) $this->args['onComplete']($this);
	}

	function queryResult() {
		if (!$this->status || empty($this->srcStmt)) return;

		// Prepare statement
		$this->prepare();

		$this->setDebugMessage('SRC', $this->srcStmt);
		$this->setDebugMessage('QUERY', $this->stmt);
		$this->setDebugMessage('VAR', $this->args['var']);

		$start = microtime(true);
		try {
			$query = $this->PDO->query($this->stmt, \PDO::FETCH_ASSOC);
		} catch (\PDOException $e) {
			$queryError = $this->PDO->errorInfo();
			$this->logError('mydb', 'query', $this->stmt, $queryError[1], $queryError[2]);
			$this->updateLastQueryStmt($this->stmt(), $queryError);

			return false;
		}

		// Query complete
		$end = microtime(true);
		$this->updateLastQueryStmt($this->stmt(['rowCount' => $query->rowCount(), 'time' => $end - $start]));
		$this->queryItems($this->stmt);
		if (function_exists('R')) R()->DB->queryItems($this->stmt);

		if ($this->args['onComplete'] && is_callable($this->args['onComplete'])) $this->args['onComplete']($this);
	}

	function fetchRow($items) {
		$result = [];
		foreach ($items as $key => $value) {
			$value = (Object) $value;
			if ($this->options->group) {
				// Collect result using group of items and/or using key and value
				if ($this->options->key && $this->options->value) {
					$result[$value->{$this->options->group}][$value->{$this->options->key}] = $value->{$this->options->value};
				} else if ($this->options->key) {
					$result[$value->{$this->options->group}][$value->{$this->options->key}] = $value;
				} else {
					$result[$value->{$this->options->group}][] = $value;
				}
			} else if ($this->options->key && $this->options->value) {
				// Collect result using key and value
				$result[$value->{$this->options->key}] = $value->{$this->options->value};
			} else if ($this->options->key) {
				// Collect result using key
				$result[$value->{$this->options->key}] = $value;
			} else {
				// Collect result by auto key
				$result[] = $value;
			}

			// Generate sum value of fields
			if ($this->options->sum) {
				foreach ($this->options->sum as $keySum => $valueSum) {
					$this->options->sum->{$keySum} += $value->{$keySum};
				}
			}
		}
		return $result;
	}

	function lastInsertId() {return $this->PDO->lastInsertId();}

	function args() {return $this->args;}

	function options() {return $this->options;}

	function PDO() {return $this->PDO;}

	function stmt($addMessage = []) {
		$stmt = $this->stmt;
		if ($this->errorMsg) {
			$stmt .= '; <span style="color:red;">-- ERROR :: '.$this->errorMsg.'</span>';
		} else {
			$stmt .= '; <span style="color:green">-- '
				. (isset($addMessage['rowCount']) ? '<b>'.number_format($addMessage['rowCount']).'</b> affected rows' : '')
				. (isset($addMessage['time']) ? ' in <b>'.number_format($addMessage['time'] * 1000, 2).'</b> ms' : '')
				. '</span>';
		}
		$stmt .= '.';
		return $stmt;
	}

	function sum($key) {if ($this->options->sum) return $this->options->sum->{$key};}

	function errors() {return $this->errors;}

	function errorMsg() {return $this->errorMsg;}

	function queryItems($stmt = NULL) {
		if ($stmt) $this->queryItems[] = $stmt;
		return $this->queryItems;
	}

	function debugMsg() {
		$ret = '';
		foreach ($this->debugMessage as $debugKey => $debugText) {
			$ret .= '<div>';
			if (!is_numeric($debugKey)) $ret .= '<b><i>'.$debugKey.' ::</i></b><br />';
			if (is_string($debugText)) {
				$ret .= preg_match('/^SELECT|UPDATE|INSERT|DELETE|ALTER/i', $debugText) ? '<pre>'.$debugText.'</pre>' : $debugText;
			} else {
				$ret .= class_exists('\DebugMsg') ? (new \DebugMsg($debugText, $debugKey))->build() : '<pre>'.print_r($debugText, true).'</pre>';
			}
			$ret .= '</div>';
		}
		return $ret;
	}



	// Private method
	private function createDbConnection($connection) {
		// debugMsg('CREATE CONNECTION');
		// debugMsg($connection, '$connection');
		if (is_string($connection['uri'])) {
			preg_match('/(mysql)\:\/\/([^:]*)\:([^@]*)\@([^\/]*)\/(.*)/i', $connection['uri'], $out);
			$connection['type'] = $out[1];
			$connection['user'] = $out[2];
			$connection['password'] = $out[3];
			$connection['host'] = $out[4];
			$connection['database'] = $out[5];
		} else if (is_array($connection['uri'])) {
			$connection['type'] = $connection['uri']['type'];
			$connection['user'] = $connection['uri']['user'];
			$connection['password'] = $connection['uri']['password'];
			$connection['host'] = $connection['uri']['host'];
			$connection['database'] = $connection['uri']['database'];
		}

		// debugMsg($connection, '$connection');

		$dsn = $connection['type'].':dbname='.$connection['database'].';host='.$connection['host'];

		try {
			$pdoOptions = [
				\PDO::ATTR_EMULATE_PREPARES   => false, // turn off emulation mode for "real" prepared statements
				\PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION, //turn on errors in the form of exceptions
				\PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC, //make the default fetch be an associative array
				\PDO::MYSQL_ATTR_FOUND_ROWS		=> true
			];
			$this->PDO = new \PDO($dsn, $connection['user'], $connection['password'], $pdoOptions);
			// $this->PDO->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
		} catch (\PDOException $e) {
			$this->errors[] = $this->errorMsg = $e->getMessage();
			// debugMsg($e->getMessage());
			return false;
		}

		$this->status = true;

		// Disabled Strict mode
		$this->PDO->query('SET @@SESSION.sql_mode = ""');
		// $this->PDO->query('SET GLOBAL slow_query_log=1;');

		if ($connection['characterSetClient']) {
			$characterSetClientSql = 'SET character_set_client="'.$connection['characterSetClient'].'"';
			$this->PDO->query($characterSetClientSql);
			$this->queryItems($characterSetClientSql);

			$characterSetResultsSql = 'SET character_set_results="'.$connection['characterSetClient'].'"';
			$this->PDO->query($characterSetResultsSql);
			$this->queryItems($characterSetResultsSql);
		}

		if ($connection['characterSetConnection']) {
			$characterSetConnectionSql = 'SET character_set_connection="'.$connection['characterSetConnection'].'"';
			$this->PDO->query($characterSetConnectionSql);
			$this->queryItems($characterSetConnectionSql);
		}
	}

	private function setOptions() {
		if (isset($this->args['options'])) $this->options = (Object) $this->args['options'];
		if (isset($this->options->sum)) {
			$this->options->sum = (Object) [];
			foreach (explode(',', $this->args['options']['sum']) as $value) {
			 	$this->options->sum->{$value} = 0;
			 }
		}
	}

	private function setDebugMessage($key, $message) {
		if ($key) {
			$this->debugMessage[$key] = $message;
		} else {
			$this->debugMessage[] = $message;
		}
		if ($this->debug) {
			if (function_exists('debugMsg')) debugMsg($message); else echo '<div class="debug-msg">'.$message.'</div>';
		}
	}

	private function setWhere() {
		if (empty($this->args['where'])) return;

		foreach ($this->args['where'] as $whereKey => $whereGroup) {
			$where = [];
			foreach ($whereGroup as $whereItem) {
				if ($whereItem[0]) $where[] = $whereItem[0];
				unset($whereItem[0]);
				if ($whereItem) $this->setVariable($whereItem);
			}
			$this->where[$whereKey] = empty($where) ? '' : 'WHERE '.implode(' AND ', $where);
		}
	}

	private function setVariable($variable = []) {
		if (empty($variable)) return;

		if (is_object($variable)) $variable = $this->valueObjectConvert($variable);

		// TODO: convert variable to string with quote
		foreach ($variable as $key => $value) {
			if (is_null($value)) {
				$value = "NULL";
			} else if (is_string($value) && preg_match('/^\$/', $key)) {
				$value = $value;
			} else if (is_string($value) && preg_match('/^func\./i', $value)) {
				$value = substr($value,5);
			} else if (preg_match('/^(\:JSON_OBJECT)(\:.*)/i', $key, $out)) {
				$value = $this->jsonObjectString($value);
			} else if (is_object($value) && get_class($value) === 'Softganz\JsonDataModel') {
				$value = $this->jsonObjectString($value->args);
			} else if (is_object($value) && get_class($value) === 'Softganz\JsonArrayDataModel') {
				$value = $this->jsonArrayString($value->args);
			} else if (is_object($value) && get_class($value) === 'Softganz\SetDataModel') {
				$value = $this->valueOfSet($value);
			} else if (is_string($value)) {
				$value = $this->quote($value);
			} else if (is_bool($value)) {
				$value = $value ? 1 : 0;
			} else if (is_numeric($value)) {
				$value = $value;
			} else {
				$value = '""';
			}

			$this->var[$key] = $value;
		}
	}

	private function replaceWhere() {
		if (empty($this->where)) return;

		$whereKey = array_keys($this->where);

		// Quote value keys before use preg_replace
		$whereKey = array_map(function($v) {return "#".preg_quote($v)."#";}, $whereKey);

		// Replace value keys with $this->var
		$this->stmt = preg_replace($whereKey, $this->where, $this->stmt);
	}

	private function replaceVariable() {
		if (empty($this->var)) return;

		$varKey = array_keys($this->var);

		// Quote value keys before use preg_replace
		$varKey = array_map(function($v) {return "#".preg_quote($v)."#";}, $varKey);

		// Replace value keys with $this->var
		$this->stmt = preg_replace($varKey, $this->var, $this->stmt);
	}

	private function migrateStmtVersion() {
		$this->jsonVersionConvert();
	}

	private function jsonVersionConvert() {
		// Convert --> to JSON_UNQUOTE(JSON_EXTRACT()) in mariadb to mySQL 5.7
		if (preg_match('/\-\>\>/', $this->stmt, $out)) {
			// debugMsg('SRC STMT');
			// debugMsg($this->stmt);
			// debugMsg($out, '$out');
			// preg_match('/(`\w.*`) \-\>\> ("\$\.\w*\")/', $this->stmt, $out);
			// debugMsg($out, '$out');

			// Convert format from `table`.`field` ->> "$.jsonKey" to JSON_UNQUOTE(JSON_EXTRACT())
			$this->stmt = preg_replace('/(`\w*`\.`\w*`) \-\>\> ("\$\.\w*")/', 'JSON_UNQUOTE(JSON_EXTRACT($1, $2))', $this->stmt);
			// $this->stmt = preg_replace('/(`dos`\.`information`) \-\>\> ("\$\.\w*\")/', 'JSON_UNQUOTE(JSON_EXTRACT($1, $2))', $this->stmt);
			// debugMsg('CONVERT STMT');
			// debugMsg($this->stmt);
		}
	}

	private function replaceTable() {
		// Replace %tablename% with db(%tablename%)
		$this->stmt = preg_replace_callback(
			'/\s\%([a-zA-Z_][a-zA-Z0-9_.]*)\%/i',
			function($m) {
				return ' '.$this->db($m[1]);
			} ,
			$this->stmt
		);
	}

	private function valueOfSet($object) {
		foreach ($object as $key => $value) {
			if (!is_numeric($value)) $object->{$key} = $this->quote($value);
		}
		return implode(' , ', (Array) $object);
	}

	private function valueObjectConvert(Object $object) {
		$result = [];
		foreach ($object as $key => $value) {
			$result[':'.$key] = $value;
		}
		return $result;
	}

	private function logError($module, $method, $stmt, $code, $message) {
		$this->errors[] = (Object) ['code' => $code, 'message' => $message];
		$this->errorMsg = $message;

		$errorMessage = '<span style="color: red">ERROR::'.$code.'::'.$message.'</span>';
		$this->setDebugMessage(NULL, $errorMessage);

		if (class_exists('mydb') && class_exists('R')) {
			if (function_exists('R')) R()->DB->queryItems($stmt.'; -- '.$errorMessage);
			\mydb()->_watchlog = false;
			\R::Model('watchdog.log', $module, $method, $stmt.'; -- '.$errorMessage);
		}
	}

	private function quote($value) {return $this->PDO->quote($value);}

	/**
	 * Set & get table with keyword
	 * @param String $key
	 * @param String $new_value
	 * @param String $prefix
	 * @param String $db
	 * @return String
	 */
	private function db($key = NULL, $new_value = NULL, $prefix = NULL, $db = NULL) {
		static $items = [];
		static $src = [];

		if (empty($items) && function_exists('db')) $items = db();

		$tablePrefix = function_exists('cfg') ? cfg('db.prefix') : '';
		$ret=NULL;

		if (isset($key) && isset($new_value)) {
			$src[$key]=$new_value;
			$tablename=(isset($db)?'`'.$db.'`.':''); // Set database name
			if (preg_match('/\`([a-zA-Z0-9_].*)\`/',$new_value,$out)) {
				$tablename.=$new_value; // Use new_value on `table` or `db`.`table` format
			} else {
				$tablename.='`'.$tablePrefix.$new_value.'`'; // Add prefix on table format
			}
			$items[$key]=$tablename;
			$ret=$items;
		} else if (!isset($key) && isset($prefix)) {
			// Change all table items to new prefix value
			foreach ($src as $key=>$value) $items[$key]=$prefix.$value;
			$ret=$items;
		} else if (isset($key)) {
			// if key return value in condition
			// key format `table_name` return `table_name`
			// key format %table_name% return value in `$items[table_name]`
			// key format table_name and key in array items return items[table_name]
			// key format table_name and key not in items return db.prefix+table_name
			if (preg_match('/\`([a-zA-Z0-9_].*)\`/',$key,$out)) {
				$ret=$key;
			} else if (preg_match('/\%([a-zA-Z0-9_].*)\%/',$key,$out)) {
				$ret = array_key_exists($out[1],$items) ? $items[$out[1]] : '`'.$tablePrefix.$out[1].'`';
			} else {
				$ret = array_key_exists($key,$items) ? $items[$key] : '`'.$tablePrefix.$key.'`';
			}
		} else {
			$ret=$items;
		}
		return $ret;
	}

	private function jsonObjectString($value, $key = NULL) {
		// debugMsg($value, 'JSON Value');
		$jsonString = '';
		if ($key) $jsonString .= '"'.$key.'" , ';
		$jsonString .= 'JSON_OBJECT(';
		foreach ((Array) $value as $jsonKey => $jsonValue) {
			// debugMsg('KEY '.$jsonKey.' = '.$jsonValue);
			if (is_array($jsonValue)) {
				// debugMsg($jsonValue, '$jsonValue');
				$jsonString .= $this->jsonObjectString($jsonValue, $jsonKey).' , ';
				// return $jsonString;
			} else if (is_object($jsonValue)) {
				$jsonString .= $this->jsonObjectString($jsonValue, $jsonKey).' , ';
			} else {
				$jsonString .= '"'.$jsonKey.'" , "'.preg_replace('/[\"]/', '', $jsonValue).'" ,';
				// debugMsg($jsonString);
			}
		}
		$jsonString = rtrim($jsonString, ' , ');
		$jsonString .= ')';
		return $jsonString;
	}

	private function jsonArrayString($value, $key = NULL) {
		// debugMsg($value, 'JSON Value');
		$jsonString = '';
		if ($key) $jsonString .= '"'.$key.'" , ';
		$jsonString .= 'JSON_ARRAY('
			. '"'.implode('","', (Array) $value).'"';
		// foreach ((Array) $value as $jsonKey => $jsonValue) {
		// 	// debugMsg('KEY '.$jsonKey.' = '.$jsonValue);
		// 	$jsonString .= '"'.$jsonKey.'" , "'.preg_replace('/[\"]/', '', $jsonValue).'" ,';
		// 	// debugMsg($jsonString);
		// }
		// $jsonString = rtrim($jsonString, ' , ');
		$jsonString .= ')';
		return $jsonString;
	}

	private function updateLastQueryStmt($stmt, $error = NULL) {
		if (function_exists('mydb')) mydb()->_query = $stmt;
	}

}
?>