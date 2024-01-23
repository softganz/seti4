<?php
/**
* API     :: Changwat API
* Created :: 2024-01-22
* Modify  :: 2024-01-22
* Version :: 1
*
* @return Array
*
* @usage api/changwat?areaFund=areaId
*/

use Softganz\DB;

class ChangwatApi extends PageApi {
	var $areaFund;

	function __construct() {
		parent::__construct([
			'areaFund' => post('areaFund'),
		]);
	}

	function build() {
		$result = [];

		// if (empty($this->changwat)) return $result;

		$dbs = DB::select([
			'SELECT `provId`, `provName`
				FROM %co_province% cop
				%WHERE%
				ORDER BY CONVERT(`provName` USING tis620) ASC',
			'where' => [
				'%WHERE%' => [
					['RIGHT(`provname`,1) != "*"'],
					'areaFund' => $this->areaFund ? ['`cop`.`provId` IN (SELECT `changwat` FROM %project_fund% WHERE `areaId` = :areaId)', ':areaId' => $this->areaFund] : NULL,
				]
			],
			// [
			// 	':q' => '%'.$this->changwat.'%',
			// 	':provid' => $this->changwat
			// ]
		]);

		foreach ($dbs->items as $rs) {
			$label = $rs->provName;

			$result[] = [
				'value' => $rs->provId,
				'changwat' => substr($rs->provId, -2),
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