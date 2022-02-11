<?php
/**
* Project :: Planning Summary
* Created 2020-06-20
* Modify  2020-06-20
*
* @param Object $self
* @return String
*/

$debug = true;

function project_report_localfund_target($self) {
	R::View('project.toolbar',$self,'กลุ่มเป้าหมายติดตามโครงการ');

	$selectArea = Array();
	$selectProvince = Array();
	$selectSector = Array();
	$selectYear = Array();

	foreach (R::Model('category.get','project:planning','catid') as $key => $value) {
		$selectPlan[$key] = $value;
	}

	foreach (mydb::select('SELECT a.`areaid`, a.`areaname`, GROUP_CONCAT(DISTINCT f.`changwat`) `provIdList` FROM %project_area% a LEFT JOIN %project_fund% f USING(`areaid`) GROUP BY `areaid` ORDER BY areaid+0')->items as $item) {
		$selectArea[$item->areaid] = Array(
			'label' => 'เขต '.$item->areaid.' '.$item->areaname,
			'attr' => Array('data-prov' => $item->provIdList),
		);
	}

	$stmt = 'SELECT
		p.*
		, cop.`provname` `name`
		, cod.`distid` `ampurId`, cod.`distname` `ampurName`
		FROM (
			SELECT o.`changwat`, o.`ampur`, f.`areaid`
			FROM %project% p
				LEFT JOIN %topic% t USING(`tpid`)
				LEFT JOIN %db_org% o USING(`orgid`)
				LEFT JOIN %project_fund% f ON f.`orgid` = t.`orgid`
			WHERE p.`prtype` = "แผนงาน" AND p.`changwat` IS NOT NULL
			GROUP BY CONCAT(o.`changwat`,o.`ampur`)
		) p
			LEFT JOIN %co_province% cop ON cop.`provid` = p.`changwat`
			LEFT JOIN %co_district% cod ON cod.`distid` = CONCAT(p.`changwat`,p.`ampur`) AND RIGHT(cod.`distname`,1) != "*"
		WHERE  cop.`provname` IS NOT NULL
		ORDER BY CONVERT(`name` USING tis620);
		-- {group: "changwat"}';

	foreach (mydb::select($stmt)->items as $changwatKey => $changwatItem) {
		$selectProvince[$changwatKey] = Array(
			'label' => reset($changwatItem)->name,
			'attr' => Array('data-area' => reset($changwatItem)->areaid),
		);
		foreach ($changwatItem as $key => $item) {
			$selectProvince[$changwatKey]['items'][$item->ampurId] = Array(
				'label' => $item->ampurName,
				'filter' => 'for_ampur',
				'attr' => Array('data-area' => $item->areaid, 'data-changwat' => $changwatKey),
			);
		}
	}
	//debugMsg($selectProvince,'$selectProvince');
	//$dbs = mydb::select($stmt);
	//debugMsg($dbs);

	foreach (project_base::$orgTypeList as $key => $value) {
		$selectSector[$key] = $value;
	}

	foreach (mydb::select('SELECT `tpid`,`title` FROM %project% p LEFT JOIN %topic% USING(`tpid`) WHERE `prtype` = "ชุดโครงการ" ORDER BY CONVERT(`title` USING tis620)')->items as $key => $item) {
		$selectSet[$item->tpid] = $item->title;
		$checkboxSet .= '<label><input type="checkbox" name="for_set[]" value="'.$item->tpid.'" />'.$item->title.'</label>';
	}

	foreach (mydb::select('SELECT p.`pryear` FROM %project% p WHERE `prtype` = "แผนงาน" AND `pryear` IS NOT NULL GROUP BY `pryear` ORDER BY `pryear` DESC')->items as $key => $item) {
		$selectYear[$item->pryear] = 'พ.ศ.'.($item->pryear+543);
	}


	$toolbar = new Report(url('project/api/follow/target'), 'project-report');

	$toolbar->addId('project-report');

	if (!$getPlanningId) {
		$toolbar->Filter('plan', Array('text' => 'แผนงาน', 'filter' => 'for_plan', 'select' => $selectPlan, 'type' => 'radio'));
	}
	$toolbar->Filter('area', Array('text' => 'เขต', 'filter' => 'for_area', 'select' => $selectArea));
	$toolbar->Filter('changwat', Array('text' => 'จังหวัด', 'filter' => 'for_changwat', 'select' => $selectProvince));
	$toolbar->Filter('year', Array('text' => 'ปี พ.ศ.', 'filter' => 'for_year', 'select' => $selectYear));
	$toolbar->Filter('sector', Array('text' => 'องค์กร', 'filter' => 'for_sector', 'select' => $selectSector));

	$toolbar->Output('html', '<div class="loader -rotate" style="width: 128px; height: 128px; margin: 64px auto; display: block;"></div>');

	$ret .= $toolbar->build();




	head('<script type="text/javascript">
		$(document).ready(function() {
			var $sgDrawReport = $("#project-report>form .btn.-primary.-submit").sgDrawReport().doAction(null,\'{dataType: "html"}\')
		});
		</script>'
	);

	$ret .= '<script type="text/javascript">
		var allProvince = $.map($(".-checkbox-for_changwat") ,function(option) {
			return {"id": option.value, "area": $(option).data("area"), "name" : $(option).html()};
		});

		console.log(allProvince)
		$(".-checkbox-for_area").change(function() {
			var $this = $(this)
			var areaId = ""
			$(".-checkbox-for_area:checked").each(function(i) {
				areaId += ","+$(this).data("prov")
			});
			var provIdList = areaId.split(",")

			//console.log(areaId)
			//console.log(provIdList)

			$.map(allProvince ,function(option) {
				//console.log(option)
				//console.log(allProvince[option.id])
				//console.log(allProvince.indexOf(option.id))
				var $input = $("#for_changwat_"+option.id)
				if (areaId == "") {
					$input.closest("label").show()
					$input.closest("abbr").children("span").hide()
				} else if (option.id != "" && provIdList.indexOf(option.id) >= 0) {
					//console.log("ADD ",option.id)
					$input.closest("label").show()
					$input.closest("abbr").children("span").css("display","block").show()
				} else {
					$input.closest("label").hide()
					$input.prop("checked", false)
					$input.closest("abbr").children("span").hide()
					$input.closest("abbr").find("input").prop("checked", false)
				}
				return true;
			});
			$(this).sgDrawReport().makeFilterBtn()
		});
	</script>';




	$ret .= '<style type="text/css">
	#detail-list>tbody>tr>td:nth-child(n+2) {text-align: center;}
	.nav.-table-export {display: none;}
	.-checkbox>abbr>span {display: none; padding-left: 16px;"}
	</style>';

	return $ret;
}
/*
ติดตามโครงการ



จำนวนติดตามโครงการที่มีวัตถุประสงค์ PA
SELECT
-- t.`title`,t.tpid
"จำนวนติดตามโครงการวัตถุประสงค์ PA" `header`
, p.`pryear`+543 `ปี`
, o.`refid`
-- , count(*) `totalPlan`
,COUNT(DISTINCT `tpid`) `totalProject`
FROM `sgz_project_tr` o
LEFT JOIN sgz_topic t USING(`tpid`)
LEFT JOIN sgz_project p USING(`tpid`)
WHERE o.`formid` LIKE 'info' AND o.`part` LIKE 'objective' AND o.`tagname`="project:problem:7" AND p.`pryear` BETWEEN 2018 AND 2020
GROUP BY p.`pryear`,`refid`
ORDER BY `pryear`,`refid` 
LIMIT 100



จำนวนโครงการที่มีวัตถุประสงค์ PA ข้อ 6
SELECT
-- t.`title`,t.tpid
"จำนวนติดตามโครงการวัตถุประสงค์ PA" `header`
, t.`tpid`
, p.`pryear`+543 `ปี`
, o.`refid`
-- , count(*) `totalPlan`
-- ,COUNT(DISTINCT `tpid`) `totalProject`
FROM `sgz_project_tr` o
LEFT JOIN sgz_topic t USING(`tpid`)
LEFT JOIN sgz_project p USING(`tpid`)
WHERE o.`formid` LIKE 'info' AND o.`part` LIKE 'objective' AND o.`tagname`="project:problem:7" AND p.`pryear` BETWEEN 2018 AND 2020 AND `refid`=6
-- GROUP BY p.`pryear`,`refid`
ORDER BY `pryear`,`refid` 
-- LIMIT 100



จำนวนกลุ่มเป้าหมายติดตามโครงการ
SELECT
"กลุ่มเป้าหมาย PA ติดตาม" `header`
-- tag.`name`,tg.`tpid`,tg.`tgtid`,tg.`amount`,plan.`refid`
, f.`areaid`
, p.`pryear`+543  `ปี`
, COUNT(*) `จำนวนคน`
, COUNT(DISTINCT tg.`tpid`) `จำนวนโครงการ`
, SUM(p.`budget`) `งบประมาณ`
FROM `sgz_project_target` tg
	LEFT JOIN `sgz_project` p USING(`tpid`)
	LEFT JOIN `sgz_topic` t USING(`tpid`)
	LEFT JOIN `sgz_project_fund` f ON f.`orgid`=t.`orgid`
	LEFT JOIN `sgz_project_tr` plan ON plan.`tpid`=tg.`tpid` AND plan.`formid`="info" AND plan.`part`="supportplan" AND plan.`refid`=7
	LEFT JOIN sgz_tag tag ON tag.`taggroup`="project:target" and tag.`catid`=tg.`tgtid`
WHERE tg.`tagname`="info" AND plan.`refid`=7 AND p.`pryear` BETWEEN 2018 AND 2020
GROUP BY `areaid`, p.`pryear`
ORDER BY `areaid`,p.`pryear`
LIMIT 100



SELECT
-- t.`title`,t.tpid
o.`refid`,count(*) `totalPlan`
,p.`pryear`+543
,COUNT(DISTINCT `tpid`) `totalProject`
-- ,o.* 
FROM `sgz_project_tr` o
LEFT JOIN sgz_topic t USING(`tpid`)
LEFT JOIN sgz_project p USING(`tpid`)
WHERE o.`formid` LIKE 'info' AND o.`part` LIKE 'objective' AND o.`tagname`="project:problem:7"
-- AND o.`refid`=8
-- GROUP BY refid
GROUP BY p.`pryear`
-- ,`refid`
ORDER BY `pryear`,`refid` 
LIMIT 100
*/
?>