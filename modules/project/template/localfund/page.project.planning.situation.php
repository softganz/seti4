<?php
/**
* Project :: Planning Situation
* Created 2020-06-20
* Modify  2021-06-06
*
* @return Widget
*
* @usage project/planning/situation
*/

$debug = true;

class ProjectPlanningSituation extends Page {

	function build() {
		// Data Model
		$getPlanId = post('plan');
		$getHideMenu = post('hide');

		$reportTypeArray = [];
		$selectPlan = [];
		$selectArea = [];
		$selectProvince = [];
		$selectSector = [];
		$selectYear = [];

		// Option for select planning
		foreach (R::Model('category.get','project:planning','catid', '{fullValue: true}') as $key => $value) {
			$selectPlan[$key] = ['label' => $value->name, 'attr' => ['data-planning' => $value->catid]];
		}

		// Option for select problem
		mydb::where('`taggroup` LIKE :taggroup AND `process` > 0', ':taggroup', $getPlanId ? 'project:problem:'.$getPlanId : 'project:problem:%');
		$selectProblem = mydb::select('
			SELECT `taggroup`, `catid`, CONCAT(`catid`, ":", `taggroup`) `id`, `name`, `description`
			FROM %tag%
			%WHERE%
			ORDER BY `taggroup`, `weight`, `catid`;
			-- {key: "id", value: "name"}
			')->items;

		// View Model

		$reportBar = new Report(url('project/api/planning/situations/summary'), 'projet-planning-situation');

		$reportBar->addId('projet-planning-situation');
		$reportBar->data('callback', 'projectReportSituationCallback');

		$reportBar->config('showArrowLeft', true);
		$reportBar->config('showArrowRight', true);

		if ($getPlanId) {
			$reportBar->addConfig('filterPretext',
				($getPlanId ? '<input type="hidden" name="for_plan[]" value="'.$getPlanId.'" />' : '')
			);
		} else {
			$reportBar->Filter('plan', ['text' => 'แผนงาน', 'filter' => 'for_plan', 'select' => $selectPlan, 'type' => 'radio']);
		}

		if ($selectProblem) {
			$reportBar->filter('problem', ['text' => 'สถานการณ์', 'filter' => 'for_problem', 'select' => $selectProblem, 'type' => 'radio']);
		}

		if (mydb::table_exists('%project_area%')) {
			$reportBar->Filter(
				'area',
				[
					'text' => 'เขต',
					'type' => 'radio',
					'filter' => 'for_area',
					'select' => (function() {
						$options = [];
						foreach (mydb::select(
							'SELECT a.`areaid`, CONCAT("เขต ", a.`areaid`, " ",a.`areaname`) `areaname`, GROUP_CONCAT(DISTINCT f.`changwat` ORDER BY `changwat`) `changwat`
							FROM %project_area% a
								LEFT JOIN %project_fund% f ON f.`areaid` = a.`areaid`
							GROUP BY `areaid`
							ORDER BY CAST(a.`areaid` AS UNSIGNED)'
						)->items as $rs) {
					 	$options[$rs->areaid] = [
					 		'label' => $rs->areaname,
					 		'attr' => ['data-changwat' => $rs->changwat],
					 	];
					 }
					 return $options;
					})(),
				]
			);
		}
		$reportBar->Filter(
			'changwat',
			[
				'text' => 'จังหวัด',
				'type' => 'radio',
				'filter' => 'for_changwat',
				'select' => mydb::select(
					'SELECT DISTINCT LEFT(`areacode`, 2) `changwat`, `provname`
					FROM %project% p
						LEFT JOIN %topic% t USING(`tpid`)
						LEFT JOIN %co_province% cop ON cop.`provid` = LEFT(t.`areacode`, 2)
					WHERE p.`prtype` IN ("แผนงาน") AND cop.`provname`!=""
					ORDER BY CONVERT(`provname` USING tis620) ASC;
					-- {key: "changwat", value: "provname"}'
				)->items
			]
		);
		$reportBar->Filter(
			'ampur',
			[
				'text' => 'อำเภอ',
				'type' => 'radio',
				'filter' => 'for_ampur',
				'select' => []
			]
		);
		if (mydb::table_exists('%project_fund%')) {
			$reportBar->Filter(
				'fund',
				[
					'text' => 'กองทุน',
					'type' => 'radio',
					'filter' => 'for_fund',
					'select' => []
				]
			);
		}

		$reportBar->Output('chart', '<p class="notify">กรุณาเลือกแผนงานและสถานการณ์</p></div>');
		$reportBar->Output('summary', '');

		head('<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>');

		head('<script type="text/javascript">
		google.charts.load("current", {packages:["corechart"]});
		$(document).ready(function() {
			var graphType = "graph_total"
			let $drawReport = $(".sg-drawreport").sgDrawReport()
			//$drawReport.doAction()

			window.projectReportSituationCallback = function($this, data) {

				let $debugOutput = $("#report-output-debug").empty()

				if (data.debug && data.process) {
					data.process.forEach(function(item) {
						$debugOutput.append($("<div></div>").html(item))
					})
				}

				showChart($this, data)
				showSummary($this, data)

				function showChart($this, data) {
					let $graphOutput = $("#report-output-chart")
					let summary = data.summary
					let chartType = "Column"
					let chart = new google.visualization.ColumnChart(document.getElementById("report-output-chart"))

					let axis = ["ปี พ.ศ.","ประเทศ"]
					if ("areaProblem" in summary[0]) axis.push("เขต")
					if ("changwatProblem" in summary[0]) axis.push("จังหวัด")
					if ("ampurProblem" in summary[0]) axis.push("อำเภอ")
					if ("fundProblem" in summary[0]) axis.push("กองทุน")

					let chartData = [axis]

					summary.map((item) => {
						if (graphType == "graph_total") {
							let row = [
								(item.year+543).toString(),
								item.countryProblem
							]
							if ("areaProblem" in item) row.push(item.areaProblem ? item.areaProblem : 0)
							if ("changwatProblem" in item) row.push(item.changwatProblem ? item.changwatProblem : 0)
							if ("ampurProblem" in item) row.push(item.ampurProblem ? item.ampurProblem : 0)
							if ("fundProblem" in item) row.push(item.fundProblem ? item.fundProblem : 0)
							chartData.push(row)
						} else {
							let row = [
								(item.year+543).toString(),
								_percent(item.totalValuation,
								item.totalProject),
							]
							if ("areaProblem" in item) row.push(_percent(item.areaProblem, item.areaProblemFollow))
							if ("changwatProblem" in item) row.push(_percent(item.changwatProblem, item.changwatTarget))
							if ("ampurProblem" in item) row.push(_percent(item.ampurProblem, item.ampurTarget))
							if ("fundProblem" in item) row.push(_percent(item.fundProblem, item.fundTarget))
							chartData.push(row)
						}
					})
					let chartTable = google.visualization.arrayToDataTable(chartData)
					$graphOutput.empty()

					let vAxis = {
						logScale: false,
						title: "ขนาดปัญหา",
						viewWindow: {
							min: 0,
						},
						gridlines: {
							//count: maxProject/20,
						}
					}
					if (graphType == "graph_percent") {
						vAxis.viewWindow.max = 100
						vAxis.title = "%"
						graphTitle = "จำนวน % ของโครงการ"
					} else {
						graphTitle = "ขนาดปัญหาของสถานการณ์ในแต่ละปี"
					}

					let hAxis = {
						title: "พ.ศ.",
					}

					let options = {
						title: graphTitle,
						//colors: ["#058DC7", "#f60", "#C605BA"],
						tooltip: {isHtml: true},
						legend: { position: "bottom", alignment: "start" },
						pointSize: 4,
						interpolateNulls: true,
						hAxis: hAxis,
						vAxes: {
							0: vAxis,
						},
						series: {
							0:{targetAxisIndex:0, pointShape: "circle"},
							1:{targetAxisIndex:0, pointShape: "circle"},
							//2:{targetAxisIndex:1}
						}
					};
					chart.draw(chartTable, options);
				}

				function showSummary($this, data) {
					let summary = data.summary
					let $summaryOutput = $("#report-output-summary")
					$summaryOutput.empty()
					if (summary === undefined || $summaryOutput.length === 0) return

					var table = $("<table></table>").addClass("item -center")
					var thead = $("<thead></thead>")
					let row1 = $("<tr></tr>");
					let row2 = $("<tr></tr>");

					row1.append("<th rowspan=\"2\">ปี พ.ศ.</th>")
					row1.append("<th colspan=\"3\">ประเทศ</th>")
					row2.append("<th>ปัญหา</th>").append("<th>เป้าหมาย</th>").append("<th>แผนงาน</th>")

					if ("areaProblem" in data.summaryFields) {
						row1.append("<th colspan=\"3\">"+data.summaryFields.areaProblem+"</th>")
						row2.append("<th>ปัญหา</th>").append("<th>เป้าหมาย</th>").append("<th>แผนงาน</th>")
					}
					if ("changwatProblem" in data.summaryFields) {
						row1.append("<th colspan=\"3\">"+data.summaryFields.changwatProblem+"</th>")
						row2.append("<th>ปัญหา</th>").append("<th>เป้าหมาย</th>").append("<th>แผนงาน</th>")
					}
					if ("ampurProblem" in data.summaryFields) {
						row1.append("<th colspan=\"3\">"+data.summaryFields.ampurProblem+"</th>")
						row2.append("<th>ปัญหา</th>").append("<th>เป้าหมาย</th>").append("<th>แผนงาน</th>")
					}
					if ("fundProblem" in data.summaryFields) {
						row1.append("<th colspan=\"3\">"+data.summaryFields.fundProblem+"</th>")
						row2.append("<th>ปัญหา</th>").append("<th>เป้าหมาย</th>").append("<th>แผนงาน</th>")
					}

					thead.append(row1)
					thead.append(row2)


					table.append(thead)

					//console.log(data.summaryFields)
					//console.log(data.parameter)

					summary.map((item) => {
						let baseParameter = {}
						if (data.parameter.plan) baseParameter.plan = data.parameter.plan
						if (data.parameter.problem) baseParameter.problem = data.parameter.problem

						if (data.parameter.type) baseParameter.type = data.parameter.type
						let paraTotalValuation = JSON.parse(JSON.stringify(baseParameter))

						let paraArea = JSON.parse(JSON.stringify(baseParameter))
						if (data.parameter.area) paraArea.area = data.parameter.area

						let paraChangwat = JSON.parse(JSON.stringify(baseParameter))
						if (data.parameter.changwat) paraChangwat.changwat = data.parameter.changwat

						let paraAmpur = JSON.parse(JSON.stringify(baseParameter))
						if (data.parameter.ampur) paraAmpur.ampur = data.parameter.ampur

						let paraFund = JSON.parse(JSON.stringify(baseParameter))
						if (data.parameter.fund) paraFund.fund = data.parameter.fund

						let row = $("<tr></tr>").addClass("row")
						row.append($("<td>พ.ศ."+(item.year + 543)+"</td>"))
						.append(_addRow(item.countryProblem, "project/api/plannings", item.year, baseParameter))
						.append(_addRow(item.countryTarget))
						.append(_addRow(item.countryPlan))

						if ("areaProblem" in item) {
							row
							.append(_addRow(item.areaProblem, "project/api/plannings", item.year, paraArea))
							.append(_addRow(item.areaTarget))
							.append(_addRow(item.areaPlan))
						}
						if ("changwatProblem" in item) {
							row
							.append(_addRow(item.changwatProblem, "project/api/plannings", item.year, paraChangwat))
							.append(_addRow(item.changwatTarget))
							.append(_addRow(item.changwatPlan))
						}
						if ("ampurProblem" in item) {
							row
							.append(_addRow(item.ampurProblem, "project/api/plannings", item.year, paraAmpur))
							.append(_addRow(item.ampurTarget))
							.append(_addRow(item.ampurPlan))
						}
						if ("fundProblem" in item) {
							row
							.append(_addRow(item.fundProblem, "project/api/plannings", item.year, paraFund))
							.append(_addRow(item.fundTarget))
							.append(_addRow(item.fundPlan))
						}
						table.append(row)
					})

					$summaryOutput.append(table)
				}

				function _addRow(value, urlLink = null, year, para = {}) {
					let td = $("<td></td>")
					let link = ""
					if (value === null) return td.text("-")
					else if (value <= 0) return td.text(0)
					if (urlLink) {
						let percent = ""
						let urlPara = "?year="+year
						Object.keys(para).map((key, index) => {
							urlPara += "&"+key+"="+para[key]
						})

						link = $("<a></a>")
							.attr("href", url+urlLink+urlPara)
							.addClass("sg-action -load-project")
							.data("rel", "box")
							.data("height", "100%")
							.data("width", "640")
							.data("callback", "showProject")
							.text(value)
					} else {
						link = $("<span></span>").text(value)
					}
					td.append(link)
					return td
				}

				$(document).on("click", "#graph_type a", function() {
					graphType = $(this).attr("id")
					$(this).closest("ul").find(".btn").removeClass("-active")
					$(this).addClass("-active")
					projectReportValuationChart(null, data)
				})

			}

			window.showProject = function($this, data) {
				if (!data.items) return false

				let table = $("<table></table>").addClass("item")
				let thead = $("<thead></thead>")
					.append("<th>แผนงาน</th>")
					.append("<th class=\"-nowrap\">สถานการณ์</th>")
					.append("<th class=\"-nowrap\">เป้าหมาย</th>")

				let tbody = $("<tbody />")

				data.items.map((item) => {
					let row = $("<tr></tr>").addClass("row"+(item.problemSize === null || item.targetSize === null ? " -cancel" : "" ))
					let link = $("<a />").attr("href", url+"project/planning/"+item.planningId).text(item.title).attr("target", "_blank")
					let td = $("<td></td>").append(link).append("<br /><em>"+item.orgName+" อ."+item.ampurName+" จ."+item.changwatName+"</em>")
					row.append(td)
					row.append($("<td />").addClass("col -amt").text(item.problemSize))
					row.append($("<td />").addClass("col -amt").text(item.targetSize))
					tbody.append(row)
				})

				table.append(thead).append(tbody)

				// Show table in box
				$(".box-page").html(table)
			}

			$(document).on("change", ".-checkbox-for_plan", function() {
				let $this = $(this)
				let $problemCheckBox = $(".-checkbox-for_problem")
				let planningSelect = $this.val()

				// Clear changwat checked and hide changwat not in area
				$problemCheckBox
				.prop("checked", false)
				.each(function(i) {
					let $checkBox = $(this)
					let res = $checkBox.val().split(":")
					if (res[3] == planningSelect) {
						$checkBox.closest("abbr").show()
					} else {
						$checkBox.closest("abbr").hide()
					}
				});
				$drawReport.makeFilterBtn()
				$(".-group-for_problem").find(".-check-count").addClass("-hidden")
			});

			$(document).on("change", ".-checkbox-for_area", function() {
				let $this = $(this)
				let changwatId = $this.data("changwat").split(",")
				let $changwatCheckBox = $(".-checkbox-for_changwat")

				// Clear changwat checked and hide changwat not in area
				$(".-group-for_ampur .-checkbox").empty()
				$(".-group-for_fund .-checkbox").empty()
				$changwatCheckBox
				.prop("checked", false)
				.each(function(i) {
					let $checkBox = $(this)
					if (changwatId.indexOf($checkBox.attr("value")) != -1) {
						$checkBox.closest("abbr").show()
					} else {
						$checkBox.closest("abbr").hide()
					}
				});
				$drawReport.makeFilterBtn()
				$(".-group-for_changwat").find(".-check-count").addClass("-hidden")
				$(".-group-for_ampur").find(".-check-count").addClass("-hidden")
				$(".-group-for_fund").find(".-check-count").addClass("-hidden")
			});

			$(document).on("change", ".-checkbox-for_changwat", function() {
				let $this = $(this)
				let $ampurContainer = $(".-group-for_ampur")
				let $ampur = $(".-group-for_ampur .-checkbox")
				$ampur.empty()
				$(".-group-for_fund .-checkbox").empty()
				$.get(url+"api/ampur",{q:$(this).val()}, function(data) {
					for (var i = 0; i < data.length; i++) {
						let ampurData = data[i]
						let ampurCode = $this.attr("value") + ampurData.ampur
						$ampur.append(
							"<abbr><label><input id=\"for_ampur_"+ampurCode+"\" class=\"-checkbox-for_ampur -filter-checkbox\" type=\"radio\" name=\"for_ampur[]\" value=\""+ampurCode+"\" />"
							+ "<span>"+ampurData.label+"</span>"
							+ "</label></abbr>"
						);
					};
				},"json")
				$drawReport.makeFilterBtn()
				$(".-group-for_ampur").find(".-check-count").addClass("-hidden")
				$(".-group-for_fund").find(".-check-count").addClass("-hidden")
			})

			$(document).on("change", ".-checkbox-for_ampur", function() {
				let $this = $(this)
				let $fundContainer = $(".-group-for_fund")
				let $fund = $(".-group-for_fund .-checkbox")
				$fund.empty()
				$.get(url+"project/api/fund",{ampur:$(this).val()}, function(data) {
					for (var i = 0; i < data.length; i++) {
						let fundData = data[i]
						let fundCode = fundData.value
						$fund.append(
							"<abbr><label><input id=\"for_fund_"+fundCode+"\" class=\"-checkbox-for_fund -filter-checkbox\" type=\"radio\" name=\"for_fund[]\" value=\""+fundCode+"\" />"
							+ "<span>"+fundData.label+"</span>"
							+ "</label></abbr>"
						);
					};
				},"json")
				$drawReport.makeFilterBtn()
				$(".-group-for_fund").find(".-check-count").addClass("-hidden")
			})
		});
		</script>');

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 	'สรุปสถานการณ์จำแนกตามแผนงาน',
				'navigator' => [
					R::View('project.nav.planning')
				],
			]), // AppBar
			'children' => [
				$reportBar,

				'<style type="text/css">
				#detail-list>tbody>tr>td:nth-child(n+2) {text-align: center;}
				.nav.-table-export {display: none;}
				.-checkbox>abbr>span {display: none; padding-left: 16px;}
				</style>',

				$getHideMenu ? '<style type="text/css">
					.page.-header {display: none;}
					.page.-content {padding-top: 8px;}
					.page.-footer {display: none;}
					.toolbar.-main .nav.-submodule {display: none;}
					</style>' : NULL,
			], // children
		]); // Scaffold

		return $ret;
	}
}
?>