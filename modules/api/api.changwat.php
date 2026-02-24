<?php
/**
 * API     :: Changwat API
 * Created :: 2024-01-22
 * Modify  :: 2026-02-24
 * Version :: 2
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

		try {
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
			]);
		} catch (Exception $e) {
			return apiError(_HTTP_ERROR_BAD_REQUEST, 'Invalid areaFund parameter.');
		}

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