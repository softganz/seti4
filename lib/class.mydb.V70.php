<?php
/**
* mydb class for database management extension
*
* @package core
* @version 3.10
* @copyright Copyright (c) 2000-present , The SoftGanz Group By Panumas Nontapan
* @author Panumas Nontapan <webmaster@softganz.com> , http://www.softganz.com
* @created 2009-07-06
* @modify  2021-10-06
* ============================================
* This program is free software. You can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 2 of the License.
* ============================================
*/

/**
* shotcut to 	call $GLOBALS['mydb']
*/

function mydb() {return R()->myDb;}

class MyDbResult {
	private $_PROPERTY;
	function __construct() {
		$this->_PROPERTY = new stdClass();
		$this->_PROPERTY->count = 0;
		$this->_PROPERTY->empty = true;
	}

	function count($num = NULL) {
		if (!is_null($num)) {
			$this->_PROPERTY->count = $num;
			$this->_PROPERTY->empty = empty($num);
		}
		return $this->_PROPERTY->count ? $this->_PROPERTY->count : 0;
	}
}

class MyDb extends mysqli {
	public $class = 'mydb';
	public static $version = '3.10';
	public $dbUri;
	private $server;
	private $user;
	private $password;
	private $host;
	public $db;
	private $mydb;

	public $status = false;
	public $simulate = false;

	public $_watchlog = true;
	public $_debug = false;
	public $_query_times = 0;
	public $_last_query_time = 0;
	public $_query;
	public $_query_count = 0;
	public $_affected_rows;
	public $_error;
	public $_error_no;
	public $_errors = [];
	public $_wheres = [];
	public $_values = [];
	public $_field = [];
	public $_query_items = [];

	private $_multiquery = false;


	/**
	 * mydb construct
	 * @param String $dbUri
	 */
	function __construct($dbUri = NULL) {
		$olderror = error_reporting(0);
		if (empty($dbUri)) $dbUri = cfg('db');
		if (is_string($dbUri)) {
			$this->dbUri = $dbUri;
			preg_match('/(mysql)\:\/\/([^:]*)\:([^@]*)\@([^\/]*)\/(.*)/i',$dbUri,$out);
		} else if (is_array($dbUri)) {
			$out = $dbUri;
		}

		// url format -> mysql://username:password@host/db
		list(,$this->server,$this->user,$this->password,$this->host,$this->db) = $out;

		mysqli::__construct($this->host,$this->user,$this->password);

		if (empty($dbUri) || mysqli_connect_error()) {
			$this->_errors[]=$this->error_msg='Connect Error (' . mysqli_connect_errno() . ') : ' . mysqli_connect_error();
		} else {
			$this->status = true;

			// Disabled Strict mode
			mysqli::query('SET @@SESSION.sql_mode = ""');

			if (cfg('db.character_set_client')) {
				$character_set_client_sql='SET character_set_client="'.cfg('db.character_set_client').'" ';
				mysqli::query($character_set_client_sql);
				$this->_query_items[]=$this->_query=$character_set_client_sql;

				$character_set_results_sql='SET character_set_results="'.cfg('db.character_set_client').'" ';
				mysqli::query($character_set_results_sql);
				$this->_query_items[]=$this->_query=$character_set_results_sql;
			}

			if (cfg('db.character_set_connection')) {
				$character_set_connection_sql='SET character_set_connection="'.cfg('db.character_set_connection').'" ';
				mysqli::query($character_set_connection_sql);
				$this->_query_items[]=$this->_query=$character_set_connection_sql;
			}

			if (cfg('db.collation_connection')) {
				$collation_connection_sql='SET collation_connection="'.cfg('db.collation_connection').'" ';
				mysqli::query($collation_connection_sql);
				$this->_query_items[]=$this->_query=$collation_connection_sql;
			}
			if (cfg('db.character_set_client')) mysqli::set_charset(cfg('db.character_set_client'));
			mysqli::select_db($this->db);
		}
		error_reporting($olderror);
		$this->mydb = $this;
	}

	public static function reset() {
		$myDb = isset($this) && $this->mydb ? $this : mydb();
		$myDb->_wheres = [];
		$myDb->_values = [];
	}

	public static function where($cond) {
		$myDb = isset($this) && $this->mydb ? $this : mydb();
		if ($cond) {
			if (is_array($cond)) {
				foreach ($cond as $k => $v) {
					$myDb->_wheres[$k] = $v;
				}
			} else {
				$myDb->_wheres[] = $cond;
			}
		}
		$args = func_get_args();
		for ($i = 1; $i < count($args); $i = $i + 2) {
			if (isset($args[$i])) {
				// Convert array to set of string
				if (is_array($args[$i+1])) $args[$i+1] = 'SET-STRING:'.implode(', ',$args[$i+1]);
				$myDb->_values[$args[$i]] = isset($args[$i+1]) ? $args[$i+1] : NULL;
			}
		}
	}

