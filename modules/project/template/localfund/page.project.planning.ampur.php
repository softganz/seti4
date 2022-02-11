<?php
/**
* Project :: Planning Ampur
* Created 2021-05-17
* Modify  2021-05-17
*
* @param Object $self
* @return String
*
* @usage project/planning/ampur
*/

$debug = true;

class ProjectPlanningAmpur extends Page {

	function build() {
		// Data Model
		$getPlanId = post('plan');
		$getHideMenu = post('hide');

		$isOrgTrainer = mydb::select('SELECT COUNT(*) `orgs` FROM %org_officer% WHERE `uid` = :uid LIMIT 1', ':uid', i()->uid)->orgs;
		$isAdd = is_admin('project') || $isOrgTrainer;

		// Create Report Bar
		$selectPlan = [];
		$selectProvince = [];
		$selectYear = [];
		$selectArea = [];

		// Option for select planning
		foreach (R::Model('category.get','project:planning','catid', '{fullValue: true}') as $key => $value) {
			$selectPlan[$key] = ['label' => $value->name, 'attr' => ['data-planning' => $value->catid]];
			//debugMsg($value, '$value');
		}

		// // Option for select problem
		// mydb::where('`taggroup` LIKE :taggroup AND `process` > 0', ':taggroup', $getPlanId ? 'project:problem:'.$getPlanId : 'project:problem:%');
		// $selectProblem = mydb::select('
		// 	SELECT `taggroup`, `catid`, CONCAT(`catid`, ":", `taggroup`) `id`, `name`, `description`
		// 	FROM %tag%
		// 	%WHERE%
		// 	ORDER BY `taggroup`, `weight`;
		// 	-- {key: "id", value: "name"}
		// 	')->items;

		// Option for select area
		if (mydb::table_exists('%project_area%')) {
			foreach (mydb::select(
					'SELECT a.`areaid`, CONCAT("เขต ", a.`areaid`, " ",a.`areaname`) `areaname`, GROUP_CONCAT(DISTINCT f.`changwat` ORDER BY `changwat`) `changwat`
					FROM %project_area% a
						LEFT JOIN %project_fund% f ON f.`areaid` = a.`areaid`
					GROUP BY `areaid`
					ORDER BY CAST(a.`areaid` AS UNSIGNED)'
				)->items as $rs) {
			 	$selectArea[$rs->areaid] = [
			 		'label' => $rs->areaname,
			 		'attr' => ['data-changwat' => $rs->changwat],
			 	];
			 }
		}

		// View Model
		$reportBar = new Report(url('project/api/planning/summary/ampur'), 'projet-planning-ampur');

		$reportBar->addId('projet-planning-ampur');
		$reportBar->data('callback', 'projectReportAmpurCallback');

		$reportBar->config('showArrowLeft', true);
		$reportBar->config('showArrowRight', true);
		//['Line','Column','Bar'];

		// if ($getPlanId) {
		// 	$reportBar->addConfig('filterPretext',
		// 		($getPlanId ? '<input type="hidden" name="for_plan[]" value="'.$getPlanId.'" />' : '')
		// 	);
		// } else {
		// 	$reportBar->Filter('plan', ['text' => 'แผนงาน', 'filter' => 'for_plan', 'select' => $selectPlan, 'type' => 'radio']);
		// }

		// if ($selectProblem) {
		// 	$reportBar->filter('problem', ['text' => 'สถานการณ์', 'filter' => 'for_problem', 'select' => $selectProblem, 'type' => 'radio']);
		// }

		//$reportBar->Filter('year', ['text' => 'ปี พ.ศ.', 'filter' => 'for_year', 'select' => $selectYear, 'type' => 'radio']);

		$reportBar->Filter(
			'year',
			[
				'text' => 'ปี พ.ศ.',
				'type' => 'radio',
				'filter' => 'for_year',
				'select' => mydb::select('SELECT p.`pryear` `year`, CONCAT("พ.ศ.",p.`pryear`+543) `name` FROM %project% p WHERE `prtype` = "แผนงาน" AND `pryear` IS NOT NULL GROUP BY `pryear` ORDER BY `pryear` DESC; -- {key: "year", value: "name"}')->items
			]
		);

		if ($selectArea) {
			$reportBar->Filter(
				'area',
				[
					'text' => 'เขต',
					'type' => 'radio',
					'filter' => 'for_area',
					'select' => $selectArea,
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
					WHERE p.`prtype` IN ("แผนงาน") AND t.`orgid` IS NULL AND LENGTH(t.`areacode`) = 4 AND cop.`provname`!=""
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


		//$reportBar->Output('chart', '<p class="notify">กรุณาเลือกแผนงานและสถานการณ์</p></div>');


		// Get Last 100 Plannings
		$lastPlanningDbs = mydb::select(
			'SELECT p.`tpid` `projectId`, t.`title`, t.`created`
			FROM %project% p
				LEFT JOIN %topic% t USING(`tpid`)
			WHERE p.`prtype` = "แผนงาน" AND t.`orgid` IS NULL AND LENGTH(t.`areacode`) = 4
			ORDER BY `projectId` DESC
			LIMIT 100'
		);

		$lastPlan = new Table();
		$lastPlan->thead = ['แผนงาน', 'create -date' => 'วันที่สร้าง'];
		foreach ($lastPlanningDbs->items as $rs) {
			$lastPlan->rows[] = [
				'<a href="'.url('project/planning/'.$rs->projectId).'" target="_blank">'.$rs->title.'</a>',
				sg_date($rs->created, 'ว ดด ปปปป H:i'),
			];
		}

		$reportBar->Output('summary', $lastPlan->build());

		// Script
		head('<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>');

		head('<script type="text/javascript">
		google.charts.load("current", {packages:["corechart"]});
		$(document).ready(function() {
			var graphType = "graph_total"
			let $drawReport = $(".sg-drawreport").sgDrawReport()
			//$drawReport.doAction()

			window.projectReportAmpurCallback = function($this, data) {
				//console.log(data)

				let $debugOutput = $("#report-output-debug").empty()

				if (data.debug && data.process) {
					data.process.forEach(function(item) {
						$debugOutput.append($("<div></div>").html(item))
					})
				}

				showChart($this, data)
				showSummary($this, data)

				function showChart($this, data) {
					// let $graphOutput = $("#report-output-chart")
					// let summary = data.summary
					// let chartType = "Column"
					// let chart = new google.visualization.ColumnChart(document.getElementById("report-output-chart"))

					// let axis = ["ปี พ.ศ.","สถานการณ์"]
					// if ("areaProblem" in summary[0]) axis.push("เขต")
					// if ("changwatProblem" in summary[0]) axis.push("จังหวัด")
					// if ("ampurProblem" in summary[0]) axis.push("อำเภอ")
					// if ("fundProblem" in summary[0]) axis.push("กองทุน")

					// let chartData = [axis]

					// //console.log("AXIS", axis)
					// summary.map((item) => {
					// 	console.log(item)
					// 	if (graphType == "graph_total") {
					// 		let row = [
					// 			(item.year+543).toString(),
					// 			item.countryProblem
					// 		]
					// 		if ("areaProblem" in item) row.push(item.areaProblem ? item.areaProblem : 0)
					// 		if ("changwatProblem" in item) row.push(item.changwatProblem ? item.changwatProblem : 0)
					// 		if ("ampurProblem" in item) row.push(item.ampurProblem ? item.ampurProblem : 0)
					// 		if ("fundProblem" in item) row.push(item.fundProblem ? item.fundProblem : 0)
					// 		chartData.push(row)
					// 	} else {
					// 		let row = [
					// 			(item.year+543).toString(),
					// 			_percent(item.totalValuation,
					// 			item.totalProject),
					// 		]
					// 		if ("areaProblem" in item) row.push(_percent(item.areaProblem, item.areaProblemFollow))
					// 		if ("changwatProblem" in item) row.push(_percent(item.changwatProblem, item.changwatTarget))
					// 		if ("ampurProblem" in item) row.push(_percent(item.ampurProblem, item.ampurTarget))
					// 		if ("fundProblem" in item) row.push(_percent(item.fundProblem, item.fundTarget))
					// 		chartData.push(row)
					// 	}
					// })
					// let chartTable = google.visualization.arrayToDataTable(chartData)
					// $graphOutput.empty()

					// let vAxis = {
					// 	logScale: false,
					// 	title: "ขนาดปัญหา",
					// 	viewWindow: {
					// 		min: 0,
					// 	},
					// 	gridlines: {
					// 		//count: maxProject/20,
					// 	}
					// }
					// if (graphType == "graph_percent") {
					// 	vAxis.viewWindow.max = 100
					// 	vAxis.title = "%"
					// 	graphTitle = "จำนวน % ของโครงการ"
					// } else {
					// 	graphTitle = "ขนาดปัญหา"
					// }

					// let hAxis = {
					// 	title: "พ.ศ.",
					// }

					// let options = {
					// 	title: graphTitle,
					// 	//colors: ["#058DC7", "#f60", "#C605BA"],
					// 	tooltip: {isHtml: true},
					// 	legend: { position: "bottom", alignment: "start" },
					// 	pointSize: 4,
					// 	interpolateNulls: true,
					// 	hAxis: hAxis,
					// 	vAxes: {
					// 		0: vAxis,
					// 	},
					// 	series: {
					// 		0:{targetAxisIndex:0, pointShape: "circle"},
					// 		1:{targetAxisIndex:0, pointShape: "circle"},
					// 		//2:{targetAxisIndex:1}
					// 	}
					// };
					// chart.draw(chartTable, options);
				}

				function showSummary($this, data) {
					//console.log($this)
					let summary = data.summary
					let $summaryOutput = $("#report-output-summary")
					$summaryOutput.empty()
					if (summary === undefined || $summaryOutput.length === 0) return

					var table = $("<table></table>").addClass("item")
					var thead = $("<thead></thead>")
					let row1 = $("<tr></tr>");
					let row2 = $("<tr></tr>");

					row1.append("<th rowspan=\"2\">ชื่อแผนงาน</th>")
					row1.append("<th rowspan=\"2\">แผนงาน</th>").append("<th rowspan=\"2\">พัฒนาโครงการ</th>").append("<th colspan=\"2\">ติดตามโครงการ</th>")
					row2.append("<th>โครงการ</th><th>งบประมาณ</th>")

					// if ("areaProblem" in data.summaryFields) {
					// 	row1.append("<th colspan=\"3\">"+data.summaryFields.areaProblem+"</th>")
					// 	row2.append("<th>ปัญหา</th>").append("<th>เป้าหมาย</th>").append("<th>แผนงาน</th>")
					// }
					// if ("changwatProblem" in data.summaryFields) {
					// 	row1.append("<th colspan=\"3\">"+data.summaryFields.changwatProblem+"</th>")
					// 	row2.append("<th>ปัญหา</th>").append("<th>เป้าหมาย</th>").append("<th>แผนงาน</th>")
					// }
					// if ("ampurProblem" in data.summaryFields) {
					// 	row1.append("<th colspan=\"3\">"+data.summaryFields.ampurProblem+"</th>")
					// 	row2.append("<th>ปัญหา</th>").append("<th>เป้าหมาย</th>").append("<th>แผนงาน</th>")
					// }
					// if ("fundProblem" in data.summaryFields) {
					// 	row1.append("<th colspan=\"3\">"+data.summaryFields.fundProblem+"</th>")
					// 	row2.append("<th>ปัญหา</th>").append("<th>เป้าหมาย</th>").append("<th>แผนงาน</th>")
					// }

					thead.append(row1)
					thead.append(row2)


					table.append(thead)

					//console.log(data.summaryFields)
					//console.log(data.parameter)

					summary.map((item) => {
						let baseParameter = {}
						if (data.parameter.year) baseParameter.year = data.parameter.year
						if (data.parameter.area) baseParameter.area = data.parameter.area
						if (data.parameter.changwat) baseParameter.changwat = data.parameter.changwat
						if (data.parameter.ampur) baseParameter.ampur = data.parameter.ampur

						// let paraArea = JSON.parse(JSON.stringify(baseParameter))
						// if (data.parameter.area) paraArea.area = data.parameter.area

						// let paraChangwat = JSON.parse(JSON.stringify(baseParameter))
						// if (data.parameter.changwat) paraChangwat.changwat = data.parameter.changwat

						// let paraAmpur = JSON.parse(JSON.stringify(baseParameter))
						// if (data.parameter.ampur) paraAmpur.ampur = data.parameter.ampur

						// let paraFund = JSON.parse(JSON.stringify(baseParameter))
						// if (data.parameter.fund) paraFund.fund = data.parameter.fund

						//let paraChangwat = {value: data.parameter.value, changwat: data.parameter.changwat}
						//let paraAmpur = {value: data.parameter.value, ampur: data.parameter.ampur}
						//let paraFund = {value: data.parameter.value, fund: data.parameter.fund}
						//let paraByValuation = {value: data.parameter.value, area: data.parameter.area}

						let row = $("<tr></tr>").addClass("row")
						row.append($("<td>"+(item.planName)+"</td>"))
						.append(_addRow(thousandsSeparators(item.planAmt,",",0), "project/planning/issue/"+item.planId, baseParameter))
						.append(_addRow(thousandsSeparators(item.proposalAmt,",",0), "project/planning/dev/"+item.planId, baseParameter))
						.append(_addRow(thousandsSeparators(item.followAmt,",",0), "project/planning/follow/"+item.planId, baseParameter))
						.append(_addRow(thousandsSeparators(item.followBudget,",",2)))


						// if ("areaProblem" in item) {
						// 	row
						// 	.append(_addRow(item.areaProblem))
						// 	.append(_addRow(item.areaTarget))
						// 	.append(_addRow(item.areaPlan))
						// }
						// if ("changwatProblem" in item) {
						// 	row
						// 	.append(_addRow(item.changwatProblem))
						// 	.append(_addRow(item.changwatTarget))
						// 	.append(_addRow(item.changwatPlan))
						// }
						// if ("ampurProblem" in item) {
						// 	row
						// 	.append(_addRow(item.ampurProblem))
						// 	.append(_addRow(item.ampurTarget))
						// 	.append(_addRow(item.ampurPlan))
						// }
						// if ("fundProblem" in item) {
						// 	row
						// 	.append(_addRow(item.fundProblem))
						// 	.append(_addRow(item.fundTarget))
						// 	.append(_addRow(item.fundPlan))
						// }
						table.append(row)
					})

					$summaryOutput.append(table)
				}

				function _addRow(value, urlLink = null, para = {}) {
					let td = $("<td class=\"col -center\"></td>")
					let link = ""
					//console.log(value)
					if (value === null) return td.text("-")
					else if (value <= 0) return td.text(0)
					if (urlLink) {
						let percent = ""
						let urlPara = "?type=ampur"
						//console.log(para)
						Object.keys(para).map((key, index) => {
							urlPara += "&"+key+"="+para[key]
						})
						//console.log(urlPara)

						link = $("<a></a>")
							.attr("href", url+urlLink+urlPara)
							.addClass("sg-action -load-project")
							.data("rel", "box")
							.data("height", "100%")
							.data("widtg", "480")
							//.data("callback", "showProject")
							.text(value)
					} else {
						link = $("<span></span>").text(value)
					}
					td.append(link)
					return td
				}

				function showProject($this, data) {
					var table = $("<table></table>").addClass("item")
					var thead = $("<thead></thead>")

					data.items.map((item) => {
						//console.log(item)
						let row = $("<tr></tr>").addClass("row")
						let link = $("<a />").attr("href", url+"project/"+item.projectId+"/valuation").text(item.title).attr("target", "_blank")
						let td = $("<td></td>").append(link).append("<br /><em>"+item.orgName+"</em>")
						row.append(td)
						table.append(row)
					})
					$(".box-page").html(table)
				}

				$(document).on("click", "#graph_type a", function() {
					graphType = $(this).attr("id")
					$(this).closest("ul").find(".btn").removeClass("-active")
					$(this).addClass("-active")
					projectReportValuationChart(null, data)
				})

			}

			$(document).on("change", ".-checkbox-for_plan", function() {
				let $this = $(this)
				// let $problemCheckBox = $(".-checkbox-for_problem")
				// let planningSelect = $this.val()

				// // Clear changwat checked and hide changwat not in area
				// $problemCheckBox
				// .prop("checked", false)
				// .each(function(i) {
				// 	let $checkBox = $(this)
				// 	let res = $checkBox.val().split(":")
				// 	//console.log($checkBox.val())
				// 	//console.log(res)
				// 	if (res[3] == planningSelect) {
				// 		$checkBox.closest("abbr").show()
				// 	} else {
				// 		$checkBox.closest("abbr").hide()
				// 	}
				// });
				// $drawReport.makeFilterBtn()
				// $(".-group-for_problem").find(".-check-count").addClass("-hidden")
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
				console.log("CHANGWAT CHANGE")
				let $this = $(this)
				let $ampurContainer = $(".-group-for_ampur")
				let $ampur = $(".-group-for_ampur .-checkbox")
				$ampur.empty()
				$(".-group-for_fund .-checkbox").empty()
				$.get(url+"api/ampur",{q:$(this).val()}, function(data) {
					//if (data.length) $ampur.show(); else $ampur.hide()
					for (var i = 0; i < data.length; i++) {
						let ampurData = data[i]
						let ampurCode = $this.attr("value") + ampurData.ampur
						//console.log(ampurData)
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

			// $(document).on("change", ".-checkbox-for_ampur", function() {
			// 	//console.log("AMPUR CHANGE")
			// 	let $this = $(this)
			// 	let $fundContainer = $(".-group-for_fund")
			// 	let $fund = $(".-group-for_fund .-checkbox")
			// 	$fund.empty()
			// 	$.get(url+"project/api/fund",{ampur:$(this).val()}, function(data) {
			// 		//if (data.length) $ampur.show(); else $ampur.hide()
			// 		for (var i = 0; i < data.length; i++) {
			// 			let fundData = data[i]
			// 			let fundCode = fundData.value
			// 			//console.log(fundData)
			// 			$fund.append(
			// 				"<abbr><label><input id=\"for_fund_"+fundCode+"\" class=\"-checkbox-for_fund -filter-checkbox\" type=\"radio\" name=\"for_fund[]\" value=\""+fundCode+"\" />"
			// 				+ "<span>"+fundData.label+"</span>"
			// 				+ "</label></abbr>"
			// 			);
			// 		};
			// 	},"json")
			// 	$drawReport.makeFilterBtn()
			// 	$(".-group-for_fund").find(".-check-count").addClass("-hidden")
			// })
		});
		</script>');

		head('<style type="text/css">
		#detail-list>tbody>tr>td:nth-child(n+2) {text-align: center;}
		.nav.-table-export {display: none;}
		.-checkbox>abbr>span {display: none; padding-left: 16px;"}
		</style>');

		if ($getHideMenu) {
			head('<style type="text/css">
			.page.-header {display: none;}
			.page.-content {padding-top: 8px;}
			.page.-footer {display: none;}
			.toolbar.-main .nav.-submodule {display: none;}
			</style>');
		}

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'แผนงานอำเภอ',
				'navigator' => [
					'info' => R::View('project.nav.planning'),
				], // navigator
			]), // AppBar
			'children' => [
				$reportBar,
				$ret,
				new FloatingActionButton([
					'children' => [
						$isAdd ? new Button([
							'url' => url('project/planning/ampur/add'),
							'icon' => 'add',
							'text' => 'สร้างแผนอำเภอ',
							'class' => 'sg-action -floating',
							'rel' => 'box',
							'boxWidth' => 320,
							'placeholder' => 'สร้างแผนงานอำเภอ',
						]) : NULL, // Button
					], // children
				]), // FloatingActionButton
				$script,
			]
		]);
	}
}
?>