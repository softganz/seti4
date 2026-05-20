<?php
/**
 * API     :: Ampur API
 * Author  :: Little Bear<softganz@gmail.com>
 * Created :: 2022-11-19
 * Modify  :: 2026-05-20
 * Version :: 4
 *
 * @return Array
 *
 * @usage api/ampur?changwat=changwatId
 */

use Softganz\DB, Softganz\SetDataModel;

class AmpurApi extends PageApi {
	var $changwatId;
	var $changwatName;
	var $group;

	function __construct() {
		parent::__construct([
			'changwatName' => Request::get(['changwat', 'q']),
			'changwatId' => Request::get(['changwat', 'q'], '/^[\d\,]+$/'),
			'group' => Request::get('group', 'en'),
		]);
	}

	function build() {
		$result = [];

		if (empty($this->changwatId) && empty($this->changwatName)) return $result;

		$dbs = DB::select([
			'SELECT `distId` AS `ampurCode`, `distName` AS `ampurName`
			, `provId` AS `changwatCode`, `provName` AS `changwatName`
			FROM %co_district% AS `cod`
				LEFT JOIN %co_province% AS `cop` ON cop.`provId` = LEFT(`cod`.`distId`, 2)
			%WHERE%
			ORDER BY CONVERT(`ampurName` USING tis620) ASC',
			'%WHERE%' => [
				['RIGHT(`distname`, 1) != "*"'],
				$this->changwatId ? ['`cop`.`provId` IN ( :changwatId )'] : ['`distname` LIKE :changwatName OR `provName` LIKE :changwatName'],
			],
			'var' => [
				':changwatName' => '%' . $this->changwatName . '%',
				':changwatId' => new SetDataModel($this->changwatId)
			],
		]);

		foreach ($dbs->items as $rs) {
			$label = is_numeric($this->changwatCode) ? htmlspecialchars($rs->ampurName) : htmlspecialchars(' อำเภอ'.$rs->ampurName.' จังหวัด'.$rs->changwatName);

			$value = [
				'value' => $rs->ampurCode,
				'ampur' => substr($rs->ampurCode, -2),
				'label' => $label,
				'ampurCode' => $rs->ampurCode,
				'ampurName' => $label,
				'changwatCode' => $rs->changwatCode,
				'changwatName' => $rs->changwatName,
			];

			if ($this->group) {
					$result[$value['changwatCode']][$value['ampur']] = $value;
			} else {
				$result[] = $value;
			}
		}

		if (debug('api')) {
			$result[] = ['value' => 'query','label' => R('query')];
			$result[] = ['value' => 'num_rows','label' => 'Result is ' . count((Array) $dbs->items) . ' row(s).'];
		}

		return $result;
	}
}
?>
