<?php
/**
* API     :: Commune API
* Created :: 2022-11-19
* Modify  :: 2022-11-19
* Version :: 2
*
* @return Array
*
* @usage api/ampur?changwat=changwatId
*/

class CommuneApi extends PageApi {
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
			'SELECT DISTINCT `commune`
					FROM %db_person% p
					WHERE `commune` LIKE :q
					ORDER BY CONVERT(`commune` USING tis620) ASC
					$LIMIT$',
			[':q' => '%'.$this->queryText.'%']
		);

		foreach ($dbs->items as $rs) {
			$result[] = ['value'=>$rs->commune, 'label'=>htmlspecialchars($rs->commune)];
		}
		if (debug('api')) {
			$result[] = ['value'=>'length','label'=>'Charactor length = '.strlen($tambon)];
			$result[] = ['value'=>'query','label'=>$dbs->_query];
			$result[] = ['value'=>'num_rows','label'=>'Result is '.$dbs->_num_rows.' row(s).'];
		}
		return $result;

	}
}
?>