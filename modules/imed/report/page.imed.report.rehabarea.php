<?php
/**
 * Disabled report by area
 * @param $_GET
 * @return String
 */
function imed_report_rehabarea($self) {
	$title='รายงานผู้ป่วยติดเตียง';

	$prov=SG\getFirst($_REQUEST['p'],'90');
	$ampur=$_REQUEST['a'];
	$tambon=$_REQUEST['t'];
	$village=$_REQUEST['v'];
	$defect=$_REQUEST['d'];
	$educate=$_REQUEST['e'];
	$reportType=SG\getFirst($_REQUEST['r'],'amt');
	$graphType=strtolower(SG\getFirst($_REQUEST['g'],'pie'));
	$detail=$_REQUEST['detail'];
	$order=$_REQUEST['o'];


	cfg('db.disabled.title',$title);

	$isAdmin=user_access('administer imeds');

	$orderArr = array(
		'na'=>'ชื่อ:name',
		'rd'=>'วันที่จดทะเบียน:c.created',
		'cd'=>'วันที่ป้อน:p.created',
		'tb'=>'ตำบล:p.tambon',
		'vi'=>'หมู่บ้าน:p.village+0',
		'age'=>'อายุ:p.birth',
		'label'=>'ป้ายรายงาน:label',
	);

	if (post('r')=='') {
		$ret.='<form class="report-form sg-form" id="report-form" data-rel="#report-output" method="get" action="'.url(q()).'"><input type="hidden" name="r" id="reporttype" value="'.$reportType.'" /><input type="hidden" name="g" id="graphtype" value="'.$graphType.'" />';
		$ret.='<h3>'.$title.'</h3>'._NL;
		$ret.='<div class="form-item">'._NL;
		$provdbs = mydb::select('SELECT DISTINCT `provid`, `provname` FROM %imed_care% c LEFT JOIN %db_person% p ON p.`psnid` = c.`pid` LEFT JOIN %co_province% cop ON cop.`provid`=p.`changwat` WHERE c.`careid` = 3 HAVING `provname` IS NOT NULL ORDER BY CONVERT(`provname` USING tis620) ASC');
		$ret.='<label for="prov">จังหวัด : </label>'._NL.'<select name="p" id="prov" class="form-select">'._NL.'<option value="">--ทุกจังหวัด--</option>'._NL;
		foreach ($provdbs->items as $rs) $ret.='<option value="'.$rs->provid.'"'.($rs->provid==$prov?' selected="selected"':'').'>'.$rs->provname.'</option>'._NL;
		$ret.='</select>'._NL;
		if ($prov) {
			$stmt='SELECT DISTINCT `distid`, `distname` FROM %co_district% cod WHERE SUBSTR(`distid`,1,2)=:prov ORDER BY CONVERT(`distname` USING tis620) ASC';
			$ret.='<label for="ampur"> อำเภอ : </label>'._NL.'<select name="a" id="ampur" class="form-select">'._NL.'<option value="">--ทุกอำเภอ--</option>'._NL;
			foreach (mydb::select($stmt,':prov',$prov)->items as $rs) $ret.='<option value="'.substr($rs->distid,2,2).'"'.(substr($rs->distid,2,2)==$ampur?' selected="selected"':'').'>'.$rs->distname.'</option>'._NL;
			$ret.='</select>'._NL;
			$ret.='<label for="tambon"> ตำบล : </label>'._NL.'<select name="t" id="tambon" class="form-select">'._NL.'<option value="">--ทุกตำบล--</option>'._NL.'</select>'._NL;
			$ret.='<label for="village"> หมู่บ้าน : </label>'._NL.'<select name="v" id="village" class="form-select">'._NL.'<option value="">--ทุกหมู่บ้าน--</option>'._NL.'</select>'._NL;
		}
		$ret.='<button class="btn -primary -main" value="ดูรายงาน" type="submit"><i class="icon -forward -white"></i><span>ดูรายงาน</span></button>'._NL;
		$ret.='</div>'._NL;

		$reportTypeArray=array(
			'amt'=>'พื้นที่',
			'sex'=>'เพศ',
			'age'=>'อายุ',
			'religion'=>'ศาสนา',
			'mstatus'=>'สมรส',
			'edu'=>'การศึกษา',
			'body'=>'ดัชนีมวลกาย',
			'dfdetail'=>'รายละเอียด',
		);

		$ret.='<a href="javascript:void(0)" class="left"><i class="icon -back"></i></a><div class="toolbar">'._NL.'<ul>';
//		foreach ($reportTypeArray as $k=>$v) $ret.='<li'.($k==$reportType?' class="active"':'').'><a href="#'.$k.'">'.$v.'</a></li>'._NL;
		foreach ($reportTypeArray as $k=>$v) {
			$ret.='<li'.($k==$reportType?' class="active"':'').'><a href="#'.$k.'">'.$v.'</a>';
			if ($k=='defect') {
				$ret.='<select class="form-select" name="d"><option value="-1">---ทุกประเภท---</option>';
				foreach (mydb::select('SELECT defect+0 defectID,defect FROM %imed_disabled_defect% df GROUP BY defect ORDER BY defect')->items as $item) {
					$ret.='<option value="'.$item->defectID.'"'.($item->defectID==$defect?' selected="selected"':'').'>'.SG\getFirst($item->defect,'ไม่ระบุ').'</option>';
				}
				$ret.='</select>';
			} else if ($k=='edu') {
				$ret.='<select name="e"><option value="-1">---ทุกระดับ---</option>';
				foreach (mydb::select('SELECT edu_code, edu_desc FROM %co_educate%')->items as $item) {
					$ret.='<option value="'.$item->edu_code.'"'.($item->edu_code==$educate?' selected="selected"':'').'>'.$item->edu_desc.'</option>';
				}
				$ret.='</select>';
			} else if ($k=='dfdetail') {

			}
			$ret.='</li>';
		}
		$ret.='</ul></div><a href="javascript:void(0)" class="right"><i class="icon -forward"></i></a>'._NL;

		$ret.='<div class="optionbar"><ul>';
		$ret.='<li><input type="submit" name="g" value="Pie" class="btn -graph'.($graphType=='pie'?'  active':'').'" /> <input type="submit" name="g" value="Bar" class="btn -graph'.($graphType=='bar'?' active':'').'" /> <input type="submit" name="g" value="Col" class="btn -graph'.($graphType=='col'?' active':'').'" /> <input type="submit" name="g" value="Line" class="btn -graph'.($graphType=='line'?' active':'').'" /></li>';
		if (i()->ok) $ret.='<li><input type="checkbox" name="detail" value="yes"'.($detail=='yes'?' checked="checked"':'').'/> แสดงรายชื่อ ';
		$ret.='<select class="form-select" name="o"><option>--เรียงตาม--</option>';
		foreach ($orderArr as $k=>$v) $ret.='<option value="'.$k.'"'.($order==$k?' selected="selected"':'').'>'.substr($v,0,strpos($v,':')).'</option>';
		$ret.='</select></li>';
	if (user_access('access debugging program')) $ret.='<li><input type="checkbox" name="debug" value="yes"'.($_REQUEST['debug']?' checked="checked"':'').' /> Debug</li>';

		$ret.='</ul></div>';
		$ret.='</form>';
	}

	$ret.='<div id="report-output">';
	$ret.='<div id="chart_div" style=""></div>';

	unset($stmt);
	$cfg['from']='%imed_care% c';
	$cfg['joins'][]='LEFT JOIN %db_person% p ON p.psnid=c.pid';

	$where=array();
	$where=sg::add_condition($where,'(c.`careid` = 3 AND c.status = 1)');
	if ($prov) $where=sg::add_condition($where,'p.`changwat`=:prov','prov',$prov);
	if ($ampur) $where=sg::add_condition($where,'p.`ampur`=:ampur','ampur',$ampur);
	if ($tambon) $where=sg::add_condition($where,'p.`tambon`=:tambon','tambon',$tambon);
	if ($village) $where=sg::add_condition($where,'LPAD(p.`village`,2,"0") = :village','village',$village);
	if ($defect>0) {
		$where=sg::add_condition($where,'ddf.`defect`+0=:defect','defect',$defect);
		$cfg['joins'][]='LEFT JOIN %imed_disabled_defect% ddf ON ddf.pid=d.pid';
	}
	if ($educate && $educate!=-1) $where=sg::add_condition($where,'p.`educate`=:educate','educate',$educate);

	switch ($reportType) {
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
				WHEN value < 18.5 THEN " < 18.5"
				WHEN value BETWEEN 18.5 and 22.9 THEN "18.5 - 22.9"
				WHEN value BETWEEN 23 and 24.9 THEN "23 - 24.9"
				WHEN value BETWEEN 25 and 29.9 THEN "25 - 29.9"
				WHEN value > 29.9 THEN ">= 30"
				WHEN value IS NULL THEN NULL
			END';
			$cfg['joins'][]='LEFT JOIN %imed_qt% qt ON qt.pid=c.pid AND qt.`part`="ดัชนีมวลกาย"';
			break;

		case 'age' :
			$cfg['caption']='จำนวนผู้สูงอายุแต่ละช่วงอายุ';
			$cfg['thead']=array('ช่วงอายุ','จำนวนคน');
			$cfg['label']='birth';
			$stmt='SELECT
				CASE
					WHEN age < 5 THEN " 0 - 5 ปี"
					WHEN age BETWEEN 6 and 12 THEN " 6 - 12 ปี"
					WHEN age BETWEEN 13 and 25 THEN "13 - 25 ปี"
					WHEN age BETWEEN 26 and 49 THEN "26 - 49 ปี"
					WHEN age BETWEEN 50 and 54 THEN "50 - 54 ปี"
					WHEN age BETWEEN 55 and 59 THEN "55 - 59 ปี"
					WHEN age BETWEEN 60 and 69 THEN "60 - 69 ปี"
					WHEN age BETWEEN 70 and 79 THEN "70 - 79 ปี"
					WHEN age BETWEEN 80 and 89 THEN "80 - 89 ปี"
					WHEN age >= 90 THEN "90 ปีขึ้นไป"
					WHEN age IS NULL THEN NULL
				END as `label`,
				COUNT(*) AS `value`
				FROM (SELECT TIMESTAMPDIFF(YEAR, birth, CURDATE()) AS age FROM %imed_care% c
								'.implode(_NL,$cfg['joins']).'
							'.($where?'WHERE '.implode(' AND ',$where['cond']):'').') as derived
				GROUP BY `label`
				ORDER BY `label` IS NULL, `label` ASC';
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

		case 'dfdetail' :
			$dfdetail=post('dfdetail');
			$cfg['caption']='จำนวนผู้สูงอายุจำแนกตาม '.$dfdetail;
			$cfg['thead']=array($detail,'จำนวน(คน)');
			$cfg['label']='qt.value';
			$cfg['joins'][]='LEFT JOIN %imed_qt% qt ON qt.pid=c.pid AND qt.`part`="'.$dfdetail.'"';
			break;

		default :
			$cfg['caption']='จำนวนผู้สูงอายุในพื้นที่';
			if ($tambon) {
				$cfg['thead']=array('หมู่บ้าน','จำนวน(คน)');
				$cfg['label']='CONCAT("หมู่ ",dv.villno," - ",dv.villname)';
				$cfg['joins'][]='LEFT JOIN %co_village% dv ON dv.villid=CONCAT(p.changwat,p.ampur,p.tambon, LPAD(p.village, 2, "0"))';
			} else if ($ampur) {
				$cfg['thead']=array('ตำบล','จำนวน(คน)');
				$cfg['label']='dd.subdistname';
				$cfg['joins'][]='LEFT JOIN %co_subdistrict% dd ON dd.subdistid=CONCAT(p.changwat,p.ampur,p.tambon)';
			} else if ($prov) {
				$cfg['thead']=array('อำเภอ','จำนวน(คน)');
				$cfg['label']='cod.distname';
				$cfg['joins'][]='LEFT JOIN %co_district% cod ON cod.distid=CONCAT(p.changwat,p.ampur)';
			}
			break;
	}

	if (!$stmt) {
		$stmt='SELECT '.($sql_fields?implode(', ',$sql_fields).', ':'').$cfg['label'].' `label`, COUNT(*) `value`
			FROM '.$cfg['from'].'
				'.implode(_NL,$cfg['joins']).'
			'.($where?'WHERE '.implode(' AND ',$where['cond']):'').'
			GROUP BY `label`
			ORDER BY `label` IS NULL, `label` ASC';
	}
	$dbs=mydb::select($stmt,$where['value']);

	include_once 'modules/imed/assets/qt.rehab.php';

	foreach (explode("\n", $qtText) as $key) {
		$key=trim($key);
		if (empty($key)) continue;
		if (strpos($key,',')) {
			$jStr='{'.$key.'}';
			$json=json_decode($jStr,true);
			if ($json) {
				$key=$json['key'];
				$json['label']=SG\getFirst($json['label'],$key);
				$json['group']='qt';
				unset($json['key']);
				$qt[$key]=$json;
			}
		} else {
			$qt[$key]=array('label'=>$value,'type'=>'text','group'=>'qt','class'=>'w-5');
		}
	}
	$qtProp=$qt[$dfdetail];

	$data->title=$cfg['caption'];
	$ghead[]='พื้นที่';
	$data->items[]=$ghead;


	$tables = new Table();
	$tables->addClass('report-summary');
	$tables->caption=$cfg['caption'];
	$tables->thead=array($cfg['thead'][0], $cfg['thead'][1],'%');
	$pie->items[]=array('รายการ','จำนวน');

	foreach ($dbs->items as $rs) $total+=$rs->value;
	foreach ($dbs->items as $rs) {
		unset($row);
		if ($reportType=='dfdetail') {
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
		if ($label != 'ไม่ระบุ') $pie->items[]=array($label,intval($rs->value));
		$tables->rows[]=array($label,number_format($rs->value),number_format(100*$rs->value/$total,2).'%');
	}
	$tables->tfoot[]=array('รวมทั้งสิ้น',number_format($total),$total?'100%':'-');
	$ret .= $tables->build();

	$ret.='<br clear="all" /><p><strong>หมายเหตุ</strong><ul><li>แหล่งที่มาของข้อมูลจากการสำรวจในบางพื้นที่</li><li>กรุณาอย่าเพิ่งนำข้อมูลในรายงานนี้ไปอ้างอิงจนกว่ากระบวนการเก็บรวมรวมข้อมูลเสร็จสมบูรณ์</ul></p>';







	unset($stmt);
	if ($detail) {
		list(,$listOrderBy)=explode(':',$orderArr[$order]);
		if (empty($listOrderBy)) $listOrderBy='name';
		if ($listOrderBy && in_array($listOrderBy,array('name','label'))) $listOrderBy='CONVERT (`'.$listOrderBy.'` USING tis620)';

		$zones=imed_model::get_user_zone(i()->uid,'imed');
		if ($isAdmin) {

		} else if ($zones) {
			$where=sg::add_condition($where,'('.'p.`uid`=:uid OR '.R::Model('imed.person.zone.condition',$zones).')','uid',i()->uid);
		} else {
			$where=sg::add_condition($where,'p.`uid`=:uid','uid',i()->uid);
		}





		$stmt='SELECT
			c.`pid`, p.`name`, CONCAT(IFNULL(`prename`,"")," ",`name`," ",`lname`) fullname,
			p.`birth` age, p.`created`, p.`house`, p.`village`, cosd.`subdistname`, copv.`provname`, codist.`distname`,
			'.$cfg['label'].' `label`
			FROM '.$cfg['from'].'
				'.implode(_NL,$cfg['joins']).'
				LEFT JOIN %co_province% copv ON copv.provid=p.changwat
				LEFT JOIN %co_district% codist ON codist.distid=CONCAT(p.changwat,p.ampur)
				LEFT JOIN %co_subdistrict% cosd ON cosd.subdistid=CONCAT(p.changwat,p.ampur,p.tambon)
			'.($where?'WHERE '.implode(' AND ',$where['cond']):'').'
			ORDER BY '.$listOrderBy.' ASC';
		$nameDbs=mydb::select($stmt,$where['value']);
		foreach ($nameDbs->items as $key => $item) {
			if ($item->age) $nameDbs->items[$key]->age=date('Y')-sg_date($item->age,'Y');
		}
		$showFields='no:ลำดับ,fullname:ชื่อ-สกุล,age:อายุ(ปี),address:ที่อยู่,label,created:วันที่เพิ่มข้อมูล';
		$ret.=R::View('imed.report.name.list',$nameDbs,'รายชื่อผู้สูงอายุ',array('prov'=>$prov,'ampur'=>$ampur,'tambon'=>$tambon,'village'=>$village,'show'=>'yes'),$showFields,$cfg['thead'][0]);
	}

	$chartTypes=array('bar'=>'BarChart','pie'=>'PieChart','col'=>'ColumnChart','line'=>'LineChart');
	$changeAxis=0;
	$ret.='</div><!--report-output-->';

	head('js.imed.public.js','<script type="text/javascript" src="imed/js.imed.public.js"></script>');

	$ret .= '
		<style type="text/css">
		table.report-summary {width:100%;}
		#chart_div {width:100%;height:400px;float:left; background: transparent;}
		table.report-summary {width:100%;float:right;}
		table.report-summary>tbody>tr>td, table.report-summary>tfoot>tr>td {text-align:center;}
		table.report-summary>tbody>tr>td:first-child, table.report-summary>tfoot>tr>td:first-child {text-align:left;}
		</style>

		<script type="text/javascript">
		$.getScript("https://www.google.com/jsapi", function(data, textStatus, jqxhr) {
			google.load("visualization", "1", {packages:["corechart"], callback: drawChart});

			//google.setOnLoadCallback(drawChart);
			function drawChart() {
				var data = google.visualization.arrayToDataTable('.json_encode($pie->items).');
				var options = {
					title: "'.$data->title.'",
					hAxis: {title: "'.$cfg['thead'][0].'", titleTextStyle: {color: "black"}},
					vAxis: {title: "'.$cfg['thead'][1].'", minValue: 0},
					isStacked: '.(post('stack') ? 'true' : 'false').'
				};
				var chart = new google.visualization.'.$chartTypes[$graphType].'(document.getElementById("chart_div"));
				chart.draw(data, options);
			}
		});
		</script>';

	if (debug('yes')) $ret.='<br clear="all" /><div style="height:400px;overflow:auto;border:1px #ccc solid;">'.print_o($dbs,'$dbs').print_o($nameDbs,'$nameDbs').'</div>';
	return $ret;
}
?>