	public static function value($key = NULL, $value = NULL, $escape = true) {
		$myDb = isset($this) && $this->mydb ? $this : mydb();
		if ($key) {
			if (isset($value)) {
				$myDb->_values = array($key => $escape ? mysqli_real_escape_string($myDb,$value) : $value) + $myDb->_values;
			}
			return $myDb->_values[$key];
		}
		return $myDb->_values;
	}


	/**
	* Prepare statement by replace keys with value
	* @param Object $myDb
	* @param String $stmt
	* @param Array $args
	* @return String
	*/
	public function prepare_stmt($myDb, $stmt, $args = Array()) {
		if (empty($myDb)) $myDb = mydb();

		//print_o($args,'prepare_stmt $args',1);

		$args_array = [];

		// Merge $this->_values to $args
		if (!empty($myDb->_values)) {
			$valueArgs = [];
			foreach ($myDb->_values as $k => $v) {
				$valueArgs[] = $k;
				$valueArgs[] = $v;
			}
			//debugMsg($valueArgs,'$valueArgs');
			$args = array_merge_recursive($valueArgs, $args);
			//debugMsg($args,'$args');
		}


		// Convert and merge array & object parameter to args_array
		foreach ($args as $k=>$ar) {
			if (is_null($k)) continue;
			//echo $k.'='.(is_array($ar) || is_object($ar)?print_o($ar,'$ar'):$ar).'<br />';
			if (is_array($ar)) $args_array=array_merge_recursive($args_array,$ar);
			if (is_object($ar)) $args_array=array_merge_recursive($args_array,(array)$ar);
			if (is_array($ar) || is_object($ar)) unset($args[$k]);
		}

		// reset array key to zero
		if (is_array($args)) $args = array_values($args);

		// merge $args_array to $args
		foreach ($args_array as $k => $v) {
			$args[] = (preg_match('/^\:/', $k) ? '' : ':') .$k;
			$args[] = $v;
		}
		unset($args_array);

		$args_num=count($args);

		//print_o($args,'$args',1);

		// replace %table_name% with table_prefix and table_name
		//$db_prefix=cfg('db.prefix');
		//	$stmt = strpos($stmt,'%')===false ? $stmt : preg_replace('/\s\%([a-zA-Z_][a-zA-Z0-9_.]*)\%/ie','\' `\'.$db_prefix."\\1".\'`\'',$stmt);

		//		$stmt = strpos($stmt,'%')===false ? $stmt : preg_replace('/\s\%([a-zA-Z_][a-zA-Z0-9_.]*)\%/ie','\' \'.(db("\\1")).\'\'',$stmt);


		// Convert argument value into right format
		$vars = $vark = [];

		if (isset($args[0]) && is_string($args[0])) {
			$args_num = count($args);
			for ($i = 0; $i < $args_num; $i = $i + 2) {
				if (isset($args[$i]) && isset($args[$i+1])) {
					$key = $args[$i];
					$value = $args[$i+1];

					if (is_null($value)) $value = "NULL";
					else if (is_string($value) && substr($key,0,1) == '$') $value = $value;
					else if (is_string($value) && substr($value,0,5) == 'func.') $value = substr($value,5);
					else if (is_string($value) && substr($value,0,4) == 'SET:') $value = substr($value,4);
					else if (is_string($value) && substr($value,0,11) == 'SET-STRING:') {
						$values = array();
						foreach (explode(',',substr($value,11)) as $v) $values[] = '"'.mysqli_real_escape_string($myDb,$v).'"';
						$value = implode(',', $values);
						//$value = implode(',', array_walk(explode(',',substr($value,11))),create_function('&$elem','$elem = mysqli_real_escape_string($myDb,$elem);'));
					} else if (is_string($value)) $value = '"'.mysqli_real_escape_string($myDb,$value).'"';
					else if (is_bool($value)) $value = $value ? 1 : 0;
					else if (is_numeric($value)) $value = $value;
					else if (is_array($value)) {$value = implode(',', $value);}
					else $value = '""';

					$vars[$args[$i]] = $value;

				} else if (is_null($args[$i]) && is_null($args[$i+1])) {
					// Do nothing
				} else if ($args[$i]) {
					$value = "NULL";
					$vars[$args[$i]] = 'NULL';
				}
			}
		}

		$vark = array_keys($vars);

		// Replace %WHERE% with implode of $this->_wheres
		$whereStr = empty($myDb->_wheres) ? '' : 'WHERE '.implode(' AND ', $myDb->_wheres);
		$stmt = str_replace('%WHERE%', $whereStr, $stmt);


		// Quote value keys before use preg_replace
		$vark = array_map(function($v) {return "#".preg_quote($v)."#";}, $vark);

		// Replace value keys with $this->_values
		$stmt = preg_replace($vark, $vars, $stmt);

		// Replace %tablename% with db(%tablename%)
		$stmt = strpos($stmt,'%')===false ? $stmt : preg_replace_callback('/\s\%([a-zA-Z_][a-zA-Z0-9_.]*)\%/i', '__mydb_db_replace' ,$stmt); // return ' '.db($m[1])

		return $stmt;
	}

