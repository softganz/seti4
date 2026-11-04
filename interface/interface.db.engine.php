<?php
/**
 * DbEngineInterface :: Contract for database engine implementations used by DB
 *
 * Each engine wraps a PDO connection and implements the statement
 * processing for one database server (e.g. MariaDbEngine, PostgresEngine).
 * Author   :: Little Bear<softganz@gmail.com>
 * Created  :: 2026-11-03
 * Modified :: 2026-11-04
 * Version  :: 2
 */

declare(strict_types=1);

namespace Softganz;

interface DbEngineInterface {
	public function select(array $args, DB $db, ?string $callerFrom = null);

	public function query(DB $db, ?string $callerFrom = null);

	public function historyEnabled();

	public function insertId();

	public function PDO();

	public function sum(string $key);

	public function queryItems($stmt = null);

	public function debugMsg();
}
