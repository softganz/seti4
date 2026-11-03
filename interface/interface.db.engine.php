<?php
/**
 * DbEngineInterface :: Contract for database engine implementations used by DB
 *
 * Each engine wraps a PDO connection and implements the statement
 * processing for one database server (e.g. MariaDbEngine, PostgresEngine).
 */

declare(strict_types=1);

namespace Softganz;

interface DbEngineInterface {
	public function select($args, DB $db, $callerFrom = null);

	public function query($db, $callerFrom = null);

	public function historyEnabled();

	public function insertId();

	public function PDO();

	public function sum($key);

	public function queryItems($stmt = null);

	public function debugMsg();
}