	/**
	* Prepare query statement
	* @param String $stmt
	* @param String $key
	* @param Mixed $value
	* @return String query statement
	*/
	public function prepare($stmt) {
		static $methodCount = 0;
		$methodCount++;
		$prepareArgs = func_get_args();
		$isExtDb = isset($prepareArgs[1]) && is_object($prepareArgs[1]) && (isset($prepareArgs[1]->class) && $prepareArgs[1]->class == 'mydb') ? $prepareArgs[1] : false;
		$myDb = $isExtDb ? $isExtDb : mydb();
		//debugMsg('<em>===== PREPARE START ('.$methodCount.') ============</em>');
		//debugMsg('DB='.$myDb->db);
		//debugMsg('Prepare :: mydb :: '.print_o($myDb,'$myDb'));

		if (is_array($stmt)) list($stmt, $options) = $stmt;
		$prepareArgs = func_get_args();

		// remove query statment in first parameter
		array_shift($prepareArgs);
		if ($isExtDb) unset($prepareArgs[0]);

		//debugMsg('Prepare :: '.$stmt);
		//debugMsg('With '.print_o($prepareArgs,'$prepareArgs'));

		$prepareStmt = mydb::prepare_stmt($myDb, $stmt, $prepareArgs);

		if (!$prepareStmt) return false;

		if ($myDb->status) {
			$mysqlStmt = mysqli_stmt_init($myDb);

			if (mysqli_stmt_prepare($mysqlStmt, $prepareStmt)) {
				$mysqlStmt->_prepare = true;
			} else {
				// if query error Prepare empty statement
				$mysqlStmt->_prepare = false;
			}
		} else {
			$mysqlStmt = false;
		}

		$mysqlStmt->_query = $prepareStmt;
		//debugMsg(print_o($mysqlStmt,'$mysqlStmt'));
		//debugMsg('<em>===== PREPARE COMPLETE ('.$methodCount.') =====</em>');
		return $mysqlStmt;
	}


