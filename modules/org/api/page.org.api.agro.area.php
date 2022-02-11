<?php
/**
* Agro Register API
* Created 2020-05-17
* Modify  2020-05-21
*
* @param Object $self
* @return JSON
*/

$debug = true;

function org_api_agro_area($self) {
	$getChangwat = SG\getFirst(post('changwat'),post('p'));
	$getAmpur = SG\getFirst(post('ampur'), post('a'));
	$getTambon = SG\getFirst(post('tambon'), post('t'));
	$getVillage = SG\getFirst(post('village'),post('v'));
	$getReportType = SG\getFirst(post('repottype'), post('r'),'area');
	$getOrderBy = SG\getFirst(post('order'), post('o'), 'label');
	$getIncludeNotSpec = post('incna');

	$filterType = post('for_type');
	$filterProduct = post('for_product');
	$filterStandard = post('for_standard');
	$filterLandSize = post('for_landsize');

	$getDetail = post('detail');

	$isAdmin = user_access('administer imeds');
	$isDebug = user_access('access debugging program') && post('debug');

	$result = new stdClass();
	$result->title = '';
	$result->total = 0;
	$result->fields = array();
	$result->items = array();
	if ($getDetail) {
		$result->nameFilds = array();
		$result->name = array();
	}

	if ($isDebug) {
		$result->process = array();
		$urlQueryString = post();
		array_shift($urlQueryString);
		$result->process[] = 'URL = '._DOMAIN.$_SERVER['REQUEST_URI'].'?'.sg_implode_attr($urlQueryString,'&', '{quote: ""}');
	}

	$orderList = array(
		'name' => 'ชื่อ:name',
		'regdate' => 'วันที่จดทะเบียน:d.regdate',
		'create' => 'วันที่ป้อน:d.created',
		'tambon' => 'ตำบล:p.tambon',
		'village' => 'หมู่บ้าน:p.village+0',
		'age' => 'อายุ:p.birth',
		'label'=>'ป้ายรายงาน:label'
	);

	list(,$listOrderBy) = explode(':',$orderList[$getOrderBy]);

	$cfg['from'] = '%agro_reg% r';

	if ($getChangwat) mydb::where('LEFT(r.`areacode`,2) = :prov', ':prov', $getChangwat);
	if ($getAmpur) mydb::where('SUBSTRING(r.`areacode`,3,2) = :ampur', ':ampur', $getAmpur);
	if ($getTambon) mydb::where('SUBSTRING(r.`areacode`,5,2) = :tambon', ':tambon', $getTambon);
	if ($getVillage) mydb::where('SUBSTRING(r.`areacode`,7,2) = :village', ':village', $getVillage);


	/*
	if ($getDefect > 0) {
		mydb::where('ddf.`defect` + 0 = :defect', ':defect', $getDefect);
		$cfg['joins'][] = 'LEFT JOIN %imed_disabled_defect% ddf ON ddf.`pid` = d.`pid`';
	}
	*/

	if ($filterType && $filterType != -1) mydb::where('r.`producttype` = :producttype', ':producttype', $filterType);

	if ($filterProduct && $filterProduct != -1) mydb::where('r.`productname` = :productname', ':productname', $filterProduct);

	if ($filterStandard && $filterStandard != -1) mydb::where('r.`standard` = :standard', ':standard', $filterStandard);

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

	/*
	if ($filterADL && $filterADL != -1) {
		mydb::where('( forADL.`part` IS NOT NULL AND forADL.`part` = "ELDER.GROUP" AND forADL.`value` IN ( :filterADL ) )', ':filterADL', 'SET:'.$filterADL);
		$cfg['joins'][]='	LEFT JOIN %imed_qt% forADL ON forADL.`pid` = p.`psnid` AND forADL.`part` = "ELDER.GROUP" AND forADL.`value` IN ( :filterADL )';
	}
	*/

	switch ($getReportType) {
		case 'type':
			$cfg['caption'] = 'จำนวนเกษตรกรจำแนกตามประเภทผลผลิต';
			$cfg['thead'] = array('ประเภทผลผลิต','จำนวน(แปลง)');
			$cfg['label'] = 'r.`producttype`';
			break;

		case 'product' :
			$cfg['caption']='จำนวนเกษตรกรจำแนกตามผลผลิต';
			$cfg['thead']=array('ผลผลิต','จำนวน(แปลง)');
			$cfg['label']='r.`productname`';
			break;

		case 'standard' :
			$cfg['caption']='จำนวนเกษตรกรจำแนกตามมาตรฐาน';
			$cfg['thead']=array('มาตรฐาน','จำนวน(แปลง)');
			$cfg['label']='r.`standard`';
			break;

		case 'landsize' :
			$cfg['caption']='จำนวนเกษตรกรจำแนกตามขนาดแปลง';
			$cfg['thead']=array('ขนาดแปลง','จำนวน(แปลง)');
			$cfg['label']='CASE
					WHEN `rai` < 1 THEN " < 1 ไร่"
					WHEN `rai` BETWEEN 1 and 9 THEN "1 - 9 ไร่"
					WHEN `rai` BETWEEN 10 and 49 THEN "10 - 49 ไร่"
					WHEN `rai` >= 50 THEN ">= 50 ไร่"
					WHEN `rai` IS NULL THEN NULL
				END';
			break;

		/*
		case 'age' :
			$cfg['caption']='จำนวนผู้สูงอายุแต่ละช่วงอายุ';
			$cfg['thead']=array('ช่วงอายุ','จำนวน(แปลง)');
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
			$cfg['caption'] = 'จำนวนเกษตรกรในพื้นที่';
			if ($getTambon) {
				$cfg['thead'] = array('หมู่บ้าน','จำนวน(แปลง)');
				$cfg['label'] = 'CONCAT("หมู่ ",dv.`villno`," - ",dv.`villname`)';
				$cfg['joins'][] = 'LEFT JOIN %co_village% dv ON dv.`villid` = LEFT(r.`areacode`,8)';
			} else if ($getAmpur) {
				$cfg['thead'] = array('ตำบล','จำนวน(แปลง)');
				$cfg['label'] = 'dd.`subdistname`';
				$cfg['joins'][] = 'LEFT JOIN %co_subdistrict% dd ON dd.`subdistid` = LEFT(r.`areacode`,6)';
			} else if ($getChangwat) {
				$cfg['thead'] = array('อำเภอ','จำนวน(แปลง)');
				$cfg['label'] = 'cod.`distname`';
				$cfg['joins'][] = 'LEFT JOIN %co_district% cod ON cod.`distid` = LEFT(r.`areacode`,4)';
			} else {
				$cfg['thead'] = array('จังหวัด','จำนวน(แปลง)');
				$cfg['label'] = 'cop.`provname`';
				$cfg['joins'][] = 'LEFT JOIN %co_province% cop ON cop.`provid` = LEFT(r.`areacode`,2)';
			}
			break;
	}

	mydb::value('$FIELDS$', ($sql_fields ? implode(', ',$sql_fields).', ' : '').$cfg['label'].' `label`', false);
	mydb::value('$JOINS$', $cfg['joins'] ? implode(_NL,$cfg['joins']) : '', false);
	mydb::value('$FROM$', 'FROM '.$cfg['from'], false);
	mydb::value('$ORDER$', 'ORDER BY '.($listOrderBy ? '`label` IS NULL, CONVERT(`label` USING tis620) ASC' : '`totalItemValue` DESC'));

	if (!$stmt) {
		$stmt = 'SELECT
			$FIELDS$
			, COUNT(*) `totalItemValue`
			$FROM$
				$JOINS$
			%WHERE%
			GROUP BY `label`
			$ORDER$;
			-- {reset: false}';
	}

	$dbs = mydb::select($stmt);

	$result->title = $cfg['caption'];
	$result->fields = array($cfg['thead'][0], $cfg['thead'][1],'%');

	if ($isDebug) {
		$result->query = str_replace("\t", ' ', mydb()->_query);
		$result->process[] = '<pre>'.str_replace("\t", ' ', mydb()->_query).'</pre>';
	}

	/*
	include_once(dirname(__FILE__).'/../default/qt.elder.php');

	foreach (explode("\n", $qtText) as $key) {
		$key = trim($key);
		if (empty($key)) continue;
		if (strpos($key,',')) {
			$jsonStr = '{'.$key.'}';
			$json = json_decode($jsonStr,true);
			if ($json) {
				$key = $json['key'];
				$json['label'] = SG\getFirst($json['label'],$key);
				$json['group'] = 'qt';
				unset($json['key']);
				$qt[$key] = $json;
			}
		} else {
			$qt[$key] = array('label' => $value, 'type' => 'text', 'group' => 'qt', 'class' => 'w-5');
		}
	}
	$qtProp = $qt[$fieldQt];

	//$result->process[] = print_o($qtProp, '$qtProp');
	//$result->process[] = print_o($qt, '$qt');
	*/


	foreach ($dbs->items as $rs) {
		if (!$getIncludeNotSpec && (is_null($rs->label) || $rs->label == '' || $rs->label == 'ไม่ระบุ')) continue;
		$total += $rs->totalItemValue;
	}

	if ($total) $result->total = $total;

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

		$result->items[] = (Object) array(
			'label' => $label,
			'value' => $rs->totalItemValue,
			'percent' => round(100*$rs->totalItemValue/$total,2),
		);

	}

	if ($isDebug) $result->process[] = print_o($urlQueryString, 'post');


	if (!$getDetail) return $result;
	if ($dbs->count() == 0) return $result;



	if (empty($listOrderBy)) $listOrderBy = 'name';
	if ($listOrderBy && in_array($listOrderBy,array('name','label'))) {
		$listOrderBy='CONVERT (`'.$listOrderBy.'` USING tis620)';
	}


	if ($isAdmin) {
		// Get all name
	} else if ($zones) {
		mydb::where('('.'p.`uid`=:uid OR '.R::Model('imed.person.zone.condition',$zones).')',':uid',i()->uid);
	} else {
		mydb::where('p.`uid` = :uid',':uid',i()->uid);
	}

	mydb::value('$FIELDS$', $cfg['label'].' `label`', false);
	mydb::value('$ORDER$', 'ORDER BY '.$listOrderBy.' ASC');

	/*
		c.`pid` `psnid`
		, p.`prename`, p.`name`, p.`lname`
		, CONCAT(IFNULL(`prename`,"")," ",`name`," ",`lname`) fullname
		, p.`birth` age
		, p.`commune`
		, p.`house`, p.`village`, cosd.`subdistname`, copv.`provname`, codist.`distname`
		, NULL `address`
		, CONCAT(p.`changwat`, p.`ampur`, p.`tambon`, p.`village`) `areacode`
		*/
	$stmt = 'SELECT
		  r.*
		, CONCAT(IFNULL(r.`prename`,"")," ",r.`name`," ",r.`lname`) `fullname`
		, r.`house`, r.`village`, cosd.`subdistname`, copv.`provname`, codist.`distname`
		, $FIELDS$
		, FROM_UNIXTIME(r.`created`, "%Y-%m-%d") `created`
		$FROM$
			$JOINS$
			LEFT JOIN %co_province% copv ON copv.`provid` = LEFT(r.`areacode`,2)
			LEFT JOIN %co_district% codist ON codist.`distid` = LEFT(r.`areacode`,4)
			LEFT JOIN %co_subdistrict% cosd ON cosd.`subdistid` = LEFT(r.`areacode`,6)
		%WHERE%
		$ORDER$
		';

	$nameDbs = mydb::select($stmt);

	$result->process[] = '<pre>'.str_replace("\t", ' ', mydb()->_query).'</pre>';

	foreach ($nameDbs->items as $key => $rs) {
		$nameDbs->items[$key]->href = url('org/agro/info/'.$rs->aid);
		$nameDbs->items[$key]->address = SG\implode_address($rs, 'short').($rs->commune?'<br /><strong>'.$rs->commune.'</strong>':'');
	}

	$showFields = 'no:ลำดับ,fullname:ชื่อ-สกุล,address:ที่อยู่,label,created:วันที่เพิ่มข้อมูล';

	$result->name = $nameDbs->items;

	//$ret .= R::View('imed.report.name.list',$nameDbs,'รายชื่อเกษตรกร',array('prov'=>$getChangwat,'ampur'=>$getAmpur,'tambon'=>$getTambon,'village'=>$getVillage,'show'=>'yes'),$showFields,$cfg['thead'][0]);


	return $result;
}
?>