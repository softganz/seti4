<?php
/**
* Project API :: Follow Summary and List
* Created 2020-05-17
* Modify  2020-05-21
*
* @param Object $self
* @param Array $REQUEST
* @return JSON
*/

$debug = true;

function project_api_follow_summary($self) {
	$getChangwat = SG\getFirst(post('changwat'),post('p'));
	$getAmpur = SG\getFirst(post('ampur'), post('a'));
	$getTambon = SG\getFirst(post('tambon'), post('t'));
	$getVillage = SG\getFirst(post('village'),post('v'));
	$getReportType = SG\getFirst(post('repottype'), post('r'),'area');
	$getOrderBy = SG\getFirst(post('order'), post('o'), 'label');
	$getIncludeNotSpec = post('incna');

	$filterChangwat = post('for_changwat');
	$filterSet = post('for_set');
	$filterYear = post('for_year');
	$filterNew = post('for_new');
	$filterOrg = post('for_org');
	$filterGoal10Year = post('for_goal10yr');
	$filterGoal3Year = post('for_goal3yr');
	$filterMt5 = post('for_mt5');
	$filterIssue = post('for_issue');
	$filterTargetGroup = post('for_targetgroup');

	$getDetail = post('detail');

	$isAdmin = user_access('administer imeds');
	$isDebug = user_access('access debugging program') && post('debug');

	$result = new stdClass();
	$result->title = '';
	$result->debug = $isDebug;
	$result->total = NULL;
	$result->summaryFields = array(
		'label' => '',
		'project' => 'โครงการ',
		'percentProject' => '%',
		'budget' => 'งบประมาณ',
		'percentBudget' => '%',
		'expense' => 'จ่าย',
		'percentExpense' => '%',
	);
	$result->summary = array();
	if ($getDetail) {
		$result->itemsFields = array(
			'title' => 'ชื่อโครงการ',
			'label' => 'Label',
			'pryear' => 'ปี',
			'budget' => 'งบประมาณ',
			'activities' => 'กิจกรรม(ตามแผน)',
			'actions' => 'กิจกรรมในพื้นที่(ทำแล้ว)',
			'created' => 'วันที่',
		);
		$result->items = array();
	}

	if ($isDebug) {
		$result->process = array();
		$urlQueryString = post();
		array_shift($urlQueryString);
		$result->process[] = 'URL = '._DOMAIN.$_SERVER['REQUEST_URI'].'?'.sg_implode_attr($urlQueryString,'&', '{quote: ""}');
	}

	$orderList = array(
		'title' => 'ชื่อ:title',
		'regdate' => 'วันที่จดทะเบียน:d.regdate',
		'create' => 'วันที่ป้อน:d.created',
		'tambon' => 'ตำบล:p.tambon',
		'village' => 'หมู่บ้าน:p.village+0',
		'age' => 'อายุ:p.birth',
		'label'=>'ป้ายรายงาน:label'
	);

	list(,$listOrderBy) = explode(':',$orderList[$getOrderBy]);

	$cfg['from'] = '%project% p';
	$cfg['joins'][] = 'LEFT JOIN %topic% t USING(`tpid`)';

	mydb::where('p.`prtype` = "โครงการ"');

	/*
	if ($getChangwat) mydb::where('LEFT(r.`areacode`,2) = :prov', ':prov', $getChangwat);
	if ($getAmpur) mydb::where('SUBSTRING(r.`areacode`,3,2) = :ampur', ':ampur', $getAmpur);
	if ($getTambon) mydb::where('SUBSTRING(r.`areacode`,5,2) = :tambon', ':tambon', $getTambon);
	if ($getVillage) mydb::where('SUBSTRING(r.`areacode`,7,2) = :village', ':village', $getVillage);
	*/

	if ($filterChangwat && $filterChangwat != -1) mydb::where('(LEFT(t.`areacode`, 2) = :filterChangwat)', ':filterChangwat', 'SET:'.implode(',',$filterChangwat));

	if ($filterYear && $filterYear != -1) mydb::where('p.`pryear` IN ( :filterYear )', ':filterYear', 'SET:'.implode(',',$filterYear));

	if ($filterSet && $filterSet != -1) mydb::where('t.`parent` IN ( :projectset )', ':projectset', 'SET:'.implode(',',$filterSet));

	if ($filterNew && $filterNew != -1) {
		mydb::where('forNew.`flddata` IN ( :filterNew )', ':filterNew', 'SET-STRING:'.implode(',',$filterNew));
		$cfg['joins'][]='	LEFT JOIN %bigdata% forNew ON forNew.`keyname` = "project.info" and forNew.`keyid` = p.`tpid` AND forNew.`fldname` = "project-type" AND forNew.`flddata` IN ( :filterNew )';
	}

	if ($filterOrg && $filterOrg != -1) {
		mydb::where('forOrg.`flddata` IN ( :filterOrg )', ':filterOrg', 'SET:'.implode(',',$filterOrg));
		$cfg['joins'][]='	LEFT JOIN %bigdata% forOrg ON forOrg.`keyname` = "project.info" and forOrg.`keyid` = p.`tpid` AND forOrg.`fldname` = "org-type" AND forOrg.`flddata` IN ( :filterOrg)';
	}

	if ($filterGoal10Year && $filterGoal10Year != -1) {
		mydb::where('forGoal10Year.`flddata` IN ( :filterGoal10Year )', ':filterGoal10Year', 'SET:'.implode(',',$filterGoal10Year));
		$cfg['joins'][]='	LEFT JOIN %bigdata% forGoal10Year ON forGoal10Year.`keyname` = "project.info" and forGoal10Year.`keyid` = p.`tpid` AND forGoal10Year.`fldname` LIKE "goal10year-%" AND forGoal10Year.`flddata` IN ( :filterGoal10Year)';
	}

	if ($filterGoal3Year && $filterGoal3Year != -1) {
		mydb::where('forGoal3Year.`flddata` IN ( :filterGoal3Year )', ':filterGoal3Year', 'SET:'.implode(',',$filterGoal3Year));
		$cfg['joins'][]='	LEFT JOIN %bigdata% forGoal3Year ON forGoal3Year.`keyname` = "project.info" and forGoal3Year.`keyid` = p.`tpid` AND forGoal3Year.`fldname` LIKE "goal3year-%" AND forGoal3Year.`flddata` IN ( :filterGoal3Year)';
	}

	if ($filterMt5 && $filterMt5 != -1) {
		mydb::where('forMt5.`flddata` IN ( :filterMt5 )', ':filterMt5', 'SET:'.implode(',',$filterMt5));
		$cfg['joins'][]='	LEFT JOIN %bigdata% forMt5 ON forMt5.`keyname` = "project.info" and forMt5.`keyid` = p.`tpid` AND forMt5.`fldname` LIKE "section5-%" AND forMt5.`flddata` IN ( :filterMt5)';
	}

	if ($filterIssue && $filterIssue != -1) {
		mydb::where('forIssue.`flddata` IN ( :filterIssue )', ':filterIssue', 'SET:'.implode(',',$filterIssue));
		$cfg['joins'][]='	LEFT JOIN %bigdata% forIssue ON forIssue.`keyname` = "project.info" and forIssue.`keyid` = p.`tpid` AND forIssue.`fldname` LIKE "category-%" AND forIssue.`flddata` IN ( :filterIssue)';
	}

	if ($filterTargetGroup && $filterTargetGroup != -1) {
		mydb::where('forTargetGroup.`tgtid` IN ( :filterTargetGroup )', ':filterTargetGroup', 'SET:'.implode(',',$filterTargetGroup));
		$cfg['joins'][]='	LEFT JOIN %project_target% forTargetGroup ON forTargetGroup.`tpid` = p.`tpid` AND forTargetGroup.`tagname` = "info" AND forTargetGroup.`tgtid` IN ( :filterTargetGroup)';
	}

	/*
	if ($filterLandSize && $filterLandSize != -1) {
		switch ($filterLandSize) {
			case '1':
				mydb::where('r.`rai` < 1');
				break;
			case '9':
				mydb::where('r.`rai` BETWEEN 1 AND 9');
				break;
			case '49':
				mydb::where('r.`rai` BETWEEN 10 AND 49');
				break;
			case '50':
				mydb::where('r.`rai` >= 50');
				break;
		}
	}

	if ($filterADL && $filterADL != -1) {
		mydb::where('( forADL.`part` IS NOT NULL AND forADL.`part` = "ELDER.GROUP" AND forADL.`value` IN ( :filterADL ) )', ':filterADL', 'SET:'.$filterADL);
		$cfg['joins'][]='	LEFT JOIN %imed_qt% forADL ON forADL.`pid` = p.`psnid` AND forADL.`part` = "ELDER.GROUP" AND forADL.`value` IN ( :filterADL )';
	}
	*/

	switch ($getReportType) {
		case 'set':
			$cfg['caption'] = 'จำนวนติดตามโครงการจำแนกตามชุดโครงการ';
			$cfg['labelName'] = 'ชุดโครงการ';
			$cfg['label'] = 'ts.`title`';
			$cfg['joins'][] = 'LEFT JOIN %topic% ts ON t.`parent` = ts.`tpid`';
			break;

		case 'year':
			$cfg['caption'] = 'จำนวนติดตามโครงการจำแนกปี';
			$cfg['labelName'] = 'ปี';
			$cfg['label'] = 'CAST(p.`pryear` AS CHAR)';
			break;

		case 'new' :
			$cfg['caption']='จำนวนติดตามโครงการจำแนกตามเนื่องของโครงการ';
			$cfg['labelName'] = 'ความต่อเนื่อง';
			$cfg['label'] = 'pnew.`flddata`';
			$cfg['joins'][] = 'LEFT JOIN %bigdata% pnew ON pnew.`keyname` = "project.info" and pnew.`keyid` = p.`tpid` AND pnew.`fldname` = "project-type"';
			break;

		case 'org' :
			$cfg['caption']='จำนวนติดตามโครงการจำแนกตามประเภทองค์กร';
			$cfg['labelName'] = 'ประเภทองค์กร';
			$cfg['label'] = 'labelcat.`name`';
			$cfg['joins'][] = 'LEFT JOIN %bigdata% porg ON porg.`keyname` = "project.info" and porg.`keyid` = p.`tpid` AND porg.`fldname` = "org-type"';
			$cfg['joins'][] = 'LEFT JOIN %tag% labelcat ON labelcat.`taggroup` = "project:orgtype" AND labelcat.`catid` = porg.`flddata`';
			break;

		case 'goal10yr':
			$cfg['caption']='จำนวนติดตามโครงการจำแนกตามเป้าหมาย แผนยุทธศาสตร์ระยะ 10 ปี';
			$cfg['labelName'] = 'เป้าหมาย แผนยุทธศาสตร์ระยะ 10 ปี';
			$cfg['label'] = 'labelcat.`name`';
			$cfg['joins'][] = '	LEFT JOIN %bigdata% porg ON porg.`keyname` = "project.info" and porg.`keyid` = p.`tpid` AND porg.`fldname` LIKE "goal10year-%"';
			$cfg['joins'][] = '	LEFT JOIN %tag% labelcat ON labelcat.`taggroup` = "project:goal10yr" AND labelcat.`catid` = porg.`flddata`';
			break;

		case 'goal3yr':
			$cfg['caption']='จำนวนติดตามโครงการจำแนกตามเป้าหมาย แผนหลัก 3 ปี ของ สสส.';
			$cfg['labelName'] = 'เป้าหมาย แผนหลัก 3 ปี ของ สสส.';
			$cfg['label'] = 'labelcat.`name`';
			$cfg['joins'][] = 'LEFT JOIN %bigdata% porg ON porg.`keyname` = "project.info" and porg.`keyid` = p.`tpid` AND porg.`fldname` LIKE "goal3year-%"';
			$cfg['joins'][] = 'LEFT JOIN %tag% labelcat ON labelcat.`taggroup` = "project:goal3yr" AND labelcat.`catid` = porg.`flddata`';
			break;

		case 'issue':
			$cfg['caption']='จำนวนติดตามโครงการจำแนกตามประเด็นที่เกี่ยวข้องกับโครงการ';
			$cfg['labelName'] = 'ประเด็นที่เกี่ยวข้องกับโครงการ';
			$cfg['label'] = 'labelcat.`name`';
			$cfg['joins'][] = 'LEFT JOIN %bigdata% porg ON porg.`keyname` = "project.info" and porg.`keyid` = p.`tpid` AND porg.`fldname` LIKE "category-%"';
			$cfg['joins'][] = 'LEFT JOIN %tag% labelcat ON labelcat.`taggroup` = "project:issue" AND labelcat.`catid` = porg.`flddata`';
			break;

		case 'mt5':
			$cfg['caption']='จำนวนติดตามโครงการจำแนกตามมาตรา 5 "วัตถุประสงค์" การจัดตั้งกองทุนสนับสนุนการสร้างเสริมสุขภาพ';
			$cfg['labelName'] = 'มาตรา 5 "วัตถุประสงค์" การจัดตั้งกองทุนสนับสนุนการสร้างเสริมสุขภาพ';
			$cfg['label'] = 'labelcat.`name`';
			$cfg['joins'][] = 'LEFT JOIN %bigdata% porg ON porg.`keyname` = "project.info" and porg.`keyid` = p.`tpid` AND porg.`fldname` LIKE "section5-%"';
			$cfg['joins'][] = 'LEFT JOIN %tag% labelcat ON labelcat.`taggroup` = "project:mt5" AND labelcat.`catid` = porg.`flddata`';
			break;

		case 'targetgroup':
			$cfg['caption']='จำนวนติดตามโครงการจำแนกตามกลุ่มเป้าหมาย';
			$cfg['labelName'] = 'กลุ่มเป้าหมาย';
			$cfg['label'] = 'labelcat.`name`';
			$cfg['joins'][] = 'LEFT JOIN %project_target% tgt ON tgt.`tpid` = p.`tpid` and tgt.`tagname` = "info"';
			$cfg['joins'][] = 'LEFT JOIN %tag% labelcat ON labelcat.`taggroup` = "project:target" AND labelcat.`catid` = tgt.`tgtid`';
			break;

		/*
		case 'landsize' :
			$cfg['caption']='จำนวนเกษตรกรจำแนกตามขนาดแปลง';
			$cfg['label']='CASE
					WHEN `rai` < 1 THEN " < 1 ไร่"
					WHEN `rai` BETWEEN 1 and 9 THEN "1 - 9 ไร่"
					WHEN `rai` BETWEEN 10 and 49 THEN "10 - 49 ไร่"
					WHEN `rai` >= 50 THEN ">= 50 ไร่"
					WHEN `rai` IS NULL THEN NULL
				END';
			break;

		case 'age' :
			$cfg['caption']='จำนวนผู้สูงอายุแต่ละช่วงอายุ';
			$cfg['label']='CASE
					WHEN TIMESTAMPDIFF(YEAR, `birth`, CURDATE()) < 50 THEN " < 50 ปี"
					WHEN TIMESTAMPDIFF(YEAR, `birth`, CURDATE()) BETWEEN 50 and 54 THEN "50 - 54 ปี"
					WHEN TIMESTAMPDIFF(YEAR, `birth`, CURDATE()) BETWEEN 55 and 59 THEN "55 - 59 ปี"
					WHEN TIMESTAMPDIFF(YEAR, `birth`, CURDATE()) BETWEEN 60 and 69 THEN "60 - 69 ปี"
					WHEN TIMESTAMPDIFF(YEAR, `birth`, CURDATE()) BETWEEN 70 and 79 THEN "70 - 79 ปี"
					WHEN TIMESTAMPDIFF(YEAR, `birth`, CURDATE()) BETWEEN 80 and 89 THEN "80 - 89 ปี"
					WHEN TIMESTAMPDIFF(YEAR, `birth`, CURDATE()) >= 90 THEN "90 ปีขึ้นไป"
					WHEN TIMESTAMPDIFF(YEAR, `birth`, CURDATE()) IS NULL THEN NULL
				END';
			break;
		*/

		default :
			$cfg['caption'] = 'จำนวนติดตามโครงการจำแนกตามพื้นที่';
			if ($getTambon) {
				$cfg['labelName'] = 'หมู่บ้าน';
				$cfg['label'] = 'CONCAT("หมู่ ",dv.`villno`," - ",dv.`villname`)';
				$cfg['joins'][] = 'LEFT JOIN %co_village% dv ON dv.`villid` = LEFT(t.`areacode`,8)';
			} else if ($getAmpur) {
				$cfg['labelName'] = 'ตำบล';
				$cfg['label'] = 'dd.`subdistname`';
				$cfg['joins'][] = 'LEFT JOIN %co_subdistrict% dd ON dd.`subdistid` = LEFT(t.`areacode`,6)';
			} else if ($getChangwat) {
				$cfg['labelName'] = 'อำเภอ';
				$cfg['label'] = 'cod.`distname`';
				$cfg['joins'][] = 'LEFT JOIN %co_district% cod ON cod.`distid` = LEFT(t.`areacode`,4)';
			} else {
				$cfg['labelName'] = 'จังหวัด';
				$cfg['label'] = 'cop.`provname`';
				$cfg['joins'][] = 'LEFT JOIN %co_province% cop ON cop.`provid` = LEFT(t.`areacode`, 2)';
			}
			break;
	}

	mydb::value('$FIELDS$', ($sql_fields ? implode(', ',$sql_fields).', ' : '').$cfg['label'].' `label`', false);
	mydb::value('$JOINS$', $cfg['joins'] ? implode(_NL,$cfg['joins']) : '', false);
	mydb::value('$FROM$', 'FROM '.$cfg['from'], false);
	mydb::value('$ORDER$', 'ORDER BY '.($listOrderBy ? '`label` IS NULL, CONVERT(`label` USING tis620) ASC' : '`totalProject` DESC'));

	if (!$stmt) {
		$stmt = 'SELECT
			$FIELDS$
			, COUNT(*) `totalProject`
			, CAST(SUM(p.`budget`) as SIGNED) `totalBudget`
			, (SELECT SUM(`num7`) FROM %project_tr% WHERE `tpid` = p.`tpid` AND `formid` = "activity" AND `part` = "owner") `totalExpense`
			$FROM$
				$JOINS$
			%WHERE%
			GROUP BY `label`
			$ORDER$;
			-- {reset: false, sum: "totalProject,totalBudget,totalExpense"}';
	}

	$dbs = mydb::select($stmt);

	$result->title = $cfg['caption'];
	$result->summaryFields['label'] = $cfg['labelName'];

	if ($isDebug) {
		$result->query = str_replace("\t", ' ', mydb()->_query);
		$result->process[] = '<pre>'.str_replace("\t", ' ', mydb()->_query).'</pre>';
	}


	foreach ($dbs->items as $rs) {
		if (!$getIncludeNotSpec && (is_null($rs->label) || $rs->label == '' || $rs->label == 'ไม่ระบุ')) continue;
		$total += $rs->totalProject;
	}

	$result->total->project = $dbs->sum->totalProject;
	$result->total->budget = $dbs->sum->totalBudget;
	$result->total->expense = $dbs->sum->totalExpense;

	foreach ($dbs->items as $rs) {
		if (!$getIncludeNotSpec && (is_null($rs->label) || $rs->label == '' || $rs->label == 'ไม่ระบุ')) continue;

		unset($row);
		if ($getReportType == 'qt') {
			if ($qtProp['option']) {
				$options=is_string($qtProp['option']) ? explode(',', $qtProp['option']) : $qtProp['option'];
				foreach ($options as $key => $value) {
					if (strpos($value, ':')) list($key,$value)=explode(':', $value);
					$labels[trim($key)]=trim($value);
				}
				//$ret.='label='.$rs->label.' key='.$key.' value='.$value.'<br />'.print_o($labels,'$labels');
				$label=SG\getFirst($labels[$rs->label],'ไม่ระบุ');
			} else {
				$label=SG\getFirst($rs->label,'ไม่ระบุ');
			}
		} else {
			$label=SG\getFirst($rs->label,'ไม่ระบุ');
		}

		$result->summary[] = (Object) array(
			'label' => $label,
			'project' => $rs->totalProject,
			'percentProject' => round(100*$rs->totalProject/$dbs->sum->totalProject,2),
			'budget' => floatval($rs->totalBudget),
			'percentBudget' => round(100*$rs->totalBudget/$dbs->sum->totalBudget,2),
			'expense' => $rs->totalExpense > 0 ? $rs->totalExpense : NULL,
			'percentExpense' => $rs->totalExpense > 0 && $dbs->sum->totalExpense > 0 ? round(100*$rs->totalExpense/$dbs->sum->totalExpense,2) : NULL,
		);

	}

	if ($isDebug) $result->process[] = print_o($urlQueryString, 'post');


	if (!$getDetail) return $result;
	if ($dbs->count() == 0) return $result;



	if (empty($listOrderBy)) $listOrderBy = 't.`title`';
	if ($listOrderBy && in_array($listOrderBy,array('t.`title`','`label`'))) {
		$listOrderBy='CONVERT ('.$listOrderBy.' USING tis620)';
	}

	mydb::value('$FIELDS$', $cfg['label'].' `label`', false);
	mydb::value('$ORDER$', 'ORDER BY '.$listOrderBy.' ASC');

	$stmt = 'SELECT
		t.*
		, p.`pryear`
		, p.`agrno`
		, p.`project_status`
		, p.`project_status` + 0 `project_statuscode`
		, p.`budget`
		, FROM_UNIXTIME((SELECT MAX(`created`) FROM %project_tr% lr WHERE lr.`tpid` = t.`tpid` AND formid = "activity"),"%Y-%m-%d %H:%i:%s") `last_report`
		,	(SELECT COUNT(*) FROM %project_tr% otr WHERE otr.`tpid` = t.`tpid` AND otr.`formid` = "info" AND otr.`part` = "activity") `activities`
		,	(SELECT COUNT(*) FROM %project_tr% WHERE `tpid` = t.`tpid` AND `formid` = "activity" AND `part` = "owner") `actions`

		FROM
			(
				SELECT
				  p.`tpid`, t.`title`
				, $FIELDS$
				, DATE_FORMAT(t.`created`, "%Y-%m-%d") `created`
				$FROM$
					$JOINS$
				--	LEFT JOIN %co_province% copv ON copv.`provid` = LEFT(r.`areacode`,2)
				--	LEFT JOIN %co_district% codist ON codist.`distid` = LEFT(r.`areacode`,4)
				--	LEFT JOIN %co_subdistrict% cosd ON cosd.`subdistid` = LEFT(r.`areacode`,6)
				%WHERE%
				GROUP BY `tpid`
				$ORDER$
			) t
			LEFT JOIN %project% p USING(`tpid`)
	';

	$nameDbs = mydb::select($stmt);

	$result->process[] = '<pre>'.str_replace("\t", ' ', mydb()->_query).'</pre>';

	foreach ($nameDbs->items as $key => $rs) {
		$nameDbs->items[$key]->config->link = (Object) Array(
			'field' => 'title',
			'href' => url('project/'.$rs->tpid.'/info.short'),
			'attr' => Array(
				'class' => 'sg-action',
				'data-rel' => 'box',
			),
		);
		//$nameDbs->items[$key]->address = SG\implode_address($rs, 'short').($rs->commune?'<br /><strong>'.$rs->commune.'</strong>':'');
	}

	$showFields = 'no:ลำดับ,fullname:ชื่อ-สกุล,address:ที่อยู่,label,created:วันที่เพิ่มข้อมูล';

	$result->items = $nameDbs->items;

	//$ret .= R::View('imed.report.name.list',$nameDbs,'รายชื่อเกษตรกร',array('prov'=>$getChangwat,'ampur'=>$getAmpur,'tambon'=>$getTambon,'village'=>$getVillage,'show'=>'yes'),$showFields,$cfg['thead'][0]);


	return $result;
}
?>