	/**
	* Extend from old mysqli::query with prepare and bind_param
	* @param string $stmt
	* @param string $types  , mixed $var1  [, mixed $...  ]
	* @param int $resultmode
	*/
	public function query($stmt, $resultmode = NULL) {
		$is_simulate = false;
		$optionDefault = '{debug: false, reset:true}';
		$optionPara = '{}';
		$debug = false;
		$myDb = isset($this) && $this->mydb ? $this : mydb();

		//$myDb = $isExtDb ? $isExtDb : mydb();

		mydb()->_query_count++;

		if (mydb()->_watchlog && $debug) debugMsg('<p>===== QUERY START =====</p>');
		//echo 'Query :: DB = '.$myDb->db.'<br />';

		if (!$myDb->status) return false;
		if ($debug) debugMsg('Query :: Src = '.$stmt);

		// $prepareArgs[0] is query statement

		// If second parameter is condition value
		if (is_string($resultmode) || is_array($resultmode) || is_object($resultmode) || strpos($stmt,'%') != false) {
			$prepareArgs = func_get_args();

			$prepareArgs[0] = trim($prepareArgs[0]);

			// Find config JSON format {} at end of stmt
			preg_match(_JSON_PATTERN_V7, $prepareArgs[0], $matches);

			if ($matches) {
				$prepareArgs[0] = trim(str_replace('-- '.$matches[1], '', $prepareArgs[0]));
				$optionPara = str_replace("'",'"',$matches[1]);
			}
			//debugMsg($args,'$args_after');

			$options = SG\json_decode($optionPara, $optionDefault);

			$mysqlStmt = call_user_func_array([$myDb, 'prepare'], $prepareArgs);

			$stmt = $mysqlStmt->_query;

			// debugMsg($prepareArgs,'$prepareArgs');
			// debugMsg('Query :: QUERY prepare = '.$stmt);

			mysqli_stmt_close($mysqlStmt);
			$resultmode = NULL;
		}

		if ($myDb->simulate) echo 'Simulate :'.$stmt;

		$data=array();

		$timer = new Timer();
		$timer->start('query');

		if ($myDb->simulate) {
			$res = NULL;
		} else if ($myDb->_multiquery) {
			$data = [];
			$res = mysqli_multi_query($myDb,$stmt);
			if ($res) {
				$i = 0;
				// Cycle through multiquery results
				do {
					$data[$i]->_query = $stmt;
					if ($res = $myDb->store_result()) {
						// Cycle through each results
						foreach ($res->fetch_all(MYSQLI_ASSOC) as $rs)
							$data[$i]->items[] = (object) $rs;;
						$res->free();
						$i++;
					}
				} while ($myDb->more_results() && $myDb->next_result());
				$myDb->next_result();
			}
		} else {
			$data = (object) NULL;
			$res = mysqli_query($myDb,$stmt);
			$data->_query = $stmt;
			if (is_object($res) && $res) {
				if ($debug) debugMsg('QUERY RESULT of '.$stmt.'<br />$res = ');
				// Cycle through results
				while ($row = $res->fetch_array(MYSQLI_ASSOC)){
					$data->items[] = (object) $row;
				}
				$res->free();
				// Free result set
				$res->close();
				$myDb->next_result();
				if ($debug) debugMsg($data, '$data');
			} else {
				if ($debug) debugMsg('QUERY NO RESULT of '.$stmt.'<br />$res = ');
			}
		}
		$timer->stop('query');


		$myDb->_last_query_time = $timer->get('query',3);
		$myDb->_query_times += $myDb->_last_query_time;

		$myDb->setMultiQuery(false);
		if ($options->reset) $myDb->reset();

		if (mydb()->_watchlog) {
			if ($debug) {
				debugMsg('QUERY='.$stmt);
				debugMsg('ERROR='.$myDb->error);
				debugMsg(mydb()->_watchlog ? 'Watch Log ENABLE':'');
			}

			$myDb->_error = $data->_error = $myDb->error;
			$myDb->_error_no = $data->_error_no = $myDb->errno;
			$myDb->_affected_rows = $data->_affected_rows = $affected_rows = $myDb->affected_rows;
			$error = isset($myDb->error) && $myDb->error ? ';<font color="red">-- '.$myDb->error.'</font>' : NULL;

			if ($error || post('debug') == 'query') $caller = get_caller(__FUNCTION__);

			$queryMsg = preg_replace('/\t/Sm', ' ', $stmt).';';
			$queryMsg .= ' <font color="green">-- '.($is_simulate?'was simulate ':'').'in <b>'.$myDb->_last_query_time.'</b> ms.</font>';
			$queryMsg .= ($affected_rows?' <strong>'.$affected_rows.'</strong> affected rows':'');
			$queryMsg .= $error ? $error : '';
			$queryMsg .= (isset($caller['from']) ? '<br /><font color="gray">-- Call from '.$caller['from'].'</font>':'');
			$data->_query = $myDb->_query = $queryMsg;
			$myDb->_query_items[] = $queryMsg;
			mydb()->_query = $queryMsg;

			if ($error) {
				mydb()->_watchlog = false;
				$myDb->_errors[] = $myDb->_error;
				R::Model('watchdog.log','mydb','query', $queryMsg);
			}
		}
		if (mydb()->_watchlog && $debug) debugMsg('<p>===== QUERY DONE =====</p>');
		mydb()->_watchlog = true;
		return $data;
	}


