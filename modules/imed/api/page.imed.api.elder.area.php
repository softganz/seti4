<?php
/**
* Module Method
* Created 2019-12-01
* Modify  2019-12-01
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function imed_api_elder_area($self) {
	$getChangwat = SG\getFirst(post('changwat'),post('p'));
	$getAmpur = SG\getFirst(post('ampur'), post('a'));
	$getTambon = SG\getFirst(post('tambon'), post('t'));
	$getVillage = intval(SG\getFirst(post('village'),post('v')));
	$getDefect = SG\getFirst(post('defect'), post('d'));
	$getReportType = SG\getFirst(post('repottype'), post('r'),'amt');
	$getOrderBy = SG\getFirst(post('order'), post('o'), 'label');
	$getIncludeNotSpec = post('incna');

	$filterSex = post('for_sex');
	$filterEducate = SG\getFirst(post('educate'),post('e'));
	$filterADL = post('for_adl');
	$fieldQt = post('qt');

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

	$zones = imed_model::get_user_zone(i()->uid,'imed');

	$orderList=array(
		'name' => 'ชื่อ:name',
		'regdate' => 'วันที่จดทะเบียน:d.regdate',
		'create' => 'วันที่ป้อน:d.created',
		'tambon' => 'ตำบล:p.tambon',
		'village' => 'หมู่บ้าน:p.village+0',
		'age' => 'อายุ:p.birth',
		'label'=>'ป้ายรายงาน:label'
	);

	list(,$listOrderBy) = explode(':',$orderList[$getOrderBy]);

	$cfg['from'] = '%imed_care% c';
	$cfg['joins'][] = 'LEFT JOIN %db_person% p ON p.`psnid` = c.`pid`';

	mydb::where('(c.`careid` = 2 AND c.`status` = 1)');
	if ($getChangwat) mydb::where('p.`changwat` = :prov', ':prov', $getChangwat);
	if ($getAmpur) mydb::where('p.`ampur` = :ampur', ':ampur', $getAmpur);
	if ($getTambon) mydb::where('p.`tambon` = :tambon', ':tambon', $getTambon);
	if ($getVillage) mydb::where('LPAD(p.`village`,2,"0") = :village', ':village', $getVillage);
	if ($getDefect > 0) {
		mydb::where('ddf.`defect` + 0 = :defect', ':defect', $getDefect);
		$cfg['joins'][] = 'LEFT JOIN %imed_disabled_defect% ddf ON ddf.`pid` = d.`pid`';
	}

	if ($filterSex && $filterSex != -1) mydb::where('p.`sex` = :sex', ':sex', $filterSex);
	if ($filterEducate && $filterEducate != -1) mydb::where('p.`educate` = :educate', ':educate', $filterEducate);

	if ($filterADL && $filterADL != -1) {
		mydb::where('( forADL.`part` IS NOT NULL AND forADL.`part` = "ELDER.GROUP" AND forADL.`value` IN ( :filterADL ) )', ':filterADL', 'SET:'.$filterADL);
		$cfg['joins'][]='	LEFT JOIN %imed_qt% forADL ON forADL.`pid` = p.`psnid` AND forADL.`part` = "ELDER.GROUP" AND forADL.`value` IN ( :filterADL )';
	}

	switch ($getReportType) {
		case 'adl':
			$cfg['caption'] = 'จำนวนผู้สูงอายุจำแนก ADL';
			$cfg['thead'] = array('ADL','จำนวน(คน)');
			$cfg['label'] = 'CASE qt.`value`
					WHEN 1 THEN "ติดเตียง"
					WHEN 2 THEN "ติดบ้าน"
					WHEN 3 THEN "ติดสังคม"
					ELSE NULL
				END';
			$cfg['joins'][] = 'LEFT JOIN %imed_qt% qt ON qt.`pid` = c.`pid` AND qt.`part`="ELDER.GROUP"';
			break;

		case 'sex' :
			$cfg['caption']='จำนวนผู้สูงอายุจำแนกตามเพศ';
			$cfg['thead']=array('เพศ','จำนวน(คน)');
			$cfg['label']='p.sex';
			break;

		case 'religion' :
			$cfg['caption']='จำนวนผู้สูงอายุจำแนกตามศาสนา';
			$cfg['thead']=array('ศาสนา','จำนวน(คน)');
			$cfg['label']='cor.reli_desc';
			$cfg['joins'][]='LEFT JOIN %co_religion% cor ON cor.reli_code=p.religion';
			break;

		case 'mstatus' :
			$cfg['caption']='จำนวนผู้สูงอายุจำแนกตามสถานภาพสมรส';
			$cfg['thead']=array('สถานภาพสมรล','จำนวน(คน)');
			$cfg['label']='co.cat_name';
			$cfg['joins'][]='LEFT JOIN %co_category% co ON co.cat_id=p.mstatus';
			break;

		case 'body' :
			$cfg['caption']='จำนวนผู้สูงอายุจำแนกตามดัชนีมวลกาย';
			$cfg['thead']=array('ดัชนีมวลกาย','จำนวน(คน)');
			$cfg['label']='CASE
					WHEN bodyMass.`value` < 18.5 THEN " < 18.5"
					WHEN bodyMass.`value` BETWEEN 18.5 and 22.9 THEN "18.5 - 22.9"
					WHEN bodyMass.`value` BETWEEN 23 and 24.9 THEN "23 - 24.9"
					WHEN bodyMass.`value` BETWEEN 25 and 29.9 THEN "25 - 29.9"
					WHEN bodyMass.`value` > 29.9 THEN ">= 30"
					ELSE NULL
				END';
			$cfg['joins'][]='LEFT JOIN %imed_qt% bodyMass ON bodyMass.`pid` = p.`psnid` AND bodyMass.`part`="ดัชนีมวลกาย"';
			break;

		case 'age' :
			$cfg['caption']='จำนวนผู้สูงอายุแต่ละช่วงอายุ';
			$cfg['thead']=array('ช่วงอายุ','จำนวนคน');
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
			//$cfg['from'] = '(SELECT TIMESTAMPDIFF(YEAR, `birth`, CURDATE()) AS `age` FROM %imed_care% c $JOINS$ %WHERE% ) as `derived`';
				/*
			$stmt = 'SELECT
				CASE
					WHEN age < 50 THEN " < 50 ปี"
					WHEN age BETWEEN 50 and 54 THEN "50 - 54 ปี"
					WHEN age BETWEEN 55 and 59 THEN "55 - 59 ปี"
					WHEN age BETWEEN 60 and 69 THEN "60 - 69 ปี"
					WHEN age BETWEEN 70 and 79 THEN "70 - 79 ปี"
					WHEN age BETWEEN 80 and 89 THEN "80 - 89 ปี"
					WHEN age >= 90 THEN "90 ปีขึ้นไป"
					WHEN age IS NULL THEN NULL
				END as `label`,
				COUNT(*) AS `totalItemValue`
				$FROM$
				GROUP BY `label`
				ORDER BY `label` IS NULL, `label` ASC;
				-- {reset: fasle}';
				*/
			break;

		case 'level' :
			$cfg['caption']='ระดับความพิการ';
			$cfg['thead']=array('ระดับความพิการ','จำนวน(คน)');
			$cfg['label']='dislevel.cat_name';
			$cfg['joins'][]='LEFT JOIN %co_category% dislevel ON dislevel.cat_id=d.disabilities_level';
			break;

		case 'cause' :
			$cfg['caption']='สาเหตุการเกิดพิการ';
			$cfg['thead']=array('สาเหตุ','จำนวน(คน)');
			$cfg['label']='CASE WHEN begetting.cat_name IS NULL OR begetting.cat_name = "" THEN null ELSE begetting.cat_name END';
			$cfg['joins'][]='LEFT JOIN %co_category% begetting ON begetting.cat_id=d.begetting';
			break;

		case 'edu' :
			$cfg['caption']='ระดับการศึกษา';
			$cfg['thead']=array('ระดับการศึกษา','จำนวน(คน)');
			$cfg['label']='coe.edu_desc';
			$cfg['joins'][]='LEFT JOIN %co_educate% coe ON coe.edu_code=p.educate';

			break;

		case 'qt' :
			$cfg['caption']='จำนวนผู้สูงอายุจำแนกตาม '.$fieldQt;
			$cfg['thead']=array($getDetail,'จำนวน(คน)');
			$cfg['label']='qt.value';
			$cfg['joins'][] = 'LEFT JOIN %imed_qt% qt ON qt.`pid` = p.`psnid` AND qt.`part` = :fieldqt';
			mydb::where(NULL, ':fieldqt', $fieldQt);
			break;

		default :
			$cfg['caption'] = 'จำนวนผู้สูงอายุในพื้นที่';
			if ($getTambon) {
				$cfg['thead'] = array('หมู่บ้าน','จำนวน(คน)');
				$cfg['label'] = 'CONCAT("หมู่ ",dv.villno," - ",dv.villname)';
				$cfg['joins'][] = 'LEFT JOIN %co_village% dv ON dv.villid=CONCAT(p.changwat,p.ampur,p.tambon, LPAD(p.village, 2, "0"))';
			} else if ($getAmpur) {
				$cfg['thead'] = array('ตำบล','จำนวน(คน)');
				$cfg['label'] = 'dd.subdistname';
				$cfg['joins'][] = 'LEFT JOIN %co_subdistrict% dd ON dd.subdistid=CONCAT(p.changwat,p.ampur,p.tambon)';
			} else if ($getChangwat) {
				$cfg['thead'] = array('อำเภอ','จำนวน(คน)');
				$cfg['label'] = 'cod.distname';
				$cfg['joins'][] = 'LEFT JOIN %co_district% cod ON cod.distid=CONCAT(p.changwat,p.ampur)';
			} else {
				$cfg['thead'] = array('จังหวัด','จำนวน(คน)');
				$cfg['label'] = 'cop.provname';
				$cfg['joins'][] = 'LEFT JOIN %co_province% cop ON cop.`provid` = p.`changwat`';
			}
			break;
	}

	mydb::value('$FIELDS$', ($sql_fields ? implode(', ',$sql_fields).', ' : '').$cfg['label'].' `label`', false);
	mydb::value('$JOINS$', implode(_NL,$cfg['joins']), false);
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


	include_once 'modules/imed/assets/qt.elder.php';

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

	//$result->nameFilds = array('name','fullname:ชื่อ-สกุล,age:อายุ(ปี),address:ที่อยู่,label,created:วันที่เพิ่มข้อมูล';



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

	$stmt = 'SELECT
		  c.`pid` `psnid`
		, p.`prename`, p.`name`, p.`lname`
		, CONCAT(IFNULL(`prename`,"")," ",`name`," ",`lname`) fullname
		, p.`birth` age
		, p.`commune`
		, p.`house`, p.`village`, cosd.`subdistname`, copv.`provname`, codist.`distname`
		, NULL `address`
		, CONCAT(p.`changwat`, p.`ampur`, p.`tambon`, p.`village`) `areacode`
		, $FIELDS$
		, FROM_UNIXTIME(p.`created`, "%Y-%m-%d") `created`
		$FROM$
			$JOINS$
			LEFT JOIN %co_province% copv ON copv.`provid` = p.`changwat`
			LEFT JOIN %co_district% codist ON codist.`distid` = CONCAT(p.`changwat`,p.`ampur`)
			LEFT JOIN %co_subdistrict% cosd ON cosd.`subdistid` = CONCAT(p.`changwat`,p.`ampur`,p.`tambon`)
		%WHERE%
		$ORDER$
		';

	$nameDbs = mydb::select($stmt);

	$result->process[] = '<pre>'.str_replace("\t", ' ', mydb()->_query).'</pre>';

	foreach ($nameDbs->items as $key => $rs) {
		if ($rs->age) $nameDbs->items[$key]->age = date('Y')-sg_date($rs->age,'Y');
		$nameDbs->items[$key]->address = SG\implode_address($rs).($rs->commune?'<br /><strong>'.$rs->commune.'</strong>':'');
	}

	$showFields = 'no:ลำดับ,fullname:ชื่อ-สกุล,age:อายุ(ปี),address:ที่อยู่,label,created:วันที่เพิ่มข้อมูล';

	$result->name = $nameDbs->items;

	$ret.=R::View('imed.report.name.list',$nameDbs,'รายชื่อผู้สูงอายุ',array('prov'=>$getChangwat,'ampur'=>$getAmpur,'tambon'=>$getTambon,'village'=>$getVillage,'show'=>'yes'),$showFields,$cfg['thead'][0]);



	return $result;
}
?>