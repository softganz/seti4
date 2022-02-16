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

class ProjectPlanningSummary extends Page {

	function build() {
		// R::View('project.toolbar',$self,'ภาพรวมแผนงานกองทุน','planning');

		$selectArea = [];
		$selectProvince = [];
		$selectSector = [];
		$selectYear = [];

		foreach (mydb::select('SELECT a.`areaid`, a.`areaname`, GROUP_CONCAT(DISTINCT f.`changwat`) `provIdList` FROM %project_area% a LEFT JOIN %project_fund% f USING(`areaid`) GROUP BY `areaid` ORDER BY areaid+0')->items as $item) {
			$selectArea[$item->areaid] = [
				'label' => 'เขต '.$item->areaid.' '.$item->areaname,
				'attr' => ['data-prov' => $item->provIdList],
			];
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
			$selectProvince[$changwatKey] = [
				'label' => reset($changwatItem)->name,
				'attr' => ['data-area' => reset($changwatItem)->areaid],
			];
			foreach ($changwatItem as $key => $item) {
				$selectProvince[$changwatKey]['items'][$item->ampurId] = [
					'label' => $item->ampurName,
					'filter' => 'for_ampur',
					'attr' => ['data-area' => $item->areaid, 'data-changwat' => $changwatKey],
				];
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


		$toolbar = new Report(url('project/api/planning/summary'), 'projet-planning-summary');

		$toolbar->addId('projet-planning-summary');

		$toolbar->Filter('year', ['text' => 'ปี พ.ศ.', 'filter' => 'for_year', 'select' => $selectYear]);
		$toolbar->Filter('area', ['text' => 'เขต', 'filter' => 'for_area', 'select' => $selectArea]);
		$toolbar->Filter('changwat', ['text' => 'จังหวัด', 'filter' => 'for_changwat', 'select' => $selectProvince]);
		$toolbar->Filter('sector', ['text' => 'องค์กร', 'filter' => 'for_sector', 'select' => $selectSector]);

		$toolbar->Output('html', '<div class="loader -rotate" style="width: 128px; height: 128px; margin: 64px auto; display: block;"></div>');

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'แผนงานกองทุน',
				'navigator' => [
					R::View('project.nav.planning'),
				], // navigator
			]), // AppBar
			'children' => [
				$toolbar,

				// Show Last 50 Plans
				new Card([
					'id' => 'project-planning-last',
					'children' => [
						new ListTile([
							'title' => 'Last 50 Plannings',
							'leading' => '<i class="icon -material">view_list</i>',
						]),
						new Table([
							'thead' => [
								'แผนงาน',
								'tran -center -nowrap'.($orderKey == 'tran' ? ' -sort' : '') => '<i class="icon -material">assessment</i>',
								'rate -center -nowrap' => '<i class="icon -material">star</i>',
								'center -chanhwat' => 'จังหวัด',
								'date' => 'วันที่สร้างแผนงาน'
							],
							'children' => array_map(
								function($rs) {
									return [
										'<a href="' . url('project/planning/' . $rs->tpid) . '">' . $rs->title . '</a>',
										$rs->totalTran ? '<i class="icon -material -sg-level -level-'.(round($rs->totalTran/10) + 1).'" title="จำนวน '.$rs->totalTran.' รายการ">check_circle_outline</i>' : '',
										'<i class="icon -material rating-star '.($rs->rating != '' ? '-rate-'.round($rs->rating) : '').'">star</i>',
										$rs->provname,
										$rs->created
									];
								},
								$this->_lastPlanningModel()
							),
						]), // Table
					], // children
				]), // Card
				$this->_script(),
			],
		]);
	}

	function _lastPlanningModel() {
		return mydb::select(
			'SELECT
			  t.`tpid`,t.`orgid`,t.`title`,t.`changwat`,cop.`provname`,t.`created`
			, t.`rating`
			, (SELECT COUNT(*) FROM %project_tr% WHERE `tpid` = t.`tpid` AND (`formid` = "info" AND `part` IN ("problem", "basic", "guideline", "project")) ) `totalTran`
			FROM %project% p
				LEFT JOIN %topic% t USING(`tpid`)
				LEFT JOIN %co_province% cop ON cop.`provid`=t.`changwat`
			WHERE p.`prtype` = "แผนงาน"
			ORDER BY t.`tpid` DESC
			LIMIT 50'
		)->items;
	}

	function _script() {
		head('<script type="text/javascript">
			$(document).ready(function() {
				var $sgDrawReport = $("#projet-planning-summary>form .btn.-primary.-submit").sgDrawReport().doAction(null,\'{dataType: "html"}\')
			});
			</script>'
		);

		return '<style type="text/css">
		#detail-list>tbody>tr>td:nth-child(n+2) {text-align: center;}
		.nav.-table-export {display: none;}
		.-checkbox>abbr>span {display: none; padding-left: 16px;"}
		</style>

		<script type="text/javascript">
			var allProvince = $.map($(".-checkbox-for_changwat") ,function(option) {
				return {"id": option.value, "area": $(option).data("area"), "name" : $(option).html()};
			});

			// console.log(allProvince)
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
	}
}
?>