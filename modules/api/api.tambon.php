<?php
/**
* API     :: Tambon API
* Created :: 2022-11-19
* Modify  :: 2022-11-19
* Version :: 2
*
* @return Array
*
* @usage api/tambon?q=tambonName
*/

class TambonApi extends PageApi {
	var $queryText;
	var $page;
	var $items;

	function __construct() {
		parent::__construct([
			'queryText' => SG\getFirst(post('q')),
			'page' => SG\getFirst(post('page'), post('p'), 1),
			'items' => SG\getFirst(post('item'), post('n'), 500),
		]);
	}

	function build() {
		$result = [];

		if (empty($this->queryText)) return $result;

		if (is_numeric($this->queryText)) {
			mydb::where('cod.`distid` = :distid', ':distid', $this->queryText);
		} else {
			mydb::where('`subdistname` LIKE :q OR `distname` LIKE :q OR `provname` LIKE :q', ':q', '%'.$this->queryText.'%');
		}
		mydb::where('LEFT(`subdistname`,1) != "*" AND RIGHT(`subdistname`,1) != "*"');
		mydb::value('$LIMIT$', 'LIMIT '.($this->page-1).','.$this->items);

		$dbs = mydb::select(
			'SELECT `subdistid`, `subdistname`, `distname`, `provname`
			FROM %co_subdistrict% co
				LEFT JOIN %co_district% cod ON cod.`distid`=LEFT(co.`subdistid`,4)
				LEFT JOIN %co_province% cop ON cop.`provid`=LEFT(co.`subdistid`,2)
			%WHERE%
			ORDER BY CONVERT(`subdistname` USING tis620) ASC
			$LIMIT$'
		);

		foreach ($dbs->items as $rs) {
			$label = is_numeric($this->queryText) ? htmlspecialchars($rs->subdistname) : htmlspecialchars('ตำบล'.$rs->subdistname.' อำเภอ'.$rs->distname.' จังหวัด'.$rs->provname);

			$result[] = array(
				'value' => $rs->subdistid,
				'tambon' => substr($rs->subdistid,-2),
				'label' => $label
			);
		}

		if (debug('api')) {
			$result[] = array('value' => 'query','label' => $dbs->_query);
			$result[] = array('value' => 'num_rows','label' => 'Result is '.$dbs->_num_rows.' row(s).');
		}
		return $result;
	}
}
?>