	/**
	* Select data from table with SELECT command
	* @param mixed $queryStmt	Use mysqlwqi_stmt or query statement with bind_param
	* @return object Record on single row request or record set on many rows request
	* resultType object, none, resource default = object
	*/
	public static function select($queryStmt = NULL) {
		static $selectCountTimes = 0;
		$optionDefault = '{debug: false, reset: true, key: null, showCount: null, resultType: "object", fieldType: false, value: null, group: null, fieldOnly: false}';
		$optionPara = '{}';
		$selectResult = new MyDbResult();
		$stmtResult = new stdClass();
		$mysqlnd = function_exists('mysqli_fetch_all');
		$sumFields = [];
		$isClearWhere = false;
		$args = func_get_args();
		$debug = mydb()->_debug;

		if (empty($queryStmt) || !is_string($queryStmt)) return $selectResult;

		$selectCountTimes++;
		$is_simulate = false;

		$isExtDb = isset($args[1]) && is_object($args[1]) && $args[1]->class=='mydb'?$args[1]:false;
		$myDb = $isExtDb ? $isExtDb : mydb();

		if (!isset($myDb) || !$myDb->status) return $selectResult;

		$myDb->_field = NULL;
		mydb()->_query_count++;

		$is_process_query_cmd = false;

		if (!is_string($queryStmt)) return $selectResult;

		$queryStmt = trim($queryStmt);

		// Find config JSON format {} at end of stmt
		preg_match(_JSON_PATTERN_V7, $queryStmt, $matches);

		if ($matches) {
			$queryStmt = $args[0] = trim(str_replace('-- '.$matches[1], '', $args[0]));
			$optionPara = str_replace("'",'"',$matches[1]);
		}

		$options = SG\json_decode($optionPara,$optionDefault);

		if (empty($options->key)) unset($options->key);
		if ($options->debug) $debug = $options->debug;

		if ($debug) {
			debugMsg('<font color="green"><b>===> MyDb Version '.MyDb::$version.' SELECT START #'.$selectCountTimes.'</b></font><br />'
				. (mydb()->_watchlog ? '<font color="green"><b>watch_log true</b></font><br />' : '')
				. ($mysqlnd ? '<font color="green"><b>Use mysqlnd and get_result for better performance *****</b></font><br />' : '')
				. '<b>Query : </b>'.$queryStmt
			);
			debugMsg(
				'<b>Default Options : </b>'.$optionDefault.'<br />'
				. '<b>Options : </b>'.$optionPara.'<br />'
				. print_o($options,'$options')
			);
			if ($matches) debugMsg($matches, '$matches');
			debugMsg('$queryStmt = '.$queryStmt.'<br />optionPara = '.$optionPara.'<br />'.print_o($args, '$args_after'));
		}

		if (isset($options->sum)) $sumFields = explode(',',$options->sum);

		$stmt = call_user_func_array([$myDb, 'prepare'], $args);

		$is_process_query_cmd = true;
		if ($isExtDb) unset($args[1]);

		if ($debug) {
			debugMsg('Prepare complete :: '.$stmt->_query);
			debugMsg($stmt, '$stmt');
		}

		$timer = new Timer();
		$timer->start('query');

		if ($mysqlnd) {
			// get_result ใช้ได้เมื่อมี driver
			// สามารถ query ได้มากกว่า
			$stmt->execute();
			$stmtResult = $stmt->get_result();
		} else {
			if ($debug) debugMsg('<font color="red"><b>Use mysqli_query is lower performance *****</b></font>');
			$stmtResult=mysqli_query($myDb,$stmt->_query);
		}

		$timer->stop('query');

		$affected_rows = $myDb->affected_rows;
		$myDb->_last_query_time = $timer->get('query',3);
		$myDb->_query_times += $myDb->_last_query_time;


		if ((isset($stmt->error) && $stmt->error) || post('debug')=='query') $caller = get_caller(__FUNCTION__);

		$queryMsg=$stmt->_query.';';
		$queryMsg.=(isset($stmt->error) && $stmt->error?'<font color="red">-- '.$stmt->errno.':'.$stmt->error.'</font>':'');

		$queryMsg.='<br /><font color="green">-- '.($is_simulate?'was simulate ':'').'in <b>'.$myDb->_last_query_time.'</b> ms.</font>';
		$queryMsg.=($affected_rows?' <strong>'.$affected_rows.'</strong> affected rows':'');
		$queryMsg.=(isset($caller['from']) ? '<br /><font color="gray">-- Call from '.$caller['from'].'</font><br />':'');

		if ($debug && $stmt->error) debugMsg('<font color="red"><b>SELECT ERROR :</b><hr />'.$queryMsg.'</font>');

		if (mydb()->_watchlog) mydb()->_query = $queryMsg;

		$myDb->_query_items[] = $queryMsg;

		$selectResult->_type = 'record set';
		$selectResult->_empty = true;
		$selectResult->_error = false;
		$selectResult->_found_rows = 0;
		$selectResult->_num_rows = 0;
		$selectResult->_start_row = 0;
		$selectResult->_times = $myDb->_last_query_time;
		$selectResult->_query = $stmt->_query; //preg_replace('/\t/Sm', ' ', $stmt->_query).';';
		$selectResult->_vars = array_merge_recursive($myDb->_values, array_slice($args,1));
		if ($sumFields) {
			$selectResult->sum = new stdClass();
			foreach ($sumFields as $v) $selectResult->sum->{$v} = 0;
		}
		if ($options->fieldType) $selectResult->_field = $myDb->__fetch_fields($stmt);

		$selectResult->items = [];


		// Clear where condition and value
		if ($options->reset) $myDb->reset();

		// If error then watchdog log
		if (isset($stmt->error) && $stmt->error && mydb()->_watchlog) {
			mydb()->_query = $queryMsg;
			$selectResult->_query = $queryMsg;
			$myDb->_error = $selectResult->_error = $stmt->errno;
			$myDb->_error_msg = $selectResult->_error_msg = $stmt->error;
			if (mydb()->_watchlog) {
				mydb()->_watchlog = false;
				R::Model('watchdog.log','mydb','select',$queryMsg);
			}
			mydb()->_watchlog = true;
			return $selectResult;
		}

		mydb()->_watchlog = true;


		//if ($debug) debugMsg($stmt->execute()?'Select :: execute OK<br />':'Select execute ERROR<br />');


		// Calculate Found Rows
		//debugMsg(mysqli_query($myDb,'SELECT FOUND_ROWS() `totals` LIMIT 1'),'CAL FOUND_ROWS => '.$stmt->_query);
		$foundRows = mysqli_query($myDb,'SELECT FOUND_ROWS() `totals` LIMIT 1')->fetch_array(MYSQLI_ASSOC);
		$selectResult->_found_rows = $foundRows['totals'];


		$selectResult->_num_rows = $stmtResult->num_rows;
		//$selectResult->count = $stmtResult->num_rows;
		$selectResult->count($stmtResult->num_rows);
		//echo 'COUNT = '.$stmtResult->num_rows.' '.$selectResult->count.' '.$selectResult->count().'<br />';

		//Uses the stored result and counts the rows.
		$lists = [];
		$rsCount = 0;
		switch ($options->resultType) {
			case 'none':
				$selectResult->_resource = $stmtResult;
				break;

			case 'array' :
				$selectResult = [];
				while($rs = $stmtResult->fetch_array(MYSQLI_ASSOC)) {
					$selectResult[$rs[$options->key]] = $rs[$options->value];
				}
				break;

			case 'resource':
				$selectResult->_resource = $stmtResult;
				break;

			default:
				if ($selectResult->count() >= 1) {
					while($rs = $stmtResult->fetch_array(MYSQLI_ASSOC)) {
						if ($options->showCount) echo ++$rsCount.' ';
						if (isset($options->group) && $options->group != '') {
							if (isset($options->key)) {
								$selectResult->items[$rs[$options->group]][$rs[$options->key]] = (object)$rs;
							} else {
								$selectResult->items[$rs[$options->group]][] = (object)$rs;
							}
						} else if (isset($options->key)) {
							if ($options->value) {
								$selectResult->items[$rs[$options->key]] = $rs[$options->value];
							} else {
								$selectResult->items[$rs[$options->key]] = (object)$rs;
							}
						} else {
							$selectResult->items[] = (object)$rs;
						}
						// Calculate SUM on field by config->sum
						if ($sumFields) {
							foreach ($sumFields as $sumFieldName) {
								$selectResult->sum->{$sumFieldName}+=$rs[$sumFieldName];
							}
						}
						if (!isset($is_lists)) $is_lists=count((array)$rs)===1;
						if ($is_lists) $lists = array_map(function($value) {return $value;}, $rs);
					}
					if ($options->showCount) echo '<br />';
				}

				if ($lists) {
					$selectResult->lists = new stdClass();
					$selectResult->lists->text = implode(',',$lists);
					$selectResult->lists->qoute = '"'.implode('","',$lists).'"';
				}

				if (preg_match('/(LIMIT[\s].*1|LIMIT[\s].*1;)$/i',$selectResult->_query)) {
					$selectResult->_type='record';
					if ($selectResult->_num_rows) {
						$rs = reset($selectResult->items);
						$selectResult->_num_rows = count((Array) $rs);
						foreach ($rs as $key=>$value) $selectResult->{$key}=$value;
					}
					unset($selectResult->items);
					//debugMsg('SELECT LIMIT 1 process ==> '.print_o($selectResult,'$selectResult'));
				}

				$selectResult->_empty = $selectResult->_num_rows <= 0;
				if ($options->fieldOnly) {
					// debugMsg($selectResult, 'selectResult');
					$selectResult = mydb::clearprop($selectResult);
					// debugMsg((Array)$selectResult, 'selectResult');
					// debugMsg('COUNT='.count((Array)$selectResult));
					// if (isset($selectResult->items) && count($selectResult->items) == 0) $selectResult = NULL;
					// else if (count((Array) $selectResult) == 0) $selectResult = NULL;
				}
				break;
		}

		if ($is_process_query_cmd) $stmt->close();
		if ($debug) debugMsg('<font color="green"><b><=== SELECT END #'.$selectCountTimes.'</b></font>');

		return $selectResult;
	}

