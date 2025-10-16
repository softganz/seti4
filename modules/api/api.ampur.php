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

class AmpurApi extends PageApi {
	var $changwat;

	function __construct() {
		parent::__construct([
			'changwat' => \SG\getFirst(post('changwat'), post('q')),
		]);
	}

	function build() {
		$result = [];

		if (empty($this->changwat)) return $result;

		$dbs = mydb::select(
			'SELECT `distid`, `distname`, `provname`
				FROM %co_district% co
					LEFT JOIN %co_province% cop ON cop.`provid` = LEFT(co.`distid`,2)
				WHERE
				'.(is_numeric($this->changwat) ? 'cop.`provid` = :provid' : '`distname` LIKE :q OR `provname` LIKE :q').'
					AND RIGHT(`distname`,1) != "*"
				ORDER BY CONVERT(`distname` USING tis620) ASC',
			[
				':q' => '%'.$this->changwat.'%',
				':provid' => $this->changwat
			]
		);

		foreach ($dbs->items as $rs) {
			$label = is_numeric($this->changwat) ? htmlspecialchars($rs->distname) : htmlspecialchars(' อำเภอ'.$rs->distname.' จังหวัด'.$rs->provname);

			$result[] = [
				'value' => $rs->subdistid,
				'ampur' => substr($rs->distid, -2),
				'label' => $label
			];
		}

		if (debug('api')) {
			$result[] = ['value' => 'query','label' => mydb()->_query];
			$result[] = ['value' => 'num_rows','label' => 'Result is '.$dbs->_num_rows.' row(s).'];
		}

		return $result;
	}
}
?>