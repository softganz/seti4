<?php
/**
* Project API :: GL Transaction
* Created 2021-12-12
* Modify  2021-12-12
*
* @return Widget
*
* @usage project/nhso/api/gl/trans
*/

class ProjectNhsoApiGlTrans extends Page {
	var $zone;
	var $changwat;
	var $budgetYear;
	var $fundId;

	function __construct($arg1 = NULL) {
		$this->zone = post('zone');
		$this->changwat = post('changwat');
		$this->budgetYear = post('budgetYear');
		$this->fundId = post('fundId');
	}

	function build() {
		// $allowOrigin = ['*', 'https://editor.swagger.io'];

		// // header('Access-Control-Allow-Origin: *');
		// // $headerResult = http_response_code(200);

		// sendHeader('application/json');
		// // header('Access-Control-Allow-Origin: "https://domain.com"');
		// // $headerResult = http_response_code(_HTTP_ERROR_NOT_ALLOWED);

		// 	$http_origin = $_SERVER['HTTP_ORIGIN'];
		// 	if (in_array($http_origin, ["http://xlocalhost","https://communeinfo.com"])) {
		// 		header("Access-Control-Allow-Origin: $http_origin");
		// 	}

		// 	// header('SG-Access-Origin: '.$http_origin);

		// // Check multiple origin
		// // $http_origin = $_SERVER['HTTP_ORIGIN'];
		// // if (in_array($http_origin, array("http://www.domain1.com","http://www.domain2.com"))) {
		// // 	header("Access-Control-Allow-Origin: $http_origin");
		// // }

		// // header('SG-Access-Origin: '.$http_origin);

		$result = (Object) [
			'params' => (Object) [
				'zone' => $this->zone,
				'changwat' => $this->changwat,
				'fundId' => $this->fundId,
				'budgetYear' => $this->budgetYear,
			],
			'count' => 0,
			'items' => [],
		];

		mydb::where('gl.`amount` >= 0 AND gl.`refDate` IS NOT NULL');

		if ($this->fundId) mydb::where('f.`fundId` = :fundId', ':fundId', $this->fundId);
		else if ($this->changwat) mydb::where('LEFT(o.`areacode`, 2) = :changwatId', ':changwatId', $this->changwat);
		else if ($this->zone) mydb::where('f.`areaId` = :zoneId', ':zoneId', $this->zone);

		if ($this->budgetYear) mydb::where('gl.`refDate` BETWEEN :startDate AND :endDate', ':startDate', $this->budgetYear.'-10-01', ':endDate', ($this->budgetYear+1).'-09-30');

		$dbsResource = mydb::select(
			'SELECT
			o.`shortname` `fundId`
			, gl.`refDate`
			, gl.`refCode`
			, gl.`glCode` `debitCode`
			, cr.`glCode` `creditCode`
			, CAST(gl.`amount` AS DOUBLE) `debit`
			, CAST(cr.`amount` AS DOUBLE) `credit`
			-- , COUNT(*) `totalTrans`
			FROM %project_gl% gl
				LEFT JOIN %project_gl% cr ON cr.`refCode` = gl.`refCode` AND cr.`amount` < 0
				LEFT JOIN %db_org% o ON o.`orgId` = gl.`orgId`
				LEFT JOIN %project_fund% f ON f.`orgId` = gl.`orgId`
			%WHERE%
			GROUP BY gl.`refCode`
			-- HAVING `debit` != ABS(`credit`)
			ORDER BY gl.`refDate` ASC;
			-- {resultType: "resource"}'
		);
		// debugMsg(mydb()->_query);

		while($rs = $dbsResource->resource->fetch_array(MYSQLI_ASSOC)) {
			$result->items[] = $rs;
			$result->count++;
		}
		return $result;

		// //TODO:: Error on , at last items of array
		// echo '{'._NL.'"items: ['._NL;
		// while($rs = $dbsResource->_resource->fetch_array(MYSQLI_ASSOC)) {
		// 	echo SG\json_encode($rs).','._NL;
		// 	echo SG\json_encode($rs).','._NL;
		// 	echo SG\json_encode($rs).','._NL;
		// 	// $ret .= SG\json_encode($rs).','._NL;
		// 	// $ret .= SG\json_encode($rs).','._NL;
		// 	// $result->count++;
		// 	// $result->count++;
		// }
		// echo ']'._NL.'}';
		// die;

		// $ret = '{'._NL.'"items: ['._NL;
		// while($rs = $dbsResource->_resource->fetch_array(MYSQLI_ASSOC)) {
		// 	$ret .= SG\json_encode($rs).','._NL;
		// 	$ret .= SG\json_encode($rs).','._NL;
		// 	// $result->count++;
		// 	// $result->count++;
		// }
		// $ret .= ']'._NL.'}';
		// die($ret);
	}
}
?>