	/**
	* Real Escape String for User Input
	*
	* @para String $str
	* @return String
	*/
	public static function escape($str) {
		return mysqli_escape_string(mydb(),$str);
	}

	/**
	* Calculate found rows on select with SQL_CALC_FOUND_ROWS
	*
	* @return Int
	*/
	public static function found_rows() {
		$stmt = 'SELECT FOUND_ROWS() `totals`;
						-- {reset:false}';
		$dbs = mydb::select($stmt);
		$totals = $dbs->items[0]->totals;
		return $totals;
	}

	/**
	* Calculate found rows on select with SQL_CALC_FOUND_ROWS
	*
	* @return Int
	*/
	public static function count_rows($table, $condition) {
		$args[] = 'SELECT COUNT(*) `totals` FROM '.$table.' WHERE '.$condition.' ;
						-- {reset:false}';
		$stmt = call_user_func_array(array(mydb(), 'prepare'), $args);
		//echo '<br /><br /><br />'.$stmt;

		$stmt = 'SELECT COUNT(*) `totals` FROM '.$table.' WHERE '.$condition.' ;
						-- {reset:false}';
		$dbs = mydb::select($stmt);
		//echo '<br /><br /><br />'.mydb()->_query;
		$totals = $dbs->items[0]->totals;
		return $totals;
	}

