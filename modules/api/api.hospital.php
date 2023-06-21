<?php
/**
* API     :: Ampur API
* Created :: 2022-11-19
* Modify  :: 2022-11-19
* Version :: 2
*
* @return Array
*
* @usage api/ampur?changwat=changwatId
*/

class HospitalApi extends PageApi {
	var $queryText;
	var $page;
	var $items;

	function __construct() {
		parent::__construct([
			'queryText' => \SG\getFirst(post('q')),
			'page' => \SG\getFirst(post('page'), post('p'), 1),
			'items' => \SG\getFirst(post('item'), post('n'), 50),
		]);
	}

	function build() {
		$result = [];

		if (empty($this->queryText)) return $result;

		mydb::value('$LIMIT$', 'LIMIT '.($this->page-1).','.$this->items);

		$dbs = mydb::select(
			'SELECT `off_id`, `off_name` FROM %co_office% co
				WHERE `off_name` LIKE :q
				ORDER BY `off_name` ASC
				$LIMIT$',
			[':q' => '%'.$this->queryText.'%']
		);

		foreach ($dbs->items as $rs) {
			$result[] = ['value'=>$rs->off_id, 'label'=>htmlspecialchars($rs->off_name)];
		}
		if (debug('api')) {
			$result[] = ['value'=>'query','label'=>$dbs->_query];
			$result[] = ['value'=>'num_rows','label'=>'Result is '.$dbs->_num_rows.' row(s).'];
		}
		return $result;

	}
}
?>