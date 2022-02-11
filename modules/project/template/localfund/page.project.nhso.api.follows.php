<?php
/**
* Project API :: Project Follow List
* Created 2021-04-09
* Modify  2021-12-12
*
* @param $_REQUEST title,q,budgetYear,area,fundId,changwat,result,items,p
* @return json
*
* @usage project/nhso/api/follows?budgetYear=2021&title=แมลง+ยุง+มาลาเรีย
*/

class ProjectNhsoApiFollows extends Page {
	function build() {
		sendheader('text/html');

		$getTitle = post('title');
		$getSearch = post('q');
		$getBudgetYear = post('budgetYear');
		$getFundArea = SG\getFirst(post('zone'), post('area'), 12);
		$getFundId = SG\getFirst(post('fund'), post('fundId'));
		$getChangwat = post('changwat');

		$resultType = SG\getFirst(post('result'), 'json');
		$getItems = post('items');
		$getPage = intval(SG\getFirst(post('p'),1));

		$debug = debug('api');

		$result = (Object) [
			'description' => 'NSHO Follow Project',
			'params' => (Object) [
				'title' => $getTitle,
				'budgetYear' => $getBudgetYear,
				'zone' => $getFundArea,
				'fundId' => $getFundId,
				'changwat' =>$getChangwat,
			],
			'count' => 0,
			'items' => NULL,
		];

		// Prepare Condition
		if ($resultType == 'autocomplete') {
			$items = SG\getFirst($getItems, 20);
			if (empty($getSearch) && empty($getTitle)) return $result;
		} else {
			$items = SG\getFirst($getItems, '*');
		}

		$options = new stdClass();
		$options->order = 'p.`tpid`';
		$options->sort = 'ASC';
		$options->items = $items;

		// Data Model
		if ($getBudgetYear) {
			mydb::where('p.`pryear` = :budgetYear', ':budgetYear', $getBudgetYear);
		}

		if ($getFundId) mydb::where('o.`shortname` = :fundId', ':fundId', $getFundId);
		else if ($getChangwat) mydb::where('o.`areacode` LIKE :changwat', ':changwat', $getChangwat.'%');
		else if ($getFundArea) mydb::where('f.`areaid` = :zone', ':zone', $getFundArea);

		if ($getTitle) {
			$q = preg_replace('/\s+/', ' ', $getTitle);
			if (preg_match('/^code:(\w.*)/', $q, $out)) {
				mydb::where('p.`tpid` = :tpid', ':tpid', $out[1]);
			} else {
				$searchList = explode('+', $q);
				//debugMsg('$q = '.$q);
				//debugMsg($searchList, '$searchList');
				$qLists = array();
				foreach ($searchList as $key => $str) {
					$str = trim($str);
					if ($str == '') continue;
					$qLists[] = '(t.`title` RLIKE :q'.$key.')';

					//$str=mysqli_real_escape_string($str);
					$str = preg_replace('/([.*?+\[\]{}^$|(\)])/','\\\\\1',$str);
					$str = preg_replace('/(\\\[.*?+\[\]{}^$|(\)\\\])/','\\\\\1',$str);

					// this comment for correct sublimetext syntax highlight
					// $str=preg_replace('/(\\[.*?+\[\]{}^$|(\)\\])/','\\\\\1',$str);

					mydb::where(NULL, ':q'.$key, str_replace(' ', '|', $str));
				}
				if ($qLists) mydb::where('('.(is_numeric($q) ? 'p.`tpid` = :q OR ' : '').implode(' AND ', $qLists).')', ':q', $q);
			}
		}

		if (empty(mydb()->_wheres)) return $result;

		mydb::where('p.`prtype` = "โครงการ"');

		//debugMsg(mydb(), 'mydb()');

		mydb::value('$ORDER$', 'ORDER BY '.$options->order.' '.$options->sort);
		mydb::value('$LIMIT$', $options->items == '*' ? '' : 'LIMIT '.$options->start.' '.$options->items);

		$projectDbs = mydb::select(
			'SELECT
			p.`tpid` `projectId`
			, o.`shortname` `fundId`
			, t.`title`
			, p.*
			, p.`pryear` `budgetYear`
			, p.`supportType` `projectType`
			, CAST(p.`budget` AS UNSIGNED) `budget`
			, CONCAT(X(p.`location`), ",", Y(p.`location`)) `location`
			, o.`name` `orgName`
			, o.`shortname` `orgShortName`
			, o.`areaCode`
			, f.`areaId`
			, a.`areaname` `areaName`
			, cop.`provname` `changwatName`
			, cod.`distname` `ampurName`
			, pt.`name` `projectTypeName`
			, GROUP_CONCAT(tg.`amount`) `targetAmt`
			, CAST(SUM(tg.`amount`) AS UNSIGNED) `targetSize`
			, GROUP_CONCAT(DISTINCT ptn.`name`) `targetGroup`
		--	, GROUP_CONCAT(tpn.`title`) `targetActivity`
			, (SELECT GROUP_CONCAT(DISTINCT ap.`title`)
				FROM %topic% t1
					LEFT JOIN %topic_parent% tp ON tp.`tpid` = t1.`tpid`
					LEFT JOIN %topic% ap ON ap.`tpid` = tp.`parent`
				WHERE t1.`tpid` = p.`tpid`) `targetActivity`
			, DATE_FORMAT(t.`created`, "%Y-%m-%d %H:%i:%s") `created`
			FROM %project% p
				LEFT JOIN %topic% t USING(`tpid`)
				LEFT JOIN %db_org% o ON o.`orgid` = t.`orgid`
				LEFT JOIN %project_fund% f ON f.`orgid` = t.`orgid`
				LEFT JOIN %project_area% a ON a.`areaid` = f.`areaid`
				LEFT JOIN %co_province% cop ON cop.`provid` = LEFT(o.`areacode`, 2)
				LEFT JOIN %co_district% cod ON cod.`distid` = LEFT(o.`areacode`, 4)
				-- Support Type
				LEFT JOIN %tag% pt ON pt.`taggroup` = "project:supporttype" AND pt.`catid` = p.`supporttype`
				LEFT JOIN %project_target% tg ON tg.`tpid` = p.`tpid` AND `tagname` = "info"
				-- Project Tagret Name
				LEFT JOIN %tag% ptn ON ptn.`taggroup` = "project:target" AND ptn.`catid` = tg.`tgtid`
				-- Activity On Target
				-- LEFT JOIN %topic_parent% tp ON tp.`tpid` = p.`tpid`
			%WHERE%
			GROUP BY `projectId`
			$ORDER$
			$LIMIT$;
			-- {resultType: "resource"}'
		);
		// debugMsg(mydb()->_query);
		//print_r(reset($projectDbs->items));

		$result->count = $projectDbs->count();



		// View Model
		if ($debug) $result->debug[] = reset($projectDbs->items);

		// Field :: เขต	จังหวัด	อำเภอ	รหัสกองทุน	กองทุน	รหัสโครงการ	ชื่อโครงการ	ปีงบประมาณ	ประเภทโครงการ	หน่วยงาน/องค์กร/กลุ่มคน	ระยะเวลาโครงการเริ่มต้น	ระยะเวลาโครงการสิ้นสุด	งบประมาณที่เสนอ	งบประมาณที่อนุมัติ	วันที่ได้รับอนุมัติโครงการ	วันที่ต้องรายงานผล	ผู้ประสานงานโครงการ	เบอร์โทรศัพท์	กลุ่มเป้าหมาย	จำนวนคน	กิจกรรมตามกลุ่มเป้าหมาย

		$dateFormat = 'Y-m-d';

		// foreach ($projectDbs->items as $rs) {
		while($rs = $projectDbs->resource->fetch_array(MYSQLI_ASSOC)) {
			$rs = (Object) $rs;
			if ($getTitle) {
				$result->items[] = [
					'areaName' => $rs->areaName,
					'changwatName' => $rs->changwatName,
					'ampurName' => $rs->ampurName,
					'fundCode' => $rs->orgShortName,
					'fundName' => $rs->orgName,
					'projectId' => $rs->projectId,
					'projectCode' => $rs->prid,
					'projectTitle' => $rs->title,
					'budgetYear' => $rs->pryear ? $rs->pryear + 543 : NULL,
					'projectType' => $rs->projectTypeName,
					'orgName' => $rs->orgnamedo,
					'dateStart' => $rs->date_from ? sg_date($rs->date_from, $dateFormat) : NULL,
					'dateEnd' => $rs->date_end ? sg_date($rs->date_end, $dateFormat) : NULL,
					'budgetRequest' => '',
					'budgetGrant' => $rs->budget,
					'dateApprove' => $rs->date_approve ? sg_date($rs->date_approve, $dateFormat) : NULL,
					'dateReport' => $rs->date_toreport ? sg_date($rs->date_toreport, $dateFormat) : NULL,
					'coordinatorName' => $rs->prowner,
					'coordinatorPhone' => $rs->prphone,
					'targetGroup' => $rs->targetGroup,
					'targetSize' => $rs->targetSize,
					'targetActivity' => $rs->targetActivity,
					'status' => $rs->project_status,
				];
			} else {
				$result->items[] = [
					'fundId' => $rs->fundId,
					'areaId' => $rs->areaId,
					'projectId' => $rs->projectId,
					'title' => $rs->title,
					'budget' => $rs->budget,
					'budgetYear' => $rs->budgetYear,
					'projectType' => $rs->projectType,
					'areaCode' => $rs->areaCode,
				];
			}
		}

		if ($resultType == 'autocomplete') {
			if ($projectDbs->count() == $items) $result[] = array('value' => '...','label' => '+++ ยังมีอีก +++');
			if ($debug) {
				$result[] = array('value' => 'query','label' => $dbs->_query);
				$result[] = array('value' => 'num_rows','label' => 'Result is '.$dbs->_num_rows.' row(s).');
			}
		} else if ($resultType == 'excel') {
			// $rows = SG\json_decode($result)->items;
			$tables = new Table([
				'thead' => array_keys(reset($result->items)),
				'children' => $result->items,
			]);
			// die(R::Model('excel.export',$tables,'โครงการ-'.date('Y-m-d-H-i-s').'.xls','{debug:false}'));
		}

		return $result;
	}
}
?>