	/**
	* Clear property that leading with _
	*
	* @param Object $rs
	* @return Object
	*/
	public static function clearprop($rs) {
		foreach ($rs as $key => $value) {
			if (substr($key,0,1)=='_') unset($rs->{$key});
		}
		return $rs;
	}

	/**
	* Table list of current database
	*
	* @return Array
	*/
	public static function table_list() {
		//static $tables=NULL;
		$myDb = isset($this) && $this->mydb ? $this : mydb();
		//echo 'DB = '.$myDb->db.'<br />'.print_o($myDb,'$myDb');
		//if (!isset($tables)) {
			$tables = array();
			$query = 'SHOW TABLES FROM `'.$myDb->db.'`; -- {reset:false}';
			mydb()->_watchlog = false;
			$dbs = $myDb->select($query);
			foreach ($dbs->items as $rs) {
				$rs = array_values((array)$rs);
				$tables[] = $rs[0];
			}
		//}
		//debugMsg($tables,'$tables');
		return $tables;
	}

	/**
	* Table Exists
	*
	* @param String $tablename
	* @return Boolean
	*/
	public static function table_exists($table_name = NULL) {
		$table_name = trim(db($table_name));
		$table_name = str_replace('`', '', $table_name);
		//echo '<br /><br />'.$table_name.print_o(mydb()->table_list(),'$table',1);
		if (empty($table_name)) return false;
		return in_array($table_name, mydb()->table_list() ) ? $table_name : false;
	}

	/**
	* Get Column Name of Table
	*
	* @param String $table
	* @param String $col_name
	* @return Array
	*/
	public static function columns($table,$col_name='',$dbname='') {
		static $tables=NULL;
		if (!isset($tables[$table])) {
			$tables=array();
			if (substr($table,0,1)=='`') {
				$tablename=$table;
			} else if (substr($table,0,1)=='%') {
				$tablename=$table;
			} else {
				$tablename='%'.$table.'%';
			}
			$stmt = 'SHOW COLUMNS FROM '.($dbname?'`'.$dbname.'`.':'').$tablename.'; -- {reset:false}';
			if ( $dbs = mydb::select($stmt) ) {
				foreach ($dbs->items as $rs) {
					foreach ( $rs as $key=>$value ) if ( Is_Long($key) ) UnSet($rs[$key]);
					$tables[$table][$rs->Field] = $rs;
				}
			}
		}
		//print_o($tables,'$tables',1);
		return $col_name?(array_key_exists($col_name,$tables[$table])?$tables[$table][$col_name]:false):$tables[$table];
	}

	/**
	* Clear Auto ID
	*
	* @param String $table
	* @return Integer
	*/
	public static function clear_autoid($table) {
		foreach ( mydb::columns($table) as $fld ) {
			//print_o($fld,'$fld',1);
			if ( preg_match('|auto_increment|',$fld->Extra) ) {
				$auto_fld=$fld->Field;
				break;
			}
		}
		if ( isset($auto_fld) ) {
			$next_id=mydb::select('SELECT MAX('.$auto_fld.') lastid FROM '.db($table).' LIMIT 1; -- {reset:false}')->lastid+1;
			mydb::query('ALTER TABLE '.db($table).' AUTO_INCREMENT ='.$next_id);
		}
		return $next_id;
	}

	/**
	* Get set member of field
	*
	* @param String $table_name
	* @param String $col_name
	* @return Array
	*/
	public static function get_set_member($table_name=NULL,$col_name=NULL) {
		$rs = mydb::columns($table_name,$col_name);
		$type_def = $rs->Type;
		$open = strpos($type_def, '(');
		$close = strrpos($type_def, ')');
		if (!$open || !$close) {
			return FALSE;
		}
		$options = substr($type_def, $open + 2, $close - $open - 3);
		$options = explode('\',\'', $options);
		return $options;
	}

	/**
	* Make set string from array
	*
	* @param Array set_value
	* @return String
	*/
	public static function make_set($set_value=array()) {
		$result = NULL;
		if ( is_array($set_value) and count($set_value) ) {
			$set_array = array();
			foreach ( $set_value as $set_key=>$v ) if ( $v ) $set_array[] = $set_key;
			$result = implode(",",$set_array);
		}
		return $result;
	}

	/**
	* Convert record to record set
	* @param record $rs
	* @return record set
	*/
	public static function convert_record_to_recordset($rs) {
		$value->_type='record set';
		$value->_empty=true;
		$value->_error=false;
		$value->_num_rows=0;
		$value->items=array();
		if (!is_object($rs)) return $value;
		if ($rs->_num_rows) {
			foreach ($rs as $k=>$v) if (substr($k,0,1)=='_') unset($rs->$k);
			$value->items[]=$rs;
			$value->_num_rows=1;
			$value->_empty=false;
		}
		return $value;
	}


