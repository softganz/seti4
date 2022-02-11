<?php
/**
* Project situation
*
* @param Object $self
* @return String
*/
function project_report_checkweightinput($self) {
	project_model::set_toolbar($self,'ตรวจสอบการบันทึกข้อมูลภาวะโภชนาการ');


	$ret.='<h3>รายการบันทึกสถานการณ์ภาวะโภชนาการ</h3>';
	$stmt='SELECT
					  tr.`tpid`
					, tr.`trid`
					, t.`title`
					, tr.`detail1` `year`
					, tr.`detail2` `term`
					, tr.`period`
					, cop.`provname` `changwatName`
					, CASE
							WHEN LEFT(t.`changwat`,1) IN (1,2,7) THEN "ภาคกลาง"
							WHEN LEFT(t.`changwat`,1) IN (3,4) THEN "ภาคอีสาน"
							WHEN LEFT(t.`changwat`,1) IN (5,6) THEN "ภาคเหนือ"
							WHEN LEFT(t.`changwat`,1) IN (8,9) THEN "ภาคใต้"
							END AS `zoneName`
					FROM `sgz_project_tr` tr
						LEFT JOIN %topic% t USING(`tpid`)
						LEFT JOIN %co_province% cop ON cop.`provid`=t.`changwat`
					WHERE `formid`="weight" AND `part`="title"
					ORDER BY `zoneName`,CONVERT(`changwatName` USING tis620) ASC, CONVERT(`title` USING tis620) ASC';
	$dbs=mydb::select($stmt);

	$yearsDbs=mydb::select('SELECT MIN(`detail1`) `minYear`, MAX(`detail1`) `maxYear` FROM `sgz_project_tr` WHERE `formid`="weight" AND `part`="title" LIMIT 1');

	$maxPeriod=mydb::select('SELECT MAX(`period`) `maxperiod` FROM `sgz_project_tr` WHERE `formid`="weight" AND `part`="title" LIMIT 1')->maxperiod;

	$tables = new Table();
	$tables->addClass('-weightinput');
	$tables->thead[]='โรงเรียน';
	$tables->thead[]='ภาค';
	$tables->thead[]='จังหวัด';
	for($y=$yearsDbs->minYear; $y<=$yearsDbs->maxYear; $y++) {
		for($i=1; $i<=2; $i++) {
			for($j=1; $j<=$maxPeriod;$j++) {
				$idx=$y.':'.$i.':'.$j;
				$emptyArray[$idx]='';
				$tables->thead['amt '.$idx]=($y+543).'<br />เทอม '.$i.'/'.$j;
			}
		}
	}

	$total=array('title'=>'รวม','','')+$emptyArray;

	$lastZone='';
	foreach ($dbs->items as $rs) {
		if (!isset($tables->rows[$rs->tpid])) {
			if ($lastZone!=$rs->zoneName) {
				$tables->rows[$rs->zoneName]=array('<th colspan="3">'.$rs->zoneName.'</th>','<th colspan="8"></th>','config'=>array('class'=>'subheader'));
				$tables->rows[]='<header>';
				$lastZone=$rs->zoneName;
			}
			$tables->rows[$rs->tpid]=array(
								'title'=>'<a href="'.url('project/'.$rs->tpid.'/info.weight').'" target="_blank">'.$rs->title.'</a>',
								'zone'=>$rs->zoneName,
								'prov'=>$rs->changwatName
								)+$emptyArray;
		}
		$idx=($rs->year).':'.$rs->term.':'.$rs->period;
		$tables->rows[$rs->tpid][$idx]='<a href="'.url('project/'.$rs->tpid.'/info.weight/view/'.$rs->trid).'" target="_blank"><span class="haveinput">✔</span></a>';
		$total[$idx]++;
	}
	$tables->tfoot[]=$total;
	$ret.=$tables->build();

	$ret.='<h3>รายการบันทึก เทอม/ครั้งที่ ซ้ำ</h3>';
	$stmt='SELECT `tpid`, `title`,`detail1` `year`, `detail2` `term`, `period`,COUNT(*) `amt`
					FROM %project_tr% tr
					LEFT JOIN %topic% t USING(`tpid`)
					WHERE `formid`="weight" AND `part`="title"
					GROUP BY `tpid`,`detail1`,`detail2`,`period`
					HAVING `amt`>1';
	$dbs=mydb::select($stmt);
	$tables = new Table();
	$tables->thead=array('โรงเรียน','center 1'=>'ปีการศึกษา','center 2'=>'เทอม','center 3'=>'ครั้งที่','center 4'=>'จำนวนรายการ');
	foreach ($dbs->items as $rs) {
		$tables->rows[]=array('<a href="'.url('project/'.$rs->tpid.'/info.weight').'" target="_blank">'.$rs->title.'</a>',$rs->year+543,$rs->term,$rs->period,$rs->amt);
	}
	$ret.=$tables->build();






	$ret.='<h3>รายการบันทึก เทอม/ครั้งที่ ไม่ถูกต้อง</h3>';
	$stmt='SELECT tr.`tpid`, tr.`trid`, t.`title`, tr.`detail1` `year`, tr.`detail2` `term`, tr.`period`
					FROM `sgz_project_tr` tr
						LEFT JOIN %topic% t USING(`tpid`)
					WHERE `formid`="weight" AND `part`="title" AND (`detail2`<1 OR `detail2`>2 OR `period`<1 OR `period`>2)';
	$dbs=mydb::select($stmt);

	$ret.=mydb::printtable($dbs);

	$ret.='<style type="text/css">
	.haveinput {width:1.5em;height:1.5em;margin:0 auto;background:green;color:#fff;display:block; text-align:center;border-radius:50%;line-height:1.5em;}
	.item td:nth-child(2n+3) {background:#f0f0f0;}
	.item td:nth-child(n+3) {width: 50px;}
	.item thead th:nth-child(n+3) {width: 48.6px;}
	.item>tfoot>tr>td:nth-child(n+2) {text-align: center;}
	.item.-weightinput>thead {display: none;}
	.item>tbody>tr.subheader>th {font-size:1.4em; background-color:#666; color:#fff;}
	/*
	.item.-weightinput {margin-top:30px;}
	.item.-weightinput>thead {position:fixed; top:95px; z-index:1;}
	*/
	.item.-weightinput>thead>tr>th {}
	</style>';
	$ret.='<script type="text/javascript">
	$(document).ready(function() {
		//alert($(".item.-weightinput>tbody>tr>td:nth-child(4)").width()+"px")
		//$(".item.-weightinput>thead>tr>th:nth-child(1)").width($(".item.-weightinput .row-1 td:nth-child(1)").width()+"px");
		//$(".item.-weightinput>thead>tr>th:nth-child(2)").width($(".item.-weightinput .row-1 td:nth-child(2)").width()+"px");

	});
	</script>';

	return $ret;
}
?>