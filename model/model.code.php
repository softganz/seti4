<?php
/**
 * Code     :: Code Collection Model
 * Author   :: Little Bear<softganz@gmail.com>
 * Created  :: 2021-09-11
 * Modified :: 2026-08-23
 * Version  :: 5
 *
 * @usage import('model:code')
 */

use Softganz\DB;

class ChangwatModel {
	public static function items($conditions = NULL, $options = '{}') {
		$defaults = '{debug: false, result: "record", zone: "changwat", selectText: null}';

		if (is_string($conditions) && preg_match('/^{/',$conditions)) {
			$conditions = \SG\json_decode($conditions);
		} else if (is_object($conditions)) {
			//
		} else if (is_array($conditions)) {
			$conditions = (Object) $conditions;
		} else {
			$conditions = (Object) ['id' => $conditions];
		}

		$options = SG\json_decode($conditions->options, $options, $defaults);
		$debug = $options->debug;
		// debugMsg($options, '$options');

		$result = [];
		if ($options->selectText) $result[-1] = $options->selectText;

		$changwatList = DB::select([
			'SELECT `provid`, `provname`
			FROM %co_province%
			%WHERE%
			ORDER BY CONVERT(`provname` USING tis620) ASC',
			'%WHERE%' => [
				$conditions->idLike ? ['`provId` LIKE :idLike', ':idLike' => $conditions->idLike.'%'] : null
			]
		])->items;

		if ($options->zone === 'country') {
			$result = $result + [
				'ระดับภูมิภาค' => [
					// 'ASIAN' => '++ อาเซียน',
					'SEA' => '++ เอเชียตะวันออกเฉียงใต้ (South East Asia)',
					'ASIA' => '++ ทวีปเอเชีย (Asia)',
					'EU' => '++ ทวีปยุโรป (Europe)',
				],
				'TH' => '++ ทั้งประเทศ',
				'ระดับภาค' => [
					1 => '++ ภาคกลาง',
					3 => '++ ภาคตะวันออกเฉียงเหนือ',
					5 => '++ ภาคเหนือ',
					8 => '++ ภาคใต้',
				]
			];
		}

		foreach ($changwatList as $rs) {
			if ($options->zone === 'changwat') {
				$result[$rs->provid] = $rs->provname;
			} else {
				$result['ระดับจังหวัด'][$rs->provid] = $rs->provname;
			}
		}

		return $result;
	}

	// Get changwat, ampur, tombon name from areacode
	public static function getNameFromAreaCode($areacode) {
		if (empty($areacode)) return;

		$areaName = DB::select([
			'SELECT
				CONCAT(
					IF(`tambon`.`subDistName` IS NOT NULL, CONCAT("ต.", `tambon`.`subDistName`, " "), "")
					, IF(`ampur`.`distName` IS NOT NULL, CONCAT("อ.", `ampur`.`distName`, " "), "")
					, "จ."
					, `changwat`.`provName`
				) `areaName`
			FROM %co_province% `changwat`
				LEFT JOIN %co_district% `ampur` ON `ampur`.`distId` = :ampurId
				LEFT JOIN %co_subdistrict% `tambon` ON `tambon`.`subDistId` = :tambonId
			WHERE `provId` = :changwatId
			LIMIT 1',
			'var' => [
				':changwatId' => substr($areacode, 0, 2),
				':ampurId' => substr($areacode, 0, 4),
				':tambonId' => substr($areacode, 0, 6),
			]
		])->areaName;

		return $areaName;
	}
}

class AmpurModel {
	public static function inChangwat($changwat) {
		if (empty($changwat)) return [];

		return DB::select([
			'SELECT `distid`, `distname`
			FROM  %co_district%
			WHERE LEFT(`distid`, 2) = :changwat AND NOT INSTR(`distname`, "*")',
			'var' => [':changwat' => $changwat],
			'options' => ['key' => 'distid', 'value' => 'distname']
		])->items;
	}
}
?>