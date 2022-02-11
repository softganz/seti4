<?php
/**
* Project Report : Planning Summary
* Created 2019-05-12
* Modify  2020-03-28
*
* @param Object $self
* @return String
*
* @usage green/report/plant
*/

$debug = true;

function green_report_plant($self) {
	$toolbar = new Toolbar($self, 'ผลผลิต');

	$selectProvince = Array();
	$selectSector = Array();
	$selectYear = Array();

	$stmt = 'SELECT
		p.*
		, LEFT(p.`areacode`,2) `changwatCode`
		, cop.`provname` `name`
		, cod.`distid` `ampurId`, cod.`distname` `ampurName`
		FROM (
			SELECT o.`areacode`, o.`changwat`, o.`ampur`
			FROM %ibuy_farmplant% p
				LEFT JOIN %db_org% o USING(`orgid`)
			GROUP BY LEFT(o.`areacode`,4)
		) p
			LEFT JOIN %co_province% cop ON cop.`provid` = LEFT(p.`areacode`,2)
			LEFT JOIN %co_district% cod ON cod.`distid` = LEFT(p.`areacode`,4) AND RIGHT(cod.`distname`,1) != "*"
		WHERE  cop.`provname` IS NOT NULL
		ORDER BY CONVERT(`name` USING tis620);
		-- {group: "changwatCode"}';

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
	//debugMsg($dbs, '$dbs');

	foreach (mydb::select('SELECT `tpid`,`title` FROM %project% p LEFT JOIN %topic% USING(`tpid`) WHERE `prtype` = "ชุดโครงการ" ORDER BY CONVERT(`title` USING tis620)')->items as $key => $item) {
		$selectSet[$item->tpid] = $item->title;
		$checkboxSet .= '<label><input type="checkbox" name="for_set[]" value="'.$item->tpid.'" />'.$item->title.'</label>';
	}

	foreach (mydb::select('SELECT p.`pryear` FROM %project% p WHERE `prtype` = "แผนงาน" AND `pryear` IS NOT NULL GROUP BY `pryear` ORDER BY `pryear` DESC')->items as $key => $item) {
		$selectYear[$item->pryear] = 'พ.ศ.'.($item->pryear+543);
	}


	$reportBar = new Report(url('green/api/plant'), 'green-report-plant');

	$reportBar->addId('green-report-plant');

	$reportBar->Filter('changwat', Array('text' => 'จังหวัด', 'filter' => 'for_changwat', 'select' => $selectProvince));
	//$reportBar->Filter('year', Array('text' => 'ปี พ.ศ.', 'filter' => 'for_year', 'select' => $selectYear));

	$reportBar->Output('html', '<div class="loader -rotate" style="width: 128px; height: 128px; margin: 64px auto; display: block;"></div>');

	$ret .= $reportBar->build();




	head('<script type="text/javascript">
		$(document).ready(function() {
			var $sgDrawReport = $("#green-report-plant>form .btn.-primary.-submit").sgDrawReport().doAction(null,\'{dataType: "html"}\')
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
?>