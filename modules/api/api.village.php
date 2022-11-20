<?php
/**
* API     :: Village API
* Created :: 2022-11-19
* Modify  :: 2022-11-19
* Version :: 2
*
* @return Array
*
* @usage api/ampur?changwat=changwatId
*/

class VillageApi extends PageApi {
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

		if (is_numeric($this->queryText)) mydb::where('cos.`subdistid` = :subdistid', ':subdistid', $this->queryText);
		else mydb::where('co.`villname` LIKE :q', ':q', '%'.$this->queryText.'%');

		mydb::value('$LIMIT$', 'LIMIT '.($this->page-1).','.$this->items);

		$dbs = mydb::select(
			'SELECT `villid`, `villname`, `subdistname`, `distname`, `provname`
			FROM %co_village% co
				LEFT JOIN %co_subdistrict% cos ON cos.`subdistid` = LEFT(co.`villid`,6)
				LEFT JOIN %co_district% cod ON cod.`distid` = LEFT(co.`villid`,4)
				LEFT JOIN %co_province% cop ON cop.`provid` = LEFT(co.`villid`,2)
			%WHERE%
			ORDER BY `villid` ASC
			$LIMIT$'
		);
		foreach ($dbs->items as $rs) {
			$label = is_numeric($this->queryText) ? htmlspecialchars('ม.'.intval(substr($rs->villid,-2)).' - บ้าน'.$rs->villname) : htmlspecialchars('ตำบล'.$rs->subdistname.' อำเภอ'.$rs->distname.' จังหวัด'.$rs->provname);

			$result[] = [
				'value' => $rs->villid,
				'village' => substr($rs->villid,-2),
				'label' => $label
			];
		}

		if (debug('api')) {
			$result[] = array('value' => 'query','label' => mydb()->_query);
			$result[] = array('value' => 'num_rows','label' => 'Result is '.$dbs->_num_rows.' row(s).');
		}

		return $result;
	}
}
?>