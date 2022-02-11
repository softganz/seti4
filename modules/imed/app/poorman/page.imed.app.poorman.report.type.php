<?php
function imed_app_poorman_report_type($self) {
	R::View('imed.toolbar',$self,'ประเภทของสภาวะความยากลำบาก','none');

	$typeList=array(
							'POOR.TYPE.LIST.1'=>'คนไร้บ้าน',
							'POOR.TYPE.LIST.2'=>'คนไร้สัญชาติ',
							'POOR.TYPE.LIST.3'=>'ผู้สูงอายุที่ถูกทอดทิ้ง',
							'POOR.TYPE.LIST.4'=>'ผู้ติดเชื้อ',
							'POOR.TYPE.LIST.5'=>'ผู้ป่วยติดบ้าน/ติดเตียง',
							'POOR.TYPE.LIST.6'=>'อดีตผู้ต้องขัง',
							'POOR.TYPE.LIST.7'=>'คนพิการ',
							'POOR.TYPE.LIST.8'=>'ผู้ได้รับผลกระทบจากสถานการณ์',
							'POOR.TYPE.LIST.9'=>'เด็กกำพร้า (ทั่วไป/สถานการณ์)',
							'POOR.TYPE.LIST.10'=>'ผู้มีรายได้น้อย/ผู้ขัดสน(ซะกาต)',
							);

	$prov=post('prov');
	$ampur=post('ampur');
	$year=post('year');

	$yearList=mydb::select('SELECT DISTINCT `pryear` FROM %project% ORDER BY `pryear` ASC')->lists->text;

	$ret.='<nav class="nav -page">';
	$ret.='<form class="form -report" method="get" action="'.url('imed/app/poorman/report/type').'">';
	$ret.='<ul>';

	// Select province
	$ret.='<li class="ui-nav">';
	$ret.='<select class="form-select" name="prov"><option value="">==ทุกจังหวัด==</option>';
	mydb::where('q.`qtgroup`=4 AND q.`qtstatus`>=0');
	$provDb=mydb::select('SELECT `changwat`,`provname`,COUNT(*) FROM %qtmast% q LEFT JOIN %db_person% p USING(`psnid`) LEFT JOIN %co_province% cop ON cop.`provid`=p.`changwat` %WHERE% GROUP BY `changwat` HAVING `provname`!="" ORDER BY CONVERT(`provname` USING tis620) ASC');
	foreach ($provDb->items as $item) {
		$ret.='<option value="'.$item->changwat.'" '.($item->changwat==$prov?'selected="selected"':'').'>'.$item->provname.'</option>';
	}
	$ret.='</select>';

	//debugMsg($provDb,'$provDb');

	// Select ampur
	/*
	if ($prov) {
		$ret.='<li class="ui-nav"><select class="form-select" name="ampur" id="input-ampur"><option value="">==ทุกอำเภอ==</option>';
		$stmt='SELECT DISTINCT `ampur`,`nameampur` FROM %project_fund% WHERE `changwat`=:prov ORDER BY CONVERT(`nameampur` USING tis620) ASC';
		$dbs=mydb::select($stmt,':prov',$prov);
		foreach ($dbs->items as $item) {
			$ret.='<option value="'.$item->ampur.'" '.($item->ampur==$ampur?'selected="selected"':'').'>'.$item->nameampur.'</option>';

		}
		$ret.='</select></li>';
	}
	*/

	// Select year
	/*
	if (strpos($yearList,',')) {
		$ret.='<li class="ui-nav"><select class="form-select" name="year" id="develop-year"><option value="">==ทุกปี==</option>';
		foreach (explode(',',$yearList) as $item) {
			$ret.='<option value="'.$item.'" '.($item==$year?'selected="selected"':'').'>พ.ศ. '.($item+543).'</option>';
		}
		$ret.='</select></li>';
	} else {
		$ret.='<input type="hidden" name="year" value="'.$yearList.'" />';
	}
	*/

	$ret.='&nbsp;&nbsp;<button type="submit" class="btn -primary"><i class="icon -search -white"></i><span>ดูรายงาน</span></button>';
	$ret.='</li>';
	//$ret.='<li>';
	//$ret.='<select class="form-select" name="sex"><option value="" />ทุกเพศ</option><option value="1">ชาย</option><option value="2">หญิง</option></option></select>';
	//$ret.='</li>';
	$ret.='</ul></form>';
	$ret.='</nav>';



	//mydb::where('q.`qtgroup`=4 AND q.`qtstatus` IN ('._START.','._COMPLETE.','._DRAFT.','._WAITING.')');
	mydb::where('q.`qtgroup`=4 AND q.`qtstatus`>=0');
	if ($prov) mydb::where('p.`changwat`=:changwat',':changwat',$prov);

	$stmt='SELECT
				  COUNT(*) `totalPerson`
					FROM %qtmast% q
						LEFT JOIN %db_person% p USING(`psnid`)
						LEFT JOIN %co_province% cop ON cop.`provid`=p.`changwat`
					%WHERE%
					LIMIT 1;
					-- {reset:false}';
	$totalPerson=mydb::select($stmt)->totalPerson;
	//$ret.='$TotalPerson ='.$totalPerson;

	mydb::where('SUBSTR(tr.`part`,1,15)="POOR.TYPE.LIST."');

	$stmt='SELECT
					  tr.`part`
					, COUNT(*) `totalType`
					, q.`qtref`
					, COUNT(DISTINCT q.`qtref`) `totalPerson`
					, SUBSTRING(tr.`part`,1,15) `spart`
					FROM %qtmast% q
						LEFT JOIN %db_person% p USING(`psnid`)
						LEFT JOIN %co_province% cop ON cop.`provid`=p.`changwat`
						LEFT JOIN %qttran% tr USING(`qtref`)
					%WHERE%
					GROUP BY `part`
					ORDER BY `totalType` DESC;
					-- {sum:"totalType"}';
	$dbs=mydb::select($stmt);

	$tables = new Table();
	$graphYear = new Table();
	$graphYear->addClass('-hidden');

	$tables->thead=array('ประเภท','amt -total-person'=>'จำนวนคนทั้งหมด','amt -total-type'=>'จำนวนคน','amt -percent'=>'%');
	foreach ($dbs->items as $rs) {
		$tables->rows[]=array(
			$typeList[$rs->part],
			$totalPerson,
			number_format($rs->totalType),
			number_format($rs->totalType*100/$totalPerson,2),
		);

		$graphYear->rows[]=array(
			'string:Year'=>$typeList[$rs->part],
			'number:Budget'=>$rs->totalType
		);
	}
	$ret.='<div id="chart-app" class="sg-chart -chart-app" data-chart-type="pie" data-options=\'{"pieHole":0.4}\'>'._NL.$graphYear->build().'</div>'._NL;

	$ret.=$tables->build();


	head('googlegraph','<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>');

	//$ret.=print_o($dbs,'$dbs');
	$ret.='<style type="text/css">
	.sg-chart.-chart-app {height:400px;background-color:#fff;}
	</style>';
	return $ret;
}
?>