	/**
	* Create Insert Query Statement
	* @param String $table_name
	* @param Array $values
	* @return String $sql_cmd
	*/
	public static function create_insert_cmd($table_name=NULL,$values=array(),$postsign='') {
		if (is_object($values)) $values=(array)$values;
		if ( empty($values) || !is_array($values) ) $values=array();
		if (!preg_match('/^[\`\%]/',$table_name)) $table_name='%'.$table_name.'%';
		$columns = mydb::columns($table_name);
		$field_list = $value_list = '';
		foreach ( $values as $field=>$value ) {
			if (!isset($columns[$field])) continue;
			$field_list .= ' `'.$field.'` ,';
			if ( substr($value,0,5) === 'func.' ) {
				$func=substr($value,5);
				if ($func==='NOW()') $func='"'.date('Y-m-d H:i:s').'"';
				$value_list .= ' '.$func.' ';
			} else {
				$value_list .= ' :'.$field.$postsign.' ';
			}
			$value_list .= ',';
		}
		$field_list = substr($field_list,0,-1);
		$value_list = substr($value_list,0,-1);
		$sql_cmd = 'INSERT INTO '.$table_name.' ( '.$field_list.' ) VALUES ( '.$value_list.' );';
		return $sql_cmd;
	}

	/**
	* Create Update Query Statement
	* @param String $table_name
	* @param Array $values
	* @param String $cond
	* @return String $sql_cmd
	*/
	public static function create_update_cmd($table_name=NULL,$values=array(),$cond="") {
		if (is_object($values)) $values=(array)$values;
		if ( empty($values) || !is_array($values) ) $values=array();
		if (!preg_match('/^[\`\%]/',$table_name)) $table_name='%'.$table_name.'%';
		$columns = mydb::columns($table_name);
		$value_list = "";
		foreach ( $values as $field=>$value ) {
			if ( IsSet($columns[$field]) ) {
				$value_list .= ' `'.$field.'` = ';
				if ( substr($value,0,5) === 'func.' ) {
					$func=substr($value,5);
					if ($func==='NOW()') $func='"'.date('Y-m-d H:i:s').'"';
					$value_list .= ' '.$func.' ';
				} else if (is_null($value)) {
					$value_list .= 'NULL';
				} else {
					$value_list .= ' "'.trim(addslashes($value)).'" ';
				}
				$value_list .= ',';
			}
		}
		$field_list = substr($field_list,0,-1);
		$value_list = substr($value_list,0,-1);
		//$table_name = mydb::db_replace(' '.$table_name);
		$sql_cmd = 'UPDATE '.$table_name.' SET '.$value_list.' WHERE '.$cond.' ;';
		return $sql_cmd;
	}


	public static function create_fieldupdate($field, $separator = ',') {
		$result = array();
		foreach (explode($separator,$field) as $value)
			$result[] = '`'.mysqli_escape_string(mydb(),$value).'` = :'.$value;
		$ret = implode(' , ', $result);
		return $ret;
	}

	/**
	* Check for table is lock
	* @param String $table_name
	* @param String $db_name
	* @return Boolean
	*/
	public static function table_is_lock($table_name,$db_name=NULL) {
		if (!$db_name) $db_name=mydb()->db;
		$stmt='SHOW OPEN TABLES FROM `'.$db_name.'` LIKE "'.$table_name.'"; -- {reset:false}';
		$dbs=mydb::select($stmt);
		$isTableLock=false;
		foreach ($dbs->items as $rs) {
			if ($rs->In_use) {
				$isTableLock=true;
				break;
			}
		}
		return $isTableLock;
	}

	private function __fetch_fields($stmt) {
		$fields = array();
		$metadata = $stmt->result_metadata();
		//debugMsg(print_r($metadata,1));
		if (!isset($metadata)) return;
		while ($field = $metadata->fetch_field()) {
			//$fields->{$field->name} = $field;
			$fields[$field->name]= $field;
		}
		return $fields;
	}

	public static function printtable($dbs, $options = '{}') {
		$defaults = '{class: ""}';
		$options = sg_json_decode($options,$defaults);

		if (is_array($dbs->items))
			foreach(reset($dbs->items) as $key => $value) $thead[] = $key;
		else
			foreach(reset($dbs) as $key => $value) $thead[] = $key;

		$tables = new Table();
		if ($options->class) $tables->addClass($options->class);
		$tables->thead = $thead;

		if (is_array($dbs->items))
			foreach ($dbs->items as $value) $tables->rows[] = (array) $value;
		else
			foreach ($dbs as $value) $tables->rows[] = (array) $value;

		$ret .= $tables->build();

		return $ret;
	}

	public static function setMultiQuery($value=true) {
		mydb()->_multiquery=$value;
	}
} // end of class mydb
?>
