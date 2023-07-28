<?php
/**
* DB.     :: Database Management
* Created :: 2023-07-28
* Modify  :: 2023-07-28
* Version :: 1
*
* @param Array $args
* @return Object
*
* @usage new DB([])
* @usage DB::function($conditions)
*/

// namespace Softganz;

// new Softganz\DB();

// use Softganz\DB;
// new DB();

// use Softganz\DB AS DBS;
// new DBS();

class DataModel {
	function __construct(Array $args) {
		foreach ($args as $key => $value) {
			$this->{$key} = $value;
		}
	}
}

class SetDataModel extends DataModel {
	function __construct(Array $args) {$this->args = $args;}
}

class JsonDataModel extends DataModel {
	function __construct(Array $args) {$this->args = json_encode($args);}
}

class DB {
	private $stmt;
	private $args = [];
	private $error;

	public $count = 0;
	public $items = ['Data Item 1'];

	function __construct(Array $args) {
		$this->stmt = $args[0];
		unset($args[0]);
		$this->args = $args;
		if (preg_match('/^SELECT /i', $this->stmt)) {
			$this->select();
		} else {
			$this->query();
		}
	}

	function args() {return $this->args;}

	function stmt() {return $this->stmt;}

	function error() {return $this->error;}

	function select() {
		$this->items[] = 'EXECUTE SELECT : '.$this->stmt;
		$this->count = count($this->items);

		if ($this->args['onComplete'] && is_callable($this->args['onComplete'])) $this->args['onComplete']($this);
		$this->count = count($this->items);
	}

	function query() {
		$this->count = 1;
		$this->items[] = 'EXECUTE QUERY : '.$this->stmt;
	}
}
?>