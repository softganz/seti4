<?php
/**
 * API      :: Code API
 * Author   :: Little Bear<softganz@gmail.com>
 * Created  :: 2026-08-07
 * Modified :: 2026-08-07
 * Version  :: 1
 */

use Softganz\DB, Softganz\SetDataModel;

class CodeApi extends PageApi {
	function __construct($action = NULL) {
		parent::__construct([
			'action' => $action,
		]);
	}

	/**
	 * Right to build
	 *
	 * @return object|boolean
	 */
	function rightToBuild(): object|bool {
		return true;
	}

	/**
	 * Get changwats
	 *
	 * @return array
	 * 
	 * @usage api/code/changwat[?q=text]
	 */
	function changwat(): array {
		header('Access-Control-Allow-Origin: *');

		$queryText = \SG\getFirst(Request::all('q'));
		$areaFund = Request::all('areaFund', 'number');

		$result = [];

		try {
			$dbs = DB::select([
				'SELECT `cop`.`provId`, `cop`.`provName`
					FROM %co_province% AS `cop`
					%WHERE%
					ORDER BY CONVERT(`cop`.`provName` USING tis620) ASC',
				'where' => [
					'%WHERE%' => [
						['RIGHT(`cop`.`provname`,1) != "*"'],
						is_numeric($queryText) ? ['cop.`provId` LIKE :provId', ':provId' => $queryText . '%'] : ['`cop`.`provName` LIKE :q', ':q' => '%' . $queryText . '%'],
						'areaFund' => $areaFund ? ['`cop`.`provId` IN (SELECT `changwat` FROM %project_fund% WHERE `areaId` = :areaId)', ':areaId' => $areaFund] : NULL,
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
				'changwat' => $rs->provId,
				'changwatCode' => $rs->provId,
				'label' => $label,
				'changwatName' => $label,
			];
		}

		if (debug('api')) {
			$result[] = ['value' => 'query', 'label' => R('query')];
			$result[] = ['value' => 'rowCount', 'label' => 'Result is ' . $dbs->rowCount() . ' row(s).'];
		}

		return $result;
	}

	/**
	 * Get ampurs
	 *
	 * @return array
	 *
	 * @usage api/code/ampur?q=text
	 */
	function ampur(): array {
		$changwatName = Request::get(['changwat', 'q']);
		$changwatId = Request::get(['changwat', 'q'], '/^[\d\,]+$/');
		$group = Request::get('group', 'en');

		$result = [];

		if (empty($changwatId) && empty($changwatName)) return $result;

		$dbs = DB::select([
			'SELECT `distId` AS `ampurCode`, `distName` AS `ampurName`
			, `provId` AS `changwatCode`, `provName` AS `changwatName`
			FROM %co_district% AS `cod`
				LEFT JOIN %co_province% AS `cop` ON cop.`provId` = LEFT(`cod`.`distId`, 2)
			%WHERE%
			ORDER BY CONVERT(`ampurName` USING tis620) ASC',
			'%WHERE%' => [
				['RIGHT(`distname`, 1) != "*"'],
				$changwatId ? ['`cop`.`provId` IN ( :changwatId )'] : ['`distname` LIKE :changwatName OR `provName` LIKE :changwatName'],
			],
			'var' => [
				':changwatName' => '%' . $changwatName . '%',
				':changwatId' => new SetDataModel($changwatId)
			],
		]);

		foreach ($dbs->items as $rs) {
			$ampurName = is_numeric($changwatCode) ? $rs->ampurName : ' อำเภอ' . $rs->ampurName . ' จังหวัด' . $rs->changwatName;

			$value = [
				'value' => $rs->ampurCode,
				'ampur' => substr($rs->ampurCode, -2),
				'label' => htmlspecialchars($label),
				'ampurCode' => $rs->ampurCode,
				'ampurName' => $label,
				'changwatCode' => $rs->changwatCode,
				'changwatName' => $rs->changwatName,
			];

			if ($group) {
					$result[$value['changwatCode']][$value['ampur']] = $value;
			} else {
				$result[] = $value;
			}
		}

		if (debug('api')) {
			$result[] = ['value' => 'query', 'label' => R('query')];
			$result[] = ['value' => 'rowCount', 'label' => 'Result is ' . $dbs->rowCount() . ' row(s).'];
		}

		return $result;
	}

	/**
	 * Get tambons
	 *
	 * @return array
	 *
	 * @usage api/code/tambon?q=text
	 */
	function tambon(): array {
		$queryText = \SG\getFirst(post('q'));
		$page = \SG\getFirst(post('page'), post('p'), 1);
		$items = \SG\getFirst(post('item'), post('n'), 500);

		$result = [];

		if (empty($queryText)) return $result;

		$dbs = DB::select([
			'SELECT `subdistid`, `subdistname`, `distname`, `provname`
			FROM %co_subdistrict% co
				LEFT JOIN %co_district% cod ON cod.`distid`=LEFT(co.`subdistid`,4)
				LEFT JOIN %co_province% cop ON cop.`provid`=LEFT(co.`subdistid`,2)
			%WHERE%
			ORDER BY CONVERT(`subdistname` USING tis620) ASC
			$LIMIT$',
			'%WHERE%' => [
				is_numeric($queryText) ? ['cod.`distid` = :distid', ':distid' => $queryText] : ['`subdistname` LIKE :q OR `distname` LIKE :q OR `provname` LIKE :q', ':q' => '%' . $queryText . '%'],
				['LEFT(`subdistname`,1) != "*" AND RIGHT(`subdistname`,1) != "*"'],
			],
			'var' => [
				'$LIMIT$' => 'LIMIT '.($page-1).','.$items
			]
		]);

		foreach ($dbs->items as $rs) {
			$tambonName = is_numeric($queryText) ? $rs->subdistname : 'ตำบล' . $rs->subdistname . ' อำเภอ' . $rs->distname . ' จังหวัด' . $rs->provname;

			$result[] = array(
				'value' => $rs->subdistid,
				'tambon' => substr($rs->subdistid, -2),
				'tambonCode' => $rs->subdistid,
				'label' => htmlspecialchars($tambonName)
			);
		}

		if (debug('api')) {
			$result[] = array('value' => 'query', 'label' => R('query'));
			$result[] = array('value' => 'rowCount', 'label' => 'Result is ' . $dbs->rowCount() . ' row(s).');
		}
		return $result;
	}

	/**
	 * Get villages
	 *
	 * @return array
	 *
	 * @usage api/code/village?q=text
	 */
	function village(): array {
		$queryText = \SG\getFirst(post('q'));
		$page = \SG\getFirst(post('page'), post('p'), 1);
		$items = \SG\getFirst(post('item'), post('n'), 500);

			$result = [];

		if (empty($queryText)) return $result;

		$dbs = DB::select([
			'SELECT `villid`, `villname`, `subdistname`, `distname`, `provname`
			FROM %co_village% co
				LEFT JOIN %co_subdistrict% cos ON cos.`subdistid` = LEFT(co.`villid`,6)
				LEFT JOIN %co_district% cod ON cod.`distid` = LEFT(co.`villid`,4)
				LEFT JOIN %co_province% cop ON cop.`provid` = LEFT(co.`villid`,2)
			%WHERE%
			ORDER BY `villid` ASC
			$LIMIT$',
			'%WHERE%' => [
				is_numeric($queryText) ? ['cos.`subdistid` = :subdistid', ':subdistid' => $queryText] : ['co.`villname` LIKE :q', ':q' => '%'.$queryText.'%']
			],
			'var' => [
				'$LIMIT$' => 'LIMIT '.($page-1).','.$items
			]
		]);
		foreach ($dbs->items as $rs) {
			$villageName = is_numeric($queryText) ? 'ม.' . intval(substr($rs->villid,-2)) . ' - บ้าน' . $rs->villname : 'บ้าน' . $rs->villname . ' ตำบล' . $rs->subdistname . ' อำเภอ' . $rs->distname . ' จังหวัด' . $rs->provname;

			$result[] = [
				'value' => $rs->villid,
				'village' => substr($rs->villid, -2),
				'villageCode' => $rs->villid,
				'villageName' => $villageName,
				'label' => htmlspecialchars($villageName)
			];
		}

		if (debug('api')) {
			$result[] = array('value' => 'query', 'label' => R('query'));
			$result[] = array('value' => 'rowCount', 'label' => 'Result is ' . $dbs->rowCount() . ' row(s).');
		}

		return $result;
	}

	/**
	 * Get Issues
	 *
	 * @return object
	 *
	 * @usage api/code/issue
	 */
	function issue(): object {
		header('Access-Control-Allow-Origin: *');
		return DB::select(
			'SELECT
				`catId` `id`
				, `catParent` `parent`
				, `process`
				, `name`
			FROM %tag%
			WHERE `tagGroup` = "project:planning"
			ORDER BY `weight` ASC, `catId` ASC'
		);
	}

	/**
	 * Get hospitals
	 *
	 * @return array
	 *
	 * @usage api/code/hospital?q=text
	 */
	function hospital(): array {
		$queryText = \SG\getFirst(post('q'));
		$page = \SG\getFirst(post('page'), post('p'), 1);
		$items = \SG\getFirst(post('item'), post('n'), 50);

			$result = [];

		if (empty($queryText)) return $result;

		$dbs = DB::select([
			'SELECT `off_id`, `off_name` FROM %co_office% co
				WHERE `off_name` LIKE :q
				ORDER BY `off_name` ASC
				$LIMIT$',
			'var' => [
				':q' => '%'.$queryText.'%',
			]
		]);

		foreach ($dbs->items as $rs) {
			$result[] = [
				'value' => $rs->off_id,
				'label' => htmlspecialchars($rs->off_name)
			];
		}
		if (debug('api')) {
			$result[] = ['value'=>'query', 'label'=>$dbs->_query];
			$result[] = ['value'=>'rowCount', 'label'=>'Result is ' . $dbs->rowCount() . ' row(s).'];
		}
		return $result;

	}

	/**
	 * Get communes
	 *
	 * @return array
	 *
	 * @usage api/code/commune?q=text
	 */
	function commune(): array {
		$queryText = \SG\getFirst(post('q'));
		$page = \SG\getFirst(post('page'), post('p'), 1);
		$items = \SG\getFirst(post('item'), post('n'), 50);

		$result = [];

		if (empty($queryText)) return $result;

		$dbs = DB::select([
			'SELECT DISTINCT `commune`
			FROM %db_person% p
			WHERE `commune` LIKE :q
			ORDER BY CONVERT(`commune` USING tis620) ASC
			$LIMIT$',
			'var' => [
				':q' => '%'.$queryText.'%',
				'$LIMIT$' => 'LIMIT '.($page-1).','.$items
			]
		]);

		foreach ($dbs->items as $rs) {
			$result[] = [
				'value'=>$rs->commune,
				'label' => htmlspecialchars($rs->commune)
			];
		}
		if (debug('api')) {
			$result[] = ['value'=>'length', 'label' => 'Charactor length = ' . strlen($tambon)];
			$result[] = ['value'=>'query', 'label' => R('query')];
			$result[] = ['value'=>'rowCount', 'label'=>'Result is ' . $dbs->rowCount() . ' row(s).'];
		}
		return $result;

	}

}
?>